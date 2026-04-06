/**
 * block_registry.js
 * =================
 * Single source of truth cho tất cả block type ở phía client.
 * 
 * Mỗi entry map một `type` string sang một descriptor object gồm:
 *   - label:      tên hiển thị trong BlockToolbar
 *   - icon:       ký tự / HTML nhỏ hiển thị trên nút
 *   - createForm: factory fn → trả về { el: HTMLElement, getData: () => object }
 * 
 * Quy tắc thêm block mới:
 *   1. Khai báo entry ở đây
 *   2. Implement createForm tương ứng
 *   3. Thêm entry vào BlockSchema.php (server-side)
 *   4. Tạo templates/blocks/{type}/v1.php
 * 
 * Không cần đụng vào BlockEditor, BlockList, hay BlockSerializer.
 */

const BlockRegistry = (() => {

  // ── Heading Form ────────────────────────────────────────────────────────────
  /**
   * createHeadingForm()
   * 
   * Trả về:
   *   el:      DOM node của form (sẽ được BlockItem wrap lại)
   *   getData: fn trả về data object khớp với BlockSchema heading v1
   */
  function createHeadingForm(initialData = {}) {
    const el = document.createElement('div');
    el.className = 'block-form block-form--heading';
    el.innerHTML = `
      <div class="block-form__row block-form__row--split">
        <div class="field" style="flex: 0 0 120px;">
          <label class="field__label">Cấp độ</label>
          <select class="field__input js-heading-level" name="">
            <option value="2">H2 — Mục chính</option>
            <option value="3">H3 — Mục phụ</option>
            <option value="4">H4 — Mục nhỏ</option>
          </select>
        </div>
        <div class="field" style="flex: 1;">
          <label class="field__label">Nội dung tiêu đề <span class="field__required">*</span></label>
          <input
            class="field__input js-heading-text"
            type="text"
            placeholder="Nhập tiêu đề..."
            maxlength="500"
          >
        </div>
      </div>
      <div class="block-form__preview js-heading-preview" aria-hidden="true"></div>
    `;

    const levelEl   = el.querySelector('.js-heading-level');
    const textEl    = el.querySelector('.js-heading-text');
    const previewEl = el.querySelector('.js-heading-preview');

    // Hydrate từ initialData (dùng cho edit mode)
    if (initialData.level) levelEl.value = String(initialData.level);
    if (initialData.text)  textEl.value  = initialData.text;

    // Live preview — render thẻ heading thật để người dùng thấy font size
    function updatePreview() {
      const level = levelEl.value;
      const text  = textEl.value.trim() || '(Chưa có nội dung)';
      previewEl.innerHTML = `<h${level} class="post-block post-block--heading post-block--h${level}" style="margin:0.5rem 0 0;">${text}</h${level}>`;
    }

    levelEl.addEventListener('change', updatePreview);
    textEl.addEventListener('input', updatePreview);
    updatePreview(); // render ngay khi tạo

    return {
      el,
      /**
       * getData() — được gọi bởi BlockSerializer khi submit form.
       * Trả về object khớp với schema heading v1 bên PHP.
       */
      getData() {
        return {
          level: parseInt(levelEl.value, 10),
          text:  textEl.value.trim(),
        };
      },
      /** focus() — được gọi sau khi block được thêm vào DOM */
      focus() {
        textEl.focus();
      },
    };
  }

  // ── Registry map ────────────────────────────────────────────────────────────
  const _registry = {
    heading: {
      label:      'Tiêu đề',
      icon:       'H',
      version:    1,
      createForm: createHeadingForm,
    },

    // Thêm block type mới vào đây:
    // paragraph: { label: 'Đoạn văn',  icon: '¶', version: 1, createForm: createParagraphForm },
    // image:     { label: 'Hình ảnh',  icon: '⬛', version: 1, createForm: createImageForm     },
    // quote:     { label: 'Trích dẫn', icon: '"', version: 1, createForm: createQuoteForm     },
    // list:      { label: 'Danh sách', icon: '☰', version: 1, createForm: createListForm      },
  };

  return {
    /** Trả về toàn bộ map để BlockToolbar render menu */
    getAll() {
      return _registry;
    },

    /** Lấy descriptor của một type cụ thể */
    get(type) {
      return _registry[type] ?? null;
    },

    /** Kiểm tra type có tồn tại không */
    has(type) {
      return type in _registry;
    },
  };
})();
