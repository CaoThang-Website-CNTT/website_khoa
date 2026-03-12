<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$errors = $_SESSION['errors'] ?? [];
$old_data = $_SESSION['old_data'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_data']);
function errorFor($field, $errors)
{
  if (isset($errors[$field])) {
    return '<span class="field__error">'
      . htmlspecialchars($errors[$field][0]) .
      '</span>';
  }
  return '';
}
?>
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
  <form id="detail-form" action="<?= url('admin/teachers/update/') . $teacher->account_id ?>" method="POST">
    <div class="card__content">
      <div class="field-group">

        <div class="field">
          <label for="full_name">Full Name *</label>
          <input id="full_name" class="field__input <?= isset($errors['full_name']) ? 'field__input--error' : '' ?>"
            type="text" name="full_name" value="<?= htmlspecialchars($teacher->full_name ?? '') ?>">
          <?= errorFor('full_name', $errors) ?>
        </div>

        <div class="field">
          <label for="email">Email</label>
          <input id="email" class="field__input" type="email" name="email"
            value="<?= htmlspecialchars($teacher->account->email ?? '') ?>" disabled>
        </div>

        <div class="field">
          <label for="gender">Gender *</label>
          <select id="gender" class="field__input <?= isset($errors['gender']) ? 'field__input--error' : '' ?>"
            name="gender">
            <option value="Male" <?= ($teacher?->gender == 'Nam') ? 'selected' : '' ?>>Nam</option>
            <option value="Female" <?= ($teacher?->gender == 'Nữ') ? 'selected' : '' ?>>Nữ</option>
          </select>
          <?= errorFor('gender', $errors) ?>
        </div>

        <div class="field">
          <label for="dob">Date of Birth *</label>
          <input id="dob" class="field__input <?= isset($errors['dob']) ? 'field__input--error' : '' ?>" type="date"
            name="dob" value="<?= htmlspecialchars($teacher->dob ?? '') ?>">
          <?= errorFor('dob', $errors) ?>
        </div>

        <div class="field">
          <label for="phone">Phone *</label>
          <input id="phone" class="field__input <?= isset($errors['phone']) ? 'field__input--error' : '' ?>" type="text"
            name="phone" value="<?= htmlspecialchars($teacher->phone ?? '') ?>">
          <?= errorFor('phone', $errors) ?>
        </div>

        <div class="field">
          <label for="start_date">Start Date *</label>
          <input id="start_date" class="field__input <?= isset($errors['start_date']) ? 'field__input--error' : '' ?>"
            type="date" name="start_date" value="<?= htmlspecialchars($teacher->start_date ?? '') ?>">
          <?= errorFor('start_date', $errors) ?>
        </div>

        <div class="field">
          <label for="title">Title</label>
          <input id="title" class="field__input <?= isset($errors['title']) ? 'field__input--error' : '' ?>" type="text"
            name="title" value="<?= htmlspecialchars($teacher->title ?? '') ?>">
          <?= errorFor('title', $errors) ?>
        </div>

        <div class="field">
          <label for="department">Department</label>
          <input id="department" class="field__input <?= isset($errors['department']) ? 'field__input--error' : '' ?>"
            type="text" name="department" value="<?= htmlspecialchars($teacher->department ?? '') ?>">
          <?= errorFor('department', $errors) ?>
        </div>

      </div>
    </div>
    <div class="card__footer">
      <button data-modal-trigger="#confirm-modal" id="update-submit-btn" type="button" data-variant="primary"
        data-size="lg" class="w-full btn">Lưu thay đổi</button>
      <button data-modal-trigger="#confirm-modal" id="delete-submit-btn" type="submit"
        formaction="<?= url('admin/teachers/delete/' . $teacher->account_id) ?>" data-variant="destructive"
        data-size="lg" class="w-full btn">Xóa</button>
    </div>
  </form>
</div>
<!-- Add confirm modal -->
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
  document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector('#detail-form');
    const updateBtn = document.querySelector('#update-submit-btn');
    const deleteBtn = document.querySelector('#delete-submit-btn');
    const confirmBtn = document.querySelector('#confirm-modal-btn');

    const modal = new Modal("#confirm-modal");
    const closeTriggers = document.querySelectorAll('[data-modal-close]');

    let pendingActionUrl = '';

    console.log(modal)

    // Update Btn Event Listener
    updateBtn.addEventListener('click', function(e) {
      e.preventDefault();
      pendingActionUrl = form.getAttribute('action');
    });

    // Delete Btn Event Listener
    deleteBtn.addEventListener('click', function(e) {
      e.preventDefault();
      pendingActionUrl = deleteBtn.getAttribute('formaction');
    });

    // Confirm Btn Event Listener
    confirmBtn.addEventListener('click', function() {
      form.action = pendingActionUrl;
      form.submit();
    });
  });
</script>