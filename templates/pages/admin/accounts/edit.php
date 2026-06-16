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
  Chỉnh sửa tài khoản #<?= htmlspecialchars($account->id) ?>
</h2>
<p>Xem chi tiết và chỉnh sửa thông tin tài khoản.</p>
<?php $layout->end() ?>

<?php $layout->start("actions") ?>
<a href="<?= url('admin/accounts') ?>" data-variant="outline" data-size="lg" class="btn">
  <i class="fa-solid fa-chevron-left"></i>
  Quay lại
</a>
<button data-modal-trigger="#delete-modal" type="button" data-variant="destructive" data-size="lg" class="btn">
  <i class="fa-solid fa-trash"></i>
  Xóa
</button>
<button data-modal-trigger="#confirm-modal" id="edit-submit-btn" type="button" data-variant="primary" data-size="lg"
  class="btn">
  <i class="fa-solid fa-floppy-disk"></i>
  Lưu
</button>
<?php $layout->end() ?>

<div class="detail-layout">
  <!-- LEFT - ACCOUNT INFO -->
  <div class="detail-layout__main flex-1">
    <div class="card shadow">
      <div class="card__header">
        <legend class="card__title field__legend">Chỉnh sửa tài khoản</legend>
        <p class="card__description field__description">
          Chỉnh sửa thông tin tài khoản. Để trống mật khẩu nếu không muốn thay đổi.
        </p>
      </div>
      <hr class="separator">
      <div class="card__content">
        <form id="account-edit-form" method="POST" action="<?= url('admin/accounts/' . $account->id) ?>">
          <?= csrf_field() ?>
          <div class="field-group">

            <div class="field" data-field-required>
              <label class="field__label" for="email">Email</label>
              <input id="email" class="field__input" type="email" name="email"
                value="<?= htmlspecialchars($old_input['email'] ?? $account->email) ?>">
            </div>

            <div class="field">
              <label class="field__label" for="password">Mật khẩu</label>
              <div class="password-field">
                <input id="password" class="field__input" type="password" name="password"
                  placeholder="Nhập mật khẩu mới (để trống nếu giữ nguyên)">
                <button class="password-field__toggle" type="button" data-password-toggle="password"
                  aria-label="Show password">
                  <i class="fa-solid fa-eye"></i>
                </button>
              </div>
            </div>

            <div class="field" id="password-confirmation-field" data-field-required>
              <label class="field__label" for="password_confirmation">Xác nhận mật khẩu</label>
              <div class="password-field">
                <input id="password_confirmation" class="field__input" type="password" name="password_confirmation"
                  placeholder="Xác nhận mật khẩu">
                <button class="password-field__toggle" type="button" data-password-toggle="password_confirmation"
                  aria-label="Show confirm password">
                  <i class="fa-solid fa-eye"></i>
                </button>
              </div>
              <div class="field__description">
                Nhập lại mật khẩu để xác nhận
              </div>
            </div>

            <div class="field" data-field-required>
              <label class="field__label" for="role-select">Vai trò</label>
              <button type="button" id="role-select" class="select w-full" data-select-id="role-select"
                data-select-placeholder="Chọn vai trò..."
                data-select-default-value="<?= htmlspecialchars($old_input['role'] ?? $account->role) ?>">
                <div class="select__content">
                  <div class="select__item" data-select-value="admin">Admin</div>
                  <div class="select__item" data-select-value="editor">Editor</div>
                  <div class="select__item" data-select-value="student">Student</div>
                  <div class="select__item" data-select-value="teacher">Teacher</div>
                </div>
              </button>
              <input type="hidden" name="role" id="role-input"
                value="<?= htmlspecialchars($old_input['role'] ?? $account->role) ?>">
              <?php if (isset($errors['role'])): ?>
                <span class="field__error"><i class="fa-solid fa-circle-exclamation"></i>
                  <?= htmlspecialchars($errors['role'][0]) ?></span>
              <?php endif; ?>
            </div>

          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- RIGHT - METADATA INFO -->
  <div class="detail-layout__sidebar">
    <div class="metadata-card card shadow">
      <div class="card__header">
        <div class="card__title">Thông tin bản ghi</div>
      </div>
      <hr class="separator">
      <div class="card__content space-y-4">
        <dl class="flex justify-between">
          <dt>ID</dt>
          <dd><?= htmlspecialchars($account->id) ?></dd>
        </dl>
        <hr class="separator">
        <dl class="flex justify-between">
          <dt>Được tạo vào</dt>
          <dd><?= htmlspecialchars($account->created_at) ?></dd>
        </dl>
        <hr class="separator">
        <dl class="flex justify-between">
          <dt>Lần cuối cập nhật</dt>
          <dd><?= htmlspecialchars($account->updated_at ? $account->updated_at : "Không có") ?></dd>
        </dl>
        <hr class="separator">
        <dl class="flex justify-between">
          <dt>Trạng thái dữ liệu</dt>
          <dd>
            <?php if ($account->deleted_at): ?>
              <span class="badge" data-variant="destructive">Đã xóa</span>
            <?php else: ?>
              <span class="badge" data-variant="primary">Hoạt động</span>
            <?php endif; ?>
          </dd>
        </dl>
      </div>
    </div>
  </div>
</div>

<!-- Confirm Update Modal -->
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

<!-- Confirm Delete Modal -->
<div class="modal" id="delete-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Xác nhận xóa tài khoản</h2>
    <p class="modal__description">Tài khoản này sẽ bị khóa/xóa tạm thời. Bạn có chắc chắn?</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
    <button id="delete-confirm-btn" data-variant="destructive" data-size="lg" class="btn" type="button">Xóa</button>
  </div>
  <button class="modal__close" type="button" data-modal-close>
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>

<form id="account-delete-form" method="POST" action="<?= url('admin/accounts/' . $account->id) ?>" style="display:none">
  <?= csrf_field() ?>
  <input type="hidden" name="_method" value="DELETE">
</form>

<?php $layout->start("scripts") ?>
<script>
  document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector('#account-edit-form');
    const confirmBtn = document.querySelector('#confirm-modal-btn');
    const deleteBtn = document.querySelector('#delete-confirm-btn');
    const deleteForm = document.querySelector('#account-delete-form');
    const roleSelect = document.querySelector('[data-select-id="role-select"]');
    const roleInput = document.querySelector('#role-input');
    const passwordInput = document.querySelector('#password');
    const passwordConfirmationField = document.querySelector('#password-confirmation-field');
    const passwordConfirmationInput = document.querySelector('#password_confirmation');
    if (passwordInput && passwordConfirmationField && passwordConfirmationInput) {
      const syncPasswordConfirmation = () => {
        const shouldShow = passwordInput.value.trim() !== '';
        passwordConfirmationField.classList.toggle('hidden', !shouldShow);
        passwordConfirmationInput.required = shouldShow;

        if (!shouldShow) {
          passwordConfirmationInput.value = '';
        }
      };

      passwordInput.addEventListener('input', syncPasswordConfirmation);
      syncPasswordConfirmation();
    }

    if (roleSelect && roleInput) {
      roleSelect.addEventListener('select:change', (e) => {
        roleInput.value = e.detail.value || '';
      });
    }

    if (confirmBtn && form) {
      confirmBtn.addEventListener('click', () => {
        form.submit();
      });
    }

    if (deleteBtn && deleteForm) {
      deleteBtn.addEventListener('click', () => {
        deleteForm.submit();
      });
    }
  });
</script>
<?php $layout->end() ?>