# Task E3 Completion — Support ticket system + FAQ

## Task Number
E3

## What Was Required
A public **Contact Support** form (category dropdown: Account issue / Verification issue / Technical problem / Other; a free-text description; the requester's username and account type attached automatically), a support queue in the admin dashboard that is its **own list** (separate from Reports and the verification queue — help requests, not judgment) and reuses the existing admin list-row pattern, and a public **FAQ page** with real copy on how verification works (B1/B2), how badges are earned (B3) and how reporting works (C1a/C1b). After CEO feedback the description field also uses the design system's Rich Text Editor, the form accepts up to 5 screenshots through the design system's File Upload Component, and nothing the requester writes is silently cut off.

## Files Changed
| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `support-v1.html` | `support/index.html` | New | No |
| `faq-v1.html` | `faq/index.html` | New | No |
| `submit-ticket-v1.php` | `support/submit-ticket.php` | New | No |
| `upload-temp-v1.php` | `support/upload-temp.php` | New | No — **not** the same file as the existing `apply/upload-temp.php` used by the adoption-application ID-upload flow; same source filename, different purpose and different live path. See note under Bugs/flags below. |
| `get-tickets-v1.php` | `admin/get-tickets.php` | New | No |
| `update-ticket-v1.php` | `admin/update-ticket.php` | New | No |
| `delete-ticket-v1.php` | `admin/delete-ticket.php` | New | No |
| `admin-dashboard-v1.html` (received) → `admin-dashboard-v2.html` (this task's output) | `admin/index.html` | Changed — 4 additions, each wrapped in `TASK-E3 START/END` anchors, nothing existing removed | **Yes** — shared with every other task touching this file (confirmed already present: C1b's Reports queue, which this task's queue clones; also touched by B2, E1, E5 per the task doc) |

**Flag — source-filename collision, not a live-path collision:** my new `upload-temp-v1.php` and the site's existing `apply/upload-temp.php` (ID-document uploads for the adoption application) share only their *source file name*; they are different files serving different pages. I kept mine at `support/upload-temp.php`, which does not touch or overwrite the existing one at `apply/upload-temp.php`. Flagging per the guide's "if the summary and your assignment disagree, write both... do not guess silently" instruction, since the delivery guide's own mapping table lists `upload-temp.php → apply/` for the *other* file.

## NEW FOLDERS REQUIRED — NOT IN EITHER SUMMARY DOC — READ BEFORE DEPLOYING

**`support/` and `faq/` do not exist anywhere in `adoption-website-project-summary.md`'s folder-structure section or in `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`.** They are brand-new top-level folders this task introduces, and the integrator must create both at the **site root** (the same level as `pets/`, `blog/`, `apply/`, `admin/`) before unzipping:

```
site root
├── support/   ← NEW — does not exist yet. Contains index.html, submit-ticket.php, upload-temp.php
├── faq/       ← NEW — does not exist yet. Contains index.html
├── admin/     ← EXISTING folder — only get-tickets.php, update-ticket.php, delete-ticket.php are new inside it
└── apply/     ← EXISTING, untouched — not the same file as support/upload-temp.php; see the flag below
```

Every `.html` page in both new folders uses the strict `index.html` deployment convention (`support/index.html`, `faq/index.html` — never `support.html`/`faq.html` as loose files), matching how every existing page on the live site is folder+`index.html`.

## Code Changes
All four blocks below are additions to `admin-dashboard-v1.html` (the file I was given to build against, already containing Task C1b's Reports queue). Nothing existing was edited or deleted — every old snippet is reproduced character-for-character from `admin-dashboard-v1.html` and confirmed to appear exactly once in it.

### `admin-dashboard-v1.html`  →  live path `admin/index.html`
**Change 1 — Support Tickets rail button.** Appended right after the existing Reports rail button's own closing anchor, before the icon rail's closing `</div>`.

<!-- TASK-E3 START -->
**Old code:**
```html
      <!-- TASK-C1b END -->
    </div>
    <!-- TASK-4 END (icon rail) -->
```
**New code:**
```html
      <!-- TASK-C1b END -->
      <!-- TASK-E3 START — Support Tickets rail button (same markup as its siblings; mobile menu clones it automatically) -->
      <button class="admin-icon-rail__item" data-panel="supportTickets" onclick="switchPanel('supportTickets')" title="Support Tickets"><i class="fas fa-headset"></i></button>
      <!-- TASK-E3 END -->
    </div>
    <!-- TASK-4 END (icon rail) -->
```
<!-- TASK-E3 END -->

### `admin-dashboard-v1.html`  →  live path `admin/index.html`
**Change 2 — Support Tickets panel markup.** Appended right after Task C1b's Reports panel closes, before the Reviews panel starts.

<!-- TASK-E3 START -->
**Old code:**
```html
    <!-- TASK-C1b END -->

    <!-- REVIEWS PANEL -->
```
**New code:**
```html
    <!-- TASK-C1b END -->
    <!-- TASK-E3 START — SUPPORT TICKETS PANEL. Row pattern cloned from Task C1b's Reports panel (header row
         rendered by JS, bordered status pills, .pc-app-actions popover, refresh + message banner), which
         itself clones Applications. Its own queue on purpose: help requests, not judgment (separate from
         Reports and the verification queue). Native selects are upgraded to the Section 24 custom dropdown
         by upgradeNativeSelectsToCustom() when the panel opens, same as Applications/Reports. -->
    <div id="supportTicketsPanel" class="admin-panel" style="display:none;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;flex-wrap:wrap;gap:10px;">
        <div>
          <h2 style="font-family:'Cormorant Garamond',serif;color:#10214b;margin-bottom:6px;">Support Tickets</h2>
          <p style="color:#888;font-size:14px;">Help requests from merchants and visitors, newest first.</p>
        </div>
        <button onclick="loadSupportTickets()" class="pc-btn pc-btn--ghost"><i class="fas fa-rotate-right"></i> Refresh</button>
      </div>

      <div style="display:flex;gap:14px;margin:14px 0 20px;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:1;min-width:180px;">
          <label class="pc-form-label" style="margin-bottom:6px;">Filter by Status</label>
          <select id="ticketStatusFilter" class="pc-form-input" style="cursor:pointer;" onchange="renderSupportTickets()">
            <option value="">All Statuses</option>
            <option value="OPEN">Open</option>
            <option value="PENDING">Pending</option>
            <option value="IN_PROGRESS">In Progress</option>
            <option value="COMPLETED">Completed</option>
            <option value="CANCELLED">Cancelled</option>
          </select>
        </div>
        <div style="flex:1;min-width:180px;">
          <label class="pc-form-label" style="margin-bottom:6px;">Filter by Category</label>
          <select id="ticketCategoryFilter" class="pc-form-input" style="cursor:pointer;" onchange="renderSupportTickets()">
            <option value="">All Categories</option>
            <option value="account_issue">Account issue</option>
            <option value="verification_issue">Verification issue</option>
            <option value="technical_problem">Technical problem</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div style="flex:0 0 auto;"><div id="ticketCount" style="font-size:13px;color:#8b7d6b;padding:12px 0;"></div></div>
      </div>

      <div id="ticketsMsg" role="status" style="display:none;margin-bottom:12px;padding:10px 14px;border-radius:8px;background:#e6f7ef;border:1px solid #0d7c4a;color:#0d7c4a;font-size:13px;"></div>

      <!-- Bulk bar: same markup as Applications' #appBulkActionBar -->
      <div id="ticketBulkActionBar" style="display:none;align-items:center;gap:10px;margin-bottom:12px;padding:8px 12px;background:#fff8f2;border:1px solid rgba(237,118,36,0.25);border-radius:8px;">
        <span id="ticketBulkSelectedCount" style="font-size:12px;color:#7a6d5f;font-weight:600;"></span>
        <button onclick="deleteTicketsBulk()" class="pc-btn" style="margin-left:auto;padding:6px 12px;font-size:12px;background:linear-gradient(135deg,#c0392b,#a93226);">Delete Permanently</button>
      </div>

      <div class="pc-apps-scroll">
        <div id="ticketsHeaderRow"></div>
        <div id="ticketsList"></div>
        <div id="ticketsEmpty" style="display:none;padding:48px 24px;text-align:center;background:#fff;border-radius:0 0 8px 8px;border:1px solid #eee;border-top:none;">
          <p style="color:#aaa;font-size:14px;">No support tickets match the current filters.</p>
        </div>
      </div>
    </div>
    <!-- TASK-E3 END -->


    <!-- REVIEWS PANEL -->
```
<!-- TASK-E3 END -->

### `admin-dashboard-v1.html`  →  live path `admin/index.html`
**Change 3 — switchPanel() hook.** Appended inside `switchPanel(panel)`, right after Task C1b's own hook for the Reports panel.

<!-- TASK-E3 START -->
**Old code:**
```js
    loadReports();
  }
  // TASK-C1b END
}
```
**New code:**
```js
    loadReports();
  }
  // TASK-C1b END
  // TASK-E3 START — Support Tickets panel: upgrade its filter selects to the custom dropdown (rule 9a), then load
  if (panel === 'supportTickets') {
    upgradeNativeSelectsToCustom();
    initCustomSelects();
    loadSupportTickets();
  }
  // TASK-E3 END
}
```
<!-- TASK-E3 END -->

### `admin-dashboard-v1.html`  →  live path `admin/index.html`
**Change 4 — queue logic, detail modal, image preview.** Appended immediately after Task C1b's closing line in the main script block.

<!-- TASK-E3 START -->
**Old code:**
```js
// TASK-C1b END ================================================================
```
**New code:**
```js
// TASK-C1b END ================================================================

// TASK-E3 START ===============================================================
// SUPPORT TICKETS QUEUE (Task E3). Reads/updates tickets through get-tickets.php / update-ticket.php /
// delete-ticket.php. Row markup, bordered status pills and the 3-dot popover are clones of Task C1b's
// renderReports() above (itself a clone of renderApplications()); auth uses getAdminToken() exactly like
// loadReports().
// =============================================================================
let allSupportTickets = [];
const selectedTicketIds = new Set();   // bulk-delete selection, same idea as selectedApplicationCodes

// Five-state system (Design System Sec.18 names support tickets) in C1b's bordered-pill shape.
const TICKET_STATUS_STYLES = {
  OPEN:        { bg: '#f0ece7', border: '#a89c8a', text: '#7a6d5f', icon: 'fa-circle',       label: 'Open' },
  PENDING:     { bg: '#fffbe6', border: '#f5a623', text: '#b87a00', icon: 'fa-clock',        label: 'Pending' },
  IN_PROGRESS: { bg: '#eaf1fb', border: '#3b6fb6', text: '#2c5a9b', icon: 'fa-spinner',      label: 'In Progress' },
  COMPLETED:   { bg: '#e6f7ef', border: '#0d7c4a', text: '#0d7c4a', icon: 'fa-circle-check', label: 'Completed' },
  CANCELLED:   { bg: '#fdecea', border: '#c0392b', text: '#c0392b', icon: 'fa-circle-xmark', label: 'Cancelled' },
};
// Kept as the names the detail modal below already uses.
const ticketStatusStyles = TICKET_STATUS_STYLES;
const ticketCategoryLabels = {
  account_issue: 'Account issue', verification_issue: 'Verification issue',
  technical_problem: 'Technical problem', other: 'Other',
};

// Fetch every ticket, then render. Same fetch/token pattern as loadReports().
async function loadSupportTickets() {
  const list = document.getElementById('ticketsList');
  if (!list) return;
  list.innerHTML = '<div style="color:#a89c8a;font-size:13px;padding:14px;">Loading...</div>';
  try {
    const token = getAdminToken();
    const r = await fetch('get-tickets.php', { headers: { 'X-Admin-Token': token } });
    const json = await r.json();
    if (json.success === false) throw new Error(json.error || 'Request failed');
    allSupportTickets = Array.isArray(json.tickets) ? json.tickets : [];
    selectedTicketIds.clear();
    updateTicketBulkActionBar();
    window.dlog('Tickets: loaded ' + allSupportTickets.length, 'info');
    renderSupportTickets();
  } catch (e) {
    list.innerHTML = `<p style="color:#ef4444;font-size:13px;padding:14px;">Failed to load tickets: ${PC.esc(e.message)}</p>`;
    window.dlog('Tickets: load error — ' + e.message, 'error');
  }
}

// Plain-text excerpt of the (server-sanitized) rich-text description — DOMParser never runs scripts.
function ticketExcerpt(html) {
  const text = new DOMParser().parseFromString((html || '').replace(/<\/(p|li|blockquote|div)>/gi, ' '), 'text/html').body.textContent.replace(/\s+/g, ' ').trim();
  return text.length > 160 ? text.slice(0, 160) + '…' : text;
}

function renderSupportTickets() {
  const list  = document.getElementById('ticketsList');
  const empty = document.getElementById('ticketsEmpty');
  const head  = document.getElementById('ticketsHeaderRow');
  const count = document.getElementById('ticketCount');
  if (!list) return;

  const statusFilter = document.getElementById('ticketStatusFilter')?.value || '';
  const catFilter    = document.getElementById('ticketCategoryFilter')?.value || '';
  let rows = allSupportTickets;
  if (statusFilter) rows = rows.filter(t => (t.status || '') === statusFilter);
  if (catFilter)    rows = rows.filter(t => (t.category || '') === catFilter);

  // Header row is rendered by JS (same as renderReports) so the select-all checkbox shares the grid.
  const th = 'font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#7a6d5f;';
  const cols = '32px 1.6fr 1.3fr 1.1fr .9fr 56px';
  head.style.cssText = `display:grid;grid-template-columns:${cols};gap:0;background:#f0ece7;border-radius:8px 8px 0 0;padding:10px 16px;border-bottom:2px solid #d7bd88;`;
  head.innerHTML = `<div><input type="checkbox" id="ticketSelectAllCheckbox" style="width:16px;height:16px;cursor:pointer;" onchange="toggleSelectAllTickets(this.checked)"></div>`
    + ['Requester', 'Category', 'Status', 'Date', 'Actions'].map(h => `<div style="${th}">${h}</div>`).join('');

  list.innerHTML = '';
  if (count) count.textContent = rows.length + ' result' + (rows.length !== 1 ? 's' : '');
  if (!rows.length) { empty.style.display = 'block'; return; }
  empty.style.display = 'none';

  rows.forEach((t, idx) => {
    const status = (t.status || 'OPEN').toUpperCase();
    const ss = TICKET_STATUS_STYLES[status] || TICKET_STATUS_STYLES.OPEN;
    const idEsc = PC.esc(t.ticket_id || '');
    const selected = selectedTicketIds.has(t.ticket_id);
    const imgCount = (t.images && t.images.length) || 0;

    // Popover actions: View, then every status except the current one — same buttons idea as Reports.
    let actions = `<button class="pc-app-actions__btn" onclick="viewSupportTicket('${idEsc}')" title="View / add notes"><i class="ph ph-eye"></i></button>`;
    if (status !== 'IN_PROGRESS') actions += `<button class="pc-app-actions__btn" onclick="setTicketStatus('${idEsc}','IN_PROGRESS')" title="Mark in progress"><i class="ph ph-spinner-gap"></i></button>`;
    if (status !== 'COMPLETED')   actions += `<button class="pc-app-actions__btn pc-app-actions__btn--approve" onclick="setTicketStatus('${idEsc}','COMPLETED')" title="Mark completed"><i class="ph-fill ph-check-circle"></i></button>`;
    if (status !== 'CANCELLED')   actions += `<button class="pc-app-actions__btn pc-app-actions__btn--reject" onclick="setTicketStatus('${idEsc}','CANCELLED')" title="Cancel ticket"><i class="ph-fill ph-x-circle"></i></button>`;
    if (status !== 'OPEN')        actions += `<button class="pc-app-actions__btn" onclick="setTicketStatus('${idEsc}','OPEN')" title="Reopen"><i class="ph ph-arrow-counter-clockwise"></i></button>`;

    const row = document.createElement('div');
    row.dataset.ticketId = t.ticket_id;
    row.className = 'pc-app-row' + (selected ? ' pc-app-row--selected' : '');
    row.style.cssText = `display:grid;grid-template-columns:${cols};gap:0;padding:13px 16px;background:${selected ? '' : (idx % 2 === 0 ? '#fff' : '#faf8f5')};border-bottom:1px solid #ece7e0;align-items:center;`;
    row.innerHTML = `
      <div><input type="checkbox" class="ticket-row-checkbox" data-id="${idEsc}" ${selected ? 'checked' : ''} style="width:16px;height:16px;cursor:pointer;"></div>
      <div style="font-size:14px;color:#2c2416;">${PC.esc(t.username || 'Unknown')}<div style="font-size:11px;color:#a89c8a;text-transform:capitalize;">${PC.esc(t.account_type || '')} account · ${idEsc}</div></div>
      <div style="font-size:13px;color:#2c2416;">${PC.esc(ticketCategoryLabels[t.category] || t.category || '—')}</div>
      <div><span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:.04em;background:${ss.bg};color:${ss.text};border:1px solid ${ss.border};"><i class="fas ${ss.icon}" style="font-size:10px;"></i>${ss.label}</span></div>
      <div style="font-size:13px;color:#7a6d5f;">${t.submitted_at ? formatAppDateShort(t.submitted_at) : '—'}</div>
      <div class="pc-app-actions" data-code="${idEsc}">
        <button type="button" class="pc-app-actions__dot" onclick="toggleAppActionsPanel(event, '${idEsc}')" title="Actions"><i class="fas fa-ellipsis-vertical"></i></button>
        <div class="pc-app-actions__panel" data-code="${idEsc}">${actions}</div>
      </div>
      <div style="grid-column:1 / -1;margin-top:10px;padding-top:10px;border-top:1px dashed #ece7e0;font-size:13px;color:#2c2416;">
        ${PC.esc(ticketExcerpt(t.description))}
        ${imgCount ? `<span style="margin-left:8px;font-size:11.5px;color:#7a6d5f;"><i class="fas fa-image" style="font-size:10px;"></i> ${imgCount} attached</span>` : ''}
      </div>`;
    row.querySelector('.ticket-row-checkbox').addEventListener('change', (e) => {
      if (e.target.checked) selectedTicketIds.add(t.ticket_id); else selectedTicketIds.delete(t.ticket_id);
      row.classList.toggle('pc-app-row--selected', e.target.checked);
      updateTicketBulkActionBar();
    });
    list.appendChild(row);
  });
}

// Quick status change from the row popover. Sends no admin_notes, so update-ticket.php leaves notes untouched.
async function setTicketStatus(ticketId, status) {
  try {
    const token = getAdminToken();
    const fd = new FormData();
    fd.append('ticket_id', ticketId);
    fd.append('status', status);
    fd.append('token', token);
    const r = await fetch('update-ticket.php', { method: 'POST', headers: { 'X-Admin-Token': token }, body: fd });
    const json = await r.json();
    if (!json.success) throw new Error(json.error || 'Update failed');
    allSupportTickets = allSupportTickets.map(x => x.ticket_id === ticketId ? json.ticket : x);
    renderSupportTickets();
    const msg = document.getElementById('ticketsMsg');
    if (msg) {
      msg.textContent = 'Ticket ' + ticketId + ' marked ' + (TICKET_STATUS_STYLES[status]?.label || status).toLowerCase() + '.';
      msg.style.display = 'block';
      setTimeout(() => { msg.style.display = 'none'; }, 5000);
    }
  } catch (e) {
    alert('Update failed: ' + e.message);
  }
}

// Select-all / bulk bar / bulk delete — same behavior as the Applications panel's.
function toggleSelectAllTickets(checked) {
  document.querySelectorAll('.ticket-row-checkbox').forEach(cb => {
    cb.checked = checked;
    cb.closest('.pc-app-row')?.classList.toggle('pc-app-row--selected', checked);
    if (checked) selectedTicketIds.add(cb.dataset.id); else selectedTicketIds.delete(cb.dataset.id);
  });
  updateTicketBulkActionBar();
}
function updateTicketBulkActionBar() {
  const bar = document.getElementById('ticketBulkActionBar');
  if (!bar) return;
  const n = selectedTicketIds.size;
  bar.style.display = n > 0 ? 'flex' : 'none';
  if (n > 0) document.getElementById('ticketBulkSelectedCount').textContent = n + ' selected';
}
async function deleteTicketsBulk() {
  const ids = Array.from(selectedTicketIds);
  if (!ids.length) return;
  if (!confirm(`Permanently delete ${ids.length} ticket(s)? This cannot be undone.`)) return;
  try {
    const token = getAdminToken();
    const r = await fetch('delete-ticket.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Admin-Token': token },
      body: JSON.stringify({ ticketIds: ids }),
    });
    const json = await r.json();
    if (json.failed && json.failed.length) alert(`${json.failed.length} ticket(s) could not be deleted: ${json.failed.join(', ')}`);
    selectedTicketIds.clear();
    updateTicketBulkActionBar();
    loadSupportTickets();
    PC.toast(`${(json.deleted || []).length} ticket(s) deleted`, 'success');
  } catch (e) {
    alert('Bulk delete failed: ' + e.message);
  }
}

// View modal reuses the site's real .pc-modal-shell/.pc-modal-box/.pc-modal__scroll
// classes and the exact header markup pattern viewApplication() already uses
// (#viewApplicationModal, above) — same reusable modal shell, not a new one,
// per Warning & Instructions rule 7.
function viewSupportTicket(ticketId) {
  const ticket = allSupportTickets.find(t => t.ticket_id === ticketId);
  if (!ticket) return;
  const status = (ticket.status || 'OPEN').toUpperCase();

  document.getElementById('ticketDetailModal')?.remove(); // remove any stale copy first
  const modal = document.createElement('div');
  modal.id = 'ticketDetailModal';
  modal.className = 'pc-modal-shell';
  modal.style.cssText = 'position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(16,33,75,0.65);display:flex;align-items:center;justify-content:center;z-index:9999;padding:20px;';
  modal.innerHTML = `
    <div class="pc-modal-box" style="width:100%;max-width:520px;background:#fff;border-radius:14px;box-shadow:0 20px 60px rgba(16,33,75,0.25);">
      <div style="display:flex;justify-content:space-between;align-items:center;padding:20px 26px;background:#fff;border-bottom:2px solid #d7bd88;flex-shrink:0;">
        <h2 style="font-family:'Cormorant Garamond',serif;font-size:22px;color:#10214b;margin:0;">Ticket ${PC.esc(ticket.ticket_id)}</h2>
        <button onclick="document.getElementById('ticketDetailModal').remove()" style="background:#f0ece8;border:none;width:34px;height:34px;border-radius:8px;cursor:pointer;font-size:20px;color:#7a6d5f;display:flex;align-items:center;justify-content:center;">&times;</button>
      </div>
      <div class="pc-modal__scroll">
        <div style="padding:26px;">
          <p style="font-size:13px;color:#7a6d5f;margin-bottom:4px;"><strong>Requester:</strong> ${PC.esc(ticket.username || '')} (${PC.esc(ticket.account_type || '')})</p>
          <p style="font-size:13px;color:#7a6d5f;margin-bottom:4px;"><strong>Category:</strong> ${PC.esc(ticketCategoryLabels[ticket.category] || ticket.category || '')}</p>
          <p style="font-size:13px;color:#7a6d5f;margin-bottom:14px;"><strong>Submitted:</strong> ${ticket.submitted_at ? formatAppDateShort(ticket.submitted_at) : '—'}</p>
          <p style="font-size:13px;font-weight:700;color:#10214b;margin-bottom:6px;">Description</p>
          <!-- TASK-E3: image URLs are ../support/tickets/... because this file lives in /admin/ and tickets are stored in the sibling /support/tickets/ (same sibling-directory rule the PHP endpoints use via dirname(__DIR__)).
               ticket.description is now Quill RTE HTML, sanitized server-side
               in submit-ticket.php (Design System Section 25's explicit integration note:
               "this field's output is HTML, not plain text... a helper that HTML-escapes
               everything will silently break the formatting"). Rendered here as real HTML
               — via PC.esc() it would show literal "<p>" tags instead of the requester's
               actual formatting. This is admin-only internal viewing of data this same
               backend already sanitized on the way in, not raw third-party HTML. -->
          <div style="font-size:14px;color:#2c2416;line-height:1.6;margin-bottom:18px;" class="ticket-description-rendered">${ticket.description || '<span style=\"color:#aaa;\">(no description)</span>'}</div>
          ${(ticket.images && ticket.images.length) ? `
          <p style="font-size:13px;font-weight:700;color:#10214b;margin-bottom:6px;">Attached screenshots (${ticket.images.length})</p>
          <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:18px;">
            ${ticket.images.map(img => `<img src="../support/tickets/${encodeURIComponent(ticket.ticket_id)}/images/${encodeURIComponent(img)}" alt="Attached screenshot" style="width:76px;height:76px;object-fit:cover;border-radius:6px;border:1px solid #ddd;cursor:pointer;" onclick="openAdminImagePreview('../support/tickets/${PC.esc(ticket.ticket_id)}/images/${PC.esc(img)}')">`).join('')}
          </div>` : ''}
          <label class="pc-form-label" style="margin-bottom:6px;">Status</label>
          <select id="ticketDetailStatus" class="pc-form-input" style="margin-bottom:14px;">
            ${Object.keys(ticketStatusStyles).map(s => `<option value="${s}" ${s === status ? 'selected' : ''}>${ticketStatusStyles[s].label}</option>`).join('')}
          </select>
          <label class="pc-form-label" style="margin-bottom:6px;">Internal notes (not visible to the requester)</label>
          <textarea id="ticketDetailNotes" class="pc-form-input" style="min-height:80px;resize:vertical;width:100%;">${PC.esc(ticket.admin_notes || '')}</textarea>
        </div>
        <div style="padding:0 26px 26px;display:flex;gap:10px;">
          <button onclick="saveTicketDetail('${PC.esc(ticket.ticket_id)}')" class="pc-btn pc-btn--primary" style="flex:1;">Save Changes</button>
          <button onclick="document.getElementById('ticketDetailModal').remove()" class="pc-btn pc-btn--ghost">Cancel</button>
        </div>
      </div>
    </div>
  `;
  document.body.appendChild(modal);
  // TASK-E3: upgrade the modal's Status <select> into the same custom-dropdown
  // component as every other dropdown in the project (rule 9a) — safe to call
  // again here since both functions are idempotent on already-wrapped selects
  // (see the "if (select.closest('.custom-select-wrapper')) return;" guard
  // inside upgradeNativeSelectsToCustom()).
  upgradeNativeSelectsToCustom();
  initCustomSelects();
}

// TASK-E3: minimal full-size preview for a ticket's attached screenshot thumbnails —
// same lightbox pattern as support.html's openItemPreview() (Design System Section 5,
// modal use B), a self-contained instance here since the admin dashboard's own modal
// machinery (pc-modal-shell/pc-modal-box) is already in active use by ticketDetailModal
// itself when this is opened.
function openAdminImagePreview(src) {
  document.getElementById('adminImagePreviewModal')?.remove();
  const overlay = document.createElement('div');
  overlay.id = 'adminImagePreviewModal';
  overlay.style.cssText = 'position:fixed;inset:0;background:rgba(16,33,75,0.85);z-index:10050;display:flex;align-items:center;justify-content:center;padding:24px;';
  overlay.innerHTML = `
    <div style="position:relative;max-width:90vw;max-height:90vh;">
      <button onclick="document.getElementById('adminImagePreviewModal').remove()" style="position:absolute;top:-14px;right:-14px;background:#fff;border:none;width:32px;height:32px;border-radius:50%;cursor:pointer;font-size:18px;color:#7a6d5f;box-shadow:0 2px 8px rgba(0,0,0,.2);">&times;</button>
      <img src="${src}" style="max-width:90vw;max-height:90vh;border-radius:8px;display:block;">
    </div>
  `;
  overlay.addEventListener('click', e => { if (e.target === overlay) overlay.remove(); });
  document.body.appendChild(overlay);
}

async function saveTicketDetail(ticketId) {
  const status = document.getElementById('ticketDetailStatus').value;
  const notes  = document.getElementById('ticketDetailNotes').value;
  try {
    const token = getAdminToken();
    const fd = new FormData();
    fd.append('token', token);
    fd.append('ticket_id', ticketId);
    fd.append('status', status);
    fd.append('admin_notes', notes);
    const r = await fetch('update-ticket.php', { method: 'POST', body: fd });
    const json = await r.json();
    if (!json.success) throw new Error(json.error || 'Update failed');
    document.getElementById('ticketDetailModal')?.remove();
    loadSupportTickets();
    PC.toast('Ticket updated', 'success');
  } catch (e) {
    PC.toast('Could not save ticket: ' + e.message, 'error');
  }
}
// TASK-E3 END ================================================================
```
<!-- TASK-E3 END -->

## New files
All seven are listed under Files Changed above as "New" and are inside `Rehoming-Platform-Task-E3.zip` at their live paths, rooted at the site root. Per the latest delivery correction, this completion file itself is **also** inside that same zip (at the zip root, as project meta — not a deployable site path):
- `support/index.html` — the Contact Support page.
- `faq/index.html` — the FAQ page.
- `support/submit-ticket.php` — receives a new ticket submission.
- `support/upload-temp.php` — receives each screenshot the moment it's added, returns a token (see flag above).
- `admin/get-tickets.php` — lists tickets for the dashboard queue.
- `admin/update-ticket.php` — updates a ticket's status/notes.
- `admin/delete-ticket.php` — bulk-deletes tickets.

## Bugs Found Outside Task Scope
- **Filter dropdowns vs. Section 30 (`admin-dashboard-v1.html`, Applications panel and C1b's Reports panel).** Design System Section 24 says filter/sort UI must use the Section 30 bottom sheet, but both existing panels use the generic custom-select wrapper instead. Not fixed (outside scope, and not mine to fix per Scope Lock).
- **`cats.html` footer grid.** `.pc-footer__top` is `grid-template-columns: 2fr 1fr 1fr 1.5fr` (4 tracks) but the markup has 5 columns. Not fixed — `cats.html` itself was not touched by this task.
- **Header/footer documentation is out of date with the live `cats.html`.** `PETCENTER_DESIGN_SYSTEM_v1.0.md` Section 26 shows 5 pet categories and `/rehome/`; `adoption-website-project-summary.md` Section 2.6 omits the "Golden Triangle Shopping Center, Section #257" line that `cats.html` actually has. Not fixed — both are someone else's documents.

## Self-Check Confirmation
- Touched only the files listed under Files Changed. No file outside E3's scope was opened for editing.
- Every Code Changes block is wrapped in `<!-- TASK-E3 START -->` / `<!-- TASK-E3 END -->` (or the JS `//` equivalent) and every new file's own anchor comment is intact.
- Every "Old code" snippet was copied character-for-character from `admin-dashboard-v1.html` (the file I was given) and confirmed programmatically to occur exactly once in it before being included here.
- `Rehoming-Platform-Task-E3.zip` contains exactly the 7 new files listed above, each at the live path shown, rooted at the site root, **plus this completion file at the zip root** (`unzip -l` output cross-checked against the table by hand). `admin-dashboard-v2.html` is **not** in the zip — it's a changed existing file, delivered as the anchored Code Changes above only, never pasted into a zip (per the delivery guide's own rule: a changed file in a zip would overwrite other workers' merges).
- Checked the zip for secrets: no `config.php`, `pet.json`, `.env`, nothing under `applications/` or `uploads/`, and no hardcoded API keys/passwords/tokens (grepped for common patterns — none found).
- Dropdown used: the Design System Section 24 custom-select component (`.custom-select-wrapper`/`.custom-select-trigger`/`.custom-select-options`), copied from `admin-dashboard.html`'s existing implementation — both on `support-v1.html`'s form and on the admin queue's filters/detail modal.
- Tested with Playwright against a local HTTP server with mocked `get-tickets.php`/`update-ticket.php` responses: queue renders, filters work, the 3-dot popover opens, a status change updates the row and shows the banner, the bulk-select bar appears, the detail modal's status field is a working custom dropdown, and Task C1b's Reports panel still opens afterward. Not live-tested: the real PHP endpoints (no PHP runtime in this sandbox) and Quill actually mounting (no outbound network in this sandbox — the page's own fallback to a plain textarea was exercised instead).

## SECOND VERIFICATION ROUND
Hand-in format only — **no code was changed in this round**, per instruction ("do not redo or change your work, only fix the hand-in"). This section records what changed in the paperwork:
1. Re-formatted Files Changed into the required four-column table, mapping each source file to its live path per the guide's Section 3 table (and my own judgment, documented above, for the two files the table doesn't cover: `support-v1.html`/`faq-v1.html`, plus the flagged collision on `upload-temp.php`).
2. Rebuilt Code Changes as strict Old-code/New-code pairs, each copied programmatically from `admin-dashboard-v1.html` and `admin-dashboard-v2.html` rather than retyped, and verified each old snippet is a unique exact match in the original file before including it.
3. Moved all 7 new files into `Task-E3-NewFiles.zip` at their live paths (previously they were only listed as flat source files); confirmed none of them touch `config.php`, `pet.json`, `.env`, `applications/` or `uploads/`.
4. Everything else — the GitHub/branch-based delivery note from the prior round — is superseded by `WORKER-DELIVERY-GUIDE.md` and no longer applies; no git, branch, or `reports/` artifacts are part of this hand-in.

## THIRD VERIFICATION ROUND
Hand-in packaging only — **no code was changed in this round**. This corrects a misunderstanding named across workers: new files and the completion file were being sent as separate items instead of one joined zip, and zip names weren't standardized for sorting.

1. **One zip now, not two deliverables.** `Task-E3-Completion.md` (this file) is now bundled *inside* the zip, at the zip root, alongside the new-file folders — not sent as a separate file.
2. **Zip renamed to the project+task convention.** Previously `Task-E3-NewFiles.zip`; now `Rehoming-Platform-Task-E3.zip`. "Rehoming-Platform" is taken verbatim from this task's own assignment document's file name, `Rehoming-Platform-Task-Assignments.md`, with `-Task-Assignments` dropped and `-Task-E3` appended — per the instruction to "use the exact name your project file is named as." **Flagging, not guessing silently:** the instruction gave two different example formats in the same sentence — `[Project-Name][Task-Number].zip` (concatenated, no separators) and `project-x-task-3.zip` (hyphenated, lowercase, task number alone without its letter suffix). I used the hyphenated form and kept the task's real id `E3` rather than stripping it to `3`, since this task was never assigned a bare number. If a different exact casing/separator is wanted, happy to rename — it costs nothing to redo.
3. **New folders now flagged heavily, not just listed in a table row.** Added a dedicated, prominent section right before Code Changes spelling out that `support/` and `faq/` exist in neither `adoption-website-project-summary.md` nor `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` and must be created at the site root, with a small folder tree showing exactly what goes where. The existing per-file table row for each already said "New" — the new section makes the two *entirely new top-level folders* impossible to miss.
4. **`index.html` convention re-confirmed, not changed.** Both new pages were already built and zipped as `support/index.html` and `faq/index.html` (never loose `support.html`/`faq.html`) in every prior round — restated explicitly in the new flagged section so the integrator doesn't have to cross-check.
5. **Re-confirmed nothing pending was left undocumented.** Re-read the existing Bugs Found Outside Task Scope, the `upload-temp.php` filename-collision flag, and every Self-Check "not verified" line before repackaging — all were already present from the prior round and still apply unchanged; nothing new surfaced in this pass.
