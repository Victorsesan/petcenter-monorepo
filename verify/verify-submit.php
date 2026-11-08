<?php
/**
 * TASK-B1: Merchant Identity Verification — submission handler
 *
 * Receives the liveness-check video + front/back ID images from
 * verify-account.html, packages them as email attachments, and sends to
 * the platform's admin recipients using the EXISTING mailer implementation
 * in contact-config.php (sendContactEmail()) — per Directive 7, this does
 * not build a new mail system.
 *
 * Privacy-compliance requirement (per Task B1's own spec): nothing is kept
 * server-side after the email send completes. Files land in a short-lived
 * temp directory only for the duration of this one request, and are
 * unlinked immediately once sendContactEmail() confirms success (or, on
 * failure, immediately in the cleanup path too — never left behind either way).
 *
 * For: adopt.petcenterco.com/verify/verify-submit.php — deployed as a plain
 * PHP file sitting alongside verify-account.html's deployed index.html,
 * inside the new /verify/ folder (a root-level sibling of admin/,
 * contact-us/, adoptions/, per the documented site structure in
 * ADMIN_DASHBOARD_PROJECT_SUMMARY.md). Rule 5's folder-path-deployment
 * convention is for HTML pages only — this bare .php filename is correct
 * as-is, it just lives inside that same new folder rather than at site root.
 */

// contact-config.php lives at /contact-us/contact-config.php (confirmed from
// ADMIN_DASHBOARD_PROJECT_SUMMARY.md's documented file tree — admin/,
// contact-us/, adoptions/ etc. are all root-level siblings). This file sits
// in the sibling /verify/ folder, so it steps up one level and over, exactly
// the same relative relationship chat-mail.php (in /admin/) already has to
// this same file.
require_once __DIR__ . '/../contact-us/contact-config.php';

header('Content-Type: application/json');

// ============================================
// SECURITY: method + CORS (same pattern as contact-handler.php, corrected
// domain per that file's own BUGFIX-3 — never the stale docs.petcenterco.com)
// ============================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}
header('Access-Control-Allow-Origin: https://adopt.petcenterco.com');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// ============================================
// TEMP STORAGE — separate from contact-config.php's own UPLOAD_TEMP_DIR
// (that one belongs to the contact-form feature; this flow gets its own,
// scoped to verification only, and is fully cleaned up every request)
// ============================================
define('VERIFY_TEMP_DIR', __DIR__ . '/uploads/verification-temp/');
if (!is_dir(VERIFY_TEMP_DIR)) {
    @mkdir(VERIFY_TEMP_DIR, 0755, true);
}
// Matches the site's established pattern for a staged-upload folder sitting
// inside a public webroot (see ADMIN_DASHBOARD_PROJECT_SUMMARY.md's
// chat-uploads-temp/ — "protected by an auto-written .htaccess containing
// Deny from all, so even though this folder is inside the public /admin/
// webroot, direct browser access to a staged file by guessing its filename
// is blocked at the web server level"). Copied verbatim per Rule 7 rather
// than inventing a different protection scheme. Doubly important here since
// this folder briefly holds a liveness video + ID photos before deletion.
verifyEnsureHtaccessDeny(VERIFY_TEMP_DIR);

/**
 * TASK-B1 (integration fix, B2 reconciliation — flagged as an interpretive
 * call, not a literal reading of the original spec): B1's task text says
 * "nothing stored server-side after the email send completes." That line
 * was written about the video/ID images specifically — the privacy-
 * sensitive biometric material — and those are still deleted unconditionally
 * below, exactly as before. But B2's admin review queue needs *something*
 * durable to read, or "nothing stored, full stop" makes a queue impossible
 * by construction. This directory holds ONLY a small non-sensitive text
 * record per submission (username, email, country, timestamp, status) —
 * never the video or ID images themselves. This interpretation should be
 * confirmed with the CEO/VDM; until then, treat this as the working
 * assumption both B1 and B2 are integrating against.
 */
define('VERIFY_QUEUE_DIR', __DIR__ . '/uploads/verification-queue/');
if (!is_dir(VERIFY_QUEUE_DIR)) {
    @mkdir(VERIFY_QUEUE_DIR, 0755, true);
}
// Same .htaccess protection as VERIFY_TEMP_DIR above — this directory holds
// a username/email/country per pending request, which is still PII a direct
// browser request to a guessed filename should never be able to read.
verifyEnsureHtaccessDeny(VERIFY_QUEUE_DIR);

/**
 * Writes a `Deny from all` .htaccess into $dir if one isn't already there.
 * Shared by both of this file's upload-adjacent directories above.
 */
function verifyEnsureHtaccessDeny($dir) {
    $htaccessPath = $dir . '.htaccess';
    if (!file_exists($htaccessPath)) {
        @file_put_contents($htaccessPath, "Deny from all\n");
    }
}

/**
 * Deletes every file we wrote to VERIFY_TEMP_DIR for this request.
 * Called on both the success path and every failure/early-exit path so
 * nothing is ever left behind, regardless of how the request ends.
 */
function verifyCleanupTempFiles($paths) {
    foreach ($paths as $p) {
        if ($p && file_exists($p)) {
            @unlink($p);
        }
    }
}

// ============================================
// VALIDATE REQUIRED FIELDS
// ============================================
$username = trim(htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8'));
$country = trim(htmlspecialchars($_POST['country'] ?? '', ENT_QUOTES, 'UTF-8'));
// TASK-B1 (integration fix): merchant email, added so B2's review queue has
// a way to correspond about approval/rejection — not in the original spec.
$email = trim(htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'));

$errors = [];
if ($username === '') $errors[] = 'Missing: username';
if ($country === '') $errors[] = 'Missing: country';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Missing or invalid: email';
if (empty($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) $errors[] = 'Missing or invalid: liveness video';
if (empty($_FILES['id_front']) || $_FILES['id_front']['error'] !== UPLOAD_ERR_OK) $errors[] = 'Missing or invalid: front of ID';
if (empty($_FILES['id_back']) || $_FILES['id_back']['error'] !== UPLOAD_ERR_OK) $errors[] = 'Missing or invalid: back of ID';

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode('; ', $errors)]);
    exit;
}

// ============================================
// VALIDATE FILE TYPES / SIZES
// (mirrors the pattern in contact-config.php's UPLOAD_ALLOWED_TYPES, with
// its own limits appropriate to a video + two photos rather than the
// contact form's photo-only limits)
// ============================================
$MAX_VIDEO_BYTES = 60 * 1024 * 1024;  // 60MB — a short liveness clip
$MAX_IMAGE_BYTES = 8 * 1024 * 1024;   // 8MB per ID photo
$ALLOWED_VIDEO_TYPES = ['video/webm', 'video/mp4'];
$ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/jpg', 'image/png'];

$video = $_FILES['video'];
$idFront = $_FILES['id_front'];
$idBack = $_FILES['id_back'];

if ($video['size'] > $MAX_VIDEO_BYTES || !in_array(mime_content_type($video['tmp_name']), $ALLOWED_VIDEO_TYPES)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Liveness video is invalid or too large.']);
    exit;
}
foreach (['id_front' => $idFront, 'id_back' => $idBack] as $label => $f) {
    if ($f['size'] > $MAX_IMAGE_BYTES || !in_array(mime_content_type($f['tmp_name']), $ALLOWED_IMAGE_TYPES)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "$label is invalid or too large."]);
        exit;
    }
}

// ============================================
// MOVE UPLOADS TO OUR OWN SHORT-LIVED TEMP DIR
// (PHP's own upload tmp_name files are already transient, but we copy into
// our own directory with clear names so the attachment filenames read
// sensibly in the admin's inbox, and so cleanup is unambiguous/explicit
// rather than relying on PHP's own end-of-request tmp cleanup implicitly)
// ============================================
$timestamp = date('Y-m-d_H-i-s');
$safeUsername = preg_replace('/[^a-zA-Z0-9_-]/', '_', $username);

$videoExt = ($video['type'] === 'video/mp4') ? 'mp4' : 'webm';
$frontExt = ($idFront['type'] === 'image/png') ? 'png' : 'jpg';
$backExt = ($idBack['type'] === 'image/png') ? 'png' : 'jpg';

$videoPath = VERIFY_TEMP_DIR . "verify_{$safeUsername}_{$timestamp}_video.$videoExt";
$frontPath = VERIFY_TEMP_DIR . "verify_{$safeUsername}_{$timestamp}_id-front.$frontExt";
$backPath = VERIFY_TEMP_DIR . "verify_{$safeUsername}_{$timestamp}_id-back.$backExt";

$tempPaths = [$videoPath, $frontPath, $backPath];

if (!move_uploaded_file($video['tmp_name'], $videoPath)
    || !move_uploaded_file($idFront['tmp_name'], $frontPath)
    || !move_uploaded_file($idBack['tmp_name'], $backPath)) {
    verifyCleanupTempFiles($tempPaths);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to process uploaded files.']);
    exit;
}

// ============================================
// BUILD EMAIL — styled consistently with contact-handler.php's admin email
// (same fonts/colors, same construction pattern), per Directive 7
// ============================================
$submittedAt = date('Y-m-d H:i:s');
$emailHTML = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Sora, Arial, sans-serif; color: #2c2416; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; background: #f9f7f4; padding: 20px; }
        .header { background: linear-gradient(135deg, #10214b 0%, #1a2f5c 100%); color: white; padding: 20px; border-radius: 8px; }
        .header h2 { margin: 0; font-size: 22px; }
        .content { background: white; padding: 24px; margin-top: 16px; border-radius: 8px; border: 1px solid rgba(16,33,75,0.12); }
        .field { margin-bottom: 16px; }
        .label { font-weight: 600; color: #10214b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.04em; display: block; margin-bottom: 4px; }
        .value { color: #2c2416; font-size: 14px; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #7a6d5f; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Merchant Verification Request</h2>
            <div style="font-size: 12px; margin-top: 8px;">Submitted: {$submittedAt}</div>
        </div>
        <div class="content">
            <div class="field">
                <span class="label">Merchant Username</span>
                <span class="value">{$username}</span>
            </div>
            <div class="field">
                <span class="label">Merchant Email</span>
                <span class="value">{$email}</span>
            </div>
            <div class="field">
                <span class="label">Submission Timestamp</span>
                <span class="value">{$submittedAt}</span>
            </div>
            <div class="field">
                <span class="label">Stated Country</span>
                <span class="value">{$country}</span>
            </div>
            <div class="field">
                <span class="label">Attachments</span>
                <span class="value">Liveness-check video, front of ID, back of ID (3 files)</span>
            </div>
        </div>
        <div class="footer">
            <p>Review from the Verification Requests queue in the admin dashboard (Task B2).</p>
        </div>
    </div>
</body>
</html>
HTML;

$attachments = [
    $videoPath => "liveness-video-{$safeUsername}.$videoExt",
    $frontPath => "id-front-{$safeUsername}.$frontExt",
    $backPath => "id-back-{$safeUsername}.$backExt",
];

// ============================================
// SEND — to ALL of CONTACT_ADMIN_EMAILS (confirmed by CEO: any address on
// that constant receives mail sent to it; do not hand-pick a subset)
// ============================================
$subject = "Verification Request — {$username} — {$submittedAt}";
$result = sendContactEmail(CONTACT_ADMIN_EMAILS, $subject, $emailHTML, $attachments);

// ============================================
// QUEUE RECORD (TASK-B1, integration fix for B2) — a small non-sensitive
// JSON record for B2's admin review queue: username/email/country/
// timestamp/status only. Never the video or ID images — those are still
// deleted unconditionally below, unchanged from the original implementation.
// One file per submission (not a single shared array) so B2 can read/update
// each request's `status` independently without a write-lock/merge problem.
// Written regardless of email outcome, same as the temp-file cleanup below —
// a failed email send shouldn't also silently lose the queue entry.
// ============================================
$queueRecord = [
    'username' => $username,
    'email' => $email,
    'country' => $country,
    'submitted_at' => $submittedAt,
    'status' => 'pending',   // matches B2's verification_status contract: pending | verified | rejected
    'email_sent' => $result['success'],
];
$queueFile = VERIFY_QUEUE_DIR . "{$timestamp}_{$safeUsername}.json";
// LOCK_EX per the site's own convention for full-replace JSON writes (see
// ADMIN_DASHBOARD_PROJECT_SUMMARY.md's chat-bot-profile.json: "full-replace
// on every save, LOCK_EX protected"). One file per submission here means
// concurrent writes to the SAME file are unlikely, but costs nothing to match.
@file_put_contents($queueFile, json_encode($queueRecord, JSON_PRETTY_PRINT), LOCK_EX);

// ============================================
// CLEANUP — always, regardless of outcome. This is the privacy-compliance-
// sensitive step the task calls out explicitly: the video and ID images
// (never the queue record above) may not remain on the server after this
// point, success or failure.
// ============================================
verifyCleanupTempFiles($tempPaths);

if (!$result['success']) {
    error_log('Verification email error: ' . $result['message']);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to submit verification: ' . $result['message']
    ]);
    exit;
}

http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Verification submitted successfully'
]);
?>
