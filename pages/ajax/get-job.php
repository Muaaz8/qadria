<?php
$userId = getUserId();
$job_id = $_GET['id'];

if (!canUserViewJob($pdo, $userId, $job_id)) {
    header('HTTP/1.0 403 Forbidden');
    echo json_encode(['error' => 'Access denied']);
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ?");
$stmt->execute([$job_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);
header('Content-Type: application/json');
echo json_encode($job);
?>