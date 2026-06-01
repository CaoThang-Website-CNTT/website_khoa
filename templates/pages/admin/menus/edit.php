<?php
$errors = request()->session()->getErrors() ?? [];
$old_input = request()->session()->getOldInputs() ?? [];

$isEditable = $menu->isEditable();
?>

<script>
  window.__errors__ = <?= json_encode($errors) ?>;
  window.__old__ = <?= json_encode($old_input ?? []) ?>;
</script>

<?php
function renderMenuItems(array $items, object $menu): void
{
  foreach ($items as $item):
    ?>
    <div class="menu-item" data-id="<?= $item->id ?>">
      <!-- Khối hiển thị Item -->
      <div class="menu-item-content flex items-center p-2 border rounded-md gap-2 shadow-sm hover-lift">
        <div class="drag-handle shrink-0 flex flex-col">
          <i class="fa-solid fa-grip-vertical"></i>
        </div>
        <span class="flex-1 font-medium text-sm item-label">
          <?= htmlspecialchars($item->label) ?>
        </span>
        <span class="font-mono text-xs px-2 py-1 rounded-sm border">
          <?= htmlspecialchars($item->url) ?>
        </span>
        <div>
          <button type="button" class="btn edit-item-btn" data-variant="outline" data-size="md"
            data-modal-trigger="#item-modal" data-item='<?= json_encode([
              'id' => $item->id,
              'label' => $item->label,
              'url' => $item->url,
              'parent_id' => $item->parent_id,
              'sort_order' => $item->sort_order
            ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
            <i class="fa-solid fa-eye"></i>
          </button>
        </div>
      </div>

      <!-- Container con đệ quy -->
      <div class="menu-item-children pl-8 space-y-2" data-parent-id="<?= $item->id ?>">
        <?php if ($item->hasChildren()): ?>
          <?php renderMenuItems($item->children, $menu); ?>
        <?php endif; ?>
      </div>
    </div>
    <?php
  endforeach;
}
?>

<?php if ($flash = request()->session()->getFlash("notification")): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      toast.<?= $flash['type'] ?>(
        '<?= $flash['title'] ?>',
        '<?= $flash['desc'] ?>'
      );
    });
  </script>
<?php endif; ?>

<?php if (!$menu->isEditable()): ?>
  <section class="banner" data-variant="info" role="region" aria-label="Thông báo trạng thái">
    <i class="fa-solid fa-lock"></i> Nhóm menu hệ thống. Bạn chỉ có quyền xem dữ liệu này.
  </section>
<?php endif; ?>

<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div class="col-6 col-md-6">
      <h2 class="title text-2xl font-semibold">
        Menu
        <?= ' #' . htmlspecialchars($menu->id) ?>
      </h2>
      <p>Xem chi tiết và chỉnh sửa thông tin menu.</p>
    </div>
    <div class="flex gap-2">
      <div>
        <a href="<?= url('admin/menus') ?>" data-variant="outline" data-size="lg" class="btn">
          <i class="fa-solid fa-chevron-left"></i>
          Quay lại
        </a>
      </div>
      <div>
        <?php if ($isEditable): ?>
          <button data-modal-trigger="#confirm-modal" id="edit-submit-btn" type="submit" data-variant="primary"
            data-size="lg" class="btn">
            Lưu
          </button>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="detail-layout">
  <!-- LEFT - MENU ITEM TREE -->
  <div class="detail-layout__main flex-1">
    <div class="card shadow">
      <fieldset class="field__set">
        <div class="card__header">
          <legend class="card__title field__legend">Các mục trong menu</legend>
          <p class="card__description field__description">Kéo thả các mục để sắp xếp thứ tự và cấp độ cha-con.</p>
          <?php if ($isEditable): ?>
            <button class="btn card__action" type="button" id="add-item-btn" data-modal-trigger="#item-modal"
              data-variant="outline" data-size="lg">
              <i class="fa-solid fa-plus"></i>
              Thêm item
            </button>
          <?php endif; ?>
        </div>

        <hr class="separator" />

        <div class="card__content">
          <div id="menu-items-root" class="space-y-2" data-parent-id="null">
            <?php if (!empty($menu->items)): ?>
              <?php renderMenuItems($menu->items, $menu); ?>
            <?php else: ?>
              <p class="empty-hint text-center py-4">
                Chưa có mục nào. Thêm mục đầu tiên bên dưới.
              </p>
            <?php endif; ?>
          </div>
        </div>
      </fieldset>
    </div>
  </div>

  <!-- RIGHT - MENU INFO -->
  <div class="detail-layout__sidebar">
    <div class="card shadow">
      <div class="card__header">
        <legend class="card__title field__legend">
          Thông tin nhóm menu
        </legend>
      </div>

      <hr class="separator" />

      <div class="card__content">
        <form id="menu-edit-form" method="POST" action="<?= url('admin/menus/' . $menu->id) ?>">
          <?= csrf_field() ?>
          <input type="hidden" id="menu-reorder-input" name="reorder" value="">
          <div class="field-group">
            <div class="field">
              <label class="field__label">Loại</label>
              <input class="field__input" type="text" value="<?= $menu->isEditable() ? 'Tuỳ chỉnh' : 'Hệ thống' ?>"
                disabled>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="field" data-field-required data-field-max="60">
                <label class="field__label" for="key">Key (định danh)</label>
                <input id="key" class="field__input" type="text" name="key" placeholder="VD: header_menu"
                  value="<?= htmlspecialchars($menu->key) ?>" <?= $isEditable ? '' : 'disabled' ?>>
                <p class="field__description">Chỉ dùng chữ thường, số và dấu gạch dưới. Duy nhất trong hệ thống.</p>
              </div>

              <div class="field" data-field-required data-field-max="100">
                <label class="field__label" for="label">Tên hiển thị</label>
                <input id="label" class="field__input" type="text" name="label" placeholder="VD: Menu Chính"
                  value="<?= htmlspecialchars($menu->label ?? '') ?>" <?= $isEditable ? '' : 'disabled' ?>>
              </div>
            </div>

            <div class="field" data-field-max="255">
              <label class="field__label" for="description">Mô tả</label>
              <textarea id="description" class="field__input" name="description"
                placeholder="Mô tả ngắn về nhóm menu này" <?= $isEditable ? '' : 'disabled' ?>><?= htmlspecialchars($menu->description ?? '') ?></textarea>
            </div>

            <div class="field">
              <label class="field__label" for="sort_order">Thứ tự hiển thị</label>
              <input id="sort_order" class="field__input" type="number" name="sort_order" placeholder="0" min="0"
                value="<?= htmlspecialchars($menu->sort_order ?? 0) ?>" <?= $isEditable ? '' : 'disabled' ?>>
              <p class="field__description">Số nhỏ hơn sẽ hiển thị trước.</p>
            </div>
          </div>
        </form>
      </div>
    </div>
    <!-- Metadata -->
    <div class="metadata-card card shadow">
      <div class="card__header">
        <div class="card__title">
          Thông tin bản ghi
        </div>
      </div>
      <hr class="separator">
      <div class="card__content space-y-4">
        <dl class="flex justify-between">
          <dt>ID</dt>
          <dd><?= htmlspecialchars($menu->id) ?></dd>
        </dl>
        <hr class="separator">
        <dl class="flex justify-between">
          <dt>Được tạo vào</dt>
          <dd><?= htmlspecialchars($menu->created_at) ?></dd>
        </dl>
        <hr class="separator">
        <dl class="flex justify-between">
          <dt>Lần cuối cập nhật</dt>
          <dd><?= htmlspecialchars($menu->updated_at ? $menu->updated_at : "Không có") ?></dd>
        </dl>
        <hr class="separator">
        <dl class="flex justify-between">
          <dt>Trạng thái dữ liệu</dt>
          <dd>
            <?php if ($menu->deleted_at): ?>
              <span class="badge" data-variant="destructive">
                Đã xóa
              </span>
            <?php else: ?>
              <span class="badge" data-variant="primary">Hoạt động</span>
            <?php endif; ?>
          </dd>
        </dl>
      </div>
    </div>
  </div>
</div>

<!-- ==========================================================================
     MODALS SECTION
     ========================================================================== -->
<!-- 1. Item CRUD Modal -->
<div class="modal detail-modal" id="item-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title" id="item-modal-title">Thêm mục mới</h2>
    <p class="modal__description" id="item-modal-description">Điền thông tin mục menu bên dưới.</p>
  </div>
  <form id="item-form" method="POST" action="">
    <?= csrf_field() ?>
    <div class="detail-modal__form space-y-4">
      <div class="field-group">
        <div class="grid grid-cols-2 gap-4">
          <!-- label -->
          <div class="field" data-field-required <?= !$isEditable ? ' data-field-readonly' : '' ?>>
            <label class="field__label" for="item-label">Tên hiển thị (Nhãn)</label>
            <input id="item-label" class="field__input" type="text" name="label" required placeholder="VD: Trang chủ">
          </div>

          <!-- url -->
          <div class="field" data-field-required <?= !$isEditable ? ' data-field-readonly' : '' ?>>
            <label class="field__label" for="item-url">Đường dẫn (URL)</label>
            <input id="item-url" class="field__input" type="text" name="url" placeholder="/duong-dan hoặc https://...">
          </div>
        </div>

        <!-- parent_id -->
        <div class="field" <?= !$isEditable ? ' data-field-readonly' : '' ?>>
          <label class="field__label" for="item-parent-id">Mục cha</label>
          <select id="item-parent-id" class="field__input" name="parent_id">
            <option value="">-- Không có (mục gốc) --</option>
          </select>
          <p class="field__description">Chọn mục cha để tạo menu con.</p>
        </div>

        <!-- sort_order -->
        <div class="field" <?= !$isEditable ? ' data-field-readonly' : '' ?>>
          <label class="field__label" for="item-sort-order">Thứ tự hiển thị</label>
          <input id="item-sort-order" class="field__input" type="number" name="sort_order" min="0" value="0">
        </div>
      </div>
    </div>
  </form>
  <div class="modal__footer flex justify-between items-center">
    <!-- Editable Modal Footer -->
    <?php if ($isEditable): ?>
      <div>
        <button class="btn" id="item-delete-btn" type="button" data-variant="destructive" data-size="lg">Xóa</button>
      </div>

      <div class="flex gap-2 ml-auto">
        <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
        <button id="item-save-btn" data-variant="primary" data-size="lg" class="btn" type="button">Lưu mục</button>
      </div>
      <!-- Non-editable Modal Footer -->
    <?php else: ?>
      <div class="flex gap-2 justify-end w-full">
        <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
      </div>
    <?php endif; ?>
  </div>

  <button class="modal__close" type="button" data-modal-close>
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>

<!-- 2. Confirm Update Modal -->
<?php if ($isEditable): ?>
  <div class="modal" id="confirm-modal" tabindex="-1" data-state="closed">
    <div class="modal__header">
      <h2 class="modal__title">Xác nhận chỉnh sửa</h2>
      <p class="modal__description">Bạn có chắc muốn lưu các thay đổi này?</p>
    </div>
    <div class="modal__footer">
      <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
      <button id="confirm-modal-btn" data-variant="primary" data-size="lg" class="btn" type="button">Lưu</button>
    </div>
    <button class="modal__close" type="button" data-modal-close>
      <i class="fa-solid fa-xmark"></i>
    </button>
  </div>

  <!-- 3. Confirm Delete Modal (Stacked) -->
  <div class="modal" id="item-delete-confirm-modal" tabindex="-1" data-state="closed">
    <div class="modal__header">
      <h2 class="modal__title">Xác nhận xóa Mục Menu</h2>
      <p class="modal__description">Bạn có chắc chắn muốn xóa mục menu này và toàn bộ mục con bên dưới? Hành động này
        không thể hoàn tác.</p>
    </div>
    <div class="modal__footer">
      <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
      <button id="item-delete-confirm-btn" data-variant="destructive" data-size="lg" class="btn" type="button">Xác nhận
        xóa</button>
    </div>
    <button class="modal__close" type="button" data-modal-close>
      <i class="fa-solid fa-xmark"></i>
    </button>
  </div>

  <!-- Hidden Delete Form -->
  <form id="item-delete-form" method="POST" action="" class="hidden">
    <?= csrf_field() ?>
  </form>
<?php endif; ?>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    // ── BIẾN DÙNG CHUNG CỦA MODAL ──────────────────────────────────────────
    const itemModal = document.querySelector('#item-modal');
    const itemModalTitle = itemModal?.querySelector('#item-modal-title');
    const itemModalDescription = itemModal?.querySelector('#item-modal-description');
    const itemForm = itemModal?.querySelector('#item-form');
    const itemDeleteBtn = itemModal?.querySelector('#item-delete-btn');

    // Hàm reset form
    function resetItemForm() {
      if (itemForm) itemForm.reset();
      if (itemDeleteBtn) itemDeleteBtn.classList.add('hidden');
    }

    document.querySelectorAll('.edit-item-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        resetItemForm();
        const item = JSON.parse(btn.dataset.item);

        if (itemModalTitle) {
          itemModalTitle.textContent = `<?= $isEditable ? 'Chỉnh sửa mục menu' : 'Chi tiết mục menu' ?>`;
        }
        if (itemModalDescription) {
          itemModalDescription.textContent = `<?= $isEditable ? 'Thay đổi thông tin mục menu bên dưới.' : 'Thông tin chi tiết của mục menu hệ thống.' ?>`;
        }

        if (itemForm) {
          itemForm.querySelector('#item-label').value = item.label || '';
          itemForm.querySelector('#item-url').value = item.url || '';
          itemForm.querySelector('#item-sort-order').value = item.sort_order || 0;

          // Xử lý mục cha tĩnh cho trường hợp read-only
          const parentSelect = itemForm.querySelector('#item-parent-id');
          if (parentSelect) {
            parentSelect.innerHTML = '';

            // Tìm nhãn mục cha trực tiếp từ cấu trúc DOM ngoài danh sách
            let parentLabel = '-- Không có (mục gốc) --';
            if (item.parent_id) {
              const parentEl = document.querySelector(`.menu-item[data-id="${item.parent_id}"]`);
              parentLabel = parentEl?.querySelector('.item-label')?.textContent.trim() || `Mục cha (ID: ${item.parent_id})`;
            }

            const opt = document.createElement('option');
            opt.value = item.parent_id || '';
            opt.textContent = parentLabel;
            parentSelect.appendChild(opt);
            parentSelect.value = item.parent_id || '';
          }
        }
      });
    });

    <?php if ($isEditable): ?>
      // ── CHỈ CHẠY LOGIC EDIT / CUD KHI EDITABLE ──────────────────────────────

      function handleDragEnd() {
        const rootContainer = document.querySelector('#menu-items-root');
        const serializedData = serializeMenuTree(rootContainer, null);

        const reorderInput = document.querySelector('#menu-reorder-input');
        if (reorderInput) {
          reorderInput.value = JSON.stringify(serializedData);
        }

        updateMenuDOMStates(serializedData);
      }

      document.querySelectorAll('.menu-item-children, #menu-items-root').forEach(container => {
        new DnD(container, {
          animation: 150,
          group: "menu-items",
          handle: '.drag-handle',
          draggable: '.menu-item',
          ghostClass: 'dnd-ghost',
          chosenClass: 'dnd-chosen',
          onEnd: handleDragEnd
        });
      });

      // Trích xuất cây DOM đệ quy thành mảng cấu trúc phẳng
      function serializeMenuTree(container, parentId = null) {
        const serialized = [];
        const childItems = container.querySelectorAll(':scope > .menu-item');

        childItems.forEach((el, index) => {
          const id = parseInt(el.dataset.id);
          serialized.push({
            id: id,
            parent_id: parentId,
            sort_order: index
          });

          const subContainer = el.querySelector('.menu-item-children');
          if (subContainer) {
            serialized.push(...serializeMenuTree(subContainer, id));
          }
        });

        return serialized;
      }

      // Đồng bộ hoá trạng thái data attributes trong DOM sau khi kéo thả thành công
      function updateMenuDOMStates(items) {
        items.forEach(item => {
          const itemEl = document.querySelector(`.menu-item[data-id="${item.id}"]`);
          if (itemEl) {
            const editBtn = itemEl.querySelector('.edit-item-btn');
            if (editBtn) {
              const currentData = JSON.parse(editBtn.dataset.item || '{}');
              currentData.parent_id = item.parent_id;
              currentData.sort_order = item.sort_order;
              editBtn.dataset.item = JSON.stringify(currentData);
            }
          }
        });

        updateMenuItemsListFromDOM();
      }

      // Dialog & Modal Crud Handling
      let menuItemsList = [];

      // Tự động thu thập toàn bộ menu item hiện hữu từ DOM
      function updateMenuItemsListFromDOM() {
        menuItemsList = [];
        document.querySelectorAll('.menu-item').forEach(el => {
          const id = parseInt(el.dataset.id);
          const label = el.querySelector('.item-label')?.textContent.trim() || `ID: ${id}`;
          menuItemsList.push({ id, label });
        });
      }

      const editForm = document.querySelector('#menu-edit-form');
      const confirmModal = document.querySelector('#confirm-modal');
      const confirmBtn = confirmModal?.querySelector('#confirm-modal-btn');
      confirmBtn?.addEventListener('click', () => editForm.submit());

      const itemAddBtn = document.querySelector('#add-item-btn');
      const itemSaveBtn = itemModal?.querySelector('#item-save-btn');
      const itemDeleteForm = document.querySelector('#item-delete-form');
      const itemDeleteConfirmModal = document.querySelector('#item-delete-confirm-modal');
      const confirmDeleteBtn = itemDeleteConfirmModal?.querySelector('#item-delete-confirm-btn');

      // Dựng dropdown parent dynamic
      function populateParentSelect(excludeId = null) {
        const parentSelect = itemForm.querySelector('#item-parent-id');
        parentSelect.innerHTML = '<option value="">-- Không có (mục gốc) --</option>';
        menuItemsList.forEach(item => {
          if (excludeId && parseInt(item.id) === parseInt(excludeId)) return;
          const opt = document.createElement('option');
          opt.value = item.id;
          opt.textContent = item.label;
          parentSelect.appendChild(opt);
        });
      }

      // Thêm item mới
      if (itemAddBtn) {
        itemAddBtn.addEventListener('click', () => {
          resetItemForm();
          itemModalTitle.textContent = 'Thêm mục mới';
          itemForm.action = `<?= url('admin/menus/' . $menu->id . '/items') ?>`;
          populateParentSelect();
        });
      }

      // Sửa/Xem item cho chế độ Editable (Được phép sửa action và gán nút xóa)
      document.querySelectorAll('.edit-item-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const item = JSON.parse(btn.dataset.item);
          itemForm.action = `<?= url('admin/menu-items/') ?>` + item.id;
          populateParentSelect(item.id);

          // Cập nhật giá trị cha
          itemForm.querySelector('#item-parent-id').value = item.parent_id || '';

          if (itemDeleteBtn) {
            itemDeleteBtn.classList.remove('hidden');
            itemDeleteBtn.onclick = () => {
              if (confirmDeleteBtn) {
                confirmDeleteBtn.onclick = () => {
                  itemDeleteForm.action = `<?= url('admin/menu-items/') ?>` + item.id + '/delete';
                  itemDeleteForm.submit();
                };
              }
              modalHandler.open('#item-delete-confirm-modal');
            };
          }
        });
      });

      if (itemSaveBtn) {
        itemSaveBtn.addEventListener('click', () => {
          if (itemForm.reportValidity()) {
            itemForm.submit();
          }
        });
      }

      updateMenuItemsListFromDOM();
    <?php else: ?>
      // Chế độ chỉ xem: Ẩn các nút kéo thả (drag handles)
      document.querySelectorAll('.drag-handle').forEach(el => el.style.display = 'none');
    <?php endif; ?>
  });
</script>