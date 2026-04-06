/**
 * block_item.js
 * =============
 * Factory tạo ra DOM wrapper cho một block.
 * 
 * Mỗi BlockItem chứa:
 *   - Drag handle (dùng bởi dnd.js Sortable)
 *   - Label hiển thị block type
 *   - Form do BlockRegistry.createForm() tạo ra
 *   - Nút Xóa
 * 
 * BlockItem KHÔNG biết gì về dnd.js — việc tạo Sortable instance
 * là trách nhiệm của BlockList, vì Sortable cần manager reference.
 * 
 * Public API:
 *   item.el         — DOM node gốc (để append vào container)
 *   item.id         — block id duy nhất (dùng làm Sortable id)
 *   item.type       — block type string
 *   item.getData()  — delegate sang form.getData()
 *   item.focus()    — focus vào input đầu tiên của form
 *   item.destroy()  — cleanup (được gọi bởi BlockList khi xóa)
 */

/**
 * createBlockItem(type, initialData, { onRemove })
 * 
 * @param {string}   type         Block type (phải có trong BlockRegistry)
 * @param {object}   initialData  Data để hydrate form (edit mode), {} nếu tạo mới
 * @param {object}   options
 * @param {Function} options.onRemove  Callback khi user nhấn Xóa — nhận (id) làm arg
 */
function createBlockItem(type, initialData = {}, { onRemove } = {}) {
  const descriptor = BlockRegistry.get(type);
  if (!descriptor) {
    throw new Error(`[BlockItem] Block type '${type}' không tồn tại trong BlockRegistry.`);
  }

  // Mỗi block có id duy nhất — dùng làm Sortable id và data-block-id
  const id   = initialData._id ?? `blk_${Math.random().toString(36).slice(2, 8)}`;
  const form = descriptor.createForm(initialData);

  // ── Build DOM ──────────────────────────────────────────────────────────────
  const el = document.createElement('div');
  el.className      = 'block-item card shadow';
  el.dataset.blockId   = id;
  el.dataset.blockType = type;
  el.innerHTML = `
    <div class="block-item__header card__header">
      <div class="block-item__handle" title="Kéo để sắp xếp">
        <i class="fa-solid fa-grip-vertical"></i>
      </div>
      <span class="block-item__type-label">${descriptor.label}</span>
      <button
        type="button"
        class="btn block-item__remove-btn"
        data-variant="destructive"
        data-size="sm"
        title="Xóa block này"
      >
        <i class="fa-solid fa-trash"></i>
        Xóa
      </button>
    </div>
    <hr class="separator">
    <div class="block-item__body card__content"></div>
  `;

  // Mount form vào body
  el.querySelector('.block-item__body').appendChild(form.el);

  // ── Xóa handler ───────────────────────────────────────────────────────────
  el.querySelector('.block-item__remove-btn').addEventListener('click', () => {
    onRemove?.(id);
  });

  return {
    el,
    id,
    type,
    /** Lấy drag handle element — BlockList dùng để truyền vào Sortable({ handle }) */
    get handle() {
      return el.querySelector('.block-item__handle');
    },
    /** Delegate sang form.getData() */
    getData() {
      return form.getData();
    },
    /** Focus vào input đầu tiên (nếu form hỗ trợ) */
    focus() {
      form.focus?.();
    },
    /** Cleanup — hiện tại chỉ remove DOM, sau này có thể add event cleanup */
    destroy() {
      el.remove();
    },
  };
}
