<?php
/**
 * chat-send.php
 *
 * Customer sends a message turn: any combination of text, an image token,
 * an audio token, and a video token, submitted together from one Send
 * action. Written as one or more chat_messages rows sharing a single
 * group_id (Tier 1 item 12 — grouped multi-media send), inside one DB
 * transaction so the group is atomic: either the whole turn is saved, or
 * none of it is.
 *
 * Each token (image/audio/video) refers to a file already staged by
 * chat-upload-temp.php (image/audio) or upload-video-chunk.php's finalize
 * step (video) — this endpoint's job is only to move that staged file into
 * the thread's permanent chat-media folder and record it, never to handle
 * raw file uploads itself.
 */

header('Content-Type: application/json');
error_reporting(0);
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';
// TASK 11: live chat email notifications — see chat-mail.php for the two
// exact trigger scenarios (this file fires chatNotifyReopenedThread() at the
// done->waiting state transition below, not after the message finishes
// saving — see the inline comment at that call site for why).
require_once __DIR__ . '/chat-mail.php';
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

$chatId         = trim($input['chatId'] ?? '');
$text           = trim($input['text'] ?? '');
// Accept both old single-token and new array formats
$imageTokens    = $input['imageTokens'] ?? (($input['imageToken'] ?? '') !== '' ? [$input['imageToken']] : []);
$audioToken     = trim($input['audioToken'] ?? '');
$videoTokens    = $input['videoTokens'] ?? (($input['videoToken'] ?? '') !== '' ? [$input['videoToken']] : []);
$replyToId      = $input['replyToId'] ?? null;
$idempotencyKey = trim($input['idempotencyKey'] ?? '');
/* TASK-1 START */
$sessionToken   = trim($input['sessionToken'] ?? '');
/* TASK-1 END */

// Ensure arrays
if (!is_array($imageTokens)) $imageTokens = [];
if (!is_array($videoTokens)) $videoTokens = [];
// Enforce limits server-side
$imageTokens = array_slice(array_filter($imageTokens), 0, 10);
$videoTokens = array_slice(array_filter($videoTokens), 0, 5);

if ($chatId === '') {
    chatRespond(['success' => false, 'error' => 'chatId is required'], 400);
}

$db = chatDb();

// ── Idempotency guard against accidental double-sends ───────────────────
if ($idempotencyKey !== '') {
    $stmt = $db->prepare(
        "SELECT id FROM chat_messages WHERE thread_id = (SELECT id FROM chat_threads WHERE chat_id = ?)
         AND group_id = ? LIMIT 1"
    );
    $stmt->execute([$chatId, $idempotencyKey]);
    if ($stmt->fetch()) {
        chatRespond(['success' => true, 'duplicate' => true, 'groupId' => $idempotencyKey]);
    }
}

// A turn needs at least ONE piece of content
if ($text === '' && empty($imageTokens) && $audioToken === '' && empty($videoTokens)) {
    chatRespond(['success' => false, 'error' => 'Message is empty'], 400);
}

$stmt = $db->prepare("SELECT * FROM chat_threads WHERE chat_id = ? LIMIT 1");
$stmt->execute([$chatId]);
$thread = $stmt->fetch();

if (!$thread) {
    chatRespond(['success' => false, 'error' => 'Chat not found'], 404);
}

/* TASK-1 START — this was the customer's own message-send endpoint, and
   previously the single biggest piece of the vulnerability this task
   exists to close: anyone who guessed/enumerated a chatId could send
   messages into that customer's conversation with no proof of ownership
   at all. Enforced here, right after the thread lookup (so an unknown
   chatId still gets the existing, more specific 404) and before anything
   below reads or mutates thread/message state. */
chatRequireSessionToken($chatId, $sessionToken);
/* TASK-1 END */

$wasReopenedFromDone = false;
if ($thread['status'] === 'done') {
    // Sending into a Done thread re-opens it into the waiting queue.
    // Per-reason welcome messages (Scenario B) are handled entirely in
    // chat-start.php now — fired only when the customer goes back through
    // the pre-chat gate and picks a DIFFERENT reason than the thread's
    // original one. This endpoint (the customer's actual message arriving)
    // is where the separate "Scenario B customer's first message" response
    // belongs instead — see below, after the customer's message is inserted.
    $db->prepare("UPDATE chat_threads SET status = 'waiting', assigned_admin_id = NULL WHERE id = ?")
       ->execute([$thread['id']]);
    $wasReopenedFromDone = true;

    // TASK 11: Scenario 2 of the two email-notification triggers — admin-only
    // notification that a done thread was just reopened. Fired HERE, right
    // at the actual state transition, not after the message below finishes
    // saving: the UPDATE above is a standalone statement that commits
    // immediately, independent of the transaction further down in this file.
    // If it were gated behind that later transaction instead, an expired/
    // invalid upload token (chatResolveStagedToken() below can exit early
    // via chatRespond()) would skip the notification entirely — and since
    // the thread is already 'waiting' by then, no later request could ever
    // re-trigger it for this reopen event. Firing here guarantees exactly
    // one notification per genuine done-to-waiting transition, matching
    // what actually happened in the database, regardless of whether this
    // specific message payload goes on to save successfully.
    chatNotifyReopenedThread([
        'chat_id'   => $chatId,
        'email'     => $thread['email'],
        'full_name' => $thread['full_name'],
        'reason'    => $thread['reason'],
    ]);
}

$mediaDir = CHAT_MEDIA_DIR . $chatId . '/';
if (!is_dir($mediaDir)) {
    @mkdir($mediaDir, 0755, true);
}

// Detect whether this is the customer's first-ever message in this thread —
// checked BEFORE this send's own insert, so the count reflects prior state
// only. Used below to fire the "we've received your message" bot ack,
// exactly once per thread, right after the very first customer message
// (whether this thread was brand new or just re-opened from Done — in the
// re-open case the reopen message already covers "welcome back", so this
// ack only fires for a thread's actual first-ever customer message).
$stmtCount = $db->prepare("SELECT COUNT(*) FROM chat_messages WHERE thread_id = ? AND from_type = 'user'");
$stmtCount->execute([$thread['id']]);
$isFirstUserMessage = ((int)$stmtCount->fetchColumn() === 0);

// ── Resolve all tokens to staged temp files BEFORE the transaction ──────
$resolved = []; // [ ['kind'=>'image','path'=>'...'], ... ]

foreach ($imageTokens as $tok) {
    $path = chatResolveStagedToken($tok);
    if (!$path) chatRespond(['success' => false, 'error' => "Image upload not found or expired (token: $tok)"], 410);
    $resolved[] = ['kind' => 'image', 'path' => $path];
}
if ($audioToken !== '') {
    $path = chatResolveStagedToken($audioToken);
    if (!$path) chatRespond(['success' => false, 'error' => "Audio upload not found or expired"], 410);
    $resolved[] = ['kind' => 'audio', 'path' => $path];
}
foreach ($videoTokens as $tok) {
    $path = chatResolveStagedToken($tok);
    if (!$path) chatRespond(['success' => false, 'error' => "Video upload not found or expired (token: $tok)"], 410);
    $resolved[] = ['kind' => 'video', 'path' => $path];
}

$groupId = $idempotencyKey !== '' ? $idempotencyKey : chatGenerateGroupId();
$insertStmt = $db->prepare(
    "INSERT INTO chat_messages (thread_id, from_type, msg_type, body, media_path, reply_to_id, group_id, status)
     VALUES (?, 'user', ?, ?, ?, ?, ?, 'sent')"
);

try {
    $db->beginTransaction();

    $insertedIds = [];

    if ($text !== '') {
        $insertStmt->execute([$thread['id'], 'text', $text, null, $replyToId, $groupId]);
        $insertedIds[] = $db->lastInsertId();
    }

    foreach ($resolved as $item) {
        $kind     = $item['kind'];
        $tempPath = $item['path'];
        $ext = pathinfo($tempPath, PATHINFO_EXTENSION);
        $finalName = $kind . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $finalPath = $mediaDir . $finalName;

        if (!chatMoveStagedFile($tempPath, $finalPath)) {
            throw new Exception("Failed to move staged $kind file into thread folder");
        }
        @unlink(preg_replace('/\.[a-zA-Z0-9]+$/', '.json', $tempPath)); // clean up the sidecar

        $relativePath = 'chat-media/' . $chatId . '/' . $finalName;
        $insertStmt->execute([$thread['id'], $kind, null, $relativePath, $replyToId, $groupId]);
        $insertedIds[] = $db->lastInsertId();
    }

    // Touch thread activity + unread flag for admins
    $db->prepare("UPDATE chat_threads SET last_activity_at = NOW(), unread_for_admin = 1 WHERE id = ?")
       ->execute([$thread['id']]);

    // v2.0 Scenario A: bot acknowledges the customer's first-ever message in
    // a brand-new thread — fired once, right after their first real message.
    if ($isFirstUserMessage && !$wasReopenedFromDone) {
        $botProfile = chatGetBotProfile();
        if (!empty($botProfile['active']) && !empty($botProfile['firstMessageAck'])) {
            $ackBody = chatBotSubstitute($botProfile['firstMessageAck'], $thread['full_name']);
            $db->prepare("INSERT INTO chat_messages (thread_id, from_type, msg_type, body, status) VALUES (?, 'bot', 'text', ?, 'sent')")
               ->execute([$thread['id'], $ackBody]);
        }
    }

    // v2.0 Scenario B: bot's OWN separate first-message-ack, fired when the
    // customer's message just reopened a done thread — this is a distinct
    // field from Scenario A's, per the explicit requirement that the two
    // can't share the same message. Fires every time a done thread is
    // reopened by a message (regardless of whether the reason changed —
    // the reason-change welcome message, if any, already fired separately
    // in chat-start.php before this request even happened).
    if ($wasReopenedFromDone) {
        $botProfile = chatGetBotProfile();
        if (!empty($botProfile['active']) && !empty($botProfile['reopenFirstMessageAck'])) {
            $ackBody = chatBotSubstitute($botProfile['reopenFirstMessageAck'], $thread['full_name']);
            $db->prepare("INSERT INTO chat_messages (thread_id, from_type, msg_type, body, status) VALUES (?, 'bot', 'text', ?, 'sent')")
               ->execute([$thread['id'], $ackBody]);
        }
    }

    // Clear any draft now that the message actually sent (Tier 1 item 14)
    $db->prepare("DELETE FROM chat_drafts WHERE draft_key = ?")->execute([$chatId]);

    $db->commit();

} catch (Exception $e) {
    $db->rollBack();
    error_log('chat-send.php transaction failed: ' . $e->getMessage());
    chatRespond(['success' => false, 'error' => 'Failed to send message. Please try again.'], 500);
}

chatRespond([
    'success'   => true,
    'groupId'   => $groupId,
    'messageIds' => $insertedIds,
]);
