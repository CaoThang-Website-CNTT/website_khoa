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
    const searchForm = document.querySelector('.news-search');
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
    state.search = config.initialSearch || '';
    state.category = config.initialCategory || 'all';
    state.sortMode = config.initialSort === 'oldest' ? 'oldest' : 'newest';
    state.setParam('featured', '0');

    function syncFilterButtons() {
      filterBtns.forEach(function (item) {
        const isActive = (item.dataset.category || 'all') === state.category;
        item.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        item.dataset.variant = isActive ? 'primary' : 'outline';
      });
    }

    function syncUrl(mode = 'push') {
      const next = new URL(window.location.href);
      ['search', 'category', 'sort', 'filter', 'page'].forEach((key) => next.searchParams.delete(key));
      if (state.search) next.searchParams.set('search', state.search);
      if (state.sortMode === 'oldest') next.searchParams.set('sort', 'oldest');

      // Ghi đè đường dẫn URL dựa trên danh mục đã map
      const urlMap = config.urlMap || {};
      const mappedUrlStr = urlMap[state.category];

      if (mappedUrlStr) {
        try {
          const mappedUrl = new URL(mappedUrlStr, window.location.origin);
          next.pathname = mappedUrl.pathname;
        } catch (e) {
          next.pathname = mappedUrlStr;
        }
      } else {
        // fallback: redirect về URL gốc của trang tin tức + query ?category
        try {
          const mappedUrl = new URL(postDetailBaseUrl, window.location.origin);
          next.pathname = mappedUrl.pathname;
        } catch (e) {
          next.pathname = postDetailBaseUrl;
        }
        if (state.category !== 'all') {
          next.searchParams.set('category', state.category);
        }
      }

      window.history[mode === 'replace' ? 'replaceState' : 'pushState']({}, '', next);
    }

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
      state.setParam('category', state.category !== 'all' ? state.category : null);

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
    function resetAndFetch(options = {}) {
      state.search = searchInput?.value.trim() || '';
      state.resetPage();
      if (options.updateHistory !== false) syncUrl(options.historyMode || 'push');
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
      featuredState.setParam('category', state.category !== 'all' ? state.category : null);

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
      button.addEventListener('click', function (event) {
        event.preventDefault();
        state.category = button.dataset.category || 'all';
        syncFilterButtons();
        resetAndFetch();
      });
    });

    // Ô tìm kiếm (Search Input & Button)
    searchForm?.addEventListener('submit', function (event) {
      event.preventDefault();
      resetAndFetch();
    });
    searchInput?.addEventListener('keydown', function (event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        resetAndFetch();
      }
    });
    searchBtn?.addEventListener('click', function () {
      resetAndFetch();
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

    window.addEventListener('popstate', function () {
      const params = new URLSearchParams(window.location.search);
      state.search = params.get('search') || '';
      state.category = params.get('category') || 'all';
      state.sortMode = params.get('sort') === 'oldest' ? 'oldest' : 'newest';
      if (searchInput) searchInput.value = state.search;
      syncFilterButtons();
      state.resetPage();
      fetchPosts({ reset: true });
      fetchFeaturedPosts();
    });

    syncFilterButtons();
    syncLoadMore();
  });
