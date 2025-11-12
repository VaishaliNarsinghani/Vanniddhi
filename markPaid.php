<?php
include("db.php");
if (!isset($_GET['id'])) { die("Invalid request"); }
$id = intval($_GET['id']);

$conn->query("UPDATE invoices SET status='Paid' WHERE id=$id");
echo "success";
?>
