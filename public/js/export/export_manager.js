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
      '<i class="fa-solid fa-file-export mr-1"></i>Xuất Excel';
    btnToggle.type = "button";

    // Tạo menu dropdown
    const menu = document.createElement("div");
    menu.className = "tm-export-menu";

    const btnCurrent = document.createElement("button");
    btnCurrent.className = "tm-export-item";
    btnCurrent.innerHTML =
      '<i class="fa-solid fa-table mr-1"></i></i>Xuất dữ liệu đang hiển thị';
    btnCurrent.type = "button";

    const btnAll = document.createElement("button");
    btnAll.className = "tm-export-item";
    btnAll.innerHTML =
      '<i class="fa-solid fa-database mr-1"></i>Xuất toàn bộ dữ liệu';
    btnAll.type = "button";

    const btnSelected = document.createElement("button");
    btnSelected.className = "tm-export-item";
    btnSelected.innerHTML =
      '<i class="fa-solid fa-square-check mr-1"></i>Xuất dòng đã chọn (0)';
    btnSelected.type = "button";

    menu.appendChild(btnCurrent);
    menu.appendChild(btnAll);
    menu.appendChild(btnSelected);

    wrapper.appendChild(btnToggle);
    wrapper.appendChild(menu);

    // Chèn vào toolbar của table
    const toolbar = tableInstance.root.querySelector(".tm-toolbar-actions");
    if (toolbar) {
      toolbar.appendChild(wrapper);
    } else {
      tableInstance.root.insertBefore(wrapper, tableInstance.root.firstChild);
    }

    // Xử lý logic đóng mở dropdown
    btnToggle.addEventListener("click", (e) => {
      e.stopPropagation();

      // Đóng các dropdown khác nếu có
      document.querySelectorAll(".tm-export-wrapper.is-open").forEach((el) => {
        if (el !== wrapper) el.classList.remove("is-open");
      });

      // Cập nhật số lượng row đang chọn
      const selectedIds = tableInstance.getRowSelection();
      btnSelected.innerHTML = `<i class="fa-solid fa-square-check mr-1"></i>Xuất dòng đã chọn (${selectedIds.length})`;

      wrapper.classList.toggle("is-open");
    });

    document.addEventListener("click", (e) => {
      if (!wrapper.contains(e.target)) {
        wrapper.classList.remove("is-open");
      }
    });

    // Hàm xử lý gọi API export
    const handleExport = async (mode) => {
      wrapper.classList.remove("is-open");
      btnToggle.classList.add("tm-export-loading");
      btnToggle.innerHTML =
        '<i class="fa-solid fa-spinner fa-spin mr-1"></i>Đang xuất...';
      btnToggle.disabled = true;

      try {
        const state = tableInstance.getState();
        const payload = {
          source: config.source,
          source_id: config.source_id,
          mode: mode,
          columns: config.columnsMap,
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
        console.error("Export error:", error);
        if (window.toast)
          window.toast.error(
            "Lỗi",
            error.message || "Lỗi hệ thống khi xuất dữ liệu",
          );
      } finally {
        btnToggle.classList.remove("tm-export-loading");
        btnToggle.innerHTML =
          '<i class="fa-solid fa-file-export mr-1"></i>Xuất Excel';
        btnToggle.disabled = false;
      }
    };

    // Gắn sự kiện cho các nút export
    btnCurrent.addEventListener("click", () => handleExport("current_view"));
    btnAll.addEventListener("click", () => handleExport("all"));
    btnSelected.addEventListener("click", () => handleExport("selected"));
  }
}
