<!-- 
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Tin tức và sự kiện mới nhất từ Khoa Công nghệ Thông tin. Cập nhật thông tin về sinh viên, nghiên cứu, tuyển dụng và các sự kiện đặc biệt.">
<meta name="keywords" content="tin tức, sự kiện, khoa CNTT, công nghệ thông tin, sinh viên">
<meta name="author" content="Khoa Công nghệ Thông tin">
<meta property="og:title" content="Tin tức & Sự kiện - Khoa Công nghệ Thông tin">
<meta property="og:description" content="Cập nhật thông tin mới nhất từ Khoa CNTT">
<meta property="og:type" content="website">
<title>Tin tức & Sự kiện - Khoa Công nghệ Thông tin</title>
-->

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

    <?php if (!empty($featuredNews)): ?>
      <section class="news-section" aria-labelledby="featured-title">
        <h2 id="featured-title" class="news-section__title">
          <i class="fa-solid fa-arrow-trend-up" role="img" aria-hidden="true"></i>
          Nổi bật
        </h2>

        <div class="news-featured__grid" role="list" aria-live="polite">
          <?php foreach ($featuredNews as $news): ?>
            <article class="card news-card hover-lift" role="listitem" data-id="<?= $news->id ?>">
              <div class="card__header news-card__header">
                <a class="news-card__image-wrapper" href="<?= url('tin-tuc/' . $news->slug) ?>">
                  <img src="<?= url('public/media/' . $news->seo_image_url) ?>" alt="<?= htmlspecialchars($news->title) ?>"
                    class="news-card__image">
                </a>
                <span class="badge news-card__badge"
                  data-variant="primary"><?= htmlspecialchars($news->categories[0]->name ?? 'Tin tức') ?></span>
                <span class="badge news-card__views-badge" aria-label="<?= number_format($news->view_count) ?> lượt xem"
                  data-variant="secondary">
                  <i class="fa-solid fa-arrow-trend-up" role="img" aria-hidden="true"></i>
                  <?= number_format($news->view_count) ?> lượt xem
                </span>
              </div>
              <div class="card__content news-card__content">
                <div class="news-card__meta">
                  <div>
                    <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                    <time datetime="<?= date('Y-m-d', strtotime($news->published_at)) ?>">
                      <?= date('d/m/Y', strtotime($news->published_at)) ?>
                    </time>
                  </div>

                </div>
                <h3 class="news-card__title">
                  <a href="<?= url('tin-tuc/' . $news->slug) ?>"><?= htmlspecialchars($news->title) ?></a>
                </h3>
                <p class="news-card__description"><?= htmlspecialchars($news->seo_description ?? '') ?></p>
                <a href="<?= url('tin-tuc/' . $news->slug) ?>" class="link-hover--underline news-card__link">
                  Xem chi tiết
                  <i class="fa-solid fa-arrow-right"></i>
                </a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

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
              <div class="select__item" data-select-value="most-viewed">Xem nhiều nhất</div>
            </div>
          </button>
        </div>
      </div>

      <div class="all-news__list" role="list" aria-live="polite">
        <?php foreach ($allNews->getItems() as $news): ?>
          <article class="card news-card news-card--horizontal hover-lift" role="listitem" data-id="<?= $news->id ?>">
            <div class="card__header news-card__header">
              <a class="news-card__image-wrapper" href="<?= url('tin-tuc/' . $news->slug) ?>">
                <img src="<?= url('public/media/' . $news->seo_image_url) ?>" alt="<?= htmlspecialchars($news->title) ?>"
                  class="news-card__image">
              </a>
            </div>
            <div class="card__content news-card__content">
              <div class="news-card__meta">
                <div>
                  <span class="badge news-card__badge"
                    data-variant="primary"><?= htmlspecialchars($news->categories[0]->name ?? 'Tin tức') ?></span>
                </div>
                <div>
                  <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                  <time datetime="<?= date('Y-m-d', strtotime($news->published_at)) ?>">
                    <?= date('d/m/Y', strtotime($news->published_at)) ?>
                  </time>
                </div>
                <div>
                  <i class="fa-regular fa-eye" aria-hidden="true"></i>
                  <span><?= number_format($news->view_count) ?> lượt xem</span>
                </div>
              </div>
              <h3 class="news-card__title">
                <a href="<?= url('tin-tuc/' . $news->slug) ?>"><?= htmlspecialchars($news->title) ?></a>
              </h3>
              <p class="news-card__description"><?= htmlspecialchars($news->seo_description ?? '') ?></p>
              <a href="<?= url('tin-tuc/' . $news->slug) ?>" class="link-hover--underline news-card__link">
                Xem chi tiết
                <i class="fa-solid fa-arrow-right"></i>
              </a>
            </div>
          </article>
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