<?php
/**
 * chat-manage.php
 *
 * Bulk archive / unarchive / permanent-delete for chat threads. Requires
 * admin auth. Accepts an array of chat IDs for bulk actions (or a single
 * chatId for convenience).
 *
 * archive:   sets archived=1 — thread is hidden from the normal tab views
 *            (Waiting/My Active/Others Active/Done) but its data is untouched.
 * unarchive: sets archived=0 — thread returns to its normal tab based on
 *            its current status.
 * delete:    PERMANENTLY removes the thread row (CASCADE deletes its
 *            messages and events via the FK), plus deletes its media folder
 *            and any staged review JSON file. This cannot be undone.
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
if (empty($input)) $input = $_POST;

$action = trim($input['action'] ?? '');
if (!in_array($action, ['archive', 'unarchive', 'delete'], true)) {
    chatRespond(['success' => false, 'error' => "action must be 'archive', 'unarchive', or 'delete'"], 400);
}

$chatIds = $input['chatIds'] ?? ($input['chatId'] ?? null);
if (is_string($chatIds)) $chatIds = [$chatIds];
if (!is_array($chatIds) || empty($chatIds)) {
    chatRespond(['success' => false, 'error' => 'chatIds (array) or chatId (string) required'], 400);
}

$db = chatDb();
$succeeded = [];
$failed = [];

function rrmdirChat($dir) {
    if (!is_dir($dir)) return true; // nothing to remove is not a failure
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        is_dir($path) ? rrmdirChat($path) : @unlink($path);
    }
    return @rmdir($dir);
}

foreach ($chatIds as $chatId) {
    $chatId = trim((string)$chatId);
    if ($chatId === '') { $failed[] = $chatId; continue; }

    $stmt = $db->prepare("SELECT id FROM chat_threads WHERE chat_id = ? LIMIT 1");
    $stmt->execute([$chatId]);
    $threadId = $stmt->fetchColumn();

    if (!$threadId) { $failed[] = $chatId; continue; }

    if ($action === 'archive') {
        $ok = $db->prepare("UPDATE chat_threads SET archived = 1 WHERE id = ?")->execute([$threadId]);
    } elseif ($action === 'unarchive') {
        $ok = $db->prepare("UPDATE chat_threads SET archived = 0 WHERE id = ?")->execute([$threadId]);
    } else { // delete — permanent, cannot be undone
        try {
            $db->beginTransaction();
            // chat_messages and chat_events both have ON DELETE CASCADE on
            // thread_id, so deleting the thread row removes all of its
            // messages and events automatically at the database level.
            $db->prepare("DELETE FROM chat_threads WHERE id = ?")->execute([$threadId]);
            $db->commit();

            // Clean up the media folder and any saved review file — these
            // live on disk, not in the database, so the FK cascade above
            // doesn't touch them.
            $safeChatId = preg_replace('/[^A-Za-z0-9\-]/', '', $chatId);
            rrmdirChat(CHAT_MEDIA_DIR . $safeChatId);
            $reviewFile = __DIR__ . '/reviews/' . $safeChatId . '.json';
            if (file_exists($reviewFile)) @unlink($reviewFile);

            $ok = true;
        } catch (Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            $ok = false;
        }
    }

    if ($ok) $succeeded[] = $chatId;
    else $failed[] = $chatId;
}

chatRespond([
    'success'   => count($failed) === 0,
    'succeeded' => $succeeded,
    'failed'    => $failed,
]);
