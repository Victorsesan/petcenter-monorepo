<?php
/** TASK-MANUS-06 START — shared F3 digest runtime configuration. */
function pc_digest_secret() {
    static $secret = null;
    if ($secret !== null) return $secret;
    $configured = getenv('PC_DIGEST_TOKEN_SECRET');
    if (is_string($configured) && strlen($configured) >= 32) return $secret = $configured;
    $path = dirname(__DIR__, 2) . '/rehoming/data/.digest-token-secret';
    if (is_file($path)) {
        $value = trim((string)@file_get_contents($path));
        if (strlen($value) >= 32) return $secret = $value;
    }
    $value = bin2hex(random_bytes(32));
    if (!is_dir(dirname($path))) @mkdir(dirname($path), 0700, true);
    if (@file_put_contents($path, $value . "\n", LOCK_EX) !== false) @chmod($path, 0600);
    return $secret = $value;
}
function pc_digest_token($email) { return hash_hmac('sha256', strtolower(trim($email)), pc_digest_secret()); }
function pc_digest_send($to, $subject, $html) {
    $config = dirname(__DIR__, 2) . '/contact-us/contact-config.php';
    if (is_file($config)) require_once $config;
    if (function_exists('sendContactEmail')) {
        $result = sendContactEmail([$to], $subject, $html);
        return !empty($result['success']);
    }
    error_log('Pet Center digest delivery unavailable: contact mailer not loaded.');
    return false;
}
/* TASK-MANUS-06 END */
