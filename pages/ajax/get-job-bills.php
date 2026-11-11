<?php
$userId = getUserId();
$job_id = (int)$_GET['job_id'];

if (!canUserViewJob($pdo, $userId, $job_id)) {
    echo json_encode(['error' => 'Access denied']);
    exit();
}

$bills = getJobBillsSummary($pdo, $job_id);
header('Content-Type: application/json');
echo json_encode(['bills' => $bills]);
?>