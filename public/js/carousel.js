document.addEventListener('DOMContentLoaded', () => {
  const carouselHandler = new CarouselHandler();
  carouselHandler.init();
});

class CarouselHandler {
  constructor(options = {}) {
    this._carousels = document.querySelectorAll(".carousel");
    this.AUTO_SCROLL_DELAY = options.autoScrollDelay || 4000;

    this.carouselData = {};
  }

  init() {
    this._carousels.forEach((carousel, index) => {
      if (!carousel.id) {
        carousel.id = (Date.now() + index).toString(36).slice(-8);
      }
      const id = carousel.id;

      const inner = carousel.querySelector('.carousel__inner');
      const items = carousel.querySelectorAll('.carousel__item');
      const prevBtn = carousel.querySelector('.carousel__control--prev');
      const nextBtn = carousel.querySelector('.carousel__control--next');
      const indicatorsContainer = carousel.querySelector('.carousel__indicators');

      if (!inner || items.length === 0) return;

      const totalSlides = items.length;

      this.carouselData[id] = {
        element: carousel,
        inner: inner,
        items: items,
        indicators: [],
        currentIndex: 0,
        totalSlides: totalSlides,
        autoScrollInterval: null,
        touchStartX: 0
      };

      items[0].dataset.state = "active";

      this._initIndicators(id, indicatorsContainer);
      this._bindEvents(id, prevBtn, nextBtn);

      this._updateCarousel(id);
      this.startAutoScroll(id);
    });

    this._bindGlobalEvents();
  }

  _initIndicators(id, indicatorsContainer) {
    if (!indicatorsContainer) return;

    const data = this.carouselData[id];
    indicatorsContainer.innerHTML = '';

    for (let i = 0; i < data.totalSlides; i++) {
      const indicator = document.createElement('div');
      indicator.classList.add('carousel__indicator');
      if (i === 0) indicator.dataset.state = "active";

      indicator.addEventListener('click', () => this.goToSlide(id, i));

      indicatorsContainer.appendChild(indicator);
      data.indicators.push(indicator);
    }
  }

  _bindEvents(id, prevBtn, nextBtn) {
    const data = this.carouselData[id];
    const carouselEl = data.element;

    // Controls
    if (nextBtn) {
      nextBtn.addEventListener('click', () => this.goToSlide(id, data.currentIndex + 1));
    }
    if (prevBtn) {
      prevBtn.addEventListener('click', () => this.goToSlide(id, data.currentIndex - 1));
    }

    carouselEl.addEventListener('mouseenter', () => this.pauseAutoScroll(id));
    carouselEl.addEventListener('mouseleave', () => this.startAutoScroll(id));
    carouselEl.addEventListener('focusin', () => this.pauseAutoScroll(id));
    carouselEl.addEventListener('focusout', () => this.startAutoScroll(id));

    carouselEl.addEventListener('touchstart', e => {
      data.touchStartX = e.changedTouches[0].screenX;
      this.pauseAutoScroll(id);
    }, { passive: true });

    carouselEl.addEventListener('touchend', e => {
      const touchEndX = e.changedTouches[0].screenX;
      const diff = data.touchStartX - touchEndX;

      if (diff > 50) this.goToSlide(id, data.currentIndex + 1);
      if (diff < -50) this.goToSlide(id, data.currentIndex - 1);

      setTimeout(() => this.startAutoScroll(id), 1000);
    }, { passive: true });
  }

  _bindGlobalEvents() {
    // Tạm dừng khi tab bị ẩn
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        Object.keys(this.carouselData).forEach(id => this.pauseAutoScroll(id));
      } else {
        Object.keys(this.carouselData).forEach(id => this.startAutoScroll(id));
      }
    });
  }

  goToSlide(id, targetIndex) {
    const data = this.carouselData[id];
    data.currentIndex = (targetIndex + data.totalSlides) % data.totalSlides;
    this._updateCarousel(id);
  }

  _updateCarousel(id) {
    const data = this.carouselData[id];

    data.inner.style.transform = `translateX(${-data.currentIndex * 100}%)`;

    for (let i = 0; i < data.totalSlides; i++) {
      const state = (i === data.currentIndex) ? "active" : "idle";

      data.items[i].dataset.state = state;

      if (data.indicators[i]) {
        data.indicators[i].dataset.state = state;
      }
    }
  }

  startAutoScroll(id) {
    const data = this.carouselData[id];
    if (data.autoScrollInterval) return;

    data.autoScrollInterval = setInterval(() => {
      if (Utils.isInViewport(data.element, 100)) {
        this.goToSlide(id, data.currentIndex + 1);
      }
    }, this.AUTO_SCROLL_DELAY);
  }

  pauseAutoScroll(id) {
    const data = this.carouselData[id];
    if (data.autoScrollInterval) {
      clearInterval(data.autoScrollInterval);
      data.autoScrollInterval = null;
    }
  }
}