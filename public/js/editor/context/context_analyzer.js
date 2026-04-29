export class ContextAnalyzer {
  /**
   * BASE capabilities áp dụng theo loại ngữ cảnh, không phụ thuộc block type.
   * Block-specific capabilities đến từ schema.supports (xem CapabilityResolver).
   */
  static BASE_CAPABILITIES = {
    text: ['bold', 'italic', 'underline', 'link'],
    cursor: ['delete', 'duplicate', 'move-up', 'move-down'],
    block: ['delete', 'duplicate', 'move-up', 'move-down'],
    none: [],
  };

  /**
   * Phân tích ngữ cảnh từ DOM và trả về raw context object.
   * Không mutate bất kỳ state nào — toàn bộ kết quả được ContextStore lưu.
   *
   * @param {HTMLElement}    canvas     — #be-block-list
   * @param {Selection|null} selection  — window.getSelection()
   * @param {HTMLElement|null} target   — element từ click event
   * @returns {RawContext}
   */
  static analyze(canvas, selection, target = null) {
    if (target && (!selection || selection.isCollapsed)) {
      const blockEl = target.closest('[data-be-block-id]');
      if (blockEl && canvas.contains(blockEl)) {
        return this.#buildBlockContext(blockEl);
      }
    }

    if (!selection || selection.rangeCount === 0) return this.#emptyContext();

    const range = selection.getRangeAt(0);
    if (!canvas.contains(range.commonAncestorContainer)) return this.#emptyContext();

    const blockEl = this.#findContainingBlock(range, canvas);
    if (!blockEl) return this.#emptyContext();

    const isCollapsed = selection.isCollapsed;
    const isTextSelection = !isCollapsed && this.#isValidTextRange(range, blockEl);
    const type = isTextSelection ? 'text' : (isCollapsed ? 'cursor' : 'block');

    return {
      type,
      blockId: blockEl.dataset.beBlockId,
      blockType: blockEl.dataset.beBlockType || 'unknown',
      // Chỉ clone range khi thực sự có text selection — tránh giữ reference thừa
      range: isTextSelection ? range.cloneRange() : null,
      // Cursor position chỉ có ý nghĩa khi type === 'cursor'
      cursorOffset: isCollapsed ? this.#getCursorOffset(range, blockEl) : null,
      lineHint: isCollapsed ? this.#estimateLine(range, blockEl) : null,
    };
  }

  // ─── Private helpers ──────────────────────────────────────────────────────

  static #findContainingBlock(range, canvas) {
    const toEl = n => n.nodeType === Node.TEXT_NODE ? n.parentElement : n;
    return toEl(range.commonAncestorContainer)?.closest('[data-be-block-id]');
  }

  static #isValidTextRange(range, blockEl) {
    const toEl = n => n.nodeType === Node.TEXT_NODE ? n.parentElement : n;
    const startBlock = toEl(range.startContainer)?.closest('[data-be-block-id]');
    const endBlock = toEl(range.endContainer)?.closest('[data-be-block-id]');
    if (startBlock !== endBlock || startBlock !== blockEl) return false;

    const ancestor = toEl(range.commonAncestorContainer);
    const forbidden = ancestor.querySelectorAll(
      '[contenteditable="false"], img, input, button, select, textarea'
    );
    for (const el of forbidden) {
      if (range.intersectsNode(el)) return false;
    }
    return true;
  }

  /**
   * Tính character offset tuyệt đối của con trỏ trong block editable.
   * Dùng pre-order DFS giống InlineFormatter.getRangeOffsets() để nhất quán.
   *
   * @param {Range}       range
   * @param {HTMLElement} blockEl
   * @returns {number|null}
   */
  static #getCursorOffset(range, blockEl) {
    const editable = blockEl.querySelector('[contenteditable="true"]') ?? blockEl;
    if (!editable) return null;

    let charCount = 0;
    const target = range.startContainer;
    const targetOffset = range.startOffset;

    function walk(node) {
      if (node === target) {
        if (node.nodeType === Node.TEXT_NODE) return charCount + targetOffset;
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
        const result = walk(child);
        if (result !== null) return result;
      }
      return null;
    }

    return walk(editable);
  }

  /**
   * Ước tính số dòng dựa vào getBoundingClientRect().
   * Đây là "best effort" — không parse DOM text. Đủ để label snapshot.
   *
   * @param {Range}       range
   * @param {HTMLElement} blockEl
   * @returns {number} — 1-indexed
   */
  static #estimateLine(range, blockEl) {
    const editable = blockEl.querySelector('[contenteditable="true"]') ?? blockEl;
    if (!editable) return 1;

    const caretRect = range.getBoundingClientRect();
    const editableRect = editable.getBoundingClientRect();
    const lineHeight = parseFloat(getComputedStyle(editable).lineHeight) || 20;

    return Math.max(1, Math.round((caretRect.top - editableRect.top) / lineHeight) + 1);
  }

  static #buildBlockContext(blockEl) {
    return {
      type: 'block',
      blockId: blockEl.dataset.beBlockId,
      blockType: blockEl.dataset.beBlockType || 'unknown',
      range: null,
      cursorOffset: null,
      lineHint: null,
    };
  }

  static #emptyContext() {
    return {
      type: 'none',
      blockId: null,
      blockType: null,
      range: null,
      cursorOffset: null,
      lineHint: null,
    };
  }
}