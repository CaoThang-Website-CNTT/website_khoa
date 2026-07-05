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
        teacherSelect.disabled = true;
        teacherSelect.innerHTML = '<option value="">-- Vui lòng chọn ngành trước --</option>';
        buildShortName();
        return;
      }

      const selectedMajor = getSelectedMajor();
      const filteredSpecs = allSpecs.filter(s => s.major_id === majorId);
      const filteredTeachers = allTeachers.filter(t => t.department_id === selectedMajor?.department_id);

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
          opt.textContent = `${t.full_name} - ${t.email}`;
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
