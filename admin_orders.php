<?php
session_start();
if(!isset($_SESSION['admin'])) { header("Location: admin_login.php"); exit; }
include "db.php";

/* Fetch invoices (newest first) */
$invoices = $conn->query("SELECT * FROM invoices ORDER BY created_at DESC");

/* Simple stats */
$stat = $conn->query("SELECT COUNT(*) AS c, COALESCE(SUM(total),0) AS s FROM invoices")->fetch_assoc();
$total_invoices = (int)$stat['c'];
$total_revenue  = (float)$stat['s'];

/* Theme ticker */
$news = [
  "💠 Welcome to Vanniddhi Plant Nursery — premium plants & decor 💠",
  "💠 Free delivery on orders above ₹999 (Indore city limits) 💠",
  "💠 Same-day dispatch on prepaid orders placed before 4 PM 💠",
  "💠 Diwali Season: Extended hours — store open till 11 PM, all week 💠",
  "💠 Pick-up: Shop No. 04, Temp. Cracker Market, Chhota Bangarda 💠",
  "💠 Need help choosing? Expert support & plant care guidance 💠",
  "💠 Preserved Nature Tabletops & Moss Frames now in stock 💠",
  "💠 Combo packs & bulk orders available — limited stock 💠",
  "💠 Secure payments: UPI / Card / Cash on pickup 💠",
  "💠 Helpline: 94250 46286 💠"
];

$tickerText = implode(' • ', array_map('htmlspecialchars', $news));

/* Tiny helper to parse referred_person (name + phone) */
function split_ref_person(?string $raw): array {
  $raw = trim((string)$raw);
  if ($raw === '') return ['name'=>'','phone'=>''];
  $phone = '';
  if (preg_match('/(\+?\d[\d\s\-]{8,}\d)/', $raw, $m)) {
    $phone = preg_replace('/\s+/', ' ', trim($m[1]));
  }
  $name = $raw;
  if ($phone !== '') {
    $name = trim(preg_replace('/'.preg_quote($phone,'/').'/', '', $name, 1));
    $name = trim(preg_replace('/[-:|,]+$/', '', $name));
  }
  if ($name === '' && $raw !== '') $name = $raw;
  return ['name'=>$name,'phone'=>$phone];
}

/* Status -> badge class */
function status_class($s){
  $s = strtolower(trim((string)$s));
  return match($s){
    'paid','completed'   => 'ok',
    'cancelled','failed' => 'bad',
    'processing'         => 'warn',
    default              => 'muted',
  };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin • Orders/Invoices | Vanniddhi</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  :root{
    --primary:#2e7d32; --primary-light:#4caf50; --primary-dark:#1b5e20;
    --secondary:#ff9800; --accent:#8bc34a;
    --light:#f8fdf8; --dark:#1a331c; --text:#333; --white:#fff; --gray:#f5f5f5; --border:#e0e0e0;
  }
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Open Sans',sans-serif;background:var(--light);color:var(--text);line-height:1.6}
  .container{width:100%;max-width:1200px;margin:0 auto;padding:0 16px}

  /* Top ticker */
  .top-bar{background:var(--primary-dark);color:#fff;font-size:.92rem}
  .top-bar-content{min-height:40px;display:flex;align-items:center;gap:16px}
  .ticker-track{overflow:hidden;white-space:nowrap;flex:1}
  .ticker-line{display:inline-block;padding-left:100%;animation:ticker 28s linear infinite}
  @keyframes ticker{from{transform:translateX(0)} to{transform:translateX(-100%)}}
  .top-bar-links a{color:#fff;text-decoration:none;margin-left:18px}
  .top-bar-links a:hover{color:var(--accent)}

  /* Header/Nav */
  header{background:#fff;box-shadow:0 2px 10px rgba(0,0,0,.08);position:sticky;top:0;z-index:1000}
  .header-container{display:flex;justify-content:space-between;align-items:center;padding:0px 0;gap:12px;flex-wrap:wrap}
  .logo{display:flex;align-items:center;gap:10px;text-decoration:none}
  .logo-icon{font-size:2rem;color:var(--primary)}
  .logo-text{font-size:1.6rem;font-weight:700;color:var(--primary)}
  .search-bar{flex:1;max-width:700px;position:relative;order:3;width:100%}
  .search-bar input{width:100%;height:44px;padding:0 16px;border:1px solid var(--border);border-radius:12px;outline:none}
  .search-bar button{position:absolute;right:6px;top:5px;height:34px;padding:0 14px;border:none;border-radius:10px;background:var(--primary);color:#fff}
  .header-actions{display:flex;align-items:center}
  .header-action{position:relative;margin-left:20px;display:flex;flex-direction:column;align-items:center;color:var(--text);text-decoration:none}
  .header-action i{font-size:1.4rem;margin-bottom:4px}
  nav{background:var(--gray);padding:12px 0}
  .nav-container{display:flex;justify-content:space-between;align-items:center}
  .nav-links{list-style:none;display:flex;gap:15px}
  .nav-links a{color:var(--text);text-decoration:none;font-weight:600}
  .nav-links a:hover{color:var(--primary)}
  .mobile-menu{display:none;font-size:1.5rem;color:var(--primary);cursor:pointer}
  @media (max-width:992px){
    .mobile-menu{display:block}
    .nav-links{display:none;position:fixed;inset:60px 0 0 0;background:#fff;padding:18px 20px;flex-direction:column;gap:12px;overflow:auto;z-index:1200;box-shadow:0 12px 30px rgba(0,0,0,.12)}
    .nav-links.show{display:flex}
  }

  /* Layout */
  main{padding:18px 0 28px}
  .wrap{display:grid;grid-template-columns:260px 1fr;gap:18px}
  @media (max-width:980px){ .wrap{grid-template-columns:1fr} }

  .sidebar{
    background:#fff;border:1px solid var(--border);border-radius:14px;padding:14px;
    position:sticky;top:86px
  }
  .sb-title{font-family:'Poppins',sans-serif;color:var(--primary);font-size:1.1rem;margin-bottom:8px}
  .sb-links{display:flex;flex-direction:column;gap:8px;margin-top:8px}
  .sb-links a{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;text-decoration:none;color:#2f3b33;font-weight:700;border:1px solid transparent}
  .sb-links a:hover{background:#f4fbf4;border-color:#e0f2e0}
  .sb-links a.active{background:#e8f5e9;border-color:#cfe8cf;color:var(--primary-dark)}

  .panel{background:#fff;border:1px solid var(--border);border-radius:14px;padding:16px;box-shadow:0 6px 16px rgba(0,0,0,.06)}
  .panel h2{font-family:'Poppins',sans-serif;color:#214a26;font-size:1.2rem;margin-bottom:10px}

  /* Stats */
  .stats{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px}
  .stat{background:#fff;border:1px solid var(--border);border-radius:14px;padding:14px;text-align:center;font-weight:800;box-shadow:0 6px 16px rgba(0,0,0,.06)}
  .stat .big{display:block;font-size:1.6rem;color:var(--primary-dark)}
  @media (max-width:520px){ .stats{grid-template-columns:1fr} }

  /* Filters row */
  .filters{display:flex;flex-wrap:wrap;gap:8px;margin:12px 0}
  .input,.select{height:40px;padding:0 12px;border:1px solid var(--border);border-radius:10px}
  .btn{display:inline-grid;place-items:center;height:40px;padding:0 14px;border:none;border-radius:10px;font-weight:800;cursor:pointer}
  .btn-primary{background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff}
  .btn-plain{background:#fff;border:1px solid var(--border)}

  /* Table */
  .table-wrap{overflow:auto;border:1px solid var(--border);border-radius:14px;background:#fff;box-shadow:0 6px 16px rgba(0,0,0,.06)}
  table{width:100%;border-collapse:collapse}
  th,td{padding:12px;border-bottom:1px solid #eee;text-align:left;font-size:.95rem;vertical-align:middle}
  th{background:#f9faf9}
  tr:hover{background:#fafdfa}
  .badge{display:inline-block;padding:4px 8px;border-radius:999px;font-size:.78rem;font-weight:800}
  .badge.ok{background:#e6f6ea;color:#1b5e20;border:1px solid #cce7d3}
  .badge.warn{background:#fff7e6;color:#8a5800;border:1px solid #f5e1b5}
  .badge.bad{background:#fdeaea;color:#8a0909;border:1px solid #f3c7c7}
  .badge.muted{background:#f1f1f1;color:#555;border:1px solid #ddd}
  .tag{display:inline-block;padding:3px 8px;border-radius:999px;border:1px solid #cfe2cf;background:#f5fff5;color:#214a26;font-weight:800;font-size:.78rem}
  .btn-action{display:inline-block;padding:8px 12px;margin:2px;border-radius:8px;font-size:.9rem;text-decoration:none;font-weight:700}
  .btn-orange{background:#ff9800;color:#fff}
  .btn-blue{background:#1976d2;color:#fff}
  .btn-green{background:#2e7d32;color:#fff}
  .muted{color:#888}
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
    <div class="container top-bar-content">
      <div class="ticker-track" aria-label="Announcements">
        <div class="ticker-line">&nbsp;&nbsp;<?= $tickerText ?> &nbsp;&nbsp;&bull;&nbsp;&nbsp; <?= $tickerText ?></div>
      </div>
      <div class="top-bar-links">
        <a href="#"><i class="fas fa-question-circle"></i> Help</a>
        <a href="#"><i class="fas fa-map-marker-alt"></i> Store</a>
        <a href="#"><i class="fas fa-truck"></i> Track</a>
      </div>
    </div>
  </div>

  <!-- Header / Nav -->
  <header>
    <div class="container header-container">
       <a href="index.php" class="logo" aria-label="Vanniddhi – home">
 <div class="logo-icon">
    <img src="vanniddhi.png" alt="Vanniddhi logo" class="logo-img">
  </div>
  <span class="logo-wordmark">
    <span class="logo-name">Vanniddhi</span>
    <span class="logo-tag">Plant Nursery</span>
  </span>
</a>

      <div class="search-bar">
        <input id="qTop" type="text" placeholder="Quick search (name, phone, invoice)…">
        <button><i class="fas fa-search"></i></button>
      </div>

      <div class="header-actions">
        <a href="admin_dashboard.php" class="header-action"><i class="fas fa-shield-halved"></i><span>Admin</span></a>
        <a href="logout.php" class="header-action"><i class="fas fa-right-from-bracket"></i><span>Logout</span></a>
      </div>

      <div class="mobile-menu"><i class="fas fa-bars"></i></div>
    </div>
    <nav>
      <div class="container nav-container">
        <ul class="nav-links">
          <li><a href="index.php">Home</a></li>
          <li><a href="products.php">Products</a></li>
          <li><a href="admin_dashboard.php">Admin</a></li>
          <li><a href="admin_products.php">Manage Products</a></li>
        </ul>
        <div class="nav-offer"><a href="products.php" class="btn btn-primary" style="height:38px;padding:0 16px">Special Offers</a></div>
      </div>
    </nav>
  </header>

  <main class="container">
    <div class="wrap">
      <!-- Sidebar -->
      <aside class="sidebar">
        <div class="sb-title">Admin Panel</div>
        <nav class="sb-links">
          <a href="admin_dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
          <a href="admin_products.php"><i class="fa-solid fa-seedling"></i> Products</a>
          <a class="active" href="admin_orders.php"><i class="fa-solid fa-box"></i> Orders / Invoices</a>
          <a href="admin_check.php"><i class="fa-solid fa-clipboard-check"></i> Order Check</a>
          <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </nav>
      </aside>

      <!-- Main -->
      <section>
        <div class="panel">
          <h2>Orders / Invoices</h2>

          <div class="stats">
            <div class="stat"><span class="big"><?= number_format($total_invoices) ?></span>Total Invoices</div>
            <div class="stat"><span class="big">₹<?= number_format($total_revenue,2) ?></span>Total Revenue</div>
          </div>

          <!-- Filters -->
          <div class="filters">
            <input id="q" class="input" type="search" placeholder="Search…">
            <select id="statusSel" class="select">
              <option value="all">All Statuses</option>
              <option value="paid">Paid</option>
              <option value="processing">Processing</option>
              <option value="pending">Pending</option>
              <option value="cancelled">Cancelled</option>
            </select>
            <input id="fromDate" class="input" type="date" title="From date">
            <input id="toDate" class="input" type="date" title="To date">
            <button id="clearBtn" class="btn btn-plain"><i class="fa-solid fa-rotate-left"></i>&nbsp;Clear</button>
            <button id="csvBtn" class="btn btn-primary"><i class="fa-solid fa-file-csv"></i>&nbsp;Export CSV</button>
          </div>

          <!-- Table -->
          <div class="table-wrap">
            <table id="tbl">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Customer</th>
                  <th>Phone</th>
                  <th>Referred (Name & Phone)</th>
                  <th>Total</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
              <?php while($inv = $invoices->fetch_assoc()):
                $ref = split_ref_person($inv['referred_person'] ?? '');
                $status = $inv['status'] ?? 'Pending';
                $cls = status_class($status);
                $created = $inv['created_at'] ?? '';
                $iso = substr($created,0,10); // YYYY-MM-DD for dataset filter
                $waNumber = preg_replace('/\D+/','',$inv['phone'] ?? '');
                $waMsg = rawurlencode("Hello ".($inv['name']??'').", regarding invoice ".$inv['invoice_number']." (₹".number_format((float)$inv['total'],2).").");
              ?>
                <tr data-status="<?= htmlspecialchars(strtolower($status)) ?>" data-date="<?= htmlspecialchars($iso) ?>">
                  <td><?= htmlspecialchars($inv['invoice_number']) ?></td>
                  <td><?= htmlspecialchars($inv['name']) ?></td>
                  <td>
                    <?= htmlspecialchars($inv['phone']) ?>
                    <?php if($waNumber): ?>
                      <a class="btn-action btn-green" href="https://wa.me/<?= $waNumber ?>?text=<?= $waMsg ?>" target="_blank" rel="noopener" title="WhatsApp">WhatsApp</a>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if(($inv['referred_person'] ?? '')===''): ?>
                      <span class="muted">—</span>
                    <?php else: ?>
                      <?php if($ref['name']!==''): ?><div><span class="tag"><?= htmlspecialchars($ref['name']) ?></span></div><?php endif; ?>
                      <?php if($ref['phone']!==''): ?><div>📞 <?= htmlspecialchars($ref['phone']) ?></div><?php endif; ?>
                      <?php if($ref['name']==='' && $ref['phone']===''): ?>
                        <div><?= nl2br(htmlspecialchars($inv['referred_person'])) ?></div>
                      <?php endif; ?>
                    <?php endif; ?>
                  </td>
                  <td>₹<?= number_format((float)$inv['total'], 2) ?></td>
                  <td><?= htmlspecialchars($created) ?></td>
                  <td><span class="badge <?= $cls ?>"><?= htmlspecialchars($status) ?></span></td>
                  <td>
                    <a class="btn-action btn-orange" href="invoice_preview.php?id=<?= (int)$inv['id'] ?>" target="_blank" rel="noopener">Download Bill</a>
                    <a class="btn-action btn-blue" href="invoice_items.php?id=<?= (int)$inv['id'] ?>">👁 View Items</a>
                  </td>
                </tr>
              <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </div>
  </main>

  <footer style="background:var(--dark);color:#fff">
    <div class="container" style="padding:26px 0;text-align:center">&copy; <?= date('Y') ?> Vanniddhi Plant Nursery. All rights reserved.</div>
  </footer>

<script>
  // Mobile nav
  (function(){
    const b=document.querySelector('.mobile-menu'), n=document.querySelector('.nav-links');
    if(!b||!n) return; b.addEventListener('click',()=>n.classList.toggle('show'));
    n.addEventListener('click',e=>{ if(e.target.closest('a')) n.classList.remove('show'); });
  })();

  // Filters + CSV
  const qTop = document.getElementById('qTop');
  const q = document.getElementById('q');
  const statusSel = document.getElementById('statusSel');
  const fromDate = document.getElementById('fromDate');
  const toDate = document.getElementById('toDate');
  const clearBtn = document.getElementById('clearBtn');
  const csvBtn = document.getElementById('csvBtn');
  const rows = Array.from(document.querySelectorAll('#tbl tbody tr'));

  function applyFilter(){
    const text = ((q?.value || '') + ' ' + (qTop?.value || '')).trim().toLowerCase();
    const st = (statusSel?.value || 'all').toLowerCase();
    const f = (fromDate?.value || '');
    const t = (toDate?.value || '');
    rows.forEach(r=>{
      const hay = r.textContent.toLowerCase();
      const rs = (r.dataset.status || '');
      const rd = (r.dataset.date || '0000-00-00');
      let show = true;
      if (text && !hay.includes(text)) show = false;
      if (st !== 'all' && rs !== st) show = false;
      if (f && rd < f) show = false;
      if (t && rd > t) show = false;
      r.style.display = show ? '' : 'none';
    });
  }
  [q,qTop,statusSel,fromDate,toDate].forEach(el=> el && el.addEventListener('input', applyFilter));
  clearBtn?.addEventListener('click', ()=>{
    if(q) q.value=''; if(qTop) qTop.value=''; if(statusSel) statusSel.value='all';
    if(fromDate) fromDate.value=''; if(toDate) toDate.value='';
    applyFilter();
  });
  applyFilter();

  csvBtn?.addEventListener('click', ()=>{
    const table = document.getElementById('tbl');
    const rows = Array.from(table.querySelectorAll('tr')).filter(r=> r.style.display !== 'none');
    const getText = (el)=> {
      // remove buttons text like "👁"
      return (el.innerText || '').replace(/\s+/g,' ').trim();
    };
    const data = rows.map(r => Array.from(r.querySelectorAll('th,td')).map(getText));
    const csv = data.map(row => row.map(v=>{
      v = v.replace(/"/g,'""');
      return `"${v}"`;
    }).join(',')).join('\n');
    const blob = new Blob([csv], {type:'text/csv;charset=utf-8;'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    const stamp = new Date().toISOString().slice(0,19).replace(/[:T]/g,'-');
    a.href = url; a.download = `invoices_${stamp}.csv`;
    document.body.appendChild(a); a.click(); a.remove();
    URL.revokeObjectURL(url);
  });
</script>
</body>
</html>
