<?php
if (!isAdmin()) exit();
$data = json_decode(file_get_contents('php://input'), true);
$stmt = $pdo->prepare("UPDATE masters SET code=?, name=?, sort_order=?, is_active=? WHERE id=?");
$stmt->execute([$data['code'], $data['name'], $data['sort_order'], $data['is_active'], $data['id']]);
echo json_encode(['success'=>true]);
?>