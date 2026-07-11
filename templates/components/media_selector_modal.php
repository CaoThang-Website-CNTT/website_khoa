<div class="modal msm" id="media-selector-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Thư viện Media</h3>
    <p class="modal__description">Chọn ảnh từ thư viện hoặc tải lên ảnh mới.</p>
  </div>
  <div class="msm-content">
    <div class="tabs" data-tabs data-tabs-id="media-selector-tabs" data-tabs-panel-active="library">

      <div class="tabs__list" role="tablist">
        <a href="#media-selector-tabs:library" role="tab" aria-controls="media-selector-tabs-panel-library"
          data-tabs-trigger="library" class="tabs__trigger">
          <i class="fa-solid fa-images"></i> Thư viện
        </a>
        <a href="#media-selector-tabs:upload" role="tab" aria-controls="media-selector-tabs-panel-upload"
          data-tabs-trigger="upload" class="tabs__trigger">
          <i class="fa-solid fa-upload"></i> Tải lên
        </a>
      </div>

      <div id="media-selector-tabs-panel-library" role="tabpanel" data-tabs-panel="library" class="tabs__panel">

        <div class="media-modal-toolbar">
          <div class="msm-search-bar">
            <label for="msm-search">
              <i class="msm-search-bar__icon fa-solid fa-magnifying-glass"></i>
            </label>
            <input id="msm-search" class="msm-search-bar__input" type="search" placeholder="Tìm theo tên, alt text...">
          </div>
        </div>

        <div id="msm-grid" class="msm-grid msm-grid"></div>

        <div id="msm-empty" class="empty">
          <div class="empty__header">
            <div class="empty__media">
              <i class="fa-solid fa-photo-film"></i>
            </div>
            <div class="empty__title">Chưa có ảnh nào</div>
          </div>
        </div>

        <div id="msm-loading" class="msm-loading">
          <i class="fa-solid fa-circle-notch fa-spin"></i>
        </div>

        <div id="msm-pagination" class="flex justify-center gap-2 mt-3"></div>
      </div>

      <div id="media-selector-tabs-panel-upload" role="tabpanel" data-tabs-panel="upload" class="tabs__panel">

        <div class="field-group">

          <div id="msm-upload-zone" class="msm-upload-zone" role="button" tabindex="0" aria-label="Khu vực kéo thả ảnh"
            data-mu-zone data-mu-max-bytes="<?= (int) ($_ENV['MAX_UPLOAD_SIZE'] ?? 5) * 1024 * 1024 ?>">

            <input type="file" name="file" accept="image/*,video/*" hidden data-mu-input>

            <div class="empty msm-upload-zone__content" data-mu-empty>
              <div class="empty__header">
                <div class="empty__media">
                  <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <div class="empty__title">Kéo thả ảnh vào đây</div>
                <div class="empty__description">
                  Tối đa <?= (int) ($_ENV['MAX_UPLOAD_SIZE'] ?? 5) ?>MB · JPG, PNG, GIF, WebP
                </div>
              </div>
              <div class="empty__content">
                <span>hoặc</span>
                <button type="button" class="btn" data-variant="primary" data-size="md" data-mu-trigger>
                  Chọn file
                </button>
              </div>
            </div>

            <div class="msm-preview-content" hidden data-mu-preview>
              <div data-mu-preview-frame></div>
              <p class="text-sm" data-mu-preview-info></p>
              <button type="button" class="btn" data-variant="destructive" data-size="sm" data-mu-remove>
                <i class="fa-solid fa-trash"></i> Xóa
              </button>
            </div>

          </div>

          <div class="field">
            <label class="field__label" for="msm-title-input">Tiêu đề</label>
            <input id="msm-title-input" class="field__input" type="text" name="title" placeholder="Nhập tiêu đề"
              value="">
            <p class="field__description">Đặt tên dễ nhớ (Optional)</p>
          </div>

          <div class="field">
            <label class="field__label" for="msm-alt-text">Alt Text</label>
            <input id="msm-alt-text" class="field__input" type="text"
              placeholder="Mô tả nội dung media (SEO, accessibility)">
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal__footer">
    <div data-tabs-observe="media-selector-tabs:library"
      class="msm-footer-container flex justify-between items-center w-full">
      <div id="msm-selected-info" class="text-sm text-muted-foreground">
        Chưa chọn ảnh nào
      </div>
      <div class="flex gap-2 ml-auto">
        <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
        <button id="msm-select-btn" type="button" data-variant="primary" data-size="lg" class="btn" disabled>
          <i class="fa-solid fa-check"></i> Chọn ảnh này
        </button>
      </div>
    </div>

    <div data-tabs-observe="media-selector-tabs:upload"
      class="msm-footer-container flex justify-between items-center w-full">
      <div class="text-sm text-muted-foreground">
        Vui lòng điền đủ thông tin trước khi tải lên
      </div>
      <div class="flex gap-2 ml-auto">
        <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
        <button id="msm-upload-submit-btn" type="button" data-variant="primary" data-size="lg" class="btn" disabled>
          Tải lên
        </button>
      </div>
    </div>
  </div>

  <button class="modal__close" type="button" data-modal-close>
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>

<script>
  (function () {
    const API_MEDIA = '<?= url('api/v1/media') ?>';
    const MEDIA_BASE = '<?= url('public') ?>';
    const CSRF = '<?= csrf_token() ?>';
    const MAX_BYTES = <?= (int) ($_ENV['MAX_UPLOAD_SIZE'] ?? 5) * 1024 * 1024 ?>;

    let _selectedMedia = null;
    let _pendingFile = null;
    let _pendingUrl = '';
    let _loadedImg = null;
    let _searchTimeout = null;
    let _msmPage = 1;

    // Elements
    const modal = document.querySelector('#media-selector-modal');

    const msmGrid = document.querySelector('#msm-grid');
    const msmLoading = document.querySelector('#msm-loading');
    const msmEmpty = document.querySelector('#msm-empty');
    const msmPagination = document.querySelector('#msm-pagination');
    const msmSearch = document.querySelector('#msm-search');

    const uploadZone = document.querySelector('#msm-upload-zone');
    const msmTitleInput = document.querySelector('#msm-title-input');
    const msmAltText = document.querySelector('#msm-alt-text');

    const selectedInfo = document.querySelector('#msm-selected-info');
    const selectBtn = document.querySelector('#msm-select-btn');
    const uploadSubmitBtn = document.querySelector('#msm-upload-submit-btn');

    modal.addEventListener('modal:open', (e) => {
      const { modal: targetModal } = e.detail;
      if (targetModal.id !== modal.id) return;

      loadLibrary(1);
    });

    modal.addEventListener('mu:file-selected', () => {
      uploadSubmitBtn.disabled = false;
    })

    modal.addEventListener('mu:file-removed', () => {
      uploadSubmitBtn.disabled = true;
    })


    // ── Library: Load grid ─────────────────────────────────────────────────────
    async function loadLibrary(page = 1) {
      _msmPage = page;
      const search = msmSearch.value.trim();
      const params = new URLSearchParams({ page, per_page: 16 });
      if (search) params.set('search', search);

      msmLoading.dataset.show = 'true';
      msmGrid.style.display = 'none';
      msmEmpty.style.display = 'none';
      msmPagination.style.display = 'none';

      try {
        const res = await fetch(`${API_MEDIA}?${params}`);
        const data = await res.json();
        const items = data?.data ?? [];
        const meta = data?.meta ?? {};

        delete msmLoading.dataset.show;

        if (items.length === 0) {
          msmEmpty.style.display = 'flex';
          return;
        }

        msmGrid.innerHTML = '';
        items.forEach(item => {
          const imgUrl = item.file_path;
          const card = document.createElement('div');
          card.className = 'card msm-card';
          card.dataset.id = item.id;

          card.innerHTML = `
          <div class="card__content">
            <div class="msm-thumb">
              ${item.mime_type?.startsWith('image/')
              ? `<img src="${mediaUrl(imgUrl)}" alt="${escHtml(item.alt_text || item.file_name)}" loading="lazy">`
              : `<div class="media-card__thumb-icon"><i class="fa-solid fa-file"></i></div>`
            }
            </div>
          </div>
          <div class="card__footer">
            <div class="msm-card__info">
              <div class="msm-card__name" title="${escHtml(item.title)}">${escHtml(item.title)}</div>
              <div class="msm-card__meta">${fmtBytes(item.file_size)}</div>
            </div>
          </div>
        `;

          card.addEventListener('click', () => selectLibraryItem(card, item));
          msmGrid.appendChild(card);
        });
        msmGrid.style.display = 'grid';

        // Pagination
        if ((meta.last_page ?? 1) > 1) {
          msmPagination.style.removeProperty('display');
          msmPagination.innerHTML = '';
          const prev = makePageBtn('<i class="fa-solid fa-chevron-left"></i>', meta.current_page <= 1, () => loadLibrary(meta.current_page - 1));
          const info = document.createElement('span');
          info.style.cssText = 'display:flex;align-items:center;padding:0 .5rem;font-size:var(--text-sm);color:var(--muted-foreground)';
          info.textContent = `${meta.current_page} / ${meta.last_page}`;
          const next = makePageBtn('<i class="fa-solid fa-chevron-right"></i>', meta.current_page >= meta.last_page, () => loadLibrary(meta.current_page + 1));
          msmPagination.append(prev, info, next);
        }
      } catch (e) {
        delete msmLoading.dataset.show;
        msmEmpty.style.display = 'flex';
      }
    }

    function makePageBtn(html, disabled, onClick) {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'btn';
      btn.setAttribute('data-variant', 'outline');
      btn.setAttribute('data-size', 'md');
      btn.innerHTML = html;
      btn.disabled = disabled;
      btn.addEventListener('click', onClick);
      return btn;
    }

    function selectLibraryItem(card, item) {
      if (_selectedMedia && _selectedMedia.id === item.id) {
        _selectedMedia = null;
        selectedInfo.textContent = "Chưa chọn ảnh";
        card.dataset.selected = "false";
        selectBtn.disabled = true;
        return;
      };

      msmGrid.querySelectorAll('.msm-card').forEach(c => delete c.dataset.selected);
      card.dataset.selected = "true";

      _selectedMedia = item;
      selectedInfo.textContent = `${item.title} (${item.file_name})`;
      selectBtn.disabled = false;
    }

    msmSearch.addEventListener('input', () => {
      clearTimeout(_searchTimeout);
      _searchTimeout = setTimeout(() => loadLibrary(1), 400);
    });

    // ── File upload - delegated to MediaUploader module ──────────────────────
    uploadZone.addEventListener('mu:file-selected', (e) => {
      const { file } = e.detail;
      _pendingFile = file;
    });

    uploadZone.addEventListener('mu:preview-ready', (e) => {
      const { kind, file } = e.detail;

      if (kind === 'image' && file) {
        const url = URL.createObjectURL(file);
        const img = new Image();
        img.onload = () => {
          URL.revokeObjectURL(url);
          _loadedImg = img;
        };
        img.src = url;
      }
    });

    uploadZone.addEventListener('mu:file-removed', () => {
      _pendingFile = null;
      _loadedImg = null;
    });

    uploadZone.addEventListener('mu:error', (e) => {
      const { reason, maxBytes } = e.detail;
      if (reason === 'size') toast.error('Có lỗi xảy ra!', `File quá lớn! Tối đa ${Math.round(maxBytes / 1024 / 1024)}MB.`);
      if (reason === 'type') toast.error('Có lỗi xảy ra!', 'Định dạng file không được hỗ trợ.');
    });

    // ── Xử lý gửi Form tải ảnh ────────────────────────────────────────────────
    uploadSubmitBtn.addEventListener('click', async () => {
      if (!_pendingFile) {
        toast.error('Lỗi', 'Vui lòng chọn ảnh trước khi tải lên.');
        return;
      }

      try {
        uploadSubmitBtn.disabled = true;

        const formData = new FormData();
        formData.append('file', _pendingFile);
        formData.append('title', msmTitleInput.value.trim());
        formData.append('alt_text', msmAltText.value.trim());
        formData.append('_token', CSRF);

        const response = await fetch(API_MEDIA, {
          method: 'POST',
          body: formData
        });

        if (response.ok) {
          toast.success('Thành công', 'Ảnh đã được tải lên.');

          // Reset form upload
          _pendingFile = null;
          msmTitleInput.value = '';
          msmAltText.value = '';

          // Quay lại tab thư viện và đồng bộ container footer
          const libraryTrigger = document.querySelector('[data-tabs-trigger="library"]');
          if (libraryTrigger) {
            libraryTrigger.click();
          } else {
            syncObservers('media-selector-tabs', 'library');
          }

          // Tải lại thư viện để cập nhật ảnh vừa tải lên
          loadLibrary(1);
        } else {
          toast.error('Thất bại', 'Tải lên không thành công.');
        }
      } catch (e) {
        toast.error('Lỗi', 'Có lỗi xảy ra trong quá trình kết nối.');
      } finally {
        uploadSubmitBtn.disabled = false;
      }
    });

    // ── Select button ──────────────────────────────────────────────────────────
    selectBtn.addEventListener('click', () => {
      if (!_selectedMedia) return;

      modal.dispatchEvent(new CustomEvent('msm:submit', {
        bubbles: true,
        detail: {
          media: _selectedMedia,
          pendingFile: _pendingFile,
          close: () => {
            if (typeof ModalHandler.instance !== 'undefined' && ModalHandler.instance.close) {
              ModalHandler.instance.close();
            }
          }
        }
      }));
    });

    // ── Utilities ──────────────────────────────────────────────────────────────
    function escHtml(str) {
      if (!str) return '';
      return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function mediaUrl(path) {
      const raw = String(path || '').trim();
      if (!raw) return <?= json_encode(url('public/media')) ?>;
      if (/^(https?:)?\/\//i.test(raw) || raw.startsWith('data:')) return raw;

      let normalized = raw.replace(/\\/g, '/').replace(/^\/+/, '');
      if (normalized.startsWith('public/media/')) normalized = normalized.slice('public/media/'.length);
      if (normalized.startsWith('media/')) normalized = normalized.slice('media/'.length);

      return `${<?= json_encode(url('public/media')) ?>}/${normalized}`;
    }

    function fmtBytes(bytes) {
      if (!bytes) return '0 B';
      const k = 1024, sizes = ['B', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }
  })();
</script>

<style>
  .msm {
    max-width: 52rem;
    width: calc(100% - 2rem);
  }

  .msm-content {
    overflow-y: auto;
    max-height: 60vh;
  }

  /* Toolbar & Search Components */
  .media-modal-toolbar {
    display: flex;
    gap: 1rem;
    align-items: center;
    margin-bottom: 1rem;
  }

  .media-modal-toolbar>div:first-child {
    position: relative;
    flex: 1;
  }

  .msm-search-bar {
    position: relative;
    display: flex;
    align-items: center;
    background: var(--background);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 0 0.75rem;
    gap: 0.5rem;
  }

  .msm-search-bar__icon {
    color: var(--muted-foreground);
    display: flex;
    font-size: 0.875rem;
  }

  .msm-search-bar__input {
    padding: 0.5rem 0;
    border: none;
    background: transparent;
    font-size: var(--text-sm);
    outline: none;
    color: var(--foreground);
    min-width: 200px;
    width: 100%;
  }

  /* Empty States */
  #msm-empty {
    display: none;
    min-height: 8rem;
  }

  #msm-empty .empty__media {
    font-size: 1.5rem;
  }

  .msm-loading {
    display: none;
    height: 10rem;
  }

  .msm-loading[data-show="true"] {
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .msm-loading .fa-solid {
    font-size: var(--text-2xl);
    color: var(--muted-foreground);
  }

  .msm-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.5rem;
  }

  .msm-card {
    position: relative;
    overflow: hidden;
    width: 100%;
    padding: 0;
    cursor: pointer;
    gap: 0rem;
  }

  .msm-card[data-selected="true"]::before {
    content: "";
    width: 100%;
    height: 100%;
    background: var(--ring);
    border-radius: var(--rounded-md);
    opacity: 0.1;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
  }

  .msm-card .card__content {
    flex: 1;
    padding: 0.5rem 0.5rem 0 0.5rem;
    display: grid;
    place-items: center;
  }

  .msm-thumb img {
    object-fit: contain;
  }

  .msm-card__name {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    height: 3em;
    font-size: var(--text-sm);
  }

  .msm-card__meta {
    font-size: var(--text-xs);
    color: var(--muted-foreground);
  }

  .msm-card .card__footer {
    flex-shrink: 0;
    padding: 0.5rem;
  }

  /* Pagination */
  #msm-pagination {
    display: none !important;
  }

  #msm-pagination span {
    display: flex;
    align-items: center;
    padding: 0 0.5rem;
    font-size: var(--text-sm);
    color: var(--muted-foreground);
  }

  /* Upload Zone */
  .msm-upload-zone {
    border: 2px dashed var(--border);
    border-radius: var(--radius-md);
    padding: 2rem;
    text-align: center;
    transition: border-color 150ms ease, background-color 150ms ease;
  }

  .msm-upload-zone.mu--dragging {
    opacity: 0.5;
    background-color: var(--muted);
    border-color: var(--primary);
  }

  .msm-upload-zone .mu-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
  }

  .msm-upload-zone.mu--has-file .mu-empty {
    display: none;
  }

  .msm-preview-content {
    display: flex;
    width: 100%;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
  }

  .msm-upload-zone:not(.mu--has-file) .msm-preview-content {
    display: none;
  }

  .mu-preview__img {
    max-height: 10rem;
    max-width: 100%;
    object-fit: contain;
    border-radius: var(--radius-md);
  }

  .mu-preview__video {
    max-height: 10rem;
    max-width: 100%;
    border-radius: var(--radius-md);
  }

  [data-mu-preview-info] {
    color: var(--muted-foreground);
    font-size: var(--text-sm);
  }

  /* Modal Footer layout & Elements */
  .msm-footer-container {
    display: none !important;
  }

  .msm-footer-container[data-tabs-panel-state="active"] {
    display: flex !important;
  }
</style>
