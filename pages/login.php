<?php
// Agar user already logged in hai toh dashboard pe bhej do
if (isset($_SESSION['user_id'])) {
    header("Location: system.php?page=dashboard");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['phone'] = $user['phone'];
        
        header("Location: system.php?page=dashboard");
        exit();
    } else { 
        $error = "Invalid username or password!"; 
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Qadria International</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',sans-serif; background:linear-gradient(135deg,#fcfdff 0%,#f8fafd 100%); min-height:100vh; }
        .login-wrapper { min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px; }
        .login-card { background:white; border-radius:24px; padding:40px; width:100%; max-width:450px; box-shadow:0 20px 60px rgba(0,0,0,0.15); text-align:center; }
        .login-logo { height:60px; width:auto; margin-bottom:20px; }
        .login-title { font-size:1.5rem; text-align:center; color:#3a4984; margin-bottom:5px; }
        .login-subtitle { text-align:center; color:#479ace; margin-bottom:30px; }
        .form-group { margin-bottom:20px; }
        .form-group input { width:100%; padding:12px 15px; border:2px solid #e9edf2; border-radius:12px; font-size:1rem; }
        .form-group input:focus { outline:none; border-color:#8e181e; }
        .checkbox-group { display:flex; justify-content:space-between; margin:20px 0; }
        .login-btn { width:100%; padding:12px; background:#8e181e; color:white; border:none; border-radius:12px; font-size:1rem; font-weight:600; cursor:pointer; }
        .login-btn:hover { background:#6b1217; }
        .error-message { background:#ffebee; color:#c62828; padding:12px; border-radius:12px; margin-bottom:20px; text-align:center; }
        .success-message { background:#e8f5e9; color:#2e7d32; padding:12px; border-radius:12px; margin-bottom:20px; text-align:center; }
    </style>
</head>
<body>
<div class="login-wrapper">
    <div class="login-card">
        <img class="login-logo" src="img/logo.png" alt="QADIRA INTERNATIONAL Logo" onerror="this.style.display='none'">
        <div class="login-title">Welcome Back!</div>
        <div class="login-subtitle">Complete Job & Billing Management System</div>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="system.php?page=login">
            <div class="form-group">
                <input type="text" name="username" placeholder="Username" required autofocus>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="checkbox-group">
                <label>
                    <input type="checkbox" name="remember"> Remember me
                </label>
            </div>
            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Login to Qadria System
            </button>
        </form>
    </div>
</div>
</body>
</html>