<?php
require_once 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// ✅ Turn off warnings/extra output
error_reporting(0);
ob_clean();

include("db.php"); // your DB connection

if (!isset($_GET['id'])) { die("Invoice ID missing."); }
$invoice_id = (int)$_GET['id'];

// Fetch invoice details
$inv = $conn->query("SELECT * FROM invoices WHERE id = $invoice_id")->fetch_assoc();
$items = $conn->query("SELECT * FROM invoice_items WHERE invoice_id = $invoice_id");

// ✅ Setup Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

// ✅ Build HTML dynamically
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Invoice #<?= $inv['invoice_number'] ?></title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    h2 { color: #d6681aff; text-align: center; }
    .customer-info { margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    table, th, td { border: 1px solid #000; }
    th, td { padding: 8px; text-align: left; }
    th { background: #d6681aff; color: #fff; }
    .total-row td { font-weight: bold; }
  </style>
</head>
<body>
  <h2>Invoice #<?= htmlspecialchars($inv['invoice_number']) ?></h2>
  <div class="customer-info">
    <p><b><?= htmlspecialchars($inv['name']) ?></b></p>
    <p>Email: <?= htmlspecialchars($inv['email']) ?></p>
    <p>Phone: <?= htmlspecialchars($inv['phone']) ?></p>
    <p>Date: <?= $inv['created_at'] ?></p>
  </div>

  <table>
    <tr>
      <th>Product</th>
      <th>Description</th>
      <th>Qty</th>
      <th>Rate (₹)</th>
      <th>Total (₹)</th>
    </tr>
    <?php $i=1; while($it=$items->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($it['product_name']) ?></td>
      <td><?= !empty($it['description']) ? htmlspecialchars($it['description']) : '—' ?></td>
      <td><?= (int)$it['qty'] ?></td>
      <td><?= number_format($it['price'], 2) ?></td>
      <td><?= number_format($it['qty'] * $it['price'], 2) ?></td>
    </tr>
    <?php endwhile; ?>
    <tr class="total-row">
      <td colspan="4" style="text-align:right;">Grand Total</td>
      <td>₹<?= number_format($inv['total'], 2) ?></td>
    </tr>
  </table>

  <p style="margin-top:30px; text-align:center;">🎆 Thank you for shopping at <b>Safal Sales</b> 🎆</p>
</body>
</html>
<?php
$html = ob_get_clean();

// ✅ Generate PDF
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// ✅ Output
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="Invoice-'.$inv['invoice_number'].'.pdf"');
echo $dompdf->output();
exit;
?>
