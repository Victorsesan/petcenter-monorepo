<?php
/**
 * chat-message-edit.php
 *
 * Edit own message within the short post-send window (Tier 2 7a / NEXT UP).
 * Only msg_type='text' messages are editable — editing an image/audio/video
 * attachment isn't a meaningful concept the way editing a typo is, so
 * those are rejected outright rather than silently allowed to misbehave.
 *
 * Same ownership pattern as chat-message-delete.php: customer edits their
 * own user messages (no admin auth), admin edits their own admin messages
 * (requires admin auth + matching admin_id).
 *
 * original_body is preserved on the FIRST edit only (if a message is
 * edited twice, original_body still holds what was actually first sent,
 * not the intermediate edit) — useful as a permanent record of the
 * original wording without needing a separate edit-history table.
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
$newBody   = trim($input['body'] ?? '');
$adminId   = (int)($input['adminId'] ?? 0);

if ($chatId === '' || !$messageId) {
    chatRespond(['success' => false, 'error' => 'chatId and messageId are required'], 400);
}
if ($newBody === '') {
    chatRespond(['success' => false, 'error' => 'Message cannot be empty — use delete instead if you want to remove it'], 400);
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
    chatRespond(['success' => false, 'error' => 'This message has been deleted'], 410);
}

if ($message['msg_type'] !== 'text') {
    chatRespond(['success' => false, 'error' => 'Only text messages can be edited'], 403);
}

// ── Ownership check (same pattern as chat-message-delete.php) ───────────
if ($adminId) {
    chatRequireAdmin();
    if (!chatValidateAdminId($adminId)) {
        chatRespond(['success' => false, 'error' => 'That admin profile no longer exists or is inactive.'], 400);
    }
    if ($message['from_type'] !== 'admin' || (int)$message['admin_id'] !== $adminId) {
        chatRespond(['success' => false, 'error' => 'You can only edit your own messages'], 403);
    }
} else {
    if ($message['from_type'] !== 'user') {
        chatRespond(['success' => false, 'error' => 'You can only edit your own messages'], 403);
    }
}

// ── Time window check ────────────────────────────────────────────────────
$sentAt = strtotime($message['created_at']);
$minutesSinceSent = (time() - $sentAt) / 60;
if (CHAT_EDIT_DELETE_WINDOW_MINUTES > 0 && $minutesSinceSent > CHAT_EDIT_DELETE_WINDOW_MINUTES) {
    chatRespond(['success' => false, 'error' => 'This message is too old to edit (window: ' . CHAT_EDIT_DELETE_WINDOW_MINUTES . ' minutes)'], 403);
}

// Preserve original_body only on the first edit
$originalBody = $message['original_body'] ?? $message['body'];

$stmt = $db->prepare(
    "UPDATE chat_messages SET body = ?, original_body = ?, edited_at = NOW() WHERE id = ?"
);
$stmt->execute([$newBody, $originalBody, $messageId]);

chatRespond(['success' => true, 'messageId' => $messageId, 'body' => $newBody]);
