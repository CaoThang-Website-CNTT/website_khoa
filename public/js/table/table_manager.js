import { ColumnRegistry } from './column_registry.js';
import { ColumnDef } from './column_def.js';
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

    const selectable = 'tmSelectable' in root.dataset && root.dataset.tmSelectable === 'true';
    if (selectable) {
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

  init() {
    const wrapper = document.createElement('div');
    wrapper.className = 'tm-wrapper';
    wrapper.dataset.tmWrapper = this.id;

    // 1. Header Controls
    const header = document.createElement('div');
    header.className = 'tm-header-controls';
    header.appendChild(this.#renderer.buildToolbar());
    wrapper.appendChild(header);

    // 2. Data Wrapper
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

    // 3. Footer Controls
    const footer = document.createElement('div');
    footer.className = 'tm-footer-controls';

    const pagId = this.id;
    // Check if there is an EXTERNAL pagination container defined anywhere on the page
    const externalPag = document.querySelector(`[data-tm-pagination="${pagId}"]`);
    const isExternal = externalPag && !this.root.contains(externalPag);

    // Check for declarative pagination template inside the root
    const hasInternalTemplate = this.root.querySelector('template[data-tm-pagination]');

    // If we have an internal template AND no external container, build footer pagination
    if (hasInternalTemplate && !isExternal) {
      const info = document.createElement('div');
      info.className = 'tm-page-info';
      info.dataset.tmPageInfo = pagId;
      footer.appendChild(info);

      const pagContainer = document.createElement('div');
      pagContainer.dataset.tmPagination = pagId;
      footer.appendChild(pagContainer);

      wrapper.appendChild(footer);
    }
    // If there is an external one, we might still want the footer for other controls, 
    // but we won't create a second pagination container inside it.
    else if (footer.children.length > 0) {
      wrapper.appendChild(footer);
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

  static getSelectedIds(tableId) {
    return TableManager.get(tableId)?.getSelectedIds() || [];
  }

  static clearSelection(tableId) {
    TableManager.get(tableId)?.clearSelection();
  }
}
