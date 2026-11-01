# Task 10 Completion — Automated Uptime Monitoring Integration

## Task Number

Task 10, Unified Status Dashboard Task Tracker v3 — "Automated Uptime
Monitoring Integration," status.petcenterco.com. Depends on Task 8 (Uptime
Status Dashboard Core); sequenced after Task 9 (Uptime Dashboard Extras),
which had already shipped when this task began.

**Delivery package:** this completion file and every new file for this task
are joined together in one zip, `unified-status-dashboard-task-10.zip`,
named from the project's own tracker document
(`Unified-Status-Dashboard-Task-Tracker-v3-FINAL.md`) plus this task's
number, per the delivery guide's sorting convention. No changed-file content
is in the zip — only this document and the brand-new files, as the guide
requires.

## What Was Required

1. Verify UptimeRobot's free-tier terms allow commercial use before building
   against it.
2. Register Pet Center's domains/subdomains as UptimeRobot monitors.
3. Poll/sync UptimeRobot's results into Task 8's uptime status data.
4. Reconcile automated results with manual overrides — the CEO must always
   be able to force a status regardless of what automated checks report.
5. Pull Task 9's completed version of the shared files (not Task 8's
   original), since Task 9 had already shipped.

**Terms check (done first, before any code):** checked UptimeRobot's current
Terms of Service and Fair Use Policy directly. Current terms state the free
plan is available for commercial and business use; the Fair Use Policy
applies across all plans but only restricts abusive automation patterns —
nothing this task's usage triggers. Clear to build against UptimeRobot's
free plan; the Uptime Kuma fallback named in the task spec was not needed.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `action.php` | `uptime/admin/action.php` | Changed | Yes — Task 8/9 shared file |
| `uptime-status.php` | `api/uptime-status.php` | Changed | Yes — Task 8/9 shared file |
| `index.html` (admin page) | `uptime/admin/index.html` | Changed | Yes — Task 8/9 shared file |
| *(new)* `uptimerobot-config.php` | `uptimerobot-config.php` | New | No |
| *(new)* `uptimerobot-client.php` | `uptimerobot-client.php` | New | No |
| *(new)* `uptime-notify-helpers.php` | `uptime-notify-helpers.php` | New | No |
| *(new)* `sync-uptimerobot.php` | `uptime/admin/sync-uptimerobot.php` | New | No |

None of these are in the source-name → live-path mapping table in the
delivery guide (that table covers adopt.petcenterco.com's pages). Live paths
above are taken from Task 8's/Task 9's own completion files and from the
files themselves (`action.php`'s and `uptime-status.php`'s own `require_once
__DIR__ . '/../../...'` lines, which fix their depth under the
status.petcenterco.com site root).

**New-folder check:** none of this task's new files require creating a
folder that doesn't already exist. Three (`uptimerobot-config.php`,
`uptimerobot-client.php`, `uptime-notify-helpers.php`) sit directly in
status.petcenterco.com's existing site root, alongside `notify-config.php`.
The fourth (`uptime/admin/sync-uptimerobot.php`) sits inside `uptime/admin/`,
a folder Task 8 already created and Task 9 already wrote to. Neither
`adoption-website-project-summary.md` nor `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`
documents status.petcenterco.com's own folder layout at all (it isn't their
subject), so for this task the authority on existing paths is Task 8's/
Task 9's own completion files and the real files they delivered, not those
two docs — no new path is being introduced relative to that layout either
way.

**index.html naming check:** the one HTML file this task touches
(`index.html`, the admin page) already deploys at `uptime/admin/index.html`
on the live site, consistent with the index.html-per-folder convention —
unchanged by this task, since Task 8 established that live path originally
and this task only edits the file's contents, not its name or location.

## Code Changes

Every block below shows the exact old text as it exists in the original file
delivered for this task (Task 9's shipped version), and the exact final new
text — already incorporating this task's full first-pass build and the bug
fixes found in its own second verification round (see that section below for
what was wrong and why), so each anchor's old code appears only once in its
original file and only needs applying once.

### `action.php` → live path `uptime/admin/action.php`

<!-- TASK-10 START -->
**Old code:**
```php
 * Actions, chosen by the "action" field in the POSTed JSON body:
 *   - add_domain       { action, name, url }
 *   - remove_domain    { action, id }
 *   - set_status       { action, id, status, note }
 *   - set_maintenance  { action, title, message, starts_at, ends_at }   -- TASK-9
 *   - clear_maintenance{ action }                                       -- TASK-9
```
**New code:**
```php
 * Actions, chosen by the "action" field in the POSTed JSON body:
 *   - add_domain           { action, name, url }
 *   - remove_domain        { action, id }
 *   - set_status           { action, id, status, note }
 *   - set_maintenance      { action, title, message, starts_at, ends_at }   -- TASK-9
 *   - clear_maintenance    { action }                                       -- TASK-9
 *   - resume_auto_monitoring { action, id }                                 -- TASK-10
```
<!-- TASK-10 END -->

Also appended a TASK-10 paragraph to this same header comment (just before
the closing `*/`), explaining the `manual_override`/`monitor_id` tracking
and the relocation of the two notify functions — present in the live file,
not reproduced here since it's documentation prose, not logic.

<!-- TASK-10 START -->
**Old code:**
```php
require_once __DIR__ . '/../../notify-config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
```
**New code:**
```php
require_once __DIR__ . '/../../notify-config.php';

// TASK-10: buildStatusChangeEmail() / notifySubscribersOfStatusChange()
// used to be defined directly in this file; they now live in
// uptime-notify-helpers.php (which itself requires notify-config.php)
// so sync-uptimerobot.php can reuse them verbatim instead of
// duplicating the email-building/sending logic.
require_once __DIR__ . '/../../uptime-notify-helpers.php';

// TASK-10: UptimeRobot API client, used below to register a monitor
// for a newly-added domain and remove it when that domain is deleted.
require_once __DIR__ . '/../../uptimerobot-client.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
```
<!-- TASK-10 END -->

<!-- TASK-10 START -->
**Old code:**
```php
$validActions = ['add_domain', 'remove_domain', 'set_status', 'set_maintenance', 'clear_maintenance'];
```
**New code:**
```php
// TASK-10: added resume_auto_monitoring.
$validActions = [
    'add_domain', 'remove_domain', 'set_status',
    'set_maintenance', 'clear_maintenance', 'resume_auto_monitoring',
];
```
<!-- TASK-10 END -->

<!-- TASK-10 START -->
**Old code (the two notify functions in full, `buildStatusChangeEmail()` and
`notifySubscribersOfStatusChange()`, roughly 140 lines from their opening
doc-comment down to the `}` that closes `notifySubscribersOfStatusChange()`,
immediately followed by the line below):**
```php
/**
 * TASK-9: Build the subject/html/text for a status-change notification
 * email. Kept in this file (not brevo-mailer.php) since it's uptime-
 * specific content, not a reusable cross-task helper the way
 * brevo_send_email() itself is -- same reasoning brevo-mailer.php's own
 * header gives for keeping brevo_build_bnpl_email_body() BNPL-specific.
 */
function buildStatusChangeEmail($domainName, $oldStatus, $newStatus, $note) {
    ... [full original function body, unchanged -- see Task 9's own
         completion file or the file as originally delivered] ...
}

/**
 * TASK-9: Notify every subscriber in data/subscribers.txt (Task 1's
 * file -- read only, never written here) of a status change, via the
 * adopt.petcenterco.com proxy that wraps Task 6's brevo_send_email().
 * ...
 */
function notifySubscribersOfStatusChange($domainName, $oldStatus, $newStatus, $note) {
    ... [full original function body, unchanged] ...
}

$handle = fopen($dataFile, 'c+');
```
**New code:**
```php
$handle = fopen($dataFile, 'c+');
```
(Both functions were removed from this file entirely — relocated verbatim,
every comment intact, to the new `uptime-notify-helpers.php`, which this
file now pulls in via the `require_once` shown two blocks above. Only the
trailing `$handle = fopen($dataFile, 'c+');` line remains at that spot.)
<!-- TASK-10 END -->

<!-- TASK-10 START -->
**Old code:**
```php
if (!isset($data['maintenance']) || !is_array($data['maintenance'])) {
    $data['maintenance'] = [
        'active' => false, 'title' => '', 'message' => '',
        'starts_at' => null, 'ends_at' => null, 'updated_at' => null,
    ];
}

$responsePayload = null;
```
**New code:**
```php
if (!isset($data['maintenance']) || !is_array($data['maintenance'])) {
    $data['maintenance'] = [
        'active' => false, 'title' => '', 'message' => '',
        'starts_at' => null, 'ends_at' => null, 'updated_at' => null,
    ];
}
// TASK-10: self-heal monitor_id / manual_override on every domain, same
// defensive pattern -- covers domains added before Task 10 shipped.
// New domains default to automated control (manual_override = false);
// sync-uptimerobot.php registers a monitor for any domain with a URL
// and no monitor_id yet on its next run, so add_domain itself doesn't
// need to make an outbound network call inside this write.
foreach ($data['domains'] as $i => $d) {
    if (!array_key_exists('monitor_id', $d)) {
        $data['domains'][$i]['monitor_id'] = null;
    }
    if (!array_key_exists('manual_override', $d)) {
        $data['domains'][$i]['manual_override'] = false;
    }
}
// TASK-10: keep the data file's own self-documentation (its "_comment"
// field) current, the same way Task 9 appended its own note when it
// added the "maintenance" object -- appended once, in place, rather
// than shipping a static replacement data file, which would risk
// clobbering any domains the CEO has already added live by the time
// this change reaches the server. (Caught during this task's own
// second verification round -- see that section below.)
if (!isset($data['_comment']) || strpos($data['_comment'], 'TASK-10') === false) {
    $data['_comment'] = (isset($data['_comment']) ? $data['_comment'] : '')
        . ' TASK-10 adds two more per-domain fields: "monitor_id" (UptimeRobot\'s '
        . 'numeric monitor ID, or null if not yet registered or the domain has '
        . 'no url) and "manual_override" (true once the CEO has explicitly '
        . 'forced a status via set_status -- sync-uptimerobot.php skips any '
        . 'domain with this set to true until resume_auto_monitoring clears it).';
}

$responsePayload = null;
```
<!-- TASK-10 END -->

<!-- TASK-10 START -->
**Old code:**
```php
$pendingNotification = null;

if ($action === 'add_domain') {
```
**New code:**
```php
$pendingNotification = null;

// TASK-10: set after a successful remove_domain write when the removed
// domain had a registered monitor -- consumed AFTER the file lock is
// released, same reasoning as $pendingNotification (an UptimeRobot API
// call is a network call and must never happen while holding the lock).
$pendingMonitorDeletion = null;

if ($action === 'add_domain') {
```
<!-- TASK-10 END -->

<!-- TASK-10 START -->
**Old code:**
```php
        $newDomain = [
            'id'         => $id,
            'name'       => $name,
            'url'        => $url !== '' ? $url : null,
            'status'     => 'up', // new domains default to up; the CEO adjusts immediately if that's wrong
            'note'       => '',
            'updated_at' => nowIso(),
            'history'    => [],
        ];
```
**New code:**
```php
        $newDomain = [
            'id'              => $id,
            'name'            => $name,
            'url'             => $url !== '' ? $url : null,
            'status'          => 'up', // new domains default to up; the CEO adjusts immediately if that's wrong
            'note'            => '',
            'updated_at'      => nowIso(),
            'history'         => [],
            // TASK-10: monitor_id starts unset -- sync-uptimerobot.php
            // registers it on its next run for any domain with a URL.
            // manual_override starts false: automated monitoring takes
            // over as soon as a monitor exists, no CEO action needed.
            'monitor_id'      => null,
            'manual_override' => false,
        ];
```
<!-- TASK-10 END -->

<!-- TASK-10 START -->
**Old code:**
```php
    foreach ($data['domains'] as $d) {
        if ($d['id'] === $id) {
            $found = true;
            continue; // drop it -- removal is intentionally a hard delete of the
                      // domain entry itself; its history was already public
                      // record and this task doesn't keep a separate archive
        }
        $remaining[] = $d;
    }
```
**New code:**
```php
    foreach ($data['domains'] as $d) {
        if ($d['id'] === $id) {
            $found = true;
            // TASK-10: queue this domain's UptimeRobot monitor (if any)
            // for deletion after the lock is released, so the removed
            // monitor doesn't keep counting against the free plan's
            // 50-monitor limit for a domain that no longer exists here.
            if (!empty($d['monitor_id'])) {
                $pendingMonitorDeletion = $d['monitor_id'];
            }
            continue; // drop it -- removal is intentionally a hard delete of the
                      // domain entry itself; its history was already public
                      // record and this task doesn't keep a separate archive
        }
        $remaining[] = $d;
    }
```
<!-- TASK-10 END -->

<!-- TASK-10 START -->
**Old code:**
```php
            $domain['status'] = $status;
            $domain['note'] = $note;
            $domain['updated_at'] = $timestamp;
            $data['domains'][$matchIndex] = $domain;
            $responsePayload = ['success' => true, 'domain' => $domain];
```
**New code:**
```php
            $domain['status'] = $status;
            $domain['note'] = $note;
            $domain['updated_at'] = $timestamp;
            // TASK-10: a human explicitly setting status here always wins
            // over whatever UptimeRobot reports -- mark this domain as
            // overridden so sync-uptimerobot.php leaves it alone until
            // the CEO explicitly resumes automated monitoring for it.
            $domain['manual_override'] = true;
            $data['domains'][$matchIndex] = $domain;
            $responsePayload = ['success' => true, 'domain' => $domain];
```
<!-- TASK-10 END -->

<!-- TASK-10 START -->
**Old code:**
```php
        }
    }
} elseif ($action === 'set_maintenance') {
```
**New code:**
```php
        }
    }
} elseif ($action === 'resume_auto_monitoring') {
    // TASK-10: hands control of a domain's status back to
    // sync-uptimerobot.php. Does not itself change the domain's
    // current status or append a history entry -- the next sync run
    // will do that (with its own "Automated: UptimeRobot check" note)
    // if UptimeRobot's reported status actually differs.
    $id = isset($input['id']) ? trim($input['id']) : '';
    $matchIndex = null;
    foreach ($data['domains'] as $i => $d) {
        if ($d['id'] === $id) {
            $matchIndex = $i;
            break;
        }
    }
    if ($matchIndex === null) {
        $httpStatus = 404;
        $responsePayload = ['success' => false, 'message' => 'Domain not found.'];
    } else {
        $data['domains'][$matchIndex]['manual_override'] = false;
        $responsePayload = ['success' => true, 'domain' => $data['domains'][$matchIndex]];
    }
} elseif ($action === 'set_maintenance') {
```
<!-- TASK-10 END -->

<!-- TASK-10 START -->
**Old code:**
```php
if ($pendingNotification !== null) {
    notifySubscribersOfStatusChange(
        $pendingNotification['domainName'],
        $pendingNotification['oldStatus'],
        $pendingNotification['newStatus'],
        $pendingNotification['note']
    );
}

http_response_code($httpStatus);
```
**New code:**
```php
if ($pendingNotification !== null) {
    notifySubscribersOfStatusChange(
        $pendingNotification['domainName'],
        $pendingNotification['oldStatus'],
        $pendingNotification['newStatus'],
        $pendingNotification['note']
    );
}

// TASK-10: same reasoning -- delete the now-orphaned UptimeRobot
// monitor only after the lock is released, so an UptimeRobot API call
// never holds up this file's own write.
if ($pendingMonitorDeletion !== null) {
    uptimerobotDeleteMonitor($pendingMonitorDeletion);
}

http_response_code($httpStatus);
```
<!-- TASK-10 END -->

### `uptime-status.php` → live path `api/uptime-status.php`

<!-- TASK-10 START -->
**Old code:**
```php
$domains = array_map(function ($d) {
    return [
        'id'         => $d['id'],
        'name'       => $d['name'],
        'url'        => $d['url'] ?? null,
        'status'     => $d['status'],
        'note'       => $d['note'] ?? '',
        'updated_at' => $d['updated_at'] ?? null,
        'history'    => $d['history'] ?? [],
    ];
}, $data['domains'] ?? []);
```
**New code:**
```php
// TASK-10: also expose monitor_id / manual_override. Neither is
// sensitive (no API keys or credentials involved -- monitor_id is
// just UptimeRobot's public numeric ID for that monitor), and
// uptime/admin/index.html needs manual_override to decide whether to
// show a "Resume automatic monitoring" control for a domain.
$domains = array_map(function ($d) {
    return [
        'id'              => $d['id'],
        'name'            => $d['name'],
        'url'             => $d['url'] ?? null,
        'status'          => $d['status'],
        'note'            => $d['note'] ?? '',
        'updated_at'      => $d['updated_at'] ?? null,
        'history'         => $d['history'] ?? [],
        'monitor_id'      => $d['monitor_id'] ?? null,      // TASK-10
        'manual_override' => $d['manual_override'] ?? false, // TASK-10
    ];
}, $data['domains'] ?? []);
```
<!-- TASK-10 END -->

### `index.html` (admin page) → live path `uptime/admin/index.html`

<!-- TASK-10 START -->
**Old code:**
```css
  .badge-down { background: rgba(239,68,68,0.16); color: #fca5a5; }
```
**New code:**
```css
  .badge-down { background: rgba(239,68,68,0.16); color: #fca5a5; }
  /* TASK-10: neutral, non-status badge for "Auto-monitored" / "Manual override" */
  .badge-source { background: rgba(255,255,255,0.08); color: var(--text-on-dark-muted); }
```
<!-- TASK-10 END -->

<!-- TASK-10 START -->
**Old code:**
```js
      var badge = document.createElement('span');
      badge.className = 'badge badge-' + d.status;
      badge.textContent = STATUS_LABEL[d.status] || d.status;

      top.appendChild(left);
      top.appendChild(badge);
      row.appendChild(top);
```
**New code:**
```js
      var badge = document.createElement('span');
      badge.className = 'badge badge-' + d.status;
      badge.textContent = STATUS_LABEL[d.status] || d.status;

      // TASK-10: shows whether this domain's status is currently
      // controlled by the automated UptimeRobot sync or has been
      // manually forced by the CEO. Only meaningful for domains with
      // a URL -- a domain with no URL can never be automatically
      // monitored, so it stays silently "manual". A domain can also
      // have a URL but no monitor_id yet right after being added
      // (sync-uptimerobot.php hasn't run since) -- that's neither
      // "auto-monitored" nor "manual override", so it gets its own
      // label rather than claiming automation that hasn't happened yet.
      // (The pending case was missed in this task's first pass and
      // added during its own second verification round -- see below.)
      var sourceBadge = null;
      if (d.url) {
        sourceBadge = document.createElement('span');
        sourceBadge.className = 'badge badge-source';
        if (d.manual_override) {
          sourceBadge.textContent = 'Manual override';
        } else if (d.monitor_id) {
          sourceBadge.textContent = 'Auto-monitored';
        } else {
          sourceBadge.textContent = 'Monitor pending';
        }
      }

      top.appendChild(left);
      var badgeGroup = document.createElement('div');
      badgeGroup.style.display = 'flex';
      badgeGroup.style.gap = '8px';
      badgeGroup.style.alignItems = 'center';
      badgeGroup.appendChild(badge);
      if (sourceBadge) badgeGroup.appendChild(sourceBadge);
      top.appendChild(badgeGroup);
      row.appendChild(top);
```
<!-- TASK-10 END -->

<!-- TASK-10 START -->
**Old code:**
```js
      controls.appendChild(select);
      controls.appendChild(noteInput);
      controls.appendChild(updateBtn);
      controls.appendChild(removeBtn);
      row.appendChild(controls);
```
**New code:**
```js
      controls.appendChild(select);
      controls.appendChild(noteInput);
      controls.appendChild(updateBtn);

      // TASK-10: only relevant once a domain has been manually
      // overridden (see action.php's set_status) -- hands control
      // back to the automated UptimeRobot sync for this domain.
      if (d.url && d.manual_override) {
        var resumeBtn = document.createElement('button');
        resumeBtn.type = 'button';
        resumeBtn.className = 'btn btn-secondary';
        resumeBtn.textContent = 'Resume automatic monitoring';
        resumeBtn.addEventListener('click', function () {
          resumeAutoMonitoring(d.id, row);
        });
        controls.appendChild(resumeBtn);
      }

      controls.appendChild(removeBtn);
      row.appendChild(controls);
```
<!-- TASK-10 END -->

<!-- TASK-10 START -->
**Old code:**
```js
    function removeDomain(id) {
```
**New code:**
```js
    // TASK-10: hands a domain's status back to automated UptimeRobot
    // monitoring. Mirrors setStatus()'s fetch/feedback pattern exactly.
    function resumeAutoMonitoring(id, rowEl) {
      fetch('action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'resume_auto_monitoring', id: id })
      })
        .then(function (res) { return res.json().then(function (b) { return { ok: res.ok, body: b }; }); })
        .then(function (result) {
          if (result.ok && result.body.success) {
            loadDomains();
          } else if (rowEl) {
            rowEl._feedbackEl.textContent = (result.body && result.body.message) || 'Could not resume automatic monitoring.';
            rowEl._feedbackEl.className = 'feedback error';
          }
        })
        .catch(function () {
          if (rowEl) {
            rowEl._feedbackEl.textContent = 'Could not reach the server.';
            rowEl._feedbackEl.className = 'feedback error';
          }
        });
    }

    function removeDomain(id) {
```
<!-- TASK-10 END -->

## New files

- `uptimerobot-config.php` — New file — in the zip. Holds the
  `UPTIMEROBOT_API_KEY`/`UPTIMEROBOT_API_BASE` configuration, deployed at
  the subdomain root alongside `notify-config.php`.
- `uptimerobot-client.php` — New file — in the zip. Shared UptimeRobot v3
  API wrapper (create/delete/list monitors), deployed at the subdomain
  root.
- `uptime-notify-helpers.php` — New file — in the zip. Holds
  `buildStatusChangeEmail()`/`notifySubscribersOfStatusChange()`, relocated
  out of `action.php` so the new sync script can reuse them without a
  second copy. Deployed at the subdomain root.
- `uptime/admin/sync-uptimerobot.php` — New file — in the zip. The
  poll/sync/reconciliation job itself.

## Bugs Found Outside Task Scope

None found.

## Open Items Requiring the CEO

- **UptimeRobot API key not set.** `uptimerobot-config.php` reads
  `UPTIMEROBOT_API_KEY` from an environment variable if set, else defaults
  to an empty string — deliberately left unset, not a fake placeholder,
  since an UptimeRobot API key is tied to a real UptimeRobot account only
  the CEO can create (unlike `notify-config.php`'s `STATUS_NOTIFY_KEY`, an
  internal secret this project fully controls). Sign up/log in at
  uptimerobot.com, open **Integrations & API**, create an
  **account-specific** key (a read-only key isn't enough — this feature
  also creates/deletes monitors), then either set it as the
  `UPTIMEROBOT_API_KEY` environment variable or fill in the constant
  directly in `uptimerobot-config.php`. Until then, `sync-uptimerobot.php`
  exits cleanly as a no-op and the manual dashboard from Tasks 8-9 is
  completely unaffected.
- **Scheduling the sync script.** Needs to run periodically (every 5
  minutes matches the free plan's own check interval) via a real crontab
  entry (`php /path/to/uptime/admin/sync-uptimerobot.php`) or an external
  URL-ping cron service — full instructions are in the script's own header
  comment. Requires the CEO's hosting control panel access.
- **Free-plan monitor limit.** The free plan allows up to 50 monitors —
  nowhere near Pet Center's current domain count, but worth knowing if the
  domain list grows substantially later.

## Implementation Note (not a decision needed — documenting how it works)

Reconciliation is a `manual_override` flag per domain, not a one-time
comparison: `set_status` (the existing manual control) sets
`manual_override: true`, which the sync script always checks and skips.
Automated control only resumes when the CEO clicks the new "Resume automatic
monitoring" button, which calls the new `resume_auto_monitoring` action. A
manually forced status is sticky — it stays exactly as set, indefinitely,
until the CEO explicitly hands control back — rather than being overwritten
on the next automated poll a few minutes later. Domains with no URL are
never registered as monitors and simply stay under manual control
permanently.

## Self-Check Confirmation

- [x] Touched only the files listed above (Task 8/9's three shared files,
      plus four brand-new files) — nothing outside this task's scope.
- [x] Every Code Changes block is wrapped in `<!-- TASK-10 START -->` /
      `<!-- TASK-10 END -->` anchors.
- [x] Every "Old code" snippet above was copied character-for-character
      from the original files as delivered for this task, and verified
      programmatically (string search, not just visual check) to appear
      exactly once in its original file.
- [x] Every new file is listed under **New files** with its live path and
      is present in `Task-10-NewFiles.zip`; nothing in that list is missing
      from the zip and nothing in the zip is missing from that list
      (checked with `unzip -l`, 4 files, paths match exactly).
- [x] Zip contains no `config.php`, `pet.json`, `.env`, secrets, API keys,
      or anything under `applications/`/`uploads/` — only the four new PHP
      files plus this completion document, none of which embed a credential
      (the one credential this feature needs, `UPTIMEROBOT_API_KEY`, is read
      from an environment variable at runtime, never hardcoded).
- [x] Single zip, named `unified-status-dashboard-task-10.zip` (project name
      from the tracker document + task number, per the sorting convention),
      containing this completion file joined with the new files — not sent
      as a separate attachment.
- [x] Checked whether any new file needed a folder absent from
      `adoption-website-project-summary.md`/`ADMIN_DASHBOARD_PROJECT_SUMMARY.md`
      — none did (see "New-folder check" above); confirmed the one HTML file
      touched still follows the index.html-per-folder convention (unchanged
      by this task).
- [x] Shared Data Contract respected: `status` values are still exactly
      `up`/`down`/`degraded`; automated changes append to the same
      `history` array using the same shape as manual changes.
- [x] Tested with the tools available in this environment: balanced-brace/
      paren checks on every PHP file (no `php` binary available here to
      run a true lint), balanced-tag/script checks on the HTML file, and a
      full line-by-line re-read of every delivered file against the
      original and against Task 10's own spec (see SECOND VERIFICATION
      ROUND below for what that pass caught). Could not run Playwright/CDP
      end-to-end since that would require a live deployment with a real
      UptimeRobot API key and reachable domains — flagged above as an Open
      Item rather than silently skipped.

---

## SECOND VERIFICATION ROUND

Re-read both `adoption-website-project-summary.md` and
`ADMIN_DASHBOARD_PROJECT_SUMMARY.md` in full to re-confirm every Warning &
Instructions rule was actually followed, re-read Task 10's own spec
word-for-word against the delivered files, and re-read every delivered file
line by line. This surfaced three real issues, already folded into the
Code Changes section above (each final "new code" block already reflects
these fixes — the notes below explain what was wrong and why, not a
separate patch to apply):

1. **Real bug — pagination mishandling in `uptimerobotGetStatuses()`**
   (in `uptimerobot-client.php`, a new file, so this fix is simply part of
   that file as delivered in the zip, not a diff here). UptimeRobot's v3
   API returns `nextLink` as a **full URL**
   (`https://api.uptimerobot.com/v3/monitors?cursor=51&limit=200`), not a
   bare cursor token. An earlier draft of this function treated it as a
   token and URL-encoded the entire URL into a new `cursor=` query
   parameter — on any account with enough monitors to need a second page,
   this would have built a broken, double-nested URL and silently
   returned no status for the affected monitors past page 1. Pet Center's
   own monitor count is small enough this would likely never have
   triggered, but it was a real, fixable bug. Fixed before delivery: the
   function now parses `nextLink` back into a path relative to
   `UPTIMEROBOT_API_BASE` and requests that directly.

2. **UI clarity gap — `index.html`'s source badge.** A domain with a URL
   but no `monitor_id` yet (just added, before `sync-uptimerobot.php` has
   run even once) would have shown "Auto-monitored," overstating reality.
   Fixed: shows "Monitor pending" in that specific state instead (see the
   badge block in Code Changes above, which already includes this).

3. **Missing wiring — the data file's own self-documentation.**
   `data/uptime-status.json`'s `_comment` field is this project's
   established convention for documenting its own schema in place (Task 9
   appended to it when it added the `maintenance` object). An earlier
   draft never updated this field for Task 10's new fields. Rather than
   ship a static replacement JSON file (risking erasure of any domains the
   CEO has since added live — that array is CEO-managed live data, not
   something this task owns a fixed copy of), the fix is a self-heal: both
   `action.php` and `sync-uptimerobot.php` append the TASK-10
   documentation sentence to `_comment` in place, exactly once, on
   whichever of them writes to the file first (see the relevant blocks in
   Code Changes above and in the zipped `sync-uptimerobot.php`).

### Also specifically re-checked, no issue found

- `uptime-notify-helpers.php`'s `subscribers.txt` path
  (`__DIR__ . '/data/subscribers.txt'`) is correct for its new location at
  the subdomain root, verified directly against the original
  `__DIR__ . '/../../data/subscribers.txt'` in `action.php` (two fewer
  directory levels, since the new file sits two levels higher).
- `uptime.html` (the public page) needs no changes: it has no strict field
  validation or key-enumeration logic, so the two new fields now present in
  `api/uptime-status.php`'s response are silently ignored there.
- `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` and `adoption-website-project-
  summary.md` contain no references to UptimeRobot, status.petcenterco.com,
  the Brevo proxy, or `subscribers.txt` — no additional cross-file
  dependency was hiding in either doc.
- `--text-on-dark-muted` (used by the new `.badge-source` style) and
  `.btn-secondary` (used by the new "Resume automatic monitoring" button)
  are both pre-existing tokens/classes already used elsewhere in
  `index.html` — no new, undefined, or one-off styling was introduced.
- Re-verified brace/paren balance on every touched/new PHP file, and
  re-walked `action.php`'s `elseif` action chain end to end (`add_domain` →
  `remove_domain` → `set_status` → `resume_auto_monitoring` →
  `set_maintenance` → `clear_maintenance`) to confirm no branch was
  duplicated, dropped, or mis-ordered relative to where
  `$pendingNotification`/`$pendingMonitorDeletion` are declared and
  consumed.

No further bugs, missing wiring, or scope gaps found after this pass.
