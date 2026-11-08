<?php
/** TASK-WEB-04 START — Protected merchant listing-entry route wrapper. */
declare(strict_types=1);
require_once __DIR__ . '/../web04-auth-core.php';
web04_security_headers();
try {
    $user = web04_current_user();
} catch (Throwable $error) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'The account service is temporarily unavailable.';
    exit;
}
if (!$user) web04_redirect_to_signin('/rehoming/list-a-pet/');
if (!web04_user_has_role($user, 'merchant')) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'This account is not authorized to publish merchant listings.';
    exit;
}
include __DIR__ . '/index.html';
/* TASK-WEB-04 END — Protected merchant listing-entry route wrapper. */
