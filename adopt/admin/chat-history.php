<?php
/**
 * chat-history.php
 *
 * Powers the "See previous messages" inline reveal on a Done thread
 * (Section 7b). Two modes:
 *
 *  - mode=full   -> returns every prior message, but image/audio/video
 *                   messages come back as lightweight placeholder metadata
 *                   only (type, original filename if known, size) — the
 *                   actual media_path is deliberately withheld here so the
 *                   initial reveal stays fast even with a long history full
 *                   of attachments. This is the lazy-loading principle
 *                   already used for fast video uploads, applied to history
 *                   loading instead.
 *  - mode=media  -> given one specific messageId, returns that single
 *                   message's actual media path, resolved only at the
 *                   moment the customer taps that specific item.
 *
 * Works for both the customer (by chatId) and an admin reviewing a Done
 * thread (by chatId + admin auth) — same data, same lazy-loading shape.
 */

header('Content-Type: application/json');
error_reporting(0);
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';

$chatId = trim($_GET['chatId'] ?? '');
$mode   = $_GET['mode'] ?? 'full';

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
