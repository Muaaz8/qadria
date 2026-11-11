<?php
if (!isAdmin()) {
    $_SESSION['error'] = "Access denied!";
    header("Location: system.php?page=dashboard");
    exit();
}

$user_id = (int)$_POST['user_id'];
$job_ids = isset($_POST['job_ids']) ? $_POST['job_ids'] : [];

if (empty($job_ids)) {
    $_SESSION['error'] = "No jobs selected!";
    header("Location: system.php?page=manage-permissions&user_id=" . $user_id);
    exit();
}

$granted_count = 0;
foreach ($job_ids as $job_id) {
    if (grantJobPermission($pdo, $user_id, $job_id)) {
        $granted_count++;
    }
}

$_SESSION['success'] = "Permissions granted for " . $granted_count . " job(s)!";
header("Location: system.php?page=manage-permissions&user_id=" . $user_id);
exit();
?>