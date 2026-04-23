import { EditorBlock } from './editor_block.js';

export const ImageSchema = {
  name: 'blocks/image',
  title: 'Hình ảnh',
  group: 'media',
  groupLabel: 'Phương tiện',
  icon: '<i class="fa-regular fa-image"></i>',
  attributes: {
    mediaId: { default: null },
    url: { default: '' },       // Đường dẫn ảnh thật sau khi upload
    alt: { default: '' },
    caption: { default: '' },   // Chú thích dưới ảnh
    align: { default: 'center' }, // Căn lề
    width: { default: '100%' }
  }
};

export class ImageBlock extends EditorBlock {
  /**
   * post_id hiện tại — chỉ có giá trị khi đang ở chế độ edit (post đã tồn tại).
   * Khi tạo mới: null — media upload sẽ là "orphan", được attach sau khi post được save.
   * @type {number|null}
   */
  #postId = null;

  constructor(blockData, schema, bus) {
    super(blockData, schema, bus);

    // Lắng nghe post_id từ metadata qua bus.
    // Khi edit: EditorCanvasMetadata dispatch 'meta:sync_request' → 'meta:updated' với post_id.
    // Khi tạo mới: event này không bao giờ có key 'post_id' nên #postId vẫn là null.
    if (this.bus) {
      this.bus.subscribe('meta:updated', ({ key, value }) => {
        if (key === 'post_id' && value) {
          this.#postId = value;
        }
      });
    }
  }

  render() {
    this.dom = document.createElement('figure');
    this.dom.className = `be-preview-image be-align-${this.data.align}`;
    this.dom.contentEditable = 'false';

    this.#renderCurrentState();

    return this.dom;
  }

  #renderCurrentState() {
    this.dom.innerHTML = '';
    this.dom.className = `be-preview-image be-align-${this.data.align}`;

    if (this.data.url) {
      // Có hình
      this.#renderResolved();
    } else {
      // Chưa có hình
      this.#renderPlaceholder();
    }
  }

  #renderPlaceholder() {
    this.dom.innerHTML = `
      <div class="be-image-placeholder">
        <div class="be-image-placeholder__icon">
          <i class="fa-regular fa-image"></i>
        </div>
        <div class="be-image-placeholder__actions">
          <button type="button" class="btn be-upload-btn" data-variant="primary" data-size="md">Tải lên</button>
          <input type="file" class="be-file-input" accept="image/*" hidden>

          <span class="be-placeholder-divider">hoặc</span>

          <div class="be-image-url-input-group">
            <input type="text" class="be-image-external-url" placeholder="Dán URL hình ảnh...">
            <button type="button" class="btn be-apply-url-btn" data-variant="outline">Chèn</button>
          </div>
        </div>
      </div>
    `;

    const uploadBtn = this.dom.querySelector('.be-upload-btn');
    const fileInput = this.dom.querySelector('.be-file-input');
    const urlInput = this.dom.querySelector('.be-image-external-url');
    const applyUrlBtn = this.dom.querySelector('.be-apply-url-btn');

    uploadBtn.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', async (e) => {
      const file = e.target.files[0];
      if (file) await this.#handleUpload(file);
    });

    const handleExternalUrl = () => {
      const url = urlInput.value.trim();
      if (url) {
        this.data.url = url;
        if (this.bus) this.bus.dispatch('block:updated', { block: this });
        this.#renderCurrentState();
      }
    };

    applyUrlBtn.addEventListener('click', handleExternalUrl);

    urlInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        handleExternalUrl();
      }
    });
  }

  async #handleUpload(file) {
    // Hiển thị preview mờ + spinner ngay lập tức — không cần chờ server
    const blobUrl = URL.createObjectURL(file);

    this.dom.innerHTML = `
      <div class="be-image-loading" style="position: relative;">
        <img src="${blobUrl}" style="opacity: 0.5; max-width: 100%; border-radius: var(--radius-md);">
        <div class="be-spinner" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
          <i class="fa-solid fa-circle-notch fa-spin fa-2x"></i>
        </div>
      </div>
    `;

    try {
      const formData = new FormData();
      formData.append('file', file);

      // alt_text: lấy từ data hiện tại nếu user đã nhập trước đó
      if (this.data.alt) {
        formData.append('alt_text', this.data.alt);
      }

      // post_id: chỉ gửi khi đang edit (post đã tồn tại trên DB).
      // Khi tạo mới: không gửi — API chấp nhận upload không có post_id,
      // media sẽ là "orphan" và được attach khi server xử lý payload lúc save.
      if (this.#postId) {
        formData.append('post_id', this.#postId);
      }

      const response = await fetch('http://localhost/website_khoa/api/v1/media', {
        method: 'POST',
        body: formData,
      });

      if (!response.ok) {
        const err = await response.json().catch(() => ({}));
        throw new Error(err.message || `HTTP ${response.status}`);
      }

      const result = await response.json();

      this.data.mediaId = result.data.id;
      this.data.url = `http://localhost/website_khoa/storage/${result.data.file_path}`;

      if (result.data.alt_text && !this.data.alt) {
        this.data.alt = result.data.alt_text;
      }

      if (this.bus) {
        this.bus.dispatch('block:updated', { block: this });
      }

      this.#renderCurrentState();

    } catch (error) {
      console.error('Lỗi upload ảnh:', error);
      alert('Không thể tải ảnh lên. Vui lòng thử lại.');
      this.#renderCurrentState();
    } finally {
      // Giải phóng blob URL khỏi memory dù thành công hay thất bại
      URL.revokeObjectURL(blobUrl);
    }
  }

  #renderResolved() {
    const imgWrapper = document.createElement('div');
    imgWrapper.className = 'be-image-content-wrapper';
    imgWrapper.style.display = 'inline-block';
    imgWrapper.style.position = 'relative';
    imgWrapper.style.maxWidth = '100%';

    const img = document.createElement('img');
    img.src = this.data.url;
    img.alt = this.data.alt || '';
    img.loading = 'lazy';
    img.className = 'be-image-element';

    img.style.width = this.data.width.includes('%') || this.data.width.includes('px')
      ? this.data.width
      : `${this.data.width}px`;

    // Render khu vực nhập caption
    const caption = document.createElement('figcaption');
    caption.contentEditable = 'true';
    caption.className = 'be-editable be-image-caption';
    caption.dataset.placeholder = 'Viết chú thích ảnh...';
    caption.textContent = this.data.caption || '';
    caption.spellcheck = false;

    imgWrapper.appendChild(img);
    this.dom.appendChild(imgWrapper);
    this.dom.appendChild(caption);

    // Xử lý sự kiện cho caption
    caption.addEventListener('input', () => {
      this.data.caption = caption.textContent.trim();
    });

    caption.addEventListener('blur', () => {
      if (this.bus) this.bus.dispatch('block:updated', { block: this });
    });

    caption.addEventListener('paste', (e) => {
      e.preventDefault();
      const text = (e.originalEvent || e).clipboardData.getData('text/plain');
      this.paste(text);
    });

    caption.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        caption.blur();
      }
    });
  }

  renderInspectorControls() {
    const wrap = document.createElement('div');

    wrap.innerHTML = `
      <div class="be-settings-property-section">
        <span class="be-settings-property__label">Căn lề (Align)</span>
        <div class="be-settings-level-group">
          <button type="button" class="btn be-align-btn ${this.data.align === 'left' ? 'active' : ''}" data-align="left" title="Căn trái"><i class="fa-solid fa-align-left"></i></button>
          <button type="button" class="btn be-align-btn ${this.data.align === 'center' ? 'active' : ''}" data-align="center" title="Căn giữa"><i class="fa-solid fa-align-center"></i></button>
          <button type="button" class="btn be-align-btn ${this.data.align === 'right' ? 'active' : ''}" data-align="right" title="Căn phải"><i class="fa-solid fa-align-right"></i></button>
          <button type="button" class="btn be-align-btn ${this.data.align === 'full' ? 'active' : ''}" data-align="full" title="Toàn màn hình"><i class="fa-solid fa-arrows-left-right"></i></button>
        </div>
      </div>

      <div class="be-settings-property-section">
        <span class="be-settings-property__label">Văn bản thay thế (Alt Text)</span>
        <textarea class="be-settings-textarea be-alt-input" rows="3" placeholder="Mô tả hình ảnh cho SEO...">${this.esc(this.data.alt)}</textarea>
      </div>

      <div class="be-settings-property-section">
        <span class="be-settings-property__label">Kích thước ảnh</span>
        <div class="be-settings-level-group">
          <button type="button" class="btn be-size-btn" data-size="25%">25%</button>
          <button type="button" class="btn be-size-btn" data-size="50%">50%</button>
          <button type="button" class="btn be-size-btn" data-size="75%">75%</button>
          <button type="button" class="btn be-size-btn" data-size="100%">100%</button>
        </div>
      </div>
    `;

    wrap.querySelectorAll('.be-align-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const newAlign = btn.dataset.align;
        this.data.align = newAlign;

        this.dom.className = `be-preview-image be-align-${newAlign}`;

        wrap.querySelectorAll('.be-align-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        if (this.bus) this.bus.dispatch('block:updated', { block: this });
      });
    });

    const altInput = wrap.querySelector('.be-alt-input');
    altInput.addEventListener('input', () => {
      this.data.alt = altInput.value;

      const imgNode = this.dom.querySelector('.be-image-element');
      if (imgNode) imgNode.alt = this.data.alt;
    });

    altInput.addEventListener('blur', () => {
      if (this.bus) this.bus.dispatch('block:updated', { block: this });
    });

    wrap.querySelectorAll('.be-size-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const newSize = btn.dataset.size;
        this.data.width = newSize;

        const imgNode = this.dom.querySelector('.be-image-element');
        if (imgNode) imgNode.style.width = newSize;

        if (this.bus) this.bus.dispatch('block:updated', { block: this });
      });
    });

    return wrap;
  }
}