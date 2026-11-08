/* DESIGN-01 START: shared public navigation accessibility enhancer
   Existing page handlers remain authoritative for menu open/close and scroll locking. */
(function () {
  'use strict';

  var menuButton = document.getElementById('pcHamburger');
  var mobileMenu = document.getElementById('pcMobileMenu');
  if (!menuButton || !mobileMenu) return;

  /* DESIGN-01 — return keyboard users to the menu control after Escape. */
  document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape' || !mobileMenu.classList.contains('open')) return;

    mobileMenu.classList.remove('open');
    mobileMenu.style.display = '';
    menuButton.classList.remove('active');
    menuButton.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
    menuButton.focus();
  });
})();
/* DESIGN-01 END: shared public navigation accessibility enhancer */
