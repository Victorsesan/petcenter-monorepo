/* =====================================================================
   TASK-reh-A4 START — Shared authentication prompt
   ---------------------------------------------------------------------
   Public API:
     window.showAuthPrompt(contextMessage)
     window.PetCenterAuthPrompt.configure({ signUpUrl, logInUrl })

   This component deliberately does not inspect login state. Callers decide
   whether a visitor is logged out, then invoke showAuthPrompt(). Destinations
   are configurable because the account routes are owned outside this task;
   the current public sign-in entry point is the safe default for both actions.
   ===================================================================== */
(function (window, document) {
  'use strict';

  var config = {
    // TASK-WEB-04 START — Match the gateway's explicit account-creation mode; callers may still override either URL.
    signUpUrl: '/signin/?mode=signup',
    logInUrl: '/signin/?mode=login'
    // TASK-WEB-04 END
  };
  var modal = null;
  var previousFocus = null;

  function configure(next) {
    next = next || {};
    if (typeof next.signUpUrl === 'string' && next.signUpUrl.trim()) {
      config.signUpUrl = next.signUpUrl.trim();
    }
    if (typeof next.logInUrl === 'string' && next.logInUrl.trim()) {
      config.logInUrl = next.logInUrl.trim();
    }
  }

  function addStyles() {
    if (document.getElementById('pc-auth-prompt-style')) return;
    var style = document.createElement('style');
    style.id = 'pc-auth-prompt-style';
    style.textContent = [
      '.pc-auth-prompt{position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;padding:16px;background:rgba(0,0,0,.65)}',
      '.pc-auth-prompt.is-open{display:flex}',
      '.pc-auth-prompt__box{width:100%;max-width:480px;background:#fff;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,.3);overflow:hidden;font-family:Sora,system-ui,sans-serif;color:#2c2416}',
      '.pc-auth-prompt__head{display:flex;align-items:flex-start;gap:12px;padding:20px 22px 12px}',
      '.pc-auth-prompt__icon{width:40px;height:40px;display:grid;place-items:center;flex:0 0 auto;border-radius:50%;background:#fff3ea;color:#ED7624}',
      '.pc-auth-prompt__title{margin:0;color:#10214b;font:700 25px/1.15 "Cormorant Garamond",Georgia,serif}',
      '.pc-auth-prompt__close{margin-left:auto;width:40px;height:40px;border:0;border-radius:8px;background:#f9f7f4;color:#10214b;font-size:24px;line-height:1;cursor:pointer}',
      '.pc-auth-prompt__close:focus-visible,.pc-auth-prompt__action:focus-visible{outline:3px solid rgba(215,189,136,.7);outline-offset:2px}',
      '.pc-auth-prompt__body{padding:0 22px 22px}',
      '.pc-auth-prompt__message{margin:0 0 20px;color:#7a6d5f;font-size:14px;line-height:1.6}',
      '.pc-auth-prompt__actions{display:flex;flex-wrap:wrap;gap:10px}',
      '.pc-auth-prompt__action{min-height:44px;flex:1 1 150px;padding:11px 18px;border-radius:8px;text-align:center;text-decoration:none;font:600 14px/1.4 Sora,system-ui,sans-serif;cursor:pointer}',
      '.pc-auth-prompt__action--primary{background:#ED7624;color:#fff}',
      '.pc-auth-prompt__action--secondary{background:#10214b;color:#fff}',
      '@media(max-width:480px){.pc-auth-prompt{padding:10px}.pc-auth-prompt__actions{flex-direction:column}.pc-auth-prompt__action{flex-basis:auto;width:100%}}'
    ].join('');
    document.head.appendChild(style);
  }

  function close() {
    if (!modal) return;
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.removeEventListener('keydown', onKeyDown);
    if (previousFocus && typeof previousFocus.focus === 'function') previousFocus.focus();
    previousFocus = null;
  }

  function onKeyDown(event) {
    if (!modal || !modal.classList.contains('is-open')) return;
    if (event.key === 'Escape') {
      event.preventDefault();
      close();
      return;
    }
    if (event.key !== 'Tab') return;
    var focusable = modal.querySelectorAll('a[href],button:not([disabled])');
    if (!focusable.length) return;
    var first = focusable[0];
    var last = focusable[focusable.length - 1];
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  }

  function build() {
    if (modal) return modal;
    addStyles();
    modal = document.createElement('div');
    modal.className = 'pc-auth-prompt';
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('aria-modal', 'true');
    modal.setAttribute('aria-hidden', 'true');
    modal.setAttribute('aria-labelledby', 'pc-auth-prompt-title');
    modal.innerHTML = [
      '<div class="pc-auth-prompt__box" role="document">',
        '<div class="pc-auth-prompt__head">',
          '<div class="pc-auth-prompt__icon" aria-hidden="true">',
            '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
          '</div>',
          '<h2 class="pc-auth-prompt__title" id="pc-auth-prompt-title">One more step</h2>',
          '<button type="button" class="pc-auth-prompt__close" aria-label="Close">&times;</button>',
        '</div>',
        '<div class="pc-auth-prompt__body">',
          '<p class="pc-auth-prompt__message"></p>',
          '<div class="pc-auth-prompt__actions">',
            '<a class="pc-auth-prompt__action pc-auth-prompt__action--primary" data-auth-action="signup">Sign Up</a>',
            '<a class="pc-auth-prompt__action pc-auth-prompt__action--secondary" data-auth-action="login">Log In</a>',
          '</div>',
        '</div>',
      '</div>'
    ].join('');
    document.body.appendChild(modal);
    modal.querySelector('.pc-auth-prompt__close').addEventListener('click', close);
    modal.addEventListener('click', function (event) {
      if (event.target === modal) close();
    });
    return modal;
  }

  function showAuthPrompt(contextMessage) {
    var m = build();
    var context = typeof contextMessage === 'string' && contextMessage.trim()
      ? contextMessage.trim()
      : 'continue';
    var message = m.querySelector('.pc-auth-prompt__message');
    var signup = m.querySelector('[data-auth-action="signup"]');
    var login = m.querySelector('[data-auth-action="login"]');
    message.textContent = 'Please sign up or log in to ' + context + '.';
    signup.href = config.signUpUrl;
    login.href = config.logInUrl;
    previousFocus = document.activeElement;
    m.classList.add('is-open');
    m.setAttribute('aria-hidden', 'false');
    document.addEventListener('keydown', onKeyDown);
    m.querySelector('.pc-auth-prompt__close').focus();
    return m;
  }

  window.showAuthPrompt = showAuthPrompt;
  window.PetCenterAuthPrompt = {
    show: showAuthPrompt,
    close: close,
    configure: configure
  };
})(window, document);
/* TASK-reh-A4 END */
