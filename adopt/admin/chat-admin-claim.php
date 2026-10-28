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

// TASK-2 — Rate limiting on claim attempts (Recommended Enhancement, adopted
// and modified). Reuses the existing generic chatCheckRateLimit()/
// chat_rate_limits infrastructure from chat-config.php rather than a new
// table, per the standing "never rebuild an existing reusable feature" rule
// — chat_rate_limits already accepts an arbitrary rate_key, so no schema
// change is needed here (and chat_auth is untouched, as required).
//
// Deviation from the enhancement's literal wording: this app authenticates
// with one shared ADMIN_TOKEN (see chatIsAdminAuthenticated() above) —
// adminId is only an attribution label, not a real credential boundary
// (chatValidateAdminId() and chat-admin-list.php both say so explicitly).
// Anyone holding the token can pick any active adminId per request, so a
// per-adminId-only limit is trivially evaded by rotating adminId on each
// claim. Current rate-limiting best practice for exactly this shape
// (a spoofable account dimension) is to layer a coarser IP-based limit
// alongside the account-based one, so a rotating-identity attacker still
// gets caught on the IP axis. Both are checked independently; either one
// tripping throttles the request. This is a sliding window, not a lockout —
// it recovers on its own — and its purpose per the task brief is primarily
// to leave an audit trail if a compromised token is used to rapidly claim
// many chats, not to be the security boundary itself (the token is that).
$clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$adminRateOk = chatCheckRateLimit("admin_claim:admin:$adminId", 20, 300); // 20 claims / 5 min per admin identity
$ipRateOk    = chatCheckRateLimit("admin_claim:ip:$clientIp", 40, 300);   // 40 claims / 5 min per source IP (coarser, catches identity-rotation)
if (!$adminRateOk || !$ipRateOk) {
    error_log("chat-admin-claim.php rate limit exceeded — adminId=$adminId ip=$clientIp");
    chatRespond(['success' => false, 'error' => 'Too many claim attempts. Please slow down and try again shortly.'], 429);
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
