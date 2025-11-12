<?php // /pay/env.php
return [
  'MODE'         => 'TEST', // change to PROD on go-live
  'MERCHANT_ID'  => 'XXXX_FROM_BANK',
  'ACCESS_CODE'  => 'XXXX_FROM_BANK',   // optional; some PGs use it
  'HMAC_SECRET'  => 'XXXX_FROM_BANK',
  'WEBHOOK_SECRET' => 'XXXX_FROM_BANK', // if given, else leave ''
  'CURRENCY'     => 'INR',
  'RETURN_URL'   => 'https://yourdomain.com/pay/return.php',
  'WEBHOOK_URL'  => 'https://yourdomain.com/pay/webhook.php',

  // Gateway endpoints
  'PG_URL_TEST'  => 'https://pg-test.example.com/checkout',
  'PG_URL_PROD'  => 'https://pg.example.com/checkout',
];
