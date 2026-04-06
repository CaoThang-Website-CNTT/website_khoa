/**
 * block_list.js
 * =============
 * Quản lý danh sách block trong editor.
 * 
 * Trách nhiệm:
 *   1. Thêm / xóa BlockItem vào DOM container
 *   2. Tạo Sortable instance (dnd.js) cho mỗi block mới
 *   3. Sync lại index sau mỗi thao tác drag/add/remove
 *   4. Duy trì internal Map<id, { item, sortable }> để tra cứu nhanh
 * 
 * BlockList là layer duy nhất biết về DragDropManager.
 * Các module khác (Toolbar, Serializer) chỉ tương tác qua public API.
 * 
 * Public API:
 *   list.add(type, initialData?)  — thêm block mới
 *   list.remove(id)               — xóa block theo id
 *   list.getOrderedItems()        — trả về mảng item đúng thứ tự DOM
 *   list.isEmpty()                — kiểm tra rỗng
 *   list.destroy()                — dọn dẹp toàn bộ
 */

const DND_GROUP = 'editor-blocks'; // Tên group cho dnd.js

/**
 * createBlockList(containerEl, dndManager)
 * 
 * @param {HTMLElement}      containerEl  Phần tử chứa danh sách block
 * @param {DragDropManager}  dndManager   Instance từ dnd.js (đã khởi tạo)
 */
function createBlockList(containerEl, dndManager) {
  /** Map<blockId, { item: BlockItem, sortable: Sortable }> */
  const _registry = new Map();

  let _sortableIndex = 0; // tăng dần mỗi lần add, dùng làm initial index

  // ── Empty state ────────────────────────────────────────────────────────────
  const emptyHint = containerEl.querySelector('.block-list__empty');

  function _updateEmptyState() {
    if (!emptyHint) return;
    emptyHint.style.display = _registry.size === 0 ? 'flex' : 'none';
  }

  // ── Reindex ────────────────────────────────────────────────────────────────
  /**
   * Sau mỗi drag/add/remove, đồng bộ lại Sortable.index
   * theo thứ tự DOM thực tế (không phải thứ tự thêm vào).
   * Tương tự reindexSlides() trong carousel create.php.
   */
  function _reindex() {
    const ordered = getOrderedItems();
    ordered.forEach((item, i) => {
      const entry = _registry.get(item.id);
      if (entry?.sortable) {
        entry.sortable.index        = i;
        entry.sortable.initialIndex = i;
      }
    });
    dndManager.reindexGroup(DND_GROUP);
    _updateEmptyState();
  }

  // ── Lắng nghe dragend từ dnd.js ───────────────────────────────────────────
  dndManager.monitor.addEventListener('dragend', (e) => {
    if (!e.canceled && isSortable(e.operation.source)) {
      _reindex();
    }
  });

  // ── Add block ──────────────────────────────────────────────────────────────
  /**
   * Thêm một block mới vào cuối danh sách.
   * 
   * @param {string} type         Block type (vd: 'heading')
   * @param {object} initialData  Data để hydrate form trong edit mode
   * @returns {string}            id của block vừa tạo
   */
  function add(type, initialData = {}) {
    const item = createBlockItem(type, initialData, {
      onRemove: (id) => remove(id),
    });

    // Tạo Sortable instance — mỗi block là một sortable node
    const sortable = new Sortable(
      {
        id:      item.id,
        element: item.el,
        handle:  item.handle,  // chỉ kéo được từ grip handle
        group:   DND_GROUP,
        index:   _sortableIndex++,
      },
      dndManager
    );

    _registry.set(item.id, { item, sortable });
    containerEl.appendChild(item.el);

    _reindex();
    item.focus(); // focus input đầu tiên sau khi thêm

    return item.id;
  }

  // ── Remove block ───────────────────────────────────────────────────────────
  /**
   * Xóa block theo id, dọn dẹp Sortable và DOM.
   * @param {string} id
   */
  function remove(id) {
    const entry = _registry.get(id);
    if (!entry) return;

    entry.sortable.destroy(); // unregister khỏi dnd.js registry
    entry.item.destroy();     // remove DOM node

    _registry.delete(id);
    _reindex();
  }

  // ── Query ──────────────────────────────────────────────────────────────────
  /**
   * Trả về mảng BlockItem theo đúng thứ tự trong DOM (sau khi drag).
   * Đây là nguồn dữ liệu cho BlockSerializer.
   * 
   * Không dùng Map iteration order vì drag thay đổi thứ tự DOM
   * nhưng không thay đổi thứ tự insert vào Map.
   */
  function getOrderedItems() {
    const domOrder = [...containerEl.querySelectorAll('[data-block-id]')];
    return domOrder
      .map(el => {
        const id    = el.dataset.blockId;
        const entry = _registry.get(id);
        return entry?.item ?? null;
      })
      .filter(Boolean);
  }

  function isEmpty() {
    return _registry.size === 0;
  }

  function destroy() {
    for (const { sortable, item } of _registry.values()) {
      sortable.destroy();
      item.destroy();
    }
    _registry.clear();
  }

  // Khởi tạo empty state
  _updateEmptyState();

  return { add, remove, getOrderedItems, isEmpty, destroy };
}
