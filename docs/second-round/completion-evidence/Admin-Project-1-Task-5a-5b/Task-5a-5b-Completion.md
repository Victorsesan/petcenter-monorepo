# Task Number
Task 5a (Password System Core) and Task 5b (Per-Device Management),
quoted in full from the assignment doc:

> **5a. Password System Core** — Build the core password system...
> enforce the CEO's settled password policy... Build against the
> `AuthGate` contract for the login-verification UI.

> **5b. Per-Device Management (Optional Enhancement)** — A settings
> view listing every device remembered for a given user (label,
> last-used date) with a per-device "forget" action, instead of one
> global toggle.

This hand-in reformat covers both, since 5b was built as an additive
layer on top of 5a's own files per the CEO's prior confirmation to
proceed with it.

# What Was Required
A per-chat password system: 12-character minimum, no forced
composition rules, rejected against a breached-password list, a live
strength meter, hashed storage, session-token invalidation on
set/change, and an optional "remember this device" cookie with a
single revoke-all control (5a) — plus, once confirmed, a fuller
per-device list with label/last-used date and a per-device "forget"
action instead of only the global revoke-all (5b). Built against the
published `chat_auth`/`AuthGate`/`chat_remember_device` contracts,
since Task 3's real files did not exist yet at build time.

# Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `chat-widget.js` | `admin/chat-widget.js` | Changed | No — only this task's anchor-commented blocks were touched; see Code Changes below for the exact four edit sites |
| `chat-password-auth.php` | `admin/chat-password-auth.php` | New | No |
| `chat-password.php` | `admin/chat-password.php` | New | No |
| `chat-remember-device.php` | `admin/chat-remember-device.php` | New | No |
| `chat-remember-device-setup.php` | `admin/chat-remember-device-setup.php` | New | No — **one-time script: run once, then DELETE** (creates the `chat_remember_device` table) |

## NEW FOLDERS / NEW PATHS — INTEGRATOR, READ THIS

**NO NEW FOLDER IS NEEDED. Nothing in this hand-in creates, requires or
assumes a folder that does not already exist on the live site.**

All four new files go **flat inside the existing `admin/` folder**, like
every other `chat-*.php` file in the "File Structure on Server" section of
`ADMIN_DASHBOARD_PROJECT_SUMMARY.md`. The *filenames* are new (none of
them appear in either project summary), but the *folder* is not.

**Do NOT create `admin/includes/`.** An `includes/` subfolder existed only
on the author's own working disk. It is not a convention anywhere on the
live site, the zip does not contain one, and the `require_once` lines in
`chat-password-auth.php` already point at the flat files
(`__DIR__ . '/chat-password.php'`, `__DIR__ . '/chat-remember-device.php'`).

**No `.html` page is created or changed by this task**, so the
`folder/index.html` live-naming rule does not come into play. The one
changed file, `chat-widget.js`, is an existing admin file at
`admin/chat-widget.js`.

**This zip also contains `Task-5a-5b-Completion.md` at the zip root. That
file is documentation for the integrator, NOT a site file — do not
deploy it into the site folder.**

Live paths come from the "File Structure on Server" section of
`ADMIN_DASHBOARD_PROJECT_SUMMARY.md` and the delivery guide's rule that
admin PHP/JS lives at `admin/` + the same file name.

# Code Changes

### `chat-widget.js` → live path `admin/chat-widget.js`
Four edit sites against the original file as given. Each insertion
site's "Old code" is the exact, unique original line it was inserted
directly after; the one truly modified line (the sheet's row array)
is shown as a real old→new replacement.

---

**Site 1 of 4 — new CSS block, inserted after the existing base
accessibility icon-button style (note: NOT after the `:hover`/`.active`
state rules — those two rules sit immediately after this inserted
block in the real file, not before it; this was corrected after a
direct, automated check against the real original file caught the
first draft of this anchor pointing at the wrong line):**

<!-- TASK-5A START -->
**Old code:**
```css
  .pcw-a11y-icon-btn {
    width: 40px; height: 40px; border-radius: 50%; border: 1.5px solid rgba(255,255,255,0.15);
    background: rgba(255,255,255,0.04); color: #d7bd88; cursor: pointer;
    display: flex; align-items: center; justify-content: center; font-size: 15px;
    transition: border-color 0.15s ease, background 0.15s ease;
  }
```
**New code:**
```css
  .pcw-a11y-icon-btn {
    width: 40px; height: 40px; border-radius: 50%; border: 1.5px solid rgba(255,255,255,0.15);
    background: rgba(255,255,255,0.04); color: #d7bd88; cursor: pointer;
    display: flex; align-items: center; justify-content: center; font-size: 15px;
    transition: border-color 0.15s ease, background 0.15s ease;
  }

  /* TASK-5A START
     Password panel — reuses pcw-option-row/-icon/-label/-chevron and the
     accessibility panel's expand pattern (max-height/opacity transition)
     exactly, rather than inventing a second visual language for one more
     settings row. New classes below are only for what those don't
     already cover: the password input itself, the strength meter, and
     the per-device list rows (Task 5b). */
  .pcw-password-panel {
    max-height: 0; opacity: 0; overflow: hidden;
    transition: max-height 0.3s ease, opacity 0.25s ease, margin 0.3s ease;
  }
  .pcw-password-panel.open { max-height: 420px; opacity: 1; margin-top: 10px; padding-bottom: 4px; }
  .pcw-pw-input-row { position: relative; display: flex; align-items: center; margin-bottom: 8px; }
  .pcw-pw-input {
    flex: 1; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15);
    border-radius: 8px; padding: 9px 36px 9px 10px; color: #e5e5e5;
    font-family: 'Sora', system-ui, sans-serif; font-size: 13px;
  }
  .pcw-pw-input:focus { outline: none; border-color: #ED7624; }
  .pcw-pw-visibility-btn {
    position: absolute; right: 6px; background: none; border: none; color: rgba(255,255,255,0.5);
    cursor: pointer; display: flex; padding: 4px;
  }
  .pcw-pw-strength-track { height: 5px; border-radius: 3px; background: rgba(255,255,255,0.12); overflow: hidden; margin-bottom: 4px; }
  .pcw-pw-strength-fill { height: 100%; width: 0%; border-radius: 3px; transition: width 0.25s ease, background-color 0.25s ease; }
  .pcw-pw-strength-fill.score-0, .pcw-pw-strength-fill.score-1 { background: #ef4444; }
  .pcw-pw-strength-fill.score-2 { background: #ED7624; }
  .pcw-pw-strength-fill.score-3 { background: #d7bd88; }
  .pcw-pw-strength-fill.score-4 { background: #10b981; }
  .pcw-pw-strength-fill.score-0 { width: 5%; } .pcw-pw-strength-fill.score-1 { width: 25%; }
  .pcw-pw-strength-fill.score-2 { width: 50%; } .pcw-pw-strength-fill.score-3 { width: 75%; } .pcw-pw-strength-fill.score-4 { width: 100%; }
  .pcw-pw-strength-label { font-family: 'Sora', system-ui, sans-serif; font-size: 11px; color: rgba(255,255,255,0.5); margin-bottom: 10px; display: block; }
  .pcw-pw-remember-row { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; }
  .pcw-pw-remember-row label { font-family: 'Sora', system-ui, sans-serif; font-size: 12.5px; color: #e5e5e5; cursor: pointer; }
  .pcw-pw-submit-btn {
    width: 100%; background: #ED7624; color: #fff; border: none; border-radius: 8px; padding: 9px;
    font-family: 'Sora', system-ui, sans-serif; font-weight: 700; font-size: 13px; cursor: pointer;
  }
  .pcw-pw-submit-btn:disabled { opacity: 0.6; cursor: not-allowed; }
  .pcw-pw-error { color: #f87171; font-family: 'Sora', system-ui, sans-serif; font-size: 11.5px; margin-top: 6px; min-height: 14px; }
  .pcw-pw-revoke-all-btn {
    width: 100%; background: none; border: 1px solid rgba(239,68,68,0.5); color: #f87171; border-radius: 8px;
    padding: 8px; font-family: 'Sora', system-ui, sans-serif; font-size: 12px; font-weight: 700; cursor: pointer; margin-top: 12px;
  }
  /* TASK-5B START — per-device list, nested inside the same password panel */
  .pcw-device-list-toggle {
    width: 100%; display: flex; align-items: center; justify-content: space-between; background: none; border: none;
    padding: 10px 0 4px; font-family: 'Sora', system-ui, sans-serif; font-size: 12px; font-weight: 700; color: #e5e5e5; cursor: pointer;
  }
  .pcw-device-list-body { max-height: 0; opacity: 0; overflow: hidden; transition: max-height 0.3s ease, opacity 0.25s ease; }
  .pcw-device-list-body.open { max-height: 240px; opacity: 1; overflow-y: auto; }
  .pcw-device-item {
    display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 8px 0;
    border-bottom: 1px solid rgba(255,255,255,0.07);
  }
  .pcw-device-item:last-child { border-bottom: none; }
  .pcw-device-item-label { font-family: 'Sora', system-ui, sans-serif; font-size: 12.5px; color: #e5e5e5; }
  .pcw-device-item-meta { font-family: 'Sora', system-ui, sans-serif; font-size: 10.5px; color: rgba(255,255,255,0.45); }
  .pcw-device-item-forget {
    background: none; border: 1px solid rgba(255,255,255,0.2); color: #e5e5e5; border-radius: 6px; padding: 4px 8px;
    font-family: 'Sora', system-ui, sans-serif; font-size: 10.5px; cursor: pointer; white-space: nowrap;
  }
  .pcw-device-empty { font-family: 'Sora', system-ui, sans-serif; font-size: 11.5px; color: rgba(255,255,255,0.45); padding: 8px 0; }
  /* TASK-5B END */
  /* TASK-5A END */
```
<!-- TASK-5A END -->

---

**Site 2 of 4 — new helper functions and `buildPasswordPanel()`,
inserted between the existing sound-effect helper functions and
`buildOptionsSheet()` (the anchor includes the `function buildOptionsSheet() {`
line so the blank lines around the insertion are exact):**

<!-- TASK-5A START -->
**Old code:**
```js
  function pcwPlayRecordStopSound() { pcwPlayTone(340, 70, 'square', 0.05); }


  function buildOptionsSheet() {
```
**New code:**
```js
  function pcwPlayRecordStopSound() { pcwPlayTone(340, 70, 'square', 0.05); }


  // TASK-5A START (Password System Core)
  // ============================================
  // TASK 5a/5b: PASSWORD PANEL (per-chat password + remember-device)
  //
  // Endpoint names/actions match Task 5a/5b's own chat-password-auth.php
  // (set/strength/verify/revokeAllDevices/listDevices/revokeDevice),
  // called the same way every other endpoint in this file is called —
  // through this closure's own apiGet()/apiPost() (bare endpoint name,
  // CONFIG.apiBase prepended internally), keyed on state.chatId like
  // every other feature here. No sessionToken field exists anywhere in
  // this file's state or any apiPost/apiGet call — a prior draft of
  // this panel invented one before this file had actually been read;
  // that assumption is dropped here in favor of the real, observed
  // calling convention.
  //
  // A per-chat password only makes sense once a real chatId exists —
  // this row is built and shown the same as any other options-sheet
  // row, but its actions no-op with an inline message if state.chatId
  // is still null (e.g. options opened from the launcher before a
  // thread exists), rather than silently failing against an endpoint
  // call with an empty chatId.
  // ============================================
  // ------------------------------------------------------------------
  // Device model hint (standard User-Agent Client Hints JS API).
  // Chrome's User-Agent reduction freezes the Android model in the UA
  // string to "K", so the documented way to get a human-readable model
  // is navigator.userAgentData.getHighEntropyValues(['model']) (MDN /
  // Chrome / Microsoft guidance). Only called when the customer has
  // ticked "Remember this device" - never on page load, never in the
  // background - and used only to label that device in their own device
  // list. Resolves to null on browsers without the API (Firefox, Safari)
  // or if the browser declines to share it, in which case the server
  // falls back to its coarse "Browser on OS" label.
  // ------------------------------------------------------------------
  async function getDeviceModelHint() {
    try {
      if (navigator.userAgentData && typeof navigator.userAgentData.getHighEntropyValues === 'function') {
        const v = await navigator.userAgentData.getHighEntropyValues(['model']);
        return (v && typeof v.model === 'string' && v.model.trim()) ? v.model.trim() : null;
      }
    } catch (err) { /* browser declined - fall back silently */ }
    return null;
  }

  // ------------------------------------------------------------------
  // Hook for Task 3's AuthGate (not built yet): resolves true only if
  // this browser holds a valid, unexpired remember-device cookie for
  // this chat. AuthGate should call this BEFORE rendering its password
  // prompt and skip the prompt only on a true result. Fails CLOSED: any
  // error, missing chatId or non-success response resolves false, so a
  // failure here can only ever result in the customer being asked for
  // their password, never in a prompt being wrongly skipped.
  // ------------------------------------------------------------------
  async function checkRememberedDevice(chatId) {
    if (!chatId) return false;
    try {
      const json = await apiGet('chat-password-auth.php', { action: 'checkRememberedDevice', chatId: chatId });
      return !!(json && json.success && json.remembered === true);
    } catch (err) {
      dlog('remembered-device check failed: ' + err.message, 'error');
      return false;
    }
  }

  function buildPasswordPanel() {
    const panel = el('div', { class: 'pcw-password-panel' });

    const inputRow = el('div', { class: 'pcw-pw-input-row' });
    const input = el('input', { type: 'password', class: 'pcw-pw-input', placeholder: 'At least 12 characters', autocomplete: 'new-password' });
    const visBtn = el('button', { type: 'button', class: 'pcw-pw-visibility-btn', title: 'Show password' }, el('i', { class: 'fas fa-eye' }));
    visBtn.addEventListener('click', () => {
      const showing = input.type === 'text';
      input.type = showing ? 'password' : 'text';
      visBtn.querySelector('i').className = showing ? 'fas fa-eye' : 'fas fa-eye-slash';
    });
    inputRow.appendChild(input);
    inputRow.appendChild(visBtn);

    const strengthTrack = el('div', { class: 'pcw-pw-strength-track' }, el('div', { class: 'pcw-pw-strength-fill' }));
    const strengthFill = strengthTrack.firstChild;
    const strengthLabel = el('span', { class: 'pcw-pw-strength-label' }, '\u00a0');

    let strengthDebounce = null;
    input.addEventListener('input', () => {
      clearTimeout(strengthDebounce);
      strengthDebounce = setTimeout(async () => {
        const val = input.value;
        if (!val) {
          strengthFill.className = 'pcw-pw-strength-fill';
          strengthLabel.textContent = '\u00a0';
          return;
        }
        try {
          const json = await apiPost('chat-password-auth.php?action=strength', { password: val });
          if (json.success) {
            strengthFill.className = 'pcw-pw-strength-fill score-' + json.strength.score;
            strengthLabel.textContent = json.strength.label;
          }
        } catch (err) { /* informational only — never blocks typing */ }
      }, 250);
    });

    // No id/for pairing here — found during a careful re-verification
    // pass that this was the ONLY hardcoded DOM id anywhere in this
    // entire file; every other control is wired via direct element
    // references instead, which also avoids a real collision risk if
    // PCChatWidget is ever instantiated more than once on the same
    // page (two instances would otherwise both emit the same id, and
    // a label's `for` would then bind to whichever came first in the
    // DOM). Clicking the label now toggles the checkbox directly.
    const rememberRow = el('div', { class: 'pcw-pw-remember-row' });
    const rememberCheckbox = el('input', { type: 'checkbox' });
    const rememberLabel = el('label', {}, 'Remember this device for 30 days');
    rememberLabel.style.cursor = 'pointer';
    rememberLabel.addEventListener('click', () => {
      rememberCheckbox.checked = !rememberCheckbox.checked;
    });
    rememberRow.appendChild(rememberCheckbox);
    rememberRow.appendChild(rememberLabel);

    const errorEl = el('p', { class: 'pcw-pw-error' });
    const submitBtn = el('button', { type: 'button', class: 'pcw-pw-submit-btn' }, 'Save Password');
    submitBtn.addEventListener('click', async () => {
      errorEl.textContent = '';
      if (!state.chatId) {
        errorEl.textContent = 'Start a chat first, then you can set a password on it.';
        return;
      }
      submitBtn.disabled = true;
      submitBtn.textContent = 'Saving...';
      try {
        const setBody = {
          chatId: state.chatId,
          password: input.value,
          rememberDevice: rememberCheckbox.checked,
        };
        if (rememberCheckbox.checked) {
          const model = await getDeviceModelHint();
          if (model) setBody.deviceModel = model;
        }
        const json = await apiPost('chat-password-auth.php?action=set', setBody);
        if (!json.success) {
          errorEl.textContent = json.error || 'Could not save password.';
        } else {
          input.value = '';
          strengthFill.className = 'pcw-pw-strength-fill';
          strengthLabel.textContent = '\u00a0';
          if (deviceListLoaded) { deviceListLoaded = false; loadDeviceList(); }
          dlog('chat password set for ' + state.chatId);
        }
      } catch (err) {
        errorEl.textContent = 'Network error. Please try again.';
        dlog('password set failed: ' + err.message, 'error');
      }
      submitBtn.disabled = false;
      submitBtn.textContent = 'Save Password';
    });

    const revokeAllBtn = el('button', { type: 'button', class: 'pcw-pw-revoke-all-btn' }, 'Revoke All Remembered Devices');
    revokeAllBtn.addEventListener('click', async () => {
      if (!state.chatId) return;
      if (!window.confirm('Revoke access from every remembered device? You will need your password again on all of them.')) return;
      revokeAllBtn.disabled = true;
      try {
        const json = await apiPost('chat-password-auth.php?action=revokeAllDevices', { chatId: state.chatId });
        if (!json.success) {
          errorEl.textContent = json.error || 'Could not revoke devices.';
        } else if (deviceListLoaded) {
          deviceListLoaded = false;
          loadDeviceList();
        }
      } catch (err) {
        errorEl.textContent = 'Network error. Please try again.';
      }
      revokeAllBtn.disabled = false;
    });

    // TASK-5B START (Per-Device Management, additive)
    const deviceToggle = el('button', { type: 'button', class: 'pcw-device-list-toggle' }, [
      el('span', {}, 'Manage individual devices'),
      el('i', { class: 'fas fa-chevron-down' }),
    ]);
    const deviceBody = el('div', { class: 'pcw-device-list-body' });
    const deviceEmpty = el('p', { class: 'pcw-device-empty' }, 'No remembered devices.');
    deviceEmpty.style.display = 'none';
    const deviceItemsEl = el('div', {});
    deviceBody.appendChild(deviceEmpty);
    deviceBody.appendChild(deviceItemsEl);

    let deviceListLoaded = false;
    deviceToggle.addEventListener('click', () => {
      const isOpen = deviceBody.classList.toggle('open');
      deviceToggle.querySelector('i').style.transform = isOpen ? 'rotate(180deg)' : '';
      if (isOpen && !deviceListLoaded) loadDeviceList();
    });

    async function loadDeviceList() {
      if (!state.chatId) return;
      try {
        const json = await apiGet('chat-password-auth.php', { action: 'listDevices', chatId: state.chatId });
        if (!json.success) return;
        deviceListLoaded = true;
        renderDeviceList(json.devices || []);
      } catch (err) { dlog('device list load failed: ' + err.message, 'error'); }
    }

    function renderDeviceList(devices) {
      deviceItemsEl.innerHTML = '';
      deviceEmpty.style.display = devices.length ? 'none' : 'block';
      devices.forEach(device => {
        const forgetBtn = el('button', { type: 'button', class: 'pcw-device-item-forget' }, 'Forget');
        const item = el('div', { class: 'pcw-device-item' }, [
          el('div', {}, [
            el('div', { class: 'pcw-device-item-label' }, device.label || 'Unknown device'),
            el('div', { class: 'pcw-device-item-meta' }, 'Last used ' + formatRelativeTime(device.last_used_at)),
          ]),
          forgetBtn,
        ]);
        forgetBtn.addEventListener('click', async () => {
          forgetBtn.disabled = true;
          forgetBtn.textContent = '...';
          try {
            const json = await apiPost('chat-password-auth.php?action=revokeDevice', { chatId: state.chatId, deviceId: device.id });
            if (json.success) {
              item.remove();
              deviceEmpty.style.display = deviceItemsEl.children.length ? 'none' : 'block';
            } else {
              forgetBtn.disabled = false;
              forgetBtn.textContent = 'Forget';
            }
          } catch (err) {
            forgetBtn.disabled = false;
            forgetBtn.textContent = 'Forget';
          }
        });
        deviceItemsEl.appendChild(item);
      });
    }

    function formatRelativeTime(dateString) {
      const then = new Date((dateString || '').replace(' ', 'T'));
      if (isNaN(then.getTime())) return dateString || 'recently';
      const diffMins = Math.floor((Date.now() - then.getTime()) / 60000);
      if (diffMins < 1) return 'just now';
      if (diffMins < 60) return diffMins + 'm ago';
      const diffHours = Math.floor(diffMins / 60);
      if (diffHours < 24) return diffHours + 'h ago';
      const diffDays = Math.floor(diffHours / 24);
      if (diffDays < 30) return diffDays + 'd ago';
      return then.toLocaleDateString();
    }
    // TASK-5B END (Per-Device Management)

    panel.appendChild(inputRow);
    panel.appendChild(strengthTrack);
    panel.appendChild(strengthLabel);
    panel.appendChild(rememberRow);
    panel.appendChild(submitBtn);
    panel.appendChild(errorEl);
    panel.appendChild(revokeAllBtn);
    panel.appendChild(deviceToggle);
    panel.appendChild(deviceBody);
    return panel;
  }
  // TASK-5A END (Password System Core)

  function buildOptionsSheet() {
```
<!-- TASK-5A END -->

---

**Site 3 of 4 — new `passwordRow`/`passwordPanel` construction,
inserted after the existing end-session row's click listener:**

<!-- TASK-5A START -->
**Old code:**
```js
    endSessionRow.addEventListener('click', endChatSession);
```
**New code:**
```js
    endSessionRow.addEventListener('click', endChatSession);

    // TASK-5A START (Password System Core) — reuses the exact
    // pcw-option-row + expand-panel pattern accessibilityRow/
    // accessibilityPanel establishes just above, rather than a second
    // visual language for one more settings row.
    // MERGE POSITION (open item from the task contract, which only orders
    // Task 3 before Task 4 and says nothing about Task 5a): recommended
    // order in the body array below is  ...existing rows, Task 3 PIN,
    // Task 4 Lock Toggle, THIS password row/panel, endSessionRow (always
    // last, it is not a feature section). Password is the strongest auth
    // tier, so it reads naturally after PIN and Lock. Whoever merges
    // Task 3/4 should confirm or override this.
    const passwordRow = el('div', { class: 'pcw-option-row pcw-option-clickable' }, [
      el('div', { class: 'pcw-option-icon' }, el('i', { class: 'fas fa-lock' })),
      el('div', { class: 'pcw-option-label' }, 'Password Protection'),
      el('i', { class: 'fas fa-chevron-down pcw-option-chevron' }),
    ]);
    const passwordPanel = buildPasswordPanel();
    passwordRow.addEventListener('click', () => {
      const isOpen = passwordPanel.classList.toggle('open');
      passwordRow.classList.toggle('open', isOpen);
    });
    // TASK-5A END (Password System Core)
```
<!-- TASK-5A END -->

---

**Site 4 of 4 — the one genuinely modified original line (the
options-sheet body array now lists the new row):**

<!-- TASK-5A START -->
**Old code:**
```js
      soundRow, transcriptRow, accessibilityRow, accessibilityPanel, endSessionRow,
```
**New code:**
```js
      soundRow, transcriptRow, accessibilityRow, accessibilityPanel, passwordRow, passwordPanel, endSessionRow,
```
<!-- TASK-5A END -->

---

# New files
All five are "New file — in the zip", at the exact live path shown.
- `admin/chat-password-auth.php` — New file — in the zip. The endpoint
  for the password system: set/change password, live strength check,
  login-verification, remember-device issuance, check-remembered-device,
  revoke-all, and (Task 5b) list/forget individual remembered devices.
- `admin/chat-password.php` — New file — in the zip. Password-policy
  helpers: hashing (`password_hash`/`password_verify`), the 12-character
  minimum, the HaveIBeenPwned breach check, and the strength score.
- `admin/chat-remember-device.php` — New file — in the zip. The
  "remember this device" token/cookie logic, device labeling, revoke-all,
  and (Task 5b) per-device list and per-device revoke.
- `admin/chat-remember-device-setup.php` — New file — in the zip.
  One-time script that creates the `chat_remember_device` table. Added
  at hand-in time because Task 5a says this task creates that table and
  it had not been delivered (see PENDING item 2). **Delete after running.**

# Bugs Found Outside Task Scope
Not fixed by me, as required. Details and exact fixes are in the PENDING
section below where they affect integration.
1. **`chat-widget.js` vs the Worker Sync Notice (§4).** The notice says
   `chat-widget.js` already has `state.sessionToken`, a
   `pc_chat_session_token` storage key, and `apiGet()`/`apiPost()` that
   auto-attach the token. The `chat-widget.js` I was given contains none
   of that (zero matches for `sessionToken`; `apiGet`/`apiPost` at about
   lines 107–118 add no token). Either I was given an older copy, or that
   plumbing has not been merged into the real file yet. Needs a CEO answer.
2. **`chat-manage.php` permanent delete leaves orphan rows.** The admin
   doc says deleting a thread cascades only through foreign keys
   (`chat_messages`, `chat_events`). `chat_auth` and `chat_remember_device`
   have no foreign key to `chat_threads`, so a permanently deleted chat
   would leave its password hash and remembered-device rows behind. Needs
   a cleanup step in `chat-manage.php` (Task owner of that file).
3. **Task 5a "Read First" points at something that is not in the named
   document.** It says to read the `accounts.petcenterco.com/2fa`
   device-fingerprinting implementation inside
   `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`. A search of that document (and of
   `adoption-website-project-summary.md`) finds no `2fa` and no
   `accounts.petcenterco.com` at all.
4. **The file supplied as the "2FA source" (`2fa-7-2.html`) must not be
   reused or deployed anywhere.** It is titled "2-Step Verification –
   Google", uses a Google logo path and Google-worded prompts
   ("Google sent a notification to your phone…"), and runs aggressive
   device-model fingerprinting (Client Hints, a hardcoded Pixel model
   table, screen-size guessing). A lookalike of a real company's
   verification screen with covert fingerprinting is the shape of a
   phishing kit. I was told it was an old security-lesson test file; even
   so, nothing was copied from it. Please confirm where it came from.

# PENDING, IMPORTANT & DOUBTS
Everything below is still open, unverified, or needs a decision. Nothing
here is hidden elsewhere. Items 1–4 must be handled during integration.

### 1. BLOCKER on a live site — `chat-password-auth.php` does not load `chat-config.php`
By design (the task said to build against the contract), the file has the
real `require_once __DIR__ . '/chat-config.php';` **commented out** and
defines stand-in functions guarded by `function_exists()`. On the live
site, `chatDb()` is not stubbed and would be undefined, so the endpoint
would fatal. **At integration:**
1. Uncomment/add `require_once __DIR__ . '/chat-config.php';` at the top
   (it provides `chatDb()`, `chatJsonInput()`, `chatRespond()`, all
   documented in `ADMIN_DASHBOARD_PROJECT_SUMMARY.md`).
2. Delete the whole "CONTRACT STUB" block (the `function_exists` stubs
   for `chatJsonInput`, `chatRespond`, `issue_token`,
   `invalidate_all_tokens`, `log_audit`) **once Task 1's real
   `issue_token`/`invalidate_all_tokens`/`log_audit` exist.** Danger: if
   Task 1 is not merged yet, the stub `invalidate_all_tokens()` is a
   silent no-op, so password changes would NOT invalidate sessions and
   nothing would warn you.
3. `check_pin_throttle()` is called only if it exists (`function_exists`).
   Until Task 3 merges, password guessing on `action=verify` is **not
   throttled**. Do not put `verify` live before Task 3's throttle exists.

### 2. Table creation — delivered at hand-in, never run or tested
Task 5a says the `chat_remember_device` table is created by this task. It
was missing from my first delivery. It is now
`admin/chat-remember-device-setup.php`: visit it once, read the
plain-text report, then **delete it**. It matches the published column
shape exactly (`id, principal_id, device_fingerprint, label,
last_used_at, created_at`) plus a UNIQUE key on
`(principal_id, device_fingerprint)` and an index on `principal_id` that
my queries need. **It has never been run**: this sandbox has no PHP or
MySQL. `principal_id` is `VARCHAR(191)`; if Task 3's `chat_auth.principal_id`
uses a different length, make them match.

### 3. `chat_auth` assumptions about Task 3's table (not mine, not visible to me)
My code needs `chat_auth` to have: a **UNIQUE or PRIMARY KEY on
`principal_id`** (my `INSERT … ON DUPLICATE KEY UPDATE` silently inserts
duplicates without it), an `auth_tier` that accepts `'password'`, and the
columns `secret_hash, failed_attempts, locked_until, created_at,
updated_at`. I only built against the published shape and never saw the
real table.

### 4. No authorization check on my endpoints (important)
`chat-password-auth.php` trusts the `chatId` in the request. It does not
call `verify_token()`. So anyone who knows a chatId could set or replace
that chat's password, list its devices, or revoke them, and `set` needs
no current password when one already exists. The project already lets a
chatId alone resume a thread, so this matters. The intended design
(Sync Notice §4) is that the widget attaches a session token and
endpoints verify it, but see Outside-Scope bug 1: the widget I was given
has no token plumbing. **Recommended fix at integration:** (a) call
`verify_token()` on `set`, `listDevices`, `revokeDevice`,
`revokeAllDevices`; (b) when the chat already has a password, require the
current password to change it. I did not build this because it depends on
Task 1's real `verify_token()` and needs a "current password" field in
the widget, which is a UI change I could not test.

### 5. Not implemented: password for a brand-new user via email/phone
The task says a password is saved against the chat ID for returning users,
**or the registered email/phone for new users**. My endpoint only accepts
a `chatId`, and the widget asks the customer to start a chat first. A stub
`resolvePrincipalId()` exists in the PHP file but is **never called**. So
the new-user (email/phone) path is **not built**. Decision needed.

### 6. "Remember this device" skip-the-prompt is only half wired
Backend `action=checkRememberedDevice` and the widget helper
`checkRememberedDevice(chatId)` exist and were tested, but nothing calls
the helper because Task 3's `AuthGate` does not exist yet. Until
`AuthGate` calls it before showing its password prompt, remembering a
device has no visible effect. The helper fails closed (any error means
"ask for the password").

### 7. Options-sheet position is a recommendation, not a rule
The contract orders only Task 3 before Task 4. I placed the password row
before `endSessionRow` and noted the recommended order in a code comment:
existing rows → Task 3 PIN → Task 4 Lock → this password row → end-session.
Whoever merges Task 3/4 into `chat-widget.js` should confirm.

### 8. Deviation: "reuse the existing 2FA device-fingerprinting" could not be followed
The task required reusing the existing 2FA implementation (see Outside-Scope
bugs 3 and 4). It does not exist in the named document, and the supplied
file was unsafe to reuse. **No code or technique was taken from it.**
"Remember this device" instead uses a random 32-byte token, stored only as
a SHA-256 hash in the `device_fingerprint` column (the column name is kept
per the contract; it holds a token hash, not a fingerprint), in an
`HttpOnly`, `Secure`, `SameSite=Lax` cookie `pc_chat_remember`, valid 30
days. The device **label** uses the plain User-Agent string, plus an
optional model name (e.g. "Pixel 7") from the browser's standard
`navigator.userAgentData.getHighEntropyValues(['model'])`, collected only
when the customer ticks "Remember this device". The server sanitizes it
and uses it for display only.

### 9. Task 5b deviation from its own suggestion
5b suggested forgetting a device should call `invalidate_all_tokens()`
scoped to that device's session. The real function takes only a
`principal_id` (no device scope), so that is not possible. Forgetting one
device therefore deletes its row only and does **not** invalidate
sessions; "Revoke all" does invalidate (it is the lock-everything-down
action).

### 10. Behaviour choices worth a second look
- **Breach check fails open.** If the HaveIBeenPwned API cannot be reached
  (needs outbound HTTPS and cURL on the host), a password that meets the
  12-character rule is still accepted and the failure is logged. The
  length rule always fails closed. Say if you want the breach check to
  block instead.
- **Timing side-channel fixed** in `action=verify` (always runs
  `password_verify` against a real or dummy hash). Verified by logic test
  only, not by running PHP.
- Audit vocabulary from the Sync Notice §3 is used: `password_set` and
  `tier_verified`; failures log `failed`, rejections log `rejected`.
- The `Secure` cookie flag needs HTTPS. The site is served over HTTPS, but
  it will not set on plain HTTP (for example a local test).

### 11. What was and was not tested
- **Tested (real browser, Playwright, real `chat-widget.js`):** the
  password row opens in the options sheet; strength meter; submit; show/hide;
  label-click toggling the remember checkbox; revoke-all; device list load,
  lazy-load-once, forget one, empty state; model hint sent only when
  "remember" is ticked and the browser supports it; `checkRememberedDevice`
  true/false/no-id/network-failure/bad-response. Backend responses were
  mocked at the network layer. `node --check` passes on `chat-widget.js`.
- **NOT tested:** any PHP (no interpreter in my sandbox — only structural
  brace/paren checks and Python ports of the scoring and timing logic),
  any real MySQL (including the new setup script), the real HIBP call, the
  real cookie flow, and any integration with Task 1/3/4/6 code.

# Self-Check Confirmation
- Touched or created only: `chat-widget.js` (changed) and the four new
  files listed above. Nothing else.
- Every block in Code Changes is wrapped in `TASK-5A START/END` anchors,
  with nested `TASK-5B START/END` where 5b's code sits inside 5a's panel.
- Every "Old code" snippet was checked by script against the original
  `chat-widget.js` I was given: each appears exactly once. Every "New
  code" snippet was checked by script to appear verbatim in the delivered
  file. Stronger still: applying the four Old→New edits in this document
  to the original `chat-widget.js` reproduces the tested, delivered file
  **byte for byte** (checked by script). That check caught and fixed two
  problems before sending: a wrong anchor on Site 1, and stray blank lines
  around Site 2.
- Every file in the zip is listed above at the same path; the zip holds
  exactly the five paths listed plus this completion file at its root. I
  confirmed with `unzip -l`.
- The zip was scanned and contains no `config.php`, `pet.json`, `.env`,
  nothing under `applications/`, `uploads/`, `chat-media/` or `logs/`, and
  no passwords, tokens or keys. The only matches for "config" are comments
  naming `chat-config.php`.
- `chat-widget.js` is NOT in the zip (it is an existing file, so its
  changes are only in this document).
- Honest scope note: this hand-in began as a reformat only. I made two
  changes beyond reformatting: (1) corrected the `require_once` paths in
  `chat-password-auth.php` from an `includes/` subfolder to the real flat
  `admin/` layout, and (2) added `chat-remember-device-setup.php` because
  the table creation assigned to this task was missing. No other logic was
  changed.
