<?php // /pay/webhook.php
require __DIR__.'/common.php';
header('Content-Type: application/json');

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) { $data = $_POST; } // some PGs send form-encoding
log_pay('webhook', ['raw'=>$data]);

$order_id = $data['order_id'] ?? '';
$amount   = $data['amount']   ?? '';
$sign     = $data['signature']?? '';
$status   = strtolower($data['status'] ?? '');

if (!$order_id || !$amount || !$sign) { http_response_code(400); echo json_encode(['ok'=>false]); exit; }

// Signature rule could be different for webhook; check docs
$dataString = (cfg()['MERCHANT_ID']).'|'.$order_id.'|'.$amount.'|'.(cfg()['CURRENCY']);
if (!verify_signature($dataString, $sign)) { http_response_code(403); echo json_encode(['ok'=>false]); exit; }

$conn = db();
// load payment
$stmt = $conn->prepare("SELECT * FROM payments WHERE order_id=? LIMIT 1");
$stmt->bind_param('s', $order_id); $stmt->execute();
$pay = $stmt->get_result()->fetch_assoc();
if (!$pay) { http_response_code(404); echo json_encode(['ok'=>false]); exit; }

$newStatus = ($status==='success') ? 'SUCCESS' : (($status==='cancelled')?'CANCELLED':'FAILED');
if ($pay['status'] !== $newStatus) {
  $u = $conn->prepare("UPDATE payments SET status=?, raw_response=? WHERE id=?");
  $rawResp = json_encode($data, JSON_UNESCAPED_UNICODE);
  $u->bind_param('ssi', $newStatus, $rawResp, $pay['id']); $u->execute();

  if ($newStatus==='SUCCESS') {
    $conn->query("UPDATE invoices SET status='Paid' WHERE id=".intval($pay['invoice_id'])." LIMIT 1");
    // TODO: call your stock deduction, SMS/WhatsApp, email, etc.
  }
}

echo json_encode(['ok'=>true]);
