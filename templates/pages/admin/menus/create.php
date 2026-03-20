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
      <h6>Tạo menu mới</h6>
    </div>
    <div class="card__description">
      Tạo một nhóm menu tuỳ chỉnh mới. Menu hệ thống được định nghĩa bởi dev.
    </div>
  </div>

  <div class="card__content">
    <?php include BASE_PATH . '/templates/components/flash_alert.php'; ?>

    <form id="menu-create-form" method="POST" action="<?= url('admin/menus') ?>">
      <div class="field-group">

        <div class="field" data-field-required>
          <label for="label">Tên hiển thị</label>
          <input id="label" class="field__input" type="text" name="label"
            value="<?= htmlspecialchars($old_input['label'] ?? '') ?>" placeholder="VD: Liên kết nhanh">
        </div>

        <div class="field" data-field-required>
          <label for="key">Key</label>
          <input id="key" class="field__input" type="text" name="key"
            value="<?= htmlspecialchars($old_input['key'] ?? '') ?>" placeholder="VD: quick_links">
        </div>

        <div class="field">
          <label for="description">Mô tả</label>
          <input id="description" class="field__input" type="text" name="description"
            value="<?= htmlspecialchars($old_input['description'] ?? '') ?>"
            placeholder="VD: Khối liên kết nhanh trên trang chủ">
        </div>

        <div class="field">
          <label for="sort_order">Thứ tự hiển thị</label>
          <input id="sort_order" class="field__input" type="number" name="sort_order" min="0"
            value="<?= htmlspecialchars($old_input['sort_order'] ?? '0') ?>">
        </div>

      </div>
    </form>
  </div>

  <div class="card__footer">
    <button data-modal-trigger="#confirm-modal" id="create-submit-btn" type="button" data-variant="primary"
      data-size="lg" class="btn">
      Tạo menu
    </button>
  </div>
</div>

<!-- Confirm create modal -->
<div class="modal" id="confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Bạn có chắc</h2>
    <p class="modal__description">
      Những thao tác này sẽ không thể hoàn tác.
    </p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
    <button id="confirm-modal-btn" data-variant="primary" data-size="lg" class="btn" type="button">Chắc chắn</button>
  </div>
  <button class="modal__close" type="button" data-modal-close>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
      <path
        d="M55.1 73.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L147.2 256 9.9 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192.5 301.3 329.9 438.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.8 256 375.1 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192.5 210.7 55.1 73.4z" />
    </svg>
  </button>
</div>

<div class="modal-overlay" data-modal-close></div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('#menu-create-form');
    const createBtn = document.querySelector('#create-submit-btn');
    const confirmBtn = document.querySelector('#confirm-modal-btn');

    new Modal('#confirm-modal');

    createBtn.addEventListener('click', (e) => {
      e.preventDefault();
    });

    confirmBtn.addEventListener('click', () => {
      form.submit();
    });
  });
</script>