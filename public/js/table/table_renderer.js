export class TableRenderer {
  /** @type {TableInstance} */
  #inst;
  #searchTimeout = null;
  #bulkActionBar = null;
  #bulkMoreDropdown = null;
  #bulkMoreContent = null;
  #headerEl = null;
  #wrapperEl = null;
  #dataWrapperEl = null;

  constructor(inst) { this.#inst = inst; }
  get #root() { return this.#inst.root; }
  get #cols() { return this.#inst.columns.all; }

  buildLayout() {
    const wrapper = document.createElement('div');
    wrapper.className = 'tm-wrapper';
    wrapper.dataset.tmWrapper = this.#inst.id;
    this.#wrapperEl = wrapper;

    const headerTarget = this.#inst.root.dataset.tmToolbarTarget;
    const externalHeader = headerTarget ? document.querySelector(headerTarget) : null;
    const toolbar = this.buildToolbar();

    if (externalHeader) {
      externalHeader.appendChild(toolbar);
      this.#headerEl = externalHeader;
    } else {
      const header = document.createElement('div');
      header.className = 'tm-header-controls';
      header.appendChild(toolbar);
      wrapper.appendChild(header);
      this.#headerEl = header;
    }

    const dataWrapper = document.createElement('div');
    dataWrapper.className = 'tm-data-wrapper';
    this.#dataWrapperEl = dataWrapper;

    const scroll = document.createElement('div');
    scroll.className = 'tm-scroll';
    const table = this.buildTable();
    scroll.appendChild(table);
    dataWrapper.appendChild(scroll);

    const overlay = document.createElement('div');
    overlay.className = 'tm-loading-overlay';
    overlay.dataset.tmLoading = '';
    overlay.innerHTML = '<div class="tm-spinner"></div>';
    dataWrapper.appendChild(overlay);

    const footerTarget = this.#inst.root.dataset.tmFooterTarget;
    const externalFooter = footerTarget ? document.querySelector(footerTarget) : null;
    const pagId = this.#inst.id;
    const hasInternalTemplate = this.#inst.root.querySelector('template[data-tm-pagination]');
    const externalPag = document.querySelector(`[data-tm-pagination="${pagId}"]`);
    const isExternal = externalPag && !this.#inst.root.contains(externalPag);
    const hasPagination = !!(hasInternalTemplate || isExternal);

    if (hasPagination) {
      const footer = document.createElement('div');
      footer.className = 'tm-footer-controls';

      const info = document.createElement('div');
      info.className = 'tm-page-info';
      info.dataset.tmPageInfo = pagId;
      footer.appendChild(info);

      if (!isExternal) {
        const pagContainer = document.createElement('div');
        pagContainer.dataset.tmPagination = pagId;
        footer.appendChild(pagContainer);
      }

      if (externalFooter) {
        externalFooter.appendChild(footer);
      } else {
        dataWrapper.appendChild(footer);
      }
    }

    wrapper.appendChild(dataWrapper);
    this.#inst.root.appendChild(wrapper);

    return { table, hasPagination };
  }

  // ── Toolbar (tìm kiếm + bộ lọc) ───────────────────────────────────────────
  buildToolbar() {
    const inst = this.#inst;
    const toolbar = document.createElement('div');
    toolbar.className = 'tm-toolbar';

    const topRow = document.createElement('div');
    topRow.className = 'tm-toolbar__top';

    // Tìm kiếm
    if ('tmSearchable' in inst.root.dataset) {
      const externalSel = inst.root.dataset.tmSearchTarget;
      if (externalSel) {
        const ext = document.querySelector(externalSel);
        if (ext) {
          ext.addEventListener('input', e => {
            clearTimeout(this.#searchTimeout);
            this.#searchTimeout = setTimeout(() => {
              inst.setSearch(e.target.value);
            }, 300);
          });
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
        const searchInput = search.querySelector('input');
        searchInput.addEventListener('input', e => {
          clearTimeout(this.#searchTimeout);
          this.#searchTimeout = setTimeout(() => {
            inst.setSearch(e.target.value);
            this.#root.dispatchEvent(new CustomEvent('tm:search:change', {
              detail: { search: e.target.value }
            }));
          }, 300);
        });
        wrap.appendChild(search);
        topRow.appendChild(wrap);
      }
    }

    // Dropdown bộ lọc theo cột
    const filterCols = this.#cols.filter(c => c.filterType);
    if (filterCols.length) {
      const filterBar = document.createElement('div');
      filterBar.className = 'tm-filter-bar';
      filterCols.forEach(col => filterBar.appendChild(this.#buildFilterDropdown(col)));
      topRow.appendChild(filterBar);
    }
    toolbar.appendChild(topRow);

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
      inst.setFilter(col.key, op, val);

      this.#root.dispatchEvent(new CustomEvent('tm:filter:apply', {
        detail: { column: col.key, operator: op, value: val }
      }));

      if (DropdownHandler?.instance) {
        DropdownHandler.instance.close(wrap.dataset.dropdownId);
      }
    };
    applyBtn.addEventListener('click', activate);

    if (valueEl.classList.contains('select')) {
      valueEl.addEventListener('select:change', activate);
    } else {
      valueEl.addEventListener('keydown', e => { if (e.key === 'Enter') activate(); });
    }

    if (DropdownHandler?.instance) {
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
        this.#inst.clearFilter(rule.col);

        this.#root.dispatchEvent(new CustomEvent('tm:filter:clear', {
          detail: { column: rule.col }
        }));

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
            const selectId = valueEl.dataset.selectId;
            if (selectId && window.SelectHandler && window.SelectHandler.instance) {
              window.SelectHandler.instance.clearSelection(selectId);
            } else {
              valueEl._currentValue = '';
              const vContainer = valueEl.querySelector('.select__value');
              if (vContainer) vContainer.innerHTML = `<span class="select__value" data-select-placeholder="">${valueEl.dataset.selectPlaceholder}</span>`;
            }
          } else {
            valueEl.value = '';
          }
        }
      });
      container.appendChild(pill);
    });
  }

  buildBulkActionBar() {
    const config = this.#inst.bulkActionsConfig;
    if (!config || this.#bulkActionBar) return;

    const bar = document.createElement('div');
    bar.className = ['tm-bulk-action-bar', config.wrapperClass || ''].filter(Boolean).join(' ');
    bar.dataset.tmBulkActionBar = this.#inst.id;
    bar.dataset.state = 'closed';
    bar.setAttribute('role', 'toolbar');
    bar.setAttribute('aria-label', 'Bulk actions');
    bar.hidden = true;

    const count = document.createElement('span');
    count.className = 'badge tm-bulk-action-bar__count';
    count.dataset.variant = 'primary';
    count.dataset.tmBulkCount = '';
    count.setAttribute('aria-live', 'polite');

    const loading = document.createElement('span');
    loading.className = 'tm-bulk-action-bar__loading';
    loading.dataset.tmBulkLoading = '';
    loading.hidden = true;
    loading.innerHTML = '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i><span data-tm-bulk-loading-text></span>';

    const actions = document.createElement('div');
    actions.className = 'tm-bulk-action-bar__actions';
    actions.dataset.tmBulkActions = '';

    bar.append(count, loading, actions);
    document.body.appendChild(bar);
    this.#bulkActionBar = bar;

    document.addEventListener('keydown', (e) => {
      if (e.key !== 'Escape' || !this.#inst.getRowSelection().length) return;
      this.#inst.clearSelection();
    });

    window.addEventListener('resize', () => this.syncBulkActionBar());
  }

  syncBulkActionBar() {
    const config = this.#inst.bulkActionsConfig;
    if (!config) return;
    this.buildBulkActionBar();
    if (!this.#bulkActionBar) return;

    const selectedCount = this.#inst.getRowSelection().length;
    const isOpen = selectedCount > 0;
    const loading = this.#inst.bulkActionsLoading || { active: false, message: '' };

    this.#bulkActionBar.hidden = false;
    this.#bulkActionBar.dataset.state = isOpen ? 'open' : 'closed';
    this.#bulkActionBar.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
    this.#bulkActionBar.querySelector('[data-tm-bulk-count]').textContent = config.countLabel(selectedCount);
    this.#bulkActionBar.querySelector('[data-tm-bulk-loading]').hidden = !loading.active;
    this.#bulkActionBar.querySelector('[data-tm-bulk-loading-text]').textContent = loading.message || 'Working...';
    this.#bulkActionBar.querySelector('[data-tm-bulk-actions]').hidden = loading.active;
    this.#wrapperEl?.toggleAttribute('data-tm-bulk-open', isOpen);
    this.#dataWrapperEl?.toggleAttribute('data-tm-bulk-open', isOpen);

    if (!isOpen) {
      this.#closeBulkMorePanel();
      window.setTimeout(() => {
        if (this.#bulkActionBar?.dataset.state === 'closed') this.#bulkActionBar.hidden = true;
      }, 200);
    }

    this.#renderBulkActionButtons();
  }

  #renderBulkActionButtons() {
    const actionsEl = this.#bulkActionBar?.querySelector('[data-tm-bulk-actions]');
    const config = this.#inst.bulkActionsConfig;
    if (!actionsEl || !config) return;

    this.#removeBulkMoreDropdown();
    actionsEl.innerHTML = '';
    const actions = config.actions || [];
    const limit = this.#getBulkVisibleLimit(config.visibleLimit);
    const visible = actions.slice(0, limit);
    const overflow = actions.slice(limit);
    const isLoading = this.#inst.bulkActionsLoading?.active;

    visible.forEach(action => actionsEl.appendChild(this.#buildBulkActionButton(action, false, isLoading)));

    const dropdown = document.createElement('div');
    dropdown.className = 'dropdown tm-bulk-action-bar__more-dropdown';

    const moreBtn = document.createElement('button');
    moreBtn.type = 'button';
    moreBtn.className = 'dropdown__trigger btn tm-bulk-action-bar__more';
    moreBtn.dataset.variant = 'outline';
    moreBtn.dataset.size = 'md';
    moreBtn.dataset.dropdownTriggerMode = 'click';
    moreBtn.dataset.side = 'bottom';
    moreBtn.setAttribute('aria-label', 'More bulk actions');
    moreBtn.innerHTML = '<i class="fa-solid fa-ellipsis-vertical" aria-hidden="true"></i>';
    moreBtn.disabled = isLoading;

    const content = this.#buildBulkMoreDropdownContent(overflow, isLoading);
    dropdown.append(moreBtn, content);
    actionsEl.appendChild(dropdown);
    this.#bulkMoreDropdown = dropdown;
    this.#bulkMoreContent = content;

    if (DropdownHandler?.instance) {
      requestAnimationFrame(() => DropdownHandler.instance.register(dropdown));
    }

    this.#bulkActionBar.querySelectorAll('button').forEach(btn => {
      btn.disabled = isLoading;
    });
  }

  #buildBulkActionButton(action, inOverflow = false, isLoading = false) {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = inOverflow ? 'dropdown__item btn tm-bulk-action-bar__more-dropdown-item' : 'btn tm-bulk-action-bar__action';
    btn.dataset.variant = action.destructive ? 'destructive' : (action.variant || 'outline');
    if (!inOverflow) btn.dataset.size = 'md';
    btn.setAttribute('aria-label', action.ariaLabel || action.label);
    btn.disabled = isLoading || action.disabled === true;

    if (action.icon) {
      const icon = document.createElement('i');
      icon.className = action.icon;
      icon.setAttribute('aria-hidden', 'true');
      btn.appendChild(icon);
    }

    const label = document.createElement('span');
    label.textContent = action.label;
    btn.appendChild(label);

    btn.addEventListener('click', (event) => this.#runBulkAction(action, event));
    return btn;
  }

  #runBulkAction(action, event) {
    const selectedIds = this.#inst.getRowSelection();
    if (!selectedIds.length) return;
    if (action.confirm && !this.#confirmBulkAction(action, selectedIds.length)) return;

    this.#closeBulkMorePanel();
    const result = action.onClick?.({
      selectedIds,
      selectedRows: this.#inst.getSelectedRows(),
      table: this.#inst,
      event,
      setLoading: (isLoading, message = '') => this.#inst.setBulkActionLoading(isLoading, message),
      clearSelection: () => this.#inst.clearSelection(),
    });

    if (result?.then) {
      this.#inst.setBulkActionLoading(true);
      result.finally(() => this.#inst.setBulkActionLoading(false));
    }
  }

  #confirmBulkAction(action, selectedCount) {
    const confirmConfig = action.confirm;
    if (confirmConfig === false) return true;
    const message = typeof confirmConfig === 'object'
      ? (confirmConfig.message || `Áp dụng ${action.label} cho ${selectedCount} dòng được chọn?`)
      : `Áp dụng ${action.label} cho ${selectedCount} dòng được chọn?`;
    return window.confirm(message);
  }

  #getBulkVisibleLimit(limitConfig = {}) {
    const width = window.innerWidth;
    if (width < 600) return limitConfig.mobile ?? 2;
    if (width < 900) return limitConfig.tablet ?? 4;
    return limitConfig.desktop ?? 4;
  }

  #buildBulkMoreDropdownContent(actions, isLoading = false) {
    const content = document.createElement('div');
    content.className = 'dropdown__content';
    content.dataset.state = 'closed';
    content.setAttribute('role', 'menu');

    const nonDestructive = actions.filter(action => !action.destructive);
    const destructive = actions.filter(action => action.destructive);
    const orderedActions = [...nonDestructive, ...destructive];
    orderedActions.forEach((action, index) => {
      if (index === nonDestructive.length && destructive.length) {
        const divider = document.createElement('hr');
        divider.className = 'separator';
        content.appendChild(divider);
      }
      content.appendChild(this.#buildBulkActionButton(action, true, isLoading));
    });

    if (orderedActions.length) {
      const divider = document.createElement('hr');
      divider.className = 'separator';
      content.appendChild(divider);
    }
    content.appendChild(this.#buildBulkClearButton(isLoading));

    return content;
  }

  #buildBulkClearButton(isLoading = false) {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'dropdown__item btn tm-bulk-action-bar__more-dropdown-item';
    btn.dataset.variant = 'destructive';
    btn.setAttribute('aria-label', 'Clear selection');
    btn.disabled = isLoading;
    btn.innerHTML = '<i class="fa-solid fa-xmark" aria-hidden="true"></i><span>Clear</span>';
    btn.addEventListener('click', () => {
      this.#closeBulkMorePanel();
      this.#inst.clearSelection();
    });
    return btn;
  }

  #closeBulkMorePanel() {
    if (!this.#bulkMoreDropdown) return;
    DropdownHandler?.instance?.close(this.#bulkMoreDropdown.dataset.dropdownId);
  }

  #removeBulkMoreDropdown() {
    if (!this.#bulkMoreDropdown) return;
    this.#bulkMoreContent?.remove();
    this.#bulkMoreDropdown.remove();
    this.#bulkMoreDropdown = null;
    this.#bulkMoreContent = null;
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

      if (col.key === '__selector') {
        th.innerHTML = `<input type="checkbox" class="tm-bulk-checkbox-all">`;
      } else if (col.key === '_checkbox') {
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
          e.target.indeterminate = false;
          const visibleCheckboxes = this.#root.querySelectorAll('.tm-row-checkbox');
          visibleCheckboxes.forEach(cb => {
            cb.checked = checked;
            const id = String(cb.value);
            this.#inst.toggleRowSelection(id, checked);
          });
          this.#inst.updateHeaderCheckbox();
          this.#inst.dispatchSelectionChange();
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
          this.#inst.setSort(col.key);
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
        if (col.key === '__selector') {
          td.innerHTML = `
            <div class="tm-selector-cell">
              <input type="checkbox" class="tm-bulk-checkbox">
            </div>
          `;
        } else if (col.key === '_checkbox') {
          td.appendChild(this.#buildRowCheckbox(row));
        } else if (col.render) {
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

  #buildRowCheckbox(row) {
    const wrap = document.createElement('div');
    wrap.className = 'tm-checkbox-wrapper';
    wrap.innerHTML = `
      <label class="checkbox">
        <input type="checkbox" class="checkbox__input tm-row-checkbox">
        <span class="checkbox__label"></span>
      </label>
    `;
    const input = wrap.querySelector('input');
    const rawId = row?.[this.#inst.idKey];
    const id = rawId == null ? '' : String(rawId);
    input.value = id;
    input.checked = this.#inst.hasRowSelection(id);
    input.addEventListener('change', (e) => {
      this.#inst.toggleRowSelection(id, e.target.checked);
      this.#inst.updateHeaderCheckbox();
      this.#inst.dispatchSelectionChange();
    });
    return wrap;
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
  renderPagination({
    pagState,
    totalRows
  }) {
    const id = this.#inst.id;
    const container = document.querySelector(`[data-tm-pagination="${id}"]`);
    if (!container) return;
    container.innerHTML = '';
    const { pageIndex, pageSize } = pagState;
    const totalPages = Math.max(1, Math.ceil(totalRows / pageSize));
    const currentPage = pageIndex + 1;

    const pages = totalPages <= 7
      ? Array.from({ length: totalPages }, (_, i) => i + 1)
      : currentPage <= 4
        ? [1, 2, 3, 4, 5, '…', totalPages]
        : currentPage >= totalPages - 3
          ? [1, '…', totalPages - 4, totalPages - 3, totalPages - 2, totalPages - 1, totalPages]
          : [1, '…', currentPage - 1, currentPage, currentPage + 1, '…', totalPages];

    const makeItem = (label, targetPage, disabled = false, active = false) => {
      const el = document.createElement('button');

      el.className = 'tm-page-btn';
      if (active) el.classList.add('tm-page-btn--active');
      if (disabled) el.classList.add('tm-page-btn--disabled');
      el.innerHTML = label;
      el.type = 'button';

      if (!disabled && !active) {
        el.addEventListener('click', () => {
          this.#inst.setPageIndex(targetPage - 1);
          this.#root.dispatchEvent(new CustomEvent('tm:pagination:change', {
            detail: {
              page: targetPage,
              limit: pageSize,
              totalPages: totalPages
            }
          }));
        });
      }

      el.dataset.tmPage = targetPage;
      return el;
    };

    container.appendChild(makeItem('<i class="fa-solid fa-angle-left"></i>', currentPage - 1, currentPage <= 1));
    pages.forEach(p => {
      if (p === '…') {
        const span = document.createElement('span');
        span.className = 'tm-page-ellipsis';
        span.textContent = '…';
        container.appendChild(span);
      } else {
        container.appendChild(makeItem(p, p, false, p === currentPage));
      }
    });
    container.appendChild(makeItem('<i class="fa-solid fa-angle-right"></i>', currentPage + 1, currentPage >= totalPages));

    // Văn bản thông tin trang
    document.querySelectorAll(`[data-tm-page-info="${id}"]`).forEach(el => {
      el.textContent = `Trang ${currentPage} / ${totalPages}`;
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

    if (window.SelectHandler?.instance) {
      requestAnimationFrame(() => window.SelectHandler.instance.register(el));
    }

    return el;
  }
}
