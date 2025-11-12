<?php
session_start();
include("db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invoice_number = $_POST['invoice_number'];
    $cart    = json_decode($_POST['cart'], true);
    $total   = $_POST['total'];
    $name    = $_POST['name'];
    $email   = $_POST['email']; // (not used in insert, keeping as-is)
    $phone   = $_POST['phone'];
    $address = $_POST['address'];
    $referred_person = isset($_POST['referred_person']) ? $_POST['referred_person'] : ''; // ✅ added

    // ✅ Save invoice first (now includes referred_person)
    $stmt = $conn->prepare("INSERT INTO invoices 
        (invoice_number, name, phone, address, total, created_at, referred_person) 
        VALUES (?,?,?,?,?,NOW(),?)");
    $stmt->bind_param("sssdss", $invoice_number, $name, $phone, $address, $total, $referred_person);
    // Types explained: s s s s d s
    //   invoice_number(s), name(s), phone(s), address(s), total(d), referred_person(s)
    $stmt->execute();
    $invoice_id = $stmt->insert_id;

    // ✅ Now loop cart and save items
    foreach ($cart as $item) {
        $pname = $item['name'];
        $qty   = (int)$item['qty'];
        $price = (float)$item['price'];
        $lineTotal = $price * $qty;

        // Lock row
        $sel = $conn->prepare("SELECT id, online_stock FROM products WHERE name = ? FOR UPDATE");
        $sel->bind_param("s", $pname);
        $sel->execute();
        $res = $sel->get_result();
        if (!$prod = $res->fetch_assoc()) {
            throw new Exception("Product not found: " . $pname);
        }

        if ((int)$prod['online_stock'] < $qty) {
            throw new Exception("Insufficient stock for " . $pname);
        }

        // Deduct online stock
        $upd = $conn->prepare("UPDATE products 
            SET online_stock = online_stock - ? 
            WHERE id = ?");
        $upd->bind_param("ii", $qty, $prod['id']);
        $upd->execute();

        // ✅ Save invoice item
        $ins = $conn->prepare("INSERT INTO invoice_items 
            (invoice_id, product_name, qty, price, subtotal) 
            VALUES (?,?,?,?,?)");
        $ins->bind_param("isidd", $invoice_id, $pname, $qty, $price, $lineTotal);
        $ins->execute();
    }

    echo "<script>
        localStorage.removeItem('cart');
        alert('✅ Order placed successfully! Invoice ID: $invoice_id');
        window.location.href='cart.php';
    </script>";
}
?>
