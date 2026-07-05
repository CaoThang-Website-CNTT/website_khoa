<?php

namespace App\Cms;

final class CmsPageSchemaRegistry
{
  private CmsSectionRegistry $_sections;

  public function __construct()
  {
    $this->_sections = CmsStaticPageRenderer::defaultRegistry();
  }

  private const PAGES = [
    'landing' => [
      'title' => 'Trang chủ',
      'slug' => 'landing',
      'route_path' => '/',
      'type' => 'landing_page',
      'layout_mode' => 'block_builder',
      'blocks' => [
        [
          'id' => 'hero',
          'type' => 'sections/landing_hero',
          'locked' => true,
        ],
        [
          'id' => 'landing_about',
          'type' => 'sections/landing_about',
          'locked' => false,
        ],
        [
          'id' => 'why_choose_us',
          'type' => 'sections/why_choose_us',
          'locked' => false,
        ],
        [
          'id' => 'stats',
          'type' => 'sections/stats',
          'locked' => false,
        ],
        [
          'id' => 'partnerships',
          'type' => 'sections/partnerships',
          'locked' => false,
        ],
        [
          'id' => 'newsfeed',
          'type' => 'sections/newsfeed',
          'locked' => true,
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
          'id' => 'breadcrumbs',
          'type' => 'sections/breadcrumbs',
          'locked' => true,
        ],
        [
          'id' => 'about_hero',
          'type' => 'sections/about_hero',
          'locked' => false,
        ],
        [
          'id' => 'vision_mission',
          'type' => 'sections/vision_mission',
          'locked' => false,
        ],
        [
          'id' => 'history',
          'type' => 'sections/history',
          'locked' => false,
        ],
        [
          'id' => 'bento_grid',
          'type' => 'sections/bento_grid',
          'locked' => false,
        ],
      ],
    ],
    'education' => [
      'title' => 'Đào tạo',
      'slug' => 'education',
      'route_path' => '/dao-tao',
      'type' => 'education_page',
      'layout_mode' => 'section_schema',
      'sections' => [
        ['id' => 'education_hub', 'type' => 'sections/education_hub', 'locked' => false],
        ['id' => 'admissions', 'type' => 'sections/admissions', 'locked' => false],
        ['id' => 'programs', 'type' => 'sections/programs', 'locked' => false],
        ['id' => 'outcomes', 'type' => 'sections/outcomes', 'locked' => false],
        ['id' => 'curriculum', 'type' => 'sections/curriculum', 'locked' => false],
      ],
    ],
    /* Retired pages are kept out of the registry; legacy public routes redirect below. */
    /* 'admissions' => [
      'title' => 'Thông tin tuyển sinh',
      'slug' => 'admissions',
      'route_path' => '/dao-tao/tuyen-sinh',
      'type' => 'education_page',
      'layout_mode' => 'section_schema',
      'sections' => [['id' => 'admissions', 'type' => 'sections/admissions', 'locked' => false]],
    ],
    'academic-programs' => [
      'title' => 'Chương trình đào tạo',
      'slug' => 'academic-programs',
      'route_path' => '/dao-tao/chuong-trinh-dao-tao',
      'type' => 'education_page',
      'layout_mode' => 'section_schema',
      'sections' => [['id' => 'programs', 'type' => 'sections/programs', 'locked' => false]],
    ],
    'program-outcomes' => [
      'title' => 'Chuẩn đầu ra',
      'slug' => 'program-outcomes',
      'route_path' => '/dao-tao/chuan-dau-ra',
      'type' => 'education_page',
      'layout_mode' => 'section_schema',
      'sections' => [['id' => 'outcomes', 'type' => 'sections/outcomes', 'locked' => false]],
    ],
    'curriculum' => [
      'title' => 'Danh sách môn học',
      'slug' => 'curriculum',
      'route_path' => '/dao-tao/danh-sach-mon-hoc',
      'type' => 'education_page',
      'layout_mode' => 'section_schema',
      'sections' => [['id' => 'curriculum', 'type' => 'sections/curriculum', 'locked' => false]],
    ], */
  ];

  public function allPages(): array
  {
    return array_map(fn(array $page) => $this->hydratePage($page), self::PAGES);
  }

  public function page(string $slug): ?array
  {
    return isset(self::PAGES[$slug]) ? $this->hydratePage(self::PAGES[$slug]) : null;
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
    $section = $this->hydrateSection($section);

    return [
      'id' => $section['id'],
      'type' => $section['type'],
      'locked' => (bool) ($section['locked'] ?? false),
      'data' => $section['data'] ?? [],
    ];
  }

  private function hydratePage(array $page): array
  {
    $page['sections'] = array_map(fn(array $section) => $this->hydrateSection($section), $page['sections'] ?? []);

    return $page;
  }

  private function hydrateSection(array $section): array
  {
    $definition = $this->_sections->get((string) ($section['type'] ?? ''));

    if ($definition === null) {
      return $section + [
        'label' => $section['id'] ?? 'Unknown section',
        'data' => [],
        'editable_fields' => [],
        'variants' => [],
      ];
    }

    $section['label'] ??= $definition->label();
    $section['data'] = array_replace_recursive($definition->defaults(), is_array($section['data'] ?? null) ? $section['data'] : []);
    $section['editable_fields'] ??= $definition->editableFields();
    $section['field_labels'] = array_replace(
      $definition->fieldLabels(),
      is_array($section['field_labels'] ?? null) ? $section['field_labels'] : [],
    );
    $section['variants'] ??= $definition->variants();

    if (
      str_starts_with((string) ($section['type'] ?? ''), 'sections/education_')
      || in_array($section['type'] ?? '', ['sections/admissions', 'sections/programs', 'sections/outcomes', 'sections/curriculum'], true)
    ) {
      $allRepeaters = EducationPageDefaults::repeaterBlueprints();
      $section['repeaters'] = array_filter(
        $allRepeaters,
        fn(string $path): bool => self::repeaterAppliesToFields($path, $section['editable_fields'] ?? []),
        ARRAY_FILTER_USE_KEY,
      );
    }

    return $section;
  }

  private static function repeaterAppliesToFields(string $repeater, array $fields): bool
  {
    $prefix = rtrim(str_replace('.*', '', $repeater), '.');
    foreach ($fields as $field) {
      if ($field === $repeater || str_starts_with(str_replace('.*', '', $field), $prefix . '.'))
        return true;
    }
    return false;
  }
}
