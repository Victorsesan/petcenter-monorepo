<?php
/**
 * check-pet-match-status.php
 *
 * TASK-7a — New file, part of the notification-icon STUB. This is
 * deliberately a public, unauthenticated, email-in-URL lookup — matching
 * the stub's own documented trust level exactly (a plain email string a
 * visitor's browser remembered, not a verified session). It must not be
 * upgraded to trust this email any more than the rest of the stub does.
 *
 * CORRECTED against the real update-pet-match-request.php (Task 6b),
 * supplied after this file's first draft, and UPDATED again once that
 * file gained an optional matched_pet_id field (added alongside this
 * task, since no admin-side input for a structured pet link existed
 * before — see the TASK-7a comment in update-pet-match-request.php and
 * admin-dashboard.html's pmFulfillModal for the full reasoning). Current
 * behavior:
 *   1. Storage is nested by category — /adoptions/pet-match-requests/
 *      {category}/{request_code}/request.json. This file walks each of
 *      the seven valid categories.
 *   2. A FULFILLED request may or may not have a matched_pet_id (it's
 *      optional — staff often won't have a real pet.json to link to yet,
 *      per this feature's own premise). When present AND it resolves to
 *      a real pet.json, this endpoint returns a real pet_name + pet_url.
 *      When absent or it doesn't resolve, this falls back to returning
 *      just the free-text fulfilled_pet_note, exactly as before.
 *
 * LIVE LOCATION (hand-in review pass): this file deploys to
 * /adoptions/check-pet-match-status.php — a sibling of
 * /adoptions/pets/ and /adoptions/pet-match-requests/, NOT the bare
 * site root. Neither adoption-website-project-summary.md nor
 * ADMIN_DASHBOARD_PROJECT_SUMMARY.md documents any existing PHP file
 * living directly at the site root, so this placement was reconsidered
 * during the hand-in review (the very first draft had assumed bare
 * site-root, which would have been a genuinely new, undocumented
 * pattern) in favor of sitting next to the exact data this file reads —
 * consistent with how adoption-application.php sits inside /apply/
 * alongside its own data, per Rule 7. This also happened to surface and
 * fix a real bug: the first draft used __DIR__ . '/pets/' for the pet.json
 * lookup, which only accidentally "worked" by coincidence of site-root
 * placement; moving here forced the fix to the correct, always-right
 * /adoptions/pets/ path below rather than carrying the ambiguity forward.
 * cats.html's own fetch call (see that file) must point to
 * /adoptions/check-pet-match-status.php, not /check-pet-match-status.php —
 * also corrected as part of this pass.
 *
 * GET /adoptions/check-pet-match-status.php?email=someone@example.com
 * -> { "matches": [ { "request_code": "PM-00000-0000", "category": "dogs", "fulfilled_pet_note": "...", "pet_name": "...", "pet_url": "...", "fulfilled_at": "2026-..." }, ... ] }
 * (pet_name / pet_url are only present when matched_pet_id resolved to a real pet)
 *
 * Only returns requests an admin has actually set to FULFILLED.
 */

header('Content-Type: application/json');

// TASK-7a: file-backed per-IP rate limiting prevents this public email
// lookup from becoming an enumeration probe. It intentionally does not load
// the chat MySQL configuration: this endpoint reads the JSON data store only.
$limitDir = __DIR__ . '/pet-match-status-rate-limits';
if (!is_dir($limitDir)) @mkdir($limitDir, 0750, true);
$limitPath = $limitDir . '/' . hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown') . '.json';
$limitHandle = @fopen($limitPath, 'c+');
if ($limitHandle === false) {
    http_response_code(503);
    echo json_encode(['matches' => []]);
    exit;
}
flock($limitHandle, LOCK_EX);
$limit = json_decode(stream_get_contents($limitHandle) ?: '', true);
if (!is_array($limit) || (int)($limit['window_start'] ?? 0) < time() - 600) $limit = ['window_start' => time(), 'attempts' => 0];
$limit['attempts']++;
ftruncate($limitHandle, 0); rewind($limitHandle); fwrite($limitHandle, json_encode($limit)); fflush($limitHandle);
$limitExceeded = $limit['attempts'] > 20;
flock($limitHandle, LOCK_UN); fclose($limitHandle);
if ($limitExceeded) {
    http_response_code(429);
    echo json_encode(['matches' => [], 'message' => 'Too many requests, please try again shortly.']);
    exit;
}

$email = isset($_GET['email']) ? trim($_GET['email']) : '';

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // Same generic-failure principle used elsewhere in this project
    // (Section 15.3 / Task 7b's Section 8) — never reveal *why* nothing
    // came back, just return an empty result.
    echo json_encode(['matches' => []]);
    exit;
}

// TASK-7a: same category list update-pet-match-request.php itself
// validates against — copied verbatim so this endpoint only ever looks in
// directories that can actually exist.
$validCategories = ['dogs', 'cats', 'birds', 'reptiles', 'small-mammals', 'livestocks', 'aquariums'];

// TASK-7a FIX (hand-in review pass): was __DIR__ . '/adoptions/pet-match-requests/'
// — correct only if this file sat at the site root, which it no longer
// does (see the LIVE LOCATION note above). This file now sits inside
// /adoptions/ itself, so the sibling folder is reached directly.
$baseDir = __DIR__ . '/pet-match-requests/';
$matches = [];

foreach ($validCategories as $category) {
    $categoryDir = $baseDir . $category . '/';
    if (!is_dir($categoryDir)) continue;

    foreach (scandir($categoryDir) as $requestCode) {
        if ($requestCode === '.' || $requestCode === '..') continue;
        $requestFile = $categoryDir . $requestCode . '/request.json';
        if (!is_file($requestFile)) continue;

        $request = json_decode(file_get_contents($requestFile), true);
        if (!is_array($request)) continue;

        // Only this email's own requests — case-insensitive compare, since
        // the visitor form doesn't normalize case on entry either.
        if (strcasecmp($request['email'] ?? '', $email) !== 0) continue;

        // Only requests an admin has actually marked FULFILLED.
        if (($request['status'] ?? '') !== 'FULFILLED') continue;

        $match = [
            'request_code' => $requestCode,
            'category' => $category,
            'fulfilled_pet_note' => $request['fulfilled_pet_note'] ?? '',
            'fulfilled_at' => $request['fulfilled_at'] ?? null
        ];

        // TASK-7a: resolve matched_pet_id into a real pet_name/pet_url
        // when present and it actually points at a real pet.json — this
        // field is optional on the request (see update-pet-match-request.php),
        // so its absence or a stale/typo'd ID must never break this
        // endpoint; it just falls back to the free-text note alone.
        if (!empty($request['matched_pet_id'])) {
            // TASK-7a FIX (hand-in review pass): was __DIR__ . '/pets/' —
            // real pet data lives at /adoptions/pets/{category}/, which is
            // now simply __DIR__ . '/pets/' correctly, since this file
            // itself sits inside /adoptions/ (see LIVE LOCATION note at
            // the top of this file). Confirmed against
            // adoption-website-project-summary.md's explicit warning that
            // /adoptions/pets/ (data) is NOT the same folder as the
            // page-serving /pets/ near the site root.
            $petFile = __DIR__ . '/pets/' . $category . '/' . $request['matched_pet_id'] . '/pet.json';
            if (is_file($petFile)) {
                $pet = json_decode(file_get_contents($petFile), true);
                if (is_array($pet)) {
                    $match['pet_name'] = $pet['name'] ?? 'A pet';
                    $match['pet_url'] = '/pets/detail/?pet_id=' . urlencode($request['matched_pet_id']) . '&category=' . urlencode($category);
                }
            }
        }

        $matches[] = $match;
    }
}

echo json_encode(['matches' => $matches]);
