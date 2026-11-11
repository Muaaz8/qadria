<?php
$user_id = getUserId();
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

$stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($user && password_verify($current_password, $user['password'])) {
    if ($new_password == $confirm_password) {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed, $user_id]);
        $_SESSION['success'] = "Password changed!";
    } else {
        $_SESSION['error'] = "Passwords do not match!";
    }
} else {
    $_SESSION['error'] = "Current password is incorrect!";
}
header("Location: system.php?page=dashboard");
exit();
?>