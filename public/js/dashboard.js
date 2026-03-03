(function () {
  /* ========= Preloader ======== */
  const preloader = document.querySelectorAll("#preloader");

  window.addEventListener("load", function () {
    if (preloader.length) {
      this.document.getElementById("preloader").style.display = "none";
    }
  });

  /* ========= Add Box Shadow in Header on Scroll ======== */
  window.addEventListener("scroll", function () {
    const header = document.querySelector(".header");
    if (window.scrollY > 0) {
      header.style.boxShadow = "0px 0px 30px 0px rgba(200, 208, 216, 0.30)";
    } else {
      header.style.boxShadow = "none";
    }
  });

  /* ========= sidebar toggle ======== */
  const sidebarNavWrapper = document.querySelector(".sidebar-nav-wrapper");
  const mainWrapper = document.querySelector(".main-wrapper");
  const menuToggleButton = document.querySelector("#menu-toggle");
  const menuToggleButtonIcon = document.querySelector("#menu-toggle svg path");
  const overlay = document.querySelector(".overlay");

  menuToggleButton.addEventListener("click", () => {
    sidebarNavWrapper.classList.toggle("active");
    overlay.classList.add("active");
    mainWrapper.classList.toggle("active");

    if (document.body.clientWidth > 1200) {
      if (menuToggleButtonIcon.getAttribute("d") === "M15 18L9 12L15 6") {
        menuToggleButtonIcon.setAttribute("d", "M9 18L15 12L9 6");
      } else {
        menuToggleButtonIcon.setAttribute("d", "M15 18L9 12L15 6");
      }
    } else {
      //TODO: đổi icon menu cho mobile
      if (menuToggleButtonIcon.classList.contains("lni-chevron-left")) {
        menuToggleButtonIcon.classList.remove("lni-chevron-left");
        menuToggleButtonIcon.classList.add("lni-menu");
      }
    }
  });
  overlay.addEventListener("click", () => {
    sidebarNavWrapper.classList.remove("active");
    overlay.classList.remove("active");
    mainWrapper.classList.remove("active");
  });
})();
