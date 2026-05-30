<?php
$errors = request()->session()->getErrors() ?? [];
$old_input = request()->session()->getOldInputs() ?? [];
?>
<script>
  window.__errors__ = <?= json_encode($errors) ?>;
  window.__old__ = <?= json_encode($old_input) ?>;
</script>

<!-- Toast khi redirect về đây có set flash (ví dụ: sau khi xóa thành công) -->
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

<?php if (!$category->isEditable()): ?>
  <section class="banner" data-variant="info" role="region" aria-label="Thông báo trạng thái">
    <i class="fa-solid fa-lock"></i> Danh mục hệ thống. Bạn chỉ có quyền xem dữ liệu này.
  </section>
<?php endif; ?>

<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div class="col-6 col-md-6">
      <h2 class="title text-2xl font-semibold">
        Chỉnh sửa danh mục #<?= htmlspecialchars($category->id) ?>
      </h2>
      <p>Xem chi tiết và chỉnh sửa thông tin danh mục.</p>
    </div>
    <div class="flex gap-2">
      <div>
        <a href="<?= request()->previous(fallback: 'admin/categories') ?>" data-variant="outline" data-size="lg"
          class="btn">
          <i class="fa-solid fa-chevron-left"></i>
          Quay lại
        </a>
      </div>
      <?php if ($category->isEditable()): ?>
        <div>
          <button data-modal-trigger="#confirm-modal" id="edit-submit-btn" type="submit" data-variant="primary"
            data-size="lg" class="btn">
            Lưu
          </button>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="detail-layout">
  <!-- LEFT - CATEGORY INFO -->
  <div class="detail-layout__main flex-1">
    <div class="card shadow">
      <div class="card__header">
        <legend class="card__title field__legend">
          Chỉnh sửa danh mục
        </legend>
        <p class="card__description field__description">
          Chỉnh sửa thông tin danh mục — #<?= htmlspecialchars($category->id) ?>
        </p>
      </div>
      <hr class="separator">
      <div class="card__content">
        <form id="category-edit-form" method="POST" action="<?= url('admin/categories/' . $category->id) ?>">
          <?= csrf_field() ?>
          <div class="field-group">

            <div class="field" <?= $category->isEditable() ? 'data-field-required' : '' ?> <?= !$category->isEditable() ? ' data-field-readonly' : '' ?>>
              <label class="field__label" for="name">Tên danh mục</label>
              <input id="name" class="field__input" type="text" name="name" value="<?= htmlspecialchars($category->name) ?>">
            </div>

            <div class="field" <?= $category->isEditable() ? 'data-field-required' : '' ?> <?= !$category->isEditable() ? ' data-field-readonly' : '' ?>>
              <label class="field__label" for="slug">Slug</label>
              <input id="slug" class="field__input" type="text" name="slug"
                value="<?= htmlspecialchars($category->slug ?? '') ?>">
            </div>

            <div class="field" <?= !$category->isEditable() ? ' data-field-readonly' : '' ?>>
              <label class="field__label" for="parent_id">Danh mục cha</label>
              <select id="parent_id" class="field__input" name="parent_id">
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

            <div class="field" <?= !$category->isEditable() ? ' data-field-readonly' : '' ?>>
              <label class="field__label" for="description">Mô tả</label>
              <textarea id="description" class="field__input" name="description" rows="4"><?= htmlspecialchars($category->description ?? '') ?></textarea>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- RIGHT - METADATA INFO -->
  <div class="detail-layout__sidebar">
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
          <dd><?= htmlspecialchars($category->id) ?></dd>
        </dl>
        <hr class="separator">
        <dl class="flex justify-between">
          <dt>Được tạo vào</dt>
          <dd><?= htmlspecialchars($category->created_at) ?></dd>
        </dl>
        <hr class="separator">
        <dl class="flex justify-between">
          <dt>Lần cuối cập nhật</dt>
          <dd><?= htmlspecialchars($category->updated_at ? $category->updated_at : "Không có") ?></dd>
        </dl>
        <hr class="separator">
        <dl class="flex justify-between">
          <dt>Trạng thái dữ liệu</dt>
          <dd>
            <?php if ($category->deleted_at): ?>
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

<?php if ($category->isEditable()): ?>

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
      <i class="fa-solid fa-xmark"></i>
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
      <i class="fa-solid fa-xmark"></i>
    </button>
  </div>

  <form id="category-delete-form" method="POST" action="<?= url('admin/categories/delete/' . $category->id) ?>"
    style="display:none"><?= csrf_field() ?></form>

<?php endif; ?>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    <?php if ($category->isEditable()): ?>
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
