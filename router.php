<?php

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$urlPath = strtok($requestUri, '?');

if ($urlPath === '/.well-known/appspecific/com.chrome.devtools.json') {
    header('Content-Type: application/json');
    echo json_encode(new stdClass());
    return true;
}

if ($urlPath === '/favicon.ico') {
    $faviconPath = __DIR__ . '/favicon.ico';
    if (file_exists($faviconPath)) {
        header('Content-Type: image/x-icon');
        readfile($faviconPath);
    } else {
        http_response_code(204);
    }
    return true;
}

return false;
