<?php
/**
 * update-verification.php
 *
 * TASK-B2 — new file, no existing code to merge (per Directive 8a's "New
 * files created from scratch" exception).
 *
 * Handles both Approve and Reject actions from the admin dashboard's
 * Verification Requests panel. Mirrors update-adoption.php's shape
 * (token + POST fields, updates a record, returns {success, message})
 * per Rule 7 — same call pattern the dashboard's JS (submitVerifApprove()/
 * submitVerifReject() in admin-dashboard.html) already expects.
 *
 * Exact behavior per the task spec:
 *  - Approve -> verification_status = 'verified', triggers B3's badge
 *    immediately (badge logic reads this field directly per SHARED
 *    CONTRACTS — no separate "award badge" call needed from here).
 *  - Reject  -> verification_status = 'rejected', REQUIRED reason is
 *    stored for internal record and included in the merchant's email.
 *    Resubmission is allowed by default (not a permanent lock-out) — the
 *    merchant's own re-entry into B1's flow re-runs verification from
 *    scratch, this endpoint doesn't need to do anything special to
 *    "allow" that beyond not deleting their record or blocking it.
 *  - Either action -> confirmation email via the platform's existing
 *    email-sending implementation, reused (not rebuilt) per Directive 7 —
 *    see verification-mail.php, which mirrors chat-mail.php's exact
 *    structure (require contact-config.php, sendContactEmail(), try/catch
 *    + return-value-checked send-and-log wrapper).
 */

require_once __DIR__ . '/verification-store.php';      // TASK-B2: shared read/write + admin-token check
require_once __DIR__ . '/verification-mail.php';       // TASK-B2: approve/reject email templates

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!verifRequireAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid or missing admin token']);
    exit;
}

// ── INPUT ────────────────────────────────────────────────────────────────
// Accept both real POST fields and a JSON body, same defensive pattern
// contact-handler.php already uses (json_decode(...) ?? $_POST).
$input  = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$username = trim((string)($input['username'] ?? ''));
$status   = trim((string)($input['verification_status'] ?? ''));
$reason   = trim((string)($input['rejection_reason'] ?? ''));

// Alphanumeric + hyphen/underscore only, same input-sanitization pattern
// documented for ID-style fields elsewhere in the admin dashboard
// (delete-application.php's precedent, per ADMIN_DASHBOARD_PROJECT_SUMMARY.md) —
// prevents path-traversal-style or malformed usernames reaching the store.
if ($username === '' || !preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing or invalid username']);
    exit;
}

if (!in_array($status, ['verified', 'rejected'], true)) {
    // NOTE: 'pending' is deliberately not accepted here — this endpoint only
    // ever moves a request OUT of pending, matching the two buttons the UI
    // actually offers (Approve/Reject). The full enum ('pending'|'verified'|
    // 'rejected') is still exactly what SHARED CONTRACTS locks for this
    // field project-wide; this endpoint just doesn't need to set 'pending'.
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'verification_status must be "verified" or "rejected"']);
    exit;
}

if ($status === 'rejected' && $reason === '') {
    // Required per the task spec — enforced again here server-side even
    // though the dashboard's own JS already checks this, since a client
    // check alone is never sufficient.
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'A rejection reason is required']);
    exit;
}

// ── UPDATE RECORD ───────────────────────────────────────────────────────
// Reads every record to find the matching one (still needed — we only know
// a username, not which folder it's in without checking), but writes back
// ONLY that one record's own file via verifSaveOneRequest(), not the whole
// list. Doing this with verifSaveRequests($requests) here was a real bug:
// it would rewrite every OTHER request's folder too, each time overwriting
// them with whatever was read in THIS request's $requests snapshot — a
// classic lost-update race if two admins (or two tabs) act on different
// requests close together. Fixed by only ever writing the single record
// that actually changed.
$requests = verifLoadRequests();
$match = null;
foreach ($requests as $req) {
    if (($req['username'] ?? '') === $username) {
        $match = $req;
        break;
    }
}

if ($match === null) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'No pending verification request found for that username']);
    exit;
}

$match['verification_status'] = $status;
if ($status === 'rejected') {
    $match['rejection_reason'] = $reason;
}

if (!verifSaveOneRequest($match)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not save the updated request']);
    exit;
}

// ── EMAIL (reused existing implementation, per Directive 7) ────────────
// verifNotify*() never throws and never blocks the response on a mail
// failure (same swallow-and-log contract as chat-mail.php's
// chatMailSendAndLog()) — the status update above has already succeeded
// and is saved regardless of whether the email send itself works.
$emailSent = ($status === 'verified')
    ? verifNotifyApproved($username)
    : verifNotifyRejected($username, $reason);

echo json_encode([
    'success'    => true,
    'message'    => $status === 'verified' ? 'Merchant approved' : 'Merchant rejected',
    'email_sent' => $emailSent,
]);
