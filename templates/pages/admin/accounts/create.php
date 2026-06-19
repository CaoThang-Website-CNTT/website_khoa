<?php
$errors = request()->session()->getErrors() ?? [];
$old_input = request()->session()->getOldInputs() ?? [];
?>
<script>
  window.__errors__ = <?= json_encode($errors) ?>;
  window.__old__ = <?= json_encode($old_input) ?>;
</script>

<?php $layout->start("heading") ?>
<h2 class="title-wrapper__title">
  Thêm tài khoản mới
</h2>
<p>Điền thông tin tài khoản mới vào các trường dưới đây</p>
<?php $layout->end() ?>

<?php $layout->start("actions") ?>
<a href="<?= url('admin/accounts') ?>" data-variant="outline" data-size="lg" class="btn">
  <i class="fa-solid fa-chevron-left"></i>
  Quay lại
</a>
<button data-modal-trigger="#confirm-modal" id="create-submit-btn" type="button" data-variant="primary" data-size="lg"
  class="btn">

  Thêm
</button>
<?php $layout->end() ?>

<?php $layout->start("content"); ?>
<form id="account-add-form" action="<?= url('admin/accounts') ?>" method="POST">
  <?= csrf_field() ?>

  <!-- ── Card: Thông tin tài khoản ── -->
  <div class="card shadow w-full">
    <div class="card__header">
      <legend class="card__title field__legend">Thông tin tài khoản mới</legend>
      <p class="card__description field__description">
        Vui lòng điền đầy đủ thông tin. Những trường có dấu * là bắt buộc.
      </p>
    </div>
    <hr class="separator" />
    <div class="card__content">
      <div class="field-group">

        <div class="field" data-field-required>
          <label class="field__label" for="email">Email</label>
          <input id="email" class="field__input" type="email" name="email" placeholder="VD: user@example.com" value="">
        </div>

        <div class="field" data-field-required>
          <label class="field__label" for="password">Mật khẩu</label>
          <div class="password-field">
            <input id="password" class="field__input" type="password" name="password" placeholder="Nhập mật khẩu"
              value="">
            <button class="password-field__toggle" type="button" data-password-toggle="password"
              aria-label="Show password">
              <i class="fa-solid fa-eye"></i>
            </button>
          </div>
        </div>

        <div class="field" data-field-required>
          <label class="field__label" for="password_confirmation">Confirm password</label>
          <div class="password-field">
            <input id="password_confirmation" class="field__input" type="password" name="password_confirmation"
              placeholder="Confirm password" value="">
            <button class="password-field__toggle" type="button" data-password-toggle="password_confirmation"
              aria-label="Show confirm password">
              <i class="fa-solid fa-eye"></i>
            </button>
          </div>
        </div>

        <div class="field" data-field-required>
          <label class="field__label" for="role-select">Vai trò</label>
          <button type="button" id="role-select" class="select w-full" data-select-id="role-select"
            data-select-placeholder="Chọn vai trò..."
            data-select-default-value="<?= htmlspecialchars($old_input['role'] ?? 'admin') ?>">
            <div class="select__content">
              <div class="select__item" data-select-value="admin">Admin</div>
              <div class="select__item" data-select-value="editor">Editor</div>
              <div class="select__item" data-select-value="student">Student</div>
              <div class="select__item" data-select-value="teacher">Teacher</div>
            </div>
          </button>
          <input type="hidden" name="role" id="role-input"
            value="<?= htmlspecialchars($old_input['role'] ?? 'admin') ?>">
          <?php if (isset($errors['role'])): ?>
            <span class="field__error"><i class="fa-solid fa-circle-exclamation"></i>
              <?= htmlspecialchars($errors['role'][0]) ?></span>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </div>
</form>

<!-- ── Confirm Create Modal ── -->
<div class="modal" id="confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h3 class="modal__title">Xác nhận tạo Account</h3>
    <p class="modal__description">Bạn có chắc chắn muốn tạo account này?</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
    <button id="confirm-modal-btn" data-variant="primary" data-size="lg" class="btn" type="button">Xác nhận</button>
  </div>
  <button class="modal__close" type="button" data-modal-close>
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>
<?php $layout->end(); ?>

<?php $layout->start("scripts") ?>
<script src="<?= url('public/js/pages/admin/accounts/create.js') ?>" type="module"></script>
<?php $layout->end() ?>