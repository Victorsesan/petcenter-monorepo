<?php
/**
 * chat-admin-claim.php
 *
 * Admin claims a thread from the open queue (Tier 1 item 19 — Waiting,
 * open queue any admin can claim). Fires the admin_joined event, which is
 * what drives the joined-chat banner (item 7) on the customer side via
 * chat-poll.php's events array.
 *
 * Open-queue semantics: claiming is first-come-first-served. If two admins
 * race to claim the same waiting thread, this is guarded by only updating
 * rows still in 'waiting' status — the loser of the race gets a clean
 * "already claimed" response rather than silently overwriting the winner's
 * claim.
 */

header('Content-Type: application/json');
error_reporting(0);
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';

chatRequireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    chatRespond(['success' => false, 'error' => 'POST required'], 405);
}

$input = chatJsonInput();
if (empty($input)) {
    $input = $_POST;
}

$chatId  = trim($input['chatId'] ?? '');
$adminId = (int)($input['adminId'] ?? 0);

if ($chatId === '' || !$adminId) {
    chatRespond(['success' => false, 'error' => 'chatId and adminId are required'], 400);
}

if (!chatValidateAdminId($adminId)) {
    chatRespond(['success' => false, 'error' => 'That admin profile no longer exists or is inactive. Pick a different one.'], 400);
}

$db = chatDb();

$stmt = $db->prepare("SELECT * FROM chat_threads WHERE chat_id = ? LIMIT 1");
$stmt->execute([$chatId]);
$thread = $stmt->fetch();

if (!$thread) {
    chatRespond(['success' => false, 'error' => 'Chat not found'], 404);
}

if ($thread['status'] !== 'waiting') {
    if ((int)$thread['assigned_admin_id'] === $adminId && $thread['status'] === 'active') {
        // Already claimed by this same admin — treat as a harmless no-op,
        // not an error (e.g. a double-click or a retried request).
        chatRespond(['success' => true, 'alreadyClaimed' => true, 'chatId' => $chatId]);
    }
    chatRespond(['success' => false, 'error' => 'This chat has already been claimed by another admin.'], 409);
}

try {
    $db->beginTransaction();

    // Atomic claim: only succeeds if it's still 'waiting' at the moment of
    // this UPDATE — closes the race-condition window between the SELECT
    // above and this write.
    $stmt = $db->prepare(
        "UPDATE chat_threads SET status = 'active', assigned_admin_id = ?, unread_for_admin = 0
         WHERE chat_id = ? AND status = 'waiting'"
    );
    $stmt->execute([$adminId, $chatId]);

    if ($stmt->rowCount() === 0) {
        // Someone else claimed it in the moment between our SELECT and this UPDATE.
        $db->rollBack();
        chatRespond(['success' => false, 'error' => 'This chat was just claimed by another admin.'], 409);
    }

    $db->prepare("INSERT INTO chat_events (thread_id, event_type, admin_id) VALUES (?, 'admin_joined', ?)")
       ->execute([$thread['id'], $adminId]);

    $db->commit();

} catch (Exception $e) {
    $db->rollBack();
    error_log('chat-admin-claim.php failed: ' . $e->getMessage());
    chatRespond(['success' => false, 'error' => 'Failed to claim chat. Please try again.'], 500);
}

chatRespond(['success' => true, 'chatId' => $chatId]);
