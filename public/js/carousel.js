document.addEventListener('DOMContentLoaded', function () {
  const inner = document.getElementById('carouselInner');
  const items = document.querySelectorAll('.carousel__item');
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const indicatorsContainer = document.getElementById('indicators');
  const carouselContainer = inner.parentElement;

  let currentIndex = 0;
  const totalSlides = items.length;
  let autoScrollInterval = null;
  const AUTO_SCROLL_DELAY = 4000;

  // INITIALIZE
  /**
   * Tạo các indicator cho từng slide
   */
  for (let i = 0; i < totalSlides; i++) {
    const indicator = document.createElement('div');
    indicator.classList.add('carousel__indicator');
    if (i === 0) indicator.classList.add('carousel__indicator--active');
    indicator.addEventListener('click', () => goToSlide(i));
    indicatorsContainer.appendChild(indicator);
  }

  const indicators = document.querySelectorAll('.carousel__indicator');

  // MAIN LOGIC
  /**
   * Cập nhật vị trí slide và trạng thái indicator
   */
  function updateCarousel() {
    inner.style.transform = `translateX(${-currentIndex * 100}%)`;
    indicators.forEach((ind, idx) => {
      ind.classList.toggle('carousel__indicator--active', idx === currentIndex);
    });
  }

  /**
   * Chuyển đến slide theo chỉ số
   * @param {number} index - Chỉ số slide cần chuyển đến
   */
  function goToSlide(index) {
    currentIndex = (index + totalSlides) % totalSlides;
    updateCarousel();
  }

  // CONTROLS
  // Nút điều hướng
  nextBtn.addEventListener('click', () => goToSlide(currentIndex + 1));
  prevBtn.addEventListener('click', () => goToSlide(currentIndex - 1));

  // Điều khiển bằng bàn phím (chỉ khi carousel trong viewport)
  // capture: event bubbling (child -> parent)
  document.addEventListener('keydown', e => {
    if (!isInViewport(carouselContainer, 100)) return;

    if (e.key === 'ArrowRight') {
      e.preventDefault();
      goToSlide(currentIndex + 1);
    }
    if (e.key === 'ArrowLeft') {
      e.preventDefault();
      goToSlide(currentIndex - 1);
    }
  }, { capture: true });

  // Điều khiển bằng thao tác cảm ứng
  // passive: tối ưu hiệu năng scroll
  let touchStartX = 0;
  inner.parentElement.addEventListener('touchstart', e => {
    touchStartX = e.changedTouches[0].screenX;
    pauseAutoScroll(); // Tạm dừng khi chạm
  }, { passive: true });

  inner.parentElement.addEventListener('touchend', e => {
    const touchEndX = e.changedTouches[0].screenX;
    if (touchStartX - touchEndX > 50) goToSlide(currentIndex + 1);
    if (touchEndX - touchStartX > 50) goToSlide(currentIndex - 1);
    setTimeout(startAutoScroll, 1000); // Tiếp tục sau 1s
  }, { passive: true });

  // AUTO SCROLL
  /**
   * Bắt đầu tự động chuyển slide
   */
  function startAutoScroll() {
    if (autoScrollInterval) return;
    autoScrollInterval = setInterval(() => {
      if (isInViewport(carouselContainer, 100)) {
        goToSlide(currentIndex + 1);
      }
    }, AUTO_SCROLL_DELAY);
  }

  /**
   * Tạm dừng tự động chuyển slide
   */
  function pauseAutoScroll() {
    if (autoScrollInterval) {
      clearInterval(autoScrollInterval);
      autoScrollInterval = null;
    }
  }

  /**
   * Cài đặt lại lại bộ đếm
   */
  function resetAutoScroll() {
    pauseAutoScroll();
    startAutoScroll();
  }

  // Tạm dừng khi di chuột vào / tập trung
  carouselContainer.addEventListener('mouseenter', pauseAutoScroll);
  carouselContainer.addEventListener('mouseleave', startAutoScroll);
  carouselContainer.addEventListener('focusin', pauseAutoScroll);
  carouselContainer.addEventListener('focusout', startAutoScroll);

  // Tạm dừng khi tab bị ẩn
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      pauseAutoScroll();
    } else {
      startAutoScroll();
    }
  });

  // START
  updateCarousel();
  startAutoScroll();
});