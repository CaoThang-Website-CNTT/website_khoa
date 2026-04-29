export class SnapshotRing {
  /** @type {T[]} */
  #slots;
  /** @type {number} — index vòng để ghi delta mới tiếp theo */
  #writeHead = 0;
  /** @type {number} — số delta thực sự đang chứa (≤ capacity) */
  #size = 0;
  /** @type {number} — con trỏ undo/redo, tính từ 0 = delta cũ nhất còn lưu */
  #cursor = -1;
  /** @type {number} */
  #capacity;

  /** @param {number} capacity — số snapshot tối đa (cấu hình từ ngoài) */
  constructor(capacity) {
    if (capacity < 1) throw new RangeError('SnapshotRing capacity phải ≥ 1');
    this.#capacity = capacity;
    this.#slots = new Array(capacity);
  }

  // ─── Write ────────────────────────────────────────────────────────────────

  /**
   * Ghi một delta mới vào ring.
   * Nếu cursor không ở cuối (tức đang ở trạng thái "đã undo một phần"),
   * toàn bộ delta phía sau cursor sẽ bị loại bỏ trước khi ghi.
   *
   * @param {T} delta
   */
  push(delta) {
    // Nếu cursor không ở cuối ring → có "future" bị overwrite (redo branch)
    // Đây là hành vi chuẩn: edit mới sau undo xoá toàn bộ redo stack
    if (this.#cursor < this.#size - 1) {
      // Truncate: chỉ giữ lại phần [0..cursor], kích thước = cursor + 1
      this.#size = this.#cursor + 1;
      // writeHead cũng phải nhảy về sau phần còn lại
      this.#writeHead = this.#size % this.#capacity;
    }

    this.#slots[this.#writeHead] = delta;
    this.#writeHead = (this.#writeHead + 1) % this.#capacity;

    if (this.#size < this.#capacity) {
      this.#size++;
    }
    // Khi vượt capacity: size giữ nguyên = capacity, writeHead vòng lại ghi đè oldest

    this.#cursor = this.#size - 1;
  }

  // ─── Traversal ────────────────────────────────────────────────────────────

  /**
   * Lấy delta tại cursor hiện tại để undo, sau đó lùi cursor.
   * @returns {T|null}
   */
  stepBack() {
    if (this.#cursor < 0) return null;
    const delta = this.#getAt(this.#cursor);
    this.#cursor--;
    return delta;
  }

  /**
   * Tiến cursor rồi trả về delta tại vị trí đó để redo.
   * @returns {T|null}
   */
  stepForward() {
    if (this.#cursor >= this.#size - 1) return null;
    this.#cursor++;
    return this.#getAt(this.#cursor);
  }

  // ─── Query ────────────────────────────────────────────────────────────────

  get canUndo() { return this.#cursor >= 0; }
  get canRedo() { return this.#cursor < this.#size - 1; }
  get size() { return this.#size; }

  /**
   * Trả về snapshot tại vị trí logic `index` (0 = oldest còn lại trong ring).
   * @param {number} index
   * @returns {T}
   */
  #getAt(index) {
    // Nếu ring chưa đầy: slot vật lý = index thẳng
    // Nếu ring đã vòng: oldest ở writeHead, các slot kế tiếp theo vòng
    const physicalOldest = this.#size < this.#capacity
      ? 0
      : this.#writeHead; // writeHead đang trỏ vào oldest bị overwrite tiếp theo

    return this.#slots[(physicalOldest + index) % this.#capacity];
  }

  /** Xoá toàn bộ lịch sử */
  clear() {
    this.#slots = new Array(this.#capacity);
    this.#writeHead = 0;
    this.#size = 0;
    this.#cursor = -1;
  }
}