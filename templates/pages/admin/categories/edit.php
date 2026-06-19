<?php
$errors = request()->session()->getErrors() ?? [];
$old_input = request()->session()->getOldInputs() ?? [];
?>


<?php $layout->start("banner") ?>
<?php if (!$category->isEditable()): ?>
  <section class="banner" data-variant="info" role="region" aria-label="Thông báo trạng thái">
    <i class="fa-solid fa-lock"></i> Danh mục hệ thống. Bạn chỉ có quyền xem dữ liệu này.
  </section>
<?php endif; ?>
<?php $layout->end() ?>

<?php $layout->start('heading') ?>
<h2 class="title-wrapper__title">
  Chỉnh sửa danh mục #<?= htmlspecialchars($category->id) ?>
</h2>
<p class="title-wrapper__description">Xem chi tiết và chỉnh sửa thông tin danh mục.</p>
<?php $layout->end() ?>

<?php $layout->start('actions') ?>
<a href="<?= url('admin/categories') ?>" data-variant="outline" data-size="lg" class="btn">
  <i class="fa-solid fa-chevron-left"></i>
  Quay lại
</a>
<?php if ($category->isEditable()): ?>
  <button data-modal-trigger="#confirm-modal" id="edit-submit-btn" type="submit" data-variant="primary" data-size="lg"
    class="btn">
    Lưu
  </button>
<?php endif; ?>
<?php $layout->end() ?>

<div class="detail-layout">
  <!-- LEFT - CATEGORY INFO -->
  <div class="detail-layout__main flex-1">
    <div class="card shadow">
      <div class="card__header">
        <legend class="card__title field__legend">
          Chỉnh sửa danh mục
        </legend>
        <p class="card__description field__description">
          Chỉnh sửa thông tin danh mục - #<?= htmlspecialchars($category->id) ?>
        </p>
      </div>
      <hr class="separator">
      <div class="card__content">
        <form id="category-edit-form" method="POST" action="<?= url('admin/categories/' . $category->id) ?>">
          <?= csrf_field() ?>
          <div class="field-group">

            <div class="field" <?= $category->isEditable() ? 'data-field-required' : '' ?> <?= !$category->isEditable() ? ' data-field-readonly' : '' ?>>
              <label class="field__label" for="name">Tên danh mục</label>
              <input id="name" class="field__input" type="text" name="name"
                value="<?= htmlspecialchars($category->name) ?>">
            </div>

            <div class="field" <?= $category->isEditable() ? 'data-field-required' : '' ?> <?= !$category->isEditable() ? ' data-field-readonly' : '' ?>>
              <label class="field__label" for="slug">Slug</label>
              <input id="slug" class="field__input" type="text" name="slug"
                value="<?= htmlspecialchars($category->slug ?? '') ?>">
            </div>

            <div class="field" <?= !$category->isEditable() ? ' data-field-readonly' : '' ?>>
              <label class="field__label" for="parent_id">Danh mục cha</label>
              <button type="button" class="select" data-select-id="parent_id" data-select-searchable
                data-select-placeholder="Chọn danh mục cha" name="parent_id" data-be-meta-key="parent_id" role="listbox"
                data-select-default-value="<?= $category->parent_id ?>">
                <div class="select__content">
                  <?php foreach (($categories ?? []) as $cat): ?>
                    <?php if ($cat->id === $category->id)
                      continue; ?>
                    <div class="select__item" data-select-value="<?= $cat->id ?>">
                      <?= htmlspecialchars($cat->name) ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              </button>
            </div>

            <div class="field" <?= !$category->isEditable() ? ' data-field-readonly' : '' ?>>
              <label class="field__label" for="description">Mô tả</label>
              <textarea id="description" class="field__input" name="description"
                rows="4"><?= htmlspecialchars($category->description ?? '') ?></textarea>
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
      <h3 class="modal__title">Xác nhận chỉnh sửa</h3>
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
      <h3 class="modal__title">Xác nhận xóa</h3>
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

<?php $layout->start("scripts") ?>
<script>
  window.__errors__ = <?= json_encode($errors) ?>;
  window.__old__ = <?= json_encode($old_input) ?>;
</script>
<script>window.__categoryEdit__ = { isEditable: <?= $category->isEditable() ? 'true' : 'false' ?> };</script>
<script src="<?= url('public/js/pages/admin/categories/edit.js') ?>" type="module"></script>
<?php $layout->end() ?>
