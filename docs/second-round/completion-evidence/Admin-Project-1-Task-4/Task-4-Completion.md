# Task 4 — Lock Toggle — Completion File

## Task Number
Task 4 — Lock Toggle (from "Live Chat Security & Session Overhaul," Admin-Project-1-Worker-Task-Assignments.md)

## What Was Required
While a chat is open, the customer can toggle a setting that locks the conversation once the widget is closed. Reopening requires the PIN through Task 3's AuthGate. Two configurable behaviors live in the same options panel as PIN setup: a delay setting, and a separate immediate (no-delay) option. The locked/unlocked transition must be a fade (not a slide, not instant). On lock trigger, `invalidate_all_tokens()` must be called. The idle countdown may run client-side, but the server must independently re-check elapsed idle time and never trust a client-reported idle state. Recommended enhancement adopted: a "Locking in N seconds" indicator.

## Files Changed

| Source file name | Live path | Changed or New | Shared with another task? |
|---|---|---|---|
| `chat-widget.js` | `admin/chat-widget.js` | Changed | Yes. Task 1 (session-token plumbing in `apiGet`/`apiPost`, `openLiveThread`, `pollOnce`), Task 3 (PIN section, `pcwRenderAuthGate`, `pcwUpdateSessionToken` — Task 3's own `TASK-3` anchors sit inside my overlay code at `pcwShowLockedOverlay()`) |
| `chat-lock.php` | `admin/chat-lock.php` | New | Yes. Calls Task 1/3 contract functions in `includes/chat-auth.php`; Task 3 integrated the real PIN-verification path into my `unlock` action (anchored `TASK-3` inside my `TASK-4` file) |

No other file was touched.

## Code Changes

All blocks below are from `chat-widget.js` (existing file — changed, not in the zip). `chat-lock.php` is a brand-new file; see **New files** below — its code is not pasted here per the hand-in rule.

---

### `chat-widget.js` → live path `admin/chat-widget.js`

**Block 1 — Lock Toggle CSS (added at the end of the existing style block, after the responsive media-query rules)**

<!-- TASK-4 START -->
**Old code:**
```css
      border-radius: 16px;
    }
    .pcw-launcher-btn { bottom: 16px; right: 16px; }
  }
```
**New code:**
```css
      border-radius: 16px;
    }
    .pcw-launcher-btn { bottom: 16px; right: 16px; }
  }

  /* TASK-4 START — Lock Toggle: only genuinely new rules live here.
     The toggle itself reuses .pcw-toggle-switch / .pcw-option-row /
     .pcw-option-icon as-is (see buildLockRow()) rather than introducing a
     second toggle component with different colors — matching what's
     already shipped in this exact sheet beats a spec-sheet's abstract
     toggle pattern when the two conflict on a component that already
     exists here. See Task-4-Completion.md, Contract Deviations. */
  .pcw-lock-desc {
    font-family: 'Sora', system-ui, sans-serif; font-size: 11.5px; color: rgba(255,255,255,0.45);
    margin-top: 2px;
  }
  .pcw-lock-delay-panel {
    max-height: 0; opacity: 0; overflow: hidden; display: flex; gap: 8px; flex-wrap: wrap;
    transition: max-height 0.3s ease, opacity 0.25s ease, margin 0.3s ease;
  }
  .pcw-lock-delay-panel.open { max-height: 70px; opacity: 1; margin-top: 10px; padding-bottom: 4px; }
  .pcw-lock-delay-chip {
    font-family: 'Sora', system-ui, sans-serif; font-size: 12px; font-weight: 600;
    padding: 6px 12px; border-radius: 999px; border: 1.5px solid rgba(255,255,255,0.15);
    background: rgba(255,255,255,0.04); color: rgba(255,255,255,0.75); cursor: pointer;
    transition: border-color 0.15s ease, background 0.15s ease;
  }
  .pcw-lock-delay-chip:hover { border-color: #ED7624; background: rgba(237,118,36,0.1); }
  .pcw-lock-delay-chip.selected { border-color: #ED7624; background: #ED7624; color: #fff; }
  .pcw-lock-countdown {
    font-family: 'Sora', system-ui, sans-serif; font-size: 11.5px; color: #d7bd88; margin-top: 8px;
  }

  /* Locked overlay — fade ONLY (Design System Section 8: fadeIn, 0.3s,
     cubic-bezier(0.4,0,0.2,1)) — never a slide, never instant, per the
     Task 4 spec. */
  .pcw-locked-overlay {
    position: absolute; inset: 0; background: rgba(18,18,18,0.96); z-index: 10003;
    display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 14px;
    opacity: 0; visibility: hidden; pointer-events: none;
    transition: opacity 0.3s cubic-bezier(0.4,0,0.2,1), visibility 0.3s cubic-bezier(0.4,0,0.2,1);
    border-radius: inherit;
  }
  .pcw-locked-overlay.show { opacity: 1; visibility: visible; pointer-events: auto; }
  .pcw-locked-overlay h4 { font-family: 'Sora', system-ui, sans-serif; font-size: 15px; color: #fff; margin: 0; font-weight: 700; }
  .pcw-locked-overlay p { font-family: 'Sora', system-ui, sans-serif; font-size: 12.5px; color: rgba(255,255,255,0.55); margin: 0; text-align: center; padding: 0 24px; }
  .pcw-locked-overlay .pcw-unlock-btn {
    font-family: 'Sora', system-ui, sans-serif; font-size: 13.5px; font-weight: 600;
    background: #ED7624; color: #fff; border: none; border-radius: 999px; padding: 10px 24px; cursor: pointer;
  }
  /* TASK-4 FIX: AuthGate fields were dark-on-dark inside the overlay; give
     the gate a bounded width, a legible label, and the overlay a close button. */
  .pcw-locked-overlay .pcw-locked-gate { width: 100%; max-width: 260px; padding: 0 16px; box-sizing: border-box; }
  .pcw-locked-overlay .pcw-field label { color: #e5e5e5; }
  .pcw-locked-overlay .pcw-authgate-msg { color: rgba(255,255,255,0.7); }
  .pcw-locked-overlay .pcw-authgate-msg.error { color: #ef4444; }
  .pcw-locked-overlay .pcw-locked-close {
    position: absolute; top: 12px; right: 12px; width: 32px; height: 32px; border-radius: 50%;
    border: none; background: rgba(255,255,255,0.1); color: #fff; cursor: pointer;
  }
  .pcw-locked-overlay .pcw-unlock-err { font-family: 'Sora', system-ui, sans-serif; font-size: 11.5px; color: #ef4444; }
  /* TASK-4 END — Lock Toggle CSS */
```
<!-- TASK-4 END -->


---

**Block 2a — row build, inserted after the existing `endSessionRow` click handler, before the `body` array**

<!-- TASK-4 START -->
**Old code:**
```js
    endSessionRow.addEventListener('click', endChatSession);
```
**New code:**
```js
    endSessionRow.addEventListener('click', endChatSession);

    // TASK-4 START (Lock Toggle — options-panel section, appended after
    // Task 3's PIN-setup section per the options-panel section contract.
    // NOTE (updated by Task 3 during merge): Task 3's section is now
    // merged in directly above this row, exactly where Task 4's own
    // comment anticipated it would land — Task 4's row build itself is
    // unchanged, only this comment was corrected for accuracy.)
    const lockRow = buildLockRow();
    const lockDelayPanel = buildLockDelayPanel();
    // TASK-4 END (Lock Toggle — row build)
```
<!-- TASK-4 END -->

**Block 2b — the same `body` array literal gains `lockRow, lockDelayPanel` after Task 3's `pinRow, pinPanel` entry**

<!-- TASK-4 START -->
**Old code:**
```js
      pinRow, pinPanel, // TASK-3 — PIN Protection section
```
**New code:**
```js
      pinRow, pinPanel, // TASK-3 — PIN Protection section
      lockRow, lockDelayPanel, // TASK-4 — Lock Toggle section
```
<!-- TASK-4 END -->

*(This line occurs once in the file, inside the `body = el('div', { class: 'pcw-sheet-body' }, [...])` array that already carries `soundRow, transcriptRow, accessibilityRow, accessibilityPanel` before it and `endSessionRow` after it.)*

---

**Block 3 — the full Lock Toggle feature logic block, inserted after `closeOptionsSheet()`**

<!-- TASK-4 START -->
**Old code:**
```js
    optionsSheetEl.classList.remove('open');
    optionsBackdropEl.classList.remove('show');
    pcwUnlockBodyScroll();
  }
```
**New code:**
```js
    optionsSheetEl.classList.remove('open');
    optionsBackdropEl.classList.remove('show');
    pcwUnlockBodyScroll();
  }

  // ============================================
  // TASK-4 START — LOCK TOGGLE (Live Chat Security & Session Overhaul, Task 4)
  //
  // While a chat is open, the customer can opt to have this conversation
  // require their PIN again after the widget is closed. Two independent
  // settings live in this section: a delay (how long after close before
  // it locks) and a separate "lock immediately" switch with no delay.
  //
  // Identity: this file has no persistent "principal"/account concept —
  // only a per-thread state.chatId. Task 3's real PIN/AuthGate contract
  // was described (in Admin-Project-1-Worker-Task-Assignments.md) as
  // keying on a principalId; absent that file, state.chatId is used here
  // as the principal for this feature, matching this row's own "Lock
  // THIS CONVERSATION" wording (it's a per-thread lock, not an account-
  // wide one). Swap for Task 3's real principal concept at integration
  // if it turns out to differ.
  //
  // Contract calls: all requests go through this file's own apiGet()/
  // apiPost() (CONFIG.apiBase + endpoint) — the same helpers every other
  // feature in this widget already uses. This file has no session-token
  // field to attach (see Task-4-Completion.md, Contract Deviations, for
  // why that's a deliberate departure from the Worker-Sync-Notice).
  //
  // Fade-only transition (Design System Section 8, fadeIn 0.3s
  // cubic-bezier(0.4,0,0.2,1)) is used for the locked overlay only — see
  // .pcw-locked-overlay in STYLE. Everything else in this section reuses
  // .pcw-toggle-switch / .pcw-option-row exactly as shipped elsewhere in
  // this sheet, intentionally NOT introducing the Design System's Navy
  // toggle-switch pattern (see completion file for why).
  // ============================================

  const LOCK_PREF_KEY = 'pc_chat_lock_prefs'; // local mirror of server prefs, for instant UI on reopen before the server round-trip resolves
  const LOCK_DELAY_OPTIONS_SECONDS = [60, 300, 900]; // 1 / 5 / 15 min

  function loadLocalLockPrefs() {
    try { return JSON.parse(localStorage.getItem(LOCK_PREF_KEY)) || { enabled: false, mode: 'delay', delaySeconds: 300 }; }
    catch (e) { return { enabled: false, mode: 'delay', delaySeconds: 300 }; }
  }
  function saveLocalLockPrefs(prefs) {
    localStorage.setItem(LOCK_PREF_KEY, JSON.stringify(prefs));
  }

  let lockToggleEl, lockImmediateToggleEl, lockCountdownEl, lockDelayPanelRef, lockDescEl;
  const LOCK_DESC_DEFAULT = 'Requires your PIN to reopen this chat after it’s closed.';
  let lockNoticeTimer = null;

  // TASK-4 FIX: show a short message in the lock row's description line
  // (amber), then restore the normal text after a few seconds.
  function pcwLockShowNotice(text) {
    if (!lockDescEl) return;
    lockDescEl.textContent = text;
    lockDescEl.style.color = '#d97706';
    if (lockNoticeTimer) clearTimeout(lockNoticeTimer);
    lockNoticeTimer = setTimeout(() => {
      lockDescEl.textContent = LOCK_DESC_DEFAULT;
      lockDescEl.style.color = '';
    }, 6000);
  }

  function buildLockRow() {
    const prefs = loadLocalLockPrefs();

    lockToggleEl = el('button', { type: 'button', class: 'pcw-toggle-switch' + (prefs.enabled ? ' on' : '') });
    // TASK-4 FIX: keep a reference to the description line so a server
    // refusal (e.g. "set a PIN first") can be shown right where the customer
    // is looking, then restored to the normal text.
    lockDescEl = el('div', { class: 'pcw-lock-desc' }, LOCK_DESC_DEFAULT);
    lockToggleEl.addEventListener('click', async () => {
      // TASK-4 FIX: the server keys the preference on the thread id, so
      // before a chat exists there is nothing to attach it to. The old code
      // flipped the switch anyway and only mirrored it into localStorage,
      // leaving a lock the server never knew about. Refuse up front instead.
      if (!state.chatId) {
        pcwLockShowNotice('Start a chat first, then you can lock it.');
        return;
      }
      lockToggleEl.classList.toggle('on');
      const nowOn = lockToggleEl.classList.contains('on');
      const before = loadLocalLockPrefs();
      const p = Object.assign({}, before, { enabled: nowOn });
      saveLocalLockPrefs(p);
      pcwSyncLockDelayPanelVisibility();
      dlog('lock toggle: enabled = ' + nowOn);
      // Wait for the server's verdict; it refuses to enable a lock when no
      // PIN exists (that would lock the customer out of their own chat).
      const json = await persistLockPrefs(p);
      if (json && json.success === false) {
        saveLocalLockPrefs(before); // roll the local mirror back
        lockToggleEl.classList.toggle('on', before.enabled); // roll the switch back
        pcwSyncLockDelayPanelVisibility();
        pcwLockShowNotice(json.error || 'Could not change the lock setting. Please try again.');
      }
    });

    return el('div', { class: 'pcw-option-row' }, [
      // Distinct icon: Task 3's PIN row already uses fa-lock right above this row
      el('div', { class: 'pcw-option-icon' }, el('i', { class: 'fas fa-hourglass-half' })),
      el('div', {}, [
        el('div', { class: 'pcw-option-label' }, 'Lock this conversation'),
        lockDescEl,
      ]),
      lockToggleEl,
    ]);
  }

  function buildLockDelayPanel() {
    const prefs = loadLocalLockPrefs();
    lockDelayPanelRef = el('div', { class: 'pcw-lock-delay-panel' + (prefs.enabled ? ' open' : '') });

    // "Lock immediately" is its own row inside this panel, mutually
    // exclusive with the delay chips beneath it (task spec: "a separate
    // immediate/aggressive option", not a 0-second entry in the delay list)
    lockImmediateToggleEl = el('button', { type: 'button', class: 'pcw-toggle-switch' + (prefs.mode === 'immediate' ? ' on' : '') });
    const immediateRow = el('div', { class: 'pcw-option-row', style: 'border-bottom:none;padding:6px 4px;' }, [
      el('div', { class: 'pcw-option-icon', style: 'width:28px;height:28px;font-size:12px;' }, el('i', { class: 'fas fa-bolt' })),
      el('div', {}, [
        el('div', { class: 'pcw-option-label', style: 'font-size:12.5px;' }, 'Lock immediately'),
        el('div', { class: 'pcw-lock-desc' }, 'No delay \u2014 locks the instant the chat is closed.'),
      ]),
      lockImmediateToggleEl,
    ]);

    const chipsRow = el('div', { class: 'pcw-lock-delay-chips', style: 'display:flex;gap:8px;flex-wrap:wrap;padding:2px 4px 4px;' });
    LOCK_DELAY_OPTIONS_SECONDS.forEach((secs) => {
      const chip = el('button', {
        type: 'button',
        class: 'pcw-lock-delay-chip' + (prefs.delaySeconds === secs && prefs.mode !== 'immediate' ? ' selected' : ''),
      }, secs < 60 ? secs + 's' : Math.round(secs / 60) + ' min');
      chip.addEventListener('click', () => {
        chipsRow.querySelectorAll('.pcw-lock-delay-chip').forEach((c) => c.classList.remove('selected'));
        chip.classList.add('selected');
        const p = loadLocalLockPrefs();
        p.delaySeconds = secs;
        p.mode = 'delay';
        saveLocalLockPrefs(p);
        persistLockPrefs(p);
        dlog('lock toggle: delaySeconds = ' + secs);
      });
      chipsRow.appendChild(chip);
    });

    lockImmediateToggleEl.addEventListener('click', () => {
      lockImmediateToggleEl.classList.toggle('on');
      const nowImmediate = lockImmediateToggleEl.classList.contains('on');
      chipsRow.style.display = nowImmediate ? 'none' : 'flex';
      const p = loadLocalLockPrefs();
      p.mode = nowImmediate ? 'immediate' : 'delay';
      saveLocalLockPrefs(p);
      persistLockPrefs(p);
      dlog('lock toggle: mode = ' + p.mode);
    });
    if (prefs.mode === 'immediate') chipsRow.style.display = 'none';

    lockCountdownEl = el('div', { class: 'pcw-lock-countdown' });
    lockCountdownEl.style.display = 'none';

    lockDelayPanelRef.appendChild(immediateRow);
    lockDelayPanelRef.appendChild(chipsRow);
    lockDelayPanelRef.appendChild(lockCountdownEl);
    return lockDelayPanelRef;
  }

  // Toggling the master lock switch shows/hides the delay panel with the
  // same collapsible treatment the Accessibility row already uses in this
  // sheet (max-height/opacity), for visual consistency — the fade-only
  // requirement from the task spec applies specifically to the
  // locked/unlocked overlay transition below, not to this menu expand.
  function pcwSyncLockDelayPanelVisibility() {
    if (!lockDelayPanelRef || !lockToggleEl) return;
    lockDelayPanelRef.classList.toggle('open', lockToggleEl.classList.contains('on'));
  }

  async function persistLockPrefs(prefs) {
    // No thread exists yet (customer hasn't started a chat) — nothing to
    // key the server-side row on. Local prefs already saved by the caller;
    // this just re-syncs to the server once a real chatId exists.
    if (!state.chatId) return null;
    try {
      // TASK-4 FIX: now returns the server's JSON so callers can roll back
      // when the server refuses (success:false, e.g. reason 'pin_required').
      return await apiPost('chat-lock.php', { action: 'set_preference', principalId: state.chatId, prefs });
    } catch (err) {
      dlog('lock prefs save failed: ' + err.message, 'error');
      return null; // network failure: keep the local state, nothing to roll back against
    }
  }

  async function loadServerLockPrefs() {
    if (!state.chatId) return null;
    try {
      // TASK-4 FIX: was apiGet(), but chat-lock.php reads only a POST JSON
      // body, so this never worked. apiPost also attaches the session token.
      const json = await apiPost('chat-lock.php', { action: 'get_preference', principalId: state.chatId });
      return (json && json.prefs) || null;
    } catch (err) {
      dlog('lock prefs load failed: ' + err.message, 'error');
      return null;
    }
  }

  // TASK-4 FIX (found during review — see Task-4-Completion.md): the
  // function above was written but never actually called anywhere. Every
  // enforcement check (pcwLockOnPanelClosed/pcwLockCheckOnPanelOpen/
  // pcwLockBackgroundCheck) gates on loadLocalLockPrefs() — a per-BROWSER
  // localStorage mirror — never on the server's real per-THREAD
  // (principal_id = chatId) preference. Resuming the same chat ID from a
  // different device/browser (or the same browser after localStorage was
  // cleared) is a first-class path this widget already supports
  // (resumeStoredChat()); that device's local mirror would silently
  // default to {enabled:false} even if the lock was genuinely turned on
  // from elsewhere, so the lock would just never engage there. Fix: pull
  // the real server prefs once per thread-acquisition — openLiveThread()
  // is the single choke point both a fresh prechat submit and every
  // resume path already go through — and reconcile both the local mirror
  // and the options-panel toggle UI (built once at widget init, before
  // any chatId was known, so it can't have reflected server truth yet).
  async function pcwLockSyncFromServer() {
    const serverPrefs = await loadServerLockPrefs();
    if (!serverPrefs) return; // no row yet (never configured) or request failed — local default of {enabled:false} is already the correct state for "never configured"
    saveLocalLockPrefs(serverPrefs);
    if (!lockToggleEl || !lockImmediateToggleEl || !lockDelayPanelRef) return; // options sheet not built yet in this edge case — nothing to reconcile visually
    lockToggleEl.classList.toggle('on', serverPrefs.enabled);
    lockImmediateToggleEl.classList.toggle('on', serverPrefs.mode === 'immediate');
    pcwSyncLockDelayPanelVisibility();
    const chipsRow = lockDelayPanelRef.querySelector('.pcw-lock-delay-chips');
    if (chipsRow) {
      chipsRow.style.display = serverPrefs.mode === 'immediate' ? 'none' : 'flex';
      chipsRow.querySelectorAll('.pcw-lock-delay-chip').forEach((chip, i) => {
        chip.classList.toggle('selected', LOCK_DELAY_OPTIONS_SECONDS[i] === serverPrefs.delaySeconds && serverPrefs.mode !== 'immediate');
      });
    }
  }

  // TASK-4 FIX (found during review — see Task-4-Completion.md "Bugs Found
  // / Self-Check" for the real gap this closes):
  // Without this, the server only ever evaluated a DELAYED lock's elapsed
  // idle time lazily, inside pcwLockCheckOnPanelOpen() — i.e. only at the
  // moment someone next opens the panel again. invalidate_all_tokens() is
  // the thing that actually kills an already-open second tab, and it was
  // only being called from inside that same lazy check — so a second tab,
  // already open elsewhere, would stay fully valid for however long it
  // took anyone to reopen the panel on ANY device, not "after the
  // configured delay" as the task spec requires ("an already-open second
  // tab stays valid past the lock, which defeats the point of locking").
  // Fix: piggyback a lock check onto the background poll loop that
  // ALREADY runs every CONFIG.pollIntervalBlurred while the panel is
  // closed and the thread is live (scheduleNextPoll(), below) — reuses
  // existing polling infrastructure instead of inventing a second timer,
  // per the standing "never recreate a feature from scratch" rule. This
  // makes the delay genuinely self-enforcing within one blurred-poll
  // interval of actually elapsing, matching the immediate-mode case
  // (which already triggers synchronously in the 'panel_closed' action
  // below and never had this gap).
  async function pcwLockBackgroundCheck() {
    if (state.panelOpen) return; // open-panel path already runs its own fresh check on open
    const prefs = loadLocalLockPrefs();
    if (!prefs.enabled || prefs.mode === 'immediate' || !state.chatId) return; // immediate already locks synchronously in pcwLockOnPanelClosed()
    try {
      await apiPost('chat-lock.php', { action: 'check_lock', principalId: state.chatId });
      // Response intentionally unused here — nothing to render while the
      // panel is closed. The call's only purpose is server-side: let
      // pcw_lock_check_idle_and_maybe_trigger() actually run and, if the
      // delay has elapsed, call invalidate_all_tokens() on schedule
      // instead of waiting for a future panel-open to notice.
    } catch (err) {
      dlog('lock background check failed: ' + err.message, 'error');
    }
  }

  // Called from closePanel() below — tells the server the panel closed so
  // ITS OWN idle clock (not this browser tab) can start. Also drives the
  // purely-cosmetic "locking in Ns" indicator — now backed by the
  // background check above for the actual lock, not just a comment's
  // claim that "the server decides independently" (it does now, but
  // didn't before this fix).
  function pcwLockOnPanelClosed() {
    const prefs = loadLocalLockPrefs();
    if (!prefs.enabled || !state.chatId) return;
    apiPost('chat-lock.php', { action: 'panel_closed', principalId: state.chatId }).catch(() => {});
    if (prefs.mode === 'immediate' || !lockCountdownEl) return;
    let remaining = prefs.delaySeconds;
    lockCountdownEl.style.display = 'block';
    lockCountdownEl.textContent = 'Locking in ' + remaining + 's';
    if (window._pcwLockCountdownTimer) clearInterval(window._pcwLockCountdownTimer);
    window._pcwLockCountdownTimer = setInterval(() => {
      remaining -= 1;
      if (remaining <= 0) {
        clearInterval(window._pcwLockCountdownTimer);
        lockCountdownEl.style.display = 'none';
        return;
      }
      lockCountdownEl.textContent = 'Locking in ' + remaining + 's';
    }, 1000);
  }

  // Called from openPanel() below — the server-authoritative check. Never
  // trusts anything client-side about elapsed idle time; this just asks
  // and shows the overlay if the server says locked.
  async function pcwLockCheckOnPanelOpen(chatIdOverride) {
    // resumeStoredChat() resolves state.chatId asynchronously, so the
    // caller may not have it yet on this same tick — fall back to
    // localStorage's copy (the same one resumeStoredChat itself reads).
    const principalId = chatIdOverride || state.chatId || localStorage.getItem(CONFIG.storageKeyChatId);
    const prefs = loadLocalLockPrefs();
    if (!prefs.enabled || !principalId) return;
    // TASK-4 FIX (found during review): these two requests were previously
    // fired without sequencing — 'check_lock' (reads panel_closed_at to
    // decide whether the delay has elapsed) and 'panel_opened' (clears
    // panel_closed_at for the new open session) raced at the server with
    // no guaranteed order. If 'panel_opened' happened to land FIRST,
    // 'check_lock' would read an already-cleared panel_closed_at and miss
    // a lock that should have triggered moments earlier — the evidence
    // it needs would already be gone. Fix: check_lock must be awaited to
    // completion before panel_opened clears the timestamp it depends on.
    let locked = false;
    try {
      const json = await apiPost('chat-lock.php', { action: 'check_lock', principalId });
      locked = !!(json && json.locked);
    } catch (err) {
      dlog('lock check failed: ' + err.message, 'error');
    }
    apiPost('chat-lock.php', { action: 'panel_opened', principalId }).catch(() => {}); // reset the idle clock for this new open session — fine to fire-and-forget now that check_lock has already read it
    if (locked) pcwShowLockedOverlay();
  }

  // TASK-4 FIX: second-tab coverage. A tab that is already OPEN when the
  // chat locks elsewhere has its token invalidated, so its next poll is
  // rejected. Ask the server (pre-auth check_lock) whether that is a lock
  // and, if so, cover the panel with the same fading overlay. Skipped when
  // the overlay is already showing so it is not re-triggered every poll.
  let lockPollCheckBusy = false;
  async function pcwLockOnPollRejected() {
    if (!state.panelOpen || !state.chatId || lockPollCheckBusy) return;
    if (lockedOverlayEl && lockedOverlayEl.classList.contains('show')) return;
    if (!loadLocalLockPrefs().enabled) return;
    lockPollCheckBusy = true;
    try {
      const json = await apiPost('chat-lock.php', { action: 'check_lock', principalId: state.chatId });
      if (json && json.locked) pcwShowLockedOverlay();
    } catch (err) {
      dlog('lock poll-reject check failed: ' + err.message, 'error');
    }
    lockPollCheckBusy = false;
  }

  let lockedOverlayEl = null;
  let lockedGateContainer = null;
  function pcwShowLockedOverlay() {
    if (!lockedOverlayEl) {
      lockedOverlayEl = el('div', { class: 'pcw-locked-overlay' });
      // TASK-4 FIX: the overlay covers the whole panel including its header
      // close button, which trapped the customer. Give it its own close.
      const closeBtn = el('button', { type: 'button', class: 'pcw-locked-close', 'aria-label': 'Close chat' }, el('i', { class: 'fas fa-times' }));
      closeBtn.addEventListener('click', () => closePanel());
      lockedOverlayEl.appendChild(closeBtn);
      const lockIcon = el('div', {}, el('i', { class: 'fas fa-lock', style: 'font-size:26px;color:#d7bd88;' }));
      const title = el('h4', {}, 'This chat is locked');
      const desc = el('p', {}, 'Enter your PIN to keep reading and reply.');
      /* TASK-3 START — integration: this replaces Task 4's own
         window.prompt() TODO with the real AuthGate widget, per Task 4's
         completion note that the call site was shaped for exactly this
         drop-in. The verify() call below keeps Task 4's exact existing
         request shape (action:'unlock', principalId, secret) unchanged.
         UPDATE: chat-lock.php's server-side 'unlock' stub has since been
         wired to the real verify_pin()/check_pin_throttle() path (see that
         file's own TASK-3 integration block) — no longer a stub, and it
         now reissues a fresh sessionToken on success (locking invalidated
         the old one via invalidate_all_tokens()), stored below the same
         way pinManageCall()'s handlers already do. */
      lockedGateContainer = el('div', { class: 'pcw-locked-gate' });
      /* TASK-3 END */
      lockedOverlayEl.appendChild(lockIcon);
      lockedOverlayEl.appendChild(title);
      lockedOverlayEl.appendChild(desc);
      lockedOverlayEl.appendChild(lockedGateContainer);
      panelEl.appendChild(lockedOverlayEl);
    }
    // TASK-4 FIX: render the gate on EVERY show, not once. pcwRenderAuthGate
    // clears its container, so a second lock now starts with a fresh empty
    // input and an enabled button instead of the stuck "Checking..." state
    // (and old PIN) left by the previous successful unlock.
    /* TASK-3 START — AuthGate integration (same call shape as before) */
    pcwRenderAuthGate(lockedGateContainer, {
      tier: 'pin',
      principalId: state.chatId,
      verify: (pin) => apiPost('chat-lock.php', { action: 'unlock', principalId: state.chatId, secret: pin }),
      onVerifySuccess: (json) => {
        pcwUpdateSessionToken(json.sessionToken);
        lockedOverlayEl.classList.remove('show'); // fade out — Design System Section 8
      },
      onVerifyFail: () => {}, // pcwRenderAuthGate already renders the error inline
    });
    /* TASK-3 END */
    void lockedOverlayEl.offsetWidth; // force reflow so the fade-in actually animates from opacity 0
    lockedOverlayEl.classList.add('show');
  }
  // ============================================
  // TASK-4 END — LOCK TOGGLE
  // ============================================
```
<!-- TASK-4 END -->

*(This block is the complete, verbatim Task 4 feature section as it exists in the delivered file; it is inserted directly after the closing brace of `closeOptionsSheet()`, and the old-code anchor above occurs once in the file.)*

---

**Block 4 — hook in `openPanel()`, live-session branch**

<!-- TASK-4 START -->
**Old code:**
```js
      // actually shown, rather than silently missing.
      rebuildLiveView();
```
**New code:**
```js
      // actually shown, rather than silently missing.
      rebuildLiveView();
      pcwLockCheckOnPanelOpen(); // TASK-4 — server-authoritative lock check, may show the overlay on top of the rebuilt view
```
<!-- TASK-4 END -->

---

**Block 5 — hook in `openPanel()`, resume/launcher branch**

<!-- TASK-4 START -->
**Old code:**
```js
    } else if (!state.mode) {
      renderLauncher();
    }
```
**New code:**
```js
    } else if (!state.mode) {
      renderLauncher();
    }
    pcwLockCheckOnPanelOpen(); // TASK-4 — server-authoritative lock check for the resume/launcher paths too
```
<!-- TASK-4 END -->

*(This `} else if (!state.mode) { renderLauncher(); }` sequence occurs once in the file, immediately before `openPanel()`'s closing brace and the following `function rebuildLiveView() {`.)*

---

**Block 6 — hook in `closePanel()`**

<!-- TASK-4 START -->
**Old code:**
```js
    // unread badge can still pick up new messages while collapsed.
```
**New code:**
```js
    // unread badge can still pick up new messages while collapsed.
    pcwLockOnPanelClosed(); // TASK-4 — tells the server to start its own idle clock
```
<!-- TASK-4 END -->

---

**Block 7 — hook in `openLiveThread()`, after the TASK-1 token-persist block**

<!-- TASK-4 START -->
**Old code:**
```js
    if (state.sessionToken) localStorage.setItem(CONFIG.storageKeyToken, state.sessionToken);
    /* TASK-1 END */
```
**New code:**
```js
    if (state.sessionToken) localStorage.setItem(CONFIG.storageKeyToken, state.sessionToken);
    /* TASK-1 END */
    pcwLockSyncFromServer(); // TASK-4 FIX — reconcile this device's lock-preference mirror against the real per-thread server row now that chatId is known; see that function's own comment for why this was missing
```
<!-- TASK-4 END -->

*(This `if (state.sessionToken) localStorage.setItem(...); /* TASK-1 END */` pair occurs once in the file, inside `openLiveThread()`, immediately before the existing `updateLauncherButtonState();` call.)*

---

**Block 8 — `pollOnce()`: show the lock overlay on a rejected poll, and piggyback the delayed-lock server check on the existing poll tick. This function was last submitted by Task 1; this round added the `else` branch and the `pcwLockBackgroundCheck()` call shown below — no other line in the function changed.**

<!-- TASK-4 START -->
**Old code:**
```js
    try {
      const json = await apiGet('chat-poll.php', {
        chatId: state.chatId,
        lastMessageId: state.lastMessageId,
        lastEventId: state.lastEventId,
      });
      if (json.success) {
        handlePollResult(json);
      }
    } catch (err) {
      dlog('poll failed: ' + err.message, 'error');
    }
    state.pollInFlight = false;
    scheduleNextPoll();
  }
```
**New code:**
```js
    try {
      const json = await apiGet('chat-poll.php', {
        chatId: state.chatId,
        lastMessageId: state.lastMessageId,
        lastEventId: state.lastEventId,
      });
      if (json.success) {
        handlePollResult(json);
      } else {
        pcwLockOnPollRejected(); // TASK-4 FIX — a rejected poll on an open panel may mean the server just locked this chat (token invalidated)
      }
    } catch (err) {
      dlog('poll failed: ' + err.message, 'error');
    }
    pcwLockBackgroundCheck(); // TASK-4 FIX — piggyback the delayed-lock server check on this existing poll tick; see its own comment above for why this is required, not cosmetic
    state.pollInFlight = false;
    scheduleNextPoll();
  }
```
<!-- TASK-4 END -->

*(The closing `  }` of `pollOnce()` is reached via its unique opening line `async function pollOnce() {` a few lines above this block, confirming there is only one function this snippet can belong to.)*

## New files
- `admin/chat-lock.php` — New file — in the zip. Session-locking endpoint: stores each thread's lock preference, records the server-side idle clock, independently re-checks elapsed idle time, triggers the lock (calling the shared `invalidate_all_tokens()`), and handles PIN-gated unlock. Creates its own table `chat_lock_prefs` idempotently on first use (not added to `chat-setup.php`, per Scope Lock — flagged below for whoever owns the project-wide schema).

**New-path check:** `admin/` is an existing, documented folder (ADMIN_DASHBOARD_PROJECT_SUMMARY.md's folder-structure tree and its list of `admin/chat-*.php` endpoints both show it). My one new file sits inside that existing folder at an existing naming pattern (`admin/chat-<feature>.php`), so I am not introducing any new top-level folder or path the integrator doesn't already expect. No `index.html` naming question applies here — this task added no HTML page.

## Bugs Found Outside Task Scope
1. After a lock triggers, a full page reload sends the customer through the pre-chat gate and then the PIN gate rather than straight to the lock overlay (`chat-widget.js`, session-bootstrap logic — Task 1's design, not something I touched).
2. `chat-start.php`'s own IP derivation for its `pin_ip:` rate-limit bucket was not visible to me. `chat-lock.php`'s `unlock` action uses `$_SERVER['REMOTE_ADDR']` for the same bucket name; if `chat-start.php` derives the IP differently (e.g. from a proxy header), the two buckets will not actually share a limit as intended. Someone with access to `chat-start.php` should confirm.
3. The `chat-rate.php` email cross-check mentioned in Worker-Sync-Notice-Post-Task1-Task7.md is still unassigned to any task, as of this file.
4. `includes/chat-auth.php` and `chat-pin-manage.php` source were not made available to me at any point, so I could not verify `verify_token()`'s exact signature or behavior beyond what the shared contract documents describe. The task assignment's "files to request: none" was not accurate for fully wiring this feature — I needed to see the current `chat-widget.js` (supplied mid-task) to integrate correctly.

## Pending / Doubts / Still Open
- `chat_lock_prefs` is created by `chat-lock.php` itself on first call (`CREATE TABLE IF NOT EXISTS`), not via `chat-setup.php`. If the live DB user lacks `CREATE TABLE`, the first request will fail. Whoever owns schema setup should either grant that permission or add this table to `chat-setup.php` directly.
- Deviation 2 (REMOTE_ADDR vs. chat-start.php's real IP source) is unverified and could silently split one rate-limit bucket into two weaker ones — see Bug 2 above. I could not resolve this myself without `chat-start.php`'s source.
- I have not been able to run `php -l` or any PHP test in my sandbox at any point in this task, in this round or prior ones. `chat-lock.php` has only ever been hand-reviewed, never executed. This remains true as of this hand-in.
- No other item is outstanding on my side; everything else described as a gap in earlier rounds of this file (session-token plumbing, the delayed-lock enforcement gap, the `check_lock`/`panel_opened` race, the PIN-required lockout, the stuck overlay, the missing close button, dark-on-dark labels, second-tab detection) was fixed in the Code Changes section above.

## Self-Check Confirmation
- I touched only `chat-widget.js` (existing, changed) and `chat-lock.php` (new). No other file was opened or edited.
- Every code block above is wrapped in `TASK-4 START` / `TASK-4 END` anchor comments, matching the anchors actually present in the delivered file.
- Every "Old code" anchor was confirmed by exact-string search to occur exactly once in the file. For the `pollOnce()` block (Block 8), the old text is the Task 1 version of the function; it no longer exists verbatim in my working copy because my two additions sit inside it, and I verified programmatically that the old block equals the new block with only my two `TASK-4 FIX` additions removed. Every "New code" block is the verbatim text from the delivered file, with no elisions.
- This completion file and `admin/chat-lock.php` are both inside `Admin-Project-1-Task-4.zip`, at `Task-4-Completion.md` (zip root) and `admin/chat-lock.php` respectively — `unzip -l` confirms both paths and no others.
- The zip's copy of `admin/chat-lock.php` is byte-identical to my working copy (`cmp`). It has no `config.php`, `pet.json`, `.env`, and nothing under `applications/` or `uploads/`. I searched the file for credentials (passwords, keys, DSNs); the only hits are the PIN variable `$secret`. It reaches the database through the existing `chatDb()` helper from `chat-config.php` and needs no new setting. It creates the `chat_lock_prefs` table itself on first use — see Pending/Doubts above.
- `node --check` passes on the delivered `chat-widget.js`. `php -l` is unavailable in my sandbox (no PHP interpreter); `chat-lock.php` was hand-reviewed line by line instead, including every response field the widget's `pcwRenderAuthGate()` and lock functions consume.
- Zip name `Admin-Project-1-Task-4.zip` matches the project file's own name, `Admin-Project-1-Worker-Task-Assignments.md`, plus my task number.
- Fade requirement met (opacity/visibility transition, 0.3s, `cubic-bezier(0.4,0,0.2,1)`, Design System Section 8); `invalidate_all_tokens()` is called inside `pcw_lock_trigger()` in `chat-lock.php`; the server independently re-checks elapsed idle time on every `check_lock` call rather than trusting the client's countdown.
