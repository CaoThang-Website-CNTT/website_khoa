import { EditorBlock } from './editor_block.js';
import { BlockSerializer } from '../block_serializer_v2.js';
import { RichTextParser } from '../rich_text_parser.js';

export const ImageSchema = {
  version: 1,
  icon: '<i class="fa-regular fa-image"></i>',
  type: 'blocks/image',
  title: 'Hình ảnh',
  group: 'media',
  groupLabel: 'Phương tiện',
  meta: {
    mediaId: { default: null },
    url: { default: '' },
    alt: { default: '' },
    caption: { default: [] },
    align: { default: 'center' },
    width: { default: '100%' },
  }
};

export class ImageBlock extends EditorBlock {

  /** @type {number|null} */
  #postId = null;
  /** @type {HTMLElement|null} */
  #captionEl = null;

  constructor(blockData, schema, bus) {
    super(blockData, schema, bus);

    this.bus?.subscribe('meta:updated', ({ key, value }) => {
      if (key === 'post_id' && value) this.#postId = value;
    });
  }

  render() {
    this.dom = document.createElement('figure');
    this.dom.className = `be-image be-image-align--${this.data.meta.align}`;
    this.dom.contentEditable = 'false';

    this.#renderCurrentState();
    return this.dom;
  }

  #renderCurrentState() {
    this.dom.innerHTML = '';
    this.#captionEl = null;
    this.data.meta.url ? this.#renderResolved() : this.#renderPlaceholder();
  }

  #renderPlaceholder() {
    this.dom.innerHTML = `
      <div class="be-image-placeholder">
        <div class="be-image-placeholder__icon">
          <i class="fa-regular fa-image"></i>
        </div>
        <div class="be-image-placeholder__actions">
          <button type="button" class="btn be-upload-btn" data-variant="primary" data-size="md" data-modal-trigger="#media-selector-modal">Tải lên</button>
          <span class="be-placeholder-divider">hoặc</span>
          <div class="be-image-url-input-group">
            <input type="text" class="be-image-external-url" placeholder="Dán URL hình ảnh...">
            <button type="button" class="btn be-apply-url-btn" data-variant="outline">Chèn</button>
          </div>
        </div>
      </div>
    `;

    const uploadBtn = this.dom.querySelector('.be-upload-btn');
    const urlInput = this.dom.querySelector('.be-image-external-url');
    const applyBtn = this.dom.querySelector('.be-apply-url-btn');

    uploadBtn.addEventListener('click', () => {
      const mediaModal = document.querySelector('#media-selector-modal');
      if (mediaModal) mediaModal.dataset.beMediaTarget = `block-image:${this.id}`;
    });

    const handleMediaSubmit = ({ detail }) => {
      const mediaModal = document.querySelector('#media-selector-modal');
      if (mediaModal?.dataset.beMediaTarget !== `block-image:${this.id}`) return;

      const { media, close } = detail;
      if (!media || !media.file_path) return;

      // Cập nhật thông tin ảnh từ thư viện vào meta của block
      this.data.meta.mediaId = media.id;
      this.data.meta.alt = media.alt_text || media.title || '';

      // Xử lý URL theo cấu trúc bạn yêu cầu
      const mediaBase = (window.PUBLIC_MEDIA_BASE || `${location.origin}/public/media`).replace(/\/$/, '');
      let filePath = media.file_path.replace(/\\/g, '/').replace(/^\//, '');
      if (filePath.startsWith('public/media/')) filePath = filePath.slice('public/media/'.length);
      if (filePath.startsWith('media/')) filePath = filePath.slice('media/'.length);
      this.data.meta.url = `${mediaBase}/${filePath}`;

      this.bus?.dispatch('block:updated', { block: this });
      this.#renderCurrentState();

      delete mediaModal.dataset.beMediaTarget;
      if (typeof close === 'function') close();

      document.removeEventListener("msm:submit", handleMediaSubmit);
    };

    document.addEventListener("msm:submit", handleMediaSubmit);

    const applyExternalUrl = () => {
      const url = urlInput.value.trim();
      if (!url) return;
      this.data.meta.url = url;
      this.bus?.dispatch('block:updated', { block: this });
      this.#renderCurrentState();
      document.removeEventListener("msm:submit", handleMediaSubmit);
    };

    applyBtn.addEventListener('click', applyExternalUrl);
    urlInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') { e.preventDefault(); applyExternalUrl(); }
    });
  }

  #renderResolved() {
    const wrapper = document.createElement('div');
    wrapper.className = 'be-image-wrapper';

    const img = document.createElement('img');
    img.src = this.data.meta.url;
    img.alt = this.data.meta.alt || '';
    img.loading = 'lazy';
    img.style.width = this.data.meta.width.includes('%') || this.data.meta.width.includes('px')
      ? this.data.meta.width
      : `${this.data.meta.width}px`;
    img.dataset.width = img.style.width;

    const caption = document.createElement('figcaption');
    caption.contentEditable = 'true';
    caption.className = 'be-editable be-image-caption';
    caption.spellcheck = false;
    caption.dataset.placeholder = 'Viết chú thích ảnh...';
    caption.dataset.beEditable = '';
    caption.innerHTML = BlockSerializer.toHTML({ data: { rich_text: this.data.meta.caption } });

    this.#captionEl = caption;

    wrapper.appendChild(img);
    this.dom.appendChild(wrapper);
    this.dom.appendChild(caption);

    this.#attachCaptionEvents(caption);
  }

  #attachCaptionEvents(caption) {
    caption.addEventListener('input', () => {
      this.bus?.dispatch('block:input', { blockId: this.id });
    });

    caption.addEventListener('blur', () => {
      this.bus?.dispatch('block:updated', { block: this });
    });

    caption.addEventListener('paste', (e) => this.#handleCaptionPaste(e));

    caption.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') { e.preventDefault(); caption.blur(); }
    });
  }

  #handleCaptionPaste(e) {
    e.preventDefault();

    const clipboard = (e.originalEvent || e).clipboardData;
    const text = clipboard?.getData('text/plain') ?? '';
    if (!text) return;

    this.#ensureCaptionSelection();
    this.paste(text.replace(/\s*\r?\n\s*/g, ' '));
    this.bus?.dispatch('block:input', { blockId: this.id });
    this.bus?.dispatch('block:updated', { block: this });
  }

  #ensureCaptionSelection() {
    if (!this.#captionEl) return;

    const sel = window.getSelection();
    if (sel?.rangeCount && this.#captionEl.contains(sel.getRangeAt(0).commonAncestorContainer)) {
      return;
    }

    this.#captionEl.focus();
    const range = document.createRange();
    range.selectNodeContents(this.#captionEl);
    range.collapse(false);
    sel?.removeAllRanges();
    sel?.addRange(range);
  }

  /**
   * Override: Image có caption là rich text và các field media khác.
   * @param {HTMLElement|null} _editableEl - không dùng, tự quản lý #captionEl
   * @returns {object}
   */
  serializeData(_editableEl) {
    const captionHtml = this.#captionEl?.innerHTML?.trim() ?? '';
    return {
      rich_text: [],
      meta: {
        ...this.data.meta,
        caption: captionHtml
          ? BlockSerializer.tokensToSegments(RichTextParser.parse(captionHtml))
          : [],
      },
    };
  }

  renderInspectorControls() {
    const wrap = document.createElement('div');
    wrap.className = 'field-group';

    wrap.innerHTML = `
      <div class="field">
        <label class="field__label">Văn bản thay thế (Alt Text)</label>
        <textarea class="field__input be-alt-input" rows="3"
                  placeholder="Mô tả hình ảnh cho SEO...">${this.esc(this.data.meta.alt)}</textarea>
      </div>
      <fieldset class="field__set">
        <legend class="field__label">Kích thước ảnh</legend>
        <div class="radio-group grid grid-cols-2 gap-2"
             data-radio-name="image_size"
             data-radio-default-value="${this.data.meta.width}">
          ${[25, 50, 75, 100].map(pct => `
            <label class="field__label">
              <div class="field" data-orientation="horizontal">
                <button id="size-${pct}" class="radio-group__item"
                        type="button" role="radio" value="${pct}%"></button>
                <div class="field__title">${pct}%</div>
              </div>
            </label>
          `).join('')}
        </div>
      </fieldset>
    `;

    const radioGroup = wrap.querySelector('.radio-group');
    RadioHandler.instance.register(radioGroup);

    radioGroup.addEventListener('radio:change', (e) => {
      this.data.meta.width = e.detail.value;
      this.bus?.dispatch('block:updated', { block: this });
    });

    const altInput = wrap.querySelector('.be-alt-input');
    altInput.addEventListener('input', () => { this.data.meta.alt = altInput.value; });
    altInput.addEventListener('blur', () => {
      this.bus?.dispatch('block:updated', { block: this });
    });

    return wrap;
  }
}
