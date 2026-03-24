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
      <h6>Tạo carousel mới</h6>
    </div>
    <div class="card__description">
      Thêm một carousel vào hệ thống.
    </div>
  </div>

  <div class="card__content">
    <?php include BASE_PATH . '/templates/components/flash_alert.php'; ?>

    <form id="carousel-create-form" method="POST" action="<?= url('admin/carousels') ?>">
      <div class="field-group">

        <div class="field" data-field-required>
          <label for="name">Tên Carousel</label>
          <input id="name" class="field__input" type="text" name="name"
            value="<?= htmlspecialchars($old_input['name'] ?? '') ?>" placeholder="VD: Home Banner Khuyến Mãi">
        </div>

        <div class="field">
          <label for="slug">Đường dẫn (Slug)</label>
          <input id="slug" class="field__input" type="text" name="slug"
            value="<?= htmlspecialchars($old_input['slug'] ?? '') ?>" placeholder="Để trống để tự động tạo từ tên">
        </div>

        <div class="field">
          <label class="field__toggle">
            <input type="checkbox" name="is_active" id="is_active" value="1" <?= (!isset($old_input['name']) || !empty($old_input['is_active'])) ? 'checked' : '' ?>>
            <span class="field__toggle-track"></span>
            <span class="field__toggle-label">Kích hoạt / Hiển thị Carousel này</span>
          </label>
        </div>

      </div>
    </form>
  </div>

  <div class="card__footer">
    <button data-modal-trigger="#confirm-modal" type="button" data-variant="primary" data-size="lg" class="btn">
      Thêm
    </button>
  </div>
</div>

<div class="modal" id="confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Xác nhận tạo carousel</h2>
    <p class="modal__description">Bạn có chắc muốn tạo carousel mới này?</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
    <button id="confirm-modal-btn" data-variant="primary" data-size="lg" class="btn" type="button">Xác nhận</button>
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
    const form = document.querySelector('#carousel-create-form');
    const confirmBtn = document.querySelector('#confirm-modal-btn');
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

    new Modal('#confirm-modal');
    confirmBtn.addEventListener('click', () => form.submit());
  });
</script>