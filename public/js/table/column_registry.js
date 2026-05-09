import { ColumnDef } from './column_def.js';
import { TemplateEngine } from './template_engine.js';

export class ColumnRegistry {
  /** @type {ColumnDef[]} */
  #cols = [];

  /**
   * @param {HTMLElement} root — Phần tử gốc [data-tm]
   * @param {Record<string, string[]>} filterOpsMap — Bản đồ toán tử mặc định
   */
  constructor(root, filterOpsMap = {}) {
    root.querySelectorAll(':scope > template[data-tm-col]').forEach(tpl => {
      const filterType = tpl.dataset.tmFilterType || null;
      const opsRaw = tpl.dataset.tmFilterOps;
      const filterOps = opsRaw
        ? opsRaw.split(',').map(s => s.trim())
        : (filterType ? filterOpsMap[filterType] ?? [] : []);

      let filterOptions = null;
      if (tpl.dataset.tmFilterOptions) {
        try { filterOptions = JSON.parse(tpl.dataset.tmFilterOptions); } catch (e) { console.error('[TableManager] Invalid JSON for filter options:', e); }
      }

      /** @type {ColumnDef} */
      const def = new ColumnDef({
        key: tpl.dataset.tmCol,
        label: tpl.dataset.tmLabel ?? tpl.dataset.tmCol,
        sortable: 'tmSortable' in tpl.dataset,
        width: tpl.dataset.tmWidth ?? null,
        align: tpl.dataset.tmAlign ?? 'left',
        filterType,
        filterOps,
        filterOptions,
        render: tpl.innerHTML.trim() ? TemplateEngine.compile(tpl) : null,
      });
      this.#cols.push(def);
    });
  }

  /** @returns {ColumnDef[]} */
  get all() { return this.#cols; }
}