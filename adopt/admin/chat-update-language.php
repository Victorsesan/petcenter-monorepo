<?php
/** AP4-TASK-3: update a customer's saved chat language for an existing thread. */
header('Content-Type: application/json');
error_reporting(0);
@ini_set('display_errors', 0);
require_once __DIR__ . '/chat-config.php';
require_once __DIR__ . '/includes/chat-auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') chatRespond(['success' => false, 'error' => 'POST required'], 405);
$input = chatJsonInput();
$chatId = trim($input['chatId'] ?? '');
$language = trim($input['preferredLanguage'] ?? '');
$sessionToken = trim($input['sessionToken'] ?? '');
$allowed = ['en','es','fr','de','pt','it','zh-Hans','ja','ko','ar','hi','vi','tl','ru'];
if ($chatId === '' || !in_array($language, $allowed, true)) {
    chatRespond(['success' => false, 'error' => 'A valid chatId and preferredLanguage are required'], 400);
}
// Authenticate the supplied chat ID before revealing whether a thread exists.
chatRequireSessionToken($chatId, $sessionToken);
$db = chatDb();
$stmt = $db->prepare('SELECT id FROM chat_threads WHERE chat_id = ? LIMIT 1');
$stmt->execute([$chatId]);
$threadId = $stmt->fetchColumn();
if (!$threadId) chatRespond(['success' => false, 'error' => 'Chat not found'], 404);
$db->prepare('UPDATE chat_threads SET preferred_chat_language = ? WHERE id = ?')->execute([$language, $threadId]);
chatRespond(['success' => true, 'preferredChatLanguage' => $language]);
