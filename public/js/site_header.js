(function () {
  function setExpanded(button, target, expanded) {
    if (!button || !target) return;

    button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    target.hidden = !expanded;
  }

  document.addEventListener('DOMContentLoaded', function () {
    const searchToggle = document.querySelector('[data-mobile-search-toggle]');
    const searchClose = document.querySelector('[data-mobile-search-close]');
    const searchPanel = document.querySelector('[data-mobile-search]');
    const searchInput = document.getElementById('mobile-search-input');
    const menuToggle = document.querySelector('[data-mobile-menu-toggle]');
    const menuPanel = document.querySelector('[data-mobile-menu]');

    function closeSearch() {
      setExpanded(searchToggle, searchPanel, false);
    }

    function closeMenu() {
      setExpanded(menuToggle, menuPanel, false);
    }

    searchToggle?.addEventListener('click', function () {
      const nextOpen = searchPanel?.hidden !== false;
      closeMenu();
      setExpanded(searchToggle, searchPanel, nextOpen);
      if (nextOpen) {
        requestAnimationFrame(function () {
          searchInput?.focus();
        });
      }
    });

    searchClose?.addEventListener('click', closeSearch);

    menuToggle?.addEventListener('click', function () {
      const nextOpen = menuPanel?.hidden !== false;
      closeSearch();
      setExpanded(menuToggle, menuPanel, nextOpen);
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        closeSearch();
        closeMenu();
      }
    });

    document.querySelectorAll('[data-mobile-submenu-toggle]').forEach(function (button) {
      const submenu = document.getElementById(button.getAttribute('aria-controls'));
      button.addEventListener('click', function () {
        const nextOpen = submenu?.hidden !== false;
        button.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
        if (submenu) submenu.hidden = !nextOpen;
      });
    });
  });
})();
