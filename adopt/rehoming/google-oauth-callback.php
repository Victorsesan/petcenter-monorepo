<?php
/** TASK-WEB-04 START — Google OIDC callback and verified server-side account session. */
declare(strict_types=1);
require_once __DIR__ . '/web04-auth-core.php';
web04_session_start();
web04_security_headers();

function web04_oauth_callback_error(string $reason): never {
    header('Location: /signin/?mode=login&oauth_error=' . rawurlencode($reason), true, 302);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    exit;
}
if (!web04_oauth_configured()) web04_oauth_callback_error('not_configured');
$state = is_string($_GET['state'] ?? null) ? (string)$_GET['state'] : '';
$transaction = web04_oauth_consume_state($state);
if (!$transaction) web04_oauth_callback_error('invalid_state');
if (isset($_GET['error'])) web04_oauth_callback_error($_GET['error'] === 'access_denied' ? 'cancelled' : 'provider_error');
$code = is_string($_GET['code'] ?? null) ? (string)$_GET['code'] : '';
if ($code === '' || strlen($code) > 4096 || preg_match('/[\x00-\x20\x7f]/', $code)) web04_oauth_callback_error('invalid_callback');
$idToken = web04_google_exchange_code($code, (string)($transaction['code_verifier'] ?? ''));
if (!$idToken) web04_oauth_callback_error('exchange_failed');
$jwks = web04_google_jwks();
if (!$jwks) web04_oauth_callback_error('verification_failed');
$identity = web04_verify_google_id_token($idToken, (string)($transaction['nonce'] ?? ''), web04_env('GOOGLE_CLIENT_ID'), $jwks);
if (!$identity) web04_oauth_callback_error('verification_failed');
try {
    $account = web04_upsert_google_account($identity);
} catch (Throwable $error) {
    $account = null;
}
if (!$account || ($account['status'] ?? '') !== 'active') web04_oauth_callback_error('account_unavailable');
try {
    web04_finish_login($account);
} catch (Throwable $error) {
    web04_oauth_callback_error('session_error');
}
$destination = web04_return_for_role(web04_safe_account($account), (string)($transaction['return_to'] ?? '/rehoming/'));
header('Location: ' . $destination, true, 302);
exit;
/* TASK-WEB-04 END — Google OIDC callback and verified server-side account session. */
