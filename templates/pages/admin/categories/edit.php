<?php
$errors = request()->session()->getErrors()() ?? [];
$old_input = request()->session()->getOldInputs() ?? [];
$isConst = $category->type === 'const';
?>
<script>
  window.__errors__ = <?= json_encode($errors) ?>;
  window.__old__ = <?= json_encode($old_input) ?>;
</script>

<div class="detail-panel card shadow">
  <div class="card__header">
    <div class="card__title">
      <h6>
        Chỉnh sửa danh mục
        <?php if ($isConst): ?>
          <span class="badge">Hệ thống</span>
        <?php endif; ?>
      </h6>
    </div>
    <div class="card__description">
      <?php if ($isConst): ?>
        Danh mục hệ thống — chỉ xem, không thể chỉnh sửa.
      <?php else: ?>
        Chỉnh sửa thông tin danh mục — #<?= htmlspecialchars($category->id) ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="card__content">
    <?php include BASE_PATH . '/templates/components/flash_alert.php'; ?>

    <form id="category-edit-form" method="POST" action="<?= url('admin/categories/' . $category->id) ?>">
      <?= csrf_field() ?>
      <div class="field-group">

        <div class="field" <?= !$isConst ? 'data-field-required' : '' ?>>
          <label for="name">Tên danh mục</label>
          <input id="name" class="field__input" type="text" name="name" value="<?= htmlspecialchars($category->name) ?>"
            <?= $isConst ? 'disabled' : '' ?>>
        </div>

        <div class="field">
          <label for="slug">Slug</label>
          <input id="slug" class="field__input" type="text" name="slug"
            value="<?= htmlspecialchars($category->slug ?? '') ?>" <?= $isConst ? 'disabled' : '' ?>>
        </div>

        <div class="field">
          <label for="parent_id">Danh mục cha</label>
          <select id="parent_id" class="field__input" name="parent_id" <?= $isConst ? 'disabled' : '' ?>>
            <option value="">-- Không có (danh mục gốc) --</option>
            <?php foreach ($categories as $cat): ?>
              <?php if ($cat->id === $category->id)
                continue; ?>
              <option value="<?= htmlspecialchars($cat->id) ?>" <?= $cat->id === $category->parent_id ? 'selected' : '' ?>>
                <?= str_repeat('—', $cat->depth) . htmlspecialchars($cat->name) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field">
          <label for="description">Mô tả</label>
          <textarea id="description" class="field__input" name="description" rows="4" <?= $isConst ? 'disabled' : '' ?>><?= htmlspecialchars($category->description ?? '') ?></textarea>
        </div>

      </div>
    </form>
  </div>

  <div class="card__footer">
    <?php if ($isConst): ?>
      <p class="text-muted" style="font-size: 13px;">
        Danh mục hệ thống không thể chỉnh sửa hoặc xóa.
      </p>
    <?php else: ?>
      <button data-modal-trigger="#confirm-modal" id="update-submit-btn" type="button" data-variant="primary"
        data-size="lg" class="w-full btn">Lưu thay đổi</button>
      <button data-modal-trigger="#delete-modal" id="delete-submit-btn" type="button" data-variant="destructive"
        data-size="lg" class="w-full btn">Xóa</button>
    <?php endif; ?>
  </div>
</div>

<?php if (!$isConst): ?>

  <!-- Confirm update modal -->
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
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
        <path
          d="M55.1 73.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L147.2 256 9.9 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192.5 301.3 329.9 438.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.8 256 375.1 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192.5 210.7 55.1 73.4z" />
      </svg>
    </button>
  </div>

  <!-- Confirm delete modal -->
  <div class="modal" id="delete-modal" tabindex="-1" data-state="closed">
    <div class="modal__header">
      <h2 class="modal__title">Xác nhận xóa</h2>
      <p class="modal__description">
        Danh mục <strong><?= htmlspecialchars($category->name) ?></strong> sẽ bị xóa và không thể khôi phục.
      </p>
    </div>
    <div class="modal__footer">
      <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
      <button id="delete-confirm-btn" data-variant="destructive" data-size="lg" class="btn" type="button">Xóa</button>
    </div>
    <button class="modal__close" type="button" data-modal-close>
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
        <path
          d="M55.1 73.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L147.2 256 9.9 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192.5 301.3 329.9 438.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.8 256 375.1 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192.5 210.7 55.1 73.4z" />
      </svg>
    </button>
  </div>
  <div class="modal-overlay" data-modal-close></div>

  <form id="category-delete-form" method="POST" action="<?= url('admin/categories/delete/' . $category->id) ?>"
    style="display:none"><?= csrf_field() ?></form>

<?php endif; ?>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    <?php if (!$isConst): ?>
      const form = document.querySelector('#category-edit-form');
      const confirmBtn = document.querySelector('#confirm-modal-btn');
      const deleteBtn = document.querySelector('#delete-confirm-btn');
      const deleteForm = document.querySelector('#category-delete-form');
      const nameInput = document.querySelector('#name');
      const slugInput = document.querySelector('#slug');

      nameInput.addEventListener('input', () => {
        if (slugInput.dataset.manual) return;
        slugInput.value = nameInput.value
          .toLowerCase()
          .normalize('NFD')
          .replace(/[\u0300-\u036f]/g, '')
          .replace(/đ/g, 'd')
          .replace(/[^a-z0-9\s-]/g, '')
          .trim()
          .replace(/\s+/g, '-');
      });

      slugInput.addEventListener('input', () => {
        slugInput.dataset.manual = 'true';
      });

      confirmBtn.addEventListener('click', () => form.submit());
      deleteBtn.addEventListener('click', () => deleteForm.submit());
    <?php endif; ?>
  });
</script>