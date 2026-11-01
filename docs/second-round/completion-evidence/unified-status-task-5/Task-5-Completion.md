# Task 5 — BNPL Admin Review Panel — Completion File

## Task Number

**Task 5 — BNPL Admin Review Panel** (Unified Status Dashboard Task Tracker, v3 FINAL)

## What Was Required

A new "BNPL Applications" panel inside `admin-dashboard.html` — list, status filter, detail view,
approve/reject — matching the shape of the existing adoption Applications panel. The existing
upload/preview modal had to be reused for viewing submitted documents rather than building a new
viewer. Bulk approve/reject and a staff notes field per submission were also required. The panel
depends on Task 4's BNPL status backend (`bnpl-status-lib.php`, `update-bnpl-status.php`).

**Two scope notes, both surfaced and resolved before writing code, not discovered after:**

1. **No list/fetch endpoint existed.** Task 4 only built a per-`submission_id` read/write backend —
   nothing enumerates *which* submissions exist. The CEO explicitly authorized building this
   (`get-bnpl-applications.php`, new file, Task-5-owned) rather than guessing at scope.
2. **"Staff notes" has no home in the locked schema.** The Shared Data Contracts section fixes the
   BNPL status record's fields (`submission_id`, `status`, `approved_by`, `approved_at`,
   `rejection_notes`, `history`) for Tasks 4/5/6/7, and Task 4's completion file documents
   `update-bnpl-status.php` as its own endpoint that later tasks call, not edit. Rather than adding
   a field to that locked schema or editing Task 4's endpoint (both out of Scope Lock), staff notes
   got its own small, entirely Task-5-owned file pair (`bnpl-notes-lib.php` + `update-bnpl-notes.php`),
   mirroring Task 4's own validate-then-flock pattern for consistency (rule 7) but touching none of
   Task 4's files.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `admin-dashboard.html` | `admin/index.html` | Changed (existing) | Yes — shared with chat security work and VDM-2's dark/light mode work; all additions scoped inside `<!-- TASK-5 START -->` / `<!-- TASK-5 END -->` (HTML) and `// TASK-5 START` / `// TASK-5 END` (JS) anchor comments, named-zone `Bnpl`/`bnpl` prefix throughout |
| `get-bnpl-applications.php` | `installments/get-bnpl-applications.php` *(see path note below)* | New file | No — Task-5-owned. Reads (does not modify) Task 4's `installment_submissions.log` and calls Task 4's `bnpl_read_status()` |
| `bnpl-notes-lib.php` | `installments/bnpl-notes-lib.php` *(see path note below)* | New file | No — Task-5-owned. Requires Task 4's `bnpl-status-lib.php` for `bnpl_validate_submission_id()` only |
| `update-bnpl-notes.php` | `installments/update-bnpl-notes.php` *(see path note below)* | New file | No — Task-5-owned |

No file belonging to Task 4 (`bnpl-status-lib.php`, `update-bnpl-status.php`, `submit-installment.php`)
was modified.

**Path note (per the delivery guide's "if the summary and your assignment disagree, write both"
instruction):** The guide's generic Source→Live-Path table (Section 3) says "Any admin PHP/JS... →
`admin/` + the same file name," which would place these three files at `admin/get-bnpl-applications.php`,
`admin/bnpl-notes-lib.php`, `admin/update-bnpl-notes.php`. However, `BNPL-Path-Sync.md` — a later,
more specific, CEO-confirmed instruction that explicitly governs Tasks 4/5/6/7's BNPL files by name —
states the entire BNPL backend "lives and runs entirely self-contained on `adopt.petcenterco.com`"
under `/installments/`, lists Task 4's sibling files (`bnpl-status-lib.php`, `update-bnpl-status.php`,
`submit-installment.php`) at that exact path, and gives Task 5 itself the instruction "call
`update-bnpl-status.php` at `/installments/update-bnpl-status.php`." My three files are BNPL backend
files of the same kind (list/fetch and staff-notes endpoints sitting next to Task 4's own), not general
admin-dashboard utilities, so I've placed them under `installments/` to match their siblings and the
explicit sync doc, rather than the generic table's default. **The zip ships these files at the
`installments/` path.** Flagging the generic table's conflicting default here per the guide's own
instruction, rather than silently picking one without saying so.

**⚠️ NEW FOLDER FLAG — not in either project summary's folder structure:** I checked both
`adoption-website-project-summary.md` and `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`'s Section 1 "Server
Folder Structure" directly, and `installments/` is **not listed in either one** — it appears nowhere
in either summary's known folder tree. Its only source is `BNPL-Path-Sync.md` (a CEO-confirmed doc for
Tasks 4/5/6/7, not a revision of either project summary). **The integrator must create a brand-new
top-level folder, `installments/`, directly under the site root** (sibling to `admin/`, `pets/`,
`apply/`, etc. — i.e. `public_html/adopt/installments/`) if it does not already exist from Task 4's own
hand-in. All three of my new files, plus Task 4's own `submit-installment.php`,
`bnpl-status-lib.php`, and `update-bnpl-status.php`, belong inside it. I am not assuming Task 4 already
created this folder on the live site — flagging it here regardless so the integrator knows to create it
if it's missing, rather than silently assuming it exists.

## Code Changes

### `get-bnpl-applications.php`, `bnpl-notes-lib.php`, `update-bnpl-notes.php`

**New files — no existing code to merge; shipped in `Task-5-NewFiles.zip`** at the live paths above.
Structural lint (balanced braces/parens/brackets after stripping comments and string literals — no PHP
interpreter is available in this sandbox, same limitation Task 4 flagged) passes clean on all three.

### `admin-dashboard.html`  →  live path `admin/index.html`

**1. Nav rail button** (auto-clones into the mobile menu via the existing `renderMobileIconMenu()`,
unchanged):

<!-- TASK-5 START -->
**Old code:**
```html
      <button class="admin-icon-rail__item" data-panel="applications" onclick="switchPanel('applications')" title="Applications"><i class="fas fa-file-lines"></i></button>
      <button class="admin-icon-rail__item" data-panel="adminProfiles" onclick="switchPanel('adminProfiles')" title="Admin Profiles"><i class="fas fa-user-shield"></i></button>
```
**New code:**
```html
      <button class="admin-icon-rail__item" data-panel="applications" onclick="switchPanel('applications')" title="Applications"><i class="fas fa-file-lines"></i></button>
      <!-- TASK-5 START: BNPL Applications nav entry. Matches the existing rail buttons'
           exact shape (data-panel + onclick="switchPanel(...)" + title + Font Awesome icon,
           per rule 2's "match the existing icon style already in use on the page" clause --
           this file uses fa-* icon-font classes throughout the rail, not inline SVG, so
           this follows that established pattern rather than introducing a new one).
           renderMobileIconMenu() clones every .admin-icon-rail__item automatically, so no
           separate mobile-menu edit is needed for this button to appear there too. -->
      <button class="admin-icon-rail__item" data-panel="bnpl" onclick="switchPanel('bnpl')" title="BNPL Applications"><i class="fas fa-credit-card"></i></button>
      <!-- TASK-5 END -->
      <button class="admin-icon-rail__item" data-panel="adminProfiles" onclick="switchPanel('adminProfiles')" title="Admin Profiles"><i class="fas fa-user-shield"></i></button>
```
<!-- TASK-5 END -->

**2. New panel markup**, inserted immediately after the existing Applications panel's closing `</div>`:

<!-- TASK-5 START -->
**Old code:**
```html
        <div id="applicationsEmpty" style="display:none;padding:48px 24px;text-align:center;background:#fff;border-radius:0 0 8px 8px;border:1px solid #eee;border-top:none;">
          <p style="color:#aaa;font-size:14px;">No applications match the current filters.</p>
        </div>
        </div>
      </div>

    </div>
```
**New code:**
```html
        <div id="applicationsEmpty" style="display:none;padding:48px 24px;text-align:center;background:#fff;border-radius:0 0 8px 8px;border:1px solid #eee;border-top:none;">
          <p style="color:#aaa;font-size:14px;">No applications match the current filters.</p>
        </div>
        </div>
      </div>

      <!-- TASK-5 START: BNPL APPLICATIONS PANEL.
           Named zone: every id/function below is prefixed "bnpl"/"Bnpl" to stay
           collision-free with the existing Applications panel above and with
           other in-flight work on this shared file (chat security, dark/light
           mode), per rule 12's "name each worker's zone explicitly" requirement.
           Structure below intentionally mirrors the Adoption Applications panel
           immediately above it 1:1 (same filter-row shape, same .pc-apps-scroll
           table wrapper, same .pc-app-row/.pc-app-avatar/.pc-app-actions classes
           reused rather than re-invented, per rule 7) since Task 5's own spec
           requires "matching the existing adoption Applications panel's shape". -->
      <div id="bnplPanel" class="admin-panel" style="display:none;">
        <h2 style="font-family:'Cormorant Garamond',serif;color:#10214b;margin-bottom:6px;">BNPL Applications</h2>
        <p style="color:#888;margin-bottom:24px;font-size:14px;">Review installment-plan (Pay-in-4 / in-house) applications — newest first</p>

        <!-- Filters -->
        <div style="display:flex;gap:14px;margin-bottom:24px;flex-wrap:wrap;align-items:flex-end;">
          <div style="flex:1;min-width:180px;">
            <label class="pc-form-label" style="margin-bottom:6px;">Filter by Status</label>
            <!-- Shared Data Contracts section fixes BNPL status values as lowercase
                 pending/approved/rejected -- option values match that exactly, not
                 the uppercase convention the adoption Applications filter above uses
                 (that filter's PENDING/APPROVED/REJECTED belongs to a different,
                 adoption-only status field with its own separate contract). -->
            <select id="bnplStatusFilter" class="pc-form-input" style="cursor:pointer;" onchange="renderBnplApplications()">
              <option value="">All Statuses</option>
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>
          <div style="flex:0 0 auto;">
            <div id="bnplCount" style="font-size:13px;color:#8b7d6b;padding:12px 0;"></div>
          </div>
        </div>

        <!-- Bulk action bar -- mirrors #appBulkActionBar's shape exactly, but offers
             Approve/Reject (not delete), since bulk approve/reject is Task 5's explicit
             required scope while bulk delete was never asked for here. -->
        <div id="bnplBulkActionBar" style="display:none;align-items:center;gap:10px;margin-bottom:12px;padding:8px 12px;background:#fff8f2;border:1px solid rgba(237,118,36,0.25);border-radius:8px;">
          <span id="bnplBulkSelectedCount" style="font-size:12px;color:#7a6d5f;font-weight:600;"></span>
          <button onclick="bulkApproveBnpl()" class="pc-btn" style="margin-left:auto;padding:6px 12px;font-size:12px;background:linear-gradient(135deg,#0d7c4a,#0a6340);">Approve Selected</button>
          <button onclick="bulkRejectBnpl()" class="pc-btn" style="padding:6px 12px;font-size:12px;background:linear-gradient(135deg,#c0392b,#a93226);">Reject Selected</button>
        </div>

        <div class="pc-apps-scroll">
        <!-- Table header -- same 32px/2fr/2fr/1.2fr/1fr/56px grid shape as the adoption
             Applications header above (Applicant / Plan & Amount / Submitted / Status /
             Actions), so the two tables read as one consistent pattern. -->
        <div id="bnplHeaderRow" style="display:grid;grid-template-columns:32px 2fr 2fr 1.2fr 1fr 56px;gap:0;background:#f0ece7;border-radius:8px 8px 0 0;padding:10px 16px;border-bottom:2px solid #d7bd88;">
          <div><input type="checkbox" id="bnplSelectAllCheckbox" style="width:16px;height:16px;cursor:pointer;" onchange="toggleSelectAllBnpl(this.checked)"></div>
          <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#7a6d5f;">Applicant</div>
          <div class="pc-app-row__desktop-col" style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#7a6d5f;">Plan &amp; Amount</div>
          <div class="pc-app-row__desktop-col" style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#7a6d5f;">Submitted</div>
          <div class="pc-app-row__desktop-col" style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#7a6d5f;">Status</div>
          <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#7a6d5f;">Actions</div>
        </div>
        <div id="bnplList"></div>
        <div id="bnplEmpty" style="display:none;padding:48px 24px;text-align:center;background:#fff;border-radius:0 0 8px 8px;border:1px solid #eee;border-top:none;">
          <p style="color:#aaa;font-size:14px;">No BNPL applications match the current filter.</p>
        </div>
        </div>
      </div>
      <!-- TASK-5 END (BNPL APPLICATIONS PANEL) -->

    </div>
```
<!-- TASK-5 END -->

**3. Three new modals**, inserted immediately after the existing (reused, unmodified) `uploadPreviewModal`:

<!-- TASK-5 START -->
**Old code:**
```html
    <div class="pc-modal__scroll">
      <div id="uploadPreviewContent" style="padding:6px 30px 30px;"></div>
    </div>
  </div>
</div>
```
**New code:**
```html
    <div class="pc-modal__scroll">
      <div id="uploadPreviewContent" style="padding:6px 30px 30px;"></div>
    </div>
  </div>
</div>

<!-- TASK-5 START: BNPL VIEW / APPROVE / REJECT MODALS.
     bnplViewModal mirrors viewApplicationModal's exact "Use B/C" sticky-header +
     .pc-modal__scroll structure (Design System Section 5) -- copied rather than
     re-derived, per rule 7. Document previews inside it reuse the SAME
     uploadPreviewModal/#uploadPreviewContent/PC.modal.open('uploadPreviewModal')
     shell defined immediately above (no new viewer built), per Task 5's explicit
     "do not build a new viewer" requirement -- showBnplDocPreview() below only
     supplies different content into that same shell. -->
<div id="bnplViewModal" class="pc-modal-shell" style="position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(16,33,75,0.65);display:none;align-items:center;justify-content:center;z-index:9500;padding:20px;">
  <!-- z-index intentionally 9500, BELOW uploadPreviewModal's 9999 (see Addendum 2,
       bug #3): this modal opens the reused preview modal on top of itself, so it
       must defer to it in the stacking order or the preview modal's own close
       button becomes unclickable. -->
  <div class="pc-modal-box" style="width:100%;max-width:680px;background:#fff;border-radius:14px;box-shadow:0 20px 60px rgba(16,33,75,0.25);">
    <div style="display:flex;justify-content:space-between;align-items:center;padding:20px 26px;background:#fff;border-bottom:2px solid #d7bd88;flex-shrink:0;">
      <h2 style="font-family:'Cormorant Garamond',serif;font-size:22px;color:#10214b;margin:0;">BNPL Application Details</h2>
      <button onclick="closeBnplViewModal()" style="background:#f0ece8;border:none;width:34px;height:34px;border-radius:8px;cursor:pointer;font-size:20px;color:#7a6d5f;display:flex;align-items:center;justify-content:center;">×</button>
    </div>
    <div class="pc-modal__scroll">
      <div id="bnplViewContent" style="padding:26px;"></div>
    </div>
  </div>
</div>

<!-- BNPL APPROVE MODAL -- same "Use A" confirmation-modal shape as approveModal
     above, but asks only for the fields update-bnpl-status.php actually requires
     (approved_by; no checkout link / position -- those are adoption-specific
     concepts from Task 10, not part of the BNPL status contract). -->
<div id="bnplApproveModal" class="pc-modal-shell" style="position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(16,33,75,0.65);display:none;align-items:center;justify-content:center;z-index:10000;padding:20px;">
  <div style="width:100%;max-width:520px;max-height:85vh;overflow-y:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch;background:#fff;border-radius:14px;padding:32px;box-shadow:0 20px 60px rgba(16,33,75,0.25);">
    <h3 style="font-family:'Cormorant Garamond',serif;font-size:22px;color:#10214b;margin:0 0 8px;">Approve this BNPL application?</h3>
    <p style="color:#7a6d5f;font-size:14px;margin-bottom:20px;">The applicant will be marked as approved. This is recorded in the submission's audit trail.</p>
    <label class="pc-form-label" style="margin-bottom:6px;">Approved By (Staff Name)</label>
    <input type="text" id="bnplApproveByName" class="pc-form-input" placeholder="e.g. John Doe" style="margin-bottom:20px;">
    <div style="display:flex;gap:10px;">
      <button onclick="submitBnplApprove()" class="pc-btn pc-btn--primary" style="flex:1;background:linear-gradient(135deg,#0d7c4a,#0a6340);">Approve</button>
      <button onclick="closeBnplApproveModal()" class="pc-btn pc-btn--ghost">Cancel</button>
    </div>
  </div>
</div>

<!-- BNPL REJECT MODAL -- same shape as rejectModal above. Unlike the adoption
     flow's optional rejection reason, update-bnpl-status.php requires
     rejection_notes to be non-empty when rejecting, so this is marked required
     and submitBnplReject() blocks the request client-side too, matching the
     server's own validation instead of letting a blank submission round-trip
     into an avoidable 400. A "Rejected By (Staff Name)" field is also required
     (see Addendum 2, bug #2): update-bnpl-status.php's history log always uses
     approved_by for the "by" field, approve or reject, so this field is what
     keeps the audit trail attributing rejections to the actual staff member
     instead of silently defaulting to "admin". -->
<div id="bnplRejectModal" class="pc-modal-shell" style="position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(16,33,75,0.65);display:none;align-items:center;justify-content:center;z-index:10000;padding:20px;">
  <div style="width:100%;max-width:520px;max-height:85vh;overflow-y:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch;background:#fff;border-radius:14px;padding:32px;box-shadow:0 20px 60px rgba(16,33,75,0.25);">
    <h3 style="font-family:'Cormorant Garamond',serif;font-size:22px;color:#10214b;margin:0 0 8px;">Reject this BNPL application?</h3>
    <p style="color:#7a6d5f;font-size:14px;margin-bottom:20px;">This application will be marked as rejected. A reason is required — it's shown to the applicant.</p>
    <label class="pc-form-label" style="margin-bottom:6px;">Rejected By (Staff Name)</label>
    <input type="text" id="bnplRejectByName" class="pc-form-input" placeholder="e.g. John Doe" style="margin-bottom:18px;">
    <label class="pc-form-label" style="margin-bottom:6px;">Reason for rejection <span style="font-weight:400;text-transform:none;">(required)</span></label>
    <textarea id="bnplRejectReason" class="pc-form-input" placeholder="e.g., Documents did not verify, Proof of funding insufficient..." style="min-height:90px;resize:vertical;margin-bottom:18px;"></textarea>
    <div style="display:flex;gap:10px;">
      <button onclick="submitBnplReject()" class="pc-btn pc-btn--primary" style="flex:1;background:linear-gradient(135deg,#c0392b,#a93226);">Send Rejection</button>
      <button onclick="closeBnplRejectModal()" class="pc-btn pc-btn--ghost">Cancel</button>
    </div>
  </div>
</div>
<!-- TASK-5 END (BNPL VIEW / APPROVE / REJECT MODALS) -->
```
<!-- TASK-5 END -->

**4. `switchPanel()` wiring:**

<!-- TASK-5 START -->
**Old code:**
```javascript
  if (panel === 'applications') { 
    upgradeNativeSelectsToCustom();
    initCustomSelects();
    loadApplications(); 
  }
```
**New code:**
```javascript
  if (panel === 'applications') { 
    upgradeNativeSelectsToCustom();
    initCustomSelects();
    loadApplications(); 
  }
  // TASK-5 START: mirrors the 'applications' branch immediately above --
  // upgrade this panel's native <select> (bnplStatusFilter) to the shared
  // custom-dropdown widget, then load data, exactly the same two-step
  // sequence the adoption Applications panel already uses.
  if (panel === 'bnpl') {
    upgradeNativeSelectsToCustom();
    initCustomSelects();
    loadBnplApplications();
  }
  // TASK-5 END
```
<!-- TASK-5 END -->

**5. Full BNPL panel JS module** (`loadBnplApplications`, `renderBnplApplications`, bulk select/approve/reject,
`viewBnplApplication`, `showBnplDocPreview` (reuses `uploadPreviewModal`), `saveBnplStaffNotes`, and the
approve/reject open/close/submit functions), inserted immediately before the `// INIT` block:

<!-- TASK-5 START -->
**Old code:**
```javascript
// INIT
document.addEventListener('DOMContentLoaded', () => {
```
**New code** (the full ~340-line module, wrapped in its own `// TASK-5 START` / `// TASK-5 END`
markers, placed directly before that same unchanged `// INIT` line — reproduced in full below,
exactly as it exists in the delivered `admin-dashboard.html`, since every touched line must be
documented, not just pointed to):

```javascript
// TASK-5 START: BNPL APPLICATIONS PANEL — all logic for the new panel.
// Named zone: every function/variable here is prefixed "Bnpl"/"bnpl" so
// nothing collides with the adoption Applications logic above or with
// other in-flight work on this shared file, per rule 12.
//
// Endpoints called (all new, Task-5-owned; none edit Task 4's files):
//   get-bnpl-applications.php  — list + single-submission read
//   update-bnpl-status.php     — Task 4's own write endpoint (approve/reject);
//                                 called here, not edited, per Task 4's
//                                 completion-file note that later tasks only
//                                 call it
//   update-bnpl-notes.php      — Task 5's own staff-notes write endpoint

let allBnplApplications = [];
const selectedBnplIds = new Set();

async function loadBnplApplications() {
  if (!await checkTokenBeforeAction('loadBnplApplications')) return;
  try {
    const fd = new FormData();
    fd.append('token', adminToken);
    const res  = await fetch('/installments/get-bnpl-applications.php', { method: 'POST', body: fd });
    const json = await res.json();
    allBnplApplications = Array.isArray(json.applications) ? json.applications : [];
    allBnplApplications.sort((a, b) => (b.submitted_at || '').localeCompare(a.submitted_at || ''));
    renderBnplApplications();
  } catch (e) {
    PC.toast('Error loading BNPL applications', 'error');
  }
}

const BNPL_STATUS_STYLES = {
  pending:  { bg: '#fffbe6', border: '#f5a623', text: '#b87a00', icon: 'fa-clock', label: 'PENDING' },
  approved: { bg: '#e6f7ef', border: '#0d7c4a', text: '#0d7c4a', icon: 'fa-circle-check', label: 'APPROVED' },
  rejected: { bg: '#fdecea', border: '#c0392b', text: '#c0392b', icon: 'fa-circle-xmark', label: 'REJECTED' },
};

function renderBnplApplications() {
  const statusFilter = document.getElementById('bnplStatusFilter')?.value || '';
  let filtered = allBnplApplications;
  if (statusFilter) filtered = filtered.filter(a => (a.status || 'pending') === statusFilter);

  const list  = document.getElementById('bnplList');
  const empty = document.getElementById('bnplEmpty');
  const count = document.getElementById('bnplCount');
  list.innerHTML = '';
  if (count) count.textContent = filtered.length + ' result' + (filtered.length !== 1 ? 's' : '');
  if (!filtered.length) { if (empty) empty.style.display = 'block'; return; }
  if (empty) empty.style.display = 'none';

  filtered.forEach((app, idx) => {
    const status = app.status || 'pending';
    const ss = BNPL_STATUS_STYLES[status] || BNPL_STATUS_STYLES.pending;
    const isEven = idx % 2 === 0;
    const isSelected = selectedBnplIds.has(app.submission_id || '');
    const initials = (app.email || '?').trim().charAt(0).toUpperCase() || '?';
    const planLabel = app.plan_label ? PC.esc(app.plan_label) : '<span style="color:#aaa;">—</span>';
    const amountLabel = app.amount ? '$' + PC.esc(app.amount) : '';
    const dateLabel = app.submitted_at ? formatAppDateShort(app.submitted_at) : '—';
    const sidEsc = PC.esc(app.submission_id || '');

    const actionIcons = (() => {
      let icons = `<button class="pc-app-actions__btn" onclick="viewBnplApplication('${sidEsc}')" title="View"><i class="ph ph-eye"></i></button>`;
      if (status === 'pending') {
        icons += `<button class="pc-app-actions__btn pc-app-actions__btn--approve" onclick="openBnplApproveModal('${sidEsc}')" title="Approve"><i class="ph-fill ph-check-circle"></i></button>`;
        icons += `<button class="pc-app-actions__btn pc-app-actions__btn--reject" onclick="openBnplRejectModal('${sidEsc}')" title="Reject"><i class="ph-fill ph-x-circle"></i></button>`;
      }
      return icons;
    })();

    const row = document.createElement('div');
    row.className = 'pc-app-row' + (isSelected ? ' pc-app-row--selected' : '');
    row.style.cssText = `display:grid;grid-template-columns:32px 2fr 2fr 1.2fr 1fr 56px;gap:0;padding:13px 16px;background:${isSelected ? '' : (isEven ? '#fff' : '#faf8f5')};border-bottom:1px solid #ece7e0;align-items:center;`;
    row.innerHTML = `
      <div>
        <input type="checkbox" class="bnpl-row-checkbox" data-sid="${sidEsc}" ${isSelected ? 'checked' : ''} style="width:16px;height:16px;cursor:pointer;">
      </div>
      <div class="pc-app-applicant">
        <div class="pc-app-avatar" aria-hidden="true">${PC.esc(initials)}</div>
        <div>
          <div style="font-weight:600;color:#10214b;font-size:14px;">${PC.esc(app.email || 'Unknown')}</div>
          <div style="font-size:12px;color:#8b7d6b;margin-top:2px;font-family:monospace;">${sidEsc}</div>
        </div>
      </div>
      <div class="pc-app-row__desktop-col" style="font-size:14px;color:#2c2416;">${planLabel}${amountLabel ? ' · ' + amountLabel : ''}</div>
      <div class="pc-app-row__desktop-col" style="font-size:13px;color:#7a6d5f;">${dateLabel}</div>
      <div class="pc-app-row__desktop-col">
        <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:.04em;background:${ss.bg};color:${ss.text};border:1px solid ${ss.border};"><i class="fas ${ss.icon}" style="font-size:10px;"></i>${ss.label}</span>
      </div>
      <div class="pc-app-actions" data-sid="${sidEsc}">
        <button type="button" class="pc-app-actions__dot" onclick="toggleAppActionsPanel(event, '${sidEsc}')" title="Actions"><i class="fas fa-ellipsis-vertical"></i></button>
        <div class="pc-app-actions__panel" data-code="${sidEsc}">${actionIcons}</div>
      </div>
    `;
    row.querySelector('.bnpl-row-checkbox').addEventListener('change', (e) => {
      const sid = app.submission_id || '';
      if (e.target.checked) selectedBnplIds.add(sid);
      else selectedBnplIds.delete(sid);
      row.classList.toggle('pc-app-row--selected', e.target.checked);
      updateBnplBulkActionBar();
    });
    list.appendChild(row);
  });
}

function toggleSelectAllBnpl(checked) {
  document.querySelectorAll('.bnpl-row-checkbox').forEach(cb => {
    cb.checked = checked;
    const sid = cb.dataset.sid;
    if (checked) selectedBnplIds.add(sid);
    else selectedBnplIds.delete(sid);
  });
  updateBnplBulkActionBar();
}

function updateBnplBulkActionBar() {
  const bar = document.getElementById('bnplBulkActionBar');
  const count = selectedBnplIds.size;
  if (!bar) return;
  if (count > 0) {
    bar.style.display = 'flex';
    document.getElementById('bnplBulkSelectedCount').textContent = count + ' selected';
  } else {
    bar.style.display = 'none';
  }
}

async function bnplUpdateStatus(submissionId, status, extra) {
  const fd = new FormData();
  fd.append('token', adminToken);
  fd.append('submission_id', submissionId);
  fd.append('status', status);
  Object.keys(extra || {}).forEach(k => fd.append(k, extra[k]));
  const res  = await fetch('/installments/update-bnpl-status.php', { method: 'POST', body: fd });
  return res.json();
}

async function bulkApproveBnpl() {
  const ids = Array.from(selectedBnplIds);
  if (!ids.length) return;
  const approvedBy = prompt('Approved by (staff name) — applied to all ' + ids.length + ' selected:');
  if (!approvedBy || !approvedBy.trim()) return;
  if (!confirm(`Approve ${ids.length} BNPL application(s)?`)) return;
  if (!await checkTokenBeforeAction('bulkApproveBnpl')) return;
  const results = await Promise.all(ids.map(sid => bnplUpdateStatus(sid, 'approved', { approved_by: approvedBy.trim() }).catch(e => ({ success: false, message: e.message }))));
  const failed = results.filter(r => !r.success).length;
  PC.toast(`${ids.length - failed} approved${failed ? ', ' + failed + ' failed' : ''}`, failed ? 'error' : 'success');
  selectedBnplIds.clear();
  updateBnplBulkActionBar();
  loadBnplApplications();
}

async function bulkRejectBnpl() {
  const ids = Array.from(selectedBnplIds);
  if (!ids.length) return;
  const rejectedBy = prompt('Rejected by (staff name) — applied to all ' + ids.length + ' selected:');
  if (!rejectedBy || !rejectedBy.trim()) { PC.toast('A staff name is required', 'error'); return; }
  const reason = prompt('Rejection reason (required) — applied to all ' + ids.length + ' selected:');
  if (!reason || !reason.trim()) { PC.toast('A rejection reason is required', 'error'); return; }
  if (!confirm(`Reject ${ids.length} BNPL application(s)?`)) return;
  if (!await checkTokenBeforeAction('bulkRejectBnpl')) return;
  const results = await Promise.all(ids.map(sid => bnplUpdateStatus(sid, 'rejected', { rejection_notes: reason.trim(), approved_by: rejectedBy.trim() }).catch(e => ({ success: false, message: e.message }))));
  const failed = results.filter(r => !r.success).length;
  PC.toast(`${ids.length - failed} rejected${failed ? ', ' + failed + ' failed' : ''}`, failed ? 'error' : 'success');
  selectedBnplIds.clear();
  updateBnplBulkActionBar();
  loadBnplApplications();
}

let currentBnplViewId = null;

async function viewBnplApplication(sid) {
  if (!sid) return;
  if (!await checkTokenBeforeAction('viewBnplApplication')) return;
  currentBnplViewId = sid;
  const modal = document.getElementById('bnplViewModal');
  const content = document.getElementById('bnplViewContent');
  modal.style.display = 'flex';
  content.innerHTML = '<div style="text-align:center;padding:40px;"><div class="pc-spinner" style="margin:0 auto;"></div><p style="margin-top:16px;color:#888;font-size:14px;">Loading...</p></div>';
  try {
    const fd = new FormData();
    fd.append('token', adminToken);
    fd.append('submission_id', sid);
    const res  = await fetch('/installments/get-bnpl-applications.php', { method: 'POST', body: fd });
    const json = await res.json();
    const app  = json.application || {};
    const status = app.status || 'pending';
    const ss = BNPL_STATUS_STYLES[status] || BNPL_STATUS_STYLES.pending;
    const dateLabel = app.submitted_at
      ? new Date(app.submitted_at).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
      : '—';
    const field = (label, val) => val
      ? `<div class="pc-field"><span class="pc-field__label">${label}</span><div class="pc-field__value">${PC.esc(String(val))}</div></div>`
      : '';
    const files = Array.isArray(app.files) ? app.files : [];
    const filesHtml = files.length
      ? files.map(f => `<button type="button" class="pc-btn pc-btn--sm" style="margin:0 8px 8px 0;" onclick="showBnplDocPreview('${PC.esc(f.url)}', '${PC.esc(f.name)}')"><i class="ph ph-file-text" style="margin-right:6px;"></i>${PC.esc(f.name)}</button>`).join('')
      : '<span style="color:#aaa;font-size:13px;">No documents on file.</span>';
    const history = Array.isArray(app.history) ? app.history : [];
    const historyHtml = history.length
      ? '<div style="display:flex;flex-direction:column;gap:8px;">' + history.map(h => `
        <div style="display:flex;justify-content:space-between;gap:10px;font-size:12.5px;padding:8px 10px;background:#f9f7f2;border-radius:6px;">
          <span style="color:#2c2416;"><strong>${PC.esc(h.from || '—')}</strong> → <strong>${PC.esc(h.to || '—')}</strong> by ${PC.esc(h.by || '—')}</span>
          <span style="color:#8b7d6b;white-space:nowrap;">${PC.esc(h.at || '')}</span>
        </div>`).join('') + '</div>'
      : '<span style="color:#aaa;font-size:13px;">No history yet.</span>';

    content.innerHTML = `
      <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;">
        <div>
          <div style="font-size:13px;color:#8b7d6b;margin-bottom:4px;">Submission ID: <strong style="font-family:monospace;">${PC.esc(app.submission_id || sid)}</strong></div>
          <div style="font-size:13px;color:#8b7d6b;">Submitted: <strong>${dateLabel}</strong></div>
        </div>
        <span style="padding:4px 14px;border-radius:20px;font-size:12px;font-weight:700;letter-spacing:.04em;background:${ss.bg};color:${ss.text};border:1px solid ${ss.border};">${ss.label}</span>
      </div>
      <div class="pc-field-card" style="background:#fff;border:1px solid #ece7e0;border-radius:10px;padding:16px;margin-bottom:16px;">
        <div class="pc-field-card__title" style="font-family:'Cormorant Garamond',serif;font-size:16px;font-weight:700;color:#10214b;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid #ece7e0;">Application Info</div>
        <div class="pc-field-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:0 16px;">
          ${field('Email', app.email)}
          ${field('Plan Type', app.plan_label)}
          ${field('Total Amount', app.amount ? '$' + app.amount : null)}
        </div>
      </div>
      <div style="background:#fff;border:1px solid #ece7e0;border-radius:10px;padding:16px;margin-bottom:16px;">
        <div style="font-family:'Cormorant Garamond',serif;font-size:16px;font-weight:700;color:#10214b;margin-bottom:10px;padding-bottom:8px;border-bottom:1px solid #ece7e0;">Submitted Documents</div>
        <div>${filesHtml}</div>
      </div>
      ${status === 'approved' && app.approved_by ? `
      <div style="background:#f9f7f2;border:1px solid #ece7e0;border-radius:10px;padding:14px 16px;margin-bottom:16px;">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#8b7d6b;margin-bottom:4px;">Approved By</div>
        <div style="font-weight:700;font-size:14px;color:#10214b;">${PC.esc(app.approved_by)}</div>
        ${app.approved_at ? `<div style="font-size:12px;color:#7a6d5f;margin-top:2px;">${PC.esc(app.approved_at)}</div>` : ''}
      </div>` : ''}
      ${status === 'rejected' && app.rejection_notes ? `
      <div style="background:#fdecea;border:1px solid #c0392b;border-radius:10px;padding:14px 16px;margin-bottom:16px;">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#c0392b;margin-bottom:4px;">Rejection Note</div>
        <p style="font-size:13px;color:#c0392b;margin:0;">${PC.esc(app.rejection_notes)}</p>
      </div>` : ''}
      <div style="background:#fff;border:1px solid #ece7e0;border-radius:10px;padding:16px;margin-bottom:16px;">
        <div style="font-family:'Cormorant Garamond',serif;font-size:16px;font-weight:700;color:#10214b;margin-bottom:10px;padding-bottom:8px;border-bottom:1px solid #ece7e0;">Status History</div>
        ${historyHtml}
      </div>
      <div style="background:#fff;border:1px solid #ece7e0;border-radius:10px;padding:16px;margin-bottom:16px;">
        <div style="font-family:'Cormorant Garamond',serif;font-size:16px;font-weight:700;color:#10214b;margin-bottom:10px;padding-bottom:8px;border-bottom:1px solid #ece7e0;">Staff Notes <span style="font-weight:400;font-size:12px;color:#8b7d6b;text-transform:none;">(internal only — never shown to the applicant)</span></div>
        <textarea id="bnplStaffNotesInput" class="pc-form-input" style="min-height:80px;resize:vertical;margin-bottom:10px;" placeholder="Internal notes for staff...">${PC.esc(app.staff_notes || '')}</textarea>
        <button onclick="saveBnplStaffNotes('${PC.esc(app.submission_id || sid)}')" class="pc-btn pc-btn--ghost" style="padding:8px 16px;font-size:13px;">Save Notes</button>
      </div>
      ${status === 'pending' ? `
      <div style="display:flex;gap:10px;padding-top:4px;">
        <button onclick="closeBnplViewModal();openBnplApproveModal('${PC.esc(app.submission_id || sid)}')" class="pc-btn pc-btn--primary" style="flex:1;background:linear-gradient(135deg,#0d7c4a,#0a6340);">Approve</button>
        <button onclick="closeBnplViewModal();openBnplRejectModal('${PC.esc(app.submission_id || sid)}')" class="pc-btn pc-btn--ghost" style="flex:1;">Reject</button>
      </div>` : ''}
    `;
  } catch (e) {
    content.innerHTML = '<p style="color:#c0392b;padding:20px;">Error loading application details.</p>';
  }
}

function closeBnplViewModal() {
  document.getElementById('bnplViewModal').style.display = 'none';
  currentBnplViewId = null;
}

function showBnplDocPreview(url, filename) {
  const isPdf = /\.pdf$/i.test(filename || url);
  const content = document.getElementById('uploadPreviewContent');
  content.innerHTML = isPdf
    ? `<embed src="${url}" type="application/pdf" style="width:100%;height:70vh;border-radius:8px;">`
    : `<img src="${url}" style="width:100%;border-radius:8px;" onerror="this.replaceWith(Object.assign(document.createElement('p'),{textContent:'Could not load this file.',style:'color:#c0392b;'}))">`;
  PC.modal.open('uploadPreviewModal');
}

async function saveBnplStaffNotes(sid) {
  if (!await checkTokenBeforeAction('saveBnplStaffNotes')) return;
  const text = document.getElementById('bnplStaffNotesInput')?.value ?? '';
  try {
    const fd = new FormData();
    fd.append('token', adminToken);
    fd.append('submission_id', sid);
    fd.append('staff_notes', text);
    const res  = await fetch('/installments/update-bnpl-notes.php', { method: 'POST', body: fd });
    const json = await res.json();
    if (json.success) {
      PC.toast('Staff notes saved');
    } else {
      PC.toast(json.message || 'Error saving notes', 'error');
    }
  } catch (e) {
    PC.toast('Network error: ' + e.message, 'error');
  }
}

let currentBnplApproveId = null;

function openBnplApproveModal(sid) {
  currentBnplApproveId = sid;
  document.getElementById('bnplApproveByName').value = '';
  document.getElementById('bnplApproveModal').style.display = 'flex';
}

function closeBnplApproveModal() {
  document.getElementById('bnplApproveModal').style.display = 'none';
  currentBnplApproveId = null;
}

async function submitBnplApprove() {
  if (!await checkTokenBeforeAction('approveBnplApplication')) return;
  const approvedBy = document.getElementById('bnplApproveByName').value.trim();
  if (!approvedBy) { PC.toast('Please enter your name', 'error'); return; }
  const sid = currentBnplApproveId;
  if (!sid) return;
  try {
    const json = await bnplUpdateStatus(sid, 'approved', { approved_by: approvedBy });
    if (json.success) {
      PC.toast('Approved!');
      closeBnplApproveModal();
      loadBnplApplications();
    } else {
      PC.toast(json.message || 'Error', 'error');
    }
  } catch (e) {
    PC.toast('Network error: ' + e.message, 'error');
  }
}

let currentBnplRejectId = null;

function openBnplRejectModal(sid) {
  currentBnplRejectId = sid;
  document.getElementById('bnplRejectByName').value = '';
  document.getElementById('bnplRejectReason').value = '';
  document.getElementById('bnplRejectModal').style.display = 'flex';
}

function closeBnplRejectModal() {
  document.getElementById('bnplRejectModal').style.display = 'none';
  currentBnplRejectId = null;
}

async function submitBnplReject() {
  if (!await checkTokenBeforeAction('rejectBnplApplication')) return;
  const rejectedBy = document.getElementById('bnplRejectByName').value.trim();
  if (!rejectedBy) { PC.toast('Please enter your name', 'error'); return; }
  const reason = document.getElementById('bnplRejectReason').value.trim();
  if (!reason) { PC.toast('A rejection reason is required', 'error'); return; }
  const sid = currentBnplRejectId;
  if (!sid) return;
  try {
    const json = await bnplUpdateStatus(sid, 'rejected', { rejection_notes: reason, approved_by: rejectedBy });
    if (json.success) {
      PC.toast('Rejected. Note saved.');
      closeBnplRejectModal();
      loadBnplApplications();
    } else {
      PC.toast(json.message || 'Error', 'error');
    }
  } catch (e) {
    PC.toast('Network error: ' + e.message, 'error');
  }
}
// TASK-5 END (BNPL APPLICATIONS PANEL)
```
<!-- TASK-5 END -->

## New files

- `installments/get-bnpl-applications.php` — New file — in the zip. List/fetch endpoint: returns all
  BNPL submissions (parsed from Task 4's `installment_submissions.log`, merged with each submission's
  status record via Task 4's `bnpl_read_status()`) for the panel's list view, or a single submission's
  full detail (including its per-applicant document URLs and staff notes) when `submission_id` is
  posted.
- `installments/bnpl-notes-lib.php` — New file — in the zip. Task-5-owned shared library for reading/
  writing a submission's staff-notes field, mirroring Task 4's `bnpl-status-lib.php` validate-then-
  `flock()` read-modify-write pattern (`bnpl_write_notes`) for consistency, without touching or
  depending on Task 4's actual status file beyond `bnpl_validate_submission_id()`.
- `installments/update-bnpl-notes.php` — New file — in the zip. Write endpoint the panel's "Save Notes"
  button calls; validates the admin token and submission id, then calls `bnpl_write_notes()`.

*(See the "Path note" under Files Changed above for why these are placed under `installments/` rather
than the generic table's `admin/` default.)*

## Bugs Found Outside Task Scope

- **No applicant profile data is ever persisted for BNPL submissions.** `submit-installment.php`
  collects name, phone, DOB, full address, and pet details in `$_POST`, but only ever puts them into
  the admin notification *email* body — none of it is written to `installment_submissions.log` or any
  other file. The panel's detail view can therefore only show what the log actually persists (email,
  plan, amount, submission date, document filenames) plus the status backend's fields — it cannot show
  applicant name/phone/address the way the adoption Applications detail view can, because that data
  simply doesn't exist on disk anywhere. Flagging rather than fixing, per Scope Lock — fixing this
  would mean modifying `submit-installment.php`, which is Task 4's file, not this task's.
- `installment_submissions.log` contains two different line shapes in production (3 older rows with 4
  fields, no `plan_label`/`amount`; later rows with 6 fields). `get-bnpl-applications.php` handles both,
  but this is a pre-existing data inconsistency in a file this task only reads, not something introduced
  here.

## Addendum — BNPL-Path-Sync.md applied

After initial submission, `BNPL-Path-Sync.md` (CEO-confirmed, applies to Tasks 4/5/6/7) finalized the
BNPL backend's deployment layout: everything lives self-contained under `/installments/` on
`adopt.petcenterco.com` (not a separate payment subdomain), with `/admin/.env` as a sibling of
`/installments/` under `/adopt/`. Two categories of change followed:

1. **Path updates** (mechanical, once the sync arrived):
   - `admin-dashboard.html`'s three fetch calls now hit `/installments/get-bnpl-applications.php`,
     `/installments/update-bnpl-status.php`, `/installments/update-bnpl-notes.php` (site-root-relative,
     matching how the sync doc itself gives Task 7's URL), replacing the earlier `./`-relative guesses
     made before any path was confirmed.
   - `get-bnpl-applications.php` and `update-bnpl-notes.php`'s `$ADMIN_ENV_CANDIDATES` arrays are now
     pinned to the single real path (`dirname(__DIR__) . '/admin/.env'`), matching Task 4's own
     now-pinned lookup exactly, replacing the earlier 3-candidate defensive guess.

2. **A real bug caught while applying the sync, not introduced by it:** the original
   `showBnplDocPreview()` built a document's URL from the bare filename alone
   (`./uploads/installments/{filename}`). But `submit-installment.php` actually saves each file under
   a **per-applicant subfolder** — `uploads/installments/{sanitize_folder(email)}/{filename}` — and
   `installment_submissions.log` never records that subfolder, only the bare filename. That URL would
   have 404'd for every submission. Fixed by copying `sanitize_folder()` verbatim into
   `get-bnpl-applications.php` (per rule 7) as `bnpl_sanitize_folder()`, computing the correct URL
   server-side per file, and changing the `files` array shape from bare filenames to `{name, url}`
   objects — `admin-dashboard.html`'s `viewBnplApplication()` and `showBnplDocPreview()` were updated
   to match (the latter now takes `url` directly instead of reconstructing one).

All three PHP files re-passed structural lint (balanced braces/parens/brackets) after these edits; the
JS `TASK-5` block in `admin-dashboard.html` re-passed brace/paren balance and every `onclick` handler
still resolves to a defined function.

## Addendum 2 — Full re-verification pass, real Playwright browser testing, 3 more bugs found & fixed

Per a CEO request to re-verify everything against the project docs before calling this done, the
tracker, both project summaries, and this task's own section were re-read in full, both PHP backend
files Task 5 depends on (`bnpl-status-lib.php`, `update-bnpl-status.php`) were re-read line by line
against my own code's assumptions, and — since this sandbox does have a working Chromium — the panel
was actually rendered in a real headless browser and driven end-to-end with Playwright, with every
BNPL network call route-stubbed (`page.route`) rather than skipped. This caught three real bugs the
earlier structural/lint-only verification could not have found:

1. **Double `$` in amount display.** `submit-installment.php` writes the log line's amount field as
   `"\${$amount}"` — i.e. the log already contains a literal `$` (confirmed again against the real
   log: `... | $450 | ...`). `get-bnpl-applications.php` was passing that straight through, and the
   frontend prepended its own `'$' +`, producing `$$450`. Fixed by stripping the leading `$` server-side
   (`ltrim($parts[4], '$')`) so every consumer gets a bare numeric string. Verified fixed: the rendered
   table shows `$450` once, confirmed by an assertion that `"$$"` never appears in the panel's text.

2. **Rejections were silently recorded under a generic "admin" in the audit trail.**
   `update-bnpl-status.php`'s read-modify-write closure builds the `history[]` entry's `by` field from
   the `approved_by` POST var *regardless* of whether the action is `approved` or `rejected`
   (`'by' => $approved_by !== '' ? $approved_by : 'admin'`). My reject modal and `bulkRejectBnpl()`
   only ever sent `rejection_notes`, never `approved_by` — so every rejection's history entry would
   have read "admin" instead of the actual staff member, even though the UI never explained why.
   Fixed: added a required "Rejected By (Staff Name)" field to the reject modal (and an equivalent
   `prompt()` in the bulk-reject flow), sent as `approved_by` alongside `rejection_notes`. Verified with
   Playwright: submitting with the reason filled but the name blank is blocked client-side with no
   request sent; submitting both sends a request whose body contains both `rejection_notes` and the
   staff name.

3. **Modal z-index stacking bug.** `bnplViewModal` was given `z-index:9999` — the same value as the
   *reused* `uploadPreviewModal` it opens on top of for document previews. With equal z-index, later
   DOM order wins the stacking tie, and since `bnplViewModal` comes after `uploadPreviewModal` in the
   document, it rendered on top and intercepted clicks on the preview modal underneath it (reproduced
   directly: Playwright's click on the preview modal's close button timed out with "element intercepts
   pointer events"). Fixed by pinning `bnplViewModal` to `z-index:9500`, below the shared modal it must
   defer to. Verified: the preview modal is now clickable and closable while `bnplViewModal` is open
   behind it.

Also re-confirmed via real rendering (not just re-reading code) and not found to be bugs:
- The per-applicant upload subfolder fix from Addendum 1 (`{name, url}` file objects, `sanitize_folder()`
  copy) — confirmed end-to-end: the rendered `<img>` element's `src` is
  `/installments/uploads/installments/{sanitized_email}/{filename}`, matching `submit-installment.php`'s
  actual save path exactly.
- The status filter dropdown correctly narrows the list (tested selecting "Approved" → exactly the one
  approved mock row shown).
- A submission from `installment_submissions.log`'s older 4-field shape (no `plan_label`/`amount`)
  renders cleanly with placeholder dashes — no `"undefined"` or `"NaN"` in its row.
- Of every failed/4xx network request observed during the full run, none were BNPL/installments-related
  — the rest are pre-existing, unrelated dashboard resources (a missing `video-upload-manager.js`, and
  CDN assets blocked by this sandbox having no outbound network access), confirmed by explicitly
  filtering the failed-request list for anything BNPL/installments-related and finding none.

The test script (`test_bnpl_panel.py`) is included alongside the deliverables so this run can be
reproduced or extended.

## Addendum 3 — Hand-in reformatted to WORKER-DELIVERY-GUIDE.md

The actual code was not redone or changed in substance for this addendum — only the hand-in format.
Per the CEO's instruction, `WORKER-DELIVERY-GUIDE.md` was read in full and now governs how this task's
work is delivered instead of git/branches/GitHub/`reports/`:

- The **Files Changed** table above was rebuilt with the required four columns (Source file name |
  Live path | Changed or New | Shared with another task?), using the guide's Section 3 mapping table
  for `admin-dashboard.html` → `admin/index.html`, and documenting the path ambiguity for the three new
  PHP files rather than silently resolving it (see the "Path note" under Files Changed).
- Every old-code snippet in the **Code Changes** section above was re-verified character-for-character
  against the pristine original `admin-dashboard.html` supplied at the start of this task, and
  reconfirmed to occur exactly once in it (checked again just before this addendum, not assumed from
  the first pass).
- The three new PHP files (`get-bnpl-applications.php`, `bnpl-notes-lib.php`, `update-bnpl-notes.php`)
  are no longer pasted as code in this completion file — per the guide's "do not paste its code" rule
  for new files — and instead ship in `Task-5-NewFiles.zip` at the live paths given above, listed under
  **New files**.
- Nothing about the panel's behavior, the PHP files' logic, or any previously-fixed bug was touched in
  this pass. This addendum only changes how the existing, already-verified work is packaged and
  documented.

## Addendum 4 — Hand-in format update: combined zip, zip naming, new-folder flagging

A further hand-in clarification was issued after Addendum 3, correcting a widespread misunderstanding
among workers of `WORKER-DELIVERY-GUIDE.md`. Again, **no code was redone or changed in substance** —
only how it's packaged:

1. **Single combined zip, not two separate deliverables.** This completion file and all new files now
   ship together in one zip. `Task-5-Completion.md` sits at the zip root (project paperwork, not a
   deployed site file); the three new PHP files sit at their live path, `installments/`, inside the same
   zip.
2. **Zip naming convention.** Named `[Project-Name]-[Task-Number].zip`, using this project's own name
   using the project's own name ("Unified Status" — the CEO's correction on an earlier pass of this
   hand-in, which mistakenly used "Pet Center" instead) rather than an invented name, so sorting across
   all workers' hand-ins stays consistent. This hand-in is **`unified-status-task-5.zip`**.
3. **New-folder flagging, done heavily and explicitly.** Per the new instruction, any new file whose
   path doesn't already exist in either project summary's folder structure must be flagged, naming
   exactly which new folder the integrator needs to create and where. This applies to all three of my
   new files — see the **"⚠️ NEW FOLDER FLAG"** note under Files Changed above: `installments/` is not
   in either summary's Section 1 folder tree, so the integrator must create it as a new top-level folder
   at the site root (`public_html/adopt/installments/`) if Task 4 hasn't already.
4. **`index.html` naming confirmed.** `admin-dashboard.html`'s live path, `admin/index.html`, already
   follows the strict "every page is a folder containing `index.html`" rule — no change needed there.
   None of my three new PHP files are pages, so this rule doesn't otherwise apply to this task's new
   files.
5. **Pending/incomplete items re-confirmed already documented before zipping**, per the new instruction
   to check this explicitly: see **Bugs Found Outside Task Scope** (the unpersisted applicant-profile
   data gap in `submit-installment.php`, and the two-line-format inconsistency in
   `installment_submissions.log`) and the **Self-Check Confirmation**'s sandbox-limitation note (no PHP
   interpreter available to actually execute the three new PHP files against a real engine) — both were
   already written up in earlier passes of this same completion file, nothing new surfaced in this
   packaging-only pass.

## Self-Check Confirmation

- Only the files listed under Files Changed were touched. No file belonging to Task 4
  (`bnpl-status-lib.php`, `update-bnpl-status.php`, `submit-installment.php`) was modified in any way —
  confirmed by re-diffing them against the originals supplied, byte-for-byte identical.
- Every HTML addition to `admin-dashboard.html` is wrapped in `<!-- TASK-5 START -->` /
  `<!-- TASK-5 END -->`; every JS addition is wrapped in `// TASK-5 START` / `// TASK-5 END`. Verified
  programmatically: all three HTML `TASK-5` blocks are tag-balanced, the JS `TASK-5` block is
  brace/paren-balanced (after stripping comments and string/template literals), no duplicate `id`
  was introduced (the one duplicate `id="editCategory"` found in the file pre-dates this task,
  confirmed present in the pristine upload before any edit), and every `onclick`/`onchange` handler
  added — both static and dynamically built inside template strings — resolves to an actually-defined
  function.
- Every "Old code" snippet above was copied character for character from the original
  `admin-dashboard.html` supplied for this task, and re-confirmed (grep, this pass) to appear exactly
  once in that original file: the nav-rail pair (lines 1958–1959), the `applicationsEmpty`/panel-close
  block (lines 2233–2239), the `uploadPreviewContent`/modal-close block (lines 2630–2636), the
  `switchPanel()` `'applications'` branch (line 4405), and the `// INIT` line (line 7359) — each is
  unique in the file.
- Every new file in `unified-status-task-5.zip` (`installments/get-bnpl-applications.php`,
  `installments/bnpl-notes-lib.php`, `installments/update-bnpl-notes.php`, plus this completion file,
  `Task-5-Completion.md`, at the zip root) is listed under both Files Changed and New files with the
  matching path, and nothing else is in the zip — confirmed via `unzip -l`.
- The zip contains no secret, no `config.php`, no `pet.json`, no `.env`, and nothing under
  `applications/`, `uploads/`, `chat-media/`, or `logs/`. All three PHP files read the admin token from
  `.env` at runtime via `file_get_contents()` rather than hardcoding any credential.
- The new `installments/` folder is explicitly flagged above (see "⚠️ NEW FOLDER FLAG" under Files
  Changed) as not present in either project summary's folder structure, naming exactly where the
  integrator needs to create it.
- **Sandbox limitation, stated plainly rather than overstated:** no PHP interpreter is available in this
  environment (same limitation Task 4 flagged), so the three new PHP files were checked with structural
  lint (balanced braces/parens/brackets after stripping comments and string literals) rather than
  actually executed — their PHP logic was never run against a real PHP engine. What *was* possible, and
  was done (see Addendum 2): the actual `admin-dashboard.html` was served from a local static file
  server and driven in a real headless Chromium browser with Playwright, with every BNPL network call
  route-stubbed to a mock backend built from the real schema/response shapes (not guessed). That caught
  three real bugs a structural check alone would have missed — a double-`$` display bug, a missing
  audit-trail field on reject, and a modal z-index stacking bug — all now fixed and re-verified. This
  exercises the HTML/JS/DOM logic for real; it does not substitute for running the three new PHP files
  against an actual PHP engine and the real file layout, which is still recommended before this ships.
