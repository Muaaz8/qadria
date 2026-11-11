<?php
$id = $_GET['id'];
$record = getLoadingFileById($pdo, $id);
header('Content-Type: application/json');
echo json_encode($record);
?>