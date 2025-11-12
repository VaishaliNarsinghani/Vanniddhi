<?php
session_start();
if(!isset($_SESSION['admin'])) { 
    header("Location: admin_login.php"); 
    exit; 
}
include("db.php");

// Get product by id
if(!isset($_GET['id'])) { 
    header("Location: admin_products.php"); 
    exit; 
}
$id = (int)$_GET['id'];
$product = $conn->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();
if(!$product){ 
    die("Product not found"); 
}

if(isset($_POST['update'])){
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $description = $_POST['description']; // NEW
    $online_stock = $_POST['online_stock'];
    $actual_stock = $_POST['actual_stock'];
    $featured = isset($_POST['featured']) ? 1 : 0;

    $uploadDir = __DIR__ . "/uploads/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    // Handle image uploads
    $images = ['image1','image2','image3','thumbnail'];
    $imagePaths = [];
    foreach($images as $img){
        $imagePaths[$img] = $product[$img]; // keep old if not uploaded
        if(!empty($_FILES[$img]['name'])){
            $newName = "uploads/".time()."_".basename($_FILES[$img]['name']);
            move_uploaded_file($_FILES[$img]['tmp_name'], __DIR__.'/'.$newName);
            $imagePaths[$img] = $newName;
        }
    }

    // Handle video upload or URL
    $videoPath = $product['video'];
    if(!empty($_FILES['video']['name'])){
        $videoPath = "uploads/".time()."_".basename($_FILES['video']['name']);
        move_uploaded_file($_FILES['video']['tmp_name'], __DIR__.'/'.$videoPath);
    } elseif(!empty($_POST['video_url'])){
        $videoPath = $_POST['video_url'];
    }

    // Update product including description
    $stmt = $conn->prepare("UPDATE products SET name=?, category=?, price=?, description=?, online_stock=?, actual_stock=?, image1=?, image2=?, image3=?, thumbnail=?, video=?, featured=? WHERE id=?");
    $stmt->bind_param(
        "ssdsiisssssii", 
        $name, $category, $price, $description, $online_stock, $actual_stock, 
        $imagePaths['image1'], $imagePaths['image2'], $imagePaths['image3'], $imagePaths['thumbnail'], 
        $videoPath, $featured, $id
    );
    $stmt->execute();

    // Log activity
    $admin = $_SESSION['admin'];
    $action = "Updated product: $name (ID $id)";
    $log = $conn->prepare("INSERT INTO activities (admin_user, action) VALUES (?, ?)");
    $log->bind_param("ss", $admin, $action);
    $log->execute();

    header("Location: admin_products.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Product</title>
<style>
body { font-family: Arial, sans-serif; background: #f5f6fa; padding: 20px; }
form { background: #fff; padding: 20px; border-radius: 10px; max-width: 600px; margin: auto; }
input, button, textarea { width: 100%; padding: 10px; margin: 8px 0; border-radius: 6px; border: 1px solid #ccc; }
button { background: #d6681aff; color: white; border: none; cursor: pointer; }
button:hover { background: #e03e00; }
</style>
</head>
<body>

<h2>Edit Product</h2>
<form method="post" enctype="multipart/form-data">
  <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
  <input type="text" name="category" value="<?= htmlspecialchars($product['category']) ?>" required>
  <input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" required>

  <!-- NEW: Description -->
  <label>Description:</label>
  <textarea name="description" rows="4" required><?= htmlspecialchars($product['description']) ?></textarea>

  <input type="number" name="online_stock" value="<?= $product['online_stock'] ?>" required>
  <input type="number" name="actual_stock" value="<?= $product['actual_stock'] ?>" required>

  <?php foreach(['image1','image2','image3','thumbnail'] as $img): ?>
    <label>Upload <?= ucfirst($img) ?>:</label>
    <input type="file" name="<?= $img ?>">
  <?php endforeach; ?>

  <label>Upload Video (PC):</label>
  <input type="file" name="video">
  <label>Or Video URL:</label>
  <input type="url" name="video_url" placeholder="https://example.com/video.mp4" value="<?= htmlspecialchars($product['video']) ?>">

  <label><input type="checkbox" name="featured" value="1" <?= $product['featured'] ? "checked" : "" ?>> Mark as Featured</label>
  
  <button type="submit" name="update">Update Product</button>
</form>
</body>
</html>
