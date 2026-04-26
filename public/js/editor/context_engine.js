export class ContextEngine {
  /**
   * Phân tích ngữ cảnh từ selection hoặc DOM target.
   * @param {HTMLElement} canvas - #be-block-list
   * @param {Selection|null} selection - window.getSelection()
   * @param {HTMLElement|null} target - Element từ click event (cho block context)
   * @returns {{ type: string, blockId: string|null, blockType: string|null, range: Range|null, capabilities: string[] }}
   */
  static analyze(canvas, selection, target = null) {
    // Ưu tiên kiểm tra block context từ target (click vào handle/wrapper)
    if (target && (!selection || selection.isCollapsed)) {
      const blockEl = target.closest('[data-be-block-id]');
      if (blockEl && canvas.contains(blockEl)) {
        return this.#buildBlockContext(blockEl);
      }
    }

    // Xử lý text/cursor context
    if (!selection || selection.rangeCount === 0) {
      return this.#emptyContext();
    }

    const range = selection.getRangeAt(0);
    if (!canvas.contains(range.commonAncestorContainer)) {
      return this.#emptyContext();
    }

    const isCollapsed = selection.isCollapsed;
    const blockEl = this.#findContainingBlock(range, canvas);
    if (!blockEl) return this.#emptyContext();

    const blockId = blockEl.dataset.beBlockId;
    const blockType = blockEl.dataset.beBlockType || 'text';
    const isTextSelection = !isCollapsed && this.#isTextRange(range, blockEl);

    const type = isTextSelection ? 'text' : (isCollapsed ? 'cursor' : 'block');
    const capabilities = this.#resolveCapabilities(type, blockType, isTextSelection);

    return {
      type,
      blockId,
      blockType,
      range: isTextSelection ? range.cloneRange() : null,
      capabilities
    };
  }

  static #findContainingBlock(range, canvas) {
    const toEl = n => n.nodeType === Node.TEXT_NODE ? n.parentElement : n;
    return toEl(range.commonAncestorContainer)?.closest('[data-be-block-id]');
  }

  static #isTextRange(range, blockEl) {
    const toEl = n => n.nodeType === Node.TEXT_NODE ? n.parentElement : n;
    const startBlock = toEl(range.startContainer)?.closest('[data-be-block-id]');
    const endBlock = toEl(range.endContainer)?.closest('[data-be-block-id]');
    if (startBlock !== endBlock || startBlock !== blockEl) return false;

    const ancestor = toEl(range.commonAncestorContainer);
    const forbidden = ancestor.querySelectorAll('[contenteditable="false"], img, input, button, select, textarea');
    for (const el of forbidden) {
      if (range.intersectsNode(el)) return false;
    }
    return true;
  }

  static #buildBlockContext(blockEl) {
    return {
      type: 'block',
      blockId: blockEl.dataset.beBlockId,
      blockType: blockEl.dataset.beBlockType || 'unknown',
      range: null,
      capabilities: this.#resolveCapabilities('block', blockEl.dataset.beBlockType, false)
    };
  }

  static #resolveCapabilities(contextType, blockType, isText) {
    const caps = [];
    if (contextType === 'text' && isText) {
      caps.push('bold', 'italic', 'underline', 'link');
    }
    if (contextType === 'block' || contextType === 'cursor') {
      caps.push('delete', 'duplicate', 'move-up', 'move-down');
    }
    // Tương lai: tích hợp BLOCK_REGISTRY.get(blockType)?.schema?.toolbarCommands
    return caps;
  }

  static #emptyContext() {
    return { type: 'none', blockId: null, blockType: null, range: null, capabilities: [] };
  }
}