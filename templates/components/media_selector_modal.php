<div class="modal msm" id="media-selector-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Thư viện Media</h2>
    <p class="modal__description">Chọn ảnh từ thư viện hoặc tải lên ảnh mới.</p>
  </div>
<div class="msm-content">
  <!-- Tabs: Media Selector -->
  <div class="tabs" data-tabs data-id="media-selector-tabs" data-active="library">
  
    <!-- Tab List -->
    <div class="tabs__list" role="tablist">
      <a href="#media-selector-tabs:library"
        role="tab"
        aria-controls="media-selector-tabs-panel-library"
        data-tabs-trigger="library"
        class="tabs__trigger">
        <i class="fa-solid fa-images"></i> Thư viện
      </a>
      <a href="#media-selector-tabs:upload"
        role="tab"
        aria-controls="media-selector-tabs-panel-upload"
        data-tabs-trigger="upload"
        class="tabs__trigger">
        <i class="fa-solid fa-upload"></i> Tải lên
      </a>
    </div>
  
    <!-- Panel: Thư viện -->
    <div id="media-selector-tabs-panel-library"
      role="tabpanel"
      data-tabs-panel="library"
      class="tabs__panel">
  
      <div class="media-modal-toolbar">
        <div>
          <i class="fa-solid fa-magnifying-glass"></i>
          <input id="msm-search" class="field__input" type="search"
            placeholder="Tìm theo tên, alt text...">
        </div>
      </div>
  
      <div id="msm-grid" class="msm-grid msm-grid">
        <!-- Populated by JS -->
      </div>
  
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
  
    <!-- Panel: Tải lên -->
    <div id="media-selector-tabs-panel-upload"
      role="tabpanel"
      data-tabs-panel="upload"
      class="tabs__panel">
  
      <div class="field-group">
  
        <!-- Drag & Drop Zone -->
        <div id="msm-upload-zone" class="msm-upload-zone" role="button" tabindex="0" aria-label="Khu vực kéo thả ảnh">
          <input type="file" id="msm-file-input" name="file" accept="image/*,video/*" hidden required>
          <div class="empty msm-upload-zone__content" id="msm-upload-zone-content">
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
              <button type="button" class="btn" data-variant="primary" data-size="md" id="msm-upload-trigger">
                Chọn file
              </button>
            </div>
          </div>
          <div id="msm-preview-content">
            <img id="msm-preview-img" src="" alt="">
            <div>
              <p id="msm-preview-info" class="text-sm"></p>
            </div>
            <button type="button" id="msm-remove-file" class="btn"
              data-variant="destructive" data-size="sm">
              <i class="fa-solid fa-trash"></i> Xóa
            </button>
          </div>
        </div>

        <!-- Title -->
        <div class="field" data-field-required>
          <label class="field__label" for="msm-title-input">Tiêu đề</label>
          <input id="msm-title-input" class="field__input" type="text" name="title" placeholder="Nhập tiêu đề" value="">
        </div>

        <!-- File Name -->
        <div class="field" data-field-required data-field-readonly>
          <label class="field__label" for="msm-file-name-input">Tên file</label>
          <input id="msm-file-name-input" class="field__input" type="text"
                name="file_name" placeholder="Tự động điền khi chọn file"
                value="">
        </div>
  
        <!-- Alt Text -->
        <div class="field">
          <label class="field__label" for="msm-alt-text">Alt Text</label>
          <input id="msm-alt-text" class="field__input" type="text"
            placeholder="Mô tả nội dung media (SEO, accessibility)">
        </div>
  
        <!-- Compress Mode -->
        <div class="field" data-field-required>
          <label class="field__label" for="msm-compress-select">Chế độ nén ảnh</label>
          <button type="button" class="select" data-select-id="msm-compress-select" data-select-placeholder="Chọn" name="compress_mode" id="msm-compress-select" role="listbox" data-select-default-value="default" data-select-placeholder="Chọn chế độ nén">
            <div class="select__content">
              <div class="select__item" data-select-value="default">Kích thước chuẩn (max 800px, WebP 80%)</div>
              <div class="select__item" data-select-value="thumbnail">Ảnh thu nhỏ (max 320px, WebP 75%)</div>
            </div>
          </button>
          <p class="field__description">
            Hệ thống sẽ nén và chuyển đổi sang định dạng WebP tối ưu.
          </p>
        </div>
  
        <!-- Client-side compress preview -->
        <div id="msm-client-preview">
          <div class="media-compress-preview">
            <div class="media-compress-preview__col">
              <p class="text-sm font-medium">Gốc</p>
              <canvas id="msm-canvas-original"></canvas>
              <p id="msm-original-size" class="text-sm"></p>
            </div>
            <div class="media-compress-preview__col">
              <p class="text-sm font-medium">
                Sau nén
                <span id="msm-compress-label" class="badge" data-variant="primary"></span>
              </p>
              <canvas id="msm-canvas-preview"></canvas>
              <p id="msm-preview-size" class="text-sm"></p>
            </div>
          </div>
        </div>
  
        <!-- Upload Progress -->
        <div id="msm-progress">
          <div class="msm-progress-bar">
            <div id="msm-progress-fill" class="msm-progress-bar__fill"></div>
          </div>
          <p id="msm-progress-label" class="text-sm">
            Đang tải lên...
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

  <!-- Footer -->
  <div class="modal__footer flex justify-between items-center">
    <div id="msm-selected-info">
      Chưa chọn ảnh nào
    </div>
    <div class="flex gap-2 ml-auto">
      <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
      <button id="msm-select-btn" type="button" data-variant="primary" data-size="lg" class="btn" disabled>
        <i class="fa-solid fa-check"></i> Chọn ảnh này
      </button>
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

  // Compress mode config (maps client-side preview widths)
  const COMPRESS_CONFIG = {
    default: { width: 0, quality: 0.85, label: 'Default' },
    thumbnail: { width: 320, quality: 0.75, label: 'Thumbnail 320px' },
  };

  let _selectCallback = null;
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

  const fileInput = document.querySelector('#msm-file-input');
  const uploadZone = document.querySelector('#msm-upload-zone');
  const uploadTrigger = document.querySelector('#msm-upload-trigger');
  const uploadZoneContent = document.querySelector('#msm-upload-zone-content');
  const fileNameInput = document.querySelector('#msm-file-name-input');
  const previewContent = document.querySelector('#msm-preview-content');
  const previewImg = document.querySelector('#msm-preview-img');
  const previewInfo = document.querySelector('#msm-preview-info');
  const removeFileBtn = document.querySelector('#msm-remove-file');

  const altText = document.querySelector('#msm-alt-text');
  const clientPreview = document.querySelector('#msm-client-preview');
  const canvasOriginal = document.querySelector('#msm-canvas-original');
  const canvasPreview = document.querySelector('#msm-canvas-preview');
  const originalSizeLabel = document.querySelector('#msm-original-size');
  const previewSizeLabel = document.querySelector('#msm-preview-size');
  const compressLabel = document.querySelector('#msm-compress-label');

  const progressWrapper = document.querySelector('#msm-progress');
  const progressFill = document.querySelector('#msm-progress-fill');
  const progressLabel = document.querySelector('#msm-progress-label');

  const selectedInfo = document.querySelector('#msm-selected-info');
  const selectBtn = document.querySelector('#msm-select-btn');

  modal.addEventListener('modal:open', (e) => {
    const { modal: targetModal } = e.detail;
    if (targetModal.id !== modal.id) return;

    loadLibrary(1);
  });
  
  uploadTrigger.addEventListener('click', () => fileInput.click());

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
      console.log(`${API_MEDIA}?${params}`);
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
                ? `<img src="<?php echo url('public/img/'); ?>/${escHtml(imgUrl)}" alt="${escHtml(item.alt_text || item.file_name)}" loading="lazy">`
                : `<div class="media-card__thumb-icon"><i class="fa-solid fa-file"></i></div>`
              }
            </div>
          </div>
          <div class="card__footer">
            <div class="media-card__info">
              <div class="msm-card__name" title="${escHtml(item.file_name)}">${escHtml(item.file_name)}</div>
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
    if(_selectedMedia && _selectedMedia.id === item.id) {
      _selectedMedia = null;
      selectedInfo.textContent = "Chưa chọn ảnh";
      card.dataset.selected = "false";
      selectBtn.disabled = true;
      return;
    };

    msmGrid.querySelectorAll('.msm-card').forEach(c => delete c.dataset.selected);
    card.dataset.selected = "true";

    _selectedMedia = item;
    selectedInfo.textContent = item.file_name;
    selectBtn.disabled = false;
  }

  msmSearch.addEventListener('input', () => {
    clearTimeout(_searchTimeout);
    _searchTimeout = setTimeout(() => loadLibrary(1), 400);
  });

  // ── File input & Drag/Drop ─────────────────────────────────────────────────
  fileInput.addEventListener('change', () => {
    if (fileInput.files[0]) handleFile(fileInput.files[0]);
  });

    uploadZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadZone.classList.add('media-upload-zone--draging');
  });

  ['dragleave', 'dragend'].forEach(ev =>
    uploadZone.addEventListener(ev, () =>
      uploadZone.classList.remove('media-upload-zone--draging')
    )
  );

  uploadZone.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadZone.classList.remove('media-upload-zone--draging');

    const file = e.dataTransfer.files[0];
    if (!file) return;

    const dt = new DataTransfer();
    dt.items.add(file);
    fileInput.files = dt.files;

    handleFile(file);
  });

  removeFileBtn.addEventListener('click', () => {
    _pendingFile = null;
    _loadedImg = null;
    fileInput.value = '';
    uploadZoneContent.style.display = '';
    previewContent.style.display = 'none';
    clientPreview.style.display = 'none';
  });

  function handleFile(file) {
    if (file.size > MAX_BYTES) {
      toast.error(
        'Có lỗi xảy ra!',
        `File quá lớn! Tối đa ${MAX_BYTES / 1024 / 1024}MB.`
      );
      return;
    }
    renderPreview(file);
    autoFillName(file.name);
  }

  function renderPreview(file) {
    if (file.type.startsWith('image/')) {
      const reader = new FileReader();
      reader.onload = e => {
        previewImg.src = e.target.result;
        previewInfo.textContent = `${fmtBytes(file.size)} · ${file.type}`;
        uploadZoneContent.style.display = 'none';
        previewContent.style.display = 'flex';
      };
      reader.readAsDataURL(file);
    } else if (file.type.startsWith('video/')) {
      const video = document.createElement('video');
      video.controls = true;
      video.src = URL.createObjectURL(file);
      previewContent.style.display = 'flex';
      previewContent.innerHTML = '';
      previewContent.appendChild(video);
    }
  }

  function autoFillName(rawFileName) {
    const nameWithoutExt = rawFileName.replace(/\.[^.]+$/, '');
    if (!fileNameInput.value.trim()) {
      fileNameInput.value = nameWithoutExt;
    }
  }

  // ── Compress preview ───────────────────────────────────────────────────────
  function renderCompressPreview() {
    if (!_loadedImg) return;

    clientPreview.style.display = '';
    const mode = "";
    const cfg = COMPRESS_CONFIG[mode] ?? COMPRESS_CONFIG.standard;

    compressLabel.textContent = cfg.label;

    // Original canvas
    const ctxO = canvasOriginal.getContext('2d');
    canvasOriginal.width = _loadedImg.naturalWidth;
    canvasOriginal.height = _loadedImg.naturalHeight;
    ctxO.drawImage(_loadedImg, 0, 0);
    const origSize = dataUrlSize(canvasOriginal.toDataURL('image/png'));
    originalSizeLabel.textContent = `${_loadedImg.naturalWidth}×${_loadedImg.naturalHeight} · ${fmtBytes(origSize)}`;

    // Preview canvas
    let targetW = cfg.width > 0 ? Math.min(cfg.width, _loadedImg.naturalWidth) : _loadedImg.naturalWidth;
    const scale = targetW / _loadedImg.naturalWidth;
    const targetH = Math.round(_loadedImg.naturalHeight * scale);
    canvasPreview.width = targetW;
    canvasPreview.height = targetH;
    const ctxP = canvasPreview.getContext('2d');
    ctxP.drawImage(_loadedImg, 0, 0, targetW, targetH);

    const previewDataUrl = canvasPreview.toDataURL('image/webp', cfg.quality);
    const previewSize = dataUrlSize(previewDataUrl);
    previewSizeLabel.textContent = `${targetW}×${targetH} · ~${fmtBytes(previewSize)} (WebP)`;
  }

  modal.addEventListener('select:change', (e) => {
    const { detail } = e;
    console.log(detail);
    renderCompressPreview();
  });

  function dataUrlSize(dataUrl) {
    const base64 = dataUrl.split(',')[1] ?? '';
    return Math.round((base64.length * 3) / 4);
  }

  // ── Select button ──────────────────────────────────────────────────────────
  selectBtn.addEventListener('click', () => {
    if (!_selectedMedia || !_selectCallback) return;
    _selectCallback(_selectedMedia);
    modalHandler.close('#media-selector-modal');
    _selectCallback = null;
  });

  // ── Utilities ──────────────────────────────────────────────────────────────
  function escHtml(str) {
    if (!str) return '';
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  function fmtBytes(bytes) {
    if (!bytes) return '0 B';
    const k = 1024, sizes = ['B','KB','MB','GB'];
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

.media-modal-toolbar > div:first-child {
  position: relative;
  flex: 1;
}

.media-modal-toolbar .fa-magnifying-glass {
  position: absolute;
  left: 0.6rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--muted-foreground);
  font-size: var(--text-sm);
}

.media-modal-toolbar #msm-search {
  padding-left: 2rem;
  height: 2rem;
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
  aspect-ratio: 1 / 1;
  padding: 0;
  cursor: pointer;
  gap: 0rem;
}

.msm-card[data-selected="true"]::before {
  content: "";
  width: 100%;
  height: 100%;
  background: black;
  border-radius: var(--rounded-md);
  opacity: 0.4;
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
  display: none !important; /* Mặc định ẩn, điều khiển hiển thị bằng JS */
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
}

#msm-upload-zone .msm-file-input {
  width: 20rem;
}

#msm-preview-content {
  display: none;
  width: 100%;
  height: 100%;
  flex-direction: column;
  align-items: center;
}

#msm-preview-img {
  max-height: 10rem;
  max-width: 100%;
  object-fit: contain;
  border-radius: var(--radius-md);
}

#msm-preview-content > div:nth-child(2) {
  margin-top: 0.5rem;
}

#msm-preview-name {
  color: var(--foreground);
}

#msm-preview-info {
  color: var(--muted-foreground);
}

#msm-remove-file {
  margin-top: 0.5rem;
}

#msm-compress-mode {
  height: 2.25rem;
}

/* Client-side Compress Preview Section */
#msm-client-preview {
  display: none;
}

.media-compress-preview {
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
}

.media-compress-preview__col {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

#msm-canvas-original,
#msm-canvas-preview {
  max-width: 100%;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border);
}

#msm-original-size,
#msm-preview-size {
  color: var(--muted-foreground);
}

#msm-compress-label {
  font-size: 0.7rem;
}

/* Upload Progress Components */
#msm-progress {
  display: none;
}

#msm-progress-label {
  color: var(--muted-foreground);
  margin-top: 0.25rem;
  text-align: center;
}

/* Modal Footer layout & Elements */
.modal__footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

#msm-selected-info {
  font-size: var(--text-sm);
  color: var(--muted-foreground);
}

.modal__footer .flex.gap-2 {
  display: flex;
  gap: 0.5rem;
  margin-left: auto;
}
</style>