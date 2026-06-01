import { BlockSerializer } from '../block_serializer_v2.js';
import { RichTextParser } from '../rich_text_parser.js';

export class EditorBlock {
  /**
   * @param {object} blockData  - dữ liệu từ DB
   * @param {object} schema     - config của block
   * @param {object} [bus]      - EditorEventBus
   */
  constructor(blockData = {}, schema, bus = null) {
    this.id = blockData.id || crypto.randomUUID();
    this.type = schema.type;
    this.data = this.#parseDataWithSchema(blockData.data || {}, schema);
    this.schema = schema;

    /** @type {import('./editor.js').EditorEventBus} */
    this.bus = bus;

    /** @type {HTMLElement|null} */
    this._dom = null;
  }

  get dom() {
    return this._dom;
  }

  set dom(el) {
    this._dom = el;
    if (this._dom && this.data.meta.anchor_id) {
      this._dom.id = this.data.meta.anchor_id;
    }
  }

  // ─── Schema ───────────────────────────────────────────────────────────────

  #parseDataWithSchema(currentData, schema) {
    const meta = this.#parseMeta(currentData.meta ?? {}, schema.meta ?? {});

    // Luôn đảm bảo có anchor_id trong meta để dùng chung
    meta.anchor_id = currentData.meta?.anchor_id ?? '';

    return {
      rich_text: currentData.rich_text ?? [],
      meta: meta,
    };
  }

  #parseMeta(currentMeta, metaSchema) {
    const parsed = {};
    for (const [key, config] of Object.entries(metaSchema)) {
      parsed[key] = currentMeta[key] !== undefined ? currentMeta[key] : config.default;
    }
    return parsed;
  }

  // ─── Abstract - phải override ─────────────────────────────────────────────

  /**
   * Render block ra canvas.
   * @abstract
   * @returns {HTMLElement}
   */
  render() {
    throw new Error(`${this.type} phải implement render().`);
  }

  /**
   * Render panel settings bên phải.
   * @abstract
   * @returns {HTMLElement}
   */
  renderInspectorControls() {
    throw new Error(`${this.type} phải implement renderInspectorControls().`);
  }

  /**
   * Đóng gói data để gửi server - mỗi block tự biết cách đọc từ DOM.
   * Base class xử lý trường hợp có field `content` đọc từ editableEl.
   * Block phức tạp (List, Table) override để đọc từ this.data trực tiếp.
   *
   * @param {HTMLElement|null} editableEl
   * @returns {object}
   */
  serializeData(editableEl) {
    const html = editableEl?.innerHTML?.trim() ?? '';
    return {
      rich_text: html
        ? BlockSerializer.tokensToSegments(RichTextParser.parse(html))
        : [],
      meta: { ...this.data.meta },
    };
  }

  // ─── Rich text helpers ────────────────────────────────────────────────────

  /**
   * Extract plain text từ content - hỗ trợ cả string (legacy) lẫn RichSegment[].
   * @param {string|Array<{text:string}>|null|undefined} content
   * @returns {string}
   */
  _extractText(content) {
    if (!content) return '';
    if (typeof content === 'string') return content;
    if (Array.isArray(content)) return content.map(s => s.text ?? '').join('');
    return '';
  }

  /**
   * Deep clone this.data - tránh mutate khi serialize.
   * @returns {object}
   */
  _cloneData() {
    try { return JSON.parse(JSON.stringify(this.data)); } catch { return { ...this.data }; }
  }

  // ─── DOM helpers ──────────────────────────────────────────────────────────

  /**
   * Escape string tránh XSS khi inject vào innerHTML.
   * @param {string} s
   * @returns {string}
   */
  esc(s) {
    return (s || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  /**
   * Focus block, đặt caret theo position.
   * Các block có nhiều editable el (Quote, List, Table) nên override.
   *
   * @param {object}          bus
   * @param {'start'|'end'}   [position='end']
   */
  focus(bus, position = 'end') {
    if (!this.dom) return;

    this.dom.focus();

    const range = document.createRange();
    const sel = window.getSelection();
    range.selectNodeContents(this.dom);
    range.collapse(position === 'start');
    sel.removeAllRanges();
    sel.addRange(range);

    bus.dispatch('block:selected', { blockId: this.id });
  }

  /**
   * Paste plain text tại vị trí caret hiện tại.
   * @param {string} text
   */
  paste(text) {
    const sel = window.getSelection();
    if (!sel?.rangeCount) return;
    const range = sel.getRangeAt(0);
    range.deleteContents();
    range.insertNode(document.createTextNode(text));
    sel.collapseToEnd();
  }

  exportCanonical() {
    return {
      rich_text: this.data.rich_text ?? [],
      meta: { ...this.data.meta },
    };
  }

  importCanonical({ rich_text }) {
    this.data.rich_text = rich_text ?? [];
  }
}