import { ContextAnalyzer } from './context_analyzer.js';

export class ContextStore {
  /** @type {EditorEventBus} */
  #bus;
  /** @type {HTMLElement} - #be-block-list */
  #canvas;
  /** @type {AbortController} - dùng để unmount tất cả listener một lần */
  #ac = new AbortController();

  /** @type {ContextType} */
  #type = 'none';

  /** @type {CursorState} */
  #cursor = {
    activeMarks: new Set(), // Trạng thái thực tế tại caret
    blockId: null,
    blockType: null,
    charOffset: null,
    line: null,
  };

  /** @type {SelectionState} */
  #selection = {
    blockId: null,
    start: null,
    end: null,
    range: null,
  };

  /**
   * blockId của block đang active (focused).
   * Khác #cursor.blockId ở chỗ: activeBlockId tồn tại ngay cả khi
   * user click ra ngoài editable (vd: click vào settings panel).
   * @type {string|null}
   */
  #activeBlockId = null;

  /**
   * Deprecated compatibility field. Kept empty so context snapshots preserve shape.
   * @type {Set<string>}
   */
  #capabilities = new Set();

  constructor(bus, canvas, options = {}) {
    this.#bus = bus;
    this.#canvas = canvas;

    this.mount();
  }

  mount() {
    const { signal } = this.#ac;

    // Lắng nghe selection thay đổi từ InlineToolbar
    this.#bus.subscribe('inline:selection_changed', ({ blockId, range }) => {
      this.#syncFromInlineSelection(blockId, range);
    });

    // Block được focus/activate
    this.#bus.subscribe('block:selected', ({ blockId }) => {
      this.#onBlockActivated(blockId);
    });

    this.#bus.subscribe('block:removed', ({ blockId }) => {
      if (this.#activeBlockId === blockId) this.#resetToNone();
    });

    document.addEventListener('selectionchange', () => {
      this.#onSelectionChange();
    }, { signal });
  }

  /**
   * Hủy tất cả listeners, giải phóng references.
   * Gọi khi editor bị unmount khỏi DOM.
   */
  destroy() {
    this.#ac.abort();
    this.#selection.range = null;
  }

  /** @returns {ContextType} */
  get type() { return this.#type; }

  /** @returns {Readonly<CursorState>} */
  get cursor() { return Object.freeze({ ...this.#cursor }); }

  /** @returns {Readonly<SelectionState>} */
  get selection() {
    // Range không được freeze nhưng clone để tránh mutate
    return Object.freeze({
      ...this.#selection,
      range: this.#selection.range?.cloneRange() ?? null,
    });
  }

  /** @returns {string|null} */
  get activeBlockId() { return this.#activeBlockId; }

  /** @returns {Set<string>} - bản copy, không phải reference gốc */
  get capabilities() { return new Set(this.#capabilities); }

  /**
   * Force sync context từ bên ngoài (vd: Manager gọi sau khi block được focus
   * bằng programmatic focus, không qua selection event).
   *
   * @param {Selection|null}   selection
   * @param {HTMLElement|null} target
   */
  sync(selection = null, target = null) {
    const raw = ContextAnalyzer.analyze(
      this.#canvas,
      selection ?? window.getSelection(),
      target
    );
    this.#applyRawContext(raw);
  }

  // Tất cả mutation state đều đi qua đây để đảm bảo nhất quán.

  /**
   * Apply raw context từ ContextAnalyzer vào state.
   * Đây là hàm mutation trung tâm.
   * @param {RawContext} raw
   */
  #applyRawContext(raw) {
    this.#type = raw.type;

    if (raw.type === 'cursor') {
      this.#cursor = {
        ...this.#cursor,
        blockId: raw.blockId,
        blockType: raw.blockType,
        charOffset: raw.cursorOffset,
        line: raw.lineHint,
        activeMarks: raw.activeMarks,
      };

      if (raw.blockId) this.#activeBlockId = raw.blockId;
      this.#clearSelection();

    } else if (raw.type === 'text') {
      this.#cursor = {
        blockId: raw.blockId,
        blockType: raw.blockType,
        charOffset: null,
        line: null,
      };
      if (raw.blockId) this.#activeBlockId = raw.blockId;
      // Ghi selection - dùng InlineFormatter.getRangeOffsets() từ Manager
      // ContextStore chỉ lưu range ref ở đây; offsets tính bởi Manager
      this.#selection = {
        blockId: raw.blockId,
        start: null, // Manager sẽ fill thông qua recordSelectionOffsets()
        end: null,
        range: raw.range,
      };

    } else if (raw.type === 'block') {
      this.#cursor = {
        blockId: raw.blockId,
        blockType: raw.blockType,
        charOffset: null,
        line: null,
      };
      if (raw.blockId) this.#activeBlockId = raw.blockId;
      this.#clearSelection();

    }
    else {
      // none
      this.#clearSelection();
    }

    // Emit state thay đổi
    this.#bus.dispatch('context:changed', this.#buildPublicSnapshot());
  }

  /**
   * Cập nhật selection offsets sau khi Manager tính xong.
   * Được gọi từ #syncFromInlineSelection khi đã có offsets.
   *
   * @param {number} start
   * @param {number} end
   */
  #applySelectionOffsets(start, end) {
    this.#selection = { ...this.#selection, start, end };
  }

  #clearSelection() {
    this.#selection = { blockId: null, start: null, end: null, range: null };
  }

  #resetToNone() {
    this.#type = 'none';
    this.#cursor = { blockId: null, blockType: null, charOffset: null, line: null };
    this.#clearSelection();
    this.#bus.dispatch('context:changed', this.#buildPublicSnapshot());
  }

  #onSelectionChange() {
    const sel = window.getSelection();
    if (!sel || !this.#canvas) return;

    // Chỉ xử lý nếu selection nằm trong canvas
    if (sel.rangeCount === 0) return;
    const range = sel.getRangeAt(0);
    if (!this.#canvas.contains(range.commonAncestorContainer)) return;

    const raw = ContextAnalyzer.analyze(this.#canvas, sel, null);
    this.#applyRawContext(raw);
  }

  /**
   * Sync khi InlineToolbar emit 'inline:selection_changed'.
   * Tại điểm này Manager đã tính offsets - store nhận lại qua event này.
   *
   * @param {string} blockId
   * @param {Range}  range
   */
  #syncFromInlineSelection(blockId, range) {
    // Tính offsets ở đây để store tự đủ, không phụ thuộc Manager tính rồi gửi lại
    const editable = this.#canvas.querySelector(
      `[data-be-block-id="${blockId}"] [contenteditable="true"]`
    ) ?? this.#canvas.querySelector(`[data-be-block-id="${blockId}"]`);

    if (!editable) return;

    // Import InlineFormatter bị circular nếu context_store import từ inline_formatter
    // Giải pháp: dùng cùng thuật toán DFS nhỏ inline tại đây
    const start = this.#charOffsetAt(range.startContainer, range.startOffset, editable);
    const end = this.#charOffsetAt(range.endContainer, range.endOffset, editable);

    if (start !== null && end !== null) {
      this.#applySelectionOffsets(start, end);
    }
  }

  /**
   * Tính char offset - nhỏ gọn, tự chứa, không import InlineFormatter
   * để tránh circular dependency.
   */
  #charOffsetAt(targetNode, targetOffset, container) {
    let count = 0;
    function walk(node) {
      if (node === targetNode) {
        if (node.nodeType === Node.TEXT_NODE) return count + targetOffset;
        let c = 0;
        for (let i = 0; i < targetOffset; i++) c += node.childNodes[i]?.textContent?.length ?? 0;
        return count + c;
      }
      if (node.nodeType === Node.TEXT_NODE) { count += node.textContent.length; return null; }
      for (const child of node.childNodes) {
        const r = walk(child);
        if (r !== null) return r;
      }
      return null;
    }
    return walk(container);
  }

  #onBlockActivated(blockId) {
    this.#activeBlockId = blockId;
    this.#bus.dispatch('context:changed', this.#buildPublicSnapshot());
  }

  /**
   * Tạo snapshot public để gắn vào event payload.
   * Frozen object - caller không thể mutate.
   * @returns {object}
   */
  #buildPublicSnapshot() {
    return Object.freeze({
      type: this.#type,
      cursor: Object.freeze({ ...this.#cursor }),
      selection: Object.freeze({
        blockId: this.#selection.blockId,
        start: this.#selection.start,
        end: this.#selection.end,
      }),
      activeBlockId: this.#activeBlockId,
      capabilities: new Set(this.#capabilities),
    });
  }
}
