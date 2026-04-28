export class EditorBlock {
  /**
   * @param {Object} blockData - Dữ liệu thực tế lưu trong Database (nếu có)
   * @param {Object} schema - File config JSON của block này
   */
  constructor(blockData = {}, schema, bus = null) {
    this.id = blockData.id || crypto.randomUUID();

    this.type = schema.type;

    this.data = this.#parseDataWithSchema(blockData.data || {}, schema.attributes);

    this.schema = schema;

    /**@type {EditorEventBus} */
    this.bus = bus;

    /** @type {HTMLElement} */
    this.dom = null;
  }

  #parseDataWithSchema(currentData, attributesSchema) {
    const parsedData = {};
    for (const [key, config] of Object.entries(attributesSchema)) {
      parsedData[key] = currentData[key] !== undefined ? currentData[key] : config.default;
    }
    return parsedData;
  }

  /**
 * Render ra canvas
 * @abstract
 * 
 * @param {{onUpdate:Function}} param1 
 */
  render(
    {
      onUpdate,
    } = null
  ) {
    throw new Error("Block phải triển khai phương thức 'render()'.");
  }

  /**
   * Render ra cửa sổ settings
   * @abstract
   * 
   * @param {{onUpdate:Function}} param1 
   */
  renderInspectorControls(
    data,
    {
      onUpdate,
    } = null
  ) {
    throw new Error("Block phải triển khai phương thức 'renderInspectorControls()'.");
  }

  /**
   * Escape string tránh XSS
   * @param {string} s 
   * @returns {string} String đã được escaped
   */
  esc(s) {
    return (s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  /**
   * Focus block
   * @param {'start' | 'end'} position 
   */
  focus(bus, position = 'end') {
    if (!this.dom) return;

    if (this.dom) {
      this.dom.focus();

      const range = document.createRange();
      const selection = window.getSelection();
      range.selectNodeContents(this.dom);
      range.collapse(position === 'start');
      selection.removeAllRanges();
      selection.addRange(range);
    }

    bus.dispatch('block:selected', { blockId: this.id });
  }

  paste(text) {
    const sel = window.getSelection();
    if (!sel.rangeCount) return;
    const range = sel.getRangeAt(0);
    range.deleteContents();
    range.insertNode(document.createTextNode(text));
    sel.collapseToEnd();
  }
}