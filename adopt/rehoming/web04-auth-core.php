<?php
/**
 * TASK-WEB-04 START — Rehoming authentication and session core.
 *
 * Account roles and identity are read from a private server-side account
 * registry. Public Google sign-in may create only visitor records; merchant
 * and admin roles can only be pre-provisioned by trusted server-side tools.
 * The browser never supplies a role as authority.
 */
declare(strict_types=1);

const WEB04_SESSION_IDLE_SECONDS = 1800;
const WEB04_SESSION_ABSOLUTE_SECONDS = 43200;
const WEB04_OAUTH_STATE_TTL = 600;
const WEB04_AUTH_STORE_NAME = 'accounts';
const WEB04_ALLOWED_ROLES = ['visitor', 'merchant', 'admin'];

function web04_env(string $name): string {
    $value = getenv($name);
    if ($value === false || $value === '') $value = (string)($_ENV[$name] ?? '');
    return trim($value);
}

function web04_session_secret(): string {
    $secret = web04_env('SESSION_SECRET');
    return strlen($secret) >= 32 ? $secret : '';
}

function web04_external_private_dir(string $envName, string $label): string {
    $configured = web04_env($envName);
    if ($configured === '' || $configured[0] !== DIRECTORY_SEPARATOR || !is_dir($configured) || is_link(rtrim($configured, DIRECTORY_SEPARATOR))) {
        throw new RuntimeException($label . ' directory must be explicitly configured as an existing absolute private path');
    }
    $real = realpath($configured);
    $webRoot = realpath(dirname(__DIR__));
    if ($real === false || $webRoot === false || $real === $webRoot
        || str_starts_with($real, rtrim($webRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR)) {
        throw new RuntimeException($label . ' directory must resolve outside the web root');
    }
    $permissions = @fileperms($real);
    if (!is_writable($real) || $permissions === false || ($permissions & 0077) !== 0) {
        throw new RuntimeException($label . ' directory must be owner-only and writable');
    }
    return rtrim($real, DIRECTORY_SEPARATOR);
}

function web04_auth_ready(): bool {
    if (web04_session_secret() === '' || web04_app_origin() === null) return false;
    try {
        web04_auth_store_dir();
        web04_runtime_store_dir();
        return true;
    } catch (Throwable $error) {
        return false;
    }
}

function web04_test_auth_enabled(): bool {
    return web04_env('WEB04_ENABLE_TEST_AUTH') === '1' && in_array(strtolower(web04_env('APP_ENV')), ['local', 'staging'], true);
}

function web04_https_request(): bool {
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && strtolower((string)$_SERVER['HTTPS']) !== 'off') return true;
    $base = web04_env('APP_BASE_URL');
    return str_starts_with(strtolower($base), 'https://');
}

function web04_session_start(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    if (headers_sent()) throw new RuntimeException('Session must start before output');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => web04_https_request(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    if (!session_start()) throw new RuntimeException('Unable to start session');
}

function web04_security_headers(): void {
    if (headers_sent()) return;
    header('Cache-Control: no-store, private');
    header('Pragma: no-cache');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    header('X-Frame-Options: DENY');
}

function web04_auth_store_dir(): string {
    return web04_external_private_dir('REHOMING_AUTH_DATA_DIR', 'Account data');
}

function web04_runtime_store_dir(): string {
    return web04_external_private_dir('REHOMING_RUNTIME_DIR', 'Rehoming runtime data');
}

function web04_store_path(string $name): string {
    if (!in_array($name, ['accounts', 'throttle', 'profiles'], true)) throw new InvalidArgumentException('Invalid store name');
    $filename = ['accounts' => 'web04-accounts.json', 'throttle' => 'web04-throttle.json', 'profiles' => 'web04-profiles.json'][$name];
    return web04_auth_store_dir() . DIRECTORY_SEPARATOR . $filename;
}

function web04_store_default(string $name): array {
    if ($name === 'accounts') return ['version' => 1, 'accounts' => []];
    if ($name === 'profiles') return ['version' => 1, 'profiles' => []];
    return ['version' => 1, 'attempts' => []];
}

function web04_store_read(string $name): array {
    $path = web04_store_path($name);
    if (!is_file($path)) return web04_store_default($name);
    $raw = file_get_contents($path);
    if ($raw === false) throw new RuntimeException('Unable to read private account store');
    $data = json_decode($raw, true);
    $key = $name === 'accounts' ? 'accounts' : ($name === 'profiles' ? 'profiles' : 'attempts');
    if (!is_array($data) || !is_array($data[$key] ?? null)) {
        throw new RuntimeException('Private account store is malformed');
    }
    return $data;
}

function web04_store_update(string $name, callable $update): mixed {
    $path = web04_store_path($name);
    $lockPath = web04_auth_store_dir() . DIRECTORY_SEPARATOR . '.web04-' . $name . '.lock';
    $lock = fopen($lockPath, 'c');
    if ($lock === false) throw new RuntimeException('Unable to lock private account store');
    @chmod($lockPath, 0600);
    try {
        if (!flock($lock, LOCK_EX)) throw new RuntimeException('Unable to lock private account store');
        $data = is_file($path) ? web04_store_read($name) : web04_store_default($name);
        $result = $update($data);
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        if ($json === false) throw new RuntimeException('Unable to encode private account store');
        $tmp = $path . '.' . bin2hex(random_bytes(8)) . '.tmp';
        $previousMask = umask(0077);
        try { $written = file_put_contents($tmp, $json, LOCK_EX); }
        finally { umask($previousMask); }
        if ($written === false || !@chmod($tmp, 0600) || !rename($tmp, $path)) {
            @unlink($tmp);
            throw new RuntimeException('Unable to persist private account store');
        }
        @chmod($path, 0600);
        return $result;
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}

function web04_normalize_email(string $email): string {
    return function_exists('mb_strtolower') ? mb_strtolower(trim($email), 'UTF-8') : strtolower(trim($email));
}

function web04_text_length(string $value): int {
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

function web04_text_slice(string $value, int $start, int $length): string {
    return function_exists('mb_substr') ? mb_substr($value, $start, $length, 'UTF-8') : substr($value, $start, $length);
}

function web04_find_account_by_id(string $accountId): ?array {
    foreach (web04_store_read('accounts')['accounts'] as $account) {
        if (is_array($account) && hash_equals((string)($account['account_id'] ?? ''), $accountId)) return $account;
    }
    if (web04_test_auth_enabled()) {
        foreach (web04_external_test_accounts() as $account) if (($account['account_id'] ?? '') === $accountId) return $account;
    }
    return null;
}

function web04_find_account_by_email(string $email): ?array {
    $needle = web04_normalize_email($email);
    foreach (web04_store_read('accounts')['accounts'] as $account) {
        if (is_array($account) && web04_normalize_email((string)($account['email'] ?? '')) === $needle) return $account;
    }
    return null;
}

function web04_find_account_by_google_sub(string $sub): ?array {
    foreach (web04_store_read('accounts')['accounts'] as $account) {
        if (is_array($account) && is_string($account['google_sub'] ?? null) && hash_equals($account['google_sub'], $sub)) return $account;
    }
    return null;
}

function web04_find_test_account_by_username(string $username): ?array {
    $needle = strtolower(trim($username));
    foreach (web04_store_read('accounts')['accounts'] as $account) {
        if (is_array($account) && !empty($account['is_test_account']) && is_string($account['username'] ?? null)
            && hash_equals(strtolower($account['username']), $needle)) return $account;
    }
    if (web04_test_auth_enabled()) {
        foreach (web04_external_test_accounts() as $account) {
            if (is_string($account['username'] ?? null) && hash_equals(strtolower($account['username']), $needle)) return $account;
        }
    }
    return null;
}

function web04_external_test_accounts(): array {
    if (!web04_test_auth_enabled()) return [];
    $configured = web04_env('WEB04_TEST_ACCOUNTS_FILE');
    if ($configured === '' || $configured[0] !== DIRECTORY_SEPARATOR || is_link($configured)) {
        throw new RuntimeException('Test-account storage must be explicitly configured outside the web root');
    }
    $root = realpath(dirname(__DIR__));
    $parent = realpath(dirname($configured));
    if ($root === false || $parent === false || $parent === $root
        || str_starts_with($parent, rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR)) {
        throw new RuntimeException('Configured test-account storage must be outside the web root');
    }
    $path = $configured;
    if (!is_file($path)) return [];
    $size = @filesize($path);
    $permissions = @fileperms($path);
    if (is_link($path) || !is_readable($path) || $size === false || $size > 5242880
        || $permissions === false || ($permissions & 0077) !== 0) throw new RuntimeException('Test-account store is unavailable');
    $raw = file_get_contents($path);
    if ($raw === false) throw new RuntimeException('Test-account store is unavailable');
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) throw new RuntimeException('Test-account store is malformed');
    $records = is_array($decoded['accounts'] ?? null) ? $decoded['accounts'] : $decoded;
    $accounts = [];
    foreach ($records as $record) {
        if (!is_array($record) || empty($record['is_test_account']) || !is_string($record['account_id'] ?? null)
            || !in_array(($record['account_type'] ?? ''), ['visitor', 'merchant'], true)) continue;
        $record['status'] = is_string($record['status'] ?? null) ? $record['status'] : 'active';
        $record['display_name'] = (string)($record['display_name'] ?? $record['merchant_name'] ?? $record['username'] ?? '');
        $record['email'] = is_string($record['email'] ?? null) ? $record['email'] : '';
        $record['profile'] = is_array($record['profile'] ?? null) ? $record['profile'] : [];
        $accounts[] = $record;
    }
    return $accounts;
}

function web04_verify_test_login(string $username, string $password): ?array {
    if (!web04_test_auth_enabled() || $username === '' || strlen($username) > 120 || $password === '' || strlen($password) > 1024) return null;
    $account = web04_find_test_account_by_username($username);
    $hash = is_array($account) && is_string($account['password_hash'] ?? null) ? $account['password_hash'] : '';
    if ($hash === '') {
        static $dummyHash = null;
        $dummyHash ??= password_hash('not-a-real-account-password', PASSWORD_DEFAULT);
        password_verify($password, $dummyHash);
        return null;
    }
    if (($account['status'] ?? '') !== 'active' || !in_array(($account['account_type'] ?? ''), ['visitor', 'merchant'], true)) return null;
    if (($account['account_type'] ?? '') === 'merchant' && ($account['verification_status'] ?? '') !== 'verified') return null;
    return password_verify($password, $hash) ? $account : null;
}

function web04_safe_account(array $account): array {
    $profileStore = web04_store_read('profiles')['profiles'];
    $accountId = (string)$account['account_id'];
    return [
        'account_id' => $accountId,
        'account_type' => (string)$account['account_type'],
        'verification_status' => (string)($account['verification_status'] ?? ''),
        'display_name' => (string)($account['display_name'] ?? ''),
        'email' => (string)($account['email'] ?? ''),
        'is_test_account' => (bool)($account['is_test_account'] ?? false),
        'profile' => is_array($profileStore[$accountId] ?? null) ? $profileStore[$accountId] : (is_array($account['profile'] ?? null) ? $account['profile'] : []),
    ];
}

function web04_current_user(): ?array {
    web04_session_start();
    if (!web04_auth_ready()) {
        web04_clear_identity();
        return null;
    }
    $principal = $_SESSION['pc_account'] ?? null;
    if (!is_array($principal)) return null;
    $id = is_string($principal['account_id'] ?? null) ? $principal['account_id'] : '';
    $started = (int)($_SESSION['web04_auth_started_at'] ?? 0);
    $last = (int)($_SESSION['web04_last_activity'] ?? 0);
    $now = time();
    if ($id === '' || $started < 1 || $last < 1 || $now - $last > WEB04_SESSION_IDLE_SECONDS || $now - $started > WEB04_SESSION_ABSOLUTE_SECONDS) {
        web04_clear_identity();
        return null;
    }
    $account = web04_find_account_by_id($id);
    if (!$account || ($account['status'] ?? '') !== 'active' || !in_array(($account['account_type'] ?? ''), WEB04_ALLOWED_ROLES, true)
        || !hash_equals((string)($account['account_type'] ?? ''), (string)($principal['account_type'] ?? ''))) {
        web04_clear_identity();
        return null;
    }
    $_SESSION['web04_last_activity'] = $now;
    return web04_safe_account($account);
}

function web04_user_has_role(array $user, string $role): bool {
    return in_array($role, WEB04_ALLOWED_ROLES, true) && ($user['account_type'] ?? '') === $role;
}

function web04_clear_identity(): void {
    web04_session_start();
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
        $_SESSION['web04_csrf'] = bin2hex(random_bytes(32));
    }
}

function web04_finish_login(array $account): void {
    web04_session_start();
    if (!session_regenerate_id(true)) throw new RuntimeException('Unable to rotate session identifier');
    $_SESSION = [];
    $_SESSION['pc_account'] = [
        'account_id' => (string)$account['account_id'],
        'account_type' => (string)$account['account_type'],
        'is_test_account' => (bool)($account['is_test_account'] ?? false),
    ];
    $_SESSION['web04_auth_started_at'] = time();
    $_SESSION['web04_last_activity'] = time();
    $_SESSION['web04_csrf'] = bin2hex(random_bytes(32));
}

function web04_csrf_token(): string {
    web04_session_start();
    if (!is_string($_SESSION['web04_csrf'] ?? null) || strlen($_SESSION['web04_csrf']) !== 64) {
        $_SESSION['web04_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['web04_csrf'];
}

function web04_app_origin(): ?string {
    $base = web04_env('APP_BASE_URL');
    if ($base === '') return null;
    $parts = parse_url($base);
    if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host']) || isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])) return null;
    $scheme = strtolower((string)$parts['scheme']);
    $host = strtolower((string)$parts['host']);
    $local = in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    if (($scheme !== 'https' && !($local && $scheme === 'http')) || isset($parts['path']) && !in_array($parts['path'], ['', '/'], true)) return null;
    $port = isset($parts['port']) ? ':' . (int)$parts['port'] : '';
    return $scheme . '://' . (str_contains($host, ':') ? '[' . trim($host, '[]') . ']' : $host) . $port;
}

function web04_same_origin_request(): bool {
    $expected = web04_app_origin();
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if ($expected === null || !is_string($origin) || $origin === '' || !hash_equals($expected, rtrim($origin, '/'))) return false;
    $fetchSite = strtolower((string)($_SERVER['HTTP_SEC_FETCH_SITE'] ?? ''));
    if ($fetchSite !== '' && !in_array($fetchSite, ['same-origin', 'none'], true)) return false;
    return true;
}

function web04_csrf_valid(bool $requireToken = true): bool {
    if (!web04_same_origin_request()) return false;
    $expected = $_SESSION['web04_csrf'] ?? '';
    $actual = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_csrf'] ?? '');
    if (!$requireToken && $actual === '') return true;
    return is_string($expected) && is_string($actual) && $expected !== '' && hash_equals($expected, $actual);
}

function web04_safe_return_path(mixed $value): string {
    if (!is_string($value) || $value === '' || strlen($value) > 2048 || preg_match('/[\x00-\x20\x7f\\\\]/', $value)) return '/rehoming/';
    $decoded = rawurldecode($value);
    if ($decoded === '' || preg_match('/[\x00-\x20\x7f\\\\]/', $decoded) || preg_match('/%(?:2f|5c|00|0a|0d)/i', $decoded)) return '/rehoming/';
    if ($decoded === '' || $decoded[0] !== '/' || str_starts_with($decoded, '//') || str_starts_with($decoded, '/\\')) return '/rehoming/';
    $parts = parse_url($decoded);
    if (!is_array($parts) || isset($parts['scheme']) || isset($parts['host']) || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment'])) return '/rehoming/';
    $path = (string)($parts['path'] ?? '');
    $allowed = ['/rehoming/', '/rehoming/list-a-pet/', '/account/settings/', '/merchant/'];
    if (str_contains($path, '//') || preg_match('~(?:^|/)\.\.?(?:/|$)~', $path)) return '/rehoming/';
    foreach ($allowed as $prefix) if ($path === $prefix || str_starts_with($path, $prefix) && $prefix === '/rehoming/') return $decoded;
    return '/rehoming/';
}

function web04_return_for_role(array $user, string $path): string {
    $path = web04_safe_return_path($path);
    if ($user['account_type'] !== 'merchant' && str_starts_with($path, '/merchant/')) return $user['account_type'] === 'visitor' ? '/account/settings/' : '/rehoming/';
    if ($user['account_type'] !== 'visitor' && str_starts_with($path, '/account/settings/')) return $user['account_type'] === 'merchant' ? '/merchant/' : '/rehoming/';
    if ($user['account_type'] !== 'merchant' && $path === '/rehoming/list-a-pet/') return '/rehoming/';
    return $path;
}

function web04_redirect_to_signin(mixed $returnPath = null): never {
    $path = web04_safe_return_path($returnPath ?? ($_SERVER['REQUEST_URI'] ?? '/rehoming/'));
    header('Location: /signin/?mode=login&return_to=' . rawurlencode($path), true, 302);
    exit;
}

function web04_json_response(array $payload, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    web04_security_headers();
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function web04_rate_limit(string $action, string $identity, int $max = 10, int $window = 300): bool {
    $secret = web04_session_secret();
    if ($secret === '') return false;
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $key = hash_hmac('sha256', $action . '|' . web04_normalize_email($identity) . '|' . $ip, $secret);
    $now = time();
    return (bool)web04_store_update('throttle', function (array &$store) use ($key, $now, $max, $window): bool {
        $attempt = $store['attempts'][$key] ?? ['count' => 0, 'window_start' => $now];
        if ($now - (int)($attempt['window_start'] ?? 0) >= $window) $attempt = ['count' => 0, 'window_start' => $now];
        $attempt['count'] = (int)$attempt['count'] + 1;
        $store['attempts'][$key] = $attempt;
        foreach ($store['attempts'] as $k => $v) if ($now - (int)($v['window_start'] ?? 0) > $window * 2) unset($store['attempts'][$k]);
        return $attempt['count'] <= $max;
    });
}

function web04_account_status(string $accountId, string $status): bool {
    if (!in_array($status, ['active', 'suspended', 'deleted'], true)) return false;
    return (bool)web04_store_update('accounts', function (array &$store) use ($accountId, $status): bool {
        foreach ($store['accounts'] as &$account) {
            if (is_array($account) && ($account['account_id'] ?? '') === $accountId) {
                if (($account['status'] ?? '') === 'deleted' && $status !== 'deleted') return false;
                $account['status'] = $status;
                $account['updated_at'] = gmdate('c');
                return true;
            }
        }
        unset($account);
        return false;
    });
}

function web04_revoke_account_tokens(string $accountId, ?string $pathOverride = null): void {
    $path = $pathOverride ?? (web04_runtime_store_dir() . DIRECTORY_SEPARATOR . 'tokens.json');
    if (is_link($path) || is_link($path . '.lock')) throw new RuntimeException('Session-token store path is unsafe');
    if (!is_file($path)) return;
    $lockPath = $path . '.lock';
    $lock = fopen($lockPath, 'c');
    if ($lock === false) throw new RuntimeException('Unable to lock session-token store');
    @chmod($lockPath, 0600);
    try {
        if (!flock($lock, LOCK_EX)) throw new RuntimeException('Unable to lock session-token store');
        $data = json_decode((string)file_get_contents($path), true);
        if (!is_array($data)) throw new RuntimeException('Session-token store is malformed');
        foreach ($data as &$token) if (is_array($token) && ($token['account_id'] ?? '') === $accountId) $token['revoked'] = true;
        unset($token);
        $tmp = $path . '.' . bin2hex(random_bytes(8)) . '.tmp';
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $previousMask = umask(0077);
        try { $written = $json === false ? false : file_put_contents($tmp, $json, LOCK_EX); }
        finally { umask($previousMask); }
        if ($written === false || !@chmod($tmp, 0600) || !rename($tmp, $path)) {
            @unlink($tmp);
            throw new RuntimeException('Unable to revoke session tokens');
        }
        @chmod($path, 0600);
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}

function web04_logout(): void {
    try { $user = web04_current_user(); }
    catch (Throwable $error) { $user = null; }
    web04_session_start();
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        if (!session_regenerate_id(true)) {
            session_destroy();
            throw new RuntimeException('Unable to rotate session during logout');
        }
        $_SESSION['web04_csrf'] = bin2hex(random_bytes(32));
        if (!session_write_close()) throw new RuntimeException('Unable to persist signed-out session');
    }
    if ($user) web04_revoke_account_tokens($user['account_id']);
}

function web04_oauth_configured(): bool {
    $clientId = web04_env('GOOGLE_CLIENT_ID');
    $clientSecret = web04_env('GOOGLE_CLIENT_SECRET');
    $redirect = web04_env('GOOGLE_REDIRECT_URI');
    $base = web04_app_origin();
    return web04_auth_ready() && $clientId !== '' && $clientSecret !== '' && $redirect !== '' && $base !== null
        && hash_equals($base . '/rehoming/google-oauth-callback.php', $redirect);
}

function web04_oauth_start(string $returnPath): array {
    web04_session_start();
    $state = web04_base64url_encode(random_bytes(32));
    $nonce = web04_base64url_encode(random_bytes(32));
    $verifier = web04_base64url_encode(random_bytes(32));
    $challenge = web04_base64url_encode(hash('sha256', $verifier, true));
    $_SESSION['web04_oauth'] = [
        'state' => $state,
        'nonce' => $nonce,
        'code_verifier' => $verifier,
        'return_to' => web04_safe_return_path($returnPath),
        'created_at' => time(),
    ];
    return ['state' => $state, 'nonce' => $nonce, 'code_verifier' => $verifier, 'code_challenge' => $challenge];
}

function web04_oauth_consume_state(string $state, ?int $now = null): ?array {
    web04_session_start();
    $transaction = $_SESSION['web04_oauth'] ?? null;
    unset($_SESSION['web04_oauth']);
    $now ??= time();
    if (!is_array($transaction) || !is_string($transaction['state'] ?? null) || !is_string($state) || !hash_equals($transaction['state'], $state)) return null;
    if ($now - (int)($transaction['created_at'] ?? 0) < 0 || $now - (int)$transaction['created_at'] > WEB04_OAUTH_STATE_TTL) return null;
    return $transaction;
}

function web04_base64url_encode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function web04_base64url_decode(string $data): string|false {
    if ($data === '' || preg_match('/[^A-Za-z0-9_-]/', $data)) return false;
    $padding = (4 - strlen($data) % 4) % 4;
    $decoded = base64_decode(strtr($data, '-_', '+/') . str_repeat('=', $padding), true);
    return $decoded === false ? false : $decoded;
}

function web04_google_jwks(): ?array {
    if (!function_exists('curl_init')) return null;
    $curl = curl_init('https://www.googleapis.com/oauth2/v3/certs');
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
        CURLOPT_USERAGENT => 'PetCenter-Rehoming-Auth/1.0',
    ]);
    $body = curl_exec($curl);
    $status = (int)curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    curl_close($curl);
    if (!is_string($body) || strlen($body) > 1048576 || $status !== 200) return null;
    $decoded = json_decode($body, true);
    return is_array($decoded) && is_array($decoded['keys'] ?? null) ? $decoded : null;
}

function web04_der_length(int $length): string {
    if ($length < 128) return chr($length);
    $bytes = '';
    while ($length > 0) { $bytes = chr($length & 0xff) . $bytes; $length >>= 8; }
    return chr(0x80 | strlen($bytes)) . $bytes;
}

function web04_der_wrap(int $tag, string $value): string {
    return chr($tag) . web04_der_length(strlen($value)) . $value;
}

function web04_jwk_public_key_pem(array $jwk): ?string {
    $modulus = is_string($jwk['n'] ?? null) ? web04_base64url_decode($jwk['n']) : false;
    $exponent = is_string($jwk['e'] ?? null) ? web04_base64url_decode($jwk['e']) : false;
    if ($modulus === false || $exponent === false || $modulus === '' || $exponent === '') return null;
    $integer = static function (string $bytes): string {
        if ((ord($bytes[0]) & 0x80) !== 0) $bytes = "\x00" . $bytes;
        return web04_der_wrap(0x02, $bytes);
    };
    $rsa = web04_der_wrap(0x30, $integer($modulus) . $integer($exponent));
    $algorithm = hex2bin('300d06092a864886f70d0101010500');
    if ($algorithm === false) return null;
    $spki = web04_der_wrap(0x30, $algorithm . web04_der_wrap(0x03, "\x00" . $rsa));
    return "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($spki), 64, "\n") . "-----END PUBLIC KEY-----\n";
}

function web04_verify_google_id_token(string $jwt, string $nonce, string $clientId, array $jwks, ?int $now = null): ?array {
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) return null;
    $headerJson = web04_base64url_decode($parts[0]);
    $claimsJson = web04_base64url_decode($parts[1]);
    $signature = web04_base64url_decode($parts[2]);
    if ($headerJson === false || $claimsJson === false || $signature === false) return null;
    $header = json_decode($headerJson, true);
    $claims = json_decode($claimsJson, true);
    if (!is_array($header) || !is_array($claims) || ($header['alg'] ?? '') !== 'RS256' || !is_string($header['kid'] ?? null)) return null;
    $key = null;
    foreach ($jwks['keys'] ?? [] as $candidate) if (is_array($candidate) && ($candidate['kid'] ?? '') === $header['kid'] && ($candidate['kty'] ?? '') === 'RSA' && ($candidate['alg'] ?? 'RS256') === 'RS256' && is_string($candidate['n'] ?? null) && is_string($candidate['e'] ?? null)) { $key = $candidate; break; }
    if (!$key) return null;
    $pem = web04_jwk_public_key_pem($key);
    if ($pem === null) return null;
    $publicKey = openssl_pkey_get_public($pem);
    if ($publicKey === false) return null;
    $verified = openssl_verify($parts[0] . '.' . $parts[1], $signature, $publicKey, OPENSSL_ALGO_SHA256);
    if ($verified !== 1) return null;
    $now ??= time();
    $issuer = $claims['iss'] ?? null;
    $audience = $claims['aud'] ?? null;
    $audiences = is_string($audience) ? [$audience] : (is_array($audience) ? $audience : []);
    if (!in_array($issuer, ['https://accounts.google.com', 'accounts.google.com'], true)
        || !in_array($clientId, $audiences, true)
        || (array_key_exists('azp', $claims) && (!is_string($claims['azp']) || !hash_equals($clientId, $claims['azp'])))
        || (count($audiences) > 1 && ($claims['azp'] ?? '') !== $clientId)
        || !is_numeric($claims['exp'] ?? null) || (int)$claims['exp'] < $now - 30
        || !is_numeric($claims['iat'] ?? null) || (int)$claims['iat'] > $now + 30
        || (int)$claims['iat'] < $now - 7200
        || (array_key_exists('nbf', $claims) && (!is_numeric($claims['nbf']) || (int)$claims['nbf'] > $now + 30))
        || !is_string($claims['sub'] ?? null) || $claims['sub'] === '' || strlen($claims['sub']) > 255
        || !is_string($claims['nonce'] ?? null) || !hash_equals($nonce, $claims['nonce'])
        || ($claims['email_verified'] ?? null) !== true
        || !is_string($claims['email'] ?? null) || filter_var($claims['email'], FILTER_VALIDATE_EMAIL) === false) return null;
    return [
        'sub' => $claims['sub'],
        'email' => web04_normalize_email($claims['email']),
            'display_name' => is_string($claims['name'] ?? null) ? trim(web04_text_slice($claims['name'], 0, 120)) : '',
        'email_verified' => true,
    ];
}

function web04_google_exchange_code(string $code, string $verifier): ?string {
    if (!web04_oauth_configured() || !function_exists('curl_init') || $code === '' || strlen($code) > 4096) return null;
    $curl = curl_init('https://oauth2.googleapis.com/token');
    $payload = http_build_query([
        'code' => $code,
        'client_id' => web04_env('GOOGLE_CLIENT_ID'),
        'client_secret' => web04_env('GOOGLE_CLIENT_SECRET'),
        'redirect_uri' => web04_env('GOOGLE_REDIRECT_URI'),
        'grant_type' => 'authorization_code',
        'code_verifier' => $verifier,
    ], '', '&', PHP_QUERY_RFC3986);
    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded', 'Accept: application/json'],
        CURLOPT_USERAGENT => 'PetCenter-Rehoming-Auth/1.0',
    ]);
    $body = curl_exec($curl);
    $status = (int)curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    curl_close($curl);
    if (!is_string($body) || strlen($body) > 1048576 || $status !== 200) return null;
    $result = json_decode($body, true);
    return is_array($result) && is_string($result['id_token'] ?? null) ? $result['id_token'] : null;
}

function web04_upsert_google_account(array $identity): ?array {
    if (($identity['email_verified'] ?? false) !== true || !is_string($identity['sub'] ?? null) || $identity['sub'] === '' || !is_string($identity['email'] ?? null)) return null;
    $email = web04_normalize_email($identity['email']);
    $sub = $identity['sub'];
    if (web04_find_account_by_email($email) === null
        && (!web04_rate_limit('google-account-create', $email, 5, 3600)
            || !web04_rate_limit('google-account-create-ip', '', 20, 3600))) return null;
    return web04_store_update('accounts', function (array &$store) use ($email, $sub, $identity): ?array {
        foreach ($store['accounts'] as &$account) {
            if (!is_array($account)) continue;
            if (($account['google_sub'] ?? null) === $sub) {
                if (!empty($account['is_test_account'])) { unset($account); return null; }
                if (web04_normalize_email((string)($account['email'] ?? '')) !== $email || ($account['status'] ?? '') !== 'active') { unset($account); return null; }
                $account['email_verified'] = true;
                if (($account['display_name'] ?? '') === '' && ($identity['display_name'] ?? '') !== '') $account['display_name'] = $identity['display_name'];
                $account['updated_at'] = gmdate('c');
                $found = $account;
                unset($account);
                return $found;
            }
            if (web04_normalize_email((string)($account['email'] ?? '')) === $email) {
                if (!empty($account['is_test_account'])) { unset($account); return null; }
                if (!empty($account['google_sub']) && !hash_equals((string)$account['google_sub'], $sub)) { unset($account); return null; }
                if (!in_array(($account['account_type'] ?? ''), WEB04_ALLOWED_ROLES, true) || ($account['status'] ?? '') !== 'active') { unset($account); return null; }
                $account['google_sub'] = $sub;
                $account['email_verified'] = true;
                if (($account['display_name'] ?? '') === '' && ($identity['display_name'] ?? '') !== '') $account['display_name'] = $identity['display_name'];
                $account['updated_at'] = gmdate('c');
                $found = $account;
                unset($account);
                return $found;
            }
        }
        unset($account);
        $now = gmdate('c');
        $record = [
            'account_id' => 'acct-' . strtoupper(bin2hex(random_bytes(12))),
            'account_type' => 'visitor',
            'status' => 'active',
            'verification_status' => null,
            'email' => $email,
            'email_verified' => true,
            'display_name' => trim((string)($identity['display_name'] ?? '')),
            'google_sub' => $sub,
            'is_test_account' => false,
            'profile' => [],
            'created_at' => $now,
            'updated_at' => $now,
        ];
        $store['accounts'][] = $record;
        return $record;
    });
}

function web04_provision_account(string $email, string $role, string $displayName = ''): array {
    $email = web04_normalize_email($email);
    if (!in_array($role, WEB04_ALLOWED_ROLES, true) || filter_var($email, FILTER_VALIDATE_EMAIL) === false) throw new InvalidArgumentException('A valid email and role are required');
    return web04_store_update('accounts', function (array &$store) use ($email, $role, $displayName): array {
        foreach ($store['accounts'] as $account) {
            if (is_array($account) && web04_normalize_email((string)($account['email'] ?? '')) === $email) {
                if (($account['account_type'] ?? '') !== $role) throw new RuntimeException('An account already exists with a different role; review it manually');
                return $account;
            }
        }
        $now = gmdate('c');
        $record = [
            'account_id' => 'acct-' . strtoupper(bin2hex(random_bytes(12))),
            'account_type' => $role,
            'status' => 'active',
            'verification_status' => $role === 'merchant' ? 'pending' : null,
            'email' => $email,
            'email_verified' => false,
            'display_name' => trim(web04_text_slice($displayName, 0, 120)),
            'google_sub' => null,
            'is_test_account' => false,
            'profile' => [],
            'provisioned_by' => 'trusted_cli',
            'created_at' => $now,
            'updated_at' => $now,
        ];
        $store['accounts'][] = $record;
        return $record;
    });
}

function web04_save_profile(string $accountId, array $profile): ?array {
    foreach (['householdType', 'experienceLevel', 'otherPets', 'kidsInHome', 'otherPetsSpecies', 'whyThisPet'] as $field) {
        if (array_key_exists($field, $profile) && !is_string($profile[$field])) return null;
    }
    $household = (string)($profile['householdType'] ?? '');
    $experience = (string)($profile['experienceLevel'] ?? '');
    $otherPets = (string)($profile['otherPets'] ?? '');
    $kids = (string)($profile['kidsInHome'] ?? '');
    $species = trim((string)($profile['otherPetsSpecies'] ?? ''));
    $why = trim((string)($profile['whyThisPet'] ?? ''));
    if (!in_array($household, ['', 'apartment', 'house_yard', 'house_no_yard'], true)
        || !in_array($experience, ['', 'first_time', 'some', 'experienced'], true)
        || !in_array($otherPets, ['', 'yes', 'no'], true)
        || !in_array($kids, ['', 'yes', 'no'], true)
        || web04_text_length($species) > 120 || web04_text_length($why) > 400) return null;
    if ($otherPets !== 'yes') $species = '';
    $account = web04_find_account_by_id($accountId);
    if (!$account || ($account['status'] ?? '') !== 'active' || ($account['account_type'] ?? '') !== 'visitor') return null;
    $savedProfile = ['householdType' => $household, 'experienceLevel' => $experience, 'otherPets' => $otherPets, 'otherPetsSpecies' => $species, 'kidsInHome' => $kids, 'whyThisPet' => $why];
    $saved = web04_store_update('profiles', function (array &$store) use ($accountId, $savedProfile): array {
        $store['profiles'][$accountId] = $savedProfile;
        return $savedProfile;
    });
    return ['account_id' => $accountId, 'account_type' => 'visitor', 'status' => 'active', 'profile' => $saved];
}

function web04_delete_profile(string $accountId): void {
    web04_store_update('profiles', function (array &$store) use ($accountId): void { unset($store['profiles'][$accountId]); });
}

function web04_account_has_listing_eligibility(array $account): bool {
    return ($account['account_type'] ?? '') === 'merchant'
        && ($account['status'] ?? '') === 'active'
        && ($account['verification_status'] ?? '') === 'verified';
}

/* TASK-WEB-04 END — Rehoming authentication and session core. */
