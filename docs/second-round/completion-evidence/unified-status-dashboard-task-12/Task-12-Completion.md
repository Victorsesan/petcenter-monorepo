# Task 12 Completion

## Task Number

**Task 12 — Extend Email Notifications to the Adoption Approval Flow**

## What Was Required

When an admin approves or rejects an adoption application through `update-adoption.php`, the applicant should be notified by email — the same way Task 6 already wired an email trigger into the BNPL approve/reject endpoint (`update-bnpl-status.php`). This meant reusing Task 6's existing `brevo_send_email()` helper (never writing a second Brevo integration), writing an adoption-specific email body (approved vs. rejected, referencing the pet and adoption code rather than a loan), and making the whole thing fail-safe: a Brevo error or missing file must never block or fail the actual approve/reject write, which is the part of this endpoint every other task depends on. Scope is `update-adoption.php` only — mirroring Task 6's backend-only trigger, with no `admin-dashboard.html` UI changes implied or required.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `update-adoption.php` | `admin/update-adoption.php` | Changed | Yes — also carries `TASK-10` anchors (approved_by/approved_position/approved_at) and `SHIPPING-TIER1` anchors (manual shipping-status endpoint). Neither was touched; both anchor blocks are reproduced below only as surrounding context, unmodified. |

No other file was created or modified. `brevo-mailer.php`, `config.php` (Task 6, under `/installments/`), and `adoption-application.php` (for its established email branding) were read but not edited — Task 12 only calls into the Brevo helper and copies the existing email template's structure.

## Code Changes

### `update-adoption.php`  →  live path `admin/update-adoption.php`

**Change 1 of 2 — new function added, immediately after the existing `BUGFIX-4` block, before the `APPROVED`/`REJECTED` branch.**

<!-- TASK-12 START -->
**Old code:**
```php
function getPetCategory($pet_id) {
    $map = ['DOG' => 'dogs', 'CAT' => 'cats', 'BIRD' => 'birds', 'REP' => 'reptiles', 'SM' => 'small-mammals', 'LVS' => 'livestocks', 'AQU' => 'aquariums'];
    $dashPos = strpos($pet_id, '-');
    $prefix = strtoupper($dashPos !== false ? substr($pet_id, 0, $dashPos) : substr($pet_id, 0, 3));
    return $map[$prefix] ?? null;
}
/* BUGFIX-4 END */

if ($status === 'APPROVED') {
```

**New code:**
```php
function getPetCategory($pet_id) {
    $map = ['DOG' => 'dogs', 'CAT' => 'cats', 'BIRD' => 'birds', 'REP' => 'reptiles', 'SM' => 'small-mammals', 'LVS' => 'livestocks', 'AQU' => 'aquariums'];
    $dashPos = strpos($pet_id, '-');
    $prefix = strtoupper($dashPos !== false ? substr($pet_id, 0, $dashPos) : substr($pet_id, 0, 3));
    return $map[$prefix] ?? null;
}
/* BUGFIX-4 END */

/* TASK-12 START — adoption-specific email body builder.
   Task 6's brevo-mailer.php intentionally ships brevo_build_bnpl_email_body()
   as a BNPL-only pattern to copy, not a shared function to call (see that
   file's own docblock: "Task 9 / Task 12 workers can see the pattern and
   write their own body builders alongside their own logic without touching
   this function"). This function follows that same return shape (subject/
   html/text array, htmlspecialchars-escaped interpolation) but its actual
   branding follows adoption-application.php's Section 6.2 "Email content
   rules" instead of BNPL's minimal template, since these are adoption-
   applicant-facing emails from the same family as that confirmation email:
     - Uses the label "Adoption Code", never "reference" or "Submission ID"
       (6.2: an earlier version mixed terminology between popup and email).
     - Includes the logo at /assets/images/logo-petcenter.png (6.2: an
       earlier version pointed at a nonexistent /logo.png).
     - No Stripe payment icon/branding (6.2: explicitly removed project-wide
       — not applicable to this content, but intentionally never added here).
     - Footer links point to docs.petcenterco.com privacy-policy/returns-
       refunds/contact-us, "consistent with every other page's footer" (6.2).
   The logo URL and footer markup below are copied verbatim (same scheme/
   host detection, same href values and styling) from the confirmation email
   adoption-application.php already sends on submission, rather than
   reinvented, per Rule 7. Uses this file's own uppercase 'APPROVED' /
   'REJECTED' status strings rather than BNPL's lowercase ones, since that's
   the convention already used throughout this file. */
function build_adoption_email_body($new_status, $pet_name, $adoption_code, $approved_by = '', $rejection_notes = '') {
    $pet_safe   = htmlspecialchars($pet_name,       ENT_QUOTES, 'UTF-8');
    $code_safe  = htmlspecialchars($adoption_code,  ENT_QUOTES, 'UTF-8');
    $by_safe    = htmlspecialchars($approved_by,    ENT_QUOTES, 'UTF-8');
    $notes_safe = htmlspecialchars($rejection_notes, ENT_QUOTES, 'UTF-8');

    // Same scheme/host detection adoption-application.php uses to build $logo_url.
    $host     = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'petcenterco.com';
    $scheme   = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $logo_url = $scheme . '://' . $host . '/assets/images/logo-petcenter.png';

    $status_url = $scheme . '://' . $host . '/adoptions/status/?code=' . urlencode($adoption_code);

    // Shared footer, copied verbatim from adoption-application.php's confirmation email.
    $footer = '
      <tr><td style="background:#f8fafc;border-radius:12px;padding:40px 20px;text-align:center;">
        <p style="margin:0 0 15px;font-size:14px;color:#1a1a1a;font-weight:700;">Thank you for giving a rescue pet a second chance.</p>
        <p style="margin:0 0 15px;font-size:12px;color:#64748b;line-height:1.6;"><strong>Pet Center</strong><br>
          <a href="https://docs.petcenterco.com/privacy-policy/" style="color:#1a1a1a;text-decoration:none;">Privacy Policy</a> |
          <a href="https://docs.petcenterco.com/returns-refunds/" style="color:#1a1a1a;text-decoration:none;">Returns &amp; Refunds</a> |
          <a href="https://docs.petcenterco.com/contact-us" style="color:#1a1a1a;text-decoration:none;">Contact</a>
        </p>
        <p style="margin:0;font-size:11px;color:#94a3b8;">Keep your adoption code handy: ' . $code_safe . '. You can check your application status anytime at <a href="' . htmlspecialchars($status_url, ENT_QUOTES, 'UTF-8') . '" style="color:#94a3b8;">adopt.petcenterco.com/adoptions/status/</a>.</p>
      </td></tr>';

    $header = '
      <tr><td align="center" style="padding-bottom:30px;">
        <img src="' . $logo_url . '" alt="PetCenter" style="width:140px;height:auto;">
      </td></tr>';

    if ($new_status === 'APPROVED') {

        $subject = 'Your Pet Center Adoption Application Has Been Approved';

        $html = '<!DOCTYPE html>
<html lang="en">
<body style="margin:0;padding:0;background:#ffffff;font-family:Arial,sans-serif;color:#1a1a1a;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#ffffff;padding:40px 20px;">
  <tr><td align="center">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;">' . $header . '
      <tr><td align="center">
        <h1 style="margin:0;font-size:26px;font-weight:700;">Application Approved</h1>
        <p style="margin:10px 0 0;font-size:16px;color:#4b5563;">
          Great news! Your adoption application for <strong>' . $pet_safe . '</strong> has been
          <strong style="color:#2a7a2a;">approved</strong>' . ($by_safe !== '' ? ' by ' . $by_safe : '') . '.
        </p>
      </td></tr>
      <tr><td>
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f9fafb;border-radius:12px;padding:24px;border:1px solid #f1f5f9;margin-top:30px;">
          <tr><td>
            <span style="font-size:14px;color:#6b7280;">Adoption Code</span>
            <span style="font-size:14px;color:#1a1a1a;font-weight:700;float:right;font-family:monospace;">' . $code_safe . '</span>
          </td></tr>
        </table>
      </td></tr>
      <tr><td align="center" style="padding:30px 0 10px;">
        <a href="' . htmlspecialchars($status_url, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;background:#ED7624;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;padding:12px 28px;border-radius:8px;">View Status &amp; Checkout</a>
      </td></tr>
      <tr><td align="center" style="padding:10px 0 30px;">
        <p style="margin:0;font-size:14px;color:#4b5563;">
          Visit your application status page to complete the next steps and checkout.
          Questions? Contact us at <a href="mailto:adopt@petcenterco.com">adopt@petcenterco.com</a>.
        </p>
      </td></tr>' . $footer . '
    </table>
  </td></tr>
</table>
</body>
</html>';

        $text = "Application Approved\n\n"
            . "Great news! Your adoption application for {$pet_name} has been "
            . "APPROVED" . ($approved_by !== '' ? " by {$approved_by}" : '') . ".\n\n"
            . "Adoption Code: {$adoption_code}\n\n"
            . "Visit your application status page to complete the next steps and checkout: {$status_url}\n\n"
            . "If you have questions, contact adopt@petcenterco.com.\n\n"
            . "Pet Center -- Privacy Policy: https://docs.petcenterco.com/privacy-policy/ | "
            . "Returns & Refunds: https://docs.petcenterco.com/returns-refunds/ | "
            . "Contact: https://docs.petcenterco.com/contact-us";

    } else {
        // REJECTED

        $subject = 'Update on Your Pet Center Adoption Application';

        $html = '<!DOCTYPE html>
<html lang="en">
<body style="margin:0;padding:0;background:#ffffff;font-family:Arial,sans-serif;color:#1a1a1a;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#ffffff;padding:40px 20px;">
  <tr><td align="center">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;">' . $header . '
      <tr><td align="center">
        <h1 style="margin:0;font-size:26px;font-weight:700;">Application Update</h1>
        <p style="margin:10px 0 0;font-size:16px;color:#4b5563;">
          Thank you for applying to adopt <strong>' . $pet_safe . '</strong> with Pet Center.
          After reviewing your application, we are unfortunately unable to approve it at this time.
        </p>
      </td></tr>
      <tr><td>
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f9fafb;border-radius:12px;padding:24px;border:1px solid #f1f5f9;margin-top:30px;">
          <tr><td' . ($notes_safe !== '' ? ' style="padding-bottom:12px;border-bottom:1px solid #e5e7eb;"' : '') . '>
            <span style="font-size:14px;color:#6b7280;">Adoption Code</span>
            <span style="font-size:14px;color:#1a1a1a;font-weight:700;float:right;font-family:monospace;">' . $code_safe . '</span>
          </td></tr>'
          . ($notes_safe !== '' ? '
          <tr><td style="padding-top:12px;">
            <span style="font-size:14px;color:#6b7280;display:block;margin-bottom:4px;">Reason</span>
            <span style="font-size:14px;color:#1a1a1a;">' . $notes_safe . '</span>
          </td></tr>' : '') . '
        </table>
      </td></tr>
      <tr><td align="center" style="padding:30px 0 30px;">
        <p style="margin:0;font-size:14px;color:#4b5563;">
          If you have questions or would like to discuss other options, please contact us at
          <a href="mailto:adopt@petcenterco.com">adopt@petcenterco.com</a>.
        </p>
      </td></tr>' . $footer . '
    </table>
  </td></tr>
</table>
</body>
</html>';

        $text = "Application Update\n\n"
            . "Thank you for applying to adopt {$pet_name} with Pet Center. "
            . "After reviewing your application, we are unfortunately unable to approve it at this time.\n\n"
            . "Adoption Code: {$adoption_code}\n"
            . ($rejection_notes !== '' ? "Reason: {$rejection_notes}\n" : '')
            . "\nIf you have questions or would like to discuss other options, "
            . "please contact adopt@petcenterco.com.\n\n"
            . "Pet Center -- Privacy Policy: https://docs.petcenterco.com/privacy-policy/ | "
            . "Returns & Refunds: https://docs.petcenterco.com/returns-refunds/ | "
            . "Contact: https://docs.petcenterco.com/contact-us";
    }

    return ['subject' => $subject, 'html' => $html, 'text' => $text];
}
/* TASK-12 END */

if ($status === 'APPROVED') {
```
<!-- TASK-12 END -->

**Change 2 of 2 — email trigger, added after the status write, replacing the final `echo` so both APPROVED and REJECTED paths get an advisory notification result in the response.**

<!-- TASK-12 START -->
**Old code:**
```php
$application['updated_date'] = date('Y-m-d H:i:s');
file_put_contents($app_file, json_encode($application, JSON_PRETTY_PRINT));

echo json_encode(['success' => true, 'message' => 'Application updated successfully']);
?>
```

**New code:**
```php
$application['updated_date'] = date('Y-m-d H:i:s');
file_put_contents($app_file, json_encode($application, JSON_PRETTY_PRINT));

// TASK-12 START: notify the applicant by email when their adoption application
// is approved or rejected. Reuses Task 6's brevo_send_email() -- the ONE
// reusable Brevo integration for this project (see brevo-mailer.php's own
// header comment: "Do NOT create a second Brevo client or a second
// email-send function anywhere else -- Task 9, Task 12, or any future
// task"). Mirrors Task 6's non-blocking pattern for the BNPL approve/reject
// flow: this can never fail or delay the status write above, which has
// already completed by this point. Any problem here (missing files, no
// applicant_email on file, Brevo API error) is caught, logged via
// error_log(), and surfaced only as an advisory field in the JSON response
// -- it never changes 'success' or blocks the admin's approve/reject action.
$email_notification_sent   = false;
$email_notification_detail = 'Email notification not attempted.';

$applicant_email = $application['applicant_email'] ?? null;
$applicant_name  = $application['applicant_name']  ?? '';

if ($applicant_email) {
    // Per BNPL-Path-Sync.md, brevo-mailer.php lives self-contained on this
    // same domain at /installments/, alongside submit-installment.php and
    // config.php (which defines BREVO_API_KEY and must be loaded first).
    $config_file = __DIR__ . '/../installments/config.php';
    $mailer_file = __DIR__ . '/../installments/brevo-mailer.php';

    if (file_exists($config_file) && file_exists($mailer_file)) {
        // Defensive: output buffering around the require calls so that any
        // stray output from config.php (this file already calls
        // session_start() above) can never corrupt the JSON response below.
        // See SECOND VERIFICATION ROUND -- this is now belt-and-suspenders
        // only, confirmed unnecessary in practice, but kept since it is free.
        ob_start();
        require_once $config_file;
        require_once $mailer_file;
        $stray_output = ob_get_clean();
        if ($stray_output !== '') {
            error_log('update-adoption.php (TASK-12): config.php/brevo-mailer.php produced unexpected output, suppressed: ' . $stray_output);
        }

        if (function_exists('brevo_send_email')) {
            $email_body = build_adoption_email_body(
                $status,
                $application['pet_name']      ?? '',
                $application['adoption_code'] ?? $code,
                $approved_by ?? '',
                $rejection_notes ?? ''
            );

            $send_result = brevo_send_email(
                $applicant_email,
                $applicant_name,
                $email_body['subject'],
                $email_body['html'],
                $email_body['text']
            );

            $email_notification_sent   = $send_result['success'] ?? false;
            $email_notification_detail = $send_result['message'] ?? 'Unknown result from brevo_send_email().';
        } else {
            $email_notification_detail = 'brevo_send_email() not found after requiring brevo-mailer.php.';
            error_log('update-adoption.php (TASK-12): brevo_send_email() missing after require -- check brevo-mailer.php.');
        }
    } else {
        $email_notification_detail = 'Brevo config/mailer files not found at expected /installments/ path.';
        error_log('update-adoption.php (TASK-12): expected files not found -- ' . $config_file . ' / ' . $mailer_file);
    }
} else {
    $email_notification_detail = 'Application has no applicant_email on file -- notification skipped.';
}
// TASK-12 END

echo json_encode([
    'success' => true,
    'message' => 'Application updated successfully',
    'email_notification_sent'   => $email_notification_sent,
    'email_notification_detail' => $email_notification_detail,
]);
?>
```
<!-- TASK-12 END -->

## New files

No new files.

## Bugs Found Outside Task Scope

1. **Documentation vs. code mismatch:** `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` Section 9 describes `update-adoption.php` as updating both `application.json` and `metadata.json` on every status change. The actual file (`admin/update-adoption.php`) only ever reads and writes `application.json` — there is no `metadata.json` reference anywhere in it. If `metadata.json` is relied on elsewhere for quick lookups, it will go stale on every approve/reject/ship-status update. Not fixed — outside Task 12's scope.
2. **No `flock()` on `file_put_contents()`** in `admin/update-adoption.php`, unlike Task 8's `action.php`. Previously flagged by the Shipping Tier 1 build; Task 12 performs no additional writes to `$app_file` so it doesn't worsen this, but doesn't fix it either. Not fixed — outside Task 12's scope.

## Self-Check Confirmation

- Only `admin/update-adoption.php` was touched. No other file (including `brevo-mailer.php`, `config.php`, `admin/index.html`, or any project summary document) was modified.
- Every added block is wrapped in `TASK-12 START` / `TASK-12 END` anchor comments with an explanatory comment.
- The pre-existing `TASK-10` and `SHIPPING-TIER1` anchor blocks were left completely untouched — reproduced above only where they sit adjacent to a change, for context.
- Both old-code snippets above were copied character for character from the original file I was given and each appears only once in it (confirmed mechanically — both edits were applied with an exact-match string-replace tool that rejects non-unique or non-matching text).
- No new files were created, so no zip accompanies this delivery.
- No PHP interpreter was available in this environment to run `php -l`; the edit was hand-verified by full-file review for brace/quote/paren balance and correct variable scope instead (including a careful trace through the nested ternaries and string concatenation in both HTML blocks).

## SECOND VERIFICATION ROUND

A later verification pass (re-reading `adoption-website-project-summary.md` and `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` against the first delivery) found and fixed two real issues. Both fixes are already folded into the Code Changes above — this section documents what changed and why; it is not a separate patch to apply.

1. **Branding/terminology mismatch.** Section 6.2 ("Email content rules") explicitly documents two past bugs in this exact area: inconsistent terminology instead of the label "Adoption Code," and a logo linking to a file that didn't exist. The first delivery of Task 12's emails used "reference:" instead of "Adoption Code," had no logo, and had no footer — none of the branding `adoption-application.php`'s confirmation email already establishes, which Section 6.2 says should be "consistent with every other page's footer." Fixed by rebuilding `build_adoption_email_body()` to copy the logo header, "Adoption Code" labeling, and footer link block (Privacy Policy / Returns & Refunds / Contact, all pointing at `docs.petcenterco.com`) directly from `adoption-application.php`'s own confirmation-email markup.
2. **Unverified `config.php` could have corrupted the JSON response.** `update-adoption.php` already calls `session_start()` at the top of the file, and `config.php`'s source had not been seen — only its documented role (defines `BREVO_API_KEY`). Any stray output from it would have landed in the response body before the final `json_encode()`. Fixed defensively by wrapping the `require_once` calls in `ob_start()`/`ob_get_clean()`. The CEO subsequently confirmed `config.php` is only ever used for a `require_once` (no side effects, holds only live credentials) and that each email flow runs through its own separate PHP handler, so there was never a real collision risk — the `ob_start()` guard stays in regardless, as harmless extra safety.

I also re-confirmed `brevo_send_email()`'s real signature and return shape (`['success' => bool, 'message' => string, 'message_id' => ...]`) directly against the `brevo-mailer.php` source, and re-confirmed Task 12's scope is backend-only with no `admin/index.html` wiring required — neither needed a change.

No open items remain. Task 12 is complete.
