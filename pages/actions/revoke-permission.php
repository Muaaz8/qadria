<?php
if (!isAdmin()) {
    $_SESSION['error'] = "Access denied!";
    header("Location: system.php?page=dashboard");
    exit();
}

$user_id = (int)$_GET['user_id'];
$job_id = (int)$_GET['job_id'];
revokeJobPermission($pdo, $user_id, $job_id);
$_SESSION['success'] = "Permission revoked!";
header("Location: system.php?page=manage-permissions&user_id=" . $user_id);
exit();
?>