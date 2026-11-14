<?php
/** TASK-WEB-04 START — Trusted CLI-only provisioning of a Rehoming merchant/admin identity. */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/../adopt/rehoming/web04-auth-core.php';

if ($argc < 3 || $argc > 4) {
    fwrite(STDERR, "Usage: php tools/provision-rehoming-account.php <email> <visitor|merchant|admin> [display-name]\n");
    exit(2);
}
$email = (string)$argv[1];
$role = (string)$argv[2];
$displayName = (string)($argv[3] ?? '');
if (!in_array($role, WEB04_ALLOWED_ROLES, true)) {
    fwrite(STDERR, "Role must be visitor, merchant, or admin.\n");
    exit(2);
}
try {
    $account = web04_provision_account($email, $role, $displayName);
    fwrite(STDOUT, "Provisioned role: {$account['account_type']}\nAccount ID: {$account['account_id']}\nGoogle sign-in will claim this record only after the provider verifies the matching email.\n");
} catch (Throwable $error) {
    fwrite(STDERR, "Provisioning failed: {$error->getMessage()}\n");
    exit(1);
}
/* TASK-WEB-04 END — Trusted CLI-only provisioning utility. */
