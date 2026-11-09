<?php
/** Public half of Turnstile configuration; never expose the secret key. */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

$siteKey = getenv('TURNSTILE_SITE_KEY');
if (!is_string($siteKey) || trim($siteKey) === '') {
    $envPath = __DIR__ . '/.env';
    if (is_file($envPath)) {
        foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (strpos(trim($line), 'TURNSTILE_SITE_KEY=') === 0) {
                $siteKey = trim(substr(trim($line), strlen('TURNSTILE_SITE_KEY=')), " \t\"'");
                break;
            }
        }
    }
}

if (!is_string($siteKey) || trim($siteKey) === '') {
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'Turnstile site key is not configured.']);
    exit;
}

echo json_encode(['success' => true, 'site_key' => trim($siteKey)]);
