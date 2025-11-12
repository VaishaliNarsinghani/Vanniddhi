<?php // /pay/common.php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function db(): mysqli {
  static $conn;
  if ($conn) return $conn;
  // aapki real DB settings:
  $conn = new mysqli("localhost","root","","vanniddhi_db");
  $conn->set_charset('utf8mb4');
  return $conn;
}
function cfg(): array { static $c; if(!$c) $c = require __DIR__.'/env.php'; return $c; }
function pgUrl(): string { $c = cfg(); return $c['MODE']==='PROD' ? $c['PG_URL_PROD'] : $c['PG_URL_TEST']; }

function sign_string(string $data): string {
  $secret = cfg()['HMAC_SECRET'];
  return hash_hmac('sha256', $data, $secret);
}
function verify_signature(string $data, string $signature): bool {
  return hash_equals(sign_string($data), $signature);
}
function log_pay(string $file, array $arr): void {
  $line = date('c').' '.json_encode($arr, JSON_UNESCAPED_UNICODE).PHP_EOL;
  @file_put_contents(__DIR__."/logs_$file.log", $line, FILE_APPEND);
}
