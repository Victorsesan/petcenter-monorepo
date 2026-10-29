/* =====================================================================
   TASK C3 - ITEM 2: SAFE-EXCHANGE-ZONE CHAT PROMPT (new file)
   ---------------------------------------------------------------------
   Self-contained client-side module. It does not edit Task C5's chat UI;
   C5's thread view just calls render() each time it paints/polls messages.

   TRIGGER (all must be true):
     - the thread has 3 or more messages in total
     - both parties (merchant and visitor) have sent at least one message
     - at least one message contains a meetup keyword
       ("meet", "pick up", "when can I", "address")
     - the prompt has not already been shown in this thread

   EXPECTED MESSAGE SHAPE (ASSUMPTION - C5's real shape is not published yet):
     { sender_role: 'merchant' | 'visitor', text: '...' }
   If C5 uses other field names, call configure({ getRole, getText }).

   PUBLIC API (window.PCSafeExchange):
     .shouldShow(messages, threadId)          pure check, true/false
     .render(messages, containerEl, threadId) shows the banner if triggered
     .configure({ getRole, getText })
   ===================================================================== */
(function () {
  'use strict';

  var PROMPT_TEXT = 'Planning to meet in person? Consider a police-monitored Safe Exchange Zone near you, and always meet in daylight.';
  var KEYWORDS = ['meet', 'pick up', 'when can i', 'address'];
  var MIN_MESSAGES = 3;
  var STORE_PREFIX = 'pc_safe_exchange_shown_';
  var STYLE_ID = 'pc-sez-style';

  var accessors = {
    getRole: function (m) { return m.sender_role; },
    getText: function (m) { return m.text; }
  };
  // In-memory fallback so "once per thread" still holds if storage is blocked
  var shownMemory = {};

  // ---- "Already shown" check/record. Per-browser only (see completion doc:
  //      server-side persistence would need a C5 field). ----
  function wasShown(threadId) {
    if (shownMemory[threadId]) return true;
    try { return localStorage.getItem(STORE_PREFIX + threadId) === '1'; } catch (e) { return false; }
  }
  function markShown(threadId) {
    shownMemory[threadId] = true;
    try { localStorage.setItem(STORE_PREFIX + threadId, '1'); } catch (e) { /* storage unavailable */ }
  }

  // ---- Does any message contain a meetup keyword (case-insensitive)? ----
  function hasKeyword(messages) {
    return messages.some(function (m) {
      var t = String(accessors.getText(m) || '').toLowerCase();
      return KEYWORDS.some(function (k) { return t.indexOf(k) !== -1; });
    });
  }

  // ---- Pure trigger check ----
  function shouldShow(messages, threadId) {
    if (!messages || messages.length < MIN_MESSAGES) return false;
    if (wasShown(threadId)) return false;
    var roles = {};
    messages.forEach(function (m) { roles[accessors.getRole(m)] = true; });
    if (!(roles.merchant && roles.visitor)) return false;   // both parties must have written
    return hasKeyword(messages);
  }

  function injectStyle() {
    if (document.getElementById(STYLE_ID)) return;
    var st = document.createElement('style');
    st.id = STYLE_ID;
    st.textContent =
      '.pc-sez{display:flex;align-items:flex-start;gap:10px;margin:12px auto;max-width:92%;padding:12px 14px;background:#eff6ff;border:1.5px solid rgba(59,130,246,0.3);border-radius:10px;font-family:"Sora",system-ui,sans-serif;font-size:13.5px;line-height:1.6;color:#2c2416;}' +
      '.pc-sez__icon{flex-shrink:0;margin-top:2px;}' +
      '.pc-sez__text{flex:1;}' +
      '.pc-sez__close{flex-shrink:0;width:44px;height:44px;margin:-12px -14px -12px 0;border:0;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;border-radius:10px;}' +
      '.pc-sez__close:hover{background:rgba(16,33,75,0.08);}' +
      '.pc-sez__close:focus-visible{outline:3px solid rgba(215,189,136,0.6);outline-offset:-3px;}';
    document.head.appendChild(st);
  }

  // ---- Render the dismissible system message into the thread container ----
  function render(messages, containerEl, threadId) {
    if (!containerEl || !shouldShow(messages, threadId)) return false;
    injectStyle();
    var el = document.createElement('div');
    el.className = 'pc-sez';
    el.setAttribute('role', 'status');
    el.innerHTML =
      '<svg class="pc-sez__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>' +
      '<div class="pc-sez__text"></div>' +
      '<button type="button" class="pc-sez__close" aria-label="Dismiss safety tip">' +
        '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#7a6d5f" stroke-width="2" stroke-linecap="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' +
      '</button>';
    el.querySelector('.pc-sez__text').textContent = PROMPT_TEXT;   // textContent: no HTML injection
    el.querySelector('.pc-sez__close').addEventListener('click', function () { el.remove(); });
    containerEl.appendChild(el);
    markShown(threadId);   // recorded when shown, so a dismissal or re-render never repeats it
    return true;
  }

  function configure(o) {
    if (o.getRole) accessors.getRole = o.getRole;
    if (o.getText) accessors.getText = o.getText;
  }

  window.PCSafeExchange = { shouldShow: shouldShow, render: render, configure: configure };
})();
