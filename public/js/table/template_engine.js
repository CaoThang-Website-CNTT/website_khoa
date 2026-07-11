export class TemplateEngine {
  /** @type {Record<string, Function>} */
  static #helpers = {};

  /**
   * Đăng ký helper function để dùng trong template expression.
   * Helper được inject vào scope của mọi template được compile SAU lời gọi này.
   * Template compile TRƯỚC khi đăng ký sẽ không có helper.
   *
   * @param {string} name - Tên gọi trong template, VD: 'formatDate'
   * @param {Function} fn
   *
   * @example
   * TemplateEngine.registerHelper('formatDate', val => dayjs(val).format('DD/MM/YYYY'));
   * TemplateEngine.registerHelper('currency', val => new Intl.NumberFormat('vi-VN').format(val));
   *
   * // Trong template:
   * // {{ formatDate(row.created_at) }}
   * // {{ currency(value) }}
   */
  static registerHelper(name, fn) {
    if (typeof fn !== 'function') throw new Error(`[TemplateEngine] Helper "${name}" phải là function`);
    TemplateEngine.#helpers[name] = fn;
  }

  /**
   * Biên dịch innerHTML của phần tử <template> thành hàm render.
   * Hỗ trợ: {{value}}, {{row.field}}, {{row.field || 'fallback'}}.
   * @param {HTMLTemplateElement} tpl
   * @returns {(row: object, value: any) => DocumentFragment}
   */
  static compile(tpl) {
    const html = tpl.innerHTML;
    const body = html.replace(/{{([\s\S]+?)}}/g, (_, expr) => `\${__esc(__val(ctx, ${JSON.stringify(expr.trim())}))}`);

    const helperNames = Object.keys(TemplateEngine.#helpers);
    const helperVals = helperNames.map(k => TemplateEngine.#helpers[k]);

    const fn = new Function(
      '__esc', '__val', 'ctx', ...helperNames,
      `return \`${body}\`;`
    );

    return (row, value) => {
      const ctx = { ...row, value, row };
      helperNames.forEach((name, index) => {
        ctx[name] = helperVals[index];
      });
      const rendered = fn(TemplateEngine.#esc, TemplateEngine.#val, ctx, ...helperVals);
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
      const scope = { ...TemplateEngine.#helpers, ...ctx };
      const keys = Object.keys(scope);
      const vals = keys.map(k => scope[k]);
      return new Function(...keys, `return (${expr});`)(...vals);
    } catch {
      return '';
    }
  }
}
