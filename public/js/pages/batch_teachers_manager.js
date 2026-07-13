document.addEventListener('DOMContentLoaded', () => {
  const tableContainer = document.querySelector('[data-tm="batch_teachers_table"]');
  if (!tableContainer) return;

  // Event Delegation: Click-to-edit quota
  tableContainer.addEventListener('click', (e) => {
    // Nút Sửa hạn mức
    const editBtn = e.target.closest('.btn-quota-edit');
    if (editBtn) {
      const cell = editBtn.closest('.quota-cell');
      openEditor(cell);
      return;
    }

    // Nút Lưu
    const saveBtn = e.target.closest('.btn-quota-save');
    if (saveBtn) {
      const cell = saveBtn.closest('.quota-cell');
      saveQuota(cell);
      return;
    }

    // Nút Hủy
    const cancelBtn = e.target.closest('.btn-quota-cancel');
    if (cancelBtn) {
      const cell = cancelBtn.closest('.quota-cell');
      closeEditor(cell);
      return;
    }

    // Nút Xóa
    const deleteBtn = e.target.closest('.btn-delete-teacher');
    if (deleteBtn) {
      const teacherId = deleteBtn.dataset.teacherId;
      const teacherName = deleteBtn.dataset.teacherName;
      document.getElementById('delete-teacher-id').value = teacherId;
      
      const desc = document.getElementById('delete-teacher-desc');
      if (desc) desc.textContent = `Bạn có chắc chắn muốn xóa giảng viên ${teacherName} khỏi đợt thực tập không?`;

      if (typeof ModalHandler !== 'undefined') {
        ModalHandler.instance.open('#modal-delete-teacher');
      } else {
        document.getElementById('modal-delete-teacher').setAttribute('data-state', 'open');
      }
      return;
    }
  });

  // Xử lý phím Enter / Escape trong input
  tableContainer.addEventListener('keydown', (e) => {
    if (!e.target.classList.contains('quota-cell__input')) return;

    const cell = e.target.closest('.quota-cell');
    if (e.key === 'Enter') {
      e.preventDefault();
      saveQuota(cell);
    } else if (e.key === 'Escape') {
      closeEditor(cell);
    }
  });

  /**
   * Mở chế độ chỉnh sửa quota
   */
  function openEditor(cell) {
    const display = cell.querySelector('.quota-cell__display');
    const editor = cell.querySelector('.quota-cell__editor');
    const input = cell.querySelector('.quota-cell__input');
    const currentValue = cell.querySelector('.quota-cell__value').textContent.trim();

    display.classList.add('hidden');
    editor.classList.remove('hidden');
    input.value = currentValue;
    input.focus();
    input.select();
  }

  /**
   * Đóng chế độ chỉnh sửa, khôi phục giá trị cũ
   */
  function closeEditor(cell) {
    const display = cell.querySelector('.quota-cell__display');
    const editor = cell.querySelector('.quota-cell__editor');

    editor.classList.add('hidden');
    display.classList.remove('hidden');
  }

  /**
   * Gọi API cập nhật hạn mức
   */
  async function saveQuota(cell) {
    const teacherId = cell.dataset.teacherId;
    const input = cell.querySelector('.quota-cell__input');
    const newQuota = parseInt(input.value, 10);

    if (isNaN(newQuota) || newQuota < 0) {
      if (window.toast) {
        window.toast.error('Lỗi', 'Hạn mức phải là số nguyên không âm.');
      }
      return;
    }

    const saveBtn = cell.querySelector('.btn-quota-save');
    const cancelBtn = cell.querySelector('.btn-quota-cancel');
    saveBtn.disabled = true;
    cancelBtn.disabled = true;
    input.disabled = true;

    try {
      const res = await fetch(
        `${window.API_BASE_URL}/internship/batches/${BATCH_ID}/management/supervisors/${teacherId}`,
        {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.CSRF_TOKEN || ''
          },
          body: JSON.stringify({ max_students: newQuota })
        }
      );

      const result = await res.json();

      if (!res.ok) {
        throw new Error(result.message || 'Có lỗi xảy ra.');
      }

      // Cập nhật giá trị hiển thị
      cell.querySelector('.quota-cell__value').textContent = newQuota;
      closeEditor(cell);

      if (window.toast) {
        window.toast.success('Thành công', result.message || 'Cập nhật hạn mức thành công.');
      }
    } catch (err) {
      if (window.toast) {
        window.toast.error('Lỗi', err.message);
      }
    } finally {
      saveBtn.disabled = false;
      cancelBtn.disabled = false;
      input.disabled = false;
    }
  }
  // Xóa giảng viên
  const btnSubmitDelete = document.getElementById("btn-submit-delete-teacher");
  if (btnSubmitDelete) {
    btnSubmitDelete.addEventListener("click", async () => {
      const teacherId = document.getElementById("delete-teacher-id").value;
      if (!teacherId) return;

      btnSubmitDelete.disabled = true;
      try {
        const res = await fetch(
          `${window.API_BASE_URL}/internship/batches/${BATCH_ID}/management/supervisors/${teacherId}`,
          {
            method: "DELETE",
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": window.CSRF_TOKEN || "",
            },
          },
        );

        const result = await res.json();
        if (!res.ok)
          throw new Error(result.message || "Lỗi khi xóa giảng viên.");

        if (window.toast)
          window.toast.success(
            "Thành công",
            result.message || "Xóa giảng viên thành công.",
          );
        setTimeout(() => window.location.reload(), 500);
      } catch (err) {
        if (window.toast) window.toast.error("Lỗi", err.message);
        btnSubmitDelete.disabled = false;
      }
    });
  }

  // Thêm giảng viên
  const searchInput = document.getElementById("search-teacher-input");
  const searchResults = document.getElementById("search-teacher-results");
  const selectedContainer = document.getElementById(
    "selected-teachers-container",
  );
  const btnSubmitAdd = document.getElementById("btn-submit-add-teacher");

  let searchTimeout = null;
  let selectedTeachers = [];

  function renderSelectedTeachers() {
    if (!selectedContainer) return;
    selectedContainer.innerHTML = "";
    if (selectedTeachers.length === 0) {
      if (btnSubmitAdd) btnSubmitAdd.disabled = true;
      return;
    }
    if (btnSubmitAdd) btnSubmitAdd.disabled = false;

    selectedTeachers.forEach((t) => {
      const el = document.createElement("div");
      el.className = "teacher-preview";
      el.innerHTML = `
        <div class="teacher-preview__info">
          <div class="teacher-preview__name">${t.name}</div>
          <div class="teacher-preview__dept">${t.dept}</div>
        </div>
        <div class="teacher-preview__actions">
          <input type="number" class="teacher-preview__quota field__input" min="1" max="999" value="${t.max_students}" data-id="${t.id}" title="Hạn mức sinh viên">
          <button type="button" class="teacher-preview__clear btn-remove-selected" data-id="${t.id}" title="Bỏ chọn">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
      `;
      selectedContainer.appendChild(el);
    });
  }

  if (selectedContainer) {
    selectedContainer.addEventListener("input", (e) => {
      if (e.target.classList.contains("teacher-preview__quota")) {
        const id = e.target.dataset.id;
        const val = parseInt(e.target.value, 10) || 15;
        const teacher = selectedTeachers.find((t) => t.id == id);
        if (teacher) teacher.max_students = val;
      }
    });

    selectedContainer.addEventListener("click", (e) => {
      const removeBtn = e.target.closest(".btn-remove-selected");
      if (removeBtn) {
        const id = removeBtn.dataset.id;
        selectedTeachers = selectedTeachers.filter((t) => t.id != id);
        renderSelectedTeachers();
      }
    });
  }

  if (searchInput) {
    searchInput.addEventListener("input", (e) => {
      const query = e.target.value.trim();
      clearTimeout(searchTimeout);

      if (query.length < 2) {
        searchResults.classList.add("hidden");
        return;
      }

      searchTimeout = setTimeout(async () => {
        try {
          const res = await fetch(
            `${window.API_BASE_URL}/internship/batches/${BATCH_ID}/management/search-eligible-teachers?q=${encodeURIComponent(query)}`,
          );
          if (!res.ok) return;
          const responsePayload = await res.json();
          renderSearchResults(responsePayload.data || []);
        } catch (err) {
          console.error("Lỗi tìm kiếm giảng viên:", err);
        }
      }, 300);
    });

    // Ẩn kết quả khi click ra ngoài
    document.addEventListener("click", (e) => {
      if (
        !searchInput.contains(e.target) &&
        !searchResults.contains(e.target)
      ) {
        searchResults.classList.add("hidden");
      }
    });
  }

  function renderSearchResults(teachers) {
    if (!teachers || teachers.length === 0) {
      searchResults.innerHTML =
        '<div class="teacher-search__empty">Không tìm thấy giảng viên phù hợp.</div>';
    } else {
      searchResults.innerHTML = teachers
        .map(
          (t) => `
        <div class="teacher-search__item" 
             data-id="${t.id}" 
             data-name="${t.full_name}" 
             data-dept="${t.department_name || ""}">
          <div class="teacher-search__item-name">${t.degree ? t.degree + ". " : ""}${t.full_name}</div>
          <div class="teacher-search__item-dept">${t.department_name || "Chưa cập nhật"}</div>
        </div>
      `,
        )
        .join("");

      searchResults
        .querySelectorAll(".teacher-search__item")
        .forEach((item) => {
          item.addEventListener("click", () => {
            selectTeacher(
              item.dataset.id,
              item.dataset.name,
              item.dataset.dept,
            );
          });
        });
    }
    searchResults.classList.remove("hidden");
  }

  function selectTeacher(id, name, dept) {
    if (!selectedTeachers.find((t) => t.id == id)) {
      selectedTeachers.push({ id, name, dept, max_students: 15 });
    }
    searchInput.value = "";
    searchResults.classList.add("hidden");
    searchInput.focus();
    renderSelectedTeachers();
  }

  if (btnSubmitAdd) {
    btnSubmitAdd.addEventListener("click", async () => {
      if (selectedTeachers.length === 0) return;

      btnSubmitAdd.disabled = true;
      const payload = selectedTeachers.map((t) => ({
        teacher_id: t.id,
        max_students: t.max_students,
      }));

      try {
        const res = await fetch(
          `${window.API_BASE_URL}/internship/batches/${BATCH_ID}/management/supervisors`,
          {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": window.CSRF_TOKEN || "",
            },
            body: JSON.stringify({ teachers: payload }),
          },
        );

        const result = await res.json();
        if (!res.ok)
          throw new Error(result.message || "Lỗi khi thêm giảng viên.");

        if (window.toast)
          window.toast.success(
            "Thành công",
            result.message || "Thêm giảng viên thành công.",
          );
        setTimeout(() => window.location.reload(), 500);
      } catch (err) {
        if (window.toast) window.toast.error("Lỗi", err.message);
        btnSubmitAdd.disabled = false;
      }
    });
  }
});
