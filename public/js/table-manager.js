class TableManager {
  constructor(config) {
    this.apiUrl = config.apiUrl;
    this.tableSelector = config.tableSelector;
    this.tableElement = document.querySelector(config.tableSelector);
    this.tbody = this.tableElement.querySelector("tbody");
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
    // Tìm kiếm
    this.searchTerm = "";
    if (config.searchSelector) {
      this.initSearch(config.searchSelector);
    }
    // Quản lý Filters
    this.filterConfigs = config.filters || [];
    this.activeFilters = {}; // Lưu trữ value: { 'classroom_id': Set(['1', '2']) }
    this.filterOptions = {}; // Lưu trữ toàn bộ data gốc để map Label cho Pills

    this.filterConfigs.forEach((f) => {
      this.activeFilters[f.key] = new Set();
      this.filterOptions[f.key] = [];
    });
  }
  debounce(func, timeout = 500) {
    let timer;
    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => {
        func.apply(this, args);
      }, timeout);
    };
  }
  async init(searchPlaceholder = "Tìm kiếm...") {
    await this.initToolbar(searchPlaceholder);
    this.initSortableHeaders();
    this.loadData(1);
  }
  async initToolbar(searchPlaceholder) {
    const toolbar = document.createElement("div");
    toolbar.className = "tm-toolbar";

    // BLOCK: Search và Filters Dropdown
    const topRow = document.createElement("div");
    topRow.className = "tm-toolbar__main";

    // Render khung Search
    const searchWrapper = document.createElement("div");
    searchWrapper.className = "tm-toolbar__search";
    searchWrapper.innerHTML = `
      <i class="fa-solid fa-magnifying-glass tm-toolbar__search-icon"></i>
      <input type="text" class="tm-toolbar__search-input" placeholder="${searchPlaceholder}">
    `;
    searchWrapper.querySelector("input").addEventListener(
      "input",
      this.debounce((e) => {
        this.searchTerm = removeVietnameseTones(e.target.value.trim());
        this.loadData(1);
      }),
    );
    topRow.appendChild(searchWrapper);

    // Render Toolbar
    this.tableElement.parentNode.insertBefore(toolbar, this.tableElement);

    // Xử lý tất cả các API Filters
    const filterPromises = this.filterConfigs.map((filter) =>
      this.buildFilterDropdown(filter, topRow),
    );
    await Promise.all(filterPromises);

    toolbar.appendChild(topRow);
    // BLOCK: Hiển thị Các option filter được chọn
    this.pillsContainer = document.createElement("div");
    this.pillsContainer.className = "tm-pills";
    toolbar.appendChild(this.pillsContainer);

    // Click ra ngoài để đóng popover
    document.addEventListener("click", (e) => {
      if (!e.target.closest(".tm-filter")) {
        document
          .querySelectorAll(".tm-filter__popover")
          .forEach((p) => p.classList.remove("tm-filter__popover--active"));
      }
    });

    this.renderActivePills();
  }

  async buildFilterDropdown(filter, container) {
    const wrapper = document.createElement("div");
    wrapper.className = "tm-filter";
    wrapper.setAttribute("data-key", filter.key);

    const btn = document.createElement("button");
    btn.className = "tm-filter__btn";
    btn.innerHTML = `<i class="fa-solid fa-circle-plus"></i> ${filter.label}`;

    const popover = document.createElement("div");
    popover.className = "tm-filter__popover";
    popover.innerHTML = `
      <div class="tm-filter__search-box">
        <input type="text" placeholder="Tìm ${filter.label.toLowerCase()}...">
      </div>
      <div class="tm-filter__list"></div>
      <div class="tm-filter__empty tm-hidden">Không tìm thấy kết quả</div>
    `;

    wrapper.appendChild(btn);
    wrapper.appendChild(popover);
    container.appendChild(wrapper);

    // Toggle Popover
    btn.addEventListener("click", () => {
      const isActive = popover.classList.contains("tm-filter__popover--active");
      document
        .querySelectorAll(".tm-filter__popover")
        .forEach((p) => p.classList.remove("tm-filter__popover--active"));

      if (!isActive) {
        popover.classList.add("tm-filter__popover--active");
        const rect = btn.getBoundingClientRect();
        if (rect.left + 240 > window.innerWidth) {
          popover.style.right = "0";
          popover.style.left = "auto";
        } else {
          popover.style.left = "0";
          popover.style.right = "auto";
        }
      }
    });

    // Lấy Option
    let options = [];
    if (filter.type === "static") {
      options = filter.options;
    } else if (filter.type === "api") {
      try {
        const res = await fetch(filter.url);
        const json = await res.json();
        if (json.success) options = json.data;
      } catch (err) {
        console.error(err);
      }
    }

    this.filterOptions[filter.key] = options;

    // Mặc định chọn 1 lớp đầu tiên (niên khóa mới nhất do server trả ra)
    if (filter.autoSelectFirst && options.length > 0) {
      this.activeFilters[filter.key].add(options[0].value.toString());
    }

    const listContainer = popover.querySelector(".tm-filter__list");
    const emptyState = popover.querySelector(".tm-filter__empty");
    const searchInput = popover.querySelector(".tm-filter__search-box input");

    const renderList = (items) => {
      listContainer.innerHTML = "";
      if (items.length === 0) {
        listContainer.classList.add("tm-hidden");
        emptyState.classList.remove("tm-hidden");
      } else {
        listContainer.classList.remove("tm-hidden");
        emptyState.classList.add("tm-hidden");

        items.forEach((opt) => {
          const isChecked = this.activeFilters[filter.key].has(
            opt.value.toString(),
          );
          const itemHtml = `
              <label class="tm-filter__item">
                <input type="checkbox" value="${opt.value}" ${isChecked ? "checked" : ""}>
                <span>${opt.label}</span>
              </label>
            `;
          listContainer.insertAdjacentHTML("beforeend", itemHtml);
        });

        listContainer.querySelectorAll("input[type=checkbox]").forEach((cb) => {
          cb.addEventListener("change", (e) => {
            if (e.target.checked)
              this.activeFilters[filter.key].add(e.target.value);
            else this.activeFilters[filter.key].delete(e.target.value);

            this.renderActivePills();
            this.loadData(1);
          });
        });
      }
    };

    // Render danh sách ban đầu
    renderList(options);

    searchInput.addEventListener("input", (e) => {
      const text = removeVietnameseTones(e.target.value.toLowerCase());

      const filtered = options.filter((o) =>
        removeVietnameseTones(o.label.toLowerCase()).includes(text),
      );
      renderList(filtered);
    });
  }
  renderActivePills() {
    this.pillsContainer.innerHTML = "";
    let hasAnyFilter = false;

    Object.keys(this.activeFilters).forEach((key) => {
      const selectedSet = this.activeFilters[key];
      if (selectedSet.size === 0) return;
      hasAnyFilter = true;

      // Map từng ID lấy ra Label từ filterOptions
      selectedSet.forEach((val) => {
        const option = this.filterOptions[key].find(
          (o) => o.value.toString() === val.toString(),
        );
        if (!option) return;

        const countHtml =
          option.count !== undefined
            ? `<span class="tm-pill__count">(${option.count})</span>`
            : "";

        const pill = document.createElement("div");
        pill.className = "tm-pill";
        pill.innerHTML = `
          <span>${option.label} ${countHtml}</span>
          <button class="tm-pill__remove"><i class="fa-solid fa-xmark"></i></button>
        `;

        // Click dấu X để xóa active option
        pill.querySelector(".tm-pill__remove").addEventListener("click", () => {
          this.activeFilters[key].delete(val);
          this.syncCheckboxUI(key);
          this.renderActivePills();
          this.loadData(1);
        });

        this.pillsContainer.appendChild(pill);
      });
    });

    // Nút Clean All
    if (hasAnyFilter) {
      const cleanBtn = document.createElement("button");
      cleanBtn.className = "tm-pill tm-pill--clean";
      cleanBtn.textContent = "Clean filter";
      cleanBtn.addEventListener("click", () => {
        Object.keys(this.activeFilters).forEach((k) =>
          this.activeFilters[k].clear(),
        );
        this.filterConfigs.forEach((f) => this.syncCheckboxUI(f.key));
        this.renderActivePills();
        this.loadData(1);
      });
      this.pillsContainer.appendChild(cleanBtn);
    }
  }

  // Đồng bộ lại Checkbox trong Dropdown khi Pill bị xóa
  syncCheckboxUI(filterKey) {
    const wrapper = document.querySelector(
      `.tm-filter[data-key="${filterKey}"]`,
    );
    if (!wrapper) return;
    wrapper.querySelectorAll(`input[type="checkbox"]`).forEach((cb) => {
      cb.checked = this.activeFilters[filterKey].has(cb.value);
    });
  }
  initSearch(selector) {
    const searchInput = document.querySelector(selector);
    if (!searchInput) return;

    const handleSearch = this.debounce((e) => {
      this.searchTerm = e.target.value.trim();
      this.loadData(1);
    });

    searchInput.addEventListener("input", handleSearch);
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
    const isCleanFilter = this.filterConfigs.some(
      (f) => f.required && this.activeFilters[f.key].size === 0,
    );
    if (isCleanFilter) {
      this.tbody.innerHTML = `<tr><td colspan="10" class="text-center p-4 tm__row--empty"">Không có dữ liệu. Vui lòng chọn ít nhất 1 lớp học để xem danh sách.</td></tr>`;
      this.paginationContainer.innerHTML = "";
      return;
    }

    if (this.isLoading) return;
    this.isLoading = true;
    this.tbody.innerHTML = `<tr><td colspan="10" class="text-center p-4">Đang tải dữ liệu...</td></tr>`;

    try {
      const url = new URL(this.apiUrl, window.location.origin);
      url.searchParams.append("page", page);
      url.searchParams.append("sort", this.sortCol);
      url.searchParams.append("dir", this.sortDir);

      if (this.searchTerm) url.searchParams.append("search", this.searchTerm);

      // Gắn filter vào API (vd: &classroom_id[]=1&classroom_id[]=2)
      for (const [key, setValues] of Object.entries(this.activeFilters)) {
        setValues.forEach((val) => url.searchParams.append(`${key}[]`, val));
      }

      const response = await fetch(url);
      const result = await response.json();

      if (result.success) {
        this.currentPage = result.data.currentPage;
        this.updateTable(result.data.items);
        this.updatePagination(result.data.totalPages);
      }
    } catch (error) {
      this.tbody.innerHTML = `<tr><td colspan="10" class="text-danger p-4">Lỗi kết nối máy chủ!</td></tr>`;
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
