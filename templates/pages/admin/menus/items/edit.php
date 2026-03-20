<?php
$errors = request()->getErrors() ?? [];
$old_input = request()->getOldInputs() ?? [];
?>
<script>
  window.__errors__ = <?= json_encode($errors) ?>;
  window.__old__ = <?= json_encode($old_input) ?>;
</script>

<div class="detail-panel card shadow">
  <div class="card__header">
    <div class="card__title">
      <h6>
        Sửa mục menu
        <span class="font-bold">#<?= htmlspecialchars($item->id ?? '') ?></span>
      </h6>
    </div>
    <div class="card__description">
      Thuộc menu: <strong><?= htmlspecialchars($menu->label) ?></strong>
      (<?= htmlspecialchars($menu->key) ?>)
    </div>
  </div>

  <div class="card__content">
    <?php include BASE_PATH . '/templates/components/flash_alert.php'; ?>

    <form id="item-edit-form" method="POST" action="<?= url('admin/menus/' . $menu->id . '/items/' . $item->id) ?>">

      <div class="field-group">

        <div class="field" data-field-required>
          <label for="label">Nhãn hiển thị</label>
          <input id="label" class="field__input" type="text" name="label"
            value="<?= htmlspecialchars($old_input['label'] ?? $item->label ?? '') ?>">
        </div>

        <div class="field" data-field-required>
          <label for="url">URL</label>
          <input id="url" class="field__input" type="text" name="url"
            value="<?= htmlspecialchars($old_input['url'] ?? $item->url ?? '') ?>">
        </div>

        <div class="field">
          <label for="parent_id">Thuộc mục cha</label>
          <select id="parent_id" class="field__input" name="parent_id">
            <option value="">— Không có (root item) —</option>
            <?php foreach ($items as $candidate): ?>
              <option value="<?= $candidate->id ?>" <?= ((int) ($old_input['parent_id'] ?? $item->parent_id) === $candidate->id) ? 'selected' : '' ?>>
                <?= str_repeat('&nbsp;&nbsp;', $candidate->depth ?? 0) ?>
                <?= htmlspecialchars($candidate->label) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field">
          <label for="sort_order">Thứ tự hiển thị</label>
          <input id="sort_order" class="field__input" type="number" name="sort_order" min="0"
            value="<?= htmlspecialchars($old_input['sort_order'] ?? $item->sort_order ?? '0') ?>">
        </div>

        <?php if ($item->hasChildren()): ?>
          <div class="field">
            <label>Mục con hiện tại</label>
            <div class="flex flex-col gap-1">
              <?php foreach ($item->children as $child): ?>
                <div class="flex justify-between items-center rounded-md border px-3 py-2">
                  <span class="text-sm"><?= htmlspecialchars($child->label) ?></span>
                  <span class="text-xs font-mono"><?= htmlspecialchars($child->url) ?></span>
                </div>
              <?php endforeach; ?>
            </div>
            <p class="text-xs mt-1">
              Xóa mục này sẽ xóa toàn bộ <?= count($item->children) ?> mục con bên dưới.
            </p>
          </div>
        <?php endif; ?>

      </div>
    </form>
  </div>

  <div class="card__footer">
    <button data-modal-trigger="#confirm-modal" id="update-submit-btn" type="button" data-variant="primary"
      data-size="lg" class="btn">
      Lưu thay đổi
    </button>
    <button data-modal-trigger="#delete-modal" id="delete-submit-btn" type="button" data-variant="destructive"
      data-size="lg" class="btn">
      Xóa mục
    </button>
  </div>
</div>

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

<div class="modal-overlay" data-modal-close></div>

<!-- Confirm delete modal -->
<div class="modal" id="delete-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Xác nhận xóa</h2>
    <p class="modal__description">
      Mục <strong><?= htmlspecialchars($item->label ?? '') ?></strong>
      <?php if ($item->hasChildren()): ?>
        và <strong>toàn bộ <?= count($item->children) ?> mục con</strong>
      <?php endif; ?>
      sẽ bị xóa, không thể khôi phục.
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

<!-- Hidden delete form -->
<form id="item-delete-form" method="POST" action="<?= url('admin/menus/' . $menu->id . '/items/delete/' . $item->id) ?>"
  style="display:none">
</form>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const editForm = document.querySelector('#item-edit-form');
    const deleteForm = document.querySelector('#item-delete-form');

    new Modal('#confirm-modal');
    new Modal('#delete-modal');

    document.querySelector('#confirm-modal-btn').addEventListener('click', () => {
      editForm.submit();
    });

    document.querySelector('#delete-confirm-btn').addEventListener('click', () => {
      deleteForm.submit();
    });
  });
</script>