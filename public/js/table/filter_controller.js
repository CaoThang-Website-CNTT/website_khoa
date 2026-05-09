/**
 * @typedef {{ col: string, op: string, value: string }} FilterRule
 */
export class FilterController {
  #search = '';
  /** @type {FilterRule[]} */
  #rules = [];
  #onChange;

  /** @param {(rules: FilterRule[], search: string) => void} onChange */
  constructor(onChange) { this.#onChange = onChange; }

  setSearch(v) {
    this.#search = v.trim().toLowerCase();
    this.#onChange(this.#rules, this.#search);
  }

  /** Thêm hoặc thay thế quy tắc cho một cột cụ thể */
  setRule(col, op, value) {
    this.#rules = [...this.#rules.filter(r => r.col !== col), { col, op, value }];
    this.#onChange(this.#rules, this.#search);
  }

  clearRule(col) {
    this.#rules = this.#rules.filter(r => r.col !== col);
    this.#onChange(this.#rules, this.#search);
  }

  clearAll() {
    this.#rules = [];
    this.#search = '';
    this.#onChange(this.#rules, this.#search);
  }

  get rules() { return [...this.#rules]; }
  get search() { return this.#search; }

  /** Tuần tự hóa cho query string / AJAX */
  toParams() {
    const params = {};
    if (this.#search) params['search'] = this.#search;
    this.#rules.forEach((r, i) => {
      params[`filter[${i}][col]`] = r.col;
      params[`filter[${i}][op]`] = r.op;
      params[`filter[${i}][val]`] = r.value;
    });
    return params;
  }

  static #normalize(str) {
    return String(str || '')
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/đ/g, 'd');
  }

  /**
   * Predicate phía client. Trả về true nếu row thỏa mãn tất cả quy tắc + tìm kiếm.
   * @param {object} row
   * @param {string[]} searchableKeys — Các trường để tìm kiếm
   */
  predicate(row, searchableKeys) {
    // Tìm kiếm
    if (this.#search) {
      const q = FilterController.#normalize(this.#search);
      const hit = searchableKeys.some(k =>
        FilterController.#normalize(row[k]).includes(q)
      );
      if (!hit) return false;
    }
    // Quy tắc lọc
    for (const r of this.#rules) {
      const cell = row[r.col];
      if (!FilterController.#eval(cell, r.op, r.value)) return false;
    }
    return true;
  }

  static #eval(cell, op, value) {
    const c = typeof cell === 'number' ? cell : FilterController.#normalize(cell);
    const v = isNaN(Number(value)) ? FilterController.#normalize(value) : Number(value);

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
}