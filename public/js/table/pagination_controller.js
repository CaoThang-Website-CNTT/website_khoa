export class PaginationController {
  #page;
  #limit;
  #total;
  #pageParam;
  #strategy; // 'qs' | 'ajax'
  #onChange;

  /**
   * @param {{
   * page: number,
   * limit: number,
   * total: number,
   * pageParam: string,
   * strategy: 'qs'|'ajax',
   * onChange: (page: number) => void
   * }} opts
   */
  constructor({ page, limit, total, pageParam, strategy, onChange }) {
    this.#page = page;
    this.#limit = limit;
    this.#total = total;
    this.#pageParam = pageParam;
    this.#strategy = strategy;
    this.#onChange = onChange;
  }

  get page() { return this.#page; }
  get limit() { return this.#limit; }
  get total() { return this.#total; }
  get totalPages() { return Math.max(1, Math.ceil(this.#total / this.#limit)); }
  get strategy() { return this.#strategy; }

  /** Được gọi khi phản hồi server đến với tổng số cập nhật */
  update({ total, page, limit }) {
    if (total != null) this.#total = total;
    if (page != null) this.#page = page;
    if (limit != null) this.#limit = limit;
  }

  goTo(page) {
    const clamped = Math.max(1, Math.min(page, this.totalPages));
    if (clamped === this.#page) return;
    this.#page = clamped;
    this.#onChange(clamped);
  }

  toParams() {
    return { [this.#pageParam]: this.#page };
  }

  /** Xây dựng URL cho một số trang cụ thể, giữ nguyên các tham số QS khác */
  buildUrl(page) {
    const url = new URL(window.location.href);
    url.searchParams.set(this.#pageParam, page);
    return url.toString();
  }

  /**
   * Tạo cửa sổ trang: [1, '…', 4, 5, 6, '…', 20]
   * @returns {(number|'…')[]}
   */
  window() {
    const { totalPages: n } = this;
    const p = this.#page;
    if (n <= 7) return Array.from({ length: n }, (_, i) => i + 1);
    if (p <= 4) return [1, 2, 3, 4, 5, '…', n];
    if (p >= n - 3) return [1, '…', n - 4, n - 3, n - 2, n - 1, n];
    return [1, '…', p - 1, p, p + 1, '…', n];
  }
}