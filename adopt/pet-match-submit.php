<?php
/**
 * pet-match-submit.php
 *
 * Backend submission handler for the Pet Match Request form (pet-match.html,
 * built in Task 5/5b). This file did not exist prior to Task 9 — Task 5b's
 * completion doc documented the expected contract for it but explicitly
 * left it unbuilt. Per the CEO's instruction, this task builds it from
 * that contract plus pet-match.html's real, already-shipped
 * collectPayload() fields.
 *
 * REVISION NOTE: an earlier draft of this file assumed MySQL/PDO storage
 * (chatDb(), an INSERT into a pet_match_requests table). That assumption
 * was wrong and has been corrected here after reading Task-6a-Completion.md
 * and Task-6b-Completion.md, which together confirm the real storage
 * convention this endpoint must write into: one request.json file per
 * submission, under /adoptions/pet-match-requests/{category}/{request_code}/,
 * mirroring /adoptions/applications/{code}/application.json exactly —
 * the same convention get-pet-match-requests.php (Task 6a) already reads
 * from and update-pet-match-request.php (Task 6b) already writes back to.
 *
 * SCOPE NOTE: Only the block wrapped in TASK-9 START/END below is this
 * task's actual assignment (duplicate-submission detection). Everything
 * else in this file (honeypot/timing/Turnstile checks, rate limiting,
 * the JSON write itself) is scaffolding required for this endpoint to
 * exist and run at all — it implements Task 5b's own already-documented
 * contract and Task 6a/6b's already-established storage convention, not
 * new design decisions made by this task. See Task-9-Completion.md for
 * the full explanation of why building this endpoint was necessary.
 *
 * This is a PUBLIC, visitor-facing endpoint (the form at /pet-match/ is
 * open to any site visitor) — unlike get-pet-match-requests.php and
 * update-pet-match-request.php, it deliberately does NOT check an admin
 * token. Its defenses are the honeypot/timing-trap/Turnstile/rate-limit
 * checks below instead, per Task 5b's documented spec for this endpoint.
 */

require_once __DIR__ . '/admin/chat-config.php'; // reused only for chatCheckRateLimit(), not for any DB connection — this endpoint uses flat-file storage, not chatDb(). Path corrected: this file lives at the site root (pet-match.html's fetch() call targets the root-relative /pet-match-submit.php), while chat-config.php lives inside admin/ per the documented File Structure on Server — an earlier version of this file incorrectly assumed both files were siblings in admin/

header('Content-Type: application/json');

// ─────────────────────────────────────────────────────────────────────────
// Only accept POST with a JSON body, matching pet-match.html's fetch() call
// (Content-Type: application/json, JSON.stringify(payload) body).
// ─────────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid request body']);
    exit;
}

// ─────────────────────────────────────────────────────────────────────────
// Form Hardening checks (Task 5b's documented contract, implemented here
// since this is the first time the endpoint itself has been built).
// ─────────────────────────────────────────────────────────────────────────

// 1. Honeypot: real visitors never fill this field; a non-empty value means
//    a bot's generic form-filler touched it. Respond as if successful
//    (fabricated reference code, nothing written to disk) so bots aren't
//    tipped off that they were caught.
if (!empty($payload['website'])) {
    http_response_code(200);
    echo json_encode(['reference_code' => 'PM-00000-0000']);
    exit;
}

// 2. Minimum-fill-time trap: reject submissions faster than a human could
//    plausibly complete a 7-step wizard (~3 seconds, per Task 5b's spec).
$renderedAt = isset($payload['form_rendered_at']) ? (float) $payload['form_rendered_at'] : 0;
if ($renderedAt <= 0 || (microtime(true) * 1000 - $renderedAt) < 3000) {
    http_response_code(400);
    echo json_encode(['message' => 'Submission rejected — please try again.']);
    exit;
}

// 3. Cloudflare Turnstile — verify the token server-side before accepting.
//    TURNSTILE_SECRET_KEY must be defined in chat-config.php or an env var;
//    this file does not hardcode it. Fails closed (rejects) if unset.
$turnstileToken = $payload['turnstile_token'] ?? '';
if (empty($turnstileToken) || !defined('TURNSTILE_SECRET_KEY')) {
    http_response_code(400);
    echo json_encode(['message' => 'Verification failed — please refresh and try again.']);
    exit;
}
$verify = @file_get_contents('https://challenges.cloudflare.com/turnstile/v0/siteverify', false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => http_build_query([
            'secret' => TURNSTILE_SECRET_KEY,
            'response' => $turnstileToken,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ]),
    ],
]));
$verifyResult = $verify ? json_decode($verify, true) : null;
if (!$verifyResult || empty($verifyResult['success'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Verification failed — please refresh and try again.']);
    exit;
}

// 4. Server-side rate limiting — reuses the existing chatCheckRateLimit()
//    sliding-window helper already documented in
//    ADMIN_DASHBOARD_PROJECT_SUMMARY.md, for consistency with the rest of
//    the site rather than inventing a second limiter. (This helper's own
//    bookkeeping may itself use chatDb() internally — that's unrelated
//    to how THIS endpoint stores pet match requests, which is flat-file.)
$clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!chatCheckRateLimit('pet_match_submit_' . $clientIp, 5, 600)) { // 5 submissions per 10 minutes per IP
    http_response_code(429);
    echo json_encode(['message' => 'Too many requests — please wait a few minutes and try again.']);
    exit;
}

// ─────────────────────────────────────────────────────────────────────────
// Required-field validation (mirrors pet-match.html's own client-side
// validateStep() checks for step 1/5/7, re-checked server-side since
// client-side validation alone is never a real defense).
// ─────────────────────────────────────────────────────────────────────────
$fullName = trim($payload['full_name'] ?? '');
$email = trim($payload['email'] ?? '');
if ($fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['message' => 'Please provide a valid name and email address.']);
    exit;
}
if (empty($payload['image_terms_agree'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Please confirm the reference image terms before submitting.']);
    exit;
}

// Category must be one of the 7 valid slugs — same set get-pet-match-requests.php
// (Task 6a) validates against, and the same folder names used under
// /adoptions/pet-match-requests/{category}/.
$validCategories = ['dogs', 'cats', 'birds', 'reptiles', 'small-mammals', 'livestocks', 'aquariums'];
$category = $payload['category'] ?? '';
if (!in_array($category, $validCategories, true)) {
    http_response_code(400);
    echo json_encode(['message' => 'Please select a valid pet category.']);
    exit;
}

// ─────────────────────────────────────────────────────────────────────────
// Base storage path — mirrors /adoptions/applications/{code}/application.json's
// existing convention, adapted with a category subfolder since Pet Match
// Requests are organized by category from the start (confirmed against
// Task-6a-Completion.md's documented storage shape).
// ─────────────────────────────────────────────────────────────────────────
// Path corrected: this file lives at the site root (see require_once note
// above), where adoptions/ is a direct child — not inside admin/, where an
// earlier version of this file incorrectly assumed it lived (which would
// have required '../adoptions/' instead). This now matches the same
// directory get-pet-match-requests.php (admin/get-pet-match-requests.php)
// correctly reaches via its own '../adoptions/' — both resolve to the same
// real /adoptions/pet-match-requests/{category}/ directory.
$baseDir = __DIR__ . '/adoptions/pet-match-requests/' . $category;

// TASK-9 START — Duplicate-Submission Detection (this task's actual scope)
//
// Requirement: when a new Pet Match Request comes in, check whether the
// same visitor already has an existing UNRESOLVED request (matching email,
// similar free-text description, submitted within a short recent window).
// Do NOT block the new submission either way — just flag it, so admin
// staff see a duplicate-likely indicator on the matching request(s) in
// Task 6a's listing (the reserved <span class="pm-row__dupe-flag"> mount
// point Task 6a's row markup already carries) instead of manually
// cross-referencing every new request against every open one by hand.
//
// Enhancement 1 (per the task's own Recommended Enhancements, applied):
// the detection window is a named constant, not a hardcoded magic number,
// so staff/devs can tune it later without hunting through the match logic.
const DUPLICATE_DETECTION_WINDOW_HOURS = 72;

// Enhancement 2 (applied): normalize email before matching — lowercase +
// trim — so "Jane@Example.com " and "jane@example.com" are correctly
// treated as the same visitor rather than missed as a false negative.
$normalizedEmail = strtolower(trim($email));

// Description similarity threshold. Researched via web search (Rule 11):
// PHP's built-in similar_text() with the percent-by-reference form is the
// standard, dependency-free way to score similarity between two strings.
// 60% was chosen as a deliberately loose-but-not-trivial bar: catches a
// visitor re-describing the same pet in different words (the realistic
// case — someone re-submitting because they think the first one didn't
// go through) without flagging two genuinely different requests that
// happen to share a few common words like "friendly" or "medium size".
const DUPLICATE_SIMILARITY_THRESHOLD_PERCENT = 60.0;

// "Unresolved" statuses: anything NOT yet FULFILLED or CLOSED, matching
// the exact status values Task 6b's update-pet-match-request.php writes
// (confirmed uppercase in Task-6b-Completion.md). A freshly-submitted
// request with no status field yet (or the pre-status-update default) is
// treated as unresolved by default, since absence of a resolved status
// means it's still open.
$resolvedStatuses = ['FULFILLED', 'CLOSED'];

$isDuplicateFlagged = false;
$duplicateOfRequestId = null;

// additional_detail is Quill-generated HTML (see pet-match.html's Rich
// Text Editor section) — strip tags before comparing, so formatting
// differences (e.g. added bold) don't distort the similarity score.
$newDescriptionPlain = trim(strip_tags($payload['additional_detail'] ?? ''));

$windowCutoffTimestamp = strtotime('-' . DUPLICATE_DETECTION_WINDOW_HOURS . ' hours');

// Scan this category's existing request folders for this visitor's own
// unresolved requests within the window. Scoped to the same category
// (not all 7) since a duplicate "asking for the same pet" only makes
// sense within one category — a dog request and a bird request from the
// same person are two different, legitimate asks, not a duplicate.
if (is_dir($baseDir)) {
    foreach (scandir($baseDir) as $existingCode) {
        if ($existingCode === '.' || $existingCode === '..') {
            continue;
        }
        $existingFile = $baseDir . '/' . $existingCode . '/request.json';
        if (!is_file($existingFile)) {
            continue;
        }
        $existingData = json_decode(file_get_contents($existingFile), true);
        if (!is_array($existingData)) {
            continue; // skip unreadable/corrupt entries rather than fatally erroring the new submission
        }

        $existingEmail = strtolower(trim($existingData['email'] ?? ''));
        if ($existingEmail !== $normalizedEmail) {
            continue; // not the same visitor
        }

        $existingStatus = strtoupper($existingData['status'] ?? '');
        if (in_array($existingStatus, $resolvedStatuses, true)) {
            continue; // resolved/closed requests are irrelevant to "did they already ask for this"
        }

        $existingCreatedAt = isset($existingData['created_at']) ? strtotime($existingData['created_at']) : false;
        if ($existingCreatedAt === false || $existingCreatedAt < $windowCutoffTimestamp) {
            continue; // outside the detection window
        }

        $existingDescriptionPlain = trim(strip_tags($existingData['additional_detail'] ?? ''));
        if ($existingDescriptionPlain === '' || $newDescriptionPlain === '') {
            continue; // nothing meaningful to compare — skip rather than false-flag on two blank descriptions
        }

        similar_text(
            strtoupper($newDescriptionPlain),
            strtoupper($existingDescriptionPlain),
            $percent
        );
        if ($percent >= DUPLICATE_SIMILARITY_THRESHOLD_PERCENT) {
            $isDuplicateFlagged = true;
            $duplicateOfRequestId = $existingCode; // request codes are the identifier this storage convention uses, not numeric IDs
            break; // first match is enough — flag and stop, no need to keep scanning
        }
    }
}
// TASK-9 END

// ─────────────────────────────────────────────────────────────────────────
// Reference code generation — collision-checked against existing folders,
// following the same "loop until unused" spirit as the rest of the site's
// code-generation helpers.
// ─────────────────────────────────────────────────────────────────────────
function generatePetMatchReferenceCode(string $baseDir): string {
    do {
        $code = 'PM-' . strtoupper(bin2hex(random_bytes(3))) . '-' . random_int(1000, 9999);
    } while (is_dir($baseDir . '/' . $code));
    return $code;
}
$referenceCode = generatePetMatchReferenceCode($baseDir);

// ─────────────────────────────────────────────────────────────────────────
// Write request.json — field list mirrors pet-match.html's real
// collectPayload() fields exactly (verified against the uploaded file),
// matching the response-field contract get-pet-match-requests.php (Task
// 6a) already expects to read, minus the form-hardening fields (website,
// form_rendered_at, turnstile_token) which Task 6a's own doc explicitly
// says are spam-detection internals, not data admins need.
//
// TASK-9: is_duplicate_flag / duplicate_of_request_id are the two fields
// this task adds so Task 6a's reserved pm-row__dupe-flag mount point has
// something to read from when rendering the duplicate indicator.
// ─────────────────────────────────────────────────────────────────────────
$requestData = [
    'reference_code' => $referenceCode,
    'full_name' => $fullName,
    'email' => $email, // stored as originally entered (not normalized) for display; matching used the normalized form separately, above
    'phone' => trim($payload['phone'] ?? ''),
    'category' => $category,
    'pet_name_preference' => trim($payload['pet_name_preference'] ?? ''),
    'gender_preference' => $payload['gender_preference'] ?? '',
    'breed_purebred' => !empty($payload['breed_purebred']),
    'breed_crossbred' => !empty($payload['breed_crossbred']),
    'breed' => $payload['breed'] ?? '',
    'pedigree_certified' => $payload['pedigree_certified'] ?? '',
    'size_preference' => $payload['size_preference'] ?? '',
    'age_min_years' => $payload['age_min_years'] !== '' ? (int) $payload['age_min_years'] : null,
    'age_max_years' => $payload['age_max_years'] !== '' ? (int) $payload['age_max_years'] : null,
    'budget_max' => $payload['budget_max'] !== '' ? (float) $payload['budget_max'] : null,
    'personality_preference' => trim($payload['personality_preference'] ?? ''),
    'compatible_kids' => $payload['compatible_kids'] ?? 'no',
    'compatible_dogs' => $payload['compatible_dogs'] ?? 'no',
    'compatible_cats' => $payload['compatible_cats'] ?? 'no',
    'compatible_other_pets' => $payload['compatible_other_pets'] ?? 'no',
    'visitor_location' => trim($payload['visitor_location'] ?? ''),
    'additional_detail' => $payload['additional_detail'] ?? '', // stored as Quill HTML, matching what the form sends
    'reference_image_token' => $payload['reference_image_token'] ?? null,
    'status' => 'new', // pre-resolution default; Task 6b's update-pet-match-request.php overwrites this to FULFILLED or CLOSED later
    'is_duplicate_flag' => $isDuplicateFlagged, // TASK-9
    'duplicate_of_request_id' => $duplicateOfRequestId, // TASK-9 — null when not flagged
    'created_at' => date('c'), // ISO 8601, matching update-pet-match-request.php's own documented date('c') convention for its timestamp fields
];

$requestDir = $baseDir . '/' . $referenceCode;
if (!is_dir($requestDir) && !mkdir($requestDir, 0755, true) && !is_dir($requestDir)) {
    http_response_code(500);
    echo json_encode(['message' => 'Could not save your request — please try again.']);
    exit;
}
file_put_contents($requestDir . '/request.json', json_encode($requestData, JSON_PRETTY_PRINT));

http_response_code(200);
echo json_encode(['reference_code' => $referenceCode]);
