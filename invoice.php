<?php
include "db.php";

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM invoices WHERE id=?");
$stmt->execute([$id]);
$invoice = $stmt->fetch();

$cart = json_decode($invoice["cart"], true);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Invoice <?= $invoice["invoice_no"] ?></title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body>
  <h1>🔥 Safal Sales Invoice 🔥</h1>
  <p><b>Invoice No:</b> <?= $invoice["invoice_no"] ?><br>
  <b>Date:</b> <?= date("d/m/Y", strtotime($invoice["created_at"])) ?></p>

  <p><b>Bill To:</b><br>
  <?= htmlspecialchars($invoice["name"]) ?><br>
  <?= htmlspecialchars($invoice["email"]) ?><br>
  <?= htmlspecialchars($invoice["phone"]) ?><br>
  <?= nl2br(htmlspecialchars($invoice["address"])) ?></p>

  <table border="1" cellpadding="6" cellspacing="0" width="100%">
    <tr><th>Item</th><th>Price</th><th>Qty</th><th>Total</th></tr>
    <?php
    $grandTotal = 0;
    foreach($cart as $c){
      $lineTotal = $c["price"] * $c["qty"];
      $grandTotal += $lineTotal;
      echo "<tr>
              <td>{$c["name"]}</td>
              <td>₹{$c["price"]}</td>
              <td>{$c["qty"]}</td>
              <td>₹{$lineTotal}</td>
            </tr>";
    }
    ?>
    <tr>
      <td colspan="3"><b>Total</b></td>
      <td><b>₹<?= $grandTotal ?></b></td>
    </tr>
  </table>

  <script>
    window.onload = function() {
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF();

      doc.setFontSize(18);
      doc.text("🔥 Safal Sales Invoice 🔥", 20, 20);
      doc.setFontSize(12);

      doc.text("Invoice No: <?= $invoice["invoice_no"] ?>", 20, 30);
      doc.text("Date: <?= date("d/m/Y", strtotime($invoice["created_at"])) ?>", 150, 30, { align: "right" });

      doc.text("Name: <?= addslashes($invoice["name"]) ?>", 20, 50);
      doc.text("Email: <?= addslashes($invoice["email"]) ?>", 20, 58);
      doc.text("Phone: <?= addslashes($invoice["phone"]) ?>", 20, 66);
      doc.text("Address: <?= addslashes($invoice["address"]) ?>", 20, 74);

      doc.text("Order Details:", 20, 90);

      let y = 100;
      let total = 0;
      <?php foreach($cart as $c): ?>
        doc.text("<?= addslashes($c["name"]) ?> (x<?= $c["qty"] ?>) - ₹<?= number_format($c["price"]*$c["qty"], 2) ?>", 20, y);
        y += 10;
      <?php endforeach; ?>

      doc.text("Total: ₹<?= number_format($grandTotal, 2) ?>", 20, y + 10);

      doc.save("invoice_<?= $invoice["invoice_no"] ?>.pdf");
    }
  </script>
</body>
</html>
