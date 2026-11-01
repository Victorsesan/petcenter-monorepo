# Task 9 Completion — Uptime Dashboard Extras

## Task Number
Task 9, quoted in full from `Unified-Status-Dashboard-Task-Tracker-v3-FINAL.md`:

> **What:** Three additions to Task 8's dashboard: (1) a scheduled-maintenance banner the CEO can set to pre-announce planned downtime; (2) a small embeddable status badge (image or script snippet) that could later be dropped into `adopt.petcenterco.com`'s footer — generating it is this task's job, actually placing it on `adopt.petcenterco.com` is a different, unassigned task, not this one; (3) wiring Task 8's status-change action to notify everyone on Task 1's subscriber list, using Task 6's email helper — do not write a second email integration, call the existing one.
>
> **Where:** Extends Task 8's files (shared — anchor comments, pull latest version), reads Task 1's subscriber-list storage, calls Task 6's helper.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `action.php` | `uptime/admin/action.php` | Changed | Yes — Task 8 created it; Task 10 further extended it after this task shipped (see note below) |
| `uptime-status.php` | `api/uptime-status.php` | Changed | Yes — Task 8 created it; Task 10 further extended it after this task shipped |
| `uptime.html` | `uptime/index.html` | Changed | Yes — Task 8 created it |
| `uptime-admin.html` (deployed as `index.html`) | `uptime/admin/index.html` | Changed | Yes — Task 8 created it; Task 10 further extended it after this task shipped |
| `uptime-status.json` | `data/uptime-status.json` | Changed (documentation/schema note only — see Code Changes; the real functional change is a self-heal in `action.php`, not a static data overwrite) | Yes — Task 8 created it; Task 10 extended its schema further |
| `notify-config.php` | `notify-config.php` | New | No |
| `badge.php` | `api/badge.php` | New | No |
| `send-status-notification.php` | `installments/send-status-notification.php` | New | No — **but flagged heavily below: this file's live path is on a different site root entirely** |

### ⚠️ Heavily flagged: paths not covered by either named project doc, and one cross-site file

- **status.petcenterco.com's entire folder structure (`uptime/`, `uptime/admin/`, `api/`, `data/`) is not documented in either `adoption-website-project-summary.md` or `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`.** Neither doc covers this subdomain at all — it's a separate project Task 1/Task 8 established from scratch. Nothing in this task invents a new folder beyond what Task 1/Task 8 already created; `api/badge.php` and `notify-config.php` both sit in folders (`api/`, site root) that already existed before this task touched them.
- **`send-status-notification.php` is NOT on status.petcenterco.com.** Its live path, `installments/send-status-notification.php`, is relative to **adopt.petcenterco.com's own site root** (the same `/installments/` folder Task 6's `brevo-mailer.php`/`config.php` and Task 7's `bnpl-status-read.php` already live in — not a new folder, per `BNPL-Path-Sync.md`). This is the one file in this task's zip that does **not** belong under status.petcenterco.com's root — do not place it there. It belongs on the adoption site, alongside Task 6/Task 7's files.

## New-folder check
No new top-level folders were created by this task. `api/` and `data/` already existed (Task 8); `uptime/` and `uptime/admin/` already existed (Task 8); `installments/` already existed on adopt.petcenterco.com (Task 6). `notify-config.php` sits at status.petcenterco.com's existing site root, no new folder needed.

## index.html naming check
`uptime.html` deploys as `uptime/index.html` and `uptime-admin.html` deploys as `uptime/admin/index.html` — both follow the strict folder+`index.html` convention. Neither `.php` file (`action.php`, `uptime-status.php`, `badge.php`, `notify-config.php`, `send-status-notification.php`) is a page, so this convention doesn't apply to them.

New files are listed above, not pasted in this section — their full content is inside the zip.

## Code Changes

All old-code snippets below are copied exactly, character for character, from the original files as received for this task (Task 8's originals, before this task's edits), and each appears only once in that original file.

### `action.php` → live path `uptime/admin/action.php`

**Old code (top of file):**
```php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$rawBody = file_get_contents('php://input');
$input = json_decode($rawBody, true);

if (!is_array($input) || !isset($input['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing or invalid action.']);
    exit;
}

$action = $input['action'];
$validActions = ['add_domain', 'remove_domain', 'set_status'];
```

**New code:**
```php
header('Content-Type: application/json');

// TASK-9: cross-domain notify config (proxy URL + shared secret).
require_once __DIR__ . '/../../notify-config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$rawBody = file_get_contents('php://input');
$input = json_decode($rawBody, true);

if (!is_array($input) || !isset($input['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing or invalid action.']);
    exit;
}

$action = $input['action'];
// TASK-9: added set_maintenance / clear_maintenance
$validActions = ['add_domain', 'remove_domain', 'set_status', 'set_maintenance', 'clear_maintenance'];
```

**Old code (default data shape when file is empty):**
```php
if ($data === null) {
    $data = ['_comment' => 'TASK-8: see data/uptime-status.json header comment.', 'domains' => []];
}
if (!isset($data['domains']) || !is_array($data['domains'])) {
    $data['domains'] = [];
}

$responsePayload = null;
$httpStatus = 200;
```

**New code:**
```php
if ($data === null) {
    $data = ['_comment' => 'TASK-8: see data/uptime-status.json header comment.', 'domains' => []];
}
if (!isset($data['domains']) || !is_array($data['domains'])) {
    $data['domains'] = [];
}
// TASK-9: self-heal the maintenance field on any write.
if (!isset($data['maintenance']) || !is_array($data['maintenance'])) {
    $data['maintenance'] = [
        'active' => false, 'title' => '', 'message' => '',
        'starts_at' => null, 'ends_at' => null, 'updated_at' => null,
    ];
}

$responsePayload = null;
$httpStatus = 200;

// TASK-9: set after a successful set_status write, consumed AFTER the
// file lock is released.
$pendingNotification = null;
```

**Old code (inside `set_status`, history-append block):**
```php
            if ($oldStatus !== $status) {
                if (!isset($domain['history']) || !is_array($domain['history'])) {
                    $domain['history'] = [];
                }
                $domain['history'][] = [
                    'from' => $oldStatus,
                    'to'   => $status,
                    'note' => $note,
                    'at'   => $timestamp,
                ];
            }
```

**New code:**
```php
            if ($oldStatus !== $status) {
                if (!isset($domain['history']) || !is_array($domain['history'])) {
                    $domain['history'] = [];
                }
                $domain['history'][] = [
                    'from' => $oldStatus,
                    'to'   => $status,
                    'note' => $note,
                    'at'   => $timestamp,
                ];

                // TASK-9: queue the subscriber notification -- fired
                // after the lock is released, not here.
                $pendingNotification = [
                    'domainName' => $domain['name'],
                    'oldStatus'  => $oldStatus,
                    'newStatus'  => $status,
                    'note'       => $note,
                ];
            }
```

**Old code (write/unlock/return, end of file):**
```php
}

if ($httpStatus === 200) {
    ftruncate($handle, 0);
    rewind($handle);
    fwrite($handle, json_encode($data, JSON_PRETTY_PRINT));
    fflush($handle);
}

flock($handle, LOCK_UN);
fclose($handle);

http_response_code($httpStatus);
echo json_encode($responsePayload);
```

**New code:**
```php
} elseif ($action === 'set_maintenance') {
    // TASK-9: CEO-settable scheduled-maintenance banner. Global (one
    // banner for the whole status page), not per-domain.
    $title = isset($input['title']) ? trim($input['title']) : '';
    $message = isset($input['message']) ? trim($input['message']) : '';
    $startsAt = isset($input['starts_at']) ? trim($input['starts_at']) : '';
    $endsAt = isset($input['ends_at']) ? trim($input['ends_at']) : '';

    if ($title === '' || $message === '') {
        $httpStatus = 400;
        $responsePayload = ['success' => false, 'message' => 'Title and message are required.'];
    } else {
        $data['maintenance'] = [
            'active'     => true,
            'title'      => $title,
            'message'    => $message,
            'starts_at'  => $startsAt !== '' ? $startsAt : null,
            'ends_at'    => $endsAt !== '' ? $endsAt : null,
            'updated_at' => nowIso(),
        ];
        $responsePayload = ['success' => true, 'maintenance' => $data['maintenance']];
    }
} elseif ($action === 'clear_maintenance') {
    $data['maintenance'] = [
        'active' => false, 'title' => '', 'message' => '',
        'starts_at' => null, 'ends_at' => null, 'updated_at' => nowIso(),
    ];
    $responsePayload = ['success' => true, 'maintenance' => $data['maintenance']];
}

if ($httpStatus === 200) {
    ftruncate($handle, 0);
    rewind($handle);
    fwrite($handle, json_encode($data, JSON_PRETTY_PRINT));
    fflush($handle);
}

flock($handle, LOCK_UN);
fclose($handle);

// TASK-9: fire the subscriber notification only after the file lock is
// fully released, so Brevo's response time never holds up the write.
if ($pendingNotification !== null) {
    notifySubscribersOfStatusChange(
        $pendingNotification['domainName'],
        $pendingNotification['oldStatus'],
        $pendingNotification['newStatus'],
        $pendingNotification['note']
    );
}

http_response_code($httpStatus);
echo json_encode($responsePayload);
```

Also added two new top-level functions with no prior equivalent — `buildStatusChangeEmail()` and `notifySubscribersOfStatusChange()`. (As of the SECOND VERIFICATION ROUND below, Task 10 has since relocated both into their own `uptime-notify-helpers.php` — see that section.)

---

### `uptime-status.php` → live path `api/uptime-status.php`

**Old code (tail of file):**
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

echo json_encode([
    'success' => true,
    'domains' => $domains,
], JSON_PRETTY_PRINT);
```

**New code:**
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

/**
 * TASK-9 START
 * Expose the scheduled-maintenance banner alongside domains so
 * uptime.html can render it in the same fetch -- no second request.
 */
$maintenanceRaw = $data['maintenance'] ?? null;
$maintenance = [
    'active'     => is_array($maintenanceRaw) ? (bool) ($maintenanceRaw['active'] ?? false) : false,
    'title'      => is_array($maintenanceRaw) ? ($maintenanceRaw['title'] ?? '') : '',
    'message'    => is_array($maintenanceRaw) ? ($maintenanceRaw['message'] ?? '') : '',
    'starts_at'  => is_array($maintenanceRaw) ? ($maintenanceRaw['starts_at'] ?? null) : null,
    'ends_at'    => is_array($maintenanceRaw) ? ($maintenanceRaw['ends_at'] ?? null) : null,
    'updated_at' => is_array($maintenanceRaw) ? ($maintenanceRaw['updated_at'] ?? null) : null,
];
/** TASK-9 END */

echo json_encode([
    'success'     => true,
    'domains'     => $domains,
    'maintenance' => $maintenance, // TASK-9
], JSON_PRETTY_PRINT);
```

---

### `uptime.html` → live path `uptime/index.html`

**Old code (CSS, tail of style block):**
```css
  .empty-state { color: var(--text-on-dark-muted); font-size: 14px; background: var(--navy-card); border: 1px solid var(--navy-card-border); border-radius: 12px; padding: 20px; }
</style>
```

**New code:**
```css
  .empty-state { color: var(--text-on-dark-muted); font-size: 14px; background: var(--navy-card); border: 1px solid var(--navy-card-border); border-radius: 12px; padding: 20px; }

  /* ===== TASK-9: scheduled-maintenance banner ===== */
  .maintenance-banner {
    display: none;
    align-items: flex-start; gap: 12px;
    background: rgba(245,158,11,0.12); border: 1px solid var(--warning);
    border-radius: 12px; padding: 16px 20px; margin-bottom: 20px;
  }
  .maintenance-banner.active { display: flex; }
  .maintenance-banner__icon { flex-shrink: 0; margin-top: 2px; color: var(--warning); }
  .maintenance-banner__title { font-weight: 700; font-size: 14.5px; margin: 0 0 4px; color: var(--text-on-dark); }
  .maintenance-banner__message { font-size: 13.5px; color: var(--text-on-dark-muted); margin: 0; }
  .maintenance-banner__window { font-size: 12px; color: var(--text-on-dark-muted); margin-top: 6px; }
</style>
```

**Old code (`<main>`, before legend):**
```html
  <p class="subtitle">Live status and incident history for Pet Center's domains and services.</p>

  <div class="legend">
```

**New code:**
```html
  <p class="subtitle">Live status and incident history for Pet Center's domains and services.</p>

  <!-- TASK-9: scheduled-maintenance banner, hidden by default, toggled by JS -->
  <div class="maintenance-banner" id="maintenanceBanner">
    <span class="maintenance-banner__icon" aria-hidden="true">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 3l10 18H2L12 3z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><line x1="12" y1="9" x2="12" y2="14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="17" r="1" fill="currentColor"/></svg>
    </span>
    <div>
      <p class="maintenance-banner__title" id="maintenanceBannerTitle"></p>
      <p class="maintenance-banner__message" id="maintenanceBannerMessage"></p>
      <p class="maintenance-banner__window" id="maintenanceBannerWindow"></p>
    </div>
  </div>

  <div class="legend">
```

**Old code (fetch success handler):**
```javascript
      .then(function (result) {
        var list = document.getElementById('domainList');
        list.innerHTML = '';
        if (!result.success) {
          list.innerHTML = '<p class="empty-state">Status data is temporarily unavailable.</p>';
          return;
        }
        if (!result.domains.length) {
```

**New code:**
```javascript
      .then(function (result) {
        var list = document.getElementById('domainList');
        list.innerHTML = '';
        if (!result.success) {
          list.innerHTML = '<p class="empty-state">Status data is temporarily unavailable.</p>';
          return;
        }
        renderMaintenance(result.maintenance); // TASK-9
        if (!result.domains.length) {
```

(Also added a new `renderMaintenance(maintenance)` function, full new code, inserted just before the `fetch('/api/uptime-status.php')` call.)

---

### `uptime-admin.html` → live path `uptime/admin/index.html`

**Old code (`<main>`, after the Domains panel closes):**
```html
  <!-- ===== TASK-8: Domain list + per-domain status control ===== -->
  <section class="panel">
    <h2>Domains</h2>
    <div id="domainList"><p class="empty-state">Loading...</p></div>
  </section>
</main>
```

**New code:**
```html
  <!-- ===== TASK-8: Domain list + per-domain status control ===== -->
  <section class="panel">
    <h2>Domains</h2>
    <div id="domainList"><p class="empty-state">Loading...</p></div>
  </section>

  <!-- ===== TASK-9: Scheduled maintenance banner ===== -->
  <section class="panel">
    <h2>Scheduled Maintenance</h2>
    <div class="maintenance-current" id="maintenanceCurrent">Loading current banner state...</div>
    <div class="maintenance-form">
      <div><label for="maintTitle">Title</label><input type="text" id="maintTitle" placeholder="e.g. Scheduled maintenance on petcenterco.com"></div>
      <div><label for="maintMessage">Message</label><textarea id="maintMessage" placeholder="What's happening and what visitors should expect."></textarea></div>
      <div class="maintenance-form__row">
        <div><label for="maintStarts">Starts (optional)</label><input type="datetime-local" id="maintStarts"></div>
        <div><label for="maintEnds">Ends (optional)</label><input type="datetime-local" id="maintEnds"></div>
      </div>
      <div class="maintenance-form__actions">
        <button type="button" class="btn" id="setMaintenanceBtn">Set banner</button>
        <button type="button" class="btn btn-secondary" id="clearMaintenanceBtn">Clear banner</button>
      </div>
      <div class="feedback" id="maintenanceFeedback" role="status"></div>
    </div>
  </section>

  <!-- ===== TASK-9: Embeddable status badge ===== -->
  <section class="panel">
    <h2>Embeddable Status Badge</h2>
    <p class="subtitle" style="margin-bottom:14px;">Placing this on adopt.petcenterco.com (or anywhere else) is a separate task -- this is just the badge and the snippet to hand off.</p>
    <div class="badge-panel__preview"><img id="badgePreview" src="/api/badge.php" alt="Pet Center Status badge preview" width="1" height="20"></div>
    <div class="badge-panel__snippet"><textarea id="badgeSnippet" readonly></textarea><button type="button" class="btn btn-secondary" id="copyBadgeBtn">Copy</button></div>
    <div class="feedback" id="badgeFeedback" role="status"></div>
  </section>
</main>
```

**Old code (`loadDomains()` success handler):**
```javascript
          result.domains.forEach(function (d) {
            list.appendChild(renderDomain(d));
          });
        })
        .catch(function () {
          document.getElementById('domainList').innerHTML = '<p class="empty-state">Could not reach the server.</p>';
        });
```

**New code:**
```javascript
          result.domains.forEach(function (d) {
            list.appendChild(renderDomain(d));
          });
          renderMaintenanceCurrent(result.maintenance); // TASK-9: same response, no second fetch
        })
        .catch(function () {
          document.getElementById('domainList').innerHTML = '<p class="empty-state">Could not reach the server.</p>';
        });
```

**Old code (end of admin script, before closing IIFE):**
```javascript
    loadDomains();
  })();
```

**New code:**
```javascript
    // TASK-9: scheduled-maintenance panel logic (renderMaintenanceCurrent,
    // toDatetimeLocalValue, and the setMaintenanceBtn / clearMaintenanceBtn
    // click handlers) and embeddable badge panel logic (builds the badge
    // <img> src and copy-paste snippet from window.location.origin, wires
    // the Copy button to navigator.clipboard) -- full new code, both
    // self-contained blocks inserted here, before the existing loadDomains()
    // call below.

    loadDomains();
  })();
```

---

### `uptime-status.json` → live path `data/uptime-status.json`

**Old code:**
```json
{
  "_comment": "TASK-8: Uptime status record. ...",
  "domains": []
}
```

**New code:**
```json
{
  "_comment": "TASK-8/TASK-9: Uptime status record. ... TASK-9 adds a single top-level 'maintenance' object (NOT per-domain) for the CEO-settable scheduled-maintenance banner shown on uptime.html -- see uptime/admin/action.php's set_maintenance/clear_maintenance actions.",
  "domains": [],
  "maintenance": {
    "active": false,
    "title": "",
    "message": "",
    "starts_at": null,
    "ends_at": null,
    "updated_at": null
  }
}
```

**Important:** this diff is documentation only. The live file's `domains` array is CEO-managed and may no longer be empty — `action.php`'s TASK-9 self-heal block (see above) already adds the `maintenance` field automatically on the very next write regardless of what's in `domains`, so this file does **not** need to be manually patched for the feature to work. Only patch it by hand if the `_comment` text itself is worth updating for documentation purposes.

## New Files (not pasted — see zip)
- `notify-config.php` → `notify-config.php`
- `badge.php` → `api/badge.php`
- `send-status-notification.php` → `installments/send-status-notification.php` **on adopt.petcenterco.com, not status.petcenterco.com — see the heavily-flagged note above**

## Bugs Found Outside Task Scope
None found in Task 8's original files at the time of the first delivery.

## Pending / Open Items / Doubts
1. **`STATUS_NOTIFY_KEY` activation.** `notify-config.php` reads this from a local `.env` file (`status.petcenterco.com/.env`, same folder as `notify-config.php` itself) rather than hardcoding it, per this delivery format's no-secrets-in-zip rule. The real, already-generated working value (not a placeholder — standing instruction is to never ship a placeholder) is:
   ```
   STATUS_NOTIFY_KEY=26362be5ae284a59db76cdef8aa928e577c43461ee15643198a8f786b8e50b9d
   ```
   This exact line needs to be added to **two** places to activate the feature: `status.petcenterco.com/.env` (new file, not in the zip) and `/adopt/admin/.env` (existing file, alongside `BNPL_INTERNAL_READ_KEY`) — `send-status-notification.php` on the adopt.petcenterco.com side reads it from there. If a different value is wanted later, generate a new 64-char hex string and update both places.
2. **Where the embed snippet gets placed** is explicitly a separate, unassigned task per the spec quoted above — not done here on purpose.
3. **No PHP interpreter or network access was available in the build sandbox** to run live/Playwright verification — only JSON validity and brace/paren/bracket structural checks were performed. Flagging this honestly rather than claiming verification that didn't happen; a live smoke test before/shortly after deploy is recommended.

## SECOND VERIFICATION ROUND (CEO-requested re-sync, after Task 10 shipped)

Task 10 had since shipped and pulled this task's version of the shared files (`action.php`, `api/uptime-status.php`, `uptime/admin/index.html`), extracting `buildStatusChangeEmail()`/`notifySubscribersOfStatusChange()` into their own `uptime-notify-helpers.php` (new live path: `uptime-notify-helpers.php`, site root) and adding `monitor_id`/`manual_override` tracking. Re-read Task 10's completion doc and every real current file it touched, line by line, rather than trusting the diff summary alone.

**No bugs found in Task 10's integration of this task's work.** Specifically checked and confirmed clean: `uptime-notify-helpers.php` preserves both functions verbatim (comments included) with a correctly re-derived `subscribers.txt` path for its new location; `action.php`'s `elseif` chain, `$pendingNotification`/Task 10's own pending-deletion handling, and the maintenance self-heal block are all intact and uncorrupted by Task 10's additions; `api/uptime-status.php` still exposes the `maintenance` object unchanged alongside Task 10's new fields; `uptimerobot-config.php` correctly flags its API key as something only the CEO can generate rather than fabricating one.

**Two pre-existing bugs confirmed and fixed in this round** (inherited from Task 8's original files, present in this task's first delivery too, not introduced by Task 10):

1. **Missing `<noscript>` fallback** on both `uptime/index.html` and `uptime/admin/index.html` — Task 1's own pages set this precedent and neither Task 8 nor this task's first delivery followed it. Fixed in both files now (new markup, no outside source needed).

   **Old code** (`uptime/index.html`, start of `<body>` — copied exactly from this task's own first-round delivered file, since Task 10 did not touch this file):
   ```html
   <body>

   <!-- ===== TASK-1: Header — copied verbatim ===== -->
   <header class="status-header" id="statusHeader">
   ```

   **New code:**
   ```html
   <body>

   <!-- TASK-9 (follow-up fix): noscript fallback, matching Task 1's established
        precedent for pages on this subdomain. Status/history/maintenance-banner
        rendering is entirely JS-driven here, so without JS visitors would
        otherwise see an empty page with no explanation. -->
   <noscript>
     <div style="max-width:900px;margin:40px auto;padding:20px 24px;background:#172c60;border:1px solid rgba(255,255,255,0.08);border-radius:12px;color:#e7eaf3;font-family:-apple-system,'Segoe UI',Roboto,system-ui,sans-serif;">
       This page requires JavaScript to display live status information. Please enable JavaScript and reload.
     </div>
   </noscript>

   <!-- ===== TASK-1: Header — copied verbatim ===== -->
   <!-- FLAGGED FOR INTEGRATOR: this header only has the brand logo. Task 1's
        real index.html header also has a "Subscribe to updates" dropdown
        (with its own CSS/JS) that never made it into Task 8's original copy
        of this header, so every page since (this one included) inherited the
        same incomplete copy. Needs Task 1's real index.html as the source to
        fix properly -- not reconstructed here since no page in this task
        chain has ever had that real source to copy from. -->
   <header class="status-header" id="statusHeader">
   ```

   **Old code** (`uptime/admin/index.html`, start of `<body>` — copied exactly from the current Task-10-merged file received for this round):
   ```html
   <body>

   <!-- ===== TASK-1: Header — copied verbatim, per its own instruction to future pages ===== -->
   <header class="status-header" id="statusHeader">
   ```

   **New code:**
   ```html
   <body>

   <!-- TASK-9 (follow-up fix): noscript fallback, matching Task 1's established
        precedent for pages on this subdomain. Every control on this page is
        JS-driven (fetch calls to action.php), so without JS the page would
        otherwise render as an empty shell with no explanation. -->
   <noscript>
     <div style="max-width:900px;margin:40px auto;padding:20px 24px;background:#172c60;border:1px solid rgba(255,255,255,0.08);border-radius:12px;color:#e7eaf3;font-family:-apple-system,'Segoe UI',Roboto,system-ui,sans-serif;">
       This page requires JavaScript to load and manage domain statuses. Please enable JavaScript and reload.
     </div>
   </noscript>

   <!-- ===== TASK-1: Header — copied verbatim, per its own instruction to future pages ===== -->
   <!-- FLAGGED FOR INTEGRATOR: this header only has the brand logo. Task 1's
        real index.html header also has a "Subscribe to updates" dropdown
        (with its own CSS/JS) that never made it into Task 8's original copy
        of this header, so every page since (this one included) inherited the
        same incomplete copy. Needs Task 1's real index.html as the source to
        fix properly -- not reconstructed here since no page in this task
        chain has ever had that real source to copy from. -->
   <header class="status-header" id="statusHeader">
   ```

2. **Incomplete header** on both pages — confirmed directly against Task 8's original `uptime.html` (only the brand logo, no "Subscribe to updates" dropdown that Task 1's real header has per its own completion doc). This was never fixed because **no page in this task chain has ever actually received Task 1's real `index.html`** — Task 8 copied an already-incomplete version, and that incompleteness propagated through every page since, including Task 10's additions to the admin page. Per the CEO's explicit instruction not to chase Task 1's file for this, it's flagged in-place (see the `FLAGGED FOR INTEGRATOR` comments in the diffs above) rather than guessed at.

**Also re-confirmed, no issue found:** `api/badge.php` and `installments/send-status-notification.php` needed no changes — neither was touched by Task 10, and both were re-read against their own code (not just memory) to confirm that.

**Update to the open item above:** `notify-config.php` was additionally reworked in this round to read `STATUS_NOTIFY_KEY` from `.env` instead of hardcoding it, specifically to comply with `WORKER-DELIVERY-GUIDE.md`'s rule that secrets never travel inside a hand-in zip — this is a hand-in-format fix, not a functional behavior change (the real secret value is unchanged, just where it's read from).

## Self-Check Confirmation
- Scope Lock respected across both rounds: only Task 8's five shared files were extended (with TASK-9 anchor comments preserving every line of TASK-8's/Task 10's original code and comments elsewhere in each file), plus three genuinely new files — no unrelated file was touched.
- Reused Task 6's `brevo_send_email()` rather than writing a second Brevo integration; the new proxy endpoint contains no email-sending logic of its own.
- Reused Task 1's `data/subscribers.txt` read-only — never written to by this task.
- `set_status`'s existing `$oldStatus !== $status` history-trigger condition is the same condition gating the new subscriber notification — no separate/diverging trigger logic.
- Notification calls happen only after `flock()`/`fclose()` release, so a slow or failing Brevo call can never hold up the admin write lock or the public read endpoint.
- Every value written to the shared `status` field is still restricted server-side to exactly `up`/`down`/`degraded` — unchanged from Task 8, not touched by this task's additions.
- No emojis anywhere; all icons are inline SVG matching the existing design-system/Task-8 icon style.
- Badge endpoint always returns HTTP 200 with a valid SVG, even on missing/corrupt data, so it never renders as a broken `<img>`.
- No secret values are hardcoded inside any file in the zip — `notify-config.php` reads from `.env`; `send-status-notification.php` already did.
- JSON validity and brace/paren/bracket balance were checked for every new/modified PHP, JSON, and HTML file across both rounds.
