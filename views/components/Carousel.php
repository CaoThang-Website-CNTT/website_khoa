<?php

namespace App\Views\Components;

use App\Core\ViewComponent;

class Carousel extends ViewComponent
{
  private readonly array $slides;
  public function __construct(array $slides)
  {
    $this->slides = $slides;
  }
  public function render(): string
  {
    $slideHtml = '';
    foreach ($this->slides as $slide) {
      $slideHtml .= <<<HTML
        <div class="carousel__item flex justify-between items-center gap-8">
          <div class="carousel__content flex flex-col gap-4">
            <h2 class="carousel__title text-6xl font-normal">
              {$this->e($slide['title'])}
              <span class="text-6xl">{$this->e($slide['subtitle'])}</span>
            </h2>
            <p class="carousel__description">
              {$this->e($slide['description'])}
            </p>
            <div>
              <a href="#" class="carousel__btn px-8 py-2 rounded-lg text-sm font-medium">{$this->e($slide['button'])}</a>
            </div>
          </div>

          <div class="image-wrapper carousel__image-wrapper rounded-2xl">
            <img src="{$this->e($slide['image'])}" alt="{$this->e($slide['alt'])}" class="image carousel__image">
          </div>
        </div>
      HTML;
    }

    return <<<HTML
    <div class="carousel py-8 relative" id="learningCarousel">
      <div class="carousel__inner flex" id="carouselInner">
        {$slideHtml}
      </div>

      <!-- Controls -->
      <button class="carousel__control absolute rounded-full flex justify-center items-center carousel__control--prev" id="prevBtn">
        {$this->icon("chevron_left")}
      </button>
      <button class="carousel__control absolute rounded-full flex justify-center items-center carousel__control--next" id="nextBtn">
        {$this->icon("chevron_right")}
      </button>

      <!-- Indicators -->
      <div class="carousel__indicators z-10 flex justify-center gap-2" id="indicators"></div>

      <!-- Thêm Script -->
      <script src="{$this->asset('js/carousel.js')}"></script>
    </div>
    HTML;
  }
}
