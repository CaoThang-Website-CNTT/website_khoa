<?php
if (!function_exists('newsCardEscape')) {
  function newsCardEscape(?string $value): string
  {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
  }
}

if (!function_exists('newsCardCategory')) {
  function newsCardCategory(object $news): string
  {
    return $news->categories[0]->name ?? 'Tin tức';
  }
}

if (!function_exists('newsCardUrl')) {
  function newsCardUrl(object $news): string
  {
    return url('tin-tuc/' . ($news->slug ?? ''));
  }
}

if (!function_exists('newsCardImageUrl')) {
  function newsCardImageUrl(object $news): string
  {
    return url('public/media/' . ($news->seo_image_url ?? ''));
  }
}

if (!function_exists('newsCardFallbackImageUrl')) {
  function newsCardFallbackImageUrl(): string
  {
    return url('public/img/default-post-thumb.jpg');
  }
}

if (!function_exists('newsCardPublishedTimestamp')) {
  function newsCardPublishedTimestamp(object $news): int
  {
    $timestamp = strtotime($news->published_at ?? '');
    return $timestamp ?: 0;
  }
}

if (!function_exists('newsCardPublishedDate')) {
  function newsCardPublishedDate(object $news): string
  {
    return date('d/m/Y', newsCardPublishedTimestamp($news));
  }
}

if (!function_exists('newsCardPublishedDateTime')) {
  function newsCardPublishedDateTime(object $news): string
  {
    return date('Y-m-d', newsCardPublishedTimestamp($news));
  }
}

if (!function_exists('newsCardViews')) {
  function newsCardViews(object $news): string
  {
    return number_format((int) ($news->view_count ?? 0));
  }
}

if (!function_exists('renderLandingNewsCard')) {
  function renderLandingNewsCard(object $news, array $options = []): void
  {
    $variant = $options['variant'] ?? 'standard';
    $categoryLabel = $options['category_label'] ?? newsCardCategory($news);
    $showReadMore = (bool) ($options['show_read_more'] ?? false);
    $extraClass = trim($options['class'] ?? '');
    $contentClass = $options['content_class'] ?? 'p-3 md:p-6';
    $articleClass = trim(sprintf('%s news-card %s', $extraClass, $variant === 'standard' ? 'flex-1' : ''));
    $isFeatured = $variant === 'featured';
    ?>
    <article class="card <?= newsCardEscape($articleClass) ?>" data-landing="true">
      <a class="news-card__cover-link" href="<?= newsCardEscape(newsCardUrl($news)) ?>"
        aria-label="<?= newsCardEscape($news->title ?? '') ?>">
        <div class="card__header news-card__header">
          <span class="news-card__image-wrapper image-wrapper">
            <img src="<?= newsCardEscape(newsCardImageUrl($news)) ?>"
              onerror="this.onerror=null; this.src='<?= newsCardEscape(newsCardFallbackImageUrl()) ?>'"
              alt="<?= newsCardEscape($news->title ?? '') ?>"
              class="news-card__image absolute w-full h-full object-cover image">
          </span>
        </div>

        <div
          class="card__content news-card__content absolute inset-0 flex flex-col justify-end items-start <?= newsCardEscape($contentClass) ?>">
          <div class="news-card__meta flex items-center gap-1">
            <span class="badge" <?= $isFeatured ? ' data-variant="destructive"' : '' ?>>
              <?= newsCardEscape($categoryLabel) ?>
            </span>
            <span class="news-card__date flex items-center gap-1 <?= $isFeatured ? 'text-md' : 'text-sm' ?>">
              <i class="fa-regular fa-calendar news-card__date-icon"></i>
              <?= newsCardPublishedDate($news) ?>
            </span>
          </div>

          <h3 class="news-card__title">
            <?= newsCardEscape($news->title ?? '') ?>
          </h3>

          <p class="news-card__description">
            <?= newsCardEscape($news->seo_description ?? '') ?>
          </p>

          <?php if ($showReadMore): ?>
            <span data-variant="outline-alt"
              class="news-card__link hidden md:flex items-center gap-1 text-md px-4 py-2 btn bouncy-btn rounded-full">
              Đọc Thêm
            </span>
          <?php endif; ?>
        </div>
      </a>
    </article>
    <?php
  }
}

if (!function_exists('renderNewsCard')) {
  function renderNewsCard(object $news, array $options = []): void
  {
    $variant = $options['variant'] ?? 'featured';
    $extraClass = trim($options['class'] ?? '');
    $showViewsBadge = (bool) ($options['show_views_badge'] ?? ($variant === 'featured'));
    $showCategoryInMeta = (bool) ($options['show_category_in_meta'] ?? ($variant === 'horizontal'));
    $articleClass = trim(sprintf(
      'card news-card %s hover-lift %s',
      $variant === 'horizontal' ? 'news-card--horizontal' : '',
      $extraClass
    ));
    ?>
    <article class="<?= newsCardEscape($articleClass) ?>" role="listitem" data-id="<?= (int) ($news->id ?? 0) ?>">
      <div class="card__header news-card__header">
        <a class="news-card__image-wrapper" href="<?= newsCardEscape(newsCardUrl($news)) ?>">
          <img src="<?= newsCardEscape(newsCardImageUrl($news)) ?>"
            onerror="this.onerror=null; this.src='<?= newsCardEscape(newsCardFallbackImageUrl()) ?>'"
            alt="<?= newsCardEscape($news->title ?? '') ?>" class="news-card__image">
        </a>
        <?php if (!$showCategoryInMeta): ?>
          <span class="badge news-card__badge" data-variant="primary"><?= newsCardEscape(newsCardCategory($news)) ?></span>
        <?php endif; ?>
        <?php if ($showViewsBadge): ?>
          <span class="badge news-card__views-badge" aria-label="<?= newsCardViews($news) ?> lÆ°á»£t xem"
            data-variant="secondary">
            <i class="fa-solid fa-arrow-trend-up" role="img" aria-hidden="true"></i>
            <?= newsCardViews($news) ?> lượt xem
          </span>
        <?php endif; ?>
      </div>
      <div class="card__content news-card__content">
        <div class="news-card__meta">
          <?php if ($showCategoryInMeta): ?>
            <div>
              <span class="badge news-card__badge"
                data-variant="primary"><?= newsCardEscape(newsCardCategory($news)) ?></span>
            </div>
          <?php endif; ?>
          <div>
            <i class="fa-regular fa-calendar" aria-hidden="true"></i>
            <time datetime="<?= newsCardEscape(newsCardPublishedDateTime($news)) ?>">
              <?= newsCardPublishedDate($news) ?>
            </time>
          </div>
          <?php if ($variant === 'horizontal'): ?>
            <div>
              <i class="fa-regular fa-eye" aria-hidden="true"></i>
              <span><?= newsCardViews($news) ?> lượt xem</span>
            </div>
          <?php endif; ?>
        </div>
        <h3 class="news-card__title">
          <a href="<?= newsCardEscape(newsCardUrl($news)) ?>"><?= newsCardEscape($news->title ?? '') ?></a>
        </h3>
        <p class="news-card__description"><?= newsCardEscape($news->seo_description ?? '') ?></p>
        <a href="<?= newsCardEscape(newsCardUrl($news)) ?>" class="link-hover--underline news-card__link">
          Xem chi tiết
          <i class="fa-solid fa-arrow-right"></i>
        </a>
      </div>
    </article>
    <?php
  }
}
