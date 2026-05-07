document.addEventListener("DOMContentLoaded", () => {
  const apiBase = window.API_BASE_URL;

  // State
  let state = {
    currentStep: 1,
    batchData: {},
    selectedClassrooms: new Set(),
    eligibleStudents: [],
    selectedStudents: new Set(),
    teachers: [],
    selectedTeachers: {}, // { teacherId: maxStudents }
  };

  // DOM Elements
  const steps = document.querySelectorAll(".wizard-step");
  const stepItems = document.querySelectorAll(".step-item");
  const btnNext = document.querySelectorAll(".btn-next");
  const btnPrev = document.querySelectorAll(".btn-prev");
  const form = document.getElementById("form-create-batch");
  const loader = document.getElementById("wizard-loader");

  const classroomsContainer = document.getElementById("classrooms-container");
  const searchClassroomsInput = document.getElementById("search-classrooms");
  const searchStudentsInput = document.getElementById("search-students");
  const studentsTbody = document.getElementById("students-tbody");
  const selectedStudentsCount = document.getElementById(
    "selected-students-count",
  );
  const checkAllStudents = document.getElementById("check-all-students");

  const btnOpenImportModal = document.getElementById("btn-open-import-modal");
  const btnProcessImport = document.getElementById("btn-process-import");
  const importTextarea = document.getElementById("import-student-ids-textarea");
  const importResultsContainer = document.getElementById(
    "import-results-container",
  );

  const teachersContainer = document.getElementById("teachers-container");
  const btnSubmit = document.getElementById("btn-submit-batch");
  const quotaSummaryBox = document.getElementById("quota-summary-box");
  const totalCapacityText = document.getElementById("total-capacity-text");
  const quotaStatusIcon = document.getElementById("quota-status-icon");

  const searchTeachersInput = document.getElementById("search-teachers");
  const btnEqualizeQuotas = document.getElementById("btn-equalize-quotas");

  // Helpers
  const showLoader = () => loader.classList.remove("hidden");
  const hideLoader = () => loader.classList.add("hidden");

  const updateCounter = () => {
    const importCount = state.eligibleStudents.filter(
      (s) => s.source === "bulk_import" && state.selectedStudents.has(s.id),
    ).length;
    const dbCount = state.selectedStudents.size - importCount;
    selectedStudentsCount.textContent = `Đã chọn: ${state.selectedStudents.size} SV (${dbCount} từ Database, ${importCount} từ Import)`;
  };

  // --- WIZARD NAVIGATION ---
  const updateWizardUI = () => {
    steps.forEach((step, index) => {
      if (index + 1 === state.currentStep) {
        step.classList.remove("hidden");
      } else {
        step.classList.add("hidden");
      }
    });

    stepItems.forEach((item, index) => {
      const stepNum = index + 1;
      item.classList.remove("active", "completed");
      if (stepNum === state.currentStep) {
        item.classList.add("active");
      } else if (stepNum < state.currentStep) {
        item.classList.add("completed");
      }
    });
  };

  const validateStep1 = () => {
    if (!form.reportValidity()) return false;
    const start = new Date(form.start_at.value);
    const end = new Date(form.end_at.value);
    if (end <= start) {
      if (window.toast)
        toast.error("Lỗi", "Ngày kết thúc phải lớn hơn ngày bắt đầu.");
      else alert("Ngày kết thúc phải lớn hơn ngày bắt đầu.");
      return false;
    }

    const oldClassOf = state.batchData.class_of;
    const oldLevel = state.batchData.level;

    const formData = new FormData(form);
    const newClassOf = parseInt(formData.get("class_of"));
    const newLevel = formData.get("level");

    if (
      oldClassOf &&
      oldLevel &&
      (oldClassOf !== newClassOf || oldLevel !== newLevel)
    ) {
      // Reset Step 2 selection if Step 1 filter changed
      state.selectedClassrooms.clear();
      state.eligibleStudents = state.eligibleStudents.filter(
        (s) => s.source === "bulk_import",
      );
      state.selectedStudents = new Set(state.eligibleStudents.map((s) => s.id));

      if (window.toast)
        toast.info(
          "Thông báo",
          "Niên khóa/Bậc học thay đổi, danh sách lớp đã được đặt lại.",
        );
    }

    state.batchData = {
      title: formData.get("title"),
      class_of: newClassOf,
      level: newLevel,
      description: formData.get("description"),
      start_at: formData.get("start_at"),
      end_at: formData.get("end_at"),
    };
    return true;
  };

  const validateStep2 = () => {
    if (state.selectedStudents.size === 0) {
      if (window.toast)
        toast.error("Lỗi", "Vui lòng chọn ít nhất 1 sinh viên tham gia đợt.");
      else alert("Vui lòng chọn ít nhất 1 sinh viên tham gia đợt.");
      return false;
    }
    return true;
  };

  btnNext.forEach((btn) => {
    btn.addEventListener("click", () => {
      if (state.currentStep === 1 && !validateStep1()) return;
      if (state.currentStep === 2 && !validateStep2()) return;

      state.currentStep++;
      if (state.currentStep === 2) {
        renderClassrooms(allClassroomsData);
      }
      if (state.currentStep === 3) {
        renderTeachers();
      }
      updateWizardUI();
    });
  });

  btnPrev.forEach((btn) => {
    btn.addEventListener("click", () => {
      state.currentStep--;
      updateWizardUI();
    });
  });

  stepItems.forEach((item) => {
    item.addEventListener("click", () => {
      const targetStep = parseInt(item.getAttribute("data-step"));
      if (targetStep < state.currentStep) {
        state.currentStep = targetStep;
        updateWizardUI();
      }
    });
  });

  // --- STEP 2: CHỌN SINH VIÊN ---
  let allClassroomsData = [];
  const loadClassrooms = async () => {
    try {
      const res = await fetch(`${apiBase}/classrooms`);
      if (!res.ok) throw new Error("Không thể tải danh sách lớp");
      const result = await res.json();
      allClassroomsData = result.data || [];
      renderClassrooms(allClassroomsData);
    } catch (err) {
      classroomsContainer.innerHTML = `<span class="text-danger">${err.message}</span>`;
    }
  };

  const renderClassrooms = (classrooms) => {
    classroomsContainer.innerHTML = "";

    // Lọc theo thông tin ở Step 1
    let filtered = classrooms;
    if (state.batchData.class_of && state.batchData.level) {
      filtered = classrooms.filter(
        (cls) =>
          cls.class_of == state.batchData.class_of &&
          cls.level == state.batchData.level,
      );
    }

    if (!filtered || filtered.length === 0) {
      classroomsContainer.innerHTML = `<span class="text-sm">Không tìm thấy lớp học phù hợp với Khóa ${state.batchData.class_of || ""} và Hệ ${state.batchData.level || ""}.</span>`;
      return;
    }

    filtered.forEach((cls) => {
      const badge = document.createElement("span");
      badge.className = `classroom-badge ${state.selectedClassrooms.has(cls.id) ? "selected" : ""}`;
      badge.textContent = cls.name;
      badge.dataset.id = cls.id;

      badge.addEventListener("click", () => toggleClassroom(badge, cls.id));
      classroomsContainer.appendChild(badge);
    });
  };

  // Filter Classrooms (by Search Input)
  if (searchClassroomsInput) {
    searchClassroomsInput.addEventListener("input", (e) => {
      const term = e.target.value.toLowerCase().trim();

      // Lấy danh sách đã qua filter Step 1 trước
      let step1Filtered = allClassroomsData;
      if (state.batchData.class_of && state.batchData.level) {
        step1Filtered = allClassroomsData.filter(
          (cls) =>
            cls.class_of == state.batchData.class_of &&
            cls.level == state.batchData.level,
        );
      }

      const searched = step1Filtered.filter((cls) =>
        cls.name.toLowerCase().includes(term),
      );
      renderClassrooms(searched); // Lưu ý: renderClassrooms cũng có logic filter Step 1, nên truyền searched vào sẽ bị filter 2 lần.
      // Tốt nhất là renderClassrooms không nên tự filter nếu đã nhận input là searched.
    });
  }

  // Batching API Requests
  let queuedClassroomIds = new Set();
  let fetchQueueTimeout = null;

  const toggleClassroom = (badge, clsId) => {
    badge.classList.toggle("selected");
    if (badge.classList.contains("selected")) {
      state.selectedClassrooms.add(clsId);
      queuedClassroomIds.add(clsId);

      clearTimeout(fetchQueueTimeout);
      fetchQueueTimeout = setTimeout(processClassroomQueue, 500); // Debounce 500ms
    } else {
      state.selectedClassrooms.delete(clsId);
      queuedClassroomIds.delete(clsId);
      removeStudentsOfClassroom(clsId);
    }
  };

  const processClassroomQueue = async () => {
    if (queuedClassroomIds.size === 0) return;

    const idsToFetch = Array.from(queuedClassroomIds);
    queuedClassroomIds.clear();

    showLoader();
    try {
      const query = idsToFetch.map((id) => `classroom_ids[]=${id}`).join("&");
      const res = await fetch(`${apiBase}/students-eligible?${query}`);
      if (!res.ok) throw new Error("Không thể tải danh sách sinh viên");
      const result = await res.json();
      const students = result.data || [];

      students.forEach((s) => (s.source = "db_select"));

      state.eligibleStudents = [...state.eligibleStudents, ...students];
      students.forEach((s) => state.selectedStudents.add(s.id));

      renderStudentsTable();
    } catch (err) {
      if (window.toast) toast.error("Lỗi", err.message);
    } finally {
      hideLoader();
    }
  };

  const removeStudentsOfClassroom = (classroomId) => {
    const studentsToRemove = state.eligibleStudents.filter(
      (s) => s.classroom_id == classroomId,
    );
    studentsToRemove.forEach((s) => state.selectedStudents.delete(s.id));
    state.eligibleStudents = state.eligibleStudents.filter(
      (s) => s.classroom_id != classroomId,
    );
    renderStudentsTable();
  };

  // Filter Students
  if (searchStudentsInput) {
    searchStudentsInput.addEventListener("input", () => {
      renderStudentsTable();
    });
  }

  const renderStudentsTable = () => {
    studentsTbody.innerHTML = "";

    const searchTerm = searchStudentsInput
      ? searchStudentsInput.value.toLowerCase().trim()
      : "";
    let filteredStudents = state.eligibleStudents;

    if (searchTerm) {
      filteredStudents = filteredStudents.filter(
        (s) =>
          (s.student_id && s.student_id.toLowerCase().includes(searchTerm)) ||
          (s.full_name && s.full_name.toLowerCase().includes(searchTerm)),
      );
    }

    if (filteredStudents.length === 0) {
      studentsTbody.innerHTML = `<tr><td colspan="5" class="text-center py-4">Không có dữ liệu sinh viên.</td></tr>`;
      checkAllStudents.checked = false;
      updateCounter();
      return;
    }

    // Grouping
    const groups = {};
    filteredStudents.forEach((s) => {
      const groupKey = s.source === "bulk_import" ? "import" : s.classroom_id;
      if (!groups[groupKey]) groups[groupKey] = [];
      groups[groupKey].push(s);
    });

    let allChecked = true;

    // Render bulk import first
    if (groups["import"]) {
      renderGroup("Danh sách Import thủ công", groups["import"]);
      delete groups["import"];
    }

    // Render other classrooms
    Object.keys(groups).forEach((classroomId) => {
      const badge = document.querySelector(
        `.classroom-badge[data-id="${classroomId}"]`,
      );
      const className = badge ? badge.textContent : `Lớp ID: ${classroomId}`;
      renderGroup(className, groups[classroomId]);
    });

    function renderGroup(groupName, students) {
      const headerTr = document.createElement("tr");
      headerTr.innerHTML = `
        <td colspan="10" class="td-classroom font-medium text-sm py-2 ml-4">
          <i class="fa-solid fa-chalkboard-user mr-4"></i>
          ${groupName} 
          <span class="text-xs font-normal ml-2">(${students.length} SV)</span>
        </td>
      `;
      studentsTbody.appendChild(headerTr);

      students.forEach((student) => {
        const isChecked = state.selectedStudents.has(student.id);
        if (!isChecked) allChecked = false;

        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td><input type="checkbox" class="student-checkbox" value="${student.id}" ${isChecked ? "checked" : ""}></td>
          <td>${student.student_id}</td>
          <td>${student.full_name}</td>
          <td>${student.phone}</td>
          <td>${student.source === "bulk_import" ? `<span class="badge" data-variant="secondary">${student.classroom_name}</span>` : groupName}</td>
        `;
        studentsTbody.appendChild(tr);
      });
    }

    checkAllStudents.checked = allChecked;
    updateCounter();
    bindCheckboxEvents();
  };

  const bindCheckboxEvents = () => {
    document.querySelectorAll(".student-checkbox").forEach((cb) => {
      cb.addEventListener("change", (e) => {
        const id = parseInt(e.target.value);
        if (e.target.checked) state.selectedStudents.add(id);
        else state.selectedStudents.delete(id);

        updateCounter();

        const allBoxes = document.querySelectorAll(".student-checkbox");
        const checkedBoxes = document.querySelectorAll(
          ".student-checkbox:checked",
        );
        checkAllStudents.checked = allBoxes.length === checkedBoxes.length;
      });
    });
  };

  checkAllStudents.addEventListener("change", (e) => {
    const isChecked = e.target.checked;
    document.querySelectorAll(".student-checkbox").forEach((cb) => {
      cb.checked = isChecked;
      const id = parseInt(cb.value);
      if (isChecked) state.selectedStudents.add(id);
      else state.selectedStudents.delete(id);
    });
    updateCounter();
  });

  // --- MODAL BULK IMPORT ---
  if (btnOpenImportModal) {
    btnOpenImportModal.addEventListener("click", () => {
      importResultsContainer.classList.add("hidden");
      importTextarea.value = "";
    });
  }

  if (btnProcessImport) {
    btnProcessImport.addEventListener("click", async () => {
      const rawText = importTextarea.value;
      // Lọc bằng Regex: Cắt theo xuống dòng, bỏ khoảng trắng dư, chỉ lấy các dòng có nội dung

      const studentIds = rawText
        .split(/\r?\n/)
        .map((s) => s.trim())
        .filter((s) => s.length > 0);

      if (studentIds.length === 0) {
        if (window.toast)
          toast.warning("Cảnh báo", "Vui lòng nhập ít nhất 1 MSSV.");
        return;
      }

      btnProcessImport.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...`;
      btnProcessImport.setAttribute("disabled", "disabled");

      try {
        const res = await fetch(`${apiBase}/validate-students-bulk`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ student_ids: studentIds }),
        });

        if (!res.ok) throw new Error("Lỗi khi kiểm tra danh sách");
        const result = await res.json();
        const data = result.data;

        // Render kết quả
        importResultsContainer.classList.remove("hidden");

        const validList = document.getElementById("import-valid-list");
        const invalidList = document.getElementById("import-invalid-list");
        document.getElementById("import-valid-count").textContent =
          data.valid.length;
        document.getElementById("import-invalid-count").textContent =
          data.invalid.length;

        validList.innerHTML = "";
        invalidList.innerHTML = "";

        data.valid.forEach((s) => {
          validList.innerHTML += `<li>${s.student_id} - ${s.full_name}</li>`;
          // Thêm vào state
          // Kiểm tra xem đã có trong eligible chưa để tránh trùng
          if (!state.eligibleStudents.some((es) => es.id === s.id)) {
            s.source = "bulk_import";
            state.eligibleStudents.push(s);
          }
          state.selectedStudents.add(s.id);
        });

        data.invalid.forEach((err) => {
          invalidList.innerHTML += `<li><b>${err.student_id}</b>: ${err.reason}</li>`;
        });

        renderStudentsTable();
      } catch (err) {
        if (window.toast) toast.error("Lỗi", err.message);
      } finally {
        btnProcessImport.innerHTML = `Kiểm tra & Thêm`;
        btnProcessImport.removeAttribute("disabled");
      }
    });
  }

  // --- STEP 3: CHỈ ĐỊNH GIẢNG VIÊN ---
  const loadTeachers = async () => {
    try {
      const res = await fetch(`${apiBase}/teachers-active`);
      if (!res.ok) throw new Error("Không thể tải danh sách GV");
      const result = await res.json();
      state.teachers = result.data || [];
    } catch (err) {
      console.error(err);
    }
  };

  const calculateDefaultQuota = () => {
    const totalStudents = state.selectedStudents.size;
    const selectedTeacherIds = Object.keys(state.selectedTeachers);
    if (selectedTeacherIds.length === 0) return 0;
    return Math.ceil(totalStudents / selectedTeacherIds.length);
  };

  const validateQuota = () => {
    let totalCapacity = 0;
    Object.values(state.selectedTeachers).forEach((quota) => {
      totalCapacity += quota;
    });

    const totalStudents = state.selectedStudents.size;
    totalCapacityText.textContent = `${totalStudents} / ${totalCapacity}`;

    if (totalCapacity >= totalStudents && totalStudents > 0) {
      quotaSummaryBox.classList.remove("error");
      quotaSummaryBox.classList.add("success");
      quotaStatusIcon.classList.remove("hidden");
      btnSubmit.removeAttribute("disabled");
    } else {
      quotaSummaryBox.classList.add("error");
      quotaSummaryBox.classList.remove("success");
      quotaStatusIcon.classList.add("hidden");
      btnSubmit.setAttribute("disabled", "disabled");
    }

    // Cập nhật progress bar cho từng GV (nếu đang ở Step 3)
    if (state.currentStep === 3) {
      document.querySelectorAll(".teacher-card.selected").forEach((card) => {
        const tId = card.querySelector(".quota-input").dataset.id;
        const quota = state.selectedTeachers[tId] || 0;
        const percentage =
          totalStudents > 0 ? (quota / totalStudents) * 100 : 0;

        const progressBar = card.querySelector(".quota-progress-bar");
        const percentageText = card.querySelector(".quota-percentage");

        if (progressBar) {
          progressBar.style.width = `${Math.min(percentage, 100)}%`;
          progressBar.classList.remove("warning", "danger");
          if (percentage > 50) progressBar.classList.add("danger");
          else if (percentage > 30) progressBar.classList.add("warning");
        }
        if (percentageText)
          percentageText.textContent = `${Math.round(percentage)}%`;
      });
    }
  };

  const renderTeachers = () => {
    teachersContainer.innerHTML = "";

    const searchTerm = searchTeachersInput
      ? searchTeachersInput.value.toLowerCase().trim()
      : "";
    let filteredTeachers = state.teachers;

    if (searchTerm) {
      filteredTeachers = state.teachers.filter(
        (t) =>
          t.full_name.toLowerCase().includes(searchTerm) ||
          (t.email && t.email.toLowerCase().includes(searchTerm)),
      );
    }

    if (filteredTeachers.length === 0) {
      teachersContainer.innerHTML = `<span class="text-sm">Không tìm thấy giảng viên phù hợp.</span>`;
      return;
    }

    // Nhóm theo Bộ môn
    const groups = {};
    filteredTeachers.forEach((t) => {
      const dept = t.department || "Khác";
      if (!groups[dept]) groups[dept] = [];
      groups[dept].push(t);
    });

    const defaultQuota = calculateDefaultQuota();

    Object.keys(groups)
      .sort()
      .forEach((deptName) => {
        const deptGroup = document.createElement("div");
        deptGroup.className = "department-group";

        const teachersInDept = groups[deptName];
        const selectedInDept = teachersInDept.filter(
          (t) => state.selectedTeachers[t.teacher_id] !== undefined,
        );
        const isAllSelected =
          selectedInDept.length === teachersInDept.length &&
          teachersInDept.length > 0;

        deptGroup.innerHTML = `
        <div class="department-header">
          <div class="department-title">
            <i class="fa-solid fa-users-rectangle text-primary"></i>
            ${deptName}
          </div>
          <div class="flex items-center gap-4">
            <label class="flex items-center gap-2 text-sm font-medium">
              <input type="checkbox" class="dept-select-all" ${isAllSelected ? "checked" : ""}>
              Chọn tất cả
            </label>
            <button type="button" class="btn-toggle-dept btn btn--icon btn--sm" data-variant="ghost">
              <i class="fa-solid fa-chevron-up"></i>
            </button>
          </div>
        </div>
        <div class="teacher-grid"></div>
      `;

        const grid = deptGroup.querySelector(".teacher-grid");
        const toggleBtn = deptGroup.querySelector(".btn-toggle-dept");

        toggleBtn.addEventListener("click", () => {
          const isHidden = grid.classList.toggle("hidden");
          toggleBtn.innerHTML = isHidden
            ? '<i class="fa-solid fa-chevron-down"></i>'
            : '<i class="fa-solid fa-chevron-up"></i>';
        });

        teachersInDept.forEach((teacher) => {
          const isSelected =
            state.selectedTeachers[teacher.teacher_id] !== undefined;
          const currentQuota = isSelected
            ? state.selectedTeachers[teacher.teacher_id]
            : 0;
          const totalStudents = state.selectedStudents.size;
          const percentage =
            totalStudents > 0 ? (currentQuota / totalStudents) * 100 : 0;

          const card = document.createElement("div");
          card.className = `teacher-card ${isSelected ? "selected" : ""}`;

          card.innerHTML = `
          <div class="flex gap-2 items-center justify-between">
            <div class="teacher-info card-clickable-area mb-2">
              <input type="checkbox" class="teacher-checkbox" value="${teacher.teacher_id}" ${isSelected ? "checked" : ""}>
              <div>
                <div class="font-medium">${teacher.full_name}</div>
                <div class="text-xs">${teacher.email || "Không có email"}</div>
              </div>
            </div>
            <div class="quota-controls">
              <div class="quota-input-wrapper">
                <p class="text-xs font-medium text-center">Hạn mức:</p>
                <input type="number" class="quota-input" data-id="${teacher.teacher_id}" value="${currentQuota}" min="1" ${!isSelected ? "disabled" : ""}>
              </div>
            </div>
          </div>
          <div class="quota-progress-wrapper flex items-center justify-center gap-2">
            <div class="quota-progress-container">
              <div class="quota-progress-bar ${percentage > 50 ? "danger" : percentage > 30 ? "warning" : ""}" style="width: ${Math.min(percentage, 100)}%"></div>
            </div>
            <div class="quota-percentage">${Math.round(percentage)}%</div>
          </div>
        `;

          grid.appendChild(card);

          const checkbox = card.querySelector(".teacher-checkbox");
          const clickableArea = card.querySelector(".card-clickable-area");
          const quotaInput = card.querySelector(".quota-input");

          const toggleTeacher = (forceState = null) => {
            const newState =
              forceState !== null ? forceState : !checkbox.checked;
            checkbox.checked = newState;

            if (newState) {
              card.classList.add("selected");
              quotaInput.removeAttribute("disabled");
              state.selectedTeachers[teacher.teacher_id] =
                parseInt(quotaInput.value) || defaultQuota;
            } else {
              card.classList.remove("selected");
              quotaInput.setAttribute("disabled", "disabled");
              delete state.selectedTeachers[teacher.teacher_id];
            }
            validateQuota();
          };

          clickableArea.addEventListener("click", (e) => {
            if (e.target !== checkbox) toggleTeacher();
          });

          checkbox.addEventListener("change", (e) => {
            const isChecked = e.target.checked;
            checkbox.checked = !isChecked; // revert to let toggleTeacher handle it
            toggleTeacher(isChecked);
          });

          quotaInput.addEventListener("input", (e) => {
            if (checkbox.checked) {
              state.selectedTeachers[teacher.teacher_id] =
                parseInt(e.target.value) || 0;
              validateQuota();
            }
          });
        });

        teachersContainer.appendChild(deptGroup);

        // Event cho checkbox "Chọn tất cả tổ"
        const deptCheckAll = deptGroup.querySelector(".dept-select-all");
        deptCheckAll.addEventListener("change", (e) => {
          const isChecked = e.target.checked;
          deptGroup.querySelectorAll(".teacher-checkbox").forEach((cb) => {
            if (cb.checked !== isChecked) {
              // Trigger toggle logic
              const card = cb.closest(".teacher-card");
              const quotaInput = card.querySelector(".quota-input");
              const teacherId = cb.value;

              cb.checked = isChecked;
              if (isChecked) {
                card.classList.add("selected");
                quotaInput.removeAttribute("disabled");
                state.selectedTeachers[teacherId] =
                  parseInt(quotaInput.value) || defaultQuota;
              } else {
                card.classList.remove("selected");
                quotaInput.setAttribute("disabled", "disabled");
                delete state.selectedTeachers[teacherId];
              }
            }
          });
          validateQuota();
        });
      });

    validateQuota();
  };

  // Search Teachers Event
  if (searchTeachersInput) {
    searchTeachersInput.addEventListener("input", () => {
      renderTeachers();
    });
  }

  // Equalize Quotas Event
  if (btnEqualizeQuotas) {
    btnEqualizeQuotas.addEventListener("click", () => {
      const selectedIds = Object.keys(state.selectedTeachers);
      if (selectedIds.length === 0) {
        if (window.toast)
          toast.warning("Cảnh báo", "Vui lòng chọn ít nhất 1 giảng viên.");
        return;
      }

      const totalStudents = state.selectedStudents.size;
      const eqQuota = Math.ceil(totalStudents / selectedIds.length);

      selectedIds.forEach((id) => {
        state.selectedTeachers[id] = eqQuota;
      });

      // Re-render only Step 3 if currently viewing it
      if (state.currentStep === 3) renderTeachers();
      validateQuota();

      if (window.toast)
        toast.success("Thành công", `Đã chia đều hạn mức (${eqQuota} SV/GV).`);
    });
  }

  // --- SUBMIT ---
  btnSubmit.addEventListener("click", async () => {
    const payload = {
      ...state.batchData,
      classroom_ids: Array.from(state.selectedClassrooms),
      student_ids: Array.from(state.selectedStudents),
      supervisors: Object.keys(state.selectedTeachers).map((tId) => ({
        teacher_id: parseInt(tId),
        max_students: state.selectedTeachers[tId],
      })),
    };

    try {
      showLoader();
      btnSubmit.setAttribute("disabled", "disabled");
      btnSubmit.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...`;

      const res = await fetch(apiBase, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });

      const result = await res.json();
      const data = result.data;

      if (!res.ok)
        throw new Error(
          data.message || data.errors
            ? Object.values(data.errors)[0]
            : "Lỗi khi tạo đợt",
        );

      if (window.toast) toast.success("Thành công", data.message);

      setTimeout(() => {
        window.location.href = window.REDIRECT_URL.replace(
          "{id}",
          data.batch_id,
        );
      }, 1500);
    } catch (err) {
      if (window.toast) toast.error("Lỗi", err.message);
      else alert(err.message);
      btnSubmit.removeAttribute("disabled");
      btnSubmit.innerHTML = `<i class="fa-solid fa-check mr-2"></i> Hoàn tất tạo đợt`;
    } finally {
      hideLoader();
    }
  });

  // Init
  updateWizardUI();
  loadClassrooms();
  loadTeachers();
});
