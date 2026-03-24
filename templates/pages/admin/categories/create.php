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
      <h6>Tạo danh mục mới</h6>
    </div>
    <div class="card__description">
      Điền thông tin để tạo danh mục mới
    </div>
  </div>

  <div class="card__content">
    <?php
    include BASE_PATH . '/templates/components/flash_alert.php';
    ?>

    <form id="category-create-form" method="POST" action="<?= url('admin/categories') ?>">

      <div class="field-group">

        <div class="field">
          <label for="name">Tên danh mục</label>
          <input id="name" class="field__input" type="text" name="name">
        </div>

        <div class="field">
          <label for="slug">Slug</label>
          <input id="slug" class="field__input" type="text" name="slug">
        </div>

        <div class="field">
          <label for="parent_id">Danh mục cha</label>
          <select id="parent_id" class="field__input" name="parent_id">
            <option value="">-- Không có (danh mục gốc) --</option>
            <?php foreach ($categories as $category): ?>
              <?php
              $indent = str_repeat('—', $category->depth);
              $value = htmlspecialchars($category->id);
              $label = htmlspecialchars($category->name);
              ?>
              <option value="<?= $value ?>">
                <?= $indent . $label ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field">
          <label for="description">Mô tả</label>
          <textarea id="description" class="field__input" name="description" rows="4"></textarea>
        </div>

      </div>
    </form>
  </div>

  <div class="card__footer">
    <button data-modal-trigger="#confirm-modal" id="create-submit-btn" type="button" data-variant="primary"
      data-size="lg" class="w-full btn">
      Thêm
    </button>
  </div>
</div>

<!-- Confirm modal -->
<div class="modal" id="confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Bạn có chắc</h2>
    <p class="modal__description">Những thao tác này sẽ không thể hoàn tác.</p>
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
  document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector('#category-create-form');
    const createBtn = document.querySelector('#create-submit-btn');
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

    createBtn.addEventListener('click', () => {
      new Modal('#confirm-modal');
    });

    confirmBtn.addEventListener('click', () => {
      form.submit();
    });
  });
</script>