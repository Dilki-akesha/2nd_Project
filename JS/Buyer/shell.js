/*
 * Harvestly Buyer shell behaviour.
 * Vanilla JavaScript only - no jQuery and no other library.
 */
(function () {
  'use strict';

  function initMobileNav() {
    var toggle = document.getElementById('buyerMenuToggle');
    if (!toggle) return;
    toggle.addEventListener('click', function () {
      var open = document.body.classList.toggle('buyer-nav-open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    // Close the panel when tapping the dimmed area behind it.
    document.addEventListener('click', function (event) {
      if (!document.body.classList.contains('buyer-nav-open')) return;
      if (event.target === document.body) {
        document.body.classList.remove('buyer-nav-open');
        toggle.setAttribute('aria-expanded', 'false');
      }
    });
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && document.body.classList.contains('buyer-nav-open')) {
        document.body.classList.remove('buyer-nav-open');
        toggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  /* Confirm before any destructive submit (delete buttons use data-confirm). */
  function initConfirmations() {
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!window.confirm(form.getAttribute('data-confirm'))) {
          event.preventDefault();
        }
      });
    });
  }

  /* Show the chosen file name next to any optional evidence upload. */
  function initFileNames() {
    document.querySelectorAll('input[type="file"][data-file-target]').forEach(function (input) {
      var output = document.getElementById(input.getAttribute('data-file-target'));
      if (!output) return;
      input.addEventListener('change', function () {
        output.textContent = input.files && input.files.length ? input.files[0].name : '';
      });
    });
  }

  /* Stop accidental double submission of order / review forms. */
  function initSubmitGuard() {
    document.querySelectorAll('form[data-once]').forEach(function (form) {
      form.addEventListener('submit', function () {
        var button = form.querySelector('button[type="submit"]');
        if (button) {
          button.disabled = true;
          button.textContent = 'Working...';
        }
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initMobileNav();
    initConfirmations();
    initFileNames();
    initSubmitGuard();
  });
})();
