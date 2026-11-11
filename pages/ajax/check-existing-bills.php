<?php
$job_id = $_GET['job_id'];
$userId = getUserId();

if (!canUserViewJob($pdo, $userId, $job_id)) {
    echo json_encode(['has_bills' => false, 'error' => 'Access denied']);
    exit();
}

$hasBills = checkExistingBills($pdo, $job_id);
$billsList = [];
if ($hasBills) {
    $billsList = getExistingBillsList($pdo, $job_id);
}
echo json_encode(['has_bills' => $hasBills, 'bills' => $billsList]);
?>