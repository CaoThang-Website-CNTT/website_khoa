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
   * @param {((row: object, value: any) => DocumentFragment)|null} [opts.render=null]
   */
  constructor(opts) {
    this.key = opts.key;
    this.label = opts.label;
    this.sortable = Boolean(opts.sortable);
    this.width = opts.width ?? null;
    this.align = opts.align ?? 'left';
    this.filterType = opts.filterType ?? null;
    this.filterOps = Array.isArray(opts.filterOps) ? opts.filterOps : [];
    this.render = opts.render ?? null;
  }
}