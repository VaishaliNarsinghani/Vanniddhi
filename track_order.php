<?php
// track_order.php
include("db.php");

$err = null; $order = null; $items = [];
if ($_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['order_id']) || isset($_GET['phone']))) {
  $order_id = trim($_GET['order_id'] ?? '');
  $phone    = trim($_GET['phone'] ?? '');

  // Build query attempts
  $sql = ""; $params = []; $types = "";
  if ($order_id !== "") {
    // Try id or order_code
    $sql = "SELECT * FROM orders WHERE id = ? OR order_code = ? LIMIT 1";
    $params = [$order_id, $order_id];
    $types = "ss";
  } elseif ($phone !== "") {
    $sql = "SELECT * FROM orders WHERE phone = ? ORDER BY created_at DESC LIMIT 1";
    $params = [$phone];
    $types = "s";
  }

  if ($sql) {
    if ($stmt = $conn->prepare($sql)) {
      $stmt->bind_param($types, ...$params);
      if ($stmt->execute()) {
        $res = $stmt->get_result();
        $order = $res->fetch_assoc();
        if (!$order) $err = "No matching order found.";
      } else {
        $err = "Could not run lookup.";
      }
      $stmt->close();
    } else {
      $err = "Orders table not found or schema mismatch.";
    }
  }

  // Fetch line items (best effort)
  if ($order) {
    $orderIdForItems = $order['id'] ?? null;
    if ($orderIdForItems) {
      if ($q = $conn->prepare("
          SELECT oi.product_id, oi.qty, oi.price,
                 COALESCE(p.name, oi.product_name) AS name,
                 COALESCE(p.thumbnail, '') as thumbnail
          FROM order_items oi
          LEFT JOIN products p ON p.id = oi.product_id
          WHERE oi.order_id = ?
      ")) {
        $q->bind_param("i", $orderIdForItems);
        if ($q->execute()) {
          $items = $q->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        $q->close();
      }
    }
  }
}
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Track Order • Vanniddhi</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;background:#f6faf6;margin:0}
    .container{max-width:1100px;margin:0 auto;padding:20px}
    header{background:#fff;border-bottom:1px solid #eee;position:sticky;top:0;z-index:10}
    header .container{display:flex;align-items:center;gap:14px}
    .logo{color:#2e7d32;font-weight:800;text-decoration:none;font-size:20px}
    h1{margin:22px 0 8px;color:#2e7d32}
    .card{background:#fff;border:1px solid #eaeaea;border-radius:12px;padding:18px}
    .grid{display:grid;grid-template-columns:380px 1fr;gap:18px;margin-top:18px}
    @media(max-width:980px){.grid{grid-template-columns:1fr}}
    label{display:block;font-weight:600;margin:8px 0 6px}
    input{width:100%;padding:12px;border:1px solid #dfe6df;border-radius:10px}
    button{margin-top:10px;background:#2e7d32;color:#fff;border:none;padding:12px 16px;border-radius:10px;font-weight:700;cursor:pointer}
    .muted{color:#6a7670;font-size:14px}
    .row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .alert{margin:12px 0;padding:10px 12px;border-radius:8px;background:#fff3cd;border:1px solid #ffe08a;color:#7c5c00}
    .ok{background:#e8f5e9;border:1px solid #c8e6c9;color:#1b5e20}
    table{width:100%;border-collapse:collapse;margin-top:10px}
    th,td{border:1px solid #eee;padding:10px;text-align:left}
    th{background:#fafafa}
    .pill{display:inline-block;padding:4px 10px;border-radius:999px;font-size:12px;background:#eef7ee;color:#2e7d32;border:1px solid #d7eed7}
  </style>
</head>
<body>
<header>
  <div class="container">
    <a class="logo" href="index.php"><i class="fa-solid fa-leaf"></i> Vanniddhi</a>
    <div style="margin-left:auto"><a href="index.php" style="text-decoration:none;color:#2e7d32;font-weight:600">← Back to Home</a></div>
  </div>
</header>

<main class="container">
  <h1>Track Your Order</h1>
  <p class="muted">Enter your Order ID or phone number.</p>

  <div class="grid">
    <section class="card">
      <form method="get">
        <label for="order_id">Order ID</label>
        <input id="order_id" name="order_id" placeholder="e.g. 1024 or VN-2025-001" value="<?= h($_GET['order_id'] ?? '') ?>">
        <div style="text-align:center;margin:8px 0">— or —</div>
        <label for="phone">Phone</label>
        <input id="phone" name="phone" placeholder="+91 XXXXX XXXXX" value="<?= h($_GET['phone'] ?? '') ?>">
        <button type="submit">Track</button>
      </form>
      <p class="muted" style="margin-top:8px">Need help? <a href="help_center.php">Contact support</a>.</p>
    </section>

    <section>
      <?php if ($err): ?>
        <div class="alert"><?= h($err) ?></div>
      <?php elseif ($order): ?>
        <div class="card">
          <h2 style="margin-top:0">Order Summary</h2>
          <p><strong>Order:</strong> <?= h($order['order_code'] ?? $order['id']) ?></p>
          <p><strong>Status:</strong> <span class="pill"><?= h($order['status'] ?? 'Processing') ?></span></p>
          <p><strong>Placed on:</strong> <?= h($order['created_at'] ?? '') ?></p>
          <p><strong>Customer:</strong> <?= h($order['customer_name'] ?? '') ?> <?= $order['phone'] ? '• '.h($order['phone']) : '' ?></p>
          <?php if (!empty($order['shipping_address'])): ?>
            <p><strong>Shipping:</strong> <?= nl2br(h($order['shipping_address'])) ?></p>
          <?php endif; ?>
          <?php if (isset($order['total'])): ?>
            <p><strong>Total:</strong> ₹<?= h(number_format((float)$order['total'], 2)) ?></p>
          <?php endif; ?>

          <?php if ($items): ?>
            <h3>Items</h3>
            <table>
              <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Line Total</th></tr></thead>
              <tbody>
                <?php $sum = 0; foreach ($items as $it): 
                  $line = (float)($it['qty']*$it['price']); $sum += $line; ?>
                  <tr>
                    <td><?= h($it['name'] ?? 'Item') ?></td>
                    <td><?= (int)$it['qty'] ?></td>
                    <td>₹<?= h(number_format((float)$it['price'],2)) ?></td>
                    <td>₹<?= h(number_format($line,2)) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot><tr><th colspan="3" style="text-align:right">Subtotal</th><th>₹<?= h(number_format($sum,2)) ?></th></tr></tfoot>
            </table>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="alert ok">Enter details and click “Track”.</div>
      <?php endif; ?>
    </section>
  </div>
</main>
</body>
</html>
