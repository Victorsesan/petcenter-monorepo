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

/** TASK-WEB-04 START — The pre-account upload remains public but every submission is CSRF-protected. */
require_once __DIR__ . '/../rehoming/web04-auth-core.php';
web04_session_start();
web04_security_headers();
/** TASK-WEB-04 END */

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
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
/** TASK-WEB-04 START — Require the session-bound token and canonical same-origin request. */
if (!web04_csrf_valid(true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Request verification failed. Refresh and try again.']);
    exit;
}
/** TASK-WEB-04 END */

// ============================================
// TEMP STORAGE — separate from contact-config.php's own UPLOAD_TEMP_DIR.
// Temporary media and the small review queue live under a required external
// directory, never beneath the served tree.
// ============================================
try { $verifyStorageRoot = verifyPrivateStorageRoot(); }
catch (Throwable $error) {
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'Verification uploads are temporarily unavailable.']);
    exit;
}
define('VERIFY_TEMP_DIR', $verifyStorageRoot . DIRECTORY_SEPARATOR . 'verification-temp' . DIRECTORY_SEPARATOR);
try { verifyEnsurePrivateDirectory(VERIFY_TEMP_DIR); }
catch (Throwable $error) {
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'Verification uploads are temporarily unavailable.']);
    exit;
}
// The directory is owner-only and outside the web root; its deny rule is
// retained as defense in depth in case hosting maps it into a served path.

/**
 * TASK-B1 (integration fix, B2 reconciliation — flagged as an interpretive
 * call, not a literal reading of the original spec): B1's task text says
 * "nothing stored server-side after the email send completes." That line
 * was written about the video/ID images specifically — the privacy-
 * sensitive biometric material — and those are still deleted unconditionally
 * below, exactly as before. But B2's admin review queue needs *something*
 * durable to read, or "nothing stored, full stop" makes a queue impossible
 * by construction. This directory holds ONLY a small text record per
 * submission (username, email, country, timestamp, status, and email result);
 * these fields are personal data and the directory is restricted. The video
 * and ID images themselves are never stored in the queue. This interpretation should be
 * confirmed with the CEO/VDM; until then, treat this as the working
 * assumption both B1 and B2 are integrating against.
 */
define('VERIFY_QUEUE_DIR', $verifyStorageRoot . DIRECTORY_SEPARATOR . 'verification-queue' . DIRECTORY_SEPARATOR);
try { verifyEnsurePrivateDirectory(VERIFY_QUEUE_DIR); }
catch (Throwable $error) {
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'Verification uploads are temporarily unavailable.']);
    exit;
}
// This queue contains PII and remains restricted to the server owner.

/**
 * Prepares owner-only verification storage and a deny-all web-server rule;
 * failure blocks the upload instead of falling back to public storage.
 */
function verifyEnsurePrivateDirectory(string $dir): void {
    $dir = rtrim($dir, DIRECTORY_SEPARATOR);
    if ($dir === '' || is_link($dir) || (!is_dir($dir) && !@mkdir($dir, 0700, true) && !is_dir($dir)) || !is_writable($dir)) {
        throw new RuntimeException('Private verification storage is unavailable');
    }
    if (!@chmod($dir, 0700)) throw new RuntimeException('Private verification storage permissions could not be set');
    verifyEnsureHtaccessDeny($dir);
}

function verifyPrivateStorageRoot(): string {
    $configured = getenv('VERIFY_STORAGE_DIR') ?: ($_ENV['VERIFY_STORAGE_DIR'] ?? '');
    if (!is_string($configured) || trim($configured) === '' || $configured[0] !== DIRECTORY_SEPARATOR
        || !is_dir($configured) || is_link(rtrim($configured, DIRECTORY_SEPARATOR))) {
        throw new RuntimeException('Verification storage must be explicitly configured as an external absolute directory');
    }
    $real = realpath($configured);
    $webRoot = realpath(dirname(__DIR__));
    if ($real === false || $webRoot === false || $real === $webRoot
        || str_starts_with($real, rtrim($webRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR)) {
        throw new RuntimeException('Verification storage must resolve outside the web root');
    }
    $permissions = @fileperms($real);
    if (!is_writable($real) || $permissions === false || ($permissions & 0077) !== 0) {
        throw new RuntimeException('Verification storage must be owner-only and writable');
    }
    return rtrim($real, DIRECTORY_SEPARATOR);
}

function verifyEnsureHtaccessDeny(string $dir): void {
    $htaccessPath = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.htaccess';
    $rules = "<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n    Order allow,deny\n    Deny from all\n</IfModule>\n";
    if (is_link($htaccessPath)) throw new RuntimeException('Verification storage access rules are unsafe');
    $current = is_file($htaccessPath) ? file_get_contents($htaccessPath) : '';
    if ($current === false) throw new RuntimeException('Verification storage access rules cannot be read');
    if ($current !== $rules) {
        $tmp = $htaccessPath . '.' . bin2hex(random_bytes(8)) . '.tmp';
        $previousMask = umask(0077);
        try { $written = file_put_contents($tmp, $rules, LOCK_EX); }
        finally { umask($previousMask); }
        if ($written === false || !@chmod($tmp, 0640) || !@rename($tmp, $htaccessPath)) {
            @unlink($tmp);
            throw new RuntimeException('Verification storage access rules could not be installed');
        }
    }
    if (!@chmod($htaccessPath, 0640)) throw new RuntimeException('Verification storage access rules could not be secured');
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
$usernameInput = $_POST['username'] ?? null;
$countryInput = $_POST['country'] ?? null;
$emailInput = $_POST['email'] ?? null;
$username = is_string($usernameInput) ? trim($usernameInput) : '';
$country = is_string($countryInput) ? trim($countryInput) : '';
// TASK-B1 (integration fix): merchant email, added so B2's review queue has
// a way to correspond about approval/rejection — not in the original spec.
$email = is_string($emailInput) ? trim($emailInput) : '';

$errors = [];
if ($username === '' || web04_text_length($username) > 120 || preg_match('//u', $username) !== 1 || preg_match('/[\x00-\x1f\x7f]/', $username)) $errors[] = 'Missing or invalid: username';
if ($country === '' || web04_text_length($country) > 100 || preg_match('//u', $country) !== 1 || preg_match('/[\x00-\x1f\x7f]/', $country)) $errors[] = 'Missing or invalid: country';
if ($email === '' || strlen($email) > 254 || preg_match('/[\x00-\x20\x7f]/', $email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Missing or invalid: email';
if (empty($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) $errors[] = 'Missing or invalid: liveness video';
if (empty($_FILES['id_front']) || $_FILES['id_front']['error'] !== UPLOAD_ERR_OK) $errors[] = 'Missing or invalid: front of ID';
if (empty($_FILES['id_back']) || $_FILES['id_back']['error'] !== UPLOAD_ERR_OK) $errors[] = 'Missing or invalid: back of ID';

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode('; ', $errors)]);
    exit;
}
$htmlUsername = htmlspecialchars($username, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$htmlEmail = htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$htmlCountry = htmlspecialchars($country, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

/** TASK-WEB-04 START — Limit repeat biometric/document submissions by account email and source IP. */
if (!web04_rate_limit('merchant-verification-upload-ip', '', 10, 900)
    || !web04_rate_limit('merchant-verification-upload', $email, 3, 900)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many verification submissions. Please try again later.']);
    exit;
}
/** TASK-WEB-04 END */

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
foreach ($tempPaths as $tempPath) {
    if (!@chmod($tempPath, 0600)) {
        verifyCleanupTempFiles($tempPaths);
        http_response_code(503);
        echo json_encode(['success' => false, 'message' => 'Verification uploads are temporarily unavailable.']);
        exit;
    }
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
                <span class="value">{$htmlUsername}</span>
            </div>
            <div class="field">
                <span class="label">Merchant Email</span>
                <span class="value">{$htmlEmail}</span>
            </div>
            <div class="field">
                <span class="label">Submission Timestamp</span>
                <span class="value">{$submittedAt}</span>
            </div>
            <div class="field">
                <span class="label">Stated Country</span>
                <span class="value">{$htmlCountry}</span>
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
try { $result = sendContactEmail(CONTACT_ADMIN_EMAILS, $subject, $emailHTML, $attachments); }
catch (Throwable $error) { $result = ['success' => false, 'message' => 'Email delivery failed']; }
if (!is_array($result) || !is_bool($result['success'] ?? null)) $result = ['success' => false, 'message' => 'Email delivery failed'];

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
$queueJson = json_encode($queueRecord, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
$previousMask = umask(0077);
try { $queueWritten = $queueJson === false ? false : file_put_contents($queueFile, $queueJson, LOCK_EX); }
finally { umask($previousMask); }
if ($queueWritten === false || !@chmod($queueFile, 0600)) {
    error_log('Verification review-queue record could not be written.');
}

// ============================================
// CLEANUP — always, regardless of outcome. This is the privacy-compliance-
// sensitive step the task calls out explicitly: the video and ID images
// (never the queue record above) may not remain on the server after this
// point, success or failure.
// ============================================
verifyCleanupTempFiles($tempPaths);

if (!$result['success']) {
    error_log('Verification email delivery failed.');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'We could not submit your verification just now. Please try again later or contact Pet Center support.'
    ]);
    exit;
}

http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Verification submitted successfully'
]);
?>
