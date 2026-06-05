import { TableManager } from "../table/table_manager.js";
import LayoutCollapsible from "../layout-collapsible.js";

document.addEventListener("DOMContentLoaded", () => {
  // Init Collapsible Sidebar (Default to expanded for admin management)
  new LayoutCollapsible({
    containerSelector: ".detail-layout--collapsible",
    toggleSelector: ".js-sidebar-toggle",
    storageKey: "admin-batch-students-sidebar-collapsed",
    defaultCollapsed: false,
  });

  const batchId = window.BATCH_ID;
  const batchStatus = window.BATCH_STATUS;
  const apiBase = window.API_BASE_URL;

  let supervisors = [];
  let tableData = [];

  // DOM Elements
  const bulkActionBar = document.querySelector("#bulk-action-bar");
  const selectedCountText = document.querySelector("#selected-count");

  // Modals
  const modalBulkAssign = document.querySelector("#modal-bulk-assign");
  const modalBulkUnassign = document.querySelector("#modal-bulk-unassign");
  const modalAutoEven = document.querySelector("#modal-auto-even");
  const modalAutoShuffle = document.querySelector("#modal-auto-shuffle");

  // Stat Elements
  const statTotalStudents = document.querySelector("#stat-total-students");
  const statAssignedStudents = document.querySelector(
    "#stat-assigned-students",
  );
  const statUnassignedStudents = document.querySelector(
    "#stat-unassigned-students",
  );
  const supervisorStatsContainer = document.querySelector(
    "#supervisor-stats-container",
  );
  const supervisorCountBadge = document.querySelector("#supervisor-count");

  /**
   * Tải dữ liệu ban đầu
   */
  const loadData = async () => {
    try {
      const [supRes, assRes] = await Promise.all([
        fetch(`${apiBase}/${batchId}/supervisors`),
        fetch(`${apiBase}/${batchId}/assignments`),
      ]);

      if (!supRes.ok || !assRes.ok)
        throw new Error("Không thể tải dữ liệu từ máy chủ.");

      const supData = await supRes.json();
      const assData = await assRes.json();

      supervisors = supData.data || [];
      tableData = assData.data || [];

      // Cập nhật thống kê
      renderStats();
      renderSupervisorStats();

      // Khởi tạo/Cập nhật bảng
      renderTable();
    } catch (error) {
      console.error(error);
      if (window.toast) toast.error("Lỗi", error.message);
    }
  };

  /**
   * Cập nhật Dashboard thống kê tổng quan
   */
  const renderStats = () => {
    const total = tableData.length;
    const assigned = tableData.filter((r) => r.teacher_id).length;
    const unassigned = total - assigned;

    statTotalStudents.textContent = total;
    statAssignedStudents.textContent = assigned;
    statUnassignedStudents.textContent = unassigned;
    supervisorCountBadge.textContent = supervisors.length;
  };

  /**
   * Render danh sách giảng viên ở sidebar kèm quota
   */
  const renderSupervisorStats = () => {
    if (!supervisorStatsContainer) return;

    if (supervisors.length === 0) {
      supervisorStatsContainer.innerHTML = `
        <div class="flex flex-col items-center justify-center p-8">
          <i class="fa-solid fa-user-slash text-2xl mb-2"></i>
          <span class="text-xs">Chưa có giảng viên trong đợt này.</span>
        </div>`;
      return;
    }

    let html = "";
    supervisors.forEach((sup) => {
      const percent =
        sup.max_students > 0
          ? Math.min(100, (sup.current_assigned / sup.max_students) * 100)
          : 0;
      const isFull = sup.current_assigned >= sup.max_students;

      html += `
        <div class="supervisor-card shadow-sm border" ${isFull ? "style='background: var(--muted)'" : ""}>
          <div class="supervisor-card__header">
            <div>
              <div class="supervisor-card__name text-sm">${sup.teacher_name}</div>
              <div class="text-xs">${sup.email || ""}</div>
            </div>
            <div class="supervisor-card__stats">
              <span class="font-bold text-sm">${sup.current_assigned}</span>
              <span>/${sup.max_students}</span>
            </div>
          </div>
          <div class="quota-progress">
            <div class="quota-progress__inner ${isFull ? "quota-progress__inner--full" : ""}" 
                 style="width: ${percent}%"></div>
          </div>
        </div>`;
    });

    supervisorStatsContainer.innerHTML = html;
  };

  /**
   * Đổ dữ liệu vào TableManager
   */
  const renderTable = () => {
    // Đợi TableManager sẵn sàng (max 5 lần thử)
    let retries = 0;
    const interval = setInterval(() => {
      const tm = TableManager.get("batch_students_table");
      if (tm || retries > 5) {
        clearInterval(interval);
        if (tm) {
          tm.loadData(tableData);
          attachTableEvents();
        }
      }
      retries++;
    }, 100);
  };

  /**
   * Xử lý sự kiện trong bảng (Inline Edit)
   */
  const attachTableEvents = () => {
    const tableRoot = document.querySelector(
      '[data-tm="batch_students_table"]',
    );

    // Xử lý click để bật editor
    tableRoot.addEventListener("click", (e) => {
      if (batchStatus === "closed") return;

      const display = e.target.closest(".teacher-cell__display");
      if (display) {
        const cell = display.closest(".teacher-cell");
        const editor = cell.querySelector(".teacher-cell__editor");
        const select = editor.querySelector("select");

        // Render options nếu chưa có
        if (select.children.length === 0) {
          renderTeacherOptions(select, cell.dataset.teacherId);
        }

        display.classList.add("hidden");
        editor.classList.remove("hidden");
        select.focus();
      }
    });

    // Xử lý khi đổi giá trị select (Inline save)
    tableRoot.addEventListener("change", async (e) => {
      if (e.target.classList.contains("teacher-cell__select")) {
        const select = e.target;
        const cell = select.closest(".teacher-cell");
        const batchStudentId = cell.dataset.batchStudentId;
        const assignmentId = cell.dataset.assignmentId;
        const newTeacherId =
          select.value && select.value !== "unassign"
            ? parseInt(select.value)
            : null;

        await saveAssignments(
          [
            {
              assignment_id: assignmentId ? parseInt(assignmentId) : null,
              batch_student_id: parseInt(batchStudentId),
              new_teacher_id: newTeacherId,
            },
          ],
          "Cập nhật giảng viên hướng dẫn trực tiếp từ bảng.",
        );
      }
    });

    // Xử lý khi blur select (Cancel edit)
    tableRoot.addEventListener("focusout", (e) => {
      if (e.target.classList.contains("teacher-cell__select")) {
        setTimeout(() => {
          const select = e.target;
          const cell = select.closest(".teacher-cell");
          const display = cell.querySelector(".teacher-cell__display");
          const editor = cell.querySelector(".teacher-cell__editor");
          if (display && editor) {
            display.classList.remove("hidden");
            editor.classList.add("hidden");
          }
        }, 200);
      }
    });
  };

  /**
   * Render options cho dropdown giáo viên
   */
  const renderTeacherOptions = (select, currentTeacherId) => {
    let html = '<option value="">-- Chọn Giảng viên --</option>';
    html +=
      '<option value="unassign" class="font-bold">Không có (Hủy phân công)</option>';
    html += "<hr>";

    supervisors.forEach((sup) => {
      const isFull =
        sup.current_assigned >= sup.max_students &&
        sup.teacher_id != currentTeacherId;
      html += `<option value="${sup.teacher_id}" ${sup.teacher_id == currentTeacherId ? "selected" : ""} ${isFull ? "disabled" : ""}>
        ${sup.teacher_name} (${sup.current_assigned}/${sup.max_students}) ${isFull ? "[Hết chỗ]" : ""}
      </option>`;
    });

    select.innerHTML = html;
  };

  /**
   * Gọi API lưu phân công
   */
  const saveAssignments = async (assignments, reason) => {
    try {
      if (batchStatus === "published" && !reason) {
        reason = prompt(
          "Đợt thực tập đã công bố. Vui lòng nhập lý do thay đổi:",
          "",
        );
        if (reason === null) {
          loadData(); // Reset UI
          return;
        }
        if (!reason.trim()) {
          toast.warning("Yêu cầu", "Lý do không được để trống.");
          loadData();
          return;
        }
      }

      const res = await fetch(`${apiBase}/${batchId}/bulk-save`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          reason: reason || "Quản trị viên cập nhật phân công.",
          assignments: assignments,
        }),
      });

      const data = await res.json();
      if (!res.ok) throw new Error(data.message || "Lỗi khi lưu dữ liệu.");

      toast.success("Thành công", "Đã cập nhật phân công.");
      await loadData();
    } catch (error) {
      toast.error("Lỗi", error.message);
      loadData();
    }
  };

  /**
   * Xử lý Selection Change từ TableManager
   */
  document
    .querySelector('[data-tm="batch_students_table"]')
    .addEventListener("tm:selection-change", (e) => {
      const selectedIds = e.detail.selectedIds;
      const count = selectedIds.length;

      if (count > 0) {
        bulkActionBar.classList.remove("hidden");
        bulkActionBar.setAttribute("data-state", "open");
        selectedCountText.textContent = `Đã chọn: ${count}`;
      } else {
        bulkActionBar.classList.add("hidden");
        bulkActionBar.setAttribute("data-state", "closed");
      }
    });

  // Bulk Action: Cancel
  document
    .querySelector("#btn-cancel-selection")
    .addEventListener("click", () => {
      TableManager.clearSelection("batch_students_table");
    });

  // Bulk Action: Assign Modal
  document.querySelector("#btn-bulk-assign").addEventListener("click", () => {
    const selectedIds = TableManager.getSelectedIds("batch_students_table");
    document.querySelector("#bulk-student-count").textContent =
      selectedIds.length;

    const select = document.querySelector("#bulk-teacher-select");
    renderTeacherOptions(select, null);

    modalBulkAssign.classList.remove("hidden");
    modalBulkAssign.setAttribute("data-state", "open");
  });

  document
    .querySelector("#btn-confirm-bulk-assign")
    .addEventListener("click", async () => {
      const teacherId = document.querySelector("#bulk-teacher-select").value;
      if (!teacherId || teacherId === "unassign") {
        alert("Vui lòng chọn một giảng viên.");
        return;
      }

      const selectedIds = TableManager.getSelectedIds("batch_students_table");
      const assignments = selectedIds.map((sid) => {
        const row = tableData.find((r) => r.batch_student_id == sid);
        return {
          assignment_id: row?.assignment_id || null,
          batch_student_id: parseInt(sid),
          new_teacher_id: parseInt(teacherId),
        };
      });

      modalBulkAssign.classList.add("hidden");
      modalBulkAssign.setAttribute("data-state", "closed");
      await saveAssignments(assignments, "Cập nhật phân công hàng loạt.");
      TableManager.clearSelection("batch_students_table");
    });

  // Bulk Action: Unassign Modal
  document.querySelector("#btn-bulk-unassign").addEventListener("click", () => {
    const selectedIds = TableManager.getSelectedIds("batch_students_table");
    document.querySelector("#bulk-unassign-count").textContent =
      selectedIds.length;
    modalBulkUnassign.classList.remove("hidden");
    modalBulkUnassign.setAttribute("data-state", "open");
  });

  document
    .querySelector("#btn-confirm-bulk-unassign")
    .addEventListener("click", async () => {
      const selectedIds = TableManager.getSelectedIds("batch_students_table");
      const assignments = selectedIds.map((sid) => {
        const row = tableData.find((r) => r.batch_student_id == sid);
        return {
          assignment_id: row?.assignment_id || null,
          batch_student_id: parseInt(sid),
          new_teacher_id: null,
        };
      });

      modalBulkUnassign.classList.add("hidden");
      modalBulkUnassign.setAttribute("data-state", "closed");
      await saveAssignments(assignments, "Hủy phân công hàng loạt.");
      TableManager.clearSelection("batch_students_table");
    });

  // Auto Assign: Shuffle
  document.querySelector("#btn-auto-shuffle")?.addEventListener("click", () => {
    modalAutoShuffle.classList.remove("hidden");
    modalAutoShuffle.setAttribute("data-state", "open");
  });

  document
    .querySelector("#btn-confirm-auto-shuffle")
    ?.addEventListener("click", async () => {
      await performAutoAssign("auto_shuffle", modalAutoShuffle);
    });

  // Auto Assign: Even (Load Balancing)
  document.querySelector("#btn-auto-even")?.addEventListener("click", () => {
    modalAutoEven.classList.remove("hidden");
    modalAutoEven.setAttribute("data-state", "open");
  });

  document
    .querySelector("#btn-confirm-auto-even")
    ?.addEventListener("click", async () => {
      await performAutoAssign("auto_even", modalAutoEven);
    });

  /**
   * Helper thực hiện API phân công tự động
   */
  const performAutoAssign = async (method, modal) => {
    try {
      const res = await fetch(`${apiBase}/${batchId}/auto-assign`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ method }),
      });

      const data = await res.json();
      if (!res.ok)
        throw new Error(data.message || "Lỗi khi phân công tự động.");

      toast.success("Thành công", data.message);
      modal.classList.add("hidden");
      modal.setAttribute("data-state", "closed");
      await loadData();
    } catch (error) {
      toast.error("Lỗi", error.message);
    }
  };

  // Close Modals logic
  const closeModal = (modal) => {
    if (!modal) return;
    modal.classList.add("hidden");
    modal.setAttribute("data-state", "closed");
  };

  document.querySelectorAll('.btn[id*="close"]').forEach((btn) => {
    btn.addEventListener("click", () => {
      closeModal(modalBulkAssign);
      closeModal(modalBulkUnassign);
      closeModal(modalAutoEven);
      closeModal(modalAutoShuffle);
    });
  });

  // Khởi chạy
  loadData();
});
