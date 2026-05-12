export class TemplateEngine {
  /**
   * Biên dịch innerHTML của phần tử <template> thành hàm render.
   * Hỗ trợ: {{value}}, {{row.field}}, {{row.field || 'fallback'}}.
   * @param {HTMLTemplateElement} tpl
   * @returns {(row: object, value: any) => DocumentFragment}
   */
  static compile(tpl) {
    const html = tpl.innerHTML;
    // Chuyển đổi {{expr}} → placeholder của template literal ES6
    const body = html.replace(/{{([\s\S]+?)}}/g, (_, expr) => `\${__esc(__val(ctx, ${JSON.stringify(expr.trim())}))}`);

    const fn = new Function(
      '__esc', '__val', 'ctx',
      `return \`${body}\`;`
    );

    return (row, value) => {
      const ctx = { ...row, value, row };
      const rendered = fn(TemplateEngine.#esc, TemplateEngine.#val, ctx);
      const div = document.createElement('div');
      div.innerHTML = rendered;
      const frag = document.createDocumentFragment();
      while (div.firstChild) frag.appendChild(div.firstChild);
      return frag;
    };
  }

  /** Escape an toàn cho ngữ cảnh HTML */
  static #esc(v) {
    if (v == null) return '';
    return String(v)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  /**
   * Đánh giá đường dẫn dot-path hoặc biểu thức đơn giản dựa trên ctx.
   * Hỗ trợ: "row.status", "value", "row.x || 'default'"
   */
  static #val(ctx, expr) {
    // Đường dẫn nhanh: khóa đơn giản hoặc dot-path
    if (/^[\w.]+$/.test(expr)) {
      return expr.split('.').reduce((o, k) => (o != null ? o[k] : undefined), ctx);
    }
    // Fallback: eval an toàn với các khóa ctx trong scope
    try {
      const keys = Object.keys(ctx);
      const vals = keys.map(k => ctx[k]);
      return new Function(...keys, `return (${expr});`)(...vals);
    } catch {
      return '';
    }
  }
}