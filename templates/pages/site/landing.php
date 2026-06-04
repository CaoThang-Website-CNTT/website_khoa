<?php
function renderCarousel(array $carouselSlides): void
{
  if (empty($carouselSlides)) {
    return;
  }
  ?>
  <div class="carousel py-8" id="landingCarousel">
    <div class="carousel__inner" id="carouselInner">

      <?php foreach ($carouselSlides as $slide): ?>
        <?php
        // Bỏ qua các slide không được kích hoạt
        if (!$slide->isActive())
          continue;
        ?>

        <div class="carousel__item flex justify-between items-center gap-8">

          <?php if ($slide->isCustom()): ?>
            <?= $slide->custom_html ?>
          <?php else: ?>

            <div class="carousel__content flex flex-col gap-6">
              <h2 class="carousel__title text-6xl font-normal">
                <?= htmlspecialchars($slide->title) ?>
                <?php if (!empty($slide->title_highlight)): ?>
                  <span class="text-6xl">
                    <?= htmlspecialchars($slide->title_highlight) ?>
                  </span>
                <?php endif; ?>
              </h2>

              <?php if (!empty($slide->description)): ?>
                <p class="carousel__description">
                  <?= nl2br(htmlspecialchars($slide->description)) ?>
                </p>
              <?php endif; ?>

              <?php if ($slide->hasCta()): ?>
                <div>
                  <a href="<?= htmlspecialchars(url($slide->cta_url)) ?>"
                    data-variant="<?= htmlspecialchars($slide->cta_variant) ?>" class="btn px-8 py-2 rounded-3xl bouncy-btn">
                    <?= htmlspecialchars($slide->cta_label) ?>
                  </a>
                </div>
              <?php endif; ?>
            </div>

            <div class="image-wrapper carousel__image-wrapper rounded-3xl">
              <img src="<?= htmlspecialchars(get_media_url($slide->media->file_path)) ?>"
                alt="<?= htmlspecialchars($slide->media->alt_text ?: $slide->title) ?>" class="image carousel__image">
            </div>

          <?php endif; ?>
        </div>
      <?php endforeach; ?>

    </div>

    <button class="carousel__control carousel__control--prev">
      <i class="fa-solid fa-angle-left"></i>
    </button>
    <button class="carousel__control carousel__control--next">
      <i class="fa-solid fa-angle-right"></i>
    </button>

    <div class="carousel__indicators">
    </div>
  </div>
  <?php
}
?>
<!-- HERO-SECTION: START -->
<section class="relative" id="hero-section">
  <div class="container">
    <?php renderCarousel($carouselSlides); ?>
  </div>
  <!-- Wave -->
  <div class="wave-container">
    <svg class="wave" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 60" preserveAspectRatio="none" fill="none">
      <path
        d="M0 60L48 48.3333C96 38.6667 192 19.3333 288 9.66667C384 0 480 0 576 4.83333C672 9.66667 768 19.3333 864 24.1667C960 29 1056 29 1152 24.1667C1248 19.3333 1344 9.66667 1392 4.83333L1440 0V60H1392C1344 60 1248 60 1152 60C1056 60 960 60 864 60C768 60 672 60 576 60C480 60 384 60 288 60C192 60 96 60 48 60H0Z"
        fill="currentColor"></path>
    </svg>
  </div>
</section>
<!-- HERO-SECTION: END -->

<!-- ABOUT-SECTION: START -->
<section class="relative container py-16" id="about-section">
  <h2 class="sr-only">About Us</h2>
  <div class="container-wrapper">
    <div class="flex flex-col justify-center items-center gap-4 mb-12"></div>
    <div class="about-container flex flex-col">
      <div class="about-item flex gap-12 flex-row">
        <div class="relative">
          <div class="about-item__image-container overflow-hidden rounded-3xl">
            <div class="image-wrapper">
              <img class="image w-full h-full object-fit" src="./public/img/about.jpg" alt="Lecture hall with students">
            </div>
          </div>
          <div class="about-item__card absolute z-10 rounded-3xl p-6 flex flex-col gap-1">
            <div class="about-item__card-main-content text-5xl">Top 1</div>
            <div class="about-item__card-sub-content text-sm">
              Khoa CNTT tại Miền Nam (So với các Cao Đẳng khác)
            </div>
          </div>
        </div>
        <div class="about-item__content-container flex flex-col justify-center gap-4">
          <p class="number-of-text text-7xl">01</p>
          <p class="about-item__sub-title text-xs uppercase font-medium">
            LOREM ISPUM GÌ ĐÓ Ở ĐÂY
          </p>
          <p class="about-item__title text-4xl">
            Đảm bảo chất lượng đào tạo
          </p>
          <p class="about-item__content">
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris
            sed leo et neque vehicula lacinia vel at lorem. Lorem ipsum
            dolor sit amet, consectetur adipiscing elit. Mauris sed leo et
            neque vehicula lacinia vel at lorem. Lorem ipsum dolor sit amet,
            consectetur adipiscing elit. Mauris sed leo et neque vehicula
            lacinia vel at lorem. Lorem ipsum dolor sit amet, consectetur
            adipiscing elit.
          </p>
        </div>
      </div>
      <div class="about-item flex gap-12 flex-row-reverse">
        <div class="relative">
          <div class="about-item__image-container overflow-hidden rounded-3xl">
            <div class="image-wrapper">
              <img class="image w-full h-full object-fit" src="./public/img/about.jpg" alt="Lecture hall with students">
            </div>
          </div>
          <div class="about-item__card absolute z-10 rounded-3xl p-6 flex flex-col gap-1">
            <div class="about-item__card-main-content text-5xl">98%</div>
            <div class="about-item__card-sub-content text-sm">
              Tỷ lệ có việc làm
            </div>
          </div>
        </div>
        <div class="about-item__content-container flex flex-col justify-center gap-4">
          <p class="number-of-text text-7xl">02</p>
          <p class="about-item__sub-title text-xs uppercase font-medium">
            LOREM ISPUM GÌ ĐÓ Ở ĐÂY
          </p>
          <p class="about-item__title text-4xl">Cơ hội Nghề nghiệp</p>
          <p class="about-item__content">
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris
            sed leo et neque vehicula lacinia vel at lorem. Lorem ipsum
            dolor sit amet, consectetur adipiscing elit. Mauris sed leo et
            neque vehicula lacinia vel at lorem. Lorem ipsum dolor sit amet,
            consectetur adipiscing elit. Mauris sed leo et neque vehicula
            lacinia vel at lorem. Lorem ipsum dolor sit amet, consectetur
            adipiscing elit.
          </p>
        </div>
      </div>
      <div class="about-item flex gap-12 flex-row">
        <div class="relative">
          <div class="about-item__image-container overflow-hidden rounded-3xl">
            <div class="image-wrapper">
              <img class="image w-full h-full object-fit" src="./public/img/about.jpg" alt="Lecture hall with students">
            </div>
          </div>
          <div class="about-item__card absolute z-10 rounded-3xl p-6 flex flex-col gap-1">
            <div class="about-item__card-main-content text-5xl">50+</div>
            <div class="about-item__card-sub-content text-sm">
              Doanh nghiệp
            </div>
          </div>
        </div>
        <div class="about-item__content-container flex flex-col justify-center gap-4">
          <p class="number-of-text text-7xl">03</p>
          <p class="about-item__sub-title text-xs uppercase font-medium">
            LOREM ISPUM GÌ ĐÓ Ở ĐÂY
          </p>
          <p class="about-item__title text-4xl">Nghiên cứu Đột phá</p>
          <p class="about-item__content">
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris
            sed leo et neque vehicula lacinia vel at lorem. Lorem ipsum
            dolor sit amet, consectetur adipiscing elit. Mauris sed leo et
            neque vehicula lacinia vel at lorem. Lorem ipsum dolor sit amet,
            consectetur adipiscing elit. Mauris sed leo et neque vehicula
            lacinia vel at lorem. Lorem ipsum dolor sit amet, consectetur
            adipiscing elit.
          </p>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- ABOUT-SECTION: END -->

<!-- WHY-CHOOSE-US-SECTION: START -->
<section class="wcu relative container py-16" id="why-choose-us-section">
  <div class="wcu__container container-wrapper">
    <div class="wcu__header flex flex-col justify-center items-center gap-4 mb-12">
      <div class="wcu__badge section__badge px-4 py-2 rounded-3xl text-sm mb-4">
        Tại sao chọn chúng tôi
      </div>

      <h2 class="wcu__title section__title text-4xl font-semibold">
        Trải nghiệm Khoa CNTT Cao Thắng
      </h2>

      <p class="wcu__subtitle section__sub-title font-normal text-base">
        Nơi ươm mầm tài năng công nghệ thông tin, kết nối tri thức với thực
        tiễn
      </p>
    </div>

    <div class="wcu__content flex flex-col items-center justify-center">

      <div class="wcu__features-grid grid grid-cols-3 grid-rows-2 gap-6 mb-6 self-stretch">

        <div
          class="wcu__feature-card wcu__feature-card--large wcu-feature-container overflow-hidden relative col-span-2 row-span-2 rounded-3xl image-wrapper">
          <img class="wcu__feature-card-image image"
            src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?..." alt="Trường Cao Thắng">
          <div class="wcu__feature-card-content absolute inset-0 flex flex-col justify-end items-start p-8">
            <span class="wcu__feature-card-badge badge mb-3" data-variant="primary">
              Nổi bật
            </span>
            <h3 class="wcu__feature-card-title text-3xl font-semibold mb-4">
              Môi trường học tập hiện đại, sáng tạo
            </h3>
            <p class="wcu__feature-card-description text-base font-normal mb-4">
              Trang bị phòng lab tiêu chuẩn quốc tế, thư viện số phong phú,
              không gian làm việc nhóm linh hoạt và hệ thống học tập trực
              tuyến tiên tiến.
            </p>
            <a href="#" class="wcu__feature-card-link text-base font-normal">
              Khám phá ngay
              <i class="fa-solid fa-arrow-up-right-from-square"></i>
            </a>
          </div>
        </div>

        <div
          class="wcu__stat-card wcu__stat-card--primary col-start-3 row-start-1 rounded-3xl p-8 flex flex-col gap-2 justify-center">
          <h2 class="wcu__stat-card-number text-7xl font-bold">20</h2>
          <p class="wcu__stat-card-title text-xl font-semibold">
            Năm kinh nghiệm
          </p>
          <p class="wcu__stat-card-description text-base font-normal">
            Tiên phong trong đào tạo CNTT chất lượng cao tại TP.HCM từ năm
            2003
          </p>
        </div>

        <div
          class="wcu__stat-card wcu__stat-card--gradient col-start-3 row-start-2 rounded-3xl p-8 flex flex-col gap-2 justify-center">
          <h2 class="wcu__stat-card-number text-7xl font-bold">95%</h2>
          <p class="wcu__stat-card-title text-xl font-semibold">Tỷ lệ việc làm</p>
          <p class="wcu__stat-card-description text-base font-normal">
            Sinh viên có việc làm trong vòng 6 tháng sau tốt nghiệp
          </p>
        </div>
      </div>

      <div class="wcu__perks-list flex justify-center items-stretch self-stretch gap-6 mb-6">

        <div class="wcu__perk-item flex flex-col items-start justify-start flex-1 rounded-3xl p-6">
          <div class="wcu__perk-item-icon-wrapper flex justify-center items-center rounded-full text-4xl mb-4 p-3">
            <i class="fa-solid fa-code wcu__perk-item-icon"></i>
          </div>
          <h4 class="wcu__perk-item-title text-base font-semibold mb-2">
            Công nghệ tiên tiến
          </h4>
          <p class="wcu__perk-item-description text-sm font-normal">
            Học tập với các công nghệ mới nhất: AI, Cloud, Blockchain, IoT
          </p>
        </div>

        <div class="wcu__perk-item flex flex-col items-start justify-start flex-1 rounded-3xl p-6">
          <div class="wcu__perk-item-icon-wrapper flex justify-center items-center rounded-full text-4xl mb-4 p-3">
            <i class="fa-solid fa-user-group wcu__perk-item-icon"></i>
          </div>
          <h4 class="wcu__perk-item-title text-base font-semibold text-foreground mb-2">
            Cộng đồng Mạnh mẽ
          </h4>
          <p class="wcu__perk-item-description text-sm font-normal">
            Kết nối với 10,000+ sinh viên và cựu sinh viên trên toàn quốc
          </p>
        </div>

        <div class="wcu__perk-item flex flex-col items-start justify-start flex-1 rounded-3xl p-6">
          <div class="wcu__perk-item-icon-wrapper flex justify-center items-center rounded-full text-4xl mb-4 p-3">
            <i class="fa-solid fa-award wcu__perk-item-icon"></i>
          </div>
          <h4 class="wcu__perk-item-title text-base font-semibold text-foreground mb-2">
            Chất lượng Quốc tế
          </h4>
          <p class="wcu__perk-item-description text-sm font-normal">
            Chương trình đạt chuẩn ABET và kiểm định quốc tế
          </p>
        </div>

        <div class="wcu__perk-item flex flex-col items-start justify-start flex-1 rounded-3xl p-6">
          <div class="wcu__perk-item-icon-wrapper flex justify-center items-center rounded-full text-4xl mb-4 p-3">
            <i class="fa-solid fa-rocket wcu__perk-item-icon"></i>
          </div>
          <h4 class="wcu__perk-item-title text-base font-semibold text-foreground mb-2">
            Khởi nghiệp
          </h4>
          <p class="wcu__perk-item-description text-sm font-normal">
            Hỗ trợ ý tưởng startup và kết nối nhà đầu tư
          </p>
        </div>
      </div>

      <div class="wcu__highlights-list flex justify-center items-stretch self-stretch gap-6">

        <div class="wcu__highlight-item flex-1 overflow-hidden relative rounded-3xl image-wrapper text-white">
          <img class="wcu__highlight-item-image image object-fit"
            src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?..." alt="Trường Cao Thắng">
          <div
            class="wcu__highlight-item-content wcu__highlight-item-content--blue absolute inset-0 flex flex-col justify-end items-start p-6">
            <h3 class="wcu__highlight-item-title text-2xl font-semibold mb-2">
              Nghiên cứu &amp; Phát triển
            </h3>
            <p class="wcu__highlight-item-description text-sm font-normal">
              Tham gia các dự án nghiên cứu thực tế cùng giảng viên
            </p>
          </div>
        </div>

        <div class="wcu__highlight-item flex-1 overflow-hidden relative rounded-3xl image-wrapper text-white">
          <img class="wcu__highlight-item-image image object-fit"
            src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?..." alt="Trường Cao Thắng">
          <div
            class="wcu__highlight-item-content wcu__highlight-item-content--green absolute inset-0 flex flex-col justify-end items-start p-6">
            <h3 class="wcu__highlight-item-title text-2xl font-semibold mb-2">
              Hợp tác Quốc tế
            </h3>
            <p class="wcu__highlight-item-description text-sm font-normal">
              Cơ hội trao đổi sinh viên và học bổng du học
            </p>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>
<!-- WHY-CHOOSE-US-SECTION: END -->

<!-- STATS-SECTION: START -->
<section class="relative container py-16" id="stats-section">
  <div class="container-wrapper">
    <div class="flex flex-col justify-center items-center gap-4 mb-12">

      <h2 class="section__title text-4xl font-semibold">
        Khoa CNTT Cao Thắng
      </h2>

      <p class="section__sub-title font-normal text-base">
        Định hình tương lai công nghệ thông tin Việt Nam
      </p>
    </div>
    <div class="flex flex-col items-stretch justify-center gap-6">
      <div class="stats__grid flex gap-6">
        <div class="stats__stat-card flex flex-1 flex-col items-center gap-6 rounded-3xl p-8">
          <div class="stats__stat-card-icon-wrapper flex items-center justify-center rounded-full">
            <i class="fa-solid fa-award stats__stat-card-icon"></i>
          </div>
          <div class="flex flex-col gap-1 items-center">
            <h3 class="stats__stat-card-number text-5xl font-bold">50+</h3>
            <h4 class="stats__stat-card-label font-semibold">Giải thưởng</h4>
            <p class="stats__stat-card-description text-sm">
              Trong các cuộc thi lập trình
            </p>
          </div>
        </div>
        <div class="stats__stat-card flex flex-1 flex-col items-center gap-6 rounded-3xl p-8">
          <div class="stats__stat-card-icon-wrapper flex items-center justify-center rounded-full">
            <i class="fa-solid fa-graduation-cap stats__stat-card-icon"></i>
          </div>
          <div class="flex flex-col gap-1 items-center">
            <h3 class="stats__stat-card-number text-5xl font-bold">10K+</h3>
            <h4 class="stats__stat-card-label font-semibold">Sinh viên</h4>
            <p class="stats__stat-card-description text-sm">Tốt nghiệp thành công</p>
          </div>
        </div>
        <div class="stats__stat-card flex flex-1 flex-col items-center gap-6 rounded-3xl p-8">
          <div class="stats__stat-card-icon-wrapper flex items-center justify-center rounded-full">
            <i class="fa-solid fa-briefcase stats__stat-card-icon"></i>
          </div>
          <div class="flex flex-col gap-1 items-center">
            <h3 class="stats__stat-card-number text-5xl font-bold">95%</h3>
            <h4 class="stats__stat-card-label font-semibold">Việc làm</h4>
            <p class="stats__stat-card-description text-sm">Sau 6 tháng tốt nghiệp</p>
          </div>
        </div>
        <div class="stats__stat-card flex flex-1 flex-col items-center gap-6 rounded-3xl p-8">
          <div class="stats__stat-card-icon-wrapper flex items-center justify-center rounded-full">
            <i class="fa-solid fa-earth-americas stats__stat-card-icon"></i>
          </div>
          <div class="flex flex-col gap-1 items-center">
            <h3 class="stats__stat-card-number text-5xl font-bold">20+</h3>
            <h4 class="stats__stat-card-label font-semibold">Quốc gia</h4>
            <p class="stats__stat-card-description text-sm">Hợp tác quốc tế</p>
          </div>
        </div>
      </div>
      <div class="stats__benefits-grid flex gap-6 items-stretch">
        <div class="stats__benefit-card flex-1 flex flex-col gap-6 p-8 rounded-3xl">
          <div class="stats__benefit-card-header flex gap-4 items-center">
            <div class="stats__benefit-card-icon-wrapper flex justify-center items-center rounded-full">
              <i class="fa-solid fa-building-columns stats__benefit-card-icon"></i>
            </div>
            <h3 class="stats__benefit-card-title text-2xl font-semibold">
              Chương trình Đào tạo Tiên tiến
            </h3>
          </div>
          <ul class="stats__benefit-card-list flex flex-col gap-4">
            <li class="stats__benefit-card-item flex items-center gap-2">
              <span class="stats__benefit-card-item-icon rounded-full"></span>
              <p class="stats__benefit-card-item-text">
                Cập nhật theo công nghệ mới nhất
              </p>
            </li>
            <li class="stats__benefit-card-item flex items-center gap-2">
              <span class="stats__benefit-card-item-icon rounded-full"></span>
              <p class="stats__benefit-card-item-text">
                Tích hợp chứng chỉ quốc tế
              </p>
            </li>
            <li class="stats__benefit-card-item flex items-center gap-2">
              <span class="stats__benefit-card-item-icon rounded-full"></span>
              <p class="stats__benefit-card-item-text">
                Thực hành dự án thực tế
              </p>
            </li>
            <li class="stats__benefit-card-item flex items-center gap-2">
              <span class="stats__benefit-card-item-icon rounded-full"></span>
              <p class="stats__benefit-card-item-text">
                Đào tạo kỹ năng mềm
              </p>
            </li>
          </ul>
        </div>

        <div class="stats__benefit-card flex-1 flex flex-col gap-6 p-8 rounded-3xl">
          <div class="stats__benefit-card-header flex gap-4 items-center">
            <div class="stats__benefit-card-icon-wrapper flex justify-center items-center rounded-full">
              <i class="fa-solid fa-arrow-trend-up stats__benefit-card-icon"></i>
            </div>
            <h3 class="stats__benefit-card-title text-2xl font-semibold">
              Phát triển Nghề nghiệp
            </h3>
          </div>
          <ul class="stats__benefit-card-list flex flex-col gap-4">
            <li class="stats__benefit-card-item flex items-center gap-2">
              <span class="stats__benefit-card-item-icon rounded-full"></span>
              <p class="stats__benefit-card-item-text">
                Kết nối với 100+ doanh nghiệp
              </p>
            </li>
            <li class="stats__benefit-card-item flex items-center gap-2">
              <span class="stats__benefit-card-item-icon rounded-full"></span>
              <p class="stats__benefit-card-item-text">
                Thực tập tại công ty hàng đầu
              </p>
            </li>
            <li class="stats__benefit-card-item flex items-center gap-2">
              <span class="stats__benefit-card-item-icon rounded-full"></span>
              <p class="stats__benefit-card-item-text">
                Tư vấn định hướng nghề nghiệp
              </p>
            </li>
            <li class="stats__benefit-card-item flex items-center gap-2">
              <span class="stats__benefit-card-item-icon rounded-full"></span>
              <p class="stats__benefit-card-item-text">
                Cơ hội việc làm cao
              </p>
            </li>
          </ul>
        </div>
      </div>
      <div class="stats__cta flex flex-col items-center p-12 rounded-3xl">
        <h3 class="stats__cta-title text-center text-3xl font-semibold mb-2">
          Sẵn sàng bắt đầu hành trình của bạn?
        </h3>
        <p class="stats__cta-description text-center text-xl font-light mb-6">
          Gia nhập cộng đồng hơn 10,000 sinh viên và cựu sinh viên đang
          làm việc tại các công ty công nghệ hàng đầu
        </p>
        <div class="stats__cta-buttons flex gap-4">
          <a href="#" data-variant="outline-alt"
            class="stats__cta-button stats__cta-button--primary flex items-center px-8 py-4 btn bouncy-btn rounded-full">
            Đăng ký tư vấn
          </a>
          <a href="#" data-variant="outline"
            class="stats__cta-button stats__cta-button--secondary flex items-center px-8 py-4 btn bouncy-btn rounded-full bg-transparent">
            Xem chương trình đào tạo
          </a>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- STATS-SECTION: END -->

<!-- NEWSFEED-SECTION: START -->
<section class="newsfeed relative container py-16" id="newsfeed-section">
  <div class="newsfeed__container container-wrapper">

    <div class="newsfeed__header flex flex-col justify-center items-center gap-4 mb-12">
      <h2 class="newsfeed__title section__title text-4xl font-semibold text-center">
        Tin tức &amp; Sự kiện
      </h2>
      <p class="newsfeed__subtitle section__sub-title font-normal text-base text-center">
        Cập nhật những tin tức mới nhất về hoạt động của khoa, thành tích
        sinh viên và các sự kiện sắp tới
      </p>
    </div>

    <div class="newsfeed__content-wrapper container-wrapper flex flex-col gap-16">

      <div id="featured-news" class="newsfeed__featured-group flex flex-col gap-6">

        <article class="news-card news-card--featured relative overflow-hidden rounded-3xl">
          <div class="news-card__image-wrapper image-wrapper">
            <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?..."
              alt="This is an image of a student won a tournament"
              class="news-card__image absolute w-full h-full object-cover image">
            <div class="news-card__content absolute inset-0 flex flex-col justify-end items-start p-8">
              <div class="news-card__meta mb-2 flex items-center gap-2">
                <span class="news-card__tag badge text-sm px-3">
                  Nổi bật
                </span>
                <span class="news-card__date flex items-center gap-1 text-base">
                  <i class="fa-regular fa-calendar news-card__date-icon"></i>
                  15/01/2025
                </span>
              </div>
              <h3 class="news-card__title text-4xl font-medium mb-2">
                Sinh viên khoa giành giải Nhất cuộc thi Olympic Tin Học 2025
              </h3>
              <p class="news-card__description font-light text-xl mb-6">
                Cuộc thi giữa 60 thí sinh cuối cùng trong trận chung kết để
                đạt được danh hiệu cao quý
              </p>
              <a href="#" data-variant="outline-alt"
                class="news-card__link flex items-center gap-2 text-base px-4 py-2 btn bouncy-btn rounded-full">
                Đọc thêm
              </a>
            </div>
          </div>
        </article>

        <div class="newsfeed__secondary-grid flex gap-6 justify-center items-stretch self-stretch">

          <a href="#" class="news-card news-card--secondary flex-1 overflow-hidden relative rounded-3xl">
            <div class="news-card__image-wrapper image-wrapper">
              <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?..."
                alt="This is an image of a student won a tournament"
                class="news-card__image absolute w-full h-full object-cover image">
              <div class="news-card__content absolute inset-0 flex flex-col justify-end items-start p-4">
                <div class="news-card__meta mb-2 flex items-center gap-2">
                  <span class="news-card__tag badge text-xs">
                    Thành tích
                  </span>
                  <span class="news-card__date flex items-center gap-1 text-sm">
                    <i class="fa-regular fa-calendar news-card__date-icon"></i>
                    15/01/2025
                  </span>
                </div>
                <h3 class="news-card__title text-xl font-semibold mb-2">
                  Sinh viên khoa giành giải Nhất cuộc thi Olympic Tin Học
                  2025
                </h3>
                <p class="news-card__description font-light text-sm">
                  Cuộc thi giữa 60 thí sinh cuối cùng trong trận chung kết
                  để đạt được danh hiệu cao quý
                </p>
              </div>
            </div>
          </a>

          <a href="#" class="news-card news-card--secondary flex-1 overflow-hidden relative rounded-3xl">
            <div class="news-card__image-wrapper image-wrapper">
              <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?..."
                alt="This is an image of a student won a tournament"
                class="news-card__image absolute w-full h-full object-cover image">
              <div class="news-card__content absolute inset-0 flex flex-col justify-end items-start p-4">
                <div class="news-card__meta mb-2 flex items-center gap-2">
                  <span class="news-card__tag badge text-xs">
                    Thành tích
                  </span>
                  <span class="news-card__date flex items-center gap-1 text-sm">
                    <i class="fa-regular fa-calendar news-card__date-icon"></i>
                    15/01/2025
                  </span>
                </div>
                <h3 class="news-card__title text-xl font-semibold mb-2">
                  Sinh viên khoa giành giải Nhất cuộc thi Olympic Tin Học
                  2025
                </h3>
                <p class="news-card__description font-light text-sm">
                  Cuộc thi giữa 60 thí sinh cuối cùng trong trận chung kết
                  để đạt được danh hiệu cao quý
                </p>
              </div>
            </div>
          </a>

          <a href="#" class="news-card news-card--secondary flex-1 overflow-hidden relative rounded-3xl">
            <div class="news-card__image-wrapper image-wrapper">
              <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?..."
                alt="This is an image of a student won a tournament"
                class="news-card__image absolute w-full h-full object-cover image">
              <div class="news-card__content absolute inset-0 flex flex-col justify-end items-start p-4">
                <div class="news-card__meta mb-2 flex items-center gap-2">
                  <span class="news-card__tag badge text-xs">
                    Thành tích
                  </span>
                  <span class="news-card__date flex items-center gap-1 text-sm">
                    <i class="fa-regular fa-calendar news-card__date-icon"></i>
                    15/01/2025
                  </span>
                </div>
                <h3 class="news-card__title text-white text-xl font-semibold mb-2">
                  Sinh viên khoa giành giải Nhất cuộc thi Olympic Tin Học
                  2025
                </h3>
                <p class="news-card__description font-light text-sm">
                  Cuộc thi giữa 60 thí sinh cuối cùng trong trận chung kết
                  để đạt được danh hiệu cao quý
                </p>
              </div>
            </div>
          </a>
        </div>
      </div>

      <div id="newsfeed-other" class="flex flex-col gap-4">
        <div class="newsfeed__other-header flex">
          <h2 class="newsfeed__other-title text-4xl font-medium flex-1">
            Tin tức khác
          </h2>
          <a href="#"
            class="newsfeed__view-all-link flex items-center gap-1 text-base font-medium link-hover--underline">
            Xem thêm
            <svg class="newsfeed__view-all-icon" aria-hidden="true" width="20" height="20" viewBox="0 0 20 20"
              fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M5.83334 5.83325H14.1667V14.1666" stroke="currentColor" stroke-width="1.66667"
                stroke-linecap="round" stroke-linejoin="round"></path>
              <path d="M5.83334 14.1666L14.1667 5.83325" stroke="currentColor" stroke-width="1.66667"
                stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
          </a>
        </div>

        <div class="newsfeed__other-grid flex gap-6 justify-center items-stretch self-stretch">

          <a href="#" class="news-card news-card--secondary flex-1 overflow-hidden relative rounded-3xl">
            <div class="news-card__image-wrapper image-wrapper">
              <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?..."
                alt="This is an image of a student won a tournament"
                class="news-card__image absolute w-full h-full object-cover image">
              <div class="news-card__content absolute inset-0 flex flex-col justify-end items-start p-4">
                <div class="news-card__meta mb-2 flex items-center gap-2">
                  <span class="news-card__tag badge text-xs">
                    Thành tích
                  </span>
                  <span class="news-card__date flex items-center gap-1 text-sm">
                    <i class="fa-regular fa-calendar news-card__date-icon"></i>
                    15/01/2025
                  </span>
                </div>
                <h3 class="news-card__title text-xl font-semibold mb-2">
                  Sinh viên khoa giành giải Nhất cuộc thi Olympic Tin Học 2025
                </h3>
                <p class="news-card__description font-light text-sm">
                  Cuộc thi giữa 60 thí sinh cuối cùng trong trận chung kết để
                  đạt được danh hiệu cao quý
                </p>
              </div>
            </div>
          </a>

          <a href="#" class="news-card news-card--secondary flex-1 overflow-hidden relative rounded-3xl">
            <div class="news-card__image-wrapper image-wrapper">
              <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?..."
                alt="This is an image of a student won a tournament"
                class="news-card__image absolute w-full h-full object-cover image">
              <div class="news-card__content absolute inset-0 flex flex-col justify-end items-start p-4">
                <div class="news-card__meta mb-2 flex items-center gap-2">
                  <span class="news-card__tag badge text-xs">
                    Thành tích
                  </span>
                  <span class="news-card__date flex items-center gap-1 text-sm">
                    <i class="fa-regular fa-calendar news-card__date-icon"></i>
                    15/01/2025
                  </span>
                </div>
                <h3 class="news-card__title news__item--title text-xl font-semibold mb-2">
                  Sinh viên khoa giành giải Nhất cuộc thi Olympic Tin Học 2024
                </h3>
                <p class="news-card__description font-light news__item--sub_title text-sm font-normal">
                  Cuộc thi giữa 60 thí sinh cuối cùng trong trận chung kết để
                  đạt được danh hiệu
                </p>
              </div>
            </div>
          </a>

          <a href="#" class="news-card news-card--secondary flex-1 overflow-hidden relative rounded-3xl">
            <div class="news-card__image-wrapper image-wrapper">
              <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?..."
                alt="This is an image of a student won a tournament"
                class="news-card__image absolute w-full h-full object-cover image">
              <div class="news-card__content absolute inset-0 flex flex-col justify-end items-start p-4">
                <div class="news-card__meta mb-2 flex items-center gap-2">
                  <span class="news-card__tag badge text-xs">
                    Thành tích
                  </span>
                  <span class="news-card__date flex items-center gap-1 text-sm">
                    <i class="fa-regular fa-calendar news-card__date-icon"></i>
                    15/01/2025
                  </span>
                </div>
                <h3 class="news-card__title text-xl font-semibold mb-2">
                  Sinh viên khoa giành giải Nhất cuộc thi Olympic Tin Học 2025
                </h3>
                <p class="news-card__description font-light text-sm">
                  Cuộc thi giữa 60 thí sinh cuối cùng trong trận chung kết để
                  đạt được danh hiệu cao quý
                </p>
              </div>
            </div>
          </a>
        </div>
      </div>

    </div>
  </div>
</section>
<!-- NEWSFEED-SECTION: END -->