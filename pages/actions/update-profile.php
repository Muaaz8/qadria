<?php
$user_id = getUserId();
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');

$stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
$stmt->execute([$full_name, $email, $phone, $user_id]);

$_SESSION['full_name'] = $full_name;
$_SESSION['email'] = $email;
$_SESSION['phone'] = $phone;

$_SESSION['success'] = "Profile updated!";
header("Location: system.php?page=dashboard");
exit();
?>