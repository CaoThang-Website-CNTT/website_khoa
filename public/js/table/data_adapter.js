export class DataAdapter {
  #src;   // URL AJAX hoặc null
  #mode;  // 'server' | 'client'
  /** @type {object[]} Raw inline rows (PHP-inline hoặc nguồn client-side) */
  #inlineRows = [];

  /**
   * @param {{ src: string|null, mode: 'server'|'client', inlineRows: object[] }} opts
   */
  constructor({ src, mode, inlineRows }) {
    this.#src = src;
    this.#mode = mode;
    this.#inlineRows = inlineRows;
  }

  get mode() { return this.#mode; }
  get isServerDriven() { return this.#mode === 'server' && !!this.#src; }

  /**
   * Lấy dữ liệu.
   * Chế độ server-AJAX: gọi endpoint.
   * Chế độ server-PHP: dữ liệu đã có trong bộ nhớ (không cần fetch).
   * @param {object} params — Các tham số sort + filter + pagination đã gộp
   * @returns {Promise<{ rows: object[], total: number, page: number, limit: number }>}
   */
  async fetch(params) {
    if (this.isServerDriven) {
      const url = new URL(this.#src, window.location.origin);
      Object.entries(params).forEach(([k, v]) => url.searchParams.set(k, v));
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      if (!res.ok) throw new Error(`[TableManager] Fetch failed: ${res.status}`);
      const json = await res.json();

      // Hỗ trợ cả { rows, total, page, limit } và { data: { items, total, ... } }
      if (json.data?.items) {
        return { rows: json.data.items, total: json.data.total, page: json.data.currentPage, limit: json.data.limit };
      }
      return json;
    }
    // Client-side hoặc PHP-inline: áp dụng sort + filter + pagination trong JS
    return { rows: this.#inlineRows, total: this.#inlineRows.length, page: 1, limit: Infinity };
  }

  get allRows() { return this.#inlineRows; }

  updateInlineRows(newRows) {
    this.#inlineRows = newRows;
  }
}