<?php
$layout->start("content");

$historySections = [
  [
    'img' => 'public/img/about.jpg',
    'imgAlt' => 'Lecture hall with students',
    'year' => '1998',
    'imageCaption' => 'Khoa CNTT được thành lập',
    'icon' => 'fa-solid fa-graduation-cap',
    'badge' => 'Khoa Công Nghệ Thông Tin',
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
    'img' => 'public/img/about.jpg',
    'imgAlt' => 'Lecture hall with students',
    'year' => '1906',
    'imageCaption' => 'Trường được thành lập',
    'icon' => 'fa-solid fa-building-columns',
    'badge' => 'Trường Cao Đẳng Kỹ Thuật Cao Thắng',
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
    'img' => 'public/img/about.jpg',
    'content' => 'Thành tựu đặc biệt/tiêu điểm',
    'footer' => "
      <span class='badge p-4 text-md' data-variant='glass'>30+ Quốc gia công nhận</span>
      <span class='badge p-4 text-md' data-variant='glass'>Top 5 Khoa CNTT VN</span>",
  ],
  [
    'badge' => '<i class="fa-solid fa-user-group"></i>',
    'img' => '',
    'content' => '25+',
    'subContent' => 'Giảng Viên',
    'footer' => 'Có hơn 15 năm kinh nghiệm trong việc giảng dạy',
  ],
  [
    'badge' => '<i class="fa-solid fa-award"></i>',
    'img' => '',
    'content' => '50+',
    'subContent' => 'Giải Thưởng',
    'footer' => 'Từ chính phủ và các tổ chức kiểm định quốc tế',
  ],
  [
    'badge' => '<i class="fa-solid fa-rocket"></i>',
    'img' => '',
    'content' => '10+',
    'subContent' => 'Phòng Lab hiện đại',
    'footer' => 'Trang bị công nghệ tiên tiến phục vụ học tập và nghiên cứu',
  ],
  [
    'badge' => '<i class="fa-solid fa-graduation-cap"></i>',
    'img' => '',
    'content' => '100+',
    'subContent' => 'Học bổng hàng năm',
    'footer' => 'Từ học bổng toàn phần đến các suất trao đổi quốc tế',
  ],
  [
    'badge' => '<i class="fa-solid fa-user-group"></i> <span>Cộng đồng học tập</span>',
    'img' => 'public/img/about.jpg',
    'content' => 'Môi trường',
    'subContent' => 'Năng động & sáng tạo',
    'footer' => 'Nhiều hoạt động, sự kiện nhằm thúc đẩy tinh thần năng nổ của Sinh Viên',
  ],
  [
    'badge' => '<i class="fa-solid fa-building"></i>',
    'img' => '',
    'content' => '100+',
    'subContent' => 'Doanh nghiệp đối tác',
    'footer' => 'FPT, Viettel, Samsung, Google và nhiều công ty hàng đầu',
  ],
  [
    'badge' => '<i class="fa-solid fa-arrow-trend-up"></i>',
    'img' => '',
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
  <div class="container">
    <div class="container-wrapper">
      <div class="about-thumbnail-content__wrapper absolute inset-0 flex justify-center items-center">
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
        <div class="flex <?= ($index % 2 !== 0) ? 'flex-row-reverse' : 'flex-row' ?> flex-1 gap-12">
          <div class="flex-1 relative overflow-hidden rounded-3xl">
            <div class="history-image-wrapper image-wrapper">
              <img class="image w-full h-full" src="<?= url($section['img']) ?>" alt="<?= $section['imgAlt'] ?>">
            </div>
            <div class="history-image-wrapper__content absolute inset-0 flex flex-col justify-end gap-1">
              <div class="text-6xl"><?= $section['year'] ?></div>
              <div class="text-xl"><?= $section['imageCaption'] ?></div>
            </div>
          </div>
          <div class="flex-1 history-content flex flex-col justify-center gap-8">
            <span class="badge" data-variant="primary">
              <i class="<?= $section['icon'] ?>"></i>
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
          <?php $hasImage = !empty($item['img']); ?>
          <div class="card bento-grid-item <?= $hasImage ? 'bento-grid-item--has-image' : '' ?>">
            <?php if ($hasImage): ?>
              <img class="bento-grid-item__image" src="<?= url($item['img']) ?>" alt="">
            <?php endif; ?>
            <div class="card__header">
              <span class="badge" data-variant="glass">
                <?= $item['badge'] ?? "" ?>
              </span>
            </div>
            <div class="card__content">
              <div class="text-6xl">
                <?= $item['content'] ?? "" ?>
              </div>
              <div class="text-xl">
                <?= $item['subContent'] ?? "" ?>
              </div>
            </div>
            <div class="card__footer flex flex-row">
              <?= $item['footer'] ?? "" ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<?php $layout->end(); ?>