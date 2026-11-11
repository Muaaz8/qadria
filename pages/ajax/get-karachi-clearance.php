<?php
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM karachi_clearance WHERE id = ?");
$stmt->execute([$id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);
header('Content-Type: application/json');
echo json_encode($record);
?>