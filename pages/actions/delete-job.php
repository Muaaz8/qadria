<?php
$userId = getUserId(); 
$jobId = $_GET['id'];

if (!canUserViewJob($pdo, $userId, $jobId)) {
    $_SESSION['error'] = "Permission denied!";
    header("Location: system.php?page=jobs");
    exit();
}

$stmt = $pdo->prepare("SELECT id FROM bills WHERE job_id = ?");
$stmt->execute([$jobId]);
$bills = $stmt->fetchAll();

foreach ($bills as $bill) {
    $pdo->prepare("DELETE FROM bill_items WHERE bill_id = ?")->execute([$bill['id']]);
    $pdo->prepare("DELETE FROM bills WHERE id = ?")->execute([$bill['id']]);
}

$pdo->prepare("DELETE FROM user_job_permissions WHERE job_id = ?")->execute([$jobId]);

if (isAdmin()) { 
    $pdo->prepare("DELETE FROM jobs WHERE id = ?")->execute([$jobId]); 
} else { 
    $pdo->prepare("DELETE FROM jobs WHERE id = ? AND created_by = ?")->execute([$jobId, $userId]); 
}

$_SESSION['success'] = "Job and all associated bills deleted!";
header("Location: system.php?page=jobs");
exit();
?>