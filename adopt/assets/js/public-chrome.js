/* DESIGN-01 START: shared public navigation accessibility enhancer. */
(function () {
  'use strict';

  var menuButton = document.getElementById('pcHamburger');
  var mobileMenu = document.getElementById('pcMobileMenu');
  if (!menuButton || !mobileMenu) return;

  var managedMenu = menuButton.getAttribute('data-pc-managed-menu') === 'true';
  function closeMenu(restoreFocus) {
    mobileMenu.classList.remove('open');
    mobileMenu.style.display = '';
    menuButton.classList.remove('active');
    menuButton.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
    if (restoreFocus) menuButton.focus();
  }

  /* Pages that receive the static shared shell have no page-owned menu handler. */
  if (managedMenu) {
    menuButton.addEventListener('click', function (event) {
      event.stopPropagation();
      if (mobileMenu.classList.contains('open')) {
        closeMenu(false);
      } else {
        menuButton.classList.add('active');
        mobileMenu.classList.add('open');
        menuButton.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
      }
    });
    mobileMenu.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () { closeMenu(false); });
    });
    document.addEventListener('click', function (event) {
      if (!event.target.closest('#pcHeader') && !event.target.closest('#pcMobileMenu')) closeMenu(false);
    });
  }

  /* Escape closes either menu implementation and restores focus to its trigger. */
  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && mobileMenu.classList.contains('open')) closeMenu(true);
  });
})();
/* DESIGN-01 END: shared public navigation accessibility enhancer. */
