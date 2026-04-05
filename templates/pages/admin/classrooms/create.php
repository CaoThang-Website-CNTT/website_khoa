<?php
$errors = request()->session()->getErrors()() ?? [];
$old_input = request()->session()->getOldInputs() ?? [];
?>
<script>
  window.__errors__ = <?= json_encode($errors) ?>;
  window.__old__ = <?= json_encode($old_input) ?>;
  window.__specializations__ = <?= json_encode(
    array_map(fn($s) => [
      'id' => $s->id,
      'major_id' => $s->major_id,
      'full_name' => $s->full_name,
      'short_name' => $s->short_name,
    ], $specializations)
  ) ?>;
  window.__majors__ = <?= json_encode(
    array_map(fn($m) => [
      'id' => $m->id,
      'short_name' => $m->short_name,
      'level' => $m->level,
    ], $majors)
  ) ?>;
  window.__teachers__ = <?= json_encode(
    array_map(fn($t) => [
      'id' => $t->id,
      'full_name' => $t->full_name,
      'staff_code' => $t->staff_code,
      'department' => $t->department,
    ], $teachers)
  ) ?>;
</script>

<?php if ($flash = request()->session()->getFlash()): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      toast.<?= ($flash['type']) ?>(
        '<?= $flash['title'] ?>',
        '<?= $flash['desc'] ?>'
      );
    });
  </script>
<?php endif; ?>

<!-- ========== title-wrapper start ========== -->
<div class="title-wrapper">
  <div class="flex justify-between items-center">
    <div class="col-6 col-md-6">
      <h2 class="title text-2xl font-semibold">Thêm lớp học mới</h2>
      <p>Điền thông tin lớp học mới vào các trường dưới đây</p>
    </div>
    <div class="flex gap-2">
      <div>
        <a href="<?= request()->previous(fallback: 'admin/classrooms') ?>" data-variant="outline" data-size="lg"
          class="btn">
          <i class="fa-solid fa-chevron-left"></i>
          Quay lại
        </a>
      </div>
      <div>
        <button data-modal-trigger="#confirm-modal" id="create-submit-btn" type="button" data-variant="primary"
          data-size="lg" class="w-full btn">
          <i class="fa-solid fa-floppy-disk"></i>
          Thêm
        </button>
      </div>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->
<form class="detail-layout" id="classroom-add-form" action="<?= url('admin/classrooms') ?>" method="POST">
  <div class="detail-layout__main">
    <div class="card shadow">
      <fieldset class="field__set">
        <div class="card__header">
          <legend class="field__legend">Thông tin lớp học</legend>
          <p class="field__description">
            Vui lòng điền đầy đủ thông tin. Những trường có dấu * là bắt buộc.
          </p>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <div class="field-group">

            <div class="field" data-field-required>
              <label class="field__label" for="major_id">Ngành học</label>
              <select id="major_id" class="field__input" name="major_id">
                <option value="">-- Chọn ngành học --</option>
                <?php foreach ($majors as $major): ?>
                  <option value="<?= htmlspecialchars($major->id) ?>"
                    data-short="<?= htmlspecialchars($major->short_name) ?>"
                    data-level="<?= htmlspecialchars($major->level) ?>">
                    <?= htmlspecialchars($major->full_name) ?> (<?= htmlspecialchars($major->short_name) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="field">
              <label class="field__label" for="specialization_id">Chuyên ngành</label>
              <select id="specialization_id" class="field__input" name="specialization_id" disabled>
                <option value="">-- Chọn ngành trước --</option>
              </select>
              <p class="field__description">Chọn ngành học trước để lọc chuyên ngành. Có thể bỏ trống.</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="field" data-field-required data-field-max="2">
                <label class="field__label" for="class_of">Khóa học</label>
                <input id="class_of" class="field__input" type="number" name="class_of" placeholder="VD: 23" min="20"
                  max="99" value="">
                <p class="field__description">2 chữ số cuối của năm nhập học (VD: 23 → 2023).</p>
              </div>

              <div class="field" data-field-required data-field-max="1">
                <label class="field__label" for="letter">Ký tự lớp</label>
                <input id="letter" class="field__input" type="text" name="letter" placeholder="VD: A" maxlength="1"
                  value="" style="text-transform: uppercase;">
                <p class="field__description">Một ký tự phân biệt lớp (A, B, C…).</p>
              </div>
            </div>

            <div class="field" data-field-readonly>
              <label class="field__label" for="short_name">Mã lớp</label>
              <input id="short_name" class="field__input" type="text" name="short_name" placeholder="Tự động tạo"
                value="" readonly>
              <p class="field__description">
                Mã lớp được tự động tạo theo định dạng:
                <strong>{Hệ}{Ngành}{Khóa}{Ký tự}</strong> — VD: CĐ CNTT 23A
              </p>
            </div>

          </div>
        </div>
      </fieldset>
    </div>
    <div class="card shadow">
      <fieldset class="field__set">
        <div class="card__header">
          <legend class="field__legend">Giáo viên chủ nhiệm</legend>
        </div>
        <hr class="separator" />
        <div class="card__content">
          <div class="field-group">

            <div class="field" data-field-required>
              <label class="field__label" for="homeroom_teacher_id">Giáo viên chủ nhiệm</label>
              <select id="homeroom_teacher_id" class="field__input" name="homeroom_teacher_id">
                <option value="">-- Chưa phân công --</option>
                <?php foreach ($teachers as $teacher): ?>
                  <option value="<?= htmlspecialchars($teacher->id) ?>">
                    <?= htmlspecialchars($teacher->full_name) ?>
                    (<?= htmlspecialchars($teacher->staff_code) ?>)
                    — <?= htmlspecialchars($teacher->department) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <p class="field__description">Có thể phân công sau khi tạo lớp.</p>
            </div>

          </div>
        </div>
      </fieldset>
    </div>

  </div>
</form>

<!-- ── Confirm Modal ── -->
<div class="modal" id="confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Bạn có chắc</h2>
    <p class="modal__description">Những thao tác này sẽ không thể hoàn tác.</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
    <button id="confirm-modal-btn" data-variant="primary" data-size="lg" class="btn" type="button">Chắc chắn</button>
  </div>
  <button class="modal__close" type="button" data-modal-close>
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector('#classroom-add-form');
    const confirmBtn = document.querySelector('#confirm-modal-btn');

    const majorSelect = document.querySelector('#major_id');
    const specSelect = document.querySelector('#specialization_id');
    const teacherSelect = document.querySelector('#homeroom_teacher_id');
    const classOfInput = document.querySelector('#class_of');
    const letterInput = document.querySelector('#letter');
    const shortNameInput = document.querySelector('#short_name');

    const allSpecs = window.__specializations__ ?? [];
    const allMajors = window.__majors__ ?? [];
    const allTeachers = window.__teachers__ ?? [];

    function getSelectedMajor() {
      const id = parseInt(majorSelect.value);
      return allMajors.find(m => m.id === id) ?? null;
    }

    function buildShortName() {
      const major = getSelectedMajor();
      const classOf = classOfInput.value.trim();
      const letter = letterInput.value.trim().toUpperCase();

      if (!major || !classOf || !letter) {
        shortNameInput.value = '';
        return;
      }

      shortNameInput.value = `${major.level}${major.short_name}${classOf}${letter}`;
    }

    function updateSpecsAndTeachers() {
      const majorId = parseInt(majorSelect.value);

      // Reset options
      specSelect.innerHTML = '<option value="">-- Chưa có --</option>';

      if (!majorId) {
        specSelect.disabled = true;
        buildShortName();
        return;
      }

      const selectedMajor = getSelectedMajor();
      const filteredSpecs = allSpecs.filter(s => s.major_id === majorId);
      const filteredTeachers = allTeachers.filter(t => t.department === selectedMajor?.short_name);

      if (filteredSpecs.length === 0) {
        specSelect.disabled = true;
        specSelect.innerHTML = '<option value="">-- Ngành này không có chuyên ngành --</option>';
      } else {
        filteredSpecs.forEach(s => {
          const opt = document.createElement('option');
          opt.value = s.id;
          opt.textContent = `${s.full_name} (${s.short_name})`;
          specSelect.appendChild(opt);
        });
        specSelect.disabled = false;
      }

      if (filteredTeachers.length === 0) {
        teacherSelect.innerHTML = '<option value="">-- Không có giáo viên phù hợp --</option>';
        teacherSelect.disabled = true;
      } else {
        teacherSelect.innerHTML = '<option value="">-- Chưa phân công --</option>';
        filteredTeachers.forEach(t => {
          const opt = document.createElement('option');
          opt.value = t.id;
          opt.textContent = `${t.full_name} (${t.staff_code}) — ${t.department}`;
          teacherSelect.appendChild(opt);
        });
        teacherSelect.disabled = false;
      }

      buildShortName();
    }

    majorSelect.addEventListener('change', updateSpecsAndTeachers);

    classOfInput.addEventListener('input', buildShortName);

    letterInput.addEventListener('input', function () {
      this.value = this.value.replace(/[^a-zA-Z]/g, '').toUpperCase().slice(0, 1);
      buildShortName();
    });

    teacherSelect.addEventListener('change', function () {
      const teacherId = parseInt(this.value);
      if (!teacherId) {
        return;
      }
    });

    confirmBtn.addEventListener('click', () => {
      form.submit();
    });

    updateSpecsAndTeachers();
  });
</script>