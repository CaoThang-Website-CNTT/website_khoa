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
      <h6>Create new Classroom</h6>
    </div>
    <div class="card__description">
      This is creating new Classroom form
    </div>
  </div>
  <div class="card__content">
    <?php
    include BASE_PATH . '/templates/components/flash_alert.php';
    ?>
    <form id="classroom-add-form" method="POST" action="<?= url('admin/classrooms') ?>">
      <div class="field-group">

        <div class="field" data-field-required>
          <label for="major_id">Major</label>
          <select id="major_id" class="field__input" name="major_id" required>
            <option value="" selected disabled>
              -- Chọn ngành học --
            </option>
            <?php foreach ($majors as $major): ?>
              <option value="<?= htmlspecialchars($major->id) ?>">
                <?= htmlspecialchars($major->full_name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field" data-field-required>
          <label for="class_of">Starting Year (Khóa học)</label>
          <input id="class_of" class="field__input" type="number" name="class_of" value="" placeholder="VD: 2021, 23"
            required>
        </div>

        <div class="field">
          <label for="letter">Class Letter (Phân lớp)</label>
          <input id="letter" class="field__input" type="text" name="letter" value="" placeholder="VD: A, B, C">
        </div>

      </div>
    </form>
  </div>
  <div class="card__footer">
    <button data-modal-trigger="#confirm-modal" id="create-submit-btn" type="submit" data-variant="primary"
      data-size="lg" class="w-full btn">Thêm</button>
  </div>
</div>

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
    const form = document.querySelector('#classroom-add-form');
    const createBtn = document.querySelector('#create-submit-btn');
    const confirmBtn = document.querySelector('#confirm-modal-btn');

    const modal = new Modal("#confirm-modal");
    const closeTriggers = document.querySelectorAll('[data-modal-close]');

    // Confirm Btn Event Listener
    confirmBtn.addEventListener('click', () => form.submit());
  });
</script>