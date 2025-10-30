document.addEventListener('DOMContentLoaded', function () {
  const inner = document.getElementById('carouselInner');
  const items = document.querySelectorAll('.carousel__item');
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const indicatorsContainer = document.getElementById('indicators');

  let currentIndex = 0;
  const totalSlides = items.length;

  // Khởi tạo indicator
  for (let i = 0; i < totalSlides; i++) {
    const indicator = document.createElement('div');
    indicator.classList.add('carousel__indicator');
    if (i === 0) indicator.classList.add('carousel__indicator--active');
    indicator.addEventListener('click', () => goToSlide(i));
    indicatorsContainer.appendChild(indicator);
  }

  const indicators = document.querySelectorAll('.carousel__indicator');

  function updateCarousel() {
    inner.style.transform = `translateX(${-currentIndex * 100}%)`;
    indicators.forEach((ind, idx) => {
      ind.classList.toggle('carousel__indicator--active', idx === currentIndex);
    });
  }

  function goToSlide(index) {
    currentIndex = (index + totalSlides) % totalSlides;
    updateCarousel();
  }

  nextBtn.addEventListener('click', () => goToSlide(currentIndex + 1));
  prevBtn.addEventListener('click', () => goToSlide(currentIndex - 1));

  // Điều khiển bằng bàn phím
  document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowRight') goToSlide(currentIndex + 1);
    if (e.key === 'ArrowLeft') goToSlide(currentIndex - 1);
  });

  // Điều khiển bằng thao tác cảm ứng
  let touchStartX = 0;
  inner.parentElement.addEventListener('touchstart', e => {
    touchStartX = e.changedTouches[0].screenX;
  }, { passive: true });

  inner.parentElement.addEventListener('touchend', e => {
    const touchEndX = e.changedTouches[0].screenX;
    if (touchStartX - touchEndX > 50) goToSlide(currentIndex + 1);
    if (touchEndX - touchStartX > 50) goToSlide(currentIndex - 1);
  }, { passive: true });

  updateCarousel();
});