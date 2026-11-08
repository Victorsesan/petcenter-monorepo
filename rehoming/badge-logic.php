<?php
/**
 * badge-logic.php
 * TASK-B3 — Badge & reputation system: core tiers (earn/award logic only — see Task B4 for display)
 *
 * Pure calculation/data-access library, no HTTP entry point of its own.
 * Included by:
 *   - badge-calculation-cron.php (this task) — the daily scheduled recalculation pass
 *   - (future) B2's admin-approval endpoint — should call recalculateAllBadges() immediately
 *     after flipping verification_status to 'verified', so the Verified badge doesn't wait
 *     for the next cron run. That wiring itself belongs to B2, not this task (Scope Lock) —
 *     this file just needs to already exist and be safe to call from anywhere by the time B2
 *     reconciles against it.
 *
 * Threshold constants live here, in one place, so they are easy to find and adjust later
 * without re-reading the whole task (per B3's "Completion file must show" requirement).
 */

// TASK-B3 START: threshold constants
/**
 * Minimum verified-rehome completions (Task D1's placement_complete contract) to reach
 * the "Active" activity tier. Starting value — flagged as adjustable, not a hardcoded
 * magic number scattered through the logic below.
 */
define('B3_ACTIVE_TIER_MIN_PLACEMENTS', 1);

/**
 * Minimum verified-rehome completions to reach the "Trusted" activity tier.
 * Both this AND B3_TRUSTED_TIER_MIN_RESPONSE_RATE must be met — see calculateActivityTier().
 */
define('B3_TRUSTED_TIER_MIN_PLACEMENTS', 5);

/**
 * Minimum response rate (0.0-1.0) within the reply window below, required for "Trusted".
 * 0.80 = 80%, per the task spec.
 */
define('B3_TRUSTED_TIER_MIN_RESPONSE_RATE', 0.80);

/**
 * The reply-time window (in hours) that counts as "responded" for the response-rate
 * calculation. Spec'd against the current chat/messaging model per B4's note in the task
 * tracker — this project's own merchant<->visitor messaging system doesn't exist as a built
 * file yet (Task C5 may reshape its data structure), so calculateResponseRate() below is a
 * stub against this contract, reconciled once that messaging system's real file exists.
 */
define('B3_RESPONSE_RATE_WINDOW_HOURS', 48);

/**
 * Where per-merchant profile.json files live, one folder per merchant — mirrors the
 * platform's existing flat-file JSON convention (see adoption-website-project-summary.md
 * Section 1, e.g. /adoptions/pets/{category}/{pet_id}/pet.json).
 */
define('B3_MERCHANTS_BASE_DIR', __DIR__ . '/merchants');
// TASK-B3 END: threshold constants


// TASK-B3 START: activity tier (Badge 1)
/**
 * calculateActivityTier()
 * Badge 1 — server-derived activity tier. Never manually assigned.
 *
 * @param int        $completedPlacements  Count of this merchant's placement_complete
 *                                          events (Task D1's contract — stubbed until D1's
 *                                          real implementation exists; see calculateResponseRate()
 *                                          below for how this file sources it today).
 * @param float|null $responseRate         0.0-1.0, or null if there is not yet enough message
 *                                          history to compute one (a brand-new merchant with no
 *                                          inbound messages should not be penalized as if they
 *                                          had a 0% response rate).
 * @return string one of 'new_merchant' | 'active' | 'trusted'
 */
function calculateActivityTier($completedPlacements, $responseRate) {
    // Trusted requires BOTH the placement count AND the response-rate threshold —
    // a merchant with 10 completed placements but a poor response rate is not Trusted.
    $meetsTrustedPlacements = $completedPlacements >= B3_TRUSTED_TIER_MIN_PLACEMENTS;
    $meetsTrustedResponseRate = ($responseRate !== null) && ($responseRate >= B3_TRUSTED_TIER_MIN_RESPONSE_RATE);

    if ($meetsTrustedPlacements && $meetsTrustedResponseRate) {
        return 'trusted';
    }

    if ($completedPlacements >= B3_ACTIVE_TIER_MIN_PLACEMENTS) {
        return 'active';
    }

    // Default state — 0 completed placements.
    return 'new_merchant';
}
// TASK-B3 END: activity tier (Badge 1)


// TASK-B3 START: verified badge (Badge 2)
/**
 * calculateVerifiedBadge()
 * Badge 2 — Verified (identity). Binary, no tiers. Set directly by Task B2's admin-approval
 * action (verification_status === 'verified') — this function does not independently decide
 * verification, it only exposes the badge state derived from that field, per the
 * SHARED CONTRACTS section of the task tracker ("B3 builds its 'Verified' badge trigger
 * logic against this contract... doesn't need B1/B2 to have actually run a real
 * verification to write and test 'if verification_status equals verified, award the badge'").
 *
 * @param array $merchantProfile decoded profile.json
 * @return bool
 */
function calculateVerifiedBadge($merchantProfile) {
    $status = isset($merchantProfile['verification_status']) ? $merchantProfile['verification_status'] : 'pending';
    return $status === 'verified';
}
// TASK-B3 END: verified badge (Badge 2)


// TASK-B3 START: charity/rescue badge (Badge 3)
/**
 * calculateCharityBadge()
 * Badge 3 — Registered Charity/Rescue. Awarded once the merchant's charity_request
 * (registration number + country, submitted at account setup or in settings) has been
 * manually looked up and approved by an admin in the same review queue B2 uses for
 * identity verification (per the task spec — "add as a second request type, not a
 * separate dashboard section"). The queue UI itself is out of this task's file zone
 * (merchant profile data model + badge-calculation logic only) — see this task's
 * completion file, "Bugs Found Outside Task Scope" / integration note.
 *
 * @param array $merchantProfile decoded profile.json
 * @return bool
 */
function calculateCharityBadge($merchantProfile) {
    $charityRequest = isset($merchantProfile['charity_request']) ? $merchantProfile['charity_request'] : null;
    if (!$charityRequest || !isset($charityRequest['status'])) {
        return false;
    }
    return $charityRequest['status'] === 'approved';
}
// TASK-B3 END: charity/rescue badge (Badge 3)


// TASK-B3 START: takeback commitment badge (Badge 4)
/**
 * calculateTakebackBadge()
 * Badge 4 — Takeback Commitment. Self-declared opt-in toggle in merchant settings, no
 * verification required (a false claim is a reportable ToS violation, not something this
 * system proactively checks). The settings toggle UI itself is out of this task's file
 * zone — this function only reads the stored boolean.
 *
 * @param array $merchantProfile decoded profile.json
 * @return bool
 */
function calculateTakebackBadge($merchantProfile) {
    return !empty($merchantProfile['takeback_commitment']);
}
// TASK-B3 END: takeback commitment badge (Badge 4)


// TASK-B3 START: response rate stub (feeds Badge 1)
/**
 * calculateResponseRate()
 * Stub against this project's own merchant<->visitor messaging system, which does not yet
 * exist as a real file (Task C5's endpoint hardening may reshape its data structure — see
 * the SHARED CONTRACTS / Universal Requirements notes in the task tracker). Until that real
 * message store exists, this reads a manually-set `response_rate` field directly off the
 * merchant's own profile (useful for testing this task's logic in isolation, per the
 * Boundary-case testing requirement) rather than inventing a fake message log to compute
 * against. Whoever reconciles this against the real messaging system's file at the
 * integration checkpoint should replace this function's body — the signature and the
 * B3_RESPONSE_RATE_WINDOW_HOURS constant it should honor are the part of the contract that
 * should not change.
 *
 * @param array $merchantProfile decoded profile.json
 * @return float|null 0.0-1.0, or null if there isn't enough data yet to compute one
 */
function calculateResponseRate($merchantProfile) {
    if (isset($merchantProfile['response_rate']) && $merchantProfile['response_rate'] !== null) {
        return (float) $merchantProfile['response_rate'];
    }
    // No message history yet (brand-new merchant) — null, not 0, so a new merchant isn't
    // treated as if they had already failed to respond to anything.
    return null;
}
// TASK-B3 END: response rate stub (feeds Badge 1)


// TASK-B3 START: placement count stub (feeds Badge 1)
/**
 * getCompletedPlacementsCount()
 * Stub against Task D1's placement lifecycle contract (`status` reaching
 * 'placement_complete'). D1's real implementation does not exist as a file yet, so this
 * reads a merchant-maintained running count off their own profile instead of scanning a
 * listings file that isn't built. Reconcile against D1's real completed-placement records
 * at that task's integration checkpoint, per SHARED CONTRACTS.
 *
 * @param array $merchantProfile decoded profile.json
 * @return int
 */
function getCompletedPlacementsCount($merchantProfile) {
    return isset($merchantProfile['completed_placements_count'])
        ? (int) $merchantProfile['completed_placements_count']
        : 0;
}
// TASK-B3 END: placement count stub (feeds Badge 1)


// TASK-B3 START: single-merchant recalculation + safe read/write helpers
/**
 * IMPORTANT — found during self re-verification, flagged here so it isn't missed later:
 * `completed_placements_count` and `response_rate` (read by getCompletedPlacementsCount()
 * and calculateResponseRate() above) are stub sources for contracts owned by Task D1
 * (placement completions) and this project's own messaging system, respectively. They
 * must only ever be written by server-side/admin-triggered logic (D1's real placement
 * flow once it exists, and the real message-reply-time calculation) — NEVER exposed as a
 * merchant-editable field on any settings form. If a future merchant-facing profile-edit
 * endpoint is built generically (e.g. "save whatever fields are in this JSON body"), these
 * two field names must be explicitly excluded from what a merchant can write directly, or
 * a merchant could self-report fake completions/response rates to fraudulently reach the
 * Trusted tier. This is a design note for whoever builds that endpoint, not a bug in this
 * task's own files — flagging it here because it lives right next to the fields in
 * question, where it's most likely to be seen before a mistake ships.
 */

/**
 * recalculateAllBadges()
 * Runs all four badge calculations for one merchant profile and returns the updated
 * profile array (does not write to disk — callers decide when/whether to persist, so
 * this same function can be used both by the nightly cron below and, later, by an
 * immediate-trigger hook such as B2's approve action).
 *
 * Note on `activity_tier` appearing twice in the returned profile: once as its own
 * top-level field (kept for any code that only needs the tier, not the full badges
 * object) and again inside `badges.activity_tier` (what B4's display layer reads,
 * alongside the other three badges, as one object). This function is the ONLY place
 * that writes either of them — anything that sets `$profile['activity_tier']` directly
 * outside of this function will desync the two copies. Always go through
 * recalculateAllBadges() rather than setting either field by hand.
 *
 * @param array $merchantProfile decoded profile.json
 * @return array the same profile, with `badges` and `activity_tier` recomputed
 */
function recalculateAllBadges($merchantProfile) {
    $completedPlacements = getCompletedPlacementsCount($merchantProfile);
    $responseRate = calculateResponseRate($merchantProfile);

    $merchantProfile['activity_tier'] = calculateActivityTier($completedPlacements, $responseRate);
    $merchantProfile['badges'] = array(
        'verified' => calculateVerifiedBadge($merchantProfile),
        'activity_tier' => $merchantProfile['activity_tier'],
        'charity' => calculateCharityBadge($merchantProfile),
        'takeback_commitment' => calculateTakebackBadge($merchantProfile),
    );
    $merchantProfile['last_badge_calculated_at'] = gmdate('Y-m-d\TH:i:s\Z');

    return $merchantProfile;
}

/**
 * readMerchantProfile()
 * Reads and decodes one merchant's profile.json.
 *
 * @param string $merchantId
 * @return array|null null if the file doesn't exist or fails to decode
 */
function readMerchantProfile($merchantId) {
    $path = B3_MERCHANTS_BASE_DIR . '/' . $merchantId . '/profile.json';
    if (!file_exists($path)) {
        return null;
    }
    $raw = file_get_contents($path);
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : null;
}

/**
 * writeMerchantProfile()
 * Writes a merchant's profile.json back to disk. Uses LOCK_EX, matching the platform's
 * existing full-replace-on-save convention (see ADMIN_DASHBOARD_PROJECT_SUMMARY.md's
 * chat-bot-profile.json documentation) rather than a partial/append write.
 *
 * @param string $merchantId
 * @param array  $merchantProfile
 * @return bool true on success
 */
function writeMerchantProfile($merchantId, $merchantProfile) {
    $path = B3_MERCHANTS_BASE_DIR . '/' . $merchantId . '/profile.json';
    $json = json_encode($merchantProfile, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return false;
    }
    $bytesWritten = file_put_contents($path, $json, LOCK_EX);
    return $bytesWritten !== false;
}
// TASK-B3 END: single-merchant recalculation + safe read/write helpers


// TASK-B3 START: admin-token guard for the cron script's HTTP invocation path
/**
 * b3IsAdminAuthenticated()
 * Reconciled against the real source (admin-auth.php and chat-config.php, both now
 * provided — previously this task only had their documented behavior, not the literal
 * code, and said so explicitly). This function's `.env`-parsing and comparison lines are
 * copied verbatim from chat-config.php's `chatIsAdminAuthenticated()`, per Rule 7 (use the
 * exact same code, don't write a close approximation):
 *
 *     preg_match('/ADMIN_TOKEN=(.+)/', file_get_contents($env_file), $m);
 *     $admin_token = isset($m[1]) ? trim($m[1]) : null;
 *     ...
 *     return $provided && hash_equals($admin_token, $provided);
 *
 * `chatIsAdminAuthenticated()` itself was deliberately NOT required/called directly —
 * `chat-config.php` is a heavy shared-foundation file (defines real-looking live MySQL
 * credentials, lazy-connects to a database most of this function's callers never touch,
 * and has require-time side effects: auto-creating `chat-media/`/`chat-uploads-temp/` and
 * writing a `.htaccess` into the latter). Requiring that whole file just to borrow one
 * auth check would couple this independent merchant-badge system to the entire,
 * unrelated live-chat subsystem's configuration and filesystem side effects — a real cost
 * for no benefit, since the auth check itself has no actual dependency on any of that.
 * Copying the exact three lines above avoids that coupling while still using the exact
 * same logic, not a reinvented one.
 *
 * One deliberate, narrow difference from the literal function, flagged here rather than
 * silently added: the real `chatIsAdminAuthenticated()` only checks `$_POST['token']` and
 * the `X-Admin-Token` header — it has no `$_GET` path, because every existing admin
 * endpoint that calls it is hit via POST from the dashboard's own JS. This task's cron
 * script can also be triggered by Hostinger's hPanel "Cron Job calls a URL" option, which
 * issues a plain GET with no custom body or header support — a case the real function was
 * never written to handle, because nothing else on this platform is invoked that way. So
 * `$_GET['token']` is checked here too, in addition to (not instead of) the real function's
 * two checks, specifically so the URL-cron path can actually authenticate at all. This is
 * the one place this code intentionally does more than the literal shared function, and
 * it's additive only — every request the real function would already accept, this still
 * accepts identically.
 *
 * @return bool
 */
function b3IsAdminAuthenticated() {
    // .env lives in /admin/ (confirmed directly from admin-auth.php and chat-config.php,
    // both of which read it as `__DIR__ . '/.env'` — i.e. the same directory they
    // themselves live in). This task's files live in a sibling `/rehoming/` folder at the
    // same project root, so: one level up from this file, then into `admin/.env`.
    $env_file = dirname(__DIR__) . '/admin/.env';
    if (!file_exists($env_file)) {
        return false;
    }
    // Next two lines copied verbatim from chat-config.php's chatIsAdminAuthenticated().
    preg_match('/ADMIN_TOKEN=(.+)/', file_get_contents($env_file), $m);
    $admin_token = isset($m[1]) ? trim($m[1]) : null;
    if (!$admin_token) {
        return false;
    }

    // TASK-B3 addition beyond the literal source (see doc comment above): also accept
    // $_GET['token'], needed only for Hostinger's URL-triggered cron invocation.
    $provided = $_POST['token'] ?? ($_SERVER['HTTP_X_ADMIN_TOKEN'] ?? ($_GET['token'] ?? null));

    // Final line copied verbatim from chat-config.php's chatIsAdminAuthenticated().
    return $provided && hash_equals($admin_token, $provided);
}
// TASK-B3 END: admin-token guard for the cron script's HTTP invocation path
