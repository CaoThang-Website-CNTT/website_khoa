  document.addEventListener('DOMContentLoaded', function () {
    // Cấu hình URLs hệ thống
    const config = window.__siteNewsIndex__ || {};
    const apiUrl = config.apiUrl || '';
    const postDetailBaseUrl = config.postDetailBaseUrl || '';
    const mediaBaseUrl = config.mediaBaseUrl || '';
    const defaultImageUrl = config.defaultImageUrl || '';

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
    const loadMoreIdleHtml = loadMoreBtn?.innerHTML || 'Xem Thêm';
    const spinnerHtml = '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>';

    // Quản lý trạng thái phân trang, tìm kiếm và bộ lọc
    const state = new ApiResultState({
      page: config.page || 1,
      limit: config.limit || 10,
      lastPage: config.lastPage || 1,
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
        image_url: post.image_url || AppUtils.resolveAssetUrl(post.seo_image_url, mediaBaseUrl, defaultImageUrl),
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
      searchBtn && (searchBtn.disabled = isLoading);
      if (sortSelect) {
        sortSelect.disabled = isLoading;
        if (isLoading) {
          sortSelect.setAttribute('data-select-disabled', '');
        } else {
          sortSelect.removeAttribute('data-select-disabled');
        }
      }
      filterBtns.forEach(function (button) {
        button.disabled = isLoading;
      });
      queueMicrotask(function () {
        loadMoreBtn.innerHTML = isLoading
          ? `${spinnerHtml} Đang tải...`
          : loadMoreIdleHtml;
      });
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
      state.setParams(getSortParams());
      state.setParam('search', state.search);
      state.setParam('filter', state.category !== 'all' ? state.category : null);

      try {
        const payload = await requestPosts(state, { page: targetPage });
        const posts = Array.isArray(payload.data) ? payload.data : [];

        if (reset) {
          list.innerHTML = posts.length
            ? posts.map(renderPost).join('')
            : '<p class="news-list__empty">Không có tin tức phù hợp.</p>';
        } else {
          list.insertAdjacentHTML('beforeend', posts.map(renderPost).join(''));
        }

        state.setMeta(payload.meta || { current_page: targetPage });
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
