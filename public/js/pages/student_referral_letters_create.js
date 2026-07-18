document.addEventListener("DOMContentLoaded", () => {
  // Logic form đăng ký sinh viên
  const btnAddStudent = document.getElementById("rl_btnAddStudent");
  const studentsContainer = document.getElementById("rl_studentsContainer");
  const rosterSelect = document.getElementById("rl_rosterStudent");
  let studentCount = 0;
  const MAX_STUDENTS = 15;

  function updateAddButtonState() {
    if (btnAddStudent && studentsContainer) {
      if (studentsContainer.children.length >= MAX_STUDENTS) {
        btnAddStudent.disabled = true;
        btnAddStudent.title = "Đã đạt tối đa 15 sinh viên";
      } else {
        btnAddStudent.disabled = false;
        btnAddStudent.title = "Thêm sinh viên";
      }
    }
  }

  function addStudentRow(data = {}, isPrimary = false) {
    if (!studentsContainer) return;
    if (!isPrimary) {
      const id = Number(rosterSelect?.value || 0);
      const member = window.__studentReferralLetters__?.roster?.find(item => Number(item.batchStudentId) === id);
      if (!member) return;
      if (studentsContainer.querySelector(`input[name="student_batch_student_id[]"][value="${id}"]`)) return;
      data = { ...data, ...member };
      rosterSelect.value = '';
    }
    
    if (!isPrimary && studentsContainer.children.length >= MAX_STUDENTS) {
      if (window.toast) window.toast.error("Lỗi", `Chỉ được thêm tối đa ${MAX_STUDENTS} sinh viên.`);
      return;
    }

    const idx = studentCount++;
    const row = document.createElement("div");
    row.className = "p-3 border rounded-md flex gap-4 items-start relative";
    row.innerHTML = `
      <input type="hidden" name="student_batch_student_id[]" value="${data.batchStudentId || ''}">
      <div class="flex-1 grid grid-cols-2 gap-3">
        <div class="field" data-field-required>
          <label class="field__label mb-1 text-xs">Họ và tên ${isPrimary ? "(Người đại diện)" : ""}</label>
          <input type="text" name="student_name[]" class="field__input py-2 px-2 text-sm" value="${data.fullName || ""}" required>
        </div>
        <div class="field" data-field-required>
          <label class="field__label mb-1 text-xs">Ngành/Nghề</label>
          <select name="student_major[]" class="field__input text-sm" required>
            <option value="">-- Chọn ngành/nghề --</option>
            ${window.__studentReferralLetters__.majors.map(m => `<option value="${m}" ${m === data.majorName ? 'selected' : ''}>${m}</option>`).join('')}
          </select>
        </div>
        <div class="field" data-field-required>
          <label class="field__label mb-1 text-xs">Ngày sinh</label>
          <input type="date" name="student_dob[]" class="field__input py-2 px-2 text-sm" value="${data.dob || ""}" required>
        </div>
        <div class="field" data-field-required>
          <label class="field__label mb-1 text-xs">Địa chỉ hiện tại</label>
          <textarea name="student_address[]" class="field__input py-2 px-2 text-sm" required>${data.address || ""}</textarea>
        </div>
      </div>
      ${
        !isPrimary
          ? `
      <button type="button" class="btn btn-remove-student absolute" style="top: 6px; right: 12px;" data-variant="destructive" data-size="sm" title="Xóa">
        <i class="fa-solid fa-trash"></i>
      </button>
      `
          : ""
      }
    `;

    if (!isPrimary) {
      row.querySelector(".btn-remove-student").addEventListener("click", () => {
        row.remove();
        updateAddButtonState();
      });
    }

    studentsContainer.appendChild(row);
    updateAddButtonState();
  }

  if (
    studentsContainer &&
    window.__studentReferralLetters__ &&
    window.__studentReferralLetters__.currentStudent
  ) {
    addStudentRow(window.__studentReferralLetters__.currentStudent, true);
  }

  if (btnAddStudent) {
    btnAddStudent.addEventListener("click", () => {
      addStudentRow({
        majorName:
          window.__studentReferralLetters__?.currentStudent?.majorName ||
          "Công nghệ thông tin",
      });
    });
  }
});
