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

    chatRespond(['success' => true, 'results' => $results]);

} else {
    chatRespond(['success' => false, 'error' => "action must be 'list' or 'lookup'"], 400);
}
