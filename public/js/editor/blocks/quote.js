import { EditorBlock } from './editor_block.js';
import { BlockSerializer } from '../block_serializer.js';

export const QuoteSchema = {
  version: 1,
  icon: "<i class='fa-solid fa-quote-left'></i>",
  name: 'blocks/quote',
  title: 'Trích dẫn',
  group: 'paragraph',
  groupLabel: 'Văn Bản',
  attributes: {
    content: { default: '' },   // rich-text segment[] | plain string
    citation: { default: '' },   // plain string — tác giả không cần mark
  },
  supports: {
    typography: true,
  }
};

export class QuoteBlock extends EditorBlock {
  /**@type {HTMLElement} */
  textEl = null;
  /**@type {HTMLElement} */
  authorEl = null;

  render() {
    const wrap = document.createElement('blockquote');
    wrap.className = 'be-preview-quote be-editable-wrap';

    this.dom = wrap;

    const textEl = document.createElement('p');
    textEl.contentEditable = 'true';
    textEl.className = 'be-editable';
    textEl.dataset.placeholder = 'Nội dung trích dẫn...';
    textEl.dataset.beEditable = '';
    textEl.spellcheck = false;

    textEl.innerHTML = BlockSerializer.toHTML({ data: { content: this.data.content } });

    const authorEl = document.createElement('cite');
    authorEl.contentEditable = 'true';
    authorEl.className = 'be-editable be-editable-cite';
    authorEl.dataset.placeholder = '— Tác giả (không bắt buộc)';
    authorEl.spellcheck = false;
    authorEl.textContent = this.data.citation || '';

    this.textEl = textEl;
    this.authorEl = authorEl;

    wrap.appendChild(textEl);
    wrap.appendChild(authorEl);

    textEl.addEventListener('input', () => {
      this.data.content = textEl.innerHTML.trim();
    });

    authorEl.addEventListener('input', () => {
      this.data.citation = authorEl.textContent.trim();
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

  renderInspectorControls() {
    const wrap = document.createElement('div');
    wrap.className = "field-group";

    wrap.innerHTML = `
        <div class="be-settings-property-section">
          <span class="be-settings-property__label">Định dạng Quote</span>
        </div>
      `;
    return wrap;
  }

  // Override focus: ưu tiên focus vào textEl (content) ở start
  // hoặc authorEl ở end nếu đã có tác giả
  focus(bus, position = 'end') {
    if (!this.dom) return;

    let targetEl;
    if (position === 'end') {
      targetEl = this.authorEl.textContent.trim() !== ''
        ? this.authorEl
        : this.textEl;
    } else {
      targetEl = this.textEl;
    }

    if (targetEl) {
      targetEl.focus();

      const range = document.createRange();
      const selection = window.getSelection();

      range.selectNodeContents(targetEl);
      range.collapse(position === 'start');

      selection.removeAllRanges();
      selection.addRange(range);

      bus.dispatch('block:selected', { blockId: this.id });
    }
  }
}