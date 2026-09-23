<?php
/**
 * chat-poll.php
 *
 * Customer-side polling endpoint, hit every 2-3 seconds while the chat
 * panel is open and the tab is focused (backing off to ~15s when hidden —
 * that backoff timing logic lives client-side in chat-widget.js; this
 * endpoint just answers whatever it's asked, as cheaply as possible, since
 * it's the single most frequently-called endpoint in this entire feature).
 *
 * Returns:
 *  - new messages since lastMessageId
 *  - new events (admin_joined / admin_left_done) since lastEventId
 *  - current thread status
 *  - typing status from the other side
 *  - the honest "no one's online yet" message, computed server-side so the
 *    client never has to reason about clock skew (Section 7b / 8)
 *
 * As a side effect of being polled, any unseen admin messages in this
 * thread are marked 'seen' — this is the read-receipt mechanism (Tier 2
 * 7a), included now since the `status` column already exists and costs
 * nothing extra to wire up at this point versus retrofitting it later.
 */

header('Content-Type: application/json');
error_reporting(0);
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';

$chatId        = trim($_GET['chatId'] ?? '');
$lastMessageId = (int)($_GET['lastMessageId'] ?? 0);
$lastEventId   = (int)($_GET['lastEventId'] ?? 0);

if ($chatId === '') {
    chatRespond(['success' => false, 'error' => 'chatId is required'], 400);
}

$db = chatDb();

$stmt = $db->prepare("SELECT * FROM chat_threads WHERE chat_id = ? LIMIT 1");
$stmt->execute([$chatId]);
$thread = $stmt->fetch();

if (!$thread) {
    chatRespond(['success' => false, 'error' => 'Chat not found'], 404);
}

$threadId = $thread['id'];

// ── Mark admin messages as seen (read receipts groundwork) ──────────────
$db->prepare(
    "UPDATE chat_messages SET status = 'seen'
     WHERE thread_id = ? AND from_type = 'admin' AND status != 'seen' AND deleted_at IS NULL"
)->execute([$threadId]);

// Clear the customer's own unread flag now that they're actively polling
if ($thread['unread_for_user']) {
    $db->prepare("UPDATE chat_threads SET unread_for_user = 0 WHERE id = ?")->execute([$threadId]);
}

// ── New messages since lastMessageId ─────────────────────────────────────
$stmt = $db->prepare(
    "SELECT m.*, a.name AS admin_name, a.position AS admin_position, a.photo_url AS admin_photo, a.blue_check AS admin_blue_check
     FROM chat_messages m
     LEFT JOIN chat_admin_profiles a ON a.id = m.admin_id
     WHERE m.thread_id = ? AND m.id > ? AND m.deleted_at IS NULL
     ORDER BY m.id ASC"
);
$stmt->execute([$threadId, $lastMessageId]);
$messages = $stmt->fetchAll();

// ── New events since lastEventId ─────────────────────────────────────────
$stmt = $db->prepare(
    "SELECT e.*, a.name AS admin_name, a.position AS admin_position, a.photo_url AS admin_photo, a.blue_check AS admin_blue_check
     FROM chat_events e
     LEFT JOIN chat_admin_profiles a ON a.id = e.admin_id
     WHERE e.thread_id = ? AND e.id > ?
     ORDER BY e.id ASC"
);
$stmt->execute([$threadId, $lastEventId]);
$events = $stmt->fetchAll();

// ── Typing status from the other side (admin typing, shown to customer) ──
$stmt = $db->prepare(
    "SELECT 1 FROM chat_typing WHERE thread_id = ? AND who = 'admin' AND expires_at > NOW()"
);
$stmt->execute([$threadId]);
$adminTyping = (bool)$stmt->fetchColumn();

// TASK-8 START (8b — admin voice-recording indicator, admin → customer)
// Task 8b addition — same query shape as adminTyping above, just the
// 'admin_recording' who value (see chat-typing.php). Independent row/PK
// from the plain 'admin' typing row above, so both can be checked
// without interfering with each other.
$stmt = $db->prepare(
    "SELECT 1 FROM chat_typing WHERE thread_id = ? AND who = 'admin_recording' AND expires_at > NOW()"
);
$stmt->execute([$threadId]);
$adminRecording = (bool)$stmt->fetchColumn();
// TASK-8 END (8b — admin voice-recording indicator, admin → customer)

// Bot name/photo are never sensitive, so always include them regardless of
// the bot's current 'active' state — a bot message already sent while the
// bot was active must keep showing its name/photo correctly even if an
// admin later toggles the bot off, since this is re-evaluated fresh on
// every single poll rather than being fixed at the moment the message was
// first created.
$botProfile = chatGetBotProfile();
$botPublicInfo = [
    'name'     => $botProfile['name'] ?: 'Pet Center Bot',
    'photoUrl' => $botProfile['photoUrl'],
];

chatRespond([
    'success'        => true,
    'status'         => $thread['status'],
    'messages'       => $messages,
    'events'         => $events,
    'adminTyping'    => $adminTyping,
    /* TASK-8 START (8b — expose adminRecording in the response) */
    'adminRecording' => $adminRecording,
    /* TASK-8 END (8b — expose adminRecording in the response) */
    'botProfile'     => $botPublicInfo,
]);
