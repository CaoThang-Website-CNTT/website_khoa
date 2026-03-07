(function () {
  /* ========= Add Box Shadow in Header on Scroll ======== */
  window.addEventListener("scroll", function () {
    const header = document.querySelector(".header");
    if (window.scrollY > 0) {
      header.classList.add("shadow");
    } else {
      header.classList.remove("shadow");
    }
  });

  /* ========= sidebar toggle ======== */
  const sidebar = document.querySelector("#sidebar");
  const mainWrapper = document.querySelector(".main-wrapper");
  const menuToggleButton = document.querySelector("#menu-toggle");

  menuToggleButton.addEventListener("click", () => {
    sidebar.classList.toggle("active");
    mainWrapper.classList.toggle("active");
  });

  // =========== COLLAPSE TOGGLE =============
  const collapseToggles = document.querySelectorAll('[data-toggle="collapse"]');

  collapseToggles.forEach(toggle => {
    toggle.addEventListener('click', function (e) {
      e.preventDefault();
      const targetId = this.getAttribute('data-target');
      if (targetId) {
        const target = document.querySelector(targetId);
        if (target) {
          target.classList.toggle('show');

          // Update aria-expanded for accessibility
          const isExpanded = target.classList.contains('show');
          this.setAttribute('aria-expanded', isExpanded);

          // Update toggle button color based on collapse state
          if (isExpanded) {
            this.classList.remove('collapsed');
            this.style.color = 'var(--primary)';
          } else {
            this.classList.add('collapsed');
            this.style.color = 'var(--muted-foreground)';
          }
        }
      }
    });
  })
})();
