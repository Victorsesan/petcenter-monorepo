# Task 6 Completion — Password Reset Flow

## Task Number
Task 6 — Password Reset Flow (from `Admin-Project-1-Worker-Task-Assignments.md`)

## What Was Required
Build a self-service password reset path for chat accounts that set a
password under Task 5a: the customer confirms the recovery contact on
file (email) for their chat ID, and — only if it genuinely matches —
receives a single-use, 45-minute temporary password by email, using the
project's existing admin-to-user email automation pattern
(`sendContactEmail()` in `contact-config.php`) rather than a new mail
path. The response (content and timing) must be identical whether or
not the recovery contact actually matches an account, so the endpoint
can't be used to enumerate valid chat IDs or contacts. An active
PIN/password throttle must never block this recovery flow. A successful
reset must invalidate all outstanding session tokens and force a
password change on the very next successful login with the temp
password.

One Recommended Enhancement was listed for this task — rate-limiting
reset requests per recovery contact, separate from the PIN/password
throttle, to reduce email-spam abuse. **Adopted as written**, via the
existing `chatCheckRateLimit()` helper (reused rather than rebuilt, per
rule 7), keyed by `pwreset:{recoveryContact}`, capped at 5/hour —
symmetric for real and fake contacts alike, so it never reopens the
enumeration gap this task exists to close.

This build was done entirely against the published Shared Contracts
(`chat-auth.php` function signatures, `chat_auth` table shape) at first,
since the real `includes/chat-auth.php` and the confirmed `chat_auth`
schema were never supplied for this task (per the brief, both are
requested only at integration time, and remain outstanding). `chat-config.php`,
`contact-config.php`, and `chat-setup.php` were later supplied and are
read-only references this task's code calls into — see Files Changed.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `chat-password-reset-request.php` | `admin/chat-password-reset-request.php` | New | No |
| `includes/chat-auth.php` | `admin/includes/chat-auth.php` | Changed — addition pending (see note below) | Yes — shared with Task 3 (creates it) and Task 5a (activates password fields on it); Task 6 is its documented last owner |

**Note on `includes/chat-auth.php`'s status:** this file was never
supplied to Task 6 — not at the start (expected, per the brief) and not
since. Per this guide's own rule ("if a completion file you're reviewing
claims a 'new file' that was never supplied to you, do not create your
own version of it — stop and flag exactly which file is missing"), the
same principle is applied here in reverse: rather than fabricate an "old
code" snippet against text that was never seen, or wrongly place this
addition in the zip (which would overwrite Task 3's real file on
unzip — a live file this addition only ever adds to, never replaces),
the six functions below are documented in full under Code Changes as a
**pending addition**, ready to paste in once the real file is obtained
at integration. **This is the one open item blocking a fully clean
hand-in for this file** — flagging for the CEO rather than guessing.

## Code Changes

### `includes/chat-auth.php` → live path `admin/includes/chat-auth.php`

<!-- TASK-6 START -->
**Old code:** not available — the real file has never been supplied to
this task (see the note under Files Changed above). No original text
exists to copy from, so none is fabricated here.

**New code** (the exact pending addition — six functions, to be pasted
in alongside the file's existing `chat_auth`-table functions once the
real file is obtained):
```php
/**
 * generate_temp_password()
 * Produces a random, human-typeable temporary password for the reset
 * flow. Uses random_int() (CSPRNG-backed) rather than mt_rand(), and
 * excludes visually ambiguous characters (0/O, 1/l/I) since this string
 * has to be read from an email and typed back in by hand. At the
 * project's chosen length (14), against this 55-character alphabet,
 * that's 14 * log2(55) ~= 81 bits of entropy -- well above the
 * 12-character minimum the CEO's password policy requires, and far
 * beyond what's brute-forceable inside the temp password's short
 * expiry window.
 *
 * @param int $length Number of characters to generate (default 14).
 * @return string
 */
function generate_temp_password(int $length = 14): string
{
    $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
    $max = strlen($alphabet) - 1;
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $alphabet[random_int(0, $max)];
    }
    return $password;
}

/**
 * set_password_secret_hash()
 * The ONLY write Task 6 performs against the chat_auth table itself,
 * exactly matching the Shared Contract's limit. Called once per issued
 * reset, from chat-password-reset-request.php.
 *
 * @param string $principalId
 * @param string $secretHash  Output of password_hash() -- never the
 *                             plaintext temp password.
 * @return void
 */
function set_password_secret_hash(string $principalId, string $secretHash): void
{
    $db = chatDb();
    $stmt = $db->prepare('UPDATE chat_auth SET secret_hash = ?, updated_at = NOW() WHERE principal_id = ?');
    $stmt->execute([$secretHash, $principalId]);
}

/**
 * check_pin_throttle_reset_exempt()
 * Named, greppable stand-in for check_pin_throttle() that the password
 * reset-request flow calls instead of the real throttle. Always reports
 * "allowed" -- an active PIN/password throttle must never block the
 * recovery flow itself (a self-service recovery path that can be
 * throttled defeats its own purpose). Exists purely so that exemption is
 * explicit in code rather than an easy-to-miss "we just never call the
 * throttle here" -- a future edit that accidentally wires
 * check_pin_throttle() into the reset-request endpoint would be an
 * obvious, visible regression against this function's presence.
 *
 * Every other caller in the codebase must keep calling
 * check_pin_throttle() directly, unmodified -- this function must never
 * be used anywhere except the reset-request flow.
 *
 * @param string $principalId Unused -- kept to mirror check_pin_throttle()'s
 *                             signature so the two stay interchangeable.
 * @return array{allowed: bool, delay_seconds: int}
 */
function check_pin_throttle_reset_exempt(string $principalId): array
{
    return ['allowed' => true, 'delay_seconds' => 0];
}

/**
 * is_temp_password_pending()
 * Reports whether a principal's CURRENT chat_auth.secret_hash is still
 * an unused, unexpired temp password issued by the reset flow, versus a
 * normal standing password. This does not verify any password itself --
 * it only answers "is the secret currently sitting in chat_auth a
 * pending reset, or not."
 *
 * INTEGRATION DEPENDENCY FOR TASK 5A (flagged in Task-6-Completion.md,
 * not built by Task 6 -- outside this task's Scope Lock): whichever file
 * performs password_verify() against chat_auth.secret_hash for a login
 * attempt must, immediately after a successful verify, call this
 * function. If it returns is_temp = true, the login just succeeded using
 * a temp password: that endpoint must call consume_temp_password() below
 * and require the user to set a new password before granting full
 * access, rather than treating it as a normal successful login. This
 * satisfies the task requirement to "force a password change on next
 * successful login" with the temp password, without Task 6 needing to
 * touch Task 5a's login-verification file.
 *
 * @param string $principalId
 * @return array{is_temp: bool, expires_at: ?string}
 */
function is_temp_password_pending(string $principalId): array
{
    // TASK-6 BUGFIX (Round 2): this function is meant to be called from
    // Task 5a's login-verification code on every password login, not
    // just from chat-password-reset-request.php. On a fresh install
    // where no reset has ever been requested yet, chat_password_resets
    // wouldn't exist, and this function would throw "table doesn't
    // exist" on PDO's ERRMODE_EXCEPTION -- breaking every normal
    // password login, not just reset ones. Calling this here (cheap and
    // idempotent -- CREATE TABLE IF NOT EXISTS) makes this function
    // self-sufficient instead of silently depending on the
    // reset-request endpoint having run first.
    ensure_chat_password_resets_table();

    $db = chatDb();
    $stmt = $db->prepare(
        'SELECT expires_at, used_at FROM chat_password_resets WHERE principal_id = ? ORDER BY requested_at DESC LIMIT 1'
    );
    $stmt->execute([$principalId]);
    $row = $stmt->fetch();

    if (!$row || $row['used_at'] !== null) {
        return ['is_temp' => false, 'expires_at' => null];
    }

    $isExpired = strtotime($row['expires_at']) < time();
    if ($isExpired) {
        return ['is_temp' => false, 'expires_at' => null];
    }

    return ['is_temp' => true, 'expires_at' => $row['expires_at']];
}

/**
 * consume_temp_password()
 * Marks the principal's most recent pending reset row as used, enforcing
 * single-use regardless of whether the expiry window has elapsed (an
 * already-expired row is simply never returned as "pending" by
 * is_temp_password_pending() above, so this only ever needs to handle
 * the still-valid case). Must be called exactly once, by the login
 * endpoint, immediately after a successful temp-password verification --
 * see the integration dependency note above.
 *
 * @param string $principalId
 * @return void
 */
function consume_temp_password(string $principalId): void
{
    // TASK-6: same self-sufficiency reasoning as is_temp_password_pending()
    // above -- cheap, idempotent, and removes any ordering dependency on
    // the reset-request endpoint having run first.
    ensure_chat_password_resets_table();

    $db = chatDb();
    $stmt = $db->prepare(
        'UPDATE chat_password_resets SET used_at = NOW() WHERE principal_id = ? AND used_at IS NULL ORDER BY requested_at DESC LIMIT 1'
    );
    $stmt->execute([$principalId]);
}

/**
 * ensure_chat_password_resets_table()
 * Lazily creates the chat_password_resets table on first use, mirroring
 * the IF NOT EXISTS idempotent pattern chat-setup.php already uses for
 * every other chat table. This table is entirely new and owned by Task
 * 6 -- it is NOT part of the locked chat_auth shape, so creating it does
 * not conflict with "no further schema changes [to chat_auth] past this
 * point, by anyone." Called at the top of chat-password-reset-request.php
 * and defensively from the two functions above, before any
 * INSERT/SELECT against it.
 *
 * NOTE for CEO/Dev Manager: chat-setup.php is outside Task 6's file
 * scope, so this table is not registered there. Functionally
 * self-sufficient as-is; folding the CREATE TABLE below into
 * chat-setup.php's own migration list at some point would be a
 * reasonable follow-up, not a requirement.
 *
 * @return void
 */
function ensure_chat_password_resets_table(): void
{
    $db = chatDb();
    $db->exec(
        'CREATE TABLE IF NOT EXISTS chat_password_resets (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            principal_id VARCHAR(64) NOT NULL,
            requested_at DATETIME NOT NULL,
            expires_at DATETIME NOT NULL,
            used_at DATETIME NULL,
            request_ip VARCHAR(45) NULL,
            PRIMARY KEY (id),
            INDEX idx_principal_id (principal_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );
}
```
<!-- TASK-6 END -->

None of these six functions touch any `chat_auth` column other than
`secret_hash`, honoring the Shared Contract's limit ("Task 6 only ever
writes to secret_hash"); the temp-password tracking state lives in the
brand-new `chat_password_resets` table instead. This is a genuine
addition alongside the Shared Contract (not a change to anything
published) — flagged here for visibility since it wasn't pre-published.
Also considered and deliberately **rejected**: clearing
`failed_attempts`/`locked_until` on a successful reset to guarantee the
temp password works immediately even mid-lockout. Rejected because (a)
the Shared Contract restricts Task 6 to writing `secret_hash` only, and
(b) Task 3's PIN/password throttle is a growing delay, never a hard
lockout, so it can never fully block the temp-password login anyway —
only slow it down. If the CEO wants an immediate-unlock guarantee
instead, that's a one-line addition to `set_password_secret_hash()`, but
it would be a real Contract Deviation and is left out pending explicit
sign-off.

At integration time: pull Task 3's real `includes/chat-auth.php`, paste
the block above in alongside the existing `chat_auth`-table functions
(`issue_token` / `verify_token` / `invalidate_all_tokens` / `log_audit` /
`check_pin_throttle`), and run the final integration/lint pass on the
whole file (balanced braces, no duplicate function names) — Task 6 is
that file's documented last owner.

## New files

- **`admin/chat-password-reset-request.php`** — New file, in the zip
  (`Task-6-NewFiles.zip`). Public endpoint (no admin auth) implementing
  the full password-reset-request flow: confirms the recovery contact
  (email) on file for a given chat ID, issues a single-use 45-minute
  temp password by email only on a genuine match, pads every response to
  an identical fixed minimum time regardless of match, rate-limits by
  recovery contact, and never blocks on the PIN/password throttle. Calls
  the six `includes/chat-auth.php` functions documented above (contract
  stubs until that file is pulled at integration) and the real,
  confirmed `chatDb()`, `chatRespond()`, `chatJsonInput()`,
  `chatCheckRateLimit()` (from `chat-config.php`) and `sendContactEmail()`
  (from `contact-config.php`) — none of those three files are modified,
  only required/called into.

## Bugs Found Outside Task Scope
One thing noticed while reading the real, supplied `chat-config.php`,
not a Task 6 bug and not fixed (outside Scope Lock): the real
`DB_PASSWORD` constant in that file and the SMTP password read in
`contact-config.php` follow the same pattern/strength as each other,
suggesting possible credential reuse across the database and the mail
account. Worth a look from a general security-hygiene standpoint, but
it's an existing, already-deployed site credential — flagging only so
it's visible to the CEO, not acting on it.

## Self-Check Confirmation
Only two files were touched for Task 6: `chat-password-reset-request.php`
(new — in the zip, at live path `admin/chat-password-reset-request.php`)
and the documented pending addition to `includes/chat-auth.php` (live
path `admin/includes/chat-auth.php` — not yet merged into a real file,
since none was ever supplied; documented in full above, not placed in
the zip, since zipping a partial version of a file Task 3 owns would
overwrite Task 3's real work on unzip). `chat-config.php`,
`contact-config.php`, and `chat-setup.php` were read for reference only
(confirming real function signatures and table schemas) and were never
modified — none of the three appear in Files Changed or the zip. Every
code block above carries a `TASK-6`-numbered anchor comment and an
explanatory comment, per rule 6. Every "Old code" that exists to copy
was copied exactly as-is; where no original text was ever available
(`includes/chat-auth.php`), that is stated plainly rather than
fabricated. The zip (`Task-6-NewFiles.zip`) contains exactly one file,
`admin/chat-password-reset-request.php`, rooted at the site root,
verified with `unzip -l` before sending; it matches the one "New file"
row in Files Changed exactly. Checked the zip's one file for secrets —
it defines no credentials itself and only references `chat-config.php`
and `contact-config.php` by `require_once`, neither of which is
included; nothing under `applications/`, `uploads/`, `config.php`,
`pet.json`, or `.env` is present anywhere in the zip. No other project
file was created, edited, or renamed. No `.md`/`.txt` file was created
other than this required completion file.

## SECOND VERIFICATION ROUND

### Round 1 — real `contact-config.php` supplied; mail integration wired for real

`contact-config.php` was supplied after initial submission. This round
wired `send_password_reset_email()` in `chat-password-reset-request.php`
to the real `sendContactEmail()` helper it defines, replacing a guessed
placeholder function name. No other file was touched.

**Files Changed (this round)**

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `chat-password-reset-request.php` | `admin/chat-password-reset-request.php` | Changed (mail integration seam only) | No |

`contact-config.php` was read only, never modified, so it is not listed
as changed — see the general note on read-only references in Self-Check
Confirmation above.

**Code Changes (this round)**

`chat-password-reset-request.php`, inside `send_password_reset_email()`:

<!-- TASK-6 START -->
**Old code** (placeholder, guessed function name — copied exactly as it
existed in the previously-submitted version of this file):
```php
// INTEGRATION SEAM: swap this for contact-config.php's real
// send function once its signature is confirmed (see note above).
if (function_exists('sendSiteMail')) {
    sendSiteMail($to, $subject, $htmlBody);
} else {
    // Fail-safe fallback so this endpoint never fatals even if
    // the integration seam hasn't been wired yet -- matches
    // chat-mail.php's own "never break the request" contract.
    error_log('chat-password-reset-request.php: sendSiteMail() not available, temp password email NOT sent for a pending reset. Wire up contact-config.php\'s real mail function.');
}
```

**New code** (real call, confirmed against the supplied source):
```php
// TASK-6: EMAIL_ENABLED guard -- belt-and-suspenders on top of
// sendContactEmail()'s own internal try/catch, so a disabled or
// misconfigured mailer can never surface as anything other than the
// identical generic response this endpoint always returns.
if (!defined('EMAIL_ENABLED') || !EMAIL_ENABLED) {
    error_log('chat-password-reset-request.php: EMAIL_ENABLED is off, temp password NOT sent for a pending reset.');
    return;
}

// ... (subject/htmlBody construction unchanged) ...

// TASK-6: real call, matching contact-config.php's actual signature:
// sendContactEmail($recipients, $subject, $htmlBody, $attachments = []).
// $to is a single recovery-contact email address; sendContactEmail()
// accepts a bare string and normalizes it to a one-element array
// internally, so no wrapping is needed here.
$sendResult = sendContactEmail($to, $subject, $htmlBody);

if (empty($sendResult['success'])) {
    // sendContactEmail() never throws on a normal SMTP failure -- it
    // returns success=false instead -- so that case has to be checked
    // explicitly here, on top of the try/catch which only covers
    // require_once/class-loading failures.
    error_log('chat-password-reset-request.php: sendContactEmail failed: ' . ($sendResult['message'] ?? 'unknown error'));
}
```
<!-- TASK-6 END -->

Both blocks remain inside the same `try`/`catch (\Throwable $e)` already
in the file; that wrapper is unchanged. `sendContactEmail()`'s real
signature — `sendContactEmail($recipients, $subject, $htmlBody, $attachments = [])`,
returning `['success' => bool, 'message' => string, 'errors' => array]` —
was used exactly as defined; no wrapper or second mail path was built,
per rule 7.

**Bugs Found Outside Task Scope (this round):** none found.
`contact-config.php` was read only for its `sendContactEmail()`
signature; its SMTP credentials and admin recipient list are unrelated
to Task 6 and were left untouched.

**Self-Check (this round):** only `chat-password-reset-request.php` was
modified, and only inside `send_password_reset_email()`. The
added/changed lines carry `TASK-6` comments per rule 6.

---

### Round 2 — Full re-audit against both project summary docs, requested by the CEO

Asked to take the time to re-read both project summary docs and this
task's own section in full again and confirm no bugs, missing
requirements, or wrong assumptions, cross-checked line by line against
`adoption-website-project-summary.md`, `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`,
and the Task 6 section of `Admin-Project-1-Worker-Task-Assignments.md`.
This surfaced two real bugs and one risk worth hardening defensively.
All three were fixed directly in `chat-password-reset-request.php` and
the (still-pending) `includes/chat-auth.php` addition above — no new
files created.

**Bug 1 (severe) — `phone` column doesn't exist on `chat_threads`.** The
lookup query previously also selected and matched against a `phone`
column. On re-reading `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`'s
authoritative Database Schema table, `chat_threads`'s real column list
is `chat_id, email, full_name, reason, status, assigned_admin_id,
previous_chat_id, unread_for_admin, unread_for_user, last_activity_at,
archived` — no `phone` column. Since `chatDb()` is configured with
`PDO::ERRMODE_EXCEPTION`, selecting a nonexistent column throws on every
single call; the surrounding try/catch silently turned that into the
generic "no match" response every time — meaning this endpoint could
never have issued a single real reset, for anyone, with no visible
error. Fixed: the query now selects only `chat_id, email`; matching is
email-only.

**Bug 2 (hardened defensively at the time) — `chatCheckRateLimit()`'s
return shape was never actually published.** Unlike `check_pin_throttle()`,
whose shape the Shared Contract spells out, `chatCheckRateLimit()`'s
return shape was assumed (`{'allowed' => bool}`) rather than confirmed.
Hardened to fail open rather than closed if that assumption was wrong,
pending the real source — see Round 3, where this was confirmed and
fixed for real.

**Bug 3 (real, fixed) — `is_temp_password_pending()` could crash every
normal password login on a fresh install.** This function and
`consume_temp_password()` depend on the `chat_password_resets` table,
which was only being created by `ensure_chat_password_resets_table()`
when called from `chat-password-reset-request.php`. On a fresh install
with no prior reset request, Task 5a's first-ever password login would
hit "table doesn't exist" and crash. Fixed: both functions now call
`ensure_chat_password_resets_table()` themselves on entry.

**Additional files recommended for integration time, beyond Task 6's
original list:** `chat-setup.php` (to confirm `chat_threads`'s real
columns) and `chat-config.php` (to confirm `chatCheckRateLimit()`'s real
return shape). Both were subsequently supplied — see Round 3.

**Everything else checked and found correct:** `chatRespond()`'s
internal `exit` (confirms early `400`/`429` returns never fall through),
the 12-character password-policy minimum (a real, CEO-resolved decision,
not fabricated), `chatDb()`'s `PDO::ERRMODE_EXCEPTION` configuration,
and the Sync Notice (`Worker-Sync-Notice-Post-Task1-Task7.md`, confirmed
to raise no conflicts with any fix here). A small entropy-comment
inaccuracy was also fixed: the temp-password alphabet is 55 characters,
not 54 as originally written (the ~=80-bit entropy claim was already
correct either way; now written as the precise ~=81 bits).

**Contract Deviations (Round 2):** none against the published Shared
Contract — Bug 1's fix narrows an unconfirmed assumption back down to
what the contract and confirmed schema actually support.

**Enhancement Decisions (Round 2):** unchanged — this round was a bug
audit, not a revisit of the Recommended Enhancement.

**Bugs Found Outside Task Scope (Round 2):** none found outside Task 6's
own files.

**Self-Check (Round 2):** only `chat-password-reset-request.php` and the
pending `includes/chat-auth.php` addition were modified this round — the
same files already listed under Files Changed.

---

### Round 3 — real `chat-config.php` and `chat-setup.php` supplied; every remaining flag closed

The CEO supplied both files Round 2 asked for. Both open items from
Round 2 are now resolved for real rather than defensively guarded.

**Confirmed: `chat_threads` really has no `phone` column.**
`chat-setup.php`'s own `CREATE TABLE chat_threads` statement matches the
summary doc exactly — `id, chat_id, email, full_name, reason,
pet_context, status, assigned_admin_id, previous_chat_id, created_at,
last_activity_at, unread_for_admin, unread_for_user, archived`. No
`phone` column anywhere. Round 2's fix needed no further change.

**Bug 4 (real, now fixed for real) — `chatCheckRateLimit()` returns a
plain `bool`, not an array.** Confirmed directly from `chat-config.php`'s
source: it returns `true`/`false` directly, never an array, never an
`allowed` key. Round 2's defensive fallback meant `is_array($rate)` was
*always* false against the real return type, so the "fail open" branch
fired on every single request — the anti-spam throttle had never
actually been enforced at any point.

**Fix:**
<!-- TASK-6 START -->
**Old code** (defensive, guessed array shape — copied exactly as it
existed in the previously-submitted version of this file):
```php
$rateKey = 'pwreset:' . strtolower($recoveryContact);
$rate = chatCheckRateLimit($rateKey, CHAT_PW_RESET_RATE_MAX, CHAT_PW_RESET_RATE_WINDOW_SECONDS);
$rateAllowed = (is_array($rate) && array_key_exists('allowed', $rate)) ? (bool) $rate['allowed'] : true;
if (!$rateAllowed) {
```

**New code** (real, confirmed signature — returns a plain bool):
```php
// TASK-6 BUGFIX (Round 3 -- confirmed against the real chat-config.php):
// chatCheckRateLimit($key, $maxAttempts, $windowSeconds) returns a plain
// bool -- true if the attempt is allowed (and records it), false if the
// window's cap is already hit. It is NOT an array and has no 'allowed'
// key.
$rateKey = 'pwreset:' . strtolower($recoveryContact);
$rateAllowed = chatCheckRateLimit($rateKey, CHAT_PW_RESET_RATE_MAX, CHAT_PW_RESET_RATE_WINDOW_SECONDS);
if (!$rateAllowed) {
```
<!-- TASK-6 END -->

**Confirmed correct, no change needed:** `chatJsonInput()` always
returns an array (confirmed from source — the redundant `!is_array()`
guard around it was dead code and was removed for clarity);
`chatDb()`'s `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC`
(confirms every `$row['column']` access was already correct);
`chatRespond()`'s internal `exit`; and `chat_auth` correctly not
existing yet in `chat-setup.php` (Task 3 hasn't shipped it — expected,
this file predates the security-overhaul project).

**Bugs Found Outside Task Scope (Round 3):** see the top-level Bugs
Found Outside Task Scope section above (the `DB_PASSWORD`/SMTP password
pattern-reuse observation) — first noticed in this round, recorded once
at the top level rather than duplicated here.

**Contract Deviations (Round 3):** none — both fixes bring the code in
line with the real, confirmed behavior of existing site infrastructure.

**Enhancement Decisions (Round 3):** unchanged — the rate-limit
enhancement is still adopted, now correctly enforced.

**Self-Check (Round 3):** only `chat-password-reset-request.php` was
modified this round. `chat-setup.php`/`chat-config.php` were read only,
never modified. With this round, every flag raised in Round 2 is closed.

---

### Round 4 — full re-read of both project summary docs completed end to end

Previous rounds had read `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`
thoroughly but only the first ~900 of `adoption-website-project-summary.md`'s
2086 lines. Read the remaining ~1186 lines this round — confirmed
(via direct reading plus a targeted scan for
`chat`/`password`/`security`/`auth`/`session`/`token`) that the
remainder covers testimonials, pet-detail, blog, homepage, and services
page UI, none of it relevant to Task 6's backend scope. Also re-read the
full Warning & Instructions section, the full Shared Contracts section,
and Task 6's own section one more time, word for word, against the
actual submitted code.

**Fixed: `privacy-policy` link missing its trailing slash.** The only
documented instance of this exact link anywhere in either doc
(`adoption-website-project-summary.md` line 660) is
`https://docs.petcenterco.com/privacy-policy/` — with a trailing slash;
`contact-us` is documented consistently without one. The temp-password
email had `/privacy-policy` without the slash. Fixed to match exactly.

**Fixed: `log_audit()`'s `endpoint` parameter now includes `.php`,**
matching every other endpoint reference throughout both docs
(`chat-start.php`, `update-adoption.php`, etc.). Cosmetic only — the
`result` vocabulary Task 7's badge logic reads was untouched.

<!-- TASK-6 START -->
**Old code** (both fixes, copied exactly as they existed in the
previously-submitted version of this file):
```php
            . '<a href="https://docs.petcenterco.com/privacy-policy">Privacy Policy</a> | '
```
```php
        log_audit($principalId, 'chat-password-reset-request', 'issued');
```
```php
    log_audit($chatId !== '' ? $chatId : 'unknown', 'chat-password-reset-request', 'not_found_or_no_match');
```

**New code:**
```php
            . '<a href="https://docs.petcenterco.com/privacy-policy/">Privacy Policy</a> | '
```
```php
        log_audit($principalId, 'chat-password-reset-request.php', 'issued');
```
```php
    log_audit($chatId !== '' ? $chatId : 'unknown', 'chat-password-reset-request.php', 'not_found_or_no_match');
```
<!-- TASK-6 END -->

**Checked and confirmed correct, not a bug (worth recording why):**
Directive 8a and Task 6's own section both show the anchor-comment
format as `<!-- TASK-X START -->` / `<!-- TASK-X END -->`. This file and
the `chat-auth.php` addition use the same `<!-- -->` style now in this
rebuilt completion file; the live PHP source files themselves use `//
TASK-6 START` / `// TASK-6 END` inline, checked directly against real,
already-integrated precedent in the supplied `chat-setup.php`, which has
an actual shipped `/* BUGFIX-1 START ... */` / `/* BUGFIX-1 END */`
anchor pair in PHP comment syntax, not HTML comment syntax — confirming
PHP files adapt the anchor format to their own comment syntax in
practice. Also re-confirmed word for word against the Shared Contract:
`log_audit(principal_id, endpoint, result)`'s argument order,
`invalidate_all_tokens(principal_id)`, and
`check_pin_throttle(principal_id): {allowed, delay_seconds}`'s exact
shape — all three match this file's usage exactly.

**Contract Deviations / Enhancement Decisions / Bugs Found Outside Task
Scope (Round 4):** none.

**Self-Check (Round 4):** only `chat-password-reset-request.php` was
modified this round (the two small fixes above). The
`includes/chat-auth.php` addition was re-checked against the real
`chat-setup.php` anchor-comment precedent and needed no change.

---

### Round 5 — hand-in format updated to match `WORKER-DELIVERY-GUIDE.md`

The CEO supplied `WORKER-DELIVERY-GUIDE.md`, which replaces every prior
instruction about git/branches/GitHub/`reports/` with a two-part hand-in:
this completion file (now with a four-column Files Changed table and
explicit live paths) plus `Task-6-NewFiles.zip` for the one true new
file. **No code was changed this round** — this round only restructures
how the already-finished, already-verified work (Rounds 1–4 above) is
packaged and described. `includes/chat-auth.php`'s pending-addition
status (never supplied, so no real "old code" exists to diff against)
is unchanged by this round and remains the one open item — see the note
under Files Changed.

**Live paths assigned**, per the guide's mapping table and its own
`admin/` + same-filename rule for admin PHP files:
- `chat-password-reset-request.php` -> `admin/chat-password-reset-request.php`
- `includes/chat-auth.php` -> `admin/includes/chat-auth.php` (the task
  brief consistently refers to this file with its `includes/` prefix,
  unlike every other bare-named admin PHP file such as `chat-config.php`
  or `chat-setup.php`, confirming it lives in its own subfolder under
  `admin/`)

**Zip built and verified:** `Task-6-NewFiles.zip`, rooted at the site
root, contains exactly one file at `admin/chat-password-reset-request.php`,
checked with `unzip -l` before sending. Checked its contents for
secrets, `config.php`, `pet.json`, `.env`, and anything under
`applications/`/`uploads/` — none present; the file defines no
credentials of its own and only `require_once`s `chat-config.php` and
`contact-config.php` by relative path, neither of which is bundled.

**Contract Deviations / Enhancement Decisions / Bugs Found Outside Task
Scope (Round 5):** none — packaging only, no code changed.

**Self-Check (Round 5):** no file's code was modified this round. This
completion file was restructured in full to the new required format,
preserving every finding from Rounds 1–4 without loss. The zip was
built, listed, and checked against the secrets exclusion list before
being sent.

---

### Round 6 — zip naming and contents corrected per the CEO's clarification

The CEO clarified three points about hand-in that this round applies.
**No code was changed this round** — packaging only, same as Round 5.

1. **Zip name.** Must be `[exact project file name]-Task-[number].zip`,
   not a worker-chosen name. The project file this task was assigned
   from is `Admin-Project-1-Worker-Task-Assignments.md`, so the project
   name token is `Admin-Project-1`. Renamed from `Task-6-NewFiles.zip`
   to **`Admin-Project-1-Task-6.zip`**.
2. **This completion file now ships inside that same zip**, joined
   together with the new code file, rather than sent as a separate
   attachment. It sits at the zip's top level (as `Task-6-Completion.md`),
   not inside `admin/`, since it is documentation, not a live site path —
   flagging this plainly: if the zip is ever unzipped directly onto the
   site root, this one `.md` file would land in the site root alongside
   it and would need to be excluded or removed by whoever does that
   unzip. Worth the integrator's awareness, not a code concern.
3. **New paths not found in either summary doc must be heavily flagged,
   with the folder to create named explicitly.** Re-checked both docs
   against every path mentioned in this task:
   - `admin/chat-password-reset-request.php` — **not a new folder.**
     `admin/` itself is already a documented, existing folder in
     `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`'s file-structure section; only
     the filename is new, which is normal for any new file.
   - `admin/includes/chat-auth.php` — **this one needs the flag.**
     `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`'s documented file-structure
     tree has no `includes/` subfolder anywhere under `admin/` — every
     existing admin PHP file (`chat-config.php`, `chat-setup.php`,
     `chat-start.php`, `chat-mail.php`, etc.) sits bare, directly in
     `admin/`. **A new folder, `admin/includes/`, needs to be created on
     the live site to hold `chat-auth.php`** once Task 3 delivers it.
     This isn't Task 6's folder to create (Task 3 creates the file
     itself; Task 6 only adds to it later), but since Task 6 is that
     file's documented last owner and this is the clearest place to
     surface it, flagging it here heavily rather than assuming the
     integrator already knows.

Re-confirmed the `index.html`-only rule for any `.html` page: not
applicable to this task — Task 6 has no `.html` files, only `.php`.

Re-confirmed nothing new is pending beyond what Rounds 1–5 already
documented: the `includes/chat-auth.php` real-file gap (Files Changed
note) and the `DB_PASSWORD`/SMTP credential-reuse observation (Bugs
Found Outside Task Scope) remain the only two open items, both already
on record before this round began.

**Contract Deviations / Enhancement Decisions (Round 6):** none —
packaging only.

**Bugs Found Outside Task Scope (Round 6):** none new — see the
restated items above, both already logged in earlier rounds.

**Self-Check (Round 6):** no file's code was modified. This completion
file was updated in place (this section) and is now itself one of the
two files joined inside `Admin-Project-1-Task-6.zip`, alongside
`admin/chat-password-reset-request.php`. Re-verified with `unzip -l`
after rebuilding: both paths present, nothing under `applications/`,
`uploads/`, `config.php`, `pet.json`, or `.env`, zip name matches the
required `Admin-Project-1-Task-6.zip` pattern exactly.
