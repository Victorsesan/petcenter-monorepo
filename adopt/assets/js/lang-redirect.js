(function () {
  'use strict';
  var key = 'preferences';
  var map = { es: '/es/', pt: '/pt/', fr: '/fr/', ar: '/ar/', 'zh-Hans': '/zh/' };
  var lang = '';
  try { lang = JSON.parse(localStorage.getItem(key) || '{}').lang || ''; } catch (_) {}
  if (!map[lang] || location.pathname !== '/' || document.documentElement.dataset.langRedirectChecked) return;
  document.documentElement.dataset.langRedirectChecked = 'true';
  fetch(map[lang], { method: 'HEAD', credentials: 'same-origin' }).then(function (r) {
    if (r.ok) location.replace(map[lang]);
  }).catch(function () {});
}());
