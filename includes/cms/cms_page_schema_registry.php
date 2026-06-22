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
          'type' => 'cms/heading',
          'data' => ['rich_text' => [['type' => 'text', 'text' => 'Khoa Công Nghệ Thông Tin', 'marks' => []]], 'meta' => ['level' => 2, 'align' => 'center', 'variant' => 'display']],
        ],
        [
          'type' => 'cms/paragraph',
          'data' => ['rich_text' => [['type' => 'text', 'text' => 'Xây dựng năng lực công nghệ, kết nối doanh nghiệp và đồng hành cùng sinh viên trong hành trình nghề nghiệp.', 'marks' => []]], 'meta' => ['align' => 'center', 'variant' => 'lead']],
        ],
        [
          'type' => 'cms/stat_grid',
          'data' => ['meta' => ['variant' => 'cards', 'columns' => 4, 'items' => [
            ['number' => '20+', 'label' => 'Năm đào tạo', 'description' => 'Kinh nghiệm đào tạo nhân lực CNTT.'],
            ['number' => '1K+', 'label' => 'Sinh viên', 'description' => 'Sinh viên học tập và thực hành mỗi năm.'],
            ['number' => '50+', 'label' => 'Đối tác', 'description' => 'Doanh nghiệp đồng hành tuyển dụng và thực tập.'],
            ['number' => '95%', 'label' => 'Có việc làm', 'description' => 'Sinh viên sẵn sàng gia nhập thị trường lao động.'],
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
          'type' => 'cms/heading',
          'data' => ['rich_text' => [['type' => 'text', 'text' => 'Giới thiệu khoa', 'marks' => []]], 'meta' => ['level' => 1, 'align' => 'center', 'variant' => 'display']],
        ],
        [
          'type' => 'cms/paragraph',
          'data' => ['rich_text' => [['type' => 'text', 'text' => 'Khoa Công Nghệ Thông Tin là nơi đào tạo, nghiên cứu và kết nối cộng đồng công nghệ ứng dụng.', 'marks' => []]], 'meta' => ['align' => 'center', 'variant' => 'lead']],
        ],
        [
          'type' => 'cms/image',
          'data' => ['meta' => ['url' => 'public/img/about.jpg', 'alt' => 'Giới thiệu khoa', 'ratio' => 'wide', 'variant' => 'rounded']],
        ],
        [
          'type' => 'cms/columns',
          'data' => ['meta' => ['columns' => [
            ['title' => 'Sứ mệnh', 'body' => 'Đào tạo nhân lực CNTT có năng lực thực hành, tư duy hệ thống và tinh thần học tập lâu dài.'],
            ['title' => 'Kết nối', 'body' => 'Gắn kết sinh viên, giảng viên và doanh nghiệp qua các hoạt động học tập, thực tập và nghiên cứu.'],
          ], 'variant' => 'balanced']],
        ],
        [
          'type' => 'cms/card_grid',
          'data' => ['meta' => ['columns' => 3, 'variant' => 'soft', 'items' => [
            ['title' => 'Đào tạo ứng dụng', 'description' => 'Chương trình học chú trọng thực hành và dự án.'],
            ['title' => 'Môi trường mở', 'description' => 'Khuyến khích sáng tạo, chia sẻ và hợp tác.'],
            ['title' => 'Cơ hội nghề nghiệp', 'description' => 'Kết nối thực tập và tuyển dụng với doanh nghiệp.'],
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
