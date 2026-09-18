<?php
// login.php

session_start([
    'cookie_lifetime' => 3600,
    'cookie_httponly' => true,
    'use_strict_mode' => true,
    'sid_length' => 48,
    'sid_bits_per_character' => 6,
]);

if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$errorMessages = [
    'invalid' => 'Username atau Password salah.',
    'empty' => 'Username dan Password harus diisi.',
    'system' => 'Terjadi kesalahan sistem. Silakan coba lagi.',
    'timeout' => 'Koneksi ke server timeout. Silakan coba lagi.',
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Login - Simulation App</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php require __DIR__ . '/assets/views/styles.php'; ?>
</head>

<body class="login-page-body">
    <div class="login-container">
        <div class="login-header">
            <h1>Selamat Datang</h1>
            <p>Masuk menggunakan akun yang telah terdaftar.</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert">
                <?php echo $errorMessages[$_GET['error']] ?? 'Terjadi kesalahan saat login.'; ?>
            </div>
        <?php endif; ?>

        <form action="checklogin.php" method="POST" id="loginForm">
            <div class="login-form-group">
                <label for="username" class="login-form-label">Username</label>
                <input type="text" id="username" name="username" class="login-form-input" required
                    autocomplete="username" placeholder="Masukkan Username" maxlength="50">
            </div>

            <div class="login-form-group">
                <label for="password" class="login-form-label">Password</label>
                <input type="password" id="password" name="password" class="login-form-input" required
                    autocomplete="current-password" placeholder="Masukkan Password" maxlength="100">
            </div>

            <button type="submit" class="login-btn-submit" id="submitBtn">Sign In</button>
            <div class="login-loading" id="loadingIndicator">Memproses login...</div>
        </form>
    </div>

    <script>
        const loginForm = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        const loadingIndicator = document.getElementById('loadingIndicator');

        loginForm.addEventListener('submit', function (e) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Memproses...';
            loadingIndicator.classList.add('active');

            setTimeout(function () {
                if (submitBtn.disabled) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Sign In';
                    loadingIndicator.classList.remove('active');
                    alert('Proses login time out. Silakan coba lagi.');
                }
            }, 15000);
        });
    </script>
</body>

</html>