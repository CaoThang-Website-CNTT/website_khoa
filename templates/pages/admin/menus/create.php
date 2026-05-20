<?php
$errors = request()->session()->getErrors() ?? [];
$old_input = request()->session()->getOldInputs() ?? [];
?>

<script>
  window.__errors__ = <?= json_encode($errors) ?>;
  window.__old__ = <?= json_encode($old_input) ?>;
</script>

<?php if ($flash = request()->session()->getFlash("notification")): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      toast.<?= ($flash['type']) ?>(
        '<?= $flash['title'] ?>',
        '<?= $flash['desc'] ?>'
      );
    });
  </script>
<?php endif; ?>

<!-- ========== title-wrapper start ========== -->
<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div class="col-6 col-md-6">
      <h2 class="title text-2xl font-semibold">Thêm nhóm menu mới</h2>
      <p>Điền thông tin nhóm menu và các mục bên dưới</p>
    </div>
    <div class="flex gap-2">
      <a href="<?= request()->previous(fallback: 'admin/menus') ?>" data-variant="outline" data-size="lg" class="btn">
        <i class="fa-solid fa-chevron-left"></i>
        Quay lại
      </a>
      <button data-modal-trigger="#confirm-modal" type="button" data-variant="primary" data-size="lg" class="btn">
        <i class="fa-solid fa-floppy-disk"></i>
        Thêm
      </button>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->

<form id="menu-add-form" action="<?= url('admin/menus') ?>" method="POST">
  <?= csrf_field() ?>

  <!-- Hidden inputs for menu items will be dynamically injected here -->
  <div id="menu-items-inputs-container"></div>

  <div class="detail-layout">
    <!-- LEFT - MENU ITEM TREE -->
    <div class="detail-layout__main flex-1">
      <div class="card shadow">
        <fieldset class="field__set">
          <div class="card__header">
            <legend class="card__title field__legend">Các mục trong menu</legend>
            <p class="card__description field__description">Kéo thả các mục để sắp xếp thứ tự và cấp độ cha-con.</p>
            <button class="btn card__action" type="button" id="add-item-btn" data-variant="outline" data-size="lg">
              <i class="fa-solid fa-plus"></i>
              Thêm item
            </button>
          </div>
          
          <hr class="separator" />

          <div class="card__content">
            <div id="menu-items-root" class="space-y-2" data-parent-id="null">
              <!-- Empty state visual -->
              <div class="empty" id="items-empty-hint">
                <div class="empty__header">
                  <div class="empty__media">
                    <i class="fa-solid fa-link"></i>
                  </div>
                  <div class="empty__title">Menu trống</div>
                  <div class="empty__description">
                    Chưa có mục nào. Thêm mục đầu tiên bên dưới.
                  </div>
                </div>
              </div>
            </div>
          </div>
        </fieldset>
      </div>
    </div>

    <!-- RIGHT - MENU INFO -->
    <div class="detail-layout__sidebar">
      <div class="card shadow">
        <div class="card__header">
          <legend class="card__title field__legend">Thông tin nhóm menu</legend>
          <p class="field__description">Những trường có dấu * là bắt buộc.</p>
        </div>

        <hr class="separator" />

        <div class="card__content">
          <div class="field-group">
            <div class="field" data-field-required data-field-max="60">
              <label class="field__label" for="key">Key (định danh)</label>
              <input id="key" class="field__input" type="text" name="key" 
                placeholder="VD: header_menu" value="<?= htmlspecialchars($old_input['key'] ?? '') ?>">
              <p class="field__description">Chỉ dùng chữ thường, số và dấu gạch dưới. Duy nhất trong hệ thống.</p>
            </div>

            <div class="field" data-field-required data-field-max="100">
              <label class="field__label" for="label">Tên hiển thị</label>
              <input id="label" class="field__input" type="text" name="label" 
                placeholder="VD: Menu Chính" value="<?= htmlspecialchars($old_input['label'] ?? '') ?>">
            </div>

            <div class="field" data-field-max="255">
              <label class="field__label" for="description">Mô tả</label>
              <textarea id="description" class="field__input" name="description"
                placeholder="Mô tả ngắn về nhóm menu này"><?= htmlspecialchars($old_input['description'] ?? '') ?></textarea>
            </div>

            <div class="field">
              <label class="field__label" for="sort_order">Thứ tự hiển thị</label>
              <input id="sort_order" class="field__input" type="number" name="sort_order" 
                placeholder="0" min="0" value="<?= htmlspecialchars($old_input['sort_order'] ?? '0') ?>">
              <p class="field__description">Số nhỏ hơn sẽ hiển thị trước.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>

<!-- ── Confirm Menu Create Modal ── -->
<div class="modal" id="confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Xác nhận tạo Menu</h2>
    <p class="modal__description">Bạn có chắc chắn muốn lưu nhóm Menu này cùng toàn bộ cấu trúc hiện tại?</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
    <button id="confirm-modal-btn" data-variant="primary" data-size="lg" class="btn" type="button">Xác nhận</button>
  </div>
  <button class="modal__close" type="button" data-modal-close>
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>

<!-- ── Item Edit / Create Modal ── -->
<div class="modal detail-modal" id="item-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title" id="item-modal-title">Thêm mục mới</h2>
    <p class="modal__description" id="item-modal-description">Điền thông tin mục menu bên dưới.</p>
  </div>
  
  <form id="item-form" onsubmit="event.preventDefault();">
    <div class="detail-modal__form space-y-4">
      <div class="field-group">
        <div class="grid grid-cols-2 gap-4">
          <!-- label -->
          <div class="field" data-field-required>
            <label class="field__label" for="item-label">Tên hiển thị (Nhãn)</label>
            <input id="item-label" class="field__input" type="text" name="label" required placeholder="VD: Trang chủ">
          </div>
  
          <!-- url -->
          <div class="field" data-field-required>
            <label class="field__label" for="item-url">Đường dẫn (URL)</label>
            <input id="item-url" class="field__input" type="text" name="url" required placeholder="/duong-dan hoặc https://...">
          </div>
        </div>

        <!-- parent_id -->
        <div class="field">
          <label class="field__label" for="item-parent-id">Mục cha</label>
          <select id="item-parent-id" class="field__input" name="parent_id">
            <option value="">-- Không có (mục gốc) --</option>
          </select>
          <p class="field__description">Chọn mục cha để tạo menu con.</p>
        </div>
      </div>
    </div>
  </form>

  <div class="modal__footer flex justify-between items-center">
    <div>
      <button class="btn hidden" id="item-delete-btn" type="button" data-variant="destructive" data-size="lg">Xóa</button>
    </div>
    <div class="flex gap-2 ml-auto">
      <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
      <button id="item-save-btn" data-variant="primary" data-size="lg" class="btn" type="button">Lưu mục</button>
    </div>
  </div>

  <button class="modal__close" type="button" data-modal-close>
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>

<!-- ── Confirm Delete Item Modal ── -->
<div class="modal" id="item-delete-confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Xác nhận xóa Mục Menu</h2>
    <p class="modal__description">Bạn có chắc chắn muốn xóa mục menu này và toàn bộ mục con bên dưới?</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
    <button id="item-delete-confirm-btn" data-variant="destructive" data-size="lg" class="btn" type="button">Xác nhận xóa</button>
  </div>
  <button class="modal__close" type="button" data-modal-close>
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const mainForm = document.querySelector('#menu-add-form');
    const confirmBtn = document.querySelector('#confirm-modal-btn');
    const keyInput = document.querySelector('#key');

    // Key auto-formatting
    keyInput.addEventListener('input', function () {
      this.value = this.value
        .toLowerCase()
        .replace(/\s+/g, '_')
        .replace(/[^a-z0-9_]/g, '');
    });

    // Confirm Modal trigger submit
    confirmBtn.addEventListener('click', () => {
      mainForm.submit();
    });

    // ── TREE LOCAL STATE & CONNECTOR GRAPHICS ENGINE ───────────────────────
    const menuItemsRoot = document.querySelector('#menu-items-root');
    const itemsEmptyHint = document.querySelector('#items-empty-hint');
    const menuItemsInputsContainer = document.querySelector('#menu-items-inputs-container');

    const itemModal = document.querySelector('#item-modal');
    const itemForm = itemModal.querySelector('#item-form');
    const itemModalTitle = itemModal.querySelector('#item-modal-title');
    const itemParentSelect = itemModal.querySelector('#item-parent-id');
    const itemDeleteBtn = itemModal.querySelector('#item-delete-btn');
    const itemSaveBtn = itemModal.querySelector('#item-save-btn');

    const itemDeleteConfirmModal = document.querySelector('#item-delete-confirm-modal');
    const confirmDeleteBtn = itemDeleteConfirmModal.querySelector('#item-delete-confirm-btn');

    // Reset Modal
    function resetItemForm() {
      itemForm.reset();
      itemDeleteBtn.classList.add('hidden');
      delete itemModal.dataset.editingTempId;
    }

    // Check if an item is a nested descendant of another item
    function isDescendant(parentEl, childEl) {
      let node = childEl.parentNode;
      while (node != null) {
        if (node === parentEl) return true;
        node = node.parentNode;
      }
      return false;
    }

    // Populate Parent selection list from visual DOM
    function populateParentSelect(excludeTempId = null) {
      itemParentSelect.innerHTML = '<option value="">-- Không có (mục gốc) --</option>';
      const allItems = menuItemsRoot.querySelectorAll('.menu-item');
      const excludeEl = excludeTempId ? menuItemsRoot.querySelector(`.menu-item[data-temp-id="${excludeTempId}"]`) : null;

      allItems.forEach(el => {
        const tempId = el.dataset.tempId;
        const label = el.dataset.label;

        // Prevent selecting self or descendant nodes to prevent loop cycles
        if (excludeEl) {
          if (el === excludeEl || isDescendant(excludeEl, el)) {
            return;
          }
        }

        const opt = document.createElement('option');
        opt.value = tempId;
        opt.textContent = label;
        itemParentSelect.appendChild(opt);
      });
    }

    // Trigger parent dropdown population on Add click
    document.querySelector('#add-item-btn').addEventListener('click', () => {
      resetItemForm();
      populateParentSelect();
      itemModalTitle.textContent = 'Thêm mục mới';
      modalHandler.open('#item-modal');
    });

    // Helper: Escape HTML string
    function escapeHtml(str) {
      if (!str) return '';
      return str
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
    }

    // Recursive DOM tree serialization to flat index arrays
    function serializeMenuTree(container, parentTempId = null) {
      const result = [];
      const childItems = container.querySelectorAll(':scope > .menu-item');
      
      childItems.forEach((el, index) => {
        const tempId = el.dataset.tempId;
        result.push({
          tempId: tempId,
          parentTempId: parentTempId,
          label: el.dataset.label,
          url: el.dataset.url,
          sort_order: index
        });

        const subContainer = el.querySelector('.menu-item-children');
        if (subContainer) {
          result.push(...serializeMenuTree(subContainer, tempId));
        }
      });
      
      return result;
    }

    // Synchronize inputs under main form
    function serializeAndSyncInputs() {
      const flatItems = serializeMenuTree(menuItemsRoot, null);
      menuItemsInputsContainer.innerHTML = '';

      // Update empty hint display
      const hasItems = menuItemsRoot.querySelectorAll('.menu-item').length > 0;
      itemsEmptyHint.style.display = hasItems ? 'none' : 'flex';

      // Maps temporary string ID to zero-based sequential index
      const tempIdToIndex = {};
      flatItems.forEach((item, index) => {
        tempIdToIndex[item.tempId] = index;
      });

      // Generate clean fields (Omit ID field completely)
      flatItems.forEach((item, index) => {
        let parentRef = '';
        if (item.parentTempId !== null && tempIdToIndex[item.parentTempId] !== undefined) {
          parentRef = tempIdToIndex[item.parentTempId];
        }

        const fields = {
          label: item.label,
          url: item.url,
          parent_ref: parentRef,
          sort_order: item.sort_order
        };

        Object.entries(fields).forEach(([key, val]) => {
          const inp = document.createElement('input');
          inp.type = 'hidden';
          inp.name = `items[${index}][${key}]`;
          inp.value = val;
          menuItemsInputsContainer.appendChild(inp);
        });
      });
    }

    // Bind edit/view click events to newly created DOM elements
    function bindItemCardEvents(card) {
      card.querySelector('.edit-item-btn').addEventListener('click', (e) => {
        e.stopPropagation();
        resetItemForm();
        
        const tempId = card.dataset.tempId;
        populateParentSelect(tempId);

        itemModalTitle.textContent = 'Chỉnh sửa mục menu';
        itemModal.dataset.editingTempId = tempId;

        // Populate fields
        itemForm.querySelector('#item-label').value = card.dataset.label || '';
        itemForm.querySelector('#item-url').value = card.dataset.url || '';

        // Select the current parent element's temporary ID if nested
        const parentEl = card.parentNode.closest('.menu-item');
        itemParentSelect.value = parentEl ? parentEl.dataset.tempId : '';

        itemDeleteBtn.classList.remove('hidden');
        modalHandler.open('#item-modal');
      });
    }

    // Save menu item click
    itemSaveBtn.addEventListener('click', () => {
      if (!itemForm.reportValidity()) return;

      const label = itemForm.querySelector('#item-label').value.trim();
      const url = itemForm.querySelector('#item-url').value.trim();
      const newParentTempId = itemParentSelect.value;

      const editingTempId = itemModal.dataset.editingTempId;

      if (editingTempId) {
        // Edit existing element
        const card = menuItemsRoot.querySelector(`.menu-item[data-temp-id="${editingTempId}"]`);
        if (card) {
          card.dataset.label = label;
          card.dataset.url = url;
          card.querySelector('.item-label').textContent = label;
          card.querySelector('.item-url').textContent = url;

          // Handle parent hierarchy changes
          const currentParentEl = card.parentNode.closest('.menu-item');
          const currentParentTempId = currentParentEl ? currentParentEl.dataset.tempId : '';

          if (newParentTempId !== currentParentTempId) {
            if (newParentTempId === '') {
              // Move to root
              menuItemsRoot.appendChild(card);
            } else {
              // Move to new parent container
              const targetParent = menuItemsRoot.querySelector(`.menu-item[data-temp-id="${newParentTempId}"]`);
              if (targetParent) {
                const subContainer = targetParent.querySelector('.menu-item-children');
                if (subContainer) subContainer.appendChild(card);
              }
            }
          }
        }
      } else {
        // Create new element
        const tempId = 'item_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        const card = document.createElement('div');
        card.className = 'menu-item';
        card.dataset.tempId = tempId;
        card.dataset.label = label;
        card.dataset.url = url;

        card.innerHTML = `
          <div class="menu-item-content flex items-center p-2 border rounded-md gap-2 shadow-sm hover-lift">
            <div class="drag-handle shrink-0 flex flex-col cursor-grab">
              <i class="fa-solid fa-grip-vertical"></i>
            </div>
            <span class="flex-1 font-medium text-sm item-label">
              ${escapeHtml(label)}
            </span>
            <span class="font-mono text-xs px-2 py-1 rounded-sm border item-url">
              ${escapeHtml(url)}
            </span>
            <div>
              <button type="button" class="btn edit-item-btn" data-variant="outline" data-size="md">
                <i class="fa-solid fa-eye"></i>
              </button>
            </div>
          </div>
          <div class="menu-item-children pl-8 space-y-2"></div>
        `;

        bindItemCardEvents(card);

        if (newParentTempId === '') {
          menuItemsRoot.appendChild(card);
        } else {
          const targetParent = menuItemsRoot.querySelector(`.menu-item[data-temp-id="${newParentTempId}"]`);
          if (targetParent) {
            const subContainer = targetParent.querySelector('.menu-item-children');
            if (subContainer) subContainer.appendChild(card);
          }
        }
      }

      initializeNestedDnD();
      serializeAndSyncInputs();
      modalHandler.close('#item-modal');
    });

    // Delete item click
    itemDeleteBtn.addEventListener('click', () => {
      const editingTempId = itemModal.dataset.editingTempId;
      if (!editingTempId) return;

      confirmDeleteBtn.onclick = () => {
        const card = menuItemsRoot.querySelector(`.menu-item[data-temp-id="${editingTempId}"]`);
        if (card) {
          // 1. Destroy DnD instance bound to its children dropzone before removal
          const subContainer = card.querySelector('.menu-item-children');
          if (subContainer) {
            const inst = DnD.get(subContainer);
            if (inst) inst.destroy();
          }
          card.remove();
        }
        initializeNestedDnD();
        serializeAndSyncInputs();
        modalHandler.close('#item-delete-confirm-modal');
        modalHandler.close('#item-modal');
      };
      modalHandler.open('#item-delete-confirm-modal');
    });

    // ── RECURSIVE MULTI-LEVEL DRAG AND DROP ORCHESTRATION ───────────────────
    function initializeNestedDnD() {
      // Clean up past bindings
      menuItemsRoot.querySelectorAll('.menu-item-children, #menu-items-root').forEach(container => {
        const inst = DnD.get(container);
        if (inst) inst.destroy();
      });

      // Bind fresh DnD boundaries recursively across all nested branch layers
      menuItemsRoot.querySelectorAll('.menu-item-children, #menu-items-root').forEach(container => {
        new DnD(container, {
          animation: 150,
          group: "menu-items",
          handle: '.drag-handle',
          draggable: '.menu-item',
          ghostClass: 'dnd-ghost',
          chosenClass: 'dnd-chosen',
          onEnd: () => {
            serializeAndSyncInputs(); // Update indices and parent relations instantly
          }
        });
      });
    }

    // Start with empty root DnD enabled
    initializeNestedDnD();
    serializeAndSyncInputs();
  });
</script>