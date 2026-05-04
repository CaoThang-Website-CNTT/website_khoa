import { RichToken } from './rich_text_token.js';
import { RichTextParser } from './rich_text_parser.js';

export class InlineFormatter {
  /**
   * Toggle mark trên vùng [selStart, selEnd) của chuỗi token.
   *
   * @param {RichToken[]} tokens
   * @param {number}      selStart
   * @param {number}      selEnd
   * @param {string}      markType
   * @param {string}      [href]
   * @returns {RichToken[]}
   */
  static applyMark(tokens, selStart, selEnd, markType, href = '') {
    if (selStart >= selEnd) return tokens;

    const shouldRemove = InlineFormatter.#allHaveMark(tokens, selStart, selEnd, markType);

    return InlineFormatter.#transformRange(tokens, selStart, selEnd, (token) => {
      const newMarks = new Set(token.marks);
      if (shouldRemove) {
        newMarks.delete(markType);
      } else {
        newMarks.add(markType);
      }

      const newHref = markType === 'link'
        ? (shouldRemove ? undefined : href || undefined)
        : token.href;

      return new RichToken(token.text, newMarks, newHref);
    });
  }

  /**
   * Force remove link mark.
   *
   * @param {RichToken[]} tokens
   * @param {number}      selStart
   * @param {number}      selEnd
   * @returns {RichToken[]}
   */
  static removeLink(tokens, selStart, selEnd) {
    return InlineFormatter.#transformRange(tokens, selStart, selEnd, (token) => {
      const newMarks = new Set(token.marks);
      newMarks.delete('link');
      return new RichToken(token.text, newMarks, undefined);
    });
  }

  /**
   * Cốt lõi của việc xử lý range: Chia tách tokens tại điểm start/end 
   * và áp dụng một callback biến đổi trên phân đoạn nằm trong selection.
   *
   * @param {RichToken[]} tokens
   * @param {number}      selStart
   * @param {number}      selEnd
   * @param {Function}    transformFn — (token) => token
   * @returns {RichToken[]}
   */
  static #transformRange(tokens, selStart, selEnd, transformFn) {
    const result = [];
    let charOffset = 0;

    for (const token of tokens) {
      const tokenStart = charOffset;
      const tokenEnd = charOffset + token.text.length;
      charOffset = tokenEnd;

      if (tokenEnd <= selStart || tokenStart >= selEnd) {
        result.push(token.clone());
        continue;
      }

      if (tokenStart < selStart) {
        result.push(token.clone({ text: token.text.slice(0, selStart - tokenStart) }));
      }

      const sliceStart = Math.max(selStart, tokenStart) - tokenStart;
      const sliceEnd = Math.min(selEnd, tokenEnd) - tokenStart;

      result.push(transformFn(token.clone({
        text: token.text.slice(sliceStart, sliceEnd)
      })));

      if (tokenEnd > selEnd) {
        result.push(token.clone({ text: token.text.slice(sliceEnd) }));
      }
    }

    return RichTextParser.mergeAdjacentTokens(result);
  }

  // Alias methods to keep EditorManager compatible
  static parse(html) { return RichTextParser.parse(html); }
  static serialize(tokens) { return RichTextParser.serialize(tokens); }

  // ─── Query ────────────────────────────────────────────────────────────────

  /**
   * Trả về Set<string> các mark mà TẤT CẢ ký tự trong [selStart, selEnd) đều có.
   * Dùng để sync trạng thái active của toolbar buttons.
   *
   * @param {RichToken[]} tokens
   * @param {number}      selStart
   * @param {number}      selEnd
   * @returns {Set<string>}
   */
  static getActiveMarks(tokens, selStart, selEnd) {
    if (selStart >= selEnd) return new Set();

    /** @type {Set<string>|null} */
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
        for (const m of intersection) {
          if (!token.marks.has(m)) intersection.delete(m);
        }
      }

      if (intersection.size === 0) break; // early exit
    }

    return intersection ?? new Set();
  }

  /**
   * Tính character offsets [start, end) từ browser Range.
   * Điểm DUY NHẤT InlineFormatter chạm Selection API — chỉ đọc, không write.
   *
   * @param {Range}       range
   * @param {HTMLElement} container — contenteditable root
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
   * Tính character offset của điểm (node, offset) tính từ đầu container.
   * Pre-order DFS, cộng dồn textContent.length đến khi gặp đúng target node.
   *
   * @param {Node}        targetNode
   * @param {number}      targetOffset
   * @param {HTMLElement} container
   * @returns {number|null}
   */
  static #getCharOffset(targetNode, targetOffset, container) {
    let charCount = 0;

    function countTo(node) {
      if (node === targetNode) {
        if (node.nodeType === Node.TEXT_NODE) {
          return charCount + targetOffset;
        }
        // Element node: offset là child index
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

  /**
   * Kiểm tra tất cả ký tự trong [selStart, selEnd) đều có markType.
   * Dùng để quyết định add hay remove trong applyMark.
   *
   * @param {RichToken[]} tokens
   * @param {number}      selStart
   * @param {number}      selEnd
   * @param {string}      markType
   * @returns {boolean}
   */
  static #allHaveMark(tokens, selStart, selEnd, markType) {
    let charOffset = 0;
    let covered = 0;
    const targetLen = selEnd - selStart;

    for (const token of tokens) {
      const tokenStart = charOffset;
      const tokenEnd = charOffset + token.text.length;
      charOffset = tokenEnd;

      if (tokenEnd <= selStart || tokenStart >= selEnd) continue;
      if (!token.marks.has(markType)) return false;

      covered += Math.min(selEnd, tokenEnd) - Math.max(selStart, tokenStart);
      if (covered >= targetLen) return true;
    }

    return false;
  }
}