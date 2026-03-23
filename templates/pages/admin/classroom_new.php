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
      <h6>Create new Classroom</h6>
    </div>
    <div class="card__description">
      This is creating new Classroom form
    </div>
  </div>
  <div class="card__content">
    <form id="classroom-add-form" method="POST" action="<?= url('admin/classrooms/store') ?>">
      <div class="field-group">
        <div class="field">
          <label for="short_name">Short Name (auto generate)</label>
          <input id="short_name_display" class="field__input" type="text" name="short_name_display" value="" readonly
            disabled>
          <input id="short_name" class="field__input" type="text" name="short_name" value="" hidden>
          <?= errorFor('short_name', $errors) ?>
        </div>

        <div class="field">
          <label for="level">Level *</label>
          <select id="level" class="field__input  <?= isset($errors['level']) ? 'field__input--error' : '' ?>"
            name="level">
            <option value="" disabled selected>
              -- Chọn bậc học--
            </option>
            <option value="CĐ">
              <?= htmlspecialchars(LEVELS['CĐ']) ?>
            </option>
            <option value="CĐN">
              <?= htmlspecialchars(LEVELS['CĐN']) ?>
            </option>
          </select>
          <?= errorFor('level', $errors) ?>
        </div>

        <div class="field">
          <label for="class_of">Class Of *</label>
          <input id="class_of" class="field__input <?= isset($errors['class_of']) ? 'field__input--error' : '' ?>"
            type="number" min="1" max="999" name="class_of" value="<?= date('y') ?>">
          <?= errorFor('class_of', $errors) ?>
        </div>

        <div class="field">
          <label for="major_id">Major *</label>
          <div class="flex gap-4">
            <select id="major_id"
              class="field__input flex-1 <?= isset($errors['major_id']) ? 'field__input--error' : '' ?>"
              name="major_id">
              <option value="" disabled hidden selected>-- Chọn Ngành --</option>
            </select>
            <button type="button" id="btn-add-major" data-modal-trigger="#add-major-modal" class="btn"
              data-variant="outline" disabled>+</button>
          </div>
          <?= errorFor('major_id', $errors) ?>
        </div>

        <div class="field">
          <label for="specialization_id">Specialization</label>
          <div class="flex gap-4">
            <select id="specialization_id" disabled
              class="field__input flex-1 <?= isset($errors['specialization_id']) ? 'field__input--error' : '' ?>"
              name="specialization_id">
              <option value="" disabled hidden selected>-- Chọn Chuyên Ngành --</option>
            </select>
            <button type="button" id="btn-add-specialization" data-modal-trigger="#add-specialization-modal" class="btn"
              data-variant="outline" disabled>+</button>
          </div>
          <?= errorFor('specialization_id', $errors) ?>
        </div>

        <div class="field">
          <label for="letter">Letter</label>
          <input id="letter" class="field__input <?= isset($errors['letter']) ? 'field__input--error' : '' ?>"
            type="text" name="letter" pattern="[A-Z]+" title="Chỉ được nhập chữ cái A-Z" maxlength="1">
          <?= errorFor('letter', $errors) ?>
        </div>
      </div>
    </form>
  </div>
  <div class="card__footer">
    <button data-modal-trigger="#confirm-modal" id="create-submit-btn" type="submit" data-variant="primary"
      data-size="lg" class="w-full btn">Thêm</button>
  </div>
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

<!-- add major modal -->
<div class="modal" id="add-major-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Thêm Ngành Mới</h2>
  </div>
  <div class="modal__content field-group">
    <div class="field">
      <label>Tên Ngành Đầy Đủ</label>
      <input type="text" id="new_major_full_name" class="field__input">
    </div>
    <div class="field">
      <label>Tên Ngành Viết Tắt</label>
      <input type="text" id="new_major_short_name" class="field__input" placeholder="VD: CNTT">
    </div>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" class="btn" type="button">Hủy</button>
    <button id="submit-new-major" data-variant="primary" class="btn" type="button">Lưu</button>
  </div>
</div>
<!-- add specialization modal -->
<div class="modal" id="add-specialization-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Thêm Chuyên Ngành Mới</h2>
  </div>
  <div class="modal__content field-group">
    <div class="field">
      <label>Tên Chuyên Ngành Đầy Đủ</label>
      <input type="text" id="new_spec_full_name" class="field__input">
    </div>
    <div class="field">
      <label>Tên Chuyên Ngành Viết Tắt</label>
      <input type="text" id="new_spec_short_name" class="field__input" placeholder="VD: PM">
    </div>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" class="btn" type="button">Hủy</button>
    <button id="submit-new-specialization" data-variant="primary" class="btn" type="button">Lưu</button>
  </div>
</div>
<script src="<?= url('/public/js/classroom_new.js') ?>"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector('#classroom-add-form');
  const createBtn = document.querySelector('#create-submit-btn');
  const confirmBtn = document.querySelector('#confirm-modal-btn');

  const modal = new Modal("#confirm-modal");
  const closeTriggers = document.querySelectorAll('[data-modal-close]');

  let pendingActionUrl = '';

  // Create Btn Event Listener
  createBtn.addEventListener('click', function(e) {
    e.preventDefault();
    pendingActionUrl = form.getAttribute('action');
  });

  // Confirm Btn Event Listener
  confirmBtn.addEventListener('click', function() {
    form.action = pendingActionUrl;
    form.submit();
  });
});
</script>