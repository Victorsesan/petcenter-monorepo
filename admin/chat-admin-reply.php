<?php
/**
 * chat-admin-reply.php
 *
 * Admin sends a reply: identical grouped multi-media send mechanism as
 * chat-send.php (Tier 1 item 12 — "this composer is identical on both
 * sides"). Same token resolution, same atomic transaction, same group_id
 * sharing. The only real differences from chat-send.php: from_type is
 * 'admin' not 'user', admin_id is recorded, and the admin must actually be
 * assigned to this thread (or it must still be in the open 'waiting' queue,
 * in which case replying also claims it — see note below).
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

$chatId         = trim($input['chatId'] ?? '');
$adminId        = (int)($input['adminId'] ?? 0);
$text           = trim($input['text'] ?? '');
$imageTokens    = $input['imageTokens'] ?? (($input['imageToken'] ?? '') !== '' ? [$input['imageToken']] : []);
$audioToken     = trim($input['audioToken'] ?? '');
$videoTokens    = $input['videoTokens'] ?? (($input['videoToken'] ?? '') !== '' ? [$input['videoToken']] : []);
$replyToId      = $input['replyToId'] ?? null;
$idempotencyKey = trim($input['idempotencyKey'] ?? '');

if (!is_array($imageTokens)) $imageTokens = [];
if (!is_array($videoTokens)) $videoTokens = [];
$imageTokens = array_slice(array_filter($imageTokens), 0, 10);
$videoTokens = array_slice(array_filter($videoTokens), 0, 5);

if ($chatId === '' || !$adminId) {
    chatRespond(['success' => false, 'error' => 'chatId and adminId are required'], 400);
}

if (!chatValidateAdminId($adminId)) {
    chatRespond(['success' => false, 'error' => 'That admin profile no longer exists or is inactive. Pick a different one.'], 400);
}

if ($text === '' && empty($imageTokens) && $audioToken === '' && empty($videoTokens)) {
    chatRespond(['success' => false, 'error' => 'Message is empty'], 400);
}

$db = chatDb();

// ── Idempotency guard, same mechanism as chat-send.php ───────────────────
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

$stmt = $db->prepare("SELECT * FROM chat_threads WHERE chat_id = ? LIMIT 1");
$stmt->execute([$chatId]);
$thread = $stmt->fetch();

if (!$thread) {
    chatRespond(['success' => false, 'error' => 'Chat not found'], 404);
}

// If the thread is still in the open queue ('waiting'), replying to it also
// claims it — same admin_joined event chat-admin-claim.php would fire, so
// an admin who jumps straight to typing a reply (rather than clicking
// "Claim" first) still gets the joined-chat banner working correctly on
// the customer side.
$justClaimed = false;
if ($thread['status'] === 'waiting') {
    $stmt = $db->prepare(
        "UPDATE chat_threads SET status = 'active', assigned_admin_id = ? WHERE chat_id = ? AND status = 'waiting'"
    );
    $stmt->execute([$adminId, $chatId]);
    if ($stmt->rowCount() > 0) {
        $db->prepare("INSERT INTO chat_events (thread_id, event_type, admin_id) VALUES (?, 'admin_joined', ?)")
           ->execute([$thread['id'], $adminId]);
        $justClaimed = true;
        $thread['status'] = 'active';
        $thread['assigned_admin_id'] = $adminId;
    } else {
        // Someone else claimed it between our SELECT and this UPDATE.
        chatRespond(['success' => false, 'error' => 'This chat was just claimed by another admin.'], 409);
    }
} elseif ((int)$thread['assigned_admin_id'] !== $adminId) {
    chatRespond(['success' => false, 'error' => 'This chat is assigned to a different admin.'], 403);
}

$mediaDir = CHAT_MEDIA_DIR . $chatId . '/';
if (!is_dir($mediaDir)) {
    @mkdir($mediaDir, 0755, true);
}

$resolved = [];
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
    "INSERT INTO chat_messages (thread_id, from_type, admin_id, msg_type, body, media_path, reply_to_id, group_id, status)
     VALUES (?, 'admin', ?, ?, ?, ?, ?, ?, 'sent')"
);

try {
    $db->beginTransaction();

    $insertedIds = [];

    if ($text !== '') {
        $insertStmt->execute([$thread['id'], $adminId, 'text', $text, null, $replyToId, $groupId]);
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
        @unlink(preg_replace('/\.[a-zA-Z0-9]+$/', '.json', $tempPath));

        $relativePath = 'chat-media/' . $chatId . '/' . $finalName;
        $insertStmt->execute([$thread['id'], $adminId, $kind, null, $relativePath, $replyToId, $groupId]);
        $insertedIds[] = $db->lastInsertId();
    }

    // Touch thread activity, flip unread for the customer
    $db->prepare("UPDATE chat_threads SET last_activity_at = NOW(), unread_for_user = 1 WHERE id = ?")
       ->execute([$thread['id']]);

    $db->commit();

} catch (Exception $e) {
    $db->rollBack();
    error_log('chat-admin-reply.php transaction failed: ' . $e->getMessage());
    chatRespond(['success' => false, 'error' => 'Failed to send reply. Please try again.'], 500);
}

chatRespond([
    'success'    => true,
    'groupId'    => $groupId,
    'messageIds' => $insertedIds,
    'justClaimed' => $justClaimed,
]);
