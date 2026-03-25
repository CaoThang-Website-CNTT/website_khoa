class TableManager {
  constructor(config) {
    this.apiUrl = config.apiUrl;
    this.tableSelector = config.tableSelector;
    this.tbody = document.querySelector(`${config.tableSelector} tbody`);
    this.paginationContainer = document.querySelector(
      config.paginationSelector,
    );

    // Hàm callback tự định nghĩa để render HTML cho 1 dòng
    this.renderRow = config.renderRow;

    this.currentPage = 1;
    this.isLoading = false;

    // Trạng thái sắp xếp
    this.sortCol = config.defaultSort || "id";
    this.sortDir = config.defaultDir || "ASC";
    this.initSortableHeaders();
  }
  initSortableHeaders() {
    const headers = document.querySelectorAll(
      `${this.tableSelector} th[data-sort]`,
    );
    headers.forEach((th) => {
      th.classList.add("tm__head-cell", "tm__head-cell--sortable");

      th.addEventListener("click", () => {
        const column = th.getAttribute("data-sort");

        if (this.sortCol === column) {
          this.sortDir = this.sortDir === "ASC" ? "DESC" : "ASC";
        } else {
          this.sortCol = column;
          this.sortDir = "ASC";
        }

        this.updateHeaderUI();
        this.loadData(1);
      });
    });

    this.updateHeaderUI();
  }
  /**
   * Hiển thị icon mũi tên lên xuống
   */
  updateHeaderUI() {
    const headers = document.querySelectorAll(
      `${this.tableSelector} th[data-sort]`,
    );
    headers.forEach((th) => {
      const existingIcon = th.querySelector(".tm__sort-icon");
      if (existingIcon) existingIcon.remove();

      if (th.getAttribute("data-sort") === this.sortCol) {
        const icon = this.sortDir === "ASC" ? "▲" : "▼";
        th.insertAdjacentHTML(
          "beforeend",
          `<span class="tm__sort-icon">${icon}</span>`,
        );
      }
    });
  }
  /**
   * Tải và hiển thị dữ liệu lên bảng
   * @param {Int} page
   * @returns
   */
  async loadData(page = 1) {
    if (this.isLoading) return;
    this.isLoading = true;
    this.tbody.innerHTML = `<tr><td colspan="10" class="tm__row--loading">Đang tải dữ liệu...</td></tr>`;

    try {
      // Nối thêm tham số page vào URL
      const url = new URL(this.apiUrl, window.location.origin);
      url.searchParams.append("page", page);

      url.searchParams.append("sort", this.sortCol);
      url.searchParams.append("dir", this.sortDir);

      const response = await fetch(url);
      const result = await response.json();
      if (result.success) {
        this.currentPage = result.data.currentPage;

        this.updateTable(result.data.items);
        this.updatePagination(result.data.totalPages);
      } else {
        this.tbody.innerHTML = `<tr><td colspan="10" class="tm__row--error">${result.message || "Lỗi dữ liệu từ máy chủ"}</td></tr>`;
      }
    } catch (error) {
      console.error("Lỗi tải dữ liệu:", error);
      this.tbody.innerHTML = `<tr><td colspan="10" class="tm__row--error">Lỗi kết nối máy chủ!</td></tr>`;
    } finally {
      this.isLoading = false;
    }
  }
  /**
   * Hàm cập nhật nội dung (dữ liệu) bảng
   * @param {Array} data
   * @returns
   */
  updateTable(items) {
    if (!items || items.length === 0) {
      this.tbody.innerHTML = `<tr><td colspan="10" class="tm__row--empty">Không có dữ liệu.</td></tr>`;
      return;
    }
    this.tbody.innerHTML = items.map((item) => this.renderRow(item)).join("");
  }
  /**
   * Hàm cập nhật nội dung cho phần phân trang
   * @param {Int} totalPages
   * @returns
   */
  updatePagination(totalPages) {
    if (!this.paginationContainer || totalPages <= 1) {
      this.paginationContainer.innerHTML = "";
      return;
    }
    const isPrevDisabled = this.currentPage <= 1;
    const prevTag = isPrevDisabled ? "span" : "a";
    const prevDataPage = isPrevDisabled
      ? ""
      : `data-page="${this.currentPage - 1}"`;
    const prevDisabled = isPrevDisabled ? "data-disabled" : "";
    const prevAria = isPrevDisabled
      ? 'aria-disabled="true"'
      : 'aria-label="Go to previous page"';

    let html = `<li class="pagination-item">
      <${prevTag} class="pagination-link pagination-prev" ${prevDisabled} ${prevDataPage} ${prevAria}>
        <i class="fa-solid fa-chevron-left"></i>
        <span>Trước</span>
      </${prevTag}>
    </li>`;
    html += this.getPaginationElements(totalPages)
      .map((el) => {
        if (el === "...")
          return `
          <li
            class="pagination-item pagination-item--ellipsis"
            aria-hidden="true"
          >
            <span class="pagination-ellipsis">
              <i class="fa-solid fa-ellipsis"></i>
              <span class="sr-only">More pages</span>
            </span>
          </li>`;
        else {
          const isActive = el == this.currentPage;
          const tag = isActive ? "span" : "a";
          const active = isActive ? "data-active" : "";
          const dataPage = isActive ? "" : `data-page=${el}`;
          const ariaCur = isActive
            ? ' aria-current="page"'
            : `aria-label="Go to page ${el}"`;
          return `
            <li class="pagination-item">
              <${tag} class="pagination-link" ${dataPage} ${active} ${ariaCur}>${el}</${tag}>
            </li>`;
        }
      })
      .join("");
    const isNextDisabled = this.currentPage >= totalPages;
    const nextTag = isNextDisabled ? "span" : "a";
    const nextDataPage = isNextDisabled
      ? ""
      : `data-page="${this.currentPage + 1}"`;
    const nextDisabled = isNextDisabled ? "data-disabled" : "";
    const nextAria = isNextDisabled
      ? 'aria-disabled="true"'
      : 'aria-label="Go to next page"';

    html += `<li class="pagination-item">
      <${nextTag} class="pagination-link pagination-next" ${nextDisabled} ${nextDataPage} ${nextAria}>
        <span>Sau</span>
        <i class="fa-solid fa-chevron-right"></i>
      </${nextTag}>
    </li>`;

    this.paginationContainer.innerHTML = html;

    // Lắng nghe sự kiện click cho các nút vừa tạo
    const buttons = this.paginationContainer.querySelectorAll("a[data-page]");
    buttons.forEach((btn) => {
      btn.onclick = (e) => {
        const page = parseInt(e.currentTarget.getAttribute("data-page"));
        this.loadData(page);
      };
    });
  }
  /**
   * Hàm tính toán mảng dữ liệu cho phân trang
   * @param {Int} totalPages
   * @returns Array
   */
  getPaginationElements(totalPages) {
    if (totalPages < 1) return [];

    if (totalPages <= 7) {
      return Array.from({ length: totalPages }, (_, i) => i + 1);
    }

    if (this.currentPage <= 4) {
      return [1, 2, 3, 4, 5, "...", totalPages];
    }

    if (this.currentPage >= totalPages - 3) {
      return [
        1,
        "...",
        totalPages - 4,
        totalPages - 3,
        totalPages - 2,
        totalPages - 1,
        totalPages,
      ];
    }

    return [
      1,
      "...",
      this.currentPage - 1,
      this.currentPage,
      this.currentPage + 1,
      "...",
      totalPages,
    ];
  }
}
