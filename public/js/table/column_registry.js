import { TemplateEngine } from './template_engine.js';

/**
 * Đại diện cho một cột trong bảng
 */
export class ColumnDef {
  /** @type {string} Khóa trường dữ liệu (map với thuộc tính của row) */
  key;

  /** @type {string} Tiêu đề hiển thị trên header */
  label;

  /** @type {boolean} Cờ cho phép người dùng sắp xếp cột */
  sortable;

  /** @type {string|null} Độ rộng CSS (VD: '100px', '15%', 'auto') */
  width;

  /** @type {'left'|'center'|'right'} Căn lề nội dung trong ô */
  align;

  /** @type {string|null} Loại bộ lọc: 'text' | 'number' | 'select' | 'date' | null */
  filterType;

  /** @type {string[]} Danh sách toán tử lọc được phép áp dụng cho cột */
  filterOps;

  /** @type {array|null} Danh sách option cho bộ lọc select */
  filterOptions;

  /** @type {((row: object, value: any) => DocumentFragment)|null} Hàm render DOM tùy chỉnh từ <template> */
  render;

  /**
   * Khởi tạo instance ColumnDef.
   * @param {Object} opts - Đối tượng cấu hình cột
   * @param {string} opts.key
   * @param {string} opts.label
   * @param {boolean} opts.sortable
   * @param {string|null} [opts.width=null]
   * @param {'left'|'center'|'right'} [opts.align='left']
   * @param {string|null} [opts.filterType=null]
   * @param {string[]} [opts.filterOps=[]]
   * @param {array|null} [opts.filterOptions=null]
   * @param {((row: object, value: any) => DocumentFragment)|null} [opts.render=null]
   */
  constructor(opts) {
    this.key = opts.key;
    this.label = opts.label;
    this.sortable = Boolean(opts.sortable);
    this.width = opts.width ?? null;
    this.align = opts.align ?? "left";
    this.filterType = opts.filterType ?? null;
    this.filterOps = Array.isArray(opts.filterOps) ? opts.filterOps : [];
    this.filterOptions = opts.filterOptions ?? null;
    this.render = opts.render ?? null;
  }
}

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

  /**
   * @param {ColumnDef} col
   */
  prepend(col) {
    this.#cols.unshift(col);
  }
}