<?php
session_start();

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: pages/dashboard.php");
    exit;
}

require_once 'classes/User.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {
        $user = new User();
        if ($user->login($username, $password)) {
            header("Location: pages/dashboard.php");
            exit;
        } else {
            $error = 'Username atau password salah!';
        }
    } else {
        $error = 'Username dan password wajib diisi!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – Sistem Absensi</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="login-body">

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <div class="login-icon">📋</div>
            <h1>Sistem Absensi</h1>
            <p>Masuk untuk mengelola data kehadiran siswa</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" 
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                       placeholder="Masukkan username" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" 
                       placeholder="Masukkan password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Masuk</button>
        </form>

        <div class="login-hint">
            <p><strong>Akun Demo:</strong></p>
            <p>Admin: <code>admin</code> / <code>password123</code></p>
            <p>Guru&nbsp;&nbsp;: <code>guru1</code> / <code>password123</code></p>
        </div>
    </div>
</div>

</body>
</html>
