<?php
$userId = getUserId();
$job_id = (int)$_GET['job_id'];
$bill_type = $_GET['bill_type'];

if (!canUserViewJob($pdo, $userId, $job_id)) {
    echo json_encode(['error' => 'Access denied']);
    exit();
}

$previousItems = getPreviousBillItems($pdo, $job_id, $bill_type, $userId);
header('Content-Type: application/json');
echo json_encode(['items' => $previousItems]);
?>