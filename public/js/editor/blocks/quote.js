import { EditorBlock } from './editor_block.js';

export const QuoteSchema = {
  version: 1,
  icon: "<i class='fa-solid fa-quote-left'></i>",
  name: 'blocks/quote',
  title: 'Trích dẫn',
  group: 'paragraph',
  groupLabel: 'Văn Bản',
  attributes: {
    text: { default: '' },
    author: { default: '' },
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

  render(data) {
    /* Quote có 2 field: text + author — dùng 2 contenteditable riêng */
    const wrap = document.createElement('blockquote');
    wrap.className = 'be-preview-quote be-editable-wrap';

    this.dom = wrap;

    const textEl = document.createElement('p');
    textEl.contentEditable = 'true';
    textEl.className = 'be-editable';
    textEl.dataset.placeholder = 'Nội dung trích dẫn...';
    textEl.textContent = data?.text || '';
    textEl.spellcheck = false;

    const authorEl = document.createElement('cite');
    authorEl.contentEditable = 'true';
    authorEl.className = 'be-editable be-editable-cite';
    authorEl.dataset.placeholder = '— Tác giả (không bắt buộc)';
    authorEl.textContent = data?.author || '';
    authorEl.spellcheck = false;

    // Lưu lại tham chiếu
    this.textEl = textEl;
    this.authorEl = authorEl;

    wrap.appendChild(textEl);
    wrap.appendChild(authorEl);

    const emit = () => onUpdate({
      text: textEl.textContent,
      author: authorEl.textContent,
    });

    textEl.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); authorEl.focus(); } });
    textEl.addEventListener('input', emit);
    authorEl.addEventListener('input', emit);
    authorEl.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); authorEl.blur(); } });

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
    wrap.innerHTML = `
        <div class="be-settings-property-section">
          <span class="be-settings-property__label">Định dạng Quote</span>

        </div>
      `;
    return wrap;
  }

  focus(bus, position = 'end') {
    if (!this.dom) return;

    let targetEl;
    if (position === 'end') {
      targetEl = this.authorEl.textContent.trim() !== '' ? this.authorEl : this.textEl;
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