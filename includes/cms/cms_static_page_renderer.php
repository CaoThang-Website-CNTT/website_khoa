<?php

namespace App\Cms;

final class CmsStaticPageRenderer
{
  private CmsSectionRegistry $_sections;

  public function __construct(
    private array $context = [],
    ?CmsSectionRegistry $sections = null,
    private string $pageSlug = '',
    private bool $editorMode = false,
  ) {
    $this->_sections = $sections ?? self::defaultRegistry();
  }

  public function render(array $document): string
  {
    $html = '';
    $context = new CmsRenderContext($this->pageSlug, '', $this->context, $this->editorMode);

    foreach ($document['sections'] ?? [] as $section) {
      if (!is_array($section)) {
        continue;
      }
      $html .= $this->_sections->renderSection($section, $context);
    }
    return $html;
  }

  public function renderPreviewDocument(array $document): string
  {
    $public = htmlspecialchars(url('public'), ENT_QUOTES, 'UTF-8');
    $styles = [
      'css/fontawesome/fontawesome.min.css',
      'css/fontawesome/solid.min.css',
      'css/fontawesome/regular.min.css',
      'css/fonts.css',
      'css/base.css',
      'css/common.css',
      'css/main.css',
      'css/landing.css',
      'css/block_preview.css',
      'css/cms_page_editor.css',
    ];
    if ($this->pageSlug === 'faculty') {
      $styles[] = 'css/pages/faculty.css';
    }
    $links = implode('', array_map(
      fn(string $file): string => '<link rel="stylesheet" href="' . $public . '/' . $file . '">',
      $styles,
    ));
    $scripts = '<script src="' . $public . '/js/accordion.js"></script>';

    return '<!doctype html><html lang="vi"><head><meta charset="UTF-8">'
      . '<meta name="viewport" content="width=device-width,initial-scale=1">'
      . $links
      . '<style>html,body{margin:0;min-height:0;overflow:visible;scrollbar-width:none}body::-webkit-scrollbar{display:none}</style>'
      . '</head><body class="cms-preview-body"><main class="cms-live-page">'
      . $this->render($document)
      . '</main>' . $scripts . '</body></html>';
  }

  public static function defaultRegistry(): CmsSectionRegistry
  {
    $renderer = new self([], new CmsSectionRegistry());
    $registry = new CmsSectionRegistry();

    $registry->register(new CmsCallbackSectionDefinition(
      'sections/landing_hero',
      'Hero carousel',
      [],
      [],
      ['default' => 'Default'],
      fn(array $data, CmsRenderContext $context): string => $renderer->renderHero($context),
    ));

    $registry->register(new CmsCallbackSectionDefinition(
      'sections/newsfeed',
      'Newsfeed',
      [],
      [],
      ['default' => 'Default'],
      fn(array $data, CmsRenderContext $context): string => $renderer->renderNewsfeed($context),
    ));

    $registry->register(new CmsCallbackSectionDefinition(
      'sections/breadcrumbs',
      'Breadcrumbs',
      [],
      [],
      ['default' => 'Default'],
      fn(array $data, CmsRenderContext $context): string => $renderer->renderBreadcrumbs($context),
    ));

    $registry->register(new CmsCallbackSectionDefinition(
      'sections/landing_about',
      'Landing about',
      ['variant' => 'default', 'items' => []],
      [
        'items.*.number',
        'items.*.image.src',
        'items.*.image.alt',
        'items.*.card.value',
        'items.*.card.label',
        'items.*.eyebrow',
        'items.*.title',
        'items.*.description',
      ],
      ['default' => 'Default'],
      fn(array $data, CmsRenderContext $context): string => $renderer->renderLandingAbout($data, $context),
      [
        'items.*.number' => 'Number',
        'items.*.image.src' => 'Image',
        'items.*.image.alt' => 'Image alt text',
        'items.*.card.value' => 'Card value',
        'items.*.card.label' => 'Card label',
        'items.*.eyebrow' => 'Eyebrow',
        'items.*.title' => 'Title',
        'items.*.description' => 'Description',
      ],
    ));

    $registry->register(new CmsCallbackSectionDefinition(
      'sections/why_choose_us',
      'Why choose us',
      ['variant' => 'default', 'badge' => '', 'title' => '', 'subtitle' => '', 'feature' => [], 'stats' => [], 'perks' => [], 'highlights' => []],
      [
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
      ['default' => 'Default'],
      fn(array $data, CmsRenderContext $context): string => $renderer->renderWhyChooseUs($data, $context),
      [
        'badge' => 'Badge',
        'title' => 'Title',
        'subtitle' => 'Subtitle',
        'feature.image' => 'Feature image',
        'feature.alt' => 'Feature image alt text',
        'feature.badge' => 'Feature badge',
        'feature.title' => 'Feature title',
        'feature.description' => 'Feature description',
        'feature.cta_label' => 'Feature button label',
        'feature.cta_url' => 'Feature button URL',
        'stats.*.number' => 'Stat number',
        'stats.*.title' => 'Stat title',
        'stats.*.description' => 'Stat description',
        'perks.*.icon' => 'Perk icon classes',
        'perks.*.title' => 'Perk title',
        'perks.*.description' => 'Perk description',
        'highlights.*.image' => 'Highlight image',
        'highlights.*.alt' => 'Highlight image alt text',
        'highlights.*.title' => 'Highlight title',
        'highlights.*.description' => 'Highlight description',
      ],
    ));

    $registry->register(new CmsCallbackSectionDefinition(
      'sections/stats',
      'Statistics',
      ['variant' => 'default', 'title' => '', 'subtitle' => '', 'stats' => [], 'benefits' => [], 'cta' => []],
      [
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
      ['default' => 'Default'],
      fn(array $data, CmsRenderContext $context): string => $renderer->renderStats($data, $context),
      [
        'title' => 'Title',
        'subtitle' => 'Subtitle',
        'stats.*.icon' => 'Stat icon classes',
        'stats.*.number' => 'Stat number',
        'stats.*.label' => 'Stat label',
        'stats.*.description' => 'Stat description',
        'benefits.*.icon' => 'Benefit icon classes',
        'benefits.*.title' => 'Benefit title',
        'benefits.*.items.*' => 'Benefit item',
        'cta.title' => 'CTA title',
        'cta.description' => 'CTA description',
        'cta.buttons.*.label' => 'Button label',
        'cta.buttons.*.url' => 'Button URL',
        'cta.buttons.*.variant' => 'Button variant',
      ],
    ));

    $registry->register(new CmsCallbackSectionDefinition(
      'sections/partnerships',
      'Partnerships',
      self::partnershipDefaults(),
      [
        'title',
        'subtitle',
        'partners.*.name',
        'partners.*.url',
        'partners.*.image.src',
        'partners.*.image.alt',
        'partners.*.description',
        'partners.*.description_source_url',
      ],
      ['default' => 'Default'],
      fn(array $data, CmsRenderContext $context): string => $renderer->renderPartnerships($data, $context),
      [
        'title' => 'Title',
        'subtitle' => 'Subtitle',
        'partners.*.name' => 'Partner name',
        'partners.*.url' => 'Partner URL',
        'partners.*.image.src' => 'Partner logo',
        'partners.*.image.alt' => 'Partner logo alt text',
        'partners.*.description' => 'Partner description',
        'partners.*.description_source_url' => 'Description source URL',
      ],
    ));

    $registry->register(new CmsCallbackSectionDefinition(
      'sections/about_hero',
      'About hero',
      ['variant' => 'default', 'image' => 'public/img/about.jpg', 'badge' => '', 'title' => '', 'subtitle' => ''],
      ['image', 'badge', 'title', 'subtitle'],
      ['default' => 'Default'],
      fn(array $data, CmsRenderContext $context): string => $renderer->renderAboutHero($data, $context),
      [
        'image' => 'Hero image',
        'badge' => 'Badge',
        'title' => 'Title',
        'subtitle' => 'Subtitle',
      ],
    ));

    $registry->register(new CmsCallbackSectionDefinition(
      'sections/teacher_directory',
      'Teacher directory',
      ['variant' => 'default', 'teachers' => []],
      [
        'teachers.*.name',
        'teachers.*.role',
        'teachers.*.phone',
        'teachers.*.email',
        'teachers.*.portrait.src',
      ],
      ['default' => 'Default'],
      fn(array $data, CmsRenderContext $context): string => $renderer->renderTeacherDirectory($data, $context),
      [
        'teachers.*.name' => 'Họ tên',
        'teachers.*.role' => 'Chức vụ / vị trí',
        'teachers.*.phone' => 'Số điện thoại',
        'teachers.*.email' => 'Email',
        'teachers.*.portrait.src' => 'Ảnh chân dung',
      ],
    ));

    $registry->register(new CmsCallbackSectionDefinition(
      'sections/history',
      'History',
      ['variant' => 'default', 'sections' => []],
      [
        'sections.*.image.src',
        'sections.*.image.alt',
        'sections.*.image.caption',
        'sections.*.year',
        'sections.*.badge',
        'sections.*.title',
        'sections.*.timeline.*.year',
        'sections.*.timeline.*.description',
      ],
      ['default' => 'Default'],
      fn(array $data, CmsRenderContext $context): string => $renderer->renderHistory($data, $context),
      [
        'sections.*.image.src' => 'History image',
        'sections.*.image.alt' => 'History image alt text',
        'sections.*.image.caption' => 'Image caption',
        'sections.*.year' => 'Year',
        'sections.*.badge' => 'Badge',
        'sections.*.title' => 'Title',
        'sections.*.timeline.*.year' => 'Timeline year',
        'sections.*.timeline.*.description' => 'Timeline description',
      ],
    ));

    $registry->register(new CmsCallbackSectionDefinition(
      'sections/bento_grid',
      'Bento grid',
      ['variant' => 'default', 'items' => []],
      [
        'items.*.badge',
        'items.*.image.src',
        'items.*.image.alt',
        'items.*.content',
        'items.*.subContent',
        'items.*.footer',
        'items.*.background',
      ],
      ['default' => 'Default'],
      fn(array $data, CmsRenderContext $context): string => $renderer->renderBentoGrid($data, $context),
      [
        'items.*.badge' => 'Badge',
        'items.*.image.src' => 'Image',
        'items.*.image.alt' => 'Image alt text',
        'items.*.content' => 'Content',
        'items.*.subContent' => 'Sub content',
        'items.*.footer' => 'Footer',
        'items.*.background' => 'Background color',
      ],
    ));

    $registry->register(new CmsCallbackSectionDefinition(
      'sections/vision_mission',
      'Vision and mission',
      self::visionMissionDefaults(),
      ['title', 'introduction', 'vision_title', 'vision', 'mission_title', 'mission'],
      ['default' => 'Default'],
      fn(array $data, CmsRenderContext $context): string => $renderer->renderVisionMission($data, $context),
    ));

    $educationRenderer = new EducationSectionRenderer();
    $educationSections = [
      'sections/education_hub' => ['Education hub', EducationPageDefaults::hub()],
      'sections/admissions' => ['Admissions', EducationPageDefaults::admissions()],
      'sections/programs' => ['Academic programs', EducationPageDefaults::programsSection()],
      'sections/outcomes' => ['Program outcomes', EducationPageDefaults::outcomes()],
      'sections/curriculum' => ['Curriculum', EducationPageDefaults::curriculum()],
    ];

    foreach ($educationSections as $type => [$label, $defaults]) {
      $fields = self::educationEditableFields($type);
      $registry->register(new CmsCallbackSectionDefinition(
        $type,
        $label,
        $defaults,
        $fields,
        ['default' => 'Default'],
        fn(array $data, CmsRenderContext $context): string => $educationRenderer->render($type, $data, $context),
        array_combine($fields, array_map(fn(string $field): string => self::educationFieldLabel($field), $fields)) ?: [],
      ));
    }

    return $registry;
  }

  private static function educationEditableFields(string $type): array
  {
    $header = ['title', 'description'];
    return match ($type) {
      'sections/education_hub' => $header,
      'sections/admissions' => ['title', 'cta_label', 'cta_url'],
      'sections/programs' => [...$header, 'programs.*.key', 'programs.*.short_name', 'programs.*.name', 'programs.*.summary', 'programs.*.duration', 'programs.*.credits', 'programs.*.source_year', 'programs.*.updated_at', 'programs.*.career', 'programs.*.objectives.*', 'programs.*.specializations.*'],
      'sections/outcomes' => [...$header, 'programs.*.key', 'programs.*.short_name', 'programs.*.name', 'programs.*.source_year', 'programs.*.updated_at', 'programs.*.objectives.*', 'programs.*.outcomes.*'],
      'sections/curriculum' => [...$header, 'programs.*.key', 'programs.*.short_name', 'programs.*.name', 'programs.*.source_year', 'programs.*.updated_at', 'programs.*.credits', 'programs.*.semesters.*.key', 'programs.*.semesters.*.name', 'programs.*.semesters.*.courses.*.code', 'programs.*.semesters.*.courses.*.name', 'programs.*.semesters.*.courses.*.credits', 'programs.*.semesters.*.courses.*.theory', 'programs.*.semesters.*.courses.*.practice'],
      default => $header,
    };
  }

  private static function educationFieldLabel(string $field): string
  {
    $leaf = str_replace('_', ' ', basename(str_replace('.', '/', str_replace('.*', '', $field))));
    return ucfirst($leaf);
  }

  private function renderHero(CmsRenderContext $context): string
  {
    ob_start();
    $carouselSlides = $context->value('carouselSlides', []);
?>
    <section class="relative" id="hero-section" <?= $context->sectionAttributes() ?>>
      <div class="container">
        <?php $this->renderCarousel($carouselSlides); ?>
      </div>
      <div class="wave-container">
        <svg class="wave" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 60" preserveAspectRatio="none" fill="none">
          <path
            d="M0 60L48 48.3333C96 38.6667 192 19.3333 288 9.66667C384 0 480 0 576 4.83333C672 9.66667 768 19.3333 864 24.1667C960 29 1056 29 1152 24.1667C1248 19.3333 1344 9.66667 1392 4.83333L1440 0V60H1392C1344 60 1248 60 1152 60C1056 60 960 60 864 60C768 60 672 60 576 60C480 60 384 60 288 60C192 60 96 60 48 60H0Z"
            fill="currentColor"></path>
        </svg>
      </div>
    </section>
  <?php
    return (string) ob_get_clean();
  }

  private function renderCarousel(array $carouselSlides): void
  {
    if (empty($carouselSlides)) {
      return;
    }
  ?>
    <div class="carousel" id="landingCarousel">
      <div class="carousel__inner" id="carouselInner">
        <?php foreach ($carouselSlides as $slide): ?>
          <?php if (!$slide->isActive())
            continue; ?>
          <div
            class="carousel__item <?= $slide->isCustom() ? 'carousel__item--custom' : 'carousel__item--standard' ?> flex justify-between items-center">
            <?php if ($slide->isCustom()): ?>
              <?= $slide->custom_html ?>
            <?php else: ?>
              <div class="carousel__content flex flex-col">
                <h2 class="carousel__title font-normal">
                  <?= $this->e($slide->title) ?>
                  <?php if (!empty($slide->title_highlight)): ?><span><?= $this->e($slide->title_highlight) ?></span><?php endif; ?>
                </h2>
                <?php if (!empty($slide->description)): ?>
                  <p class="carousel__description"><?= nl2br($this->e($slide->description)) ?></p><?php endif; ?>
                <?php if ($slide->hasCta()): ?>
                  <div class="carousel__cta">
                    <a href="<?= $this->e(url($slide->cta_url)) ?>" data-variant="<?= $this->e($slide->cta_variant) ?>"
                      class="btn px-8 py-2 rounded-3xl bouncy-btn"><?= $this->e($slide->cta_label) ?></a>
                  </div>
                <?php endif; ?>
              </div>
              <div class="image-wrapper carousel__image-wrapper rounded-3xl">
                <img src="<?= $this->e(url('public/media/' . $slide->media->file_path)) ?>"
                  alt="<?= $this->e($slide->media->alt_text ?: $slide->title) ?>" class="image carousel__image"
                  <?= !isset($isFirstSlideRendered) ? 'fetchpriority="high"' : 'loading="lazy" decoding="async"' ?>>
              </div>
            <?php endif; ?>
          </div>
          <?php $isFirstSlideRendered = true; ?>
        <?php endforeach; ?>
      </div>
      <button class="carousel__control carousel__control--prev"><i class="fa-solid fa-angle-left"></i></button>
      <button class="carousel__control carousel__control--next"><i class="fa-solid fa-angle-right"></i></button>
      <div class="carousel__indicators"></div>
    </div>
  <?php
  }

  private function renderBreadcrumbs(CmsRenderContext $context): string
  {
    $links = [
      ['icon' => '<i class="fa-regular fa-house"></i>', 'url' => url('/'), 'title' => 'Trang chủ'],
      ['url' => url('/gioi-thieu'), 'title' => 'Giới Thiệu'],
    ];
    if ($context->pageSlug() === 'faculty') {
      $links[] = ['url' => url('/giang-vien'), 'title' => 'Đội ngũ giảng viên'];
    }
    ob_start();
  ?>
    <section class="site-breadcrumbs py-4" <?= $context->sectionAttributes() ?>>
      <div class="container">
        <div class="container-wrapper">
          <?php
          include_once BASE_PATH . '/templates/components/breadcrumb.php';
          renderBreadcrumb($links);
          ?>
        </div>
      </div>
    </section>
  <?php
    return (string) ob_get_clean();
  }

  private function renderLandingAbout(array $data, CmsRenderContext $context): string
  {
    ob_start();
  ?>
    <section class="relative container py-16" id="landing-about-section" <?= $context->sectionAttributes() ?>>
      <h2 class="sr-only">About Us</h2>
      <div class="container-wrapper">
        <div class="landing-about-container flex flex-col gap-12 md:gap-0">
          <?php foreach ($this->items($data, 'items') as $index => $item): ?>
            <div class="flex gap-4 md:gap-12 flex-col md:<?= $index % 2 === 0 ? 'flex-row-reverse' : 'flex-row' ?>" <?= $context->repeaterItemAttributes('items', $index) ?>>
              <div class="flex-1 relative">
                <div class="overflow-hidden rounded-3xl">
                  <div class="image-wrapper">
                    <img class="image w-full h-full" <?= $context->imageAttributes("items.$index.image.src") ?> src="<?= $this->e($this->asset($item['image']['src'] ?? '')) ?>"
                      alt="<?= $this->e($item['image']['alt'] ?? '') ?>" loading="lazy" decoding="async">
                  </div>
                </div>
                <div class="landing-about-item__card absolute z-10 rounded-3xl p-3 md:p-6 flex flex-col gap-1">
                  <div class="landing-about-item__card-main-content text-lg md:text-5xl" <?= $context->textAttributes("items.$index.card.value") ?>><?= $this->e($item['card']['value'] ?? '') ?></div>
                  <div class="landing-about-item__card-sub-content md:text-sm" <?= $context->textAttributes("items.$index.card.label") ?>><?= $this->e($item['card']['label'] ?? '') ?></div>
                </div>
              </div>
              <div class="flex-1 flex flex-col justify-center gap-4">
                <p class="number-of-text text-7xl hidden md:block" <?= $context->textAttributes("items.$index.number") ?>><?= $this->e($item['number'] ?? '') ?></p>
                <p class="landing-about-item__sub-title text-xs uppercase font-medium" <?= $context->textAttributes("items.$index.eyebrow") ?>><?= $this->e($item['eyebrow'] ?? '') ?></p>
                <p class="about-item__title text-4xl" <?= $context->textAttributes("items.$index.title") ?>><?= $this->e($item['title'] ?? '') ?></p>
                <p class="landing-about-item__content" <?= $context->textAttributes("items.$index.description", true) ?>><?= $this->e($item['description'] ?? '') ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php
    return (string) ob_get_clean();
  }

  private function renderWhyChooseUs(array $data, CmsRenderContext $context): string
  {
    ob_start();
    $feature = is_array($data['feature'] ?? null) ? $data['feature'] : [];
  ?>
    <section class="wcu relative container py-16" id="why-choose-us-section" <?= $context->sectionAttributes() ?>>
      <div class="wcu__container container-wrapper">
        <div class="wcu__header flex flex-col justify-center items-center gap-2 md:gap-4 mb-8 md:mb-12">
          <div class="wcu__badge section__badge px-4 py-2 rounded-3xl text-sm mb-2 md:mb-4" <?= $context->textAttributes('badge') ?>><?= $this->e($data['badge'] ?? '') ?></div>
          <h2 class="wcu__title section__title" <?= $context->textAttributes('title') ?>><?= $this->e($data['title'] ?? '') ?></h2>
          <p class="wcu__subtitle section__sub-title" <?= $context->textAttributes('subtitle', true) ?>><?= $this->e($data['subtitle'] ?? '') ?></p>
        </div>
        <div class="wcu__content flex flex-col items-center justify-center">
          <div class="wcu__features-grid grid grid-cols-2 md:grid-cols-3 grid-rows-2 gap-3 md:gap-6 mb-6 self-stretch">
            <div
              class="wcu__feature-card wcu__feature-card--large wcu-feature-container overflow-hidden relative row-start-1 col-span-2 row-span-1 md:row-span-2 rounded-3xl image-wrapper">
              <img class="wcu__feature-card-image image" <?= $context->imageAttributes('feature.image') ?> src="<?= $this->e($this->asset($feature['image'] ?? '')) ?>"
                alt="<?= $this->e($feature['alt'] ?? '') ?>" loading="lazy" decoding="async">
              <div
                class="wcu__feature-card-content absolute inset-0 flex flex-col justify-end items-start gap-2 md:gap-4 p-3 md:p-6">
                <span class="wcu__feature-card-badge badge"
                  data-variant="primary"><?= $this->e($feature['badge'] ?? '') ?></span>
                <h3 class="wcu__feature-card-title text-md md:text-3xl font-semibold">
                  <?= $this->e($feature['title'] ?? '') ?></h3>
                <p class="wcu__feature-card-description text-xs md:text-md font-normal">
                  <?= $this->e($feature['description'] ?? '') ?></p>
                <a href="<?= $this->e($this->siteUrl($feature['cta_url'] ?? '#')) ?>" <?= $context->linkAttributes('feature.cta_url') ?>
                  class="wcu__feature-card-link md:text-md font-normal"><?= $this->e($feature['cta_label'] ?? '') ?> <i
                    class="fa-solid fa-arrow-up-right-from-square"></i></a>
              </div>
            </div>
            <?php foreach ($this->items($data, 'stats') as $index => $stat): ?>
              <div
                <?= $context->repeaterItemAttributes('stats', $index) ?>
                class="wcu__stat-card <?= $index === 0 ? 'wcu__stat-card--primary col-start-1 md:col-start-3 row-start-2 md:row-start-1' : 'wcu__stat-card--gradient col-start-2 md:col-start-3 row-start-2 md:row-start-2' ?> rounded-3xl p-3 md:p-6 flex flex-col gap-2 justify-center">
                <?php if (trim((string) ($stat['number'] ?? '')) !== ''): ?>
                  <h2
                    class="wcu__stat-card-number flex-1 md:flex-none flex justify-center items-center md:block text-6xl md:text-7xl font-bold">
                    <?= $this->e($stat['number']) ?></h2>
                <?php endif; ?>
                <div class="wcu__stat-card-content flex flex-col gap-2">
                  <p class="wcu__stat-card-title md:text-xl font-semibold"><?= $this->e($stat['title'] ?? '') ?></p>
                  <p class="wcu__stat-card-description text-xs md:text-md font-normal">
                    <?= $this->e($stat['description'] ?? '') ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div
            class="wcu__perks-list grid grid-cols-2 grid-rows-2 md:flex justify-center items-stretch self-stretch gap-3 md:gap-6 mb-6">
            <?php foreach ($this->items($data, 'perks') as $perk): ?>
              <div class="wcu__perk-item flex flex-col items-start justify-start flex-1 rounded-3xl p-3 md:p-6">
                <div class="wcu__perk-item-icon-wrapper flex justify-center items-center rounded-full text-4xl mb-4 p-3"><i
                    class="<?= $this->e($perk['icon'] ?? 'fa-solid fa-circle') ?> wcu__perk-item-icon"></i></div>
                <h4 class="wcu__perk-item-title md:text-md font-semibold mb-2"><?= $this->e($perk['title'] ?? '') ?></h4>
                <p class="wcu__perk-item-description text-xs md:text-sm font-normal">
                  <?= $this->e($perk['description'] ?? '') ?></p>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="wcu__highlights-list self-stretch grid grid-rows-2 md:grid-rows-1 md:grid-cols-2 gap-3 md:gap-6">
            <?php foreach ($this->items($data, 'highlights') as $index => $highlight): ?>
              <div class="wcu__highlight-item flex-1 overflow-hidden relative rounded-3xl image-wrapper text-white" <?= $context->repeaterItemAttributes('highlights', $index) ?>>
                <img class="wcu__highlight-item-image image" <?= $context->imageAttributes("highlights.$index.image") ?> src="<?= $this->e($this->asset($highlight['image'] ?? '')) ?>"
                  alt="<?= $this->e($highlight['alt'] ?? '') ?>" loading="lazy" decoding="async">
                <div
                  class="wcu__highlight-item-content <?= $index === 0 ? 'wcu__highlight-item-content--blue' : 'wcu__highlight-item-content--green' ?> absolute inset-0 flex flex-col justify-end items-start p-3 md:p-6">
                  <h3 class="wcu__highlight-item-title text-md md:text-2xl font-semibold mb-2" <?= $context->textAttributes("highlights.$index.title") ?>><?= $this->e($highlight['title'] ?? '') ?></h3>
                  <p class="wcu__highlight-item-description text-xs md:text-sm font-normal" <?= $context->textAttributes("highlights.$index.description", true) ?>><?= $this->e($highlight['description'] ?? '') ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </section>
  <?php
    return (string) ob_get_clean();
  }

  private function renderStats(array $data, CmsRenderContext $context): string
  {
    ob_start();
    $cta = is_array($data['cta'] ?? null) ? $data['cta'] : [];
  ?>
    <section class="relative container py-16" id="stats-section" <?= $context->sectionAttributes() ?>>
      <div class="container-wrapper">
        <div class="flex flex-col justify-center items-center gap-2 md:gap-4 mb-8 md:mb-12">
          <h2 class="section__title" <?= $context->textAttributes('title') ?>><?= $this->e($data['title'] ?? '') ?></h2>
          <p class="section__sub-title" <?= $context->textAttributes('subtitle', true) ?>><?= $this->e($data['subtitle'] ?? '') ?></p>
        </div>
        <div class="flex flex-col items-stretch justify-center gap-3 md:gap-6">
          <div class="stats__grid grid grid-cols-2 grid-rows-2 md:grid-cols-4 md:grid-rows-1 gap-3 md:gap-6">
            <?php foreach ($this->items($data, 'stats') as $stat): ?>
              <div class="stats__stat-card flex flex-1 flex-col items-center gap-3 md:gap-6 rounded-3xl p-3 md:p-6">
                <div class="stats__stat-card-icon-wrapper flex items-center justify-center rounded-full"><i
                    class="<?= $this->e($stat['icon'] ?? 'fa-solid fa-award') ?> stats__stat-card-icon"></i></div>
                <div class="flex flex-col gap-1 items-center">
                  <?php if (trim((string) ($stat['number'] ?? '')) !== ''): ?>
                    <h3 class="stats__stat-card-number text-3xl md:text-5xl font-bold"><?= $this->e($stat['number']) ?></h3>
                  <?php endif; ?>
                  <h4 class="stats__stat-card-label font-semibold"><?= $this->e($stat['label'] ?? '') ?></h4>
                  <p class="stats__stat-card-description text-xs md:text-sm text-center">
                    <?= $this->e($stat['description'] ?? '') ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div
            class="stats__benefits-grid grid grid-cols-1 md:grid-cols-2 grid-rows-2 md:grid-rows-1 gap-3 md:gap-6 items-stretch">
            <?php foreach ($this->items($data, 'benefits') as $benefit): ?>
              <div class="stats__benefit-card flex-1 flex flex-col gap-3 md:gap-6 p-3 md:p-6 rounded-3xl">
                <div class="stats__benefit-card-header flex gap-2 md:gap-4 items-center">
                  <div class="stats__benefit-card-icon-wrapper flex justify-center items-center rounded-full"><i
                      class="<?= $this->e($benefit['icon'] ?? 'fa-solid fa-building-columns') ?> stats__benefit-card-icon"></i>
                  </div>
                  <h3 class="stats__benefit-card-title text-lg md:text-2xl font-semibold">
                    <?= $this->e($benefit['title'] ?? '') ?></h3>
                </div>
                <ul class="stats__benefit-card-list flex flex-col gap-2 md:gap-4">
                  <?php foreach ($this->items($benefit, 'items') as $item): ?>
                    <li class="stats__benefit-card-item flex items-center gap-2"><span
                        class="stats__benefit-card-item-icon rounded-full"></span>
                      <p class="stats__benefit-card-item-text"><?= $this->e($item) ?></p>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="stats__cta flex flex-col items-center p-3 md:p-12 rounded-3xl">
            <h3 class="stats__cta-title text-center text-xl md:text-3xl font-semibold mb-2">
              <?= $this->e($cta['title'] ?? '') ?></h3>
            <p class="stats__cta-description text-center text-sm md:text-xl font-light mb-6">
              <?= $this->e($cta['description'] ?? '') ?></p>
            <div class="stats__cta-buttons flex flex-col w-full md:w-fit md:flex-row gap-2 md:gap-4">
              <?php foreach ($this->items($cta, 'buttons') as $index => $button): ?>
                <a href="<?= $this->e($this->siteUrl($button['url'] ?? '#')) ?>" <?= $context->linkAttributes("cta.buttons.$index.url") ?><?= $context->repeaterItemAttributes('cta.buttons', $index) ?>
                  data-variant="<?= $this->e($button['variant'] ?? 'outline') ?>"
                  class="stats__cta-button stats__cta-button--secondary flex items-center px-8 py-4 btn bouncy-btn rounded-full <?= $index === 1 ? 'bg-transparent' : '' ?>"><?= $this->e($button['label'] ?? '') ?></a>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </section>
  <?php
    return (string) ob_get_clean();
  }

  private function renderPartnerships(array $data, CmsRenderContext $context): string
  {
    $partners = array_values(array_filter(
      $this->items($data, 'partners'),
      fn(mixed $partner): bool => is_array($partner) && trim((string) ($partner['image']['src'] ?? '')) !== '',
    ));

    if ($partners === []) {
      return '';
    }

    ob_start();
  ?>
    <section class="partnerships relative container py-16" id="partnerships-section" <?= $context->sectionAttributes() ?>>
      <div class="container-wrapper">
        <div class="partnerships__header flex flex-col justify-center items-center gap-2 md:gap-4 mb-8 md:mb-12">
          <h2 class="partnerships__title section__title" <?= $context->textAttributes('title') ?>><?= $this->e($data['title'] ?? '') ?></h2>
          <p class="partnerships__subtitle section__sub-title" <?= $context->textAttributes('subtitle', true) ?>><?= $this->e($data['subtitle'] ?? '') ?></p>
        </div>
        <div class="partnerships__viewport" aria-label="<?= $this->e($data['title'] ?? 'Đối tác doanh nghiệp') ?>">
          <div class="partnerships__track">
            <?php for ($loop = 0; $loop < 2; $loop++): ?>
              <div class="partnerships__group" aria-hidden="<?= $loop === 1 ? 'true' : 'false' ?>">
                <?php foreach ($partners as $partnerIndex => $partner): ?>
                  <?php
                  $name = trim((string) ($partner['name'] ?? ''));
                  $url = $this->safeExternalUrl($partner['url'] ?? '');
                  $src = $this->asset($partner['image']['src'] ?? '');
                  $alt = trim((string) ($partner['image']['alt'] ?? $name));
                  ?>
                  <a class="partnerships__item" href="<?= $this->e($url) ?>" <?= $context->linkAttributes("partners.$partnerIndex.url") ?><?= $context->repeaterItemAttributes('partners', $partnerIndex) ?> target="_blank" rel="noopener noreferrer" <?= $loop === 1 ? 'tabindex="-1"' : '' ?>
                    aria-label="<?= $this->e($name !== '' ? 'Mở website ' . $name : 'Mở website đối tác') ?>">
                    <img class="partnerships__logo" <?= $context->imageAttributes("partners.$partnerIndex.image.src") ?> src="<?= $this->e($src) ?>" alt="<?= $this->e($alt) ?>" loading="lazy" decoding="async">
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endfor; ?>
          </div>
        </div>
      </div>
    </section>
  <?php
    return (string) ob_get_clean();
  }

  private function renderNewsfeed(CmsRenderContext $context): string
  {
    include_once BASE_PATH . '/templates/components/news_card.php';
    ob_start();
    $featuredNews = $context->value('featuredNews', []);
    $latestNewsItems = $context->value('latestNewsItems', []);
  ?>
    <section class="relative container py-16" id="newsfeed-section" <?= $context->sectionAttributes() ?>>
      <div class="container-wrapper">
        <div class="flex flex-col justify-center items-center gap-2 md:gap-4 mb-8 md:mb-12">
          <h2 class="section__title">Tin tức &amp; Sự kiện</h2>
          <p class="section__sub-title">Cập nhật những tin tức mới nhất về hoạt động của khoa, thành tích sinh viên và các
            sự kiện sắp tới</p>
        </div>
        <div class="newsfeed__content-wrapper flex flex-col gap-16">
          <div class="flex flex-col gap-3 md:gap-6">
            <?php if (!empty($featuredNews)):
              $featured = $featuredNews[0]; ?>
              <?php renderLandingNewsCard($featured, ['variant' => 'featured', 'category_label' => 'Nổi bật', 'show_read_more' => true, 'class' => 'super-landing-featured-news landing-featured-news relative overflow-hidden rounded-3xl']); ?>
            <?php endif; ?>
            <div class="flex flex-col md:flex-row gap-3 md:gap-6 justify-center items-stretch self-stretch">
              <?php for ($i = 1, $count = count($featuredNews); $i < 4 && $i < $count; $i++):
                $news = $featuredNews[$i]; ?>
                <?php renderLandingNewsCard($news, ['variant' => 'secondary', 'category_label' => $news->categories[0]->name ?? 'Tin tức', 'class' => 'landing-sub-featured-news flex-1']); ?>
              <?php endfor; ?>
            </div>
          </div>
          <div id="newsfeed-other" class="flex flex-col gap-4">
            <div class="newsfeed__other-header flex">
              <h2 class="newsfeed__other-title text-2xl md:text-4xl font-medium flex-1">Tin tức khác</h2>
              <a href="<?= $this->e(url('tin-tuc')) ?>"
                class="newsfeed__view-all-link md:text-md font-medium link-hover--underline">Xem thêm <i
                  class="fa-solid fa-arrow-up-right-from-square"></i></a>
            </div>
            <div class="flex flex-col md:flex-row gap-3 md:gap-6 justify-center items-stretch self-stretch">
              <?php for ($i = 0, $count = count($latestNewsItems); $i < 3 && $i < $count; $i++):
                $news = $latestNewsItems[$i]; ?>
                <?php renderLandingNewsCard($news, ['variant' => 'standard', 'category_label' => $news->categories[0]->name ?? 'Tin tức']); ?>
              <?php endfor; ?>
            </div>
          </div>
        </div>
      </div>
    </section>
  <?php
    return (string) ob_get_clean();
  }

  private function renderAboutHero(array $data, CmsRenderContext $context): string
  {
    ob_start();
  ?>
    <section class="relative" <?= $context->sectionAttributes() ?>>
      <div class="page-thumbnail__media"><img class="w-full h-full object-cover" <?= $context->imageAttributes('image') ?>
          src="<?= $this->e($this->asset($data['image'] ?? '')) ?>" alt=""></div>
      <div class="page-thumbnail__overlay absolute inset-0 flex justify-center items-center">
        <div class="container">
          <div class="container-wrapper">
            <div class="page-thumbnail__content flex flex-col justify-center items-center gap-6 text-center">
              <span class="badge" data-variant="primary" <?= $context->textAttributes('badge') ?>><?= $this->e($data['badge'] ?? '') ?></span>
              <div class="page-thumbnail__title" <?= $context->textAttributes('title') ?>><?= $this->e($data['title'] ?? '') ?></div>
              <div class="page-thumbnail__subtitle" <?= $context->textAttributes('subtitle', true) ?>><?= $this->e($data['subtitle'] ?? '') ?></div>
            </div>
          </div>
        </div>
      </div>
    </section>
  <?php
    return (string) ob_get_clean();
  }

  private function renderTeacherDirectory(array $data, CmsRenderContext $context): string
  {
    $teachers = array_values(array_filter(
      $this->items($data, 'teachers'),
      static fn(mixed $teacher): bool => is_array($teacher) && trim((string) ($teacher['name'] ?? '')) !== '',
    ));

    ob_start();
  ?>
    <section class="faculty-directory py-12" <?= $context->sectionAttributes() ?>>
      <div class="container">
        <div class="container-wrapper">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" data-faculty-grid>
            <?php foreach ($teachers as $index => $teacher): ?>
              <?php
              $name = trim((string) ($teacher['name'] ?? ''));
              $role = trim((string) ($teacher['role'] ?? ''));
              $phone = trim((string) ($teacher['phone'] ?? ''));
              $email = trim((string) ($teacher['email'] ?? ''));
              $portrait = is_array($teacher['portrait'] ?? null) ? $teacher['portrait'] : [];
              ?>
              <article class="card faculty-card overflow-hidden" data-faculty-card tabindex="0" role="button" aria-expanded="false" <?= $context->repeaterItemAttributes('teachers', $index) ?>>
                <div class="card__header faculty-card__portrait">
                  <img class="w-full h-full object-cover" <?= $context->imageAttributes("teachers.$index.portrait.src") ?> src="<?= $this->e($this->asset($portrait['src'] ?? '')) ?>" alt="<?= $this->e('Ảnh chân dung ' . $name) ?>" loading="lazy">
                </div>
                <div class="card__content faculty-card__content">
                  <h3 class="card__title text-xl" <?= $context->textAttributes("teachers.$index.name") ?>><?= $this->e($name) ?></h3>
                  <?php if ($role !== ''): ?><p class="card__description mt-1" <?= $context->textAttributes("teachers.$index.role") ?>><?= $this->e($role) ?></p><?php endif; ?>
                  <?php if ($phone !== '' || $email !== ''): ?>
                    <div class="faculty-card__contact mt-4" data-faculty-contact aria-hidden="true">
                      <?php if ($phone !== ''): ?><p class="flex items-center gap-2"><i class="fa-solid fa-phone" aria-hidden="true"></i>
                          <span<?= $context->textAttributes("teachers.$index.phone") ?>><?= $this->e($phone) ?></span>
                        </p><?php endif; ?>
                      <?php if ($email !== ''): ?><p class="flex items-center gap-2<?= $phone !== '' ? ' mt-2' : '' ?>"><i class="fa-regular fa-envelope" aria-hidden="true"></i>
                          <span<?= $context->textAttributes("teachers.$index.email") ?>><?= $this->e($email) ?></span>
                        </p><?php endif; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </section>
  <?php
    return (string) ob_get_clean();
  }

  private function renderHistory(array $data, CmsRenderContext $context): string
  {
    ob_start();
  ?>
    <section id="lich-su-phat-trien" class="py-12 scroll-section" <?= $context->sectionAttributes() ?>>
      <div class="container">
        <div class="container-wrapper flex flex-col gap-16">
          <?php foreach ($this->items($data, 'sections') as $index => $item): ?>
            <div
              class="flex flex-col md:<?= $index % 2 !== 0 ? 'flex-row-reverse' : 'flex-row' ?> flex-1 items-center gap-12">
              <div class="history-image-card flex-1 relative overflow-hidden rounded-3xl">
                <div class="history-image-wrapper image-wrapper"><img class="image w-full h-full"
                    src="<?= $this->e($this->asset($item['image']['src'] ?? '')) ?>"
                    alt="<?= $this->e($item['image']['alt'] ?? '') ?>" loading="lazy" decoding="async"></div>
                <div class="history-image-wrapper__content absolute inset-0 flex flex-col justify-end gap-1">
                  <div class="text-6xl"><?= $this->e($item['year'] ?? '') ?></div>
                  <div class="text-xl"><?= $this->e($item['image']['caption'] ?? '') ?></div>
                </div>
              </div>
              <div class="flex-1 history-content flex flex-col justify-center gap-8">
                <span class="badge" data-variant="primary"><?= $item['badge'] ?? '' ?></span>
                <p class="text-4xl"><?= $this->e($item['title'] ?? '') ?></p>
                <div class="history-content-timeline flex flex-col gap-4">
                  <?php foreach ($this->items($item, 'timeline') as $timeline): ?>
                    <div class="history-content-timeline__item"><span
                        class="history-content-timeline__item-year"><?= $this->e($timeline['year'] ?? '') ?>:</span>
                      <span><?= $this->e($timeline['description'] ?? '') ?></span>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php
    return (string) ob_get_clean();
  }

  private function renderBentoGrid(array $data, CmsRenderContext $context): string
  {
    ob_start();
  ?>
    <section class="py-12" <?= $context->sectionAttributes() ?>>
      <div class="container">
        <div class="container-wrapper">
          <div class="bento-grid">
            <?php foreach (array_slice($this->items($data, 'items'), 0, 5) as $index => $item): ?>
              <?php $hasImage = !empty($item['image']['src']); ?>
              <div
                class="card bento-grid-item <?= $hasImage ? 'bento-grid-item--has-image' : 'bento-grid-item--empty-image' ?>"
                <?= $this->bentoStyle($item) ?>>
                <?php if ($hasImage): ?><img class="bento-grid-item__image"
                    src="<?= $this->e($this->asset($item['image']['src'])) ?>"
                    alt="<?= $this->e($item['image']['alt'] ?? '') ?>" loading="lazy" decoding="async"><?php endif; ?>
                <div class="card__header"><span class="badge"
                    data-variant="glass"><?= $item['badge'] ?? '<i class="fa-solid fa-lock"></i>' ?></span></div>
                <div class="card__content">
                  <div class="text-4xl md:text-6xl"><?= $this->e($item['content'] ?? '') ?></div>
                  <div class="text-xl"><?= $this->e($item['subContent'] ?? '') ?></div>
                </div>
                <div class="card__footer flex flex-row flex-wrap"><?= $item['footer'] ?? '' ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </section>
  <?php
    return (string) ob_get_clean();
  }

  private function renderVisionMission(array $data, CmsRenderContext $context): string
  {
    $defaults = self::visionMissionDefaults();
    foreach ($defaults as $key => $value) {
      if (!isset($data[$key]) || $data[$key] === '' || $data[$key] === []) $data[$key] = $value;
    }
    ob_start(); ?>
    <section id="tam-nhin-su-menh" class="vision-mission py-12 scroll-section" <?= $context->sectionAttributes() ?>>
      <div class="container">
        <div class="container-wrapper">
          <header class="flex flex-col items-center gap-2 md:gap-4 mb-8 md:mb-12 text-center">
            <h2 class="section__title"><?= $this->e($data['title'] ?? '') ?></h2>
            <p class="section__sub-title"><?= $this->e($data['introduction'] ?? '') ?></p>
          </header>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-6 items-stretch">
            <article class="stats__benefit-card flex flex-col gap-3 md:gap-6 p-3 md:p-6 rounded-3xl">
              <div class="flex gap-2 md:gap-4 items-center">
                <div class="stats__benefit-card-icon-wrapper flex justify-center items-center rounded-full"><i class="fa-solid fa-eye stats__benefit-card-icon" aria-hidden="true"></i></div>
                <h3 class="text-lg md:text-2xl font-semibold"><?= $this->e($data['vision_title'] ?? 'Tầm nhìn') ?></h3>
              </div>
              <p><?= $this->e($data['vision'] ?? '') ?></p>
            </article>
            <article class="stats__benefit-card flex flex-col gap-3 md:gap-6 p-3 md:p-6 rounded-3xl">
              <div class="flex gap-2 md:gap-4 items-center">
                <div class="stats__benefit-card-icon-wrapper flex justify-center items-center rounded-full"><i class="fa-solid fa-bullseye stats__benefit-card-icon" aria-hidden="true"></i></div>
                <h3 class="text-lg md:text-2xl font-semibold"><?= $this->e($data['mission_title'] ?? 'Sứ mệnh') ?></h3>
              </div>
              <p><?= $this->e($data['mission'] ?? '') ?></p>
            </article>
          </div>
        </div>
      </div>
    </section>
<?php return (string) ob_get_clean();
  }

  private static function visionMissionDefaults(): array
  {
    return [
      'eyebrow' => 'Định hướng phát triển',
      'title' => 'Tầm nhìn & Sứ mệnh',
      'introduction' => 'Kế thừa truyền thống đào tạo kỹ thuật của Cao Thắng, Khoa Công nghệ thông tin gắn tri thức với thực hành, đổi mới và nhu cầu của xã hội.',
      'vision_title' => 'Tầm nhìn',
      'vision' => 'Trở thành đơn vị đào tạo công nghệ thông tin ứng dụng vững mạnh, hiện đại và nhân văn; không ngừng nâng cao chất lượng để người học thích nghi, sáng tạo và phát triển trong môi trường công nghệ luôn thay đổi.',
      'mission_title' => 'Sứ mệnh',
      'mission' => 'Đào tạo nguồn nhân lực có kỷ luật, đạo đức nghề nghiệp, kiến thức vững và tay nghề tốt; kết nối đào tạo với thực tiễn doanh nghiệp, thúc đẩy nghiên cứu, đổi mới phương pháp giảng dạy và ứng dụng công nghệ phục vụ nhà trường và cộng đồng.',
      'principles' => [
        ['title' => 'Học đi đôi với hành', 'description' => 'Chú trọng năng lực thực hành, giải quyết vấn đề và khả năng đáp ứng công việc thực tế.'],
        ['title' => 'Đổi mới liên tục', 'description' => 'Cập nhật chương trình, công nghệ và phương pháp giảng dạy phù hợp với sự phát triển của xã hội.'],
        ['title' => 'Đồng hành cùng doanh nghiệp', 'description' => 'Mở rộng hợp tác trong đào tạo, thực tập, nghiên cứu và tạo cơ hội nghề nghiệp cho sinh viên.'],
      ],
      'source_note' => 'Nội dung được biên soạn từ tư liệu lịch sử Kỷ yếu Khoa Điện tử - Tin học; đây là bản CMS có thể tiếp tục hiệu chỉnh và phê duyệt.',
    ];
  }

  public static function partnershipDefaults(): array
  {
    return [
      'variant' => 'default',
      'title' => 'Đối tác Doanh nghiệp',
      'subtitle' => 'Sinh viên được kết nối trực tiếp với các doanh nghiệp hàng đầu trong lĩnh vực công nghệ.',
      'partners' => [
        ['name' => 'NVIDIA', 'url' => 'https://www.nvidia.com/en-us/', 'image' => ['src' => 'https://cntt.caothang.edu.vn/uploads/doanh-nghiep/f241854894669bf5ca4b65ce5614b3d2.png', 'alt' => 'NVIDIA'], 'description' => 'NVIDIA là tập đoàn công nghệ toàn cầu tiên phong trong điện toán tăng tốc, GPU, AI và mô phỏng số. Các nền tảng phần cứng, phần mềm của NVIDIA được ứng dụng rộng trong đồ họa, trung tâm dữ liệu, xe tự hành, y tế và nhiều ngành công nghiệp cần năng lực tính toán cao.', 'description_source_url' => 'https://www.nvidia.com/en-us/about-nvidia/'],
        ['name' => 'Lexar Việt Nam', 'url' => 'https://www.facebook.com/Lexarviet', 'image' => ['src' => 'https://cntt.caothang.edu.vn/uploads/doanh-nghiep/41f87152f57bd05bf848bcf01113f3b3.png', 'alt' => 'Lexar Việt Nam'], 'description' => 'Lexar là thương hiệu chuyên về giải pháp lưu trữ như thẻ nhớ, USB, SSD, DRAM và phụ kiện cho nhiếp ảnh, sáng tạo nội dung, chơi game và làm việc chuyên nghiệp. Tại Việt Nam, Lexar kết nối người dùng qua kênh cộng đồng và phân phối sản phẩm lưu trữ hiệu năng cao.', 'description_source_url' => 'https://www.lexar.com/company/'],
        ['name' => 'Tin học ngôi sao', 'url' => 'https://tinhocngoisao.com/', 'image' => ['src' => 'https://cntt.caothang.edu.vn/uploads/doanh-nghiep/0c09c04e408300357f835dc97eb1a6e6.png', 'alt' => 'Tin học ngôi sao'], 'description' => 'Tin Học Ngôi Sao được thành lập từ năm 2013, kinh doanh và phân phối linh kiện máy tính, laptop, gaming gear, thiết bị âm thanh, camera và dịch vụ công nghệ thông tin. Doanh nghiệp cũng tư vấn giải pháp phòng net, máy tính và thiết bị công nghệ cho khách hàng trên toàn quốc.', 'description_source_url' => 'https://tinhocngoisao.com/pages/gioi-thieu'],
        ['name' => 'An Phát Co.,Ltd', 'url' => 'https://vitinhanphat.com.vn/', 'image' => ['src' => 'https://cntt.caothang.edu.vn/uploads/doanh-nghiep/1ae64b95ef7ddafef717166e9edabfd5.png', 'alt' => 'An Phát Co.,Ltd'], 'description' => 'Vi Tính An Phát cung cấp máy tính, linh kiện, thiết bị văn phòng, thiết bị mạng và dịch vụ kỹ thuật cho cá nhân lẫn doanh nghiệp. Đơn vị hướng đến đáp ứng nhu cầu mua sắm, lắp đặt và hỗ trợ hệ thống công nghệ thông tin.', 'description_source_url' => 'https://vitinhanphat.com.vn/'],
        ['name' => 'SMNET', 'url' => 'https://smnet.vn/', 'image' => ['src' => 'https://cntt.caothang.edu.vn/uploads/doanh-nghiep/20131828c2f34e5eec6dc06826cb936f.png', 'alt' => 'SMNET'], 'description' => 'SMNET hoạt động trong lĩnh vực thiết bị mạng, camera, lưu trữ, máy tính và các giải pháp công nghệ cho hạ tầng doanh nghiệp. Website của SMNET tập trung vào hệ sinh thái sản phẩm, dịch vụ kỹ thuật và tư vấn triển khai.', 'description_source_url' => 'https://smnet.vn/'],
        ['name' => 'Tin học đại dương', 'url' => 'https://tinhocdaiduong.vn/', 'image' => ['src' => 'https://cntt.caothang.edu.vn/uploads/doanh-nghiep/cd22e173fe39bc2f2f1c6f756e46d78e.png', 'alt' => 'Tin học đại dương'], 'description' => 'Tin học Đại Dương là đơn vị kinh doanh máy tính, linh kiện, thiết bị văn phòng và phụ kiện công nghệ. Doanh nghiệp phục vụ nhu cầu mua sắm, lắp đặt và hỗ trợ kỹ thuật cho khách hàng cá nhân và văn phòng.', 'description_source_url' => 'https://tinhocdaiduong.vn/'],
        ['name' => 'Nguyễn Thuận', 'url' => 'https://thuancomputer.com/', 'image' => ['src' => 'https://cntt.caothang.edu.vn/uploads/doanh-nghiep/6858fbd5fab3b644b2b486587b94cda5.png', 'alt' => 'Nguyễn Thuận'], 'description' => 'Thuận Computer cung cấp máy tính, linh kiện, phụ kiện, thiết bị văn phòng và dịch vụ kỹ thuật liên quan đến hệ thống máy tính. Danh mục của doanh nghiệp phục vụ cả nhu cầu cá nhân và môi trường làm việc.', 'description_source_url' => 'https://thuancomputer.com/'],
        ['name' => 'ENGENIUS', 'url' => 'https://engenius.vn/', 'image' => ['src' => 'https://cntt.caothang.edu.vn/uploads/doanh-nghiep/4846e8f8f53fd5bb49b7ecda45792660.png', 'alt' => 'ENGENIUS'], 'description' => 'EnGenius phát triển các giải pháp mạng không dây, switch, quản trị đám mây và hạ tầng kết nối cho doanh nghiệp. Tại Việt Nam, thương hiệu giới thiệu thiết bị mạng và giải pháp triển khai Wi-Fi, quản trị hệ thống cho môi trường chuyên nghiệp.', 'description_source_url' => 'https://engenius.vn/'],
        ['name' => 'PAT GROUP / Siêu Thị Công Nghệ', 'url' => 'https://www.sieuthicongnghe.com.vn/', 'image' => ['src' => 'https://cntt.caothang.edu.vn/uploads/doanh-nghiep/1cc65af6ae144feaa9d8ed7f66d7aee4.png', 'alt' => 'PAT GROUP / Siêu Thị Công Nghệ'], 'description' => 'Siêu Thị Công Nghệ thuộc PAT Group là kênh bán lẻ và tư vấn thiết bị công nghệ, máy tính, linh kiện, thiết bị mạng và giải pháp phục vụ học tập, làm việc và giải trí.', 'description_source_url' => 'https://www.sieuthicongnghe.com.vn/'],
        ['name' => 'Anta6', 'url' => 'https://anta6.com/', 'image' => ['src' => 'https://cntt.caothang.edu.vn/uploads/doanh-nghiep/bb134adbde2b708a630f3539ac02aa02.png', 'alt' => 'Anta6'], 'description' => 'Anta6 giới thiệu các sản phẩm, dịch vụ công nghệ và giải pháp số thông qua website chính thức của doanh nghiệp. Hoạt động của đơn vị hướng đến hỗ trợ nhu cầu ứng dụng công nghệ của khách hàng.', 'description_source_url' => 'https://anta6.com/'],
        ['name' => 'Waverley Software', 'url' => 'https://waverleysoftware.com', 'image' => ['src' => 'https://cntt.caothang.edu.vn/uploads/doanh-nghiep/7717dab3bbd70e2f3d31aa0cd6202ff5.png', 'alt' => 'Waverley Software'], 'description' => 'Waverley Software là công ty phát triển phần mềm toàn cầu, cung cấp đội ngũ kỹ thuật và dịch vụ xây dựng sản phẩm số cho khách hàng ở nhiều thị trường. Doanh nghiệp tập trung vào phần mềm tùy chỉnh, AI, cloud, IoT, mobile và hệ thống doanh nghiệp.', 'description_source_url' => 'https://waverleysoftware.com/about-us/'],
        ['name' => 'Ryomo Vietnam Solutions Co., Ltd.', 'url' => 'http://rvsc.ryomo-gr.com/vn/', 'image' => ['src' => 'https://cntt.caothang.edu.vn/uploads/doanh-nghiep/c31d40d21703b76e070989017e080e3d.png', 'alt' => 'Ryomo Vietnam Solutions Co., Ltd.'], 'description' => 'Ryomo Vietnam Solutions là thành viên của Ryomo Group, cung cấp dịch vụ phát triển phần mềm và giải pháp công nghệ thông tin theo tiêu chuẩn Nhật Bản. Công ty hỗ trợ khách hàng trong phát triển, vận hành và nâng cấp hệ thống.', 'description_source_url' => 'http://rvsc.ryomo-gr.com/vn/'],
      ],
    ];
  }

  private function bentoStyle(array $item): string
  {
    $background = trim((string) ($item['background'] ?? ''));
    return preg_match('/^#[0-9a-f]{6}$/i', $background)
      ? ' style="--bento-item-background:' . $this->e($background) . '"'
      : '';
  }

  private function asset(?string $value): string
  {
    $src = trim((string) $value);
    if ($src === '') {
      return '';
    }
    if (preg_match('/^(https?:)?\/\//', $src) || str_starts_with($src, 'data:') || str_starts_with($src, '/')) {
      return $src;
    }
    $normalized = ltrim(preg_replace('/^\.\//', '', $src), '/');
    if (str_starts_with($normalized, 'public/media/') || str_starts_with($normalized, 'media/')) {
      return url('public/media/' . $normalized);
    }
    if (str_starts_with($normalized, 'public/')) {
      return url($normalized);
    }
    return url('public/' . $normalized);
  }

  private function items(array $data, string $key): array
  {
    return is_array($data[$key] ?? null) ? $data[$key] : [];
  }

  private function safeExternalUrl(mixed $value): string
  {
    $value = trim((string) $value);
    return filter_var($value, FILTER_VALIDATE_URL) && preg_match('/^https?:\/\//i', $value) ? $value : '#';
  }

  private function siteUrl(mixed $value): string
  {
    $value = trim((string) $value);
    if ($value === '' || $value === '#') {
      return '#';
    }

    if (preg_match('/^https?:\/\//i', $value)) {
      return $value;
    }

    return url($value);
  }

  private function e(mixed $value): string
  {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
  }
}
