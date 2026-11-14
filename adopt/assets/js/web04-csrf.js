/* TASK-WEB-04 START — In-memory CSRF token attachment for authenticated same-origin mutations. */
(function (window, document) {
  'use strict';
  if (window.PetCenterCSRF) return;

  var originalFetch = window.fetch.bind(window);
  var csrfToken = null;
  var csrfPending = null;
  var protectedPrefixes = ['/rehoming/', '/account/', '/verify/'];

  function isProtectedMutation(input, init) {
    var request = input instanceof Request ? input : null;
    var url = new URL(request ? request.url : String(input), window.location.href);
    var method = String((init && init.method) || (request && request.method) || 'GET').toUpperCase();
    return url.origin === window.location.origin && !['GET', 'HEAD', 'OPTIONS'].includes(method)
      && protectedPrefixes.some(function (prefix) { return url.pathname.indexOf(prefix) === 0; });
  }

  function obtainToken() {
    if (csrfToken) return Promise.resolve(csrfToken);
    if (csrfPending) return csrfPending;
    csrfPending = originalFetch('/rehoming/auth.php?action=csrf', {
      method: 'GET',
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json' },
      cache: 'no-store'
    }).then(function (response) {
      return response.json().then(function (body) {
        if (!response.ok || !body || typeof body.csrf_token !== 'string' || body.csrf_token.length !== 64) {
          throw new Error((body && body.message) || 'This request could not be verified. Refresh the page and try again.');
        }
        csrfToken = body.csrf_token;
        return csrfToken;
      });
    }).finally(function () { csrfPending = null; });
    return csrfPending;
  }

  window.fetch = function (input, init) {
    if (!isProtectedMutation(input, init)) return originalFetch(input, init);
    return obtainToken().then(function (token) {
      var headers = new Headers(input instanceof Request ? input.headers : undefined);
      if (init && init.headers) new Headers(init.headers).forEach(function (value, key) { headers.set(key, value); });
      headers.set('X-CSRF-Token', token);
      var next = Object.assign({}, init || {}, { headers: headers, credentials: (init && init.credentials) || 'same-origin' });
      return originalFetch(input, next).then(function (response) {
        if (response.url.indexOf('/rehoming/auth.php?action=logout') !== -1 && response.ok) csrfToken = null;
        if (response.status === 403) csrfToken = null;
        return response;
      });
    });
  };

  window.PetCenterCSRF = {
    refresh: function () { csrfToken = null; return obtainToken(); },
    clear: function () { csrfToken = null; csrfPending = null; }
  };
})(window, document);
/* TASK-WEB-04 END — In-memory CSRF token attachment. */
