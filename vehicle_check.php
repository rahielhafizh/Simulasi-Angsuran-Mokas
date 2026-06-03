<?php declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

header('Content-Type: application/json');

require_once __DIR__ . '/config/appConstants.php';

if (!function_exists('curl_init')) {
    echo json_encode([
        'success' => false,
        'message' => 'Ekstensi cURL tidak tersedia pada server ini.',
    ]);
    exit;
}

$nomorRangka = trim((string) ($_POST['nomor_rangka'] ?? ''));

if ($nomorRangka === '') {
    echo json_encode(['success' => false, 'message' => 'Nomor Rangka tidak boleh kosong.']);
    exit;
}

$payload = json_encode([
    'AppName' => AppConstants::RAPINDO_APP_NAME,
    'NomorRangka' => $nomorRangka,
], JSON_UNESCAPED_UNICODE);

$rawResponse = sendViaCurl(AppConstants::RAPINDO_API_URL, $payload);

if ($rawResponse === null) {
    echo json_encode([
        'success' => false,
        'message' => 'Tidak dapat terhubung ke layanan pengecekan kendaraan. Silakan coba lagi.',
    ]);
    exit;
}

$decoded = json_decode($rawResponse, true);

if (!is_array($decoded)) {
    error_log('[VehicleCheck] Non-JSON response: ' . substr($rawResponse, 0, 300));
    echo json_encode([
        'success' => false,
        'message' => 'Respons dari layanan tidak valid. Silakan coba lagi.',
    ]);
    exit;
}

// EKSTRAKSI DATA BARU
$cert = $decoded['cert'] ?? null;
$companyName = is_array($cert) ? trim((string) ($cert['companyName'] ?? '')) : '';
$status = is_array($cert) ? trim((string) ($cert['status'] ?? '')) : '';

$isFound = $companyName !== '' && strtoupper($companyName) !== 'N/A';

// PEMBARUAN RESPONSE JSON
echo json_encode([
    'success' => true,
    'found' => $isFound,
    'certified_to' => $isFound ? $companyName : 'N/A',
    'status' => $isFound && $status !== '' ? $status : 'N/A',
]);

function sendViaCurl(string $url, string $jsonPayload): ?string
{
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $jsonPayload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_CAINFO => resolveCaBundlePath(),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: */*',
            'Accept-Encoding: gzip, deflate, br',
            'Connection: keep-alive',
            'User-Agent: EchoapiRuntime/1.1.0',
            'Content-Length: ' . strlen($jsonPayload),
        ],
    ]);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false || $curlError !== '') {
        error_log('[VehicleCheck] cURL error: ' . $curlError);
        return null;
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        error_log('[VehicleCheck] HTTP ' . $httpCode . ' | body: ' . substr((string) $response, 0, 300));
        return null;
    }

    return (string) $response;
}

function resolveCaBundlePath(): string
{
    $candidates = [
        __DIR__ . '/cacert.pem',
        ini_get('curl.cainfo'),
        ini_get('openssl.cafile'),
        '/etc/ssl/certs/ca-certificates.crt',
        '/etc/pki/tls/certs/ca-bundle.crt',
    ];

    foreach ($candidates as $path) {
        if (!empty($path) && file_exists($path)) {
            return $path;
        }
    }

    return '';
}
