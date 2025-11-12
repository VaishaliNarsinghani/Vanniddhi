<?php
header('Content-Type: application/json; charset=utf-8');
include 'db.php';

$idsParam = $_GET['ids'] ?? '';
$ids = array_values(array_filter(array_map('intval', explode(',', $idsParam)), fn($v)=>$v>0));
if (!$ids) { echo json_encode([]); exit; }

$place = implode(',', array_fill(0, count($ids), '?'));
$types = str_repeat('i', count($ids));
$sql = "SELECT id,name,price,thumbnail,category FROM products WHERE id IN ($place)";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$ids);
$stmt->execute();
$res = $stmt->get_result();
$out = [];
while($row = $res->fetch_assoc()) $out[] = $row;
echo json_encode($out);
