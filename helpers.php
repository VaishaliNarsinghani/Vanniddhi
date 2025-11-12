<?php
// helpers.php
function log_activity(mysqli $conn, string $message, string $action='general', ?string $entity_type=null, ?int $entity_id=null): void {
    $admin_id = isset($_SESSION['admin']) ? (int)$_SESSION['admin'] : null; // adjust key if you store another field/id
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

    $stmt = $conn->prepare("
        INSERT INTO admin_activity_logs (admin_id, action, entity_type, entity_id, message, ip, user_agent)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "ississs",
        $admin_id,
        $action,
        $entity_type,
        $entity_id,
        $message,
        $ip,
        $ua
    );
    $stmt->execute();
    $stmt->close();
}
