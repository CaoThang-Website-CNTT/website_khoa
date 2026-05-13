import { ColumnRegistry } from './column_registry.js';
import { DataAdapter } from './data_adapter.js';
import { SortController } from './sort_controller.js';
import { FilterController } from './filter_controller.js';
import { PaginationController } from './pagination_controller.js';
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
  columns;
  adapter;
  sort;
  filter;
  pagination;
  #renderer;
  #table = null;
  #loading = false;

  constructor(root) {
    this.root = root;
    this.id = root.dataset.tm;
    if (!this.id) throw new Error('[TableManager] Missing data-tm id');

    this.columns = new ColumnRegistry(root, DEFAULT_FILTER_OPS);

    // Tự động thêm cột Drag Handle & Checkbox ở đầu
    this.columns.all.unshift({
      key: '__selector',
      label: '',
      width: '48px',
      align: 'center',
      sortable: false,
      isSpecial: true
    });

    const inlineRows = TableInstance.#readInlineData(this.id);
    const mode = root.dataset.tmMode ?? 'server';
    const src = root.dataset.tmSrc ?? null;

    this.adapter = new DataAdapter({ src, mode, inlineRows: inlineRows.rows });

    const pageParam = root.dataset.tmPageParam ?? `${this.id}_page`;
    const strategy = root.dataset.tmStrategy ?? 'qs';
    const initPage = Number(new URLSearchParams(window.location.search).get(pageParam)) || inlineRows.page || 1;

    this.sort = new SortController(() => { if (this.adapter.isServerDriven) this.reload(); else this.render(); });
    this.filter = new FilterController(() => { if (this.adapter.isServerDriven) this.reload(); else this.render(); });

    this.pagination = new PaginationController({
      page: initPage,
      limit: inlineRows.limit ?? 20,
      total: inlineRows.total ?? inlineRows.rows.length,
      pageParam,
      strategy,
      onChange: (page) => {
        if (strategy === 'ajax') {
          this.reload();
        }
      },
    });

    this.#renderer = new TableRenderer(this);
  }

  static #readInlineData(id) {
    const el = document.querySelector(`script[data-tm-data="${id}"]`);
    if (!el) return { rows: [], total: 0, page: 1, limit: 20 };
    try { return JSON.parse(el.textContent); } catch { return { rows: [], total: 0, page: 1, limit: 20 }; }
  }

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
    wrapper.appendChild(dataWrapper);

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
        const tfoot = this.#table.querySelector('tfoot');
        if (tfoot) {
          tfoot.innerHTML = '';
          const tr = document.createElement('tr');
          tr.className = 'tm-footer-tr';
          const td = document.createElement('td');
          td.colSpan = this.columns.all.length;
          td.appendChild(footer);
          tr.appendChild(td);
          tfoot.appendChild(tr);
        }
      }
    }

    this.root.appendChild(wrapper);
    console.log(`[TableManager] Bảng "${this.id}" đã được khởi tạo.`, {
      columns: this.columns.all.length,
      mode: this.adapter.mode,
      hasPagination: !!(hasInternalTemplate || isExternal)
    });
    this.render();
  }

  render() {
    if (!this.#table) return;
    const { mode } = this.adapter;
    let rows = this.adapter.allRows;

    if (mode === 'client' || !this.adapter.isServerDriven) {
      const searchKeys = this.columns.all.map(c => c.key);
      rows = rows.filter(r => this.filter.predicate(r, searchKeys));

      const cmp = this.sort.comparator();
      if (cmp) rows = [...rows].sort(cmp);

      this.pagination.update({ total: rows.length });

      const { page, limit } = this.pagination;
      if (limit !== Infinity) {
        rows = rows.slice((page - 1) * limit, page * limit);
      }
    }

    this.#renderer.renderRows(rows, this.#table);
    this.#renderer.updateSortUI(this.#table, this.sort.state);
    this.#renderer.renderPagination(this.pagination);
    this.#renderer.renderFilters(this.filter.rules);
    console.log(`[TableManager] Render "${this.id}":`, {
      rows: rows.length,
      sort: this.sort.state,
      filters: this.filter.rules.length
    });
  }

  async reload() {
    if (this.#loading) return;
    this.#loading = true;
    this.#setLoading(true);
    const params = {
      ...this.sort.toParams(),
      ...this.filter.toParams(),
      ...this.pagination.toParams(),
    };

    try {
      const result = await this.adapter.fetch(params);
      this.pagination.update(result);
      this.#renderer.renderRows(result.rows, this.#table);
      this.#renderer.updateSortUI(this.#table, this.sort.state);
      this.#renderer.renderPagination(this.pagination);
    } catch (err) {
      console.error('[TableManager] reload error:', err);
    } finally {
      this.#loading = false;
      this.#setLoading(false);
    }
  }

  #setLoading(on) {
    const overlay = this.root.querySelector('[data-tm-loading]');
    if (overlay) overlay.style.display = on ? 'flex' : 'none';
  }
}

export class TableManager {
  static #registry = new Map();

  static init() {
    document.querySelectorAll('[data-tm]:not([data-tm-initialized])').forEach(root => {
      try {
        const id = root.dataset.tm;
        if (TableManager.#registry.has(id)) return;

        const inst = new TableInstance(root);
        TableManager.#registry.set(inst.id, inst);
        inst.init();
        root.dataset.tmInitialized = 'true';
        console.log(`[TableManager] Đã đăng ký bảng: ${id}`);
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
}
