# Task C1a / C1b Completion — Report System, Welfare Hotline Routing, Admin Reports Queue

## Task Number
C1a and C1b — both assigned to this worker per the CEO's message in this thread, against the Rehoming
Platform task document ("You do both c1a and c1b").

## What Was Required
**C1a** — a Report control (pet-detail page + Task B6's merchant profile `actions` section) that opens a
two-path modal: "Platform issue" (category dropdown + description form, min 20 characters, written as a
Report record and emailed to the target merchant with no reporter identity) or "Animal welfare concern"
(no form — the country/region-specific hotline is shown immediately, logged as a record-only entry that
never enters the triage queue). Logged-out visitors must hit Task A4's `showAuthPrompt()` instead of the
modal. Merchants who are `is_test_account` never get a written record. Any dropdown must use the design
system's Section 24 locked "message bubble" shell.

**C1b** — the admin-side Reports queue (Queue tab: user reports + system flags, filterable by status/source,
with triage actions; Welfare log tab: record-only, no actions) plus the merchant notification email and its
standalone response page. Dismissing a report whose `holds_listing` is true clears the listing's
`held_for_review` field.

Both tasks build against the SHARED CONTRACTS' **Report record**, **`held_for_review`**, and
**`is_test_account`** fields exactly as published, and against Task A4's `showAuthPrompt(contextMessage)`
signature as a call site only (not re-implemented here).

This hand-in follows a CEO-requested self-audit ("confirm you have everything in order and there are no
bugs... do not overlook minor details"), which found and fixed real defects: wrong AU NSW/WA hotline digits,
missing UK/Alberta regional caveats, a modal shell that didn't actually match Design System Section 5, no
focus management, a welfare-record dedupe bug, a mixed Apache-syntax `.htaccess`, a missing `no-referrer`
meta on the merchant response page, and a mobile layout bug in the admin Reports queue. All are described
below, folded into each file's own entry rather than kept as a separate findings list, per the current
hand-in format.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `pet-detail.html` | `pets/detail/index.html` | Changed (3 anchored edits) | Yes — calls Task A4's `showAuthPrompt()`; renders Report-record fields this task (C1b) owns |
| `admin-dashboard.html` | `admin/index.html` | Changed (6 anchored edits: rail button, panel HTML, tab CSS, mobile min-width CSS, `switchPanel()` hook, Reports JS block) | Yes — reads Report records this task (C1a/C1b) and Task C2's detectors write |
| `report-modal.js` | `admin/report-modal.js` | New file — in the zip | Yes — called from `pets/detail/index.html` and from Task B6's merchant profile page once it exists |
| `reports-lib.php` | `admin/reports-lib.php` | New file — in the zip | Yes — shared library `report-submit.php`/`reports-admin.php`/`report-respond.php` all use; also the code path Task C2's detectors call (`pc_report_create()`) to write `system_flag` records |
| `report-submit.php` | `admin/report-submit.php` | New file — in the zip | Yes — called by `report-modal.js` |
| `reports-admin.php` | `admin/reports-admin.php` | New file — in the zip | Yes — called by the Reports panel in `admin/index.html` |
| `report-respond.php` | `admin/report-respond.php` | New file — in the zip | Yes — called by the merchant response page below |
| `merchant-report-response-v1.html` | `report/respond/index.html` | New file — in the zip | Yes — reached from the email `reports-lib.php` sends |
| Task B6's merchant profile page (`<!-- MERCHANT-PROFILE: actions -->` section) | Not yet published by B6 — **not touched** | Not applicable — see note below | B6 |

**⚠ Flagging heavily — new top-level folder required:** `report/` is **not** in either
`adoption-website-project-summary.md`'s or `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`'s documented folder
structure — I checked both in full before writing this. It needs to be created as a new top-level folder on
the live site (a sibling of `admin/`, `pets/`, `apply/`, etc.), containing `respond/index.html`, so the live
URL is `adopt.petcenterco.com/report/respond/`. This follows the site's own existing convention — a folder
containing `index.html`, never a loose `name.html` — it is simply a path neither summary document anticipated
because the Report system itself is new. If `report/` collides with a path another worker is also adding,
that needs reconciling before deploy; I have no visibility into other workers' new top-level folders.

**Note on B6:** Task B6 has not published a completion file yet, so its merchant profile page does not exist
as a real file to edit — there is nothing to put in the zip or in Code Changes for it. `report-modal.js` was
built and tested against B6's published `<!-- MERCHANT-PROFILE: actions -->` section name using a local stub
page (`screenshots/C1a-07-from-profile-page.png`). The stub markup is reproduced below for whoever reconciles
B6's real file once it exists — it is **not** part of this hand-in's Files Changed table or zip, since it
edits a file this task was never given:

```html
<!-- MERCHANT-PROFILE: actions -->
<!-- TASK-C1a START -->
<button type="button" class="pcr-launch" id="pcReportBtn">Report</button>
<!-- TASK-C1a END -->
<script src="/admin/report-modal.js"></script>
<script>
/* TASK-C1a: same pattern as pets/detail/index.html — getOpts is read at click time. targetType is
   'merchant' here because this button reports the merchant/profile itself, not one specific listing. */
window.__pcReportCtx = { targetType: 'merchant', targetId: MERCHANT_ID, merchantId: MERCHANT_ID, targetLabel: MERCHANT_NAME, isTestAccount: MERCHANT_IS_TEST_ACCOUNT };
PCReport.attach(document.getElementById('pcReportBtn'), function () { return window.__pcReportCtx; });
</script>
```

## Code Changes

Every "Old code" block below is copied character for character from the original `pet-detail.html` /
`admin-dashboard.html` files attached to this thread, and each was confirmed to occur exactly once in that
original file before this document was written. These are the **only two files** with Code Changes blocks —
every other file in this task is brand-new, so per the current hand-in rules it has no old/new diff here; see
**New files** below instead.

### `pet-detail.html` → live path `pets/detail/index.html` — edit 1 of 3 (Report button, under the Pet ID row)

<!-- TASK-C1a START -->
**Old code:**
```html
          <div class="pc-pet-id-row">
            <span>Pet ID: <strong id="petId">—</strong></span>
          </div>
```

**New code:**
```html
          <div class="pc-pet-id-row">
            <span>Pet ID: <strong id="petId">—</strong></span>
          </div>

          <!-- TASK-C1a START — Report button. Logged-out visitors see it; clicking calls
               showAuthPrompt() (Task A4) inside PCReport.open(). Wired in the C1a script below. -->
          <div class="pcr-launch-row" style="margin-top:12px;">
            <button type="button" class="pcr-launch" id="pcReportBtn" aria-label="Report this listing">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
              Report
            </button>
          </div>
          <!-- TASK-C1a END -->
```
<!-- TASK-C1a END -->

### `pet-detail.html` → live path `pets/detail/index.html` — edit 2 of 3 (capture the Report target once the pet loads)

<!-- TASK-C1a START -->
**Old code:**
```javascript
    renderMerchantBadgeInline(pet.merchant || null, 'petMerchantBadgeInline');
```

**New code:**
```javascript
    renderMerchantBadgeInline(pet.merchant || null, 'petMerchantBadgeInline');

    // TASK-C1a START — remember which listing/merchant the Report button targets.
    // Read at click time by PCReport.attach() (see the C1a script before </body>).
    // merchant.id / merchant.is_test_account follow the SHARED CONTRACTS (is_test_account
    // owned by Task E5); posted_by is the display fallback while no merchant object exists.
    window.__pcReportCtx = {
      targetType: 'listing',                                   // Report record: target_type
      targetId: pet.pet_id || pet.id || petId,                 // listing ID field = existing pet_id
      merchantId: (pet.merchant && pet.merchant.id) || pet.merchant_id || null,
      targetLabel: pet.name || 'this listing',
      isTestAccount: !!(pet.merchant && pet.merchant.is_test_account)
    };
    // TASK-C1a END
```
<!-- TASK-C1a END -->

### `pet-detail.html` → live path `pets/detail/index.html` — edit 3 of 3 (load the shared modal script, wire the button)

<!-- TASK-C1a START -->
**Old code:**
```html
<script src="/admin/chat-widget.js"></script>
```

**New code:**
```html
<script src="/admin/chat-widget.js"></script>
<!-- TASK-C1a START — shared Report modal (new file, same /admin/ delivery pattern as chat-widget.js) -->
<script src="/admin/report-modal.js"></script>
<script>
/* Wire the Report button. getOpts runs at click time so it always sees the loaded pet.
   If the page hasn't finished loading the pet yet, the click is ignored safely. */
(function () {
  if (!window.PCReport) return;
  PCReport.attach(document.getElementById('pcReportBtn'), function () {
    return window.__pcReportCtx || { targetType: 'listing', targetId: '', targetLabel: '' };
  });
})();
</script>
<!-- TASK-C1a END -->
```
<!-- TASK-C1a END -->

### `admin-dashboard.html` → live path `admin/index.html` — edit 1 of 6 (icon rail button)

<!-- TASK-C1b START -->
**Old code:**
```html
      <button class="admin-icon-rail__item" data-panel="chatBot" onclick="switchPanel('chatBot')" title="Bot Profile"><i class="fas fa-robot"></i></button>
    </div>
    <!-- TASK-4 END (icon rail) -->
```

**New code:**
```html
      <button class="admin-icon-rail__item" data-panel="chatBot" onclick="switchPanel('chatBot')" title="Bot Profile"><i class="fas fa-robot"></i></button>
      <!-- TASK-C1b START — Reports rail button (same markup as its siblings; mobile menu clones it automatically) -->
      <button class="admin-icon-rail__item" data-panel="reports" onclick="switchPanel('reports')" title="Reports"><i class="fas fa-flag"></i></button>
      <!-- TASK-C1b END -->
    </div>
    <!-- TASK-4 END (icon rail) -->
```
<!-- TASK-C1b END -->

Verified in a real headless browser against the actual file: `renderMobileIconMenu()` clones every
`.admin-icon-rail__item` generically by reading each button's `title` attribute, so "Reports" appears in the
mobile hamburger drawer automatically with no extra wiring — `screenshots/C1b-mobile-menu-reports.png`.

### `admin-dashboard.html` → live path `admin/index.html` — edit 2 of 6 (Reports panel HTML, inserted immediately before the Reviews panel)

<!-- TASK-C1b START -->
**Old code (the line this block is inserted directly before — unchanged, shown as the anchor):**
```html
<!-- REVIEWS PANEL -->
```

**New code (inserted immediately before that line; the old line itself is untouched and still follows):**
```html
<!-- TASK-C1b START — REPORTS PANEL. Cloned patterns: Reviews panel header, Applications panel
         filter row + .pc-apps-scroll grid list + status pills + .pc-app-actions popover.
         Tabs are .rpt-tab-btn (own class on purpose: .chat-tab-btn is toggled by switchChatTab()). -->
    <div id="reportsPanel" class="admin-panel" style="display:none;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;flex-wrap:wrap;gap:10px;">
        <div>
          <h2 style="font-family:'Cormorant Garamond',serif;color:#10214b;margin-bottom:6px;">Reports</h2>
          <p style="color:#888;font-size:14px;">User reports and automatic fraud flags. Welfare concerns are kept in a separate record-only log.</p>
        </div>
        <button onclick="loadReports()" class="pc-btn pc-btn--ghost"><i class="fas fa-rotate-right"></i> Refresh</button>
      </div>

      <!-- Tabs: triage queue vs. record-only welfare log -->
      <div style="display:flex;gap:4px;margin:14px 0 18px;border-bottom:1px solid #ece7e0;flex-wrap:wrap;">
        <button class="rpt-tab-btn active" data-rtab="queue" onclick="switchReportsTab('queue')">Queue <span id="rptCountQueue" class="chat-tab-count"></span></button>
        <button class="rpt-tab-btn" data-rtab="welfare" onclick="switchReportsTab('welfare')">Welfare log (record only) <span id="rptCountWelfare" class="chat-tab-count"></span></button>
      </div>

      <!-- Filters (queue tab only). Native selects here are upgraded to the dashboard's Section 24
           custom dropdown by upgradeNativeSelectsToCustom() when the panel opens — same as Applications. -->
      <div id="reportsFilters" style="display:flex;gap:14px;margin-bottom:20px;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:1;min-width:180px;">
          <label class="pc-form-label" style="margin-bottom:6px;">Filter by Status</label>
          <select id="rptStatusFilter" class="pc-form-input" style="cursor:pointer;" onchange="renderReports()">
            <option value="">All Statuses</option>
            <option value="open">Open</option>
            <option value="reviewed">Reviewed</option>
            <option value="actioned">Actioned</option>
            <option value="dismissed">Dismissed</option>
          </select>
        </div>
        <div style="flex:1;min-width:180px;">
          <label class="pc-form-label" style="margin-bottom:6px;">Filter by Source</label>
          <select id="rptSourceFilter" class="pc-form-input" style="cursor:pointer;" onchange="renderReports()">
            <option value="">All Sources</option>
            <option value="user_report">User reports</option>
            <option value="system_flag">System flags</option>
          </select>
        </div>
        <div style="flex:0 0 auto;"><div id="rptCount" style="font-size:13px;color:#8b7d6b;padding:12px 0;"></div></div>
      </div>

      <div id="reportsMsg" role="status" style="display:none;margin-bottom:12px;padding:10px 14px;border-radius:8px;background:#e6f7ef;border:1px solid #0d7c4a;color:#0d7c4a;font-size:13px;"></div>

      <div class="pc-apps-scroll">
        <div id="reportsHeaderRow"></div>
        <div id="reportsList"></div>
        <div id="reportsEmpty" style="display:none;padding:48px 24px;text-align:center;background:#fff;border-radius:0 0 8px 8px;border:1px solid #eee;border-top:none;">
          <p style="color:#aaa;font-size:14px;">Nothing here yet.</p>
        </div>
      </div>
    </div>
    <!-- TASK-C1b END -->

<!-- REVIEWS PANEL -->
```
<!-- TASK-C1b END -->

### `admin-dashboard.html` → live path `admin/index.html` — edit 3 of 6 (Reports tab button CSS + mobile min-width fix)

<!-- TASK-C1b START -->
**Old code:**
```css
.chat-tab-btn.active { color: #10214b; border-bottom-color: #ED7624; }
```

**New code:**
```css
.chat-tab-btn.active { color: #10214b; border-bottom-color: #ED7624; }
/* TASK-C1b START — Reports tabs: visual clone of .chat-tab-btn under its own class so the chat's
   switchChatTab() (which toggles every .chat-tab-btn) never touches these. */
.rpt-tab-btn {
  border: none; background: none; cursor: pointer; padding: 10px 16px; font-size: 13px;
  font-weight: 600; color: #888; border-bottom: 2px solid transparent; font-family: 'Sora', system-ui, sans-serif;
}
.rpt-tab-btn.active { color: #10214b; border-bottom-color: #ED7624; }
/* TASK-C1b END */
/* TASK-C1b START — Reports queue mobile fix: without a min-width, the grid's fractional (fr)
   columns just shrink to fit the viewport instead of triggering .pc-apps-scroll's horizontal
   scroll — found during self-audit by testing the panel at 390px: scrollWidth === clientWidth
   (418 === 418) before this rule, i.e. no scroll was happening and the 7 columns were crushed
   into illegible slivers (see screenshots/C1b-mobile-bug-before-fix.png). Same root-cause class
   of bug ADMIN_DASHBOARD_PROJECT_SUMMARY.md documents Task 4 finding and fixing for the
   Applications panel, but Task 4's fix was a bespoke stacked/accordion layout built specifically
   for that panel's own .pc-app-row class, not a mechanism Reports inherits automatically —
   building an equivalent accordion for Reports was outside this task's scope, so this instead
   makes the dashboard's other, still-live responsive mechanism actually trigger. Scoped by the
   data-rtab attribute renderReports() sets on the header/list wrappers, so the Queue tab (7
   columns, needs more room) and the Welfare log tab (4 simpler columns) each get their own floor
   instead of one forcing unnecessary horizontal scroll on the other. Applies only at the same
   <=768px breakpoint used throughout this file. Verified after the fix: scrollWidth (560) now
   genuinely exceeds clientWidth (350) inside the panel, and the surrounding page does not balloon
   (screenshots/C1b-mobile-bug-after-fix.png, C1b-01-queue-mobile.png, C1b-02-welfare-mobile.png). */
@media (max-width: 768px) {
  #reportsHeaderRow[data-rtab="queue"][style*="grid-template-columns"],
  #reportsList[data-rtab="queue"] > div[style*="grid-template-columns"] {
    min-width: 560px;
  }
  #reportsHeaderRow[data-rtab="welfare"][style*="grid-template-columns"],
  #reportsList[data-rtab="welfare"] > div[style*="grid-template-columns"] {
    min-width: 420px;
  }
}
/* TASK-C1b END */
```
<!-- TASK-C1b END -->

### `admin-dashboard.html` → live path `admin/index.html` — edit 4 of 6 (`switchPanel()` hook)

<!-- TASK-C1b START -->
**Old code:**
```javascript
  if (panel === 'chatBot') {
    loadChatBotProfile();
  }
}
```

**New code:**
```javascript
  if (panel === 'chatBot') {
    loadChatBotProfile();
  }
  // TASK-C1b START — Reports panel: upgrade its filter selects to the custom dropdown, then load
  if (panel === 'reports') {
    upgradeNativeSelectsToCustom();
    initCustomSelects();
    loadReports();
  }
  // TASK-C1b END
}
```
<!-- TASK-C1b END -->

### `admin-dashboard.html` → live path `admin/index.html` — edit 5 of 6 (Reports queue JS, inserted immediately before the `BOT PROFILE (v2.0)` section)

<!-- TASK-C1b START -->
**Old code (the line this block is inserted directly before — unchanged, shown as the anchor):**
```javascript
// ============================================================
// BOT PROFILE (v2.0)
// ============================================================
```

**New code (inserted immediately before that line; the old lines themselves are untouched and still follow):**
```javascript
// TASK-C1b START ==============================================================
// REPORTS QUEUE (Task C1b). Reads/updates Report records through /admin/reports-admin.php.
// Row markup, status pills and the 3-dot popover are clones of renderApplications() above;
// auth uses getAdminToken() exactly like loadChatReviews().
// =============================================================================
let allReports = [];          // every Report record returned by the last load (queue + welfare)
let reportsTab = 'queue';     // 'queue' (user reports + system flags) | 'welfare' (record only)

// Status pill colors — same shape as Applications' statusStyles.
const REPORT_STATUS_STYLES = {
  open:      { bg: '#fffbe6', border: '#f5a623', text: '#b87a00', icon: 'fa-clock' },
  reviewed:  { bg: '#eaf1fb', border: '#3b6fb6', text: '#2c5a9b', icon: 'fa-eye' },
  actioned:  { bg: '#e6f7ef', border: '#0d7c4a', text: '#0d7c4a', icon: 'fa-circle-check' },
  dismissed: { bg: '#f0ece7', border: '#a89c8a', text: '#7a6d5f', icon: 'fa-circle-xmark' },
};
// System-flag categories are detector names (Report record contract) — shown as readable labels.
const REPORT_DETECTOR_LABELS = {
  duplicate_photo: 'Duplicate photo', posting_velocity: 'Posting velocity',
  off_platform_contact: 'Off-platform contact', reputation_anomaly: 'Reputation anomaly',
  welfare_concern: 'Animal welfare concern',
};

// Fetch all records, then render. Same fetch/token pattern as loadChatReviews().
async function loadReports() {
  const list = document.getElementById('reportsList');
  if (!list) return;
  list.innerHTML = '<div style="color:#a89c8a;font-size:13px;padding:14px;">Loading...</div>';
  try {
    const token = getAdminToken();
    const r = await fetch('reports-admin.php?action=list', { headers: { 'X-Admin-Token': token } });
    const json = await r.json();
    if (!json.success) throw new Error(json.error || 'Request failed');
    allReports = json.reports || [];
    window.dlog('Reports: loaded ' + allReports.length, 'info');
    renderReports();
  } catch (e) {
    list.innerHTML = `<p style="color:#ef4444;font-size:13px;padding:14px;">Failed to load reports: ${PC.esc(e.message)}</p>`;
    window.dlog('Reports: load error — ' + e.message, 'error');
  }
}

// Switch between the triage queue and the record-only welfare log.
function switchReportsTab(tab) {
  reportsTab = tab;
  document.querySelectorAll('.rpt-tab-btn').forEach(b => b.classList.toggle('active', b.dataset.rtab === tab));
  // Filters only make sense for the triage queue.
  const f = document.getElementById('reportsFilters');
  if (f) f.style.display = (tab === 'queue') ? 'flex' : 'none';
  renderReports();
}

// Short date like the Applications table (D/MM/YY) — reuses formatAppDateShort().
function reportDate(iso) { return iso ? formatAppDateShort(iso) : '—'; }

function renderReports() {
  const list  = document.getElementById('reportsList');
  const empty = document.getElementById('reportsEmpty');
  const head  = document.getElementById('reportsHeaderRow');
  const count = document.getElementById('rptCount');
  if (!list) return;

  // Tab counters (queue counts only records still open — that is the triage workload).
  const queueAll   = allReports.filter(r => r.report_type === 'platform');
  const welfareAll = allReports.filter(r => r.report_type === 'welfare');
  const qc = document.getElementById('rptCountQueue');
  const wc = document.getElementById('rptCountWelfare');
  if (qc) qc.textContent = queueAll.filter(r => r.status === 'open').length || '';
  if (wc) wc.textContent = welfareAll.length || '';

  const th = 'font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#7a6d5f;';
  list.innerHTML = '';

  // ---------- WELFARE LOG: record only, no triage actions ----------
  // data-rtab lets the TASK-C1b mobile min-width CSS apply a different
  // floor per tab (the welfare grid has fewer/narrower columns than the
  // queue grid and would scroll unnecessarily at the queue's wider floor).
  head.setAttribute('data-rtab', reportsTab);
  list.setAttribute('data-rtab', reportsTab);
  if (reportsTab === 'welfare') {
    const cols = '1fr 1.6fr 2.6fr 1fr';
    head.style.cssText = `display:grid;grid-template-columns:${cols};gap:0;background:#f0ece7;border-radius:8px 8px 0 0;padding:10px 16px;border-bottom:2px solid #d7bd88;`;
    head.innerHTML = ['Type', 'Target', 'Details', 'Date'].map(h => `<div style="${th}">${h}</div>`).join('');
    if (count) count.textContent = welfareAll.length + ' record' + (welfareAll.length !== 1 ? 's' : '');
    if (!welfareAll.length) { empty.style.display = 'block'; return; }
    empty.style.display = 'none';
    welfareAll.forEach((r, idx) => {
      const row = document.createElement('div');
      row.style.cssText = `display:grid;grid-template-columns:${cols};gap:0;padding:13px 16px;background:${idx % 2 === 0 ? '#fff' : '#faf8f5'};border-bottom:1px solid #ece7e0;align-items:center;`;
      row.innerHTML = `
        <div><span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;background:#f4f1ea;color:#7a6d5f;border:1px solid #d7bd88;"><i class="fas fa-paw" style="font-size:10px;"></i>Welfare</span></div>
        <div style="font-size:14px;color:#2c2416;">${PC.esc(r.target_label || r.target_id)}<div style="font-size:11px;color:#a89c8a;">${PC.esc(r.target_type)} · ${PC.esc(r.target_id)}</div></div>
        <div style="font-size:13px;color:#7a6d5f;">${PC.esc(r.description || '')}</div>
        <div style="font-size:13px;color:#7a6d5f;">${reportDate(r.created_at)}</div>`;
      list.appendChild(row);
    });
    return;
  }

  // ---------- QUEUE: user reports + system flags (platform type) ----------
  const statusFilter = document.getElementById('rptStatusFilter')?.value || '';
  const sourceFilter = document.getElementById('rptSourceFilter')?.value || '';
  let rows = queueAll;
  if (statusFilter) rows = rows.filter(r => r.status === statusFilter);
  if (sourceFilter) rows = rows.filter(r => r.source === sourceFilter);

  const cols = '1.1fr 1fr 1.7fr 1.5fr 1fr .9fr 56px';
  head.style.cssText = `display:grid;grid-template-columns:${cols};gap:0;background:#f0ece7;border-radius:8px 8px 0 0;padding:10px 16px;border-bottom:2px solid #d7bd88;`;
  head.innerHTML = ['Source', 'Type', 'Target', 'Category', 'Status', 'Date', 'Actions'].map(h => `<div style="${th}">${h}</div>`).join('');
  if (count) count.textContent = rows.length + ' result' + (rows.length !== 1 ? 's' : '');
  if (!rows.length) { empty.style.display = 'block'; return; }
  empty.style.display = 'none';

  rows.forEach((r, idx) => {
    const ss = REPORT_STATUS_STYLES[r.status] || REPORT_STATUS_STYLES.open;
    const isSystem = r.source === 'system_flag';
    const idEsc = PC.esc(r.report_id);
    const category = isSystem ? (REPORT_DETECTOR_LABELS[r.category] || r.category) : (r.category_label || r.category);

    // Popover actions: every status except the current one (reopen included), same buttons as Applications.
    let actions = '';
    if (r.status !== 'reviewed')  actions += `<button class="pc-app-actions__btn" onclick="setReportStatus('${idEsc}','reviewed')" title="Mark reviewed"><i class="ph ph-eye"></i></button>`;
    if (r.status !== 'actioned')  actions += `<button class="pc-app-actions__btn pc-app-actions__btn--approve" onclick="setReportStatus('${idEsc}','actioned')" title="Mark actioned"><i class="ph-fill ph-check-circle"></i></button>`;
    if (r.status !== 'dismissed') actions += `<button class="pc-app-actions__btn pc-app-actions__btn--reject" onclick="setReportStatus('${idEsc}','dismissed')" title="Dismiss"><i class="ph-fill ph-x-circle"></i></button>`;
    if (r.status !== 'open')      actions += `<button class="pc-app-actions__btn" onclick="setReportStatus('${idEsc}','open')" title="Reopen"><i class="ph ph-arrow-counter-clockwise"></i></button>`;

    // Detail block: what was said, the merchant's reply (context_token'd response), and the hold flag.
    const merchantBlock = isSystem ? '' : (r.merchant_context
      ? `<div style="margin-top:8px;"><span style="${th}">Merchant response</span><div style="margin-top:4px;background:#f9f7f2;border-left:3px solid #d7bd88;border-radius:6px;padding:9px 12px;font-size:13px;color:#2c2416;white-space:pre-wrap;">${PC.esc(r.merchant_context)}</div></div>`
      : `<div style="margin-top:8px;font-size:12px;color:#a89c8a;">No merchant response yet.</div>`);
    const holdBadge = r.holds_listing
      ? `<span style="display:inline-block;margin-left:8px;padding:2px 8px;border-radius:12px;font-size:10.5px;font-weight:700;background:#fdecea;color:#c0392b;border:1px solid #c0392b;">Holding listing</span>` : '';
    const reporter = isSystem ? 'Automatic flag' : ('Reporter: ' + PC.esc(r.reporter_id || 'unknown'));

    const row = document.createElement('div');
    row.dataset.reportId = r.report_id;
    row.style.cssText = `display:grid;grid-template-columns:${cols};gap:0;padding:13px 16px;background:${idx % 2 === 0 ? '#fff' : '#faf8f5'};border-bottom:1px solid #ece7e0;align-items:center;`;
    row.innerHTML = `
      <div style="font-size:13px;font-weight:600;color:#10214b;">${isSystem ? 'System flag' : 'User report'}</div>
      <div style="font-size:13px;color:#7a6d5f;">Platform</div>
      <div style="font-size:14px;color:#2c2416;">${PC.esc(r.target_label || r.target_id)}<div style="font-size:11px;color:#a89c8a;">${PC.esc(r.target_type)} · ${PC.esc(r.target_id)}</div></div>
      <div style="font-size:13px;color:#2c2416;">${PC.esc(category)}</div>
      <div><span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:.04em;background:${ss.bg};color:${ss.text};border:1px solid ${ss.border};"><i class="fas ${ss.icon}" style="font-size:10px;"></i>${PC.esc(r.status.toUpperCase())}</span></div>
      <div style="font-size:13px;color:#7a6d5f;">${reportDate(r.created_at)}</div>
      <div class="pc-app-actions" data-code="${idEsc}">
        <button type="button" class="pc-app-actions__dot" onclick="toggleAppActionsPanel(event, '${idEsc}')" title="Actions"><i class="fas fa-ellipsis-vertical"></i></button>
        <div class="pc-app-actions__panel" data-code="${idEsc}">${actions}</div>
      </div>
      <div style="grid-column:1 / -1;margin-top:10px;padding-top:10px;border-top:1px dashed #ece7e0;">
        <div style="font-size:12px;color:#8b7d6b;">${reporter}${holdBadge}</div>
        <div style="margin-top:6px;font-size:13px;color:#2c2416;white-space:pre-wrap;">${PC.esc(r.description || '')}</div>
        ${merchantBlock}
      </div>`;
    list.appendChild(row);
  });
}

// Change one record's status. Dismissing a report with holds_listing:true also releases the
// listing server-side (clears held_for_review); the response tells us so we can say it.
async function setReportStatus(reportId, status) {
  try {
    const token = getAdminToken();
    const fd = new FormData();
    fd.append('action', 'update');
    fd.append('report_id', reportId);
    fd.append('status', status);
    fd.append('token', token);
    const r = await fetch('reports-admin.php', { method: 'POST', headers: { 'X-Admin-Token': token }, body: fd });
    const json = await r.json();
    if (!json.success) throw new Error(json.error || 'Update failed');
    // Replace the record in memory and re-render without a second fetch.
    allReports = allReports.map(x => x.report_id === reportId ? json.report : x);
    renderReports();
    const msg = document.getElementById('reportsMsg');
    if (msg) {
      msg.textContent = 'Report marked ' + status + '.' + (json.hold_cleared === true ? ' The listing is no longer held for review.' : '');
      msg.style.display = 'block';
      setTimeout(() => { msg.style.display = 'none'; }, 5000);
    }
    window.dlog('Reports: ' + reportId + ' -> ' + status + (json.hold_cleared === true ? ' (hold cleared)' : ''));
  } catch (e) {
    alert('Update failed: ' + e.message);
  }
}
// TASK-C1b END ================================================================

// ============================================================
// BOT PROFILE (v2.0)
// ============================================================
```
<!-- TASK-C1b END -->

### `admin-dashboard.html` → live path `admin/index.html` — edit 6 of 6 note

Edit 6 is the mobile min-width CSS shown inside edit 3 above (both live in the same `<style>` block,
immediately after the tab CSS). It is counted as its own numbered edit in Files Changed because it is a
distinct, separately-added anchored block from this task's self-audit, not part of the original Reports-tab
styling first written for C1b.

## New files
All six are brand-new — no existing file to merge against — and are in `Rehoming-Platform-C1a-C1b.zip` at
the exact live paths below. No code is pasted for these per the current hand-in rules; what each file does,
including the audit fixes made inside it, is summarized in one sentence:

- `admin/report-modal.js` — New file — in the zip. Shared two-path Report modal (Platform issue form /
  Welfare hotline routing), built to Design System Section 5's modal shell and Section 24's dropdown shell,
  with focus management and a dedupe-safe welfare log write; corrected country/region hotline data for
  AU NSW/WA, UK/Scotland, and Alberta during self-audit.
- `admin/reports-lib.php` — New file — in the zip. Shared library: Report record storage (one JSON file per
  record, `LOCK_EX`), response-token issuing/expiry, `held_for_review` clearing, merchant email composition;
  `.htaccess` write corrected to valid single-syntax `Deny from all` during self-audit.
- `admin/report-submit.php` — New file — in the zip. Public POST endpoint the modal calls to write a Report
  record (platform or welfare), enforcing the `is_test_account` skip and rate limiting.
- `admin/reports-admin.php` — New file — in the zip. Admin-only endpoint (`chatRequireAdmin()`-gated) behind
  the Reports queue: list all records, update a record's status, clear `held_for_review` on dismiss.
- `admin/report-respond.php` — New file — in the zip. Public token-gated endpoint behind the merchant
  response page: shows the report summary, accepts one free-text response tied to the token.
- `report/respond/index.html` — New file — in the zip. Standalone page the merchant reaches from the
  notification email to add context to a report; `no-referrer` meta added during self-audit since the token
  credential lives in the page's own URL. **New top-level folder `report/` — see the flagged note under
  Files Changed above.**

## Bugs Found Outside Task Scope
- The `showToast()` call noted as a known open item in `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`'s v2.2 section is
  confirmed still present and was not touched (out of scope for C1a/C1b) — the Reports queue deliberately
  avoids calling it and uses its own inline `#reportsMsg` banner instead, cloned from patterns already in the
  file, so this task never exercises the missing function.
- Task 4's compact-mobile-layout work for the Applications panel (`.pc-app-row`) is scoped only to that
  panel's own row class and is not a mechanism other panels inherit automatically. This directly caused a
  real defect in this task's own new Reports panel (the mobile squeeze described in edit 3 of 6 above), so it
  was fixed within this task's own files using the dashboard's other, still-functional responsive mechanism
  (`.pc-apps-scroll{overflow-x:auto}` + an explicit min-width) rather than building a new stacked/accordion UI
  pattern. Flagging for the CEO/Design: if Reports should instead get the same stacked/accordion mobile
  treatment as Applications, that is a larger follow-up outside what this task's file list authorized.
- No other bugs found outside this task's scope.
- **Northern Ireland's animal-cruelty reporting route** could not be verified to a specific phone line (the
  authoritative nidirect page did not return usable content during a web search) — no NI-specific hotline
  text was added rather than guessing a number; NI visitors currently see the base UK (RSPCA England/Wales +
  Scottish SPCA) card. Flagging in case someone with access to that page can supply the real number.
- **Files that would have made this task more complete, not in the original file request:** `chat-config.php`
  (referenced by filename only — `chatRequireAdmin()`'s real implementation; `reports-admin.php` was built
  against its documented signature, never its source); `chat-mail.php`/`contact-config.php` (the task doc
  names these as the mailer to reuse; `pc_reports_send_mail()` in `reports-lib.php` is a clearly marked single
  adapter function pending the real file's call signature); the account-record schema (whatever file defines
  `window.PC_ACCOUNT`/`$_SESSION['pc_account']` — `pc_current_account()` in `reports-lib.php` is a documented
  stub); `publish-pet.php` or wherever `pet.json` is written (to confirm `held_for_review` is written in
  exactly the shape `pc_listing_clear_hold()` expects — this task only ever read that contract from the task
  doc, never from Task C2's own code).
- **Merchant response link expiry** is set to 30 days (`PC_REPORT_TOKEN_TTL_DAYS` in `reports-lib.php`) — this
  task's own call, since no number was specified anywhere in the task doc. Easy to change in one place if the
  CEO wants a different value.

## Self-Check Confirmation
- Only the files listed under **Files Changed** were touched. `admin-dashboard.html`'s pre-existing "Lavender
  Mist" background/sidebar colors (flagged in its own doc as unapproved and someone else's to fix) were left
  exactly as found — the new Reports panel's own elements (status pills, buttons, tab underline) use the Navy
  `#10214b` / Gold `#d7bd88` / Orange `#ED7624` palette per Section 15, not Lavender Mist.
- Every block above carries a `<!-- TASK-C1a START/END -->` or `<!-- TASK-C1b START/END -->` (or `//`
  equivalent) anchor comment, and every new file's own header comment states which task it belongs to.
- Every "Old code" snippet above was copied character for character from the original `pet-detail.html` /
  `admin-dashboard.html` files attached to this thread, and each was confirmed by an exact-text search to
  occur exactly once in that original file before this document was written.
- Every path in `Rehoming-Platform-C1a-C1b.zip` is listed above under **New files** with the same path, and
  every "New file" listed there is present in the zip — confirmed with `unzip -l` before sending: 6 files,
  `admin/report-modal.js`, `admin/reports-lib.php`, `admin/report-submit.php`, `admin/reports-admin.php`,
  `admin/report-respond.php`, `report/respond/index.html`. No wrapper folder; the zip root is the site root.
  No `config.php`, `pet.json`, `.env`, or anything from `applications/`/`uploads/`/`chat-media/`/`logs/` is
  included.
- **Dropdown confirmation:** this task built two dropdowns — the report category dropdown in
  `report-modal.js` and the country/region fallback pickers in the same file — and the Reports panel's two
  filter `<select>` elements are upgraded to the dashboard's existing custom-select component by the same
  `upgradeNativeSelectsToCustom()`/`initCustomSelects()` calls Applications already uses. All of these
  implement Section 24's locked "message bubble" shell (14px radius with the sharp top-right notch, layered
  shadow, fade+scale+slide, 5-item/225px cap, gold left-accent on the selected row) — confirmed against
  `PETCENTER_DESIGN_SYSTEM_v1.0.md` Section 24 line by line. The modal shell itself (separate from the
  dropdowns inside it) was checked against Section 5 and corrected during self-audit to match exactly:
  `rgba(0,0,0,0.65)` solid overlay, 12px radius, sticky header/scrolling body for the Choose/Platform/Welfare
  steps, and the dedicated success-modal layout for the confirmation step.
- **SHARED CONTRACTS built against:** Report record (all field names/enums used exactly as published, owned
  by this task itself — C1b); `held_for_review` (cleared via `pc_listing_clear_hold()`, owned by C2 —
  reconcile once C2's completion file exists and confirms the exact `pet.json` field name/location match);
  `is_test_account` (checked server-side in `report-submit.php`, owned by E5 — reconcile once E5's completion
  file exists); `showAuthPrompt(contextMessage)` (called only, owned by A4 — reconcile once A4's completion
  file exists to confirm the signature matches exactly).
- **Testing:** a real PHP 8.3 runtime and a real Chromium browser were both used (not network-mocked). 43
  backend assertions (every rejection path, the exact Report record contract shape, the merchant email's
  content/link/no-reporter-leak, the welfare path's no-token/no-extra-mail behavior, the `is_test_account`
  server-side skip, the 10/hour rate limit, the response endpoint's full state machine, the system-flag write
  path Task C2 will use, dismiss-clears-hold vs. other-statuses-keep-hold), all passing. A 24-assertion
  Playwright suite against the real `report-modal.js` covering every step, the corrected hotline data for
  UK/US/AU-NSW/CA-AB, focus-on-open, Escape-to-close, and the 390px mobile layout, all passing, plus separate
  checks for the welfare dedupe fix, the logged-out `showAuthPrompt` gate, the `is_test_account` client-side
  skip (zero network calls), and the modal opening correctly from both `pets/detail/index.html`'s own button
  and a stub reproducing B6's launch pattern. The real `admin/index.html` file was loaded in a headless
  browser (login bypassed the same way `handleLogin()` sets its token) and `switchPanel('reports')` called
  directly: zero JS errors, correct row counts for a mixed queue, the mobile min-width fix verified to
  actually trigger horizontal scroll without the page ballooning, and the rail button confirmed present in
  the cloned mobile hamburger menu. Screenshots in `screenshots/`: `C1a-01` through `C1a-05` (Choose/dropdown/
  form/success/mobile), `C1a-07-from-profile-page` (B6 stub), `C1a-welfare-{UK,US,AU-NSW,CA-AB}` (hotline
  variants), `C1b-01/02-queue-mobile` and `C1b-02-welfare-mobile` (fixed mobile layout),
  `C1b-mobile-bug-before-fix`/`-after-fix` (the isolated repro proving the defect and its fix),
  `C1b-mobile-menu-reports` (rail button cloned into the mobile drawer), plus the earlier desktop admin
  screenshots (`C1b-01-queue-default`, `C1b-02-welfare-log`, `C1b-03-filtered-system-flags`,
  `C1b-04-actions-popover-open`, `C1b-05-dismissed-hold-cleared`, `C1b-06/07/08-respond-page-*`), unaffected
  by the audit fixes so not retaken. One known cosmetic gap: Font Awesome/Phosphor icon webfonts are blocked
  in this sandbox (no network egress to cdnjs), so icon buttons render as blank squares in some admin
  screenshots — a test-environment artifact, not a code defect; the same `<i class="...">` markup Applications
  already uses successfully in production is reused unchanged.
