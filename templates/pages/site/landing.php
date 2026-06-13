<?php
include_once BASE_PATH . '/templates/components/news_card.php';

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
              <img src="<?= htmlspecialchars(url('public/media/' . $slide->media->file_path)) ?>"
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

$aboutItems = [
  [
    'number' => '01',
    'image' => [
      'src' => './public/img/about.jpg',
      'alt' => 'Lecture hall with students',
    ],
    'card' => [
      'value' => 'Top 1',
      'label' => 'Khoa CNTT tại Miền Nam',
    ],
    'eyebrow' => 'LOREM ISPUM GÌ ĐÓ Ở ĐÂY',
    'title' => 'Đảm bảo chất lượng đào tạo',
    'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sed leo et neque vehicula lacinia vel at lorem. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sed leo et neque vehicula lacinia vel at lorem. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sed leo et neque vehicula lacinia vel at lorem. Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
  ],
  [
    'number' => '02',
    'image' => [
      'src' => './public/img/about.jpg',
      'alt' => 'Lecture hall with students',
    ],
    'card' => [
      'value' => '98%',
      'label' => 'Tỷ lệ có việc làm',
    ],
    'eyebrow' => 'LOREM ISPUM GÌ ĐÓ Ở ĐÂY',
    'title' => 'Cơ hội Nghề nghiệp',
    'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sed leo et neque vehicula lacinia vel at lorem. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sed leo et neque vehicula lacinia vel at lorem. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sed leo et neque vehicula lacinia vel at lorem. Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
  ],
  [
    'number' => '03',
    'image' => [
      'src' => './public/img/about.jpg',
      'alt' => 'Lecture hall with students',
    ],
    'card' => [
      'value' => '50+',
      'label' => 'Doanh nghiệp',
    ],
    'eyebrow' => 'LOREM ISPUM GÌ ĐÓ Ở ĐÂY',
    'title' => 'Nghiên cứu Đột phá',
    'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sed leo et neque vehicula lacinia vel at lorem. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sed leo et neque vehicula lacinia vel at lorem. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sed leo et neque vehicula lacinia vel at lorem. Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
  ],
];

$whyChooseUs = [
  'badge' => 'Tại sao chọn chúng tôi',
  'title' => 'Trải nghiệm Khoa CNTT Cao Thắng',
  'subtitle' => 'Nơi ươm mầm tài năng công nghệ thông tin, kết nối tri thức với thực tiễn',
  'feature' => [
    'image' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?...',
    'alt' => 'Trường Cao Thắng',
    'badge' => 'Nổi bật',
    'title' => 'Môi trường học tập hiện đại, sáng tạo',
    'description' => 'Trang bị phòng lab tiêu chuẩn quốc tế, thư viện số phong phú, không gian làm việc nhóm linh hoạt và hệ thống học tập trực tuyến tiên tiến.',
    'cta_label' => 'Khám phá ngay',
    'cta_url' => '#',
  ],
  'stats' => [
    [
      'number' => '20',
      'title' => 'Năm kinh nghiệm',
      'description' => 'Tiên phong trong đào tạo CNTT chất lượng cao tại TP.HCM từ năm 2003',
    ],
    [
      'number' => '95%',
      'title' => 'Tỷ lệ việc làm',
      'description' => 'Sinh viên có việc làm trong vòng 6 tháng sau tốt nghiệp',
    ],
  ],
  'perks' => [
    [
      'icon' => 'fa-solid fa-code',
      'title' => 'Công nghệ tiên tiến',
      'description' => 'Học tập với các công nghệ mới nhất: AI, Cloud, Blockchain, IoT',
    ],
    [
      'icon' => 'fa-solid fa-user-group',
      'title' => 'Cộng đồng Mạnh mẽ',
      'description' => 'Kết nối với 10,000+ sinh viên và cựu sinh viên trên toàn quốc',
    ],
    [
      'icon' => 'fa-solid fa-award',
      'title' => 'Chất lượng Quốc tế',
      'description' => 'Chương trình đạt chuẩn ABET và kiểm định quốc tế',
    ],
    [
      'icon' => 'fa-solid fa-rocket',
      'title' => 'Khởi nghiệp',
      'description' => 'Hỗ trợ ý tưởng startup và kết nối nhà đầu tư',
    ],
  ],
  'highlights' => [
    [
      'image' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?...',
      'alt' => 'Trường Cao Thắng',
      'title' => 'Nghiên cứu & Phát triển',
      'description' => 'Tham gia các dự án nghiên cứu thực tế cùng giảng viên',
    ],
    [
      'image' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?...',
      'alt' => 'Trường Cao Thắng',
      'title' => 'Hợp tác Quốc tế',
      'description' => 'Cơ hội trao đổi sinh viên và học bổng du học',
    ],
  ],
];

$statsSection = [
  'title' => 'Khoa CNTT Cao Thắng',
  'subtitle' => 'Định hình tương lai công nghệ thông tin Việt Nam',
  'stats' => [
    ['icon' => 'fa-solid fa-award', 'number' => '50+', 'label' => 'Giải thưởng', 'description' => 'Trong các cuộc thi lập trình'],
    ['icon' => 'fa-solid fa-graduation-cap', 'number' => '10K+', 'label' => 'Sinh viên', 'description' => 'Tốt nghiệp thành công'],
    ['icon' => 'fa-solid fa-briefcase', 'number' => '95%', 'label' => 'Việc làm', 'description' => 'Sau 6 tháng tốt nghiệp'],
    ['icon' => 'fa-solid fa-earth-americas', 'number' => '20+', 'label' => 'Quốc gia', 'description' => 'Hợp tác quốc tế'],
  ],
  'benefits' => [
    [
      'icon' => 'fa-solid fa-building-columns',
      'title' => 'Chương trình Đào tạo Tiên tiến',
      'items' => ['Cập nhật theo công nghệ mới nhất', 'Tích hợp chứng chỉ quốc tế', 'Thực hành dự án thực tế', 'Đào tạo kỹ năng mềm'],
    ],
    [
      'icon' => 'fa-solid fa-arrow-trend-up',
      'title' => 'Phát triển Nghề nghiệp',
      'items' => ['Kết nối với 100+ doanh nghiệp', 'Thực tập tại công ty hàng đầu', 'Tư vấn định hướng nghề nghiệp', 'Cơ hội việc làm cao'],
    ],
  ],
  'cta' => [
    'title' => 'Sẵn sàng bắt đầu hành trình của bạn?',
    'description' => 'Gia nhập cộng đồng hơn 10,000 sinh viên và cựu sinh viên đang làm việc tại các công ty công nghệ hàng đầu',
    'buttons' => [
      ['label' => 'Đăng ký tư vấn', 'url' => '#', 'variant' => 'outline-alt', 'class' => 'stats__cta-button stats__cta-button--primary flex items-center px-8 py-4 btn bouncy-btn rounded-full'],
      ['label' => 'Xem chương trình đào tạo', 'url' => '#', 'variant' => 'outline', 'class' => 'stats__cta-button stats__cta-button--secondary flex items-center px-8 py-4 btn bouncy-btn rounded-full bg-transparent'],
    ],
  ],
];
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
<section class="relative container py-16" id="landing-about-section">
  <h2 class="sr-only">About Us</h2>
  <div class="container-wrapper">
    <div class="flex flex-col justify-center items-center gap-2 md:gap-4 mb-8 md:mb-12"></div>
    <div class="landing-about-container flex flex-col gap-12 md:gap-0">
      <?php foreach ($aboutItems as $index => $item): ?>
        <div class="flex gap-4 md:gap-12 flex-col md:<?= $index % 2 === 0 ? 'flex-row-reverse' : 'flex-row' ?>">
          <div class="flex-1 relative">
            <div class="overflow-hidden rounded-3xl">
              <div class="image-wrapper">
                <img class="image w-full h-full" src="<?= htmlspecialchars($item['image']['src']) ?>"
                  alt="<?= htmlspecialchars($item['image']['alt']) ?>">
              </div>
            </div>
            <div class="landing-about-item__card absolute z-10 rounded-3xl p-3 md:p-6 flex flex-col gap-1">
              <div class="landing-about-item__card-main-content text-lg md:text-5xl">
                <?= htmlspecialchars($item['card']['value']) ?>
              </div>
              <div class="landing-about-item__card-sub-content md:text-sm">
                <?= htmlspecialchars($item['card']['label']) ?>
              </div>
            </div>
          </div>
          <div class="flex-1 flex flex-col justify-center gap-4">
            <p class="number-of-text text-7xl hidden md:block"><?= htmlspecialchars($item['number']) ?></p>
            <p class="landing-about-item__sub-title text-xs uppercase font-medium">
              <?= htmlspecialchars($item['eyebrow']) ?>
            </p>
            <p class="about-item__title text-4xl">
              <?= htmlspecialchars($item['title']) ?>
            </p>
            <p class="landing-about-item__content">
              <?= htmlspecialchars($item['description']) ?>
            </p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<!-- ABOUT-SECTION: END -->

<!-- WHY-CHOOSE-US-SECTION: START -->
<section class="wcu relative container py-16" id="why-choose-us-section">
  <div class="wcu__container container-wrapper">
    <div class="wcu__header flex flex-col justify-center items-center gap-2 md:gap-4 mb-8 md:mb-12">
      <div class="wcu__badge section__badge px-4 py-2 rounded-3xl text-sm mb-2 md:mb-4">
        <?= htmlspecialchars($whyChooseUs['badge']) ?>
      </div>

      <h2 class="wcu__title section__title">
        <?= htmlspecialchars($whyChooseUs['title']) ?>
      </h2>

      <p class="wcu__subtitle section__sub-title">
        <?= htmlspecialchars($whyChooseUs['subtitle']) ?>
      </p>
    </div>

    <div class="wcu__content flex flex-col items-center justify-center">
      <div class="wcu__features-grid grid grid-cols-2 md:grid-cols-3 grid-rows-2 gap-3 md:gap-6 mb-6 self-stretch">
        <div
          class="wcu__feature-card wcu__feature-card--large wcu-feature-container overflow-hidden relative row-start-1 col-span-2 row-span-1 md:row-span-2 rounded-3xl image-wrapper">
          <img class="wcu__feature-card-image image" src="<?= htmlspecialchars($whyChooseUs['feature']['image']) ?>"
            alt="<?= htmlspecialchars($whyChooseUs['feature']['alt']) ?>">
          <div
            class="wcu__feature-card-content absolute inset-0 flex flex-col justify-end items-start gap-2 md:gap-4 p-3 md:p-6 ">
            <span class="wcu__feature-card-badge badge" data-variant="primary">
              <?= htmlspecialchars($whyChooseUs['feature']['badge']) ?>
            </span>
            <h3 class="wcu__feature-card-title text-md md:text-3xl font-semibold">
              <?= htmlspecialchars($whyChooseUs['feature']['title']) ?>
            </h3>
            <p class="wcu__feature-card-description text-xs md:text-md font-normal">
              <?= htmlspecialchars($whyChooseUs['feature']['description']) ?>
            </p>
            <a href="<?= htmlspecialchars($whyChooseUs['feature']['cta_url']) ?>"
              class="wcu__feature-card-link md:text-md font-normal">
              <?= htmlspecialchars($whyChooseUs['feature']['cta_label']) ?>
              <i class="fa-solid fa-arrow-up-right-from-square"></i>
            </a>
          </div>
        </div>

        <?php foreach ($whyChooseUs['stats'] as $index => $stat): ?>
          <?php
          $statCardClass = $index === 0
            ? 'wcu__stat-card--primary col-start-1 md:col-start-3 row-start-2 md:row-start-1'
            : 'wcu__stat-card--gradient col-start-2 md:col-start-3 row-start-2 md:row-start-2';
          ?>
          <div class="wcu__stat-card <?= $statCardClass ?> rounded-3xl p-3 md:p-6 flex flex-col gap-2 justify-center">
            <h2
              class="wcu__stat-card-number flex-1 md:flex-none flex justify-center items-center md:block text-6xl md:text-7xl font-bold">
              <?= htmlspecialchars($stat['number']) ?>
            </h2>
            <div class="wcu__stat-card-content flex flex-col gap-2">
              <p class="wcu__stat-card-title md:text-xl font-semibold">
                <?= htmlspecialchars($stat['title']) ?>
              </p>
              <p class="wcu__stat-card-description text-xs md:text-md font-normal">
                <?= htmlspecialchars($stat['description']) ?>
              </p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div
        class="wcu__perks-list grid grid-cols-2 grid-rows-2 md:flex justify-center items-stretch self-stretch gap-3 md:gap-6 mb-6">
        <?php foreach ($whyChooseUs['perks'] as $perk): ?>
          <div class="wcu__perk-item flex flex-col items-start justify-start flex-1 rounded-3xl p-3 md:p-6">
            <div class="wcu__perk-item-icon-wrapper flex justify-center items-center rounded-full text-4xl mb-4 p-3">
              <i class="<?= htmlspecialchars($perk['icon']) ?> wcu__perk-item-icon"></i>
            </div>
            <h4 class="wcu__perk-item-title md:text-md font-semibold mb-2">
              <?= htmlspecialchars($perk['title']) ?>
            </h4>
            <p class="wcu__perk-item-description text-xs md:text-sm font-normal">
              <?= htmlspecialchars($perk['description']) ?>
            </p>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="wcu__highlights-list self-stretch grid grid-rows-2 md:grid-rows-1 md:grid-cols-2 gap-3 md:gap-6">
        <?php foreach ($whyChooseUs['highlights'] as $index => $highlight): ?>
          <?php $highlightContentClass = $index === 0 ? 'wcu__highlight-item-content--blue' : 'wcu__highlight-item-content--green'; ?>
          <div class="wcu__highlight-item flex-1 overflow-hidden relative rounded-3xl image-wrapper text-white">
            <img class="wcu__highlight-item-image image" src="<?= htmlspecialchars($highlight['image']) ?>"
              alt="<?= htmlspecialchars($highlight['alt']) ?>">
            <div
              class="wcu__highlight-item-content <?= $highlightContentClass ?> absolute inset-0 flex flex-col justify-end items-start p-3 md:p-6">
              <h3 class="wcu__highlight-item-title text-md md:text-2xl font-semibold mb-2">
                <?= htmlspecialchars($highlight['title']) ?>
              </h3>
              <p class="wcu__highlight-item-description text-xs md:text-sm font-normal">
                <?= htmlspecialchars($highlight['description']) ?>
              </p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<!-- WHY-CHOOSE-US-SECTION: END -->

<!-- STATS-SECTION: START -->
<section class="relative container py-16" id="stats-section">
  <div class="container-wrapper">
    <div class="flex flex-col justify-center items-center gap-2 md:gap-4 mb-8 md:mb-12">
      <h2 class="section__title">
        <?= htmlspecialchars($statsSection['title']) ?>
      </h2>

      <p class="section__sub-title">
        <?= htmlspecialchars($statsSection['subtitle']) ?>
      </p>
    </div>
    <div class="flex flex-col items-stretch justify-center gap-3 md:gap-6">
      <div class="stats__grid grid grid-cols-2 grid-rows-2 md:grid-cols-4 md:grid-rows-1 gap-3 md:gap-6">
        <?php foreach ($statsSection['stats'] as $stat): ?>
          <div class="stats__stat-card flex flex-1 flex-col items-center gap-3 md:gap-6 rounded-3xl p-3 md:p-6">
            <div class="stats__stat-card-icon-wrapper flex items-center justify-center rounded-full">
              <i class="<?= htmlspecialchars($stat['icon']) ?> stats__stat-card-icon"></i>
            </div>
            <div class="flex flex-col gap-1 items-center">
              <h3 class="stats__stat-card-number text-3xl md:text-5xl font-bold"><?= htmlspecialchars($stat['number']) ?>
              </h3>
              <h4 class="stats__stat-card-label font-semibold"><?= htmlspecialchars($stat['label']) ?></h4>
              <p class="stats__stat-card-description text-xs md:text-sm text-center">
                <?= htmlspecialchars($stat['description']) ?>
              </p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div
        class="stats__benefits-grid grid grid-cols-1 md:grid-cols-2 grid-rows-2 md:grid-rows-1 gap-3 md:gap-6 items-stretch">
        <?php foreach ($statsSection['benefits'] as $benefit): ?>
          <div class="stats__benefit-card flex-1 flex flex-col gap-3 md:gap-6 p-3 md:p-6 rounded-3xl">
            <div class="stats__benefit-card-header flex gap-2 md:gap-4 items-center">
              <div class="stats__benefit-card-icon-wrapper flex justify-center items-center rounded-full">
                <i class="<?= htmlspecialchars($benefit['icon']) ?> stats__benefit-card-icon"></i>
              </div>
              <h3 class="stats__benefit-card-title text-lg md:text-2xl font-semibold">
                <?= htmlspecialchars($benefit['title']) ?>
              </h3>
            </div>
            <ul class="stats__benefit-card-list flex flex-col gap-2 md:gap-4">
              <?php foreach ($benefit['items'] as $item): ?>
                <li class="stats__benefit-card-item flex items-center gap-2">
                  <span class="stats__benefit-card-item-icon rounded-full"></span>
                  <p class="stats__benefit-card-item-text">
                    <?= htmlspecialchars($item) ?>
                  </p>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="stats__cta flex flex-col items-center p-3 md:p-12 rounded-3xl">
        <h3 class="stats__cta-title text-center text-xl md:text-3xl font-semibold mb-2">
          <?= htmlspecialchars($statsSection['cta']['title']) ?>
        </h3>
        <p class="stats__cta-description text-center text-sm md:text-xl font-light mb-6">
          <?= htmlspecialchars($statsSection['cta']['description']) ?>
        </p>
        <div class="stats__cta-buttons flex flex-col w-full md:w-fit md:flex-row gap-2 md:gap-4">
          <?php foreach ($statsSection['cta']['buttons'] as $button): ?>
            <a href="<?= htmlspecialchars($button['url']) ?>" data-variant="<?= htmlspecialchars($button['variant']) ?>"
              class="<?= htmlspecialchars($button['class']) ?>">
              <?= htmlspecialchars($button['label']) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- STATS-SECTION: END -->

<!-- NEWSFEED-SECTION: START -->
<section class="relative container py-16" id="newsfeed-section">
  <div class="container-wrapper">

    <div class="flex flex-col justify-center items-center gap-2 md:gap-4 mb-8 md:mb-12">
      <h2 class="section__title">
        Tin tức &amp; Sự kiện
      </h2>
      <p class="section__sub-title">
        Cập nhật những tin tức mới nhất về hoạt động của khoa, thành tích
        sinh viên và các sự kiện sắp tới
      </p>
    </div>

    <div class="newsfeed__content-wrapper flex flex-col gap-16">
      <div class="flex flex-col gap-3 md:gap-6">
        <?php if (!empty($featuredNews)):
          $featured = $featuredNews[0]; ?>
          <!-- Main Featured News -->
          <?php renderLandingNewsCard($featured, [
            'variant' => 'featured',
            'category_label' => 'Nổi bật',
            'show_read_more' => true,
            'class' => 'landing-featured-news relative overflow-hidden rounded-3xl',
          ]); ?>
        <?php endif; ?>

        <!-- Sub Featured News Grid -->
        <div class="flex gap-3 md:gap-6 justify-center items-stretch self-stretch">
          <?php
          $featuredCount = count($featuredNews);
          for ($i = 1; $i < 4 && $i < $featuredCount; $i++):
            $news = $featuredNews[$i];
            $catName = !empty($news->categories) ? $news->categories[0]->name : 'Tin tức';
            ?>
            <?php renderLandingNewsCard($news, [
              'variant' => 'secondary',
              'category_label' => $catName,
              'class' => 'landing-sub-featured-news flex-1',
            ]); ?>
          <?php endfor; ?>
        </div>
      </div>

      <div id="newsfeed-other" class="flex flex-col gap-4">
        <div class="newsfeed__other-header flex">
          <h2 class="newsfeed__other-title text-2xl md:text-4xl font-medium flex-1">
            Tin tức khác
          </h2>
          <a href="<?= htmlspecialchars(url('tin-tuc')) ?>"
            class="newsfeed__view-all-link md:text-md font-medium link-hover--underline">
            Xem thêm
            <i class="fa-solid fa-arrow-up-right-from-square"></i>
          </a>
        </div>

        <div class="flex flex-col md:flex-row gap-3 md:gap-6 justify-center items-stretch self-stretch">
          <?php
          $latestNews = $latestNewsItems ?? [];
          $latestCount = count($latestNews);
          for ($i = 0; $i < 3 && $i < $latestCount; $i++):
            $news = $latestNews[$i];
            $catName = !empty($news->categories) ? $news->categories[0]->name : 'Tin tức';
            ?>
            <?php renderLandingNewsCard($news, [
              'variant' => 'standard',
              'category_label' => $catName,
            ]); ?>
          <?php endfor; ?>
        </div>
      </div>

    </div>
  </div>
</section>
<!-- NEWSFEED-SECTION: END -->