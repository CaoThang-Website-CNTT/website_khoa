document.addEventListener("DOMContentLoaded", () => {
  const scrollToTopBtn = document.getElementById("scrollToTopBtn");

  if (!scrollToTopBtn) return;

  const toggleVisibility = () => {
    if (window.scrollY > 300) {
      scrollToTopBtn.classList.add("is-visible");
    } else {
      scrollToTopBtn.classList.remove("is-visible");
    }
  };

  window.addEventListener("scroll", toggleVisibility);

  toggleVisibility();

  scrollToTopBtn.addEventListener("click", () => {
    window.scrollTo({
      top: 0,
      behavior: "smooth",
    });
  });
});
