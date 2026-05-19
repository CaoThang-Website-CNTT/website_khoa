export class TableRenderer {
  /** @type {TableInstance} */
  #inst;

  constructor(inst) { this.#inst = inst; }
  get #root() { return this.#inst.root; }
  get #cols() { return this.#inst.columns.all; }

  // ── Toolbar (tìm kiếm + bộ lọc) ───────────────────────────────────────────
  buildToolbar() {
    const inst = this.#inst;
    const toolbar = document.createElement('div');
    toolbar.className = 'tm-toolbar';

    // Tìm kiếm
    if ('tmSearchable' in inst.root.dataset) {
      const externalSel = inst.root.dataset.tmSearchTarget;
      if (externalSel) {
        const ext = document.querySelector(externalSel);
        if (ext) {
          ext.addEventListener('input', e => inst.filter.setSearch(e.target.value));
        }
      } else {
        const wrap = document.createElement('div');
        wrap.className = 'tm-search-wrapper';
        const search = document.createElement('div');
        search.className = 'tm-search';
        search.innerHTML = `
           <span class="tm-search__icon">
             <i class="fa-solid fa-magnifying-glass"></i>
           </span>
           <input class="tm-search__input" type="text" placeholder="Tìm kiếm...">
        `;
        search.querySelector('input').addEventListener('input', e => inst.filter.setSearch(e.target.value));
        wrap.appendChild(search);
        toolbar.appendChild(wrap);
      }
    }

    // Dropdown bộ lọc theo cột
    const filterCols = this.#cols.filter(c => c.filterType);
    if (filterCols.length) {
      const filterBar = document.createElement('div');
      filterBar.className = 'tm-filter-bar';
      filterCols.forEach(col => filterBar.appendChild(this.#buildFilterDropdown(col)));
      toolbar.appendChild(filterBar);
    }

    // Vùng hiển thị các pill bộ lọc
    const pills = document.createElement('div');
    pills.className = 'tm-filter-pills';
    pills.dataset.tmFilterPills = inst.id;
    toolbar.appendChild(pills);

    return toolbar;
  }

  #buildFilterDropdown(col) {
    const inst = this.#inst;
    const OPS_LABEL = {
      'contains': 'Chứa', '=': 'Là', '!=': 'Không là',
      '>': 'Lớn hơn', '>=': 'Lớn hơn hoặc bằng', '<': 'Nhỏ hơn', '<=': 'Nhỏ hơn hoặc bằng',
    };
    const wrap = document.createElement('div');
    wrap.className = 'dropdown tm-filter-dropdown';

    const trigger = document.createElement('button');
    trigger.className = 'dropdown__trigger tm-filter-btn';
    trigger.dataset.dropdownTriggerMode = 'click';
    trigger.innerHTML = `
       <i class="fa-solid fa-filter"></i>
      ${col.label}
    `;

    const content = document.createElement('div');
    content.className = 'dropdown__content';

    const panel = document.createElement('div');
    panel.className = 'tm-filter-panel';

    // Select toán tử
    const opSelect = this.#buildCustomSelect(
      col.filterOps.map(op => ({ value: op, label: OPS_LABEL[op] ?? op })),
      'Phép toán',
      col.filterOps[0]
    );
    opSelect.classList.add('tm-filter-panel__op');

    // Input giá trị (text / number / date / select)
    let valueEl;
    if (col.filterType === "select" && col.filterOptions) {
      valueEl = this.#buildCustomSelect(col.filterOptions, "Giá trị...");
      valueEl.classList.add("tm-filter-panel__value");
    } else {
      valueEl = document.createElement('input');
      valueEl.className = 'field__input tm-filter-panel__value';
      valueEl.type = col.filterType === 'number' ? 'number' : col.filterType === 'date' ? 'date' : 'text';
      valueEl.placeholder = 'Giá trị...';
    }

    const applyBtn = document.createElement('button');
    applyBtn.className = 'btn tm-filter-panel__action-btn';
    applyBtn.dataset.size = 'sm';
    applyBtn.dataset.variant = 'primary';
    applyBtn.textContent = 'Áp dụng';

    const actions = document.createElement('div');
    actions.className = 'tm-filter-panel__actions';
    actions.append(applyBtn);

    panel.append(opSelect, valueEl, actions);
    content.append(panel);
    wrap.append(trigger, content);

    // Lưu tham chiếu để đồng bộ hóa sau này
    wrap.dataset.tmCol = col.key;
    wrap._tmControls = { opSelect, valueEl };

    const activate = () => {
      const val = valueEl.classList.contains('select') ? valueEl._currentValue : valueEl.value;
      const op = opSelect._currentValue;

      if (!val && col.filterType !== 'select') return;
      inst.filter.setRule(col.key, op, val);

      if (DropdownHandler.instance) {
        DropdownHandler.instance.close(wrap.dataset.dropdownId);
      }
    };
    applyBtn.addEventListener('click', activate);

    if (valueEl.classList.contains('select')) {
      valueEl.addEventListener('select:change', activate);
    } else {
      valueEl.addEventListener('keydown', e => { if (e.key === 'Enter') activate(); });
    }

    if (DropdownHandler.instance) {
      console.log(`[TableManager] Đã đăng ký dropdown cho cột: ${col.label}`);
      requestAnimationFrame(() => DropdownHandler.instance.register(wrap));
    } else {
      console.warn('[TableManager] DropdownHandler không được tìm thấy! Filter sẽ không hoạt động.');
    }
    return wrap;
  }

  /** Render các pill bộ lọc đang hoạt động */
  renderFilters(rules) {
    const id = this.#inst.id;
    const container = document.querySelector(`[data-tm-filter-pills="${id}"]`);
    if (!container) return;
    container.innerHTML = '';

    rules.forEach(rule => {
      const col = this.#cols.find(c => c.key === rule.col);
      const pill = document.createElement('div');
      pill.className = 'badge tm-filter-pill';
      pill.dataset.variant = 'primary';
      pill.innerHTML = `
        <span class="tm-filter-pill__text">
          <b>${col?.label || rule.col}</b>: ${rule.value}
        </span>
        <button type="button" class="tm-filter-pill__remove" title="Xóa bộ lọc">
          <i class="fa-solid fa-xmark"></i>
        </button>
      `;
      pill.querySelector('.tm-filter-pill__remove').addEventListener('click', () => {
        this.#inst.filter.clearRule(rule.col);

        // Đồng bộ hóa: Reset UI của Dropdown tương ứng
        const dropdown = this.#root.querySelector(`.tm-filter-dropdown[data-tm-col="${rule.col}"]`);
        if (dropdown && dropdown._tmControls) {
          const { opSelect, valueEl } = dropdown._tmControls;

          // Reset Operator về mặc định
          opSelect._currentValue = col?.filterOps[0] || '=';
          const opContainer = opSelect.querySelector('.select__value');
          const OPS_LABEL = {
            'contains': 'Chứa', '=': 'Là', '!=': 'Không là',
            '>': 'Lớn hơn', '>=': 'Lớn hơn hoặc bằng', '<': 'Nhỏ hơn', '<=': 'Nhỏ hơn hoặc bằng',
          };
          if (opContainer) opContainer.textContent = OPS_LABEL[opSelect._currentValue] || opSelect._currentValue;

          // Reset Value
          if (valueEl.classList.contains('select')) {
            valueEl._currentValue = '';
            const vContainer = valueEl.querySelector('.select__value');
            if (vContainer) vContainer.innerHTML = `<span class="select__value" data-select-placeholder="">${valueEl.dataset.selectPlaceholder}</span>`;
          } else {
            valueEl.value = '';
          }
        }
      });
      container.appendChild(pill);
    });
  }

  // ── Khung bảng (Table skeleton) ───────────────────────────────────────────
  buildTable() {
    const table = document.createElement('table');
    table.className = 'tm-table';
    table.dataset.tmTable = this.#inst.id;
    table.appendChild(this.#buildColgroup());
    table.appendChild(this.#buildThead());

    const tbody = document.createElement('tbody');
    tbody.className = 'tm-tbody';
    tbody.dataset.tmBody = '';
    table.appendChild(tbody);

    const tfoot = document.createElement('tfoot');
    tfoot.className = 'tm-tfoot';
    tfoot.dataset.tmFoot = '';
    table.appendChild(tfoot);

    return table;
  }

  #buildColgroup() {
    const cg = document.createElement('colgroup');
    this.#cols.forEach(col => {
      const c = document.createElement('col');
      if (col.width) c.style.width = col.width;
      cg.appendChild(c);
    });
    return cg;
  }

  #buildThead() {
    const thead = document.createElement('thead');
    thead.className = 'tm-thead';
    const tr = document.createElement('tr');
    this.#cols.forEach(col => {
      const th = document.createElement('th');
      th.className = 'tm-th';
      th.dataset.tmColKey = col.key;
      th.style.textAlign = col.align;

      if (col.key === '_checkbox') {
        th.innerHTML = `
          <div class="tm-checkbox-wrapper">
            <label class="checkbox">
              <input type="checkbox" class="checkbox__input tm-check-all">
              <span class="checkbox__label"></span>
            </label>
          </div>
        `;
        const checkAll = th.querySelector('.tm-check-all');
        checkAll.addEventListener('change', (e) => {
          const checked = e.target.checked;
          const visibleCheckboxes = this.#root.querySelectorAll('.tm-row-checkbox');
          visibleCheckboxes.forEach(cb => {
            cb.checked = checked;
            const id = String(cb.value);
            if (checked) {
              this.#inst.selectedIds.add(id);
            } else {
              this.#inst.selectedIds.delete(id);
            }
          });
          this.#root.dispatchEvent(new CustomEvent('tm:selection-change', {
            detail: { selectedIds: Array.from(this.#inst.selectedIds) }
          }));
        });
      } else if (col.sortable) {
        th.classList.add('tm-th--sortable');
        th.dataset.tmSortCol = col.key;
        th.innerHTML = `
           <span class="tm-th__label">${col.label}</span>
           <span class="tm-sort-icon" aria-hidden="true">
             <i class="fa-solid fa-chevron-up tm-sort-icon__asc"></i>
             <i class="fa-solid fa-chevron-down tm-sort-icon__desc"></i>
           </span>
         `;
        th.addEventListener('click', () => {
          this.#inst.sort.toggle(col.key);
        });
      } else {
        th.textContent = col.label;
      }
      tr.appendChild(th);
    });

    thead.appendChild(tr);
    return thead;
  }

  // ── Render hàng (Row rendering) ───────────────────────────────────────────
  /**
   * Render các hàng vào <tbody>. Được gọi mỗi khi dữ liệu cập nhật.
   * @param {object[]} rows
   * @param {HTMLTableElement} table
   */
  renderRows(rows, table) {
    const tbody = table.querySelector('[data-tm-body]');
    tbody.innerHTML = '';
    if (!rows.length) {
      const tr = document.createElement('tr');
      const td = document.createElement('td');
      td.className = 'tm-empty';
      td.colSpan = this.#cols.length;
      td.textContent = 'Không có dữ liệu.';
      tr.appendChild(td); tbody.appendChild(tr);
      if (typeof this.#inst.updateHeaderCheckbox === 'function') {
        this.#inst.updateHeaderCheckbox();
      }
      return;
    }

    const frag = document.createDocumentFragment();
    rows.forEach((row, idx) => {
      const tr = document.createElement('tr');
      tr.className = 'tm-tr';
      tr.dataset.tmRowIndex = idx;

      this.#cols.forEach(col => {
        const td = document.createElement('td');
        td.className = 'tm-td';
        td.style.textAlign = col.align;

        const value = row[col.key];
        if (col.render) {
          td.appendChild(col.render(row, value));
        } else {
          td.textContent = value ?? '';
        }
        tr.appendChild(td);
      });
      frag.appendChild(tr);
    });
    tbody.appendChild(frag);
    if (typeof this.#inst.updateHeaderCheckbox === 'function') {
      this.#inst.updateHeaderCheckbox();
    }
  }

  /** Cập nhật chỉ báo sắp xếp trong <thead> */
  updateSortUI(table, sortState) {
    table.querySelectorAll('[data-tm-sort-col]').forEach(th => {
      const col = th.dataset.tmSortCol;
      delete th.dataset.tmSortDir;
      if (col === sortState.col && sortState.dir) {
        th.dataset.tmSortDir = sortState.dir;
      }
    });
  }

  // ── UI Phân trang ─────────────────────────────────────────────────────────
  /**
   * Render phân trang vào container [data-tm-pagination="id"], nếu có.
   * Cũng cập nhật các node văn bản [data-tm-page-info="id"].
   */
  renderPagination(pag) {
    const id = this.#inst.id;
    const container = document.querySelector(`[data-tm-pagination="${id}"]`);
    if (!container) return;
    container.innerHTML = '';
    const { page, totalPages, strategy } = pag;
    const pages = pag.window();

    const makeItem = (label, targetPage, disabled = false, active = false) => {
      const el = strategy === 'qs' && !disabled && !active
        ? document.createElement('a')
        : document.createElement('button');

      el.className = 'tm-page-btn';
      if (active) el.classList.add('tm-page-btn--active');
      if (disabled) el.classList.add('tm-page-btn--disabled');
      el.textContent = label;

      if (strategy === 'qs' && el.tagName === 'A') {
        el.href = pag.buildUrl(targetPage);
      } else {
        el.type = 'button';
        if (!disabled && !active) {
          el.addEventListener('click', () => pag.goTo(targetPage));
        }
      }
      el.dataset.tmPage = targetPage;
      return el;
    };

    container.appendChild(makeItem('‹', page - 1, page <= 1));
    pages.forEach(p => {
      if (p === '…') {
        const span = document.createElement('span');
        span.className = 'tm-page-ellipsis';
        span.textContent = '…';
        container.appendChild(span);
      } else {
        container.appendChild(makeItem(p, p, false, p === page));
      }
    });
    container.appendChild(makeItem('›', page + 1, page >= totalPages));

    // Văn bản thông tin trang
    document.querySelectorAll(`[data-tm-page-info="${id}"]`).forEach(el => {
      el.textContent = `Trang ${page} / ${totalPages}`;
    });
  }
  #buildCustomSelect(options, placeholder, defaultValue = '') {
    const el = document.createElement('div');
    el.className = 'select';
    el.dataset.selectPlaceholder = placeholder;
    if (defaultValue) el.dataset.selectDefaultValue = defaultValue;

    const content = document.createElement('div');
    content.className = 'select__content';
    options.forEach(opt => {
      const item = document.createElement('div');
      item.className = 'select__item';
      item.dataset.selectValue = opt.value;
      item.textContent = opt.label;
      content.appendChild(item);
    });
    el.appendChild(content);

    // Lưu giá trị hiện tại để dễ truy cập
    el._currentValue = defaultValue;
    el.addEventListener('select:change', e => {
      el._currentValue = e.detail.value;
    });

    if (window.SelectHandler) {
      requestAnimationFrame(() => SelectHandler.instance.register(el));
    }

    return el;
  }
}