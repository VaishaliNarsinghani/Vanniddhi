<?php
session_start();
if(!isset($_SESSION['admin'])) { 
    header("Location: admin_login.php"); 
    exit; 
}
include("db.php");
if(!isset($_SESSION['admin'])) { header("Location: admin_login.php"); exit; }
include("db.php");

$id = $_GET['id'];

// Get product name before delete
$res = $conn->prepare("SELECT name FROM products WHERE id=?");
$res->bind_param("i", $id);
$res->execute();
$res->bind_result($name);
$res->fetch();
$res->close();

// Delete
$stmt = $conn->prepare("DELETE FROM products WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

// ✅ Log activity
$admin = $_SESSION['admin'];
$action = "Deleted product: $name (ID $id)";
$log = $conn->prepare("INSERT INTO activities (admin_user, action) VALUES (?, ?)");
$log->bind_param("ss", $admin, $action);
$log->execute();

header("Location: admin_products.php");
exit;
if(isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // ✅ Get product first
    $stmt = $conn->prepare("SELECT image, video FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $product = $res->fetch_assoc();

    if($product) {
        // ✅ Delete image if exists
        if(!empty($product['image']) && file_exists(__DIR__ . "/" . $product['image'])) {
            unlink(__DIR__ . "/" . $product['image']);
        }

        // ✅ Delete video if exists
        if(!empty($product['video']) && file_exists(__DIR__ . "/" . $product['video'])) {
            unlink(__DIR__ . "/" . $product['video']);
        }

        // ✅ Delete from DB
        $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}

// Redirect back to products page
header("Location: admin_products.php");
exit;
