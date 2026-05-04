import { EditorBlock } from './editor_block.js';
import { BlockSerializer } from '../block_serializer_v2.js';

export const ParagraphSchema = {
  version: 1,
  icon: "<i class='fa-solid fa-paragraph'></i>",
  type: 'blocks/paragraph',
  title: 'Đoạn văn',
  group: 'paragraph',
  groupLabel: 'Văn Bản',
  meta: {
    align: { default: 'left' }
  },
  supports: { typography: true },
};

export class ParagraphBlock extends EditorBlock {

  render() {
    const el = document.createElement('p');
    el.className = 'be-paragraph be-editable';
    el.contentEditable = 'true';
    el.spellcheck = false;
    el.dataset.placeholder = 'Nhập nội dung đoạn văn...';
    el.dataset.beEditable = '';

    el.innerHTML = BlockSerializer.toHTML({ data: this.data });

    this.dom = el;

    el.addEventListener('paste', (e) => {
      e.preventDefault();
      const text = (e.originalEvent || e).clipboardData.getData('text/plain');
      this.paste(this.esc(text));
    });

    // input: không lưu HTML string — chỉ sync khi serialize (serializeData đọc từ DOM)
    // Giữ lại để các listener khác (word count, dirty flag) có thể subscribe nếu cần
    el.addEventListener('input', () => {
      this.bus?.dispatch('block:input', { blockId: this.id });
    });

    return el;
  }

  renderInspectorControls() {
    const wrap = document.createElement('div');
    wrap.className = 'field-group';
    return wrap;
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