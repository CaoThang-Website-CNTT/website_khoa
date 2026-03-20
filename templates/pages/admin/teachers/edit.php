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
        Teacher
        <span class="font-bold">#<?= htmlspecialchars($teacher->account_id ?? '') ?></span>
      </h6>
    </div>
    <div class="card__description">
      This is teacher detail form
    </div>
  </div>
  <div class="card__content">
    <?php
    include BASE_PATH . '/templates/components/flash_alert.php';
    ?>
    <form id="teacher-edit-form" action="<?= url('admin/teachers/') . $teacher->account_id ?>" method="POST">
      <div class="field-group">

        <div class="field">
          <label for="full_name">Full Name</label>
          <input id="full_name" class="field__input" type="text" name="full_name"
            value="<?= htmlspecialchars($teacher->full_name ?? '') ?>" required>
        </div>

        <div class="field">
          <label for="email">Email</label>
          <input id="email" class="field__input" type="email" name="email"
            value="<?= htmlspecialchars($teacher->account->email ?? '') ?>" disabled>
        </div>

        <div class="field">
          <label for="gender">Gender</label>
          <select id="gender" class="field__input" name="gender" required>
            <option value="Male" <?= ($teacher?->gender == 'Nam') ? 'selected' : '' ?>>Nam</option>
            <option value="Female" <?= ($teacher?->gender == 'Nữ') ? 'selected' : '' ?>>Nữ</option>
          </select>
        </div>

        <div class="field">
          <label for="dob">Date of Birth</label>
          <input id="dob" class="field__input" type="date" name="dob"
            value="<?= htmlspecialchars($teacher->dob ?? '') ?>" required>
        </div>

        <div class="field">
          <label for="phone">Phone</label>
          <input id="phone" class="field__input" name="phone" value="<?= htmlspecialchars($teacher->phone ?? '') ?>"
            required>
        </div>

        <div class="field">
          <label for="start_date">Start Date</label>
          <input id="start_date" class="field__input" type="date" name="start_date"
            value="<?= htmlspecialchars($teacher->start_date ?? '') ?>" required>
        </div>

        <div class="field">
          <label for="title">Title</label>
          <input id="title" class="field__input" type="text" name="title"
            value="<?= htmlspecialchars($teacher->title ?? '') ?>">
        </div>

        <div class="field">
          <label for="department">Department</label>
          <input id="department" class="field__input" type="text" name="department"
            value="<?= htmlspecialchars($teacher->department ?? '') ?>">
        </div>

      </div>
    </form>
  </div>
  <div class="card__footer">
    <button data-modal-trigger="#confirm-modal" id="update-submit-btn" type="button" data-variant="primary"
      data-size="lg" class="w-full btn">Lưu thay đổi</button>
    <button data-modal-trigger="#delete-modal" id="delete-submit-btn" type="button" data-variant="destructive"
      data-size="lg" class="w-full btn">Xóa</button>
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

<!-- Confirm delete modal -->
<div class="modal" id="delete-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Xác nhận xóa</h2>
    <p class="modal__description">
      Giảng viên <strong>
        <?= htmlspecialchars($teacher->full_name ?? '') ?>
      </strong> sẽ bị xóa và không thể khôi phục.
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
<form id="teacher-delete-form" method="POST" action="<?= url('admin/teachers/delete/' . $teacher->account_id) ?>"
  style="display:none">
</form>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const editForm = document.querySelector('#teacher-edit-form');
    const deleteForm = document.querySelector('#teacher-delete-form');
    const confirmBtn = document.querySelector('#confirm-modal-btn');
    const deleteBtn = document.querySelector('#delete-confirm-btn');

    confirmBtn.addEventListener('click', () => editForm.submit());
    deleteBtn.addEventListener('click', () => deleteForm.submit());
  });
</script>