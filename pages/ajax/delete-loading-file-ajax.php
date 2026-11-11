<?php
$id = $_GET['id'];
$result = deleteLoadingFile($pdo, $id);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>