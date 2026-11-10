<?php
/**
 * verification-mail.php
 *
 * TASK-B2 — new file, no existing code to merge (per Directive 8a's "New
 * files created from scratch" exception).
 *
 * Sends the merchant-facing approve/reject confirmation emails required by
 * the task spec, reusing the platform's EXISTING email-sending
 * implementation end-to-end (per Directive 7 — this is the "same one B1
 * uses" file named in this task's Files-to-Request line):
 *  - contact-config.php's sendContactEmail() does the actual SMTP send —
 *    never throws, always returns a result array. Not modified.
 *  - chat-mail.php's chatMailShell() builds the shared branded HTML
 *    envelope (Sora font, navy/gold) every other PetCenter email already
 *    uses. Not modified — required and called as-is.
 *  - The send-and-log wrapper below (verifMailSendAndLog) is a direct
 *    structural copy of chat-mail.php's chatMailSendAndLog(): checks
 *    sendContactEmail()'s return value directly (since it never throws),
 *    plus a defensive try/catch around the whole thing, logs failures via
 *    error_log(), and never surfaces a mail failure to the admin's
 *    approve/reject click — the verification_status update already
 *    succeeded and is saved independently of whether this email works.
 *
 * DATA NOTE (flagged, not assumed silently): B1's 7-step flow (see
 * Rehoming-Platform-Task-Assignments.md) documents collecting the
 * merchant's username, submission timestamp, and country, but does not
 * explicitly list an email/contact field. Since B2 needs SOME channel to
 * reach the merchant post-review, this file assumes the verification
 * request record includes an `email` field and reads it from the store.
 * If B1's real implementation turns out not to capture one, that's a
 * genuine cross-task gap worth confirming with the CEO at the
 * B1/B2 integration checkpoint — not something to silently paper over
 * here.
 */

// Same relative-path require pattern chat-mail.php itself uses to reach
// contact-config.php (this file lives alongside admin-dashboard.html's
// backend, i.e. the same directory chat-mail.php already lives in).
$verifMailConfigPath = __DIR__ . '/contact-config.php';
if (file_exists($verifMailConfigPath)) {
    require_once $verifMailConfigPath;
} else {
    error_log('verification-mail.php: could not find contact-config.php at ' . $verifMailConfigPath);
}

// Reuse chat-mail.php's chatMailShell() rather than defining a second copy
// of the same HTML envelope (Rule 7) — only require it if it isn't already
// loaded (chat-mail.php may already be required elsewhere in the same
// request for live-chat notifications).
if (!function_exists('chatMailShell')) {
    $chatMailPath = __DIR__ . '/chat-mail.php';
    if (file_exists($chatMailPath)) {
        require_once $chatMailPath;
    } else {
        error_log('verification-mail.php: could not find chat-mail.php at ' . $chatMailPath . ' (needed for chatMailShell())');
    }
}

// TASK-B2 START
// Direct structural copy of chat-mail.php's chatMailSendAndLog() — see the
// file-level comment above for why (sendContactEmail() never throws, so a
// bare try/catch alone would never see a real send failure).
function verifMailSendAndLog($recipients, $subject, $html, $context) {
    try {
        $result = sendContactEmail($recipients, $subject, $html);
        if (empty($result['success'])) {
            $detail = !empty($result['errors']) ? implode('; ', $result['errors']) : ($result['message'] ?? 'unknown error');
            error_log("verification-mail.php: {$context} failed: {$detail}");
            return false;
        }
        return true;
    } catch (Throwable $t) {
        error_log("verification-mail.php: {$context} threw unexpectedly: " . $t->getMessage());
        return false;
    }
}
// TASK-B2 END

// TASK-B2 START
/**
 * Approval email — confirms verified status and what it unlocks, per the
 * task's exact spec line ("approval email confirms verified status and
 * what it unlocks").
 */
function verifNotifyApproved($username) {
    if (!function_exists('sendContactEmail') || !function_exists('chatMailShell')) {
        error_log('verification-mail.php: dependencies unavailable, skipping approval email for ' . $username);
        return false;
    }

    $requests = verifLoadRequests();
    $email = '';
    foreach ($requests as $req) {
        if (($req['username'] ?? '') === $username) {
            $email = $req['email'] ?? '';
            break;
        }
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log('verification-mail.php: no valid email on file for ' . $username . ', cannot send approval email');
        return false;
    }

    $usernameEsc = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    $body = <<<HTML
        <p>Hi {$usernameEsc},</p>
        <p>Your identity verification has been <strong style="color:#0d7c4a;">approved</strong>. You're now a verified merchant on Pet Center.</p>
        <div class="field">
            <span class="label">What this unlocks</span>
            <span class="value">Your listings now display a Verified badge and are included when visitors use the "Verified only" search filter, which builds trust with visitors browsing and messaging about your pets.</span>
        </div>
        <p style="color:#7a6d5f; font-size:14px;">You can now finish setting up your account and start posting listings.</p>
HTML;

    return verifMailSendAndLog(
        $email,
        'Your Pet Center verification is approved',
        chatMailShell('Verification Approved', 'Welcome, verified merchant', $body),
        "approval email for {$username}"
    );
}

/**
 * Rejection email — includes the reason and how to resubmit, per the
 * task's exact spec line ("rejection email includes the reason and how
 * to resubmit").
 */
function verifNotifyRejected($username, $reason) {
    if (!function_exists('sendContactEmail') || !function_exists('chatMailShell')) {
        error_log('verification-mail.php: dependencies unavailable, skipping rejection email for ' . $username);
        return false;
    }

    $requests = verifLoadRequests();
    $email = '';
    foreach ($requests as $req) {
        if (($req['username'] ?? '') === $username) {
            $email = $req['email'] ?? '';
            break;
        }
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log('verification-mail.php: no valid email on file for ' . $username . ', cannot send rejection email');
        return false;
    }

    $usernameEsc = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    $reasonEsc   = htmlspecialchars($reason, ENT_QUOTES, 'UTF-8');
    $body = <<<HTML
        <p>Hi {$usernameEsc},</p>
        <p>Your identity verification was <strong style="color:#c0392b;">not approved</strong> this time.</p>
        <div class="field">
            <span class="label">Reason</span>
            <span class="value">{$reasonEsc}</span>
        </div>
        <div class="field">
            <span class="label">What to do next</span>
            <span class="value">You're welcome to resubmit — just address the reason above and go through the verification flow again.</span>
        </div>
HTML;

    return verifMailSendAndLog(
        $email,
        'Your Pet Center verification was not approved',
        chatMailShell('Verification Not Approved', 'You can resubmit', $body),
        "rejection email for {$username}"
    );
}
// TASK-B2 END
