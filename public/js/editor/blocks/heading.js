import { EditorBlock } from './editor_block.js';
import { BlockSerializer } from '../block_serializer.js';

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
    el.className = `be-heading be-editable`;
    el.contentEditable = 'true';
    el.dataset.placeholder = 'Nhập tiêu đề...';
    el.dataset.beEditable = '';
    el.innerHTML = BlockSerializer.toHTML({ data: { content: this.data.content } });
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
      this.data.content = el.innerHTML.trim();
    });

    return el;
  }

  renderInspectorControls() {
    const wrap = document.createElement('div');
    wrap.className = "field-group";

    // Tạo mảng 3 element tượng trưng cho 3 cấp độ tiêu đề H1, H2, H3
    // Trick lỏ về mặt UI là chỉ hiển thị cấp độ H1, H2, H3
    // Nhưng đằng sau sẽ là từ H2 trở đi do H1 sẽ là tiêu đề bài viết và chỉ tồn tại 1 thẻ H1 trong 1 bài viết
    // Mục đính giúp người dùng biết được rằng block Heading có BAO NHIÊU CẤP ĐỘ CÓ THỂ CHỌN
    // hơn là biết CÓ BAO NHIÊU THẺ <h> CÓ THỂ CHỌN
    wrap.innerHTML = `
        <div class="field">
          <label class="field__label">Cấp độ tiêu đề</label>
          <div class="radio-group grid gap-2" data-radio-name="heading_level" data-radio-default-value="1">
            ${[...Array.from({ length: 3 }, (_, i) => i + 1)].map(l => `
              <label class="field__label">
                <div class="field" data-orientation="horizontal">
                  <button id="${l}" class="radio-group__item" type="button" role="radio" value="${l}"></button>
                  <div class="field__title">H${l}</div>
                </div>
              </label>
            `).join('')}
          </div>
        </div>
      `;

    const radioGroup = wrap.querySelector('.radio-group');
    RadioHandler.instance.register(radioGroup);

    radioGroup.addEventListener('radio:change', (e) => {
      this.data.level = parseInt(e.detail.value) + 1;
      if (this.bus) this.bus.dispatch('block:updated', { block: this });
    });

    return wrap;
  }
}