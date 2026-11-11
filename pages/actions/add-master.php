<?php
if (!isAdmin()) exit();
$data = json_decode(file_get_contents('php://input'), true);
$stmt = $pdo->prepare("INSERT INTO masters (type, code, name, sort_order) VALUES (?, ?, ?, ?)");
$stmt->execute([$data['type'], $data['code'], $data['name'], $data['sort_order']]);
echo json_encode(['success'=>true]);
?>