<?php
require_once __DIR__ . '/../../config.php';

if (isLoggedIn()) {
    header('Location: ' . appUrl('View/dashboard.php'));
    exit();
}

$assetPath = '../assets/';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $loginInput = !empty($email) ? $email : $username;

    if (loginUser($loginInput, $password)) {
        header('Location: ' . appUrl('View/dashboard.php'));
        exit();
    }
    $error = 'Invalid credentials. Please try again.';
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — Skiller</title>
  <link rel="stylesheet" href="<?= $assetPath ?>vendor/css/core.css">
  <link rel="stylesheet" href="<?= $assetPath ?>vendor/css/theme-default.css">
  <link rel="stylesheet" href="<?= $assetPath ?>css/demo.css">
  <link rel="stylesheet" href="<?= $assetPath ?>vendor/fonts/boxicons.css">

  <style>
    .login-card {
      max-width: 480px;
      margin: 40px auto;
      padding: 40px 35px;
      border-radius: 16px;
      box-shadow: 0 15px 50px rgba(0,0,0,0.12);
    }
    .form-label {
      font-weight: 600;
      color: #475569;
      margin-bottom: 8px;
      font-size: 0.95rem;
    }
    .form-control {
      padding: 14px 16px;
      border-radius: 10px;
      border: 1px solid #cbd5e1;
      font-size: 1rem;
      transition: all 0.2s;
    }
    .form-control:focus {
      border-color: #5b6cff;
      box-shadow: 0 0 0 0.25rem rgba(91, 108, 255, 0.15);
      outline: none;
    }
    .btn-primary {
      background: #5b6cff;
      border: none;
      padding: 14px;
      font-size: 1.05rem;
      font-weight: 600;
      border-radius: 10px;
      margin-top: 10px;
    }
    .demo-accounts {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 20px;
      margin-top: 30px;
      font-size: 0.92rem;
      line-height: 1.7;
    }
    .alert {
      border-radius: 10px;
    }
  </style>
</head>
<body style="background: #f8fafc;">

<div class="container-xxl d-flex align-items-center justify-content-center min-vh-100">
  <div class="login-card card">
    
    <div class="text-center mb-4">
      <h3 class="mb-1">Skiller</h3>
      <p class="text-muted">Sign in to your account</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" id="loginForm">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" id="username" class="form-control" placeholder="e.g. admin" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Email (Optional)</label>
        <input type="email" name="email" id="email" class="form-control" placeholder="e.g. admin@skiller.com">
      </div>

      <div class="mb-4">
        <label class="form-label">Password</label>
        <input type="password" name="password" id="password" class="form-control" required>
      </div>

      <button type="submit" class="btn btn-primary w-100">Sign In</button>
    </form>

    <!-- Clear and prominent demo credentials -->
    <div class="demo-accounts">
      <strong style="color:#334155;">Demo Login Details:</strong><br><br>
      
      <strong>👤 Admin</strong><br>
      Username: <code>admin</code> <br>
      Email: <code>admin@skiller.com</code> <br>
      Password: <code>admin123</code><br><br>
      
      <strong>👤 Super User</strong><br>
      Username: <code>superuser</code> <br>
      Email: <code>super@skiller.com</code> <br>
      Password: <code>super123</code><br><br>
      
      <strong>👤 Simple User</strong><br>
      Username: <code>user</code> <br>
      Email: <code>user@skiller.com</code> <br>
      Password: <code>user123</code>
    </div>

  </div>
</div>

<script>
// Simple validation
document.getElementById('loginForm').addEventListener('submit', function(e) {
  const username = document.getElementById('username').value.trim();
  const email = document.getElementById('email').value.trim();
  const password = document.getElementById('password').value.trim();

  if (!username && !email) {
    e.preventDefault();
    alert("Please enter your username or email address");
  } else if (password.length < 3) {
    e.preventDefault();
    alert("Password must be at least 3 characters");
  }
});
</script>

</body>
</html>