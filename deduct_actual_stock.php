<?php
include("db.php");

if(!isset($_GET['id'])) die("Invalid request");

$invoice_id = (int)$_GET['id'];

// 1️⃣ Get invoice
$res = $conn->query("SELECT * FROM invoices WHERE id=$invoice_id");
$inv = $res->fetch_assoc();
if(!$inv) die("Invoice not found");

// If already Paid → don’t deduct again
if($inv['status'] == "Paid"){
    echo "success: already paid";
    exit;
}

// 2️⃣ Get invoice items
$resItems = $conn->query("SELECT * FROM invoice_items WHERE invoice_id=$invoice_id");
while($it = $resItems->fetch_assoc()){
    $pname = $it['product_name'];
    $qty   = (int)$it['qty'];

    // Deduct from actual_stock
    $upd = $conn->prepare("UPDATE products SET actual_stock = actual_stock - ? WHERE name = ?");
    $upd->bind_param("is", $qty, $pname);
    $upd->execute();
}

// 3️⃣ Mark invoice as Paid
$conn->query("UPDATE invoices SET status='Paid' WHERE id=$invoice_id");

echo "success";
?>
