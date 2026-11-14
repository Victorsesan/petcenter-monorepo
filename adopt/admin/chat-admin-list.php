<?php
/**
 * chat-admin-list.php
 *
 * Admin dashboard "Live Chat" panel data source (Tier 1 item 19).
 *
 * Two modes:
 *  - action=list   -> returns threads grouped by status: waiting (open queue,
 *                      any admin can claim), active (split into mine vs
 *                      others' via the requesting admin's adminId), done
 *  - action=lookup -> simple chatId/email lookup box (item 26) — NOT full
 *                      text search, just a direct jump to one exact thread
 *
 * Requires admin auth (shared token) for both modes. The "mine vs others"
 * split for active threads needs adminId, which is the per-tab selected
 * identity (Section 8) — sent as a normal param here, validated against
 * chat_admin_profiles so a stale/deleted profile can't be used.
 */

header('Content-Type: application/json');
error_reporting(0);
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';

chatRequireAdmin();

$db = chatDb();
$action = $_GET['action'] ?? 'list';

// <!-- TASK-7 START -->
// Task 7 — Admin Auth-State Badge. Read-only helpers that turn a chat's
// existing log_audit() rows (Task 1's contract) into one of five
// display states for the admin list/claim view: none, pin_set,
// password_set, locked, verified_session. No new logging is added here —
// this only reads rows some other task already writes.
//
// 3-PASS HISTORY (see Task-7-Completion.md for full detail on each):
//   1st pass — built against a flagged assumption (table `chat_auth_audit`,
//     endpoint substrings) since no vocabulary was published.
//   2nd pass — reconciled against Task 1's real implementation directly:
//     table is `chat_auth_audit_log`, and the state signal lives in
//     `result` (not `endpoint`). Matched on result === 'verified'.
//   3rd pass (current) — per the project-wide Worker Sync Notice
//     (Post-Task1-Task7), 'verified' was wrong for this purpose: Task 1's
//     verify_token() logs result 'verified' on *every* successful session
//     poll (~every 2.5s for any open chat panel), so treating the newest
//     'verified' row as "verified this session" would show that state for
//     nearly every active chat regardless of whether a PIN/password was
//     ever entered. The Sync Notice's proposed vocabulary fixes this with
//     a distinct result string, 'tier_verified', reserved for an actual
//     PIN/password check (to be logged by Tasks 3/5a) — and explicitly
//     calls for excluding verify_token rows from this computation
//     entirely so that high-frequency polling noise can never mask an
//     earlier real lock or tier-verification event. Both changes are
//     applied in chatDeriveAuthBadge() below.
//
// Current result-value vocabulary (CEO-confirmed):
//   pin_set | password_set | tier_verified | locked | unlocked
// 'unlocked' needs no explicit branch below — it simply isn't 'locked',
// so it naturally falls through to whatever the tier scan finds next.
//
// principal_id is assumed to equal chat_threads.chat_id, matching how
// every other file in this project identifies one anonymous chat
// conversation — see the Shared Contract's reuse notes.

// Batch-fetches every audit row for a set of chat IDs in one query (no
// N+1 per-thread lookups) and reduces each chat's rows down to a single
// badge state. Orders by created_at DESC with id DESC as a tiebreaker —
// `created_at` alone isn't reliable for same-second rows (e.g. a tier
// being set and immediately verified in one request/response cycle), and
// since the audit log is insert-only, its auto-increment id always
// matches true chronological order even when timestamps tie.
function chatFetchAuditBadges($db, array $principalIds) {
    $badges = [];
    foreach ($principalIds as $id) {
        $badges[$id] = 'none'; // default: no audit history at all for this chat
    }
    if (empty($principalIds)) {
        return $badges;
    }

    $placeholders = implode(',', array_fill(0, count($principalIds), '?'));
    $stmt = $db->prepare(
        "SELECT principal_id, endpoint, result, created_at
         FROM chat_auth_audit_log
         WHERE principal_id IN ($placeholders)
         ORDER BY created_at DESC, id DESC"
    );
    $stmt->execute($principalIds);
    $rows = $stmt->fetchAll();

    // Group rows by principal, keeping the DESC time order from the query
    // so index 0 of each group is always that chat's most recent event.
    $byPrincipal = [];
    foreach ($rows as $row) {
        $byPrincipal[$row['principal_id']][] = $row;
    }

    foreach ($byPrincipal as $principalId => $auditRows) {
        $badges[$principalId] = chatDeriveAuthBadge($auditRows);
    }

    return $badges;
}

// Applies the badge precedence rule to one chat's audit rows (already
// sorted newest-first): a live lock always wins display, then a real
// tier verification, then whichever protection tier was ever configured
// (most recent tier wins, in case of an upgrade), then 'none'.
function chatDeriveAuthBadge(array $auditRowsDesc) {
    // Exclude Task 1's high-frequency session-token polling noise —
    // verify_token() logs a 'verified' row on every successful poll, so
    // without this filter it would almost always be the literal newest
    // row for any open chat and could mask a real lock or tier-verified
    // event that happened earlier but isn't chronologically last.
    $meaningful = array_values(array_filter($auditRowsDesc, function ($row) {
        return $row['endpoint'] !== 'verify_token';
    }));

    $mostRecent = $meaningful[0] ?? null;

    if ($mostRecent && $mostRecent['result'] === 'locked') {
        return 'locked';
    }

    if ($mostRecent && in_array($mostRecent['result'], ['tier_verified', 'pin_verified', 'password_verified', 'device_verified'], true)) {
        return 'verified_session';
    }

    // Neither locked nor freshly tier-verified right now — fall back to
    // whichever tier was most recently set.
    foreach ($meaningful as $row) {
        if ($row['result'] === 'password_set') {
            return 'password_set';
        }
        if ($row['result'] === 'pin_set') {
            return 'pin_set';
        }
    }

    return 'none';
}
// <!-- TASK-7 END -->

if ($action === 'list') {

    $adminId = (int)($_GET['adminId'] ?? 0);
    // adminId is only used to split "active" into mine/others — if it's
    // missing or invalid, lists still work, "mine" is just empty. This is
    // an attribution detail, not an auth gate (Section 8).

    // Waiting: open queue, any admin can see/claim. Archived threads are
    // hidden from here — they only show in the dedicated Archived listing.
    $stmt = $db->query(
        "SELECT t.*,
                (SELECT COUNT(*) FROM chat_messages m WHERE m.thread_id = t.id AND m.deleted_at IS NULL) AS message_count,
                (SELECT m2.msg_type FROM chat_messages m2 WHERE m2.thread_id = t.id AND m2.deleted_at IS NULL ORDER BY m2.id DESC LIMIT 1) AS last_msg_type,
                (SELECT m3.body FROM chat_messages m3 WHERE m3.thread_id = t.id AND m3.deleted_at IS NULL ORDER BY m3.id DESC LIMIT 1) AS last_msg_body
         FROM chat_threads t
         WHERE t.status = 'waiting' AND t.archived = 0
         ORDER BY t.created_at ASC"
    );
    $waiting = $stmt->fetchAll();

    // Active: assigned, split mine vs others. Also flags stale claims
    // (Section 7b — claimed but quiet for CHAT_STALE_CLAIM_HOURS) so the
    // dashboard can surface a staleness indicator without a separate call.
    $stmt = $db->prepare(
        "SELECT t.*, a.name AS admin_name, a.position AS admin_position, a.photo_url AS admin_photo, a.blue_check AS admin_blue_check,
                (SELECT COUNT(*) FROM chat_messages m WHERE m.thread_id = t.id AND m.deleted_at IS NULL) AS message_count,
                (SELECT m2.msg_type FROM chat_messages m2 WHERE m2.thread_id = t.id AND m2.deleted_at IS NULL ORDER BY m2.id DESC LIMIT 1) AS last_msg_type,
                (SELECT m3.body FROM chat_messages m3 WHERE m3.thread_id = t.id AND m3.deleted_at IS NULL ORDER BY m3.id DESC LIMIT 1) AS last_msg_body,
                (TIMESTAMPDIFF(HOUR, t.last_activity_at, NOW()) >= ?) AS is_stale
         FROM chat_threads t
         LEFT JOIN chat_admin_profiles a ON a.id = t.assigned_admin_id
         WHERE t.status = 'active' AND t.archived = 0
         ORDER BY t.last_activity_at DESC"
    );
    $stmt->execute([CHAT_STALE_CLAIM_HOURS]);
    $activeAll = $stmt->fetchAll();

    $activeMine = [];
    $activeOthers = [];
    foreach ($activeAll as $row) {
        if ($adminId && (int)$row['assigned_admin_id'] === $adminId) {
            $activeMine[] = $row;
        } else {
            $activeOthers[] = $row;
        }
    }

    // Done: closed/history — archived-done threads are excluded here too,
    // they only show in the Archived listing until unarchived.
    $stmt = $db->query(
        "SELECT t.*, a.name AS admin_name, a.position AS admin_position, a.photo_url AS admin_photo, a.blue_check AS admin_blue_check,
                (SELECT COUNT(*) FROM chat_messages m WHERE m.thread_id = t.id AND m.deleted_at IS NULL) AS message_count
         FROM chat_threads t
         LEFT JOIN chat_admin_profiles a ON a.id = t.assigned_admin_id
         WHERE t.status = 'done' AND t.archived = 0
         ORDER BY t.last_activity_at DESC
         LIMIT 200"
    );
    $done = $stmt->fetchAll();

    // Archived: any status, purely filtered by the archived flag — this is
    // the "hidden but recoverable" bucket, shown in its own dedicated tab.
    $stmt = $db->query(
        "SELECT t.*, a.name AS admin_name, a.position AS admin_position, a.photo_url AS admin_photo, a.blue_check AS admin_blue_check,
                (SELECT COUNT(*) FROM chat_messages m WHERE m.thread_id = t.id AND m.deleted_at IS NULL) AS message_count
         FROM chat_threads t
         LEFT JOIN chat_admin_profiles a ON a.id = t.assigned_admin_id
         WHERE t.archived = 1
         ORDER BY t.last_activity_at DESC
         LIMIT 200"
    );
    $archived = $stmt->fetchAll();

    // <!-- TASK-7 START -->
    // Attach the read-only auth_badge field to every thread across all
    // five buckets in one batch lookup, so the admin dashboard can render
    // each chat's protection state without a second request per thread.
    $allThreadsForBadges = array_merge($waiting, $activeMine, $activeOthers, $done, $archived);
    $principalIds = array_values(array_unique(array_filter(array_map(
        function ($t) { return $t['chat_id'] ?? null; },
        $allThreadsForBadges
    ))));
    $authBadges = chatFetchAuditBadges($db, $principalIds);

    // Writes the computed auth_badge onto every row of a bucket in place.
    $attachAuthBadge = function (array &$threads) use ($authBadges) {
        foreach ($threads as &$t) {
            $t['auth_badge'] = $authBadges[$t['chat_id']] ?? 'none';
        }
        unset($t);
    };
    $attachAuthBadge($waiting);
    $attachAuthBadge($activeMine);
    $attachAuthBadge($activeOthers);
    $attachAuthBadge($done);
    $attachAuthBadge($archived);
    // <!-- TASK-7 END -->

    chatRespond([
        'success' => true,
        'waiting' => $waiting,
        'activeMine' => $activeMine,
        'activeOthers' => $activeOthers,
        'done' => $done,
        'archived' => $archived,
    ]);

} elseif ($action === 'lookup') {

    $query = trim($_GET['query'] ?? '');
    if ($query === '') {
        chatRespond(['success' => false, 'error' => 'query is required'], 400);
    }

    // Exact chat ID match, or exact email match — deliberately not a LIKE/
    // full-text search (Section 7c — explicitly cut in favor of this
    // simple lookup box).
    if (chatValidEmail($query)) {
        $stmt = $db->prepare("SELECT * FROM chat_threads WHERE email = ? ORDER BY created_at DESC");
        $stmt->execute([$query]);
    } else {
        $stmt = $db->prepare("SELECT * FROM chat_threads WHERE chat_id = ?");
        $stmt->execute([$query]);
    }
    $results = $stmt->fetchAll();

    // <!-- TASK-7 START -->
    // Lookup results also carry auth_badge, consistent with the list
    // results — an admin jumping straight to one thread via the lookup box
    // should see its protection state before opening it, same as any
    // thread found through the normal list view.
    $lookupPrincipalIds = array_values(array_unique(array_filter(array_map(
        function ($t) { return $t['chat_id'] ?? null; },
        $results
    ))));
    $lookupBadges = chatFetchAuditBadges($db, $lookupPrincipalIds);
    foreach ($results as &$r) {
        $r['auth_badge'] = $lookupBadges[$r['chat_id']] ?? 'none';
    }
    unset($r);
    // <!-- TASK-7 END -->

    chatRespond(['success' => true, 'results' => $results]);

} else {
    chatRespond(['success' => false, 'error' => "action must be 'list' or 'lookup'"], 400);
}
