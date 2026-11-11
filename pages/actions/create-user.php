<?php
if (!isAdmin()) {
    $_SESSION['error'] = "Access denied!";
    header("Location: system.php?page=dashboard");
    exit();
}

$username = trim($_POST['username']);
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$full_name = trim($_POST['full_name']);
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$role = $_POST['role'];

$stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email, phone, role) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$username, $password, $full_name, $email, $phone, $role]);

$_SESSION['success'] = "User created!";
header("Location: system.php?page=users");
exit();
?>