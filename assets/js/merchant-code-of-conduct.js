/* =====================================================================
   TASK C3 - ITEM 1: MERCHANT CODE OF CONDUCT (new file, built from scratch)
   ---------------------------------------------------------------------
   A single required-agreement modal for the merchant onboarding flow.
   No merchant onboarding flow file exists yet, so this is a standalone,
   self-contained component the onboarding flow (and Task A1's posting
   panel, as the "before first listing" gate) can call.

   PUBLIC API (window.PetCenterCodeOfConduct):
     .show({ onAgree })            open the modal; onAgree(record) fires only
                                   after the agreement was saved successfully
     .requireBeforeListing(account, proceedFn)
                                   call proceedFn() right away if the account
                                   already agreed, otherwise show the modal
                                   first and call proceedFn() after agreement
     .configure({ endpoint, version })   override defaults (see below)

   WHAT GETS LOGGED (the record sent to the endpoint):
     { code_of_conduct_agreed_at: <ISO 8601 UTC timestamp>,
       code_of_conduct_version:   <string> }
   The account field to check for "already agreed" is
   account.code_of_conduct_agreed_at (truthy = agreed).
   ===================================================================== */
(function () {
  'use strict';

  // ---- Config (overridable via configure()) ----
  var config = {
    // PLACEHOLDER endpoint: no backend exists for this yet (see completion doc).
    endpoint: '/merchant-conduct-agree',
    version: '1.0'
  };

  // ---- Code of Conduct wording (plain-language bullets, per task spec) ----
  var BULLETS = [
    'Your listings must be accurate and current. Remove or update a listing as soon as it is no longer true.',
    'Never ask for payment before meeting in person, unless you are using the platform\'s checkout-link feature.',
    'Know your country\'s breed and pet laws. The platform does not restrict listings, but you are personally responsible for following your own local laws.',
    'Your verification data is used only to approve your account and is not stored after review.'
  ];

  var STYLE_ID = 'pc-coc-style';
  var modalEl = null;

  // ---- Inject scoped CSS once. Modal shell follows Design System Section 5
  //      (solid overlay, no blur, no open/close animation, z-index 9999). ----
  function injectStyle() {
    if (document.getElementById(STYLE_ID)) return;
    var css = '' +
      '.pc-coc{position:fixed;inset:0;background:rgba(0,0,0,0.65);display:none;align-items:center;justify-content:center;z-index:9999;padding:16px;}' +
      '.pc-coc.open{display:flex;}' +
      '.pc-coc__box{background:#ffffff;border-radius:12px;max-width:560px;width:100%;max-height:85vh;max-height:85dvh;box-shadow:0 20px 60px rgba(0,0,0,0.3);display:flex;flex-direction:column;overflow:hidden;font-family:"Sora",system-ui,sans-serif;color:#2c2416;}' +
      '.pc-coc__top{padding:16px 20px;border-bottom:1px solid rgba(16,33,75,0.12);flex-shrink:0;display:flex;align-items:center;gap:12px;}' +
      '.pc-coc__icon{width:40px;height:40px;border-radius:50%;background:rgba(16,33,75,0.08);display:flex;align-items:center;justify-content:center;flex-shrink:0;}' +
      '.pc-coc__title{font-family:"Cormorant Garamond",Georgia,serif;font-size:24px;font-weight:700;color:#10214b;margin:0;line-height:1.2;}' +
      '.pc-coc__scroll{flex:1;overflow-y:auto;padding:20px 20px 8px;overscroll-behavior:contain;-webkit-overflow-scrolling:touch;}' +
      '.pc-coc__intro{font-size:14px;line-height:1.6;color:#7a6d5f;margin:0 0 14px;}' +
      '.pc-coc__list{list-style:none;margin:0 0 16px;padding:0;display:flex;flex-direction:column;gap:12px;}' +
      '.pc-coc__list li{display:flex;gap:10px;font-size:14px;line-height:1.6;}' +
      '.pc-coc__list svg{flex-shrink:0;margin-top:3px;}' +
      '.pc-coc__foot{padding:14px 20px 20px;border-top:1px solid rgba(16,33,75,0.12);flex-shrink:0;background:#ffffff;}' +
      '.pc-coc__agree{display:flex;align-items:flex-start;gap:12px;cursor:pointer;font-size:14px;line-height:1.5;font-weight:600;color:#10214b;min-height:44px;padding:4px 0;}' +
      '.pc-coc__agree input{width:22px;height:22px;margin:0;flex-shrink:0;accent-color:#10214b;cursor:pointer;}' +
      '.pc-coc__agree input:focus-visible{outline:3px solid rgba(215,189,136,0.6);outline-offset:2px;}' +
      '.pc-coc__error{display:none;margin:8px 0 0;font-size:13px;color:#ef4444;line-height:1.5;}' +
      '.pc-coc__error.show{display:block;}' +
      '.pc-coc__btn{margin-top:12px;width:100%;height:40px;padding:0 24px;border:0;border-radius:9px;background:#10214b;color:#fff;font-family:inherit;font-size:14px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;box-sizing:border-box;}' +
      '.pc-coc__btn:hover:not(:disabled){transform:translateY(-3px);}' +
      '.pc-coc__btn:active:not(:disabled){background:#0c1a3d;transform:none;}' +
      '.pc-coc__btn:disabled{background:#7a6d5f;opacity:0.5;cursor:not-allowed;}' +
      '@media (max-width:600px){.pc-coc{padding:12px;}.pc-coc__box{max-height:88vh;max-height:88dvh;max-width:100%;}}' +
      '@media (max-width:400px){.pc-coc{padding:8px;}}';
    var st = document.createElement('style');
    st.id = STYLE_ID;
    st.textContent = css;
    document.head.appendChild(st);
  }

  // ---- Inline SVG icons (no emoji rule) ----
  var ICON_SHIELD = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#10214b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l8 3v6c0 4.5-3.2 8.3-8 9-4.8-.7-8-4.5-8-9V6l8-3z"/><polyline points="9 12 11 14 15 10"/></svg>';
  var ICON_CHECK = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>';

  // ---- Build the modal DOM once ----
  function build() {
    if (modalEl) return modalEl;
    injectStyle();
    var lis = BULLETS.map(function (t) { return '<li>' + ICON_CHECK + '<span>' + t + '</span></li>'; }).join('');
    modalEl = document.createElement('div');
    modalEl.className = 'pc-coc';
    modalEl.setAttribute('role', 'dialog');
    modalEl.setAttribute('aria-modal', 'true');
    modalEl.setAttribute('aria-labelledby', 'pcCocTitle');
    modalEl.innerHTML =
      '<div class="pc-coc__box">' +
        '<div class="pc-coc__top"><div class="pc-coc__icon">' + ICON_SHIELD + '</div>' +
          '<h2 class="pc-coc__title" id="pcCocTitle">Merchant Code of Conduct</h2></div>' +
        '<div class="pc-coc__scroll">' +
          '<p class="pc-coc__intro">Before you post your first listing, please read and agree to the following.</p>' +
          '<ul class="pc-coc__list">' + lis + '</ul>' +
        '</div>' +
        '<div class="pc-coc__foot">' +
          '<label class="pc-coc__agree"><input type="checkbox" id="pcCocAgree"><span>I have read and agree to the Merchant Code of Conduct.</span></label>' +
          '<p class="pc-coc__error" id="pcCocError" role="alert"></p>' +
          '<button type="button" class="pc-coc__btn" id="pcCocBtn" disabled>Continue</button>' +
        '</div>' +
      '</div>';
    document.body.appendChild(modalEl);
    return modalEl;
  }

  // ---- Keep Tab focus inside the modal while open ----
  function trapFocus(e) {
    if (e.key !== 'Tab' || !modalEl || !modalEl.classList.contains('open')) return;
    var f = modalEl.querySelectorAll('input,button:not(:disabled)');
    if (!f.length) return;
    var first = f[0], last = f[f.length - 1];
    if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
    else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
  }

  // ---- Save the agreement. Resolves only on a successful save so the
  //      timestamp is truly logged before the merchant is let through. ----
  function saveAgreement(record) {
    return fetch(config.endpoint, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(record)
    }).then(function (res) {
      if (!res.ok) throw new Error('Save failed (' + res.status + ')');
      return record;
    });
  }

  // ---- Open the modal. There is deliberately no close button, no overlay
  //      click-to-close and no Escape-to-close: agreement is required. ----
  function show(opts) {
    opts = opts || {};
    var m = build();
    var cb = m.querySelector('#pcCocAgree');
    var btn = m.querySelector('#pcCocBtn');
    var err = m.querySelector('#pcCocError');
    cb.checked = false; btn.disabled = true; err.classList.remove('show');
    btn.textContent = 'Continue';
    m.classList.add('open');
    document.addEventListener('keydown', trapFocus);
    cb.focus();

    // Continue button only becomes active once the box is ticked
    cb.onchange = function () { btn.disabled = !cb.checked; };

    btn.onclick = function () {
      if (!cb.checked) return;
      btn.disabled = true; btn.textContent = 'Saving...';
      err.classList.remove('show');
      var record = {
        code_of_conduct_agreed_at: new Date().toISOString(),
        code_of_conduct_version: config.version
      };
      saveAgreement(record).then(function (saved) {
        m.classList.remove('open');
        document.removeEventListener('keydown', trapFocus);
        if (typeof opts.onAgree === 'function') opts.onAgree(saved);
      }).catch(function () {
        // Save failed: stay open so the agreement is never silently unlogged
        err.textContent = 'We could not save your agreement. Please check your connection and try again.';
        err.classList.add('show');
        btn.textContent = 'Continue'; btn.disabled = !cb.checked;
      });
    };
  }

  // ---- Gate helper for "required before first listing" ----
  function requireBeforeListing(account, proceedFn) {
    if (account && account.code_of_conduct_agreed_at) { proceedFn(); return; }
    show({ onAgree: function (rec) {
      if (account) account.code_of_conduct_agreed_at = rec.code_of_conduct_agreed_at;
      proceedFn();
    } });
  }

  function configure(o) { for (var k in o) if (Object.prototype.hasOwnProperty.call(config, k)) config[k] = o[k]; }

  window.PetCenterCodeOfConduct = { show: show, requireBeforeListing: requireBeforeListing, configure: configure };
})();
