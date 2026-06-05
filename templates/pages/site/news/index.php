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

    <section class="news-section" aria-labelledby="featured-title">
      <h2 id="featured-title" class="news-section__title">
        <i class="fa-solid fa-arrow-trend-up" role="img" aria-hidden="true"></i>
        Nổi bật
      </h2>

      <div class="news-featured__grid" role="list" aria-live="polite">
        <?php if (!empty($featuredNews)): ?>
          <?php foreach ($featuredNews as $news): ?>
            <article class="card news-card hover-lift" role="listitem" data-id="<?= $news->id ?>">
              <div class="card__header news-card__header">
                <a class="news-card__image-wrapper" href="<?= url('tin-tuc/' . $news->slug) ?>">
                  <img src="<?= url('public/media/' . $news->seo_image_url) ?>"
                    onerror="this.onerror=null; this.src='<?= url('public/img/default-post-thumb.jpg') ?>'"
                    alt="<?= htmlspecialchars($news->title) ?>" class="news-card__image">
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
        <?php else: ?>
          <p class="news-list__empty">KhÃ´ng cÃ³ tin ná»•i báº­t phÃ¹ há»£p.</p>
        <?php endif; ?>
      </div>
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
          <article class="card news-card news-card--horizontal hover-lift" role="listitem" data-id="<?= $news->id ?>">
            <div class="card__header news-card__header">
              <a class="news-card__image-wrapper" href="<?= url('tin-tuc/' . $news->slug) ?>">
                <img src="<?= url('public/media/' . $news->seo_image_url) ?>"
                  onerror="this.onerror=null; this.src='<?= url('public/img/default-post-thumb.jpg') ?>'"
                  alt="<?= htmlspecialchars($news->title) ?>" class="news-card__image">
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

<template id="news-card-horizontal-template-v2">
  <article class="card news-card news-card--horizontal hover-lift" role="listitem" data-id="{{ id }}">
    <div class="card__header news-card__header">
      <a class="news-card__image-wrapper" href="{{ url }}">
        <img src="{{ image_url }}"
          onerror="this.onerror=null; this.src='<?= url('public/img/default-post-thumb.jpg') ?>'" alt="{{ title }}"
          class="news-card__image">
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
        <img src="{{ image_url }}"
          onerror="this.onerror=null; this.src='<?= url('public/img/default-post-thumb.jpg') ?>'" alt="{{ title }}"
          class="news-card__image">
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
  document.addEventListener('DOMContentLoaded', function () {
    // Cấu hình URLs hệ thống
    const apiUrl = '<?= url('api/v1/posts') ?>';
    const postDetailBaseUrl = '<?= url('tin-tuc') ?>';
    const mediaBaseUrl = '<?= url('public/media') ?>';
    const defaultImageUrl = '<?= url('public/img/default-post-thumb.jpg') ?>';

    // Khởi tạo và tham chiếu các phần tử DOM
    const list = document.querySelector('.all-news__list');
    const featuredList = document.querySelector('.news-featured__grid');
    const loadMoreBtn = document.getElementById('load-more-btn');
    const template = document.getElementById('news-card-horizontal-template-v2');
    const featuredTemplate = document.getElementById('news-card-featured-template');
    const searchInput = document.getElementById('news-search-input');
    const searchBtn = document.querySelector('.news-searchbar__btn');
    const filterBtns = document.querySelectorAll('.news-filters__tag');
    const sortSelect = document.getElementById('all-news-sort-select');

    // Quản lý trạng thái phân trang, tìm kiếm và bộ lọc
    const state = new ApiResultState({
      page: <?= (int) $allNews->getCurrentPage() ?>,
      limit: <?= (int) $allNews->getPerPage() ?>,
      lastPage: <?= (int) $allNews->getTotalPages() ?>,
      params: {
        sort: 'published_at',
        order: 'desc',
      }
    });
    state.search = '';
    state.category = 'all';
    state.sortMode = 'newest';

    // Xử lý và chuẩn hóa dữ liệu bài viết
    function mapPost(post) {
      const category = Array.isArray(post.categories) && post.categories.length ? post.categories[0] : null;

      return {
        id: post.id,
        title: post.title || '',
        description: post.seo_description || '',
        category_name: category?.name || 'Tin tức',
        published_datetime: AppUtils.formatDateTimeAttribute(post.published_at),
        published_date: AppUtils.formatDate(post.published_at),
        view_count: AppUtils.formatNumber(post.view_count),
        image_url: AppUtils.resolveAssetUrl(post.seo_image_url, mediaBaseUrl, defaultImageUrl),
        url: AppUtils.joinUrl(postDetailBaseUrl, encodeURIComponent(post.slug || '')),
      };
    }

    // Render HTML bài viết thường
    function renderPost(post) {
      return AppUtils.bindTemplate(template.innerHTML, mapPost(post));
    }

    // Render HTML bài viết nổi bật
    function renderFeaturedPost(post) {
      return AppUtils.bindTemplate(featuredTemplate.innerHTML, mapPost(post));
    }

    // Trạng thái loading nút "Xem thêm"
    function setLoading(isLoading) {
      state.loading = isLoading;
      if (!loadMoreBtn) return;

      loadMoreBtn.disabled = isLoading;
      loadMoreBtn.textContent = isLoading ? 'Đang tải...' : 'Xem Thêm';
    }

    // Ẩn/hiện nút "Xem thêm" theo phân trang
    function syncLoadMore() {
      if (!loadMoreBtn) return;
      loadMoreBtn.hidden = !state.canLoadMore();
      loadMoreBtn.setAttribute('aria-expanded', state.page > 1 ? 'true' : 'false');
    }

    // Xử lý tham số sắp xếp
    function getSortParams() {
      return {
        sort: 'published_at',
        order: state.sortMode === 'oldest' ? 'asc' : 'desc',
      };
    }

    // Gọi API lấy dữ liệu thô
    async function requestPosts(queryState, overrides = {}) {
      const response = await fetch(`${apiUrl}?${queryState.queryString(overrides)}`, {
        headers: { 'Accept': 'application/json' },
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      return response.json();
    }

    // Lấy dữ liệu và cập nhật danh sách bài viết thường
    async function fetchPosts(options = {}) {
      const reset = options.reset === true;
      if (!list || !template || state.loading) return;

      setLoading(true);

      const targetPage = reset ? 1 : state.nextPage();
      const sortParams = getSortParams();
      state.setParams(sortParams);
      state.setParam('search', state.search);
      state.setParam('filter', state.category !== 'all' ? state.category : null);

      try {
        const payload = await requestPosts(state, { page: targetPage });
        const posts = Array.isArray(payload.data) ? payload.data : [];

        if (reset) {
          list.innerHTML = '';
        }

        list.insertAdjacentHTML('beforeend', posts.map(renderPost).join(''));
        state.setMeta(payload.meta || { current_page: targetPage });

        if (reset && posts.length === 0) {
          list.innerHTML = '<p class="news-list__empty">Không có tin tức phù hợp.</p>';
        }

        syncLoadMore();
      } catch (error) {
        console.error('Không thể truy vấn bài viết', error);
      } finally {
        setLoading(false);
      }
    }

    // Reset trạng thái và tải lại toàn bộ danh sách
    function resetAndFetch() {
      state.search = searchInput?.value.trim() || '';
      state.resetPage();
      fetchPosts({ reset: true });
      fetchFeaturedPosts();
    }

    // Lấy dữ liệu và cập nhật danh sách bài viết nổi bật
    async function fetchFeaturedPosts() {
      if (!featuredList || !featuredTemplate) return;

      const featuredState = new ApiResultState({
        page: 1,
        limit: 2,
        params: {
          featured: 1,
          sort: 'published_at',
          order: 'desc',
        },
      });

      featuredState.setParam('search', state.search);
      featuredState.setParam('filter', state.category !== 'all' ? state.category : null);

      try {
        const payload = await requestPosts(featuredState);
        const posts = Array.isArray(payload.data) ? payload.data : [];
        featuredList.innerHTML = posts.length
          ? posts.map(renderFeaturedPost).join('')
          : '<p class="news-list__empty">Không có tin nổi bật phù hợp.</p>';
      } catch (error) {
        console.error('Không thể truy vấn bài viết nổi bật', error);
      }
    }

    // Bộ lọc danh mục (Category)
    filterBtns.forEach(function (button) {
      button.addEventListener('click', function () {
        state.category = button.dataset.category || 'all';

        filterBtns.forEach(function (item) {
          const isActive = item === button;
          item.setAttribute('aria-pressed', isActive ? 'true' : 'false');
          item.dataset.variant = isActive ? 'primary' : 'outline';
        });

        resetAndFetch();
      });
    });

    // Ô tìm kiếm (Search Input & Button)
    searchBtn?.addEventListener('click', resetAndFetch);
    searchInput?.addEventListener('keydown', function (event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        resetAndFetch();
      }
    });

    // Thay đổi kiểu sắp xếp (Sort Select)
    sortSelect?.addEventListener('select:change', function (event) {
      state.sortMode = event.detail?.value || 'newest';
      resetAndFetch();
    });

    // Nút tải thêm dữ liệu (Load More)
    if (loadMoreBtn && template) {
      loadMoreBtn.addEventListener('click', function () {
        fetchPosts();
      });
    }

    syncLoadMore();
  });
</script>
<?php $layout->end() ?>