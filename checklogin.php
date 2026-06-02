<?php
set_time_limit(300);
ini_set('max_execution_time', 300);
ini_set('default_socket_timeout', 300);
ini_set('memory_limit', '256M');

session_start([
    'cookie_lifetime' => 3600,
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => true,
    'sid_length' => 48,
    'sid_bits_per_character' => 6
]);

require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($username) || empty($password)) {
    header('Location: login.php?error=empty');
    exit;
}

if (strlen($username) > 50 || strlen($password) > 100) {
    header('Location: login.php?error=invalid');
    exit;
}

if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $username)) {
    header('Location: login.php?error=invalid');
    exit;
}

try {
    $db = Database::getInstance();
    $enc_password = md5($password);

    $sql = '{call SP_LOGIN_CHECK_DEALER(?, ?)}';
    $params = [$username, $enc_password];

    $stmt = $db->query($sql, $params);

    if ($stmt === false) {
        throw new Exception('QUERY EXECUTION FAILED');
    }

    $user = $db->fetchOne($stmt);

    if (!$user || !is_array($user)) {
        header('Location: login.php?error=invalid');
        exit;
    }

    session_regenerate_id(true);

    $_SESSION['user_logged_in'] = true;
    $_SESSION['username'] = $username;
    $_SESSION['login_time'] = time();
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';

    if (isset($user['DEALER_NAME'])) {
        $_SESSION['dealer_name'] = trim((string) $user['DEALER_NAME']);
    }

    if (isset($user['AREA_NEW'])) {
        $_SESSION['area_new'] = trim((string) $user['AREA_NEW']);
    }

    if (isset($user['DEALER_CODE'])) {
        $_SESSION['dealer_code'] = trim((string) $user['DEALER_CODE']);
    }

    if (isset($user['BRANCH'])) {
        $_SESSION['branch'] = trim((string) $user['BRANCH']);
    }

    if (isset($user['DEALER_ID'])) {
        $_SESSION['dealer_id'] = (int) $user['DEALER_ID'];
    }

    header('Location: index.php');
    exit;
} catch (Exception $e) {
    $errorContext = [
        'message' => $e->getMessage(),
        'user' => $username,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'timestamp' => date('Y-m-d H:i:s')
    ];

    error_log('LOGIN ERROR: ' . json_encode($errorContext));

    $errorType = 'system';
    $errorMsg = strtolower($e->getMessage());

    if (str_contains($errorMsg, 'timeout') ||
            str_contains($errorMsg, 'timed out') ||
            str_contains($errorMsg, 'connection') ||
            str_contains($errorMsg, 'sqlsrv_connect')) {
        $errorType = 'timeout';
    }

    header('Location: login.php?error=' . $errorType);
    exit;
}
