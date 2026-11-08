<?php
/**
 * get-pet-match-requests.php
 *
 * TASK-6a (Project 5: Pet-Match Request feature, Admin Listing & Filtering
 * Structure, LISTING-CORE zone).
 *
 * New file — no prior version exists. Built as a direct sibling of
 * get-applications.php (see ADMIN_DASHBOARD_PROJECT_SUMMARY.md Section 8),
 * reusing that endpoint's exact shape rather than inventing a new one, per
 * Warning & Instructions Rule 7 ("never recreate a feature from scratch —
 * reuse the exact existing pattern"):
 *   - Same token-validation style as the rest of the pet-management family
 *     (get-applications.php, get-pets.php): read ADMIN_TOKEN from .env,
 *     compare against the incoming token, accepting it from either the
 *     POST body or the X-Admin-Token header (the same dual-acceptance
 *     pattern documented for chatRequireAdmin() / chatIsAdminAuthenticated(),
 *     so both plain form POSTs and fetch()-with-header calls work
 *     identically here too, even though this endpoint isn't a chat
 *     endpoint and doesn't call that shared chat helper directly).
 *   - Same one-folder-per-submission storage shape as
 *     /adoptions/applications/{code}/{application.json,metadata.json},
 *     adapted to Pet Match Requests: /adoptions/pet-match-requests/
 *     {category}/{request_code}/request.json. Categorized by folder (not
 *     just filtered in code) so a future task that needs to browse the
 *     filesystem directly (backups, migrations, manual review) can do so
 *     the same way an admin already can for applications.
 *   - Field names returned match exactly what Task-5-Completion.md and
 *     Task-5b-Completion.md documented as the submitted payload from
 *     pet-match.html's form (full_name, email, phone, visitor_location,
 *     category, breed, age_preference, size_preference, gender_preference,
 *     budget_max, personality_preference, compatible_kids, compatible_dogs,
 *     compatible_cats, compatible_other_pets, additional_detail,
 *     reference_image_token, image_terms_agree) plus a request_code and
 *     submitted_at that /pet-match-submit.php is expected to assign at
 *     write time (that endpoint is documented but not yet built — see
 *     Task-5b-Completion.md's "Backend expectations" section; this file
 *     only reads whatever it eventually writes, in this same shape).
 *
 * This endpoint intentionally does NOT implement update/delete — Task 6a's
 * own scope is the listing/filtering structure only ("do NOT build the
 * detail modal or admin actions — that's 6b"). A future
 * update-pet-match-request.php (Task 6b's likely counterpart to
 * update-adoption.php) is out of scope here.
 */

header('Content-Type: application/json');

// ---------------------------------------------------------------------
// Token validation — mirrors admin-auth.php / security.php's documented
// pattern exactly: read ADMIN_TOKEN from .env, compare against whatever
// the request sent, accepting either $_POST['token'] or the
// X-Admin-Token header.
// ---------------------------------------------------------------------
function pmGetAdminToken(): ?string {
    $envPath = __DIR__ . '/.env';
    if (!file_exists($envPath)) {
        return null;
    }
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), 'ADMIN_TOKEN=') === 0) {
            return substr(trim($line), strlen('ADMIN_TOKEN='));
        }
    }
    return null;
}

$storedToken = pmGetAdminToken();
if ($storedToken === null) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '.env file missing or ADMIN_TOKEN not set']);
    exit;
}

$incomingToken = $_POST['token'] ?? ($_SERVER['HTTP_X_ADMIN_TOKEN'] ?? null);
if ($incomingToken === null || !hash_equals($storedToken, $incomingToken)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    exit;
}

// ---------------------------------------------------------------------
// Category input — required, since the admin panel's own UI (Task 6a's
// petMatchPanel) always scopes a fetch to one selected category at a
// time, exactly like get-pets.php requires a category for
// loadPetsForCategory(). No category means no folder to scan, so this
// returns an empty result rather than guessing or scanning every
// category folder at once.
// ---------------------------------------------------------------------
$validCategories = ['dogs', 'cats', 'birds', 'reptiles', 'small-mammals', 'livestocks', 'aquariums'];
$category = $_POST['category'] ?? '';

if (!in_array($category, $validCategories, true)) {
    echo json_encode(['success' => true, 'requests' => []]);
    exit;
}

// ---------------------------------------------------------------------
// Scan /adoptions/pet-match-requests/{category}/ — one subfolder per
// submission, each holding a single request.json, mirroring
// /adoptions/applications/{code}/application.json.
// ---------------------------------------------------------------------
// BUGFIX-MANUS-04-1-START — /admin/ and /adoptions/ are sibling folders;
// the previous path incorrectly resolved to /admin/adoptions/.
$baseDir = __DIR__ . '/../adoptions/pet-match-requests/' . $category;
// BUGFIX-MANUS-04-1-END
$requests = [];

if (is_dir($baseDir)) {
    $entries = scandir($baseDir);
    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        $requestFile = $baseDir . '/' . $entry . '/request.json';
        if (!file_exists($requestFile)) {
            continue;
        }
        $raw = file_get_contents($requestFile);
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            continue;
        }
        // Never return the visitor's raw hardening fields (honeypot value,
        // form_rendered_at, turnstile_token) to the admin UI — those are
        // spam-detection internals for /pet-match-submit.php, not data an
        // admin needs to review a request. Explicitly building the
        // response object (rather than returning $data as-is) keeps this
        // endpoint from ever leaking a field that wasn't meant for it,
        // even if a future version of the submit endpoint stores those
        // hardening fields alongside the real submission for its own
        // audit purposes.
        //
        // FIX (post-Task-9 bug report): this allowlist originally assumed
        // field names from Task-5-Completion.md/Task-5b-Completion.md's
        // documented payload (age_preference, reference_image_url,
        // request_code, submitted_at). pet-match.html was revised after
        // those completion docs were written (wizard flow + real species
        // taxonomy pass, per Task 5's own completion notes on that
        // revision), and the version actually built and confirmed live —
        // pet-match.html plus the real pet-match-submit.php (Task 9) —
        // uses a different, larger field set. Corrected below against
        // pet-match-submit.php's actual request.json write block, not
        // against the older completion-doc description. Old field names
        // are commented alongside their replacements so the mapping is
        // traceable rather than silently rewritten.
        $requests[] = [
            'request_code'              => $data['reference_code']           ?? $entry, // was 'request_code'
            'full_name'                 => $data['full_name']                ?? '',
            'email'                     => $data['email']                    ?? '',
            'phone'                     => $data['phone']                    ?? '',
            'visitor_location'          => $data['visitor_location']         ?? '',
            'category'                  => $data['category']                 ?? $category,
            'pet_name_preference'       => $data['pet_name_preference']      ?? '',
            'breed'                     => $data['breed']                    ?? '',
            'breed_purebred'            => $data['breed_purebred']           ?? false,
            'breed_crossbred'           => $data['breed_crossbred']          ?? false,
            'pedigree_certified'        => $data['pedigree_certified']       ?? '',
            'age_min_years'             => $data['age_min_years']            ?? null, // was single 'age_preference'
            'age_max_years'             => $data['age_max_years']            ?? null, // was single 'age_preference'
            'size_preference'           => $data['size_preference']          ?? '',
            'gender_preference'         => $data['gender_preference']        ?? '',
            'budget_max'                => $data['budget_max']               ?? null,
            'personality_preference'    => $data['personality_preference']   ?? '',
            'compatible_kids'           => $data['compatible_kids']          ?? 'unknown',
            'compatible_dogs'           => $data['compatible_dogs']          ?? 'unknown',
            'compatible_cats'           => $data['compatible_cats']          ?? 'unknown',
            'compatible_other_pets'     => $data['compatible_other_pets']    ?? 'unknown',
            'additional_detail'         => $data['additional_detail']        ?? '', // stored as Quill HTML by the submit endpoint
            'reference_image_token'     => $data['reference_image_token']    ?? null, // was 'reference_image_url' — submit endpoint stores the upload token, not a resolved URL
            'status'                    => $data['status']                   ?? 'new',
            'submitted_at'              => $data['created_at']               ?? '', // was 'submitted_at'
            // TASK-9 fields (unchanged — already correct, added after this fix)
            'is_duplicate_flag'         => $data['is_duplicate_flag']        ?? false,
            'duplicate_of_request_id'   => $data['duplicate_of_request_id']  ?? null,
        ];
    }
}

echo json_encode(['success' => true, 'requests' => $requests]);
