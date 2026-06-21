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
      'layout_mode' => 'section_schema',
      'sections' => [
        [
          'id' => 'hero',
          'type' => 'sections/landing_hero',
          'label' => 'Hero carousel',
          'locked' => true,
          'data' => [],
          'editable_fields' => [],
        ],
        [
          'id' => 'landing_about',
          'type' => 'sections/landing_about',
          'label' => 'Landing about',
          'locked' => false,
          'data' => [
            'items' => [],
          ],
          'editable_fields' => [
            'items.*.number',
            'items.*.image.src',
            'items.*.image.alt',
            'items.*.card.value',
            'items.*.card.label',
            'items.*.eyebrow',
            'items.*.title',
            'items.*.description',
          ],
        ],
        [
          'id' => 'why_choose_us',
          'type' => 'sections/why_choose_us',
          'label' => 'Why choose us',
          'locked' => false,
          'data' => [
            'badge' => '',
            'title' => '',
            'subtitle' => '',
            'feature' => [],
            'stats' => [],
            'perks' => [],
            'highlights' => [],
          ],
          'editable_fields' => [
            'badge',
            'title',
            'subtitle',
            'feature.image',
            'feature.alt',
            'feature.badge',
            'feature.title',
            'feature.description',
            'feature.cta_label',
            'feature.cta_url',
            'stats.*.number',
            'stats.*.title',
            'stats.*.description',
            'perks.*.icon',
            'perks.*.title',
            'perks.*.description',
            'highlights.*.image',
            'highlights.*.alt',
            'highlights.*.title',
            'highlights.*.description',
          ],
        ],
        [
          'id' => 'stats',
          'type' => 'sections/stats',
          'label' => 'Statistics',
          'locked' => false,
          'data' => [
            'title' => '',
            'subtitle' => '',
            'stats' => [],
            'benefits' => [],
            'cta' => [],
          ],
          'editable_fields' => [
            'title',
            'subtitle',
            'stats.*.icon',
            'stats.*.number',
            'stats.*.label',
            'stats.*.description',
            'benefits.*.icon',
            'benefits.*.title',
            'benefits.*.items.*',
            'cta.title',
            'cta.description',
            'cta.buttons.*.label',
            'cta.buttons.*.url',
            'cta.buttons.*.variant',
          ],
        ],
        [
          'id' => 'newsfeed',
          'type' => 'sections/newsfeed',
          'label' => 'Newsfeed',
          'locked' => true,
          'data' => [],
          'editable_fields' => [],
        ],
      ],
    ],
    'about' => [
      'title' => 'Gioi thieu',
      'slug' => 'about',
      'route_path' => '/gioi-thieu',
      'type' => 'landing_page',
      'layout_mode' => 'section_schema',
      'sections' => [
        [
          'id' => 'breadcrumbs',
          'type' => 'sections/breadcrumbs',
          'label' => 'Breadcrumbs',
          'locked' => true,
          'data' => [],
          'editable_fields' => [],
        ],
        [
          'id' => 'about_hero',
          'type' => 'sections/about_hero',
          'label' => 'About hero',
          'locked' => false,
          'data' => [
            'image' => 'public/img/about.jpg',
            'badge' => '',
            'title' => '',
            'subtitle' => '',
          ],
          'editable_fields' => [
            'image',
            'badge',
            'title',
            'subtitle',
          ],
        ],
        [
          'id' => 'history',
          'type' => 'sections/history',
          'label' => 'History',
          'locked' => false,
          'data' => [
            'sections' => [],
          ],
          'editable_fields' => [
            'sections.*.image.src',
            'sections.*.image.alt',
            'sections.*.image.caption',
            'sections.*.year',
            'sections.*.badge',
            'sections.*.title',
            'sections.*.timeline.*.year',
            'sections.*.timeline.*.description',
          ],
        ],
        [
          'id' => 'bento_grid',
          'type' => 'sections/bento_grid',
          'label' => 'Bento grid',
          'locked' => false,
          'data' => [
            'items' => [],
          ],
          'editable_fields' => [
            'items.*.badge',
            'items.*.image.src',
            'items.*.image.alt',
            'items.*.content',
            'items.*.subContent',
            'items.*.footer',
          ],
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

    return [
      'version' => 1,
      'sections' => array_map(
        fn(array $section) => $this->sectionToDocumentNode($section),
        $schema['sections'],
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
    foreach ($schema['sections'] as $section) {
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
}
