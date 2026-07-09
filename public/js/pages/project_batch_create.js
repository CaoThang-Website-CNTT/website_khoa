import { TableManager } from "../table/index.js";

document.addEventListener("DOMContentLoaded", () => {
  const apiBase = window.API_BASE_URL;

  // State
  let state = {
    currentStep: 1,
    batchData: {},
    teachers: [],
    selectedTeachers: {}, // { teacherId: { min_students, max_students } }
  };

  // DOM Elements
  const wizardRoot = document.getElementById("batch-create-step-wizard");
  const steps = Array.from(
    document.querySelectorAll("[data-step-wizard-panel]"),
  );
  const btnNext = document.querySelectorAll(".btn-next");
  const btnPrev = document.querySelectorAll(".btn-prev");
  const form = document.getElementById("form-create-batch");

  const teachersContainer = document.getElementById("teachers-container");
  const btnSubmit = document.getElementById("btn-submit-batch");
  const searchTeachersInput = document.getElementById("search-teachers");

  // WIZARD VALIDATION
  const validateStep1 = () => {
    if (!form.reportValidity()) return false;
    const propStart = new Date(form.topic_proposal_start.value);
    const propEnd = new Date(form.topic_proposal_end.value);
    if (propEnd <= propStart) {
      if (window.toast)
        toast.error("Lỗi", "Ngày kết thúc đề xuất phải lớn hơn ngày bắt đầu.");
      else alert("Ngày kết thúc đề xuất phải lớn hơn ngày bắt đầu.");
      return false;
    }

    if (form.registration_start.value && form.registration_end.value) {
      const regStart = new Date(form.registration_start.value);
      const regEnd = new Date(form.registration_end.value);

      if (regEnd <= regStart) {
        if (window.toast)
          toast.error(
            "Lỗi",
            "Ngày kết thúc đăng ký phải lớn hơn ngày bắt đầu.",
          );
        else alert("Ngày kết thúc đăng ký phải lớn hơn ngày bắt đầu.");
        return false;
      }

      if (propEnd > regStart) {
        if (window.toast)
          toast.error(
            "Lỗi",
            "Thời gian đề xuất phải kết thúc trước khi đăng ký.",
          );
        else alert("Thời gian đề xuất phải kết thúc trước khi đăng ký.");
        return false;
      }
    }

    const formData = new FormData(form);
    state.batchData = {
      title: formData.get("title"),
      description: formData.get("description"),
      class_of: formData.get("class_of"),
      max_aspirations: formData.get("max_aspirations"),
      topic_proposal_start: formData.get("topic_proposal_start"),
      topic_proposal_end: formData.get("topic_proposal_end"),
      registration_start: formData.get("registration_start"),
      registration_end: formData.get("registration_end"),
    };
    return true;
  };

  const validateStep2 = () => {
    if (Object.keys(state.selectedTeachers).length === 0) {
      if (window.toast)
        toast.error("Lỗi", "Vui lòng chọn ít nhất 1 giảng viên.");
      else alert("Vui lòng chọn ít nhất 1 giảng viên.");
      return false;
    }

    // Validate quotas
    for (const [id, teacher] of Object.entries(state.selectedTeachers)) {
      if (teacher.min_students < 0 || teacher.max_students <= 0) {
        if (window.toast)
          toast.error("Lỗi", "Số lượng sinh viên không hợp lệ (phải > 0).");
        else alert("Số lượng sinh viên không hợp lệ (phải > 0).");
        return false;
      }
      if (teacher.min_students > teacher.max_students) {
        if (window.toast)
          toast.error(
            "Lỗi",
            "Số SV tối đa phải lớn hơn hoặc bằng SV tối thiểu.",
          );
        else alert("Số SV tối đa phải lớn hơn hoặc bằng SV tối thiểu.");
        return false;
      }
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
      return true;
    },
  });

  wizard.onChange((index) => {
    state.currentStep = index + 1;

    if (state.currentStep === 2) {
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

  // --- STEP 2: CHỈ ĐỊNH GIẢNG VIÊN ---
  const loadTeachers = async () => {
    try {
      const res = await fetch(`${apiBase}/teachers-available`);
      if (!res.ok) throw new Error("Không thể tải danh sách GV");
      const result = await res.json();
      state.teachers = result.data || [];
    } catch (err) {
      console.error(err);
      if (teachersContainer)
        teachersContainer.innerHTML = `<span class="text-sm text-red-500">Lỗi: ${err.message}</span>`;
    }
  };

  const checkSubmitButtonStatus = () => {
    const hasTeachers = Object.keys(state.selectedTeachers).length > 0;
    if (hasTeachers) {
      btnSubmit.removeAttribute("disabled");
    } else {
      btnSubmit.setAttribute("disabled", "disabled");
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

    const grid = document.createElement("div");
    grid.className = "teacher-grid";

    filteredTeachers.forEach((teacher) => {
      const isSelected =
        state.selectedTeachers[teacher.teacher_id] !== undefined;
      const currentMin = isSelected
        ? state.selectedTeachers[teacher.teacher_id].min_students
        : 0;
      const currentMax = isSelected
        ? state.selectedTeachers[teacher.teacher_id].max_students
        : 20;

      const card = document.createElement("div");
      card.className = `teacher-card ${isSelected ? "selected" : ""}`;

      // We will adjust the style inline because internship-batch-css might be slightly different.
      // But we can re-use the class names
      card.innerHTML = `
        <div class="flex flex-col gap-2 p-2">
          <div class="teacher-info card-clickable-area flex items-start gap-2 mb-2 cursor-pointer">
            <input type="checkbox" class="teacher-checkbox mt-1" value="${teacher.teacher_id}" ${isSelected ? "checked" : ""}>
            <div>
              <div class="font-medium">${teacher.full_name}</div>
              <div class="text-xs" style="color: var(--muted-foreground)">${teacher.email || "Không có email"}</div>
            </div>
          </div>
          <div class="quota-controls grid grid-cols-2 gap-2 mt-2">
            <div class="field">
              <label class="field__label text-xs">SV Tối thiểu</label>
              <input type="number" class="field__input text-sm px-2 py-1 h-8 input-min" 
                value="${currentMin}" ${!isSelected ? "disabled" : ""} min="0" step="2">
            </div>
            <div class="field">
              <label class="field__label text-xs">SV Tối đa</label>
              <input type="number" class="field__input text-sm px-2 py-1 h-8 input-max" 
                value="${currentMax}" ${!isSelected ? "disabled" : ""} min="0" step="2">
            </div>
          </div>
        </div>
      `;

      grid.appendChild(card);

      const checkbox = card.querySelector(".teacher-checkbox");
      const clickableArea = card.querySelector(".card-clickable-area");
      const minInput = card.querySelector(".input-min");
      const maxInput = card.querySelector(".input-max");

      const toggleTeacher = (forceState = null) => {
        const newState = forceState !== null ? forceState : !checkbox.checked;
        checkbox.checked = newState;

        if (newState) {
          card.classList.add("selected");
          minInput.removeAttribute("disabled");
          maxInput.removeAttribute("disabled");
          state.selectedTeachers[teacher.teacher_id] = {
            min_students: parseInt(minInput.value) || 0,
            max_students: parseInt(maxInput.value) || 20,
          };
        } else {
          card.classList.remove("selected");
          minInput.setAttribute("disabled", "disabled");
          maxInput.setAttribute("disabled", "disabled");
          delete state.selectedTeachers[teacher.teacher_id];
        }
        checkSubmitButtonStatus();
      };

      clickableArea.addEventListener("click", (e) => {
        if (e.target !== checkbox) toggleTeacher();
      });

      checkbox.addEventListener("change", (e) => {
        const isChecked = e.target.checked;
        checkbox.checked = !isChecked; // revert to let toggleTeacher handle it
        toggleTeacher(isChecked);
      });

      minInput.addEventListener("input", (e) => {
        if (checkbox.checked) {
          state.selectedTeachers[teacher.teacher_id].min_students =
            parseInt(e.target.value) || 0;
        }
      });
      maxInput.addEventListener("input", (e) => {
        if (checkbox.checked) {
          state.selectedTeachers[teacher.teacher_id].max_students =
            parseInt(e.target.value) || 20;
        }
      });
    });

    teachersContainer.appendChild(grid);
    checkSubmitButtonStatus();
  };

  // Search Teachers Event
  if (searchTeachersInput) {
    searchTeachersInput.addEventListener("input", () => {
      renderTeachers();
    });
  }

  // --- SUBMIT ---
  const doSubmitBatch = async () => {
    if (!validateStep2()) return;

    const payload = {
      ...state.batchData,
      supervisors: Object.keys(state.selectedTeachers).map((tId) => ({
        teacher_id: parseInt(tId),
        min_students: state.selectedTeachers[tId].min_students,
        max_students: state.selectedTeachers[tId].max_students,
      })),
    };

    try {
      btnSubmit.setAttribute("disabled", "disabled");
      btnSubmit.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...`;

      const res = await fetch(apiBase, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });

      const result = await res.json();
      const data = result.data;

      if (!res.ok) {
        let errorMsg = result.message || "Lỗi khi tạo đợt";
        throw new Error(errorMsg);
      }

      if (window.toast)
        toast.success(
          "Thành công",
          result.message || "Đã tạo đợt đồ án thành công.",
        );

      setTimeout(() => {
        window.location.href = window.REDIRECT_URL;
      }, 1500);
    } catch (err) {
      if (window.toast) toast.error("Lỗi", err.message);
      else alert(err.message);
      btnSubmit.removeAttribute("disabled");
      btnSubmit.innerHTML = `<i class="fa-solid fa-check mr-2"></i> Hoàn tất tạo đợt`;
    }
  };

  btnSubmit.addEventListener("click", () => {
    doSubmitBatch();
  });

  // Init
  wizard
    .renderProgress(wizardRoot, window.BATCH_CREATE_WIZARD_STEPS || [])
    .init();
  loadTeachers();
});
