<?php
$errors = request()->session()->getErrors() ?? [];
$old_input = request()->session()->getOldInputs() ?? [];
?>





<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">Thêm nhóm menu mới</h2>
<p class="title-wrapper__description">Điền thông tin nhóm menu và các mục bên dưới</p>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url('admin/menus') ?>" data-variant="outline" data-size="lg" class="btn">
  <i class="fa-solid fa-chevron-left"></i>
  Quay lại
</a>
<button data-modal-trigger="#confirm-modal" type="button" data-variant="primary" data-size="lg" class="btn">

  Thêm
</button>
<?php $layout->end() ?>
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
              <input id="key" class="field__input" type="text" name="key" placeholder="VD: header_menu"
                value="<?= htmlspecialchars($old_input['key'] ?? '') ?>">
              <p class="field__description">Chỉ dùng chữ thường, số và dấu gạch dưới. Duy nhất trong hệ thống.</p>
            </div>

            <div class="field" data-field-required data-field-max="100">
              <label class="field__label" for="label">Tên hiển thị</label>
              <input id="label" class="field__input" type="text" name="label" placeholder="VD: Menu Chính"
                value="<?= htmlspecialchars($old_input['label'] ?? '') ?>">
            </div>

            <div class="field" data-field-max="255">
              <label class="field__label" for="description">Mô tả</label>
              <textarea id="description" class="field__input" name="description"
                placeholder="Mô tả ngắn về nhóm menu này"><?= htmlspecialchars($old_input['description'] ?? '') ?></textarea>
            </div>

            <div class="field">
              <label class="field__label" for="sort_order">Thứ tự hiển thị</label>
              <input id="sort_order" class="field__input" type="number" name="sort_order" placeholder="0" min="0"
                value="<?= htmlspecialchars($old_input['sort_order'] ?? '0') ?>">
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
    <h3 class="modal__title">Xác nhận tạo Menu</h3>
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
            <input id="item-url" class="field__input" type="text" name="url" required
              placeholder="/duong-dan hoặc https://...">
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
      <button class="btn hidden" id="item-delete-btn" type="button" data-variant="destructive"
        data-size="lg">Xóa</button>
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
    <h3 class="modal__title">Xác nhận xóa Mục Menu</h3>
    <p class="modal__description">Bạn có chắc chắn muốn xóa mục menu này và toàn bộ mục con bên dưới?</p>
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

<?php $layout->start("scripts") ?>
<script>
  window.__errors__ = <?= json_encode($errors) ?>;
  window.__old__ = <?= json_encode($old_input) ?>;
</script>
<script src="<?= url('public/js/pages/admin/menus/create.js') ?>" type="module"></script>
<?php $layout->end() ?>
