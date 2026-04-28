import { EditorBlock } from './editor_block.js';
import { BlockSerializer } from '../block_serializer.js';

export const ImageSchema = {
  type: 'blocks/image',
  version: 1,
  title: 'Hình ảnh',
  group: 'media',
  groupLabel: 'Phương tiện',
  icon: '<i class="fa-regular fa-image"></i>',
  attributes: {
    mediaId: { default: null },
    url: { default: '' },
    alt: { default: '' },
    caption: { default: '' },  // rich-text segment[] | plain string
    align: { default: 'center' },
    width: { default: '100%' }
  }
};

export class ImageBlock extends EditorBlock {
  /**
   * post_id hiện tại — chỉ có giá trị khi đang edit (post đã tồn tại).
   * @type {number|null}
   */
  #postId = null;

  constructor(blockData, schema, bus) {
    super(blockData, schema, bus);

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
    this.dom.className = `be-image be-image-align--${this.data.align}`;
    this.dom.contentEditable = 'false';

    this.#renderCurrentState();

    return this.dom;
  }

  #renderCurrentState() {
    this.dom.innerHTML = '';

    if (this.data.url) {
      this.#renderResolved();
    } else {
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
      if (e.key === 'Enter') { e.preventDefault(); handleExternalUrl(); }
    });
  }

  async #handleUpload(file) {
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

      if (this.data.alt) formData.append('alt_text', this.data.alt);
      if (this.#postId) formData.append('post_id', this.#postId);

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

      if (this.bus) this.bus.dispatch('block:updated', { block: this });

      this.#renderCurrentState();

    } catch (error) {
      console.error('Lỗi upload ảnh:', error);
      alert('Không thể tải ảnh lên. Vui lòng thử lại.');
      this.#renderCurrentState();
    } finally {
      URL.revokeObjectURL(blobUrl);
    }
  }

  #renderResolved() {
    const imgWrapper = document.createElement('div');
    imgWrapper.className = "be-image-wrapper";

    const img = document.createElement('img');
    img.src = this.data.url;
    img.alt = this.data.alt || '';
    img.loading = 'lazy';
    img.style.width = this.data.width.includes('%') || this.data.width.includes('px')
      ? this.data.width
      : `${this.data.width}px`;

    const caption = document.createElement('figcaption');
    caption.contentEditable = 'true';
    caption.className = 'be-editable be-image-caption';
    caption.dataset.placeholder = 'Viết chú thích ảnh...';
    caption.dataset.beEditable = '';
    caption.spellcheck = false;
    caption.innerHTML = BlockSerializer.toHTML({ data: { content: this.data.caption } });

    imgWrapper.appendChild(img);
    this.dom.appendChild(imgWrapper);
    this.dom.appendChild(caption);

    caption.addEventListener('input', () => {
      this.data.caption = caption.innerHTML.trim();
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
      if (e.key === 'Enter') { e.preventDefault(); caption.blur(); }
    });
  }

  renderInspectorControls() {
    const wrap = document.createElement('div');
    wrap.className = "field-group";

    wrap.innerHTML = `
      <div class="field">
        <label class="field__label">Văn bản thay thế (Alt Text)</label>
        <textarea class="field__input be-alt-input" rows="3" placeholder="Mô tả hình ảnh cho SEO...">${this.esc(this.data.alt)}</textarea>
      </div>

      <fieldset class="field__set">
        <legend class="field__label">Kích thước ảnh</legend>
        <div class="radio-group grid grid-cols-2 gap-2" data-radio-name="image_size" data-radio-default-value="100%">
          ${[...Array.from({ length: 4 }, (_, i) => i + 1)].map(l => `
            <label class="field__label">
              <div class="field" data-orientation="horizontal">
                <button id="size-${l * 25}" class="radio-group__item" type="button" role="radio" value="${l * 25}%"></button>
                <div class="field__title">${l * 25}%</div>
              </div>
            </label>
          `).join('')}
        </div>
      </fieldset>
    `;

    const radioGroup = wrap.querySelector('.radio-group');
    RadioHandler.instance.register(radioGroup);

    radioGroup.addEventListener('radio:change', (e) => {
      this.data.width = e.detail.value;
      if (this.bus) this.bus.dispatch('block:updated', { block: this });
    });

    const altInput = wrap.querySelector('.be-alt-input');
    altInput.addEventListener('input', () => {
      this.data.alt = altInput.value;
      if (this.bus) this.bus.dispatch('block:updated', { block: this });
    });

    altInput.addEventListener('blur', () => {
      if (this.bus) this.bus.dispatch('block:updated', { block: this });
    });

    return wrap;
  }
}