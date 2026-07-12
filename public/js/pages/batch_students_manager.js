import { TableManager } from "../table/index.js";
import { ExportManager } from "../export/export_manager.js";

document.addEventListener("DOMContentLoaded", () => {
  const batchId = window.BATCH_ID;
  const batchStatus = window.BATCH_STATUS;
  const apiBase = window.API_BASE_URL;

  let supervisors = [];
  let tableData = [];
  let bulkActionsRegistered = false;
  let isTableInitialized = false;

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
      const [supRes] = await Promise.all([
        fetch(`${apiBase}/${batchId}/supervisors`),
      ]);

      if (!supRes.ok) throw new Error("Không thể tải dữ liệu từ máy chủ.");

      const supData = await supRes.json();

      supervisors = supData.data || [];

      // Cập nhật thống kê giảng viên
      renderSupervisorStats();

      // Khởi tạo/Cập nhật bảng
      const tm =
        typeof TableManager !== "undefined"
          ? TableManager.get("batch_students_table")
          : null;
      if (tm && isTableInitialized) {
        tm.root.dispatchEvent(
          new CustomEvent("tm:state-change", {
            detail: { reason: "reload", state: tm.getState() },
          }),
        );
      } else {
        renderTable();
      }
    } catch (error) {
      console.error(error);
      if (window.toast) toast.error("Lỗi", error.message);
    }
  };

  /**
   * Render danh sách giảng viên ở sidebar kèm quota
   */
  const renderSupervisorStats = () => {
    if (supervisorCountBadge) {
      supervisorCountBadge.textContent = supervisors.length;
    }

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
        <div class="supervisor-card">
          <div class="supervisor-card__header">
            <div>
              <div class="supervisor-card__name text-sm">${sup.teacher_name}</div>
              <div class="text-xs">${sup.email || ""}</div>
            </div>
            <div class="supervisor-card__stats">
              <div><span class="font-bold text-sm">${sup.current_assigned}</span><span>/${sup.max_students}</span></div>
              ${isFull ? '<span class="badge" data-variant="secondary">Đã đầy</span>' : ""}
            </div>
          </div>
          <div class="quota-progress">
            <div class="quota-progress__inner"
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
          if (!isTableInitialized) {
            isTableInitialized = true;

            tm.root.addEventListener("tm:state-change", async (e) => {
              const { reason, state } = e.detail;

              if (
                reason === "search" ||
                reason === "filter" ||
                reason === "sort" ||
                reason === "pagination" ||
                reason === "reload"
              ) {
                try {
                  const url = new URL(
                    `${apiBase}/${batchId}/assignments`,
                    window.location.origin,
                  );

                  if (state.search)
                    url.searchParams.set("search", `%${state.search}%`);

                  const page = (state.pagination?.pageIndex || 0) + 1;
                  const limit = state.pagination?.pageSize || 15;
                  url.searchParams.set("page", page);
                  url.searchParams.set("limit", limit);

                  if (state.sort && state.sort.col) {
                    url.searchParams.set("sort[col]", state.sort.col);
                    url.searchParams.set("sort[dir]", state.sort.dir);
                  }

                  if (state.filters && state.filters.length > 0) {
                    state.filters.forEach((f, i) => {
                      url.searchParams.set(`filters[${i}][col]`, f.col);
                      url.searchParams.set(`filters[${i}][op]`, f.op);
                      url.searchParams.set(`filters[${i}][value]`, f.value);
                    });
                  }

                  const response = await fetch(url);
                  if (!response.ok)
                    throw new Error(`HTTP error! status: ${response.status}`);
                  const result = await response.json();
                  const payload = result.data || {};

                  tm.loadData({
                    rows: payload.data || [],
                    total: payload.total || 0,
                    page: payload.page || 1,
                    limit: payload.limit || 15,
                  });
                } catch (error) {
                  console.error("Lỗi khi fetch dữ liệu:", error);
                }
              }
            });

            // Initial fetch
            tm.root.dispatchEvent(
              new CustomEvent("tm:state-change", {
                detail: { reason: "pagination", state: tm.getState() },
              }),
            );

            attachTableEvents();
            registerBulkActions();

            // Đăng ký Export Excel
            const slugify = (str) => {
              return String(str)
                .normalize("NFKD")
                .replace(/[\u0300-\u036f]/g, "")
                .replace(/[đĐ]/g, "d")
                .trim()
                .toLowerCase()
                .replace(/[^a-z0-9 -]/g, "")
                .replace(/\s+/g, "-")
                .replace(/-+/g, "-");
            };

            const batchTitle = window.BATCH_TITLE || `Đợt ${batchId}`;
            const batchSlug = slugify(batchTitle);

            ExportManager.register(tm, {
              target: "#batch-students-export-action",
              triggerLabel: "Export dữ liệu",
              triggerIcon: "fa-file-excel",
              triggerVariant: "outline",
              triggerSize: "lg",
              source: "batch_students",
              source_id: batchId,
              endpoint: window.API_BASE_URL.replace(
                "/internship/batches",
                "/export",
              ),
              filename: `danh-sach-sinh-vien-${batchSlug}`,
              metadataTitle: `Danh sách sinh viên - ${batchTitle}`,
              metadataDateRange:
                window.BATCH_START && window.BATCH_END
                  ? `Từ ngày ${window.BATCH_START} đến ngày ${window.BATCH_END}`
                  : null,
              columnGroups: [
                {
                  label: "Sinh viên",
                  columns: [
                    "student_code",
                    "student_name",
                    "classroom_name",
                    "student_phone",
                    "student_email",
                  ],
                },
                {
                  label: "Công ty",
                  columns: [
                    "company_name",
                    "company_tax_code",
                    "company_address",
                    "company_mentor_name",
                    "company_mentor_phone",
                    "company_mentor_email",
                  ],
                },
                {
                  label: "Kết quả",
                  columns: [
                    "teacher_name",
                    "grade_score",
                    "grade_reason",
                    "grade_feedback",
                  ],
                },
              ],
              columnsMap: {
                student_code: "MSSV",
                student_name: "Họ và tên",
                classroom_name: "Lớp",
                student_phone: "SĐT",
                student_email: "Email",
                company_name: "Tên Công ty",
                company_tax_code: "Mã số thuế",
                company_address: "Địa chỉ",
                company_mentor_name: "Cán bộ hướng dẫn",
                company_mentor_phone: "SĐT CBHD",
                company_mentor_email: "Email CBHD",
                teacher_name: "Giảng viên hướng dẫn",
                grade_score: "Điểm số",
                grade_reason: "Diễn giải điểm",
                grade_feedback: "Nhận xét",
              },
            });
          }
        }
      }
      retries++;
    }, 100);
  };

  const registerBulkActions = () => {
    TableManager.registerBulkActions("batch_students_table", {
      countLabel: (count) => `Đã chọn: ${count}`,
      actions: [
        {
          id: "assign",
          label: "Phân công",
          icon: "fa-solid fa-user-plus",
          variant: "primary",
          onClick: ({ selectedIds }) => {
            document.querySelector("#bulk-student-count").textContent =
              selectedIds.length;

            const select = document.querySelector("#bulk-teacher-select");
            renderTeacherOptions(select, null);

            modalHandler.open("#modal-bulk-assign");
          },
        },
        {
          id: "export-selected",
          label: "Export đã chọn",
          tooltip: "Export các dòng sinh viên đã chọn",
          icon: "fa-solid fa-file-excel",
          variant: "outline",
          onClick: () => {
            document
              .querySelector('[data-tm="batch_students_table"]')
              ?.dispatchEvent(
                new CustomEvent("tm:export", {
                  detail: { mode: "selected" },
                }),
              );
          },
        },
        {
          id: "unassign",
          label: "Hủy phân công",
          icon: "fa-solid fa-user-minus",
          destructive: true,
          confirm: false,
          onClick: ({ selectedIds }) => {
            document.querySelector("#bulk-unassign-count").textContent =
              selectedIds.length;
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
      const viewDetailsBtn = e.target.closest(".btn-view-details");
      if (viewDetailsBtn) {
        const studentId = viewDetailsBtn.dataset.id;
        showStudentDetails(studentId);
        return;
      }

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
   * Xác nhận thay đổi phân công khi đợt đã công bố
   */
  const confirmPublishedAssignment = () => {
    return new Promise((resolve) => {
      const modal = document.querySelector(
        "#modal-confirm-published-assignment",
      );
      if (!modal) return resolve(true);

      const btnConfirm = modal.querySelector(
        "#btn-confirm-published-assignment",
      );
      const btnCancel = modal.querySelector("#btn-close-published-assignment");
      const btnClose = modal.querySelector(".modal__close");

      const cleanup = () => {
        btnConfirm.removeEventListener("click", onConfirm);
        btnCancel.removeEventListener("click", onCancel);
        btnClose.removeEventListener("click", onCancel);
      };

      const onConfirm = () => {
        cleanup();
        modalHandler.close();
        resolve(true);
      };

      const onCancel = () => {
        cleanup();
        modalHandler.close();
        resolve(false);
      };

      btnConfirm.addEventListener("click", onConfirm);
      btnCancel.addEventListener("click", onCancel);
      btnClose.addEventListener("click", onCancel);

      modalHandler.open("#modal-confirm-published-assignment");
    });
  };

  /**
   * Gọi API lưu phân công
   */
  const saveAssignments = async (assignments, reason) => {
    try {
      if (batchStatus === "published") {
        const confirmed = await confirmPublishedAssignment();
        if (!confirmed) {
          return false;
        }
      }

      const res = await fetch(`${apiBase}/${batchId}/bulk-save`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": window.CSRF_TOKEN || "",
        },
        body: JSON.stringify({
          reason: reason || "Quản trị viên cập nhật phân công.",
          assignments: assignments,
        }),
      });

      const data = await res.json();
      if (!res.ok) throw new Error(data.message || "Lỗi khi lưu dữ liệu.");

      toast.success("Thành công", "Đã cập nhật phân công.");
      setTimeout(() => window.location.reload(), 500);
      return true;
    } catch (error) {
      toast.error("Lỗi", error.message);
      return false;
    }
  };

  document
    .querySelector("#btn-confirm-bulk-assign")
    .addEventListener("click", async () => {
      const teacherId = document.querySelector("#bulk-teacher-select").value;
      if (!teacherId) {
        alert("Vui lòng chọn một giảng viên.");
        return;
      }

      const selectedIds = TableManager.getRowSelection("batch_students_table");
      const assignments = selectedIds.map((sid) => {
        return {
          batch_student_id: parseInt(sid),
          new_teacher_id: teacherId === "unassign" ? null : parseInt(teacherId),
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
        return {
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

  // Action: Publish Grades
  document
    .querySelector("#btn-publish-grades")
    ?.addEventListener("click", () => {
      modalHandler.open("#modal-publish-grades");
    });

  document
    .querySelector("#btn-confirm-publish-grades")
    ?.addEventListener("click", async () => {
      try {
        const res = await fetch(
          `${apiBase}/${batchId}/management/publish-grades`,
          {
            method: "POST",
            headers: { "X-CSRF-TOKEN": window.CSRF_TOKEN || "" },
          },
        );
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || "Lỗi khi công bố điểm.");
        modalHandler.close("#modal-publish-grades");
        toast.success("Thành công", data.message);
        setTimeout(() => window.location.reload(), 1000);
      } catch (e) {
        modalHandler.close("#modal-publish-grades");
        toast.error("Lỗi", e.message);
      }
    });

  // Edit grade submit
  document
    .querySelector("#edit-grade-form")
    ?.addEventListener("submit", async (e) => {
      e.preventDefault();
      const batchStudentId = document.querySelector(
        "#edit-grade-batch-student-id",
      ).value;
      const score = document.querySelector("#edit-grade-score").value;
      const reason = document.querySelector("#edit-grade-reason").value;
      const feedback = document.querySelector("#edit-grade-feedback").value;

      try {
        const res = await fetch(
          `${apiBase}/${batchId}/management/students/${batchStudentId}/grade`,
          {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": window.CSRF_TOKEN || "",
            },
            body: JSON.stringify({
              grade: score,
              score_reason: reason,
              feedback,
            }),
          },
        );
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || "Lỗi khi cập nhật điểm.");
        toast.success("Thành công", data.message);
        modalHandler.close();
        loadData();
      } catch (error) {
        toast.error("Lỗi", error.message);
      }
    });

  const showStudentDetails = (batchStudentId) => {
    const tm = TableManager.get("batch_students_table");
    const rowData = tm.data.find((r) => r.batch_student_id == batchStudentId);
    if (!rowData) return;

    const fallback =
      '<span style="color: var(--muted-foreground);">Chưa cập nhật</span>';

    const content = document.querySelector("#student-details-content");
    content.innerHTML = `
      <div class="grid grid-cols-3 gap-2 py-2 border-b" style="border-color: var(--border);">
        <span class="font-semibold" style="color: var(--muted-foreground);">Công ty:</span>
        <span class="col-span-2 font-medium">${rowData.company_name || fallback}</span>
      </div>
      <div class="grid grid-cols-3 gap-2 py-2 border-b" style="border-color: var(--border);">
        <span class="font-semibold" style="color: var(--muted-foreground);">Địa chỉ:</span>
        <span class="col-span-2">${rowData.company_address || fallback}</span>
      </div>
      <div class="grid grid-cols-3 gap-2 py-2 border-b" style="border-color: var(--border);">
        <span class="font-semibold" style="color: var(--muted-foreground);">Mã số thuế:</span>
        <span class="col-span-2">${rowData.company_tax_code || fallback}</span>
      </div>
      <div class="grid grid-cols-3 gap-2 py-2 border-b" style="border-color: var(--border);">
        <span class="font-semibold" style="color: var(--muted-foreground);">CBHD Doanh nghiệp:</span>
        <span class="col-span-2 font-medium">${rowData.company_mentor_name || fallback}</span>
      </div>
      <div class="grid grid-cols-3 gap-2 py-2 border-b" style="border-color: var(--border);">
        <span class="font-semibold" style="color: var(--muted-foreground);">SĐT CBHD:</span>
        <span class="col-span-2">${rowData.company_mentor_phone || fallback}</span>
      </div>
      <div class="grid grid-cols-3 gap-2 py-2">
        <span class="font-semibold" style="color: var(--muted-foreground);">Email CBHD:</span>
        <span class="col-span-2">${rowData.company_mentor_email || fallback}</span>
      </div>
    `;
    modalHandler.open("#modal-student-details");
  };

  const openEditGradeModal = (batchStudentId) => {
    const tm = TableManager.get("batch_students_table");
    const rowData = tm.data.find((r) => r.batch_student_id == batchStudentId);
    if (!rowData) return;

    document.querySelector("#edit-grade-student-name").textContent =
      `${rowData.student_name} - ${rowData.student_code}`;
    document.querySelector("#edit-grade-batch-student-id").value =
      batchStudentId;
    document.querySelector("#edit-grade-score").value =
      rowData.grade !== null ? rowData.grade : "";
    document.querySelector("#edit-grade-reason").value =
      rowData.score_reason || "";
    document.querySelector("#edit-grade-feedback").value =
      rowData.feedback || "";

    modalHandler.open("#modal-edit-grade");
  };

  /**
   * Helper thực hiện API phân công tự động
   */
  const performAutoAssign = async (method) => {
    try {
      const res = await fetch(`${apiBase}/${batchId}/auto-assign`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": window.CSRF_TOKEN || "",
        },
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
