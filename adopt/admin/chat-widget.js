/**
 * chat-widget.js
 *
 * Single drop-in script for the P2P live chat widget (Tier 1 item 21).
 * Include with one <script> tag on any page; it injects its own CSS and
 * HTML, and works unmodified everywhere.
 *
 * This file owns:
 *  - The launcher panel (Section 1): explanatory text + "Chat by Email" /
 *    "Chat Live" buttons, with seamless AJAX swapping between the two —
 *    no page navigation, no reload.
 *  - "Chat by Email": fetches contact-us.html's form markup via AJAX and
 *    injects it into the same panel. contact-handler.php itself is
 *    completely untouched; only how the form gets on-screen changes.
 *  - "Chat Live": the full live chat experience (pre-chat gate, composer,
 *    polling, drafts, MediaRecorder voice, grouped multi-media send,
 *    persistence across navigation).
 *
 * Logs to window.dlog when present (the admin dashboard's global
 * debugger) and safely no-ops everywhere else — this script runs on
 * public pages where that debugger does not exist.
 */

(function () {
  'use strict';

  // ============================================
  // CONFIG
  // ============================================
  const CONFIG = {
    apiBase: '/admin/',        // all chat PHP endpoints live in /admin/ alongside admin-dashboard.html
    pollIntervalFocused: 2500,   // 2-3s while tab focused and chat open
    pollIntervalBlurred: 15000,  // backs off when tab hidden/blurred
    typingPingInterval: 3000,
    draftDebounceMs: 10000,      // Tier 1 item 14 — 10 second "stopped typing" debounce
    // editDeleteWindowMinutes removed — edit/delete is unlimited (no time restriction), per decision
    storageKeyChatId: 'pc_chat_active_id',
    storageKeyLastMsg: 'pc_chat_last_msg_id',
    /* TASK-1 START — session-token persistence key, same localStorage
       pattern as the other pc_chat_* keys above. */
    storageKeyToken: 'pc_chat_session_token',
    /* TASK-5 START — random browser-held device secret; only its server hash
       is persisted remotely. */
    storageKeyDevice: 'pc_chat_device_token',
    /* TASK-5 END */
    /* TASK-1 END */
    storageKeyDraftUuid: 'pc_chat_draft_uuid', // pre-thread temporary draft key, Section 4
    contactFormUrl: '/contact-us/contact-us.html', // absolute path — contact-us.html lives at /contact-us/ not site root
  };

  function dlog(msg, level) {
    if (typeof window.dlog === 'function') {
      window.dlog('Chat widget: ' + msg, level || 'info');
    }
  }

  // ============================================
  // STATE
  // ============================================
  const state = {
    panelOpen: false,
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
    /* TASK-3 START — current protection tier for the open conversation. */
    authTier: 'none',
    /* TASK-3 END */
    /* TASK-1 END */
    fullName: null,
    email: null,
    status: null,          // waiting | active | done
    lastMessageId: 0,
    lastEventId: 0,
    pollTimer: null,
    pollInFlight: false,
    activeUploadCount: 0,  // drives the beforeunload warning (Tier 1 item 13)
    unreadCount: 0,        // drives the launcher button badge while panel is closed
    composer: {
      text: '',
      imageTokens: [],     // up to 10 image tokens
      audioToken: null,
      videoTokens: [],     // up to 5 video tokens
      uploadingCount: 0,   // count of active uploads (not boolean, multiple can upload simultaneously)
    },
    draftTimer: null,
    draftKey: null,        // real chatId once a thread exists, else the temp UUID
  };

  // ============================================
  // SMALL UTILITIES
  // ============================================
  function uuid() {
    if (window.crypto && window.crypto.randomUUID) return window.crypto.randomUUID();
    // fallback for older browsers
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
      const r = (Math.random() * 16) | 0;
      const v = c === 'x' ? r : (r & 0x3 | 0x8);
      return v.toString(16);
    });
  }

  function el(tag, attrs, children) {
    const node = document.createElement(tag);
    if (attrs) {
      for (const k in attrs) {
        if (k === 'class') node.className = attrs[k];
        else if (k === 'html') node.innerHTML = attrs[k];
        else node.setAttribute(k, attrs[k]);
      }
    }
    if (children) {
      (Array.isArray(children) ? children : [children]).forEach(c => {
        if (typeof c === 'string') node.appendChild(document.createTextNode(c));
        else if (c) node.appendChild(c);
      });
    }
    return node;
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

  /* TASK-5 START — create one random browser-held device secret; the server
     stores only its SHA-256 verifier and never the raw value. */
  function deviceToken() {
    let token = localStorage.getItem(CONFIG.storageKeyDevice);
    if (!token) {
      token = uuid().replace(/-/g, '') + uuid().replace(/-/g, '');
      localStorage.setItem(CONFIG.storageKeyDevice, token);
    }
    return token;
  }
  /* TASK-5 END */

  /* TASK-3 START — shared PIN/password gate contract. */
  function AuthGate({ tier, principalId, email, onVerifySuccess, onVerifyFail }) {
    const wrap = el('div', { class: 'pcw-auth-gate' });
    const input = el('input', { type: tier === 'pin' ? 'tel' : 'password', inputmode: tier === 'pin' ? 'numeric' : 'text', maxlength: tier === 'pin' ? '6' : '1024', placeholder: tier === 'pin' ? 'Enter your 6-digit PIN' : 'Enter your chat password' });
    const remember = el('label', { class: 'pcw-check-row' }, [el('input', { type: 'checkbox' }), el('span', {}, 'Remember this device')]);
    const error = el('div', { class: 'pcw-error-msg' });
    const submit = el('button', { type: 'button', class: 'pcw-primary-btn' }, 'Unlock chat');
    wrap.appendChild(el('h3', {}, tier === 'pin' ? 'This chat is PIN protected' : 'This chat is password protected'));
    wrap.appendChild(input); wrap.appendChild(remember); wrap.appendChild(error); wrap.appendChild(submit);
    submit.addEventListener('click', async () => {
      submit.disabled = true; error.classList.remove('show');
      try {
        const json = await apiPost('chat-auth-gate.php', { action:'verify', chatId:principalId, email, tier, secret:input.value, rememberDevice:remember.querySelector('input').checked, deviceToken:deviceToken() });
        if (!json.success) throw new Error(json.error || 'Could not unlock chat.');
        onVerifySuccess(json);
      } catch (e) { error.textContent = e.message; error.classList.add('show'); if (onVerifyFail) onVerifyFail(e); submit.disabled = false; }
    });
    if (tier === 'password') {
      const forgot = el('button', { type: 'button', class: 'pcw-link-btn' }, 'Forgot password?');
      forgot.addEventListener('click', async () => {
        const reset = await apiPost('chat-password-reset.php', { action:'request', email });
        const temp = window.prompt(reset.message || 'Check your email, then enter the temporary password:');
        if (!temp) return;
        const logged = await apiPost('chat-password-reset.php', { action:'temporaryLogin', email, temporaryPassword:temp });
        if (!logged.success) { error.textContent = logged.error || 'Temporary password rejected.'; error.classList.add('show'); return; }
        const next = window.prompt('Choose a new password of at least 12 characters:');
        if (!next) return;
        const changed = await apiPost('chat-password-reset.php', { action:'change', email, chatId:logged.chatId, sessionToken:logged.sessionToken, newPassword:next });
        error.textContent = changed.message || changed.error || 'Password reset complete. Enter the new password above.'; error.classList.add('show');
      });
      wrap.appendChild(forgot);
    }
    return wrap;
  }
  /* TASK-3 END */

  function initials(name) {
    if (!name) return '?';
    const parts = name.trim().split(/\s+/).filter(Boolean);
    if (parts.length === 0) return '?';
    if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
  }

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str == null ? '' : String(str);
    return div.innerHTML;
  }

  // ============================================
  // CSS — design tokens match contact-us.html exactly, no new identity invented
  // ============================================
  const STYLE = `
  .pcw-launcher-btn {
    position: fixed; bottom: 20px; right: 20px; z-index: 9998;
    width: 60px; height: 60px; border-radius: 50%;
    background: linear-gradient(135deg, #10214b, #1a2f5c);
    border: 2px solid #d7bd88; color: #fff; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; box-shadow: 0 6px 20px rgba(16,33,75,0.35);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .pcw-launcher-btn:hover { transform: scale(1.06); box-shadow: 0 8px 26px rgba(16,33,75,0.45); }
  .pcw-launcher-btn .pcw-badge {
    position: absolute; top: -4px; right: -4px; min-width: 20px; height: 20px;
    border-radius: 10px; background: #ED7624; color: #fff; font-size: 11px;
    font-weight: 700; display: none; align-items: center; justify-content: center;
    padding: 0 5px; font-family: 'Sora', system-ui, sans-serif; border: 2px solid #fff;
  }
  .pcw-launcher-btn .pcw-badge.show { display: flex; }
  .pcw-launcher-btn .pcw-active-dot {
    position: absolute; bottom: 2px; right: 2px; width: 12px; height: 12px;
    border-radius: 50%; background: #10b981; border: 2px solid #fff; display: none;
  }
  .pcw-launcher-btn .pcw-active-dot.show { display: block; }

  .pcw-backdrop {
    position: fixed; inset: 0; background: rgba(16,33,75,0.35);
    opacity: 0; visibility: hidden; transition: opacity 0.3s ease;
    z-index: 9998;
  }
  .pcw-backdrop.show { opacity: 1; visibility: visible; }

  .pcw-powered-by {
    flex-shrink: 0; text-align: center; padding: 6px 0 10px;
    font-size: 10.5px; color: #b0a69a; font-family: 'Sora', system-ui, sans-serif;
  }

  .pcw-sheet-backdrop {
    position: absolute; inset: 0; background: rgba(0,0,0,0.4);
    opacity: 0; visibility: hidden; transition: opacity 0.3s ease; z-index: 10001;
    border-radius: inherit;
  }
  .pcw-sheet-backdrop.show { opacity: 1; visibility: visible; }

  .pcw-options-sheet {
    position: absolute; left: 0; right: 0; bottom: 0;
    background: #121212; border-radius: 16px 16px 0 0; z-index: 10002;
    max-height: 82%; display: flex; flex-direction: column;
    transform: translateY(100%);
    transition: transform 0.35s cubic-bezier(.32,.72,0,1);
    box-shadow: 0 -8px 32px rgba(0,0,0,0.4);
  }
  .pcw-options-sheet.open { transform: translateY(0); }
  .pcw-sheet-handle { width: 36px; height: 4px; background: rgba(255,255,255,0.25); border-radius: 4px; margin: 9px auto 0; flex-shrink: 0; }
  .pcw-sheet-header { display: flex; align-items: center; justify-content: space-between; padding: 12px 18px 6px; flex-shrink: 0; }
  .pcw-sheet-header h3 { font-family: 'Sora', system-ui, sans-serif; font-size: 16px; color: #fff; font-weight: 700; margin: 0; }
  .pcw-sheet-close { background: none; border: none; color: #fff; font-size: 20px; line-height: 1; cursor: pointer; padding: 4px 8px; opacity: 0.8; }
  .pcw-sheet-close:hover { opacity: 1; }
  .pcw-sheet-body { overflow-y: auto; padding: 6px 18px 18px; flex: 1; }

  .pcw-option-row { display: flex; align-items: center; gap: 12px; padding: 13px 4px; border-bottom: 1px solid rgba(255,255,255,0.07); }
  .pcw-option-row:last-of-type { border-bottom: none; }
  .pcw-option-clickable { cursor: pointer; }
  .pcw-option-clickable:hover { background: rgba(255,255,255,0.04); border-radius: 8px; }
  .pcw-option-icon {
    width: 34px; height: 34px; border-radius: 50%; background: rgba(237,118,36,0.15);
    color: #ED7624; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;
  }
  .pcw-option-label { flex: 1; color: #e5e5e5; font-family: 'Sora', system-ui, sans-serif; font-size: 13.5px; }
  .pcw-option-chevron { color: rgba(255,255,255,0.4); font-size: 11px; transition: transform 0.2s ease; }
  .pcw-option-row.open .pcw-option-chevron { transform: rotate(180deg); }

  .pcw-toggle-switch {
    width: 42px; height: 24px; border-radius: 999px; border: none; background: rgba(255,255,255,0.15);
    cursor: pointer; position: relative; flex-shrink: 0; transition: background 0.2s ease;
  }
  .pcw-toggle-switch::after {
    content: ''; position: absolute; top: 2px; left: 2px; width: 20px; height: 20px;
    border-radius: 50%; background: #fff; transition: transform 0.2s ease;
  }
  .pcw-toggle-switch.on { background: #ED7624; }
  .pcw-toggle-switch.on::after { transform: translateX(18px); }

  .pcw-accessibility-panel {
    max-height: 0; opacity: 0; overflow: hidden; display: flex; gap: 10px;
    transition: max-height 0.3s ease, opacity 0.25s ease, margin 0.3s ease;
  }
  .pcw-accessibility-panel.open { max-height: 70px; opacity: 1; margin-top: 10px; padding-bottom: 4px; }
  .pcw-a11y-icon-btn {
    width: 40px; height: 40px; border-radius: 50%; border: 1.5px solid rgba(255,255,255,0.15);
    background: rgba(255,255,255,0.04); color: #d7bd88; cursor: pointer;
    display: flex; align-items: center; justify-content: center; font-size: 15px;
    transition: border-color 0.15s ease, background 0.15s ease;
  }
  .pcw-a11y-icon-btn:hover { border-color: #ED7624; background: rgba(237,118,36,0.1); }
  .pcw-a11y-icon-btn.active { border-color: #ED7624; background: #ED7624; color: #fff; }

  /* ── Accessibility effects — every rule below is scoped under .pcw-panel
     so nothing ever touches the host page outside the widget's own DOM ── */
  .pcw-panel.pcw-a11y-large-text .pcw-msg-bubble,
  .pcw-panel.pcw-a11y-large-text .pcw-composer-input,
  .pcw-panel.pcw-a11y-large-text .pcw-field label,
  .pcw-panel.pcw-a11y-large-text .pcw-field input,
  .pcw-panel.pcw-a11y-large-text .pcw-option-label,
  .pcw-panel.pcw-a11y-large-text .pcw-dropdown-item { font-size: 16px !important; line-height: 1.5 !important; }
  .pcw-panel.pcw-a11y-large-text h3 { font-size: 19px !important; }

  .pcw-panel.pcw-a11y-high-contrast { background: #000 !important; color: #fff !important; }
  .pcw-panel.pcw-a11y-high-contrast .pcw-msg-row.admin .pcw-msg-bubble,
  .pcw-panel.pcw-a11y-high-contrast .pcw-msg-row.bot .pcw-msg-bubble,
  .pcw-panel.pcw-a11y-high-contrast .pcw-msg-row.system .pcw-msg-bubble {
    background: #000 !important; color: #fff !important; border: 2px solid #ffdd00 !important;
  }
  .pcw-panel.pcw-a11y-high-contrast .pcw-msg-row.user .pcw-msg-bubble {
    background: #ffdd00 !important; color: #000 !important; border: 2px solid #ffdd00 !important;
  }
  .pcw-panel.pcw-a11y-high-contrast .pcw-header { background: #000 !important; border-bottom: 2px solid #ffdd00; }
  .pcw-panel.pcw-a11y-high-contrast .pcw-composer,
  .pcw-panel.pcw-a11y-high-contrast .pcw-body { background: #000 !important; }
  .pcw-panel.pcw-a11y-high-contrast .pcw-composer-input { background: #000 !important; color: #fff !important; border: 2px solid #ffdd00 !important; }
  .pcw-panel.pcw-a11y-high-contrast .pcw-send-btn { background: #ffdd00 !important; color: #000 !important; }
  .pcw-panel.pcw-a11y-high-contrast .pcw-powered-by { color: #888 !important; }

  .pcw-panel.pcw-a11y-reduce-motion,
  .pcw-panel.pcw-a11y-reduce-motion * {
    animation: none !important; transition: none !important;
  }

  .pcw-panel {
    position: fixed; bottom: 92px; right: 20px; z-index: 9999;
    width: 380px; max-width: calc(100vw - 32px); height: 560px; max-height: calc(100vh - 120px);
    background: #fff; border-radius: 16px; box-shadow: 0 16px 48px rgba(16,33,75,0.28);
    display: none; flex-direction: column; overflow: hidden;
    font-family: 'Sora', system-ui, sans-serif; color: #2c2416;
    animation: pcwSlideUp 0.3s ease;
  }
  .pcw-panel.open { display: flex; }
  @keyframes pcwSlideUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }

  /* TASK-9 START */
  /* ── TASK-9c: "lovely amoeba" panel-open entrance animation ──────────
     Purely decorative, one-time, plays only when a customer's pre-chat
     gate submission opens the live thread (never on session resume).
     Absolutely positioned overlay inside .pcw-panel (which is already
     position:fixed and overflow:hidden, so the blobs get clipped to the
     panel's rounded corners for free). pointer-events:none the whole
     time so it never blocks the composer/messages that render
     underneath it in the same instant — see triggerBlobIntro() in JS,
     which appends this then removes it after one cycle. Uses the
     Section 8 universal timing curve (cubic-bezier(0.4,0,0.2,1)) and
     the three core brand colors (Navy/Gold/Orange — Orange's presence
     here also satisfies the Section 2 "Orange must appear on every
     surface" rule for this transient surface). No single documented
     Section 4 loading style covers an organic blob shape, so this is a
     new, minimal addition built from the same brand-color/timing rules
     rather than reusing an unrelated loading spinner. */
  .pcw-blob-intro {
    position: absolute; inset: 0; z-index: 5; pointer-events: none;
    overflow: hidden; border-radius: inherit;
    opacity: 0; animation: pcwBlobFade 1.1s cubic-bezier(0.4,0,0.2,1) forwards;
  }
  .pcw-blob {
    position: absolute; width: 60%; padding-top: 60%; /* square, sized by width */
    animation: pcwBlobMorph 1.1s cubic-bezier(0.4,0,0.2,1) forwards;
  }
  .pcw-blob-1 {
    top: -10%; left: -12%; background: radial-gradient(circle at 35% 35%, #1a2f5c, #10214b);
  }
  .pcw-blob-2 {
    bottom: -14%; right: -10%; width: 50%; padding-top: 50%;
    background: radial-gradient(circle at 40% 40%, #e2c99a, #d7bd88);
    animation-delay: 0.08s;
  }
  .pcw-blob-3 {
    top: 30%; right: 8%; width: 30%; padding-top: 30%;
    background: radial-gradient(circle at 45% 45%, #ff8a3d, #ED7624);
    animation-delay: 0.16s;
  }
  @keyframes pcwBlobFade {
    0%   { opacity: 0; }
    12%  { opacity: 0.9; }
    75%  { opacity: 0.55; }
    100% { opacity: 0; }
  }
  @keyframes pcwBlobMorph {
    0% {
      transform: scale(0.3) rotate(0deg);
      border-radius: 42% 58% 65% 35% / 45% 40% 60% 55%;
    }
    35% {
      transform: scale(1.05) rotate(35deg);
      border-radius: 58% 42% 38% 62% / 62% 55% 45% 38%;
    }
    70% {
      transform: scale(0.92) rotate(70deg);
      border-radius: 48% 52% 55% 45% / 40% 58% 42% 60%;
    }
    100% {
      transform: scale(1.3) rotate(110deg);
      border-radius: 50% 50% 50% 50% / 50% 50% 50% 50%;
    }
  }
  /* TASK-9 END */

  .pcw-header {
    background: linear-gradient(135deg, #10214b, #1a2f5c); color: #fff;
    padding: 16px 18px; display: flex; align-items: center; justify-content: space-between;
    flex-shrink: 0;
  }
  .pcw-header h3 { font-family: 'Cormorant Garamond', serif; font-weight: 700; font-size: 19px; margin: 0; }
  .pcw-header .pcw-sub { font-size: 12px; color: rgba(255,255,255,0.65); margin-top: 2px; }
  .pcw-header-btns { display: flex; gap: 8px; align-items: center; }
  .pcw-icon-btn {
    width: 32px; height: 32px; border-radius: 50%; border: none; cursor: pointer;
    background: rgba(255,255,255,0.12); color: #fff; display: flex; align-items: center; justify-content: center;
    font-size: 14px; transition: background 0.15s ease;
  }
  .pcw-icon-btn:hover { background: rgba(255,255,255,0.22); }

  .pcw-body { flex: 1; overflow-y: auto; padding: 18px; }

  .pcw-launcher-intro { font-size: 13.5px; color: #4a4030; line-height: 1.6; margin-bottom: 18px; }
  .pcw-launcher-choices { display: flex; flex-direction: column; gap: 10px; }
  .pcw-choice-btn {
    display: flex; align-items: center; gap: 12px; padding: 14px 16px;
    border: 1.5px solid rgba(16,33,75,0.12); border-radius: 12px; background: #fdfcfa;
    cursor: pointer; text-align: left; transition: border-color 0.15s ease, background 0.15s ease;
    font-family: 'Sora', system-ui, sans-serif;
  }
  .pcw-choice-btn:hover { border-color: #ED7624; background: #fff8f2; }
  .pcw-choice-icon {
    width: 40px; height: 40px; border-radius: 10px; background: #10214b; color: #d7bd88;
    display: flex; align-items: center; justify-content: center; font-size: 17px; flex-shrink: 0;
  }
  .pcw-choice-title { font-weight: 600; font-size: 14px; color: #10214b; }
  .pcw-choice-desc { font-size: 12px; color: #7a6d5f; margin-top: 2px; }

  .pcw-back-link {
    display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px;
    color: #7a6d5f; cursor: pointer; margin-bottom: 14px; background: none; border: none; padding: 0;
    font-family: 'Sora', system-ui, sans-serif;
  }
  .pcw-back-link:hover { color: #ED7624; }

  .pcw-field { margin-bottom: 14px; }
  .pcw-field label { display: block; font-size: 12.5px; font-weight: 600; color: #10214b; margin-bottom: 5px; }
  .pcw-field input, .pcw-field select, .pcw-field textarea {
    width: 100%; padding: 10px 12px; border: 1.5px solid rgba(16,33,75,0.14); border-radius: 8px;
    font-family: 'Sora', system-ui, sans-serif; font-size: 13.5px; color: #2c2416; background: #fff;
  }
  .pcw-field input:focus, .pcw-field select:focus, .pcw-field textarea:focus {
    outline: none; border-color: #ED7624;
  }
  .pcw-field-hint { font-size: 11px; color: #7a6d5f; margin-top: 4px; }

  .pcw-primary-btn {
    width: 100%; padding: 12px; border-radius: 9px; border: none; cursor: pointer;
    background: #ED7624; color: #fff; font-weight: 600; font-size: 13.5px;
    font-family: 'Sora', system-ui, sans-serif; transition: background 0.15s ease;
  }
  .pcw-primary-btn:hover { background: #d4651a; }
  .pcw-primary-btn:disabled { background: #d7bd88; cursor: not-allowed; }

  .pcw-error-msg { font-size: 12px; color: #ef4444; margin-top: 8px; display: none; }
  .pcw-error-msg.show { display: block; }

  /* ── Live chat view ─────────────────────────────────────────────── */
  .pcw-chat-body { display: flex; flex-direction: column; height: 100%; padding: 0; }
  .pcw-messages { flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 4px; }

  .pcw-chat-id-pill {
    align-self: center; font-size: 10.5px; color: #7a6d5f; background: #f4f1ea;
    padding: 4px 10px; border-radius: 20px; margin-bottom: 10px;
  }

  .pcw-joined-banner {
    background: #fff8f2; border: 1px solid rgba(237,118,36,0.2); border-radius: 10px;
    padding: 10px 12px; margin-bottom: 12px; font-size: 12px;
  }
  .pcw-joined-banner .pcw-jb-line1 { font-weight: 600; color: #10214b; margin-bottom: 6px; }
  .pcw-joined-banner .pcw-jb-line2 { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
  .pcw-jb-person { display: flex; align-items: center; gap: 6px; }
  .pcw-jb-avatar {
    width: 22px; height: 22px; border-radius: 50%; object-fit: cover;
    background: #10214b; color: #d7bd88; font-size: 9px; font-weight: 700;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
  }
  .pcw-jb-link-icon { color: #d7bd88; font-size: 11px; }
  .pcw-jb-bluecheck { color: #1d9bf0; font-size: 11px; margin-left: 2px; }
  .pcw-jb-position { color: #7a6d5f; font-size: 11px; }

  .pcw-msg-row { display: flex; gap: 8px; max-width: 88%; margin-bottom: 2px; }
  .pcw-msg-row.user { align-self: flex-end; flex-direction: row-reverse; }
  .pcw-msg-row.admin, .pcw-msg-row.bot, .pcw-msg-row.system { align-self: flex-start; }
  .pcw-msg-avatar {
    width: 26px; height: 26px; border-radius: 50%; object-fit: cover; flex-shrink: 0;
    background: #10214b; color: #d7bd88; font-size: 10px; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
  }
  .pcw-msg-bubble { border-radius: 14px; padding: 9px 13px; font-size: 13px; line-height: 1.5; }
  .pcw-msg-row.user .pcw-msg-bubble { background: #10214b; color: #fff; border-bottom-right-radius: 4px; }
  .pcw-msg-row.admin .pcw-msg-bubble, .pcw-msg-row.bot .pcw-msg-bubble { background: #f4f1ea; color: #2c2416; border-bottom-left-radius: 4px; }
  .pcw-msg-row.system .pcw-msg-bubble { background: #fff8f2; color: #7a6d5f; font-style: italic; border: 1px dashed rgba(237,118,36,0.25); }
  .pcw-msg-meta-row {
    display: flex; align-items: center; gap: 6px; margin-top: 3px;
    font-size: 10px; color: #a89c8a; position: relative;
    transition: opacity 0.2s ease, transform 0.2s ease;
  }
  .pcw-msg-row.user .pcw-msg-meta-row { justify-content: flex-end; }
  .pcw-msg-meta-row.faded { opacity: 0; transform: translateY(-2px); pointer-events: none; position: absolute; }
  .pcw-msg-time { white-space: nowrap; }
  .pcw-edited-tag-btn {
    border: none; background: none; cursor: pointer; font-size: 10px; font-style: italic;
    color: #a89c8a; padding: 0; text-decoration: underline; text-underline-offset: 2px;
    transition: color 0.15s ease;
  }
  .pcw-edited-tag-btn:hover, .pcw-edited-tag-btn.active { color: #ED7624; }
  .pcw-original-reveal {
    max-height: 0; overflow: hidden; opacity: 0; transition: max-height 0.25s ease, opacity 0.2s ease, margin 0.25s ease;
    background: rgba(16,33,75,0.05); border-radius: 8px; margin-top: 0;
  }
  .pcw-original-reveal.show { max-height: 200px; opacity: 1; margin-top: 6px; padding: 8px 10px; }
  .pcw-original-reveal-label { font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #a89c8a; margin-bottom: 3px; }
  .pcw-original-reveal-text { font-size: 12px; color: #4a4030; font-style: italic; line-height: 1.4; }

  .pcw-msg-actions {
    display: flex; gap: 4px; margin-top: 3px; opacity: 0; max-height: 0; overflow: hidden;
    transition: opacity 0.2s ease, max-height 0.2s ease;
  }
  .pcw-msg-row.user .pcw-msg-actions { justify-content: flex-end; }
  .pcw-msg-actions.show { opacity: 1; max-height: 30px; }
  .pcw-msg-sender { font-size: 10.5px; font-weight: 600; color: #10214b; margin-bottom: 2px; }

  .pcw-msg-media img { max-width: 220px; border-radius: 10px; display: block; }
  .pcw-msg-media video { max-width: 220px; border-radius: 10px; display: block; }
  .pcw-audio-bar {
    display: flex; align-items: center; gap: 8px; min-width: 180px;
  }
  .pcw-audio-bar button { width: 28px; height: 28px; border-radius: 50%; border: none; background: #ED7624; color: #fff; cursor: pointer; flex-shrink: 0; }
  .pcw-audio-bar .pcw-audio-track { flex: 1; height: 4px; background: rgba(0,0,0,0.12); border-radius: 2px; position: relative; }
  .pcw-audio-bar .pcw-audio-fill { position: absolute; left: 0; top: 0; height: 100%; background: #ED7624; border-radius: 2px; width: 0%; }

  .pcw-typing-dots { display: flex; gap: 3px; padding: 9px 13px; align-self: flex-start; }
  .pcw-typing-dots span { width: 6px; height: 6px; border-radius: 50%; background: #a89c8a; animation: pcwTypingBounce 1.2s infinite ease-in-out; }
  .pcw-typing-dots span:nth-child(2) { animation-delay: 0.15s; }
  .pcw-typing-dots span:nth-child(3) { animation-delay: 0.3s; }
  @keyframes pcwTypingBounce { 0%, 60%, 100% { transform: translateY(0); opacity: 0.5; } 30% { transform: translateY(-4px); opacity: 1; } }

  /* TASK-8 START — 8b: voice-recording-in-progress variant of the typing
     indicator (mic icon + italic label instead of the three bouncing
     dots), and 8a: a tag class so the bot "processing" placeholder can
     reuse the same dots visually while still being distinguishable in
     JS from the real admin-typing indicator (see the :not() selector
     in handlePollResult()). */
  .pcw-typing-dots--recording { gap: 7px; padding: 8px 13px; }
  .pcw-typing-dots--recording i { color: #ED7624; font-size: 12px; animation: pcwTypingBounce 1.2s infinite ease-in-out; }
  .pcw-typing-dots--recording span { width: auto; height: auto; border-radius: 0; background: none; animation: none; font-size: 11.5px; color: #a89c8a; font-style: italic; }
  .pcw-bot-processing { margin: 2px 0; }
  /* TASK-8 END */

  .pcw-no-answer-notice {
    text-align: center; font-size: 11.5px; color: #7a6d5f; background: #f9f7f4;
    padding: 8px 12px; border-radius: 8px; margin: 8px 0;
  }

  .pcw-history-divider {
    display: flex; align-items: center; gap: 10px; margin: 10px 0; cursor: pointer;
  }
  .pcw-history-divider hr { flex: 1; border: none; border-top: 1px solid rgba(16,33,75,0.12); }
  .pcw-history-divider span { font-size: 11px; color: #7a6d5f; white-space: nowrap; display: flex; align-items: center; gap: 5px; }
  .pcw-history-divider:hover span { color: #ED7624; }

  /* ── Composer ───────────────────────────────────────────────────── */
  .pcw-composer { flex-shrink: 0; border-top: 1px solid rgba(16,33,75,0.08); padding: 10px 12px; background: #fff; }
  .pcw-attach-row { display: flex; gap: 8px; margin-bottom: 8px; flex-wrap: wrap; }
  .pcw-attach-chip {
    display: flex; align-items: center; gap: 6px; background: #f4f1ea; border-radius: 8px;
    padding: 5px 9px; font-size: 11px; color: #4a4030;
  }
  .pcw-attach-chip .pcw-chip-status { color: #7a6d5f; }
  .pcw-attach-chip.uploading .pcw-chip-status { color: #ED7624; }
  .pcw-attach-chip.ready .pcw-chip-status { color: #10b981; }
  .pcw-attach-chip.failed .pcw-chip-status { color: #ef4444; }
  .pcw-attach-chip button { border: none; background: none; cursor: pointer; color: #a89c8a; font-size: 11px; }

  .pcw-composer-row { display: flex; align-items: flex-end; gap: 8px; }
  .pcw-composer-tools { display: flex; gap: 4px; }
  .pcw-tool-btn {
    width: 34px; height: 34px; border-radius: 50%; border: none; background: none; cursor: pointer;
    color: #10214b; font-size: 15px; display: flex; align-items: center; justify-content: center;
    transition: background 0.15s ease;
  }
  .pcw-tool-btn:hover { background: #f4f1ea; }
  .pcw-tool-btn.recording { background: #ef4444; color: #fff; }
  .pcw-composer-input {
    flex: 1; resize: none; border: 1.5px solid rgba(16,33,75,0.14); border-radius: 18px;
    padding: 8px 14px; font-family: 'Sora', system-ui, sans-serif; font-size: 13px; max-height: 90px;
  }
  .pcw-composer-input:focus { outline: none; border-color: #ED7624; }
  .pcw-send-btn {
    width: 36px; height: 36px; border-radius: 50%; border: none; background: #ED7624; color: #fff;
    cursor: pointer; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 14px;
  }
  .pcw-send-btn:disabled { background: #d7bd88; cursor: not-allowed; }

  .pcw-recording-bar {
    display: none; align-items: center; gap: 10px; padding: 8px 4px; font-size: 13px; color: #10214b;
  }
  .pcw-recording-bar.active { display: flex; }
  .pcw-recording-dot { width: 9px; height: 9px; border-radius: 50%; background: #ef4444; animation: pcwPulse 1s infinite; }
  @keyframes pcwPulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }
  .pcw-recording-time { font-variant-numeric: tabular-nums; font-weight: 600; }

  .pcw-voice-preview {
    display: none; align-items: center; gap: 10px; padding: 8px 10px; background: #f4f1ea;
    border-radius: 10px; margin-bottom: 8px;
  }
  .pcw-voice-preview.active { display: flex; }

  .pcw-msg-bubble.pcw-msg-failed { background: #fef2f2; border: 1px solid rgba(239,68,68,0.3); }
  .pcw-msg-fail-row { display: flex; align-items: center; gap: 6px; margin-top: 6px; font-size: 11px; color: #ef4444; }
  .pcw-retry-btn {
    border: none; background: #ef4444; color: #fff; border-radius: 6px; padding: 3px 8px;
    font-size: 10.5px; cursor: pointer; display: flex; align-items: center; gap: 4px; margin-left: auto;
  }
  .pcw-retry-btn:hover { background: #dc2626; }

  .pcw-msg-action-btn {
    border: none; background: none; color: #a89c8a; cursor: pointer; font-size: 10px;
    display: flex; align-items: center; gap: 3px; padding: 2px 4px;
  }
  .pcw-msg-action-btn:hover { color: #ED7624; }
  .pcw-edit-input {
    width: 100%; border: 1.5px solid #ED7624; border-radius: 8px; padding: 6px 9px;
    font-family: 'Sora', system-ui, sans-serif; font-size: 13px; resize: none;
  }

  .pcw-msg-bubble.pcw-msg-deleted-tombstone { background: #f4f1ea; color: #a89c8a; font-style: italic; font-size: 12px; }

  .pcw-download-chip {
    display: inline-flex; align-items: center; gap: 8px; text-decoration: none;
    background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25);
    border-radius: 8px; padding: 7px 10px; font-size: 12px; color: inherit;
    max-width: 220px; transition: background 0.15s ease;
  }
  .pcw-msg-row.user .pcw-download-chip { background: rgba(255,255,255,0.1); color: #fff; }
  .pcw-msg-row.admin .pcw-download-chip, .pcw-msg-row.bot .pcw-download-chip, .pcw-msg-row.system .pcw-download-chip { background: rgba(16,33,75,0.06); color: #10214b; }
  .pcw-download-chip:hover { background: rgba(237,118,36,0.15); }
  .pcw-download-name { flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 140px; }
  .pcw-download-arrow { color: #ED7624; flex-shrink: 0; }

  .pcw-attach-thumb {
    width: 36px; height: 36px; object-fit: cover; border-radius: 6px;
    border: 1px solid rgba(16,33,75,0.1); flex-shrink: 0;
  }

  .pcw-ticket-closed {
    background: #f0fdf4; border: 1px solid rgba(16,185,129,0.2); border-radius: 12px;
    padding: 12px 14px; margin: 10px 0; font-size: 13px;
  }
  .pcw-ticket-closed-notice { display: flex; align-items: center; margin-bottom: 10px; font-weight: 600; color: #10214b; }
  .pcw-rating-prompt { background: #fff; border: 1.5px solid rgba(16,33,75,0.1); border-radius: 10px; padding: 12px 14px; }
  .pcw-rating-prompt-title { font-size: 13px; font-weight: 600; color: #10214b; margin-bottom: 10px; }
  .pcw-rating-btn-row { display: flex; gap: 8px; }
  .pcw-rating-dismiss-btn {
    flex: 1; padding: 8px; border: 1.5px solid rgba(16,33,75,0.14); border-radius: 9px;
    background: none; cursor: pointer; font-family: 'Sora', system-ui, sans-serif; font-size: 13px; color: #7a6d5f;
  }
  .pcw-rating-form {
    background: #fff; border: 1.5px solid rgba(16,33,75,0.1); border-radius: 12px;
    padding: 16px 14px; margin-top: 10px; position: relative;
  }
  .pcw-rating-form-close {
    position: absolute; top: 10px; right: 10px; width: 26px; height: 26px; border-radius: 50%;
    border: none; background: #f4f1ea; cursor: pointer; color: #7a6d5f; font-size: 12px;
    display: flex; align-items: center; justify-content: center;
  }
  .pcw-rating-form-title { font-size: 13.5px; font-weight: 700; color: #10214b; margin-bottom: 14px; }
  .pcw-rating-faces { display: flex; gap: 4px; justify-content: space-between; margin-bottom: 12px; }
  .pcw-rating-face {
    flex: 1; border: 1.5px solid rgba(16,33,75,0.1); border-radius: 10px; padding: 8px 2px;
    background: #fdfcfa; cursor: pointer; text-align: center; font-family: 'Sora', system-ui, sans-serif;
    transition: all 0.15s ease;
  }
  .pcw-rating-face:hover, .pcw-rating-face.selected { border-color: #ED7624; background: #fff8f2; }
  .pcw-face-icon { display: block; font-size: 26px; margin-bottom: 4px; color: #10214b; }
  .pcw-rating-face.selected .pcw-face-icon { color: #ED7624; }
  .pcw-face-label { display: block; font-size: 9.5px; color: #7a6d5f; }
  .pcw-rating-textarea {
    width: 100%; border: 1.5px solid rgba(16,33,75,0.14); border-radius: 8px; padding: 9px 12px;
    font-family: 'Sora', system-ui, sans-serif; font-size: 13px; resize: none; height: 80px; box-sizing: border-box;
  }
  .pcw-rating-textarea:focus { outline: none; border-color: #ED7624; }

  /* ── Custom reason dropdown (follows CUSTOM_DROPDOWN_DESIGN_DOCUMENTATION) ── */
  .pcw-dropdown { position: relative; width: 100%; }
  .pcw-dropdown-trigger {
    width: 100%; display: flex; align-items: center; justify-content: space-between;
    padding: 10px 14px; border: 1.5px solid rgba(16,33,75,0.18); border-radius: 10px;
    background: #fff; cursor: pointer; font-family: 'Sora', system-ui, sans-serif;
    font-size: 13px; color: #a89c8a; text-align: left; transition: border-color 0.2s ease;
  }
  .pcw-dropdown-trigger.has-value { color: #10214b; }
  .pcw-dropdown-trigger:hover, .pcw-dropdown-trigger.open { border-color: #ED7624; }
  .pcw-dropdown-trigger.pcw-field-error { border-color: #ef4444; }
  .pcw-dropdown-chevron {
    font-size: 11px; color: #a89c8a; transition: transform 0.2s ease; flex-shrink: 0;
  }
  .pcw-dropdown-trigger.open .pcw-dropdown-chevron { transform: rotate(180deg); }
  .pcw-dropdown-list {
    position: absolute; top: calc(100% + 6px); left: 0; right: 0; z-index: 9999;
    background: #1e2d4e; border-radius: 12px;
    box-shadow: 0 8px 32px rgba(16,33,75,0.35); overflow: hidden;
    max-height: 0; opacity: 0; pointer-events: none;
    transition: max-height 0.25s ease, opacity 0.2s ease;
  }
  .pcw-dropdown-list.open { max-height: 320px; opacity: 1; pointer-events: auto; }
  .pcw-dropdown-item {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 18px; cursor: pointer; color: #e8ddd0; font-size: 14px;
    border-bottom: 1px solid rgba(255,255,255,0.07); transition: background 0.15s ease;
  }
  .pcw-dropdown-item:last-child { border-bottom: none; }
  .pcw-dropdown-item:hover { background: rgba(255,255,255,0.07); }
  .pcw-dropdown-item.selected { color: #fff; background: rgba(237,118,36,0.15); }
  .pcw-dropdown-item-label { flex: 1; }
  .pcw-dropdown-item-radio {
    width: 20px; height: 20px; border-radius: 50%; border: 2px solid rgba(255,255,255,0.35);
    flex-shrink: 0; transition: border-color 0.15s ease, background 0.15s ease;
    display: flex; align-items: center; justify-content: center;
  }
  .pcw-dropdown-item.selected .pcw-dropdown-item-radio {
    border-color: #ED7624; background: #ED7624;
    box-shadow: 0 0 0 3px rgba(237,118,36,0.25);
  }
  .pcw-dropdown-item.selected .pcw-dropdown-item-radio::after {
    content: ''; width: 7px; height: 7px; border-radius: 50%; background: #fff;
  }

  @media (max-width: 480px) {
    .pcw-panel {
      bottom: 14px; right: 14px; left: 14px; width: auto; max-width: calc(100vw - 28px);
      height: 78vh; height: 78dvh; max-height: calc(100vh - 100px);
      border-radius: 16px;
    }
    .pcw-launcher-btn { bottom: 16px; right: 16px; }
  }
  `;

  function injectStyle() {
    if (document.getElementById('pcw-style')) return;
    const styleEl = document.createElement('style');
    styleEl.id = 'pcw-style';
    styleEl.textContent = STYLE;
    document.head.appendChild(styleEl);
    // Ensure the same fonts/icon library the rest of the site uses are present
    if (!document.querySelector('link[href*="font-awesome"]')) {
      const fa = document.createElement('link');
      fa.rel = 'stylesheet';
      fa.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css';
      document.head.appendChild(fa);
    }
    if (!document.querySelector('link[href*="Cormorant"]')) {
      const fonts = document.createElement('link');
      fonts.rel = 'stylesheet';
      fonts.href = 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Sora:wght@300;400;500;600;700&display=swap';
      document.head.appendChild(fonts);
    }
  }

  // ============================================
  // DOM SCAFFOLD
  // ============================================
  let launcherBtn, badgeEl, activeDotEl, panelEl, headerTitleEl, headerSubEl, bodyEl, backdropEl;

  function buildScaffold() {
    launcherBtn = el('button', { class: 'pcw-launcher-btn', type: 'button', title: 'Chat with us' }, [
      el('i', { class: 'fas fa-comment-dots' }),
      (badgeEl = el('span', { class: 'pcw-badge' })),
      (activeDotEl = el('span', { class: 'pcw-active-dot' })),
    ]);

    headerTitleEl = el('h3', {}, 'Pet Center');
    headerSubEl = el('div', { class: 'pcw-sub' }, 'How can we help?');

    backdropEl = el('div', { class: 'pcw-backdrop' });
    backdropEl.addEventListener('click', (e) => { if (e.target === backdropEl) closePanel(); });

    const optionsBtn = el('button', { class: 'pcw-icon-btn', type: 'button', title: 'Options' }, el('i', { class: 'fas fa-ellipsis-vertical' }));
    optionsBtn.addEventListener('click', openOptionsSheet);

    panelEl = el('div', { class: 'pcw-panel' }, [
      el('div', { class: 'pcw-header' }, [
        el('div', {}, [headerTitleEl, headerSubEl]),
        el('div', { class: 'pcw-header-btns' }, [
          optionsBtn,
          el('button', { class: 'pcw-icon-btn pcw-header-close', type: 'button', title: 'Close' }),
        ]),
      ]),
      (bodyEl = el('div', { class: 'pcw-body' })),
      buildPoweredByFooter(),
    ]);

    const closeBtn = panelEl.querySelector('.pcw-header-close');
    closeBtn.innerHTML = '<i class="fas fa-times"></i>';
    closeBtn.addEventListener('click', closePanel);

    launcherBtn.addEventListener('click', togglePanel);

    document.body.appendChild(backdropEl);
    document.body.appendChild(launcherBtn);
    document.body.appendChild(panelEl);
    buildOptionsSheet();
  }

  function buildPoweredByFooter() {
    return el('div', { class: 'pcw-powered-by' }, 'Powered by Petcenterco LLC');
  }

  function togglePanel() {
    if (state.panelOpen) closePanel();
    else openPanel();
  }

  // ── Scroll lock (per Reusable-Bottom-Sheet-Component-Spec — do not
  //    substitute plain overflow:hidden, it doesn't lock scroll position and
  //    can visibly reflow behind the widget on iOS Safari / Chrome Android) ──
  let _pcwScrollLockY = 0;
  let _pcwScrollLockCount = 0;
  function pcwLockBodyScroll() {
    _pcwScrollLockCount++;
    if (_pcwScrollLockCount > 1) return;
    _pcwScrollLockY = window.scrollY || window.pageYOffset || 0;
    document.body.style.position = 'fixed';
    document.body.style.top = (-_pcwScrollLockY) + 'px';
    document.body.style.left = '0';
    document.body.style.right = '0';
    document.body.style.width = '100%';
  }
  function pcwUnlockBodyScroll() {
    _pcwScrollLockCount = Math.max(0, _pcwScrollLockCount - 1);
    if (_pcwScrollLockCount > 0) return;
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.left = '';
    document.body.style.right = '';
    document.body.style.width = '';
    window.scrollTo(0, _pcwScrollLockY);
  }

  function pcwEnableDragToClose(sheetEl, handleEl, onClose) {
    let startY = 0, currentY = 0, dragging = false, sheetHeight = 0;
    function pointerDown(e) {
      dragging = true;
      startY = (e.touches ? e.touches[0].clientY : e.clientY);
      currentY = startY;
      sheetHeight = sheetEl.offsetHeight || 1;
      sheetEl.style.transition = 'none';
    }
    function pointerMove(e) {
      if (!dragging) return;
      currentY = (e.touches ? e.touches[0].clientY : e.clientY);
      const delta = Math.max(0, currentY - startY);
      sheetEl.style.transform = 'translateY(' + delta + 'px)';
    }
    function pointerUp() {
      if (!dragging) return;
      dragging = false;
      sheetEl.style.transition = '';
      const delta = Math.max(0, currentY - startY);
      if (delta > sheetHeight * 0.25) {
        sheetEl.style.transform = '';
        onClose();
      } else {
        sheetEl.style.transform = '';
      }
    }
    [handleEl, sheetEl].forEach((elm) => {
      if (!elm) return;
      elm.addEventListener('touchstart', pointerDown, { passive: true });
      elm.addEventListener('mousedown', (e) => { if (elm === handleEl) pointerDown(e); });
    });
    document.addEventListener('touchmove', pointerMove, { passive: true });
    document.addEventListener('mousemove', pointerMove);
    document.addEventListener('touchend', pointerUp);
    document.addEventListener('mouseup', pointerUp);
  }

  // ── Options bottom sheet (3-dot menu) ─────────────────────────────────
  let optionsSheetEl, optionsBackdropEl;
  const SOUND_PREF_KEY = 'pc_chat_sound_enabled';

  // ── Sound engine — synthesized tones via Web Audio API, no external audio
  //    files to host or load. Respects the "Play the sounds" toggle. ──
  let _pcwAudioCtx = null;
  function pcwGetAudioCtx() {
    if (!_pcwAudioCtx) {
      const Ctx = window.AudioContext || window.webkitAudioContext;
      if (!Ctx) return null;
      _pcwAudioCtx = new Ctx();
    }
    // Browsers suspend AudioContext until a real user gesture resumes it —
    // every sound trigger in this widget happens as a direct result of a
    // click/tap/keypress, so resume() here is safe and near-instant.
    if (_pcwAudioCtx.state === 'suspended') _pcwAudioCtx.resume();
    return _pcwAudioCtx;
  }

  function pcwSoundsEnabled() {
    return localStorage.getItem(SOUND_PREF_KEY) !== '0'; // default ON
  }

  function pcwPlayTone(freq, durationMs, type = 'sine', volume = 0.08) {
    if (!pcwSoundsEnabled()) return;
    const ctx = pcwGetAudioCtx();
    if (!ctx) return;
    try {
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = type;
      osc.frequency.value = freq;
      gain.gain.value = volume;
      gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + durationMs / 1000);
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.start();
      osc.stop(ctx.currentTime + durationMs / 1000);
    } catch (e) { /* non-critical — never let a sound failure break a real action */ }
  }

  function pcwPlaySendSound() { pcwPlayTone(880, 90, 'sine', 0.07); }
  function pcwPlayReceiveSound() {
    // Two-note "ding-dong" so incoming messages are audibly distinct from
    // the single-note outgoing send sound.
    pcwPlayTone(660, 110, 'sine', 0.07);
    setTimeout(() => pcwPlayTone(880, 110, 'sine', 0.07), 90);
  }
  function pcwPlayDeleteSound() { pcwPlayTone(220, 140, 'triangle', 0.06); }
  function pcwPlayRecordStartSound() { pcwPlayTone(520, 70, 'square', 0.05); }
  function pcwPlayRecordStopSound() { pcwPlayTone(340, 70, 'square', 0.05); }


  function buildOptionsSheet() {
    optionsBackdropEl = el('div', { class: 'pcw-sheet-backdrop' });
    const handle = el('div', { class: 'pcw-sheet-handle' });
    const closeBtn = el('button', { class: 'pcw-sheet-close', type: 'button', title: 'Close' }, '\u00d7');

    const soundEnabled = localStorage.getItem(SOUND_PREF_KEY) !== '0'; // default ON
    const soundToggle = el('button', { type: 'button', class: 'pcw-toggle-switch' + (soundEnabled ? ' on' : '') });
    soundToggle.addEventListener('click', () => {
      soundToggle.classList.toggle('on');
      const nowOn = soundToggle.classList.contains('on');
      localStorage.setItem(SOUND_PREF_KEY, nowOn ? '1' : '0');
    });

    const soundRow = el('div', { class: 'pcw-option-row' }, [
      el('div', { class: 'pcw-option-icon' }, el('i', { class: 'fas fa-bell' })),
      el('div', { class: 'pcw-option-label' }, 'Play the sounds'),
      soundToggle,
    ]);

    const transcriptRow = el('div', { class: 'pcw-option-row pcw-option-clickable' }, [
      el('div', { class: 'pcw-option-icon' }, el('i', { class: 'fas fa-download' })),
      el('div', { class: 'pcw-option-label' }, 'Download transcript'),
    ]);
    transcriptRow.addEventListener('click', downloadTranscript);

    const accessibilityRow = el('div', { class: 'pcw-option-row pcw-option-clickable' }, [
      el('div', { class: 'pcw-option-icon' }, el('i', { class: 'fas fa-universal-access' })),
      el('div', { class: 'pcw-option-label' }, 'Accessibility'),
      el('i', { class: 'fas fa-chevron-down pcw-option-chevron' }),
    ]);
    const accessibilityPanel = el('div', { class: 'pcw-accessibility-panel' });

    // Each toggle applies a class to panelEl ONLY — never document.body or
    // any element outside the widget. These are genuinely scoped display
    // preferences for the chat UI itself, not page-wide accessibility
    // settings (which would require touching the host site's own markup,
    // something a third-party widget script has no business doing).
    const A11Y_OPTIONS = [
      { key: 'largeText',  icon: 'fa-text-height',  title: 'Larger text',         panelClass: 'pcw-a11y-large-text' },
      { key: 'contrast',   icon: 'fa-adjust',        title: 'High contrast',       panelClass: 'pcw-a11y-high-contrast' },
      { key: 'srHints',    icon: 'fa-volume-up',     title: 'Screen reader hints', panelClass: null }, // handled specially — sets aria-live, not a visual class
      { key: 'reduceMotion', icon: 'fa-hand-pointer', title: 'Reduce motion',      panelClass: 'pcw-a11y-reduce-motion' },
    ];
    const A11Y_PREF_KEY = 'pc_chat_a11y_prefs';

    function loadA11yPrefs() {
      try { return JSON.parse(localStorage.getItem(A11Y_PREF_KEY)) || {}; }
      catch (e) { return {}; }
    }
    function saveA11yPrefs(prefs) {
      localStorage.setItem(A11Y_PREF_KEY, JSON.stringify(prefs));
    }
    function applyA11yPref(opt, isOn) {
      if (opt.panelClass) {
        panelEl.classList.toggle(opt.panelClass, isOn);
      }
      if (opt.key === 'srHints') {
        if (bodyEl) bodyEl.setAttribute('aria-live', isOn ? 'polite' : 'off');
      }
    }

    const a11yPrefs = loadA11yPrefs();
    const a11yButtons = {};
    A11Y_OPTIONS.forEach(opt => {
      const isOn = !!a11yPrefs[opt.key];
      applyA11yPref(opt, isOn); // apply saved state immediately on build, not just on click
      const btn = el('button', { type: 'button', class: 'pcw-a11y-icon-btn' + (isOn ? ' active' : ''), title: opt.title, 'aria-pressed': isOn ? 'true' : 'false' }, el('i', { class: 'fas ' + opt.icon }));
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const nowOn = !btn.classList.contains('active');
        btn.classList.toggle('active', nowOn);
        btn.setAttribute('aria-pressed', nowOn ? 'true' : 'false');
        applyA11yPref(opt, nowOn);
        const prefs = loadA11yPrefs();
        prefs[opt.key] = nowOn;
        saveA11yPrefs(prefs);
        dlog('accessibility: ' + opt.key + ' = ' + nowOn);
      });
      a11yButtons[opt.key] = btn;
      accessibilityPanel.appendChild(btn);
    });

    accessibilityRow.addEventListener('click', () => {
      const isOpen = accessibilityPanel.classList.toggle('open');
      accessibilityRow.classList.toggle('open', isOpen);
    });

    /* TASK-4/5 START — self-contained protection section appended after
       accessibility so other workers can merge their own section unchanged. */
    const protectionRow = el('div', { class: 'pcw-option-row pcw-option-clickable' }, [
      el('div', { class: 'pcw-option-icon' }, el('i', { class: 'fas fa-shield-alt' })),
      el('div', { class: 'pcw-option-label' }, 'Chat protection'),
      el('i', { class: 'fas fa-chevron-right pcw-option-chevron' }),
    ]);
    protectionRow.addEventListener('click', manageChatProtection);
    /* TASK-4/5 END */

    const endSessionRow = el('div', { class: 'pcw-option-row pcw-option-clickable' }, [
      el('div', { class: 'pcw-option-icon', style: 'background:rgba(239,68,68,0.15);color:#ef4444;' }, el('i', { class: 'fas fa-rotate-left' })),
      el('div', { class: 'pcw-option-label' }, 'End chat session'),
    ]);
    endSessionRow.addEventListener('click', endChatSession);

    const body = el('div', { class: 'pcw-sheet-body' }, [
      soundRow, transcriptRow, accessibilityRow, accessibilityPanel, protectionRow, endSessionRow,
    ]);

    optionsSheetEl = el('div', { class: 'pcw-options-sheet', role: 'dialog', 'aria-modal': 'true', 'aria-label': 'Chat options' }, [
      handle,
      el('div', { class: 'pcw-sheet-header' }, [el('h3', {}, 'Options'), closeBtn]),
      body,
    ]);

    closeBtn.addEventListener('click', closeOptionsSheet);
    optionsBackdropEl.addEventListener('click', (e) => { if (e.target === optionsBackdropEl) closeOptionsSheet(); });
    pcwEnableDragToClose(optionsSheetEl, handle, closeOptionsSheet);

    panelEl.appendChild(optionsBackdropEl);
    panelEl.appendChild(optionsSheetEl);
  }

  // Applies protection changes through the authenticated server endpoints.
  async function manageChatProtection() {
    if (!state.chatId || !state.sessionToken) return;
    const choice = window.prompt('Enter PIN, password, lock, or forget-devices:');
    if (!choice) return;
    if (choice === 'lock') {
      const seconds = parseInt(window.prompt('Lock after close (seconds):', '300'), 10) || 300;
      const aggressive = window.confirm('Lock immediately when this panel closes?');
      await apiPost('chat-lock.php', { action:'set', chatId:state.chatId, delaySeconds:seconds, aggressive });
    } else if (choice === 'forget-devices') {
      await apiPost('chat-auth-gate.php', { action:'forgetDevices', chatId:state.chatId, email:state.email });
    } else if (choice === 'pin' || choice === 'password') {
      const secret = window.prompt(choice === 'pin' ? 'Set a 6-digit PIN:' : 'Set a password of at least 12 characters:');
      if (!secret) return;
      const json = await apiPost('chat-auth-gate.php', { action:'set', chatId:state.chatId, email:state.email, tier:choice, secret });
      if (json.success && json.sessionToken) { state.sessionToken = json.sessionToken; localStorage.setItem(CONFIG.storageKeyToken, json.sessionToken); state.authTier = choice; }
    }
  }

  function openOptionsSheet() {
    optionsSheetEl.classList.add('open');
    optionsBackdropEl.classList.add('show');
    pcwLockBodyScroll();
  }
  function closeOptionsSheet() {
    optionsSheetEl.classList.remove('open');
    optionsBackdropEl.classList.remove('show');
    pcwUnlockBodyScroll();
  }

  // ── Download transcript as .txt ─────────────────────────────────────
  async function downloadTranscript() {
    if (!state.chatId) {
      alert('Start a chat first to download a transcript.');
      return;
    }
    try {
      const json = await apiGet('chat-history.php', { chatId: state.chatId, mode: 'full' });
      if (!json.success) { alert('Could not load transcript.'); return; }
      const lines = [`Chat Transcript — ${state.chatId}`, '='.repeat(40), ''];
      (json.messages || []).forEach(m => {
        const who = m.from_type === 'user' ? (state.fullName || 'You')
                  : m.from_type === 'admin' ? (m.admin_name || 'Admin')
                  : 'System';
        const time = m.created_at || '';
        let content = m.body || '';
        if (m.msg_type === 'image') content = '[Image attachment]';
        else if (m.msg_type === 'video') content = '[Video attachment]';
        else if (m.msg_type === 'audio') content = '[Voice message]';
        lines.push(`[${time}] ${who}: ${content}`);
      });
      const blob = new Blob([lines.join('\n')], { type: 'text/plain' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = state.chatId + '.txt';
      document.body.appendChild(a);
      a.click();
      a.remove();
      URL.revokeObjectURL(url);
      dlog('transcript downloaded: ' + state.chatId);
    } catch (err) {
      alert('Failed to download transcript.');
      dlog('transcript download error: ' + err.message, 'error');
    }
  }

  // ── End chat session: clears the customer's local session pointer only.
  //    The actual thread and its full history remain on the server and are
  //    still fully visible to admins — this just lets the customer
  //    deliberately "start over" from their own side without needing to
  //    clear their browser storage manually. ──
  function endChatSession() {
    if (!confirm('End this chat session? Your conversation history stays safe with us — you can always find it again with your email or chat ID.')) return;

    localStorage.removeItem(CONFIG.storageKeyChatId);
    localStorage.removeItem(CONFIG.storageKeyLastMsg);
    localStorage.removeItem('pc_chat_name');
    localStorage.removeItem('pc_chat_email');
    /* TASK-1 START */
    localStorage.removeItem(CONFIG.storageKeyToken);
    state.sessionToken = null;
    /* TASK-1 END */
    localStorage.removeItem(CONFIG.storageKeyDraftUuid);
    if (state.chatId) localStorage.removeItem('pc_ticket_closed_' + state.chatId);

    stopPolling();
    state.mode = null;
    state.chatId = null;
    state.fullName = null;
    state.email = null;

    closeOptionsSheet();
    renderLauncher();
    dlog('chat session ended by customer');
  }

  function openPanel() {
    state.panelOpen = true;
    panelEl.classList.add('open');
    if (backdropEl) backdropEl.classList.add('show');
    pcwLockBodyScroll();
    dlog('panel opened');

    state.unreadCount = 0;
    showUnreadBadge(0);
    if (state.chatId) saveUnreadCount(0);

    if (state.mode === 'live' && state.chatId) {
      // Session was kept alive by background polling while the panel was
      // closed — rebuild the message view from scratch so anything that
      // arrived while closed (and was never appended to messagesEl) is
      // actually shown, rather than silently missing.
      apiPost('chat-lock.php', { action: 'status', chatId: state.chatId }).then(lockCheck => {
        if (lockCheck.success) rebuildLiveView();
        else { state.sessionToken = null; localStorage.removeItem(CONFIG.storageKeyToken); renderAuthGate(state.chatId, state.fullName, state.email, state.status, state.authTier); }
      }).catch(() => renderAuthGate(state.chatId, state.fullName, state.email, state.status, state.authTier));
      return;
    }

    // Resume an existing session if one exists (Tier 1 item 22), otherwise show the launcher
    const storedChatId = localStorage.getItem(CONFIG.storageKeyChatId);
    if (storedChatId && state.mode !== 'live') {
      resumeStoredChat(storedChatId);
    } else if (!state.mode) {
      renderLauncher();
    }
  }

  function rebuildLiveView() {
    headerTitleEl.textContent = state.fullName || 'Live Chat';
    headerSubEl.textContent = state.chatId;

    bodyEl.innerHTML = '';
    bodyEl.classList.add('pcw-chat-body');

    messagesEl = el('div', { class: 'pcw-messages' });
    messagesEl.appendChild(el('div', { class: 'pcw-chat-id-pill' }, state.chatId));
    bodyEl.appendChild(messagesEl);
    bodyEl.appendChild(buildComposer());

    // Fetch the full message history fresh (from 0) so nothing that arrived
    // while the panel was closed is missed — lastMessageId/lastEventId are
    // reset for this one fetch only, then poll resumes incrementally from
    // whatever the response returns.
    apiGet('chat-poll.php', { chatId: state.chatId, lastMessageId: 0, lastEventId: 0 }).then(json => {
      if (json.success) {
        state.lastMessageId = 0;
        state.lastEventId = 0;
        state.pollHasRunOnce = false;
        handlePollResult(json);
      }
    });
  }

  function closePanel() {
    /* TASK-4 START — the browser timer is advisory; the server timestamps the
       close and re-checks it on every later token verification. */
    if (state.mode === 'live' && state.chatId && state.sessionToken) {
      apiPost('chat-lock.php', { action: 'closed', chatId: state.chatId }).catch(() => {});
    }
    /* TASK-4 END */
    state.panelOpen = false;
    panelEl.classList.remove('open');
    if (backdropEl) backdropEl.classList.remove('show');
    pcwUnlockBodyScroll();
    if (state.mode !== 'live') {
      // No active chat session to keep tabs on — fully stop.
      stopPolling();
    }
    // If a live session IS active, polling continues in the background at
    // the slower blurred interval (scheduleNextPoll handles this), so the
    // unread badge can still pick up new messages while collapsed.
    dlog('panel closed');
  }

  function updateLauncherButtonState() {
    const hasStoredChat = !!localStorage.getItem(CONFIG.storageKeyChatId);
    activeDotEl.classList.toggle('show', hasStoredChat);
  }

  function showUnreadBadge(count) {
    if (count > 0) {
      badgeEl.textContent = count > 9 ? '9+' : String(count);
      badgeEl.classList.add('show');
    } else {
      badgeEl.classList.remove('show');
    }
  }

  // ============================================
  // LAUNCHER PANEL (Section 1 — confirmed exact flow)
  // ============================================
  function renderLauncher() {
    state.mode = 'launcher';
    headerTitleEl.textContent = 'Pet Center';
    headerSubEl.textContent = 'How can we help?';

    bodyEl.innerHTML = '';
    bodyEl.appendChild(el('div', { class: 'pcw-launcher-intro' },
      'Choose how you\'d like to reach us. ' +
      '"Chat by Email" sends a one-time message and we\'ll reply by email. ' +
      '"Chat Live" opens a real conversation saved under a chat ID, so you can pick up right where you left off any time.'
    ));

    const choices = el('div', { class: 'pcw-launcher-choices' });

    const emailChoice = el('button', { class: 'pcw-choice-btn', type: 'button' }, [
      el('div', { class: 'pcw-choice-icon' }, el('i', { class: 'fas fa-envelope' })),
      el('div', {}, [
        el('div', { class: 'pcw-choice-title' }, 'Chat by Email'),
        el('div', { class: 'pcw-choice-desc' }, 'Send a message, get a reply by email'),
      ]),
    ]);
    emailChoice.addEventListener('click', renderEmailForm);

    const liveChoice = el('button', { class: 'pcw-choice-btn', type: 'button' }, [
      el('div', { class: 'pcw-choice-icon' }, el('i', { class: 'fas fa-comments' })),
      el('div', {}, [
        el('div', { class: 'pcw-choice-title' }, 'Chat Live'),
        el('div', { class: 'pcw-choice-desc' }, 'Real-time conversation, saved to your chat ID'),
      ]),
    ]);
    liveChoice.addEventListener('click', renderPreChatGate);

    choices.appendChild(emailChoice);
    choices.appendChild(liveChoice);
    bodyEl.appendChild(choices);

    dlog('launcher rendered');
  }

  function renderBackToLauncher() {
    const back = el('button', { class: 'pcw-back-link', type: 'button' }, [
      el('i', { class: 'fas fa-arrow-left' }), ' Back',
    ]);
    back.addEventListener('click', renderLauncher);
    return back;
  }

  // ============================================
  // "CHAT BY EMAIL" — AJAX-loads the existing static form, untouched backend
  // ============================================
  async function renderEmailForm() {
    state.mode = 'email';
    headerTitleEl.textContent = 'Send a Message';
    headerSubEl.textContent = "We'll reply by email";

    bodyEl.innerHTML = '';
    bodyEl.appendChild(renderBackToLauncher());

    // Use an iframe — the contact-us page has its own CSS, scripts, and
    // backend paths (/contact-us/contact-handler.php etc.) that only work
    // correctly when the page loads as a full document, not when its HTML
    // is injected via innerHTML (which strips scripts and loses CSS context).
    // This is identical to the proven original contact popup implementation.
    //
    // The wrapper scrolls (overflow-y:auto) and the iframe fills 100% of
    // whatever height the wrapper actually has — this is what lets the
    // contact form's own (longer) content keep scrolling all the way to
    // its end within the widget's own height, instead of being cut off at
    // a fixed pixel height that only happened to fit the live-chat view.
    const iframeWrap = el('div', { style: 'flex:1;overflow-y:auto;border-radius:0 0 16px 16px;-webkit-overflow-scrolling:touch;' });
    const iframe = el('iframe', {
      src: '/contact-us/',
      title: 'Contact Us',
      style: 'width:100%;border:none;display:block;height:100%;min-height:100%;',
    });
    iframeWrap.appendChild(iframe);
    bodyEl.appendChild(iframeWrap);
    bodyEl.style.padding = '0';
    dlog('contact-us loaded in iframe');
  }

  // ============================================
  // REASON LIST — mirrors CHAT_REASONS in chat-config.php exactly
  // ============================================
  const REASONS = [
    { value: 'adoption', label: 'Adoption inquiry' },
    { value: 'shipping', label: 'Shipping question' },
    { value: 'health', label: 'Health / medical' },
    { value: 'general', label: 'General / other' },
  ];

  // ============================================
  // PRE-CHAT GATE (Tier 1 items 1 & 2 — single form, no branching step)
  // ============================================
  function renderPreChatGate() {
    state.mode = 'pre-chat';
    headerTitleEl.textContent = 'Chat Live';
    headerSubEl.textContent = 'A few details to get started';

    bodyEl.innerHTML = '';
    bodyEl.appendChild(renderBackToLauncher());

    const form = el('form', {});
    const nameField = el('div', { class: 'pcw-field' }, [
      el('label', {}, 'Your name'),
      el('input', { type: 'text', name: 'fullName', required: 'required', maxlength: '255' }),
    ]);
    const emailField = el('div', { class: 'pcw-field' }, [
      el('label', {}, 'Email'),
      el('input', { type: 'email', name: 'email', required: 'required' }),
    ]);
    /* TASK-3 START — optional protection is collected directly after email so
       a new chat can be born protected without a second unguarded session. */
    const authTierInput = el('select', { name: 'authTier' }, [
      el('option', { value: '' }, 'No chat lock'),
      el('option', { value: 'pin' }, 'Protect with a 6-digit PIN'),
      el('option', { value: 'password' }, 'Protect with a password'),
    ]);
    const authSecretInput = el('input', { type: 'password', name: 'authSecret', minlength: '6', maxlength: '1024', placeholder: 'Set a PIN or password (optional)' });
    const protectionField = el('div', { class: 'pcw-field' }, [el('label', {}, 'Protect this chat (optional)'), authTierInput, authSecretInput]);
    /* TASK-3 END */
    // TASK-9 START
    // TASK-9b: phone number field, optional, added to the pre-chat gate.
    // Follows the exact same .pcw-field / label+input pattern as
    // nameField/emailField above for visual and markup consistency —
    // no new styling introduced, just a third field of the same shape.
    const phoneField = el('div', { class: 'pcw-field' }, [
      el('label', {}, 'Phone number (optional)'),
      el('input', { type: 'tel', name: 'phone', maxlength: '30', placeholder: 'e.g. (555) 123-4567' }),
    ]);
    // TASK-9 END
    // ── Custom dropdown for reason — follows the CUSTOM_DROPDOWN_DESIGN_DOCUMENTATION
    // pattern: hidden input holds the value, custom div-based UI renders on top.
    // The native <select> in screenshot 2 uses a dark full-screen Android sheet —
    // our custom version gives the same radio-circle look but stays within the widget.
    let selectedReason = '';
    const reasonInput = el('input', { type: 'hidden', name: 'reason' });
    const dropdownWrap = el('div', { class: 'pcw-dropdown' });
    const dropdownTrigger = el('button', { type: 'button', class: 'pcw-dropdown-trigger' }, [
      el('span', { class: 'pcw-dropdown-label' }, 'Select a reason...'),
      el('i', { class: 'fas fa-chevron-down pcw-dropdown-chevron' }),
    ]);
    const dropdownList = el('div', { class: 'pcw-dropdown-list' });

    REASONS.forEach(r => {
      const item = el('div', { class: 'pcw-dropdown-item', 'data-value': r.value }, [
        el('span', { class: 'pcw-dropdown-item-label' }, r.label),
        el('span', { class: 'pcw-dropdown-item-radio' }),
      ]);
      item.addEventListener('click', () => {
        selectedReason = r.value;
        reasonInput.value = r.value;
        dropdownTrigger.querySelector('.pcw-dropdown-label').textContent = r.label;
        dropdownTrigger.classList.add('has-value');
        dropdownList.querySelectorAll('.pcw-dropdown-item').forEach(i => {
          i.classList.toggle('selected', i.dataset.value === r.value);
        });
        closeDropdown();
      });
      dropdownList.appendChild(item);
    });

    let dropdownOpen = false;
    function openDropdown() {
      dropdownOpen = true;
      dropdownList.classList.add('open');
      dropdownTrigger.classList.add('open');
    }
    function closeDropdown() {
      dropdownOpen = false;
      dropdownList.classList.remove('open');
      dropdownTrigger.classList.remove('open');
    }
    dropdownTrigger.addEventListener('click', (e) => {
      e.stopPropagation();
      dropdownOpen ? closeDropdown() : openDropdown();
    });
    // Close on outside click
    document.addEventListener('click', closeDropdown);

    dropdownWrap.appendChild(reasonInput);
    dropdownWrap.appendChild(dropdownTrigger);
    dropdownWrap.appendChild(dropdownList);

    const reasonField = el('div', { class: 'pcw-field' }, [
      el('label', {}, 'Reason for contacting us'),
      dropdownWrap,
    ]);
    const chatIdField = el('div', { class: 'pcw-field' }, [
      el('label', {}, 'Already have a chat ID? (optional)'),
      el('input', { type: 'text', name: 'chatId', placeholder: 'e.g. PC-7F3A1B2C' }),
      el('div', { class: 'pcw-field-hint' }, "Leave blank if you're not sure — we'll also check by your email."),
    ]);

    const errorMsg = el('div', { class: 'pcw-error-msg' });
    const submitBtn = el('button', { type: 'submit', class: 'pcw-primary-btn' }, 'Start Chat');

    form.appendChild(nameField);
    form.appendChild(emailField);
    form.appendChild(protectionField);
    // TASK-9 START
    // TASK-9b: mount the new phone field directly after email, before the
    // reason dropdown — grouping it with the other plain text-style fields.
    form.appendChild(phoneField);
    // TASK-9 END
    form.appendChild(reasonField);
    form.appendChild(chatIdField);
    form.appendChild(errorMsg);
    form.appendChild(submitBtn);

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      errorMsg.classList.remove('show');

      // Validate custom dropdown — native required doesn't apply to hidden input
      if (!selectedReason) {
        errorMsg.textContent = 'Please select a reason for contacting us.';
        errorMsg.classList.add('show');
        dropdownTrigger.classList.add('pcw-field-error');
        return;
      }
      dropdownTrigger.classList.remove('pcw-field-error');

      submitBtn.disabled = true;
      submitBtn.textContent = 'Starting...';

      const fd = new FormData(form);
      const payload = {
        fullName: fd.get('fullName').trim(),
        email: fd.get('email').trim(),
        /* TASK-9 START */
        // TASK-9b: optional phone field — sent along with the rest of
        // the pre-chat payload; empty string if the customer left it blank.
        phone: fd.get('phone').trim(),
        /* TASK-9 END */
        reason: selectedReason,  // use the tracked value directly, not fd.get (more reliable)
        chatId: fd.get('chatId').trim(),
        authTier: fd.get('authTier'),
        authSecret: fd.get('authSecret'),
        deviceToken: deviceToken(),
      };

      // Clean up the outside-click listener once form is submitted
      document.removeEventListener('click', closeDropdown);

      dlog('submitting pre-chat gate: ' + payload.email);

      try {
        const json = await apiPost('chat-start.php', payload);
        if (json.success) {
          // TASK-9 START
          // TASK-9c: true here — this is the fresh pre-chat submission
          // path, the one moment the entrance animation should play.
          /* TASK-1 START — pass through the sessionToken chat-start.php's
             response now includes on every success path. */
          if (json.authRequired) {
            renderAuthGate(json.chatId, json.fullName, json.email, json.status, json.authTier);
          } else {
            state.authTier = json.authTier || 'none';
            openLiveThread(json.chatId, json.fullName, json.email, json.status, undefined, true, json.sessionToken);
          }
          /* TASK-1 END */
          // TASK-9 END
        } else {
          errorMsg.textContent = json.error || 'Something went wrong. Please try again.';
          errorMsg.classList.add('show');
          submitBtn.disabled = false;
          submitBtn.textContent = 'Start Chat';
        }
      } catch (err) {
        errorMsg.textContent = 'Network error. Please try again.';
        errorMsg.classList.add('show');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Start Chat';
        dlog('pre-chat gate network error: ' + err.message, 'error');
      }
    });

    bodyEl.appendChild(form);
  }

  // ============================================
  // RESUME A STORED SESSION (Tier 1 item 22 — persistence across navigation)
  // ============================================
  // Renders the shared gate after chat-start has identified a protected thread.
  function renderAuthGate(chatId, fullName, email, status, tier) {
    state.mode = 'auth-gate'; state.chatId = chatId; state.fullName = fullName; state.email = email; state.status = status; state.authTier = tier;
    headerTitleEl.textContent = 'Unlock Chat'; headerSubEl.textContent = chatId; bodyEl.innerHTML = ''; bodyEl.appendChild(renderBackToLauncher());
    bodyEl.appendChild(AuthGate({ tier, principalId: chatId, email, onVerifySuccess: (json) => { state.sessionToken = json.sessionToken; localStorage.setItem(CONFIG.storageKeyToken, json.sessionToken); openLiveThread(chatId, fullName, email, status, false, false, json.sessionToken); } }));
  }

  function resumeStoredChat(storedChatId) {
    dlog('resuming stored chat: ' + storedChatId);
    const storedName = localStorage.getItem('pc_chat_name') || '';
    const storedEmail = localStorage.getItem('pc_chat_email') || '';
    apiPost('chat-start.php', { fullName: storedName || 'Customer', email: storedEmail, reason: 'general', chatId: storedChatId, deviceToken: deviceToken() }).then(json => {
      if (!json.success) { renderLauncher(); return; }
      if (json.authRequired) renderAuthGate(json.chatId, json.fullName, json.email, json.status, json.authTier);
      else { state.sessionToken = json.sessionToken; openLiveThread(json.chatId, json.fullName, json.email, json.status, false, false, json.sessionToken); }
    }).catch(() => renderLauncher());
  }

  // ============================================
  // LIVE CHAT VIEW
  // ============================================
  let messagesEl, composerInputEl, sendBtnEl, attachRowEl, recordingBarEl, recordingTimeEl, voicePreviewEl, micBtnEl;
  let mediaRecorder = null, recordedChunks = [], recordingStartTime = null, recordingTimer = null;
  let recordedBlob = null, recordedMime = '';

  // TASK-9 START
  // TASK-9c: fires the one-time "lovely amoeba" entrance animation on
  // top of the panel. Purely decorative and non-blocking — appended
  // after the live thread's real content is already in the DOM, and
  // self-removes on animationend (with a timeout fallback in case that
  // event doesn't fire) so it never lingers or intercepts input via its
  // pointer-events:none overlay in the meantime.
  function triggerBlobIntro() {
    const overlay = el('div', { class: 'pcw-blob-intro' }, [
      el('div', { class: 'pcw-blob pcw-blob-1' }),
      el('div', { class: 'pcw-blob pcw-blob-2' }),
      el('div', { class: 'pcw-blob pcw-blob-3' }),
    ]);
    const cleanup = () => { if (overlay.parentNode) overlay.parentNode.removeChild(overlay); };
    overlay.addEventListener('animationend', cleanup);
    panelEl.appendChild(overlay);
    setTimeout(cleanup, 1400); // fallback in case animationend is missed
  }
  // TASK-9 END

  // TASK-9 START
  // TASK-9c: added the trailing `playIntroAnimation` parameter to this
  // pre-existing function (default undefined/falsy, so every other
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
    state.status = status;
    state.lastMessageId = skipRestore ? (parseInt(localStorage.getItem(CONFIG.storageKeyLastMsg), 10) || 0) : 0;
    state.lastEventId = 0;
    state.draftKey = chatId;
    state.pollHasRunOnce = false; // suppress receive sound spam on initial history load

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
    headerSubEl.textContent = chatId;

    // Add a back/options button to the right of the header title
    // so users can exit the persistent session and return to the launcher
    // (to switch to Chat by Email, start a fresh chat, or use a different email/chatId)
    const existingBackBtn = panelEl.querySelector('.pcw-header-back');
    if (existingBackBtn) existingBackBtn.remove();
    const backBtn = el('button', { class: 'pcw-icon-btn pcw-header-back', type: 'button', title: 'Back to options / Start different chat' });
    backBtn.innerHTML = '<i class="fas fa-arrow-left"></i>';
    backBtn.addEventListener('click', () => {
      // Clear stored session so the launcher shows fresh choices
      if (confirm('Go back to the start? Your current chat will still be saved under your chat ID — you can return to it anytime.')) {
        stopPolling();
        state.mode = null;
        renderLauncher();
      }
    });
    // Insert back button before the close button in the header
    const headerBtns = panelEl.querySelector('.pcw-header-btns');
    if (headerBtns) headerBtns.insertBefore(backBtn, headerBtns.firstChild);

    bodyEl.innerHTML = '';
    bodyEl.classList.add('pcw-chat-body');

    messagesEl = el('div', { class: 'pcw-messages' });
    const idPill = el('div', { class: 'pcw-chat-id-pill' }, chatId);
    messagesEl.appendChild(idPill);
    bodyEl.appendChild(messagesEl);

    bodyEl.appendChild(buildComposer());

    dlog('live thread opened: ' + chatId);

    // No need to manually pre-insert a ticket-closed notice here — lastEventId
    // is 0 on every fresh open, so the first poll naturally replays the real
    // ticket_closed event (along with all messages) in correct chronological
    // order. Pre-inserting it here was the bug: it landed before history
    // loaded, then got pushed to the top once messages appended after it.

    // TASK-9 START
    // TASK-9c: fire the entrance animation only for a genuinely fresh
    // pre-chat submission (see the call site in the pre-chat gate's
    // submit handler below) — never on resumeStoredChat(), so returning
    // to an already-open session on page reload doesn't replay it.
    // Placed after the composer/messages are already appended above, so
    // it never delays the panel becoming usable.
    if (playIntroAnimation) triggerBlobIntro();
    // TASK-9 END

    loadDraft();
    loadUnreadCount();
    startPolling();
  }

  function avatarNode(name, photoUrl, isUser) {
    if (photoUrl) {
      // photo_url is stored as a relative path (e.g. 'chat-admin-photos/file.jpg')
      // Widget loads from public root, so must prefix with /admin/
      const src = photoUrl.startsWith('/') || photoUrl.startsWith('http') ? photoUrl : '/admin/' + photoUrl;
      return el('img', { class: 'pcw-msg-avatar', src: src, alt: name || '' });
    }
    const node = el('div', { class: 'pcw-msg-avatar' }, initials(name));
    return node;
  }

  function renderJoinedBanner(adminName, adminPhoto, adminPosition, blueCheck) {
    const banner = el('div', { class: 'pcw-joined-banner' });
    banner.appendChild(el('div', { class: 'pcw-jb-line1' }, `${adminName} joined the chat`));

    const line2 = el('div', { class: 'pcw-jb-line2' });
    const userPerson = el('div', { class: 'pcw-jb-person' }, [
      avatarNode(state.fullName, null, true),
      el('span', {}, state.fullName || 'You'),
    ]);
    const linkIcon = el('i', { class: 'fas fa-link pcw-jb-link-icon' });
    const adminPerson = el('div', { class: 'pcw-jb-person' }, [
      avatarNode(adminName, adminPhoto, false),
      el('span', {}, [
        adminName,
        blueCheck ? el('i', { class: 'fas fa-circle-check pcw-jb-bluecheck' }) : null,
      ]),
      el('span', { class: 'pcw-jb-position' }, `· ${adminPosition}`),
    ]);
    line2.appendChild(userPerson);
    line2.appendChild(linkIcon);
    line2.appendChild(adminPerson);
    banner.appendChild(line2);

    return banner;
  }

  // TASK-8 START (8a — bot response pacing)
  // ── Task 8a: believable bot-reply pacing. The bot's message is already
  //    inserted into chat_messages exactly as before (chat-send.php /
  //    chat-start.php are untouched) — this only changes how an
  //    already-arrived bot message is revealed to the customer: a short
  //    "processing" pause (shown as the same typing-dots already used for
  //    a human admin) followed by a typewriter-style reveal of the text.
  //    Purely cosmetic, and never delays anything else in the merge loop
  //    since the placeholder is appended synchronously to hold the
  //    message's correct chronological position immediately. ──
  function appendBotMessageWithDelay(msg) {
    const placeholder = el('div', { class: 'pcw-typing-dots pcw-bot-processing' }, [el('span'), el('span'), el('span')]);
    const wasBottom = isScrolledToBottom();
    messagesEl.appendChild(placeholder);
    if (wasBottom) messagesEl.scrollTop = messagesEl.scrollHeight;

    // Understated pause, randomized slightly so it doesn't feel mechanical —
    // long enough to read as "thinking", short enough not to feel like a stall.
    const delay = 700 + Math.random() * 700;
    setTimeout(() => {
      // The panel may have been closed and rebuilt (rebuildLiveView) while
      // we were waiting, tearing this placeholder out of the live DOM —
      // if so, that rebuild will already render the message normally, so
      // bail out here rather than risk inserting a duplicate.
      if (!placeholder.isConnected) return;
      // Re-check scroll position live rather than reusing the `wasBottom`
      // captured before the pause: the customer may have deliberately
      // scrolled up to reread something during the ~1s delay, and forcing
      // them back down at reveal time would fight that on-purpose action.
      const stillBottom = isScrolledToBottom();
      const row = renderMessage(msg);
      placeholder.replaceWith(row);
      if (stillBottom) messagesEl.scrollTop = messagesEl.scrollHeight;
      // The notification sound fires HERE, at the moment the message
      // actually becomes visible — not back when it silently arrived
      // (see the caller in handlePollResult, which deliberately skips
      // its own immediate pcwPlayReceiveSound() call for a bot message
      // for this exact reason). Playing the "ding" during the silent
      // processing pause, seconds before anything is visible, would
      // undercut the whole point of the pause.
      pcwPlayReceiveSound();
      if (msg.msg_type === 'text') {
        const textEl = row.querySelector('.pcw-msg-bubble > span');
        typewriterReveal(textEl, msg.body || '', () => {
          if (stillBottom) messagesEl.scrollTop = messagesEl.scrollHeight;
        });
      }
    }, delay);
  }

  // ── Reveals text progressively instead of popping in all at once.
  //    Speed scales with message length so even a long reply finishes in
  //    well under a second — modern and understated, not a slow gimmicky
  //    letter-by-letter crawl. ──
  function typewriterReveal(textEl, fullText, onTick) {
    if (!textEl || !fullText) return;
    textEl.textContent = '';
    let shown = 0;
    const charsPerTick = Math.max(1, Math.ceil(fullText.length / 40));
    const tick = () => {
      shown = Math.min(fullText.length, shown + charsPerTick);
      textEl.textContent = fullText.slice(0, shown);
      if (onTick) onTick();
      if (shown < fullText.length) setTimeout(tick, 16);
    };
    tick();
  }
  // TASK-8 END (8a — bot response pacing)

  function renderMessage(msg) {
    // Defensive normalization: if from_type is somehow not one of the four
    // known values (e.g. 'bot' was rejected by a database that hasn't had
    // chat-migrate.php run yet, silently truncating to an empty string
    // under certain SQL modes), treat it as 'admin' rather than falling
    // through with no matching CSS class and rendering unstyled.
    const knownTypes = ['user', 'admin', 'system', 'bot'];
    if (!knownTypes.includes(msg.from_type)) {
      dlog('unexpected from_type "' + msg.from_type + '" on message ' + msg.id + ' — check that chat-migrate.php has been run (adds "bot" to the from_type ENUM)', 'error');
      msg.from_type = 'admin';
    }

    const row = el('div', { class: 'pcw-msg-row ' + msg.from_type });

    if (msg.from_type === 'system') {
      row.appendChild(el('div', { class: 'pcw-msg-bubble' }, msg.body));
      return row;
    }

    const isUser = msg.from_type === 'user';
    const avatar = isUser
      ? avatarNode(state.fullName, null, true)
      : avatarNode(msg.admin_name, msg.admin_photo, false);
    row.appendChild(avatar);

    const col = el('div', {});
    if (!isUser && msg.admin_name) {
      // Admin sender name with blue checkmark inline — always visible on every admin bubble
      const senderRow = el('div', { class: 'pcw-msg-sender' });
      senderRow.appendChild(document.createTextNode(msg.admin_name));
      if (msg.admin_blue_check) {
        senderRow.appendChild(el('i', { class: 'fas fa-circle-check pcw-jb-bluecheck', style: 'margin-left:4px;font-size:11px;' }));
      }
      col.appendChild(senderRow);
    }

    const bubble = el('div', { class: 'pcw-msg-bubble' });
    const bodyTextEl = el('span', {});
    if (msg.msg_type === 'text') {
      bodyTextEl.textContent = msg.body;
      bubble.appendChild(bodyTextEl);
    } else if (msg.msg_type === 'image') {
      const p = msg.media_path ? '/admin/' + msg.media_path : '';
      const img = el('img', { src: p, style: 'max-width:220px;border-radius:8px;display:block;cursor:pointer;' });
      img.addEventListener('click', () => window.open(p, '_blank'));
      bubble.appendChild(img);
    } else if (msg.msg_type === 'video') {
      const p = msg.media_path ? '/admin/' + msg.media_path : '';
      const vid = el('video', { src: p, controls: 'controls', style: 'max-width:220px;border-radius:8px;display:block;' });
      bubble.appendChild(vid);
    } else if (msg.msg_type === 'audio') {
      bubble.appendChild(buildAudioBar('/admin/' + msg.media_path));
    }
    col.appendChild(bubble);

    // ── Meta row: timestamp + persistent "Edited" tag (clickable to reveal
    //    the original pre-edit text). This row fades out and is replaced by
    //    the edit/delete action buttons when the bubble is tapped, then
    //    fades back in once editing is cancelled or saved.
    const metaRow = el('div', { class: 'pcw-msg-meta-row' });
    const timeEl = el('span', { class: 'pcw-msg-time' }, formatTime(msg.created_at));
    metaRow.appendChild(timeEl);

    let editedBtn = null;
    let originalRevealEl = null;
    if (msg.msg_type === 'text' && msg.edited_at) {
      editedBtn = el('button', { type: 'button', class: 'pcw-edited-tag-btn' }, 'Edited');
      metaRow.appendChild(editedBtn);

      // Clicking "Edited" toggles a fade-in reveal of the original text,
      // stored server-side in original_body from the very first edit.
      if (msg.original_body) {
        originalRevealEl = el('div', { class: 'pcw-original-reveal' }, [
          el('div', { class: 'pcw-original-reveal-label' }, 'Original message'),
          el('div', { class: 'pcw-original-reveal-text' }, msg.original_body),
        ]);
        editedBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          const isShown = originalRevealEl.classList.toggle('show');
          editedBtn.classList.toggle('active', isShown);
        });
      }
    }
    col.appendChild(metaRow);
    if (originalRevealEl) col.appendChild(originalRevealEl);

    // ── Edit/delete: tap the bubble to fade out the meta row and fade in
    //    edit/delete buttons in its place. Works identically on mobile and
    //    desktop (no time window — can edit/delete anytime, per decision).
    if (isUser && msg.msg_type === 'text' && !msg.deleted_at) {
      const actions = el('div', { class: 'pcw-msg-actions' });
      const editBtn = el('button', { type: 'button', class: 'pcw-msg-action-btn' }, [el('i', { class: 'fas fa-pen' }), ' Edit']);
      const deleteBtn = el('button', { type: 'button', class: 'pcw-msg-action-btn' }, [el('i', { class: 'fas fa-trash' }), ' Delete']);
      editBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleActionsVisible(false);
        startInlineEdit(msg, bubble, bodyTextEl, metaRow, timeEl, editedBtn, originalRevealEl);
      });
      deleteBtn.addEventListener('click', (e) => { e.stopPropagation(); deleteOwnMessage(msg, row); });
      actions.appendChild(editBtn);
      actions.appendChild(deleteBtn);
      col.appendChild(actions);

      function toggleActionsVisible(show) {
        actions.classList.toggle('show', show);
        metaRow.classList.toggle('faded', show);
        if (originalRevealEl) originalRevealEl.classList.remove('show'); // hide original preview while editing
        if (editedBtn) editedBtn.classList.remove('active');
      }

      let autoHideTimer;
      bubble.style.cursor = 'pointer';
      bubble.addEventListener('click', (e) => {
        e.stopPropagation();
        const willShow = !actions.classList.contains('show');
        toggleActionsVisible(willShow);
        clearTimeout(autoHideTimer);
        if (willShow) {
          autoHideTimer = setTimeout(() => toggleActionsVisible(false), 4000);
        }
      });

      // Expose the toggle so startInlineEdit's save/cancel can call it back
      bubble._pcwHideActions = () => toggleActionsVisible(false);
    }

    row.appendChild(col);

    return row;
  }

  function startInlineEdit(msg, bubble, bodyTextEl, metaRow, timeEl, editedBtn, originalRevealEl) {
    const textarea = el('textarea', { class: 'pcw-edit-input', rows: '2' });
    textarea.value = msg.body;
    bubble.innerHTML = '';
    bubble.appendChild(textarea);
    textarea.focus();

    const restoreNormalView = () => {
      bubble.innerHTML = '';
      bubble.appendChild(bodyTextEl);
      if (bubble._pcwHideActions) bubble._pcwHideActions();
    };

    const finishEdit = async (save) => {
      if (!save) {
        restoreNormalView();
        return;
      }
      const newBody = textarea.value.trim();
      if (!newBody || newBody === msg.body) {
        restoreNormalView();
        return;
      }
      try {
        const json = await apiPost('chat-message-edit.php', { chatId: state.chatId, messageId: msg.id, body: newBody });
        if (json.success) {
          // Capture original_body locally on the FIRST edit only, mirroring
          // the server's own first-edit-only preservation logic, so the
          // reveal-original feature works immediately without a re-poll.
          if (!msg.original_body) {
            msg.original_body = msg.body;
          }
          bodyTextEl.textContent = newBody;
          msg.body = newBody;
          msg.edited_at = new Date().toISOString();
          restoreNormalView();

          // Add the persistent "Edited" tag now if this was the first edit
          // (it wasn't rendered originally since msg.edited_at was empty then)
          if (!editedBtn) {
            editedBtn = el('button', { type: 'button', class: 'pcw-edited-tag-btn' }, 'Edited');
            metaRow.appendChild(editedBtn);
            const reveal = el('div', { class: 'pcw-original-reveal' }, [
              el('div', { class: 'pcw-original-reveal-label' }, 'Original message'),
              el('div', { class: 'pcw-original-reveal-text' }, msg.original_body),
            ]);
            metaRow.parentNode.insertBefore(reveal, metaRow.nextSibling);
            editedBtn.addEventListener('click', (e) => {
              e.stopPropagation();
              const isShown = reveal.classList.toggle('show');
              editedBtn.classList.toggle('active', isShown);
            });
          }
          dlog('message edited: ' + msg.id);
        } else {
          alert('Could not edit message: ' + (json.error || 'unknown error'));
          restoreNormalView();
        }
      } catch (err) {
        alert('Network error — could not edit message.');
        restoreNormalView();
      }
    };

    textarea.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); finishEdit(true); }
      if (e.key === 'Escape') { finishEdit(false); }
    });
    textarea.addEventListener('blur', () => finishEdit(true));
  }

  async function deleteOwnMessage(msg, row) {
    if (!confirm('Delete this message?')) return;
    try {
      const json = await apiPost('chat-message-delete.php', { chatId: state.chatId, messageId: msg.id });
      if (json.success) {
        pcwPlayDeleteSound();
        row.querySelector('.pcw-msg-bubble').outerHTML = '<div class="pcw-msg-bubble pcw-msg-deleted-tombstone">This message was deleted</div>';
        const actions = row.querySelector('.pcw-msg-actions');
        if (actions) actions.remove();
        const metaRow = row.querySelector('.pcw-msg-meta-row');
        if (metaRow) metaRow.remove();
        const reveal = row.querySelector('.pcw-original-reveal');
        if (reveal) reveal.remove();
        dlog('message deleted: ' + msg.id);
      } else {
        alert('Could not delete message: ' + (json.error || 'unknown error'));
      }
    } catch (err) {
      alert('Network error — could not delete message.');
    }
  }

  function buildAudioBar(src) {
    // Use a DOM <audio> element — cross-browser safe including iOS Safari.
    // The detached Audio() object used previously can be blocked by mobile browsers.
    const wrap = el('div', { class: 'pcw-audio-bar' });
    const audioEl = document.createElement('audio');
    audioEl.src = src;
    audioEl.controls = true;
    audioEl.style.cssText = 'flex:1;min-width:0;height:36px;';
    wrap.appendChild(audioEl);
    return wrap;
  }

  function buildDownloadChip(mediaPath, type, iconClass) {
    // Renders sent images/videos as a downloadable link rather than
    // inline media — reduces storage load, speeds up previous-chat history
    // loading, and works consistently across all browsers without inline
    // rendering quirks. (Option 3 decision, confirmed in planning.)
    const absPath = mediaPath ? '/admin/' + mediaPath : '#';
    const filename = mediaPath ? mediaPath.split('/').pop() : (type === 'image' ? 'image' : 'video');
    const chip = el('a', {
      href: absPath,
      download: filename,
      class: 'pcw-download-chip',
      target: '_blank',
    }, [
      el('i', { class: 'fas ' + iconClass }),
      el('span', { class: 'pcw-download-name' }, filename),
      el('i', { class: 'fas fa-download pcw-download-arrow' }),
    ]);
    return chip;
  }

  function formatTime(isoStr) {
    if (!isoStr) return '';
    const d = new Date(isoStr.replace(' ', 'T'));
    return d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
  }

  // ============================================
  // POLLING (Tier 1 item 8 — 2-3s focused, ~15s blurred)
  // ============================================
  let tabFocused = !document.hidden;
  document.addEventListener('visibilitychange', () => {
    tabFocused = !document.hidden;
    dlog('tab visibility changed, focused=' + tabFocused);
  });

  function startPolling() {
    stopPolling();
    pollOnce();
  }

  function stopPolling() {
    if (state.pollTimer) {
      clearTimeout(state.pollTimer);
      state.pollTimer = null;
    }
  }

  async function pollOnce() {
    if (!state.chatId || state.mode !== 'live' || state.pollInFlight) {
      scheduleNextPoll();
      return;
    }
    state.pollInFlight = true;
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

  function scheduleNextPoll() {
    if (state.mode !== 'live') return;
    // Keep polling even while the panel is closed (at the slower blurred
    // interval) so the unread badge and launcher dot can reflect new
    // activity without requiring the user to reopen the panel first.
    const interval = (tabFocused && state.panelOpen) ? CONFIG.pollIntervalFocused : CONFIG.pollIntervalBlurred;
    state.pollTimer = setTimeout(pollOnce, interval);
  }

  function handlePollResult(json) {
    let scrolledToBottom = isScrolledToBottom();

    // v2.0: bot messages don't have a chat_admin_profiles row to LEFT JOIN
    // against, so attach the bot's public name/photo here before rendering —
    // renderMessage() then treats a 'bot' message exactly like an 'admin'
    // one (avatar + name shown), no separate render branch needed.
    const botInfo = json.botProfile || null;
    if (botInfo && json.messages) {
      json.messages.forEach(m => {
        if (m.from_type === 'bot') {
          m.admin_name = botInfo.name;
          m.admin_photo = botInfo.photoUrl;
        }
      });
    }

    // Merge events and messages into one true chronological sequence by
    // created_at (id is the tiebreaker within the same array, since IDs are
    // independent auto-increment sequences across the two different tables
    // and can't be compared directly against each other). This is what
    // fixes the ticket_closed card always landing at the top — previously
    // ALL events were rendered in one pass, then ALL messages in a second
    // pass, regardless of actual timing.
    const events = (json.events || []).map(e => ({ ...e, _kind: 'event', _time: new Date((e.created_at || '').replace(' ', 'T')).getTime() || 0 }));
    const messages = (json.messages || []).map(m => ({ ...m, _kind: 'message', _time: new Date((m.created_at || '').replace(' ', 'T')).getTime() || 0 }));
    const merged = [...events, ...messages].sort((a, b) => {
      if (a._time !== b._time) return a._time - b._time;
      // Same timestamp (second-resolution ties are common) — events from
      // the events table and messages from the messages table can't be
      // ordered against each other by id, so fall back to insertion order
      // (events array was concatenated first, preserving a stable sort).
      return 0;
    });

    merged.forEach(item => {
      if (item._kind === 'event') {
        const evt = item;
        state.lastEventId = Math.max(state.lastEventId, evt.id);
        if (!messagesEl) return;
        const existingBanner = messagesEl.querySelector('.pcw-joined-banner');
        if (evt.event_type === 'admin_joined') {
          // Update or add the joined banner — never remove it first,
          // so there's no flicker if admin_joined fires more than once
          // (e.g. auto-claim on first reply while thread was waiting).
          const newBanner = renderJoinedBanner(evt.admin_name, evt.admin_photo, evt.admin_position, evt.admin_blue_check);
          if (existingBanner) {
            existingBanner.replaceWith(newBanner);
          } else {
            messagesEl.appendChild(newBanner);
          }
          activeDotEl.classList.add('show');
          activeDotEl.style.background = '#10b981';
        } else if (evt.event_type === 'admin_left_done') {
          if (existingBanner) existingBanner.remove();
          activeDotEl.classList.remove('show');
        } else if (evt.event_type === 'ticket_closed') {
          // TASK-8 START (8c — rating-notification persistence bug)
          // Task 8c: a stale, never-rated/never-dismissed prompt from a
          // PRIOR Done cycle should already have been cleared client-side
          // the moment the customer reopened this thread (see
          // dismissStaleTicketClosedPrompt(), called from
          // sendComposedMessage()) — this querySelector should normally
          // find nothing left over at this point. It's kept anyway as a
          // defensive fallback (e.g. a second tab, or a full-history
          // replay where the reopen-time removal never had a chance to
          // run) so a leftover card is still swapped rather than stacked.
          // TASK-8 END (8c — rating-notification persistence bug)
          const existing = messagesEl.querySelector('.pcw-ticket-closed');
          const card = buildTicketClosedCard(evt);
          if (existing) {
            existing.replaceWith(card);
          } else {
            messagesEl.appendChild(card);
          }
          activeDotEl.classList.remove('show');
        }
      } else {
        const msg = item;
        const isGenuinelyNew = msg.id > state.lastMessageId;
        state.lastMessageId = Math.max(state.lastMessageId, msg.id);
        // TASK-8 START (8a — bot response pacing)
        // Task 8a: whether a bot message's notification sound plays here
        // (immediately) or is deferred until it's actually revealed.
        // Must mirror the exact same condition used below to decide
        // whether appendBotMessageWithDelay() actually runs — including
        // messagesEl/panelOpen — otherwise a bot message arriving while
        // the panel is closed (where the delayed-reveal branch never
        // executes at all) would silently lose its notification sound
        // entirely, since neither branch would end up playing it.
        const isDelayedBotReveal = !!(messagesEl && state.panelOpen) &&
          msg.from_type === 'bot' && isGenuinelyNew && state.pollHasRunOnce;
        if (messagesEl && state.panelOpen) {
          // Task 8a: a bot reply gets a believable "processing" pause +
          // typewriter reveal instead of popping in instantly — but only
          // on a genuinely live update, never on a full-history replay
          // (state.pollHasRunOnce is false on the first load/reopen —
          // same gate the receive-sound check below already uses, for
          // the identical reason: don't re-animate old history).
          if (isDelayedBotReveal) {
            appendBotMessageWithDelay(msg); // plays the receive sound itself, at reveal time
          } else {
            messagesEl.appendChild(renderMessage(msg));
          }
        }
        // Play the receive sound only for a truly new incoming admin/bot
        // message (not the customer's own message echoing back, and not
        // historical messages replayed on a fresh full-history load) —
        // EXCEPT a bot message going through the delayed reveal above,
        // which plays its own sound once actually visible instead of
        // here, so the "ding" doesn't fire during the silent pause,
        // seconds before the message appears.
        if (isGenuinelyNew && (msg.from_type === 'admin' || msg.from_type === 'bot') && state.pollHasRunOnce && !isDelayedBotReveal) {
          pcwPlayReceiveSound();
        }
        // TASK-8 END (8a — bot response pacing)
        if (!state.panelOpen && msg.from_type === 'admin') {
          state.unreadCount++;
          showUnreadBadge(state.unreadCount);
          // Persist unread count server-side so it survives browser close/reopen
          saveUnreadCount(state.unreadCount);
        }
      }
    });
    state.pollHasRunOnce = true;

    if (json.messages && json.messages.length) {
      localStorage.setItem(CONFIG.storageKeyLastMsg, String(state.lastMessageId));
    }

    state.status = json.status;
    // Keep online/offline dot in sync with thread status on every poll
    if (state.status === 'active') {
      activeDotEl.classList.add('show');
      activeDotEl.style.background = '#10b981';
    } else {
      activeDotEl.classList.remove('show');
    }

    // TASK-8 START (8b — live typing + recording indicator, admin → customer)
    // Task 8b: live typing + voice-recording indicator, admin → customer.
    // adminTyping/adminRecording both come from chat-poll.php reading
    // chat_typing (who='admin' / who='admin_recording' respectively —
    // see chat-typing.php). Recording takes visual priority over plain
    // typing when both are somehow true, since the mic icon is the more
    // specific/useful signal to show.
    //
    // NOTE: excludes .pcw-bot-processing — that placeholder reuses the
    // same .pcw-typing-dots look-and-feel (Task 8a) but represents a
    // completely different, independently-timed thing (a pending bot
    // reply, tracked by its own setTimeout closure). Without this
    // exclusion, an admin typing at the same moment a bot reply is
    // pending could have this block seize and replace the bot's
    // placeholder, permanently orphaning that reply (its setTimeout
    // checks placeholder.isConnected and silently bails once true).
    let typingEl = messagesEl ? messagesEl.querySelector('.pcw-typing-dots:not(.pcw-bot-processing)') : null;
    const wantsRecordingUI = !!json.adminRecording;
    const wantsTypingUI = !!json.adminTyping || wantsRecordingUI;
    if (wantsTypingUI) {
      const isRecordingUI = typingEl ? typingEl.classList.contains('pcw-typing-dots--recording') : null;
      if (!typingEl || isRecordingUI !== wantsRecordingUI) {
        const replacement = wantsRecordingUI
          ? el('div', { class: 'pcw-typing-dots pcw-typing-dots--recording' }, [
              el('i', { class: 'fas fa-microphone' }),
              el('span', {}, 'Recording a voice message...'),
            ])
          : el('div', { class: 'pcw-typing-dots' }, [el('span'), el('span'), el('span')]);
        if (typingEl) typingEl.replaceWith(replacement);
        else if (messagesEl) messagesEl.appendChild(replacement);
        typingEl = replacement;
      }
    } else if (typingEl) {
      typingEl.remove();
    }
    // TASK-8 END (8b — live typing + recording indicator, admin → customer)

    if (scrolledToBottom && messagesEl) {
      messagesEl.scrollTop = messagesEl.scrollHeight;
    }
  }

  // TASK-8 START (8c — rating-notification persistence bug)
  // ── Task 8c: forcibly clears any leftover, unresolved Ticket Closed +
  //    rating card the moment the customer reopens a Done thread by
  //    sending into it, so it never lingers across a subsequent Done
  //    cycle. See the call site in sendComposedMessage(). ──
  function dismissStaleTicketClosedPrompt() {
    if (messagesEl) {
      const stale = messagesEl.querySelector('.pcw-ticket-closed');
      if (stale) stale.remove();
    }
    if (state.chatId) localStorage.removeItem('pc_ticket_closed_' + state.chatId);
  }
  // TASK-8 END (8c — rating-notification persistence bug)

  function buildTicketClosedCard(evt) {
    // Mark this ticket as closed in localStorage so it persists across sessions
    if (state.chatId) {
      localStorage.setItem('pc_ticket_closed_' + state.chatId, '1');
    }

    const wrap = el('div', { class: 'pcw-ticket-closed' });

    const closedNotice = el('div', { class: 'pcw-ticket-closed-notice' }, [
      el('i', { class: 'fas fa-circle-check', style: 'color:#10b981;margin-right:7px;' }),
      el('strong', {}, 'Ticket Closed'),
      el('span', { style: 'color:#7a6d5f;font-size:12px;margin-left:6px;' },
        'This conversation has been marked resolved, you can start a new chat below'),
    ]);
    wrap.appendChild(closedNotice);

    const adminName = evt.admin_name || 'our team';
    const ratingPrompt = el('div', { class: 'pcw-rating-prompt' });
    ratingPrompt.appendChild(el('div', { class: 'pcw-rating-prompt-title' },
      `How was your experience with ${adminName}?`));

    const btnRow = el('div', { class: 'pcw-rating-btn-row' });
    const dismissBtn = el('button', { type: 'button', class: 'pcw-rating-dismiss-btn' }, 'Dismiss');
    const rateBtn = el('button', { type: 'button', class: 'pcw-primary-btn', style: 'width:auto;padding:8px 18px;font-size:13px;' }, 'Rate');

    dismissBtn.addEventListener('click', () => {
      ratingPrompt.remove();
      // TASK-8 START (8c — rating-notification persistence bug)
      // Task 8c: dismissing genuinely resolves this prompt — clear the
      // persistence flag so a stale, already-handled prompt is never
      // mistaken for an unresolved one later.
      if (state.chatId) localStorage.removeItem('pc_ticket_closed_' + state.chatId);
      // TASK-8 END (8c — rating-notification persistence bug)
    });
    rateBtn.addEventListener('click', () => { ratingPrompt.remove(); wrap.appendChild(buildRatingForm(evt)); });

    btnRow.appendChild(dismissBtn);
    btnRow.appendChild(rateBtn);
    ratingPrompt.appendChild(btnRow);
    wrap.appendChild(ratingPrompt);
    return wrap;
  }

  function buildRatingForm(evt) {
    const form = el('div', { class: 'pcw-rating-form' });

    const closeBtn = el('button', { type: 'button', class: 'pcw-rating-form-close', title: 'Close' },
      el('i', { class: 'fas fa-times' }));
    closeBtn.addEventListener('click', () => form.remove());
    form.appendChild(closeBtn);
    form.appendChild(el('div', { class: 'pcw-rating-form-title' }, 'Share your experience'));

    // Face icon ratings using FA6 outline style (far) — matches screenshot 1
    // FA 6.4.2 is already loaded on the site
    const faces = [
      { score: 1, icon: 'far fa-face-tired',    label: 'Bad' },
      { score: 2, icon: 'far fa-face-frown',     label: 'Poor' },
      { score: 3, icon: 'far fa-face-meh',       label: 'Okay' },
      { score: 4, icon: 'far fa-face-smile',     label: 'Good' },
      { score: 5, icon: 'far fa-face-grin-beam', label: 'Perfect' },
    ];

    let selectedRating = 0;
    const faceRow = el('div', { class: 'pcw-rating-faces' });
    const faceBtns = faces.map(f => {
      const btn = el('button', { type: 'button', class: 'pcw-rating-face', 'data-score': f.score });
      btn.appendChild(el('i', { class: f.icon + ' pcw-face-icon' }));
      btn.appendChild(el('span', { class: 'pcw-face-label' }, f.label));
      btn.addEventListener('click', () => {
        selectedRating = f.score;
        faceBtns.forEach(b => b.classList.toggle('selected', b === btn));
      });
      faceRow.appendChild(btn);
      return btn;
    });
    form.appendChild(faceRow);

    const textarea = el('textarea', { class: 'pcw-rating-textarea', maxlength: '500', placeholder: 'Share your experience...' });
    form.appendChild(textarea);

    const errMsg = el('div', { style: 'color:#ef4444;font-size:12px;margin-top:6px;display:none;' });
    const submitBtn = el('button', { type: 'button', class: 'pcw-primary-btn', style: 'margin-top:10px;' }, 'Submit Review');

    submitBtn.addEventListener('click', async () => {
      if (!selectedRating) {
        errMsg.textContent = 'Please select a rating.';
        errMsg.style.display = 'block';
        return;
      }
      submitBtn.disabled = true;
      submitBtn.textContent = 'Submitting...';
      try {
        const res = await fetch(CONFIG.apiBase + 'chat-rate.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            action: 'submit',
            chatId: state.chatId,
            email: state.email,
            adminId: evt.admin_id || 0,
            adminName: evt.admin_name || '',
            rating: selectedRating,
            comment: textarea.value.trim(),
            /* TASK-1 START — chat-rate.php's submit action now requires a
               valid session token for this chatId, same as every other
               customer-facing write. */
            sessionToken: state.sessionToken,
            /* TASK-1 END */
          }),
        });
        const json = await res.json();
        if (json.success) {
          form.innerHTML = '<div style="text-align:center;padding:16px;color:#10b981;"><i class="fas fa-circle-check" style="font-size:28px;display:block;margin-bottom:8px;"></i><strong>Thank you for your feedback!</strong></div>';
          setTimeout(() => form.remove(), 3000);
          // TASK-8 START (8c — rating-notification persistence bug)
          // Task 8c: a submitted rating genuinely resolves this prompt —
          // clear the persistence flag, same as the dismiss path.
          if (state.chatId) localStorage.removeItem('pc_ticket_closed_' + state.chatId);
          // TASK-8 END (8c — rating-notification persistence bug)
        } else {
          errMsg.textContent = json.error || 'Submission failed.';
          errMsg.style.display = 'block';
          submitBtn.disabled = false;
          submitBtn.textContent = 'Submit Review';
        }
      } catch (e) {
        errMsg.textContent = 'Network error. Please try again.';
        errMsg.style.display = 'block';
        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit Review';
      }
    });

    form.appendChild(submitBtn);
    form.appendChild(errMsg);
    return form;
  }

  // ── Unread count server-side persistence ─────────────────────────────
  async function saveUnreadCount(count) {
    // Store unread count server-side keyed by chatId so it survives browser close.
    // We reuse chat_drafts table with a special key prefix rather than adding a new table.
    if (!state.chatId) return;
    try {
      await fetch(CONFIG.apiBase + 'chat-draft.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        /* TASK-1 START — draftKey 'unread:{chatId}' is one of the two real-
           chat-id shapes chat-draft.php now requires a token for (see that
           file's own TASK-1 comment on the 3 draftKey shapes). */
        body: JSON.stringify({ action: 'save', draftKey: 'unread:' + state.chatId, body: String(count), sessionToken: state.sessionToken }),
        /* TASK-1 END */
      });
    } catch (e) { /* non-critical */ }
  }

  async function loadUnreadCount() {
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
        const count = parseInt(json.draft, 10) || 0;
        if (count > 0 && !state.panelOpen) {
          state.unreadCount = count;
          showUnreadBadge(count);
        }
      }
    } catch (e) { /* non-critical */ }
  }

  function isScrolledToBottom() {
    if (!messagesEl) return true;
    return messagesEl.scrollHeight - messagesEl.scrollTop - messagesEl.clientHeight < 60;
  }

  // ============================================
  // COMPOSER — grouped multi-media send (Tier 1 item 12)
  // ============================================
  function buildComposer() {
    const composer = el('div', { class: 'pcw-composer' });

    attachRowEl = el('div', { class: 'pcw-attach-row' });
    voicePreviewEl = el('div', { class: 'pcw-voice-preview' });
    recordingBarEl = el('div', { class: 'pcw-recording-bar' }, [
      el('span', { class: 'pcw-recording-dot' }),
      (recordingTimeEl = el('span', { class: 'pcw-recording-time' }, '0:00 / 10:00')),
      el('span', {}, 'Recording...'),
    ]);

    composerInputEl = el('textarea', { class: 'pcw-composer-input', rows: '1', placeholder: 'Type a message...' });
    composerInputEl.addEventListener('input', onComposerInput);
    // Enter adds a newline — only the Send button sends (removed Enter-to-send per user request)

    const imageBtn = el('button', { type: 'button', class: 'pcw-tool-btn', title: 'Attach images (max 10)' }, el('i', { class: 'fas fa-image' }));
    const imageInput = el('input', { type: 'file', accept: 'image/jpeg,image/png', multiple: 'multiple', style: 'display:none' });
    imageInput.addEventListener('change', (e) => {
      const files = Array.from(e.target.files || []);
      const remaining = 10 - state.composer.imageTokens.length;
      files.slice(0, remaining).forEach(f => handleAttach('image', f));
      if (files.length > remaining) alert(`You can attach up to 10 images. ${files.length - remaining} file(s) were skipped.`);
      imageInput.value = '';
    });
    imageBtn.addEventListener('click', () => imageInput.click());

    const videoBtn = el('button', { type: 'button', class: 'pcw-tool-btn', title: 'Attach videos (max 5)' }, el('i', { class: 'fas fa-video' }));
    const videoInput = el('input', { type: 'file', accept: 'video/*', multiple: 'multiple', style: 'display:none' });
    videoInput.addEventListener('change', (e) => {
      const files = Array.from(e.target.files || []);
      const remaining = 5 - state.composer.videoTokens.length;
      files.slice(0, remaining).forEach(f => handleAttach('video', f));
      if (files.length > remaining) alert(`You can attach up to 5 videos. ${files.length - remaining} file(s) were skipped.`);
      videoInput.value = '';
    });
    videoBtn.addEventListener('click', () => videoInput.click());

    micBtnEl = el('button', { type: 'button', class: 'pcw-tool-btn', title: 'Record voice message' }, el('i', { class: 'fas fa-microphone' }));
    micBtnEl.addEventListener('click', toggleRecording);

    sendBtnEl = el('button', { type: 'button', class: 'pcw-send-btn', title: 'Send' }, el('i', { class: 'fas fa-paper-plane' }));
    sendBtnEl.addEventListener('click', sendComposedMessage);

    const row = el('div', { class: 'pcw-composer-row' }, [
      el('div', { class: 'pcw-composer-tools' }, [imageBtn, videoBtn, micBtnEl]),
      composerInputEl,
      sendBtnEl,
    ]);

    composer.appendChild(attachRowEl);
    composer.appendChild(voicePreviewEl);
    composer.appendChild(recordingBarEl);
    composer.appendChild(row);
    composer.appendChild(imageInput);
    composer.appendChild(videoInput);

    updateSendButtonState();
    return composer;
  }

  function updateSendButtonState() {
    const uploading = state.composer.uploadingCount > 0;
    sendBtnEl.disabled = uploading;
    if (sendBtnEl) sendBtnEl.style.opacity = uploading ? '0.5' : '1';
  }

  // ── Attach an image or video: stage immediately, show chip, block Send until ready ──
  async function handleAttach(kind, file) {
    const chip = el('div', { class: 'pcw-attach-chip uploading' }, [
      el('i', { class: kind === 'image' ? 'fas fa-image' : 'fas fa-video' }),
      el('span', {}, file.name.length > 18 ? file.name.slice(0, 15) + '...' : file.name),
      el('span', { class: 'pcw-chip-status' }, 'Uploading...'),
    ]);

    // Show a small thumbnail preview inside the chip for images
    if (kind === 'image') {
      const thumb = document.createElement('img');
      thumb.className = 'pcw-attach-thumb';
      const objUrl = URL.createObjectURL(file);
      thumb.src = objUrl;
      thumb.onload = () => URL.revokeObjectURL(objUrl);
      chip.insertBefore(thumb, chip.firstChild);
    }

    const removeBtn = el('button', { type: 'button' }, el('i', { class: 'fas fa-times' }));
    chip.appendChild(removeBtn);
    attachRowEl.appendChild(chip);

    state.composer.uploadingCount++;
    state.activeUploadCount++;
    updateSendButtonState();
    dlog(`attaching ${kind}: ${file.name}`);

    let tokenSlot = null; // reference to the slot in the tokens array for removal

    removeBtn.addEventListener('click', () => {
      chip.remove();
      // Remove this token from the array if it was already added
      if (tokenSlot !== null) {
        const arr = kind === 'image' ? state.composer.imageTokens : state.composer.videoTokens;
        const idx = arr.indexOf(tokenSlot);
        if (idx > -1) arr.splice(idx, 1);
      }
      if (chip.classList.contains('uploading')) {
        // Still uploading — decrement count
        state.composer.uploadingCount = Math.max(0, state.composer.uploadingCount - 1);
        state.activeUploadCount = Math.max(0, state.activeUploadCount - 1);
        updateSendButtonState();
      }
    });

    try {
      if (kind === 'video') {
        // Video uses chunked upload — single POST fails on Hostinger's
        // upload_max_filesize limit. 512KB chunks stay well within limits.
        const token = await uploadVideoChunked(file, (pct) => {
          chip.querySelector('.pcw-chip-status').textContent = Math.round(pct) + '%';
        });
        state.composer.uploadingCount = Math.max(0, state.composer.uploadingCount - 1);
        state.activeUploadCount = Math.max(0, state.activeUploadCount - 1);
        if (token) {
          tokenSlot = token;
          state.composer.videoTokens.push(token);
          chip.classList.remove('uploading'); chip.classList.add('ready');
          chip.querySelector('.pcw-chip-status').textContent = 'Ready';
        } else {
          chip.classList.remove('uploading'); chip.classList.add('failed');
          chip.querySelector('.pcw-chip-status').textContent = 'Upload failed';
        }
        updateSendButtonState();
      } else {
        // Image and audio: single POST, small enough for PHP limits
        const fd = new FormData();
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
        state.composer.uploadingCount = Math.max(0, state.composer.uploadingCount - 1);
        state.activeUploadCount = Math.max(0, state.activeUploadCount - 1);
        if (json.success) {
          tokenSlot = json.token;
          if (kind === 'image') state.composer.imageTokens.push(json.token);
          else state.composer.audioToken = json.token;
          chip.classList.remove('uploading'); chip.classList.add('ready');
          chip.querySelector('.pcw-chip-status').textContent = 'Ready';
        } else {
          chip.classList.remove('uploading'); chip.classList.add('failed');
          chip.querySelector('.pcw-chip-status').textContent = json.error || 'Failed';
          dlog(`${kind} attach failed: ` + json.error, 'error');
        }
        updateSendButtonState();
      }
    } catch (err) {
      state.composer.uploadingCount = Math.max(0, state.composer.uploadingCount - 1);
      state.activeUploadCount = Math.max(0, state.activeUploadCount - 1);
      chip.classList.remove('uploading'); chip.classList.add('failed');
      chip.querySelector('.pcw-chip-status').textContent = 'Failed';
      updateSendButtonState();
      dlog(`${kind} attach error: ` + err.message, 'error');
    }
  }

  // ── Chunked video upload — splits into 512KB chunks for Hostinger compatibility ──
  async function uploadVideoChunked(file, onProgress) {
    const CHUNK_SIZE = 512 * 1024; // 512KB per chunk
    const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
    const uploadId = 'chat_' + Date.now() + '_' + Math.random().toString(36).slice(2);
    const sessionId = uploadId;

    dlog(`Video chunked upload: ${file.name} (${(file.size/1024/1024).toFixed(1)}MB) ${totalChunks} chunks`);

    // Upload each chunk sequentially
    for (let i = 0; i < totalChunks; i++) {
      const start = i * CHUNK_SIZE;
      const end = Math.min(start + CHUNK_SIZE, file.size);
      const chunk = file.slice(start, end);

      const fd = new FormData();
      fd.append('action', 'upload');
      fd.append('uploadId', uploadId);
      fd.append('sessionId', sessionId);
      fd.append('chunkIndex', i);
      fd.append('totalChunks', totalChunks);
      fd.append('fileName', file.name);
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
        const res = await fetch(CONFIG.apiBase + 'chat-upload-video-chunk.php', { method: 'POST', body: fd });
        const json = await res.json();
        if (!json.success) {
          dlog('Chunk ' + i + ' failed: ' + json.error, 'error');
          return null;
        }
        if (onProgress) onProgress(((i + 1) / totalChunks) * 90); // 90% for upload phase
      } catch (err) {
        dlog('Chunk ' + i + ' network error: ' + err.message, 'error');
        return null;
      }
    }

    // Finalize — assemble chunks into a staged token
    try {
      const fd = new FormData();
      fd.append('action', 'finalize');
      fd.append('uploadId', uploadId);
      fd.append('sessionId', sessionId);
      fd.append('totalChunks', totalChunks);
      fd.append('fileName', file.name);
      /* TASK-1 START — same as the upload-action chunk loop above. */
      if (state.chatId) fd.append('chatId', state.chatId);
      if (state.sessionToken) fd.append('sessionToken', state.sessionToken);
      /* TASK-1 END */
      const res = await fetch(CONFIG.apiBase + 'chat-upload-video-chunk.php', { method: 'POST', body: fd });
      const json = await res.json();
      if (json.success) {
        if (onProgress) onProgress(100);
        dlog('Video chunked upload complete, token=' + json.token);
        return json.token;
      } else {
        dlog('Video finalize failed: ' + json.error, 'error');
        return null;
      }
    } catch (err) {
      dlog('Video finalize network error: ' + err.message, 'error');
      return null;
    }
  }

  // ── Voice recording: tap to start/stop, 10-minute countdown, preview before send ──
  async function toggleRecording() {
    if (mediaRecorder && mediaRecorder.state === 'recording') {
      stopRecording();
      return;
    }
    try {
      const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
      recordedChunks = [];
      // Detect the best supported audio format — iOS Safari requires mp4,
      // Chrome/Android use webm. Some Android browsers (e.g. Brave Beta) 
      // report video/webm even for audio-only recordings — we accept this
      // and handle it correctly on the server side.
      const preferredMime = [
        'audio/webm;codecs=opus',
        'audio/webm',
        'audio/mp4',
        'audio/ogg;codecs=opus',
        'video/webm', // fallback: some Android browsers only support this even for audio-only
      ].find(m => {
        try { return MediaRecorder.isTypeSupported(m); } catch(e) { return false; }
      }) || '';
      recordedMime = preferredMime;
      mediaRecorder = new MediaRecorder(stream, preferredMime ? { mimeType: preferredMime } : {});
      mediaRecorder.ondataavailable = (e) => { if (e.data.size > 0) recordedChunks.push(e.data); };
      mediaRecorder.onstop = onRecordingStopped;
      mediaRecorder.start();
      recordingStartTime = Date.now();
      micBtnEl.classList.add('recording');
      recordingBarEl.classList.add('active');
      pcwPlayRecordStartSound();
      dlog('voice recording started');
      // TASK-8 START (8b — outgoing recording ping)
      startRecordingPing(); // let the admin side see "recording a voice message..."
      // TASK-8 END (8b — outgoing recording ping)

      recordingTimer = setInterval(() => {
        const elapsed = Math.floor((Date.now() - recordingStartTime) / 1000);
        const mins = Math.floor(elapsed / 60), secs = elapsed % 60;
        recordingTimeEl.textContent = `${mins}:${String(secs).padStart(2, '0')} / 10:00`;
        if (elapsed >= 600) { // 10 minute hard limit, auto-stop into preview (item 8 confirmed decision)
          stopRecording();
        }
      }, 500);
    } catch (err) {
      dlog('microphone access failed: ' + err.message, 'error');
      alert("Couldn't access your microphone. Please check your browser permissions.");
    }
  }

  function stopRecording() {
    if (mediaRecorder && mediaRecorder.state === 'recording') {
      mediaRecorder.stop();
      mediaRecorder.stream.getTracks().forEach(t => t.stop());
      pcwPlayRecordStopSound();
    }
    clearInterval(recordingTimer);
    // TASK-8 START (8b — outgoing recording ping)
    stopRecordingPing(); // stop refreshing the recording ping; existing row just expires (6s)
    // TASK-8 END (8b — outgoing recording ping)
    micBtnEl.classList.remove('recording');
    recordingBarEl.classList.remove('active');
  }

  function onRecordingStopped() {
    recordedBlob = new Blob(recordedChunks, { type: recordedMime || 'audio/webm' });
    dlog('voice recording stopped, size=' + recordedBlob.size);

    voicePreviewEl.innerHTML = '';
    voicePreviewEl.classList.add('active');
    const url = URL.createObjectURL(recordedBlob);

    // Use a real DOM <audio> element — detached Audio() objects are blocked
    // by iOS Safari and some Android browsers unless created in a direct
    // user-gesture context. A DOM <audio controls> element is universally
    // supported and also gives a native scrubber without extra code.
    const audioEl = document.createElement('audio');
    audioEl.src = url;
    audioEl.controls = true;
    audioEl.style.cssText = 'flex:1;min-width:0;height:36px;';

    const reRecordBtn = el('button', { type: 'button', class: 'pcw-tool-btn', title: 'Re-record' }, el('i', { class: 'fas fa-rotate-left' }));
    reRecordBtn.addEventListener('click', () => {
      URL.revokeObjectURL(url);
      voicePreviewEl.classList.remove('active');
      voicePreviewEl.innerHTML = '';
      recordedBlob = null;
      toggleRecording();
    });
    const discardBtn = el('button', { type: 'button', class: 'pcw-tool-btn', title: 'Discard' }, el('i', { class: 'fas fa-trash' }));
    discardBtn.addEventListener('click', () => {
      URL.revokeObjectURL(url);
      voicePreviewEl.classList.remove('active');
      voicePreviewEl.innerHTML = '';
      recordedBlob = null;
    });

    voicePreviewEl.appendChild(audioEl);
    voicePreviewEl.appendChild(reRecordBtn);
    voicePreviewEl.appendChild(discardBtn);
  }

  // ── Send: uploads the recorded voice note (if any) at send time, then
  //    sends text + image + audio + video together in one grouped call ──
  async function sendComposedMessage(retryPayload) {
    // Guard: the send button's click event passes a MouseEvent as the first
    // argument. Only treat retryPayload as a real payload if it's a plain
    // object with a chatId property — never a browser Event object.
    const isRealRetry = retryPayload && typeof retryPayload === 'object' &&
                        !(retryPayload instanceof Event) && retryPayload.chatId;
    const c = state.composer;
    let payload;

    // TASK-8 START (8c — rating-notification persistence bug)
    // Task 8c fix: sending a genuinely new message while the thread's
    // current status is 'done' is exactly the client-side signal that the
    // customer is reopening it — chat-send.php flips status done→waiting
    // server-side on this same send ($wasReopenedFromDone). Any stale,
    // never-rated/never-dismissed Ticket Closed + rating prompt from the
    // PREVIOUS Done cycle must disappear right now: left alone, it sits
    // there forever, and because nothing ever resolved it, the next Done
    // cycle can't tell its own new prompt apart from that old one either.
    if (!isRealRetry && state.status === 'done') {
      dismissStaleTicketClosedPrompt();
    }
    // TASK-8 END (8c — rating-notification persistence bug)

    if (isRealRetry) {
      payload = retryPayload;
      dlog('retrying failed send');
    } else {
      const text = composerInputEl.value.trim();
      const c = state.composer;
      if (!text && !c.imageTokens.length && !c.audioToken && !c.videoTokens.length && !recordedBlob) return;
      if (c.uploadingCount > 0) return; // guard, button should already be disabled

      sendBtnEl.disabled = true;

      let audioToken = c.audioToken;
      if (recordedBlob && !audioToken) {
        try {
          const fd = new FormData();
          const voiceExt = recordedMime.includes('mp4') ? 'mp4' : recordedMime.includes('ogg') ? 'ogg' : 'webm';
          fd.append('file', recordedBlob, 'voice-message.' + voiceExt);
          fd.append('kind', 'audio');
          /* TASK-1 START — same as the other chat-upload-temp.php call
             site above. */
          if (state.chatId) fd.append('chatId', state.chatId);
          if (state.sessionToken) fd.append('sessionToken', state.sessionToken);
          /* TASK-1 END */
          const res = await fetch(CONFIG.apiBase + 'chat-upload-temp.php', { method: 'POST', body: fd });
          const json = await res.json();
          if (json.success) {
            audioToken = json.token;
          } else {
            alert('Voice message upload failed: ' + (json.error || 'unknown error'));
            sendBtnEl.disabled = false;
            return;
          }
        } catch (err) {
          alert('Voice message upload failed. Please try again.');
          sendBtnEl.disabled = false;
          return;
        }
      }

      payload = {
        chatId: state.chatId,
        text,
        imageTokens: [...c.imageTokens],   // array — up to 10
        audioToken: audioToken || '',
        videoTokens: [...c.videoTokens],   // array — up to 5
        idempotencyKey: uuid(),
      };
    }

    dlog('sending composed message');
    sendBtnEl.disabled = true;

    try {
      const json = await apiPost('chat-send.php', payload);
      if (json.success) {
        pcwPlaySendSound();
        if (!isRealRetry) {
          composerInputEl.value = '';
          attachRowEl.innerHTML = '';
          voicePreviewEl.classList.remove('active');
          voicePreviewEl.innerHTML = '';
          recordedBlob = null;
          state.composer.imageTokens = [];
          state.composer.audioToken = null;
          state.composer.videoTokens = [];
          clearDraft();
        }
        removeFailedBubble(payload);
        pollOnce();
      } else {
        showFailedBubble(payload, json.error || 'unknown error');
      }
    } catch (err) {
      dlog('send failed: ' + err.message, 'error');
      showFailedBubble(payload, 'Network error');
    }
    sendBtnEl.disabled = false;
  }

  // ── Retry-on-failed-send: persistent inline indicator with a tap-to-retry
  //    affordance, replacing the earlier one-shot alert() approach ──
  function showFailedBubble(payload, errorText) {
    if (!messagesEl) return; // panel may be closed; the retry option appears next time it's opened and re-attempted manually
    const row = el('div', { class: 'pcw-msg-row user' });
    const bubble = el('div', { class: 'pcw-msg-bubble pcw-msg-failed' });
    bubble.appendChild(el('div', {}, payload.text || '(attachment)'));
    const failRow = el('div', { class: 'pcw-msg-fail-row' });
    failRow.appendChild(el('i', { class: 'fas fa-triangle-exclamation' }));
    failRow.appendChild(el('span', {}, 'Not sent — ' + errorText));
    const retryBtn = el('button', { type: 'button', class: 'pcw-retry-btn' }, [el('i', { class: 'fas fa-rotate-right' }), ' Retry']);
    retryBtn.addEventListener('click', () => {
      row.remove();
      sendComposedMessage(payload);
    });
    failRow.appendChild(retryBtn);
    bubble.appendChild(failRow);
    row.appendChild(bubble);
    row.dataset.failedPayload = JSON.stringify(payload);
    messagesEl.appendChild(row);
    messagesEl.scrollTop = messagesEl.scrollHeight;
  }

  function removeFailedBubble(payload) {
    if (!messagesEl) return;
    const matching = messagesEl.querySelector(`[data-failed-payload='${JSON.stringify(payload).replace(/'/g, "\\'")}']`);
    if (matching) matching.remove();
  }

  // ============================================
  // DRAFTS (Tier 1 item 14 — 10s debounce, pre-thread temp key handoff)
  // ============================================
  function onComposerInput() {
    if (state.draftTimer) clearTimeout(state.draftTimer);
    state.draftTimer = setTimeout(saveDraft, CONFIG.draftDebounceMs);
    // TASK-8 START (8b — outgoing typing ping)
    pingTyping(); // throttled "user is typing" ping, see pingTyping() below
    // TASK-8 END (8b — outgoing typing ping)
  }

  // TASK-8 START (8b — live typing + recording pings, user → admin side)
  // ============================================
  // TASK 8b: LIVE TYPING / RECORDING PINGS (user → admin side)
  //
  // chat-typing.php and the read side of this (chat-poll.php's
  // adminTyping / this file's own adminRecording handling above) already
  // existed and were already correct — the bug was that nothing on
  // either side of the widget was actually CALLING chat-typing.php to
  // create a ping in the first place. This is that missing call.
  //
  // Throttled to CONFIG.typingPingInterval (3s) so every keystroke
  // doesn't hit the network — chat-typing.php's 6-second server-side
  // expiry comfortably outlives that gap, so the indicator stays live
  // without flickering between pings.
  // ============================================
  let lastTypingPingAt = 0;
  function pingTyping() {
    if (!state.chatId || state.mode !== 'live') return;
    const now = Date.now();
    if (now - lastTypingPingAt < CONFIG.typingPingInterval) return;
    lastTypingPingAt = now;
    apiPost('chat-typing.php', { chatId: state.chatId, who: 'user' }).catch(() => {});
  }

  let recordingPingTimer = null;
  function pingRecording() {
    if (!state.chatId || state.mode !== 'live') return;
    apiPost('chat-typing.php', { chatId: state.chatId, who: 'user_recording' }).catch(() => {});
  }
  function startRecordingPing() {
    stopRecordingPing();
    pingRecording(); // immediate first ping, don't wait a full interval
    recordingPingTimer = setInterval(pingRecording, CONFIG.typingPingInterval);
  }
  function stopRecordingPing() {
    if (recordingPingTimer) { clearInterval(recordingPingTimer); recordingPingTimer = null; }
  }
  // TASK-8 END (8b — live typing + recording pings, user → admin side)

  function getDraftKey() {
    if (state.draftKey) return state.draftKey;
    let temp = localStorage.getItem(CONFIG.storageKeyDraftUuid);
    if (!temp) {
      temp = uuid();
      localStorage.setItem(CONFIG.storageKeyDraftUuid, temp);
    }
    return temp;
  }

  async function saveDraft() {
    const body = composerInputEl ? composerInputEl.value.trim() : '';
    const key = getDraftKey();
    try {
      await apiPost('chat-draft.php', { action: 'save', draftKey: key, body });
      dlog('draft saved');
    } catch (err) {
      dlog('draft save failed: ' + err.message, 'error');
    }
  }

  async function loadDraft() {
    const key = getDraftKey();
    try {
      const json = await apiGet('chat-draft.php', { action: 'get', draftKey: key });
      if (json.success && json.draft && composerInputEl) {
        composerInputEl.value = json.draft;
      }
    } catch (err) {
      dlog('draft load failed: ' + err.message, 'error');
    }
  }

  async function clearDraft() {
    const key = getDraftKey();
    try {
      await apiPost('chat-draft.php', { action: 'clear', draftKey: key });
      localStorage.removeItem(CONFIG.storageKeyDraftUuid);
    } catch (err) { /* non-critical, ignore */ }
  }

  // ============================================
  // UPLOAD INTERRUPTION HANDLING (Tier 1 item 13 — three precise behaviors)
  // ============================================
  window.addEventListener('beforeunload', (e) => {
    // Only warn if something is ACTIVELY uploading right now — never just
    // because something finished uploading and is merely sitting unsent
    // (that case clears silently with no warning, per item 13).
    if (state.activeUploadCount > 0) {
      e.preventDefault();
      e.returnValue = '';
      return '';
    }
    // Nothing in flight: if there were finished-but-unsent attachments,
    // they're simply lost on reload — no warning needed, by design.
  });

  // ============================================
  // INIT
  // ============================================
  function init() {
    injectStyle();
    buildScaffold();
    updateLauncherButtonState();
    dlog('widget initialized');
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Expose a minimal public surface in case other scripts on the page need it
  window.PCChatWidget = { open: openPanel, close: closePanel };

})();
