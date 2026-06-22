<?php

namespace App\Cms;

final class CmsPageSchemaRegistry
{
  private const PAGES = [
    'landing' => [
      'title' => 'Trang chủ',
      'slug' => 'landing',
      'route_path' => '/',
      'type' => 'landing_page',
      'layout_mode' => 'block_builder',
      'blocks' => [
        [
          'type' => 'cms/carousel',
          'data' => ['meta' => ['carousel_slug' => 'landing-page', 'variant' => 'standard']],
        ],
        [
          'type' => 'cms/landing_story',
          'data' => ['meta' => ['items' => [
            ['number' => '01', 'eyebrow' => 'Lorem ispum gì đó ở đây', 'title' => 'Đảm bảo chất lượng đào tạo', 'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'image' => 'public/img/about.jpg', 'badge_value' => 'Top 1', 'badge_label' => 'Khoa CNTT tại Miền Nam', 'image_side' => 'right'],
            ['number' => '02', 'eyebrow' => 'Lorem ispum gì đó ở đây', 'title' => 'Cơ hội Nghề nghiệp', 'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'image' => 'public/img/about.jpg', 'badge_value' => '98%', 'badge_label' => 'Tỷ lệ có việc làm', 'image_side' => 'left'],
            ['number' => '03', 'eyebrow' => 'Lorem ispum gì đó ở đây', 'title' => 'Nghiên cứu Đột phá', 'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'image' => 'public/img/about.jpg', 'badge_value' => '50+', 'badge_label' => 'Doanh nghiệp', 'image_side' => 'right'],
          ]]],
        ],
        [
          'type' => 'cms/experience_grid',
          'data' => ['meta' => ['badge' => 'Tại sao chọn chúng tôi', 'title' => 'Trải nghiệm Khoa CNTT Cao Thắng', 'subtitle' => 'Nơi ươm mầm tài năng công nghệ thông tin, kết nối tri thức với thực tiễn', 'image' => 'public/img/about.jpg', 'feature_title' => 'Môi trường học tập hiện đại, sáng tạo', 'feature_description' => 'Trang bị phòng lab tiêu chuẩn quốc tế, thư viện số phong phú, không gian làm việc nhóm linh hoạt và hệ thống học tập trực tuyến tiên tiến.', 'stats' => [
            ['number' => '20', 'label' => 'Năm kinh nghiệm', 'description' => 'Tiên phong trong đào tạo CNTT chất lượng cao tại TP.HCM từ năm 2003', 'variant' => 'blue'],
            ['number' => '95%', 'label' => 'Tỷ lệ việc làm', 'description' => 'Sinh viên có việc làm trong vòng 6 tháng sau tốt nghiệp', 'variant' => 'pink'],
          ], 'perks' => [
            ['icon' => 'fa-solid fa-code', 'title' => 'Công nghệ tiên tiến', 'description' => 'Học tập với công nghệ mới nhất.'],
            ['icon' => 'fa-solid fa-briefcase', 'title' => 'Cộng đồng Mạnh mẽ', 'description' => 'Kết nối sinh viên với toàn cầu.'],
            ['icon' => 'fa-solid fa-award', 'title' => 'Chất lượng Quốc tế', 'description' => 'Chương trình đạt chuẩn ABET.'],
            ['icon' => 'fa-solid fa-rocket', 'title' => 'Khởi nghiệp', 'description' => 'Ươm mầm ý tưởng startup.'],
          ], 'cards' => [
            ['title' => 'Nghiên cứu & Phát triển', 'description' => 'Tham gia các dự án nghiên cứu thực tế cùng giảng viên', 'image' => 'public/img/about.jpg', 'variant' => 'blue'],
            ['title' => 'Hợp tác Quốc tế', 'description' => 'Cơ hội trao đổi sinh viên và học bổng du học', 'image' => 'public/img/about.jpg', 'variant' => 'green'],
          ]]],
        ],
        [
          'type' => 'cms/metric_summary',
          'data' => ['meta' => ['title' => 'Khoa CNTT Cao Thắng', 'subtitle' => 'Định hình tương lai công nghệ thông tin Việt Nam', 'metrics' => [
            ['icon' => 'fa-solid fa-award', 'number' => '50+', 'label' => 'Giải thưởng', 'description' => 'Trong các cuộc thi lập trình'],
            ['icon' => 'fa-solid fa-graduation-cap', 'number' => '10K+', 'label' => 'Sinh viên', 'description' => 'Tốt nghiệp thành công'],
            ['icon' => 'fa-solid fa-briefcase', 'number' => '95%', 'label' => 'Việc làm', 'description' => 'Sau 6 tháng tốt nghiệp'],
            ['icon' => 'fa-solid fa-earth-asia', 'number' => '20+', 'label' => 'Quốc gia', 'description' => 'Hợp tác quốc tế'],
          ], 'cards' => [
            ['title' => 'Chương trình Đào tạo Tiên tiến', 'items' => ['Cập nhật theo công nghệ mới nhất', 'Tích hợp chứng chỉ quốc tế', 'Thực hành dự án thực tế', 'Được quy kỹ năng mềm']],
            ['title' => 'Phát triển Nghề nghiệp', 'items' => ['Kết nối với 100+ doanh nghiệp', 'Thực tập tại công ty hàng đầu', 'Tư vấn định hướng nghề nghiệp', 'Cơ hội việc làm cao']],
          ]]],
        ],
        [
          'type' => 'cms/cta_band',
          'data' => ['meta' => ['title' => 'Sẵn sàng bắt đầu hành trình của bạn?', 'description' => 'Gia nhập cộng đồng hơn 10.000 sinh viên và cựu sinh viên đang làm việc tại các công ty công nghệ hàng đầu', 'buttons' => [
            ['label' => 'Đăng ký tư vấn', 'url' => '#', 'variant' => 'secondary'],
            ['label' => 'Xem chương trình đào tạo', 'url' => '#', 'variant' => 'outline'],
          ]]],
        ],
        [
          'type' => 'cms/newsfeed',
          'data' => ['meta' => ['mode' => 'featured_latest', 'featured_count' => 4, 'latest_count' => 3, 'variant' => 'landing']],
        ],
      ],
    ],
    'about' => [
      'title' => 'Gioi thieu',
      'slug' => 'about',
      'route_path' => '/gioi-thieu',
      'type' => 'landing_page',
      'layout_mode' => 'block_builder',
      'blocks' => [
        [
          'type' => 'cms/about_hero',
          'data' => ['meta' => ['image' => 'public/img/about.jpg', 'badge' => 'Về Chúng Tôi', 'title' => 'Câu chuyện của Cao Thắng', 'subtitle' => 'Từ những ngày đầu tiên đến hôm nay, Cao Thắng không ngừng phát triển để mang đến giáo dục công nghệ chất lượng cao cho sinh viên Việt Nam']],
        ],
        [
          'type' => 'cms/timeline_story',
          'data' => ['meta' => ['items' => [
            ['badge' => 'Khoa Công Nghệ Thông Tin', 'title' => '27 năm đổi mới & phát triển', 'image' => 'public/img/about.jpg', 'year' => '1998', 'caption' => 'Khoa CNTT được thành lập', 'image_side' => 'left', 'timeline' => [['year' => '1998', 'description' => 'Khoa Điện Tử - Tin Học, tiền thân của khoa Công Nghệ Thông Tin được thành lập.'], ['year' => '2020', 'description' => 'Đổi tên Khoa Điện tử - Tin học thành Khoa Công nghệ thông tin.']]],
            ['badge' => 'Trường Cao Đẳng Kỹ Thuật Cao Thắng', 'title' => '100+ năm truyền thống', 'image' => 'public/img/about.jpg', 'year' => '1906', 'caption' => 'Trường được thành lập', 'image_side' => 'right', 'timeline' => [['year' => '1906', 'description' => 'Chính thức thành lập Trường Cơ khí Á Châu.'], ['year' => '1915', 'description' => 'Chủ tịch Tôn Đức Thắng nhập học.'], ['year' => '2016', 'description' => 'Đạt chuẩn kiểm định quốc tế ABET.']]],
          ]]],
        ],
        [
          'type' => 'cms/bento_showcase',
          'data' => ['meta' => ['items' => [
            ['type' => 'image', 'span' => 'large', 'title' => 'Thành tựu', 'badge' => 'Chứng nhận Quốc Tế', 'image' => 'public/img/about.jpg', 'description' => '30+ Quốc gia công nhận'],
            ['type' => 'color', 'variant' => 'green', 'number' => '25+', 'title' => 'Giảng Viên', 'description' => 'Có hơn 15 năm kinh nghiệm trong việc giảng dạy'],
            ['type' => 'color', 'variant' => 'pink', 'number' => '50+', 'title' => 'Giải Thưởng', 'description' => 'Từ chính phủ và các tổ chức kiểm định quốc tế'],
            ['type' => 'plain', 'number' => '10+', 'title' => 'Phòng Lab hiện đại', 'description' => 'Trang bị công nghệ tiên tiến phục vụ học tập và nghiên cứu'],
            ['type' => 'color', 'variant' => 'orange', 'number' => '100+', 'title' => 'Học bổng hằng năm', 'description' => 'Từ học bổng toàn phần đến các suất trao đổi quốc tế'],
            ['type' => 'image', 'span' => 'tall', 'title' => 'Môi trường', 'badge' => 'Cộng đồng học tập', 'image' => 'public/img/about.jpg', 'description' => 'Năng động & sáng tạo'],
          ]]],
        ],
      ],
    ],
  ];

  public function allPages(): array
  {
    return self::PAGES;
  }

  public function page(string $slug): ?array
  {
    return self::PAGES[$slug] ?? null;
  }

  public function hasPage(string $slug): bool
  {
    return isset(self::PAGES[$slug]);
  }

  public function defaultDocument(string $slug): array
  {
    $schema = $this->page($slug);

    if ($schema === null) {
      throw new \InvalidArgumentException("Unknown CMS page schema: {$slug}");
    }

    if (($schema['layout_mode'] ?? 'section_schema') === 'block_builder') {
      return [
        'version' => 1,
        'blocks' => array_map(
          fn(array $block) => $this->blockToDocumentNode($block),
          $schema['blocks'] ?? [],
        ),
      ];
    }

    return [
      'version' => 1,
      'sections' => array_map(
        fn(array $section) => $this->sectionToDocumentNode($section),
        $schema['sections'] ?? [],
      ),
    ];
  }

  public function sectionMap(string $slug): array
  {
    $schema = $this->page($slug);

    if ($schema === null) {
      return [];
    }

    $map = [];
    foreach (($schema['sections'] ?? []) as $section) {
      $map[$section['id']] = $section;
    }

    return $map;
  }

  private function sectionToDocumentNode(array $section): array
  {
    return [
      'id' => $section['id'],
      'type' => $section['type'],
      'locked' => (bool) ($section['locked'] ?? false),
      'data' => $section['data'] ?? [],
    ];
  }

  private function blockToDocumentNode(array $block): array
  {
    return [
      'id' => $block['id'] ?? bin2hex(random_bytes(8)),
      'type' => $block['type'],
      'version' => (int) ($block['version'] ?? 1),
      'data' => $block['data'] ?? ['rich_text' => [], 'meta' => []],
    ];
  }
}
