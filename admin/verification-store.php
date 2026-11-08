<?php
/**
 * verification-store.php
 *
 * TASK-B2 — new file, no existing code to merge (per Directive 8a's "New
 * files created from scratch" exception).
 *
 * Two small shared helpers used by both get-verification-requests.php and
 * update-verification.php:
 *  1. verifRequireAdmin() — admin-token check. Mirrors the documented
 *     dashboard security pattern (ADMIN_DASHBOARD_PROJECT_SUMMARY.md,
 *     Security section): token via .env's ADMIN_TOKEN, accepted from
 *     either the X-Admin-Token header or a `token` POST field, same as
 *     chatRequireAdmin()'s documented behavior. This task's "Files to
 *     Request From CEO" line did not include chat-config.php itself, so
 *     rather than guess at that file's exact internals, this reimplements
 *     the same documented contract as its own small helper — flagged here,
 *     not silently assumed identical. If chat-config.php's real
 *     chatRequireAdmin() should be required directly instead once it's
 *     available, that's a one-line swap at the integration/reconciliation
 *     pass, not a rebuild.
 *  2. verifLoadRequests() / verifSaveRequests() — read/write verification
 *     request records.
 *
 * STORAGE CONVENTION (confirmed against ADMIN_DASHBOARD_PROJECT_SUMMARY.md's
 * own "File Structure on Server" section, not just adoption-application.php's
 * code): the real site root is `/public_html/adopt/`, with `admin/` (where
 * this file lives) and `adoptions/` as SIBLING folders directly under it.
 * `adoptions/applications/{code}/application.json` sits under `adoptions/`,
 * a sibling of `admin/` — not nested inside it. This store mirrors that same
 * sibling-folder convention: `verifications/requests/{username}/request.json`
 * at site root, a sibling of both `admin/` and `adoptions/`, one level ABOVE
 * this file's own `admin/` directory (see VERIF_REQUESTS_BASE below — this is
 * now a confirmed, structure-verified path, not a guess).
 *
 * *** NEW FOLDER REQUIRED — NOT IN EITHER PROJECT SUMMARY DOC ***
 * `/verifications/` does not exist anywhere in ADMIN_DASHBOARD_PROJECT_SUMMARY.md's
 * "File Structure on Server" section, nor in adoption-website-project-summary.md.
 * It must be created at site root (`/public_html/adopt/verifications/`),
 * sibling to `admin/`, `adoptions/`, `contact-us/`, `pets/`. This code creates
 * `verifications/requests/{username}/` automatically via mkdir() the first
 * time a record is written, so no manual pre-creation is strictly required —
 * but the integrator should know this folder is new and not an oversight.
 */

// TASK-B2 START
// Loads ADMIN_TOKEN the same way the rest of the dashboard's backend is
// documented to (a .env file, never hardcoded, never committed) — this
// small parser avoids requiring a full env-loading library for one value.
function verifLoadAdminToken() {
    static $token = null;
    if ($token !== null) return $token;

    $envPath = __DIR__ . '/.env';
    $token = '';
    if (file_exists($envPath)) {
        foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (strpos(trim($line), 'ADMIN_TOKEN=') === 0) {
                $token = trim(substr(trim($line), strlen('ADMIN_TOKEN=')));
                break;
            }
        }
    }
    return $token;
}

// Accepts the token via the X-Admin-Token header OR a `token` POST field,
// matching chatRequireAdmin()'s documented dual-source behavior so the
// dashboard's existing getAdminToken()/FormData('token') pattern works
// here unchanged.
function verifRequireAdmin() {
    $expected = verifLoadAdminToken();
    if ($expected === '') {
        error_log('verification-store.php: ADMIN_TOKEN not configured in .env');
        return false;
    }
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $provided = $headers['X-Admin-Token'] ?? $headers['x-admin-token'] ?? ($_POST['token'] ?? '');
    return is_string($provided) && hash_equals($expected, $provided);
}
// TASK-B2 END

// TASK-B2 START
// Site-root-relative, matching the confirmed sibling-folder convention above:
// this file lives at admin/verification-store.php, so dirname(__DIR__) steps
// up to site root (/public_html/adopt/), then into the new verifications/
// folder — exactly how adoption-application.php (at apply/adoption-application.php)
// reaches dirname(__DIR__) . '/adoptions/applications/'.
if (!defined('VERIF_REQUESTS_BASE')) {
    define('VERIF_REQUESTS_BASE', dirname(__DIR__) . '/verifications/requests/');
}

// Folder name per request — same sanitize-then-use approach as
// adoption-application.php's sanitize_folder($email) for its per-applicant
// upload folder, adapted to a username instead of an email.
function verifFolderName($username) {
    $u = strtolower(trim((string)$username));
    return preg_replace('/[^a-z0-9_\-]/', '_', $u);
}

// Reads every request.json across all request folders. Missing/corrupt
// individual files are skipped (logged), not fatal to the whole list — same
// fail-soft, log-loudly shape as the rest of this task's endpoints.
function verifLoadRequests() {
    if (!is_dir(VERIF_REQUESTS_BASE)) return [];
    $out = [];
    foreach (glob(VERIF_REQUESTS_BASE . '*', GLOB_ONLYDIR) ?: [] as $dir) {
        $file = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . 'request.json';
        if (!file_exists($file)) continue;
        $raw = @file_get_contents($file);
        if ($raw === false) {
            error_log('verification-store.php: could not read ' . $file);
            continue;
        }
        $data = json_decode($raw, true);
        if (is_array($data)) $out[] = $data;
    }
    return $out;
}

// Writes exactly ONE request's folder. This is the function update-verification.php
// should call — NOT verifSaveRequests() below, which was an earlier version of this
// store that rewrote every folder on every single approve/reject. Since
// verifLoadRequests() reads every folder fresh, a save between two concurrent
// admin actions that blindly rewrote the whole list would silently overwrite
// an unrelated request's change with stale data (lost update) — this exists
// specifically to avoid that: every write here touches only the one record it
// actually changed, matching adoption-application.php's own pattern of writing
// exactly its own submission's file, never any other submission's.
function verifSaveOneRequest($req) {
    $username = $req['username'] ?? '';
    if ($username === '') {
        error_log('verification-store.php: refused to save a request with no username');
        return false;
    }
    $dir = VERIF_REQUESTS_BASE . verifFolderName($username) . '/';
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
        error_log('verification-store.php: could not create ' . $dir);
        return false;
    }
    $ok = @file_put_contents(
        $dir . 'request.json',
        json_encode($req, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        LOCK_EX // same lock-on-write discipline as adoption-application.php's log append
    );
    if ($ok === false) {
        error_log('verification-store.php: could not write ' . $dir . 'request.json');
        return false;
    }
    return true;
}

// Kept only for a brand-new request that doesn't exist as a folder yet (an
// "add request" flow, if the CEO confirms one is needed per the open
// question in the completion file) — writes every record it's given, one
// folder per record. NOT used by update-verification.php's approve/reject
// path anymore; see verifSaveOneRequest() above for why.
function verifSaveRequests($requests) {
    $ok = true;
    foreach ($requests as $req) {
        if (!verifSaveOneRequest($req)) $ok = false;
    }
    return $ok;
}
// TASK-B2 END
