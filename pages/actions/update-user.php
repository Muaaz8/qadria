<?php
if (!isAdmin()) {
    $_SESSION['error'] = "Access denied!";
    header("Location: system.php?page=dashboard");
    exit();
}

$id = $_POST['id']; 
$full_name = trim($_POST['full_name']); 
$email = trim($_POST['email'] ?? ''); 
$phone = trim($_POST['phone'] ?? ''); 
$role = $_POST['role'];

$stmt = $pdo->prepare("UPDATE users SET full_name=?, email=?, phone=?, role=? WHERE id=?");
$stmt->execute([$full_name, $email, $phone, $role, $id]);

$_SESSION['success'] = "User updated!";
header("Location: system.php?page=users");
exit();
?>