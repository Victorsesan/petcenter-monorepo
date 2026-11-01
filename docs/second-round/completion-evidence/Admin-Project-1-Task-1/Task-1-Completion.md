# Task 1 Completion

## Task Number

Task 1 — Session-Token Base Layer + Endpoint Hardening

## What Was Required

Reopening a chat worked on email + chat ID alone, with neither treated as
a secret — both are guessable/enumerable, so either one leaking exposed a
full conversation. The job was to make email + chat ID a *lookup*
mechanism only, never an *authorization* mechanism: build the real
`chat_session_token` table and the real `includes/chat-auth.php` matching
the Shared Contract (`issue_token`, `verify_token`, `invalidate_all_tokens`,
`log_audit`), issue a token in `chat-start.php` once the existing lookup
succeeds, and enforce that token on every one of the 11 named endpoint
files. `invalidate_all_tokens()` and a per-verification audit log were
required as structural, non-optional parts of the task, not enhancements.

**Note on `chat-widget.js` being in this doc at all:** it was not one of
this task's 12 files, so it was not requested up front and was not touched
in the first version of this work — that gap was raised explicitly as a
blocker (Scope Lock, rule 8b) rather than worked around. The CEO then
supplied the file directly and asked for it to be wired in, which brought
it into this task's actual scope after the fact. It's documented below
exactly like every other file for that reason.

**Note on anchor-comment syntax:** rule 8a's format uses `<!-- TASK-1
START -->` / `<!-- TASK-1 END -->` as its example. That literal syntax is
HTML-only — inside a `<?php ?>` block it's a fatal parse error (`<!--` is
not valid PHP), and inside a `<script>` block it's not valid JavaScript
either. The existing codebase's own established convention (visible in the
untouched parts of `chat-setup.php`'s `BUGFIX-1` blocks and
`chat-poll.php`'s `TASK-8` blocks, both supplied files) already solves this
by using `/* TASK-X START */ ... /* TASK-X END */` in PHP and JS files —
same anchor text, language-native comment syntax. Per rule 7 (never
reinvent an existing pattern), every anchor below follows that same
established convention rather than the literal HTML-comment example.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `includes/chat-auth.php` | `admin/includes/chat-auth.php` | New | Yes — Task 3 (adds `check_pin_throttle()`), Task 6 (adds a lockout exemption), both after this task |
| `chat-session-token-setup.php` | `admin/chat-session-token-setup.php` | New (added beyond the 12; see note below) | No |
| `chat-start.php` | `admin/chat-start.php` | Changed | Yes — Task 3 |
| `chat-poll.php` | `admin/chat-poll.php` | Changed | No |
| `chat-typing.php` | `admin/chat-typing.php` | Changed | No |
| `chat-draft.php` | `admin/chat-draft.php` | Changed | No |
| `chat-history.php` | `admin/chat-history.php` | Changed | No |
| `chat-message-edit.php` | `admin/chat-message-edit.php` | Changed | No |
| `chat-message-delete.php` | `admin/chat-message-delete.php` | Changed | No |
| `chat-send.php` | `admin/chat-send.php` | Changed | No |
| `chat-rate.php` | `admin/chat-rate.php` | Changed | No |
| `chat-upload-temp.php` | `admin/chat-upload-temp.php` | Changed | No |
| `chat-upload-video-chunk.php` | `admin/chat-upload-video-chunk.php` | Changed | No |
| `chat-widget.js` | `admin/chat-widget.js` | Changed (added to scope after being flagged as a blocker; see note above) | Not stated as shared in the task doc |

**Note on `chat-session-token-setup.php`:** this is a 13th file, beyond the
task's stated 12. It's a new, standalone one-time DB setup script creating
`chat_session_token` (the Shared Contract's exact shape) and
`chat_auth_audit_log` (a new table backing `log_audit()` — no shape for it
was published in the Shared Contract, so its shape is this task's own
design, documented in the Code Changes section below). It is NOT an edit
to the existing `chat-setup.php`, since that file was never one of the 12
in scope (Scope Lock, rule 8b) — rather than silently touching it, the new
tables got their own script following its exact existing pattern. Flagging
this explicitly here rather than presenting it as if it were part of the
original 12.

**Live path basis:** every `chat-*.php` file and `chat-widget.js` maps to `admin/` + the same file name (mapping table, delivery guide section 3, and the folder-structure section of `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`, which lists both under `admin/`). The two new files are not in the mapping table. `chat-session-token-setup.php` sits beside `chat-setup.php` in `admin/`. `includes/chat-auth.php` is required by every endpoint as `__DIR__ . '/includes/chat-auth.php'`, so it resolves to `admin/includes/chat-auth.php`; `admin/includes/` is a new folder. The summary and the assignment do not disagree on any path.

## Code Changes

Every block below is a change to an existing file. The old snippet is copied character for character from the original file supplied for this task and appears exactly once in it. The new code is the exact final text of the delivered file. I verified this mechanically by replaying every old/new pair on the original and comparing the result to the delivered file. The two brand-new files are not pasted here; they are listed under "New files" and are in the zip.

### `chat-start.php`  →  live path `admin/chat-start.php` (shared with Task 3)

**Change 1** (original line ~31) — session-token base layer

<!-- TASK-1 START -->
**Old code:**
```php
// Path 3, the only place a genuinely new thread is created).
require_once __DIR__ . '/chat-mail.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
```
**New code:**
```php
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
```
<!-- TASK-1 END -->

**Change 2** (original line ~94) — issue a fresh session token on every successful

<!-- TASK-1 START -->
**Old code:**
```php
        }

        chatRespond([
            'success'  => true,
            'resumed'  => true,
            'chatId'   => $thread['chat_id'],
            'status'   => $thread['status'],
            'fullName' => $thread['full_name'],
            'email'    => $thread['email'],
        ]);
    }
```
**New code:**
```php
        }

        /* TASK-1 START — issue a fresh session token on every successful
           resume (Path 1: explicit chat ID). A fresh token is issued on
           each resume rather than trying to reuse/return a prior one —
           simpler, and it means a customer resuming on a new device/browser
           naturally gets their own valid token without needing to reason
           about whether an old one is still around. */
        $sessionToken = issue_token($thread['chat_id']);
        /* TASK-1 END */

        chatRespond([
            'success'      => true,
            'resumed'      => true,
            'chatId'       => $thread['chat_id'],
            'status'       => $thread['status'],
            'fullName'     => $thread['full_name'],
            'email'        => $thread['email'],
            /* TASK-1 START — sessionToken in the response payload */
            'sessionToken' => $sessionToken,
            /* TASK-1 END */
        ]);
    }
```
<!-- TASK-1 END -->

**Change 3** (original line ~136) — issue a fresh session token on every successful

<!-- TASK-1 START -->
**Old code:**
```php
    }

    chatRespond([
        'success'  => true,
        'resumed'  => true,
        'chatId'   => $existing['chat_id'],
        'status'   => $existing['status'],
        'fullName' => $existing['full_name'],
        'email'    => $existing['email'],
    ]);
}
```
**New code:**
```php
    }

    /* TASK-1 START — issue a fresh session token on every successful
       resume (Path 2: email match). Same reasoning as Path 1 above. */
    $sessionToken = issue_token($existing['chat_id']);
    /* TASK-1 END */

    chatRespond([
        'success'      => true,
        'resumed'      => true,
        'chatId'       => $existing['chat_id'],
        'status'       => $existing['status'],
        'fullName'     => $existing['full_name'],
        'email'        => $existing['email'],
        /* TASK-1 START — sessionToken in the response payload */
        'sessionToken' => $sessionToken,
        /* TASK-1 END */
    ]);
}
```
<!-- TASK-1 END -->

**Change 4** (original line ~204) — issue the very first session token for a brand-new thread.

<!-- TASK-1 START -->
**Old code:**
```php
]);

chatRespond([
    'success'  => true,
    'resumed'  => false,
    'chatId'   => $newChatId,
    'status'   => 'waiting',
    'fullName' => $fullName,
    'email'    => $email,
]);
```
**New code:**
```php
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
    /* TASK-1 START — sessionToken in the response payload */
    'sessionToken' => $sessionToken,
    /* TASK-1 END */
]);
```
<!-- TASK-1 END -->

### `chat-poll.php`  →  live path `admin/chat-poll.php`

**Change 1** (original line ~27) — session-token base layer

<!-- TASK-1 START -->
**Old code:**
```php
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';

$chatId        = trim($_GET['chatId'] ?? '');
$lastMessageId = (int)($_GET['lastMessageId'] ?? 0);
$lastEventId   = (int)($_GET['lastEventId'] ?? 0);

if ($chatId === '') {
    chatRespond(['success' => false, 'error' => 'chatId is required'], 400);
```
**New code:**
```php
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';
/* TASK-1 START — session-token base layer */
require_once __DIR__ . '/includes/chat-auth.php';
/* TASK-1 END */

$chatId        = trim($_GET['chatId'] ?? '');
$lastMessageId = (int)($_GET['lastMessageId'] ?? 0);
$lastEventId   = (int)($_GET['lastEventId'] ?? 0);
/* TASK-1 START — read the session token the same way every other GET param
   here is read (querystring), since this is a GET endpoint */
$sessionToken  = trim($_GET['sessionToken'] ?? '');
/* TASK-1 END */

if ($chatId === '') {
    chatRespond(['success' => false, 'error' => 'chatId is required'], 400);
```
<!-- TASK-1 END -->

**Change 2** (original line ~46) — enforce the session token AFTER confirming the thread

<!-- TASK-1 START -->
**Old code:**
```php
    chatRespond(['success' => false, 'error' => 'Chat not found'], 404);
}

$threadId = $thread['id'];
```
**New code:**
```php
    chatRespond(['success' => false, 'error' => 'Chat not found'], 404);
}

/* TASK-1 START — enforce the session token AFTER confirming the thread
   exists (so a bad chatId still gets the existing, more specific 404
   rather than a generic 401), but BEFORE anything below reads or mutates
   any message/event/typing state for this thread. This is the actual fix:
   chatId alone no longer reaches any real data past this point. */
chatRequireSessionToken($chatId, $sessionToken);
/* TASK-1 END */

$threadId = $thread['id'];
```
<!-- TASK-1 END -->

### `chat-typing.php`  →  live path `admin/chat-typing.php`

**Change 1** (original line ~18) — session-token base layer

<!-- TASK-1 START -->
**Old code:**
```php
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    chatRespond(['success' => false, 'error' => 'POST required'], 405);
```
**New code:**
```php
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';
/* TASK-1 START — session-token base layer */
require_once __DIR__ . '/includes/chat-auth.php';
/* TASK-1 END */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    chatRespond(['success' => false, 'error' => 'POST required'], 405);
```
<!-- TASK-1 END -->

**Change 2** (original line ~57) — customer-side pings (who=user/user_recording) now require

<!-- TASK-1 START -->
**Old code:**
```php
}
// TASK-8 END (8b — accept voice-recording-in-progress ping values)

$db = chatDb();

$stmt = $db->prepare("SELECT id FROM chat_threads WHERE chat_id = ? LIMIT 1");
```
**New code:**
```php
}
// TASK-8 END (8b — accept voice-recording-in-progress ping values)

/* TASK-1 START — customer-side pings (who=user/user_recording) now require
   the same session token every other customer-facing endpoint requires.
   Previously these were the one class of write against a real, existing
   thread that needed no proof of anything beyond a guessed chatId — a
   typing/recording ping doesn't leak conversation content by itself, but
   letting an unauthenticated caller write into chat_typing for any known
   chatId was still a real gap in the "chatId is a lookup, not an
   authorization" fix this task exists to close. */
if ($who === 'user' || $who === 'user_recording') {
    $sessionToken = trim($input['sessionToken'] ?? '');
    chatRequireSessionToken($chatId, $sessionToken);
}
/* TASK-1 END */

$db = chatDb();

$stmt = $db->prepare("SELECT id FROM chat_threads WHERE chat_id = ? LIMIT 1");
```
<!-- TASK-1 END -->

### `chat-draft.php`  →  live path `admin/chat-draft.php`

**Change 1** (original line ~21) — session-token base layer

<!-- TASK-1 START -->
**Old code:**
```php
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';

$action = $_REQUEST['action'] ?? '';
$draftKey = trim($_REQUEST['draftKey'] ?? '');
```
**New code:**
```php
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';
/* TASK-1 START — session-token base layer */
require_once __DIR__ . '/includes/chat-auth.php';
/* TASK-1 END */

$action = $_REQUEST['action'] ?? '';
$draftKey = trim($_REQUEST['draftKey'] ?? '');
```
<!-- TASK-1 END -->

**Change 2** (original line ~30) — conditional session-token enforcement

<!-- TASK-1 START -->
**Old code:**
```php
}

$db = chatDb();

if ($action === 'save') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
```
**New code:**
```php
}

$db = chatDb();

/* TASK-1 START — conditional session-token enforcement
   draftKey takes three real shapes in this system, and each needs
   different treatment:
     1. A real chat_id (once a thread exists) -> this draft belongs to an
        existing, real conversation, so it gets the same protection every
        other piece of that conversation's data now gets: a valid session
        token for that exact chat_id, or the request is rejected. Without
        this, chat-draft.php would have been the one file where a bare
        guessed chatId still reached real data (an in-progress unsent
        message) after every other endpoint was hardened.
     2. 'unread:{chatId}' (the persistent unread-badge count, piggybacking
        on this same save/get mechanism per this file's own header note)
        -> also tied to a real chat_id, embedded after the prefix, so it
        gets the same enforcement, scoped to that embedded chat_id rather
        than the literal draftKey string.
     3. A pre-thread, client-generated UUID (used before the first message
        has created a thread) -> no thread, no principal_id, and therefore
        nothing for a session token to even be issued against yet. Not a
        meaningful exposure the way a real conversation is, either: the
        UUID is unguessable and tied to nothing else an attacker could
        look up — matching how chat-start.php itself never requires a
        token to CREATE a thread in the first place. Left unauthenticated,
        exactly as before. */
// substr() check, not str_starts_with() — this project's stated server
// requirement is PHP 7.4+ (str_starts_with is PHP 8.0+)
$draftKeyPrincipal = (substr($draftKey, 0, 7) === 'unread:') ? substr($draftKey, 7) : $draftKey;

$draftKeyThreadCheck = $db->prepare("SELECT id FROM chat_threads WHERE chat_id = ? LIMIT 1");
$draftKeyThreadCheck->execute([$draftKeyPrincipal]);
$draftKeyIsRealChatId = (bool)$draftKeyThreadCheck->fetchColumn();

if ($draftKeyIsRealChatId) {
    $draftSessionToken = trim($_REQUEST['sessionToken'] ?? '');
    chatRequireSessionToken($draftKeyPrincipal, $draftSessionToken);
}
/* TASK-1 END */

if ($action === 'save') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
```
<!-- TASK-1 END -->

### `chat-history.php`  →  live path `admin/chat-history.php`

**Change 1** (original line ~3)

<!-- TASK-1 START -->
**Old code:**
```php
 * chat-history.php
 *
 * Powers the "See previous messages" inline reveal on a Done thread
 * (Section 7b). Two modes:
 *
 *  - mode=full   -> returns every prior message, but image/audio/video
 *                   messages come back as lightweight placeholder metadata
 *                   only (type, original filename if known, size) — the
 *                   actual media_path is deliberately withheld here so the
 *                   initial reveal stays fast even with a long history full
 *                   of attachments. This is the lazy-loading principle
 *                   already used for fast video uploads, applied to history
 *                   loading instead.
 *  - mode=media  -> given one specific messageId, returns that single
 *                   message's actual media path, resolved only at the
 *                   moment the customer taps that specific item.
 *
 * Works for both the customer (by chatId) and an admin reviewing a Done
 * thread (by chatId + admin auth) — same data, same lazy-loading shape.
 */

header('Content-Type: application/json');
```
**New code:**
```php
 * chat-history.php
 *
 * TASK-1 (second verification round) — corrected header: the two
 * paragraphs below used to claim this powers a "See previous messages"
 * inline reveal with dual customer/admin usage. That's stale —
 * ADMIN_DASHBOARD_PROJECT_SUMMARY.md's current, authoritative section for
 * this file confirms it actually powers "Download transcript" only,
 * customer-side exclusively (downloadTranscript() in chat-widget.js), with
 * the same no-admin-usage, chatId-is-the-credential trust model as
 * chat-poll.php. Corrected here rather than left standing, since it
 * directly contradicts this file's own TASK-1 enforcement block below.
 *
 * Powers the widget's "Download transcript" feature. Two modes:
 *
 *  - mode=full   -> returns every prior message, but image/audio/video
 *                   messages come back as lightweight placeholder metadata
 *                   only (type, original filename if known, size) — the
 *                   actual media_path is deliberately withheld here so the
 *                   response stays fast even with a long history full
 *                   of attachments. This is the lazy-loading principle
 *                   already used for fast video uploads, applied to history
 *                   loading instead. This is the only mode actually used
 *                   end-to-end.
 *  - mode=media  -> given one specific messageId, returns that single
 *                   message's actual media path. Not currently wired to
 *                   any UI control in either the widget or the dashboard.
 *
 * Customer-side only, no admin usage — chatId is the credential, same
 * trust model as chat-poll.php.
 */

header('Content-Type: application/json');
```
<!-- TASK-1 END -->

**Change 2** (original line ~26) — session-token base layer

<!-- TASK-1 START -->
**Old code:**
```php
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';

$chatId = trim($_GET['chatId'] ?? '');
$mode   = $_GET['mode'] ?? 'full';

if ($chatId === '') {
    chatRespond(['success' => false, 'error' => 'chatId is required'], 400);
```
**New code:**
```php
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';
/* TASK-1 START — session-token base layer */
require_once __DIR__ . '/includes/chat-auth.php';
/* TASK-1 END */

$chatId = trim($_GET['chatId'] ?? '');
$mode   = $_GET['mode'] ?? 'full';
/* TASK-1 START */
$sessionToken = trim($_GET['sessionToken'] ?? '');
/* TASK-1 END */

if ($chatId === '') {
    chatRespond(['success' => false, 'error' => 'chatId is required'], 400);
```
<!-- TASK-1 END -->

**Change 3** (original line ~44) — (revised — second verification round)

<!-- TASK-1 START -->
**Old code:**
```php
    chatRespond(['success' => false, 'error' => 'Chat not found'], 404);
}

if ($mode === 'full') {
```
**New code:**
```php
    chatRespond(['success' => false, 'error' => 'Chat not found'], 404);
}

/* TASK-1 START (revised — second verification round)
   This file's own header comment above (lines 5-21) claims dual
   customer/admin usage and describes a "See previous messages" inline
   reveal. Both claims are stale: ADMIN_DASHBOARD_PROJECT_SUMMARY.md's
   current, authoritative chat-history.php section explicitly supersedes
   that description — this endpoint actually powers "Download transcript"
   only, is driven exclusively by downloadTranscript() in chat-widget.js
   (the customer widget), and is documented as "no auth (chatId is the
   credential), same trust model as chat-poll.php" with no admin usage
   anywhere in the shipped code. The first version of this fix trusted the
   file's own (stale) header comment and added a chatIsAdminAuthenticated()
   bypass here; that was wrong and has been removed. Session-token
   enforcement is now unconditional, matching chat-poll.php's own
   TASK-1 block exactly — this file has the same trust model, so it gets
   the same fix, nothing more. */
chatRequireSessionToken($chatId, $sessionToken);
/* TASK-1 END */

if ($mode === 'full') {
```
<!-- TASK-1 END -->

### `chat-message-edit.php`  →  live path `admin/chat-message-edit.php`

**Change 1** (original line ~22) — session-token base layer

<!-- TASK-1 START -->
**Old code:**
```php
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    chatRespond(['success' => false, 'error' => 'POST required'], 405);
```
**New code:**
```php
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';
/* TASK-1 START — session-token base layer */
require_once __DIR__ . '/includes/chat-auth.php';
/* TASK-1 END */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    chatRespond(['success' => false, 'error' => 'POST required'], 405);
```
<!-- TASK-1 END -->

**Change 2** (original line ~37)

<!-- TASK-1 START -->
**Old code:**
```php
$newBody   = trim($input['body'] ?? '');
$adminId   = (int)($input['adminId'] ?? 0);

if ($chatId === '' || !$messageId) {
```
**New code:**
```php
$newBody   = trim($input['body'] ?? '');
$adminId   = (int)($input['adminId'] ?? 0);
/* TASK-1 START */
$sessionToken = trim($input['sessionToken'] ?? '');
/* TASK-1 END */

if ($chatId === '' || !$messageId) {
```
<!-- TASK-1 END -->

**Change 3** (original line ~83) — the customer branch previously had NO auth check at

<!-- TASK-1 START -->
**Old code:**
```php
    if ($message['from_type'] !== 'user') {
        chatRespond(['success' => false, 'error' => 'You can only edit your own messages'], 403);
    }
}

// ── Time window check ────────────────────────────────────────────────────
```
**New code:**
```php
    if ($message['from_type'] !== 'user') {
        chatRespond(['success' => false, 'error' => 'You can only edit your own messages'], 403);
    }
    /* TASK-1 START — the customer branch previously had NO auth check at
       all beyond "the message happens to be from_type='user'" — anyone
       who knew a chatId and a messageId could edit that customer's own
       message on their behalf, purely by guessing/enumerating IDs. A
       valid session token for this exact chatId is now required on this
       branch, same as every other customer-facing write in this task. */
    chatRequireSessionToken($chatId, $sessionToken);
    /* TASK-1 END */
}

// ── Time window check ────────────────────────────────────────────────────
```
<!-- TASK-1 END -->

### `chat-message-delete.php`  →  live path `admin/chat-message-delete.php`

**Change 1** (original line ~24) — session-token base layer

<!-- TASK-1 START -->
**Old code:**
```php
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    chatRespond(['success' => false, 'error' => 'POST required'], 405);
```
**New code:**
```php
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';
/* TASK-1 START — session-token base layer */
require_once __DIR__ . '/includes/chat-auth.php';
/* TASK-1 END */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    chatRespond(['success' => false, 'error' => 'POST required'], 405);
```
<!-- TASK-1 END -->

**Change 2** (original line ~38)

<!-- TASK-1 START -->
**Old code:**
```php
$messageId = (int)($input['messageId'] ?? 0);
$adminId   = (int)($input['adminId'] ?? 0); // only present for admin-side deletes

if ($chatId === '' || !$messageId) {
```
**New code:**
```php
$messageId = (int)($input['messageId'] ?? 0);
$adminId   = (int)($input['adminId'] ?? 0); // only present for admin-side deletes
/* TASK-1 START */
$sessionToken = trim($input['sessionToken'] ?? '');
/* TASK-1 END */

if ($chatId === '' || !$messageId) {
```
<!-- TASK-1 END -->

**Change 3** (original line ~81) — same gap as chat-message-edit.php's customer branch:

<!-- TASK-1 START -->
**Old code:**
```php
    if ($message['from_type'] !== 'user') {
        chatRespond(['success' => false, 'error' => 'You can only delete your own messages'], 403);
    }
}

// ── Time window check ────────────────────────────────────────────────────
```
**New code:**
```php
    if ($message['from_type'] !== 'user') {
        chatRespond(['success' => false, 'error' => 'You can only delete your own messages'], 403);
    }
    /* TASK-1 START — same gap as chat-message-edit.php's customer branch:
       previously anyone who knew a chatId + messageId could delete that
       customer's own message with no proof they actually own the
       conversation. A valid session token for this exact chatId is now
       required. */
    chatRequireSessionToken($chatId, $sessionToken);
    /* TASK-1 END */
}

// ── Time window check ────────────────────────────────────────────────────
```
<!-- TASK-1 END -->

### `chat-send.php`  →  live path `admin/chat-send.php`

**Change 1** (original line ~27) — session-token base layer

<!-- TASK-1 START -->
**Old code:**
```php
// saving — see the inline comment at that call site for why).
require_once __DIR__ . '/chat-mail.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
```
**New code:**
```php
// saving — see the inline comment at that call site for why).
require_once __DIR__ . '/chat-mail.php';
/* TASK-1 START — session-token base layer */
require_once __DIR__ . '/includes/chat-auth.php';
/* TASK-1 END */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
```
<!-- TASK-1 END -->

**Change 2** (original line ~45)

<!-- TASK-1 START -->
**Old code:**
```php
$replyToId      = $input['replyToId'] ?? null;
$idempotencyKey = trim($input['idempotencyKey'] ?? '');

// Ensure arrays
```
**New code:**
```php
$replyToId      = $input['replyToId'] ?? null;
$idempotencyKey = trim($input['idempotencyKey'] ?? '');
/* TASK-1 START */
$sessionToken   = trim($input['sessionToken'] ?? '');
/* TASK-1 END */

// Ensure arrays
```
<!-- TASK-1 END -->

**Change 3** (original line ~83) — this was the customer's own message-send endpoint, and

<!-- TASK-1 START -->
**Old code:**
```php
    chatRespond(['success' => false, 'error' => 'Chat not found'], 404);
}

$wasReopenedFromDone = false;
```
**New code:**
```php
    chatRespond(['success' => false, 'error' => 'Chat not found'], 404);
}

/* TASK-1 START — this was the customer's own message-send endpoint, and
   previously the single biggest piece of the vulnerability this task
   exists to close: anyone who guessed/enumerated a chatId could send
   messages into that customer's conversation with no proof of ownership
   at all. Enforced here, right after the thread lookup (so an unknown
   chatId still gets the existing, more specific 404) and before anything
   below reads or mutates thread/message state. */
chatRequireSessionToken($chatId, $sessionToken);
/* TASK-1 END */

$wasReopenedFromDone = false;
```
<!-- TASK-1 END -->

### `chat-rate.php`  →  live path `admin/chat-rate.php`

**Change 1** (original line ~9) — session-token base layer

<!-- TASK-1 START -->
**Old code:**
```php
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';

$reviewsDir = __DIR__ . '/reviews/';
if (!is_dir($reviewsDir)) @mkdir($reviewsDir, 0755, true);
```
**New code:**
```php
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';
/* TASK-1 START — session-token base layer */
require_once __DIR__ . '/includes/chat-auth.php';
/* TASK-1 END */

$reviewsDir = __DIR__ . '/reviews/';
if (!is_dir($reviewsDir)) @mkdir($reviewsDir, 0755, true);
```
<!-- TASK-1 END -->

**Change 2** (original line ~34) — submitting a rating was previously completely open:

<!-- TASK-1 START -->
**Old code:**
```php
        chatRespond(['success' => false, 'error' => 'Rating must be 1–5'], 400);
    }

    $file = $reviewsDir . preg_replace('/[^A-Za-z0-9\-]/', '', $chatId) . '.json';
```
**New code:**
```php
        chatRespond(['success' => false, 'error' => 'Rating must be 1–5'], 400);
    }

    /* TASK-1 START — submitting a rating was previously completely open:
       anyone who knew a chatId (and could supply any syntactically valid
       email — the submitted email is never checked against the thread's
       actual email) could post a fake rating attributed to a real admin
       for a real conversation that wasn't theirs. A valid session token
       for this exact chatId is now required, same as every other
       customer-facing write in this task. Note: this does NOT fix the
       separate pre-existing gap that $email isn't cross-checked against
       the thread's stored email — that's unrelated to session-token
       enforcement and out of this task's scope; logged under "Bugs Found
       Outside Task Scope" in Task-1-Completion.md. */
    $sessionToken = trim($input['sessionToken'] ?? '');
    chatRequireSessionToken($chatId, $sessionToken);
    /* TASK-1 END */

    $file = $reviewsDir . preg_replace('/[^A-Za-z0-9\-]/', '', $chatId) . '.json';
```
<!-- TASK-1 END -->

### `chat-upload-temp.php`  →  live path `admin/chat-upload-temp.php`

**Change 1** (original line ~20) — session-token base layer

<!-- TASK-1 START -->
**Old code:**
```php
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    chatRespond(['success' => false, 'error' => 'POST required'], 405);
```
**New code:**
```php
@ini_set('display_errors', 0);

require_once __DIR__ . '/chat-config.php';
/* TASK-1 START — session-token base layer */
require_once __DIR__ . '/includes/chat-auth.php';
/* TASK-1 END */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    chatRespond(['success' => false, 'error' => 'POST required'], 405);
```
<!-- TASK-1 END -->

**Change 2** (original line ~36) — (revised — second verification round)

<!-- TASK-1 START -->
**Old code:**
```php
    chatRespond(['success' => false, 'error' => "kind must be 'image', 'audio', or 'video'"], 400);
}

// ── Upload error check ──────────────────────────────────────────────────
```
**New code:**
```php
    chatRespond(['success' => false, 'error' => "kind must be 'image', 'audio', or 'video'"], 400);
}

/* TASK-1 START (revised — second verification round)
   This endpoint's original contract never sent a chatId here — a staged
   upload was chat-agnostic until chat-send.php consumed its token, a
   genuine documented design choice (ADMIN_DASHBOARD_PROJECT_SUMMARY.md's
   own note that "the actual security boundary is downstream" for the
   sibling chunked-video endpoint). chat-widget.js has SINCE been updated
   (also this task, after being raised as a blocker and then supplied) to
   send chatId + sessionToken on every call here, so the first branch
   below is the live, normal customer path now, not a someday-ready one.
   Second verification round addition: this file is very likely also used
   by the admin dashboard's chat composer for image/audio attachments
   ahead of chat-admin-reply.php — chat-admin-reply.php's own
   imageTokens[]/audioToken inputs have to come staged from somewhere, and
   no separate admin-only staging endpoint is documented anywhere in the
   file structure, so this is the only candidate. Not explicitly confirmed
   the way chat-upload-video-chunk.php's admin usage is (that one names
   `uploadVideoChunked()` and its X-Admin-Token header directly) — the
   same free win already given to
   chat-upload-video-chunk.php (checking X-Admin-Token, which that
   sibling endpoint is confirmed to already receive from the admin
   dashboard) is added here too. Unlike that sibling file, nothing in the
   project documentation confirms whether the admin dashboard currently
   sends X-Admin-Token to THIS endpoint specifically — so this check is
   purely additive and safe either way: if the header is sent, admin
   uploads get verified for real and are exempted from the anonymous rate
   limit below; if it isn't (today, most likely), nothing changes and the
   existing rate-limited fallback still applies exactly as before.
     1. A valid admin token wins outright, same precedence as
        chat-upload-video-chunk.php.
     2. Otherwise, a chatId + sessionToken pair (now sent by the current
        chat-widget.js) is verified for real.
     3. Otherwise, rate-limited + audit-logged as a fallback for any other
        caller. */
if (chatIsAdminAuthenticated()) {
    log_audit('admin', 'chat-upload-temp', 'staged_' . $kind . '_admin');
} else {
    $uploadChatId = trim($_POST['chatId'] ?? '');
    $uploadSessionToken = trim($_POST['sessionToken'] ?? '');

    if ($uploadChatId !== '') {
        chatRequireSessionToken($uploadChatId, $uploadSessionToken);
        log_audit($uploadChatId, 'chat-upload-temp', 'staged_' . $kind);
    } else {
        $uploadIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (!chatCheckRateLimit('chat_upload_temp_ip:' . $uploadIp, 30, 600)) { // 30 staged uploads / 10 min / IP
            chatRespond(['success' => false, 'error' => 'Too many uploads. Please wait a few minutes and try again.'], 429);
        }
        log_audit('unknown', 'chat-upload-temp', 'staged_' . $kind . '_no_session');
    }
}
/* TASK-1 END */

// ── Upload error check ──────────────────────────────────────────────────
```
<!-- TASK-1 END -->

### `chat-upload-video-chunk.php`  →  live path `admin/chat-upload-video-chunk.php`

**Change 1** (original line ~16) — session-token base layer

<!-- TASK-1 START -->
**Old code:**
```php
header('Content-Type: application/json');

require_once __DIR__ . '/chat-config.php';

$action    = $_POST['action'] ?? 'upload';
$uploadId  = $_POST['uploadId'] ?? '';
```
**New code:**
```php
header('Content-Type: application/json');

require_once __DIR__ . '/chat-config.php';
/* TASK-1 START — session-token base layer */
require_once __DIR__ . '/includes/chat-auth.php';
/* TASK-1 END */

$action    = $_POST['action'] ?? 'upload';
$uploadId  = $_POST['uploadId'] ?? '';
```
<!-- TASK-1 END -->

**Change 2** (original line ~26) — same structural limit as chat-upload-temp.php's TASK-1

<!-- TASK-1 START -->
**Old code:**
```php
    exit;
}

$chunkDir = CHAT_UPLOAD_TEMP_DIR . 'chunks/';
```
**New code:**
```php
    exit;
}

/* TASK-1 START — same structural limit as chat-upload-temp.php's TASK-1
   block above (see that file for the full reasoning on why full
   enforcement isn't possible here without an out-of-scope chat-widget.js
   change) — with one additional, genuinely free improvement specific to
   this file: ADMIN_DASHBOARD_PROJECT_SUMMARY.md documents that the admin
   dashboard ALREADY sends an X-Admin-Token header to this endpoint today,
   even though nothing here has ever checked it. That means the admin path
   can be fully, correctly hardened right now with zero frontend changes —
   only the customer (chat widget) path needs the same soft/rate-limited
   fallback as chat-upload-temp.php, since chat-widget.js doesn't send a
   chatId here today.
   Precedence: a valid admin token wins outright (this endpoint is
   genuinely shared by both sides, unlike every other file in this task).
   Otherwise, an explicit chatId+sessionToken pair is verified for real if
   supplied (ready for whenever chat-widget.js is updated to send them).
   Otherwise, rate-limited + audit-logged, matching the customer widget's
   actual behavior today. */
if (chatIsAdminAuthenticated()) {
    log_audit('admin', 'chat-upload-video-chunk', $action . '_admin');
} else {
    $uploadChatId = trim($_POST['chatId'] ?? '');
    $uploadSessionToken = trim($_POST['sessionToken'] ?? '');
    if ($uploadChatId !== '') {
        chatRequireSessionToken($uploadChatId, $uploadSessionToken);
        log_audit($uploadChatId, 'chat-upload-video-chunk', $action);
    } else {
        $uploadIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (!chatCheckRateLimit('chat_upload_video_ip:' . $uploadIp, 60, 600)) { // 60 chunk calls / 10 min / IP — chunked uploads mean many small calls per real video, so this ceiling is higher than the single-shot chat-upload-temp.php limit
            echo json_encode(['success' => false, 'error' => 'Too many uploads. Please wait a few minutes and try again.']);
            exit;
        }
        log_audit('unknown', 'chat-upload-video-chunk', $action . '_no_session');
    }
}
/* TASK-1 END */

$chunkDir = CHAT_UPLOAD_TEMP_DIR . 'chunks/';
```
<!-- TASK-1 END -->

### `chat-widget.js`  →  live path `admin/chat-widget.js`

**Change 1** (original line ~37) — session-token persistence key, same localStorage

<!-- TASK-1 START -->
**Old code:**
```javascript
    storageKeyChatId: 'pc_chat_active_id',
    storageKeyLastMsg: 'pc_chat_last_msg_id',
    storageKeyDraftUuid: 'pc_chat_draft_uuid', // pre-thread temporary draft key, Section 4
    contactFormUrl: '/contact-us/contact-us.html', // absolute path — contact-us.html lives at /contact-us/ not site root
```
**New code:**
```javascript
    storageKeyChatId: 'pc_chat_active_id',
    storageKeyLastMsg: 'pc_chat_last_msg_id',
    /* TASK-1 START — session-token persistence key, same localStorage
       pattern as the other pc_chat_* keys above. */
    storageKeyToken: 'pc_chat_session_token',
    /* TASK-1 END */
    storageKeyDraftUuid: 'pc_chat_draft_uuid', // pre-thread temporary draft key, Section 4
    contactFormUrl: '/contact-us/contact-us.html', // absolute path — contact-us.html lives at /contact-us/ not site root
```
<!-- TASK-1 END -->

**Change 2** (original line ~54) — the session token issued by chat-start.php for

<!-- TASK-1 START -->
**Old code:**
```javascript
    mode: null,           // 'launcher' | 'email' | 'live'
    chatId: null,
    fullName: null,
    email: null,
```
**New code:**
```javascript
    mode: null,           // 'launcher' | 'email' | 'live'
    chatId: null,
    /* TASK-1 START — the session token issued by chat-start.php for
       state.chatId. Required by the backend (this task) on every
       subsequent read/write call for an existing thread — see
       includes/chat-auth.php. Set once by openLiveThread()/
       resumeStoredChat() and read automatically by apiGet()/apiPost()
       below, so individual call sites below don't each need to remember
       to attach it by hand. */
    sessionToken: null,
    /* TASK-1 END */
    fullName: null,
    email: null,
```
<!-- TASK-1 END -->

**Change 3** (original line ~105) — attach the session token to every GET call

<!-- TASK-1 START -->
**Old code:**
```javascript
  }

  async function apiGet(endpoint, params) {
    const qs = new URLSearchParams(params || {}).toString();
    const res = await fetch(CONFIG.apiBase + endpoint + (qs ? '?' + qs : ''));
    return res.json();
  }

  async function apiPost(endpoint, body) {
    const res = await fetch(CONFIG.apiBase + endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body || {}),
    });
    return res.json();
  }
```
**New code:**
```javascript
  }

  async function apiGet(endpoint, params) {
    /* TASK-1 START — attach the session token to every GET call
       automatically, one place, rather than every call site below having
       to remember to add it by hand. Sent as a plain sessionToken query
       param, matching how chat-poll.php/chat-history.php/chat-draft.php
       read it server-side. Omitted entirely (not sent as the string
       "null") when there's no token yet — e.g. chat-start.php itself
       needs none, and the very first chat-poll.php call after a fresh
       chat-start.php response already has state.sessionToken set by then
       (see openLiveThread()), so this only stays empty pre-thread. */
    const merged = state.sessionToken ? Object.assign({ sessionToken: state.sessionToken }, params) : (params || {});
    const qs = new URLSearchParams(merged).toString();
    /* TASK-1 END */
    const res = await fetch(CONFIG.apiBase + endpoint + (qs ? '?' + qs : ''));
    return res.json();
  }

  async function apiPost(endpoint, body) {
    /* TASK-1 START — same reasoning as apiGet() above, for JSON-body POST
       calls. */
    const merged = state.sessionToken ? Object.assign({ sessionToken: state.sessionToken }, body) : (body || {});
    /* TASK-1 END */
    const res = await fetch(CONFIG.apiBase + endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(merged),
    });
    return res.json();
  }
```
<!-- TASK-1 END -->

**Change 4** (original line ~1049)

<!-- TASK-1 START -->
**Old code:**
```javascript
    localStorage.removeItem('pc_chat_name');
    localStorage.removeItem('pc_chat_email');
    localStorage.removeItem(CONFIG.storageKeyDraftUuid);
    if (state.chatId) localStorage.removeItem('pc_ticket_closed_' + state.chatId);
```
**New code:**
```javascript
    localStorage.removeItem('pc_chat_name');
    localStorage.removeItem('pc_chat_email');
    /* TASK-1 START */
    localStorage.removeItem(CONFIG.storageKeyToken);
    state.sessionToken = null;
    /* TASK-1 END */
    localStorage.removeItem(CONFIG.storageKeyDraftUuid);
    if (state.chatId) localStorage.removeItem('pc_ticket_closed_' + state.chatId);
```
<!-- TASK-1 END -->

**Change 5** (original line ~1389) — pass through the sessionToken chat-start.php's

<!-- TASK-1 START -->
**Old code:**
```javascript
          // TASK-9c: true here — this is the fresh pre-chat submission
          // path, the one moment the entrance animation should play.
          openLiveThread(json.chatId, json.fullName, json.email, json.status, undefined, true);
          // TASK-9 END
        } else {
```
**New code:**
```javascript
          // TASK-9c: true here — this is the fresh pre-chat submission
          // path, the one moment the entrance animation should play.
          /* TASK-1 START — pass through the sessionToken chat-start.php's
             response now includes on every success path. */
          openLiveThread(json.chatId, json.fullName, json.email, json.status, undefined, true, json.sessionToken);
          /* TASK-1 END */
          // TASK-9 END
        } else {
```
<!-- TASK-1 END -->

**Change 6** (original line ~1414) — load the token stored by a prior openLiveThread()

<!-- TASK-1 START -->
**Old code:**
```javascript
  function resumeStoredChat(storedChatId) {
    dlog('resuming stored chat: ' + storedChatId);
    // Always fetch from message 0 on resume so the full history loads —
    // using the stored lastMessageId would skip all existing messages and
```
**New code:**
```javascript
  function resumeStoredChat(storedChatId) {
    dlog('resuming stored chat: ' + storedChatId);
    /* TASK-1 START — load the token stored by a prior openLiveThread()
       call BEFORE polling, since apiGet() (see its own TASK-1 comment
       above) only attaches whatever's already in state.sessionToken. This
       endpoint no longer works on storedChatId alone — see chat-poll.php's
       own TASK-1 hardening — so a resume with no stored token, or an
       expired one, now correctly falls into the same "no longer resolves"
       branch below as a deleted thread would, sending the customer back
       through the pre-chat gate rather than silently failing. Using the
       SAME email there resumes this exact thread and issues a fresh
       token (chat-start.php Path 2), so no history is actually lost —
       only a rare, safe extra step. */
    state.sessionToken = localStorage.getItem(CONFIG.storageKeyToken) || null;
    /* TASK-1 END */
    // Always fetch from message 0 on resume so the full history loads —
    // using the stored lastMessageId would skip all existing messages and
```
<!-- TASK-1 END -->

**Change 7** (original line ~1421)

<!-- TASK-1 START -->
**Old code:**
```javascript
        const storedName = localStorage.getItem('pc_chat_name') || '';
        const storedEmail = localStorage.getItem('pc_chat_email') || '';
        // Pass skipRestore=false so openLiveThread also starts from 0
        openLiveThread(storedChatId, storedName, storedEmail, json.status, false);
      } else {
        // Stored chat no longer resolves (e.g. deleted by an admin) — clear
        // the stale pointer and fall back to the launcher.
        localStorage.removeItem(CONFIG.storageKeyChatId);
        localStorage.removeItem(CONFIG.storageKeyLastMsg);
        localStorage.removeItem('pc_chat_name');
        localStorage.removeItem('pc_chat_email');
        renderLauncher();
      }
```
**New code:**
```javascript
        const storedName = localStorage.getItem('pc_chat_name') || '';
        const storedEmail = localStorage.getItem('pc_chat_email') || '';
        // Pass skipRestore=false so openLiveThread also starts from 0.
        // No sessionToken arg needed here — state.sessionToken was already
        // set above, and openLiveThread() only overwrites it when a real
        // one is passed (see that function's own TASK-1 comment).
        openLiveThread(storedChatId, storedName, storedEmail, json.status, false);
      } else {
        // Stored chat no longer resolves — either genuinely deleted by an
        // admin, or (TASK-1) the stored session token was missing/expired.
        // Same safe fallback either way: clear the stale pointers and
        // return to the launcher.
        localStorage.removeItem(CONFIG.storageKeyChatId);
        localStorage.removeItem(CONFIG.storageKeyLastMsg);
        localStorage.removeItem('pc_chat_name');
        localStorage.removeItem('pc_chat_email');
        /* TASK-1 START */
        localStorage.removeItem(CONFIG.storageKeyToken);
        state.sessionToken = null;
        /* TASK-1 END */
        renderLauncher();
      }
```
<!-- TASK-1 END -->

**Change 8** (original line ~1467) — added the trailing `sessionToken` parameter, same

<!-- TASK-1 START -->
**Old code:**
```javascript
  // caller is unaffected) — set true only by the fresh pre-chat-submit
  // call site, read below to decide whether to fire triggerBlobIntro().
  function openLiveThread(chatId, fullName, email, status, skipRestore, playIntroAnimation) {
  // TASK-9 END
    state.mode = 'live';
    state.chatId = chatId;
    state.fullName = fullName;
    state.email = email;
```
**New code:**
```javascript
  // caller is unaffected) — set true only by the fresh pre-chat-submit
  // call site, read below to decide whether to fire triggerBlobIntro().
  /* TASK-1 START — added the trailing `sessionToken` parameter, same
     append-at-the-end approach as TASK-9c took just above: every existing
     caller that doesn't pass one gets undefined, which the guard below
     treats as "keep whatever's already in state" rather than clobbering
     it with nothing. */
  function openLiveThread(chatId, fullName, email, status, skipRestore, playIntroAnimation, sessionToken) {
  // TASK-9 END
  /* TASK-1 END */
    state.mode = 'live';
    state.chatId = chatId;
    /* TASK-1 START — only overwrite state.sessionToken when a real one was
       passed in. rebuildLiveView()/pollOnce() etc. never call
       openLiveThread() again mid-session, so in practice this only matters
       for resumeStoredChat(), which already sets state.sessionToken itself
       from localStorage BEFORE calling openLiveThread() — passing
       undefined here must not blank that back out. */
    if (sessionToken) state.sessionToken = sessionToken;
    /* TASK-1 END */
    state.fullName = fullName;
    state.email = email;
```
<!-- TASK-1 END -->

**Change 9** (original line ~1481) — persist the session token the same way chatId/name

<!-- TASK-1 START -->
**Old code:**
```javascript
    localStorage.setItem(CONFIG.storageKeyChatId, chatId);
    if (fullName) localStorage.setItem('pc_chat_name', fullName);
    if (email) localStorage.setItem('pc_chat_email', email);
    updateLauncherButtonState();

    headerTitleEl.textContent = fullName || 'Live Chat';
```
**New code:**
```javascript
    localStorage.setItem(CONFIG.storageKeyChatId, chatId);
    if (fullName) localStorage.setItem('pc_chat_name', fullName);
    if (email) localStorage.setItem('pc_chat_email', email);
    /* TASK-1 START — persist the session token the same way chatId/name/
       email already are, so a page reload can send it straight back to
       chat-poll.php via resumeStoredChat() without needing a fresh
       chat-start.php round-trip. */
    if (state.sessionToken) localStorage.setItem(CONFIG.storageKeyToken, state.sessionToken);
    /* TASK-1 END */
    updateLauncherButtonState();

    headerTitleEl.textContent = fullName || 'Live Chat';
```
<!-- TASK-1 END -->

**Change 10** (original line ~2257) — chat-rate.php's submit action now requires a

<!-- TASK-1 START -->
**Old code:**
```javascript
            rating: selectedRating,
            comment: textarea.value.trim(),
          }),
        });
```
**New code:**
```javascript
            rating: selectedRating,
            comment: textarea.value.trim(),
            /* TASK-1 START — chat-rate.php's submit action now requires a
               valid session token for this chatId, same as every other
               customer-facing write. */
            sessionToken: state.sessionToken,
            /* TASK-1 END */
          }),
        });
```
<!-- TASK-1 END -->

**Change 11** (original line ~2296) — draftKey 'unread:{chatId}' is one of the two real

<!-- TASK-1 START -->
**Old code:**
```javascript
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'save', draftKey: 'unread:' + state.chatId, body: String(count) }),
      });
    } catch (e) { /* non-critical */ }
```
**New code:**
```javascript
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        /* TASK-1 START — draftKey 'unread:{chatId}' is one of the two real-
           chat-id shapes chat-draft.php now requires a token for (see that
           file's own TASK-1 comment on the 3 draftKey shapes). */
        body: JSON.stringify({ action: 'save', draftKey: 'unread:' + state.chatId, body: String(count), sessionToken: state.sessionToken }),
        /* TASK-1 END */
      });
    } catch (e) { /* non-critical */ }
```
<!-- TASK-1 END -->

**Change 12** (original line ~2304) — same reasoning as saveUnreadCount() above; GET so

<!-- TASK-1 START -->
**Old code:**
```javascript
    if (!state.chatId) return;
    try {
      const res = await fetch(CONFIG.apiBase + 'chat-draft.php?action=get&draftKey=' + encodeURIComponent('unread:' + state.chatId));
      const json = await res.json();
      if (json.success && json.draft) {
```
**New code:**
```javascript
    if (!state.chatId) return;
    try {
      /* TASK-1 START — same reasoning as saveUnreadCount() above; GET so
         the token goes in the querystring, matching every other GET
         endpoint in this file. */
      const tokenQs = state.sessionToken ? '&sessionToken=' + encodeURIComponent(state.sessionToken) : '';
      const res = await fetch(CONFIG.apiBase + 'chat-draft.php?action=get&draftKey=' + encodeURIComponent('unread:' + state.chatId) + tokenQs);
      /* TASK-1 END */
      const json = await res.json();
      if (json.success && json.draft) {
```
<!-- TASK-1 END -->

**Change 13** (original line ~2459) — chat-upload-temp.php now verifies these when

<!-- TASK-1 START -->
**Old code:**
```javascript
        fd.append('file', file);
        fd.append('kind', kind);
        const res = await fetch(CONFIG.apiBase + 'chat-upload-temp.php', { method: 'POST', body: fd });
        const json = await res.json();
```
**New code:**
```javascript
        fd.append('file', file);
        fd.append('kind', kind);
        /* TASK-1 START — chat-upload-temp.php now verifies these when
           present (see that file's own TASK-1 comment on why it can't
           require them outright yet) — sending them here closes that gap
           for real, for this widget, going forward. */
        if (state.chatId) fd.append('chatId', state.chatId);
        if (state.sessionToken) fd.append('sessionToken', state.sessionToken);
        /* TASK-1 END */
        const res = await fetch(CONFIG.apiBase + 'chat-upload-temp.php', { method: 'POST', body: fd });
        const json = await res.json();
```
<!-- TASK-1 END -->

**Change 14** (original line ~2510) — see chat-upload-temp.php's TASK-1 comment above;

<!-- TASK-1 START -->
**Old code:**
```javascript
      fd.append('fileSize', file.size);
      fd.append('chunk', chunk, 'chunk_' + i);

      try {
```
**New code:**
```javascript
      fd.append('fileSize', file.size);
      fd.append('chunk', chunk, 'chunk_' + i);
      /* TASK-1 START — see chat-upload-temp.php's TASK-1 comment above;
         same reasoning applies here. Sent on every chunk, not just
         finalize, since chat-upload-video-chunk.php checks admin-token/
         session-token on both actions independently. */
      if (state.chatId) fd.append('chatId', state.chatId);
      if (state.sessionToken) fd.append('sessionToken', state.sessionToken);
      /* TASK-1 END */

      try {
```
<!-- TASK-1 END -->

**Change 15** (original line ~2533) — same as the upload-action chunk loop above.

<!-- TASK-1 START -->
**Old code:**
```javascript
      fd.append('totalChunks', totalChunks);
      fd.append('fileName', file.name);
      const res = await fetch(CONFIG.apiBase + 'chat-upload-video-chunk.php', { method: 'POST', body: fd });
      const json = await res.json();
```
**New code:**
```javascript
      fd.append('totalChunks', totalChunks);
      fd.append('fileName', file.name);
      /* TASK-1 START — same as the upload-action chunk loop above. */
      if (state.chatId) fd.append('chatId', state.chatId);
      if (state.sessionToken) fd.append('sessionToken', state.sessionToken);
      /* TASK-1 END */
      const res = await fetch(CONFIG.apiBase + 'chat-upload-video-chunk.php', { method: 'POST', body: fd });
      const json = await res.json();
```
<!-- TASK-1 END -->

**Change 16** (original line ~2694) — same as the other chat-upload-temp.php call

<!-- TASK-1 START -->
**Old code:**
```javascript
          fd.append('file', recordedBlob, 'voice-message.' + voiceExt);
          fd.append('kind', 'audio');
          const res = await fetch(CONFIG.apiBase + 'chat-upload-temp.php', { method: 'POST', body: fd });
          const json = await res.json();
```
**New code:**
```javascript
          fd.append('file', recordedBlob, 'voice-message.' + voiceExt);
          fd.append('kind', 'audio');
          /* TASK-1 START — same as the other chat-upload-temp.php call
             site above. */
          if (state.chatId) fd.append('chatId', state.chatId);
          if (state.sessionToken) fd.append('sessionToken', state.sessionToken);
          /* TASK-1 END */
          const res = await fetch(CONFIG.apiBase + 'chat-upload-temp.php', { method: 'POST', body: fd });
          const json = await res.json();
```
<!-- TASK-1 END -->

## New files

- `admin/includes/chat-auth.php` — the session-token auth layer (`issue_token`, `verify_token`, `invalidate_all_tokens`, `log_audit`, `chatRequireSessionToken`). New file — in the zip.
- `admin/chat-session-token-setup.php` — one-time schema script creating `chat_session_token` and `chat_auth_audit_log`; run once, then delete or move it out of the web root, same as `chat-setup.php`. New file — in the zip.

### ⚠️ NEW FOLDER — INTEGRATOR MUST CREATE IT: `admin/includes/`

**`admin/includes/` does not exist and is not mentioned in either `adoption-website-project-summary.md` or `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`** (checked both folder-structure sections; the only `admin/` subfolders listed are `temp_admin/`, `chat-uploads-temp/`, `chat-admin-photos/`, `reviews/`, `video_uploads_temp/`, `video_uploads_final/`, and similar). Create the folder `includes/` **inside the live `admin/` folder** (on the server: `public_html/adopt/admin/includes/`). Unzipping the zip on top of the site root creates it automatically, but if files are copied by hand, create it first. All 11 changed endpoints load `chat-auth.php` with `require_once __DIR__ . '/includes/chat-auth.php'`, so the folder name and location must be exactly this or every endpoint fails.

`admin/chat-session-token-setup.php` sits directly in the existing `admin/` folder, beside `chat-setup.php`. It is not in a new folder.

**HTML pages:** this task creates no `.html` pages. Nothing needs the `index.html` folder convention.

**What is in the zip:** `Admin-Project-1-Task-1.zip` holds the two new files above at their live paths from the site root, plus this completion file as `Task-1-Completion.md` at the zip root. The completion file is paperwork, not a site file — **do not copy `Task-1-Completion.md` into the live site folder.**

No secrets, `config.php`, `pet.json`, `.env`, or anything under `applications/`, `uploads/`, `chat-media/` or `logs/` is in the zip. No new setting is needed: both files read the database through the existing `chat-config.php`.

## Contract Deviations

None — matches published contract exactly. Specifically:
- `includes/chat-auth.php` function signatures (`issue_token(principal_id)`,
  `verify_token(token_string, principal_id)`, `invalidate_all_tokens(principal_id)`,
  `log_audit(principal_id, endpoint, result)`) match the published signatures
  exactly, with matching argument order and return types.
- `chat_session_token` table matches the published shape exactly:
  `token_hash`, `principal_id`, `issued_at`, `expires_at` — no added or
  removed columns.
- `check_pin_throttle()` was correctly left unimplemented, as the contract
  specifies it belongs to Task 3.

One addition beyond the contract, not a deviation from it:
`chat_auth_audit_log`'s table shape was never published in the Shared
Contract (only `log_audit()`'s function signature was), so its column
shape (`principal_id`, `endpoint`, `result`, `created_at`) is this task's
own design decision, documented in the Code Changes section above.

## Enhancement Decisions

- **"Use an opaque, cryptographically random token stored server-side (not
  a JWT)" — Adopted.** A DB-backed token gets real revocation for free
  (`invalidate_all_tokens()` is one `DELETE`) instead of needing a second
  subsystem (a JWT blocklist) to fake revocation on a format that's
  valid-until-expiry by design.
- **"Store the token as a hash, using the same hash/verify helper Task 5a
  will use for passwords" — Modified.** Implemented as SHA-256, deliberately
  NOT shared with password hashing. Confirmed via web search before
  implementing: SHA-256-hashed opaque tokens are the current standard
  pattern for session tokens; bcrypt/Argon2 remain the standard for
  passwords, for a different reason. A session token is a 256-bit random
  secret with no brute-forceable structure, looked up by exact match on
  every poll request (every ~2.5s per open chat) — SHA-256 is the correct,
  fast tool for that. A password is the opposite case: low-entropy,
  human-chosen, and needs a deliberately slow, salted algorithm
  (`password_hash()`'s bcrypt/Argon2) specifically to resist offline brute
  force. Sharing one helper between these two threat models would make
  either every poll request pathologically slow, or password storage
  brute-forceable — the wrong tool for one of the two jobs regardless of
  which algorithm was picked. Task 5a should call `password_hash()`/
  `password_verify()` directly, not a function from this file.
- **"Add an absolute expiry (e.g. 24h) on top of any idle expiry" —
  Adopted.** Implemented as a 4-hour sliding idle expiry (extended on every
  successful `verify_token()` call) capped by a 24-hour absolute ceiling
  from issuance. An actively-used chat session never dies mid-conversation;
  an abandoned or leaked token still expires on a fixed schedule no amount
  of activity can extend.

## Bugs Found Outside Task Scope

- `chat-rate.php`'s `submit` action never cross-checks the submitted
  `email` against the thread's actual stored email — a rating can be
  submitted with any syntactically valid email, unrelated to whoever
  actually owns the chat. Session-token enforcement (this task) now
  prevents an unrelated party from submitting at all, but doesn't fix this
  separate, pre-existing data-integrity gap. Not fixed here — out of this
  task's scope.
- `chat-config.php` had a real, non-placeholder database password
  committed in plaintext (`DB_PASSWORD` constant). Raised to the CEO
  directly; confirmed already revoked, no further action needed.

### Pending items, open doubts and new findings (everything that still sits)

Listed most important first. None of these were fixed here, because they are outside this task's scope or need a decision.

1. **Deploy order matters.** Run `admin/chat-session-token-setup.php` once (it creates `chat_session_token` and `chat_auth_audit_log`) **before** the changed endpoints go live, then delete the script. If the endpoints go live first, any call that reaches `issue_token()` / `verify_token()` / `log_audit()` hits a missing table and fails.
2. **All 12 changed files must go live together** (the 11 PHP files plus `chat-widget.js`). The new PHP rejects calls with no session token, so an old widget against new PHP shows every customer a "session required" error. A new widget against old PHP is harmless.
3. **Cached widget.** Public pages load `/admin/chat-widget.js` with no version string. A visitor's browser may keep running the old cached widget after deploy. Consider a cache-busting `?v=` on the script tag; that tag sits on every public page, so it is outside this task.
4. **Chats already open at deploy time.** A customer who opened a chat before deploy has no stored token. On their next page load they are returned to the launcher once; re-entering the same email resumes the same thread and issues a fresh token (no history lost).
5. **The audit log will grow fast (a weakness in my own design).** `verify_token()` writes one `chat_auth_audit_log` row and one `chat_session_token` update on every successful check, and an open chat polls about every 2.5 seconds. That is roughly 1,400 log rows an hour per open chat, with no pruning. Recommended fix, needs a CEO decision: log only failures and state changes (issue, revoke, lock, verify-tier events), and/or add a retention clean-up. I did not change the code in this hand-in.
6. **Admin use of `chat-draft.php` is unconfirmed.** The summary describes it as the widget's draft and unread-badge store and does not say whether `admin-dashboard.html` also calls it. If the admin composer saves drafts with a real chat ID as `draftKey`, those calls now get a 401 because that file has no admin exemption (the two upload endpoints do). `admin-dashboard.html` is needed to confirm; if so, the fix is the same `chatIsAdminAuthenticated()` check used in `chat-upload-temp.php`.
7. **Admin use of `chat-upload-temp.php` is also inferred, not confirmed.** I added an admin-token exemption there, and it is harmless either way, but I could not verify what `admin-dashboard.html` sends to it.
8. **Task 7 dependency.** Task 7's `chat-admin-list.php` was built against a table called `chat_auth_audit`; the real table is `chat_auth_audit_log`, and its `endpoint` and `result` values are as listed in this file's Second Verification Round and the sync notice. Task 7's badge logic needs the table name and the result-based matching fixed at integration. Also, `verify_token`'s frequent `verified` rows must be ignored when working out "verified this session".
9. **`admin/includes/chat-auth.php` is reachable by URL.** It only declares functions and constants, prints nothing and holds no secrets, so this is not dangerous. A `Deny from all` `.htaccess` in `admin/includes/` would be tidy; I did not add one because it is a new file beyond what was asked. CEO's call.
10. **Stale header comment in `chat-history.php`** (it described a "see previous messages" feature and admin use that the current summary says do not exist). I corrected it in place; see Change 1 for that file. Other documentation may still repeat the old description.
11. **Not run against a live database.** The PHP files pass a real PHP parser and the widget passes a JavaScript parse, but the token flow has not been exercised end to end. Please test: open a chat, send a message, reload the page, send an image, and try `chat-poll.php` with a wrong token.

## Self-Check Confirmation

- I touched only the files listed in Files Changed: the 11 files named in this task's request list, `chat-widget.js` (added to scope after being raised as a blocker and then supplied by the CEO), the new `includes/chat-auth.php`, and the new `chat-session-token-setup.php` (flagged as beyond the stated 12). I did not touch `chat-setup.php`, `chat-config.php`, `chat-admin-reply.php`, or anything else.
- Every block has `TASK-1 START` / `TASK-1 END` anchors (markdown anchors around each pair, and language-native `/* */` or `//` anchors inside the code, because `<!-- -->` is invalid inside PHP and JS), and every non-trivial block carries a comment saying what it does.
- Every old snippet was copied from the original file supplied to me and appears exactly once in it. Checked by script, not by eye: each old snippet counted once in its original, and replaying all pairs on the original reproduces the delivered file exactly.
- The zip `Admin-Project-1-Task-1.zip` contains the two files listed under New files at the paths in Files Changed, rooted at the site root (`admin/...`), plus this completion file at the zip root, and nothing else. Every new file in the zip is listed; every "New file" listed is in the zip. The new folder `admin/includes/` is flagged under New files.
- How I tested: `chat-widget.js` was parsed with Node (`new Function(source)`) and parses cleanly. No PHP binary was available, so I parsed all 13 PHP files (the 11 originals and my delivered versions, plus the two new files) with the `php-parser` npm package using the PHP 8 grammar; all parse, and I confirmed the parser rejects broken PHP, including a literal `<!-- -->` anchor inside PHP. I also read every change against the real `chat-config.php` helpers. This checks syntax only. The token flow was not run against a live database; see pending item 11.

## SECOND VERIFICATION ROUND

Asked to re-verify the whole deliverable against both project docs
(`ADMIN_DASHBOARD_PROJECT_SUMMARY.md` and this task's own assignment doc)
line by line, not just re-skim. Scope Lock held throughout — every fix
below is inside a file already in this task's 12.

### Bug found and fixed: `chat-history.php`'s admin-bypass was built on stale documentation

**What was wrong:** the original TASK-1 fix for this file added
`if (!chatIsAdminAuthenticated()) { chatRequireSessionToken(...); }` — an
admin bypass — justified by this file's own header comment, which claims
it "works for both the customer ... and an admin reviewing a Done thread
(by chatId + admin auth)." That header comment is stale.
`ADMIN_DASHBOARD_PROJECT_SUMMARY.md`'s current, authoritative section for
this exact file explicitly supersedes it: the real purpose is "Download
transcript" (not the "see previous messages" inline reveal the old header
describes), it's driven exclusively by `downloadTranscript()` in
`chat-widget.js` (the customer widget), and it's documented as "no auth
(chatId is the credential), same trust model as `chat-poll.php`" — with no
admin usage anywhere in the actual shipped code. I trusted the file's own
comment over the authoritative project doc the first time through; that
was the error.

**Fix:** removed the `chatIsAdminAuthenticated()` bypass entirely.
Enforcement is now unconditional — `chatRequireSessionToken($chatId,
$sessionToken);` — exactly matching `chat-poll.php`'s own TASK-1 block,
since both files share the identical trust model. Also corrected this
file's own stale header comment (the "Purpose" paragraph and the
dual-use claim) rather than leaving a comment standing that actively
contradicts the code three lines below it.

**Impact:** none on real behavior — no admin code path was ever
confirmed to call this endpoint, so removing the bypass closes a
theoretical/incorrect-premise branch, not a live one. This is a
correctness fix to match documented reality, not a vulnerability fix.

The Code Changes section above already shows the final code for this file. The first-pass version is not repeated here because it is not original-file text and would not match anything the integrator searches for.

The file's own header comment was also corrected in place — the "Purpose" paragraph and the dual-use claim — to stop contradicting the code below it; that edit is Change 1 for this file in the Code Changes section.

### Gap found and closed: `chat-upload-temp.php` was missing the same admin-token exemption `chat-upload-video-chunk.php` already has

**What was missing:** `chat-upload-video-chunk.php` checks
`chatIsAdminAuthenticated()` first and exempts a valid admin from the
anonymous rate-limited fallback, because the project doc explicitly
confirms the admin dashboard already sends `X-Admin-Token` to that
specific endpoint. I never added the equivalent check to
`chat-upload-temp.php`, even though it's very likely used by the admin
dashboard too — `chat-admin-reply.php`'s own `imageTokens[]`/`audioToken`
inputs have to come staged from somewhere, and no separate admin-only
staging endpoint exists anywhere in the documented file structure, so
this is the only real candidate. Unlike the video-chunk case, the project
doc does not explicitly confirm the admin dashboard sends `X-Admin-Token`
here — so this isn't a confirmed bug, but leaving admin uploads
(correctly or not) subject to the same 30-per-10-minutes-per-IP limit
built for anonymous abuse, with no way to ever get the exemption if the
header *is* already being sent, was an inconsistency worth closing.

**Fix:** added the same `chatIsAdminAuthenticated()` precedence check
used in `chat-upload-video-chunk.php`. Purely additive and safe either
way — if the admin dashboard sends the header, admin uploads now get
verified for real and exempted from the rate limit; if it doesn't
(most likely, today), nothing changes and the existing rate-limited
fallback still applies exactly as before.

The Code Changes section above already shows the final code for this file. The first-pass version is not repeated here because it is not original-file text and would not match anything the integrator searches for.

### Correction: Contract Deviations section understated an actual, minor deviation

The original "Contract Deviations: None" claim was not fully accurate.
`chat_session_token`'s real, shipped schema includes `id INT
AUTO_INCREMENT PRIMARY KEY`, which is not in the Shared Contract's
published column list (`token_hash, principal_id, issued_at,
expires_at`). This is a standard, practically-necessary surrogate primary
key — MySQL tables need one — and every published column is still present
exactly as specified, with no semantic change. But claiming "no added ...
columns" when a column was in fact added was imprecise. Corrected: this
is a flagged, understood deviation (an additive, non-breaking surrogate
key), not a true "none."

### Checked and confirmed correct — no further bugs found

- Every `sessionToken`/`chatId` read across all 12 files uses `?? ''`
  null-coalescing — zero undefined-index risk, verified by grep across
  every TASK-1 block.
- `$input`/`chatJsonInput()` is parsed before every TASK-1 line that reads
  from it, in every file — verified by line-number comparison, not
  assumption.
- `chat-typing.php`'s customer-side gate (`who === 'user' ||
  'user_recording'`) covers exactly the full `$validWho` vocabulary
  defined in that file (`user`, `admin`, `user_recording`,
  `admin_recording`) — confirmed against the real array literal, not
  the docs.
- `chat_rate_limits.rate_key` is `VARCHAR(255)` — confirmed my new keys
  (`chat_upload_temp_ip:<ip>`, `chat_upload_video_ip:<ip>`) fit with room
  to spare, including worst-case IPv6.
- `chat_threads.chat_id` is `VARCHAR(20)` in the real `chat-setup.php` —
  confirmed `chat_session_token.principal_id` and
  `chat_auth_audit_log.principal_id` match that width exactly.
- `chatCheckRateLimit()`, `chatRespond()`, `chatIsAdminAuthenticated()`,
  `chatJsonInput()` — re-read their real implementations in
  `chat-config.php` directly (not assumed from memory) and confirmed
  every one of my calls matches their actual signatures and return
  semantics.
- `chat-upload-video-chunk.php`'s own file never used `chatRespond()`
  anywhere, only raw `echo json_encode(...); exit;` — confirmed my
  addition matched that file's own existing convention (rule 7) rather
  than introducing `chatRespond()` where the file never used it.
- Re-checked every other touched file's own header comment against
  `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` for the same kind of staleness that
  caused the `chat-history.php` bug — `chat-poll.php`, `chat-send.php`,
  `chat-typing.php`, `chat-message-edit.php`, `chat-message-delete.php`,
  and `chat-rate.php` all match the authoritative doc. `chat-history.php`
  was the only mismatch.
- Brace/paren balance re-verified on every file touched in this round
  after editing.

### Files to request — none missed for this task specifically

Re-checked whether Task 1 needed any file beyond what was requested.
`chat-config.php` was the one real gap, already requested and received
mid-task. `chat-widget.js` was the second, already requested and wired in
after being raised as a blocker. No further file is needed to make Task
1's own scope (the session-token layer and its 11 enforcement points)
actually complete and correct. `admin-dashboard.html` would be needed to
*verify* (rather than reason about from documentation) exactly what it
sends to `chat-upload-temp.php` today — flagging this for whoever owns
Task 2 (admin-side hardening) or final integration, since I can't request
a file outside my own task's scope to chase down a coordination detail
that doesn't block Task 1's own deliverable.
