document.addEventListener("DOMContentLoaded", () => {
  const batchId = window.CURRENT_BATCH_ID;
  const apiBase = window.API_BASE_URL;

  let supervisors = [];
  let assignments = [];
  let selectedStudentIds = []; // Array of batch_student_id

  const tbody = document.getElementById("assignments-tbody");
  const loader = document.getElementById("table-loader");
  const checkAll = document.getElementById("check-all-students");
  const bulkActionBar = document.getElementById("bulk-action-bar");
  const selectedCountText = document.getElementById("selected-count");

  const modalHandler = ModalHandler.instance;

  // Helpers
  const showLoader = () => loader.classList.remove("hidden");
  const hideLoader = () => loader.classList.add("hidden");

  // Fetch Data
  const loadData = async () => {
    showLoader();
    try {
      const [supRes, assRes] = await Promise.all([
        fetch(`${apiBase}/${batchId}/supervisors`),
        fetch(`${apiBase}/${batchId}/assignments`),
      ]);

      if (!supRes.ok || !assRes.ok) throw new Error("Không thể tải dữ liệu");

      const supData = await supRes.json();
      const assData = await assRes.json();

      supervisors = supData.data;
      assignments = assData.data;

      selectedStudentIds = [];
      updateSelectionUI();
      renderTable();
    } catch (error) {
      if (window.toast) toast.error("Lỗi", error.message);
      else alert(error.message);
    } finally {
      hideLoader();
    }
  };

  // Render Table
  const renderTable = () => {
    tbody.innerHTML = "";

    if (assignments.length === 0) {
      tbody.innerHTML =
        '<tr><td colspan="10" class="text-center py-4">Không có sinh viên nào trong đợt.</td></tr>';
      return;
    }

    assignments.forEach((ass) => {
      const tr = document.createElement("tr");
      tr.setAttribute("data-batch-student-id", ass.batch_student_id);

      const isSelected = selectedStudentIds.includes(ass.batch_student_id);
      if (isSelected) tr.classList.add("assignment-row--selected");

      // Inline Edit UI
      const teacherName = ass.teacher_name || "<span>Chưa phân công</span>";

      tr.innerHTML = `
        <td>
          <input type="checkbox" class="student-checkbox" value="${ass.batch_student_id}" ${isSelected ? "checked" : ""}>
        </td>
        <td>${ass.student_code || "N/A"}</td>
        <td class="font-medium">${ass.student_name}</td>
        <td>${ass.student_phone || "N/A"}</td>
        <td>${ass.classroom_name || ""}</td>
        <td>
          <div class="teacher-cell" data-assignment-id="${ass.assignment_id || ""}" data-batch-student-id="${ass.batch_student_id}">
            <div class="teacher-display ${window.BATCH_STATUS === "closed" ? "teacher-display--disabled" : ""}">
              <span class="teacher-name-text">${teacherName}</span>
              ${window.BATCH_STATUS !== "closed" ? '<i class="fa-solid fa-pen edit-icon"></i>' : ""}
            </div>
            <div class="teacher-edit-container">
              <select class="field__input teacher-inline-select">
                <option value="">-- Chọn Giảng viên --</option>
                <option value="unassign" class="font-bold" ${!ass.teacher_id ? "disabled" : ""}>Không có</option>
                <hr>
                ${supervisors
                  .map(
                    (sup) => `
                  <option value="${sup.teacher_id}" ${sup.teacher_id == ass.teacher_id ? "selected" : ""}>
                    ${sup.teacher_name} (${sup.current_assigned}/${sup.max_students})
                  </option>
                `,
                  )
                  .join("")}
              </select>
            </div>
          </div>
        </td>
      `;

      tbody.appendChild(tr);
    });

    attachEventListeners();
  };

  const attachEventListeners = () => {
    // Checkbox cá nhân
    document.querySelectorAll(".student-checkbox").forEach((cb) => {
      cb.addEventListener("change", (e) => {
        const id = parseInt(e.target.value);
        if (e.target.checked) {
          if (!selectedStudentIds.includes(id)) selectedStudentIds.push(id);
        } else {
          selectedStudentIds = selectedStudentIds.filter((sid) => sid !== id);
        }
        updateSelectionUI();
        renderTable(); // Re-render to update row background
      });
    });

    // Inline Edit Toggle
    document.querySelectorAll(".teacher-display").forEach((display) => {
      display.addEventListener("click", (e) => {
        if (window.BATCH_STATUS === "closed") return;
        const container = display.closest(".teacher-cell");
        display.classList.add("hidden");
        container
          .querySelector(".teacher-edit-container")
          .classList.add("active");
        container.querySelector(".teacher-inline-select").focus();
      });
    });

    // Inline Edit Change
    document.querySelectorAll(".teacher-inline-select").forEach((select) => {
      select.addEventListener("change", async (e) => {
        const cell = select.closest(".teacher-cell");
        const batchStudentId = cell.getAttribute("data-batch-student-id");
        const assignmentId = cell.getAttribute("data-assignment-id");
        const newTeacherIdValue = select.value;

        if (newTeacherIdValue === "") return;

        let newTeacherId =
          newTeacherIdValue === "unassign" ? null : parseInt(newTeacherIdValue);

        let reason = "Cập nhật phân công trực tiếp tại bảng";
        if (window.BATCH_STATUS === "published") {
          reason = prompt(
            "Đợt thực tập đã được công bố. Vui lòng nhập lý do thay đổi:",
            "",
          );
          if (reason === null) {
            renderTable(); // Reset dropdown
            return;
          }
          if (reason.trim() === "") {
            toast.warning(
              "Yêu cầu",
              "Bạn phải nhập lý do khi đợt thực tập đã công bố.",
            );
            renderTable();
            return;
          }
        }

        // Lưu ngay lập tức qua AJAX
        try {
          showLoader();
          const res = await fetch(`${apiBase}/${batchId}/bulk-save`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
              reason: reason,
              assignments: [
                {
                  assignment_id: assignmentId ? parseInt(assignmentId) : null,
                  batch_student_id: parseInt(batchStudentId),
                  new_teacher_id: newTeacherId,
                },
              ],
            }),
          });

          const data = await res.json();
          if (!res.ok) throw new Error(data.message || "Lỗi khi lưu thay đổi");

          if (window.toast)
            toast.success("Thành công", "Đã cập nhật phân công.");
          await loadData();
        } catch (err) {
          if (window.toast) toast.error("Lỗi", err.message);
          renderTable(); // Reset UI on error
        } finally {
          hideLoader();
        }
      });

      // Blur to cancel
      select.addEventListener("blur", (e) => {
        setTimeout(() => {
          const cell = select.closest(".teacher-cell");
          cell.querySelector(".teacher-display").classList.remove("hidden");
          cell
            .querySelector(".teacher-edit-container")
            .classList.remove("active");
        }, 200);
      });
    });
  };

  // Selection Logic
  checkAll.addEventListener("change", (e) => {
    if (e.target.checked) {
      selectedStudentIds = assignments.map((ass) => ass.batch_student_id);
    } else {
      selectedStudentIds = [];
    }
    updateSelectionUI();
    renderTable();
  });

  const updateSelectionUI = () => {
    const count = selectedStudentIds.length;
    if (count > 0) {
      bulkActionBar.classList.remove("hidden");
      selectedCountText.textContent = `Đã chọn: ${count}`;
    } else {
      bulkActionBar.classList.add("hidden");
    }
    checkAll.checked = count === assignments.length && assignments.length > 0;
  };

  document
    .getElementById("btn-cancel-selection")
    .addEventListener("click", () => {
      selectedStudentIds = [];
      updateSelectionUI();
      renderTable();
    });

  // Bulk Assign Modal
  document.getElementById("btn-bulk-assign").addEventListener("click", () => {
    document.getElementById("bulk-student-count").textContent =
      selectedStudentIds.length;

    const select = document.getElementById("bulk-teacher-select");
    select.innerHTML = '<option value="">-- Chọn Giảng viên --</option>';
    supervisors.forEach((sup) => {
      const isFull = sup.current_assigned >= sup.max_students;
      select.innerHTML += `<option value="${sup.teacher_id}" ${isFull ? "disabled" : ""}>
        ${sup.teacher_name} (${sup.current_assigned}/${sup.max_students}) ${isFull ? "[Hết chỗ]" : ""}
      </option>`;
    });

    modalHandler.open("#modal-bulk-assign");
  });

  document
    .getElementById("btn-close-bulk-modal")
    .addEventListener("click", () => {
      modalHandler.close();
    });

  document
    .getElementById("btn-confirm-bulk-assign")
    .addEventListener("click", async () => {
      const teacherId = document.getElementById("bulk-teacher-select").value;
      if (!teacherId) {
        alert("Vui lòng chọn giảng viên.");
        return;
      }

      try {
        const btn = document.getElementById("btn-confirm-bulk-assign");
        btn.setAttribute("disabled", "disabled");
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...`;

        let reason = "Phân công hàng loạt từ giao diện quản lý";
        if (window.BATCH_STATUS === "published") {
          reason = prompt(
            "Đợt thực tập đã được công bố. Vui lòng nhập lý do thay đổi cho các sinh viên này:",
            "",
          );
          if (reason === null) return;
          if (reason.trim() === "") {
            toast.warning(
              "Yêu cầu",
              "Bạn phải nhập lý do khi đợt thực tập đã công bố.",
            );
            return;
          }
        }

        const payload = {
          reason: reason,
          assignments: selectedStudentIds.map((sid) => {
            const ass = assignments.find((a) => a.batch_student_id === sid);
            return {
              assignment_id: ass.assignment_id || null,
              batch_student_id: sid,
              new_teacher_id: parseInt(teacherId),
            };
          }),
        };

        const res = await fetch(`${apiBase}/${batchId}/bulk-save`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload),
        });

        const response = await res.json();
        if (!res.ok)
          throw new Error(response.message || "Lỗi khi lưu thay đổi");

        if (window.toast)
          toast.success("Thành công", "Đã phân công hàng loạt thành công.");
        modalHandler.close();
        await loadData();
      } catch (err) {
        if (window.toast) toast.error("Lỗi", err.message);
      } finally {
        const btn = document.getElementById("btn-confirm-bulk-assign");
        btn.removeAttribute("disabled");
        btn.innerHTML = `Xác nhận phân công`;
      }
    });

  // Bulk Unassign Logic
  document.getElementById("btn-bulk-unassign").addEventListener("click", () => {
    const studentsToUnassign = assignments.filter(
      (a) => selectedStudentIds.includes(a.batch_student_id) && a.assignment_id,
    );

    if (studentsToUnassign.length === 0) {
      toast.warning("Thông báo", "Không có sinh viên nào có phân công để hủy.");
      return;
    }

    document.getElementById("bulk-unassign-count").textContent =
      studentsToUnassign.length;
    modalHandler.open("#modal-bulk-unassign");
  });

  document
    .getElementById("btn-close-unassign-modal")
    .addEventListener("click", () => {
      modalHandler.close();
    });

  document
    .getElementById("btn-confirm-bulk-unassign")
    .addEventListener("click", async () => {
      const studentsToUnassign = assignments.filter(
        (a) =>
          selectedStudentIds.includes(a.batch_student_id) && a.assignment_id,
      );

      if (studentsToUnassign.length === 0) return;

      let reason = "Hủy phân công hàng loạt từ giao diện quản lý";
      if (window.BATCH_STATUS === "published") {
        reason = prompt(
          "Đợt thực tập đã được công bố. Vui lòng nhập lý do hủy phân công cho các sinh viên này:",
          "",
        );
        if (reason === null) return;
        if (reason.trim() === "") {
          toast.warning(
            "Yêu cầu",
            "Bạn phải nhập lý do khi đợt thực tập đã công bố.",
          );
          return;
        }
      }

      try {
        const btn = document.getElementById("btn-confirm-bulk-unassign");
        btn.setAttribute("disabled", "disabled");
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...`;

        const payload = {
          reason: reason,
          assignments: studentsToUnassign.map((a) => ({
            assignment_id: a.assignment_id,
            batch_student_id: a.batch_student_id,
            new_teacher_id: null,
          })),
        };

        const res = await fetch(`${apiBase}/${batchId}/bulk-save`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload),
        });

        const response = await res.json();
        if (!res.ok)
          throw new Error(response.message || "Lỗi khi hủy phân công");

        if (window.toast)
          toast.success("Thành công", "Đã hủy phân công hàng loạt thành công.");

        modalHandler.close();
        await loadData();
      } catch (err) {
        if (window.toast) toast.error("Lỗi", err.message);
      } finally {
        const btn = document.getElementById("btn-confirm-bulk-unassign");
        btn.removeAttribute("disabled");
        btn.innerHTML = `Xác nhận Hủy`;
      }
    });

  // Auto Assign Logic
  document.getElementById("btn-auto-assign").addEventListener("click", () => {
    modalHandler.open("#modal-auto-assign");
  });

  document.getElementById("btn-close-modal").addEventListener("click", () => {
    modalHandler.close();
  });

  document
    .getElementById("btn-confirm-auto-assign")
    .addEventListener("click", async () => {
      if (window.BATCH_STATUS === "closed") return;
      const method = document.querySelector(
        'input[name="auto_method"]:checked',
      ).value;

      try {
        const btn = document.getElementById("btn-confirm-auto-assign");
        btn.setAttribute("disabled", "disabled");
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...`;

        const res = await fetch(`${apiBase}/${batchId}/auto-assign`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ method }),
        });

        const response = await res.json();
        if (!res.ok)
          throw new Error(response.message || "Lỗi phân công tự động");

        modalHandler.close();
        if (window.toast) toast.success("Thành công", response.message);
        await loadData();
      } catch (err) {
        if (window.toast) toast.error("Lỗi", err.message);
      } finally {
        const btn = document.getElementById("btn-confirm-auto-assign");
        btn.removeAttribute("disabled");
        btn.innerHTML = `Tiến hành phân công`;
      }
    });

  // Khởi chạy
  if (window.BATCH_STATUS === "closed") {
    document
      .getElementById("btn-auto-assign")
      .setAttribute("disabled", "disabled");
    document
      .getElementById("check-all-students")
      .setAttribute("disabled", "disabled");
  }
  loadData();
});
