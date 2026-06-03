import { RichSegment } from './rich_text_token.js';
import { RichTextParser } from './rich_text_parser.js';

export class BlockSerializer {

  /** @type {RegExp} Chỉ chấp nhận http/https/mailto/relative URL */
  static #SAFE_HREF = /^(https?:\/\/|mailto:|\/)[^\s]*$/i;

  /** @type {string[]} Marks được phép lưu DB */
  static #ALLOWED_MARKS = ['bold', 'italic', 'underline', 'link'];

  /**
   * RichToken[] → RichSegment[] để lưu DB.
   * Filter token rỗng, sanitize marks + href trước khi lưu.
   *
   * @param {import('./rich_text_token.js').RichToken[]} tokens
   * @returns {RichSegment[]}
   */
  static tokensToSegments(tokens) {
    if (!Array.isArray(tokens) || tokens.length === 0) return [];

    return tokens
      .filter(t => typeof t.text === 'string' && t.text.length > 0)
      .map(token => {
        // Sanitize marks - chỉ giữ marks trong whitelist
        const safeMarks = [...token.marks].filter(m =>
          BlockSerializer.#ALLOWED_MARKS.includes(m)
        );
        const isLink = safeMarks.includes('link');

        return new RichSegment(
          isLink ? 'link' : 'text',
          token.text,
          safeMarks,
          isLink ? BlockSerializer.#sanitizeHref(token.href) : undefined,
        );
      });
  }

  /**
   * RichSegment[] (plain object từ DB) → RichToken[] để dùng trong editor.
   * Xử lý cả v1 schema (attrs.href) lẫn v2 schema (flat href).
   *
   * @param {object[]|RichSegment[]} segments - plain objects từ JSON.parse
   * @returns {import('./rich_text_token.js').RichToken[]}
   */
  static segmentsToTokens(segments) {
    if (!Array.isArray(segments) || segments.length === 0) return [];

    return segments
      .filter(s => typeof s.text === 'string' && s.text.length > 0)
      .map(seg => RichSegment.toToken(seg)); // toToken() tự handle v1/v2
  }

  // ─── HTML helpers ─────────────────────────────────────────────────────────

  /**
   * Chuyển data block (content field) → HTML để inject vào contenteditable.
   * Hỗ trợ cả legacy string format lẫn RichSegment[] format.
   *
   * @param {{ data: { content?: string | object[] } }} blockData
   * @returns {string}
   */
  static toHTML(blockData) {
    const data = blockData?.data;
    const content = data?.rich_text ?? data?.content;
    if (!content) return '';

    // Legacy: content là plain string (block chưa qua serializer)
    if (typeof content === 'string') {
      return BlockSerializer.#escapeHtml(content);
    }

    if (Array.isArray(content)) {
      const tokens = BlockSerializer.segmentsToTokens(content);
      return RichTextParser.serialize(tokens);
    }

    return '';
  }

  /**
   * Build payload hoàn chỉnh cho một block để gửi server.
   * Delegate việc serialize data xuống block.serializeData() -
   * mỗi block tự biết cách đọc từ DOM và đóng gói data của mình.
   *
   * @param {import('./editor_block.js').EditorBlock} block
   * @param {HTMLElement|null} editableEl - DOM element chứa nội dung editable
   * @returns {{ id: string, type: string, version: number, data: object }}
   */
  static toPayload(block, editableEl) {
    const data = block.serializeData(editableEl);
    const cleanedData = BlockSerializer.cleanBlockData(data, block.type);

    return {
      id: block.id,
      type: block.type,
      version: block.schema?.version ?? 1,
      data: cleanedData,
    };
  }

  /**
   * Làm sạch dữ liệu của các block phức tạp (VD: List) bằng cách
   * loại bỏ các mục (items) rỗng ở đầu và cuối danh sách.
   *
   * @param {object} data - data của block từ serializeData()
   * @param {string} type - type của block
   * @returns {object} data đã được làm sạch
   */
  static cleanBlockData(data, type) {
    if (type === "blocks/list" && Array.isArray(data.meta?.items)) {
      // Vì serializeData trả về tham chiếu, ta clone để tránh side effect
      const items = JSON.parse(JSON.stringify(data.meta.items));
      data.meta.items = BlockSerializer.#trimListItems(items);
    }
    return data;
  }

  /**
   * Đệ quy làm sạch mảng items của danh sách (trim đầu cuối).
   * Dùng cơ chế đệ quy để dọn dẹp các dòng con trống trước.
   */
  static #trimListItems(items) {
    if (!Array.isArray(items)) return [];

    // 1. Làm sạch các dòng con rỗng trước (Bottom-up)
    items.forEach((item) => {
      if (Array.isArray(item.children) && item.children.length > 0) {
        item.children = BlockSerializer.#trimListItems(item.children);
      }
    });

    // 2. Cắt bỏ các dòng trống ở đầu và cuối mảng hiện tại
    let start = 0;
    while (start < items.length && BlockSerializer.#isItemEmpty(items[start])) {
      start++;
    }

    let end = items.length - 1;
    while (end >= start && BlockSerializer.#isItemEmpty(items[end])) {
      end--;
    }

    return start <= end ? items.slice(start, end + 1) : [];
  }

  /** Kiểm tra 1 item danh sách có rỗng hay không */
  static #isItemEmpty(item) {
    const hasText = Array.isArray(item.rich_text) && item.rich_text.length > 0;
    const hasChildren = Array.isArray(item.children) && item.children.length > 0;
    return !hasText && !hasChildren;
  }

  /**
   * Kiểm tra xem một block payload có được coi là "trống" hay không.
   * Dùng để trim các block thừa ở đầu/cuối bài viết.
   *
   * @param {object} payload - kết quả từ toPayload()
   * @returns {boolean}
   */
  static isBlockEmpty(payload) {
    const { type, data } = payload;

    // Kiểm tra văn bản (Paragraph, Heading, Quote...)
    if (Array.isArray(data.rich_text)) {
      if (data.rich_text.length > 0) return false;

      // Nếu là List, kiểm tra sâu vào meta.items
      if (type === 'blocks/list') {
        const items = data.meta?.items || [];
        const checkItems = (list) => {
          return list.every(item => {
            const hasText = Array.isArray(item.rich_text) && item.rich_text.length > 0;
            const hasChildren = Array.isArray(item.children) && item.children.length > 0 && !checkItems(item.children);
            return !hasText && !hasChildren;
          });
        };
        return items.length === 0 || checkItems(items);
      }

      return true;
    }

    // Kiểm tra Image
    if (type === 'blocks/image') {
      return !data.meta?.url;
    }

    // Mặc định các block khác (Table, v.v.) không tự động xóa để tránh mất data ngoài ý muốn
    return false;
  }

  /**
   * Sanitize href - reject các URL không hợp lệ để tránh javascript: injection.
   * @param {string|null|undefined} href
   * @returns {string}
   */
  static #sanitizeHref(href) {
    if (typeof href !== 'string') return '';
    const t = href.trim();
    return BlockSerializer.#SAFE_HREF.test(t) ? t : '';
  }

  /** @param {string} s @returns {string} */
  static #escapeHtml(s) {
    return (s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }
}