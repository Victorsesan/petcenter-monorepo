<?php
/**
 * chat-draft.php
 *
 * Save or restore an in-progress, unsent message draft (Tier 1 item 14).
 *
 * The 10-second "stopped typing" debounce is entirely client-side timing
 * logic in chat-widget.js — this endpoint doesn't know or care about typing
 * pauses, it just does a plain upsert (action=save) or fetch (action=get)
 * against chat_drafts, keyed by whatever draftKey the client sends.
 *
 * draftKey is either a real chat_id (once a thread exists) or a temporary
 * client-generated UUID (before the first message has created a thread —
 * Section 4's "drafts and the pre-thread window" note). This endpoint
 * treats both identically; the handoff between the two keys is a client-side
 * concern, not something this file needs to reason about.
 */

header('Content-Type: application/json');
error_reporting(0);
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';

$action = $_REQUEST['action'] ?? '';
$draftKey = trim($_REQUEST['draftKey'] ?? '');

if ($draftKey === '' || mb_strlen($draftKey) > 64) {
    chatRespond(['success' => false, 'error' => 'A valid draftKey is required'], 400);
}

$db = chatDb();

if ($action === 'save') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        chatRespond(['success' => false, 'error' => 'POST required'], 405);
    }

    $input = chatJsonInput();
    if (empty($input)) {
        $input = $_POST;
    }
    $body = trim($input['body'] ?? '');

    if ($body === '') {
        // An empty draft means "nothing to save" — just delete any existing
        // one rather than storing a blank row.
        $db->prepare("DELETE FROM chat_drafts WHERE draft_key = ?")->execute([$draftKey]);
        chatRespond(['success' => true, 'cleared' => true]);
    }

    $stmt = $db->prepare(
        "INSERT INTO chat_drafts (draft_key, body) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE body = VALUES(body), updated_at = CURRENT_TIMESTAMP"
    );
    $stmt->execute([$draftKey, $body]);

    chatRespond(['success' => true]);

} elseif ($action === 'get') {
    $stmt = $db->prepare("SELECT body, updated_at FROM chat_drafts WHERE draft_key = ?");
    $stmt->execute([$draftKey]);
    $draft = $stmt->fetch();

    chatRespond([
        'success' => true,
        'draft'   => $draft ? $draft['body'] : null,
        'updatedAt' => $draft ? $draft['updated_at'] : null,
    ]);

} elseif ($action === 'clear') {
    // Explicit clear, used by the client right after a successful send as a
    // belt-and-suspenders alongside chat-send.php's own draft deletion
    // (covers the pre-thread-UUID case, which chat-send.php doesn't know
    // about since it only ever deletes by real chat_id).
    $db->prepare("DELETE FROM chat_drafts WHERE draft_key = ?")->execute([$draftKey]);
    chatRespond(['success' => true]);

} else {
    chatRespond(['success' => false, 'error' => "action must be 'save', 'get', or 'clear'"], 400);
}
