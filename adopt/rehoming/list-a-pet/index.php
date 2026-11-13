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
if (!web04_account_has_listing_eligibility($user)) {
    http_response_code(403);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Verification required</title><main><h1>Merchant verification required</h1><p>Your merchant account must be verified before you can create a listing. You can review the verification process or contact Pet Center support for help.</p><p><a href="/verify/">Open verification</a></p></main></html>';
    exit;
}
include __DIR__ . '/index.html';
/* TASK-WEB-04 END — Protected merchant listing-entry route wrapper. */
