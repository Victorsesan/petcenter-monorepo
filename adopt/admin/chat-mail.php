<?php
/**
 * chat-mail.php
 *
 * TASK 11 — Live Chat Email Notifications.
 *
 * Reuses the exact same SMTP config + sendContactEmail() helper already
 * used by the "Chat by Email" contact form, per the site's file structure
 * contact-us/ sits as a sibling of admin/ under /public_html/adopt/, so a
 * relative path from this file (which lives in admin/) reaches it directly.
 *
 * Exactly two trigger scenarios, no others:
 *  1. A genuinely new chat thread is created (chat-start.php, Path 3) ->
 *     notify the admin address AND the customer's own prechat email.
 *  2. A customer's message reopens a thread whose status was 'done'
 *     (chat-send.php, $wasReopenedFromDone) -> notify the admin address only.
 */

// TASK-11 START
$chatMailConfigPath = __DIR__ . '/../contact-us/contact-config.php';
if (file_exists($chatMailConfigPath)) {
    require_once $chatMailConfigPath;
} else {
    // Defensive fallback: if the path ever changes, log once so the gap is
    // visible in the server logs instead of every chat action silently
    // failing to notify anyone with no trace of why.
    error_log('chat-mail.php: could not find contact-config.php at ' . $chatMailConfigPath);
}
// TASK-11 END

// TASK-11 START
// ============================================
// SHARED HTML EMAIL SHELL
// Matches contact-handler.php's existing template exactly (Sora font,
// #10214b navy / #d7bd88 gold brand colors) so a chat notification email
// looks like it came from the same system as every other PetCenter email.
// ============================================
function chatMailShell($headerTitle, $headerSubtitle, $bodyHtml) {
    // Computed here, not inline in the heredoc below, since heredoc syntax
    // only interpolates variables, not function calls like date().
    $sentAt = date('Y-m-d H:i:s');
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Sora, Arial, sans-serif; color: #2c2416; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; background: #f9f7f4; padding: 20px; }
        .header { background: linear-gradient(135deg, #10214b 0%, #1a2f5c 100%); color: white; padding: 20px; border-radius: 8px; }
        .header h2 { margin: 0; font-size: 22px; }
        .content { background: white; padding: 24px; margin-top: 16px; border-radius: 8px; border: 1px solid rgba(16,33,75,0.12); }
        .field { margin-bottom: 16px; }
        .label { font-weight: 600; color: #10214b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.04em; display: block; margin-bottom: 4px; }
        .value { color: #2c2416; font-size: 14px; }
        .reference { background: rgba(215,189,136,0.1); padding: 12px; border-left: 3px solid #d7bd88; border-radius: 4px; margin: 16px 0; font-family: monospace; font-weight: 600; color: #10214b; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #7a6d5f; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>{$headerTitle}</h2>
            <div style="font-size: 12px; margin-top: 8px;">{$headerSubtitle}</div>
        </div>
        <div class="content">
            {$bodyHtml}
        </div>
        <div class="footer">
            <p>Sent {$sentAt}</p>
        </div>
    </div>
</body>
</html>
HTML;
}
// TASK-11 END

// TASK-11 START
// Human-readable reason label, e.g. 'adoption' -> 'Adoption inquiry'.
// CHAT_REASONS is defined in chat-config.php, which every caller of this
// file has already required before reaching here.
function chatMailReasonLabel($reason) {
    if (defined('CHAT_REASONS') && isset(CHAT_REASONS[$reason])) {
        return CHAT_REASONS[$reason];
    }
    return ucfirst($reason);
}
// TASK-11 END

// TASK-11 START
// Sends one email via sendContactEmail() and logs the outcome with chat
// context. IMPORTANT: sendContactEmail() (contact-config.php) never throws —
// it catches Exception/Throwable internally and always returns a result
// array (['success'=>bool,'message'=>string,'errors'=>array]). A bare
// try/catch around it would therefore never actually see a send failure, so
// this helper checks the return value directly instead. Still wrapped in its
// own try/catch as a second, defensive layer in case something upstream of
// the call (e.g. building the arguments) throws for an unrelated reason —
// either way, a failure here is logged and swallowed, never surfaced to the
// customer-facing response.
function chatMailSendAndLog($recipients, $subject, $html, $context) {
    try {
        $result = sendContactEmail($recipients, $subject, $html);
        if (empty($result['success'])) {
            $detail = !empty($result['errors']) ? implode('; ', $result['errors']) : ($result['message'] ?? 'unknown error');
            error_log("chat-mail.php: {$context} failed: {$detail}");
        }
    } catch (Throwable $t) {
        error_log("chat-mail.php: {$context} threw unexpectedly: " . $t->getMessage());
    }
}
// TASK-11 END

// TASK-11 START
/**
 * Scenario 1 — a genuinely new chat thread was just created
 * (chat-start.php, Path 3). Notifies Pet Center's admin address AND sends a
 * separate confirmation to the customer's own prechat email.
 *
 * @param array $thread ['chat_id', 'email', 'full_name', 'reason']
 */
function chatNotifyNewThread($thread) {
    if (!function_exists('sendContactEmail') || !defined('CONTACT_ADMIN_EMAILS')) {
        error_log('chat-mail.php: sendContactEmail()/CONTACT_ADMIN_EMAILS unavailable, skipping new-thread notification for ' . ($thread['chat_id'] ?? '?'));
        return;
    }

    $chatId    = htmlspecialchars($thread['chat_id'] ?? '', ENT_QUOTES, 'UTF-8');
    $fullName  = htmlspecialchars($thread['full_name'] ?? '', ENT_QUOTES, 'UTF-8');
    $email     = htmlspecialchars($thread['email'] ?? '', ENT_QUOTES, 'UTF-8');
    $reasonRaw = $thread['reason'] ?? '';
    $reason    = htmlspecialchars(chatMailReasonLabel($reasonRaw), ENT_QUOTES, 'UTF-8');

    // ── Admin notification ──────────────────────────────────────────────
    $adminBody = <<<HTML
        <div class="field">
            <span class="label">Customer</span>
            <span class="value">{$fullName}</span>
        </div>
        <div class="field">
            <span class="label">Email Address</span>
            <span class="value"><a href="mailto:{$email}">{$email}</a></span>
        </div>
        <div class="field">
            <span class="label">Reason</span>
            <span class="value">{$reason}</span>
        </div>
        <div class="reference">{$chatId}</div>
HTML;

    chatMailSendAndLog(
        CONTACT_ADMIN_EMAILS,
        "[Live Chat] New chat started - {$chatId}",
        chatMailShell('New Live Chat Started', "Chat ID: {$chatId}", $adminBody),
        "admin new-thread email for {$thread['chat_id']}"
    );

    // ── Customer confirmation ───────────────────────────────────────────
    $customerBody = <<<HTML
        <p>Hi {$fullName},</p>
        <p>Thanks for starting a live chat with PetCenter. Your chat ID is below — keep it handy if you need to return to this conversation later.</p>
        <div class="reference">{$chatId}</div>
        <p style="color:#7a6d5f; font-size:14px;">A member of our team will be with you shortly.</p>
HTML;

    chatMailSendAndLog(
        $thread['email'],
        "We've received your message - PetCenter Live Chat ({$chatId})",
        chatMailShell('Message Received', 'Thank you for contacting PetCenter', $customerBody),
        "customer new-thread email for {$thread['chat_id']}"
    );
}
// TASK-11 END

// TASK-11 START
/**
 * Scenario 2 — a customer's message reopened a thread whose status was
 * 'done' (chat-send.php, $wasReopenedFromDone). Admin-only notification;
 * the customer does not get an email here since they're still inside the
 * same chat/ID they already started with.
 *
 * @param array $thread ['chat_id', 'email', 'full_name', 'reason']
 */
function chatNotifyReopenedThread($thread) {
    if (!function_exists('sendContactEmail') || !defined('CONTACT_ADMIN_EMAILS')) {
        error_log('chat-mail.php: sendContactEmail()/CONTACT_ADMIN_EMAILS unavailable, skipping reopen notification for ' . ($thread['chat_id'] ?? '?'));
        return;
    }

    $chatId   = htmlspecialchars($thread['chat_id'] ?? '', ENT_QUOTES, 'UTF-8');
    $fullName = htmlspecialchars($thread['full_name'] ?? '', ENT_QUOTES, 'UTF-8');
    $email    = htmlspecialchars($thread['email'] ?? '', ENT_QUOTES, 'UTF-8');
    $reason   = htmlspecialchars(chatMailReasonLabel($thread['reason'] ?? ''), ENT_QUOTES, 'UTF-8');

    $adminBody = <<<HTML
        <div class="field">
            <span class="label">Customer</span>
            <span class="value">{$fullName}</span>
        </div>
        <div class="field">
            <span class="label">Email Address</span>
            <span class="value"><a href="mailto:{$email}">{$email}</a></span>
        </div>
        <div class="field">
            <span class="label">Reason</span>
            <span class="value">{$reason}</span>
        </div>
        <div class="field">
            <span class="label">Status</span>
            <span class="value">Previously closed (Done) — reopened by a new customer message</span>
        </div>
        <div class="reference">{$chatId}</div>
HTML;

    chatMailSendAndLog(
        CONTACT_ADMIN_EMAILS,
        "[Live Chat] Thread reopened - {$chatId}",
        chatMailShell('Closed Chat Reopened', "Chat ID: {$chatId}", $adminBody),
        "admin reopen email for {$thread['chat_id']}"
    );
}
// TASK-11 END
