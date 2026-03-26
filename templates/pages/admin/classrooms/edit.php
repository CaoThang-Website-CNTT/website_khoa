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
        Classroom
        <span class="font-bold">#<?= htmlspecialchars($classroom->id ?? '') ?></span>
      </h6>
    </div>
    <div class="card__description">
      This is classroom detail form
    </div>
  </div>

  <div class="card__content">
    <?php
    include BASE_PATH . '/templates/components/flash_alert.php';
    ?>
    <form id="classroom-edit-form" action="<?= url('admin/classrooms/' . $classroom->id) ?>" method="POST">

      <div class="field-group">

        <div class="field" data-field-readonly>
          <label for="major">Major</label>
          <input id="major" class="field__input" type="text" name="major"
            value="<?= htmlspecialchars($classroom->major->full_name ?? '') ?>" readonly>

          <input type="hidden" id="major_id" name="major_id"
            value="<?= htmlspecialchars($classroom->major_id ?? '') ?>">
          <input type="hidden" id="major_short" name="major_short"
            value="<?= htmlspecialchars($classroom->major->short_name ?? '') ?>">
          <input type="hidden" id="major_level" name="major_level"
            value="<?= htmlspecialchars($classroom->major->level ?? '') ?>">
        </div>

        <div class="field">
          <label for="specialization_id">Specialization</label>
          <select id="specialization_id" class="field__input" name="specialization_id">
            <option value="" data-short="" <?= is_null($classroom?->specialization_id) ? 'selected' : '' ?>>
              -- Không phân chuyên ngành --
            </option>
            <?php foreach ($specializations as $spec): ?>
              <option value="<?= $spec->id ?>" data-short="<?= htmlspecialchars($spec->short_name ?? '') ?>"
                <?= ($classroom?->specialization_id == $spec->id) ? 'selected' : '' ?>>
                <?= htmlspecialchars($spec->full_name ?? '') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field" data-field-required>
          <label for="class_of">Starting Year (Khóa học)</label>
          <input id="class_of" class="field__input" type="number" name="class_of"
            value="<?= htmlspecialchars($classroom->class_of ?? '') ?>" required>
        </div>

        <div class="field">
          <label for="letter">Class Letter (Phân lớp)</label>
          <input id="letter" class="field__input" type="text" name="letter"
            value="<?= htmlspecialchars($classroom->letter ?? '') ?>">
        </div>

        <div class="field">
          <label for="short_name">Class Code (Mã Lớp)</label>
          <input id="short_name" class="field__input" type="text" name="short_name"
            value="<?= htmlspecialchars($classroom->short_name ?? '') ?>">
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

<div class="modal" id="delete-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Xác nhận xóa</h2>
    <p class="modal__description">
      Lớp học <strong><?= htmlspecialchars($classroom->short_name ?? '') ?></strong> sẽ bị xóa và không thể khôi phục.
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

<form id="classroom-delete-form" method="POST" action="<?= url('admin/classrooms/delete/' . $classroom->id) ?>"
  style="display:none">
</form>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const editForm = document.getElementById('classroom-edit-form');
    const deleteForm = document.getElementById('classroom-delete-form');
    const confirmBtn = document.getElementById('confirm-modal-btn');
    const deleteBtn = document.getElementById('delete-confirm-btn');

    const majorShortInput = document.getElementById('major_short');
    const majorLevelInput = document.getElementById('major_level');
    const specSelect = document.getElementById('specialization_id');
    const classOfInput = document.getElementById('class_of');
    const letterInput = document.getElementById('letter');
    const shortNameInput = document.getElementById('short_name');

    function generateClassCode() {
      let level = majorLevelInput.value.trim();
      const major = majorShortInput.value.trim();
      let year = classOfInput.value.trim();
      let letter = letterInput.value.trim().toUpperCase();

      let shortSpecializationName = major;
      if (specSelect.value) {
        const selectedOption = specSelect.options[specSelect.selectedIndex];
        if (selectedOption) {
          shortSpecializationName = selectedOption.getAttribute('data-short') || '';
        }
      }

      if (!level || !year || !shortSpecializationName) {
        shortNameInput.value = '';
        return;
      }

      if (year.length === 2) {
        year = '20' + year;
      }
      else if (year.length !== 4) {
        shortNameInput.value = '';
        return;
      }

      letter = letter.replace(/[^A-Z]/g, '').slice(0, 1);

      shortNameInput.value = `${level}${shortSpecializationName}${year}${letter}`;
    }

    generateClassCode();

    const fields = [specSelect, classOfInput, letterInput];
    fields.forEach(field => {
      if (field) {
        field.addEventListener('input', generateClassCode);
        field.addEventListener('change', generateClassCode);
      }
    });

    confirmBtn.addEventListener('click', () => editForm.submit());
    deleteBtn.addEventListener('click', () => deleteForm.submit());
  });
</script>