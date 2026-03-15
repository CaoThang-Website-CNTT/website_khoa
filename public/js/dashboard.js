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

      // Kiểm tra có tồn tại dropdown không
      // tránh tình trạng thay đổi state nhưng không có dropdown
      if (targetId) {
        const currentState = this.getAttribute('data-state') || 'collapsed';
        const newState = currentState === 'collapsed' ? 'expanded' : 'collapsed';

        this.dataset.state = newState;
        this.dataset.ariaExpanded = newState === 'expanded';
      }
    });
  })
})();
