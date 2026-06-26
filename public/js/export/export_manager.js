export class ExportManager {
  /**
   * Đăng ký chức năng export cho một TableInstance
   * @param {TableInstance} tableInstance Instance của bảng
   * @param {Object} config Cấu hình export (source, source_id, filename, columnsMap, endpoint)
   */
  static register(tableInstance, config) {
    if (!tableInstance || !config.source || !config.columnsMap) {
      console.error("ExportManager: Thiếu cấu hình bắt buộc");
      return;
    }

    const endpoint = config.endpoint || "/api/v1/export";
    const wrapper = document.createElement("div");
    wrapper.className = "tm-export-wrapper";

    // Tạo nút toggle
    const btnToggle = document.createElement("button");
    btnToggle.className = "btn";
    btnToggle.setAttribute("data-variant", "primary");
    btnToggle.setAttribute("data-size", "md");
    btnToggle.innerHTML =
      '<i class="fa-solid fa-download"></i>Xuất Excel';
    btnToggle.type = "button";

    // Tạo menu dropdown
    const menu = document.createElement("div");
    menu.className = "tm-export-menu";

    const btnCurrent = document.createElement("button");
    btnCurrent.className = "tm-export-item";
    btnCurrent.title =
      "Xuất các dòng đang hiển thị trên trang (đã áp dụng tìm kiếm/lọc/sắp xếp)";
    btnCurrent.innerHTML =
      '<i class="fa-solid fa-table mr-1"></i>Xuất dữ liệu đang hiển thị';
    btnCurrent.type = "button";

    const btnAll = document.createElement("button");
    btnAll.className = "tm-export-item";
    btnAll.title =
      "Xuất tất cả dữ liệu (bỏ qua phân trang/tìm kiếm/lọc/sắp xếp)";
    btnAll.innerHTML =
      '<i class="fa-solid fa-database mr-1"></i>Xuất toàn bộ dữ liệu';
    btnAll.type = "button";

    const btnSelected = document.createElement("button");
    btnSelected.className = "tm-export-item";
    btnSelected.title = "Chỉ xuất các dòng đã được chọn bằng checkbox";
    btnSelected.innerHTML =
      '<i class="fa-solid fa-square-check mr-1"></i>Xuất dòng đã chọn (0)';
    btnSelected.type = "button";

    menu.appendChild(btnCurrent);
    menu.appendChild(btnAll);

    // Chỉ hiển thị nút "Xuất đã chọn" nếu bảng có selectable
    const hasSelectable =
      tableInstance.root?.hasAttribute("data-tm-selectable") ?? false;
    if (hasSelectable) {
      menu.appendChild(btnSelected);
    }

    wrapper.appendChild(btnToggle);
    wrapper.appendChild(menu);

    // Xóa nút export cũ nếu đã tồn tại để tránh render trùng lặp
    const toolbar = tableInstance.root.querySelector(".tm-toolbar-actions");
    const target = toolbar || tableInstance.root;
    const existing = target.querySelector(".tm-export-wrapper");
    if (existing) existing.remove();

    if (toolbar) {
      toolbar.appendChild(wrapper);
    } else {
      tableInstance.root.insertBefore(wrapper, tableInstance.root.firstChild);
    }

    // Xử lý logic đóng mở dropdown
    btnToggle.addEventListener("click", (e) => {
      e.stopPropagation();

      document.querySelectorAll(".tm-export-wrapper.is-open").forEach((el) => {
        if (el !== wrapper) el.classList.remove("is-open");
      });

      // Cập nhật số lượng row đang chọn
      if (hasSelectable) {
        const selectedIds = tableInstance.getRowSelection();
        btnSelected.innerHTML = `<i class="fa-solid fa-square-check mr-1"></i>Xuất dòng đã chọn (${selectedIds.length})`;
      }

      wrapper.classList.toggle("is-open");
    });

    document.addEventListener("click", (e) => {
      if (!wrapper.contains(e.target)) {
        wrapper.classList.remove("is-open");
      }
    });

    // Tạo Column Selection Modal
    let modalEl = null;

    const getOrCreateModal = () => {
      if (modalEl) return modalEl;
      modalEl = ExportManager.#buildColumnModal(config.columnsMap);
      document.body.appendChild(modalEl);
      return modalEl;
    };

    /**
     * Mở modal chọn cột, trả về Promise resolve khi user xác nhận hoặc reject khi hủy.
     * @param {string} mode
     * @returns {Promise<{exportColumns: string[], metadata: string[]}>}
     */
    const openColumnModal = (mode) => {
      return new Promise((resolve, reject) => {
        const modal = getOrCreateModal();

        // Reset tất cả checkbox về checked
        modal
          .querySelectorAll('.export-col-checkbox input[type="checkbox"]')
          .forEach((cb) => (cb.checked = true));

        // Update "Chọn tất cả"
        const selectAllCb = modal.querySelector("#export-select-all");
        if (selectAllCb) selectAllCb.checked = true;

        // Hiển thị thông tin mode
        const modeLabels = {
          current_view: "Xuất dữ liệu đang hiển thị",
          all: "Xuất toàn bộ dữ liệu",
          selected: "Xuất dòng đã chọn",
        };
        const modeInfo = modal.querySelector(".export-modal__mode-info");
        if (modeInfo) {
          modeInfo.textContent = modeLabels[mode] || mode;
        }

        // Hiện modal
        modal.setAttribute("data-state", "open");

        // Hiện overlay
        let overlay = document.querySelector(".export-modal-overlay");
        if (!overlay) {
          overlay = document.createElement("div");
          overlay.className = "export-modal-overlay";
          document.body.appendChild(overlay);
        }
        overlay.setAttribute("data-state", "open");

        // Cleanup listeners on resolve/reject
        const cleanup = () => {
          modal.setAttribute("data-state", "closed");
          overlay.setAttribute("data-state", "closed");
          btnConfirm.removeEventListener("click", onConfirm);
          btnCancel.removeEventListener("click", onCancel);
          overlay.removeEventListener("click", onCancel);
        };

        const btnConfirm = modal.querySelector(".export-modal__btn-confirm");
        const btnCancel = modal.querySelector(".export-modal__btn-cancel");

        const onConfirm = () => {
          const checkedKeys = [];
          modal
            .querySelectorAll(
              '.export-col-checkbox input[type="checkbox"]:checked',
            )
            .forEach((cb) => checkedKeys.push(cb.value));

          if (checkedKeys.length === 0) {
            if (window.toast)
              window.toast.warning(
                "Chưa chọn cột",
                "Vui lòng chọn ít nhất một cột để xuất.",
              );
            return;
          }

          // Build metadata
          const metadata = ExportManager.#buildMetadata(
            config,
            tableInstance,
            mode,
          );

          cleanup();
          resolve({ exportColumns: checkedKeys, metadata });
        };

        const onCancel = () => {
          cleanup();
          reject(new Error("cancelled"));
        };

        btnConfirm.addEventListener("click", onConfirm);
        btnCancel.addEventListener("click", onCancel);
        overlay.addEventListener("click", onCancel);
      });
    };

    // Hàm xử lý gọi API export
    const handleExport = async (mode) => {
      wrapper.classList.remove("is-open");

      try {
        // Mở modal chọn cột trước khi export
        const { exportColumns, metadata } = await openColumnModal(mode);

        btnToggle.classList.add("tm-export-loading");
        btnToggle.innerHTML =
          '<i class="fa-solid fa-spinner fa-spin mr-1"></i>Đang xuất...';
        btnToggle.disabled = true;

        const state = tableInstance.getState();
        const payload = {
          source: config.source,
          source_id: config.source_id,
          mode: mode,
          columns: config.columnsMap,
          export_columns: exportColumns,
          metadata: metadata,
          filename: config.filename || "export_data",
          filters: state.filters || [],
          sort: state.sort || null,
          selected_ids:
            mode === "selected" ? tableInstance.getRowSelection() : [],
        };

        const response = await fetch(endpoint, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(payload),
        });

        if (!response.ok) {
          let errorMsg = "Có lỗi xảy ra khi xuất dữ liệu";
          try {
            const errorData = await response.json();
            if (errorData.message) errorMsg = errorData.message;
          } catch (e) {
            // response is not json
          }
          throw new Error(errorMsg);
        }

        // Lấy binary data và tải file
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;

        // Lấy tên file từ header Content-Disposition nếu có
        let filename = `${payload.filename}.xlsx`;
        const disposition = response.headers.get("Content-Disposition");
        if (disposition && disposition.indexOf("filename=") !== -1) {
          const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
          const matches = filenameRegex.exec(disposition);
          if (matches != null && matches[1]) {
            filename = matches[1].replace(/['"]/g, "");
          }
        }

        a.download = filename;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        a.remove();

        if (window.toast)
          window.toast.success("Thành công", "Xuất dữ liệu thành công");
      } catch (error) {
        if (error.message === "cancelled") return;

        console.error("Export error:", error);
        if (window.toast)
          window.toast.error(
            "Lỗi",
            error.message || "Lỗi hệ thống khi xuất dữ liệu",
          );
      } finally {
        btnToggle.classList.remove("tm-export-loading");
        btnToggle.innerHTML =
          '<i class="fa-solid fa-download"></i>Xuất Excel';
        btnToggle.disabled = false;
      }
    };

    // Gắn sự kiện cho các nút export
    btnCurrent.addEventListener("click", () => handleExport("current_view"));
    btnAll.addEventListener("click", () => handleExport("all"));
    btnSelected.addEventListener("click", () => handleExport("selected"));
  }

  /**
   * Tạo DOM element cho modal chọn cột (F3: Column Selection)
   * @param {Object} columnsMap { key: label }
   * @returns {HTMLElement}
   */
  static #buildColumnModal(columnsMap) {
    const modal = document.createElement("div");
    modal.className = "modal export-column-modal";
    modal.setAttribute("data-state", "closed");

    const keys = Object.keys(columnsMap);

    let checkboxesHtml = "";
    keys.forEach((key) => {
      checkboxesHtml += `
        <label class="export-col-checkbox">
          <input type="checkbox" value="${key}" checked />
          <span>${columnsMap[key]}</span>
        </label>`;
    });

    modal.innerHTML = `
      <div class="modal__header">
        <h3 class="modal__title">
          <i class="fa-solid fa-columns mr-1"></i>Chọn cột xuất Excel
        </h3>
        <p class="modal__description">
          Chế độ: <strong class="export-modal__mode-info">--</strong>
        </p>
      </div>

      <div class="export-modal__body">
        <label class="export-col-select-all">
          <input type="checkbox" id="export-select-all" checked />
          <span>Chọn tất cả</span>
        </label>
        <hr class="separator" />
        <div class="export-col-grid">
          ${checkboxesHtml}
        </div>
      </div>

      <div class="modal__footer">
        <button type="button" class="btn export-modal__btn-cancel" data-variant="outline" data-size="md">
          <i class="fa-solid fa-xmark mr-1"></i>Hủy
        </button>
        <button type="button" class="btn export-modal__btn-confirm" data-variant="primary" data-size="md">
          <i class="fa-solid fa-download"></i>Xác nhận
        </button>
      </div>
    `;

    // toggle checkall
    const selectAllCb = modal.querySelector("#export-select-all");
    const allCheckboxes = modal.querySelectorAll(
      '.export-col-checkbox input[type="checkbox"]',
    );

    selectAllCb.addEventListener("change", () => {
      allCheckboxes.forEach((cb) => (cb.checked = selectAllCb.checked));
    });

    // Đồng bộ trạng thái checkall
    allCheckboxes.forEach((cb) => {
      cb.addEventListener("change", () => {
        const allChecked = [...allCheckboxes].every((c) => c.checked);
        const someChecked = [...allCheckboxes].some((c) => c.checked);
        selectAllCb.checked = allChecked;
        selectAllCb.indeterminate = !allChecked && someChecked;
      });
    });

    return modal;
  }

  /**
   * Tạo mảng metadata text cho file Excel
   * @param {Object} config
   * @param {TableInstance} tableInstance
   * @param {string} mode
   * @returns {string[]}
   */
  static #buildMetadata(config, tableInstance, mode) {
    const lines = [];

    // Dòng 1: Tiêu đề
    const title =
      config.metadataTitle || config.filename?.replace(/-/g, " ") || "Báo cáo";
    lines.push(title);

    // Dòng 2: Thời gian bắt đầu - kết thúc đợt
    if (config.metadataDateRange) {
      lines.push(config.metadataDateRange);
    }

    // Dòng 3: Ngày xuất
    const now = new Date();
    const dateStr = now.toLocaleDateString("vi-VN", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
    lines.push(`Ngày xuất: ${dateStr}`);

    // Dòng 3: Mô tả chế độ xuất
    const modeLabels = {
      current_view: "Xuất dữ liệu đang hiển thị",
      all: "Xuất toàn bộ dữ liệu",
      selected: `Xuất ${tableInstance.getRowSelection().length} dòng đã chọn`,
    };
    lines.push(`Chế độ: ${modeLabels[mode] || mode}`);

    // Dòng 4: Mô tả bộ lọc đang áp dụng (nếu có)
    const state = tableInstance.getState();
    if (state.filters && state.filters.length > 0) {
      const filterParts = state.filters
        .map((f) => {
          const label = config.columnsMap[f.col] || f.col;
          return `${label}: "${f.value}"`;
        })
        .filter(Boolean);

      if (filterParts.length > 0) {
        lines.push(`Bộ lọc: ${filterParts.join(", ")}`);
      }
    }

    return lines;
  }
}
