<?php
/**
 * chat-rate.php
 * Submit or retrieve admin ratings from users after a chat is marked Done.
 * Saves to /admin/reviews/{chatId}.json on disk.
 */
header('Content-Type: application/json');
error_reporting(0);
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';
/* TASK-1 START — session-token base layer */
require_once __DIR__ . '/includes/chat-auth.php';
/* TASK-1 END */

$reviewsDir = __DIR__ . '/reviews/';
if (!is_dir($reviewsDir)) @mkdir($reviewsDir, 0755, true);

/**
 * Append one review while holding an exclusive file lock across the full
 * read/modify/write cycle. A write-only LOCK_EX is not enough here: two
 * simultaneous requests could both read the same old array and lose one
 * review when they subsequently write it.
 */
function chatAppendReviewAtomically(string $file, array $entry): bool {
    $handle = @fopen($file, 'c+');
    if ($handle === false || !@flock($handle, LOCK_EX)) {
        if (is_resource($handle)) @fclose($handle);
        return false;
    }

    rewind($handle);
    $raw = stream_get_contents($handle);
    $reviews = json_decode($raw ?: '', true);
    if (!is_array($reviews)) $reviews = [];
    $reviews[] = $entry;
    $json = json_encode($reviews, JSON_PRETTY_PRINT);
    $ok = $json !== false
        && ftruncate($handle, 0)
        && rewind($handle)
        && fwrite($handle, $json) === strlen($json)
        && fflush($handle);

    @flock($handle, LOCK_UN);
    @fclose($handle);
    return $ok;
}

$action = $_REQUEST['action'] ?? 'submit';

// ── Submit a rating ─────────────────────────────────────────────────────
if ($action === 'submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = chatJsonInput();
    if (empty($input)) $input = $_POST;

    $chatId    = trim($input['chatId']   ?? '');
    $email     = trim($input['email']    ?? '');
    $adminId   = (int)($input['adminId'] ?? 0);
    $adminName = trim($input['adminName'] ?? '');
    $rating    = (int)($input['rating']  ?? 0);
    $comment   = mb_substr(trim($input['comment'] ?? ''), 0, 500);

    if ($chatId === '' || !chatValidEmail($email)) {
        chatRespond(['success' => false, 'error' => 'chatId and valid email are required'], 400);
    }
    if ($rating < 1 || $rating > 5) {
        chatRespond(['success' => false, 'error' => 'Rating must be 1–5'], 400);
    }

    /* TASK-1 START — submitting a rating now requires a valid session token
       for this exact chatId, same as every other customer-facing write in
       this task. The stored-thread-email ownership check immediately below
       is an additional integrity guard: a valid token must not be paired
       with a different syntactically valid email address. */
    $sessionToken = trim($input['sessionToken'] ?? '');
    chatRequireSessionToken($chatId, $sessionToken);
    /* BUGFIX-MANUS-09-1-START — a valid session token must not be paired
       with an unrelated syntactically valid email address. */
    $threadEmailStmt = chatDb()->prepare(
        "SELECT email FROM chat_threads WHERE chat_id = ? LIMIT 1"
    );
    $threadEmailStmt->execute([$chatId]);
    $storedEmail = $threadEmailStmt->fetchColumn();
    if ($storedEmail === false || strcasecmp(trim((string)$storedEmail), $email) !== 0) {
        chatRespond(['success' => false, 'error' => 'Email does not match this chat'], 403);
    }
    /* BUGFIX-MANUS-09-1-END */
    /* TASK-1 END */

    /* BUGFIX-MANUS-01-1-START — keep a valid session token from being paired
       with an unrelated syntactically valid email when saving a rating. */
    $threadEmailStmt = chatDb()->prepare(
        "SELECT email FROM chat_threads WHERE chat_id = ? LIMIT 1"
    );
    $threadEmailStmt->execute([$chatId]);
    $storedEmail = $threadEmailStmt->fetchColumn();
    if ($storedEmail === false || strcasecmp(trim((string)$storedEmail), $email) !== 0) {
        chatRespond(['success' => false, 'error' => 'Email does not match this chat'], 403);
    }
    /* BUGFIX-MANUS-01-1-END */

    $file = $reviewsDir . preg_replace('/[^A-Za-z0-9\-]/', '', $chatId) . '.json';
    $entry = [
        'chatId'    => $chatId,
        'email'     => $email,
        'adminId'   => $adminId,
        'adminName' => $adminName,
        'rating'    => $rating,
        'comment'   => $comment,
        'createdAt' => date('Y-m-d H:i:s'),
    ];
    if (!chatAppendReviewAtomically($file, $entry)) {
        chatRespond(['success' => false, 'error' => 'Failed to save review'], 500);
    }

    // Also insert into DB for dashboard queries
    try {
        $db = chatDb();
        $db->prepare("INSERT INTO chat_ratings (chat_id, email, admin_id, admin_name, rating, comment)
                      VALUES (?, ?, ?, ?, ?, ?)")
           ->execute([$chatId, $email, $adminId ?: null, $adminName ?: null, $rating, $comment]);
    } catch (Exception $e) {
        // Non-fatal — JSON file already saved
        error_log('chat-rate.php DB insert failed: ' . $e->getMessage());
    }

    chatRespond(['success' => true]);
}

// ── List all reviews (admin only) ───────────────────────────────────────
if ($action === 'list') {
    chatRequireAdmin();
    $files = glob($reviewsDir . '*.json') ?: [];
    $all = [];
    foreach ($files as $f) {
        $data = json_decode(file_get_contents($f), true) ?: [];
        foreach ($data as $r) $all[] = $r;
    }
    usort($all, fn($a, $b) => strcmp($b['createdAt'] ?? '', $a['createdAt'] ?? ''));
    chatRespond(['success' => true, 'reviews' => $all]);
}

// ── Get one review file (admin only) ────────────────────────────────────
if ($action === 'get') {
    chatRequireAdmin();
    $chatId = preg_replace('/[^A-Za-z0-9\-]/', '', trim($_GET['chatId'] ?? ''));
    $file = $reviewsDir . $chatId . '.json';
    if (!file_exists($file)) chatRespond(['success' => false, 'error' => 'Not found'], 404);
    chatRespond(['success' => true, 'reviews' => json_decode(file_get_contents($file), true) ?: []]);
}

// ── Delete one review file (admin only) ─────────────────────────────────
if ($action === 'delete') {
    chatRequireAdmin();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') chatRespond(['success' => false, 'error' => 'POST required'], 405);
    $input = chatJsonInput();
    if (empty($input)) $input = $_POST;
    $chatId = preg_replace('/[^A-Za-z0-9\-]/', '', trim($input['chatId'] ?? ''));
    $file = $reviewsDir . $chatId . '.json';
    if (file_exists($file)) @unlink($file);
    chatRespond(['success' => true]);
}

chatRespond(['success' => false, 'error' => 'Unknown action'], 400);
