/* TASK-MANUS-06 START — shared essential/optional cookie consent banner. */
(function () {
  'use strict';
  var key = 'pc_cookie_consent';
  if (localStorage.getItem(key)) return;
  var style = document.createElement('style');
  style.textContent = '#pc-cookie-consent{position:fixed;left:16px;right:16px;bottom:16px;z-index:1000;background:#10214b;color:#fff;padding:16px 18px;border-radius:12px;box-shadow:0 8px 30px rgba(16,33,75,.25);display:flex;gap:16px;align-items:center;justify-content:space-between;font:14px Sora,Arial,sans-serif}#pc-cookie-consent p{margin:0;line-height:1.45}#pc-cookie-consent button{border:0;border-radius:8px;padding:10px 14px;cursor:pointer;font:600 13px Sora,Arial,sans-serif}#pc-cookie-consent .pc-cc-accept{background:#ED7624;color:#fff}#pc-cookie-consent .pc-cc-reject{background:#fff;color:#10214b}@media(max-width:640px){#pc-cookie-consent{display:block}#pc-cookie-consent .pc-cc-actions{margin-top:12px;display:flex;gap:8px}}';
  document.head.appendChild(style);
  var banner = document.createElement('aside'); banner.id = 'pc-cookie-consent'; banner.setAttribute('role', 'dialog'); banner.setAttribute('aria-label', 'Cookie consent');
  banner.innerHTML = '<p>We use essential cookies to run this site, and optional cookies for analytics.</p><div class="pc-cc-actions"><button class="pc-cc-accept" type="button">Accept</button><button class="pc-cc-reject" type="button">Reject non-essential</button></div>';
  function choose(status) { localStorage.setItem(key, status); banner.remove(); document.dispatchEvent(new CustomEvent('petcenter:cookieConsent', { detail: { status: status } })); }
  banner.querySelector('.pc-cc-accept').addEventListener('click', function () { choose('accepted'); });
  banner.querySelector('.pc-cc-reject').addEventListener('click', function () { choose('rejected'); });
  (document.body || document.documentElement).appendChild(banner);
}());
/* TASK-MANUS-06 END */
