<?php
// db.php - improved connection with clear errors and optional port
$host = "127.0.0.1";    // use 127.0.0.1 to force TCP (helps with some XAMPP config)
$port = 3306;           // default MySQL port; change if your MySQL uses different port
$user = "root";
$pass = "";             // put your MySQL root password here if you set one
$db   = "vanniddhi_db";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $db, $port);
    $conn->set_charset("utf8mb4"); // set charset
} catch (mysqli_sql_exception $e) {
    // Friendly error message and helpful debug info (remove in production)
    $msg  = "Database Connection Failed.\n";
    $msg .= "Error: " . $e->getMessage() . "\n";
    $msg .= "Tried: {$host}:{$port} user={$user} db={$db}\n";
    // use die() so the script stops like your original code did
    die(nl2br(htmlspecialchars($msg)));
}
?>
