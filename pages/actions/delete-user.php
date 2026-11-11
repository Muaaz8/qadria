<?php
if (!isAdmin()) {
    $_SESSION['error'] = "Access denied!";
    header("Location: system.php?page=dashboard");
    exit();
}

$id = $_GET['id'];
if ($id != $_SESSION['user_id']) { 
    $pdo->prepare("DELETE FROM user_job_permissions WHERE user_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'")->execute([$id]); 
    $_SESSION['success'] = "User deleted!"; 
}
header("Location: system.php?page=users");
exit();
?>