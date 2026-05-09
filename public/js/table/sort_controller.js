/**
 * @typedef {'asc'|'desc'|null} SortDir
 * @typedef {{ col: string|null, dir: SortDir }} SortState
 */
export class SortController {
  /** @type {SortState} */
  #state = { col: null, dir: null };
  #onChange;

  /** @param {(state: SortState) => void} onChange */
  constructor(onChange) { this.#onChange = onChange; }

  toggle(col) {
    if (this.#state.col !== col) {
      this.#state = { col, dir: 'asc' };
    } else if (this.#state.dir === 'asc') {
      this.#state = { col, dir: 'desc' };
    } else {
      this.#state = { col: null, dir: null }; // Click lần thứ 3 để xóa sắp xếp
    }
    this.#onChange(this.#state);
  }

  get state() { return { ...this.#state }; }

  /** Tuần tự hóa cho query string / AJAX */
  toParams() {
    if (!this.#state.col) return {};
    return { sort: this.#state.col, dir: this.#state.dir };
  }

  /** Hàm so sánh sắp xếp phía client */
  comparator() {
    const { col, dir } = this.#state;
    if (!col) return null;
    return (a, b) => {
      const va = String(a[col] ?? '');
      const vb = String(b[col] ?? '');
      const cmp = va.localeCompare(vb, 'vi', { numeric: true, sensitivity: 'accent' });
      return dir === 'asc' ? cmp : -cmp;
    };
  }
}