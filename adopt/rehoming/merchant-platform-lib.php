<?php
/**
 * TASK-MANUS-05 START — Rehoming platform storage and account-scoped auth.
 * This library is deliberately separate from adopt/admin/chat-* (support chat).
 * It stores only rehoming records and accepts the existing login session shape:
 * $_SESSION['pc_account'] = ['account_id'=>..., 'account_type'=>merchant|visitor].
 */
declare(strict_types=1);

/** TASK-WEB-04 START — Shared auth contracts are sourced from the server-side account registry. */
require_once __DIR__ . '/web04-auth-core.php';
web04_session_start();
web04_security_headers();

const RH_TOKEN_TTL = 2592000;
const RH_MAX_MESSAGE_LENGTH = 4000;

function rh_json_response(array $payload, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function rh_runtime_dir(): string {
    /** TASK-WEB-04 START — Allow private external runtime storage without changing the legacy default path. */
    $override = getenv('REHOMING_RUNTIME_DIR') ?: ($_ENV['REHOMING_RUNTIME_DIR'] ?? '');
    $dir = $override !== '' ? (string)$override : (__DIR__ . '/runtime');
    if (is_link($dir)) throw new RuntimeException('Rehoming data directory must not be a symlink');
    if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) throw new RuntimeException('Rehoming data directory is unavailable');
    @chmod($dir, 0750);
    if ($override !== '') {
        $root = realpath(dirname(__DIR__));
        $real = realpath($dir);
        if ($dir[0] !== DIRECTORY_SEPARATOR || $root === false || $real === false || $real === $root || str_starts_with($real, $root . DIRECTORY_SEPARATOR)) {
            throw new RuntimeException('External Rehoming data directory must be absolute and outside the web root');
        }
    }
    if (!is_writable($dir)) throw new RuntimeException('Rehoming data directory is not writable');
    return rtrim($dir, DIRECTORY_SEPARATOR);
    /** TASK-WEB-04 END */
}

function rh_store_path(string $name): string {
    if (!preg_match('/^[a-z][a-z0-9_-]{1,50}$/', $name)) throw new InvalidArgumentException('Invalid store');
    return rh_runtime_dir() . '/' . $name . '.json';
}

function rh_read_store(string $name): array {
    $path = rh_store_path($name);
    if (!is_file($path)) return [];
    $value = json_decode((string)file_get_contents($path), true);
    return is_array($value) ? $value : [];
}

function rh_write_store(string $name, array $value): void {
    $path = rh_store_path($name);
    $tmp = $path . '.' . bin2hex(random_bytes(6)) . '.tmp';
    $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $previousMask = umask(0077);
    try { $written = $json === false ? false : file_put_contents($tmp, $json, LOCK_EX); }
    finally { umask($previousMask); }
    if ($written === false || !chmod($tmp, 0640) || !rename($tmp, $path)) {
        @unlink($tmp);
        throw new RuntimeException('Unable to persist rehoming data');
    }
    @chmod($path, 0640);
}

function rh_input(): array {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw ?: '', true);
    return is_array($json) ? $json : $_POST;
}

function rh_current_account(): array {
    try {
        $account = web04_current_user();
    } catch (Throwable $error) {
        rh_json_response(['success'=>false,'error'=>'Account service is unavailable'], 503);
    }
    if (!$account) rh_json_response(['success'=>false,'error'=>'Authentication required'], 401);
    return ['account_id'=>$account['account_id'], 'account_type'=>$account['account_type'], 'is_test_account'=>$account['is_test_account'], 'verification_status'=>$account['verification_status']];
}

function rh_require_role(string $role, bool $allowAdmin = false): array {
    $account = rh_current_account();
    if (!in_array($role, ['merchant','visitor','admin'], true) || ($account['account_type'] !== $role && !($allowAdmin && $account['account_type'] === 'admin'))) {
        rh_json_response(['success'=>false,'error'=>'This account cannot perform that action'], 403);
    }
    return $account;
}

function rh_require_verified_merchant(): array {
    $account = rh_require_role('merchant');
    if (($account['verification_status'] ?? '') !== 'verified') rh_json_response(['success'=>false,'error'=>'Merchant verification is required before publishing listings'], 403);
    return $account;
}

function rh_require_csrf(): void {
    // A valid bearer credential is not ambient browser state; session-cookie requests require a one-time server token.
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) { rh_bearer_account(); return; }
    if (!web04_auth_ready()) rh_json_response(['success'=>false,'error'=>'Account service is not configured'], 503);
    if (!web04_csrf_valid(true)) rh_json_response(['success'=>false,'error'=>'Request verification failed; refresh and try again'], 403);
}
/** TASK-WEB-04 END — Shared authorization, CSRF and verified-merchant helpers. */

function rh_token_secret(): string {
    $secret = getenv('PETCENTER_REHOMING_TOKEN_SECRET');
    if (!$secret) $secret = $_ENV['PETCENTER_REHOMING_TOKEN_SECRET'] ?? '';
    if (!$secret || strlen($secret) < 32) {
        $sessionSecret = web04_session_secret();
        if ($sessionSecret !== '') $secret = hash_hmac('sha256', 'petcenter-rehoming-bearer-token-v1', $sessionSecret);
    }
    if (!$secret || strlen($secret) < 32) rh_json_response(['success'=>false,'error'=>'Rehoming token secret is not configured'], 503);
    return (string)$secret;
}

function rh_issue_token(array $account): string {
    $token = bin2hex(random_bytes(32));
    $digest = hash_hmac('sha256', $token, rh_token_secret());
    $path = rh_store_path('tokens');
    $lockPath = $path . '.lock';
    $lock = fopen($lockPath, 'c');
    if ($lock === false) throw new RuntimeException('Unable to lock session-token store');
    @chmod($lockPath, 0640);
    try {
        if (!flock($lock, LOCK_EX)) throw new RuntimeException('Unable to lock session-token store');
        $tokens = rh_read_store('tokens');
        $tokens[$digest] = ['account_id'=>$account['account_id'], 'account_type'=>$account['account_type'], 'issued_at'=>time(), 'expires_at'=>time()+RH_TOKEN_TTL, 'revoked'=>false];
        rh_write_store('tokens', $tokens);
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
    return $token;
}

function rh_bearer_account(): array {
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/^Bearer\s+([A-Fa-f0-9]{64})$/', $header, $m)) rh_json_response(['success'=>false,'error'=>'Bearer token required'], 401);
    $digest = hash_hmac('sha256', $m[1], rh_token_secret());
    $record = rh_read_store('tokens')[$digest] ?? null;
    if (!is_array($record) || !empty($record['revoked']) || (int)($record['expires_at'] ?? 0) <= time()) rh_json_response(['success'=>false,'error'=>'Invalid or expired token'], 401);
    try { $account = web04_find_account_by_id((string)($record['account_id'] ?? '')); }
    catch (Throwable $error) { rh_json_response(['success'=>false,'error'=>'Account service is unavailable'], 503); }
    if (!$account || ($account['status'] ?? '') !== 'active' || ($account['account_type'] ?? '') !== ($record['account_type'] ?? '')) rh_json_response(['success'=>false,'error'=>'Invalid or expired token'], 401);
    return ['account_id'=>(string)$account['account_id'], 'account_type'=>(string)$account['account_type'], 'is_test_account'=>(bool)($account['is_test_account'] ?? false), 'verification_status'=>(string)($account['verification_status'] ?? '')];
}

function rh_auth_account(): array {
    return isset($_SERVER['HTTP_AUTHORIZATION']) ? rh_bearer_account() : rh_current_account();
}

function rh_id(string $prefix): string { return $prefix . '-' . strtoupper(bin2hex(random_bytes(8))); }
function rh_now(): string { return gmdate('c'); }
function rh_clean_id(mixed $value): string { return preg_replace('/[^A-Za-z0-9_.:-]/', '', trim((string)$value)); }
// TASK-MANUS-05 PORTABILITY — mbstring is optional on the existing PHP host;
// UTF-8-aware helpers are used when present and safe byte fallbacks otherwise.
function rh_text_length(string $value): int { return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value); }
function rh_text_slice(string $value, int $max): string { return function_exists('mb_substr') ? mb_substr($value, 0, $max, 'UTF-8') : substr($value, 0, $max); }
function rh_clean_text(mixed $value, int $max = RH_MAX_MESSAGE_LENGTH): string { return trim(rh_text_slice((string)$value, $max)); }

function rh_find_thread(array $threads, string $threadId): ?array {
    foreach ($threads as $thread) if (($thread['thread_id'] ?? '') === $threadId) return $thread;
    return null;
}
function rh_participant(array $thread, string $accountId, string $accountType): bool {
    if ($accountType === 'visitor') return ($thread['visitor_id'] ?? '') === $accountId;
    if ($accountType === 'merchant') return ($thread['merchant_id'] ?? '') === $accountId;
    return false;
}

function rh_test_account(array $account): bool { return !empty($account['is_test_account']); }

/* TASK-MANUS-05 END */
?>
