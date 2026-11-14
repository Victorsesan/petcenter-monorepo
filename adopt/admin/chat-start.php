<?php
/**
 * chat-start.php
 *
 * Pre-chat gate handler. Called when a customer submits the "Chat Live"
 * form: name, email, reason, optional chat ID.
 *
 * Three possible outcomes, by design (P2P-CHAT-WIDGET-PLAN.md Sections 6 & 8):
 *  1. Explicit chat ID given, thread exists           -> resume that exact thread (any status,
 *                                                         including 'done' — they asked for it by name)
 *  2. No chat ID, but email matches a waiting/active   -> resume that thread automatically
 *     thread
 *  3. No match (new email, or email's only thread is   -> create a brand-new thread. If the only
 *     'done')                                             prior thread for this email was 'done',
 *                                                          link previous_chat_id back to it.
 *
 * A thread row is only ever created here, and only once — never speculatively
 * from a filled-but-unsubmitted form (no-orphan-threads rule, Section 4/7b).
 * The system auto-response (Section 8 reason map) is inserted as the first
 * message at the moment of creation, so it's real thread history, not a
 * client-side illusion.
 */

header('Content-Type: application/json');
error_reporting(0);
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';
// TASK 11: live chat email notifications — see chat-mail.php for the two
// exact trigger scenarios (this file fires chatNotifyNewThread() below, at
// Path 3, the only place a genuinely new thread is created).
require_once __DIR__ . '/chat-mail.php';
/* TASK-1 START — session-token base layer
   chat-start.php is the single point where email+chatID lookup succeeds
   and a session token gets issued for the resulting thread. Every path
   below (explicit chatId resume, email-match resume, brand-new thread)
   now returns a sessionToken the client must send on every subsequent
   read/write call against that chat_id — see includes/chat-auth.php for
   why this closes the "email+chatID is a bare lookup, not a secret"
   vulnerability this task exists to fix. */
require_once __DIR__ . '/includes/chat-auth.php';
/* TASK-1 END */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    chatRespond(['success' => false, 'error' => 'POST required'], 405);
}

$input = chatJsonInput();
if (empty($input)) {
    $input = $_POST; // allow regular form POST too, not just JSON
}

$fullName  = trim($input['fullName'] ?? '');
$email     = trim($input['email'] ?? '');
$reason    = trim($input['reason'] ?? '');
$chatId    = trim($input['chatId'] ?? '');
$petContext = $input['petContext'] ?? null; // { name, petId, thumbUrl, pageUrl } or null, Section 7b
// AP4-TASK-3: exact supported language contract; never store arbitrary client text.
$ap4Languages = ['en','es','fr','de','pt','it','zh-Hans','ja','ko','ar','hi','vi','tl','ru'];
$rawPreferredChatLanguage = $input['preferred_chat_language'] ?? null;
$hasPreferredChatLanguage = is_string($rawPreferredChatLanguage) && trim($rawPreferredChatLanguage) !== '';
$preferredChatLanguage = $hasPreferredChatLanguage ? trim($rawPreferredChatLanguage) : 'en';
if (!in_array($preferredChatLanguage, $ap4Languages, true)) {
    $preferredChatLanguage = 'en';
    $hasPreferredChatLanguage = false;
}
$initialTier = trim((string)($input['authTier'] ?? ''));
$initialSecret = (string)($input['authSecret'] ?? '');
$deviceToken = (string)($input['deviceToken'] ?? '');

/* TASK-3 START — centralize the resume decision so both lookup paths have the
   same PIN/password gate, remembered-device behavior, and fresh-token rule. */
function chatStartAuthPayload($thread, $deviceToken) {
    $auth = chatAuthEnsure($thread['chat_id']);
    if ($auth['auth_tier'] !== 'none' && !chatAuthTrustedDevice($thread['chat_id'], $deviceToken)) {
        return ['authRequired'=>true, 'authTier'=>$auth['auth_tier'], 'sessionToken'=>null];
    }
    return ['authRequired'=>false, 'authTier'=>$auth['auth_tier'], 'sessionToken'=>issue_token($thread['chat_id'])];
}
/* TASK-3 END */

// ── Validation ───────────────────────────────────────────────────────────
if ($fullName === '' || mb_strlen($fullName) > 255) {
    chatRespond(['success' => false, 'error' => 'A valid name is required.']);
}
if (!chatValidEmail($email)) {
    chatRespond(['success' => false, 'error' => 'A valid email is required.']);
}
if (!chatValidReason($reason)) {
    chatRespond(['success' => false, 'error' => 'Please select a reason for contacting us.']);
}
/* TASK-3 SECURITY START — validate the optional initial secret before the
   thread insert. This prevents a bad PIN/password from creating an orphan
   conversation that can never complete its requested setup. */
if ($initialTier !== '') {
    $initialSecretError = chatAuthValidateSecret($initialTier, $initialSecret);
    if ($initialSecretError !== null) chatRespond(['success' => false, 'error' => $initialSecretError], 422);
}
/* TASK-3 SECURITY END */

// Basic rate limiting by IP — prevents trivial flood of new threads
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!chatCheckRateLimit('start_ip:' . $ip, 10, 600)) { // 10 attempts / 10 minutes
    chatRespond(['success' => false, 'error' => 'Too many attempts. Please wait a few minutes and try again.'], 429);
}

$db = chatDb();

// ── Path 1: explicit chat ID given ──────────────────────────────────────
if ($chatId !== '') {
    $stmt = $db->prepare("SELECT * FROM chat_threads WHERE chat_id = ? LIMIT 1");
    $stmt->execute([$chatId]);
    $thread = $stmt->fetch();

    // TASK-WEB-02-ADMIN-BASELINE START — a chat ID is a lookup hint, never proof of possession.
    // A mismatch falls through to the normal email-only recovery path, which
    // can resume only a thread belonging to the submitted email address.
    if ($thread && !hash_equals(
        strtolower(trim((string)($thread['email'] ?? ''))),
        strtolower($email)
    )) {
        $thread = false;
    }
    // TASK-WEB-02-ADMIN-BASELINE END

    if ($thread) {
        if ($hasPreferredChatLanguage) {
            $db->prepare('UPDATE chat_threads SET preferred_chat_language = ? WHERE id = ?')->execute([$preferredChatLanguage, $thread['id']]);
            $thread['preferred_chat_language'] = $preferredChatLanguage;
        }
        // v2.0 Scenario B reason-change detection: if the customer went back
        // through the pre-chat gate and picked a DIFFERENT reason than the
        // thread's original one, AND the thread is currently done, fire the
        // Scenario B per-reason welcome message once, right now. This never
        // changes the thread's own stored reason — it's purely a one-time
        // trigger comparison, exactly as specified: "tell the bot the reason
        // changed, fire that reason's message" and nothing else.
        if ($thread['status'] === 'done' && $reason !== '' && $reason !== $thread['reason']) {
            $botProfile = chatGetBotProfile();
            $reopenBody = null;
            if (!empty($botProfile['active']) && !empty($botProfile['reopenMessages'][$reason])) {
                $reopenBody = chatBotSubstitute($botProfile['reopenMessages'][$reason], $thread['full_name']);
            } elseif (!empty($botProfile['active']) && !empty($botProfile['reopenFallback'])) {
                $reopenBody = chatBotSubstitute($botProfile['reopenFallback'], $thread['full_name']);
            }
            if ($reopenBody !== null) {
                $db->prepare("INSERT INTO chat_messages (thread_id, from_type, msg_type, body, status) VALUES (?, 'bot', 'text', ?, 'sent')")
                   ->execute([$thread['id'], $reopenBody]);
            }
        }

        /* TASK-3 START — existing protected threads must complete AuthGate
           before any readable session token is issued. */
        $authPayload = chatStartAuthPayload($thread, $deviceToken);
        $sessionToken = $authPayload['sessionToken'];
        /* TASK-3 END */

        chatRespond([
            'success'      => true,
            'resumed'      => true,
            'chatId'       => $thread['chat_id'],
            'status'       => $thread['status'],
            'fullName'     => $thread['full_name'],
            'email'        => $thread['email'],
            'authRequired' => $authPayload['authRequired'],
            'authTier'     => $authPayload['authTier'],
            /* TASK-1 START — sessionToken in the response payload */
            'sessionToken' => $sessionToken,
            /* TASK-1 END */
            'preferred_chat_language' => $thread['preferred_chat_language'] ?? 'en', // AP4-TASK-3

        ]);
    }

    // Chat ID given but not found — fall through to email lookup rather than
    // hard-failing, since the customer may have mistyped but still has a
    // valid email on file.
}

// ── Path 2: email lookup — any status, most recent thread wins ──────────
// Done threads are now included: email is always sufficient to return to
// ANY previous thread, same as providing the explicit chat ID. The user
// reported that using their email after a Done thread created a new chatId
// instead of resuming — this was the bug.
$stmt = $db->prepare(
    "SELECT * FROM chat_threads WHERE email = ? ORDER BY created_at DESC LIMIT 1"
);
$stmt->execute([$email]);
$existing = $stmt->fetch();

if ($existing) {
    // Same v2.0 Scenario B reason-change detection as Path 1 above.
    if ($existing['status'] === 'done' && $reason !== '' && $reason !== $existing['reason']) {
        $botProfile = chatGetBotProfile();
        $reopenBody = null;
        if (!empty($botProfile['active']) && !empty($botProfile['reopenMessages'][$reason])) {
            $reopenBody = chatBotSubstitute($botProfile['reopenMessages'][$reason], $existing['full_name']);
        } elseif (!empty($botProfile['active']) && !empty($botProfile['reopenFallback'])) {
            $reopenBody = chatBotSubstitute($botProfile['reopenFallback'], $existing['full_name']);
        }
        if ($reopenBody !== null) {
            $db->prepare("INSERT INTO chat_messages (thread_id, from_type, msg_type, body, status) VALUES (?, 'bot', 'text', ?, 'sent')")
               ->execute([$existing['id'], $reopenBody]);
        }
    }

    /* TASK-3 START — apply the same gate to email-based resume. */
    $authPayload = chatStartAuthPayload($existing, $deviceToken);
    $sessionToken = $authPayload['sessionToken'];
    /* TASK-3 END */
    if ($hasPreferredChatLanguage) {
        $db->prepare('UPDATE chat_threads SET preferred_chat_language = ? WHERE id = ?')->execute([$preferredChatLanguage, $existing['id']]);
    }
    $resolvedPreferredChatLanguage = $hasPreferredChatLanguage
        ? $preferredChatLanguage
        : ($existing['preferred_chat_language'] ?? 'en');

    chatRespond([
        'success'      => true,
        'resumed'      => true,
        'chatId'       => $existing['chat_id'],
        'status'       => $existing['status'],
        'fullName'     => $existing['full_name'],
        'email'        => $existing['email'],
        'authRequired' => $authPayload['authRequired'],
        'authTier'     => $authPayload['authTier'],
        /* TASK-1 START — sessionToken in the response payload */
        'sessionToken' => $sessionToken,
        /* TASK-1 END */
        'preferred_chat_language' => $resolvedPreferredChatLanguage,
    ]);
}

// ── Path 3: genuinely new email — create a new thread ───────────────────
$newChatId = chatGenerateChatId();

$stmt = $db->prepare(
    "INSERT INTO chat_threads (chat_id, email, full_name, reason, pet_context, status, unread_for_admin, preferred_chat_language)
     VALUES (?, ?, ?, ?, ?, 'waiting', 0, ?)"
);
$stmt->execute([
    $newChatId,
    $email,
    $fullName,
    $reason,
    $petContext ? json_encode($petContext) : null,
    $preferredChatLanguage, // AP4-TASK-3
]);

$threadId = $db->lastInsertId();

/* TASK-3 START — a customer may opt into protection at the pre-chat gate;
   otherwise the chat receives a stable unprotected auth row. */
$authSetup = chatAuthEnsure($newChatId);
if ($initialTier !== '') {
    $authSetupResult = chatAuthSetSecret($newChatId, $initialTier, $initialSecret);
    if (!$authSetupResult['success']) chatRespond($authSetupResult, 422);
}
/* TASK-3 END */

// Insert the reason-aware automated first response as real thread history
// (Section 8 — content-aware per reason, not generic).
// v2.0: the bot profile overrides this when active and a message is
// configured for this specific reason; falls back to the CHAT_AUTO_RESPONSES
// constant when the bot is off or has no message set for this reason.
$botProfile = chatGetBotProfile();
$useBotForWelcome = !empty($botProfile['active']) && !empty($botProfile['welcomeMessages'][$reason]);

if ($useBotForWelcome) {
    $rawBody = $botProfile['welcomeMessages'][$reason];
    $autoBody = chatBotSubstitute($rawBody, $fullName);
    $fromType = 'bot';
} elseif (!empty($botProfile['active']) && !empty($botProfile['welcomeFallback'])) {
    // Bot is active with a general fallback message but no message set for
    // this specific reason — use the fallback rather than dropping to the
    // hardcoded constant, since the admin explicitly configured the bot.
    $autoBody = chatBotSubstitute($botProfile['welcomeFallback'], $fullName);
    $fromType = 'bot';
} else {
    $autoBody = CHAT_AUTO_RESPONSES[$reason] ?? CHAT_AUTO_RESPONSES['general'];
    $fromType = 'system';
}

$stmt = $db->prepare(
    "INSERT INTO chat_messages (thread_id, from_type, msg_type, body, status) VALUES (?, ?, 'text', ?, 'sent')"
);
$stmt->execute([$threadId, $fromType, $autoBody]);

// TASK 11: Scenario 1 of the two email-notification triggers — a genuinely
// new chat thread was just created (this is Path 3, the only path where
// that's true). Notifies the admin address AND the customer's own prechat
// email. Called only after every insert above succeeded, and wrapped
// internally (see chat-mail.php) so a mail failure can never break the
// customer's chat from actually starting.
chatNotifyNewThread([
    'chat_id'   => $newChatId,
    'email'     => $email,
    'full_name' => $fullName,
    'reason'    => $reason,
]);

/* TASK-1 START — issue the very first session token for a brand-new thread.
   Placed after chatNotifyNewThread() deliberately: notification failure is
   already non-fatal (wrapped internally in chat-mail.php), and token issuance
   should happen last, immediately before the response, so nothing after it
   can fail and leave a token issued for a request that never actually
   completed. */
$sessionToken = issue_token($newChatId);
/* TASK-1 END */

chatRespond([
    'success'      => true,
    'resumed'      => false,
    'chatId'       => $newChatId,
    'status'       => 'waiting',
    'fullName'     => $fullName,
    'email'        => $email,
    'authRequired' => false,
    'authTier'     => $initialTier ?: 'none',
    /* TASK-1 START — sessionToken in the response payload */
    'sessionToken' => $sessionToken,
    /* TASK-1 END */
    'preferred_chat_language' => $preferredChatLanguage,
]);
