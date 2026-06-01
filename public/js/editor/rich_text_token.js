/**
 * Tập hợp mark hợp lệ.
 * Thêm mark mới: bổ sung vào đây + RichTextParser.#TAG_TO_MARK + #MARK_TO_HTML.
 * @enum {string}
 */
export const MarkType = Object.freeze({
  BOLD: 'bold',
  ITALIC: 'italic',
  UNDERLINE: 'underline',
  LINK: 'link',
});

/**
 * Thứ tự wrap khi serialize ra HTML - outer → inner.
 * link phải ngoài cùng để <a> bao toàn bộ nội dung được format.
 * @type {readonly string[]}
 */
export const SERIALIZE_ORDER = Object.freeze([
  'link',
  'bold',
  'italic',
  'underline',
]);

/**
 * Runtime token - tồn tại trong bộ nhớ khi editor đang chạy.
 * marks dùng Set để O(1) lookup trong applyMark / getActiveMarks.
 */
export class RichToken {
  /** @type {string} */
  text;
  /** @type {Set<string>} */
  marks;
  /** @type {string|undefined} - chỉ có khi marks.has('link') */
  href;

  /**
   * @param {string}           text
   * @param {Iterable<string>} [marks]
   * @param {string}           [href]
   */
  constructor(text, marks = [], href = undefined) {
    this.text = text;
    this.marks = new Set(marks);
    if (href !== undefined) this.href = href;
  }

  /** @returns {boolean} */
  isLink() {
    return this.marks.has(MarkType.LINK);
  }

  /**
   * Clone token, optionally override fields.
   * Dùng khi applyMark cần split token mà không mutate bản gốc.
   *
   * @param {{ text?: string, marks?: Iterable<string>, href?: string }} [overrides]
   * @returns {RichToken}
   */
  clone(overrides = {}) {
    return new RichToken(
      overrides.text ?? this.text,
      overrides.marks ?? this.marks,
      overrides.href ?? this.href,
    );
  }

  /**
   * Kiểm tra hai token có thể merge không (cùng marks + href).
   * @param {RichToken} other
   * @returns {boolean}
   */
  isMergeable(other) {
    if (this.href !== other.href) return false;
    if (this.marks.size !== other.marks.size) return false;
    for (const m of this.marks) {
      if (!other.marks.has(m)) return false;
    }
    return true;
  }

  /**
   * Nối text của token khác vào, trả về token mới (immutable).
   * @param {RichToken} other
   * @returns {RichToken}
   */
  merge(other) {
    return this.clone({ text: this.text + other.text });
  }
}

/**
 * Persistence segment - cấu trúc lưu DB / gửi server.
 * marks dùng Array để JSON.stringify được.
 * href flat trên object, không nest vào attrs{}.
 */
export class RichSegment {
  /** @type {'text'|'link'} */
  type;
  /** @type {string} */
  text;
  /** @type {string[]} */
  marks;
  /** @type {string|undefined} - chỉ có khi type === 'link' */
  href;

  /**
   * @param {'text'|'link'} type
   * @param {string}        text
   * @param {string[]}      [marks]
   * @param {string}        [href]
   */
  constructor(type, text, marks = [], href = undefined) {
    this.type = type;
    this.text = text;
    this.marks = marks;
    if (href !== undefined) this.href = href;
  }

  /** @returns {boolean} */
  isLink() {
    return this.type === 'link';
  }

  /**
   * Chuyển plain object từ DB → RichToken runtime.
   * Xử lý cả v1 schema (attrs.href) lẫn v2 schema (flat href) - backward compat.
   *
   * @param {object} raw
   * @returns {RichToken}
   */
  static toToken(raw) {
    const href = raw.href ?? raw.attrs?.href ?? undefined;
    return new RichToken(raw.text ?? '', raw.marks ?? [], href);
  }

  /**
   * Chuyển RichToken → RichSegment để lưu DB.
   * Luôn ghi ra v2 format (flat href, không có attrs{}).
   *
   * @param {RichToken} token
   * @returns {RichSegment}
   */
  static fromToken(token) {
    const isLink = token.isLink();
    return new RichSegment(
      isLink ? 'link' : 'text',
      token.text,
      [...token.marks],
      isLink ? (token.href ?? '') : undefined,
    );
  }

  /**
   * Serialize thành plain object để JSON.stringify.
   * @returns {object}
   */
  toJSON() {
    const obj = { type: this.type, text: this.text, marks: this.marks };
    if (this.href !== undefined) obj.href = this.href;
    return obj;
  }
}