<?php
$errors = request()->session()->getErrors() ?? [];
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

<?php if ($flash = request()->session()->getFlash("notification")): ?>
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
      <h2 class="title text-2xl font-semibold">
        Thông tin lớp học
        <?= "#" . htmlspecialchars($classroom->short_name) ?>
      </h2>
      <p>Cập nhật thông tin lớp học tại trang này.</p>
    </div>
    <div class="flex gap-2">
      <div>
        <a href="<?= request()->previous(fallback: url('admin/classrooms')) ?>" data-variant="outline" data-size="lg"
          class="btn">
          <i class="fa-solid fa-chevron-left"></i>
          Quay lại
        </a>
      </div>
      <div>
        <button data-modal-trigger="#confirm-modal" id="edit-submit-btn" type="submit" data-variant="primary"
          data-size="lg" class="w-full btn">
          <i class="fa-solid fa-floppy-disk"></i>
          Lưu thay đổi
        </button>
      </div>
      <div>
        <button data-modal-trigger="#delete-confirm-modal" id="delete-btn" data-variant="destructive" type="button"
          data-size="lg" class="btn">
          <i class="fa-solid fa-trash"></i>
          Xóa
        </button>
      </div>
    </div>
  </div>
</div>
<!-- ========== title-wrapper end ========== -->
<form class="detail-layout" id="classroom-edit-form" action="<?= url('admin/classrooms/' . $classroom->id) ?>"
  method="POST">
  <?= csrf_field() ?>
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
                  <option value="<?= htmlspecialchars($major->id) ?>" <?= $classroom->major_id == $major->id ? 'selected' : '' ?>>
                    <?= htmlspecialchars($major->level) ?> -
                    <?= htmlspecialchars($major->short_name) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="field">
              <label class="field__label" for="specialization_id">Chuyên ngành</label>
              <select id="specialization_id" class="field__input" name="specialization_id" disabled>
                <?php if ($classroom->specialization): ?>
                  <option value="<?= htmlspecialchars($classroom->specialization->full_name) ?>" selected>
                    <?= htmlspecialchars($classroom->specialization->full_name) ?>
                  </option>
                <?php else: ?>
                  <option value="">-- Chưa có --</option>
                <?php endif; ?>
              </select>
              <p class="field__description">Chọn ngành học trước để lọc chuyên ngành. Có thể bỏ trống.</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="field" data-field-required data-field-max="2">
                <label class="field__label" for="class_of">Khóa học</label>
                <input id="class_of" class="field__input" type="number" name="class_of" placeholder="VD: 23" min="20"
                  max="99" value="<?= htmlspecialchars($classroom->class_of) ?>">
                <p class="field__description">2 chữ số cuối của năm nhập học (VD: 23 → 2023).</p>
              </div>

              <div class="field" data-field-required data-field-max="1">
                <label class="field__label" for="letter">Ký tự lớp</label>
                <input id="letter" class="field__input" type="text" name="letter" placeholder="VD: A" maxlength="1"
                  value="<?= htmlspecialchars($classroom->letter) ?>" style="text-transform: uppercase;">
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
                <?php if ($classroom->homeroomTeacher): ?>
                  <option value="<?= htmlspecialchars($classroom->homeroomTeacher->id) ?>" selected>
                    <?= htmlspecialchars($classroom->homeroomTeacher->name) ?>
                  </option>
                <?php else: ?>
                  <option value="">-- Chưa có --</option>
                <?php endif; ?>
              </select>
            </div>

          </div>
        </div>
      </fieldset>
    </div>

  </div>
  <div class="detail-layout__sidebar">
    <!-- Metadata -->
    <div class="metadata-card card shadow">
      <div class="card__header">
        Thông tin
      </div>
      <hr class="separator">
      <div class="card__content space-y-4">
        <dl class="flex justify-between">
          <dt>ID</dt>
          <dd>
            <?= htmlspecialchars($classroom->id) ?>
          </dd>
        </dl>
        <hr class="separator">
        <dl class="flex justify-between">
          <dt>Được tạo vào</dt>
          <dd>
            <?= htmlspecialchars($classroom->created_at) ?>
          </dd>
        </dl>
        <hr class="separator">
        <dl class="flex justify-between">
          <dt>Lần cuối cập nhật</dt>
          <dd>
            <?= htmlspecialchars($classroom->updated_at ? $classroom->updated_at : "Không có") ?>
          </dd>
        </dl>
        <hr class="separator">
        <dl class="flex justify-between">
          <dt>Trạng thái dữ liệu</dt>
          <dd>
            <?php if ($classroom->deleted_at): ?>
              <span class="badge" data-variant="destructive">
                Đã xóa
              </span>
            <?php else: ?>
              <span class="badge" data-variant="primary">Hoạt động</span>
            <?php endif; ?>
          </dd>
        </dl>
      </div>
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

<!-- ── Delete Confirm Modal ── -->
<div class="modal" id="delete-confirm-modal" tabindex="-1" data-state="closed">
  <div class="modal__header">
    <h2 class="modal__title">Bạn có chắc</h2>
    <p class="modal__description">Những thao tác này sẽ không thể hoàn tác.</p>
  </div>
  <div class="modal__footer">
    <button data-modal-close data-variant="outline" data-size="lg" class="btn" type="button">Hủy</button>
    <button id="delete-confirm-modal-btn" data-variant="primary" data-size="lg" class="btn" type="button">Chắc
      chắn</button>
  </div>
  <button class="modal__close" type="button" data-modal-close>
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>

<form action="<?= url("admin/classrooms/delete/{$classroom->id}") ?>" method="POST" id="delete-form"><?= csrf_field() ?>
</form>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector('#classroom-edit-form');
    const confirmBtn = document.querySelector('#confirm-modal-btn');
    const deleteConfirmBtn = document.querySelector('#delete-confirm-modal-btn');

    const majorSelect = document.querySelector('#major_id');
    const specSelect = document.querySelector('#specialization_id');
    const teacherSelect = document.querySelector('#homeroom_teacher_id');
    const classOfInput = document.querySelector('#class_of');
    const letterInput = document.querySelector('#letter');
    const shortNameInput = document.querySelector('#short_name');

    const allSpecs = window.__specializations__ ?? [];
    const allMajors = window.__majors__ ?? [];
    const allTeachers = window.__teachers__ ?? [];

    // Lấy ID hiện tại của lớp học từ PHP (để set selected khi load trang Edit)
    const initialSpecId = <?= json_encode($classroom->specialization_id) ?>;
    const initialTeacherId = <?= json_encode($classroom->homeroom_teacher_id) ?>;

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

    // Thêm cờ isInit để biết đây có phải lần chạy đầu tiên khi load trang không
    function updateSpecsAndTeachers(isInit = false) {
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

      // --- XỬ LÝ CHUYÊN NGÀNH ---
      if (filteredSpecs.length === 0) {
        specSelect.disabled = true;
        specSelect.innerHTML = '<option value="">-- Ngành này không có chuyên ngành --</option>';
      } else {
        filteredSpecs.forEach(s => {
          const opt = document.createElement('option');
          opt.value = s.id;
          opt.textContent = `${s.full_name} (${s.short_name})`;

          // Pre-select dữ liệu cũ nếu đang ở lần load đầu tiên
          if (isInit && s.id == initialSpecId) {
            opt.selected = true;
          }

          specSelect.appendChild(opt);
        });
        specSelect.disabled = false;
      }

      // --- XỬ LÝ GIÁO VIÊN ---
      if (filteredTeachers.length === 0) {
        teacherSelect.innerHTML = '<option value="">-- Không có giáo viên phù hợp --</option>';
        teacherSelect.disabled = true;
      } else {
        teacherSelect.innerHTML = '<option value="">-- Chưa phân công --</option>';
        filteredTeachers.forEach(t => {
          const opt = document.createElement('option');
          opt.value = t.id;
          opt.textContent = `${t.full_name} (${t.staff_code}) — ${t.department}`;

          // Pre-select dữ liệu cũ nếu đang ở lần load đầu tiên
          if (isInit && t.id == initialTeacherId) {
            opt.selected = true;
          }

          teacherSelect.appendChild(opt);
        });
        teacherSelect.disabled = false;
      }

      buildShortName();
    }

    // Lắng nghe sự kiện change bình thường (truyền false vì không phải init load)
    majorSelect.addEventListener('change', () => updateSpecsAndTeachers(false));

    classOfInput.addEventListener('input', buildShortName);

    letterInput.addEventListener('input', function () {
      this.value = this.value.replace(/[^a-zA-Z]/g, '').toUpperCase().slice(0, 1);
      buildShortName();
    });

    confirmBtn.addEventListener('click', () => {
      form.submit();
    });

    deleteConfirmBtn.addEventListener('click', () => {
      document.querySelector('#delete-form').submit();
    });

    updateSpecsAndTeachers(true);
  });
</script>
