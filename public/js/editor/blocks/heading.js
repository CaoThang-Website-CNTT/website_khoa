import { EditorBlock } from './editor_block.js';

export const HeadingSchema = {
  version: 1,
  icon: "<i class='fa-solid fa-heading'></i>",
  name: 'blocks/heading',
  title: 'Heading',
  group: 'paragraph',
  groupLabel: 'Văn Bản',
  attributes: {
    content: { default: '' },
    level: { default: 2 }
  },
  supports: {
    typography: true
  }
};

export class HeadingBlock extends EditorBlock {
  render() {
    const l = this.data.level || 2;
    const el = document.createElement('h' + l);
    el.className = `be-preview-h${l} be-editable`;
    el.contentEditable = 'true';
    el.dataset.placeholder = 'Nhập tiêu đề...';
    el.textContent = this.data.content || '';
    el.spellcheck = false;

    this.dom = el;

    /* Ngăn paste kéo theo HTML format */
    el.addEventListener('paste', (e) => {
      e.preventDefault();
      const text = (e.originalEvent || e).clipboardData.getData('text/plain');

      this.paste(this.esc(text));
    });

    /* Ngăn Enter xuống dòng (heading là single-line) */
    el.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') { e.preventDefault(); el.blur(); }
    });

    el.addEventListener('input', () => {
      this.data.content = el.textContent.trim();
    });

    return el;
  }

  renderInspectorControls(data, { onUpdate }) {
    const wrap = document.createElement('div');
    wrap.className = "field-group";

    // Tạo mảng 3 element tượng trưng cho 3 cấp độ tiêu đề H1, H2, H3
    // Thủ thuật là đơn giản hóa về mặt UI là chỉ hiển thị cấp độ H1, H2, H3
    // Nhưng đằng sau sẽ là từ H2 trở đi do H1 là tiêu đề bài viết
    // Giúp người dùng biết được rằng block Heading có BAO NHIÊU CẤP ĐỘ CÓ THỂ CHỌN
    // hơn là biết CÓ BAO NHIÊU THẺ <h> CÓ THỂ CHỌN
    wrap.innerHTML = `
        <div class="be-settings-property-section">
          <span class="be-settings-property__label">Cấp độ tiêu đề</span>
          <div class="be-settings-level-group">
            ${[...Array.from({ length: 3 }, (_, i) => i + 1)].map(l => `
              <button type="button" class="btn be-heading-level-btn ${data?.level === l ? ' active' : ''}" data-size="lg" data-variant="outline" data-level="${l}">H${l}</button>
            `).join('')}
          </div>
        </div>
      `;

    wrap.querySelectorAll('.be-heading-level-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        onUpdate({ level: parseInt(btn.dataset.level) });
      });
    });

    return wrap;
  }
}