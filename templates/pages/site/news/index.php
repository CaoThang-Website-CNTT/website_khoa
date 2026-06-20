<?php include_once BASE_PATH . '/templates/components/news_card.php'; ?>

<?php
$breadcrumbItems = [
  [
    '@type' => 'ListItem',
    'position' => 1,
    'name' => 'Trang chủ',
    'item' => url('/')
  ],
  [
    '@type' => 'ListItem',
    'position' => 2,
    'name' => 'Tin tức & Sự kiện',
    'item' => url('tin-tuc')
  ]
];

$breadcrumbSchema = [
  '@context' => 'https://schema.org',
  '@type' => 'BreadcrumbList',
  'itemListElement' => $breadcrumbItems
];
?>
<?php $layout->start("head_meta") ?>
  <?= seo_jsonld($breadcrumbSchema) ?>
<?php $layout->end() ?>

<!-- Breadcrumbs -->
<section class="site-breadcrumbs py-4">
  <div class="container">
    <div class="container-wrapper">
      <?php
      include_once BASE_PATH . '/templates/components/breadcrumb.php';
      renderBreadcrumb([
        ['icon' => '<i class="fa-regular fa-house"></i>', 'url' => url('/'), 'title' => 'Trang chủ'],
        ['url' => url('/tin-tuc'), 'title' => 'Tin tức & Sự kiện'],
      ]);
      ?>
    </div>
  </div>
</section>

<section class="relative container py-16">
  <div class="container-wrapper flex flex-col gap-16">
    <section class="news-title" aria-labelledby="page-title">
      <h1 id="page-title" class="news-title__heading">Tin tức & Sự kiện</h1>
      <p class="news-title__subtitle">Cập nhật thông tin mới nhất từ Khoa CNTT</p>
    </section>

    <section class="news-searchbar" aria-labelledby="search-title">
      <h2 id="search-title" class="sr-only">Tìm kiếm và lọc tin tức</h2>

      <div class="news-search">
        <label class="flex-1 search-bar rounded-full" data-variant="alt" for="news-search-input">
          <!-- <span class="search-bar__icon" aria-hidden="true" >
            <i class="fa-solid fa-magnifying-glass" role="img" aria-hidden="true"></i>
          </span> -->
          <input type="search" id="news-search-input" class="search-bar__input" placeholder="Tìm kiếm tin tức..."
            autocomplete="off" autocorrect="off" aria-label="Tìm kiếm tin tức">
        </label>
        <button class="btn news-searchbar__btn" data-variant="primary" data-size="lg" aria-label="Tìm kiếm"
          type="button">
          <i class="fa-solid fa-magnifying-glass" role="img" aria-hidden="true"></i>
          Tìm kiếm
        </button>
      </div>

      <div class="separator"></div>

      <div class="news-filters" role="group" aria-label="Danh mục tin tức">
        <button class="btn news-filters__tag" data-variant="primary" data-size="lg" data-category="all"
          aria-pressed="true">Tất cả</button>
        <button class="btn news-filters__tag" data-variant="outline" data-size="lg" data-category="tin-khoa"
          aria-pressed="false">Tin khoa</button>
        <button class="btn news-filters__tag" data-variant="outline" data-size="lg" data-category="nghien-cuu"
          aria-pressed="false">Nghiên cứu</button>
        <button class="btn news-filters__tag" data-variant="outline" data-size="lg" data-category="su-kien"
          aria-pressed="false">Sự kiện</button>
        <button class="btn news-filters__tag" data-variant="outline" data-size="lg" data-category="sinh-vien"
          aria-pressed="false">Sinh viên</button>
        <button class="btn news-filters__tag" data-variant="outline" data-size="lg" data-category="tuyen-dung"
          aria-pressed="false">Tuyển dụng</button>
        <button class="btn news-filters__tag" data-variant="outline" data-size="lg" data-category="giai-thuong"
          aria-pressed="false">Giải thưởng</button>
      </div>
    </section>

    <section class="news-section" aria-labelledby="featured-title">
      <h2 id="featured-title" class="news-section__title">
        <i class="fa-solid fa-arrow-trend-up" role="img" aria-hidden="true"></i>
        Nổi bật
      </h2>

      <?php if (!empty($featuredNews)): ?>
        <div class="news-featured__grid" role="list" aria-live="polite">
          <?php foreach ($featuredNews as $news): ?>
            <?php renderNewsCard($news, [
              'variant' => 'featured',
              'show_views_badge' => true,
            ]); ?>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="empty news-list__empty">
          <div class="empty__header">
            <div class="empty__media">
              <i class="fa-solid fa-newspaper"></i>
            </div>
            <div class="empty__title">Không có tin tức nào nổi bật</div>
          </div>
        </div>
      <?php endif; ?>
    </section>

    <section class="news-section" aria-labelledby="all-news-title">
      <div class="news-section__header">
        <h2 id="all-news-title" class="news-section__title">Tất cả tin tức</h2>
        <div class="news-list__sort">
          <label for="all-news-sort-select">Sắp xếp:</label>
          <button type="button" id="all-news-sort-select" class="select" data-select-id="all-news-sort-select"
            name="all-news-sort" role="listbox" data-select-default-value="newest">
            <div class="select__content">
              <div class="select__item" data-select-value="newest">Mới nhất</div>
              <div class="select__item" data-select-value="oldest">Cũ nhất</div>
            </div>
          </button>
        </div>
      </div>

      <div class="all-news__list" role="list" aria-live="polite">
        <?php foreach ($allNews->getItems() as $news): ?>
          <?php renderNewsCard($news, [
            'variant' => 'horizontal',
            'show_category_in_meta' => true,
          ]); ?>
        <?php endforeach; ?>
      </div>

      <div class="news-actions">
        <button type="button" id="load-more-btn" class="btn" data-variant="outline" data-size="lg"
          aria-controls="news-list" aria-expanded="false">
          Xem Thêm
        </button>
      </div>
    </section>
  </div>
</section>

<template id="news-card-horizontal-template-v2">
  <article class="card news-card news-card--horizontal hover-lift" role="listitem" data-id="{{ id }}">
    <div class="card__header news-card__header">
      <a class="news-card__image-wrapper" href="{{ url }}">
        <img src="{{ image_url }}" alt="{{ title }}" class="news-card__image">
      </a>
    </div>
    <div class="card__content news-card__content">
      <div class="news-card__meta">
        <div>
          <span class="badge news-card__badge" data-variant="primary">{{ category_name }}</span>
        </div>
        <div>
          <i class="fa-regular fa-calendar" aria-hidden="true"></i>
          <time datetime="{{ published_datetime }}">{{ published_date }}</time>
        </div>
        <div>
          <i class="fa-regular fa-eye" aria-hidden="true"></i>
          <span>{{ view_count }} lượt xem</span>
        </div>
      </div>
      <h3 class="news-card__title">
        <a href="{{ url }}">{{ title }}</a>
      </h3>
      <p class="news-card__description">{{ description }}</p>
      <a href="{{ url }}" class="link-hover--underline news-card__link">
        Xem chi tiết
        <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>
  </article>
</template>

<template id="news-card-featured-template">
  <article class="card news-card hover-lift" role="listitem" data-id="{{ id }}">
    <div class="card__header news-card__header">
      <a class="news-card__image-wrapper" href="{{ url }}">
        <img src="{{ image_url }}" alt="{{ title }}" class="news-card__image">
      </a>
      <span class="badge news-card__badge" data-variant="primary">{{ category_name }}</span>
      <span class="badge news-card__views-badge" data-variant="secondary">
        <i class="fa-solid fa-arrow-trend-up" role="img" aria-hidden="true"></i>
        {{ view_count }} lượt xem
      </span>
    </div>
    <div class="card__content news-card__content">
      <div class="news-card__meta">
        <div>
          <i class="fa-regular fa-calendar" aria-hidden="true"></i>
          <time datetime="{{ published_datetime }}">{{ published_date }}</time>
        </div>
      </div>
      <h3 class="news-card__title">
        <a href="{{ url }}">{{ title }}</a>
      </h3>
      <p class="news-card__description">{{ description }}</p>
      <a href="{{ url }}" class="link-hover--underline news-card__link">
        Xem chi tiết
        <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>
  </article>
</template>

<?php $layout->start("scripts") ?>
<script>
  window.__siteNewsIndex__ = {
    apiUrl: <?= json_encode(url('api/v1/posts')) ?>,
    postDetailBaseUrl: <?= json_encode(url('tin-tuc')) ?>,
    mediaBaseUrl: <?= json_encode(url('public/media')) ?>,
    defaultImageUrl: <?= json_encode(url('public/img/default-post-thumb.jpg')) ?>,
    page: <?= (int) $allNews->getCurrentPage() ?>,
    limit: <?= (int) $allNews->getPerPage() ?>,
    lastPage: <?= (int) $allNews->getTotalPages() ?>
  };
</script>
<script src="<?= url('public/js/pages/site/news/index.js') ?>" type="module"></script>
<?php $layout->end() ?>