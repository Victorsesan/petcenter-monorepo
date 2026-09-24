<?php
/**
 * chat-message-delete.php
 *
 * The missing write-side trigger for message deletion (Tier 2 7a / NEXT UP).
 * Schema (deleted_at) and read-side filtering already existed in
 * chat-poll.php and chat-history.php before this file was written — this
 * endpoint only sets the column, with ownership + time-window validation.
 *
 * Two callers, same endpoint:
 *  - Customer deleting their own message: identified by chatId (no admin
 *    auth), can only delete their own from_type='user' messages.
 *  - Admin deleting their own reply: requires admin auth + adminId,
 *    can only delete their own from_type='admin' messages where
 *    admin_id matches.
 *
 * Neither side can delete the other's messages, and neither can delete
 * 'system' messages (the automated first response) — that's permanent
 * thread history by design.
 */

header('Content-Type: application/json');
error_reporting(0);
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    chatRespond(['success' => false, 'error' => 'POST required'], 405);
}

$input = chatJsonInput();
if (empty($input)) {
    $input = $_POST;
}

$chatId    = trim($input['chatId'] ?? '');
$messageId = (int)($input['messageId'] ?? 0);
$adminId   = (int)($input['adminId'] ?? 0); // only present for admin-side deletes

if ($chatId === '' || !$messageId) {
    chatRespond(['success' => false, 'error' => 'chatId and messageId are required'], 400);
}

$db = chatDb();

$stmt = $db->prepare("SELECT id FROM chat_threads WHERE chat_id = ? LIMIT 1");
$stmt->execute([$chatId]);
$threadId = $stmt->fetchColumn();

if (!$threadId) {
    chatRespond(['success' => false, 'error' => 'Chat not found'], 404);
}

$stmt = $db->prepare("SELECT * FROM chat_messages WHERE id = ? AND thread_id = ?");
$stmt->execute([$messageId, $threadId]);
$message = $stmt->fetch();

if (!$message) {
    chatRespond(['success' => false, 'error' => 'Message not found'], 404);
}

if ($message['deleted_at']) {
    chatRespond(['success' => true, 'alreadyDeleted' => true]);
}

if ($message['from_type'] === 'system') {
    chatRespond(['success' => false, 'error' => 'This message cannot be deleted'], 403);
}

// ── Ownership check ──────────────────────────────────────────────────────
if ($adminId) {
    chatRequireAdmin();
    if (!chatValidateAdminId($adminId)) {
        chatRespond(['success' => false, 'error' => 'That admin profile no longer exists or is inactive.'], 400);
    }
    if ($message['from_type'] !== 'admin' || (int)$message['admin_id'] !== $adminId) {
        chatRespond(['success' => false, 'error' => 'You can only delete your own messages'], 403);
    }
} else {
    if ($message['from_type'] !== 'user') {
        chatRespond(['success' => false, 'error' => 'You can only delete your own messages'], 403);
    }
}

// ── Time window check ────────────────────────────────────────────────────
$sentAt = strtotime($message['created_at']);
$minutesSinceSent = (time() - $sentAt) / 60;
if (CHAT_EDIT_DELETE_WINDOW_MINUTES > 0 && $minutesSinceSent > CHAT_EDIT_DELETE_WINDOW_MINUTES) {
    chatRespond(['success' => false, 'error' => 'This message is too old to delete (window: ' . CHAT_EDIT_DELETE_WINDOW_MINUTES . ' minutes)'], 403);
}

$db->prepare("UPDATE chat_messages SET deleted_at = NOW() WHERE id = ?")->execute([$messageId]);

chatRespond(['success' => true, 'messageId' => $messageId]);
