<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: admin_login.php"); exit; }

/* ---- Composer autoload (required for PDF/Spreadsheet parsing) ---- */
$autoload = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoload)) {
  die("Composer autoload not found. Run in project root: composer require smalot/pdfparser phpoffice/phpspreadsheet");
}
require $autoload;

use Smalot\PdfParser\Parser;
use PhpOffice\PhpSpreadsheet\IOFactory;

/* ---- DB ---- */
include("db.php");

/* ---- helpers ---- */
function normalize_text($s) {
  $s = mb_strtolower((string)$s, 'UTF-8');
  $s = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $s);
  $s = preg_replace('/\s+/', ' ', $s);
  return trim($s);
}
function clean_number_token($t) {
  $t = str_replace([',','₹','$','Rs.','rs.'], '', $t);
  $t = preg_replace('/[^\d\.]/', '', $t);
  return $t;
}

/* load products for matching */
$products = [];
$prodRes = $conn->query("SELECT id, name, COALESCE(actual_stock,0) AS stock FROM products");
while ($r = $prodRes->fetch_assoc()) {
  $products[] = ['id'=>(int)$r['id'], 'name'=>$r['name'], 'norm'=>normalize_text($r['name']), 'stock'=>(int)$r['stock']];
}
$prodMap = [];
foreach ($products as $p) $prodMap[$p['norm']] = $p;

/* matcher */
function match_product($candidate, $products, $prodMap, $conn, &$scoreOut = 0) {
  $scoreOut = 0;
  $candNorm = normalize_text($candidate);
  if ($candNorm === '') return [null, 0, 'empty'];

  if (isset($prodMap[$candNorm])) { $scoreOut = 100; return [$prodMap[$candNorm], $scoreOut, 'exact']; }

  $words = array_filter(explode(' ', $candNorm));
  $fragWords = array_slice($words, 0, 3);
  if (!empty($fragWords)) {
    $frag = implode(' ', $fragWords);
    $stmt = $conn->prepare("SELECT id, name, COALESCE(actual_stock,0) AS stock FROM products WHERE LOWER(name) LIKE CONCAT('%', ?, '%') LIMIT 1");
    $stmt->bind_param('s', $frag);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    if ($r) {
      $matched = ['id'=>(int)$r['id'],'name'=>$r['name'],'norm'=>normalize_text($r['name']),'stock'=>(int)$r['stock']];
      similar_text($candNorm, $matched['norm'], $p);
      $scoreOut = round($p,2);
      return [$matched, $scoreOut, 'like-db'];
    }
  }

  $best = null; $bestScore = 0;
  foreach ($products as $p) {
    $pWords = explode(' ', $p['norm']);
    foreach ($words as $w) {
      if (strlen($w) < 2) continue;
      if (in_array($w, $pWords)) {
        similar_text($candNorm, $p['norm'], $perc);
        if ($perc > $bestScore) { $bestScore = $perc; $best = $p; }
        break;
      }
    }
  }
  if ($bestScore == 0) {
    foreach ($products as $p) {
      similar_text($candNorm, $p['norm'], $perc);
      if ($perc > $bestScore) { $bestScore = $perc; $best = $p; }
    }
  }
  $scoreOut = round($bestScore,2);
  return [$best, $scoreOut, 'fuzzy'];
}

/* parse one line -> product + qty */
function parse_line_to_item($line) {
  $orig = trim($line);
  $line = preg_replace('/\s+/', ' ', $orig);

  if (preg_match('/^(invoice|bill|phone|mobile|date|gst|subtotal|total|grand total)/i', $line)) return null;

  // explicit pattern: Product — QTY Rate Total
  if (preg_match('/^(.+?)\s+—\s+(\d+)\s+[\d,.]+\s+[\d,.]+$/u', $line, $m)) {
    return ['product'=>trim($m[1]), 'qty'=>(int)$m[2], 'raw'=>$orig];
  }

  // wide columns -> look for last integer
  $cols = preg_split('/\s{2,}/', $line);
  if (count($cols) >= 2) {
    for ($i = count($cols)-1; $i >= 0; $i--) {
      $tok = trim($cols[$i]); $num = clean_number_token($tok);
      if ($num !== '' && preg_match('/^\d+$/', $num) && (int)$num < 100000) {
        $qty = (int)$num;
        $product = trim(implode(' ', array_slice($cols, 0, $i)));
        if ($product !== '' && $qty > 0) return ['product'=>$product,'qty'=>$qty,'raw'=>$orig];
      }
    }
  }

  // "Name ... 10", optional unit
  if (preg_match('/(.+?)\s+([0-9]{1,6})\s*(pcs|nos|units|pack|pkt)?\s*$/i', $line, $m)) {
    $product = trim($m[1]); $qty = (int)$m[2];
    if ($product !== '' && $qty>0) return ['product'=>$product,'qty'=>$qty,'raw'=>$orig];
  }

  // "10 Name"
  if (preg_match('/^\s*([0-9]{1,6})\s+(.+)$/', $line, $m2)) {
    $qty = (int)$m2[1]; $product = trim($m2[2]);
    if ($product !== '' && $qty>0) return ['product'=>$product,'qty'=>$qty,'raw'=>$orig];
  }

  // token-scan first integer
  if (preg_match_all('/\d+(?:[.,]\d+)?/', $line, $nums, PREG_OFFSET_CAPTURE)) {
    foreach ($nums[0] as $ninfo) {
      $num = $ninfo[0]; $pos = $ninfo[1];
      $ival = (int)clean_number_token($num);
      if ($ival > 0 && $ival < 100000) {
        $prod = trim(substr($line, 0, $pos));
        if ($prod !== '') return ['product'=>$prod,'qty'=>$ival,'raw'=>$orig];
      }
    }
  }
  return null;
}

/* parse uploaded file */
function parse_uploaded_file($fileTmp, $ext) {
  $lines = [];
  try {
    if ($ext === 'pdf') {
      $parser = new Parser();
      $pdf = $parser->parseFile($fileTmp);
      $text = $pdf->getText();
      $lines = preg_split("/\r\n|\n|\r/", $text);
    } elseif ($ext === 'csv') {
      if (($h = fopen($fileTmp, 'r')) !== false) {
        $first = fgetcsv($h, 0, ",");
        if ($first !== false) {
          $headLower = array_map('mb_strtolower', $first);
          $hasProductCol = $hasQtyCol = false;
          foreach ($headLower as $v) {
            if (stripos($v,'product')!==false || stripos($v,'item')!==false || stripos($v,'name')!==false) $hasProductCol = true;
            if (stripos($v,'qty')!==false || stripos($v,'quantity')!==false) $hasQtyCol = true;
          }
          if ($hasProductCol && $hasQtyCol) {
            $prodIdx = $qtyIdx = null;
            foreach ($headLower as $i=>$v) {
              if ($prodIdx===null && (stripos($v,'product')!==false || stripos($v,'item')!==false || stripos($v,'name')!==false)) $prodIdx=$i;
              if ($qtyIdx===null  && (stripos($v,'qty')!==false || stripos($v,'quantity')!==false)) $qtyIdx=$i;
            }
            while (($row = fgetcsv($h, 0, ",")) !== false) {
              $p = isset($row[$prodIdx]) ? trim($row[$prodIdx]) : '';
              $q = isset($row[$qtyIdx]) ? preg_replace('/[^\d]/','',$row[$qtyIdx]) : '';
              if ($p !== '' && $q !== '') $lines[] = $p.' '.(int)$q;
            }
            fclose($h);
            return $lines;
          } else {
            rewind($h);
            while (($row = fgetcsv($h, 0, ",")) !== false) $lines[] = implode(' ', $row);
            fclose($h);
          }
        }
      }
    } elseif ($ext === 'xls' || $ext === 'xlsx') {
      $spreadsheet = IOFactory::load($fileTmp);
      $sheetData = $spreadsheet->getActiveSheet()->toArray();
      if (!empty($sheetData)) {
        $head = array_map(fn($c)=>mb_strtolower((string)$c), $sheetData[0]);
        $hasProductCol = $hasQtyCol = false;
        foreach ($head as $v) {
          if (stripos($v,'product')!==false || stripos($v,'item')!==false || stripos($v,'name')!==false) $hasProductCol = true;
          if (stripos($v,'qty')!==false || stripos($v,'quantity')!==false) $hasQtyCol = true;
        }
        if ($hasProductCol && $hasQtyCol) {
          $prodIdx = $qtyIdx = null;
          foreach ($head as $i=>$v) {
            if ($prodIdx===null && (stripos($v,'product')!==false || stripos($v,'item')!==false || stripos($v,'name')!==false)) $prodIdx=$i;
            if ($qtyIdx===null  && (stripos($v,'qty')!==false || stripos($v,'quantity')!==false)) $qtyIdx=$i;
          }
          for ($r=1; $r<count($sheetData); $r++) {
            $row = $sheetData[$r];
            $p = isset($row[$prodIdx]) ? trim($row[$prodIdx]) : '';
            $q = isset($row[$qtyIdx]) ? preg_replace('/[^\d]/','',$row[$qtyIdx]) : '';
            if ($p !== '' && $q !== '') $lines[] = $p.' '.(int)$q;
          }
          return $lines;
        } else {
          foreach ($sheetData as $row) $lines[] = implode(' ', $row);
        }
      }
    } else {
      return ['error'=>'unsupported'];
    }
  } catch (Exception $ex) {
    return ['error'=>'parse_failed: '.$ex->getMessage()];
  }
  return $lines;
}

/* ---- handle upload ---- */
$results = [];
$debug = [];
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_FILES['order_file']) && $_FILES['order_file']['error']===0) {
  $fname = $_FILES['order_file']['name'];
  $tmp   = $_FILES['order_file']['tmp_name'];
  $ext   = strtolower(pathinfo($fname, PATHINFO_EXTENSION));

  $parsed = parse_uploaded_file($tmp, $ext);
  if (is_array($parsed) && isset($parsed['error'])) {
    $debug[] = "Parse error: ".$parsed['error'];
  } else {
    $lines = (array)$parsed;
    foreach ($lines as $line) {
      $item = parse_line_to_item($line);
      if ($item === null) { $debug[] = "Skipped: ".$line; continue; }

      $pname = preg_replace('/\b(pcs|nos|units|pack|pkt|piece|pieces)\b/i','', trim($item['product']));
      $qty   = (int)$item['qty'];

      $score = 0;
      [$matched, $score, $method] = match_product($pname, $products, $prodMap, $conn, $score);

      if ($matched && $score >= 65) {
        $matchedId   = $matched['id'];
        $matchedName = $matched['name'];
        $stock       = (int)$matched['stock'];
        $matchedBy   = $method;
      } else {
        $frag = implode(' ', array_slice(array_filter(explode(' ', normalize_text($pname))), 0, 3));
        $stmt = $conn->prepare("SELECT id, name, COALESCE(actual_stock,0) AS stock FROM products WHERE LOWER(name) LIKE CONCAT('%', ?, '%') LIMIT 1");
        $stmt->bind_param('s', $frag);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        if ($r) {
          $matchedId   = (int)$r['id'];
          $matchedName = $r['name'];
          $stock       = (int)$r['stock'];
          $matchedBy   = 'like-db-fallback';
          similar_text(normalize_text($pname), normalize_text($matchedName), $pscore);
          $score = round($pscore,2);
        } else {
          $matchedId = null; $matchedName = null; $stock = 0; $matchedBy = 'no-match';
        }
      }

      $status = '❓ Not found';
      if ($matchedName) {
        if ($stock >= $qty) $status = '✅ Available';
        elseif ($stock > 0) $status = "⚠️ Only $stock";
        else $status = '❌ Out of stock';
      }

      $results[] = [
        'raw'           => $item['raw'],
        'parsed_product'=> $pname,
        'ordered'       => $qty,
        'matched_id'    => $matchedId,
        'matched_name'  => $matchedName,
        'stock'         => $stock ?? 0,
        'score'         => $score,
        'matched_by'    => $matchedBy,
        'status'        => $status
      ];
    }
  }
} else if ($_SERVER['REQUEST_METHOD']==='POST') {
  $debug[] = "No valid upload detected. error=".(string)($_FILES['order_file']['error'] ?? 'no_file');
}

/* Counts for stat cards */
$cntAvail = $cntPartial = $cntOOS = $cntNF = 0;
foreach ($results as $r) {
  if (str_contains($r['status'],'✅')) $cntAvail++;
  elseif (str_contains($r['status'],'⚠')) $cntPartial++;
  elseif (str_contains($r['status'],'❌')) $cntOOS++;
  else $cntNF++;
}

/* Ticker text (same theme family) */
$news = [
  "💠 Upload customer order as PDF/CSV/Excel to auto-check stock 💠",
  "💠 Parsing supports mixed layouts & quantities 💠",
  "💠 Tip: keep product names close to catalog names for best matches 💠",
];
$tickerText = implode(' • ', array_map('htmlspecialchars', $news));
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin • Order Check | Vanniddhi</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root{
  --primary:#2e7d32; --primary2:#4caf50; --primary3:#1b5e20;
  --accent:#8bc34a; --warn:#ff9800; --danger:#e53935;
  --bg:#f8fdf8; --text:#2f3b33; --border:#e0e0e0; --white:#fff;
}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--text);font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif}
.container{max-width:1200px;margin:0 auto;padding:0 16px}

/* Ticker */
.top-bar{background:var(--primary3);color:#fff}
.ticker{height:40px;display:flex;align-items:center;gap:16px}
.ticker-line{white-space:nowrap;overflow:hidden;width:100%}
.ticker-line span{display:inline-block;padding-left:100%;animation:ticker 28s linear infinite}
@keyframes ticker{from{transform:translateX(0)} to{transform:translateX(-100%)}}

/* Header + Nav */
header{position:sticky;top:0;z-index:1000;background:var(--white);box-shadow:0 2px 10px rgba(0,0,0,.08)}
.header-wrap{display:flex;align-items:center;gap:12px;flex-wrap:wrap;padding:12px 0}
.logo{display:flex;align-items:center;gap:10px;text-decoration:none}
.logo i{font-size:1.8rem;color:var(--primary)}
.logo span{font-weight:800;font-size:1.4rem;color:var(--primary)}
.search{position:relative;flex:1;min-width:240px}
.search input{width:100%;height:42px;border:1px solid var(--border);border-radius:12px;padding:0 14px}
.search button{position:absolute;right:5px;top:5px;height:32px;border:none;border-radius:8px;background:var(--primary);color:#fff;padding:0 10px}
.h-actions{margin-left:auto;display:flex;align-items:center;gap:14px}
.h-actions a{color:var(--text);text-decoration:none;display:flex;flex-direction:column;align-items:center;font-size:.9rem}
.h-actions i{font-size:1.25rem}

/* Subnav */
nav.sub{background:#f1f6f1}
.sub-wrap{display:flex;justify-content:space-between;align-items:center;padding:10px 0}
.links{display:flex;gap:14px;flex-wrap:wrap}
.links a{text-decoration:none;color:var(--text);font-weight:700}
.links a:hover{color:var(--primary)}
.mobile-btn{display:none}
@media (max-width:900px){
  .mobile-btn{display:block}
  .links{display:none;position:fixed;inset:60px 0 0 0;background:#fff;padding:16px;flex-direction:column}
  .links.show{display:flex}
}

/* Layout */
main{padding:18px 0 28px}
.grid{display:grid;grid-template-columns:260px 1fr;gap:16px}
@media (max-width:980px){ .grid{grid-template-columns:1fr} }

/* Sidebar */
.sidebar{background:#fff;border:1px solid var(--border);border-radius:14px;padding:14px;position:sticky;top:86px}
.sb-title{font-family:Poppins,sans-serif;color:var(--primary);font-size:1.05rem;font-weight:700}
.sb a{display:flex;align-items:center;gap:10px;padding:10px;border-radius:10px;text-decoration:none;color:#2f3b33;font-weight:700;border:1px solid transparent;margin-top:8px}
.sb a:hover{background:#f4fbf4;border-color:#e0f2e0}
.sb a.active{background:#e8f5e9;border-color:#cfe8cf;color:var(--primary3)}

/* Panel */
.panel{background:#fff;border:1px solid var(--border);border-radius:14px;padding:14px;box-shadow:0 6px 16px rgba(0,0,0,.06)}
.panel h2{font-family:Poppins,sans-serif;color:#214a26;margin:4px 0 10px}

/* Upload */
.drop{border:2px dashed #cfe2cf;border-radius:14px;padding:18px;text-align:center;background:#fbfffb}
.drop.drag{background:#f0fbf0}
.drop input{display:none}
.drop .btn{display:inline-block;margin-top:10px}

/* Buttons & inputs */
.row{display:flex;flex-wrap:wrap;gap:8px;align-items:center}
.input,.select{height:40px;border:1px solid var(--border);border-radius:10px;padding:0 12px}
.btn{height:40px;padding:0 14px;border:none;border-radius:10px;font-weight:800;cursor:pointer}
.btn-primary{background:linear-gradient(135deg,var(--primary),var(--primary2));color:#fff}
.btn-plain{background:#fff;border:1px solid var(--border)}
.badge{display:inline-block;padding:4px 8px;border-radius:999px;font-size:.78rem;font-weight:800}

/* Stats */
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin:12px 0}
.stat{background:#fff;border:1px solid var(--border);border-radius:14px;padding:12px;text-align:center;font-weight:800}
.stat .num{display:block;font-size:1.4rem;color:var(--primary3)}
@media (max-width:640px){ .stats{grid-template-columns:1fr 1fr} }

/* Table */
.table-wrap{overflow:auto;border:1px solid var(--border);border-radius:14px;background:#fff}
table{width:100%;border-collapse:collapse}
th,td{padding:12px;border-bottom:1px solid #eee;text-align:left;font-size:.95rem}
th{background:#f9faf9}
tr:hover{background:#fafdfa}
.tag{display:inline-block;padding:3px 8px;border-radius:999px;border:1px solid #cfe2cf;background:#f5fff5;color:#214a26;font-weight:800;font-size:.78rem}

/* Status colors */
.s-available{background:#e6f6ea;color:#1b5e20;border:1px solid #cfe8d4}
.s-partial{background:#fff3e0;color:#8a5800;border:1px solid #f2dab2}
.s-out{background:#fdeaea;color:#8a0909;border:1px solid #f3c7c7}
.s-nf{background:#f1f1f1;color:#555;border:1px solid #ddd}

/* Overlay */
#overlay{position:fixed;inset:0;background:rgba(0,0,0,.25);display:none;place-items:center;z-index:2000}
.spinner{background:#fff;padding:16px 18px;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.2);font-weight:700}
/* ---- Logo container (reuses your .logo) ---- */
.logo{
  display:flex; align-items:center; gap:0px;
  text-decoration:none;
}

/* ---- Icon with gradient ring + soft glow ---- */
.logo-icon{
  width:100px; height:100px;
  position:relative; border-radius:12px;
}

.logo-icon img{
  position:relative; z-index:1;
  width:100%; height:100%; object-fit:contain; display:block;
  filter: saturate(1.1) contrast(1.02);
}

/* ---- Wordmark ---- */
.logo-wordmark{ display:flex; flex-direction:column; line-height:1; }
.logo-name{
  font-family: 'Poppins', system-ui, -apple-system, Segoe UI, Roboto, 'Open Sans', sans-serif;
  font-weight:800; letter-spacing:.2px;
  font-size: clamp(1.15rem, 2.2vw, 1.4rem);
  background: linear-gradient(135deg, var(--primary,#2e7d32), var(--primary-light,#4caf50));
  -webkit-background-clip: text; background-clip: text; color: transparent;
  text-shadow: 0 1px 0 rgba(255,255,255,.25);
}
.logo-tag{
  margin-top:4px;
  font-weight:700; font-size:.72rem; letter-spacing:.12em; text-transform:uppercase;
  color: #5c7a60;
  opacity:.95;
}

/* ---- Hover micro-interaction ---- */
.logo:hover .logo-icon{ transform: translateY(-1px); }
.logo:hover .logo-name{ filter: brightness(1.05); }

/* ---- Compact on small screens ---- */
@media (max-width: 560px){
  .logo{ gap:10px; }
  .logo-icon{ width:40px; height:40px; flex-basis:40px; }
  .logo-tag{ display:none; }   /* keep header tidy on phones */
}

</style>
</head>
<body>

<!-- Ticker -->
<div class="top-bar">
  <div class="container ticker">
    <div class="ticker-line"><span>&nbsp;&nbsp;<?= $tickerText ?> • <?= $tickerText ?></span></div>
  </div>
</div>

<!-- Header -->
<header>
  <div class="container header-wrap">
    <a class="logo" href="index.php"><i class="fas fa-leaf"></i><span>Vanniddhi</span></a>
    <div class="search"><input id="qTop" type="text" placeholder="Quick search in results…"><button type="button"><i class="fas fa-search"></i></button></div>
    <div class="h-actions">
      <a href="admin_dashboard.php"><i class="fas fa-shield-halved"></i><span>Admin</span></a>
      <a href="logout.php"><i class="fas fa-right-from-bracket"></i><span>Logout</span></a>
    </div>
  </div>
  <nav class="sub">
    <div class="container sub-wrap">
      <div class="mobile-btn"><button id="navToggle" class="btn btn-plain"><i class="fas fa-bars"></i></button></div>
      <div class="links" id="navLinks">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="admin_products.php">Products</a>
        <a href="admin_orders.php">Orders</a>
        <a href="admin_check.php" style="color:var(--primary)">Order Check</a>
      </div>
      <a class="btn btn-primary" href="products.php">Special Offers</a>
    </div>
  </nav>
</header>

<main class="container">
  <div class="grid">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sb-title">Admin Panel</div>
      <nav class="sb">
        <a href="admin_dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
        <a href="admin_products.php"><i class="fa-solid fa-seedling"></i> Products</a>
        <a href="admin_orders.php"><i class="fa-solid fa-box"></i> Orders / Invoices</a>
        <a href="admin_check.php" class="active"><i class="fa-solid fa-clipboard-check"></i> Order Check</a>
        <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
      </nav>
    </aside>

    <!-- Main -->
    <section>
      <div class="panel">
        <h2>Upload Order (PDF / CSV / Excel)</h2>

        <!-- Uploader -->
        <form id="uForm" method="post" enctype="multipart/form-data" class="drop" ondragover="this.classList.add('drag');event.preventDefault();" ondragleave="this.classList.remove('drag');" ondrop="this.classList.remove('drag'); document.getElementById('file').files = event.dataTransfer.files; this.submit();">
          <input id="file" type="file" name="order_file" accept=".pdf,.csv,.xls,.xlsx" required>
          <div><i class="fa-solid fa-cloud-arrow-up"></i> Drag & drop file here or</div>
          <label for="file" class="btn btn-plain" style="margin-top:8px">Browse…</label>
          <div class="row" style="margin-top:10px;gap:10px">
            <button type="button" id="dlTemplate" class="btn btn-plain"><i class="fa-solid fa-file-csv"></i>&nbsp;Download CSV template</button>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-wand-magic-sparkles"></i>&nbsp;Check Inventory</button>
          </div>
        </form>

        <?php if (!empty($results)): ?>
          <!-- Stats -->
          <div class="stats">
            <div class="stat"><span class="num"><?= $cntAvail ?></span>✅ Available</div>
            <div class="stat"><span class="num"><?= $cntPartial ?></span>⚠️ Partial</div>
            <div class="stat"><span class="num"><?= $cntOOS ?></span>❌ Out of stock</div>
            <div class="stat"><span class="num"><?= $cntNF ?></span>❓ Not found</div>
          </div>

          <!-- Filters -->
          <div class="row" style="margin:10px 0">
            <input id="q" class="input" type="search" placeholder="Search in table…">
            <select id="statusSel" class="select" title="Filter by status">
              <option value="all">All statuses</option>
              <option value="available">Available</option>
              <option value="partial">Partial</option>
              <option value="out">Out of stock</option>
              <option value="nf">Not found</option>
            </select>
            <label class="row" style="gap:6px;margin-left:8px">
              <span style="font-weight:700">Min match %</span>
              <input id="scoreMin" type="range" min="0" max="100" step="1" value="0">
              <span id="scoreVal" class="badge" style="border:1px solid var(--border)">0</span>
            </label>
            <button id="clearBtn" class="btn btn-plain"><i class="fa-solid fa-rotate-left"></i>&nbsp;Clear</button>
            <button id="csvBtn" class="btn btn-primary"><i class="fa-solid fa-file-csv"></i>&nbsp;Export CSV</button>
          </div>

          <!-- Table -->
          <div class="table-wrap">
            <table id="tbl">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Raw Line</th>
                  <th>Parsed Product</th>
                  <th>Ordered</th>
                  <th>Matched</th>
                  <th>Stock</th>
                  <th>Match %</th>
                  <th>Method</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($results as $i => $r):
                  $statusClass = 's-nf'; $statusKey='nf';
                  if (str_contains($r['status'],'✅')) { $statusClass='s-available'; $statusKey='available'; }
                  elseif (str_contains($r['status'],'⚠')) { $statusClass='s-partial'; $statusKey='partial'; }
                  elseif (str_contains($r['status'],'❌')) { $statusClass='s-out'; $statusKey='out'; }
                  $score = (float)$r['score'];
                  $matchLink = $r['matched_id'] ? ('edit_product.php?id='.(int)$r['matched_id']) : null;
                ?>
                <tr data-status="<?= $statusKey ?>" data-score="<?= $score ?>" data-haystack="<?= htmlspecialchars(strtolower($r['raw'].' '.$r['parsed_product'].' '.$r['matched_name'].' '.$r['matched_by']), ENT_QUOTES) ?>">
                  <td><?= $i+1 ?></td>
                  <td><?= htmlspecialchars($r['raw']) ?></td>
                  <td><?= htmlspecialchars($r['parsed_product']) ?></td>
                  <td><?= (int)$r['ordered'] ?></td>
                  <td>
                    <?php if($r['matched_name']): ?>
                      <span class="tag"><?= htmlspecialchars($r['matched_name']) ?></span>
                    <?php else: ?>
                      <span class="badge s-nf">—</span>
                    <?php endif; ?>
                  </td>
                  <td><?= (int)$r['stock'] ?></td>
                  <td><?= $score ?>%</td>
                  <td><?= htmlspecialchars($r['matched_by']) ?></td>
                  <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars($r['status']) ?></span></td>
                  <td>
                    <?php if($matchLink): ?>
                      <a class="btn btn-plain" href="<?= $matchLink ?>" title="Edit product" target="_blank" rel="noopener"><i class="fa-regular fa-pen-to-square"></i></a>
                    <?php else: ?>
                      <button class="btn btn-plain copyBtn" title="Copy parsed name" data-copy="<?= htmlspecialchars($r['parsed_product'], ENT_QUOTES) ?>"><i class="fa-regular fa-copy"></i></button>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>

        <?php if (!empty($debug)): ?>
          <div style="margin-top:12px" class="panel">
            <h3 style="margin:0 0 8px;color:#1b5e20;font-family:Poppins">Debug</h3>
            <ul style="margin:0 0 6px 18px">
              <?php foreach ($debug as $d): ?>
                <li><?= htmlspecialchars($d) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </div>
</main>

<footer style="background:#1a331c;color:#fff">
  <div class="container" style="padding:22px 0;text-align:center">&copy; <?= date('Y') ?> Vanniddhi Plant Nursery. All rights reserved.</div>
</footer>

<!-- overlay -->
<div id="overlay"><div class="spinner"><i class="fa-solid fa-leaf"></i> Analyzing file…</div></div>

<script>
/* mobile nav */
document.getElementById('navToggle')?.addEventListener('click',()=>document.getElementById('navLinks')?.classList.toggle('show'));

/* submit overlay */
const uForm = document.getElementById('uForm');
if (uForm) uForm.addEventListener('submit', ()=>{ document.getElementById('overlay').style.display='grid'; });

/* CSV template */
document.getElementById('dlTemplate')?.addEventListener('click', ()=>{
  const csv = 'Product,Qty\\nRose Plant,2\\nBamboo Stick,10\\n';
  const blob = new Blob([csv], {type:'text/csv;charset=utf-8;'});
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = 'order_template.csv';
  document.body.appendChild(a); a.click(); a.remove();
  URL.revokeObjectURL(a.href);
});

/* Filters */
const tbl = document.getElementById('tbl');
const qTop = document.getElementById('qTop');
const q = document.getElementById('q');
const statusSel = document.getElementById('statusSel');
const scoreMin = document.getElementById('scoreMin');
const scoreVal = document.getElementById('scoreVal');
const clearBtn = document.getElementById('clearBtn');
const csvBtn = document.getElementById('csvBtn');

function applyFilter(){
  const text = ((q?.value||'') + ' ' + (qTop?.value||'')).trim().toLowerCase();
  const st = (statusSel?.value||'all');
  const smin = parseInt(scoreMin?.value||'0',10);
  scoreVal && (scoreVal.textContent = smin);

  if (!tbl) return;
  const rows = tbl.querySelectorAll('tbody tr');
  rows.forEach(r=>{
    const hay = r.dataset.haystack || '';
    const rs  = r.dataset.status || '';
    const sc  = parseFloat(r.dataset.score || '0');
    let show = true;
    if (text && !hay.includes(text)) show=false;
    if (st!=='all' && rs!==st) show=false;
    if (sc < smin) show=false;
    r.style.display = show ? '' : 'none';
  });
}
[qTop,q,statusSel,scoreMin].forEach(el=> el && el.addEventListener('input', applyFilter));
clearBtn?.addEventListener('click', ()=>{
  if(qTop) qTop.value=''; if(q) q.value=''; if(statusSel) statusSel.value='all'; if(scoreMin){ scoreMin.value=0; scoreVal.textContent='0'; }
  applyFilter();
});
applyFilter();

/* Copy buttons */
document.querySelectorAll('.copyBtn').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    const t = btn.dataset.copy || '';
    navigator.clipboard.writeText(t).then(()=>{ btn.innerHTML='<i class="fa-solid fa-check"></i>'; setTimeout(()=>btn.innerHTML='<i class="fa-regular fa-copy"></i>',900); });
  });
});

/* Export visible rows to CSV */
csvBtn?.addEventListener('click', ()=>{
  if (!tbl) return;
  const rows = Array.from(tbl.querySelectorAll('tr')).filter(r=> r.style.display !== 'none');
  const data = rows.map(r => Array.from(r.querySelectorAll('th,td')).map(c => (c.innerText||'').replace(/\s+/g,' ').trim()));
  const csv = data.map(row => row.map(v=>'"'+v.replace(/"/g,'""')+'"').join(',')).join('\\n');
  const blob = new Blob([csv], {type:'text/csv;charset=utf-8;'});
  const a = document.createElement('a');
  const stamp = new Date().toISOString().slice(0,19).replace(/[:T]/g,'-');
  a.href = URL.createObjectURL(blob);
  a.download = `order_check_${stamp}.csv`;
  document.body.appendChild(a); a.click(); a.remove();
  URL.revokeObjectURL(a.href);
});
</script>
</body>
</html>
