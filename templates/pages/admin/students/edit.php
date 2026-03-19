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
        Student
        <span class="font-bold">#<?= htmlspecialchars($student->account_id ?? '') ?></span>
      </h6>
    </div>
    <div class="card__description">
      This is student detail form
    </div>
  </div>

  <div class="card__content">
    <?php
    include BASE_PATH . '/templates/components/flash_alert.php';
    ?>
    <form id="student-edit-form" action="<?= url('admin/students/' . $student->account_id) ?>" method="POST">

      <div class="field-group">

        <div class="field" data-field-disabled data-field-readonly>
          <label for="student_id">Student ID</label>
          <input id="student_id" class="field__input" type="text" name="student_id"
            value="<?= htmlspecialchars($student->student_id ?? '') ?>" disabled>
        </div>

        <div class="field">
          <label for="full_name">Full Name</label>
          <input id="full_name" class="field__input <?= isset($errors['full_name']) ? 'field__input--error' : '' ?>"
            type="text" name="full_name" value="<?= htmlspecialchars($student->full_name ?? '') ?>" required>
        </div>

        <div class="field">
          <label for="gender">Gender</label>
          <select id="gender" class="field__input <?= isset($errors['gender']) ? 'field__input--error' : '' ?>"
            name="gender" required>
            <option value="Male" <?= ($student?->gender == 'Nam') ? 'selected' : '' ?>>Nam</option>
            <option value="Female" <?= ($student?->gender == 'Nữ') ? 'selected' : '' ?>>Nữ</option>
          </select>
        </div>

        <div class="field">
          <label for="dob">Date of Birth</label>
          <input id="dob" class="field__input <?= isset($errors['dob']) ? 'field__input--error' : '' ?>" type="date"
            name="dob" value="<?= htmlspecialchars($student->dob ?? '') ?>" required>
        </div>

        <div class="field">
          <label for="phone">Phone</label>
          <input id="phone" class="field__input <?= isset($errors['phone']) ? 'field__input--error' : '' ?>" type="text"
            name="phone" value="<?= htmlspecialchars($student->phone ?? '') ?>" required>
        </div>

        <div class="field">
          <label for="major">Major</label>
          <input id="major" class="field__input <?= isset($errors['major']) ? 'field__input--error' : '' ?>" type="text"
            name="major" value="<?= htmlspecialchars($student->major ?? '') ?>">
        </div>

        <div class="field">
          <label for="birth_place">Birth Place</label>
          <input id="birth_place" class="field__input <?= isset($errors['birth_place']) ? 'field__input--error' : '' ?>"
            type="text" name="birth_place" value="<?= htmlspecialchars($student->birth_place ?? '') ?>">
        </div>

        <div class="field">
          <label for="classroom_id">Classroom</label>
          <select id="classroom_id"
            class="field__input <?= isset($errors['classroom_id']) ? 'field__input--error' : '' ?>" name="classroom_id"
            required>
            <option value="" disabled hidden <?= is_null($student?->classroom_id) ? 'selected' : '' ?>>
              -- Chọn lớp học --
            </option>
            <?php foreach ($classrooms as $classroom): ?>
              <option value="<?= $classroom->id ?>" <?= ($student?->classroom_id == $classroom->id) ? 'selected' : '' ?>>
                <?= htmlspecialchars($classroom->name) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

      </div>
    </form>
  </div>

  <div class="card__footer">
    <button data-modal-trigger="#confirm-modal" id="update-submit-btn" type="button" data-variant="primary"
      data-size="lg" class="w-full btn">
      Lưu thay đổi
    </button>
    <button data-modal-trigger="#delete-modal" id="delete-submit-btn" type="button" data-variant="destructive"
      data-size="lg" class="w-full btn">
      Xóa
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

<!-- Confirm delete modal -->
<div class="modal" id="delete-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Xác nhận xóa</h2>
    <p class="modal__description">
      Sinh viên <strong><?= htmlspecialchars($student->full_name ?? '') ?></strong> sẽ bị xóa và không thể khôi phục.
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

<!-- Hidden delete form — no formaction hack needed -->
<form id="student-delete-form" method="POST" action="<?= url('admin/students/delete/' . $student->account_id) ?>"
  style="display:none">
</form>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const editForm = document.querySelector('#student-edit-form');
    const deleteForm = document.querySelector('#student-delete-form');
    const confirmBtn = document.querySelector('#confirm-modal-btn');
    const deleteBtn = document.querySelector('#delete-confirm-btn');

    confirmBtn.addEventListener('click', () => editForm.submit());
    deleteBtn.addEventListener('click', () => deleteForm.submit());
  });
</script>