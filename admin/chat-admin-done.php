<?php
/**
 * chat-admin-done.php
 *
 * Admin marks a thread Done. Fires admin_left_done, which is what makes
 * the joined-chat banner disappear on the customer side (item 7 — "persists
 * until that admin marks the thread done"). Clears assigned_admin_id so the
 * thread is fully closed, not just hidden.
 *
 * Only the currently-assigned admin can mark a thread done — this prevents
 * a different admin from closing someone else's active conversation.
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

if ($thread['status'] === 'done') {
    chatRespond(['success' => true, 'alreadyDone' => true, 'chatId' => $chatId]);
}

if ((int)$thread['assigned_admin_id'] !== $adminId) {
    chatRespond(['success' => false, 'error' => 'Only the admin currently assigned to this chat can mark it done.'], 403);
}

try {
    $db->beginTransaction();

    $db->prepare("UPDATE chat_threads SET status = 'done', assigned_admin_id = NULL WHERE id = ?")
       ->execute([$thread['id']]);

    $db->prepare("INSERT INTO chat_events (thread_id, event_type, admin_id) VALUES (?, 'admin_left_done', ?)")
       ->execute([$thread['id'], $adminId]);

    $db->prepare("INSERT INTO chat_events (thread_id, event_type, admin_id) VALUES (?, 'ticket_closed', ?)")
       ->execute([$thread['id'], $adminId]);

    $db->commit();

} catch (Exception $e) {
    $db->rollBack();
    error_log('chat-admin-done.php failed: ' . $e->getMessage());
    chatRespond(['success' => false, 'error' => 'Failed to mark chat as done. Please try again.'], 500);
}

chatRespond(['success' => true, 'chatId' => $chatId]);
