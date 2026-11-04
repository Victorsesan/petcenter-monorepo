<?php
/**
 * TASK-admin234-1a START — on-demand voice transcription for Admin Project 2.
 * Task 1a: customer requests an admin voice-message transcript (English model).
 * Task 1b: admin requests a customer voice-message transcript (multilingual model).
 * This endpoint caches results per message and never invokes translation.
 * TASK-admin234-1a END
 */
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);
@ini_set('display_errors', 0);
require_once __DIR__ . '/chat-config.php';
require_once __DIR__ . '/includes/chat-auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') chatRespond(['success' => false, 'error' => 'POST required'], 405);
$input = chatJsonInput();
$chatId = trim($input['chatId'] ?? '');
$messageId = (int)($input['messageId'] ?? 0);
$side = $input['side'] ?? '';
if ($chatId === '' || $messageId < 1 || !in_array($side, ['customer', 'admin'], true)) {
    chatRespond(['success' => false, 'error' => 'chatId, messageId, and a valid side are required'], 400);
}

// TASK-admin234-1a START — authenticate before querying for message/thread existence.
if ($side === 'customer') {
    chatRequireSessionToken($chatId, trim($input['sessionToken'] ?? ''));
} else {
    chatRequireAdmin();
}
// TASK-admin234-1a END

$db = chatDb();
$stmt = $db->prepare('SELECT id, preferred_chat_language FROM chat_threads WHERE chat_id = ? LIMIT 1');
$stmt->execute([$chatId]);
$thread = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$thread) chatRespond(['success' => false, 'error' => 'Chat not found'], 404);
$stmt = $db->prepare('SELECT id, from_type, msg_type, media_path, transcript, transcript_status, transcript_confidence, transcript_language, transcript_started_at FROM chat_messages WHERE id = ? AND thread_id = ? AND deleted_at IS NULL LIMIT 1');
$stmt->execute([$messageId, $thread['id']]);
$message = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$message || $message['msg_type'] !== 'audio') chatRespond(['success' => false, 'error' => 'Voice message not found'], 404);
if (($side === 'customer' && $message['from_type'] !== 'admin') || ($side === 'admin' && !in_array($message['from_type'], ['admin', 'user'], true))) {
    chatRespond(['success' => false, 'error' => 'This message is not available for transcription from this side'], 403);
}

// TASK-admin234-1a START — IP flood guard covers cache hits and pending polls too.
$ipRateKey = 'chat-transcribe-ip:' . hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
if (!chatCheckRateLimit($ipRateKey, 40, 600)) {
    chatRespond(['success' => false, 'error' => 'Too many transcription requests. Please try again in a few minutes.'], 429);
}
// TASK-admin234-1a END

if ($message['transcript_status'] === 'done' && trim((string)$message['transcript']) !== '') {
    chatRespond(['success' => true, 'status' => 'done', 'transcript' => $message['transcript'], 'confidence' => $message['transcript_confidence'] === null ? null : (float)$message['transcript_confidence'], 'language' => $message['transcript_language'], 'preferred_chat_language' => $thread['preferred_chat_language']]);
}
if ($message['transcript_status'] === 'pending' && !empty($message['transcript_started_at']) && strtotime($message['transcript_started_at']) > time() - 180) {
    chatRespond(['success' => true, 'status' => 'pending', 'preferred_chat_language' => $thread['preferred_chat_language']], 202);
}

// TASK-admin234-1a START — enforce account-wide and per-conversation quotas before provider use.
$rateKey = 'chat-transcribe:' . hash('sha256', $side . '|' . $chatId . '|' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
if (!chatCheckRateLimit('chat-transcribe-global-minute', 15, 60)
    || !chatCheckRateLimit('chat-transcribe-global-day', 1800, 86400)
    || !chatCheckRateLimit($rateKey, 15, 60)
    || !chatCheckRateLimit($rateKey . ':day', 1800, 86400)) {
    chatRespond(['success' => false, 'error' => 'Too many transcription requests. Please try again later.'], 429);
}
$apiKey = trim((string)getenv('GROQ_API_KEY'));
if ($apiKey === '') chatRespond(['success' => false, 'error' => 'Transcription is not configured on this server yet.'], 503);
if (!function_exists('curl_init')) chatRespond(['success' => false, 'error' => 'Transcription is unavailable on this server.'], 503);
// TASK-admin234-1a END

// Atomic claim: concurrent clicks share one provider request; stale claims can be retried.
$claim = $db->prepare("UPDATE chat_messages SET transcript_status='pending', transcript_started_at=NOW(), transcript=NULL, transcript_confidence=NULL, transcript_language=NULL WHERE id=? AND thread_id=? AND msg_type='audio' AND deleted_at IS NULL AND (transcript_status IN ('none','failed') OR (transcript_status='pending' AND (transcript_started_at IS NULL OR transcript_started_at < DATE_SUB(NOW(), INTERVAL 3 MINUTE))) OR (transcript_status='done' AND transcript IS NULL))");
$claim->execute([$messageId, $thread['id']]);
if ($claim->rowCount() !== 1) {
    $again = $db->prepare('SELECT transcript, transcript_status, transcript_confidence, transcript_language FROM chat_messages WHERE id=? AND thread_id=? LIMIT 1');
    $again->execute([$messageId, $thread['id']]);
    $current = $again->fetch(PDO::FETCH_ASSOC);
    if ($current && $current['transcript_status'] === 'done' && trim((string)$current['transcript']) !== '') {
        chatRespond(['success' => true, 'status' => 'done', 'transcript' => $current['transcript'], 'confidence' => $current['transcript_confidence'] === null ? null : (float)$current['transcript_confidence'], 'language' => $current['transcript_language'] ?? null, 'preferred_chat_language' => $thread['preferred_chat_language']]);
    }
    chatRespond(['success' => true, 'status' => 'pending', 'preferred_chat_language' => $thread['preferred_chat_language']], 202);
}

$relative = (string)$message['media_path'];
if (!preg_match('~^chat-media/' . preg_quote($chatId, '~') . '/[A-Za-z0-9._-]+$~', $relative)) {
    $db->prepare("UPDATE chat_messages SET transcript_status='failed', transcript_started_at=NULL WHERE id=?")->execute([$messageId]);
    chatRespond(['success' => false, 'error' => 'Voice file path is invalid'], 400);
}
$mediaRoot = realpath(CHAT_MEDIA_DIR);
$audioPath = realpath(__DIR__ . '/' . $relative);
if (!$mediaRoot || !$audioPath || strpos($audioPath, $mediaRoot . DIRECTORY_SEPARATOR) !== 0 || !is_file($audioPath)) {
    $db->prepare("UPDATE chat_messages SET transcript_status='failed', transcript_started_at=NULL WHERE id=?")->execute([$messageId]);
    chatRespond(['success' => false, 'error' => 'Voice file is unavailable for transcription'], 404);
}
if (filesize($audioPath) > 25 * 1024 * 1024) {
    $db->prepare("UPDATE chat_messages SET transcript_status='failed', transcript_started_at=NULL WHERE id=?")->execute([$messageId]);
    chatRespond(['success' => false, 'error' => 'Voice file exceeds the transcription service free-plan size limit'], 413);
}

// TASK-admin234-1a START — English-only speech direction (admin → customer).
if ($message['from_type'] === 'admin') {
    $model = 'whisper-large-v3-turbo';
    $post = ['file' => new CURLFile($audioPath), 'model' => $model, 'response_format' => 'verbose_json', 'language' => 'en'];
} else {
    /* TASK-admin234-1b START — multilingual customer → admin; let Whisper auto-detect. */
    $model = 'whisper-large-v3';
    $post = ['file' => new CURLFile($audioPath), 'model' => $model, 'response_format' => 'verbose_json'];
    /* TASK-admin234-1b END */
}
$curl = curl_init('https://api.groq.com/openai/v1/audio/transcriptions');
curl_setopt_array($curl, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $post,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $apiKey],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_SSL_VERIFYPEER => true,
]);
$raw = curl_exec($curl);
$statusCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
$curlError = curl_error($curl);
curl_close($curl);
$decoded = is_string($raw) ? json_decode($raw, true) : null;
if ($raw === false || $statusCode !== 200 || !is_array($decoded) || trim((string)($decoded['text'] ?? '')) === '') {
    // Do not log provider response bodies; they can contain audio-derived private data.
    error_log('chat-transcribe provider failure: HTTP ' . $statusCode . ($curlError !== '' ? ', cURL failure' : ''));
    $db->prepare("UPDATE chat_messages SET transcript_status='failed', transcript_started_at=NULL WHERE id=? AND transcript_status='pending'")->execute([$messageId]);
    chatRespond(['success' => false, 'error' => 'The voice message could not be transcribed. Please use its audio playback instead.'], 502);
}
$text = trim((string)$decoded['text']);
$probabilities = [];
foreach (($decoded['segments'] ?? []) as $segment) if (isset($segment['avg_logprob'])) $probabilities[] = exp((float)$segment['avg_logprob']);
$confidence = $probabilities ? max(0.0, min(1.0, round(array_sum($probabilities) / count($probabilities), 3))) : null;
$detectedLanguage = isset($decoded['language']) ? substr((string)$decoded['language'], 0, 16) : null;
$save = $db->prepare("UPDATE chat_messages SET transcript=?, transcript_status='done', transcript_confidence=?, transcript_language=?, transcript_started_at=NULL WHERE id=? AND transcript_status='pending'");
$save->execute([$text, $confidence, $detectedLanguage, $messageId]);
if ($save->rowCount() !== 1) chatRespond(['success' => false, 'error' => 'The transcript could not be saved. Please retry.'], 409);
chatRespond(['success' => true, 'status' => 'done', 'transcript' => $text, 'confidence' => $confidence, 'language' => $detectedLanguage, 'preferred_chat_language' => $thread['preferred_chat_language']]);
// TASK-admin234-1a END
