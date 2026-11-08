/* TASK-DESIGN-02 START — Shared account gateway behavior and safe auth states. */
/* DESIGN-02 AUTH GATEWAY — presentation-only adapter.
   No credential endpoint is invented here. Server-side auth/OAuth remains authoritative. */
(function () {
  'use strict';

  function safeReturnPath(value) {
    if (!value || typeof value !== 'string') return '/account/settings/';
    try {
      var parsed = new URL(value, window.location.origin);
      return parsed.origin === window.location.origin && parsed.pathname.indexOf('//') === -1
        ? parsed.pathname + parsed.search + parsed.hash
        : '/account/settings/';
    } catch (error) {
      return '/account/settings/';
    }
  }

  function setStatus(node, message, type) {
    if (!node) return;
    node.textContent = message;
    node.className = 'design02-auth-status design02-auth-status--' + (type || 'neutral');
    node.hidden = !message;
  }

  function initGateway(root) {
    var loginTab = root.querySelector('[data-auth-tab="login"]');
    var signupTab = root.querySelector('[data-auth-tab="signup"]');
    var loginPanel = root.querySelector('[data-auth-panel="login"]');
    var signupPanel = root.querySelector('[data-auth-panel="signup"]');
    var returnPath = safeReturnPath(new URLSearchParams(window.location.search).get('return'));

    function show(panel) {
      var login = panel === 'login';
      loginPanel.hidden = !login;
      signupPanel.hidden = login;
      loginTab.setAttribute('aria-selected', String(login));
      signupTab.setAttribute('aria-selected', String(!login));
      (login ? loginTab : signupTab).focus({ preventScroll: true });
    }

    loginTab.addEventListener('click', function () { show('login'); });
    signupTab.addEventListener('click', function () { show('signup'); });

    root.querySelectorAll('form[data-auth-form]').forEach(function (form) {
      form.addEventListener('submit', function (event) {
        event.preventDefault();
        var status = form.querySelector('[data-auth-status]');
        var required = Array.from(form.querySelectorAll('[required]'));
        var firstInvalid = required.find(function (field) { return !field.value.trim(); });
        required.forEach(function (field) { field.removeAttribute('aria-invalid'); });
        if (firstInvalid) {
          firstInvalid.setAttribute('aria-invalid', 'true');
          setStatus(status, 'Please complete ' + (firstInvalid.getAttribute('aria-label') || firstInvalid.name || 'the required field') + '.', 'error');
          firstInvalid.focus();
          return;
        }
        setStatus(status, 'Authentication is not configured in this environment. No request was sent and no account was created.', 'warning');
      });
    });

    root.querySelectorAll('[data-google-signin]').forEach(function (button) {
      button.addEventListener('click', function () {
        var status = button.closest('[data-auth-panel]').querySelector('[data-auth-status]');
        setStatus(status, 'Google sign-in is not configured yet. The button is shown for the planned OAuth flow; no redirect was attempted.', 'warning');
      });
    });

    root.querySelectorAll('[data-return-link]').forEach(function (link) {
      link.href = returnPath;
    });
  }

  function init() {
    document.querySelectorAll('[data-auth-gateway]').forEach(initGateway);
  }

  window.DESIGN02Auth = { safeReturnPath: safeReturnPath, init: init };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
}());

/* TASK-DESIGN-02 END */
