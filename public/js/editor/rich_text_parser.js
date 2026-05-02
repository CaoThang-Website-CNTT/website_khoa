import { RichToken, SERIALIZE_ORDER } from './rich_text_token.js';

export class RichTextParser {

  /**
   * HTML tag → mark name.
   * Thêm mark mới: thêm entry ở đây + #MARK_TO_HTML.
   * @type {Record<string, string>}
   */
  static #TAG_TO_MARK = {
    STRONG: 'bold', B: 'bold',
    EM: 'italic', I: 'italic',
    U: 'underline',
    A: 'link',
  };

  /**
   * Trả về mark name tương ứng với tagName.
   * @param {string} tagName 
   * @returns {string|null}
   */
  static getMarkByTag(tagName) {
    return RichTextParser.#TAG_TO_MARK[tagName.toUpperCase()] || null;
  }

  /**
   * mark name → hàm wrap HTML (html, token) → wrappedHtml.
   * token được truyền vào để link có thể đọc href mà không cần special-case riêng.
   * @type {Record<string, (html: string, token: RichToken) => string>}
   */
  static #MARK_TO_HTML = {
    bold: html => `<strong>${html}</strong>`,
    italic: html => `<em>${html}</em>`,
    underline: html => `<u>${html}</u>`,
    link: (html, token) => {
      const href = RichTextParser.#escapeAttr(token.href ?? '');
      return `<a href="${href}" target="_blank" rel="noopener noreferrer">${html}</a>`;
    },
  };

  /**
   * HTML string → RichToken[].
   * Entry point duy nhất để đọc nội dung từ contenteditable.
   *
   * @param {string} html
   * @returns {RichToken[]}
   */
  static parse(html) {
    if (!html || html.trim() === '') return [];

    const container = document.createElement('div');
    container.innerHTML = html;

    /** @type {RichToken[]} */
    const tokens = [];
    RichTextParser.#walk(container, new Set(), null, tokens);

    return RichTextParser.mergeAdjacentTokens(tokens);
  }

  /**
   * DFS walker — thu thập text node kèm inherited marks.
   *
   * @param {Node}        node
   * @param {Set<string>} inheritedMarks
   * @param {string|null} inheritedHref
   * @param {RichToken[]} out — mutate in-place
   */
  static #walk(node, inheritedMarks, inheritedHref, out) {
    if (node.nodeType === Node.TEXT_NODE) {
      const text = node.textContent;
      if (text.length === 0) return;
      out.push(new RichToken(
        text,
        inheritedMarks,
        inheritedHref ?? undefined,
      ));
      return;
    }

    if (node.nodeType !== Node.ELEMENT_NODE) return;

    const tag = node.tagName;

    // BR → newline token, không kế thừa mark
    if (tag === 'BR') {
      out.push(new RichToken('\n'));
      return;
    }

    const mark = RichTextParser.#TAG_TO_MARK[tag];

    // Clone để không mutate
    const currentMarks = new Set(inheritedMarks);
    let currentHref = inheritedHref;

    if (mark) {
      currentMarks.add(mark);
      if (mark === 'link') {
        currentHref = node.getAttribute('href') ?? '';
      }
    }

    for (const child of node.childNodes) {
      RichTextParser.#walk(child, currentMarks, currentHref, out);
    }
  }

  /**
   * RichToken[] → HTML string.
   * Entry point duy nhất để ghi nội dung vào innerHTML.
   *
   * @param {RichToken[]} tokens
   * @returns {string}
   */
  static serialize(tokens) {
    if (!tokens || tokens.length === 0) return '';

    return tokens.map(token => {
      // Newline token (từ <br>) → trả về <br> để đảm bảo bijective round-trip
      if (token.text === '\n' && token.marks.size === 0) return '<br>';

      let html = RichTextParser.#escapeHtml(token.text);

      // Wrap từ trong ra ngoài — reversed SERIALIZE_ORDER:
      // ['link','bold','italic','underline'] reversed → underline wrap trước, link wrap sau
      // Kết quả: <a><strong><em><u>text</u></em></strong></a>
      const order = [...SERIALIZE_ORDER].reverse();

      for (const mark of order) {
        if (!token.marks.has(mark)) continue;
        const wrapper = RichTextParser.#MARK_TO_HTML[mark];
        if (wrapper) html = wrapper(html, token);
      }

      return html;
    }).join('');
  }

  /**
   * Gộp các RichToken liền kề có thể merge (cùng marks + href).
   * Public vì InlineFormatter cần gọi sau applyMark.
   * Dùng RichToken.isMergeable() và RichToken.merge() thay vì spread object thủ công.
   *
   * @param {RichToken[]} tokens
   * @returns {RichToken[]}
   */
  static mergeAdjacentTokens(tokens) {
    if (tokens.length === 0) return [];

    const merged = [];
    let prev = tokens[0].clone(); // clone để tránh mutate input

    for (let i = 1; i < tokens.length; i++) {
      const cur = tokens[i];
      if (prev.isMergeable(cur)) {
        prev = prev.merge(cur);
      } else {
        merged.push(prev);
        prev = cur.clone();
      }
    }

    merged.push(prev);
    return merged;
  }

  /** @param {string} s @returns {string} */
  static #escapeHtml(s) {
    return s
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  /** @param {string} s @returns {string} */
  static #escapeAttr(s) {
    return s
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }
}