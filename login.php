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

require_once 'config/appColors.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Login - Simulation App</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --bg-color: #f3f4f6;
            --card-bg: #ffffff;
            --text-color: #1f2937;
            --text-muted: #6b7280;
            --border-color: #e5e7eb;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .login-container {
            background-color: var(--card-bg);
            padding: 2.5rem;
            border-radius: 1rem;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-color);
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-submit {
            width: 100%;
            padding: 0.75rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-submit:hover {
            background-color: var(--primary-hover);
        }

        .btn-submit:disabled {
            background-color: var(--text-muted);
            cursor: not-allowed;
        }

        .alert {
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            text-align: center;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .loading {
            display: none;
            text-align: center;
            margin-top: 0.5rem;
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .loading.active {
            display: block;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Selamat Datang</h1>
            <p>Masuk menggunakan akun yang telah terdaftar.</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <?php
                $errorMessages = [
                    'invalid' => 'Username atau Password salah.',
                    'empty' => 'Username dan Password harus diisi.',
                    'system' => 'Terjadi kesalahan sistem. Silakan coba lagi.',
                    'timeout' => 'Koneksi ke server timeout. Silakan coba lagi.',
                ];
                echo $errorMessages[$_GET['error']] ?? 'Terjadi kesalahan saat login.';
                ?>
            </div>
        <?php endif; ?>

        <form action="checklogin.php" method="POST" id="loginForm">
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-input" required autocomplete="username"
                    placeholder="Masukkan Username" maxlength="50">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-input" required
                    autocomplete="current-password" placeholder="Masukkan Password" maxlength="100">
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">Sign In</button>
            <div class="loading" id="loadingIndicator">Memproses login...</div>
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
                    alert('Proses login terlalu lama. Silakan coba lagi.');
                }
            }, 15000);
        });
    </script>
</body>

</html>