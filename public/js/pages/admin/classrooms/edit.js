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
    const initialSpecId = window.__classroomEdit__?.initialSpecId ?? null;
    const initialTeacherId = window.__classroomEdit__?.initialTeacherId ?? null;

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
          opt.textContent = `${t.full_name} - ${t.department}`;

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
