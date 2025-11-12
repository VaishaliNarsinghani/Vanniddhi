<?php // /pay/return.php
require __DIR__.'/common.php';

$post = $_POST ?: $_GET; // some PGs use GET
log_pay('return', $post);

$order_id = $post['order_id'] ?? '';
$amount   = $post['amount']   ?? '';
$status   = strtolower($post['status'] ?? 'failed'); // 'success' / 'failed' / 'cancelled'
$txn_id   = $post['txn_id']   ?? ($post['transaction_id'] ?? '');
$sign     = $post['signature']?? '';

if (!$order_id || !$amount || !$sign) {
  echo "Invalid response"; exit;
}

/* Signature verify – use SAME concat rule used at create-order.php */
$dataString = (cfg()['MERCHANT_ID']).'|'.$order_id.'|'.$amount.'|'.(cfg()['CURRENCY']);
$ok = verify_signature($dataString, $sign);

$conn = db();
if ($ok) {
  // fetch payment
  $stmt = $conn->prepare("SELECT * FROM payments WHERE order_id=? LIMIT 1");
  $stmt->bind_param('s', $order_id); $stmt->execute();
  $pay = $stmt->get_result()->fetch_assoc();

  if ($pay) {
    $newStatus = ($status==='success') ? 'SUCCESS' : (($status==='cancelled')?'CANCELLED':'FAILED');

    // update payment
    $u = $conn->prepare("UPDATE payments SET status=?, txn_id=?, raw_response=? WHERE id=?");
    $raw = json_encode($post, JSON_UNESCAPED_UNICODE);
    $u->bind_param('sssi', $newStatus, $txn_id, $raw, $pay['id']); $u->execute();

    // update invoice
    if ($newStatus==='SUCCESS') {
      $conn->query("UPDATE invoices SET status='Paid' WHERE id=".intval($pay['invoice_id'])." LIMIT 1");
      // (Optional) deduct stock here or via webhook
    } else {
      $conn->query("UPDATE invoices SET status='Payment Failed' WHERE id=".intval($pay['invoice_id'])." LIMIT 1");
    }

    // Show result page
    $iid = (int)$pay['invoice_id'];
    if ($newStatus==='SUCCESS') {
      echo "<script>
        // clear cart on success
        try{ localStorage.removeItem('cart'); }catch(e){}
      </script>";
      echo "<h2 style='font-family:system-ui;color:#2e7d32'>Payment Successful</h2>
            <p>Txn: <b>".htmlspecialchars($txn_id)."</b></p>
            <p><a href='/invoice_preview.php?id=$iid'>View Invoice</a></p>";
    } else {
      echo "<h2 style='font-family:system-ui;color:#b00020'>Payment $newStatus</h2>
            <p><a href='/cart.php'>Go back to cart</a></p>";
    }
  } else {
    echo "Order not found";
  }
} else {
  echo "Signature mismatch";
}
