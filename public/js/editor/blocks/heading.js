import { EditorBlock } from './editor_block.js';
import { BlockSerializer } from '../block_serializer_v2.js';

export const HeadingSchema = {
  version: 1,
  icon: "<i class='fa-solid fa-heading'></i>",
  type: 'blocks/heading',
  title: 'Heading',
  group: 'paragraph',
  groupLabel: 'Văn Bản',
  meta: {
    level: { default: 2 },
    align: { default: 'left' },
  },
  supports: { typography: true },
};

export class HeadingBlock extends EditorBlock {

  render() {
    const el = this.#createElement(this.data.meta.level);
    this.dom = el;
    this.#attachListeners(el);
    return el;
  }

  /**
   * Tạo <hN> với đầy đủ attributes.
   * @param {number} level
   * @returns {HTMLElement}
   */
  #createElement(level) {
    const el = document.createElement(`h${level}`);
    el.className = 'be-heading be-editable';
    el.contentEditable = 'true';
    el.spellcheck = false;
    el.dataset.placeholder = 'Nhập tiêu đề...';
    el.dataset.beEditable = '';

    el.innerHTML = BlockSerializer.toHTML({ data: this.data });

    return el;
  }

  /**
   * Gắn event listeners vào element.
   * Tách riêng để #swapLevel có thể re-attach sau khi thay tag.
   * @param {HTMLElement} el
   */
  #attachListeners(el) {
    el.addEventListener('paste', (e) => {
      e.preventDefault();
      const text = (e.originalEvent || e).clipboardData.getData('text/plain');
      this.paste(this.esc(text));
    });

    el.addEventListener('keydown', (e) => {
      // Heading là single-line — Enter không xuống dòng
      if (e.key === 'Enter') { e.preventDefault(); el.blur(); }
    });

    el.addEventListener('input', () => {
      this.bus?.dispatch('block:input', { blockId: this.id });
    });
  }

  renderInspectorControls() {
    const wrap = document.createElement('div');
    wrap.className = 'field-group';

    wrap.innerHTML = `
      <div class="field">
        <label class="field__label">Cấp độ tiêu đề</label>
        <div class="radio-group grid gap-2"
             data-radio-name="heading_level"
             data-radio-default-value="${this.data.meta.level - 1}">
          ${[1, 2, 3].map(display => `
            <label class="field__label">
              <div class="field" data-orientation="horizontal">
                <button id="level-${display}"
                        class="radio-group__item"
                        type="button" role="radio"
                        value="${display}">
                </button>
                <div class="field__title">H${display}</div>
              </div>
            </label>
          `).join('')}
        </div>
      </div>
    `;

    const radioGroup = wrap.querySelector('.radio-group');
    RadioHandler.instance.register(radioGroup);

    radioGroup.addEventListener('radio:change', (e) => {
      const newLevel = parseInt(e.detail.value) + 1; // display H1→2, H2→3, H3→4
      this.data.meta.level = newLevel;
      this.#swapLevel(newLevel);
    });

    return wrap;
  }

  /**
   * Swap tag <hN> in-place mà không destroy DOM node hiện tại.
   * Giữ nguyên innerHTML và Range đang held bởi InlineToolbar.
   *
   * @param {number} newLevel
   */
  #swapLevel(newLevel) {
    if (!this.dom) return;

    const newEl = document.createElement(`h${newLevel}`);
    newEl.className = this.dom.className;
    newEl.contentEditable = this.dom.contentEditable;
    newEl.spellcheck = false;
    newEl.dataset.placeholder = this.dom.dataset.placeholder;
    newEl.dataset.beEditable = '';
    newEl.innerHTML = this.dom.innerHTML; // giữ nguyên rich content

    this.dom.replaceWith(newEl);
    this.dom = newEl;

    this.#attachListeners(newEl);
  }

  focus(bus, position = 'end') {
    if (!this.dom) return;

    this.dom.focus();

    const range = document.createRange();
    const sel = window.getSelection();
    range.selectNodeContents(this.dom);
    range.collapse(position === 'start');
    sel.removeAllRanges();
    sel.addRange(range);

    this.bus.dispatch('block:selected', { blockId: this.id });
  }
}