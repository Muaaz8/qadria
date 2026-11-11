<?php
if (!isAdmin()) exit();
$pdo->prepare("DELETE FROM masters WHERE id = ?")->execute([$_GET['id']]);
echo json_encode(['success'=>true]);
?>