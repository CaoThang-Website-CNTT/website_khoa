import { ColumnDef, ColumnRegistry } from './column_registry.js';
import { SortController } from './sort_controller.js';
import { FilterController } from './filter_controller.js';
import { TableRenderer } from './table_renderer.js';

export const DEFAULT_FILTER_OPS = {
  text: ['contains', '=', '!='],
  number: ['=', '!=', '>', '>=', '<', '<='],
  date: ['=', '!=', '>', '>=', '<', '<='],
  select: ['=', '!='],
};

class TableInstance {
  id;
  root;
  #state = {
    /** @type {[rowId: string]: boolean} */
    rowSelection,
    /** @type {{pageIndex: number, pageSize: number}} */
    pagination,
    /** @type {Array<{ id: string, asc: boolean }>} */
    sorting,
    /** @type {any} */
    globalFilter,
    /** @type {Array<{ id: string, value: any }>} */
    columnFilters
  };
  selectable = false;
  data = [];
  columns;
  sort;
  filter;
  #renderer;
  #table = null;
  #loading = false;

  constructor(root) {
    this.root = root;
    this.id = root.dataset.tm;
    if (!this.id) throw new Error('[TableManager] Thiếu data-tm=[id]');

    this.state = {
      rowSelection: {},
      pagination: { pageIndex: 0, pageSize: 20 },
      sorting: [],
      globalFilter: '',
      columnFilters: []
    };

    this.columns = new ColumnRegistry(root, DEFAULT_FILTER_OPS);

    this.selectable = "tmSelectable" in root.dataset;
    if (this.selectable) {
      this.idKey = root.dataset.tmIdKey ?? 'id';
      this.selectedIds = new Set();
      this.columns.prepend(new ColumnDef({
        key: '_checkbox',
        label: '',
        sortable: false,
        width: '40px',
        align: 'center',
        render: (row) => {
          const frag = document.createDocumentFragment();
          const cbWrap = document.createElement('div');
          cbWrap.className = 'tm-checkbox-wrapper';
          cbWrap.innerHTML = `
            <label class="checkbox">
              <input type="checkbox" class="checkbox__input tm-row-checkbox" value="${row[this.idKey]}">
              <span class="checkbox__label"></span>
            </label>
          `;
          const input = cbWrap.querySelector('input');
          const id = String(row[this.idKey]);
          if (this.selectedIds.has(id)) {
            input.checked = true;
          }
          input.addEventListener('change', (e) => {
            if (e.target.checked) {
              this.selectedIds.add(id);
            } else {
              this.selectedIds.delete(id);
            }
            this.updateHeaderCheckbox();
            this.root.dispatchEvent(new CustomEvent('tm:selection-change', {
              detail: { selectedIds: Array.from(this.selectedIds) }
            }));
          });
          frag.appendChild(cbWrap);
          return frag;
        }
      }));
    }

    this.sort = new SortController(() => { this.render(); });
    this.filter = new FilterController(() => { this.render(); });

    this.#renderer = new TableRenderer(this);
  }

  updateHeaderCheckbox() {
    const checkAll = this.root.querySelector('.tm-check-all');
    if (!checkAll) return;

    const visibleCheckboxes = this.root.querySelectorAll('.tm-row-checkbox');
    if (visibleCheckboxes.length === 0) {
      checkAll.checked = false;
      checkAll.disabled = true;
      return;
    }
    checkAll.disabled = false;

    let allChecked = true;
    visibleCheckboxes.forEach(cb => {
      if (!cb.checked) allChecked = false;
    });
    checkAll.checked = allChecked;
  }

  getSelectedIds() {
    return Array.from(this.selectedIds || new Set());
  }

  clearSelection() {
    if (this.selectedIds) {
      this.selectedIds.clear();
      const visibleCheckboxes = this.root.querySelectorAll('.tm-row-checkbox');
      visibleCheckboxes.forEach(cb => {
        cb.checked = false;
      });
      this.updateHeaderCheckbox();
      this.root.dispatchEvent(new CustomEvent('tm:selection-change', {
        detail: { selectedIds: [] }
      }));
    }
  }

  // Khởi tạo các element
  init() {
    const wrapper = document.createElement('div');
    wrapper.className = 'tm-wrapper';
    wrapper.dataset.tmWrapper = this.id;

    // Header Controls
    const headerTarget = this.root.dataset.tmToolbarTarget;
    const externalHeader = headerTarget ? document.querySelector(headerTarget) : null;
    const toolbar = this.#renderer.buildToolbar();

    if (externalHeader) {
      externalHeader.appendChild(toolbar);
    } else {
      const header = document.createElement('div');
      header.className = 'tm-header-controls';
      header.appendChild(toolbar);
      wrapper.appendChild(header);
    }

    // Data Wrapper
    const dataWrapper = document.createElement('div');
    dataWrapper.className = 'tm-data-wrapper';

    const scroll = document.createElement('div');
    scroll.className = 'tm-scroll';
    this.#table = this.#renderer.buildTable();
    scroll.appendChild(this.#table);
    dataWrapper.appendChild(scroll);

    const overlay = document.createElement('div');
    overlay.className = 'tm-loading-overlay';
    overlay.dataset.tmLoading = '';
    overlay.innerHTML = '<div class="tm-spinner"></div>';
    dataWrapper.appendChild(overlay);

    // Footer Controls
    const footerTarget = this.root.dataset.tmFooterTarget;
    const externalFooter = footerTarget ? document.querySelector(footerTarget) : null;

    const pagId = this.id;
    const hasInternalTemplate = this.root.querySelector('template[data-tm-pagination]');
    const externalPag = document.querySelector(`[data-tm-pagination="${pagId}"]`);
    const isExternal = externalPag && !this.root.contains(externalPag);

    if (hasInternalTemplate || isExternal) {
      const footer = document.createElement('div');
      footer.className = 'tm-footer-controls';

      // Info "Trang X/Y"
      const info = document.createElement('div');
      info.className = 'tm-page-info';
      info.dataset.tmPageInfo = pagId;
      footer.appendChild(info);

      // Container cho các nút số trang
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

    this.root.appendChild(wrapper);
    console.log(`[TableManager] Bảng "${this.id}" đã được khởi tạo.`, {
      columns: this.columns.all.length,
      hasPagination: !!(hasInternalTemplate || isExternal)
    });
    this.render();
  }

  render() {
    if (!this.#table) return;
    let rows = [];

    const searchKeys = this.columns.all.map(c => c.key);
    rows = rows.filter(r => this.filter.predicate(r, searchKeys));

    const cmp = this.sort.comparator();
    if (cmp) rows = [...rows].sort(cmp);

    this.pagination.update({ total: rows.length });

    const { page, limit } = this.pagination;
    if (limit !== Infinity) {
      rows = rows.slice((page - 1) * limit, page * limit);
    }

    this.#renderer.renderRows(rows, this.#table);
    this.#renderer.updateSortUI(this.#table, this.sort.state);
    this.#renderer.renderPagination({
      pagState: this.#state.pagination,
      totalRows: this.data.length,
    });
    this.#renderer.renderFilters(this.filter.rules);
    console.log(`[TableManager] Render "${this.id}":`, {
      rows: rows.length,
      sort: this.sort.state,
      filters: this.filter.rules.length
    });
  }

  // Public API
  // Pagination
  canPrevPage() {
    return this.#state.pagination.pageIndex > 0;
  }
  canNextPage() {
    return this.#state.pagination.pageIndex < this.#state.pagination.totalPages - 1;
  }
  nextPage() {
    this.#state.pagination.pageIndex++;
    this.render();
  }
  prevPage() {
    this.#state.pagination.pageIndex--;
    this.render();
  }
  setPageIndex(index) {
    this.#state.pagination.pageIndex = index;
    this.render();
  }
  setPageSize(size) {
    this.#state.pagination.pageSize = size;
    this.render();
  }
  getVisibleRows() {
    return this.data.slice(
      (this.#state.pagination.pageIndex - 1) * this.#state.pagination.pageSize,
      this.#state.pagination.pageIndex * this.#state.pagination.pageSize
    );
  }

  #setLoading(on) {
    const overlay = this.root.querySelector('[data-tm-loading]');
    if (overlay) overlay.style.display = on ? 'flex' : 'none';
  }

  updateHeaderCheckbox() {
    const checkAll = this.root.querySelector('.tm-check-all');
    if (!checkAll) return;

    const visibleCheckboxes = this.root.querySelectorAll('.tm-row-checkbox');
    if (visibleCheckboxes.length === 0) {
      checkAll.checked = false;
      return;
    }

    const allChecked = Array.from(visibleCheckboxes).every(cb => cb.checked);
    checkAll.checked = allChecked;
  }
}

export class TableManager {
  static #instance = null;
  static #registry = new Map();

  #isBootstrapped = false;

  constructor() {
    if (TableManager.#instance) {
      return TableManager.#instance;
    }
    TableManager.#instance = this;

    this.#bootstrap();
  }

  static get instance() {
    return TableManager.#instance || new TableManager();
  }

  #bootstrap() {
    if (this.#isBootstrapped) return;

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => this.init());
    } else {
      this.init();
    }
    this.#isBootstrapped = true;
  }

  init() {
    document.querySelectorAll('[data-tm]').forEach(root => {
      try {
        const id = root.dataset.tm;
        if (TableManager.#registry.has(id)) {
          console.warn(`[TableManager] Đã đăng ký bảng: ${id}`);
          return;
        }

        const inst = new TableInstance(root);
        TableManager.#registry.set(inst.id, inst);
        inst.init();
      } catch (e) {
        console.error('[TableManager] Không thể khởi tạo bảng:', e);
      }
    });
  }

  static get(id) { return TableManager.#registry.get(id); }

  static setFilter(tableId, col, op, value) {
    TableManager.get(tableId)?.filter.setRule(col, op, value);
  }

  static setFilterOptions(tableId, col, options) {
    const inst = TableManager.get(tableId);
    if (!inst) return;
    const colDef = inst.columns.all.find(c => c.key === col);
    if (colDef) colDef.filterOptions = options;
  }

  static getSelectedIds(tableId) {
    return TableManager.get(tableId)?.getSelectedIds() || [];
  }

  static clearSelection(tableId) {
    TableManager.get(tableId)?.clearSelection();
  }
}
