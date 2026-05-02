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
        // Sanitize marks — chỉ giữ marks trong whitelist
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
   * @param {object[]|RichSegment[]} segments — plain objects từ JSON.parse
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
    const content = blockData?.data?.content;
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
   * Delegate việc serialize data xuống block.serializeData() —
   * mỗi block tự biết cách đọc từ DOM và đóng gói data của mình.
   *
   * @param {import('./editor_block.js').EditorBlock} block
   * @param {HTMLElement|null} editableEl — DOM element chứa nội dung editable
   * @returns {{ id: string, type: string, version: number, data: object }}
   */
  static toPayload(block, editableEl) {
    return {
      id: block.id,
      type: block.type,
      version: block.schema?.version ?? 1,
      data: block.serializeData(editableEl),
    };
  }

  /**
   * Sanitize href — reject các URL không hợp lệ để tránh javascript: injection.
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