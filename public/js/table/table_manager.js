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
    rowSelection: {},
    pagination: { pageIndex: 0, pageSize: 20 },
    sorting: [],
    globalFilter: '',
    columnFilters: []
  };
  selectable = false;
  data = [];
  columns;
  sort;
  filter;
  #renderer;
  #table = null;

  constructor(root) {
    this.root = root;
    this.id = root.dataset.tm;
    if (!this.id) throw new Error('[TableManager] Thiếu data-tm=[id]');

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
        align: 'center'
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
    const layout = this.#renderer.buildLayout();
    this.#table = layout.table;
    console.log(`[TableManager] Bảng "${this.id}" đã được khởi tạo.`, {
      columns: this.columns.all.length,
      hasPagination: layout.hasPagination
    });
    this.render();
  }

  render() {
    if (!this.#table) return;
    let rows = [...this.data];

    const searchKeys = this.columns.all.map(c => c.key);
    rows = rows.filter(r => this.filter.predicate(r, searchKeys));

    const cmp = this.sort.comparator();
    if (cmp) rows = [...rows].sort(cmp);

    const totalRows = rows.length;
    const { pageIndex, pageSize } = this.#state.pagination;
    if (pageSize !== Infinity) {
      const start = pageIndex * pageSize;
      rows = rows.slice(start, start + pageSize);
    }

    this.#renderer.renderRows(rows, this.#table);
    this.#renderer.updateSortUI(this.#table, this.sort.state);
    this.#renderer.renderPagination({
      pagState: this.#state.pagination,
      totalRows,
    });
    this.#renderer.renderFilters(this.filter.rules);
    this.root.dispatchEvent(new CustomEvent('tm:render', {
      detail: {
        tableId: this.id,
        visibleRows: rows,
        totalRows,
        state: this.getState()
      }
    }));
    console.log(`[TableManager] Render "${this.id}":`, {
      rows: rows.length,
      sort: this.sort.state,
      filters: this.filter.rules.length
    });
  }

  // Public API
  getState() {
    return {
      ...this.#state,
      rowSelection: { ...this.#state.rowSelection },
      pagination: { ...this.#state.pagination },
      sorting: [...this.#state.sorting],
      columnFilters: [...this.#state.columnFilters]
    };
  }
  setData(rows) {
    this.data = Array.isArray(rows) ? rows : [];
    this.#state.pagination.pageIndex = 0;
    this.render();
  }
  loadData(payload) {
    const rows = Array.isArray(payload) ? payload : payload?.rows;
    const page = payload?.page;
    const limit = payload?.limit;
    this.data = Array.isArray(rows) ? rows : [];
    if (page != null) this.#state.pagination.pageIndex = Math.max(0, Number(page) - 1);
    if (limit != null) this.#state.pagination.pageSize = Math.max(1, Number(limit));
    if (page == null) this.#state.pagination.pageIndex = 0;
    this.render();
  }
  setPageCount() {}
  getPageCount() {
    const total = this.filter.search || this.filter.rules.length
      ? this.getFilteredCount()
      : this.data.length;
    return Math.max(1, Math.ceil(total / this.#state.pagination.pageSize));
  }
  getFilteredCount() {
    const searchKeys = this.columns.all.map(c => c.key);
    let rows = this.data.filter(r => this.filter.predicate(r, searchKeys));
    const cmp = this.sort.comparator();
    if (cmp) rows = [...rows].sort(cmp);
    return rows.length;
  }
  // Pagination
  canPrevPage() {
    return this.#state.pagination.pageIndex > 0;
  }
  canNextPage() {
    return this.#state.pagination.pageIndex < this.getPageCount() - 1;
  }
  nextPage() {
    if (!this.canNextPage()) return;
    this.#state.pagination.pageIndex++;
    this.render();
  }
  prevPage() {
    if (!this.canPrevPage()) return;
    this.#state.pagination.pageIndex--;
    this.render();
  }
  setPageIndex(index) {
    const next = Math.max(0, Math.min(Number(index) || 0, this.getPageCount() - 1));
    this.#state.pagination.pageIndex = next;
    this.render();
  }
  setPageSize(size) {
    const nextSize = Number(size);
    this.#state.pagination.pageSize = Number.isFinite(nextSize) && nextSize > 0 ? nextSize : 20;
    this.#state.pagination.pageIndex = 0;
    this.render();
  }
  getVisibleRows() {
    const searchKeys = this.columns.all.map(c => c.key);
    const filtered = this.data.filter(r => this.filter.predicate(r, searchKeys));
    const cmp = this.sort.comparator();
    const sorted = cmp ? [...filtered].sort(cmp) : filtered;
    const start = this.#state.pagination.pageIndex * this.#state.pagination.pageSize;
    return sorted.slice(start, start + this.#state.pagination.pageSize);
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
        const inlineData = TableManager.#readInlineData(inst.id);
        if (inlineData) {
          inst.loadData(inlineData);
        }
      } catch (e) {
        console.error('[TableManager] Không thể khởi tạo bảng:', e);
      }
    });
  }

  static #readInlineData(tableId) {
    const el = document.querySelector(`script[data-tm-data="${tableId}"]`);
    if (!el) return null;
    try {
      return JSON.parse(el.textContent);
    } catch {
      console.warn(`[TableManager] Dữ liệu inline không hợp lệ: ${tableId}`);
      return null;
    }
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

  static loadData(tableId, payload) {
    TableManager.get(tableId)?.loadData(payload);
  }
}
