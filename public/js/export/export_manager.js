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

    // Xóa export wrapper cũ nếu đã tồn tại để tránh duplicate
    const existingWrapper =
      tableInstance.root.querySelector(".tm-export-wrapper");
    if (existingWrapper) {
      existingWrapper.remove();
    }

    const endpoint = config.endpoint || "/api/v1/export";
    const wrapper = document.createElement("div");
    wrapper.className = "tm-export-wrapper";

    // Tạo nút toggle
    const btnToggle = document.createElement("button");
    btnToggle.className = "btn";
    btnToggle.setAttribute("data-variant", config.triggerVariant || "outline");
    btnToggle.setAttribute("data-size", config.triggerSize || "md");
    const triggerHtml = `<i class="fa-solid ${config.triggerIcon || "fa-download"}"></i>${config.triggerLabel || "Export Excel"}<i class="fa-solid fa-chevron-down text-xs"></i>`;
    btnToggle.innerHTML = triggerHtml;
    btnToggle.type = "button";

    // Tạo menu dropdown
    const menu = document.createElement("div");
    menu.className = "tm-export-menu";

    const btnCurrent = document.createElement("button");
    btnCurrent.className = "tm-export-item";
    btnCurrent.title =
      "Export các dòng đang hiển thị trên trang (đã áp dụng tìm kiếm/lọc/sắp xếp)";
    btnCurrent.innerHTML =
      '<i class="fa-solid fa-table mr-1"></i>Dữ liệu đang hiển thị';
    btnCurrent.type = "button";

    const btnAll = document.createElement("button");
    btnAll.className = "tm-export-item";
    btnAll.title =
      "Export tất cả dữ liệu (bỏ qua phân trang/tìm kiếm/lọc/sắp xếp)";
    btnAll.innerHTML =
      '<i class="fa-solid fa-database mr-1"></i>Toàn bộ dữ liệu';
    btnAll.type = "button";

    const btnSelected = document.createElement("button");
    btnSelected.className = "tm-export-item";
    btnSelected.title = "Chỉ export các dòng đã được chọn bằng checkbox";
    btnSelected.innerHTML =
      '<i class="fa-solid fa-square-check mr-1"></i>Các dòng đã chọn (0)';
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

    // Chèn vào target tường minh hoặc vùng actions của table.
    const target = config.target
      ? document.querySelector(config.target)
      : tableInstance.root.querySelector(".tm-toolbar-actions");
    if (target) {
      target.appendChild(wrapper);
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
        btnSelected.innerHTML = `<i class="fa-solid fa-square-check mr-1"></i>Các dòng đã chọn (${selectedIds.length})`;
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
      modalEl = ExportManager.#buildColumnModal(config.columnsMap, config.columnGroups);
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
        modal.syncSelectionSummary?.();

        // Hiển thị thông tin mode
        const modeLabels = {
          current_view: "Export dữ liệu đang hiển thị",
          all: "Export toàn bộ dữ liệu",
          selected: "Export dòng đã chọn",
        };
        const modeInfo = modal.querySelector(".export-modal__mode-info");
        if (modeInfo) {
          modeInfo.textContent = modeLabels[mode] || mode;
        }

        const modalHandler = ModalHandler.instance;
        let settled = false;

        const cleanup = () => {
          btnConfirm.removeEventListener("click", onConfirm);
          btnCancel.removeEventListener("click", onCancel);
          modal.removeEventListener("modal:close", onModalClose);
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
              window.toast.warn(
                "Chưa chọn cột",
                "Vui lòng chọn ít nhất một cột để export.",
              );
            return;
          }

          // Build metadata
          const metadata = ExportManager.#buildMetadata(
            config,
            tableInstance,
            mode,
          );

          settled = true;
          cleanup();
          modalHandler.close();
          resolve({ exportColumns: checkedKeys, metadata });
        };

        const onCancel = () => {
          settled = true;
          cleanup();
          modalHandler.close();
          reject(new Error("cancelled"));
        };

        const onModalClose = () => {
          if (settled) return;
          settled = true;
          cleanup();
          reject(new Error("cancelled"));
        };

        btnConfirm.addEventListener("click", onConfirm);
        btnCancel.addEventListener("click", onCancel);
        modal.addEventListener("modal:close", onModalClose);
        modalHandler.open(`#${modal.id}`);
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
          '<i class="fa-solid fa-spinner fa-spin mr-1"></i>Đang export...';
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
          window.toast.success("Thành công", "Export dữ liệu thành công");
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
        btnToggle.innerHTML = triggerHtml;
        btnToggle.disabled = false;
      }
    };

    // Gắn sự kiện cho các nút export
    btnCurrent.addEventListener("click", () => handleExport("current_view"));
    btnAll.addEventListener("click", () => handleExport("all"));
    btnSelected.addEventListener("click", () => handleExport("selected"));

    tableInstance.root.addEventListener("tm:export", (event) => {
      const mode = event.detail?.mode;
      if (["current_view", "all", "selected"].includes(mode)) {
        handleExport(mode);
      }
    });
  }

  /**
   * Tạo DOM element cho modal chọn cột (F3: Column Selection)
   * @param {Object} columnsMap { key: label }
   * @returns {HTMLElement}
   */
  static #buildColumnModal(columnsMap, columnGroups = null) {
    const modal = document.createElement("div");
    modal.className = "modal export-column-modal detail-modal";
    modal.id = `export-column-modal-${Math.random().toString(36).slice(2)}`;
    modal.setAttribute("data-state", "closed");

    const keys = Object.keys(columnsMap);

    const checkboxHtml = (key) => `
        <label class="export-col-checkbox">
          <input type="checkbox" value="${key}" checked />
          <span>${columnsMap[key]}</span>
        </label>`;
    const configuredGroups = Array.isArray(columnGroups) ? columnGroups : [];
    const groupedKeys = new Set(configuredGroups.flatMap((group) => group.columns || []));
    const remainingKeys = keys.filter((key) => !groupedKeys.has(key));
    const groups = configuredGroups.length
      ? [...configuredGroups, ...(remainingKeys.length ? [{ label: "Khác", columns: remainingKeys }] : [])]
      : [{ label: "Các cột dữ liệu", columns: keys }];
    const groupsHtml = groups.map((group) => `
      <section class="export-col-group">
        <h4 class="export-col-group__title">${group.label}</h4>
        <div class="export-col-grid">
          ${(group.columns || []).filter((key) => columnsMap[key]).map(checkboxHtml).join("")}
        </div>
      </section>`).join("");

    modal.innerHTML = `
      <div class="modal__header">
        <h3 class="modal__title">
          <i class="fa-solid fa-file-excel mr-1"></i>Export dữ liệu
        </h3>
        <p class="modal__description">
          Chế độ export: <span class="badge export-modal__mode-info" data-variant='primary'></span>
        </p>
      </div>

      <div class="export-modal__body">
        <div class="export-selection-toolbar">
          <span class="export-selection-summary"></span>
          <div class="export-selection-toolbar__actions">
            <button type="button" class="btn export-select-all" data-variant="outline" data-size="sm">Chọn tất cả</button>
            <button type="button" class="btn export-clear-all" data-variant="outline" data-size="sm">Bỏ chọn</button>
          </div>
        </div>
        <div class="export-col-groups">${groupsHtml}</div>
      </div>

      <div class="modal__footer">
        <button type="button" class="btn export-modal__btn-cancel" data-variant="outline" data-size="md">
          <i class="fa-solid fa-xmark mr-1"></i>Hủy
        </button>
        <button type="button" class="btn export-modal__btn-confirm" data-variant="primary" data-size="md">
          <i class="fa-solid fa-file-excel"></i>Export file
        </button>
      </div>

      <button class="modal__close" type="button" aria-label="Đóng" data-modal-close><i class="fa-solid fa-xmark"></i></button>
    `;

    const allCheckboxes = modal.querySelectorAll(
      '.export-col-checkbox input[type="checkbox"]',
    );

    const summary = modal.querySelector(".export-selection-summary");
    const syncSelectionSummary = () => {
      const selected = [...allCheckboxes].filter((cb) => cb.checked).length;
      summary.textContent = `${selected}/${allCheckboxes.length} cột đã chọn`;
    };
    modal.syncSelectionSummary = syncSelectionSummary;
    modal.querySelector(".export-select-all").addEventListener("click", () => {
      allCheckboxes.forEach((cb) => (cb.checked = true));
      syncSelectionSummary();
    });
    modal.querySelector(".export-clear-all").addEventListener("click", () => {
      allCheckboxes.forEach((cb) => (cb.checked = false));
      syncSelectionSummary();
    });
    allCheckboxes.forEach((cb) => cb.addEventListener("change", syncSelectionSummary));
    syncSelectionSummary();

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
      current_view: "Export dữ liệu đang hiển thị",
      all: "Export toàn bộ dữ liệu",
      selected: `Export ${tableInstance.getRowSelection().length} dòng đã chọn`,
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
