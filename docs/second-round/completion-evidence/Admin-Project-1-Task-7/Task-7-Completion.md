# Task-7-Completion.md

## Task Number
Task 7 — Admin Auth-State Badge

## What Was Required
Add a read-only badge to the admin claim/list view showing each chat's
current protection state — `none` / PIN set / password set / currently
locked / verified-this-session — derived entirely from the `log_audit()`
Shared Contract's output (no new logging added). The original brief
listed one file (`chat-admin-list.php`); `admin-dashboard.html` was added
to scope mid-task on explicit CEO direction so the badge data could
actually be rendered. No Recommended Enhancements were listed beyond this
core scope, and none were adopted.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `chat-admin-list.php` | `admin/chat-admin-list.php` | Changed | Yes — Task 2 touched this file first (admin auth enforcement). This task built on top of Task 2's already-merged state, sequentially, per the Sequential File Ownership Chain protocol. Not concurrently shared with any other task going forward. |
| `admin-dashboard.html` | `admin/index.html` | Changed | Yes — heavily shared (visible TASK-4, TASK-8, and BUGFIX-2 anchor blocks already present in the file from other tasks). All additions here are isolated inside their own `<!-- TASK-7 START -->` / `<!-- TASK-7 END -->` blocks and don't alter any surrounding code belonging to other tasks. |

## Code Changes

### `chat-admin-list.php`  →  live path `admin/chat-admin-list.php`

> This file went through four passes before reaching the code shown
> below. Pass 1 built against a flagged assumption (no audit-log
> vocabulary had been published anywhere). Pass 2 reconciled the table
> name and matching strategy directly against a reply from Task 1's
> worker. Pass 3 applied a project-wide Worker Sync Notice
> (Post-Task1-Task7) that corrected Pass 2's result string and fixed a
> polling-noise masking bug. Pass 4 was a self-initiated re-audit that
> caught two further latent issues (a same-second ordering ambiguity and
> an unverified type hint). The "Old code" below is copied character for
> character from the original file as given; the "New code" is the
> current, final Pass-4 state — nothing here reverts to an earlier pass.
> Full reconciliation detail for each pass follows after the three
> change blocks.

<!-- TASK-7 START -->
**Old code:**
```php
$db = chatDb();
$action = $_GET['action'] ?? 'list';

if ($action === 'list') {
```
**New code:**
```php
$db = chatDb();
$action = $_GET['action'] ?? 'list';

// <!-- TASK-7 START -->
// Task 7 — Admin Auth-State Badge. Read-only helpers that turn a chat's
// existing log_audit() rows (Task 1's contract) into one of five
// display states for the admin list/claim view: none, pin_set,
// password_set, locked, verified_session. No new logging is added here —
// this only reads rows some other task already writes.
//
// 3-PASS HISTORY (see Task-7-Completion.md for full detail on each):
//   1st pass — built against a flagged assumption (table `chat_auth_audit`,
//     endpoint substrings) since no vocabulary was published.
//   2nd pass — reconciled against Task 1's real implementation directly:
//     table is `chat_auth_audit_log`, and the state signal lives in
//     `result` (not `endpoint`). Matched on result === 'verified'.
//   3rd pass (current) — per the project-wide Worker Sync Notice
//     (Post-Task1-Task7), 'verified' was wrong for this purpose: Task 1's
//     verify_token() logs result 'verified' on *every* successful session
//     poll (~every 2.5s for any open chat panel), so treating the newest
//     'verified' row as "verified this session" would show that state for
//     nearly every active chat regardless of whether a PIN/password was
//     ever entered. The Sync Notice's proposed vocabulary fixes this with
//     a distinct result string, 'tier_verified', reserved for an actual
//     PIN/password check (to be logged by Tasks 3/5a) — and explicitly
//     calls for excluding verify_token rows from this computation
//     entirely so that high-frequency polling noise can never mask an
//     earlier real lock or tier-verification event. Both changes are
//     applied in chatDeriveAuthBadge() below.
//
// Current result-value vocabulary (CEO-confirmed):
//   pin_set | password_set | tier_verified | locked | unlocked
// 'unlocked' needs no explicit branch below — it simply isn't 'locked',
// so it naturally falls through to whatever the tier scan finds next.
//
// principal_id is assumed to equal chat_threads.chat_id, matching how
// every other file in this project identifies one anonymous chat
// conversation — see the Shared Contract's reuse notes.

// Batch-fetches every audit row for a set of chat IDs in one query (no
// N+1 per-thread lookups) and reduces each chat's rows down to a single
// badge state. Orders by created_at DESC with id DESC as a tiebreaker —
// `created_at` alone isn't reliable for same-second rows (e.g. a tier
// being set and immediately verified in one request/response cycle), and
// since the audit log is insert-only, its auto-increment id always
// matches true chronological order even when timestamps tie.
function chatFetchAuditBadges($db, array $principalIds) {
    $badges = [];
    foreach ($principalIds as $id) {
        $badges[$id] = 'none'; // default: no audit history at all for this chat
    }
    if (empty($principalIds)) {
        return $badges;
    }

    $placeholders = implode(',', array_fill(0, count($principalIds), '?'));
    $stmt = $db->prepare(
        "SELECT principal_id, endpoint, result, created_at
         FROM chat_auth_audit_log
         WHERE principal_id IN ($placeholders)
         ORDER BY created_at DESC, id DESC"
    );
    $stmt->execute($principalIds);
    $rows = $stmt->fetchAll();

    // Group rows by principal, keeping the DESC time order from the query
    // so index 0 of each group is always that chat's most recent event.
    $byPrincipal = [];
    foreach ($rows as $row) {
        $byPrincipal[$row['principal_id']][] = $row;
    }

    foreach ($byPrincipal as $principalId => $auditRows) {
        $badges[$principalId] = chatDeriveAuthBadge($auditRows);
    }

    return $badges;
}

// Applies the badge precedence rule to one chat's audit rows (already
// sorted newest-first): a live lock always wins display, then a real
// tier verification, then whichever protection tier was ever configured
// (most recent tier wins, in case of an upgrade), then 'none'.
function chatDeriveAuthBadge(array $auditRowsDesc) {
    // Exclude Task 1's high-frequency session-token polling noise —
    // verify_token() logs a 'verified' row on every successful poll, so
    // without this filter it would almost always be the literal newest
    // row for any open chat and could mask a real lock or tier-verified
    // event that happened earlier but isn't chronologically last.
    $meaningful = array_values(array_filter($auditRowsDesc, function ($row) {
        return $row['endpoint'] !== 'verify_token';
    }));

    $mostRecent = $meaningful[0] ?? null;

    if ($mostRecent && $mostRecent['result'] === 'locked') {
        return 'locked';
    }

    if ($mostRecent && $mostRecent['result'] === 'tier_verified') {
        return 'verified_session';
    }

    // Neither locked nor freshly tier-verified right now — fall back to
    // whichever tier was most recently set.
    foreach ($meaningful as $row) {
        if ($row['result'] === 'password_set') {
            return 'password_set';
        }
        if ($row['result'] === 'pin_set') {
            return 'pin_set';
        }
    }

    return 'none';
}
// <!-- TASK-7 END -->

if ($action === 'list') {
```
<!-- TASK-7 END -->

<!-- TASK-7 START -->
**Old code:**
```php
    $archived = $stmt->fetchAll();

    chatRespond([
        'success' => true,
        'waiting' => $waiting,
        'activeMine' => $activeMine,
        'activeOthers' => $activeOthers,
        'done' => $done,
        'archived' => $archived,
    ]);
```
**New code:**
```php
    $archived = $stmt->fetchAll();

    // <!-- TASK-7 START -->
    // Attach the read-only auth_badge field to every thread across all
    // five buckets in one batch lookup, so the admin dashboard can render
    // each chat's protection state without a second request per thread.
    $allThreadsForBadges = array_merge($waiting, $activeMine, $activeOthers, $done, $archived);
    $principalIds = array_values(array_unique(array_filter(array_map(
        function ($t) { return $t['chat_id'] ?? null; },
        $allThreadsForBadges
    ))));
    $authBadges = chatFetchAuditBadges($db, $principalIds);

    // Writes the computed auth_badge onto every row of a bucket in place.
    $attachAuthBadge = function (array &$threads) use ($authBadges) {
        foreach ($threads as &$t) {
            $t['auth_badge'] = $authBadges[$t['chat_id']] ?? 'none';
        }
        unset($t);
    };
    $attachAuthBadge($waiting);
    $attachAuthBadge($activeMine);
    $attachAuthBadge($activeOthers);
    $attachAuthBadge($done);
    $attachAuthBadge($archived);
    // <!-- TASK-7 END -->

    chatRespond([
        'success' => true,
        'waiting' => $waiting,
        'activeMine' => $activeMine,
        'activeOthers' => $activeOthers,
        'done' => $done,
        'archived' => $archived,
    ]);
```
<!-- TASK-7 END -->

<!-- TASK-7 START -->
**Old code:**
```php
    $results = $stmt->fetchAll();

    chatRespond(['success' => true, 'results' => $results]);
```
**New code:**
```php
    $results = $stmt->fetchAll();

    // <!-- TASK-7 START -->
    // Lookup results also carry auth_badge, consistent with the list
    // results — an admin jumping straight to one thread via the lookup box
    // should see its protection state before opening it, same as any
    // thread found through the normal list view.
    $lookupPrincipalIds = array_values(array_unique(array_filter(array_map(
        function ($t) { return $t['chat_id'] ?? null; },
        $results
    ))));
    $lookupBadges = chatFetchAuditBadges($db, $lookupPrincipalIds);
    foreach ($results as &$r) {
        $r['auth_badge'] = $lookupBadges[$r['chat_id']] ?? 'none';
    }
    unset($r);
    // <!-- TASK-7 END -->

    chatRespond(['success' => true, 'results' => $results]);
```
<!-- TASK-7 END -->

#### Reconciliation history for this file (all four passes)

**Pass 1 — flagged assumption.** The Shared Contract only published
`log_audit(principal_id, endpoint, result): void` as a function
signature — no audit table name, column list, or endpoint/result value
vocabulary. Since the task brief said the badge must be sourced "entirely
from the log_audit() contract's output format," I built against a
documented assumption (table `chat_auth_audit`, endpoint substrings
`pin`/`password`/`lock`/`verify`) rather than stalling, and flagged it for
reconciliation at integration.

**Pass 2 — reconciliation reply received from Task 1's worker, quoted in
full for the record:**

> Good catch by that worker — flagging instead of guessing blind was the
> right call, and there's a real mismatch worth correcting precisely.
> Here's the reconciliation against my actual Task 1 implementation:
>
> **Table name:** Real table is `chat_auth_audit_log`, not
> `chat_auth_audit`.
>
> **Columns:** `id, principal_id, endpoint, result, created_at` — the
> extra `id` primary key is harmless against their query (they don't
> select it), everything else matches what they assumed.
>
> **The real mismatch — endpoint/result vocabulary:** Task 7's
> `chatDeriveAuthBadge()` substring-matches on `endpoint` (looking for
> `pin`/`password`/`lock`/`verify`) and checks `result === 'success'`/
> `'locked'`. That won't work against my actual convention. In my
> implementation:
> - `endpoint` = the action/function name that logged the row
>   (`issue_token`, `verify_token`, `invalidate_all_tokens`)
> - `result` = the outcome of that action (`issued`, `verified`,
>   `invalid_or_expired`, `missing_input`, `revoked_all`)
>
> So `'verify'` happens to still match (`verify_token` contains it), but
> their result check needs `=== 'verified'`, not `'success'`. More
> importantly, in my own completion doc I documented the intended pattern
> for future tasks as: Task 3 logs a `'pin_set'` result, Task 5a logs a
> `'password_set'` result, Task 4 logs a `'locked'` result — i.e., the
> tier/state signal was always meant to live in `result`, not as an
> `endpoint` substring. Under that convention, `endpoint` for those future
> calls could be anything — matching on it will silently miss real
> PIN/password/lock events even once Tasks 3/4/5a ship.
>
> **What I can't fully resolve yet:** Tasks 3, 4, and 5a haven't shipped,
> so the exact result strings they'll use aren't locked in anywhere — my
> own doc comment was illustrative, not a contract. This is genuinely a
> small gap in the Shared Contract: it published `log_audit()`'s signature
> but never an agreed result-value vocabulary, which is exactly what a
> badge reading only that table needs to be reliable.
>
> **Recommendation:** `chatDeriveAuthBadge()` should match on `result`
> values against a fixed vocabulary — `'pin_set'`, `'password_set'`,
> `'locked'`, `'verified'` — rather than substring-matching `endpoint`.
> I'd suggest that exact four-word vocabulary get published as a short
> addendum to the Shared Contract so Tasks 3/4/5a build their
> `log_audit()` calls against it deliberately.

Action taken: `chatFetchAuditBadges()` switched to querying
`chat_auth_audit_log` (corrected table name), and `chatDeriveAuthBadge()`
switched to matching on `result === 'locked'` / `'verified'` /
`'password_set'` / `'pin_set'` instead of `endpoint` substrings. The
precedence order itself was unchanged.

**Pass 3 — project-wide Worker Sync Notice (Post-Task1-Task7), shared to
all workers on Admin Project #1.** The Notice superseded part of Pass 2's
proposal with two corrections:

- Confirmed `result` (not `endpoint`) is where the state signal lives —
  same conclusion Pass 2 already reached, now confirmed project-wide.
- `'verified'` was the wrong result string to match, and matching on it
  had a real bug underneath it: `verify_token()` logs `result: 'verified'`
  on *every successful session-token poll* — roughly every 2.5 seconds
  for any open chat panel. Matching the newest `'verified'` row (as Pass
  2 did) would show "verified this session" for nearly every active chat
  regardless of whether anyone ever passed a PIN/password gate, and
  because that polling is so frequent it would also almost always be the
  literal chronologically-newest row, silently masking a real `'locked'`
  event from being detected at all.

The Notice published this five-value result vocabulary:

| State | `result` value | Logged by |
|---|---|---|
| PIN configured | `pin_set` | Task 3 |
| Password configured | `password_set` | Task 5a |
| PIN/password entered correctly (this session) | `tier_verified` | Task 3 / Task 5a |
| Chat locked | `locked` | Task 4 |
| Chat unlocked | `unlocked` | Task 4 |

Action taken: `chatDeriveAuthBadge()` now (1) filters out every row where
`endpoint === 'verify_token'` before doing anything else, so Task 1's
polling noise can never mask an earlier real event, and (2) matches
`'verified_session'` against `result === 'tier_verified'` instead of
`'verified'`. `'unlocked'` needs no explicit branch — it simply isn't
`'locked'`, so it falls through correctly to the tier scan on its own.

**Vocabulary status: CEO-confirmed.** When this was raised as still
pending CEO sign-off, the CEO confirmed it directly and asked that it be
treated as settled rather than re-flagged — the five-value vocabulary
above is final. Tasks 3, 4, and 5a should log against these exact strings
when they ship.

**Pass 4 — self-initiated re-audit.** Re-read both project summaries and
the task brief in full, then re-read every line of this task's current
code looking specifically for anything the first three passes missed.
Two real issues found, both fixed, neither reported by anyone — caught
during this review:

- **Same-second ordering ambiguity.** The badge query sorted only by
  `created_at DESC`. If two audit rows for one chat land in the same
  second — plausible, e.g. a tier being set and then immediately verified
  within one request/response cycle — their relative order was undefined,
  which could flip which state wins nondeterministically (e.g. showing
  `pin_set` instead of `verified_session`, or the reverse, depending on
  how the DB happened to return ties). Fixed by adding `id DESC` as a
  secondary sort key: `chat_auth_audit_log` is insert-only and `id` is
  its auto-increment primary key, so it always reflects true
  chronological order even when two timestamps are identical.
- **Unverified strict type hint.** `chatFetchAuditBadges()` was declared
  as `function chatFetchAuditBadges(PDO $db, ...)`. Nothing else in this
  file ever passes `$db` into a function — the rest of the file only
  calls methods on it directly in the top-level script scope — so there
  was no actual confirmation that `chatDb()` (defined in
  `chat-config.php`, which was never requested or supplied for this task)
  returns a literal `PDO` instance rather than a wrapper class. A
  class-type hint is enforced by PHP regardless of `strict_types`, so if
  `chatDb()` returns anything else, this would have been a fatal
  `TypeError` on every call to the `list` or `lookup` action — a much
  worse failure than a wrong badge. Fixed by dropping the type hint
  entirely; the function only calls `->prepare()`/`->execute()`/
  `->fetchAll()` on it, the exact same calls the rest of this file
  already makes successfully on the same variable, so no functionality is
  lost by not asserting its exact class.
- Also fixed a stray blank line left over from the Pass 3 comment edit
  (cosmetic only, no functional effect).

One item intentionally left open rather than guessed at: `chat-config.php`
(or Task 1's real `chat-auth.php`) was never requested or supplied for
this task, so `chatDb()`'s actual return type is still inferred from
behavior elsewhere in this file rather than confirmed by reading its
definition. The Pass 4 fix above makes this task's code correct
regardless of what that type turns out to be, so it is no longer a risk —
worth a quick confirmation glance only if either file is shared for a
future task.

### `admin-dashboard.html`  →  live path `admin/index.html`

<!-- TASK-7 START -->
**Old code:**
```js
function chatMsgPreviewIcon(type) {
  if (type === 'image') return '<i class="fas fa-image" style="font-size:10px;color:#a89c8a;"></i>';
  if (type === 'audio') return '<i class="fas fa-microphone" style="font-size:10px;color:#a89c8a;"></i>';
  if (type === 'video') return '<i class="fas fa-video" style="font-size:10px;color:#a89c8a;"></i>';
  return '';
}

function escapeHtml(str) {
```
**New code:**
```js
function chatMsgPreviewIcon(type) {
  if (type === 'image') return '<i class="fas fa-image" style="font-size:10px;color:#a89c8a;"></i>';
  if (type === 'audio') return '<i class="fas fa-microphone" style="font-size:10px;color:#a89c8a;"></i>';
  if (type === 'video') return '<i class="fas fa-video" style="font-size:10px;color:#a89c8a;"></i>';
  return '';
}

// <!-- TASK-7 START -->
// Task 7 — Admin Auth-State Badge (frontend half). chat-admin-list.php now
// returns an `auth_badge` field on every thread (list results and lookup
// results alike) — one of: none, pin_set, password_set, locked,
// verified_session. This maps that state to a label/color/icon for the
// small pill rendered at both render sites (renderThreadList() and
// buildAdminBanner()), centralized here so they stay visually consistent
// and only need one place to update if the palette ever changes.
function chatAuthBadgeConfig(state) {
  const configs = {
    none:             { label: 'No Protection', color: '#a89c8a', icon: 'fa-lock-open' },
    pin_set:          { label: 'PIN Set',        color: '#d7bd88', icon: 'fa-key' },
    password_set:     { label: 'Password Set',   color: '#2f6fed', icon: 'fa-key' },
    locked:           { label: 'Locked',         color: '#c0392b', icon: 'fa-lock' },
    verified_session: { label: 'Verified',       color: '#10b981', icon: 'fa-check' },
  };
  return configs[state] || configs.none;
}
// <!-- TASK-7 END -->

function escapeHtml(str) {
```
<!-- TASK-7 END -->

<!-- TASK-7 START -->
**Old code:**
```js
    const body = document.createElement('div');
    body.innerHTML = `
      <div class="chat-thread-name">${escapeHtml(t.full_name)}</div>
      <div class="chat-thread-preview">${chatMsgPreviewIcon(t.last_msg_type)} ${escapeHtml(t.last_msg_body || '—')}</div>
      <div class="chat-thread-meta">
        <span>${escapeHtml(t.chat_id)}</span>
        ${t.is_stale ? '<span style="color:#ED7624;font-weight:700;">· Stale</span>' : ''}
        ${t.unread_for_admin && t.status !== 'done' ? '<span style="color:#ED7624;">· New</span>' : ''}
      </div>`;
```
**New code:**
```js
    // <!-- TASK-7 START -->
    // Auth-state badge pill for this thread's protection state — only
    // rendered when the field is present, so a response without it (e.g.
    // before this field existed) degrades gracefully instead of showing a
    // wrong default.
    const authCfg = t.auth_badge ? chatAuthBadgeConfig(t.auth_badge) : null;
    const authBadgeHtml = authCfg
      ? `<span title="${escapeHtml(authCfg.label)}" style="margin-left:6px;background:${authCfg.color};color:#fff;font-size:9px;font-weight:700;padding:1px 6px;border-radius:8px;"><i class="fas ${authCfg.icon}"></i> ${escapeHtml(authCfg.label)}</span>`
      : '';
    // <!-- TASK-7 END -->

    const body = document.createElement('div');
    body.innerHTML = `
      <div class="chat-thread-name">${escapeHtml(t.full_name)}</div>
      <div class="chat-thread-preview">${chatMsgPreviewIcon(t.last_msg_type)} ${escapeHtml(t.last_msg_body || '—')}</div>
      <div class="chat-thread-meta">
        <span>${escapeHtml(t.chat_id)}</span>
        ${t.is_stale ? '<span style="color:#ED7624;font-weight:700;">· Stale</span>' : ''}
        ${t.unread_for_admin && t.status !== 'done' ? '<span style="color:#ED7624;">· New</span>' : ''}
        ${authBadgeHtml}
      </div>`;
```
<!-- TASK-7 END -->

<!-- TASK-7 START -->
**Old code:**
```js
  header.style.cssText = 'background:#f4f1ea;padding:12px 16px;border-bottom:1px solid rgba(16,33,75,0.08);flex-shrink:0;';
  const statusBadge = { waiting: '#d7bd88', active: '#10b981', done: '#a89c8a' }[thread.status] || '#a89c8a';
  header.innerHTML = `
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
      <div>
        <strong style="color:#10214b;font-size:14px;">${escapeHtml(thread.full_name)}</strong>
        <span style="font-size:11px;color:#7a6d5f;margin-left:8px;">${escapeHtml(thread.email)}</span>
        <span style="margin-left:8px;background:${statusBadge};color:#fff;font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px;">${thread.status}</span>
      </div>
```
**New code:**
```js
  header.style.cssText = 'background:#f4f1ea;padding:12px 16px;border-bottom:1px solid rgba(16,33,75,0.08);flex-shrink:0;';
  const statusBadge = { waiting: '#d7bd88', active: '#10b981', done: '#a89c8a' }[thread.status] || '#a89c8a';
  // <!-- TASK-7 START -->
  // Auth-state badge for the thread detail header. Not present on the
  // fakeBanner's placeholder thread object used before real data has
  // loaded (see renderAdminPollResult()), so this only appears once
  // loadThreadMeta() replaces the banner with the real thread — same
  // graceful-degradation approach as the list card badge above.
  const authCfg = thread.auth_badge ? chatAuthBadgeConfig(thread.auth_badge) : null;
  const authBadgeHtml = authCfg
    ? `<span style="margin-left:6px;background:${authCfg.color};color:#fff;font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px;"><i class="fas ${authCfg.icon}"></i> ${escapeHtml(authCfg.label)}</span>`
    : '';
  // <!-- TASK-7 END -->
  header.innerHTML = `
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
      <div>
        <strong style="color:#10214b;font-size:14px;">${escapeHtml(thread.full_name)}</strong>
        <span style="font-size:11px;color:#7a6d5f;margin-left:8px;">${escapeHtml(thread.email)}</span>
        <span style="margin-left:8px;background:${statusBadge};color:#fff;font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px;">${thread.status}</span>
        ${authBadgeHtml}
      </div>
```
<!-- TASK-7 END -->

Both render sites check `thread.auth_badge`/`t.auth_badge` for truthiness
before rendering rather than defaulting to the `'none'` config, so a
thread object that predates this field (e.g. the `fakeBanner` placeholder
built before `loadThreadMeta()` resolves) shows no badge instead of a
misleading "No Protection" pill. `admin-dashboard.html` needed no changes
across Passes 2–4 — it only consumes whatever string `auth_badge`
returns, and its five badge-config keys already match every possible
output state regardless of which audit vocabulary produced them.

## New files
No new files. Both files touched by this task (`chat-admin-list.php`,
`admin-dashboard.html`) already existed on the live site; both changes
are documented in full above under Code Changes.

**New folders required: NONE.** No new path was introduced anywhere, so
nothing here falls outside the folder structures already defined in
`adoption-website-project-summary.md` and `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`.
The `index.html` page-naming rule does not come into play — this task
creates no pages.

**Zip contents:** `Admin-Project-1-Task-7.zip` contains this completion
file only (`Task-7-Completion.md` at the zip root), because there are no
new files to add. Existing files that were changed are never placed in
the zip; they live in Code Changes above.

## Bugs Found Outside Task Scope
None found, outside what's already documented and fixed within this
task's own two files above.

### Pending items, dependencies and doubts (integrator must read)

1. **HARD DEPENDENCY — Task 1 must be integrated first.**
   `chatFetchAuditBadges()` queries the table `chat_auth_audit_log`,
   which Task 1 creates. If Task 7 goes live before Task 1's table
   exists, the query throws and the admin `list` and `lookup` actions
   will fail outright (the whole admin chat list, not just the badge).
   Merge order must be Task 1, then Task 7.
2. **Badges will be incomplete until Tasks 3, 4 and 5a ship.** The
   confirmed result vocabulary is `pin_set` (Task 3), `password_set`
   (Task 5a), `tier_verified` (Tasks 3/5a), `locked` and `unlocked`
   (Task 4). Until those tasks log those exact strings, every chat will
   show "No Protection", and "Verified" and "Locked" can never appear.
   This is expected, not a bug in Task 7. If any of those tasks ships
   with a different result string, only the string literals inside
   `chatDeriveAuthBadge()` need updating.
3. **Unconfirmed index on the audit table.** The badge query filters by
   `principal_id IN (...)`. Task 1's table definition was not supplied to
   this task, so whether `principal_id` is indexed is unknown. If it is
   not, the query will slow down as the audit log grows (it logs every
   ~2.5s session poll). Worth confirming against Task 1's schema; adding
   an index on `principal_id` would be Task 1's file, not this task's.
4. **Unconfirmed `chatDb()` return type.** `chat-config.php` was never
   supplied. The code was written not to depend on the exact class
   (see Pass 4), so this is no longer a risk, only an unverified detail.
5. **Assumption: `principal_id` equals `chat_threads.chat_id`.** Taken
   from the Shared Contract's wording ("one anonymous chat
   conversation"). If Task 1 stores a different identifier as
   `principal_id`, every badge will show "No Protection". Worth one
   glance at Task 1's `log_audit()` callers at integration.
6. **Minor edge case, left as is.** The thread-ID list is built with
   `array_filter()`, which would also drop a `chat_id` of the string
   `"0"`. Chat IDs in this project are non-numeric generated strings, so
   this should never occur.
7. **Scope note.** The original brief listed one file. Touching
   `admin-dashboard.html` was done on explicit CEO direction so the badge
   could be displayed. If the integrator applies only the brief's
   original scope, the data field is returned but no badge is visible.

## Self-Check Confirmation
- Only `chat-admin-list.php` and `admin-dashboard.html` were touched;
  `admin-dashboard.html` was added to scope mid-task on explicit CEO
  direction, nothing else was edited.
- Both files' Files Changed row uses the live path from the guide's
  mapping table (`admin-dashboard.html` → `admin/index.html` by explicit
  row; `chat-admin-list.php` → `admin/chat-admin-list.php` via the
  "any admin PHP/JS, chat-*.php" rule) and both are marked "Changed," not
  "New."
- Every "Old code" block above was copied character for character from
  the original file as given to this task, before any of this task's own
  edits — verified by a scripted exact-text search of the originally supplied
  files: all six Old code blocks were found in the original exactly
  once each, so the integrator's exact-text match can't land in the
  wrong place.
- Every code block is wrapped in `<!-- TASK-7 START -->` / `<!-- TASK-7
  END -->` anchors, both at the documentation level (this file) and
  inside the actual new code itself, and each added function/block
  carries an explanatory comment.
- Zip: `Admin-Project-1-Task-7.zip`, named after the project file
  (`Admin-Project-1-Worker-Task-Assignments.md`) plus the task number.
  It contains this completion file only, at the zip root. No new files
  exist, so no other file is in it, and no changed existing file is in it.
  Checked with `unzip -l`: nothing in the zip is a secret, `config.php`,
  `pet.json`, `.env`, or anything under `applications/` or `uploads/`.
- Every pending item, dependency, doubt and scope note known to this
  task is written up under "Pending items, dependencies and doubts"
  above.
- No logging calls were added (read-only, per the task brief); no emojis
  anywhere in code or comments.
- Testing performed: no live PHP interpreter or database was available in
  this working environment (`php -l` was attempted and is not installed
  here), so testing was a manual, line-by-line trace of
  `chatDeriveAuthBadge()`'s precedence logic against multiple constructed
  event sequences (tier set → locked → unlocked; tier set → verified;
  verified-polling noise interleaved with a real lock event; same-second
  tie between two meaningful rows), plus `grep`-verified anchor-comment
  pairing after every edit and a full re-read of both final files end to
  end during the Pass 4 audit.

Task 7 is complete, no open items.
