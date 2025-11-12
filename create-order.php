<?php // /pay/create-order.php
require __DIR__.'/common.php';

header('Content-Type: text/html; charset=UTF-8');

$c = cfg();
if (empty($c['MERCHANT_ID']) || empty($c['HMAC_SECRET'])) {
  http_response_code(503);
  echo "PG_NOT_CONFIGURED: Merchant keys missing.";
  exit;
}

$payload = json_decode(file_get_contents('php://input'), true) ?? [];
$customer = $payload['customer'] ?? [];
$cart     = $payload['cart'] ?? [];
$amount   = floatval($payload['amount'] ?? 0);
$currency = $payload['currency'] ?? $c['CURRENCY'];

if (!$cart || $amount <= 0 || empty($customer['name']) || empty($customer['phone'])) {
  http_response_code(422);
  echo "Invalid payload";
  exit;
}

$conn = db();
$conn->begin_transaction();

try {
  // 1) Create invoice draft
  $invoice_number = 'INV-'.mt_rand(100000,999999);
  $stmt = $conn->prepare("INSERT INTO invoices (invoice_number,name,phone,address,referred_person,total,status,created_at) VALUES (?,?,?,?,?,?, 'Pending', NOW())");
  $addr = $customer['address'] ?? '';
  $ref  = $customer['ref'] ?? '';
  $stmt->bind_param('sssssd', $invoice_number, $customer['name'], $customer['phone'], $addr, $ref, $amount);
  $stmt->execute();
  $invoice_id = $stmt->insert_id;

  // 2) Save items (NOTE: ideally amount recompute here)
  $itStmt = $conn->prepare("INSERT INTO invoice_items (invoice_id,product_name,qty,price,description) VALUES (?,?,?,?,?)");
  foreach ($cart as $it) {
    $nm = (string)($it['name'] ?? '');
    $qty = intval($it['qty'] ?? 1);
    $price = floatval($it['price'] ?? 0);
    $desc = (string)($it['desc'] ?? '');
    $itStmt->bind_param('isids', $invoice_id, $nm, $qty, $price, $desc);
    $itStmt->execute();
  }

  // 3) Create payment row
  $order_id = 'VN-'.time().'-'.$invoice_id; // must be unique per order
  $pStmt = $conn->prepare("INSERT INTO payments (invoice_id, provider, order_id, amount, currency, status, created_at) VALUES (?,?,?,?,?,'INIT', NOW())");
  $prov = 'ICICI';
  $pStmt->bind_param('issds', $invoice_id, $prov, $order_id, $amount, $currency);
  $pStmt->execute();

  $conn->commit();

} catch (Throwable $e) {
  $conn->rollback();
  log_pay('error', ['step'=>'create-order', 'err'=>$e->getMessage()]);
  http_response_code(500);
  echo "Server error";
  exit;
}

/*
 * 4) Build PG form
 * ---- VERY IMPORTANT ----
 * Bank aapko exact parameter names + signature string order dega.
 * Neeche ek common pattern diya hai:
 * dataString = merchant_id|order_id|amount|currency
 */
$merchant_id = $c['MERCHANT_ID'];
$access_code = $c['ACCESS_CODE']; // optional
$amount_str  = number_format($amount, 2, '.', ''); // Rs with 2 decimals
$dataString  = $merchant_id.'|'.$order_id.'|'.$amount_str.'|'.$currency;
$signature   = sign_string($dataString);

// Auto-submit HTML
$pg = htmlspecialchars(pgUrl(), ENT_QUOTES);
$ret= htmlspecialchars($c['RETURN_URL'], ENT_QUOTES);
$wh = htmlspecialchars($c['WEBHOOK_URL'], ENT_QUOTES);

// Return an auto-submitting form (hosted checkout)
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Redirecting…</title></head>
<body onload="document.forms[0].submit()" style="font-family:system-ui">
  <p>Redirecting to secure payment… please wait.</p>
  <form method="post" action="<?= $pg ?>">
    <input type="hidden" name="merchant_id" value="<?= htmlspecialchars($merchant_id) ?>">
    <input type="hidden" name="access_code"  value="<?= htmlspecialchars($access_code) ?>">
    <input type="hidden" name="order_id"     value="<?= htmlspecialchars($order_id) ?>">
    <input type="hidden" name="amount"       value="<?= htmlspecialchars($amount_str) ?>">
    <input type="hidden" name="currency"     value="<?= htmlspecialchars($currency) ?>">
    <input type="hidden" name="return_url"   value="<?= $ret ?>">
    <input type="hidden" name="webhook_url"  value="<?= $wh ?>">
    <!-- Customer hints (optional fields – PG dependent) -->
    <input type="hidden" name="customer_name"  value="<?= htmlspecialchars($customer['name']) ?>">
    <input type="hidden" name="customer_phone" value="<?= htmlspecialchars($customer['phone']) ?>">
    <input type="hidden" name="billing_address" value="<?= htmlspecialchars($customer['address'] ?? '') ?>">
    <!-- HMAC signature -->
    <input type="hidden" name="signature" value="<?= htmlspecialchars($signature) ?>">
    <noscript><button type="submit">Continue</button></noscript>
  </form>
</body></html>
