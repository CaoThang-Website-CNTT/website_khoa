<?php
function renderMobileSearch(): void
{
  ?>
  <div class="mobile-search" id="site-mobile-search" data-mobile-search hidden>
    <div class="container px-4 pb-4">
      <div class="search-bar mobile-search__bar" data-variant="alt">
        <span class="search-bar__icon" aria-hidden="true">
          <i class="fa-solid fa-magnifying-glass"></i>
        </span>
        <input class="search-bar__input" id="mobile-search-input" placeholder="Tìm kiếm..." autocomplete="off"
          autocorrect="off">
        <button class="mobile-search__close" type="button" data-mobile-search-close aria-label="Đóng tìm kiếm">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>
  </div>
  <?php
}
