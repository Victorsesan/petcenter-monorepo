(function () {
  'use strict';
  var prefs = {};
  try { prefs = JSON.parse(localStorage.getItem('preferences') || '{}'); } catch (_) {}
  var lang = document.documentElement.lang || prefs.lang || 'en';
  if (!lang || lang === 'en') return;
  var folder = lang === 'zh-Hans' ? 'zh' : lang;
  var url = '/assets/i18n/' + folder + '/index.json';
  fetch(url, { credentials: 'same-origin' }).then(function (r) {
    if (!r.ok) return null;
    return r.json();
  }).then(function (catalog) {
    if (!catalog || typeof catalog !== 'object') return;
    document.querySelectorAll('[data-i18n]').forEach(function (el) {
      var value = catalog[el.getAttribute('data-i18n')];
      if (typeof value === 'string') el.textContent = value;
    });
    document.querySelectorAll('[data-i18n-attr]').forEach(function (el) {
      String(el.getAttribute('data-i18n-attr')).split(',').forEach(function (entry) {
        var parts = entry.split(':');
        var value = catalog[parts.slice(1).join(':')];
        if (parts[0] && typeof value === 'string') el.setAttribute(parts[0], value);
      });
    });
  }).catch(function () {});
}());
