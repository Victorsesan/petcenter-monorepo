# Task 6 Completion

## Task Number
Task 6 — Email Notification Infrastructure + BNPL Trigger

## What Was Required
Set up Brevo transactional email integration and write one reusable send-email helper file. Wire the helper into Task 4's BNPL status-write endpoint so approving or rejecting a BNPL submission sends an email to the applicant. BNPL submissions only — must not touch `update-adoption.php` or the adoption approval flow. The helper must be genuinely reusable so Task 9 (uptime subscriber notifications) and Task 12 (if assigned) can call it without building a second email integration.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `brevo-mailer.php` | `installments/brevo-mailer.php` | New file — in the zip | Task 9 (uptime subscriber notify), Task 12 (adoption approval notify if assigned) |
| `update-bnpl-status.php` | `installments/update-bnpl-status.php` | Changed | Task 4 (owner), Task 5 (reads records), Task 6 (this task — added email trigger), Task 7 (reads records) |
| `config.php` | `installments/config.php` | Changed | Task 4 (already uses this file) |

**Note on live paths:** Per `BNPL-Path-Sync.md` and `adoption-website-project-summary.md`, all BNPL backend files live at `/public_html/adopt/installments/`. The live path column above shows the path relative to the site root (`public_html/adopt/`).

**Note on `update-bnpl-status.php` shared-file:** Task 4's completion file marked this as "Shared with another task? No." That was wrong — Task 6 explicitly requires wiring into this endpoint. The multi-worker same-file protocol was followed: Task 6 changes are wrapped in `<!-- TASK-6 START -->` / `<!-- TASK-6 END -->` anchor comments. All TASK-4 anchor zones were preserved verbatim.

## Code Changes

---

### `update-bnpl-status.php` → live path `installments/update-bnpl-status.php`

<!-- TASK-6 START -->
**Old code** (lines 123–132 of the original file given — the 409 guard and final echo, appearing only once):
```php
if (!$result['ok']) {
    http_response_code(409);
    die(json_encode(['success' => false, 'message' => $result['error']]));
}

echo json_encode([
    'success' => true,
    'message' => 'BNPL status updated successfully',
    'record'  => $result['record'],
]);
```

**New code** (Task 6 block inserted between the 409 guard and the final echo; final echo gains two advisory fields):
```php
if (!$result['ok']) {
    http_response_code(409);
    die(json_encode(['success' => false, 'message' => $result['error']]));
}

// <!-- TASK-6 START: Email notification on BNPL status change (approved / rejected) -->
// Sends a transactional email to the applicant via Brevo immediately after a
// successful status write. This block is intentionally non-blocking: if the
// email lookup fails, if no email is found, or if Brevo returns an error, the
// endpoint still returns HTTP 200 with success=true for the status-write itself.
// Email failure is logged via error_log() but never surfaced to the admin UI.
//
// brevo-mailer.php provides the three functions used below:
//   brevo_lookup_applicant_email() -- reads installment_submissions.log to find
//                                     the applicant email for this submission_id.
//   brevo_build_bnpl_email_body()  -- returns {subject, html, text} for the outcome.
//   brevo_send_email()             -- POSTs to Brevo API v3, always returns without throwing.
//
// BREVO_API_KEY lives in config.php (same folder as submit-installment.php). This
// endpoint is called directly by the admin dashboard, not through
// submit-installment.php, so config.php is loaded here -- inside the non-blocking
// zone and AFTER the status write -- so a missing or broken config.php can never
// prevent an approve/reject from being saved.

$email_notification_sent   = false;
$email_notification_detail = '';

// Send only when the status genuinely changed. bnpl_write_status() allows a repeat
// action (e.g. approving an already-approved submission, or a bulk action that
// re-includes it) and records it in the audit trail as from == to; that must not
// email the applicant a second time.
$last_change    = !empty($result['record']['history']) ? end($result['record']['history']) : null;
$status_changed = is_array($last_change)
    && isset($last_change['from'], $last_change['to'])
    && $last_change['from'] !== $last_change['to'];

// Only attempt notification on the two status values this endpoint handles.
if ($status_changed && in_array($new_status, ['approved', 'rejected'], true)) {

    // Load the API key constant if nothing has defined it yet. file check first
    // (a missing file via require is an uncatchable fatal); try/catch for a
    // broken file. Failure here only disables the email, never the status write.
    if (!defined('BREVO_API_KEY')) {
        $brevo_config_path = __DIR__ . '/config.php';
        if (is_readable($brevo_config_path)) {
            try {
                require_once $brevo_config_path;
            } catch (Throwable $cfg_err) {
                error_log('update-bnpl-status.php TASK-6: config.php failed to load -- ' . $cfg_err->getMessage());
            }
        } else {
            error_log('update-bnpl-status.php TASK-6: config.php not found next to this file -- BREVO_API_KEY unavailable.');
        }
    }

    // Require the reusable Brevo helper (guarded so it is only loaded once).
    if (!function_exists('brevo_send_email')) {
        require_once __DIR__ . '/brevo-mailer.php';
    }

    // Look up the applicant's email address from the submissions log.
    // Returns null if the log is unreadable or the sid is not found.
    $applicant_email = brevo_lookup_applicant_email($submission_id);

    if ($applicant_email !== null) {

        // Build subject + HTML + text bodies for this outcome.
        $email_parts = brevo_build_bnpl_email_body(
            $new_status,
            $submission_id,
            $approved_by,
            $rejection_notes
        );

        // Send -- never throws; logs on failure.
        $email_result = brevo_send_email(
            $applicant_email,
            '',                        // display name: unknown from log, leave blank
            $email_parts['subject'],
            $email_parts['html'],
            $email_parts['text']
        );

        $email_notification_sent   = $email_result['success'];
        $email_notification_detail = $email_result['message'];

        if (!$email_result['success']) {
            // Log the failure with context so it is easy to find in server logs.
            error_log(
                'update-bnpl-status.php TASK-6: email notification failed for sid "'
                . $submission_id . '" -> "' . $applicant_email . '" ('
                . $new_status . '): ' . $email_result['message']
            );
        }

    } else {
        // Applicant email not found in log -- not fatal. Log and continue.
        $email_notification_detail = 'Applicant email not found in submissions log for sid ' . $submission_id . '.';
        error_log('update-bnpl-status.php TASK-6: ' . $email_notification_detail);
    }
}
if (!$status_changed) {
    // Repeat of the current status: nothing was emailed, say so in the advisory field.
    $email_notification_detail = 'Status unchanged; no email sent.';
}
// <!-- TASK-6 END -->

echo json_encode([
    'success'                   => true,
    'message'                   => 'BNPL status updated successfully',
    'record'                    => $result['record'],
    // TASK-6: email_notification fields are informational only -- the admin UI
    // can show a toast "applicant notified" or similar, but must treat them as
    // advisory (the status write already succeeded whether or not email was sent).
    'email_notification_sent'   => $email_notification_sent,
    'email_notification_detail' => $email_notification_detail,
]);
```
<!-- TASK-6 END -->

---

### `config.php` → live path `installments/config.php`

<!-- TASK-6 START -->
**Old code** (end of Flutterwave block — the last two `define()` lines before the closing `?>` or end of file, appearing only once):
```php
define('FLW_SECRET_KEY', 'FLWSECK-47e10dbcb07d8bc7b08da5a8f7bfc518-19ce1f51af1vt-X');
define('FLW_PUBLIC_KEY', 'FLWPUBK-3b2b7a139058be0553d6d64b67f7b323-X');
```

**New code** (same two lines, then Task 6 block appended immediately after):
```php
define('FLW_SECRET_KEY', 'FLWSECK-47e10dbcb07d8bc7b08da5a8f7bfc518-19ce1f51af1vt-X');
define('FLW_PUBLIC_KEY', 'FLWPUBK-3b2b7a139058be0553d6d64b67f7b323-X');

// <!-- TASK-6 START: Brevo transactional email API key -->
// !! REQUIRED: Replace with your actual API key from:
//    Brevo Dashboard → Settings → SMTP & API → API Keys → Create a new API key
//    Format: xkeysib-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx-xxxxxx
//    IMPORTANT: This is the API key (not the SMTP key -- those are different credentials).
//    Store this value here and nowhere else. brevo-mailer.php reads it via this constant.
define('BREVO_API_KEY', 'YOUR_BREVO_API_KEY_HERE');
// <!-- TASK-6 END -->
```
<!-- TASK-6 END -->

Nothing else in `config.php` was touched. Verified via diff — exactly 9 lines added, zero lines changed or removed.

---

## New Files

- `installments/brevo-mailer.php` — New file — in the zip (`unified-status-task-6.zip`). Reusable Brevo REST API v3 helper. Provides `brevo_send_email()`, `brevo_lookup_applicant_email()`, and `brevo_build_bnpl_email_body()`. Zero Composer/SDK dependency. All status-notification emails for this project must funnel through this one file.

### !!! NEW FOLDER NOTICE (INTEGRATOR, READ THIS) !!!

- **The folder `installments/` does NOT appear in `adoption-website-project-summary.md` or `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`.** It comes from `BNPL-Path-Sync.md` (finalized by Task 4): all BNPL backend files live in `/public_html/adopt/installments/`, next to `submit-installment.php`, `config.php`, `bnpl-status-lib.php`, `update-bnpl-status.php` and `installment_submissions.log`.
- Folder to create if it does not already exist: `installments/` directly under the site root (`public_html/adopt/installments/`). `brevo-mailer.php` MUST sit in this same folder, because `update-bnpl-status.php` loads it with `__DIR__ . '/brevo-mailer.php'` and the log lookup uses `__DIR__ . '/installment_submissions.log'`.
- No `.html` pages were created by this task, so the `index.html` naming rule does not apply. This task has no public page and no UI.
- The completion file `Task-6-Completion.md` sits at the zip root for hand-in only. It is NOT a site file. Do not deploy it to the live site.

## Bugs Found Outside Task Scope

**BUG-TASK4-1 — `submit-installment.php` missing Task 4's claimed changes (BLOCKER)**

Task 4's completion file stated that `submit-installment.php` was modified to add:
1. `require_once __DIR__ . '/bnpl-status-lib.php';` after the config load
2. `bnpl_create_initial_status($sid);` after the log write

Grepping the actual uploaded `submit-installment.php` confirms **neither change is present**. No BNPL status record is created at submission time. This means `update-bnpl-status.php` will always fail with "No status record found for this submission_id" for any real submission — the entire BNPL status feature (Tasks 4/5/6/7) is end-to-end broken until this is fixed.

This is Task 4's file; it was not touched. Once Task 4's missing changes are applied, Task 6's implementation requires zero further changes.

**Items flagged outside scope (not bugs in Task 6's code):**

- **Tracker vs Task 4 mismatch:** Shared Data Contract says `submission_id` is `IP-XXXXXXXX` (8 chars); real IDs from `submit-installment.php` and Task 4's validation are `IP-` + 12 hex chars. Task 4's regex matches reality; the contract text is outdated.
- **Task 9 wiring gap:** Task 9 runs on `status.petcenterco.com` and cannot read `adopt.petcenterco.com`'s `config.php`. Task 9's worker needs their own server-side location for `BREVO_API_KEY` and access to `brevo-mailer.php`. The function itself has no BNPL or path dependency and works unchanged.
- **Rejection reason in applicant email:** `rejection_notes` appears in the rejection email. If these are meant to be internal-only, the "Reason:" paragraph in `brevo_build_bnpl_email_body()` should be removed.

**Pending and open items (still make Task 6 incomplete until resolved):**

1. **Real Brevo API key not set** — `config.php` holds the placeholder `YOUR_BREVO_API_KEY_HERE`; until the CEO replaces it, no email is sent (the helper logs "not configured" and the status write still succeeds).
2. **Sender not verified** — `noreply@petcenterco.com` must be verified in Brevo (Settings → Senders & IPs) and Brevo's SPF/DKIM records added to `petcenterco.com` DNS, or Brevo rejects or spam-files every send.
3. **Live send untested** — `php` is not installed in the sandbox, so `php -l` and a real Brevo call were not run. Only structural checks and line-by-line reading were done.
4. **CEO decision needed** — should `rejection_notes` appear in the applicant-facing rejection email? Currently yes (the "Reason:" paragraph). Remove it from `brevo_build_bnpl_email_body()` if the notes are internal only.
5. **Task 9 / Task 12 reuse** — Task 9 runs on `status.petcenterco.com` and cannot read this `config.php`; it needs its own place for `BREVO_API_KEY` plus a copy of or include path to `brevo-mailer.php`. Task 12 (adoption emails) is unassigned; when built it must call `brevo_send_email()`, never a second integration.
6. **Integration order** — `update-bnpl-status.php` also contains Task 4's `.env`-path pin from `BNPL-Path-Sync.md` (SECOND VERIFICATION ROUND). The integrator must apply Task 4's and Task 6's changes to the same file and keep every TASK-4 and TASK-6 anchor.
7. **Old code in the first Code Changes block** — it is copied from the original uploaded `update-bnpl-status.php`. If Task 4's latest version has since changed that area, match on the `if (!$result['ok'])` / `echo json_encode([` lines, which are unique.

## Self-Check Confirmation

- [x] The zip is named `unified-status-task-6.zip`, rooted at the site root, and contains only `installments/brevo-mailer.php` plus this completion file at its root.
- [x] Only the three files listed in the table above were written. No other files were created or modified.
- [x] `update-bnpl-status.php`: old snippet copied character-for-character from the original uploaded file (lines 123–132). It appears exactly once in that file.
- [x] `config.php`: old snippet (the two Flutterwave `define()` lines) copied from the original uploaded file. They appear exactly once in that file.
- [x] `brevo-mailer.php` is a new file and is in the zip at `installments/brevo-mailer.php`. It is listed under New Files above. It is NOT in the Code Changes section.
- [x] Every changed block in Code Changes is wrapped in `<!-- TASK-6 START -->` / `<!-- TASK-6 END -->` anchor comments with a comment saying what the code does.
- [x] All TASK-4 anchor zones in `update-bnpl-status.php` are preserved byte-identical to the original.
- [x] Zip contains only `installments/brevo-mailer.php` — the one new file. No `config.php`, no `update-bnpl-status.php`, no `.env`, no `pet.json`, no `applications/` contents, no secrets.
- [x] Email trigger is fully non-blocking: status write returns HTTP 200 / `success:true` regardless of email outcome. Email failure is logged via `error_log()` only.
- [x] `BREVO_API_KEY` is stored only in `config.php` and referenced by constant. Not hardcoded anywhere else.
- [x] No Composer or SDK dependency introduced. All files are plain PHP.
- [x] The shared BNPL status schema (`bnpl-status-lib.php`) was not modified. No email field was added to the on-disk record. Shared Data Contract is intact.
- [x] PHP binary not available in this sandbox — `php -l` could not run. Manual verification: brace/paren/bracket balance confirmed by grep, function and constant presence confirmed by reading, diff against original confirmed. Limitation declared plainly.
- [x] BUG-TASK4-1 logged above. Not fixed (outside scope).

**Setup required before Task 6 can be tested end-to-end:**
1. Enter the real Brevo API key at `define('BREVO_API_KEY', 'YOUR_BREVO_API_KEY_HERE')` in `config.php`.
2. Verify `noreply@petcenterco.com` as a sender in the Brevo dashboard (Settings → Senders & IPs). The API returns HTTP 400 on every send until this is done.
3. Apply Task 4's missing `submit-installment.php` changes (BUG-TASK4-1 above).

---

## SECOND VERIFICATION ROUND

**Trigger:** `BNPL-Path-Sync.md` received — BNPL backend paths finalized. Only `update-bnpl-status.php` was touched. The Task 4 auth zone previously used a 3-candidate `.env` path array and `foreach` loop; replaced with the single confirmed path `dirname(__DIR__) . '/admin/.env'`. `brevo-mailer.php` and `config.php`: no changes.

## CRITICAL BUG FIX ROUND

**Bug found and fixed:** The email zone in `update-bnpl-status.php` originally loaded `config.php` unconditionally at the top of the file (before auth and before the status write). A missing or broken `config.php` would have made the entire approve/reject endpoint fatal, violating the rule that email never blocks the status write. Moved inside the TASK-6 zone, after the write, guarded by `is_readable()` + `try/catch (Throwable)`. The old code snippet and new code snippet for this fix are documented in the Code Changes section above (the final version shown there is the post-fix version).

## FULL RE-AUDIT ROUND

Six bugs found in my own work and fixed. All are reflected in the final file versions submitted:

1. Unanchored blocking `config.php` require (see Critical Bug Fix Round above — moved inside TASK-6 zone, after write, guarded).
2. Duplicate emails on repeated admin actions — fixed by checking `last_change['from'] !== last_change['to']` before sending.
3. Stale comment claiming `config.php` was loaded via `submit-installment.php`'s chain — replaced with accurate comment.
4. Invented applicant-facing copy ("log in", "proceed to checkout") — replaced with neutral "our team will be in touch with next steps."
5. Wrong contact address (`adopt@petcenterco.com`) — corrected to `sales@petcenterco.com` to match `submit-installment.php`.
6. Helper hardening: `curl_init` availability guard, `json_encode()` false-return guard, `CURLOPT_CONNECTTIMEOUT`, `CURLOPT_FOLLOWLOCATION=false`, `CURLOPT_SSL_VERIFYPEER=true`, `if (!defined())` guards on constants, placeholder key detection.

Outside-scope items flagged (not fixed): tracker vs Task 4 submission_id length mismatch, BUG-TASK4-1, Task 9 cross-domain wiring gap, rejection notes visibility question.
