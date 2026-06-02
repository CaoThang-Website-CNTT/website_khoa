import { ColumnDef, ColumnRegistry } from './column_registry.js';
import { TableRenderer } from './table_renderer.js';

export const DEFAULT_FILTER_OPS = {
  text: ['contains', '=', '!='],
  number: ['=', '!=', '>', '>=', '<', '<='],
  date: ['=', '!=', '>', '>=', '<', '<='],
  select: ['=', '!='],
};

// ─── Sort helpers (pure) ──────────────────────────────────────────────────

/**
 * Tạo comparator function để sort client-side.
 * @param {{ col: string|null, dir: 'asc'|'desc'|null }} sortState
 * @returns {((a: object, b: object) => number)|null}
 */
function makeSortComparator(sortState) {
  const { col, dir } = sortState;
  if (!col) return null;
  return (a, b) => {
    const va = String(a[col] ?? '');
    const vb = String(b[col] ?? '');
    const cmp = va.localeCompare(vb, 'vi', { numeric: true, sensitivity: 'accent' });
    return dir === 'asc' ? cmp : -cmp;
  };
}

// ─── Filter helpers (pure) ────────────────────────────────────────────────

/**
 * Normalize chuỗi tiếng Việt để so sánh không dấu.
 * @param {string} str
 * @returns {string}
 */
function normalizeStr(str) {
  return String(str || '')
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/đ/g, 'd');
}

/**
 * Evaluate một filter rule trên một cell value.
 * @param {*} cell
 * @param {string} op
 * @param {string} value
 * @returns {boolean}
 */
function evalRule(cell, op, value) {
  const c = typeof cell === 'number' ? cell : normalizeStr(cell);
  const v = isNaN(Number(value)) ? normalizeStr(value) : Number(value);

  switch (op) {
    case 'contains': return String(c).includes(String(v));
    case '=': return c == v;  // So sánh loose để tương thích string/number
    case '!=': return c != v;
    case '>': return c > v;
    case '>=': return c >= v;
    case '<': return c < v;
    case '<=': return c <= v;
    default: return true;
  }
}

/**
 * Kiểm tra một row có thỏa mãn search query và tất cả filter rules không.
 * @param {object} row
 * @param {string[]} searchableKeys
 * @param {string} search
 * @param {FilterRule[]} rules
 * @returns {boolean}
 */
function matchesFilter(row, searchableKeys, search, rules) {
  if (search) {
    const q = normalizeStr(search);
    const hit = searchableKeys.some(k => normalizeStr(row[k]).includes(q));
    if (!hit) return false;
  }
  for (const r of rules) {
    if (!evalRule(row[r.col], r.op, r.value)) return false;
  }
  return true;
}

class TableInstance {
  id;
  root;
  /**
   * @typedef {'client'|'server'} TableMode
   *
   * - **client**: TableManager tự filter, sort, paginate toàn bộ `data` trong bộ nhớ.
   *   Phù hợp cho dataset nhỏ (<= vài nghìn rows). Dev chỉ cần gọi `loadData(rows)` một lần.
   *
   * - **server**: TableManager không tự xử lý filter/sort/paginate.
   *   Mỗi khi state thay đổi, sự kiện `tm:state-change` được dispatch kèm full state snapshot.
   *   Dev lắng nghe event này, tự gọi API, rồi trả kết quả về qua `loadData(rows, { total })`.
   *   `totalRows` lúc này phải do server cung cấp để render pagination đúng.
   */
  /** @type {{ sort: SortState, filters: FilterRule[], search: string, pagination: PaginationState, mode: TableMode, totalRows: number }} */
  #state = {
    rowSelection: new Set(),
    pagination: { pageIndex: 0, pageSize: 20 },
    sort: { col: null, dir: null },
    filters: [],
    search: '',
    mode: 'client',    // đọc từ data-tm-mode attribute khi init
    totalRows: 0,      // server mode: do loadData() set; client mode: tự tính
  };
  selectable = false;
  data = [];
  columns;
  #renderer;
  #table = null;

  constructor(root) {
    this.root = root;
    this.id = root.dataset.tm;
    this.#state.mode = (root.dataset.tmMode === 'server') ? 'server' : 'client';
    if (!this.id) throw new Error('[TableManager] Thiếu data-tm=[id]');

    this.columns = new ColumnRegistry(root, DEFAULT_FILTER_OPS);

    this.selectable = "tmSelectable" in root.dataset;
    if (this.selectable) {
      this.idKey = root.dataset.tmIdKey ?? 'id';
      this.columns.prepend(new ColumnDef({
        key: '_checkbox',
        label: '',
        sortable: false,
        width: '40px',
        align: 'center'
      }));
    }

    this.#renderer = new TableRenderer(this);
  }

  /**
   * Điểm duy nhất trigger re-render sau mỗi state mutation từ public API
   * (setSort, setFilter, setSearch, setPage...).
   *
   * - **client mode**: gọi thẳng `render()`.
   * - **server mode**: dispatch `tm:state-change` kèm state snapshot rồi dừng.
   *   TableManager không tự render - chờ dev gọi `loadData()` sau khi fetch xong.
   *
   * `loadData()` và `init()` gọi `render()` trực tiếp, không qua `#commit()`,
   * vì chúng là data-push chủ động, không phải state-change do user interaction.
   *
   * @param {'sort'|'filter'|'search'|'pagination'} reason
   */
  #commit(reason) {
    if (this.#state.mode === 'server') {
      this.root.dispatchEvent(new CustomEvent('tm:state-change', {
        detail: { reason, state: this.#getStateSnapshot() }
      }));
      return; // Dừng - không render, chờ dev gọi loadData()
    }
    this.render();
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

  getRowSelection() {
    return Array.from(this.#state.rowSelection);
  }

  clearSelection() {
    if (this.#state.rowSelection) {
      this.#state.rowSelection.clear();
      const visibleCheckboxes = this.root.querySelectorAll('.tm-row-checkbox');
      visibleCheckboxes.forEach(cb => {
        cb.checked = false;
      });
      this.updateHeaderCheckbox();
      this.root.dispatchEvent(new CustomEvent('tm:selection-change', {
        detail: { rowSelection: [] }
      }));
    }
  }

  /**
   * Kiểm tra một row có đang được chọn không.
   * @param {string} id
   * @returns {boolean}
   */
  hasRowSelection(id) {
    return this.#state.rowSelection.has(id);
  }

  /**
   * Thêm hoặc xóa một row khỏi selection state.
   * Không dispatch event - caller (TableRenderer) tự dispatch sau khi gọi.
   * @param {string} id
   * @param {boolean} selected
   */
  toggleRowSelection(id, selected) {
    if (selected) {
      this.#state.rowSelection.add(id);
    } else {
      this.#state.rowSelection.delete(id);
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
    let totalRows;

    if (this.#state.mode === 'client') {
      // Client mode: tự filter + sort
      const searchKeys = this.columns.all.map(c => c.key);
      rows = rows.filter(r => matchesFilter(r, searchKeys, this.#state.search, this.#state.filters));
      const cmp = makeSortComparator(this.#state.sort);
      if (cmp) rows = rows.sort(cmp); // sort không cần clone vì đã [...this.data]
      totalRows = rows.length;

      // Paginate
      const { pageIndex, pageSize } = this.#state.pagination;
      if (pageSize !== Infinity) rows = rows.slice(pageIndex * pageSize, (pageIndex + 1) * pageSize);
    } else {
      // Server mode: data đã là trang hiện tại, totalRows do server cung cấp
      totalRows = this.#state.totalRows;
    }

    this.#renderer.renderRows(rows, this.#table);
    this.#renderer.updateSortUI(this.#table, this.#state.sort);
    this.#renderer.renderPagination({ pagState: this.#state.pagination, totalRows });
    this.#renderer.renderFilters(this.#state.filters);

    this.root.dispatchEvent(new CustomEvent('tm:render', {
      detail: { tableId: this.id, visibleRows: rows, totalRows, state: this.#getStateSnapshot() }
    }));
  }

  // Public API
  /**
   * Trả về snapshot của toàn bộ state hiện tại - immutable copy.
   * Dùng để đọc state từ bên ngoài hoặc gửi kèm event.
   * @returns {{ sort: SortState, filters: FilterRule[], search: string, pagination: PaginationState, mode: TableMode, totalRows: number }}
   */
  getState() { return this.#getStateSnapshot(); }

  #getStateSnapshot() {
    return {
      sort: { ...this.#state.sort },
      filters: [...this.#state.filters],
      search: this.#state.search,
      pagination: { ...this.#state.pagination },
      mode: this.#state.mode,
      totalRows: this.#state.totalRows,
      rowSelection: Array.from(this.#state.rowSelection),
    };
  }
  setData(rows) {
    this.data = Array.isArray(rows) ? rows : [];
    this.#state.pagination.pageIndex = 0;
    this.render();
  }
  /**
   * Nạp dữ liệu vào bảng và trigger render.
   *
   * - **client mode**: truyền toàn bộ dataset. TableManager tự filter/sort/paginate.
   *
   * - **server mode**: truyền đúng một trang dữ liệu đã được server xử lý.
   *   Bắt buộc truyền `payload.total` để render pagination đúng.
   *   `payload.page` và `payload.limit` nếu có sẽ sync lại pagination state.
   *
   * @param {object[]|{ rows: object[], total?: number, page?: number, limit?: number }} payload
   */
  loadData(payload) {
    const rows = Array.isArray(payload) ? payload : (payload?.rows ?? []);
    this.data = rows;

    if (this.#state.mode === 'server' && payload?.total != null) {
      this.#state.totalRows = payload.total;
    }

    // Sync pagination từ server nếu có
    if (payload?.page != null) this.#state.pagination.pageIndex = Math.max(0, Number(payload.page) - 1);
    if (payload?.limit != null) this.#state.pagination.pageSize = Math.max(1, Number(payload.limit));

    this.render(); // loadData luôn render trực tiếp, không qua #commit
  }
  getPageCount() {
    const total = this.#state.mode === 'server'
      ? this.#state.totalRows
      : this.#getClientFilteredCount();
    return Math.max(1, Math.ceil(total / this.#state.pagination.pageSize));
  }
  #getClientFilteredCount() {
    const searchKeys = this.columns.all.filter(c => c.key !== '_checkbox').map(c => c.key);
    const filtered = this.data.filter(r => matchesFilter(r, searchKeys, this.#state.search, this.#state.filters));
    return filtered.length;
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
    const searchKeys = this.columns.all.filter(c => c.key !== '_checkbox').map(c => c.key);
    const filtered = this.data.filter(r => matchesFilter(r, searchKeys, this.#state.search, this.#state.filters));
    const cmp = makeSortComparator(this.#state.sort);
    const sorted = cmp ? filtered.sort(cmp) : filtered;
    const { pageIndex, pageSize } = this.#state.pagination;
    return sorted.slice(pageIndex * pageSize, (pageIndex + 1) * pageSize);
  }
  // Sort
  /**
   * Toggle sort trên một cột. Cycle: asc → desc → off.
   * Sau khi đổi sort, pagination reset về trang đầu.
   * @param {string} col
   */
  setSort(col) {
    if (this.#state.sort.col === col) {
      if (this.#state.sort.dir === 'asc') {
        this.#state.sort.dir = 'desc';
      } else if (this.#state.sort.dir === 'desc') {
        this.#state.sort = { col: null, dir: null };
      }
    } else {
      this.#state.sort = { col, dir: 'asc' };
    }

    this.#state.pagination.pageIndex = 0;
    this.#commit('sort');
  }

  /**
   * Đặt hoặc thay thế filter rule cho một cột.
   * @param {string} col
   * @param {string} op  - Toán tử: 'contains' | '=' | '!=' | '>' | '>=' | '<' | '<='
   * @param {string} value
   */
  setFilter(col, op, value) {
    this.#state.filters = [
      ...this.#state.filters.filter(r => r.col !== col),
      { col, op, value }
    ];
    this.#state.pagination.pageIndex = 0;
    this.#commit('filter');
  }

  /**
   * Xóa filter rule của một cột cụ thể.
   * @param {string} col
   */
  clearFilter(col) {
    this.#state.filters = this.#state.filters.filter(r => r.col !== col);
    this.#state.pagination.pageIndex = 0;
    this.#commit('filter');
  }

  /**
   * Xóa toàn bộ filter rules và search query.
   */
  clearFilters() {
    this.#state.filters = [];
    this.#state.search = '';
    this.#state.pagination.pageIndex = 0;
    this.#commit('filter');
  }

  /**
   * Đặt global search query.
   * @param {string} query
   */
  setSearch(query) {
    this.#state.search = query.trim().toLowerCase();
    this.#state.pagination.pageIndex = 0;
    this.#commit('search');
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
    TableManager.get(tableId)?.setFilter(col, op, value);
  }

  static setFilterOptions(tableId, col, options) {
    const inst = TableManager.get(tableId);
    if (!inst) return;
    const colDef = inst.columns.all.find(c => c.key === col);
    if (colDef) colDef.filterOptions = options;
  }

  /**
   * Legacy API để lấy các dòng được select. Cân nhắc sử dụng `getRowSelection`
   * 
   * @deprecated
   */
  static getSelectedIds(tableId) {
    return TableManager.get(tableId)?.getRowSelection() || [];
  }

  static getRowSelection(tableId) {
    return TableManager.get(tableId)?.getRowSelection() || [];
  }

  static clearSelection(tableId) {
    TableManager.get(tableId)?.clearSelection();
  }

  static loadData(tableId, payload) {
    TableManager.get(tableId)?.loadData(payload);
  }
}
