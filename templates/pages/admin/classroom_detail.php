<?php
require_once __DIR__ . '/../../../config/constants.php';

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
        Classrooms
        <span class="font-bold">#<?= htmlspecialchars($classroom?->id ?? '') ?></span>
      </h6>
    </div>
    <div class="card__description">
      This is classroom detail form
    </div>
  </div>
  <form id="detail-form" action="<?= url('admin/classrooms/update/') . $classroom?->id ?>" method="POST">
    <div class="card__content">
      <div class="field-group">
        <div class="field">
          <label for="short_name">Short Name (auto generate)</label>
          <input id="short_name" class="field__input" type="text" name="short_name"
            value="<?= htmlspecialchars($classroom->short_name ?? 'N/A') ?>" readonly disabled>
        </div>

        <div class="field">
          <label for="level">Level *</label>
          <input id="level" class="field__input" value="<?= htmlspecialchars(LEVELS[$major['level']]) ?>" readonly
            disabled>
        </div>

        <div class="field">
          <label for="class_of">Class Of *</label>
          <input id="class_of" class="field__input <?= isset($errors['class_of']) ? 'field__input--error' : '' ?>"
            type="number" min="1" max="999" name="class_of" value="<?= htmlspecialchars($classroom->class_of ?? '') ?>">
          <?= errorFor('class_of', $errors) ?>
        </div>
        <div class="field-group">
          <div class="field">
            <input id="major_id" name="major_id" value="<?= $major['id'] ?>" hidden readonly>
            <label>Major Full Name *</label>
            <input id="major_full_name" name="major_full_name"
              class="field__input <?= isset($errors['major_full_name']) ? 'field__input--error' : '' ?>"
              value="<?= htmlspecialchars($old_data['major_full_name'] ?? $major['full_name']) ?>"
              data-original="<?= htmlspecialchars($major['full_name']) ?>">
            <?= errorFor('major_full_name', $errors) ?>
          </div>

          <div class="field">
            <label>Major Short Name</label>
            <input id="maj_short" class="field__input" value="<?= htmlspecialchars($major['short_name']) ?>" readonly
              disabled>
          </div>
        </div>
        <?php if ($major['level'] !== 'CĐN' && $specialization): ?>
        <div class="field-group">
          <input id="specialization_id" name="specialization_id" value="<?= $specialization['id'] ?>" hidden readonly>
          <div class="field">
            <label>Specialization Full Name *</label>
            <input id="spec_full_name" name="spec_full_name"
              class="field__input <?= isset($errors['spec_full_name']) ? 'field__input--error' : '' ?>"
              value="<?= htmlspecialchars($old_data['spec_full_name'] ?? $specialization['full_name']) ?>"
              data-original="<?= htmlspecialchars($specialization['full_name']) ?>">
            <?= errorFor('spec_full_name', $errors) ?>
          </div>

          <div class="field">
            <label>Specialization Short Name</label>
            <input id="spec_short" class="field__input" value="<?= htmlspecialchars($specialization['short_name']) ?>"
              readonly disabled>
          </div>
        </div>
        <?php endif; ?>
        <div class="field">
          <label>Letter</label>
          <input id="letter" name="letter"
            class="field__input <?= isset($errors['letter']) ? 'field__input--error' : '' ?>"
            value="<?= htmlspecialchars($old_data['letter'] ?? $classroom->letter) ?>">
          <?= errorFor('letter', $errors) ?>
        </div>
      </div>
    </div>
    <div class="card__footer">
      <button data-modal-trigger="#confirm-modal" id="update-submit-btn" type="button" data-variant="primary"
        data-size="lg" class="w-full btn">Lưu thay đổi</button>
      <button data-modal-trigger="#confirm-modal" id="delete-submit-btn" type="submit"
        formaction="<?= url('admin/classrooms/delete/' . $classroom->id) ?>" data-variant="destructive" data-size="lg"
        class="w-full btn">Xóa</button>
    </div>
  </form>
</div>
<!-- Add confirm modal -->
<div class="modal" id="confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Bạn có chắc</h2>
    <p class="modal__description">
    <p>Nếu bạn chỉnh sửa tên đầy đủ của Ngành hoặc Chuyên ngành.</p>
    <p>Việc này sẽ làm cập nhật tên của TẤT CẢ các lớp học khác đang thuộc Ngành / Chuyên ngành này
      trong hệ thống.</p>
    <p>Bạn có chắc chắn muốn tiếp tục lưu thay đổi không?</p>
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
<script src="<?= url('/public/js/classroom_edit.js') ?>"></script>

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