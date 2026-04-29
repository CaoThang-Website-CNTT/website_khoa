export class BlockSerializer {
  /** @type {string[]} Các định dạng được phép */
  static #ALLOWED_MARKS = ['bold', 'italic', 'underline', 'link'];
  /** @type {RegExp} Kiểm tra URL an toàn */
  static #SAFE_HREF = /^(https?:\/\/|mailto:|\/)[^\s]*$/i;

  /**
   * Chuyển đổi token định dạng sang mảng đoạn văn bản an toàn cho DB.
   * @param {Array<{text: string, marks: Set, href?: string}>} tokens
   * @returns {Array}
   */
  static tokensToRichText(tokens) {
    if (!Array.isArray(tokens) || tokens.length === 0) return [];

    return tokens
      .filter(t => typeof t.text === 'string' && t.text.length > 0)
      .map(token => {
        const marks = BlockSerializer.#sanitizeMarks(token.marks);
        const isLink = marks.includes('link');

        const segment = { type: isLink ? 'link' : 'text', text: token.text, marks };

        if (isLink) {
          segment.attrs = {
            href: BlockSerializer.#sanitizeHref(token.href),
            target: '_blank',
            rel: 'noopener noreferrer',
          };
        }

        return segment;
      });
  }

  /**
   * Khôi phục mảng đoạn văn bản từ DB về token định dạng.
   * @param {Array} segments
   * @returns {Array<{text: string, marks: Set, href?: string}>}
   */
  static richTextToTokens(segments) {
    if (!Array.isArray(segments) || segments.length === 0) return [];

    return segments
      .filter(s => typeof s.text === 'string' && s.text.length > 0)
      .map(seg => {
        const marks = new Set(BlockSerializer.#sanitizeMarks(seg.marks));
        const token = { text: seg.text, marks };

        if (seg.type === 'link' && seg.attrs?.href) {
          token.href = BlockSerializer.#sanitizeHref(seg.attrs.href);
        }

        return token;
      });
  }

  /**
   * Tạo payload cấu trúc cho block, ưu tiên đọc từ DOM trực tiếp.
   * @param {import('./editor_block.js').EditorBlock} block
   * @param {HTMLElement|null} editableEl
   * @returns {{ id: string, type: string, version: number, data: object }}
   */
  static toPayload(block, editableEl) {
    const data = BlockSerializer.#cloneData(block.data);

    if ('content' in data) {
      const html = editableEl?.innerHTML?.trim() ?? '';
      if (html) {
        const tokens = BlockSerializer.parseHTML(html);
        data.content = BlockSerializer.tokensToRichText(tokens);
      } else {
        data.content = [];
      }
    }

    return {
      id: block.id,
      type: block.type,
      version: block.schema?.version ?? 1,
      data,
    };
  }

  /**
  * Chuyển dữ liệu block về HTML để hiển thị. Hỗ trợ cả định dạng mới (mảng) và cũ (chuỗi).
  * @param {{ data: { content?: any } }} blockData
  * @returns {string}
  */
  static toHTML(blockData) {
    const content = blockData?.data?.content;
    if (!content) return '';

    if (typeof content === 'string') {
      return BlockSerializer.#escapeHtml(content);
    }

    if (Array.isArray(content)) {
      const tokens = BlockSerializer.richTextToTokens(content);
      return BlockSerializer.#serializeTokensToHTML(tokens);
    }

    return '';
  }

  /**
   * Phân tích HTML thành danh sách token định dạng.
   * @param {string} html
   * @returns {Array<{text: string, marks: Set, href?: string}>}
   */
  static parseHTML(html) {
    const container = document.createElement('div');
    container.innerHTML = html;

    const TAG_TO_MARK = {
      STRONG: 'bold', B: 'bold',
      EM: 'italic', I: 'italic',
      U: 'underline',
      A: 'link',
    };

    const tokens = [];

    function walk(node, inheritedMarks, inheritedHref) {
      if (node.nodeType === Node.TEXT_NODE) {
        const text = node.textContent;
        if (!text.length) return;
        tokens.push({
          text,
          marks: new Set(inheritedMarks),
          ...(inheritedHref ? { href: inheritedHref } : {}),
        });
        return;
      }

      if (node.nodeType !== Node.ELEMENT_NODE) return;

      const tag = node.tagName;
      if (tag === 'BR') { tokens.push({ text: '\n', marks: new Set() }); return; }

      const mark = TAG_TO_MARK[tag];
      const currentMarks = new Set(inheritedMarks);
      let currentHref = inheritedHref;

      if (mark) {
        currentMarks.add(mark);
        if (mark === 'link') currentHref = node.getAttribute('href') ?? '';
      }

      for (const child of node.childNodes) walk(child, currentMarks, currentHref);
    }

    walk(container, new Set(), null);
    return BlockSerializer.#mergeAdjacentTokens(tokens);
  }

  /**
   * Gộp các token kề nhau có cùng định dạng và href.
   * @param {Array<{text: string, marks: Set, href?: string}>} tokens
   * @returns {Array<{text: string, marks: Set, href?: string}>}
   */
  static #mergeAdjacentTokens(tokens) {
    if (!tokens.length) return [];
    const merged = [];
    let prev = { ...tokens[0], marks: new Set(tokens[0].marks) };

    for (let i = 1; i < tokens.length; i++) {
      const cur = tokens[i];
      if (BlockSerializer.#markSetsEqual(prev.marks, cur.marks) && prev.href === cur.href) {
        prev.text += cur.text;
      } else {
        merged.push(prev);
        prev = { ...cur, marks: new Set(cur.marks) };
      }
    }
    merged.push(prev);
    return merged;
  }

  /**
   * Chuyển danh sách token thành chuỗi HTML để render.
   * @param {Array<{text: string, marks: Set, href?: string}>} tokens
   * @returns {string}
   */
  static #serializeTokensToHTML(tokens) {
    const ORDER = ['link', 'bold', 'italic', 'underline'].reverse();

    return tokens.map(token => {
      let content = BlockSerializer.#escapeHtml(token.text);

      for (const mark of ORDER) {
        if (!token.marks.has(mark)) continue;
        switch (mark) {
          case 'bold': content = `<strong>${content}</strong>`; break;
          case 'italic': content = `<em>${content}</em>`; break;
          case 'underline': content = `<u>${content}</u>`; break;
          case 'link': {
            const href = BlockSerializer.#escapeAttr(token.href ?? '');
            content = `<a href="${href}" target="_blank" rel="noopener noreferrer">${content}</a>`;
            break;
          }
        }
      }
      return content;
    }).join('');
  }

  /**
   * Lọc bỏ các định dạng không nằm trong danh sách cho phép.
   * @param {Set|string[]|null|undefined} marks
   * @returns {string[]}
   */
  static #sanitizeMarks(marks) {
    if (!marks) return [];
    const list = marks instanceof Set ? [...marks] : Array.isArray(marks) ? marks : [];
    return list.filter(m => BlockSerializer.#ALLOWED_MARKS.includes(m));
  }

  /**
   * Chuẩn hóa và kiểm tra tính hợp lệ của URL (chỉ chấp nhận http/https/mailto).
   * @param {string|null|undefined} href
   * @returns {string}
   */
  static #sanitizeHref(href) {
    if (typeof href !== 'string') return '';
    const t = href.trim();
    return BlockSerializer.#SAFE_HREF.test(t) ? t : '';
  }

  /**
   * Sao chép dữ liệu block để tránh thay đổi tham chiếu gốc.
   * @param {object} data
   * @returns {object}
   */
  static #cloneData(data) {
    try { return JSON.parse(JSON.stringify(data)); } catch { return { ...data }; }
  }

  /**
   * So sánh hai tập hợp định dạng có chứa cùng các phần tử không.
   * @param {Set} a
   * @param {Set} b
   * @returns {boolean}
   */
  static #markSetsEqual(a, b) {
    if (a.size !== b.size) return false;
    for (const m of a) if (!b.has(m)) return false;
    return true;
  }

  /**
   * Escape ký tự đặc biệt trong nội dung HTML.
   * @param {string} s
   * @returns {string}
   */
  static #escapeHtml(s) {
    return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  /**
   * Escape ký tự đặc biệt trong giá trị thuộc tính HTML.
   * @param {string} s
   * @returns {string}
   */
  static #escapeAttr(s) {
    return s.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }
}