import { TableManager } from "../table/index.js";
import { ExportManager } from "../export/export_manager.js";

document.addEventListener("DOMContentLoaded", () => {
  const batchId = window.BATCH_ID;
  const batchStatus = window.BATCH_STATUS;
  const apiBase = window.API_BASE_URL;

  let supervisors = [];
  let tableData = [];
  let bulkActionsRegistered = false;
  let tableEventsAttached = false;
  let isProcessing = false;

  // Modals
  const modalHandler = ModalHandler.instance;

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
          if (!tableEventsAttached) {
            attachTableEvents();
            tableEventsAttached = true;
          }
          registerBulkActions();

          // Đăng ký Export Excel
          const batchTitle = window.BATCH_TITLE || `Đợt ${batchId}`;
          ExportManager.register(tm, {
            source: 'batch_students',
            source_id: batchId,
            endpoint: window.API_BASE_URL.replace('/internship/batches', '/export'),
            filename: `Danh-sach-sinh-vien-${batchTitle}`,
            metadataTitle: `Danh sách sinh viên - ${batchTitle}`,
            metadataDateRange: window.BATCH_START && window.BATCH_END
              ? `Từ ngày ${window.BATCH_START} đến ngày ${window.BATCH_END}`
              : null,
            columnsMap: {
              student_code: "MSSV",
              student_name: "Họ và tên",
              classroom_name: "Lớp",
              student_phone: "SĐT",
              student_email: "Email",
              company_name: "Tên Công ty",
              company_tax_code: "Mã số thuế",
              company_address: "Địa chỉ",
              teacher_name: "Giảng viên hướng dẫn",
              grade_score: "Điểm số",
              grade_reason: "Diễn giải điểm",
              grade_feedback: "Nhận xét"
            }
          });
        }
      }
      retries++;
    }, 100);
  };

  const registerBulkActions = () => {
    if (bulkActionsRegistered || window.IS_READONLY) return;
    bulkActionsRegistered = true;

    TableManager.registerBulkActions("batch_students_table", {
      countLabel: count => `Đã chọn: ${count}`,
      actions: [
        {
          id: "assign",
          label: "Phân công giảng viên",
          icon: "fa-solid fa-user-plus",
          variant: "primary",
          onClick: ({ selectedIds }) => {
            document.querySelector("#bulk-student-count").textContent = selectedIds.length;

            const select = document.querySelector("#bulk-teacher-select");
            renderTeacherOptions(select, null);

            modalHandler.open("#modal-bulk-assign");
          },
        },
        {
          id: "unassign",
          label: "Hủy phân công",
          icon: "fa-solid fa-user-minus",
          destructive: true,
          confirm: false,
          onClick: ({ selectedIds }) => {
            document.querySelector("#bulk-unassign-count").textContent = selectedIds.length;
            modalHandler.open("#modal-bulk-unassign");
          },
        },
      ],
    });
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
      if (window.IS_READONLY || isProcessing) return;

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
        const display = cell.querySelector(".teacher-cell__display");
        const editor = cell.querySelector(".teacher-cell__editor");

        const batchStudentId = cell.dataset.batchStudentId;
        const assignmentId = cell.dataset.assignmentId;
        const newTeacherId =
          select.value && select.value !== "unassign"
            ? parseInt(select.value)
            : null;

        // Xác nhận gửi email
        const confirmMsg =
          "Bạn có chắc chắn muốn thay đổi phân công?\n\nThao tác này sẽ gửi thông báo qua email cho Sinh viên và Giảng viên có liên quan.";
          
        const isConfirmed = await new Promise((resolve) => {
          document.getElementById("confirm-assignment-title").textContent = "Xác nhận thay đổi";
          document.getElementById("confirm-assignment-desc").textContent = confirmMsg;
          modalHandler.open("#modal-confirm-assignment");

          const btnConfirm = document.getElementById("btn-confirm-assignment");
          const btnCancel = document.getElementById("btn-cancel-assignment");
          const btnClose = document.getElementById("btn-close-assignment-modal");

          const cleanup = () => {
            btnConfirm.removeEventListener("click", onConfirm);
            btnCancel.removeEventListener("click", onCancel);
            btnClose.removeEventListener("click", onCancel);
            modalHandler.close();
          };

          const onConfirm = () => { cleanup(); resolve(true); };
          const onCancel = () => { cleanup(); resolve(false); };

          btnConfirm.addEventListener("click", onConfirm);
          btnCancel.addEventListener("click", onCancel);
          btnClose.addEventListener("click", onCancel);
        });

        if (!isConfirmed) {
          // Khôi phục lại value cũ
          select.value = cell.dataset.teacherId || "";
          setTimeout(() => select.blur(), 10);
          return;
        }

        // Vô hiệu hóa select để tránh spam
        select.disabled = true;
        const nameSpan = display.querySelector(".teacher-cell__name");
        if (nameSpan) {
          nameSpan.innerHTML =
            '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Đang lưu...';
          nameSpan.classList.add("text-gray-500", "italic");
        }

        // Ẩn editor và hiện display ngay lập tức
        editor.classList.add("hidden");
        display.classList.remove("hidden");

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
    if (isProcessing) return false;

    try {
      if (batchStatus === "published" && !reason) {
        reason = prompt(
          "Đợt thực tập đã công bố. Vui lòng nhập lý do thay đổi:",
          "",
        );
        if (reason === null) {
          loadData(); // Reset UI
          return false;
        }
        if (!reason.trim()) {
          toast.warn("Yêu cầu", "Lý do không được để trống.");
          loadData();
          return false;
        }
      }

      isProcessing = true;
      document.body.style.cursor = "wait"; // Hiển thị con trỏ chuột loading cho toàn trang

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
      return true;
    } catch (error) {
      toast.error("Lỗi", error.message);
      loadData();
      return false;
    } finally {
      isProcessing = false;
      document.body.style.cursor = "default";
    }
  };

  document
    .querySelector("#btn-confirm-bulk-assign")
    .addEventListener("click", async () => {
      const teacherId = document.querySelector("#bulk-teacher-select").value;
      if (!teacherId || teacherId === "unassign") {
        alert("Vui lòng chọn một giảng viên.");
        return;
      }

      const selectedIds = TableManager.getRowSelection("batch_students_table");

      // Kiểm tra hạn mức
      const selectedTeacher = supervisors.find(
        (s) => s.teacher_id == teacherId,
      );
      if (selectedTeacher) {
        let newAssignmentsCount = 0;
        selectedIds.forEach((sid) => {
          const row = tableData.find((r) => r.batch_student_id == sid);
          if (row && row.teacher_id != teacherId) {
            newAssignmentsCount++;
          }
        });

        const remaining =
          selectedTeacher.max_students - selectedTeacher.current_assigned;
        if (newAssignmentsCount > remaining) {
          toast.warn(
            "Thao tác không thành công",
            `Giảng viên này chỉ còn nhận thêm tối đa ${remaining} sinh viên.`,
          );
          return;
        }
      }

      const confirmMsg =
        "Bạn có chắc chắn muốn thay đổi phân công cho các sinh viên đã chọn?\n\nThao tác này sẽ gửi thông báo qua email cho Sinh viên và Giảng viên có liên quan.";
        
      const isConfirmed = await new Promise((resolve) => {
        document.getElementById("confirm-assignment-title").textContent = "Xác nhận phân công hàng loạt";
        document.getElementById("confirm-assignment-desc").textContent = confirmMsg;
        modalHandler.open("#modal-confirm-assignment");

        const btnConfirm = document.getElementById("btn-confirm-assignment");
        const btnCancel = document.getElementById("btn-cancel-assignment");
        const btnClose = document.getElementById("btn-close-assignment-modal");

        const cleanup = () => {
          btnConfirm.removeEventListener("click", onConfirm);
          btnCancel.removeEventListener("click", onCancel);
          btnClose.removeEventListener("click", onCancel);
          modalHandler.close();
        };

        const onConfirm = () => { cleanup(); resolve(true); };
        const onCancel = () => { cleanup(); resolve(false); };

        btnConfirm.addEventListener("click", onConfirm);
        btnCancel.addEventListener("click", onCancel);
        btnClose.addEventListener("click", onCancel);
      });

      if (!isConfirmed) {
        return;
      }

      const assignments = selectedIds.map((sid) => {
        const row = tableData.find((r) => r.batch_student_id == sid);
        return {
          assignment_id: row?.assignment_id || null,
          batch_student_id: parseInt(sid),
          new_teacher_id: parseInt(teacherId),
        };
      });

      modalHandler.close();
      TableManager.setBulkActionLoading(
        "batch_students_table",
        true,
        "Đang xử lý...",
      );
      try {
        const saved = await saveAssignments(
          assignments,
          "Cập nhật phân công hàng loạt.",
        );
        if (saved) TableManager.clearSelection("batch_students_table");
      } finally {
        TableManager.setBulkActionLoading("batch_students_table", false);
      }
    });

  document
    .querySelector("#btn-confirm-bulk-unassign")
    .addEventListener("click", async () => {
      const selectedIds = TableManager.getRowSelection("batch_students_table");
      const assignments = selectedIds.map((sid) => {
        const row = tableData.find((r) => r.batch_student_id == sid);
        return {
          assignment_id: row?.assignment_id || null,
          batch_student_id: parseInt(sid),
          new_teacher_id: null,
        };
      });

      modalHandler.close();
      TableManager.setBulkActionLoading(
        "batch_students_table",
        true,
        "Đang xử lý...",
      );
      try {
        const saved = await saveAssignments(
          assignments,
          "Hủy phân công hàng loạt.",
        );
        if (saved) TableManager.clearSelection("batch_students_table");
      } finally {
        TableManager.setBulkActionLoading("batch_students_table", false);
      }
    });

  // Auto Assign: Shuffle
  document.querySelector("#btn-auto-shuffle")?.addEventListener("click", () => {
    modalHandler.open("#modal-auto-shuffle");
  });

  document
    .querySelector("#btn-confirm-auto-shuffle")
    ?.addEventListener("click", async () => {
      await performAutoAssign("auto_shuffle");
    });

  // Auto Assign: Even (Load Balancing)
  document.querySelector("#btn-auto-even")?.addEventListener("click", () => {
    modalHandler.open("#modal-auto-even");
  });

  document
    .querySelector("#btn-confirm-auto-even")
    ?.addEventListener("click", async () => {
      await performAutoAssign("auto_even");
    });

  /**
   * Helper thực hiện API phân công tự động
   */
  const performAutoAssign = async (method) => {
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
      modalHandler.close();
      await loadData();
    } catch (error) {
      toast.error("Lỗi", error.message);
    }
  };

  // Khởi chạy
  loadData();
});
