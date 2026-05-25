document.addEventListener('DOMContentLoaded', () => {
  new MediaUploadHandler().init();
});

class MediaUploadHandler {
  static #instance = null;
  #roots = null;
  uploaders = null;

  constructor() {
    if (MediaUploadHandler.#instance) return MediaUploadHandler.#instance;

    this.uploaders = new Map();
    this.#roots = document.querySelectorAll('[data-mu-zone]');

    MediaUploadHandler.#instance = this;
  }

  static get instance() {
    return MediaUploadHandler.#instance || new MediaUploadHandler();
  }

  init() {
    this.#roots.forEach(zone => {
      const id = zone.id || crypto.randomUUID();

      const newUploader = new MediaUploader(zone);

      this.uploaders.set(id, newUploader);
    })
  }
}

class MediaUploader {
  /** @type {HTMLElement} */ #zone;
  /** @type {HTMLInputElement} */ #input;
  /** @type {HTMLElement} */ #trigger;
  /** @type {HTMLElement} */ #removeBtn;
  /** @type {HTMLElement} */ #empty;
  /** @type {HTMLElement} */ #preview;
  /** @type {HTMLElement} */ #previewFrame;
  /** @type {HTMLElement} */ #previewInfo;

  /** @type {File|null} */ #currentFile = null;
  /** @type {string|null} */ #activeObjectUrl = null;
  /** @type {RegExp} */ #acceptPattern;
  /** @type {number} */ #maxBytes;
  /** @type {boolean} */ #isMobile;

  static #ACCEPT_MIME = /^(image\/|video\/|application\/pdf|application\/msword|application\/vnd\.openxmlformats|text\/plain)/i;
  static #MAX_BYTES = 5 * 1024 * 1024;

  static #KINDS = {
    image: /^image\//,
    video: /^video\//,
    pdf: /^application\/pdf/,
    doc: /^(application\/msword|application\/vnd\.openxmlformats|text\/plain)/,
  };

  /**
   * @param {string|HTMLElement} zoneSelector — CSS selector or element with [data-mu-zone]
   * @param {{ accept?: RegExp, maxBytes?: number }} [opts]
   */
  constructor(zoneSelector, opts = {}) {
    this.#zone = typeof zoneSelector === 'string'
      ? document.querySelector(zoneSelector)
      : zoneSelector;

    if (!this.#zone) throw new Error('[MediaUploader] Zone chưa được tìm thấy.');

    this.#input = this.#zone.querySelector('[data-mu-input]');
    this.#trigger = this.#zone.querySelector('[data-mu-trigger]');
    this.#removeBtn = this.#zone.querySelector('[data-mu-remove]');
    this.#empty = this.#zone.querySelector('[data-mu-empty]');
    this.#preview = this.#zone.querySelector('[data-mu-preview]');
    this.#previewFrame = this.#zone.querySelector('[data-mu-preview-frame]');
    this.#previewInfo = this.#zone.querySelector('[data-mu-preview-info]');

    this.#acceptPattern = opts.accept ?? MediaUploader.#ACCEPT_MIME;
    this.#maxBytes = opts.maxBytes ?? (parseInt(this.#zone.dataset.muMaxBytes, 10) || MediaUploader.#MAX_BYTES);

    this.#isMobile = /Mobi|Android|iPhone|iPad/i.test(navigator.userAgent);

    this.#zone.classList.add('mu-zone');
    this.#empty?.classList.add('mu-empty');
    this.#preview?.classList.add('mu-preview');

    this.#bindEvents();
    this.#showEmpty();
    this.#tryImportFromAttributes();
  }

  #tryImportFromAttributes() {
    const { muImportUrl, muImportName, muImportMime, muImportSize } = this.#zone.dataset;
    if (!muImportUrl || !muImportMime) return;

    this.import({
      url: muImportUrl,
      fileName: muImportName || muImportUrl.split('/').pop(),
      mimeType: muImportMime,
      size: parseInt(muImportSize, 10) || 0,
    });
  }

  #bindEvents() {
    this.#trigger?.addEventListener('click', () => this.#input?.click());

    this.#input?.addEventListener('change', () => {
      const file = this.#input.files?.[0];
      if (file) this.#handleFile(file);
    });

    this.#removeBtn?.addEventListener('click', () => this.clear());

    this.#zone.addEventListener('dragover', (e) => {
      if (this.#hasFile()) return;
      e.preventDefault();
      this.#zone.classList.add('mu--dragging');
    });

    ['dragleave', 'dragend'].forEach(ev =>
      this.#zone.addEventListener(ev, () => this.#zone.classList.remove('mu--dragging'))
    );

    this.#zone.addEventListener('drop', (e) => {
      this.#zone.classList.remove('mu--dragging');
      if (this.#hasFile()) return;
      e.preventDefault();
      const file = e.dataTransfer?.files?.[0];
      if (!file) return;

      const dt = new DataTransfer();
      dt.items.add(file);
      if (this.#input) this.#input.files = dt.files;

      this.#handleFile(file);
    });
  }

  #handleFile(file) {
    if (!this.#acceptPattern.test(file.type)) {
      this.#emit('mu:error', { reason: 'type', file });
      return;
    }

    if (file.size > this.#maxBytes) {
      this.#emit('mu:error', { reason: 'size', file, maxBytes: this.#maxBytes });
      return;
    }

    this.#currentFile = file;

    const base = this.#fileMeta(file);
    this.#emit('mu:file-selected', base);

    this.#renderPreview(file, base);
  }

  #fileMeta(file) {
    return {
      file,
      name: file.name,
      size: file.size,
      sizeFormatted: this.#formatBytes(file.size),
      mime: file.type,
      kind: this.#kindOf(file.type),
    };
  }

  #kindOf(mime) {
    for (const [kind, pattern] of Object.entries(MediaUploader.#KINDS)) {
      if (pattern.test(mime)) return kind;
    }
    return 'other';
  }

  #renderPreview(file, meta) {
    if (!this.#previewFrame) { this.#showPreview(); return; }

    this.#revokeActiveUrl();

    this.#previewFrame.innerHTML = '';
    this.#setInfo(`${meta.name} &nbsp;·&nbsp; ${meta.sizeFormatted}`);

    const url = URL.createObjectURL(file);
    this.#activeObjectUrl = url;

    if (meta.kind === 'image') {
      this.#renderImage(file, url, meta);
    } else if (meta.kind === 'video') {
      this.#renderVideo(file, url, meta);
    } else if (meta.kind === 'pdf') {
      this.#renderPdf(file, url, meta);
    } else if (meta.kind === 'doc') {
      this.#renderDoc(file, url, meta);
    } else {
      this.#renderFallback(file, url, meta);
    }

    this.#showPreview();
  }

  #renderImage(file, url, meta) {
    const img = document.createElement('img');
    img.src = url;
    img.className = 'mu-preview__img';
    img.alt = meta.name;
    img.onload = () => {
      if (this.#activeObjectUrl !== url) return;
      this.#revokeActiveUrl();
      const extra = { ...meta, width: img.naturalWidth, height: img.naturalHeight };
      this.#setInfo(
        `${meta.name} &nbsp;·&nbsp; ${meta.sizeFormatted} &nbsp;·&nbsp; ${img.naturalWidth}×${img.naturalHeight}px`
      );
      this.#emit('mu:preview-ready', extra);
    };
    this.#previewFrame.appendChild(img);
  }

  #renderVideo(file, url, meta) {
    if (this.#isMobile) {
      this.#renderOpenLink(url, meta, 'video');
      this.#emit('mu:preview-ready', meta);
      return;
    }

    const video = document.createElement('video');
    video.src = url;
    video.className = 'mu-preview__video';
    video.controls = true;
    video.muted = true;
    video.playsInline = true;
    video.onloadedmetadata = () => {
      if (this.#activeObjectUrl !== url) return;
      this.#revokeActiveUrl();
      const extra = {
        ...meta,
        width: video.videoWidth,
        height: video.videoHeight,
        duration: video.duration,
      };
      this.#setInfo(
        `${meta.name} &nbsp;·&nbsp; ${meta.sizeFormatted}` +
        ` &nbsp;·&nbsp; ${video.videoWidth}×${video.videoHeight}px` +
        ` &nbsp;·&nbsp; ${this.#formatDuration(video.duration)}`
      );
      this.#emit('mu:preview-ready', extra);
    };
    this.#previewFrame.appendChild(video);
  }

  #renderPdf(file, url, meta) {
    if (this.#isMobile) {
      this.#renderOpenLink(url, meta, 'PDF');
      this.#emit('mu:preview-ready', meta);
      return;
    }

    const iframe = document.createElement('iframe');
    iframe.src = url;
    iframe.className = 'mu-preview__pdf';
    iframe.title = meta.name;
    this.#previewFrame.appendChild(iframe);
    this.#emit('mu:preview-ready', meta);
  }

  #renderDoc(file, url, meta) {
    this.#renderOpenLink(url, meta, 'tài liệu');
    this.#emit('mu:preview-ready', meta);
  }

  #renderFallback(file, url, meta) {
    this.#renderOpenLink(url, meta, 'file');
    this.#emit('mu:preview-ready', meta);
  }

  #renderOpenLink(url, meta, label) {
    const wrap = document.createElement('div');
    wrap.className = 'mu-preview__open-link';
    wrap.innerHTML = `
      <i class="fa-solid fa-file-lines mu-preview__open-link-icon"></i>
      <p class="mu-preview__open-link-name">${meta.name}</p>
      <a href="${url}" target="_blank" rel="noopener noreferrer" class="btn mu-preview__open-link-btn" data-variant="outline" data-size="sm">
        <i class="fa-solid fa-arrow-up-right-from-square"></i> Mở ${label} trong tab mới
      </a>
    `;
    this.#previewFrame.appendChild(wrap);
  }

  // ─── State helpers ────────────────────────────────────────────────────────

  #hasFile() {
    return this.#zone.classList.contains('mu--has-file');
  }

  #showEmpty() {
    this.#empty?.removeAttribute('hidden');
    this.#preview?.setAttribute('hidden', '');
    this.#zone.classList.remove('mu--has-file');
  }

  #showPreview() {
    this.#empty?.setAttribute('hidden', '');
    this.#preview?.removeAttribute('hidden');
    this.#zone.classList.add('mu--has-file');
  }

  #setInfo(html) {
    if (this.#previewInfo) this.#previewInfo.innerHTML = html;
  }

  // ─── Public API ───────────────────────────────────────────────────────────

  /**
   * Import media khởi tạo từ một URL có sẵn (Ví dụ: khi Sửa/Edit bài viết)
   * @param {string} fileName — Tên file (e.g. photo.jpg)
   * @param {string} url — Đường dẫn file từ server (e.g. /uploads/2026/05/photo.jpg)
   * @param {string} mimeType — MIME Type tương ứng (e.g. image/jpeg)
   */
  import({
    fileName,
    url,
    mimeType,
    size
  }) {
    if (!this.#previewFrame) { this.#showPreview(); return; }

    // Dọn sạch vùng Preview và URL bộ nhớ đệm cũ (nếu có)
    this.#revokeActiveUrl();
    this.#previewFrame.innerHTML = '';

    // Tạo cấu trúc Meta giả lập vì file đã nằm trên server (chưa có dung lượng chính xác)
    const meta = {
      file: null,
      name: fileName,
      mime: mimeType,
      sizeFormatted: this.#formatBytes(size),
      kind: this.#kindOf(mimeType)
    };

    this.#setInfo(`${meta.name} &nbsp;·&nbsp; ${meta.sizeFormatted}`);

    // Truyền trực tiếp url của Server thay vì tạo ObjectURL từ local File
    if (meta.kind === 'image') {
      this.#renderImage(null, url, meta);
    } else if (meta.kind === 'video') {
      this.#renderVideo(null, url, meta);
    } else if (meta.kind === 'pdf') {
      this.#renderPdf(null, url, meta);
    } else if (meta.kind === 'doc') {
      this.#renderDoc(null, url, meta);
    } else {
      this.#renderFallback(null, url, meta);
    }

    this.#showPreview();
  }

  clear() {
    this.#revokeActiveUrl();
    this.#currentFile = null;
    if (this.#input) this.#input.value = '';
    if (this.#previewFrame) this.#previewFrame.innerHTML = '';
    this.#setInfo('');
    this.#showEmpty();
    this.#emit('mu:file-removed', {});
  }

  /** @returns {File|null} */
  get file() { return this.#currentFile; }

  // ─── Utilities ────────────────────────────────────────────────────────────

  #revokeActiveUrl() {
    if (this.#activeObjectUrl) {
      URL.revokeObjectURL(this.#activeObjectUrl);
      this.#activeObjectUrl = null;
    }
  }

  #emit(event, detail) {
    this.#zone.dispatchEvent(new CustomEvent(event, { bubbles: true, detail }));
  }

  #formatBytes(bytes) {
    if (bytes === 0) return '0 B';
    const units = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return `${(bytes / 1024 ** i).toFixed(i > 0 ? 1 : 0)} ${units[i]}`;
  }

  #formatDuration(seconds) {
    const m = Math.floor(seconds / 60);
    const s = Math.floor(seconds % 60);
    return `${m}:${s.toString().padStart(2, '0')}`;
  }
}