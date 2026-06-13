<?php
$layout->start("content");

$historySections = [
  [
    'image' => [
      'src' => 'public/img/about.jpg',
      'alt' => 'Lecture hall with students',
      'caption' => 'Khoa CNTT được thành lập',
    ],
    'year' => '1998',
    'badge' => "
      <i class='fa-solid fa-graduation-cap'></i>
      <span class='text-sm'>Khoa Công Nghệ Thông Tin</span>
    ",
    'title' => '27 năm đổi mới & phát triển',
    'timeline' => [
      [
        'year' => '1998',
        'description' => 'Khoa Điện Tử - Tin Học, tiền thân của khoa Công Nghệ Thông Tin được thành lập.',
      ],
      [
        'year' => '2020',
        'description' => 'Đổi tên Khoa Điện tử - Tin học thành Khoa Công nghệ thông tin.',
      ],
    ],
  ],
  [
    'image' => [
      'src' => 'public/img/about.jpg',
      'alt' => 'Lecture hall with students',
      'caption' => 'Trường được thành lập',
    ],
    'year' => '1906',
    'badge' => "
      <i class='fa-solid fa-building-columns'></i>
      <span class='text-sm'>Trường Cao Đẳng Kỹ Thuật Cao Thắng</span>
    ",
    'title' => '100+ năm truyền thống',
    'timeline' => [
      [
        'year' => '1906',
        'description' => "Chính thức thành lập Trường Cơ khí Á Châu (L'école des Mécaniciens Asiatiques), tiền thân của trường.",
      ],
      [
        'year' => '1915',
        'description' => 'Chủ tịch Tôn Đức Thắng nhập học.',
      ],
      [
        'year' => '2004',
        'description' => 'Chính thức đổi tên thành Trường Cao đẳng Kỹ thuật Cao Thắng.',
      ],
      [
        'year' => '2016',
        'description' => 'Đạt chuẩn kiểm định quốc tế ABET.',
      ],
    ],
  ],
];

$bentoGridItems = [
  [
    'badge' => '<i class="fa-solid fa-award"></i> <span>Chứng nhận Quốc Tế</span>',
    'image' => [
      'src' => 'public/img/about.jpg',
      'alt' => '',
    ],
    'content' => 'Thành tựu',
    'footer' => "
      <span class='badge px-3 py-2 text-sm md:text-md' data-variant='glass'>30+ Quốc gia công nhận</span>
      <span class='badge px-3 py-2 text-sm md:text-md' data-variant='glass'>Top 5 Khoa CNTT VN</span>",
  ],
  [
    'badge' => '<i class="fa-solid fa-user-group"></i>',
    'image' => [
      'src' => '',
      'alt' => '',
    ],
    'content' => '25+',
    'subContent' => 'Giảng Viên',
    'footer' => 'Có hơn 15 năm kinh nghiệm trong việc giảng dạy',
  ],
  [
    'badge' => '<i class="fa-solid fa-award"></i>',
    'image' => [
      'src' => '',
      'alt' => '',
    ],
    'content' => '50+',
    'subContent' => 'Giải Thưởng',
    'footer' => 'Từ chính phủ và các tổ chức kiểm định quốc tế',
  ],
  [
    'badge' => '<i class="fa-solid fa-rocket"></i>',
    'image' => [
      'src' => '',
      'alt' => '',
    ],
    'content' => '10+',
    'subContent' => 'Phòng Lab hiện đại',
    'footer' => 'Trang bị công nghệ tiên tiến phục vụ học tập và nghiên cứu',
  ],
  [
    'badge' => '<i class="fa-solid fa-graduation-cap"></i>',
    'image' => [
      'src' => '',
      'alt' => '',
    ],
    'content' => '100+',
    'subContent' => 'Học bổng hàng năm',
    'footer' => 'Từ học bổng toàn phần đến các suất trao đổi quốc tế',
  ],
  [
    'badge' => '<i class="fa-solid fa-user-group"></i> <span>Cộng đồng học tập</span>',
    'image' => [
      'src' => 'public/img/about.jpg',
      'alt' => '',
    ],
    'content' => 'Môi trường',
    'subContent' => 'Năng động & sáng tạo',
    'footer' => 'Nhiều hoạt động, sự kiện nhằm thúc đẩy tinh thần năng nổ của Sinh Viên',
  ],
  [
    'badge' => '<i class="fa-solid fa-building"></i>',
    'image' => [
      'src' => '',
      'alt' => '',
    ],
    'content' => '100+',
    'subContent' => 'Doanh nghiệp đối tác',
    'footer' => 'FPT, Viettel, Samsung, Google và nhiều công ty hàng đầu',
  ],
  [
    'badge' => '<i class="fa-solid fa-arrow-trend-up"></i>',
    'image' => [
      'src' => '',
      'alt' => '',
    ],
    'content' => '1,000+',
    'subContent' => 'Cựu sinh viên',
    'footer' => 'Làm việc tại các công ty công nghệ hàng đầu toàn cầu',
  ],
];
?>
<!-- Breadcrumbs -->
<section class="site-breadcrumbs py-4">
  <div class="container">
    <div class="container-wrapper">
      <?php
      include_once BASE_PATH . '/templates/components/breadcrumb.php';
      renderBreadcrumb([
        ['icon' => '<i class="fa-regular fa-house"></i>', 'url' => url('/'), 'title' => 'Trang chủ'],
        ['url' => url('/gioi-thieu'), 'title' => 'Giới Thiệu'],
      ]);
      ?>
    </div>
  </div>
</section>

<!-- About Thumbnail -->
<section class="relative">
  <div class="about-thumbnail__wrapper">
    <img class="w-full h-full object-cover" src="<?= url("public/img/about.jpg") ?>">
  </div>
  <div class="about-thumbnail-content__wrapper absolute inset-0 flex justify-center items-center">
    <div class="container">
      <div class="container-wrapper">
        <div class="about-thumbnail-content flex flex-col justify-center items-center gap-6 text-center">
          <span class="badge" data-variant="primary">Về Chúng Tôi</span>
          <div class="about-thumbnail-content__title">Câu chuyện của Cao Thắng</div>
          <div class="about-thumbnail-content__sub-title">Từ những ngày đầu tiên đến hôm nay, Cao Thắng không ngừng phát
            triển để mang đến giáo dục công nghệ chất lượng cao cho sinh viên Việt Nam</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- About Content -->
<section class="py-12">
  <div class="container">
    <div class="container-wrapper flex flex-col gap-16">
      <?php foreach ($historySections as $index => $section): ?>
        <!-- History Section -->
        <div
          class="flex flex-col md:<?= ($index % 2 !== 0) ? 'flex-row-reverse' : 'flex-row' ?> flex-1 items-center gap-12">
          <div class="history-image-card flex-1 relative overflow-hidden rounded-3xl">
            <div class="history-image-wrapper image-wrapper">
              <img class="image w-full h-full" src="<?= url($section['image']['src']) ?>"
                alt="<?= $section['image']['alt'] ?>">
            </div>
            <div class="history-image-wrapper__content absolute inset-0 flex flex-col justify-end gap-1">
              <div class="text-6xl"><?= $section['year'] ?></div>
              <div class="text-xl"><?= $section['image']['caption'] ?></div>
            </div>
          </div>
          <div class="flex-1 history-content flex flex-col justify-center gap-8">
            <span class="badge" data-variant="primary">
              <?= $section['badge'] ?>
            </span>
            <p class="text-4xl">
              <?= $section['title'] ?>
            </p>
            <div class="history-content-timeline flex flex-col gap-4">
              <?php foreach ($section['timeline'] as $timelineItem): ?>
                <div class="history-content-timeline__item">
                  <span class="history-content-timeline__item-year"><?= $timelineItem['year'] ?>:</span>
                  <span><?= $timelineItem['description'] ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>

      <div class="bento-grid">
        <?php foreach ($bentoGridItems as $item): ?>
          <?php $hasImage = !empty($item['image']['src']); ?>
          <div class="card bento-grid-item <?= $hasImage ? 'bento-grid-item--has-image' : '' ?>">
            <?php if ($hasImage): ?>
              <img class="bento-grid-item__image" src="<?= url($item['image']['src']) ?>"
                alt="<?= $item['image']['alt'] ?>">
            <?php endif; ?>
            <div class="card__header">
              <span class="badge" data-variant="glass">
                <?= $item['badge'] ?? "" ?>
              </span>
            </div>
            <div class="card__content">
              <div class="text-4xl md:text-6xl">
                <?= $item['content'] ?? "" ?>
              </div>
              <div class="text-xl">
                <?= $item['subContent'] ?? "" ?>
              </div>
            </div>
            <div class="card__footer flex flex-row flex-wrap">
              <?= $item['footer'] ?? "" ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<?php $layout->end(); ?>