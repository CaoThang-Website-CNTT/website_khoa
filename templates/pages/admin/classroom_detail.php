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
        <div class="field" data-field-disabled data-field-readonly>
          <label for="short_name">Short Name</label>
          <input id="short_name" class="field__input" type="text" name="short_name"
            value="<?= htmlspecialchars($classroom->short_name ?? '') ?>" disabled>
        </div>

        <div class="field">
          <label for="level">Level *</label>
          <select id="level" class="field__input  <?= isset($errors['level']) ? 'field__input--error' : '' ?>"
            name="level">
            <option value="" disabled hidden <?= is_null($classroom?->level) ? 'selected' : '' ?>>
              -- Chọn bậc học--
            </option>
            <option value="CĐ"
              <?= (mb_strtoupper($classroom?->level, 'UTF-8') === mb_strtoupper(LEVELS['CĐ'], 'UTF-8')) ? 'selected' : '' ?>>
              <?= htmlspecialchars(LEVELS['CĐ']) ?>
            </option>
            <option value="CĐN"
              <?= (mb_strtoupper($classroom?->level, 'UTF-8') === mb_strtoupper(LEVELS['CĐN'], 'UTF-8')) ? 'selected' : '' ?>>
              <?= htmlspecialchars(LEVELS['CĐN']) ?>
            </option>
          </select>
          <?= errorFor('level', $errors) ?>
        </div>

        <div class="field">
          <label for="class_of">Class Of *</label>
          <input id="class_of" class="field__input <?= isset($errors['class_of']) ? 'field__input--error' : '' ?>"
            type="number" min="1" max="999" name="class_of" value="<?= htmlspecialchars($classroom->class_of ?? '') ?>">
          <?= errorFor('class_of', $errors) ?>
        </div>

        <div class="field">
          <label for="profession_id">Profession *</label>
          <select id="profession_id"
            class="field__input <?= isset($errors['profession_id']) ? 'field__input--error' : '' ?>"
            name="profession_id">
            <option value="" disabled hidden <?= is_null($classroom?->profession_id) ? 'selected' : '' ?>>
              -- Chọn Ngành/Nghề --
            </option>
            <?php foreach ($professions as $profession): ?>
            <option value="<?= $profession->id ?>"
              <?= ($classroom?->profession_id == $profession->id) ? 'selected' : '' ?>>
              <?= htmlspecialchars($profession->full_name) ?>
            </option>
            <?php endforeach; ?>
          </select>
          <?= errorFor('profession_id', $errors) ?>
        </div>

        <div class="field">
          <label for="major_id">Major</label>
          <select id="major_id" class="field__input <?= isset($errors['major_id']) ? 'field__input--error' : '' ?>"
            name="profession_id">
            <option value="" disabled hidden <?= is_null($classroom?->major_id) ? 'selected' : '' ?>>
              -- Chọn Chuyên Ngành (Cho hệ Cao đẳng) --
            </option>
            <?php foreach ($majorsOfProfession as $major): ?>
            <option value="<?= $major->id ?>" <?= ($classroom?->major_id == $major->id) ? 'selected' : '' ?>>
              <?= htmlspecialchars($major->full_name ?? 'N/A') ?>
            </option>
            <?php endforeach; ?>
          </select>
          <?= errorFor('major_id', $errors) ?>
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