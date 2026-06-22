<?php

namespace App\Cms;

final class CmsStaticPageRenderer
{
  private CmsSectionRegistry $_sections;

  public function __construct(
    private array $context = [],
    ?CmsSectionRegistry $sections = null,
    private string $pageSlug = '',
  )
  {
    $this->_sections = $sections ?? self::defaultRegistry();
  }

  public function render(array $document): string
  {
    $html = '';
    $context = new CmsRenderContext($this->pageSlug, '', $this->context);

    foreach ($document['sections'] ?? [] as $section) {
      if (!is_array($section)) {
        continue;
      }
      $html .= $this->_sections->renderSection($section, $context);
    }
    return $html;
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
      fn(array $data, CmsRenderContext $context): string => $renderer->renderBreadcrumbs(),
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
      fn(array $data, CmsRenderContext $context): string => $renderer->renderLandingAbout($data),
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
      fn(array $data, CmsRenderContext $context): string => $renderer->renderWhyChooseUs($data),
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
      fn(array $data, CmsRenderContext $context): string => $renderer->renderStats($data),
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
      'sections/about_hero',
      'About hero',
      ['variant' => 'default', 'image' => 'public/img/about.jpg', 'badge' => '', 'title' => '', 'subtitle' => ''],
      ['image', 'badge', 'title', 'subtitle'],
      ['default' => 'Default'],
      fn(array $data, CmsRenderContext $context): string => $renderer->renderAboutHero($data),
      [
        'image' => 'Hero image',
        'badge' => 'Badge',
        'title' => 'Title',
        'subtitle' => 'Subtitle',
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
      fn(array $data, CmsRenderContext $context): string => $renderer->renderHistory($data),
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
      fn(array $data, CmsRenderContext $context): string => $renderer->renderBentoGrid($data),
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

    return $registry;
  }

  private function renderHero(CmsRenderContext $context): string
  {
    ob_start();
    $carouselSlides = $context->value('carouselSlides', []);
    ?>
    <section class="relative" id="hero-section">
      <div class="container">
        <?php $this->renderCarousel($carouselSlides); ?>
      </div>
      <div class="wave-container">
        <svg class="wave" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 60" preserveAspectRatio="none" fill="none">
          <path d="M0 60L48 48.3333C96 38.6667 192 19.3333 288 9.66667C384 0 480 0 576 4.83333C672 9.66667 768 19.3333 864 24.1667C960 29 1056 29 1152 24.1667C1248 19.3333 1344 9.66667 1392 4.83333L1440 0V60H1392C1344 60 1248 60 1152 60C1056 60 960 60 864 60C768 60 672 60 576 60C480 60 384 60 288 60C192 60 96 60 48 60H0Z" fill="currentColor"></path>
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
          <?php if (!$slide->isActive()) continue; ?>
          <div class="carousel__item <?= $slide->isCustom() ? 'carousel__item--custom' : 'carousel__item--standard' ?> flex justify-between items-center">
            <?php if ($slide->isCustom()): ?>
              <?= $slide->custom_html ?>
            <?php else: ?>
              <div class="carousel__content flex flex-col">
                <h2 class="carousel__title font-normal">
                  <?= $this->e($slide->title) ?>
                  <?php if (!empty($slide->title_highlight)): ?><span><?= $this->e($slide->title_highlight) ?></span><?php endif; ?>
                </h2>
                <?php if (!empty($slide->description)): ?><p class="carousel__description"><?= nl2br($this->e($slide->description)) ?></p><?php endif; ?>
                <?php if ($slide->hasCta()): ?>
                  <div class="carousel__cta">
                    <a href="<?= $this->e(url($slide->cta_url)) ?>" data-variant="<?= $this->e($slide->cta_variant) ?>" class="btn px-8 py-2 rounded-3xl bouncy-btn"><?= $this->e($slide->cta_label) ?></a>
                  </div>
                <?php endif; ?>
              </div>
              <div class="image-wrapper carousel__image-wrapper rounded-3xl">
                <img src="<?= $this->e(url('public/media/' . $slide->media->file_path)) ?>" alt="<?= $this->e($slide->media->alt_text ?: $slide->title) ?>" class="image carousel__image">
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <button class="carousel__control carousel__control--prev"><i class="fa-solid fa-angle-left"></i></button>
      <button class="carousel__control carousel__control--next"><i class="fa-solid fa-angle-right"></i></button>
      <div class="carousel__indicators"></div>
    </div>
    <?php
  }

  private function renderBreadcrumbs(): string
  {
    ob_start();
    ?>
    <section class="site-breadcrumbs py-4">
      <div class="container"><div class="container-wrapper">
        <?php
        include_once BASE_PATH . '/templates/components/breadcrumb.php';
        renderBreadcrumb([
          ['icon' => '<i class="fa-regular fa-house"></i>', 'url' => url('/'), 'title' => 'Trang chủ'],
          ['url' => url('/gioi-thieu'), 'title' => 'Giới Thiệu'],
        ]);
        ?>
      </div></div>
    </section>
    <?php
    return (string) ob_get_clean();
  }

  private function renderLandingAbout(array $data): string
  {
    ob_start();
    ?>
    <section class="relative container py-16" id="landing-about-section">
      <h2 class="sr-only">About Us</h2>
      <div class="container-wrapper">
        <div class="landing-about-container flex flex-col gap-12 md:gap-0">
          <?php foreach ($this->items($data, 'items') as $index => $item): ?>
            <div class="flex gap-4 md:gap-12 flex-col md:<?= $index % 2 === 0 ? 'flex-row-reverse' : 'flex-row' ?>">
              <div class="flex-1 relative">
                <div class="overflow-hidden rounded-3xl"><div class="image-wrapper">
                  <img class="image w-full h-full" src="<?= $this->e($this->asset($item['image']['src'] ?? '')) ?>" alt="<?= $this->e($item['image']['alt'] ?? '') ?>">
                </div></div>
                <div class="landing-about-item__card absolute z-10 rounded-3xl p-3 md:p-6 flex flex-col gap-1">
                  <div class="landing-about-item__card-main-content text-lg md:text-5xl"><?= $this->e($item['card']['value'] ?? '') ?></div>
                  <div class="landing-about-item__card-sub-content md:text-sm"><?= $this->e($item['card']['label'] ?? '') ?></div>
                </div>
              </div>
              <div class="flex-1 flex flex-col justify-center gap-4">
                <p class="number-of-text text-7xl hidden md:block"><?= $this->e($item['number'] ?? '') ?></p>
                <p class="landing-about-item__sub-title text-xs uppercase font-medium"><?= $this->e($item['eyebrow'] ?? '') ?></p>
                <p class="about-item__title text-4xl"><?= $this->e($item['title'] ?? '') ?></p>
                <p class="landing-about-item__content"><?= $this->e($item['description'] ?? '') ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php
    return (string) ob_get_clean();
  }

  private function renderWhyChooseUs(array $data): string
  {
    ob_start();
    $feature = is_array($data['feature'] ?? null) ? $data['feature'] : [];
    ?>
    <section class="wcu relative container py-16" id="why-choose-us-section">
      <div class="wcu__container container-wrapper">
        <div class="wcu__header flex flex-col justify-center items-center gap-2 md:gap-4 mb-8 md:mb-12">
          <div class="wcu__badge section__badge px-4 py-2 rounded-3xl text-sm mb-2 md:mb-4"><?= $this->e($data['badge'] ?? '') ?></div>
          <h2 class="wcu__title section__title"><?= $this->e($data['title'] ?? '') ?></h2>
          <p class="wcu__subtitle section__sub-title"><?= $this->e($data['subtitle'] ?? '') ?></p>
        </div>
        <div class="wcu__content flex flex-col items-center justify-center">
          <div class="wcu__features-grid grid grid-cols-2 md:grid-cols-3 grid-rows-2 gap-3 md:gap-6 mb-6 self-stretch">
            <div class="wcu__feature-card wcu__feature-card--large wcu-feature-container overflow-hidden relative row-start-1 col-span-2 row-span-1 md:row-span-2 rounded-3xl image-wrapper">
              <img class="wcu__feature-card-image image" src="<?= $this->e($this->asset($feature['image'] ?? '')) ?>" alt="<?= $this->e($feature['alt'] ?? '') ?>">
              <div class="wcu__feature-card-content absolute inset-0 flex flex-col justify-end items-start gap-2 md:gap-4 p-3 md:p-6">
                <span class="wcu__feature-card-badge badge" data-variant="primary"><?= $this->e($feature['badge'] ?? '') ?></span>
                <h3 class="wcu__feature-card-title text-md md:text-3xl font-semibold"><?= $this->e($feature['title'] ?? '') ?></h3>
                <p class="wcu__feature-card-description text-xs md:text-md font-normal"><?= $this->e($feature['description'] ?? '') ?></p>
                <a href="<?= $this->e($feature['cta_url'] ?? '#') ?>" class="wcu__feature-card-link md:text-md font-normal"><?= $this->e($feature['cta_label'] ?? '') ?> <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
              </div>
            </div>
            <?php foreach ($this->items($data, 'stats') as $index => $stat): ?>
              <div class="wcu__stat-card <?= $index === 0 ? 'wcu__stat-card--primary col-start-1 md:col-start-3 row-start-2 md:row-start-1' : 'wcu__stat-card--gradient col-start-2 md:col-start-3 row-start-2 md:row-start-2' ?> rounded-3xl p-3 md:p-6 flex flex-col gap-2 justify-center">
                <h2 class="wcu__stat-card-number flex-1 md:flex-none flex justify-center items-center md:block text-6xl md:text-7xl font-bold"><?= $this->e($stat['number'] ?? '') ?></h2>
                <div class="wcu__stat-card-content flex flex-col gap-2">
                  <p class="wcu__stat-card-title md:text-xl font-semibold"><?= $this->e($stat['title'] ?? '') ?></p>
                  <p class="wcu__stat-card-description text-xs md:text-md font-normal"><?= $this->e($stat['description'] ?? '') ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="wcu__perks-list grid grid-cols-2 grid-rows-2 md:flex justify-center items-stretch self-stretch gap-3 md:gap-6 mb-6">
            <?php foreach ($this->items($data, 'perks') as $perk): ?>
              <div class="wcu__perk-item flex flex-col items-start justify-start flex-1 rounded-3xl p-3 md:p-6">
                <div class="wcu__perk-item-icon-wrapper flex justify-center items-center rounded-full text-4xl mb-4 p-3"><i class="<?= $this->e($perk['icon'] ?? 'fa-solid fa-circle') ?> wcu__perk-item-icon"></i></div>
                <h4 class="wcu__perk-item-title md:text-md font-semibold mb-2"><?= $this->e($perk['title'] ?? '') ?></h4>
                <p class="wcu__perk-item-description text-xs md:text-sm font-normal"><?= $this->e($perk['description'] ?? '') ?></p>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="wcu__highlights-list self-stretch grid grid-rows-2 md:grid-rows-1 md:grid-cols-2 gap-3 md:gap-6">
            <?php foreach ($this->items($data, 'highlights') as $index => $highlight): ?>
              <div class="wcu__highlight-item flex-1 overflow-hidden relative rounded-3xl image-wrapper text-white">
                <img class="wcu__highlight-item-image image" src="<?= $this->e($this->asset($highlight['image'] ?? '')) ?>" alt="<?= $this->e($highlight['alt'] ?? '') ?>">
                <div class="wcu__highlight-item-content <?= $index === 0 ? 'wcu__highlight-item-content--blue' : 'wcu__highlight-item-content--green' ?> absolute inset-0 flex flex-col justify-end items-start p-3 md:p-6">
                  <h3 class="wcu__highlight-item-title text-md md:text-2xl font-semibold mb-2"><?= $this->e($highlight['title'] ?? '') ?></h3>
                  <p class="wcu__highlight-item-description text-xs md:text-sm font-normal"><?= $this->e($highlight['description'] ?? '') ?></p>
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

  private function renderStats(array $data): string
  {
    ob_start();
    $cta = is_array($data['cta'] ?? null) ? $data['cta'] : [];
    ?>
    <section class="relative container py-16" id="stats-section">
      <div class="container-wrapper">
        <div class="flex flex-col justify-center items-center gap-2 md:gap-4 mb-8 md:mb-12">
          <h2 class="section__title"><?= $this->e($data['title'] ?? '') ?></h2>
          <p class="section__sub-title"><?= $this->e($data['subtitle'] ?? '') ?></p>
        </div>
        <div class="flex flex-col items-stretch justify-center gap-3 md:gap-6">
          <div class="stats__grid grid grid-cols-2 grid-rows-2 md:grid-cols-4 md:grid-rows-1 gap-3 md:gap-6">
            <?php foreach ($this->items($data, 'stats') as $stat): ?>
              <div class="stats__stat-card flex flex-1 flex-col items-center gap-3 md:gap-6 rounded-3xl p-3 md:p-6">
                <div class="stats__stat-card-icon-wrapper flex items-center justify-center rounded-full"><i class="<?= $this->e($stat['icon'] ?? 'fa-solid fa-award') ?> stats__stat-card-icon"></i></div>
                <div class="flex flex-col gap-1 items-center">
                  <h3 class="stats__stat-card-number text-3xl md:text-5xl font-bold"><?= $this->e($stat['number'] ?? '') ?></h3>
                  <h4 class="stats__stat-card-label font-semibold"><?= $this->e($stat['label'] ?? '') ?></h4>
                  <p class="stats__stat-card-description text-xs md:text-sm text-center"><?= $this->e($stat['description'] ?? '') ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="stats__benefits-grid grid grid-cols-1 md:grid-cols-2 grid-rows-2 md:grid-rows-1 gap-3 md:gap-6 items-stretch">
            <?php foreach ($this->items($data, 'benefits') as $benefit): ?>
              <div class="stats__benefit-card flex-1 flex flex-col gap-3 md:gap-6 p-3 md:p-6 rounded-3xl">
                <div class="stats__benefit-card-header flex gap-2 md:gap-4 items-center">
                  <div class="stats__benefit-card-icon-wrapper flex justify-center items-center rounded-full"><i class="<?= $this->e($benefit['icon'] ?? 'fa-solid fa-building-columns') ?> stats__benefit-card-icon"></i></div>
                  <h3 class="stats__benefit-card-title text-lg md:text-2xl font-semibold"><?= $this->e($benefit['title'] ?? '') ?></h3>
                </div>
                <ul class="stats__benefit-card-list flex flex-col gap-2 md:gap-4">
                  <?php foreach ($this->items($benefit, 'items') as $item): ?>
                    <li class="stats__benefit-card-item flex items-center gap-2"><span class="stats__benefit-card-item-icon rounded-full"></span><p class="stats__benefit-card-item-text"><?= $this->e($item) ?></p></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="stats__cta flex flex-col items-center p-3 md:p-12 rounded-3xl">
            <h3 class="stats__cta-title text-center text-xl md:text-3xl font-semibold mb-2"><?= $this->e($cta['title'] ?? '') ?></h3>
            <p class="stats__cta-description text-center text-sm md:text-xl font-light mb-6"><?= $this->e($cta['description'] ?? '') ?></p>
            <div class="stats__cta-buttons flex flex-col w-full md:w-fit md:flex-row gap-2 md:gap-4">
              <?php foreach ($this->items($cta, 'buttons') as $index => $button): ?>
                <a href="<?= $this->e($button['url'] ?? '#') ?>" data-variant="<?= $this->e($button['variant'] ?? 'outline') ?>" class="stats__cta-button stats__cta-button--secondary flex items-center px-8 py-4 btn bouncy-btn rounded-full <?= $index === 1 ? 'bg-transparent' : '' ?>"><?= $this->e($button['label'] ?? '') ?></a>
              <?php endforeach; ?>
            </div>
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
    <section class="relative container py-16" id="newsfeed-section">
      <div class="container-wrapper">
        <div class="flex flex-col justify-center items-center gap-2 md:gap-4 mb-8 md:mb-12">
          <h2 class="section__title">Tin tức &amp; Sự kiện</h2>
          <p class="section__sub-title">Cập nhật những tin tức mới nhất về hoạt động của khoa, thành tích sinh viên và các sự kiện sắp tới</p>
        </div>
        <div class="newsfeed__content-wrapper flex flex-col gap-16">
          <div class="flex flex-col gap-3 md:gap-6">
            <?php if (!empty($featuredNews)): $featured = $featuredNews[0]; ?>
              <?php renderLandingNewsCard($featured, ['variant' => 'featured', 'category_label' => 'Nổi bật', 'show_read_more' => true, 'class' => 'super-landing-featured-news landing-featured-news relative overflow-hidden rounded-3xl']); ?>
            <?php endif; ?>
            <div class="flex flex-col md:flex-row gap-3 md:gap-6 justify-center items-stretch self-stretch">
              <?php for ($i = 1, $count = count($featuredNews); $i < 4 && $i < $count; $i++): $news = $featuredNews[$i]; ?>
                <?php renderLandingNewsCard($news, ['variant' => 'secondary', 'category_label' => $news->categories[0]->name ?? 'Tin tức', 'class' => 'landing-sub-featured-news flex-1']); ?>
              <?php endfor; ?>
            </div>
          </div>
          <div id="newsfeed-other" class="flex flex-col gap-4">
            <div class="newsfeed__other-header flex">
              <h2 class="newsfeed__other-title text-2xl md:text-4xl font-medium flex-1">Tin tức khác</h2>
              <a href="<?= $this->e(url('tin-tuc')) ?>" class="newsfeed__view-all-link md:text-md font-medium link-hover--underline">Xem thêm <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
            </div>
            <div class="flex flex-col md:flex-row gap-3 md:gap-6 justify-center items-stretch self-stretch">
              <?php for ($i = 0, $count = count($latestNewsItems); $i < 3 && $i < $count; $i++): $news = $latestNewsItems[$i]; ?>
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

  private function renderAboutHero(array $data): string
  {
    ob_start();
    ?>
    <section class="relative">
      <div class="about-thumbnail__wrapper"><img class="w-full h-full object-cover" src="<?= $this->e($this->asset($data['image'] ?? '')) ?>" alt=""></div>
      <div class="about-thumbnail-content__wrapper absolute inset-0 flex justify-center items-center">
        <div class="container"><div class="container-wrapper">
          <div class="about-thumbnail-content flex flex-col justify-center items-center gap-6 text-center">
            <span class="badge" data-variant="primary"><?= $this->e($data['badge'] ?? '') ?></span>
            <div class="about-thumbnail-content__title"><?= $this->e($data['title'] ?? '') ?></div>
            <div class="about-thumbnail-content__sub-title"><?= $this->e($data['subtitle'] ?? '') ?></div>
          </div>
        </div></div>
      </div>
    </section>
    <?php
    return (string) ob_get_clean();
  }

  private function renderHistory(array $data): string
  {
    ob_start();
    ?>
    <section class="py-12">
      <div class="container"><div class="container-wrapper flex flex-col gap-16">
        <?php foreach ($this->items($data, 'sections') as $index => $item): ?>
          <div class="flex flex-col md:<?= $index % 2 !== 0 ? 'flex-row-reverse' : 'flex-row' ?> flex-1 items-center gap-12">
            <div class="history-image-card flex-1 relative overflow-hidden rounded-3xl">
              <div class="history-image-wrapper image-wrapper"><img class="image w-full h-full" src="<?= $this->e($this->asset($item['image']['src'] ?? '')) ?>" alt="<?= $this->e($item['image']['alt'] ?? '') ?>"></div>
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
                  <div class="history-content-timeline__item"><span class="history-content-timeline__item-year"><?= $this->e($timeline['year'] ?? '') ?>:</span> <span><?= $this->e($timeline['description'] ?? '') ?></span></div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div></div>
    </section>
    <?php
    return (string) ob_get_clean();
  }

  private function renderBentoGrid(array $data): string
  {
    ob_start();
    ?>
    <section class="py-12">
      <div class="container"><div class="container-wrapper"><div class="bento-grid">
        <?php foreach ($this->items($data, 'items') as $index => $item): ?>
          <?php $hasImage = !empty($item['image']['src']); ?>
          <div class="card bento-grid-item <?= $hasImage ? 'bento-grid-item--has-image' : 'bento-grid-item--empty-image' ?>"<?= $this->bentoStyle($item) ?>>
            <?php if ($hasImage): ?><img class="bento-grid-item__image" src="<?= $this->e($this->asset($item['image']['src'])) ?>" alt="<?= $this->e($item['image']['alt'] ?? '') ?>"><?php endif; ?>
            <div class="card__header"><span class="badge" data-variant="glass"><?= $item['badge'] ?? '<i class="fa-solid fa-lock"></i>' ?></span></div>
            <div class="card__content">
              <div class="text-4xl md:text-6xl"><?= $this->e($item['content'] ?? '') ?></div>
              <div class="text-xl"><?= $this->e($item['subContent'] ?? '') ?></div>
            </div>
            <div class="card__footer flex flex-row flex-wrap"><?= $item['footer'] ?? '' ?></div>
          </div>
        <?php endforeach; ?>
      </div></div></div>
    </section>
    <?php
    return (string) ob_get_clean();
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
    if (str_starts_with($normalized, 'public/media/') || str_starts_with($normalized, 'public/')) {
      return url($normalized);
    }
    if (str_starts_with($normalized, 'media/')) {
      return url('public/' . $normalized);
    }
    return url('public/' . $normalized);
  }

  private function items(array $data, string $key): array
  {
    return is_array($data[$key] ?? null) ? $data[$key] : [];
  }

  private function e(mixed $value): string
  {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
  }
}
