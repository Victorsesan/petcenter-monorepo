<?php
/**
 * chat-history.php
 *
 * TASK-1 (second verification round) — corrected header: the two
 * paragraphs below used to claim this powers a "See previous messages"
 * inline reveal with dual customer/admin usage. That's stale —
 * ADMIN_DASHBOARD_PROJECT_SUMMARY.md's current, authoritative section for
 * this file confirms it actually powers "Download transcript" only,
 * customer-side exclusively (downloadTranscript() in chat-widget.js), with
 * the same no-admin-usage, chatId-is-the-credential trust model as
 * chat-poll.php. Corrected here rather than left standing, since it
 * directly contradicts this file's own TASK-1 enforcement block below.
 *
 * Powers the widget's "Download transcript" feature. Two modes:
 *
 *  - mode=full   -> returns every prior message, but image/audio/video
 *                   messages come back as lightweight placeholder metadata
 *                   only (type, original filename if known, size) — the
 *                   actual media_path is deliberately withheld here so the
 *                   response stays fast even with a long history full
 *                   of attachments. This is the lazy-loading principle
 *                   already used for fast video uploads, applied to history
 *                   loading instead. This is the only mode actually used
 *                   end-to-end.
 *  - mode=media  -> given one specific messageId, returns that single
 *                   message's actual media path. Not currently wired to
 *                   any UI control in either the widget or the dashboard.
 *
 * Customer-side only, no admin usage — chatId is the credential, same
 * trust model as chat-poll.php.
 */

header('Content-Type: application/json');
error_reporting(0);
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';
/* TASK-1 START — session-token base layer */
require_once __DIR__ . '/includes/chat-auth.php';
/* TASK-1 END */

$chatId = trim($_GET['chatId'] ?? '');
$mode   = $_GET['mode'] ?? 'full';
/* TASK-1 START */
$sessionToken = trim($_GET['sessionToken'] ?? '');
/* TASK-1 END */

if ($chatId === '') {
    chatRespond(['success' => false, 'error' => 'chatId is required'], 400);
}

$db = chatDb();

$stmt = $db->prepare("SELECT id FROM chat_threads WHERE chat_id = ? LIMIT 1");
$stmt->execute([$chatId]);
$threadId = $stmt->fetchColumn();

if (!$threadId) {
    chatRespond(['success' => false, 'error' => 'Chat not found'], 404);
}

/* TASK-1 START (revised — second verification round)
   This file's own header comment above (lines 5-21) claims dual
   customer/admin usage and describes a "See previous messages" inline
   reveal. Both claims are stale: ADMIN_DASHBOARD_PROJECT_SUMMARY.md's
   current, authoritative chat-history.php section explicitly supersedes
   that description — this endpoint actually powers "Download transcript"
   only, is driven exclusively by downloadTranscript() in chat-widget.js
   (the customer widget), and is documented as "no auth (chatId is the
   credential), same trust model as chat-poll.php" with no admin usage
   anywhere in the shipped code. The first version of this fix trusted the
   file's own (stale) header comment and added a chatIsAdminAuthenticated()
   bypass here; that was wrong and has been removed. Session-token
   enforcement is now unconditional, matching chat-poll.php's own
   TASK-1 block exactly — this file has the same trust model, so it gets
   the same fix, nothing more. */
chatRequireSessionToken($chatId, $sessionToken);
/* TASK-1 END */

if ($mode === 'full') {

    $stmt = $db->prepare(
        "SELECT m.*, a.name AS admin_name, a.position AS admin_position, a.photo_url AS admin_photo, a.blue_check AS admin_blue_check
         FROM chat_messages m
         LEFT JOIN chat_admin_profiles a ON a.id = m.admin_id
         WHERE m.thread_id = ? AND m.deleted_at IS NULL
         ORDER BY m.id ASC"
    );
    $stmt->execute([$threadId]);
    $rows = $stmt->fetchAll();

    // Strip media_path down to a lightweight placeholder for non-text messages —
    // the actual path is only revealed via mode=media, one item at a time.
    $messages = array_map(function ($row) {
        if ($row['msg_type'] !== 'text' && $row['media_path']) {
            $row['media_placeholder'] = [
                'type' => $row['msg_type'],
                'filename' => basename($row['media_path']),
            ];
            $row['media_path'] = null; // withheld until individually requested
        }
        return $row;
    }, $rows);

    chatRespond(['success' => true, 'messages' => $messages]);

} elseif ($mode === 'media') {

    $messageId = (int)($_GET['messageId'] ?? 0);
    if (!$messageId) {
        chatRespond(['success' => false, 'error' => 'messageId is required'], 400);
    }

    $stmt = $db->prepare(
        "SELECT media_path, msg_type FROM chat_messages WHERE id = ? AND thread_id = ? AND deleted_at IS NULL"
    );
    $stmt->execute([$messageId, $threadId]);
    $row = $stmt->fetch();

    if (!$row || !$row['media_path']) {
        chatRespond(['success' => false, 'error' => 'Media not found'], 404);
    }

    chatRespond(['success' => true, 'mediaPath' => $row['media_path'], 'msgType' => $row['msg_type']]);

} else {
    chatRespond(['success' => false, 'error' => "mode must be 'full' or 'media'"], 400);
}
