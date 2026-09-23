<?php
/**
 * chat-admin-poll.php
 *
 * Admin-side polling endpoint — deliberately separate from chat-poll.php
 * (the customer-side poller) because the two sides have inverted semantics:
 *
 *  chat-poll.php:       marks ADMIN messages as 'seen' (customer reading admin)
 *                       clears unread_for_user
 *
 *  chat-admin-poll.php: marks USER messages as 'seen' (admin reading customer)
 *                       clears unread_for_admin
 *
 * Mixing them up would incorrectly flip seen/unread state for the wrong side.
 * Requires admin auth.
 */

header('Content-Type: application/json');
error_reporting(0);
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';

chatRequireAdmin();

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

// Mark USER messages as seen (admin is reading the customer's messages)
$db->prepare(
    "UPDATE chat_messages SET status = 'seen'
     WHERE thread_id = ? AND from_type = 'user' AND status != 'seen' AND deleted_at IS NULL"
)->execute([$threadId]);

// Clear the admin's unread flag
if ($thread['unread_for_admin']) {
    $db->prepare("UPDATE chat_threads SET unread_for_admin = 0 WHERE id = ?")->execute([$threadId]);
}

// New messages since lastMessageId
$stmt = $db->prepare(
    "SELECT m.*, a.name AS admin_name, a.position AS admin_position, a.photo_url AS admin_photo, a.blue_check AS admin_blue_check
     FROM chat_messages m
     LEFT JOIN chat_admin_profiles a ON a.id = m.admin_id
     WHERE m.thread_id = ? AND m.id > ? AND m.deleted_at IS NULL
     ORDER BY m.id ASC"
);
$stmt->execute([$threadId, $lastMessageId]);
$messages = $stmt->fetchAll();

// New events since lastEventId
$stmt = $db->prepare(
    "SELECT e.*, a.name AS admin_name, a.position AS admin_position, a.photo_url AS admin_photo, a.blue_check AS admin_blue_check
     FROM chat_events e
     LEFT JOIN chat_admin_profiles a ON a.id = e.admin_id
     WHERE e.thread_id = ? AND e.id > ?
     ORDER BY e.id ASC"
);
$stmt->execute([$threadId, $lastEventId]);
$events = $stmt->fetchAll();

// Typing status from the customer side (admin sees if user is typing)
$stmt = $db->prepare(
    "SELECT 1 FROM chat_typing WHERE thread_id = ? AND who = 'user' AND expires_at > NOW()"
);
$stmt->execute([$threadId]);
$userTyping = (bool)$stmt->fetchColumn();

// TASK-8 START (8b — customer voice-recording indicator, user → admin)
// Task 8b addition — same query shape as userTyping above, just the
// 'user_recording' who value (see chat-typing.php). Independent row/PK
// from the plain 'user' typing row above, so both can be checked
// without interfering with each other.
$stmt = $db->prepare(
    "SELECT 1 FROM chat_typing WHERE thread_id = ? AND who = 'user_recording' AND expires_at > NOW()"
);
$stmt->execute([$threadId]);
$userRecording = (bool)$stmt->fetchColumn();
// TASK-8 END (8b — customer voice-recording indicator, user → admin)

// Stale claim check — for the staleness indicator in the admin thread list
$isStale = false;
if ($thread['status'] === 'active') {
    $hoursSinceActivity = (time() - strtotime($thread['last_activity_at'])) / 3600;
    $isStale = $hoursSinceActivity >= CHAT_STALE_CLAIM_HOURS;
}

/* BUGFIX-2 START — first flagged in Task 8's "Bugs Found Outside Task Scope":
   this endpoint's response never included a botProfile field, so a bot-authored
   message reaching the admin dashboard rendered with no name/photo (falling
   through to the generic customer-message avatar fallback) instead of the
   bot's own identity, unlike the customer-facing widget which already handles
   this correctly via chat-poll.php. Confirmed real by direct inspection of the
   original chat-admin-poll.php (no botProfile key anywhere in its response)
   and admin-dashboard.html's renderAdminMessage()/renderAdminPollResult()
   (no bot-attachment logic). Fixed by mirroring chat-poll.php's own botProfile
   block exactly — name/photoUrl only, never the message templates, and always
   included regardless of the bot's current 'active' state so a message
   already sent while the bot was active keeps showing correctly even after an
   admin later toggles the bot off (same reasoning as chat-poll.php). See the
   matching admin-dashboard.html change in renderAdminPollResult() for how
   this is consumed. */
$botProfile = chatGetBotProfile();
$botPublicInfo = [
    'name'     => $botProfile['name'] ?: 'Pet Center Bot',
    'photoUrl' => $botProfile['photoUrl'],
];
/* BUGFIX-2 END */

chatRespond([
    'success'     => true,
    'status'      => $thread['status'],
    'messages'    => $messages,
    'events'      => $events,
    'userTyping'  => $userTyping,
    /* TASK-8 START (8b — expose userRecording in the response) */
    'userRecording' => $userRecording,
    /* TASK-8 END (8b — expose userRecording in the response) */
    'isStale'     => $isStale,
    /* BUGFIX-2 START */
    'botProfile'  => $botPublicInfo,
    /* BUGFIX-2 END */
]);
