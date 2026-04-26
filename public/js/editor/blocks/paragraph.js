import { EditorBlock } from './editor_block.js';
import { BlockSerializer } from '../block_serializer.js';

export const ParagraphSchema = {
  version: 1,
  icon: "<i class='fa-solid fa-paragraph'></i>",
  name: 'blocks/paragraph',
  title: 'Đoạn văn',
  group: 'paragraph',
  groupLabel: 'Văn Bản',
  attributes: {
    content: { default: '' },
    align: { default: 'left' }
  },
  supports: {
    typography: true,
  }
};

export class ParagraphBlock extends EditorBlock {
  render() {
    const el = document.createElement('p');
    el.className = 'be-preview-p be-editable';
    el.contentEditable = 'true';
    el.dataset.placeholder = 'Nhập nội dung đoạn văn...';
    el.dataset.beEditable = '';
    el.innerHTML = BlockSerializer.toHTML({ data: { content: this.data.content } });
    el.spellcheck = false;

    this.dom = el;

    el.addEventListener('paste', (e) => {
      e.preventDefault();
      const text = (e.originalEvent || e).clipboardData.getData('text/plain');

      this.paste(this.esc(text));
    });

    el.addEventListener('input', () => {
      this.data.content = el.innerHTML.trim();
    });

    return el;
  }

  renderInspectorControls() {
    const wrap = document.createElement('div');
    wrap.className = "field-group";

    wrap.innerHTML = `
        <div class="be-settings-property-section">
          <span class="be-settings-property__label">Định dạng Paragraph</span>

        </div>
      `;
    return wrap;
  }
}