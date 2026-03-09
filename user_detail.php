<?php
include __DIR__ . '/services/mock_education_service.php';
use App\Services\MockEducationService;

$mockService = new MockEducationService();

$user_id = $_GET['id'] ?? null;

$student = $mockService->getStudentById((int) $user_id);
$classrooms = $mockService->getAllClassrooms();
ob_start();
?>
<div class="detail-panel card shadow">
  <div class="card__header">
    <div class="card__title">
      <h6>
        User
        <span class="font-bold">#<?= htmlspecialchars($student->account_id ?? '') ?></span>
      </h6>
    </div>
    <div class="card__description">
      This is user detail form
    </div>
  </div>
  <form id="detail-form" action="routes/users/update" method="POST">
    <div class="card__content">
      <div class="field-group">
        <div class="field" data-field-disabled data-field-readonly>
          <label for="student_id">Student ID</label>
          <input id="student_id" class="field__input" type="text" name="student_id"
            value="<?= htmlspecialchars($student->student_id ?? '') ?>">
        </div>

        <div class="field">
          <label for="fullname">Full Name</label>
          <input id="fullname" class="field__input" type="text" name="fullname"
            value="<?= htmlspecialchars($student->fullname ?? '') ?>">
        </div>

        <div class="field">
          <label for="gender">Gender</label>
          <select id="gender" class="field__input" name="gender">
            <option value="Male" <?= ($student?->gender == 'Nam') ? 'selected' : '' ?>>Nam</option>
            <option value="Female" <?= ($student?->gender == 'Nữ') ? 'selected' : '' ?>>Nữ</option>
            <option value="Other" <?= ($student?->gender == 'Khác') ? 'selected' : '' ?>>Khác</option>
          </select>
        </div>

        <div class="field">
          <label for="dob">Date of Birth</label>
          <input id="dob" class="field__input" type="date" name="dob"
            value="<?= htmlspecialchars($student->dob ?? '') ?>">
        </div>

        <div class="field">
          <label for="phone">Phone</label>
          <input id="phone" class="field__input" type="text" name="phone"
            value="<?= htmlspecialchars($student->phone ?? '') ?>">
        </div>

        <div class="field">
          <label for="class_id">Classroom</label>
          <select id="class_id" class="field__input" name="class_id">
            <option value="" disabled hidden <?= is_null($student?->class_id) ? 'selected' : '' ?>>
              -- Chọn lớp học--
            </option>
            <?php foreach ($classrooms as $classroom): ?>
              <option value="<?= $classroom->id ?>" <?= ($student?->class_id == $classroom->id) ? 'selected' : '' ?>>
                <?= htmlspecialchars($classroom->name) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>
    <div class="card__footer">
      <button data-modal-trigger="#confirm-modal" id="update-submit-btn" type="button" data-variant="primary"
        data-size="lg" class="w-full btn">Lưu thay
        đổi</button>
      <button data-modal-trigger="#confirm-modal" id="delete-submit-btn" type="submit" formaction="routes/users/destroy"
        data-variant="destructive" data-size="lg" class="w-full btn">Xóa</button>
    </div>
  </form>
</div>
<?php
$content = ob_get_clean();

require 'templates/layouts/dashboard_layout.php';
?>
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
    updateBtn.addEventListener('click', function (e) {
      e.preventDefault();
      pendingActionUrl = form.getAttribute('action');
    });

    // Delete Btn Event Listener
    deleteBtn.addEventListener('click', function (e) {
      e.preventDefault();
      pendingActionUrl = deleteBtn.getAttribute('formaction');
    });

    // Confirm Btn Event Listener
    confirmBtn.addEventListener('click', function () {
      form.action = pendingActionUrl;
      form.submit();
    });
  });
</script>