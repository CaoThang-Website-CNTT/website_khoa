document.addEventListener("DOMContentLoaded", () => {
  const batchId = window.CURRENT_BATCH_ID;
  const apiBase = window.API_BASE_URL;

  // --- TAB NAVIGATION ---
  const tabTriggers = document.querySelectorAll(".tab-trigger");
  const tabContents = document.querySelectorAll(".tab-content");

  tabTriggers.forEach((trigger) => {
    trigger.addEventListener("click", () => {
      const targetTab = trigger.getAttribute("data-tab");

      tabTriggers.forEach((t) => t.setAttribute("data-state", "inactive"));
      trigger.setAttribute("data-state", "active");

      tabContents.forEach((content) => {
        if (content.id === `tab-${targetTab}`) {
          content.setAttribute("data-state", "active");
          content.classList.remove("hidden");
        } else {
          content.setAttribute("data-state", "inactive");
          content.classList.add("hidden");
        }
      });

      if (targetTab === "students") loadBatchStudents();
      if (targetTab === "supervisors") loadBatchSupervisors();
    });
  });

  // --- DATA LOADING ---

  async function loadBatchStudents() {
    const tbody = document.querySelector("#table-batch-students tbody");
    try {
      const res = await fetch(`${apiBase}/students`);
      const result = await res.json();
      const data = result.data;

      if (!data.length) {
        tbody.innerHTML =
          '<tr><td colspan="5" class="text-center p-8 text-muted">Chưa có sinh viên nào tham gia.</td></tr>';
        return;
      }

      tbody.innerHTML = data
        .map(
          (s) => `
        <tr>
          <td>${s.student_code}</td>
          <td class="font-medium">${s.full_name}</td>
          <td>${s.classroom_name || "N/A"}</td>
          <td><span class="badge badge--pending">${s.status}</span></td>
          <td class="text-right">
            <button class="btn-icon text-destructive btn-remove-student" data-id="${s.student_id}" title="Xóa khỏi đợt">
              <i class="fa-solid fa-trash"></i>
            </button>
          </td>
        </tr>
      `,
        )
        .join("");

      attachStudentEvents();
    } catch (err) {
      toast.error("Lỗi", "Không thể tải danh sách sinh viên");
    }
  }

  async function loadBatchSupervisors() {
    const tbody = document.querySelector("#table-batch-supervisors tbody");
    try {
      const res = await fetch(`${apiBase}/supervisors`);
      const result = await res.json();
      const data = result.data;

      if (!data.length) {
        tbody.innerHTML =
          '<tr><td colspan="4" class="text-center p-8 text-muted">Chưa có giảng viên hướng dẫn.</td></tr>';
        return;
      }

      tbody.innerHTML = data
        .map(
          (sup) => `
        <tr>
          <td>
            <div class="font-medium">${sup.full_name}</div>
            <div class="text-xs text-muted-foreground">${sup.degree || ""}</div>
          </td>
          <td>${sup.department_name || ""}</td>
          <td class="text-center">
            <div class="flex items-center justify-center gap-2">
              <span class="font-medium ${sup.assigned_count >= sup.max_students ? "text-destructive" : ""}">
                ${sup.assigned_count}/${sup.max_students}
              </span>
              <button class="btn-icon btn-edit-quota" data-id="${sup.teacher_id}" data-quota="${sup.max_students}">
                <i class="fa-solid fa-pen-to-square text-xs"></i>
              </button>
            </div>
          </td>
          <td class="text-right">
            <button class="btn-icon text-destructive btn-remove-supervisor" data-id="${sup.teacher_id}" title="Xóa khỏi đợt">
              <i class="fa-solid fa-user-minus"></i>
            </button>
          </td>
        </tr>
      `,
        )
        .join("");

      attachSupervisorEvents();
    } catch (err) {
      toast.error("Lỗi", "Không thể tải danh sách giảng viên");
    }
  }

  // --- MEMBER SEARCH & ADD ---

  let selectedStudentIds = [];

  // Search Students
  document.getElementById("btn-add-student")?.addEventListener("click", () => {
    document
      .getElementById("modal-add-student")
      .setAttribute("data-state", "open");
    loadSearchClassrooms();
  });

  async function loadSearchClassrooms() {
    const select = document.getElementById("search-student-classroom");
    if (select.children.length > 1) return; // Đã load
    try {
      // Lấy root API base (bỏ phần /management ở cuối)
      const rootApi = window.API_BASE_URL.replace("/management", "");
      const res = await fetch(`${rootApi}/classrooms`);
      const result = await res.json();
      const data = result.data;
      data.forEach((c) => {
        const opt = document.createElement("option");
        opt.value = c.id;
        opt.textContent = c.name;
        select.appendChild(opt);
      });
    } catch (err) {}
  }

  document
    .getElementById("btn-do-search-student")
    ?.addEventListener("click", async () => {
      const q = document.getElementById("search-student-query").value;
      const cid = document.getElementById("search-student-classroom").value;
      const tbody = document.querySelector(
        "#table-search-student-results tbody",
      );

      tbody.innerHTML =
        '<tr><td colspan="4" class="text-center p-4"><i class="fa-solid fa-spinner fa-spin"></i> Đang tìm...</td></tr>';

      try {
        const res = await fetch(
          `${apiBase}/search-eligible-students?q=${q}&classroom_id=${cid}`,
        );
        const result = await res.json();
        const data = result.data;

        if (!data.length) {
          tbody.innerHTML =
            '<tr><td colspan="4" class="text-center p-4">Không tìm thấy sinh viên phù hợp.</td></tr>';
          return;
        }

        tbody.innerHTML = data
          .map(
            (s) => `
        <tr>
          <td><input type="checkbox" class="search-student-check" value="${s.id}"></td>
          <td>${s.student_code}</td>
          <td>${s.full_name}</td>
          <td>${s.classroom_name}</td>
        </tr>
      `,
          )
          .join("");

        document.querySelectorAll(".search-student-check").forEach((chk) => {
          chk.addEventListener("change", updateSelectedStudents);
        });
      } catch (err) {}
    });

  function updateSelectedStudents() {
    selectedStudentIds = Array.from(
      document.querySelectorAll(".search-student-check:checked"),
    ).map((c) => c.value);
    document.getElementById("btn-confirm-add-students").disabled =
      selectedStudentIds.length === 0;
  }

  document
    .getElementById("btn-confirm-add-students")
    ?.addEventListener("click", async () => {
      const btn = document.getElementById("btn-confirm-add-students");
      btn.disabled = true;
      btn.innerHTML =
        '<i class="fa-solid fa-spinner fa-spin"></i> Đang thêm...';

      try {
        // Techdebt: loại bỏ loop, dùng api thêm hàng loạt SV?
        for (const sid of selectedStudentIds) {
          await fetch(`${apiBase}/students`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ student_id: sid }),
          });
        }
        toast.success(
          "Thành công",
          `Đã thêm ${selectedStudentIds.length} sinh viên vào đợt.`,
        );
        document
          .getElementById("modal-add-student")
          .setAttribute("data-state", "closed");
        loadBatchStudents();
      } catch (err) {
        toast.error("Lỗi", "Có lỗi khi thêm sinh viên");
      } finally {
        btn.disabled = false;
        btn.innerHTML = "Thêm sinh viên đã chọn";
      }
    });

  // Search Teachers
  document
    .getElementById("btn-add-supervisor")
    ?.addEventListener("click", () => {
      document
        .getElementById("modal-add-supervisor")
        .setAttribute("data-state", "open");
    });

  document
    .getElementById("btn-do-search-teacher")
    ?.addEventListener("click", async () => {
      const q = document.getElementById("search-teacher-query").value;
      const select = document.getElementById("select-teacher-id");

      try {
        const res = await fetch(`${apiBase}/search-eligible-teachers?q=${q}`);
        const result = await res.json();
        const data = result.data;

        select.innerHTML = '<option value="">-- Chọn giảng viên --</option>';
        data.forEach((t) => {
          const opt = document.createElement("option");
          opt.value = t.id;
          opt.textContent = `${t.degree || ""} ${t.full_name} (${t.department_name || ""})`;
          select.appendChild(opt);
        });
      } catch (err) {}
    });

  document
    .getElementById("btn-confirm-add-supervisor")
    ?.addEventListener("click", async () => {
      const teacherId = document.getElementById("select-teacher-id").value;
      const quota = document.getElementById("input-teacher-quota").value;

      if (!teacherId) return toast.warning("Chú ý", "Vui lòng chọn giảng viên");

      try {
        const res = await fetch(`${apiBase}/supervisors`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ teacher_id: teacherId, max_students: quota }),
        });
        const result = await res.json();
        if (res.ok) {
          toast.success("Thành công", "Đã thêm giảng viên hướng dẫn.");
          document
            .getElementById("modal-add-supervisor")
            .setAttribute("data-state", "closed");
          loadBatchSupervisors();
        } else {
          toast.error("Lỗi", result.message);
        }
      } catch (err) {
        toast.error("Lỗi", "Không thể thêm giảng viên");
      }
    });

  // --- ACTIONS: REMOVE & UPDATE ---

  function attachStudentEvents() {
    document.querySelectorAll(".btn-remove-student").forEach((btn) => {
      btn.addEventListener("click", async () => {
        const id = btn.getAttribute("data-id");
        if (
          !confirm(
            "Bạn có chắc chắn muốn xóa sinh viên này khỏi đợt thực tập? Các dữ liệu phân công sẽ bị hủy.",
          )
        )
          return;

        try {
          const res = await fetch(`${apiBase}/students/${id}`, {
            method: "DELETE",
          });
          if (res.ok) {
            toast.success("Thành công", "Đã xóa sinh viên.");
            loadBatchStudents();
          }
        } catch (err) {}
      });
    });
  }

  function attachSupervisorEvents() {
    // Remove
    document.querySelectorAll(".btn-remove-supervisor").forEach((btn) => {
      btn.addEventListener("click", async () => {
        const id = btn.getAttribute("data-id");
        if (
          !confirm(
            "Xóa giảng viên này khỏi đợt? Tất cả sinh viên đang được GV này hướng dẫn sẽ trở về trạng thái 'Chưa phân công'.",
          )
        )
          return;

        try {
          const res = await fetch(`${apiBase}/supervisors/${id}`, {
            method: "DELETE",
          });
          if (res.ok) {
            toast.success("Thành công", "Đã xóa giảng viên.");
            loadBatchSupervisors();
          }
        } catch (err) {}
      });
    });

    // Edit Quota
    document.querySelectorAll(".btn-edit-quota").forEach((btn) => {
      btn.addEventListener("click", async () => {
        const id = btn.getAttribute("data-id");
        const oldQuota = btn.getAttribute("data-quota");
        const newQuota = prompt("Nhập định mức hướng dẫn mới:", oldQuota);

        if (newQuota === null || newQuota == oldQuota) return;
        if (isNaN(newQuota) || newQuota < 1)
          return toast.error("Lỗi", "Định mức không hợp lệ");

        try {
          const res = await fetch(`${apiBase}/supervisors/${id}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ max_students: newQuota }),
          });
          const result = await res.json();
          if (res.ok) {
            toast.success("Thành công", "Đã cập nhật định mức.");
            loadBatchSupervisors();
          } else {
            toast.error("Thất bại", result.message);
          }
        } catch (err) {}
      });
    });
  }

  // --- GENERIC ACTIONS ---

  const setupConfirmAction = (
    btnId,
    modalId,
    confirmBtnId,
    formId = null,
    actionUrl = null,
  ) => {
    const btn = document.getElementById(btnId);
    const confirmBtn = document.getElementById(confirmBtnId);
    if (confirmBtn) {
      confirmBtn.addEventListener("click", () => {
        if (formId) {
          const form = document.getElementById(formId);
          if (form) form.submit();
        } else if (actionUrl) {
          const hiddenForm = document.createElement("form");
          hiddenForm.method = "POST";
          hiddenForm.action = actionUrl;
          const csrfToken = document.querySelector('input[name="csrf_token"]');
          if (csrfToken) {
            const csrfInput = document.createElement("input");
            csrfInput.type = "hidden";
            csrfInput.name = "csrf_token";
            csrfInput.value = csrfToken.value;
            hiddenForm.appendChild(csrfInput);
          }
          document.body.appendChild(hiddenForm);
          hiddenForm.submit();
        }
      });
    }
  };

  setupConfirmAction(
    "edit-submit-btn",
    "#save-confirm-modal",
    "save-confirm-modal-btn",
    "batch-edit-form",
  );
  setupConfirmAction(
    "delete-btn",
    "#delete-confirm-modal",
    "delete-confirm-modal-btn",
    "delete-form",
  );

  const publishBtn = document.getElementById("publish-btn");
  if (publishBtn)
    setupConfirmAction(
      "publish-btn",
      "#publish-confirm-modal",
      "publish-confirm-modal-btn",
      null,
      publishBtn.getAttribute("data-action"),
    );

  const closeBtn = document.getElementById("close-btn");
  if (closeBtn)
    setupConfirmAction(
      "close-btn",
      "#close-confirm-modal",
      "close-confirm-modal-btn",
      null,
      closeBtn.getAttribute("data-action"),
    );

  // Initial load
  loadBatchStudents();
});
