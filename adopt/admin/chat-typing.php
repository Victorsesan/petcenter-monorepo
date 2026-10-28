<?php
/**
 * chat-typing.php
 *
 * Lightweight typing-indicator ping, both directions (Tier 2 7a). Called
 * repeatedly (every couple seconds) while someone is actively typing in a
 * thread; the receiving side's chat-poll.php / chat-admin-poll.php checks
 * for a fresh, unexpired row to show "X is typing...".
 *
 * No explicit "stop typing" call needed — the row just naturally expires
 * a few seconds after the last ping, which is simpler and more reliable
 * than trying to catch every way a user can stop typing without telling us
 * (closing the tab, switching threads, losing connection mid-type).
 */

header('Content-Type: application/json');
error_reporting(0);
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';
/* TASK-1 START — session-token base layer */
require_once __DIR__ . '/includes/chat-auth.php';
/* TASK-1 END */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    chatRespond(['success' => false, 'error' => 'POST required'], 405);
}

$input = chatJsonInput();
if (empty($input)) {
    $input = $_POST;
}

$chatId = trim($input['chatId'] ?? '');
// TASK-8 START (8b — accept voice-recording-in-progress ping values)
// 'user' / 'admin' = plain typing (pre-existing). 'user_recording' /
// 'admin_recording' = Task 8b addition — same upsert mechanism, just a
// distinct value so a simultaneous "typing" and "recording" ping for the
// same side can't clobber each other (the table's PK is (thread_id, who),
// so each of these four values gets its own independent, independently-
// expiring row). NOTE: chat_typing.who IS a strict MySQL ENUM — see
// BUGFIX-1 in chat-setup.php / chat-migrate.php, which widens it to
// include these two new values. On any database that hasn't had that
// migration run, an INSERT with who='user_recording'/'admin_recording'
// will fail (or be silently coerced, depending on SQL mode) and this
// endpoint will error out for those two values specifically — 'user'/
// 'admin' pings are unaffected either way.
$who = trim($input['who'] ?? '');
$validWho = ['user', 'admin', 'user_recording', 'admin_recording'];

if ($chatId === '' || !in_array($who, $validWho, true)) {
    chatRespond(['success' => false, 'error' => 'chatId and a valid who (user/admin/user_recording/admin_recording) are required'], 400);
}

// Admin-side pings (typing or recording) should be authenticated, same as
// any other admin action — a customer pretending to be "admin" typing/
// recording would be a minor but pointless spoof, cheap to block here.
if ($who === 'admin' || $who === 'admin_recording') {
    chatRequireAdmin();
}
// TASK-8 END (8b — accept voice-recording-in-progress ping values)

/* TASK-1 START — customer-side pings (who=user/user_recording) now require
   the same session token every other customer-facing endpoint requires.
   Previously these were the one class of write against a real, existing
   thread that needed no proof of anything beyond a guessed chatId — a
   typing/recording ping doesn't leak conversation content by itself, but
   letting an unauthenticated caller write into chat_typing for any known
   chatId was still a real gap in the "chatId is a lookup, not an
   authorization" fix this task exists to close. */
if ($who === 'user' || $who === 'user_recording') {
    $sessionToken = trim($input['sessionToken'] ?? '');
    chatRequireSessionToken($chatId, $sessionToken);
}
/* TASK-1 END */

$db = chatDb();

$stmt = $db->prepare("SELECT id FROM chat_threads WHERE chat_id = ? LIMIT 1");
$stmt->execute([$chatId]);
$threadId = $stmt->fetchColumn();

if (!$threadId) {
    chatRespond(['success' => false, 'error' => 'Chat not found'], 404);
}

// Upsert: ping refreshes the expiry, doesn't pile up duplicate rows
$stmt = $db->prepare(
    "INSERT INTO chat_typing (thread_id, who, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 6 SECOND))
     ON DUPLICATE KEY UPDATE expires_at = DATE_ADD(NOW(), INTERVAL 6 SECOND)"
);
$stmt->execute([$threadId, $who]);

chatRespond(['success' => true]);
