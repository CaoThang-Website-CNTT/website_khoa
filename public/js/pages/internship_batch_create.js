import { TableManager } from "../table/index.js";

document.addEventListener("DOMContentLoaded", () => {
  const apiBase = window.API_BASE_URL;

  // State
  let state = {
    currentStep: 1,
    batchData: {},
    importedStudents: [], // Dùng để nạp JSON trả về từ backend
    selectedStudents: new Set(),
    teachers: [],
    selectedTeachers: {}, // { teacherId: maxStudents }
  };

  // DOM Elements
  const wizardRoot = document.getElementById("batch-create-step-wizard");
  const steps = Array.from(document.querySelectorAll("[data-step-wizard-panel]"));
  const btnNext = document.querySelectorAll(".btn-next");
  const btnPrev = document.querySelectorAll(".btn-prev");
  const form = document.getElementById("form-create-batch");

  // STEP 2 ELEMENTS
  const fileUploadStudents = document.getElementById("file-upload-students");
  const uploadStatusText = document.getElementById("upload-status-text");
  const selectedStudentsCount = document.getElementById(
    "selected-students-count",
  );
  const checkAllStudents = document.getElementById("check-all-students");
  const wrapperTableStudents = document.getElementById(
    "wrapper-table-students-import",
  );

  let tableManager = null;

  const teachersContainer = document.getElementById("teachers-container");
  const btnSubmit = document.getElementById("btn-submit-batch");
  const quotaSummaryBox = document.getElementById("quota-summary-box");
  const totalCapacityText = document.getElementById("total-capacity-text");
  const quotaStatusIcon = document.getElementById("quota-status-icon");

  const searchTeachersInput = document.getElementById("search-teachers");
  const btnEqualizeQuotas = document.getElementById("btn-equalize-quotas");

  const updateCounter = () => {
    selectedStudentsCount.textContent = `Đã chọn: ${state.selectedStudents.size} SV`;
  };

  // WIZARD VALIDATION
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

    const formData = new FormData(form);
    state.batchData = {
      title: formData.get("title"),
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

  const wizard = new window.StepWizard({
    root: wizardRoot,
    panels: steps,
    initialIndex: 0,
    beforeChange: (nextIndex, currentIndex) => {
      if (nextIndex <= currentIndex) return true;
      if (currentIndex === 0 && !validateStep1()) return false;
      if (currentIndex === 1 && !validateStep2()) return false;
      return true;
    },
  });

  wizard.onChange((index) => {
    state.currentStep = index + 1;

    if (state.currentStep === 3) {
      renderTeachers();
    }
  });

  btnNext.forEach((btn) => {
    btn.addEventListener("click", () => {
      wizard.next();
    });
  });

  btnPrev.forEach((btn) => {
    btn.addEventListener("click", () => {
      wizard.back();
    });
  });

  // --- STEP 2: CHỌN SINH VIÊN QUA IMPORT XLSX ---

  if (fileUploadStudents) {
    fileUploadStudents.addEventListener("change", async (e) => {
      const file = e.target.files[0];
      if (!file) return;

      const formData = new FormData();
      formData.append("file_import", file);

      // Reset state
      state.importedStudents = [];
      state.selectedStudents.clear();
      updateCounter();
      uploadStatusText.textContent = "Đang xử lý file...";

      try {
        const response = await fetch(`${apiBase}/parse-import`, {
          method: "POST",
          body: formData,
        });

        const result = await response.json();

        if (!response.ok) {
          throw new Error(result.message || "Lỗi khi phân tích file.");
        }

        const data = result.data || [];

        if (data.length === 0) {
          throw new Error("Không tìm thấy dữ liệu sinh viên trong file.");
        }

        // Tự động fake 1 thuộc tính _id duy nhất (hoặc lấy mssv làm id) cho TableManager
        data.forEach((student, index) => {
          student._id = student.student_code;
          // Mặc định chọn tất cả
          state.selectedStudents.add(student._id);
        });

        state.importedStudents = data;
        uploadStatusText.innerHTML = `<span class="text-success"><i class="fa-solid fa-check mr-1"></i> Đã tải ${data.length} sinh viên</span>`;

        initOrReloadTableManager();
      } catch (error) {
        uploadStatusText.innerHTML = `<span><i class="fa-solid fa-circle-exclamation mr-1"></i> ${error.message}</span>`;
        if (window.toast) toast.error("Lỗi Import", error.message);

        wrapperTableStudents.setAttribute("data-state", "closed");
      }
    });
  }

  const initOrReloadTableManager = () => {
    wrapperTableStudents.removeAttribute("data-state");

    const tableId = "table-students-import";
    let inst = TableManager.get(tableId);

    if (!inst) {
      TableManager.init();
      inst = TableManager.get(tableId);
    }

    if (inst && !inst.root.dataset.selectionListenerBound) {
      // Lắng nghe sự kiện thay đổi checkbox từ TableManager
      inst.root.addEventListener("tm:selection-change", (e) => {
        state.selectedStudents = new Set(e.detail.rowSelection.map(String));
        updateCounter();
      });
      inst.root.dataset.selectionListenerBound = "true";
    }

    if (inst) {
      // Cập nhật dữ liệu vào bảng (mode client)
      inst.setData([...state.importedStudents]);

      // Mặc định chọn tất cả khi mới import
      if (
        state.selectedStudents.size === 0 &&
        state.importedStudents.length > 0
      ) {
        state.importedStudents.forEach((s) =>
          state.selectedStudents.add(String(s._id)),
        );
      }

      // Đồng bộ vào instance của TableManager
      inst.setRowSelection(state.selectedStudents);

      updateCounter(); // Cập nhật badge
    }
  };

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
    } else {
      quotaSummaryBox.classList.add("error");
      quotaSummaryBox.classList.remove("success");
      quotaStatusIcon.classList.add("hidden");
    }
    btnSubmit.removeAttribute("disabled");

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
            <i class="fa-solid fa-users-rectangle"></i>
            ${deptName}
          </div>
          <div class="flex items-center gap-4">
            <label class="flex items-center gap-2 text-sm font-medium">
              <input type="checkbox" class="dept-select-all" ${isAllSelected ? "checked" : ""}>
              Chọn tất cả
            </label>
            <button type="button" class="btn-toggle-dept btn" data-variant="outline-alt" data-size="sm">
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
  const doSubmitBatch = async () => {
    const payload = {
      ...state.batchData,
      students: state.importedStudents.filter((s) =>
        state.selectedStudents.has(String(s._id)),
      ),
      supervisors: Object.keys(state.selectedTeachers).map((tId) => ({
        teacher_id: parseInt(tId),
        max_students: state.selectedTeachers[tId],
      })),
    };

    try {
      if (typeof ModalHandler !== "undefined" && ModalHandler.instance) {
        ModalHandler.instance.close();
      }

      btnSubmit.setAttribute("disabled", "disabled");
      btnSubmit.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...`;
      if (document.getElementById("skip-step3-confirm-btn")) {
        document
          .getElementById("skip-step3-confirm-btn")
          .setAttribute("disabled", "disabled");
        document.getElementById("skip-step3-confirm-btn").innerHTML =
          `<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...`;
      }

      const res = await fetch(apiBase, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });

      const result = await res.json();
      const data = result.data;

      if (!res.ok) {
        let errorMsg = result.message || "Lỗi khi tạo đợt";
        if (data && data.errors) {
          errorMsg = Object.values(data.errors)[0];
        } else if (data && data.message) {
          errorMsg = data.message;
        }
        throw new Error(errorMsg);
      }

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
      if (document.getElementById("skip-step3-confirm-btn")) {
        document
          .getElementById("skip-step3-confirm-btn")
          .removeAttribute("disabled");
        document.getElementById("skip-step3-confirm-btn").innerHTML = `Đồng ý`;
      }
    }
  };

  btnSubmit.addEventListener("click", () => {
    let totalCapacity = 0;
    Object.values(state.selectedTeachers).forEach((quota) => {
      totalCapacity += quota;
    });
    const totalStudents = state.selectedStudents.size;

    if (
      totalCapacity < totalStudents ||
      Object.keys(state.selectedTeachers).length === 0
    ) {
      const msg =
        Object.keys(state.selectedTeachers).length === 0
          ? "Bạn chưa chọn giảng viên nào. Bạn có chắc muốn tạo đợt thực tập này và phân công sau không?"
          : "Hạn mức giảng viên hiện tại chưa đủ cho số lượng sinh viên. Bạn có chắc muốn tạo đợt thực tập này và phân công sau không?";

      const confirmMsgEl = document.getElementById("skip-step3-confirm-msg");
      if (confirmMsgEl) confirmMsgEl.textContent = msg;

      if (typeof ModalHandler !== "undefined") {
        ModalHandler.instance.open("#skip-step3-confirm-modal");
      } else if (window.ModalHandler) {
        window.ModalHandler.instance.open("#skip-step3-confirm-modal");
      } else {
        if (window.confirm(msg)) {
          doSubmitBatch();
        }
      }
    } else {
      doSubmitBatch();
    }
  });

  const confirmSkipBtn = document.getElementById("skip-step3-confirm-btn");
  if (confirmSkipBtn) {
    confirmSkipBtn.addEventListener("click", () => {
      doSubmitBatch();
    });
  }

  // Init
  wizard
    .renderProgress(wizardRoot, window.BATCH_CREATE_WIZARD_STEPS || [])
    .init();
  loadTeachers();
});
