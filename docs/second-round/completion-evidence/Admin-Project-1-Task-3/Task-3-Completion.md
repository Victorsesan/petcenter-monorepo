# Task-3-Completion.md

## Task Number
**Task 3 — PIN System**

## What Was Required
Build an optional, numeric, fixed-length PIN per chat thread, entered from the pre-chat form right after email and chat ID, that gates reopening that thread. Declining it must show a risk warning before letting the customer proceed without one. PIN setup/management reuses the chat widget's existing accessibility/options panel (own section, not a new UI surface). Build the real `AuthGate` component per the Shared Contract for Task 4 and Task 5a to import. Create the real `chat_auth` table (PIN fields active, password fields left for Task 5a). No hard lockout on failed attempts — a throttle that only grows the wait, never fully blocks, and never blocks the underlying email+chatID lookup itself. Own `chat-start.php` as last owner (final integration pass required); not last owner of `includes/chat-auth.php` (Task 6 finalizes that file after me — my additions are anchored, not merged into the rest of the file).

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `chat-pin-setup.php` | `admin/chat-pin-setup.php` | New | No |
| `chat-auth.php` | **`admin/includes/chat-auth.php`** — **NEW FOLDER `admin/includes/`** (not in either project summary; created by Task 1, which owns this file's first version). Path is derived from the code: every `admin/chat-*.php` file loads it with `require_once __DIR__ . '/includes/chat-auth.php'`, so it must sit in an `includes/` folder directly inside `admin/`. | Changed (existing — Task 1's real submitted file) | Yes — Task 1 (owner before me) and Task 6 (final-pass owner after me) |
| `chat-start.php` | `admin/chat-start.php` | Changed (existing — Task 1's real submitted file) | Yes — Task 1 (owner before me); I am last owner |
| `chat-pin-manage.php` | `admin/chat-pin-manage.php` | New | No |
| `chat-widget.js` | `admin/chat-widget.js` | Changed (existing, out of my originally-assigned 2 shared files) | See Contract Deviations — touched with the CEO's explicit go-ahead after I flagged it as blocking; also shared with Task 1 (session tokens) and Task 4 (lock toggle) |
| `chat-lock.php` | `admin/chat-lock.php` | Changed (existing — Task 4's real submitted file; I touched only the `unlock` action) | Yes — Task 4 (owner before me; file is otherwise untouched) |

## Code Changes

### `chat-pin-setup.php` → live path `admin/chat-pin-setup.php`
New file — in the zip. One-time setup script, `CREATE TABLE IF NOT EXISTS chat_auth` matching the published Shared Contract shape (`principal_id`, `auth_tier`, `secret_hash`, `salt`, `failed_attempts`, `locked_until`, `created_at`, `updated_at`), same run-once-then-delete convention as the existing `chat-setup.php`/`chat-session-token-setup.php`. Final column shape (corrected in the SECOND VERIFICATION ROUND below — `principal_id` is `VARCHAR(20)` matching `chat_threads.chat_id`, not an earlier `VARCHAR(64)` guess):
```php
$db->exec(
    "CREATE TABLE IF NOT EXISTS chat_auth (
        id              INT AUTO_INCREMENT PRIMARY KEY,
        principal_id    VARCHAR(20) NOT NULL,
        auth_tier       ENUM('none','pin','password') NOT NULL DEFAULT 'none',
        secret_hash     VARCHAR(255) NULL,
        salt            VARCHAR(255) NULL,
        failed_attempts INT NOT NULL DEFAULT 0,
        locked_until    TIMESTAMP NULL DEFAULT NULL,
        created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_principal (principal_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);
```

### `chat-auth.php` → live path `admin/includes/chat-auth.php` (NEW FOLDER `admin/includes/` — see Files Changed)

**Old code** (exact text from the end of the real file Task 1 submitted — the last lines of `chatRequireSessionToken()` and its closing anchor, which appear only once in the file):
```php
    if ($token === '' || !verify_token($token, $principal_id)) {
        chatRespond([
            'success' => false,
            'error'   => 'A valid chat session is required. Please reopen this chat from the start screen.',
            'code'    => 'SESSION_REQUIRED',
        ], 401);
    }
}
// TASK-1 END
```

**New code** (same lines, with Task 3's whole addition appended immediately after — nothing above `// TASK-1 END` was changed):
```php
    if ($token === '' || !verify_token($token, $principal_id)) {
        chatRespond([
            'success' => false,
            'error'   => 'A valid chat session is required. Please reopen this chat from the start screen.',
            'code'    => 'SESSION_REQUIRED',
        ], 401);
    }
}
// TASK-1 END

// TASK-3 START — PIN system (auth_tier='pin' against the chat_auth table)
/**
 * Everything below is Task 3's addition. Nothing above this line was
 * changed. All functions read/write the `chat_auth` table created by
 * chat-pin-setup.php (see that file for the full column shape).
 *
 * THROTTLE DESIGN (not a hard lockout, by deliberate requirement):
 * a hard lockout on a PIN gate lets an attacker lock out the LEGITIMATE
 * customer just by knowing/guessing the chat ID and submitting wrong PINs
 * on purpose — that would be strictly worse than no PIN at all. Instead
 * this uses capped exponential backoff (confirmed current best practice
 * via web search — see this file's "Enhancement Decisions"):
 * the first CHAT_PIN_FREE_ATTEMPTS failures are free (a genuine typo
 * costs nothing), then each further failure doubles the required wait,
 * capped at CHAT_PIN_MAX_DELAY_SECONDS. There is never a point past
 * which the PIN becomes permanently unusable — only a growing wait,
 * which keeps a brute-force attacker's cost per guess climbing forever
 * while a legitimate customer who forgot their PIN can always eventually
 * retry (or use the options panel's own "forgot PIN" path once Task 5a's
 * account-recovery flow exists — out of this task's scope to build).
 */
define('CHAT_PIN_FREE_ATTEMPTS', 3);
define('CHAT_PIN_MAX_DELAY_SECONDS', 900); // 15 minute cap, same ceiling Task 1 used for token TTL reasoning

/**
 * Loads (or implicitly represents) a principal's current chat_auth row.
 * A principal with NO row at all is auth_tier 'none' by absence — a row
 * is only ever created the moment a PIN (or later, a password) is
 * actually set, never speculatively, mirroring the project's existing
 * no-orphan-rows convention (see chat-start.php's own no-orphan-threads
 * rule for the same philosophy applied to a different table).
 */
function chat_get_auth_row($principal_id) {
    $db = chatDb();
    $stmt = $db->prepare("SELECT * FROM chat_auth WHERE principal_id = ? LIMIT 1");
    $stmt->execute([$principal_id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/**
 * check_pin_throttle(principal_id): {allowed: bool, delay_seconds: int}
 *
 * Published Shared Contract signature. Read-only — never increments
 * failed_attempts itself (an attempt made while already throttled must
 * not push the wait out further, or a third party could keep a
 * legitimate customer permanently delayed just by hammering during their
 * own cooldown window). Call this BEFORE checking a submitted PIN;
 * record_pin_failure()/reset_pin_failures() below are what actually
 * mutate the row, called only after a real verification attempt runs.
 */
function check_pin_throttle($principal_id) {
    $row = chat_get_auth_row($principal_id);
    if (!$row || $row['auth_tier'] !== 'pin') {
        // No PIN configured at all — nothing to throttle.
        return ['allowed' => true, 'delay_seconds' => 0];
    }
    if ($row['locked_until'] !== null && strtotime($row['locked_until']) > time()) {
        return ['allowed' => false, 'delay_seconds' => strtotime($row['locked_until']) - time()];
    }
    return ['allowed' => true, 'delay_seconds' => 0];
}

/**
 * Records one failed PIN attempt and computes the next backoff window.
 * Capped exponential: delay = 2^(failures - CHAT_PIN_FREE_ATTEMPTS),
 * capped at CHAT_PIN_MAX_DELAY_SECONDS. Not part of the published
 * Shared Contract (only check_pin_throttle() is) — this is Task 3's own
 * internal helper, kept in this file since it operates on the same table
 * and row as check_pin_throttle().
 */
function record_pin_failure($principal_id) {
    $db = chatDb();
    $row = chat_get_auth_row($principal_id);
    if (!$row) return; // nothing to record against — caller should not reach here without a row

    $failures = (int)$row['failed_attempts'] + 1;
    $lockedUntil = null;
    if ($failures > CHAT_PIN_FREE_ATTEMPTS) {
        // Clamp the shift exponent itself (not just the final min()) before
        // shifting. PHP's 1 << n only behaves as a clean power-of-two up to
        // n=62 on a 64-bit int; past that it is implementation-defined and
        // can wrap toward zero. An astronomically high failure count should
        // never be able to wrap the delay back down to a tiny number and
        // defeat the throttle, so the exponent is capped at 30 (already far
        // past the point where CHAT_PIN_MAX_DELAY_SECONDS would have taken
        // over) before the shift ever runs.
        $delay = min(CHAT_PIN_MAX_DELAY_SECONDS, 1 << min($failures - CHAT_PIN_FREE_ATTEMPTS, 30));
        $lockedUntil = date('Y-m-d H:i:s', time() + $delay);
    }
    $db->prepare("UPDATE chat_auth SET failed_attempts = ?, locked_until = ? WHERE principal_id = ?")
       ->execute([$failures, $lockedUntil, $principal_id]);

    log_audit($principal_id, 'pin_verify', 'failed');
}

/** Resets the throttle state on a successful PIN verification. */
function reset_pin_failures($principal_id) {
    $db = chatDb();
    $db->prepare("UPDATE chat_auth SET failed_attempts = 0, locked_until = NULL WHERE principal_id = ?")
       ->execute([$principal_id]);
    log_audit($principal_id, 'pin_verify', 'tier_verified');
}

/**
 * verify_pin(principal_id, pin): bool
 * Does NOT check the throttle itself — callers (chat-start.php) must call
 * check_pin_throttle() first and refuse to even reach this function while
 * throttled, so a blocked attempt never touches password_verify() at all
 * (keeps the cost of a blocked guess at zero, not just "slow").
 *
 * Deliberately does NOT call verify_token() for this check (Sync Notice
 * confirms verify_token() is specifically for the session-token layer) —
 * this stays entirely inside the PIN tier's own verification path, only
 * borrowing log_audit() as the shared logging sink.
 */
function verify_pin($principal_id, $pin) {
    $row = chat_get_auth_row($principal_id);
    if (!$row || $row['auth_tier'] !== 'pin' || $row['secret_hash'] === null) {
        return false;
    }
    $ok = password_verify((string)$pin, $row['secret_hash']);
    if ($ok) {
        reset_pin_failures($principal_id);
    } else {
        record_pin_failure($principal_id);
    }
    return $ok;
}

/**
 * set_pin(principal_id, pin): void
 * Used both at fresh-thread creation (chat-start.php Path 3, optional)
 * and from the options panel's PIN management section (chat-pin-manage.php).
 * INSERT ... ON DUPLICATE KEY UPDATE against the UNIQUE(principal_id) key
 * chat-pin-setup.php created — safe to call whether or not a row already
 * exists for this principal.
 */
function set_pin($principal_id, $pin) {
    $db = chatDb();
    $hash = password_hash((string)$pin, PASSWORD_DEFAULT);
    $db->prepare(
        "INSERT INTO chat_auth (principal_id, auth_tier, secret_hash, failed_attempts, locked_until)
         VALUES (?, 'pin', ?, 0, NULL)
         ON DUPLICATE KEY UPDATE auth_tier = 'pin', secret_hash = VALUES(secret_hash),
             failed_attempts = 0, locked_until = NULL"
    )->execute([$principal_id, $hash]);
    // A PIN set/changed must invalidate any already-issued tokens for this
    // principal — otherwise a session opened before the PIN existed (or
    // under the OLD PIN) would stay valid straight through the change,
    // defeating the point of setting/changing it. Same reasoning Task 4
    // uses on lock and Task 5a uses on password change (see
    // invalidate_all_tokens()'s own docblock above).
    invalidate_all_tokens($principal_id);
    log_audit($principal_id, 'pin_set', 'pin_set');
}

/** remove_pin(principal_id): void — returns the thread to auth_tier 'none'. */
function remove_pin($principal_id) {
    $db = chatDb();
    $db->prepare(
        "UPDATE chat_auth SET auth_tier = 'none', secret_hash = NULL, failed_attempts = 0, locked_until = NULL
         WHERE principal_id = ?"
    )->execute([$principal_id]);
    invalidate_all_tokens($principal_id);
    log_audit($principal_id, 'pin_remove', 'pin_removed');
}

/** Convenience read used by chat-start.php and the widget: is this principal currently PIN-gated? */
function chat_requires_pin($principal_id) {
    $row = chat_get_auth_row($principal_id);
    return $row && $row['auth_tier'] === 'pin';
}
// TASK-3 END
```
(The audit-log `result` strings above — `tier_verified`, `pin_set`, `pin_removed` — are the Sync-Notice vocabulary fix; see SECOND VERIFICATION ROUND, item B1, for why they replaced a generic `'success'`.)

### `chat-start.php` → live path `admin/chat-start.php` (I am last owner — final integration pass performed)

**a) Docblock — old vs new** (extended with a TASK-3 explanation paragraph, no functional change):

Old code (exact, from the real file Task 1 submitted — appears once):
```php
 * A thread row is only ever created here, and only once — never speculatively
 * from a filled-but-unsubmitted form (no-orphan-threads rule, Section 4/7b).
 * The system auto-response (Section 8 reason map) is inserted as the first
 * message at the moment of creation, so it's real thread history, not a
 * client-side illusion.
 */
```
New code:
```php
 * A thread row is only ever created here, and only once — never speculatively
 * from a filled-but-unsubmitted form (no-orphan-threads rule, Section 4/7b).
 * The system auto-response (Section 8 reason map) is inserted as the first
 * message at the moment of creation, so it's real thread history, not a
 * client-side illusion.
 *
 * TASK-3 (PIN System) note: this file is also where PIN gating lives.
 * email+chatID lookup itself is NEVER blocked or throttled — Paths 1 & 2
 * below always resolve who the thread belongs to and always respond
 * success. What's gated is TOKEN ISSUANCE: if the resolved thread is
 * auth_tier='pin', a valid `pin` must also be present in this same
 * request before issue_token() is called — otherwise the response comes
 * back with authRequired=true and no sessionToken, and the widget must
 * re-submit this same endpoint with the PIN included once the customer
 * enters it (see AuthGate in chat-widget.js). This keeps the PIN gate
 * entirely inside chat-start.php (the only file Task 3 owns here) without
 * touching chat-poll.php or the chat_session_token table shape — a
 * caller can never get a session token for a PIN-protected thread
 * without the PIN, and every other hardened endpoint already refuses to
 * do anything without a valid token, so this one gate covers the whole
 * thread.
 */
```

**b) Input parsing block — old vs new** (adds the optional `$pin` read and its format check; existing lines shown as anchor, appear once):

Old code:
```php
$fullName  = trim($input['fullName'] ?? '');
$email     = trim($input['email'] ?? '');
$reason    = trim($input['reason'] ?? '');
$chatId    = trim($input['chatId'] ?? '');
$petContext = $input['petContext'] ?? null; // { name, petId, thumbUrl, pageUrl } or null, Section 7b
```
New code:
```php
$fullName  = trim($input['fullName'] ?? '');
$email     = trim($input['email'] ?? '');
$reason    = trim($input['reason'] ?? '');
$chatId    = trim($input['chatId'] ?? '');
$petContext = $input['petContext'] ?? null; // { name, petId, thumbUrl, pageUrl } or null, Section 7b
/* TASK-3 START — optional PIN input
   Dual-purpose depending on which path resolves below: on Path 1/2
   (resume) this is treated as a VERIFICATION attempt against an existing
   PIN; on Path 3 (brand-new thread) this is treated as the INITIAL PIN
   the customer is choosing to set, if they opted in on the pre-chat form.
   Never required — a thread with no PIN configured ignores this field
   entirely on every path. */
$pin = trim($input['pin'] ?? '');
/* TASK-3 END */
/* TASK-3 START — PIN format validation (only when one was actually submitted)
   6-digit numeric, fixed length — see "Enhancement Decisions" below for why
   6 over 4 (more guess-resistant given the throttle below has a finite
   free-attempt budget, still short enough to enter on a phone keypad
   without friction). */
if ($pin !== '' && !preg_match('/^\d{6}$/', $pin)) {
    chatRespond(['success' => false, 'error' => 'PIN must be exactly 6 digits.']);
}
/* TASK-3 END */
```

**c) Per-IP rate limit block — old vs new** (adds the coarser PIN-specific IP throttle right after the existing new-thread IP limit; Recommended Enhancement, adopted):

Old code (exact existing new-thread IP rate-limit call, the anchor line for this insertion — appears once):
```php
// Basic rate limiting by IP — prevents trivial flood of new threads
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!chatCheckRateLimit('start_ip:' . $ip, 10, 600)) { // 10 attempts / 10 minutes
    chatRespond(['success' => false, 'error' => 'Too many attempts. Please wait a few minutes and try again.'], 429);
}
```
New code:
```php
// Basic rate limiting by IP — prevents trivial flood of new threads
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!chatCheckRateLimit('start_ip:' . $ip, 10, 600)) { // 10 attempts / 10 minutes
    chatRespond(['success' => false, 'error' => 'Too many attempts. Please wait a few minutes and try again.'], 429);
}
/* TASK-3 START — coarse per-IP throttle on PIN attempts specifically
   (Recommended Enhancement from the task brief, adopted). check_pin_throttle()
   above is per-principal_id (one specific chat thread) and is the primary,
   required defense; this is a second, coarser layer catching an attacker
   who spreads guesses across MANY different chat IDs from one IP rather
   than hammering a single one — reuses the exact existing chatCheckRateLimit()
   helper (already required via chat-config.php), nothing new invented. Only
   consulted when a PIN is actually being submitted, so it never affects
   the plain lookup case. */
if ($pin !== '' && !chatCheckRateLimit('pin_ip:' . $ip, 20, 600)) { // 20 PIN attempts / 10 minutes / IP
    chatRespond(['success' => false, 'error' => 'Too many PIN attempts from this connection. Please wait a few minutes and try again.'], 429);
}
/* TASK-3 END */
```
(The exact text of the pre-existing `chat_ip` rate-limit call is reproduced above only as the anchor line the integrator matches on; it is unchanged — my addition goes immediately after it.)

**d) New shared gate helper — old vs new** (added immediately before the `// ── Path 1` comment; that comment is the anchor, shown unchanged in both):

Old code:
```php
// ── Path 1: explicit chat ID given ──────────────────────────────────────
```
New code:
```php
/* TASK-3 START — shared PIN-gate helper for Paths 1 & 2
   Given a resolved thread, decides whether to issue a token now or return
   authRequired instead. Kept as one local closure so Path 1 and Path 2
   (identical PIN logic, different SQL above them) don't duplicate it. */
function chat_gate_and_issue_token($thread) {
    $chatId = $thread['chat_id'];

    if (!chat_requires_pin($chatId)) {
        return ['token' => issue_token($chatId), 'authRequired' => false];
    }

    global $pin;
    if ($pin === '') {
        // No PIN submitted at all — normal first pass through the gate for a
        // returning customer who doesn't yet know a PIN is required.
        return ['token' => null, 'authRequired' => true];
    }

    $throttle = check_pin_throttle($chatId);
    if (!$throttle['allowed']) {
        chatRespond([
            'success'      => true,
            'authRequired' => true,
            'throttled'    => true,
            'delaySeconds' => $throttle['delay_seconds'],
            'chatId'       => $chatId,
            'status'       => $thread['status'],
            'error'        => 'Too many attempts. Please wait before trying again.',
        ]);
    }

    if (verify_pin($chatId, $pin)) {
        return ['token' => issue_token($chatId), 'authRequired' => false];
    }

    // Wrong PIN — same generic message regardless of WHY it was wrong, so a
    // timing/response difference never tells an attacker anything the
    // authRequired=true response didn't already reveal.
    chatRespond([
        'success'      => true,
        'authRequired' => true,
        'chatId'       => $chatId,
        'status'       => $thread['status'],
        'error'        => 'Incorrect PIN. Please try again.',
    ]);
}
/* TASK-3 END */

// ── Path 1: explicit chat ID given ──────────────────────────────────────
```

**e) Path 1 — token issuance replaced with the gated version:**

Old code:
```php
        /* TASK-1 START — issue a fresh session token on every successful
           resume (Path 1: explicit chat ID). A fresh token is issued on
           each resume rather than trying to reuse/return a prior one —
           simpler, and it means a customer resuming on a new device/browser
           naturally gets their own valid token without needing to reason
           about whether an old one is still around. */
        $sessionToken = issue_token($thread['chat_id']);
        /* TASK-1 END */
```
New code:
```php
        /* TASK-3 START — PIN gate before token issuance (Path 1) */
        $gate = chat_gate_and_issue_token($thread);
        if ($gate['authRequired']) {
            chatRespond([
                'success'      => true,
                'authRequired' => true,
                'chatId'       => $thread['chat_id'],
                'status'       => $thread['status'],
            ]);
        }
        $sessionToken = $gate['token'];
        /* TASK-3 END */
```
(`issue_token()` itself, inside `chat_gate_and_issue_token()`, is Task 1's original function, called exactly as before for the no-PIN and correct-PIN cases — only WHEN it's called changed.)

**f) Path 2 — identical change, same reasoning:**

Old code:
```php
    /* TASK-1 START — issue a fresh session token on every successful
       resume (Path 2: email match). Same reasoning as Path 1 above. */
    $sessionToken = issue_token($existing['chat_id']);
    /* TASK-1 END */
```
New code:
```php
    /* TASK-3 START — PIN gate before token issuance (Path 2) */
    $gate = chat_gate_and_issue_token($existing);
    if ($gate['authRequired']) {
        chatRespond([
            'success'      => true,
            'authRequired' => true,
            'chatId'       => $existing['chat_id'],
            'status'       => $existing['status'],
        ]);
    }
    $sessionToken = $gate['token'];
    /* TASK-3 END */
```

**g) Path 3 — optional initial PIN set, added right after the new thread row/ID exist, before the welcome-message logic:**

Old code (exact existing lines establishing `$threadId`, the anchor for this insertion):
```php
$threadId = $db->lastInsertId();
```
New code:
```php
$threadId = $db->lastInsertId();

/* TASK-3 START — optional initial PIN on a brand-new thread
   Only meaningful here on Path 3: a fresh thread has no prior PIN to
   verify against, so any `pin` present at this point is the customer's
   own choice to set one up right now (surfaced by the pre-chat form's
   optional PIN section — see chat-widget.js). Placed after the thread
   row exists (needs the real chat_id) but before notification/response,
   same "only after the thing it depends on is committed" ordering the
   rest of this path already follows. */
if ($pin !== '') {
    set_pin($newChatId, $pin);
}
/* TASK-3 END */
```

### `chat-pin-manage.php` → live path `admin/chat-pin-manage.php`
New file — in the zip. Session-token-authenticated endpoint for the options panel's PIN management section: `status` (read-only tier check), `set` (no existing PIN), `change`/`remove` (both require the current PIN, throttled via `check_pin_throttle()`/`verify_pin()` exactly like `chat-start.php`'s resume gate). Every mutation reissues a session token (via `issue_token()`) because `set_pin()`/`remove_pin()` call `invalidate_all_tokens()` internally.

### `chat-widget.js` → live path `admin/chat-widget.js` (see Contract Deviations — touched beyond my originally-assigned 2 shared files, with explicit CEO authorization; also shared with Task 1 and Task 4 — see SECOND VERIFICATION ROUND for the full three-way merge)

**a) `CONFIG` — options-panel "PIN Protection" row + panel, added after `accessibilityRow`/`accessibilityPanel` and before `endSessionRow` in `buildOptionsSheet()`'s `body` array** (anchor is the existing array literal, which appears once):

Old code (exact text from the real file I was given — Task 4's baseline, which already had its own Lock Toggle section merged in; appears once):
```javascript
    const body = el('div', { class: 'pcw-sheet-body' }, [
      soundRow, transcriptRow, accessibilityRow, accessibilityPanel,
      lockRow, lockDelayPanel, // TASK-4 — Lock Toggle section
      endSessionRow,
    ]);
```
New code:
```javascript
    const body = el('div', { class: 'pcw-sheet-body' }, [
      soundRow, transcriptRow, accessibilityRow, accessibilityPanel,
      pinRow, pinPanel, // TASK-3 — PIN Protection section
      lockRow, lockDelayPanel, // TASK-4 — Lock Toggle section
      endSessionRow,
    ]);
```
(`pinRow`/`pinPanel` are built by the full TASK-3-anchored block above this array — row, expandable panel, and the `status`/`set`/`change`/`remove` calls against `chat-pin-manage.php` — kept out of this snippet for length; the complete code is in the real file, anchored `// TASK-3 START` … `// TASK-3 END`. `lockRow`/`lockDelayPanel` are Task 4's, shown only so the array's real final order is unambiguous.)

**b) CSS — "PIN Protection" options-panel section** (added immediately after the existing `.pcw-accessibility-panel.open` rule):

Old code (exact existing rule, the anchor, appears once):
```css
.pcw-accessibility-panel.open { max-height: 70px; opacity: 1; margin-top: 10px; padding-bottom: 4px; }
```
New code:
```css
.pcw-accessibility-panel.open { max-height: 70px; opacity: 1; margin-top: 10px; padding-bottom: 4px; }
/* TASK-3 START — PIN Protection section of the options panel.
   Own self-contained block, own class names, appended after the
   accessibility section per the options-panel section contract — never
   interleaved with another feature's markup. */
.pcw-pin-panel {
  max-height: 0; opacity: 0; overflow: hidden; display: flex; flex-direction: column; gap: 10px;
  transition: max-height 0.3s ease, opacity 0.25s ease, margin 0.3s ease;
}
.pcw-pin-panel.open { max-height: 220px; opacity: 1; margin-top: 10px; padding-bottom: 6px; }
.pcw-pin-panel input {
  width: 100%; box-sizing: border-box; padding: 9px 11px; border-radius: 8px;
  border: 1.5px solid rgba(255,255,255,0.15); background: rgba(255,255,255,0.04);
  color: #e5e5e5; font-family: 'Sora', system-ui, sans-serif; font-size: 13.5px; letter-spacing: 2px;
}
.pcw-pin-panel input:focus { outline: none; border-color: #ED7624; }
.pcw-pin-panel .pcw-pin-msg { font-size: 11.5px; color: #7a6d5f; }
.pcw-pin-panel .pcw-pin-msg.error { color: #ef4444; }
.pcw-pin-panel .pcw-pin-msg.success { color: #4ade80; }
.pcw-pin-panel .pcw-pin-actions { display: flex; gap: 8px; }
.pcw-pin-panel .pcw-pin-actions button {
  flex: 1; padding: 8px 10px; border-radius: 8px; border: none; cursor: pointer;
  font-family: 'Sora', system-ui, sans-serif; font-size: 12.5px;
}
.pcw-pin-panel .pcw-pin-save { background: #ED7624; color: #fff; }
.pcw-pin-panel .pcw-pin-remove { background: rgba(239,68,68,0.15); color: #ef4444; }
/* TASK-3 END */
```

**c) CSS — pre-chat gate PIN section** (added immediately after the existing `.pcw-field-hint` rule):

Old code (exact existing rule, the anchor, appears once):
```css
.pcw-field-hint { font-size: 11px; color: #7a6d5f; margin-top: 4px; }
```
New code:
```css
.pcw-field-hint { font-size: 11px; color: #7a6d5f; margin-top: 4px; }
/* TASK-3 START — pre-chat gate PIN section (own block, not interleaved
   with the existing field styles above/below it). */
.pcw-prechat-pin-toggle {
  display: flex; align-items: center; gap: 6px; cursor: pointer; color: #d7bd88;
  font-family: 'Sora', system-ui, sans-serif; font-size: 12.5px; padding: 4px 0; margin-top: 2px;
}
.pcw-prechat-pin-toggle i { font-size: 10px; transition: transform 0.2s ease; }
.pcw-prechat-pin-toggle.open i { transform: rotate(180deg); }
.pcw-prechat-pin-panel {
  max-height: 0; opacity: 0; overflow: hidden; display: flex; flex-direction: column; gap: 8px;
  transition: max-height 0.3s ease, opacity 0.25s ease, margin 0.3s ease;
}
.pcw-prechat-pin-panel.open { max-height: 160px; opacity: 1; margin-top: 6px; }
.pcw-prechat-pin-warning {
  display: none; margin-top: 8px; padding: 10px 12px; border-radius: 8px;
  background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.25);
  font-family: 'Sora', system-ui, sans-serif; font-size: 12px; color: #e5b8b8;
}
.pcw-prechat-pin-warning.show { display: block; }
.pcw-prechat-pin-warning .pcw-pin-warning-actions { display: flex; gap: 8px; margin-top: 8px; }
.pcw-prechat-pin-warning button {
  flex: 1; padding: 7px 8px; border-radius: 7px; border: none; cursor: pointer; font-size: 12px;
  font-family: 'Sora', system-ui, sans-serif;
}
.pcw-pin-warning-setup { background: #ED7624; color: #fff; }
.pcw-pin-warning-skip { background: rgba(255,255,255,0.08); color: #e5e5e5; }
.pcw-authgate-msg { font-size: 12px; margin-top: 6px; }
.pcw-authgate-msg.error { color: #ef4444; }
/* TASK-3 END */
```

**d) Pre-chat gate — new PIN field block** (`pinToggle`/`pinInput`/`pinConfirmInput`/`pinPanel`/`pinWarning`/`pinField`), inserted into the field-building code and appended to the form immediately after `chatIdField`, before `errorMsg`. Full block (anchored `// TASK-3 START` … `// TASK-3 END` in the real file — shown here as delivered, including the SECOND VERIFICATION ROUND wording fix):
```javascript
// TASK-3 START — pre-chat PIN section (own field, collapsed by default so
// the already-packed form isn't overwhelmed; expands to set/confirm a new
// PIN, or to re-enter an existing one on resume).
const pinToggle = el('div', { class: 'pcw-prechat-pin-toggle' }, [
  el('span', {}, 'Set up or enter a PIN'),
  el('i', { class: 'fas fa-chevron-down' }),
]);
const pinInput = el('input', { type: 'password', inputmode: 'numeric', maxlength: '6', placeholder: '6-digit PIN', name: 'pcwPin' });
const pinConfirmInput = el('input', { type: 'password', inputmode: 'numeric', maxlength: '6', placeholder: 'Confirm PIN', name: 'pcwPinConfirm' });
const pinPanel = el('div', { class: 'pcw-prechat-pin-panel' }, [
  el('div', { class: 'pcw-field-hint' }, "Leave blank if you're not sure — we'll also check by your email."),
  pinInput,
  el('div', { class: 'pcw-field-hint' }, 'If this chat already has a PIN, enter it here to reopen it. Setting a new PIN on a new chat keeps others from reopening it with just your email.'),
  pinConfirmInput,
]);
pinToggle.addEventListener('click', () => {
  const isOpen = pinPanel.classList.toggle('open');
  pinToggle.classList.toggle('open', isOpen);
});

const pinWarning = el('div', { class: 'pcw-prechat-pin-warning' }, [
  el('div', {}, 'If this chat doesn’t already have a PIN, anyone who has your email or chat ID could reopen it.'),
  el('div', { class: 'pcw-pin-warning-actions' }, [
    (() => {
      const b = el('button', { type: 'button', class: 'pcw-pin-warning-setup' }, 'Set up a PIN');
      b.addEventListener('click', () => {
        pinWarning.classList.remove('show');
        if (!pinPanel.classList.contains('open')) { pinToggle.click(); }
        pinInput.focus();
      });
      return b;
    })(),
    (() => {
      const b = el('button', { type: 'button', class: 'pcw-pin-warning-skip' }, 'Continue without a PIN');
      b.addEventListener('click', () => {
        pinWarningAcknowledged = true;
        pinWarning.classList.remove('show');
        form.requestSubmit ? form.requestSubmit(submitBtn) : submitBtn.click();
      });
      return b;
    })(),
  ]),
]);
let pinWarningAcknowledged = false;
const pinField = el('div', { class: 'pcw-field' }, [pinToggle, pinPanel, pinWarning]);
// TASK-3 END
```
(The text fixed in the SECOND VERIFICATION ROUND — the `pinWarning` first line above — originally read "Without a PIN, anyone who has your email or chat ID can reopen this conversation." The version shown above is the corrected, final text already in the delivered file.)

**e) Submit handler — validation + PIN gate response handling:**

Old code:
```javascript
      dropdownTrigger.classList.remove('pcw-field-error');

      submitBtn.disabled = true;
      submitBtn.textContent = 'Starting...';

      const fd = new FormData(form);
      const payload = {
        fullName: fd.get('fullName').trim(),
        email: fd.get('email').trim(),
        /* TASK-9 START */
        // TASK-9b: optional phone field — sent along with the rest of
        // the pre-chat payload; empty string if the customer left it blank.
        phone: fd.get('phone').trim(),
        /* TASK-9 END */
        reason: selectedReason,  // use the tracked value directly, not fd.get (more reliable)
        chatId: fd.get('chatId').trim(),
      };
```
New code:
```javascript
      dropdownTrigger.classList.remove('pcw-field-error');

      // TASK-3 START — PIN section validation + risk-warning gate
      const pinVal = pinInput.value.trim();
      const pinConfirmVal = pinConfirmInput.value.trim();
      if (pinVal !== '' || pinConfirmVal !== '') {
        if (!/^\d{6}$/.test(pinVal)) {
          errorMsg.textContent = 'PIN must be exactly 6 digits.';
          errorMsg.classList.add('show');
          return;
        }
        if (pinVal !== pinConfirmVal) {
          errorMsg.textContent = 'PINs do not match.';
          errorMsg.classList.add('show');
          return;
        }
      } else if (!pinWarningAcknowledged) {
        pinWarning.classList.add('show');
        return;
      }
      // TASK-3 END

      submitBtn.disabled = true;
      submitBtn.textContent = 'Starting...';

      const fd = new FormData(form);
      const payload = {
        fullName: fd.get('fullName').trim(),
        email: fd.get('email').trim(),
        /* TASK-9 START */
        phone: fd.get('phone').trim(),
        /* TASK-9 END */
        reason: selectedReason,
        chatId: fd.get('chatId').trim(),
        /* TASK-3 START */
        pin: pinVal,
        /* TASK-3 END */
      };
```

**f) Submit handler — response handling (PIN gate + token storage), final merged state with Task 1's real session-token plumbing:**

Old code:
```javascript
      try {
        const json = await apiPost('chat-start.php', payload);
        if (json.success) {
          // TASK-9 START
          // TASK-9c: true here — this is the fresh pre-chat submission
          // path, the one moment the entrance animation should play.
          openLiveThread(json.chatId, json.fullName, json.email, json.status, undefined, true);
          // TASK-9 END
        } else {
```
New code:
```javascript
      try {
        const json = await apiPost('chat-start.php', payload);
        /* TASK-3 START — PIN gate response handling
           chat-start.php responds success:true, authRequired:true (no
           sessionToken) when the resolved thread needs a PIN this request
           didn't supply/didn't get right — show AuthGate instead of
           treating it as a normal success or a hard error. */
        if (json.authRequired) {
          renderPreChatAuthGate(payload, json);
          return;
        }
        /* TASK-3 END */
        if (json.success) {
          // TASK-9 START
          // TASK-9c: true here — this is the fresh pre-chat submission
          // path, the one moment the entrance animation should play.
          openLiveThread(json.chatId, json.fullName, json.email, json.status, undefined, true);
          // TASK-9 END
        } else {
```
(The `sessionToken` returned by `chat-start.php` is now stored by `openLiveThread()`'s own 7th parameter, per Task 1's real plumbing — see SECOND VERIFICATION ROUND item B2. An earlier round of this file stored it via a standalone `storageKeySessionToken`/`localStorage.setItem` line at this call site; that line no longer exists, superseded by Task 1's real mechanism.)

**g) `renderPreChatAuthGate()` and the shared `pcwRenderAuthGate()`** — the real Shared Contract `AuthGate` (`tier`, `principalId`, `onVerifySuccess`, `onVerifyFail`). Both are new top-level functions, not a change to an existing one, so no old/new pair applies — full code is in SECOND VERIFICATION ROUND item B2, anchored `// TASK-3 START` … `// TASK-3 END` in the real file.

### `chat-lock.php` → live path `admin/chat-lock.php` (Task 4's file — I touched only the `unlock` action; rest of the file is untouched)

Old code (exact text from Task 4's real submitted file, appears once):
```php
    case 'unlock':
        // TODO(integration): once Task 3's real AuthGate + chat_auth table
        // exist, replace this stubbed check with a real call to
        // verify_token()/the PIN-verification path Task 3 builds. For now
        // this only demonstrates the flow end-to-end against the contract.
        $secret = $input['secret'] ?? '';
        if ($secret === '') {
            // No result string in the proposed vocabulary covers a failed
            // attempt — 'unlock_failed' is this file's own choice for that
            // case only; the success path below uses the exact vocabulary
            // string ('unlocked'), not this naming.
            log_audit($principalId, 'chat-lock.php', 'unlock_failed');
            echo json_encode(['success' => false, 'reason' => 'missing_secret']);
            break;
        }
        // STUB — Task 3 owns real PIN verification; this file only wires
        // the contract call site so integration is a one-line swap.
        $verified = true; // placeholder until Task 3's real verify path is integrated
        if ($verified) {
```
New code:
```php
    case 'unlock':
        $secret = $input['secret'] ?? '';
        if ($secret === '') {
            // No result string in the proposed vocabulary covers a failed
            // attempt — 'unlock_failed' is this file's own choice for that
            // case only; the success path below uses the exact vocabulary
            // string ('unlocked'), not this naming.
            log_audit($principalId, 'chat-lock.php', 'unlock_failed');
            echo json_encode(['success' => false, 'reason' => 'missing_secret']);
            break;
        }
        /* TASK-3 START — integration: replaces the $verified = true stub
           above with the real PIN-verification path, per this file's own
           TODO explicitly inviting a one-line swap once check_pin_throttle()/
           verify_pin() existed (includes/chat-auth.php, Task 3). Mirrors
           chat-start.php's resume gate exactly: throttle is checked BEFORE
           the secret is ever compared, so a blocked attempt never reaches
           password_verify() at all — not just slowed, genuinely untried. */
        if (!chat_requires_pin($principalId)) {
            // No PIN configured for this thread at all — nothing to verify
            // against. Not this file's call whether that's a reachable
            // state (Task 4 owns set_preference/enable logic) — the safe
            // default here is to refuse rather than silently unlock
            // against an unconfigured PIN.
            log_audit($principalId, 'chat-lock.php', 'unlock_failed');
            echo json_encode(['success' => false, 'reason' => 'no_pin_configured']);
            break;
        }

        $throttle = check_pin_throttle($principalId);
        if (!$throttle['allowed']) {
            // No attempt recorded — check_pin_throttle() is read-only by
            // design (see its own docblock); an attempt made WHILE already
            // throttled must not push the wait out further.
            echo json_encode(['success' => false, 'throttled' => true, 'delaySeconds' => $throttle['delay_seconds'], 'reason' => 'throttled']);
            break;
        }

        $verified = verify_pin($principalId, $secret); // also records success/failure + resets/advances the throttle internally
        /* TASK-3 END */
        if ($verified) {
```
The rest of the `unlock` case's success branch also changed, to reissue a session token (unchanged failure branch omitted — only the success branch differs):

Old code:
```php
        if ($verified) {
            $stmt = $db->prepare("UPDATE chat_lock_prefs SET locked = 0, panel_closed_at = NULL WHERE principal_id = ?");
            $stmt->execute([$principalId]);
            // Exact string 'unlocked' per Worker-Sync-Notice-Post-Task1-Task7.md's
            // proposed audit vocabulary table.
            log_audit($principalId, 'chat-lock.php', 'unlocked');
            echo json_encode(['success' => true]);
        } else {
```
New code:
```php
        if ($verified) {
            $stmt = $db->prepare("UPDATE chat_lock_prefs SET locked = 0, panel_closed_at = NULL WHERE principal_id = ?");
            $stmt->execute([$principalId]);
            // Exact string 'unlocked' per Worker-Sync-Notice-Post-Task1-Task7.md's
            // proposed audit vocabulary table.
            log_audit($principalId, 'chat-lock.php', 'unlocked');
            /* TASK-3 START — pcw_lock_trigger() called invalidate_all_tokens()
               at the moment this thread locked, so the client's own token is
               dead by now — without reissuing one here, every chat-poll.php
               call after a successful unlock would keep failing 401 forever
               and the "unlock" would look broken even though the PIN check
               itself passed. Same reasoning as chat-pin-manage.php's
               set/change/remove responses, which reissue for the identical
               reason. */
            $freshToken = issue_token($principalId);
            echo json_encode(['success' => true, 'sessionToken' => $freshToken]);
            /* TASK-3 END */
        } else {
```

## New Folders (READ THIS — integrator must create these)

> **NEW FOLDER REQUIRED: `admin/includes/`** — create an `includes` folder directly inside `admin/` (next to `chat-config.php`, `chat-start.php`, etc.). It does not appear in `adoption-website-project-summary.md` or `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`. It holds `chat-auth.php`, which Task 1 creates (new file) and Task 3 then extends. **Task 3 does NOT put `chat-auth.php` in its zip** (it is a changed file, shown as old/new code above); the folder must exist because Task 1's version goes there. Every chat endpoint requires it via `__DIR__ . '/includes/chat-auth.php'`, so nothing in the chat system works until it is in place.

Folders used by this task's zip that already exist in the summaries (no action needed): `admin/` (all `chat-*.php` files live directly inside it). No `.html` pages were created by this task, so no `index.html` folder pages apply.

## New files
Zip: `Admin-Project-1-Task-3.zip` — unzip on top of the site root. Contents:
```
Admin-Project-1-Task-3.zip
├── Task-3-Completion.md          <- this document (documentation only; do NOT deploy to the live site)
└── admin/
    ├── chat-pin-setup.php        <- new file
    └── chat-pin-manage.php       <- new file
```
- `admin/chat-pin-setup.php` — New file — in the zip. One-time setup script for the `chat_auth` table; delete from the live server after running it once.
- `admin/chat-pin-manage.php` — New file — in the zip. Session-token-authenticated PIN set/change/remove/status endpoint called by the options panel.

## Contract Deviations
- **`chat-widget.js` touched, beyond the "2 shared" files (`chat-start.php`, `includes/chat-auth.php`) named in my task brief.** I flagged this mid-task as blocking (rule 7 requires extending the exact existing prechat form / options panel markup, not recreating it, and I had no source for that file yet) and the CEO supplied the real file and explicitly authorized proceeding with full autonomy ("do whats recommended best... do everything no waiting") rather than holding for further sign-off. Everything I added there is anchored `// TASK-3 START/END` / `/* TASK-3 START/END */` exactly like my other files, and I touched only the sections directly required for the PIN prechat notice, the options-panel PIN section, and the `AuthGate` component — nothing else in that file was changed.
- **`salt` column on `chat_auth` left unpopulated for the PIN tier.** The contract's full row shape includes `salt`, and the column is created exactly as published — but `password_hash()` (PHP's own current-best-practice password hashing, confirmed via web search) already self-salts internally, so a second manual salt is redundant for the PIN tier specifically. Left `NULL` for PIN rows; Task 5a decides independently whether the password tier needs it populated.
- **PIN length fixed at 6 digits.** The brief says "numeric with a fixed length" without specifying the digit count. Picked 6 over the more common 4 for extra guess-resistance given the throttle has a finite free-attempt budget, while staying short enough for one-handed phone entry.
- **`chat-lock.php` touched.** Not one of my originally-assigned files at all — Task 4's file. Justified because its own `unlock` action had an explicit, in-code TODO inviting exactly this swap once `verify_pin()`/`check_pin_throttle()` existed, and nothing else in the file was changed.
- **Live path of `chat-auth.php` is `admin/includes/chat-auth.php`, not a bare `includes/`.** My task text says `includes/chat-auth.php` (relative to the admin files). Neither project summary lists an `includes/` folder, so the real location was derived from the code itself (`require_once __DIR__ . '/includes/chat-auth.php'` in `chat-start.php`, `chat-pin-manage.php` and `chat-lock.php`, all of which live in `admin/`). `admin/includes/` is a NEW FOLDER the integrator must create — see the New Folders section.

## Enhancement Decisions
- **"Consider layering a coarser per-IP rate limit alongside the per-`principal_id` throttle."** Adopted, unmodified in spirit: web search confirmed capped exponential backoff (silent free attempts, then doubling delay, hard cap, reset on success, never a true lockout) as current best practice for exactly this kind of gate, and confirmed the "per-IP as a second, coarser layer against distributed guessing" pattern independently (e.g. Nextcloud's own per-IP exponential-backoff brute-force protection). Implemented as `chatCheckRateLimit('pin_ip:' . $ip, 20, 600)` in `chat-start.php`, reusing the exact existing `chatCheckRateLimit()` helper already used for the IP-based new-thread limit two lines above it — no new rate-limiting mechanism invented.

## Bugs Found Outside Task Scope
All out-of-scope bugs, plus every pending item, doubt and finding, are listed together just below (items 1-11), so nothing is split across two lists. Nothing here was fixed by me (Scope Lock).

### Pending items, doubts and findings (everything still open or uncertain)
1. **[IMPORTANT — doubt, Task 4 interaction] A lock can be enabled on a thread that has no PIN.** `chat-lock.php`'s `set_preference` (Task 4's file, not changed by me) never checks `chat_requires_pin()`. If a customer turns Lock Chat on without a PIN, the widget locks and invalidates the session, and `unlock` then always answers `no_pin_configured` (my safe default: refuse rather than unlock against nothing). The customer cannot unlock from the overlay. Recovery exists by going back to the pre-chat form and resuming with their email (a no-PIN thread issues a token directly), but the overlay itself is a dead end. Suggested fix, for the owner of Task 4's file: either hide/disable the Lock row until a PIN is set, or make `set_preference` refuse `enabled=1` when `chat_requires_pin()` is false. I did not change it (Scope Lock).
2. **Orphaned `chat_auth` rows.** If any admin delete/purge path removes `chat_threads` rows, their `chat_auth` (and `chat_lock_prefs`) rows remain. No such file was supplied, so unconfirmed (likely `chat-manage.php`, bulk permanent-delete). Whoever owns thread deletion should also delete by `principal_id`.
3. **Widget numerals use Sora, not Bitter** (design-system mandatory rule, Section 1). Pre-dates Task 3: the existing unread badge `.pcw-badge` uses Sora and the widget has no Bitter import. I matched the in-widget convention.
4. **`remove_pin()` audit vocabulary** (`pin_removed`) is not in the Sync Notice's proposed table. Task 7's badge logic should treat it as a distinct state.
5. **No runtime test was possible.** I verified with `php -l` (all PHP) and `node --check` (widget) plus line-by-line review, but I had no database or browser session, so the PIN flow (set, resume with PIN, throttle growth, unlock, options panel) has NOT been exercised end to end. The integrator or CEO should run it once after deploy. The Playwright/visual check was not done either.
6. **Deploy order matters.** Run `admin/chat-pin-setup.php` once in the browser BEFORE using any PIN feature (it creates `chat_auth`), then delete it from the server. Task 1's `chat-session-token-setup.php` must also have been run.
7. **`chat-widget.js` is touched by three tasks and possibly other trackers.** My version is a three-way merge of Task 1's real file, Task 4's real file and mine. The language-preference task from the other tracker (named in the Shared Contracts) also appends to the options panel; I never received its code. If it has been merged since, the integrator must reconcile — the sections are separate anchored blocks, so it should be an append, not a rewrite.
8. **Task 6 is last owner of `chat-auth.php`.** My block (`// TASK-3 START` … `// TASK-3 END`) is left anchored for them; I did not run a whole-file final pass on it, as required.
9. **No "forgot PIN" recovery.** A customer who forgets their PIN can only wait out the throttle or ask support; recovery belongs to Task 5a/Task 6's account flows and was out of my scope.
10. **PIN length (6 digits) was my choice** — the brief only says "fixed length". Changing it means three places: client `maxlength`/regex in `chat-widget.js`, and the `/^\d{6}$/` regexes in `chat-start.php` and `chat-pin-manage.php`.
11. **Email-only resume is still possible for no-PIN threads** — by design (PIN is optional per the brief), not a bug, but it means threads without a PIN remain exactly as exposed as before this project.

## Self-Check Confirmation
I modified only the files listed in Files Changed above: `chat-pin-setup.php` (new, in the zip), `includes/chat-auth.php` (existing, shared), `chat-start.php` (existing, shared, last-owner pass performed), `chat-pin-manage.php` (new, in the zip), `chat-widget.js` (existing, touched beyond my original 2-shared-file scope with explicit CEO authorization, logged under Contract Deviations), and `chat-lock.php` (existing, Task 4's file, `unlock` action only, justified above). Every code block I added carries a `// TASK-3 START` / `// TASK-3 END` or `/* TASK-3 START */` / `/* TASK-3 END */` anchor comment and an explanatory comment. Every "Old code" snippet above was copied character for character from the real file I was given (Task 1's `chat-auth.php`/`chat-start.php`, Task 4's `chat-widget.js` baseline and `chat-lock.php`) and appears only once in that file. Both new files (`chat-pin-setup.php`, `chat-pin-manage.php`) are in `Admin-Project-1-Task-3.zip` at their live paths (together with this completion file) and are listed above; neither is pasted into this document as code. `php -l` is clean on all PHP files and `node --check` is clean on `chat-widget.js` (see SECOND VERIFICATION ROUND, item D).

## SECOND VERIFICATION ROUND

Added per Directive 8c to this existing file. It covers every change made after the first submission: the sync-notice audit fix, the three-way `chat-widget.js` merge (Task 1 real + Task 4 real + Task 3), the `chat-lock.php` unlock integration, and the bugs found in the full re-verification pass. Scope Lock applied throughout. (This section also records the hand-in-format conversion done per `WORKER-DELIVERY-GUIDE.md` — no code changed as part of that conversion, only how this document and the new files are packaged; see item H below.)

### A. Sources re-read in this round
- `Admin-Project-1-Worker-Task-Assignments.md`: Warning & Instructions (rules 1-7, Directives 8a-15), Shared Contracts, Task 3 in full, Tasks 1/2/4 for sync.
- `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`: it has no PIN/`chat_auth`/`AuthGate` content, so there is nothing in it to conflict with; it was used for the options-panel structure only. It also has no `includes/` folder entry — resolved from the code, see Files Changed and New Folders.
- `PETCENTER_DESIGN_SYSTEM_v1.0.md` (Sections 1, 2, 3, 6) — rule 9 and my task's own Read First line require this before UI decisions. The first submission did not document that it had been consulted; this round does. Result: no changes were needed. My PIN inputs use the widget's own established dark-surface focus treatment (`border-color:#ED7624`, identical to the existing `.pcw-field`, `.pcw-composer-input` and `.pcw-rating-textarea`), and rule 7 requires matching existing in-component patterns. Orange is present, there are no emojis (inline `fas` icons match the existing options rows), and Sora is used for text.
- `Task-1-Completion.md`, `Task-4-Completion.md`, `Worker-Sync-Notice-Post-Task1-Task7.md`, and Task 4's real `chat-lock.php`.

### B. Changes since first submission (all anchored `TASK-3 START/END`)

**B1. Audit-log vocabulary (`includes/chat-auth.php`) — per Sync Notice.** `result` strings now distinguish states instead of a generic `'success'`:
Old: `log_audit($principal_id, 'pin_verify', 'success');` / `log_audit($principal_id, 'pin_set', 'success');`
New (final code, already folded into the Code Changes section above):
```php
log_audit($principal_id, 'pin_verify', 'tier_verified');   // reset_pin_failures()
log_audit($principal_id, 'pin_set', 'pin_set');            // set_pin()
log_audit($principal_id, 'pin_remove', 'pin_removed');     // remove_pin() — own vocabulary choice, flagged for Task 7/6
log_audit($principal_id, 'pin_verify', 'failed');          // record_pin_failure()
```

**B2. `chat-widget.js` three-way merge.** Base = Task 1's real file (per `Task-1-Completion.md`, all 12 Task 1 changes applied verbatim) + Task 4's real additions (per `Task-4-Completion.md`) + Task 3's additions. The CEO confirmed Task 4's submission as the baseline for this project. Final state:
- Stopgap `CONFIG.storageKeySessionToken` removed. Task 1's `CONFIG.storageKeyToken: 'pc_chat_session_token'`, `state.sessionToken`, auto-attach in `apiGet`/`apiPost`, and 7-arg `openLiveThread` are used as-is.
- Options panel order: Accessibility, PIN Protection (Task 3), Lock Toggle (Task 4), End session. The sections are separate and anchored, per the options-panel section contract.
- One shared AuthGate, used by BOTH call sites (prechat resume gate AND lock-toggle unlock overlay), replacing Task 4's `window.prompt()` placeholder. Final code:
```javascript
// TASK-3 START — shared helper (top-level scope; sibling functions both need it)
function pcwUpdateSessionToken(token) {
  if (!token) return;
  state.sessionToken = token;
  localStorage.setItem(CONFIG.storageKeyToken, token);
}
// TASK-3 END

function pcwRenderAuthGate(container, { tier, principalId, verify, onVerifySuccess, onVerifyFail }) {
  container.innerHTML = '';
  const pinInput = el('input', { type: 'password', inputmode: 'numeric', maxlength: '6', placeholder: tier === 'password' ? 'Password' : '6-digit PIN', autofocus: true });
  const msg = el('div', { class: 'pcw-authgate-msg' });
  const submitBtn = el('button', { type: 'button', class: 'pcw-primary-btn' }, tier === 'password' ? 'Unlock' : 'Unlock Chat');

  async function attempt() {
    msg.textContent = '';
    msg.className = 'pcw-authgate-msg';
    const val = pinInput.value.trim();
    if (tier === 'pin' && !/^\d{6}$/.test(val)) {
      msg.textContent = 'PIN must be exactly 6 digits.';
      msg.className = 'pcw-authgate-msg error';
      return;
    }
    submitBtn.disabled = true;
    submitBtn.textContent = 'Checking...';
    try {
      const json = await verify(val);
      if (json && json.success && !json.authRequired) {
        onVerifySuccess(json);
      } else {
        msg.textContent = (json && json.throttled)
          ? ('Too many attempts. Please wait ' + json.delaySeconds + 's before trying again.')
          : ((json && json.error) || 'Incorrect ' + (tier === 'password' ? 'password' : 'PIN') + '.');
        msg.className = 'pcw-authgate-msg error';
        submitBtn.disabled = false;
        submitBtn.textContent = tier === 'password' ? 'Unlock' : 'Unlock Chat';
        onVerifyFail(json);
      }
    } catch (err) {
      msg.textContent = 'Network error. Please try again.';
      msg.className = 'pcw-authgate-msg error';
      submitBtn.disabled = false;
      submitBtn.textContent = tier === 'password' ? 'Unlock' : 'Unlock Chat';
      onVerifyFail({ error: 'network_error' });
    }
  }

  submitBtn.addEventListener('click', attempt);
  pinInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') attempt(); });

  container.appendChild(el('div', { class: 'pcw-field' }, [el('label', {}, tier === 'password' ? 'Password' : 'PIN'), pinInput]));
  container.appendChild(msg);
  container.appendChild(submitBtn);
}
```
`renderPreChatAuthGate(payload, gateJson)` wraps it for the resume case (re-POSTs `chat-start.php` with the PIN; "Not you? Start over" returns to the prechat form). `pcwShowLockedOverlay()` (Task 4's function) wraps it for the unlock case, calling `chat-lock.php`'s `unlock` action as its `verify`.

**B3. `chat-lock.php` (Task 4's file) — `unlock` action only.** Already shown in full in the Code Changes section above.

### C. Bugs found and fixed in this verification pass

| # | File | Bug | Fix |
|---|---|---|---|
| 1 | `chat-pin-setup.php` | `principal_id VARCHAR(64)` was my unverified guess. The real convention (Task 1's `chat-session-token-setup.php`) is `VARCHAR(20)`, matching `chat_threads.chat_id`. I also used `DATETIME` where the project's auth tables use `TIMESTAMP`. | `principal_id VARCHAR(20)`; `locked_until TIMESTAMP NULL DEFAULT NULL`; `created_at`/`updated_at TIMESTAMP`. Already folded into the Code Changes section above. |
| 2 | `includes/chat-auth.php` `record_pin_failure()` | `1 << ($failures - CHAT_PIN_FREE_ATTEMPTS)` is undefined/wrapping past shift 62 on 64-bit ints, so an extreme failure count could wrap the delay toward 0 and defeat the throttle. | `1 << min($failures - CHAT_PIN_FREE_ATTEMPTS, 30)` with explanatory comment. Behavior is identical below the cap. Already folded into the Code Changes section above. |
| 3 | `chat-widget.js` prechat warning | Text said "Without a PIN, anyone ... can reopen this conversation", which is wrong when the submit resumes a thread that already has a PIN (the client cannot know in advance). | Now reads: "If this chat doesn't already have a PIN, anyone who has your email or chat ID could reopen it." Already folded into the Code Changes section above. |
| 4 | Process | A sandbox reset reverted my working copies to the pre-fix state mid-pass, more than once. Re-applied all fixes and re-synced each time. | n/a |

### D. Verified correct (re-checked line by line, no change needed)
- Throttle never blocks lookup: in `chat-start.php` the `SELECT ... FROM chat_threads` runs before `chat_gate_and_issue_token()` in Paths 1 and 2, so the throttle only gates token release after lookup.
- No hard lockout: `locked_until` is a capped (900s) exponential delay, first 3 attempts free, reset on success. `check_pin_throttle()` is read-only.
- Per-IP layer: `chatCheckRateLimit('pin_ip:'.$ip, 20, 600)` in `chat-start.php`; `pin_manage_ip` in `chat-pin-manage.php` (applied to mutations only, since `status` involves no PIN guess).
- Path 3: `set_pin()` runs before `issue_token()`, so the new thread's token is not revoked by `set_pin()`'s `invalidate_all_tokens()`.
- PIN length is 6 digits everywhere (client `maxlength`/regex/placeholders; server `/^\d{6}$/` in `chat-start.php` and `chat-pin-manage.php`).
- `chatRequireSessionToken($chatId, $token)` call in `chat-pin-manage.php` matches Task 1's real signature `($principal_id, $providedToken)`.
- Anchor syntax follows the real per-file precedent (`//` in `chat-auth.php`, `/* */` in `chat-start.php`/JS), as Task 1 documented. The literal `<!-- -->` form is a parse error in PHP/JS.
- Lint: `php -l` clean on `chat-pin-setup.php`, `includes/chat-auth.php`, `chat-start.php`, `chat-pin-manage.php`, `chat-lock.php`; `node --check` clean on `chat-widget.js`.

### E. "Files to request" completeness
The brief said none were needed to start and named only `chat-start.php` and `includes/chat-auth.php` for integration. That list was incomplete. Making Task 3 actually work required: `chat-widget.js` (prechat form, options panel, AuthGate call sites), `chat-lock.php` (Task 4's unlock stub depends on my functions), and `Task-1-Completion.md`/`Task-4-Completion.md` (real token plumbing and Task 4's placeholder shape). All were obtained and wired. I found no other file in the supplied set that references `chat_auth`.

### F. Bugs Found Outside Task Scope (logged, NOT fixed)
See the Bugs Found Outside Task Scope section (items 2, 3 and 4 there); kept once, not repeated.

### G. Self-Check Confirmation (second round, code)
Files touched in this round: `chat-pin-setup.php`, `includes/chat-auth.php`, `chat-start.php` (last-owner pass; no new edits needed this round), `chat-pin-manage.php`, `chat-widget.js`, and `chat-lock.php` (`unlock` action only, justified above). Every added block carries a `TASK-3 START/END` anchor with an explanatory comment. Nothing else was edited, and no new files other than this completion doc and the zip were created.

### H. Hand-in format conversion (per `WORKER-DELIVERY-GUIDE.md`)
No code was redone or changed for this conversion — only this document's structure and file packaging changed, per the explicit instruction to fix the hand-in only:
- Rebuilt the Files Changed table to the required four columns (source name, live path, changed-or-new, shared-with-another-task), using the guide's source-to-live-path mapping table for `chat-start.php`/`chat-widget.js`/`chat-lock.php` (all `admin/` + same file name) and `chat-pin-setup.php`/`chat-pin-manage.php` (also `admin/`, since they are admin-side scripts, not public pages).
- Flagged one real mapping gap: `chat-auth.php`'s folder isn't in either project-summary document. Resolved in the third conversion pass below from the code's own `require_once` lines: `admin/includes/chat-auth.php`, new folder `admin/includes/`.
- Moved the two brand-new files (`chat-pin-setup.php`, `chat-pin-manage.php`) out of inline code blocks and into the zip, listed under "New files" per the guide's rule that a changed file never goes in the zip and a new file is never pasted as code.
- Confirmed every remaining "Old code"/"New code" pair in Code Changes is copied character-for-character from the real file I was given and appears exactly once in it, and added an explicit old/new pair for insertion points that previously had only prose description (the options-panel `body` array, the `includes/chat-auth.php` EOF anchor, the per-IP rate-limit anchor, the Path-3 `$threadId` anchor, and `chat-lock.php`'s stub).
- Checked the zip contents against this table and against the guide's "never include" list — confirmed clean.

**H2. Third conversion pass (CEO clarification of the delivery guide).** Still no code changed. (1) The zip now holds the new files AND this completion file together, named `Admin-Project-1-Task-3.zip` (project name taken from `Admin-Project-1-Worker-Task-Assignments.md`, plus task number) — the earlier separate `Task-3-NewFiles.zip` and loose completion file are superseded. (2) New folders are called out explicitly in the New Folders section. (3) Pending items, doubts and findings are collected under Bugs Found Outside Task Scope. (4) No `.html` page was created by this task, so the `index.html`-per-folder rule did not come into play.
