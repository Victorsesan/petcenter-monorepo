# Task E5 - Completion

## Task Number
TASK E5 — Super admin test-account panel and whitelist (Complexity: Medium)

## What Was Required
A new "Test Accounts" section in the super admin dashboard, cloned from an existing management panel's form-and-list pattern (the Admin Profiles panel), so the CEO can create working test visitor and test merchant accounts for pre-launch testing.
- **Create:** admin picks test visitor or test merchant; the panel generates a unique username and password (shown once, never kept in plaintext) and writes an account record with `is_test_account: true`. A test merchant is created with `verification_status: verified`.
- **Whitelist:** this task only sets the `is_test_account` flag correctly. C1a, C2 and C6 check it; this task must confirm it tested those paths once their completion files exist (see pending items below).
- **Manual reviews:** a form (merchant, thumb, review text, reviewer display name, date) and a documented JSON import, both writing Review records with `is_seeded: true`.
- **List and delete:** a list of test accounts; delete removes the account and its seeded reviews.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `admin-dashboard.html` | `admin/index.html` | Changed (5 anchored additions, see Code Changes) | Yes - shared file; C1b's Reports additions already present in the version supplied, no conflict |
| `test-accounts.php` | `admin/test-accounts.php` | New file - in the zip | No |
| `Task-E5-Completion.md` | none - zip root, documentation only, do NOT deploy | New (this file, placed in the zip as instructed) | No |

## Code Changes

Every old snippet below was copied from the **original** `admin-dashboard.html` supplied to me, and was machine-checked to appear exactly once in it. Every new snippet was machine-checked to appear verbatim, exactly once, in the final file. Nothing in the original was removed or altered: each new snippet is the old anchor, unchanged, with the E5 block added next to it (before or after, as stated per change). Code is commented per Rule 6 and carries `TASK-E5 START` / `TASK-E5 END` anchors.

### `admin-dashboard.html` → live path `admin/index.html` — Change 1: Test Accounts icon-rail button

Insertion point: the new code goes **after** the old anchor below (Reports rail button (C1b)). The old anchor is replaced by the new code, which repeats it unchanged.

<!-- TASK-E5 START -->
**Old code:**
```html
      <!-- TASK-C1b START — Reports rail button (same markup as its siblings; mobile menu clones it automatically) -->
      <button class="admin-icon-rail__item" data-panel="reports" onclick="switchPanel('reports')" title="Reports"><i class="fas fa-flag"></i></button>
      <!-- TASK-C1b END -->
```
**New code:**
```html
      <!-- TASK-C1b START — Reports rail button (same markup as its siblings; mobile menu clones it automatically) -->
      <button class="admin-icon-rail__item" data-panel="reports" onclick="switchPanel('reports')" title="Reports"><i class="fas fa-flag"></i></button>
      <!-- TASK-C1b END -->
      <!-- TASK-E5 START — Test Accounts rail button. Same markup pattern as every sibling
           icon-rail button above (cloned per Rule 7, not reinvented). -->
      <button class="admin-icon-rail__item" data-panel="testAccounts" onclick="switchPanel('testAccounts')" title="Test Accounts"><i class="fas fa-user-astronaut"></i></button>
      <!-- TASK-E5 END -->
```
<!-- TASK-E5 END -->

### `admin-dashboard.html` → live path `admin/index.html` — Change 2: `switchPanel()` hook

Insertion point: the new code goes **after** the old anchor below (Reports hook inside `switchPanel()` (C1b)). The old anchor is replaced by the new code, which repeats it unchanged.

<!-- TASK-E5 START -->
**Old code:**
```javascript
  // TASK-C1b START — Reports panel: upgrade its filter selects to the custom dropdown, then load
  if (panel === 'reports') {
    upgradeNativeSelectsToCustom();
    initCustomSelects();
    loadReports();
  }
  // TASK-C1b END
```
**New code:**
```javascript
  // TASK-C1b START — Reports panel: upgrade its filter selects to the custom dropdown, then load
  if (panel === 'reports') {
    upgradeNativeSelectsToCustom();
    initCustomSelects();
    loadReports();
  }
  // TASK-C1b END
  // TASK-E5 START — Test Accounts panel: upgrade the account-type select in the
  // create form to the dashboard's custom dropdown (design-system Section 24
  // shell), same call pattern as every other panel above, then load the list.
  if (panel === 'testAccounts') {
    upgradeNativeSelectsToCustom();
    initCustomSelects();
    loadTestAccounts();
  }
  // TASK-E5 END
```
<!-- TASK-E5 END -->

### `admin-dashboard.html` → live path `admin/index.html` — Change 3: Test Accounts panel markup

Insertion point: the new code goes **before** the old anchor below (start of the Reviews panel). The old anchor is replaced by the new code, which repeats it unchanged.

<!-- TASK-E5 START -->
**Old code:**
```html
    <!-- REVIEWS PANEL -->
    <div id="chatReviewsPanel" class="admin-panel" style="display:none;">
```
**New code:**
```html
    <!-- TASK-E5 START — TEST ACCOUNTS PANEL. Cloned from the Admin Profiles panel just above
         (header row + "+ New" button + form-area toggle + card grid list pattern) per Rule 7 —
         this is the "existing management panel's form-and-list pattern" the task calls for.
         Scope: create test visitor/merchant accounts (is_test_account:true), seed reviews on
         them by hand or JSON import, list and delete. Backend: test-accounts.php (same
         folder). The account-record shape it writes is documented in that file's header
         and in Task-E5-Completion.md; no account schema was supplied with this task, so
         that shape is flagged there as an assumption awaiting CEO confirmation. -->
    <div id="testAccountsPanel" class="admin-panel" style="display:none;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:10px;">
        <div style="flex:1 1 320px;">
          <h2 style="font-family:'Cormorant Garamond',serif;color:#10214b;margin-bottom:6px;">Test Accounts</h2>
          <p style="color:#888;font-size:14px;">Create working visitor/merchant accounts for pre-launch testing. These are whitelisted everywhere reports, anti-fraud flags, and reviews get written — actions on these accounts are never recorded. Delete all of these before real users arrive.</p>
        </div>
        <button onclick="openTestAccountForm()" class="pc-btn pc-btn--primary">+ New Test Account</button>
      </div>

      <!-- Create form area, toggled open/closed exactly like #adminProfileFormArea above -->
      <div id="testAccountFormArea" style="display:none;margin-bottom:24px;"></div>

      <div id="testAccountsList" style="display:grid;gap:14px;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));"></div>
      <div id="testAccountsEmpty" style="display:none;padding:48px 24px;text-align:center;">
        <p style="color:#aaa;font-size:14px;">No test accounts yet. Click "+ New Test Account" to create the first one.</p>
      </div>

      <!-- Per-account review seeding area (hand-entered form + JSON import), toggled open
           beneath the card whose "Seed Review" button was clicked. -->
      <div id="seedReviewFormArea" style="display:none;margin-top:24px;"></div>
    </div>
    <!-- TASK-E5 END -->

    <!-- REVIEWS PANEL -->
    <div id="chatReviewsPanel" class="admin-panel" style="display:none;">
```
<!-- TASK-E5 END -->

### `admin-dashboard.html` → live path `admin/index.html` — Change 4: one-time credentials modal

Insertion point: the new code goes **before** the old anchor below (upload/file preview modal comment line). The old anchor is replaced by the new code, which repeats it unchanged.

<!-- TASK-E5 START -->
**Old code:**
```html
<!-- TASK-4 (CEO task-list item 6): upload/file preview modal — was Use B
```
**New code:**
```html
<!-- TASK-E5 START — TEST ACCOUNT CREDENTIALS MODAL. Same .pc-modal-shell chrome as
     approveModal/rejectModal above (cloned per Rule 7) — shows the generated username/
     password exactly once, since the password is never stored in a form the admin can
     retrieve again after this (see test-accounts.php: only a hash is stored server-side). -->
<div id="testAccountCredsModal" class="pc-modal-shell" style="position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(16,33,75,0.65);display:none;align-items:center;justify-content:center;z-index:10000;padding:20px;">
  <div style="width:100%;max-width:440px;background:#fff;border-radius:14px;padding:32px;box-shadow:0 20px 60px rgba(16,33,75,0.25);">
    <h3 style="font-family:'Cormorant Garamond',serif;font-size:22px;color:#10214b;margin:0 0 8px;">Test account created</h3>
    <p style="color:#7a6d5f;font-size:14px;margin-bottom:20px;">Save these now — the password is shown once and cannot be retrieved again after you close this.</p>
    <div style="background:#f9f7f4;border:1px solid rgba(16,33,75,0.1);border-radius:10px;padding:16px;margin-bottom:20px;">
      <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#7a6d5f;margin-bottom:4px;">Username</div>
      <div id="credsUsername" style="font-family:'Sora',monospace;font-size:15px;color:#10214b;font-weight:600;margin-bottom:14px;"></div>
      <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#7a6d5f;margin-bottom:4px;">Password</div>
      <div id="credsPassword" style="font-family:'Sora',monospace;font-size:15px;color:#10214b;font-weight:600;"></div>
    </div>
    <div style="display:flex;gap:10px;">
      <button type="button" onclick="copyTestAccountCreds()" class="pc-btn pc-btn--ghost" style="flex:1;"><i class="fas fa-copy" style="font-size:11px;margin-right:6px;"></i>Copy both</button>
      <button type="button" onclick="closeTestAccountCreds()" class="pc-btn pc-btn--primary" style="flex:1;">I've saved this — Close</button>
    </div>
  </div>
</div>
<!-- TASK-E5 END -->

<!-- TASK-4 (CEO task-list item 6): upload/file preview modal — was Use B
```
<!-- TASK-E5 END -->

### `admin-dashboard.html` → live path `admin/index.html` — Change 5: Test Accounts JS

Insertion point: the new code goes **before** the old anchor below (`loadChatReviews()` function). The old anchor is replaced by the new code, which repeats it unchanged.

<!-- TASK-E5 START -->
**Old code:**
```javascript
async function loadChatReviews() {
```
**New code:**
```javascript
// TASK-E5 START — TEST ACCOUNTS PANEL JS. Structurally mirrors the Admin
// Profiles block just above (loadAdminProfiles/renderAdminProfileCards/
// openAdminProfileForm/deleteAdminProfile) per Rule 7 — same load/render/
// open-form/delete shape, adapted for test-accounts.php's action names and
// the extra review-seeding sub-flow this panel also needs.

// Loads every test account from the backend and renders the card grid.
async function loadTestAccounts() {
  try {
    const token = getAdminToken();
    const r = await fetch('test-accounts.php?action=list', { headers: { 'X-Admin-Token': token } });
    const json = await r.json();
    window.dlog('E5: loaded test accounts', 'info');
    renderTestAccountCards(json.accounts || []);
  } catch (e) {
    window.dlog('E5: failed to load test accounts — ' + e.message, 'error');
  }
}

// Renders one card per test account: type badge, username, country,
// verification badge (merchants only), Seed Review / Import JSON buttons
// (merchants only, since reviews attach to merchants per the Review
// record contract), and Delete.
function renderTestAccountCards(accounts) {
  const list = document.getElementById('testAccountsList');
  const empty = document.getElementById('testAccountsEmpty');
  if (!list) return;
  list.innerHTML = '';
  if (!accounts.length) {
    empty.style.display = 'block';
    return;
  }
  empty.style.display = 'none';
  accounts.forEach(a => {
    const card = document.createElement('div');
    card.style.cssText = 'background:#fff;border:1.5px solid rgba(16,33,75,0.1);border-radius:12px;padding:16px;display:flex;flex-direction:column;gap:10px;';
    const typeBadge = a.account_type === 'merchant'
      ? `<span style="background:rgba(215,189,136,0.25);color:#10214b;font-size:11px;font-weight:700;padding:2px 8px;border-radius:999px;">MERCHANT</span>`
      : `<span style="background:rgba(16,33,75,0.08);color:#10214b;font-size:11px;font-weight:700;padding:2px 8px;border-radius:999px;">VISITOR</span>`;
    const verifiedBadge = a.verification_status === 'verified'
      ? `<i class="fas fa-circle-check" style="color:#0d7c4a;font-size:12px;" title="Verified"></i>` : '';
    card.innerHTML = `
      <div style="display:flex;align-items:center;justify-content:space-between;">
        ${typeBadge}
        <span style="font-size:11px;color:#a89c8a;">${escapeHtml((a.created_at || '').slice(0, 10))}</span>
      </div>
      <div>
        <div style="font-weight:700;font-size:14px;color:#10214b;font-family:'Sora',monospace;">${escapeHtml(a.username)} ${verifiedBadge}</div>
        <div style="font-size:12px;color:#7a6d5f;">${escapeHtml(a.country || '')} &middot; is_test_account: true${a.account_type === 'merchant' ? ' &middot; ' + (a.seeded_review_count || 0) + ' seeded review(s)' : ''}</div>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        ${a.account_type === 'merchant' ? `
        <button onclick="openSeedReviewForm('${escapeHtml(a.account_id)}','${escapeHtml(a.username)}')" class="pc-btn pc-btn--ghost" style="flex:1;font-size:12px;padding:6px;min-width:110px;"><i class="fas fa-star" style="font-size:10px;margin-right:5px;"></i>Seed Review</button>
        ` : ''}
        <button onclick="deleteTestAccount('${escapeHtml(a.account_id)}','${escapeHtml(a.username)}')" class="pc-btn" style="flex:1;font-size:12px;padding:6px;min-width:90px;background:linear-gradient(135deg,#c0392b,#a93226);"><i class="fas fa-trash" style="font-size:10px;margin-right:5px;"></i>Delete</button>
      </div>`;
    list.appendChild(card);
  });
}

// Opens the create form: account type (visitor/merchant) + country, both
// required. account_type is a native <select> upgraded to the dashboard's
// custom dropdown by upgradeNativeSelectsToCustom()/initCustomSelects() —
// called again after this form is injected, since those functions only
// upgrade selects already in the DOM at call time.
function openTestAccountForm() {
  const formArea = document.getElementById('testAccountFormArea');
  if (!formArea) return;
  formArea.style.display = 'block';
  formArea.scrollIntoView({ behavior: 'smooth' });

  formArea.innerHTML = `
    <div style="background:#f9f7f4;border-radius:12px;padding:20px;">
      <h3 style="font-family:'Cormorant Garamond',serif;color:#10214b;margin-bottom:16px;">New Test Account</h3>
      <form id="testAccountForm">
        <div class="pc-form-group" style="margin-bottom:14px;">
          <label class="pc-form-label">Account Type</label>
          <select id="testAcctType" name="account_type" class="pc-form-input" required>
            <option value="visitor">Test Visitor</option>
            <option value="merchant">Test Merchant</option>
          </select>
        </div>
        <div class="pc-form-group" style="margin-bottom:20px;">
          <label class="pc-form-label">Country</label>
          <select id="testAcctCountry" name="country" class="pc-form-input" required>
            <option value="US">United States</option>
            <option value="CA">Canada</option>
            <option value="GB">United Kingdom</option>
            <option value="AU">Australia</option>
          </select>
        </div>
        <div style="display:flex;gap:10px;">
          <button type="submit" class="pc-btn pc-btn--primary" style="flex:1;">Create Account</button>
          <button type="button" onclick="document.getElementById('testAccountFormArea').style.display='none';" class="pc-btn pc-btn--ghost" style="flex:1;">Cancel</button>
        </div>
        <div id="testAcctFormError" style="color:#ef4444;font-size:12px;margin-top:8px;display:none;"></div>
      </form>
    </div>`;

  // Re-run the dashboard's standard select-upgrade pass so the two selects
  // just injected above get the same Section-24-shelled dropdown as every
  // other dropdown in this file (per Directive 9a — never a bare <select>).
  upgradeNativeSelectsToCustom();
  initCustomSelects();

  document.getElementById('testAccountForm').addEventListener('submit', async e => {
    e.preventDefault();
    const errEl = document.getElementById('testAcctFormError');
    errEl.style.display = 'none';
    const token = getAdminToken();
    const fd = new FormData(e.target);
    fd.append('action', 'create');
    fd.append('token', token);
    try {
      const r = await fetch('test-accounts.php', { method: 'POST', headers: { 'X-Admin-Token': token }, body: fd });
      const json = await r.json();
      if (json.success) {
        window.dlog('E5: test account created — ' + json.username, 'ok');
        formArea.style.display = 'none';
        loadTestAccounts();
        // Show the one-time credentials modal — this is the only place the
        // plaintext password is ever visible, per the task's password-
        // storage requirement.
        document.getElementById('credsUsername').textContent = json.username;
        document.getElementById('credsPassword').textContent = json.password;
        PC.modal.open('testAccountCredsModal');
      } else {
        errEl.textContent = json.error || 'Create failed';
        errEl.style.display = 'block';
      }
    } catch (err) {
      errEl.textContent = 'Network error: ' + err.message;
      errEl.style.display = 'block';
      window.dlog('E5: create error — ' + err.message, 'error');
    }
  });
}

// Deletes a test account (and, server-side, its seeded reviews file).
async function deleteTestAccount(accountId, username) {
  if (!confirm(`Delete test account "${username}"? This also deletes any reviews seeded on it. This cannot be undone.`)) return;
  try {
    const token = getAdminToken();
    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('account_id', accountId);
    fd.append('token', token);
    const r = await fetch('test-accounts.php', { method: 'POST', headers: { 'X-Admin-Token': token }, body: fd });
    const json = await r.json();
    if (json.success) {
      window.dlog('E5: test account deleted — ' + accountId, 'ok');
      loadTestAccounts();
    } else {
      alert(json.error || 'Delete failed');
    }
  } catch (e) {
    alert('Network error: ' + e.message);
    window.dlog('E5: delete error — ' + e.message, 'error');
  }
}

// Opens the per-merchant review-seeding area: a hand-entered form (thumb,
// text, reviewer name, date) plus a JSON-import textarea, both writing
// Review records with is_seeded:true via test-accounts.php.
function openSeedReviewForm(merchantId, username) {
  const area = document.getElementById('seedReviewFormArea');
  if (!area) return;
  area.style.display = 'block';
  area.scrollIntoView({ behavior: 'smooth' });

  area.innerHTML = `
    <div style="background:#f9f7f4;border-radius:12px;padding:20px;">
      <h3 style="font-family:'Cormorant Garamond',serif;color:#10214b;margin-bottom:4px;">Seed a review — ${escapeHtml(username)}</h3>
      <p style="color:#7a6d5f;font-size:13px;margin-bottom:18px;">Writes a Review record with is_seeded:true. Renders on the merchant's public profile exactly like a real review.</p>

      <form id="seedReviewForm" style="margin-bottom:24px;">
        <div class="pc-form-group" style="margin-bottom:14px;">
          <label class="pc-form-label">Thumb</label>
          <select id="seedThumb" name="thumb" class="pc-form-input" required>
            <option value="up">Thumbs up</option>
            <option value="down">Thumbs down</option>
          </select>
        </div>
        <div class="pc-form-group" style="margin-bottom:14px;">
          <label class="pc-form-label">Reviewer Display Name</label>
          <input name="reviewer_name" class="pc-form-input" required maxlength="100">
        </div>
        <div class="pc-form-group" style="margin-bottom:14px;">
          <label class="pc-form-label">Review Text (optional, 10-1000 characters)</label>
          <textarea name="review_text" class="pc-form-input" minlength="10" maxlength="1000" style="min-height:80px;resize:vertical;"></textarea>
        </div>
        <div class="pc-form-group" style="margin-bottom:18px;">
          <label class="pc-form-label">Date</label>
          <input name="date" type="date" class="pc-form-input">
        </div>
        <div style="display:flex;gap:10px;">
          <button type="submit" class="pc-btn pc-btn--primary" style="flex:1;">Add Review</button>
          <button type="button" onclick="document.getElementById('seedReviewFormArea').style.display='none';" class="pc-btn pc-btn--ghost" style="flex:1;">Close</button>
        </div>
        <div id="seedReviewError" style="color:#ef4444;font-size:12px;margin-top:8px;display:none;"></div>
        <div id="seedReviewSuccess" style="color:#0d7c4a;font-size:12px;margin-top:8px;display:none;"></div>
      </form>

      <div style="border-top:1px solid rgba(16,33,75,0.1);padding-top:18px;">
        <label class="pc-form-label" style="margin-bottom:6px;">Or import as JSON (array, max 200 reviews)</label>
        <p style="color:#7a6d5f;font-size:12px;margin-bottom:8px;line-height:1.5;">Required per review: <b>thumb</b> ("up" or "down") and <b>reviewer_display_name</b>. Optional: <b>review_text</b> (empty or 10-1000 characters), <b>created_at</b>, <b>listing_id</b>, <b>merchant_reply</b>, <b>merchant_reply_at</b>, <b>visibility</b> ("visible" or "hidden"). The merchant is the account you opened this form from, and is_seeded is always set to true. Rows that fail validation are skipped and reported; valid rows still import.</p>
        <textarea id="seedReviewJson" class="pc-form-input" placeholder='[{"thumb":"up","reviewer_display_name":"Jane D.","review_text":"Fast replies and a very honest listing.","created_at":"2026-08-01T12:00:00Z"},{"thumb":"down","reviewer_display_name":"Sam K.","review_text":""}]' style="min-height:90px;resize:vertical;font-family:monospace;font-size:12px;margin-bottom:10px;"></textarea>
        <button onclick="importSeedReviewsJson('${escapeHtml(merchantId)}')" class="pc-btn pc-btn--ghost">Import JSON</button>
        <div id="seedImportResult" style="color:#0d7c4a;font-size:12px;margin-top:8px;display:none;"></div>
      </div>
    </div>`;

  upgradeNativeSelectsToCustom();
  initCustomSelects();

  document.getElementById('seedReviewForm').addEventListener('submit', async e => {
    e.preventDefault();
    const errEl = document.getElementById('seedReviewError');
    const okEl = document.getElementById('seedReviewSuccess');
    errEl.style.display = 'none';
    okEl.style.display = 'none';
    const token = getAdminToken();
    const fd = new FormData(e.target);
    fd.append('action', 'seedReview');
    fd.append('merchant_id', merchantId);
    fd.append('token', token);
    try {
      const r = await fetch('test-accounts.php', { method: 'POST', headers: { 'X-Admin-Token': token }, body: fd });
      const json = await r.json();
      if (json.success) {
        window.dlog('E5: seeded review for ' + merchantId, 'ok');
        okEl.textContent = 'Review added.';
        okEl.style.display = 'block';
        e.target.reset();
        loadTestAccounts(); // refresh the seeded-review count on the cards
      } else {
        errEl.textContent = json.error || 'Save failed';
        errEl.style.display = 'block';
      }
    } catch (err) {
      errEl.textContent = 'Network error: ' + err.message;
      errEl.style.display = 'block';
    }
  });
}

// Imports a JSON array of Review-record-shaped objects for one merchant.
// Parses client-side first so a typo is reported instantly; the server then
// validates every row again and reports which rows it skipped and why.
async function importSeedReviewsJson(merchantId) {
  const textarea = document.getElementById('seedReviewJson');
  const resultEl = document.getElementById('seedImportResult');
  const json = textarea.value.trim();
  resultEl.style.display = 'none';
  if (!json) { PC.toast('Paste JSON to import first', 'error'); return; }
  try { JSON.parse(json); } catch (parseErr) { PC.toast('That is not valid JSON: ' + parseErr.message, 'error'); return; }
  try {
    const token = getAdminToken();
    const fd = new FormData();
    fd.append('action', 'importReviews');
    fd.append('merchant_id', merchantId);
    fd.append('reviews_json', json);
    fd.append('token', token);
    const r = await fetch('test-accounts.php', { method: 'POST', headers: { 'X-Admin-Token': token }, body: fd });
    const res = await r.json();
    if (res.success) {
      window.dlog('E5: imported ' + res.imported + ' seeded reviews for ' + merchantId + ', skipped ' + res.skipped, 'ok');
      // Server-provided row errors are shown as text, never as HTML.
      resultEl.textContent = 'Imported ' + res.imported + ' review(s)' + (res.skipped ? ', skipped ' + res.skipped + ': ' + (res.errors || []).join(' | ') : '.');
      resultEl.style.color = res.skipped ? '#c0392b' : '#0d7c4a';
      resultEl.style.display = 'block';
      if (res.imported) textarea.value = '';
      loadTestAccounts(); // refresh the seeded-review count on the cards
    } else {
      PC.toast(res.error || 'Import failed', 'error');
    }
  } catch (e) {
    PC.toast('Network error: ' + e.message, 'error');
    window.dlog('E5: import error — ' + e.message, 'error');
  }
}

// Copies "username / password" from the one-time credentials modal.
async function copyTestAccountCreds() {
  const text = document.getElementById('credsUsername').textContent + '\n' + document.getElementById('credsPassword').textContent;
  try { await navigator.clipboard.writeText(text); PC.toast('Copied username and password'); }
  catch (e) { PC.toast('Copy failed: select the text manually', 'error'); }
}

// Closes the credentials modal AND wipes the plaintext password from the DOM,
// so it cannot linger on the page after the admin has dismissed it.
function closeTestAccountCreds() {
  document.getElementById('credsUsername').textContent = '';
  document.getElementById('credsPassword').textContent = '';
  PC.modal.close('testAccountCredsModal');
}
// TASK-E5 END

async function loadChatReviews() {
```
<!-- TASK-E5 END -->

## New files

| Live path | What it is |
|---|---|
| `admin/test-accounts.php` | New file - in the zip. Backend for the Test Accounts panel: create, list and delete test accounts, seed or import reviews. Requires `admin/chat-config.php` (already live) for `chatRequireAdmin()` and `chatRespond()`. Its header comment documents the account record, the Review fields it writes, and the JSON import format. |

**NEW FOLDER - NOT IN EITHER SUMMARY DOCUMENT - THE INTEGRATOR MUST KNOW:**
`admin/test-accounts-data/` is a **new folder that does not exist in `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`'s folder structure**. It is **not in the zip**: `test-accounts.php` creates it automatically on first use (mode 0755) and writes a `Deny from all` `.htaccess` inside it, the same guard `chat-config.php` gives `chat-uploads-temp/`. It will hold two files, `accounts.json` (account records including `password_hash`) and `seeded-reviews.json`. Therefore:
1. The PHP user must be able to write inside `admin/` (the chat system already creates folders there, so this should already be true).
2. After first use, confirm `admin/test-accounts-data/.htaccess` exists and that requesting `.../admin/test-accounts-data/accounts.json` in a browser returns 403. If the host ignores `.htaccess`, move the folder outside the web root and change the `E5_DATA_DIR` constant at the top of `test-accounts.php`.
3. Do not include this folder or its contents in any deployment bundle or backup that is shared.

No new HTML pages were created: the panel lives inside the existing admin page, so the `index.html` naming rule is unaffected.

## Bugs Found Outside Task Scope
1. `admin-dashboard.html`, `handleLogout()` (approx. lines 4310-4330 of the supplied file): clears the pet, application and form lists but no panel's rendered rows, so data from one admin session stays in the hidden DOM until the next panel open. Harmless today because the login screen hides the dashboard; worth a one-line clear per panel. My panel wipes its plaintext password from the page when the credentials modal closes for this reason. Not changed (Scope Lock).
2. `ADMIN_DASHBOARD_PROJECT_SUMMARY.md` (v2.2 section) says the Lavender Mist palette was not CEO-approved and Navy/Gold/Orange should be used, while the supplied dashboard's CSS comments describe Lavender Mist as a "fixed assignment". Pre-existing; untouched.
3. Deleting a test account removes only the account and its seeded reviews, as specified. Listings, messages or reports a test account created while being tested are not removed by this task; the tasks that own those stores need a cleanup path before launch.
4. Path discrepancy: the delivery guide maps `admin-dashboard.html` to `admin/index.html`, but `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`'s folder structure lists it as `admin/admin-dashboard.html`. I used the guide's path in the Live path column. The integrator should confirm which is real.

## Self-Check Confirmation
- I touched only `admin-dashboard.html` (five anchored additions; stripping every `TASK-E5` block from the final file leaves it byte-identical to the original supplied file, checked by script) and created only `test-accounts.php`.
- Every code block carries `TASK-E5` anchors and comments; no emoji anywhere (checked by script).
- Every old snippet was copied from the original file and appears exactly once in it (machine-checked); every new snippet appears verbatim, exactly once, in the final file (machine-checked).
- Every file in the zip is listed in Files Changed with the same path (checked with `unzip -l`): `admin/test-accounts.php` and `Task-E5-Completion.md` (zip root, documentation only).
- No secrets, `config.php`, `.env`, `pet.json` or application data in the zip. No new setting is needed: authentication reuses the existing `ADMIN_TOKEN` through `chat-config.php`.
- **Dropdown confirmation:** three dropdowns were built (Account Type, Country, Thumb). All use `PETCENTER_DESIGN_SYSTEM_v1.0.md` Section 24's locked "Message Bubble" dropdown shell, through the dashboard's own `upgradeNativeSelectsToCustom()` + `initCustomSelects()`. The underlying `<select>` is only a hidden value holder (opacity 0, no pointer events). Each list has 4 items or fewer (5-item cap).
- **Shared contracts built against:** Account `is_test_account` (owned by E5) is `true` on every account created; Review record (owned by C6): every contract field is written, plus one flagged extra (below); Merchant `verification_status` (owned by B2): test merchants are `verified`. **Reconciliation with C1a, C1b, C2, C6 and B2 has not happened** - none of their completion files were supplied.
- **How it was tested:** Playwright (Chromium 1.56), 23/23 checks passed against the real dashboard file - nav button and mobile-drawer clone, panel switching, both dropdown types, create visitor and merchant, one-time credentials modal and password wipe, flag and status values, hand-entered review with every contract field, JSON import (2 imported, 1 skipped with a row-level reason), invalid JSON rejected client-side, delete removing account and reviews, no horizontal overflow at 390px, no JS page errors. Screenshots and the test scripts are delivered alongside as `Task-E5-evidence/` (not in the zip, because they are not site files). **Limit: no PHP runtime was available, so `test-accounts.php` was never executed.** The browser test ran against a Node stand-in that mirrors its rules.

### Pending, assumptions and doubts (recommended default chosen and flagged; the integrator can correct)
1. **PHP never run.** Run `php -l admin/test-accounts.php` and one real create/list/delete request before sign-off. Needs PHP 7.1+, the `mbstring` extension, and `admin/chat-config.php` present.
2. **Account record store.** No supplied document defines it. Default chosen: protected JSON in `admin/test-accounts-data/accounts.json`, with only the fields the task doc names (`account_id`, `account_type`, `username`, `password_hash`, `country`, `is_test_account`, `verification_status`, `created_at`) and `password_hash()` with `PASSWORD_DEFAULT`. If the real login uses another store (for example a MySQL table), test accounts cannot log in until the account reads and writes in `test-accounts.php` (`e5Read`, `e5Locked`, `e5FindTestMerchant`, and the `list`, `create`, `delete` cases) are pointed at it. Fields the real record may also need, not written by me: `email`, a display name, merchant name, individual vs organization, activity tier.
3. **Review store.** Seeded reviews go to `admin/test-accounts-data/seeded-reviews.json`. Task C6's display and moderation must read the same place, or this task must write to C6's store.
4. **Contract deviation, flagged:** seeded reviews carry one extra optional field `reviewer_display_name` (and `reviewer_id` is null), because this task's spec requires a reviewer display name and the Review contract has no field for it. C6 should adopt it or name the contract field that should carry it.
5. **Country set** US, CA, GB, AU was chosen to match A1's four currencies; confirm.
6. **Whitelist tests pending:** C1a (report), C2 (detectors) and C6 (thumb/review) each writing nothing against a test merchant. To be added under a second-round section once their completion files exist.
7. **Not demonstrable from this task alone:** logging in as a test account (the login system is separate and out of scope) and a seeded review rendering on the public merchant profile (needs B6 and C6).
8. **Launch hygiene (recommendation):** delete all test accounts before real users arrive; consider removing `admin/test-accounts.php` and `admin/test-accounts-data/` at launch.
9. **Delivery naming:** the guide asks for `Task-E5-NewFiles.zip` with the completion file kept out of the zip; the CEO's later instruction was a single zip named after the project and task number containing both. I followed the CEO: `Rehoming-Platform-Task-E5.zip`.

## SECOND VERIFICATION ROUND
A full re-audit of the first delivery against both project summaries, the task doc's rules and shared contracts, the design system, and the supplied dashboard file. Changes made, and why:

1. **Auth re-implemented instead of reused (Rule 7 violation) - fixed.** `test-accounts.php` now does `require_once 'chat-config.php'` and uses `chatRequireAdmin()` / `chatRespond()`.
2. **Password hashes were in a web-readable JSON file - fixed.** `/admin/` is a public webroot. Data now lives in `test-accounts-data/` with an auto-written `Deny from all` `.htaccess`.
3. **Unlocked read-modify-write could lose data - fixed** with exclusive `flock` around each cycle (and I removed a claim in my first draft that `chatSaveBotProfile()` pattern covered this; it only covers whole-file overwrite).
4. **Missing server-side validation - fixed:** country allow-list, unique username and account_id, review text 10-1000 chars (C6's rule), date parsing, row cap, thumb and visibility enums.
5. **Reviews could be seeded onto any merchant id - fixed:** seeding and import now require an existing test merchant.
6. **JSON import format was undocumented - fixed:** documented in the PHP header, in the panel UI, and above; skipped rows are reported with the reason.
7. **Delete removed reviews by file, now removes only `is_seeded` reviews for that merchant.**
8. **Dashboard fixes:** removed an unused element, corrected a comment that pointed to a file that does not exist, plaintext password is wiped from the DOM when the credentials modal closes, added a Copy button, seeded-review counts on cards, client-side JSON parse check, header layout fix.
9. **First completion file was non-compliant (Rule 8a):** it omitted the full JS and added non-standard sections. Rebuilt with all five code blocks copied exactly, and the contract notes moved into Self-Check Confirmation.
10. **Rule 15 / version control:** pristine reference copy saved as `admin-dashboard-original.html`.

**Verification run:** Playwright (Chromium 1.56) against the real dashboard file, 23/23 checks passed: nav button and mobile-drawer clone, panel switching, both dropdown types open and set their hidden value, create visitor and merchant, one-time credentials modal and password wipe, `is_test_account` and `verification_status` values, hand-entered review with every contract field, JSON import (2 imported, 1 skipped with row-level reason), invalid JSON rejected client-side, delete removing account and reviews, no horizontal overflow at 390px, no JS page errors. **Honest limit:** no PHP runtime is available here, so `test-accounts.php` was NOT executed. The browser test ran against a small Node stand-in that mirrors the PHP's validation rules (`Task-E5-evidence/mock-server.js`). The PHP was reviewed by hand against PHP 7.1+ syntax but needs a real run and a `php -l` on the server before sign-off.

Screenshots:
- `Task-E5-evidence/01-create-form.png` - Create form (type and country dropdowns)
- `Task-E5-evidence/02-dropdown-open.png` - Account-type dropdown open (design-system Section 24 shell)
- `Task-E5-evidence/03-credentials-modal.png` - One-time credentials modal
- `Task-E5-evidence/04-seed-and-import.png` - Seed form plus JSON import result (2 imported, 1 skipped with reason)
- `Task-E5-evidence/05-after-delete.png` - After deleting the merchant
- `Task-E5-evidence/06-mobile.png` - 390px mobile layout
