import { EditorBlock } from './editor_block.js';
import { BlockSerializer } from '../block_serializer_v2.js';
import { RichTextParser } from '../rich_text_parser.js';

export const QuoteSchema = {
  version: 1,
  icon: "<i class='fa-solid fa-quote-left'></i>",
  type: 'blocks/quote',
  title: 'Trích dẫn',
  group: 'paragraph',
  groupLabel: 'Văn Bản',
  meta: {
    citation: { default: '' },
  },
  supports: { typography: true },
};

export class QuoteBlock extends EditorBlock {

  /** @type {HTMLElement|null} */
  #textEl = null;
  /** @type {HTMLElement|null} */
  #authorEl = null;

  render() {
    const wrap = document.createElement('blockquote');
    wrap.className = 'be-quote be-editable-wrap';
    this.dom = wrap;

    const textEl = document.createElement('p');
    textEl.contentEditable = 'true';
    textEl.className = 'be-quote-content be-editable';
    textEl.spellcheck = false;
    textEl.dataset.placeholder = 'Nội dung trích dẫn...';
    textEl.dataset.beEditable = '';
    textEl.innerHTML = BlockSerializer.toHTML({ data: this.data });

    const authorEl = document.createElement('cite');
    authorEl.contentEditable = 'true';
    authorEl.className = 'be-quote-citation be-editable';
    authorEl.spellcheck = false;
    authorEl.dataset.placeholder = '— Tác giả (không bắt buộc)';
    authorEl.textContent = this.data.meta.citation || '';

    this.#textEl = textEl;
    this.#authorEl = authorEl;

    wrap.appendChild(textEl);
    wrap.appendChild(authorEl);

    textEl.addEventListener('input', () => {
      this.bus?.dispatch('block:input', { blockId: this.id });
    });

    authorEl.addEventListener('input', () => {
      // citation là plain text — sync ngay vì không qua RichTextParser
      this.data.meta.citation = authorEl.textContent.trim();
    });

    textEl.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') { e.preventDefault(); authorEl.focus(); }
    });

    authorEl.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') { e.preventDefault(); authorEl.blur(); }
    });

    textEl.addEventListener('paste', (e) => {
      e.preventDefault();
      const text = (e.originalEvent || e).clipboardData.getData('text/plain');
      this.paste(this.esc(text));
    });

    authorEl.addEventListener('paste', (e) => {
      e.preventDefault();
      const text = (e.originalEvent || e).clipboardData.getData('text/plain');
      this.paste(this.esc(text));
    });

    return wrap;
  }

  /**
   * Override: Quote có hai field cần serialize riêng.
   * content đọc từ #textEl (rich text), citation đọc từ this.data (plain text đã sync).
   *
   * @param {HTMLElement|null} _editableEl — không dùng vì Quote tự quản lý DOM ref
   * @returns {object}
   */
  serializeData(_editableEl) {
    const html = this.#textEl?.innerHTML?.trim() ?? '';
    return {
      rich_text: html
        ? BlockSerializer.tokensToSegments(RichTextParser.parse(html))
        : [],
      meta: {
        citation: this.data.meta.citation ?? '',
      },
    };
  }

  /**
   * Override: cộng cả content lẫn citation.
   * @returns {{ seconds: number }}
   */
  renderInspectorControls() {
    const wrap = document.createElement('div');
    wrap.className = 'field-group';
    return wrap;
  }

  focus(bus, position = 'end') {
    if (!this.dom) return;

    const targetEl = position === 'end' && this.#authorEl?.textContent.trim()
      ? this.#authorEl
      : this.#textEl;

    if (!targetEl) return;

    targetEl.focus();

    const range = document.createRange();
    const sel = window.getSelection();
    range.selectNodeContents(targetEl);
    range.collapse(position === 'start');
    sel.removeAllRanges();
    sel.addRange(range);

    this.bus.dispatch('block:selected', { blockId: this.id });
  }
}