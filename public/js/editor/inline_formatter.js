export class InlineFormatter {
  static MARKS = {
    BOLD: 'bold',
    ITALIC: 'italic',
    UNDERLINE: 'underline',
    LINK: 'link',
  };
  static #TAG_TO_MARK = {
    STRONG: 'bold', B: 'bold',
    EM: 'italic', I: 'italic',
    U: 'underline',
    A: 'link',
  };

  static #SERIALIZE_ORDER = ['link', 'bold', 'italic', 'underline'];

  /**
   * Chuyển HTML string → mảng Mark token phẳng.
   *
   * @param {string} html
   * @returns {Mark[]}
   */
  static parse(html) {
    if (!html || html.trim() === '') return [];

    // Parse vào một <div> tạm để lấy DOM tree
    const container = document.createElement('div');
    container.innerHTML = html;

    /** @type {Mark[]} */
    const tokens = [];

    /**
     * DFS walker.
     * @param {Node} node
     * @param {Set<MarkType>} inheritedMarks — marks kế thừa từ ancestor
     * @param {string|null}   inheritedHref
     */
    function walk(node, inheritedMarks, inheritedHref) {
      if (node.nodeType === Node.TEXT_NODE) {
        const text = node.textContent;
        if (text.length === 0) return;

        tokens.push({
          text,
          marks: new Set(inheritedMarks),
          ...(inheritedHref ? { href: inheritedHref } : {}),
        });
        return;
      }

      if (node.nodeType !== Node.ELEMENT_NODE) return;

      const tag = node.tagName;
      const mark = InlineFormatter.#TAG_TO_MARK[tag];

      // Clone set để không mutate ancestor scope
      const currentMarks = new Set(inheritedMarks);
      let currentHref = inheritedHref;

      if (mark) {
        currentMarks.add(mark);
        if (mark === 'link') {
          currentHref = node.getAttribute('href') ?? '';
        }
      }

      // BR → emit newline token (giữ nguyên line break)
      if (tag === 'BR') {
        tokens.push({ text: '\n', marks: new Set() });
        return;
      }

      for (const child of node.childNodes) {
        walk(child, currentMarks, currentHref);
      }
    }

    walk(container, new Set(), null);

    // Merge các token liền kề có cùng mark set + href để giảm số lượng token
    return InlineFormatter.#mergeAdjacentTokens(tokens);
  }

  /**
   * Merge các token liền kề có mark set và href giống nhau.
   * Giảm số lượng token, quan trọng khi serialize.
   *
   * @param {Mark[]} tokens
   * @returns {Mark[]}
   */
  static #mergeAdjacentTokens(tokens) {
    if (tokens.length === 0) return [];

    /** @type {Mark[]} */
    const merged = [];
    let prev = { ...tokens[0], marks: new Set(tokens[0].marks) };

    for (let i = 1; i < tokens.length; i++) {
      const cur = tokens[i];

      if (
        InlineFormatter.#markSetsEqual(prev.marks, cur.marks) &&
        prev.href === cur.href
      ) {
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
   * Toggle mark trên vùng [selStart, selEnd) của chuỗi ký tự.
   *
   * @param {Mark[]}    tokens
   * @param {number}    selStart  — character offset từ đầu full string
   * @param {number}    selEnd    — character offset (exclusive)
   * @param {MarkType}  markType
   * @param {string}    [href]    — bắt buộc khi markType === 'link'
   * @returns {Mark[]}
   */
  static applyMark(tokens, selStart, selEnd, markType, href = '') {
    if (selStart >= selEnd) return tokens;

    // Bước 1: Xác định add hay remove
    const shouldRemove = InlineFormatter.#allHaveMark(tokens, selStart, selEnd, markType);

    // Bước 2: Chia tách + áp dụng
    /** @type {Mark[]} */
    const result = [];
    let charOffset = 0;

    for (const token of tokens) {
      const tokenStart = charOffset;
      const tokenEnd = charOffset + token.text.length;
      charOffset = tokenEnd;

      // Token hoàn toàn nằm ngoài selection → giữ nguyên
      if (tokenEnd <= selStart || tokenStart >= selEnd) {
        result.push({ ...token, marks: new Set(token.marks) });
        continue;
      }

      // Phần before selection (nếu có)
      if (tokenStart < selStart) {
        result.push({
          text: token.text.slice(0, selStart - tokenStart),
          marks: new Set(token.marks),
          ...(token.href ? { href: token.href } : {}),
        });
      }

      // Phần trong selection
      const sliceStart = Math.max(selStart, tokenStart) - tokenStart;
      const sliceEnd = Math.min(selEnd, tokenEnd) - tokenStart;
      const selectedText = token.text.slice(sliceStart, sliceEnd);

      const newMarks = new Set(token.marks);
      if (shouldRemove) {
        newMarks.delete(markType);
        // Xóa link → xóa luôn href
      } else {
        newMarks.add(markType);
      }

      const selectedToken = {
        text: selectedText,
        marks: newMarks,
      };
      if (markType === 'link' && !shouldRemove && href) {
        selectedToken.href = href;
      } else if (markType !== 'link' && token.href) {
        selectedToken.href = token.href;
      }
      result.push(selectedToken);

      // Phần after selection (nếu có)
      if (tokenEnd > selEnd) {
        result.push({
          text: token.text.slice(sliceEnd),
          marks: new Set(token.marks),
          ...(token.href ? { href: token.href } : {}),
        });
      }
    }

    return InlineFormatter.#mergeAdjacentTokens(result);
  }

  /**
   * Xóa link mark trên vùng [selStart, selEnd).
   * Shorthand của applyMark với remove logic.
   *
   * @param {Mark[]} tokens
   * @param {number} selStart
   * @param {number} selEnd
   * @returns {Mark[]}
   */
  static removeLink(tokens, selStart, selEnd) {
    /** @type {Mark[]} */
    const result = [];
    let charOffset = 0;

    for (const token of tokens) {
      const tokenStart = charOffset;
      const tokenEnd = charOffset + token.text.length;
      charOffset = tokenEnd;

      if (tokenEnd <= selStart || tokenStart >= selEnd) {
        result.push({ ...token, marks: new Set(token.marks) });
        continue;
      }

      if (tokenStart < selStart) {
        result.push({
          text: token.text.slice(0, selStart - tokenStart),
          marks: new Set(token.marks),
          ...(token.href ? { href: token.href } : {}),
        });
      }

      const sliceStart = Math.max(selStart, tokenStart) - tokenStart;
      const sliceEnd = Math.min(selEnd, tokenEnd) - tokenStart;
      const selectedText = token.text.slice(sliceStart, sliceEnd);

      const newMarks = new Set(token.marks);
      newMarks.delete('link');
      result.push({ text: selectedText, marks: newMarks });

      if (tokenEnd > selEnd) {
        result.push({
          text: token.text.slice(sliceEnd),
          marks: new Set(token.marks),
          ...(token.href ? { href: token.href } : {}),
        });
      }
    }

    return InlineFormatter.#mergeAdjacentTokens(result);
  }

  // ─── Serialize ───────────────────────────────────────────────────────────

  /**
   * Chuyển Mark[] → HTML string.
   * @param {Mark[]} tokens
   * @returns {string}
   */
  static serialize(tokens) {
    return tokens.map(token => {
      let content = InlineFormatter.#escapeHtml(token.text);

      // Wrap marks từ trong ra ngoài theo thứ tự đảo ngược của SERIALIZE_ORDER
      const order = [...InlineFormatter.#SERIALIZE_ORDER].reverse();

      for (const mark of order) {
        if (!token.marks.has(mark)) continue;

        switch (mark) {
          case 'bold':
            content = `<strong>${content}</strong>`;
            break;
          case 'italic':
            content = `<em>${content}</em>`;
            break;
          case 'underline':
            content = `<u>${content}</u>`;
            break;
          case 'link': {
            const href = InlineFormatter.#escapeAttr(token.href ?? '');
            content = `<a href="${href}" target="_blank" rel="noopener noreferrer">${content}</a>`;
            break;
          }
        }
      }

      return content;
    }).join('');
  }

  // ─── Query Helpers ───────────────────────────────────────────────────────

  /**
   * Trả về Set<MarkType> của các mark mà TẤT CẢ ký tự trong [selStart, selEnd) đều có.
   * Dùng để cập nhật trạng thái active của các nút toolbar.
   *
   * @param {Mark[]} tokens
   * @param {number} selStart
   * @param {number} selEnd
   * @returns {Set<MarkType>}
   */
  static getActiveMarks(tokens, selStart, selEnd) {
    if (selStart >= selEnd) return new Set();

    /** @type {Set<MarkType>|null} */
    let intersection = null;
    let charOffset = 0;

    for (const token of tokens) {
      const tokenStart = charOffset;
      const tokenEnd = charOffset + token.text.length;
      charOffset = tokenEnd;

      if (tokenEnd <= selStart || tokenStart >= selEnd) continue;

      if (intersection === null) {
        intersection = new Set(token.marks);
      } else {
        // Giữ lại chỉ những mark xuất hiện trong CẢ HAI set
        for (const m of intersection) {
          if (!token.marks.has(m)) intersection.delete(m);
        }
      }

      if (intersection.size === 0) break; // Early exit
    }

    return intersection ?? new Set();
  }

  /**
   * Tính character offset [start, end) từ một Range của browser.
   * Đây là ĐIỂM DUY NHẤT InlineFormatter chạm vào Selection API —
   * chỉ để đọc vị trí, không để write (không execCommand, không insertNode).
   *
   * @param {Range}       range
   * @param {HTMLElement} container — element chứa toàn bộ nội dung (contenteditable root)
   * @returns {{ start: number, end: number }|null}
   */
  static getRangeOffsets(range, container) {
    if (!container.contains(range.commonAncestorContainer)) return null;

    const start = InlineFormatter.#getCharOffset(range.startContainer, range.startOffset, container);
    const end = InlineFormatter.#getCharOffset(range.endContainer, range.endOffset, container);

    if (start === null || end === null) return null;
    return { start, end };
  }

  /**
   * Tính character offset của một điểm (node, offset) trong cây DOM,
   * tính từ đầu container.
   *
   * Thuật toán:
   *   Pre-order DFS, cộng dồn textContent.length của các text node đến khi
   *   gặp đúng node cần tìm.
   *
   * @param {Node}        targetNode
   * @param {number}      targetOffset  — offset trong node (character nếu text node, child index nếu element)
   * @param {HTMLElement} container
   * @returns {number|null}
   */
  static #getCharOffset(targetNode, targetOffset, container) {
    let charCount = 0;

    /**
     * @param {Node} node
     * @returns {number|null} — null nếu chưa tìm thấy, số nếu đã xong
     */
    function countTo(node) {
      if (node === targetNode) {
        // Text node: offset là số ký tự
        if (node.nodeType === Node.TEXT_NODE) {
          return charCount + targetOffset;
        }
        // Element node: offset là child index → cộng thêm text length của các child trước đó
        let c = 0;
        for (let i = 0; i < targetOffset; i++) {
          c += node.childNodes[i]?.textContent?.length ?? 0;
        }
        return charCount + c;
      }

      if (node.nodeType === Node.TEXT_NODE) {
        charCount += node.textContent.length;
        return null;
      }

      for (const child of node.childNodes) {
        const result = countTo(child);
        if (result !== null) return result;
      }

      return null;
    }

    return countTo(container);
  }

  // ─── Internal Utilities ──────────────────────────────────────────────────

  /**
   * Kiểm tra tất cả ký tự trong [selStart, selEnd) đều có markType hay không.
   *
   * @param {Mark[]}   tokens
   * @param {number}   selStart
   * @param {number}   selEnd
   * @param {MarkType} markType
   * @returns {boolean}
   */
  static #allHaveMark(tokens, selStart, selEnd, markType) {
    let charOffset = 0;
    let coveredChars = 0;
    const targetLen = selEnd - selStart;

    for (const token of tokens) {
      const tokenStart = charOffset;
      const tokenEnd = charOffset + token.text.length;
      charOffset = tokenEnd;

      if (tokenEnd <= selStart || tokenStart >= selEnd) continue;

      if (!token.marks.has(markType)) return false;

      const overlapStart = Math.max(selStart, tokenStart);
      const overlapEnd = Math.min(selEnd, tokenEnd);
      coveredChars += overlapEnd - overlapStart;

      if (coveredChars >= targetLen) return true;
    }

    return false;
  }

  /**
   * So sánh hai Set<MarkType> bằng nhau về nội dung.
   *
   * @param {Set<MarkType>} a
   * @param {Set<MarkType>} b
   * @returns {boolean}
   */
  static #markSetsEqual(a, b) {
    if (a.size !== b.size) return false;
    for (const m of a) if (!b.has(m)) return false;
    return true;
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
    return s.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }
}