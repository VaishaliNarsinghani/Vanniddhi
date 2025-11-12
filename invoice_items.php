<?php
include("db.php");

$invoice_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($invoice_id <= 0) { http_response_code(400); die("Invoice ID missing."); }

/* Fetch invoice (prepared) */
$stmt = $conn->prepare("SELECT * FROM invoices WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$inv = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$inv) { http_response_code(404); die("Invoice not found."); }

/* Fetch items */
$stmt = $conn->prepare("SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY id ASC");
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$items = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Invoice #<?= htmlspecialchars($inv['invoice_number']) ?> • Vanniddhi</title>

<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
  :root{
    --primary:#2e7d32; --primary-light:#4caf50; --primary-dark:#1b5e20;
    --accent:#8bc34a; --secondary:#ffb300;
    --bg:#f6fbf6; --card:#fff; --text:#2f3a2f; --muted:#647565; --border:#e3ecdf;
  }
  *{box-sizing:border-box}
  html,body{margin:0}
  body{
    font-family:'Open Sans',system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;
    background: radial-gradient(1200px 600px at 20% -10%, #eaf7ea 0, transparent 60%) no-repeat,
                radial-gradient(1200px 600px at 100% 0, #eff9ef 0, transparent 60%) no-repeat,
                var(--bg);
    color:var(--text);
    padding:32px 16px;
  }

  /* Header */
  .brand{
    max-width:1100px;margin:0 auto 14px;display:flex;align-items:center;gap:12px;
  }
  .brand .logo{width:48px;height:48px}
  .brand .logo img{width:100%;height:100%;object-fit:contain;display:block}
  .brand .name{
    display:flex;flex-direction:column;line-height:1;
  }
  .brand .title{
    font-family:'Poppins',sans-serif;font-weight:800;letter-spacing:.2px;
    font-size:1.35rem;
    background:linear-gradient(135deg,var(--primary),var(--primary-light));
    -webkit-background-clip:text;background-clip:text;color:transparent;
  }
  .brand .tag{font-size:.8rem;font-weight:700;color:#5e745f;letter-spacing:.12em;text-transform:uppercase;margin-top:4px}

  /* Invoice card */
  .invoice{
    max-width:1100px;margin:0 auto;background:var(--card);
    border:1px solid var(--border);border-radius:16px;box-shadow:0 10px 24px rgba(0,0,0,.06);
    overflow:hidden;
  }
  .inv-head{
    display:flex;gap:20px;justify-content:space-between;align-items:flex-start;
    padding:22px 22px 8px 22px;
  }
  .inv-id h2{
    margin:0;color:var(--primary-dark);font-family:'Poppins',sans-serif;font-weight:700;
    font-size:1.4rem
  }
  .inv-id .chip{
    display:inline-block;margin-top:6px;padding:6px 10px;border-radius:999px;
    background:#e8f5e9;color:var(--primary-dark);font-weight:800;font-size:.85rem
  }
  .cust{
    text-align:right;color:var(--muted);font-size:.95rem
  }
  .cust p{margin:.15rem 0}
  .cust b{color:#2f3a2f}

  /* table */
  .table-wrap{padding:6px 22px 22px 22px}
  .table{
    width:100%;border-collapse:separate;border-spacing:0;overflow:hidden;border-radius:12px;border:1px solid var(--border);
    background:#fff;
  }
  .table th{
    background:linear-gradient(135deg,var(--primary),var(--primary-light));
    color:#fff;font-weight:700;text-align:left;padding:14px;font-size:.95rem;border-bottom:1px solid rgba(255,255,255,.2);
  }
  .table td{
    padding:14px;border-bottom:1px solid var(--border);font-size:.95rem;vertical-align:top;
  }
  .table tr:last-child td{border-bottom:none}
  .table tr:hover td{background:#fbfdfb}
  .num{text-align:right;white-space:nowrap}

  .total-row td{
    background:#f7faf7;font-weight:900;color:var(--primary-dark);border-top:2px solid var(--border);
  }

  /* footer + actions */
  .inv-actions{
    display:flex;gap:10px;justify-content:flex-end;padding:0 22px 22px 22px;
  }
  .btn{
    display:inline-flex;align-items:center;gap:8px;border:0;height:44px;padding:0 16px;border-radius:12px;cursor:pointer;
    font-weight:800;text-decoration:none;
  }
  .btn-primary{background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;box-shadow:0 10px 18px rgba(46,125,50,.25)}
  .btn-outline{background:#fff;color:var(--primary-dark);border:1px solid var(--border)}
  .btn i{font-size:1rem}

  .inv-foot{
    padding:14px 22px 22px 22px;text-align:center;color:#6b786d;font-size:.95rem;border-top:1px dashed var(--border);
  }

  /* print */
  @media print{
    body{padding:0;background:#fff}
    .brand,.inv-actions{display:none !important}
    .invoice{box-shadow:none;border:0;border-radius:0}
  }
</style>
</head>
<body>

  <!-- Brand header -->
  <div class="brand">
    <div class="logo"><img src="vanniddhi.png" alt="Vanniddhi logo"></div>
    <div class="name">
      <span class="title">Vanniddhi</span>
      <span class="tag">Plant Nursery</span>
    </div>
  </div>

  <!-- Invoice card -->
  <div class="invoice" id="invoice-content">
    <div class="inv-head">
      <div class="inv-id">
        <h2>Invoice #<?= htmlspecialchars($inv['invoice_number']) ?></h2>
        <div class="chip">Date: <?= htmlspecialchars($inv['created_at']) ?></div>
      </div>
      <div class="cust">
        <p><b><?= htmlspecialchars($inv['name']) ?></b></p>
        <p><b>Phone:</b> <?= htmlspecialchars($inv['phone']) ?></p>
        <p><b>Referred:</b>
          <?= ($inv['referred_person'] ?? '')!=='' ? nl2br(htmlspecialchars($inv['referred_person'])) : '—' ?>
        </p>
        <?php if (!empty($inv['address'])): ?>
        <p><b>Address:</b> <?= nl2br(htmlspecialchars($inv['address'])) ?></p>
        <?php endif; ?>
      </div>
    </div>

    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th style="width:58px">S.No</th>
            <th style="width:28%">Product</th>
            <th>Description</th>
            <th class="num" style="width:80px">Qty</th>
            <th class="num" style="width:120px">Rate (₹)</th>
            <th class="num" style="width:140px">Total (₹)</th>
          </tr>
        </thead>
        <tbody>
        <?php
          $i=1; $grand = 0.00;
          while($it = $items->fetch_assoc()):
            $qty = (int)$it['qty'];
            $rate = (float)$it['price'];
            $rowt = $qty * $rate;
            $grand += $rowt;
        ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($it['product_name']) ?></td>
            <td><?= !empty($it['description']) ? htmlspecialchars($it['description']) : '—' ?></td>
            <td class="num"><?= $qty ?></td>
            <td class="num"><?= number_format($rate, 2) ?></td>
            <td class="num"><?= number_format($rowt, 2) ?></td>
          </tr>
        <?php endwhile; ?>
          <tr class="total-row">
            <td colspan="5" class="num">Grand Total</td>
            <td class="num">₹<?= number_format($inv['total'] ?? $grand, 2) ?></td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="inv-actions no-print">
      <a class="btn btn-outline" href="javascript:window.print()"><i class="fa-solid fa-print"></i> Print</a>
      <a class="btn btn-primary btn-download" href="download_invoice.php?id=<?= (int)$inv['id'] ?>">
        <i class="fa-solid fa-file-arrow-down"></i> Download PDF
      </a>
    </div>

    <div class="inv-foot">
      Thank you for shopping with <b>Vanniddhi Plant Nursery</b> • Need help? <b>+91 94250 46286</b> • hello@vanniddhi.com
    </div>
  </div>

</body>
</html>
