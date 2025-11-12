<?php
include("db.php"); // make sure path is correct

$filter = $_GET['filter'] ?? 'all';

if($filter === 'featured') {
    $res = $conn->query("SELECT * FROM products WHERE featured=1");
} elseif($filter === 'lowstock') {
    $res = $conn->query("SELECT * FROM products WHERE online_stock < 5");
} else {
    $res = $conn->query("SELECT * FROM products");
}

if($res->num_rows == 0){
    echo "<div>No products found.</div>";
} else {
    while($p = $res->fetch_assoc()){
        echo "<div style='padding:10px; border-bottom:1px solid #eee;'>";
        echo "<strong>{$p['name']}</strong> | Category: {$p['category']} | Price: ₹{$p['price']} | Stock: {$p['online_stock']}";
        echo "</div>";
    }
}
