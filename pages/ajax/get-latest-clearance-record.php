<?php
$stmt = $pdo->prepare("SELECT * FROM karachi_clearance ORDER BY id DESC LIMIT 1");
$stmt->execute();
$record = $stmt->fetch(PDO::FETCH_ASSOC);
header('Content-Type: application/json');
echo json_encode($record);
?>