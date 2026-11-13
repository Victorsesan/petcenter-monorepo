<?php
/**
 * account-lifecycle.php
 * -----------------------------------------------------------------------
 * TASK E2 — Account lifecycle handling (Rehoming Platform)
 *
 * <!-- TASK-E2 START (new file — account management logic) -->
 *
 * WHAT THIS FILE IS FOR
 * On merchant self-deletion or admin suspension, every listing that
 * merchant owns must flip to status "no_longer_available" (an addition
 * to the canonical listing `status` enum owned by Task D1 — see the
 * "SHARED CONTRACTS" note below), and any visitor with an open chat
 * thread on one of those listings must receive a one-time system
 * message closing that conversation.
 *
 * SHARED CONTRACTS THIS FILE BUILDS AGAINST (per
 * Rehoming-Platform-Task-Assignments.md — read in full before this file
 * was written):
 *   - Listing `status` enum (owned by Task D1): active | pending_placement
 *     | placement_complete | placement_fell_through. This file adds
 *     `no_longer_available` as its own contribution to that same enum.
 *     Task D1 had not published a completion file (and no real listing
 *     data file existed) at the time this file was written, so every
 *     place this file touches the listing record is wrapped in
 *     <!-- TASK-E2 START --> / <!-- TASK-E2 END --> anchor comments,
 *     per the multi-worker same-file protocol, ready for a short
 *     reconciliation pass once D1's real listing file exists.
 *   - This project's own messaging endpoints (Task C5 / referenced by
 *     Task C2) are separate, new endpoints, not any other project's
 *     hardened chat files. This file does not implement message
 *     transport itself — it calls a small set of messaging-store
 *     helper functions (below) that assume the same shape documented
 *     for the existing site's live-chat system in
 *     ADMIN_DASHBOARD_PROJECT_SUMMARY.md (a `messages` table with a
 *     `from_type` column that already supports a 'system' value, one
 *     row per message, `thread_id` foreign key) — reusing that proven
 *     pattern per platform Rule 7 ("never recreate a feature from
 *     scratch"), NOT reusing the admin dashboard's actual support-chat
 *     tables, which are a different, unrelated conversation system.
 *   - Token invalidation on suspension is explicitly Task C5's job
 *     ("invalidate all of that principal's tokens on relevant security
 *     events (password change, account suspension per Task E2, etc.)")
 *     — this file fires a single, clearly-named hook
 *     (accountLifecycleOnStatusChange) that C5's real implementation
 *     can subscribe to once it exists; this file does not invalidate
 *     tokens itself, to respect Scope Lock (Directive 8b).
 *   - Caller identity (confirming the merchant self-deleting is who
 *     they say they are, and that a suspend/restore call really comes
 *     from an admin) is read from a generic session flag
 *     ($_SESSION['merchant_id'], $_SESSION['is_admin']) rather than
 *     this file naming, requesting, or waiting on any specific
 *     signup/login mechanism — those pages are being built separately
 *     by the CEO and are out of scope for every task in this project,
 *     per the BUILD APPROACH section's standing rule. Whatever sets
 *     those session values is not this file's concern.
 *
 * NOTE ON MISSING SOURCE FILES: this task's assigned "Files to Request
 * From CEO" were the account management logic file and the listing
 * data file, requested in parallel against D1's published status
 * contract. At the time of this task, D1 has no completion file and
 * no merchant account system (Phase B) exists yet either, so neither
 * file exists to request. Per the SHARED CONTRACTS mechanism, this is
 * therefore written as a new, self-contained file built against the
 * published contract, using the local JSON stub
 * (listing-status-stub.json, submitted alongside this file) as the
 * listing record shape to test against. This is flagged here
 * explicitly, not silently assumed — see this task's completion
 * file's "Bugs Found Outside Task Scope" / reconciliation notes.
 * -----------------------------------------------------------------------
 */

// ---------------------------------------------------------------------
// CONFIG — paths follow the same flat-file JSON pattern documented for
// /adoptions/pets/{category}/{pet_id}/pet.json in
// adoption-website-project-summary.md Section 1, applied to the new
// rehoming-platform listing records (owned for real by Task A1).
// ---------------------------------------------------------------------
define('RH_LISTINGS_BASE', __DIR__ . '/rehoming/listings/');      // one JSON file per listing, keyed by listing_id
define('RH_MERCHANT_INDEX_BASE', __DIR__ . '/rehoming/merchants/'); // one JSON file per merchant, holds their listing_id list
define('RH_THREADS_BASE', __DIR__ . '/rehoming/messaging/threads/'); // one JSON file per chat thread (this project's own, separate messaging store — see header note)

// The five canonical status values this file is allowed to write.
// `no_longer_available` is this task's own addition to D1's four.
const RH_STATUS_ACTIVE               = 'active';
const RH_STATUS_PENDING_PLACEMENT    = 'pending_placement';
const RH_STATUS_PLACEMENT_COMPLETE   = 'placement_complete';
const RH_STATUS_PLACEMENT_FELL_THROUGH = 'placement_fell_through';
const RH_STATUS_NO_LONGER_AVAILABLE  = 'no_longer_available'; // <-- E2's contribution to the shared enum

// The exact, business-approved copy for the one-time chat system message.
// Do not paraphrase this string elsewhere in the codebase — copy it
// verbatim, the same convention already used for e.g. the adoption
// status page's fixed payment-option copy (adoption-website-project-
// summary.md Section 7.2).
// <!-- TASK-E2 START (thread reopen switch — flagged product decision) -->
// When an admin restores a suspended merchant's listings, should the chat
// threads this task closed be reopened? The task spec is silent, so this is
// a single switch the CEO can flip. Default true: restoring a listing while
// leaving its conversations dead would strand every visitor who was mid-chat.
// Reopening adds NO new visitor-facing copy (no unapproved message text).
const RH_REOPEN_THREADS_ON_RESTORE = true;
// <!-- TASK-E2 END -->

const RH_CHAT_CLOSE_MESSAGE = "This merchant's account is no longer active — this conversation is now closed.";

/**
 * rhSanitizeId()
 * Strips anything outside alphanumeric + hyphen from an ID before it is
 * ever used to build a file path — this is the exact same sanitization
 * approach already documented for delete-application.php
 * (ADMIN_DASHBOARD_PROJECT_SUMMARY.md: "Sanitizes it to alphanumeric +
 * hyphen only... this is what makes path traversal via a crafted ID
 * impossible"), reused here per Rule 7 rather than re-derived. Both
 * $merchantId (client-supplied on every request) and $listingId
 * (read from a merchant's own index file, but still worth treating as
 * untrusted) are run through this before touching the filesystem.
 */
function rhSanitizeId($id) {
    return preg_replace('/[^A-Za-z0-9\-]/', '', (string) $id);
}

/**
 * rhPathIsWithinBase()
 * Confirms a candidate path's *directory* genuinely resolves (via
 * realpath(), which also collapses any symlinks) to somewhere inside
 * the given base directory — the second, independent safety layer
 * documented for delete-application.php on top of ID sanitization
 * (ADMIN_DASHBOARD_PROJECT_SUMMARY.md: "confirms the resolved path is
 * still genuinely inside the applications directory even after
 * symlink resolution"). Checked against dirname($path) rather than
 * $path itself so this still works for a file that doesn't exist on
 * disk yet (a brand-new write), not only for updating an existing one.
 */
function rhPathIsWithinBase($path, $base) {
    $realBase = realpath($base);
    $realDir = realpath(dirname($path));
    if ($realBase === false || $realDir === false) return false;
    return strpos($realDir, $realBase) === 0;
}

/**
 * rhLoadJson()
 * Small helper: reads and json_decodes a file, returns null on any
 * failure (missing file, invalid JSON) rather than throwing — every
 * caller below treats "record not found" as an ordinary, expected case
 * rather than an error.
 */
function rhLoadJson($path) {
    if (!is_file($path)) return null;
    $raw = file_get_contents($path);
    if ($raw === false) return null;
    $data = json_decode($raw, true);
    return (json_last_error() === JSON_ERROR_NONE) ? $data : null;
}

/**
 * rhWriteJson()
 * Small helper: writes JSON back to disk with LOCK_EX (same
 * file-locking discipline already used elsewhere on this platform, e.g.
 * vote-helpful.php's concurrent-vote protection documented in
 * adoption-website-project-summary.md Section 10.5) so two lifecycle
 * triggers landing at the same moment can't corrupt the file.
 */
function rhWriteJson($path, $data, $expectedBase = null) {
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    // Second, independent containment check (see rhPathIsWithinBase
    // docblock) — refuses to write if, even after ID sanitization, the
    // resolved target directory isn't really inside the base it's
    // supposed to be. Only enforced when a caller passes $expectedBase;
    // callers that already fully control their own path (e.g. writing
    // to a freshly-created directory) can omit it.
    if ($expectedBase !== null && !rhPathIsWithinBase($path, $expectedBase)) {
        return false;
    }
    $bytes = file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    return $bytes !== false;
}

/**
 * accountLifecycleGetMerchantListingIds()
 * Reads a merchant's own listing-index file and returns the array of
 * listing IDs they own. Returns an empty array (not null) if the
 * merchant has no listings on record, so callers can foreach() safely
 * without an extra null check at every call site.
 */
function accountLifecycleGetMerchantListingIds($merchantId) {
    // <!-- TASK-E2 START (path-safety: sanitize, reused verbatim from
    // delete-application.php's documented pattern — stripping anything
    // outside alphanumeric+hyphen makes a traversal sequence like
    // "../../something" impossible to construct in the first place,
    // since both "." and "/" are stripped before the path is built) -->
    $safeMerchantId = rhSanitizeId($merchantId);
    if ($safeMerchantId === '') return [];
    $indexPath = RH_MERCHANT_INDEX_BASE . $safeMerchantId . '.json';
    // <!-- TASK-E2 END -->

    $index = rhLoadJson($indexPath);
    if (!is_array($index) || empty($index['listing_ids']) || !is_array($index['listing_ids'])) {
        return [];
    }
    // Sanitize every listing ID read back out of the index too — these
    // feed directly into RH_LISTINGS_BASE path-building elsewhere, so
    // an index file that somehow contained a crafted value is still
    // caught here rather than trusted just because it came from disk.
    return array_values(array_filter(array_map('rhSanitizeId', $index['listing_ids']), function ($id) {
        return $id !== '';
    }));
}

/**
 * accountLifecycleSetListingsUnavailable()
 * Core of the required behavior: for every listing a merchant owns,
 * sets status = no_longer_available. Records the merchant's account
 * event ('self_deletion' or 'admin_suspension') and a timestamp on
 * each listing so the display layer and any later restore action have
 * something to reason from — these two extra fields are additive, not
 * part of the locked status enum itself, so they don't collide with
 * D1's own fields.
 *
 * $trigger must be either 'self_deletion' or 'admin_suspension' — this
 * is intentionally not a free-text field, since the two triggers have
 * different reversibility rules (see accountLifecycleRestoreListings()
 * below) and a typo'd trigger value must fail loudly, not silently.
 */
function accountLifecycleSetListingsUnavailable($merchantId, $trigger) {
    if (!in_array($trigger, ['self_deletion', 'admin_suspension'], true)) {
        throw new InvalidArgumentException('accountLifecycleSetListingsUnavailable: trigger must be self_deletion or admin_suspension, got: ' . $trigger);
    }

    $listingIds = accountLifecycleGetMerchantListingIds($merchantId);
    $affectedListingIds = [];

    foreach ($listingIds as $listingId) {
        $listingPath = RH_LISTINGS_BASE . $listingId . '.json';
        $listing = rhLoadJson($listingPath);
        if ($listing === null) {
            // Listing index pointed at a file that no longer exists on
            // disk — skip it rather than fatally erroring the whole
            // batch, so one stale index entry can't block every other
            // listing from being closed out correctly.
            continue;
        }

        // Self-deletion is terminal and must never become reversible
        // again. Without this guard, an admin_suspension call landing
        // on a listing that was already set unavailable_reason =
        // self_deletion (e.g. an admin action that fires after a
        // merchant already deleted their own account) would overwrite
        // the reason to admin_suspension — which would then make
        // accountLifecycleRestoreListings() able to bring back a
        // listing whose merchant account no longer exists. A later
        // self_deletion call is still allowed to overwrite an existing
        // admin_suspension (deleting the account entirely is a stronger,
        // final action than a suspension, so that direction is safe).
        if ($trigger === 'admin_suspension'
            && isset($listing['unavailable_reason'])
            && $listing['unavailable_reason'] === 'self_deletion') {
            continue;
        }

        // <!-- TASK-E2 START (shared field: `status`, owned for real by
        // Task D1 — see SHARED CONTRACTS note in the file header) -->
        // Preserve status history so overwriting `status` never erases it.
        // Only recorded on the FIRST transition into no_longer_available:
        // a repeat call (or self_deletion landing on an already-suspended
        // listing) must not replace the real prior status with
        // 'no_longer_available'.
        if (($listing['status'] ?? null) !== RH_STATUS_NO_LONGER_AVAILABLE) {
            $listing['status_before_unavailable'] = $listing['status'] ?? null;
            // Permanent marker: a listing that had reached placement_complete
            // stays identifiable as a finished rehome (Tasks B3/B4 impact
            // stats) even after `status` is overwritten. Never cleared,
            // not even on restore.
            if (($listing['status'] ?? null) === RH_STATUS_PLACEMENT_COMPLETE) {
                $listing['was_placement_complete'] = true;
            }
        }
        $listing['status'] = RH_STATUS_NO_LONGER_AVAILABLE;
        // Additive bookkeeping fields (not part of D1's enum contract
        // itself), so a later restore knows why this listing is
        // unavailable and whether it's reversible.
        $listing['unavailable_reason'] = $trigger; // 'self_deletion' | 'admin_suspension'
        $listing['unavailable_at'] = gmdate('Y-m-d\TH:i:s\Z');
        // <!-- TASK-E2 END -->

        if (rhWriteJson($listingPath, $listing, RH_LISTINGS_BASE)) {
            $affectedListingIds[] = $listingId;
        }
    }

    // Close out any open chat threads tied to the affected listings —
    // this is the "any visitor with an open chat thread ... receives a
    // one-time system message" requirement.
    accountLifecycleCloseThreadsForListings($affectedListingIds);

    // Hook for Task C5 (messaging endpoint hardening) to subscribe to —
    // token invalidation on suspension is explicitly C5's responsibility
    // per the SHARED CONTRACTS cross-project coordination note, not
    // this file's. Calling it here is a no-op stub until C5's real
    // implementation exists; kept as a named call site rather than
    // silently omitted, so integration is a one-line wire-up later.
    accountLifecycleOnStatusChange($merchantId, $trigger, $affectedListingIds);

    return $affectedListingIds;
}

/**
 * accountLifecycleRestoreListings()
 * Reversal path for a suspension that was overturned via Task C1's
 * dispute flow. Per this task's exact spec: "Suspension ... keeps
 * listings in no_longer_available but reversible — if C1's dispute
 * path succeeds, admin restores to active directly." Restoration is
 * ALWAYS to `active`, never to whatever status the listing held before
 * suspension — that is the literal behavior specified for this task,
 * not an oversight.
 *
 * Deliberately only restores listings whose `unavailable_reason` is
 * 'admin_suspension' — a self-deleted merchant's listings are not
 * reversible through this path, since account self-deletion has no
 * dispute mechanism attached to it anywhere in this project's spec.
 */
function accountLifecycleRestoreListings($merchantId) {
    $listingIds = accountLifecycleGetMerchantListingIds($merchantId);
    $restoredListingIds = [];

    foreach ($listingIds as $listingId) {
        $listingPath = RH_LISTINGS_BASE . $listingId . '.json';
        $listing = rhLoadJson($listingPath);
        if ($listing === null) continue;

        $wasSuspended = isset($listing['status'], $listing['unavailable_reason'])
            && $listing['status'] === RH_STATUS_NO_LONGER_AVAILABLE
            && $listing['unavailable_reason'] === 'admin_suspension';

        if (!$wasSuspended) {
            // Not suspended (e.g. was already active, or was
            // self-deleted and therefore not eligible for this
            // reversal path) — leave it untouched.
            continue;
        }

        // <!-- TASK-E2 START (shared field: `status` — restoring to
        // active per this task's literal spec, see docblock above) -->
        $listing['status'] = RH_STATUS_ACTIVE;
        // status_before_unavailable is cleared (restore goes to `active` per
        // spec); was_placement_complete is deliberately KEPT.
        unset($listing['unavailable_reason'], $listing['unavailable_at'], $listing['status_before_unavailable']);
        // <!-- TASK-E2 END -->

        if (rhWriteJson($listingPath, $listing, RH_LISTINGS_BASE)) {
            $restoredListingIds[] = $listingId;
        }
    }

    // <!-- TASK-E2 START (reopen threads closed by this task) -->
    if (RH_REOPEN_THREADS_ON_RESTORE && !empty($restoredListingIds)) {
        accountLifecycleReopenThreadsForListings($restoredListingIds);
    }
    // <!-- TASK-E2 END -->

    return $restoredListingIds;
}

/**
 * accountLifecycleCloseThreadsForListings()
 * For every listing ID given, finds any chat thread still open
 * ('waiting' or 'active' — anything not already 'done'/'closed') and:
 *   1. inserts exactly one from_type='system' message with the fixed
 *      RH_CHAT_CLOSE_MESSAGE text
 *   2. flips the thread's own status to a closed state
 * Guarded to run at most once per thread (the "one-time" requirement)
 * by checking the thread's own status before acting — once a thread is
 * closed by this function, calling it again for the same thread is a
 * harmless no-op, the same idempotency shape already used by
 * chat-admin-done.php / chat-send.php's idempotencyKey pattern
 * documented in ADMIN_DASHBOARD_PROJECT_SUMMARY.md.
 */
function accountLifecycleCloseThreadsForListings(array $listingIds) {
    if (empty($listingIds)) return;

    foreach ($listingIds as $listingId) {
        $threadPaths = glob(RH_THREADS_BASE . '*.json');
        if ($threadPaths === false) continue;

        foreach ($threadPaths as $threadPath) {
            $thread = rhLoadJson($threadPath);
            if ($thread === null) continue;
            if (!isset($thread['listing_id']) || $thread['listing_id'] !== $listingId) continue;

            // Idempotency guard — a thread already closed by this
            // function (or otherwise not open) is skipped, so a
            // merchant lifecycle event that somehow fires twice never
            // double-posts the system message.
            if (!in_array($thread['status'] ?? '', ['waiting', 'active'], true)) {
                continue;
            }

            // <!-- TASK-E2 START (new messaging-store write — this
            // project's own thread/message files, not any other
            // project's chat tables; see file header note) -->
            $thread['status_before_close'] = $thread['status']; // so a restore can reopen it exactly as it was
            $thread['status'] = 'closed_merchant_inactive';
            $thread['messages'] = $thread['messages'] ?? [];
            $thread['messages'][] = [
                'from_type'  => 'system', // reuses the existing site's proven from_type value, per Rule 7
                'body'       => RH_CHAT_CLOSE_MESSAGE,
                'created_at' => gmdate('Y-m-d\TH:i:s\Z'),
            ];
            // <!-- TASK-E2 END -->

            // $threadPath came from glob() against RH_THREADS_BASE
            // itself, not from any client-supplied ID, so it's already
            // safe to write — $expectedBase still passed for
            // consistency/defense-in-depth with every other write in
            // this file.
            rhWriteJson($threadPath, $thread, RH_THREADS_BASE);
        }
    }
}

/**
 * accountLifecycleReopenThreadsForListings()
 * Reverse of accountLifecycleCloseThreadsForListings(), used only after a
 * suspension is overturned. Reopens threads in 'closed_merchant_inactive'
 * (i.e. closed BY THIS TASK — threads closed any other way are never
 * touched) back to the status they had before closing. Adds no message.
 */
function accountLifecycleReopenThreadsForListings(array $listingIds) {
    $threadPaths = glob(RH_THREADS_BASE . '*.json');
    if ($threadPaths === false) return;
    foreach ($threadPaths as $threadPath) {
        $thread = rhLoadJson($threadPath);
        if ($thread === null) continue;
        if (($thread['status'] ?? '') !== 'closed_merchant_inactive') continue;
        if (!isset($thread['listing_id']) || !in_array($thread['listing_id'], $listingIds, true)) continue;
        // <!-- TASK-E2 START -->
        $thread['status'] = $thread['status_before_close'] ?? 'active';
        unset($thread['status_before_close']);
        // <!-- TASK-E2 END -->
        rhWriteJson($threadPath, $thread, RH_THREADS_BASE);
    }
}

/**
 * accountLifecycleOnStatusChange()
 * Named integration hook, intentionally left as a no-op body. Task
 * C5's messaging-token hardening is documented to invalidate a
 * principal's tokens "on relevant security events (password change,
 * account suspension per Task E2, etc.)" — that invalidation logic
 * belongs to C5, not this file (Scope Lock, Directive 8b). Keeping a
 * real, named function here — rather than omitting the call entirely —
 * means wiring C5's real implementation in later is a one-line change
 * inside this function body, not a hunt through this file to find
 * where the hook should have been.
 */
function accountLifecycleOnStatusChange($merchantId, $trigger, array $affectedListingIds) {
    // Intentionally empty — see docblock above. Task C5 wires its real
    // token-invalidation call here once that task's completion file
    // exists.
}

// ---------------------------------------------------------------------
// HTTP ENTRY POINT
// Mirrors the request/response shape already used by this platform's
// existing admin-side status-changing endpoints (e.g. update-adoption.php,
// documented in ADMIN_DASHBOARD_PROJECT_SUMMARY.md Section 9) — JSON in,
// JSON out, token-gated, no raw PHP errors ever reach the client.
// ---------------------------------------------------------------------
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    /** TASK-WEB-04 START — The executable lifecycle route uses shared WEB-04 identity and CSRF checks. */
    // Session read for the generic entry-point check below — started
    // here rather than assumed already-active, since this file makes
    // no assumption about what else runs before it on a real request.
    require_once __DIR__ . '/merchant-platform-lib.php';
    header('Content-Type: application/json; charset=utf-8');
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') rh_json_response(['success'=>false,'error'=>'POST required'],405);

    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $action = is_string($input['action'] ?? null) ? $input['action'] : '';
    // Explicit is_string() check (not just empty()) — a crafted JSON
    // body like {"merchant_id": ["x"]} would pass empty() but then hit
    // PHP's "Array to string conversion" warning the moment it's used
    // in a string comparison or concatenated into a path downstream.
    // Rejected here, at the one place input is read, rather than
    // relying on every downstream function to defend against it.
    $merchantId = is_string($input['merchant_id'] ?? null) ? $input['merchant_id'] : '';

    // Historical E2 note below describes the prior generic session-flag
    // approach. The active WEB-04 gate no longer trusts those flags.
    // Caller identity is read from a generic session flag rather than
    // this file naming or depending on any specific signup/login
    // mechanism — per the project's standing rule, no task describes
    // waiting on, or references, the platform's account pages; the
    // entry point here is just "whatever set these session values,"
    // the same generic-entry-point pattern Task B1 uses for its own
    // verification flow. Two independent checks, matching the two
    // trigger types this file supports:
    //   - self-deletion is only valid when the session's own merchant
    //     ID matches the merchant_id being acted on (a merchant can
    //     only delete their own account)
    //   - suspend/restore requires an admin session flag, since those
    //     are admin-only actions
    // <!-- TASK-E2 START (generic session-flag entry point, per the
    // standing "never reference the account pages" rule) -->
    /** TASK-WEB-04 START — Identity and authorization come only from the account registry. */
    $caller = rh_auth_account();
    rh_require_csrf();
    if ($merchantId === '') rh_json_response(['success'=>false,'error'=>'merchant_id is required'],400);
    if (!in_array($action, ['self_delete','admin_suspend','admin_restore'], true)) rh_json_response(['success'=>false,'error'=>'Unknown action'],400);
    if ($action === 'self_delete' && ($caller['account_type'] !== 'merchant' || !hash_equals($caller['account_id'], $merchantId))) rh_json_response(['success'=>false,'error'=>'Not authorized to perform this action'],403);
    if (in_array($action, ['admin_suspend','admin_restore'], true) && $caller['account_type'] !== 'admin') rh_json_response(['success'=>false,'error'=>'Not authorized to perform this action'],403);
    $merchant = web04_find_account_by_id($merchantId);
    if (!$merchant || ($merchant['account_type'] ?? '') !== 'merchant') rh_json_response(['success'=>false,'error'=>'Merchant account not found'],404);
    if (!empty($merchant['is_test_account'])) rh_json_response(['success'=>false,'error'=>'Test accounts are managed through the staging test-account panel'],409);
    /** TASK-WEB-04 END */
    // <!-- TASK-E2 END -->

    try {
        switch ($action) {
            case 'self_delete':
                $affected = accountLifecycleSetListingsUnavailable($merchantId, 'self_deletion');
                if (!web04_account_status($merchantId, 'deleted')) rh_json_response(['success'=>false,'error'=>'Account status could not be updated'],409);
                web04_revoke_account_tokens($merchantId);
                rh_json_response(['success'=>true,'affected_listing_ids'=>$affected]);
            case 'admin_suspend':
                $affected = accountLifecycleSetListingsUnavailable($merchantId, 'admin_suspension');
                if (!web04_account_status($merchantId, 'suspended')) rh_json_response(['success'=>false,'error'=>'Account status could not be updated'],409);
                web04_revoke_account_tokens($merchantId);
                rh_json_response(['success'=>true,'affected_listing_ids'=>$affected]);
            case 'admin_restore':
                if (($merchant['status'] ?? '') !== 'suspended') rh_json_response(['success'=>false,'error'=>'Only suspended merchant accounts can be restored'],409);
                $restored = accountLifecycleRestoreListings($merchantId);
                if (!web04_account_status($merchantId, 'active')) rh_json_response(['success'=>false,'error'=>'Account status could not be updated'],409);
                rh_json_response(['success'=>true,'restored_listing_ids'=>$restored]);
        }
    } catch (Throwable $e) {
        // Never leak raw PHP errors to the client — same discipline as
        // the rest of the platform's PHP endpoints.
        rh_json_response(['success'=>false,'error'=>'Internal error processing account lifecycle event.'],500);
    }
    /** TASK-WEB-04 END */
}
// <!-- TASK-E2 END (new file — account management logic) -->
