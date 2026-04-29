import { ContextAnalyzer } from './context_analyzer.js';
import { CapabilityResolver } from './capability_resolver.js';
import { SnapshotRing } from './snapshot_ring.js';

export class ContextStore {
  /** @type {EditorEventBus} */
  #bus;
  /** @type {HTMLElement} — #be-block-list */
  #canvas;
  /** @type {AbortController} — dùng để unmount tất cả listener một lần */
  #ac = new AbortController();

  /** @type {ContextType} */
  #type = 'none';

  /** @type {CursorState} */
  #cursor = {
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
   * Schema của block đang active — cần để CapabilityResolver tính đúng.
   * @type {object|null}
   */
  #activeBlockSchema = null;

  /**
   * Capabilities của context hiện tại (base + block-specific).
   * Được recompute mỗi khi type hoặc activeBlockSchema thay đổi.
   * @type {Set<string>}
   */
  #capabilities = new Set();

  /** @type {SnapshotRing<Delta>} */
  #history;

  /**
   * Lưu cursor state ngay trước khi record delta.
   * Được set bởi #capturePreCursor() khi Manager biết action sắp xảy ra.
   * @type {CursorState|null}
   */
  #pendingPreCursor = null;

  /**
   * Map blockType → schema, được inject từ ngoài (thường là BLOCK_REGISTRY).
   * ContextStore không import BLOCK_REGISTRY trực tiếp để tránh circular dep.
   * @type {Map<string, object>}
   */
  #schemaRegistry;

  constructor(bus, canvas, options = {}) {
    const { maxSnapshots = 100, schemaRegistry = new Map() } = options;

    this.#bus = bus;
    this.#canvas = canvas;
    this.#history = new SnapshotRing(maxSnapshots);
    this.#schemaRegistry = schemaRegistry;

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

    // Block bị xóa → nếu đang active thì clear
    this.#bus.subscribe('block:removed', ({ blockId }) => {
      if (this.#activeBlockId === blockId) this.#resetToNone();
    });

    // Keyboard: undo/redo — lắng nghe ở document level
    document.addEventListener('keydown', (e) => {
      const ctrl = e.ctrlKey || e.metaKey;
      if (!ctrl) return;

      if (e.key === 'z' && !e.shiftKey) {
        e.preventDefault();
        this.undo();
      } else if ((e.key === 'z' && e.shiftKey) || e.key === 'y') {
        e.preventDefault();
        this.redo();
      }
    }, { signal, capture: true });

    // selectionchange để cập nhật cursor state real-time (không debounce ở đây,
    // vì chỉ update state — render/dispatch vẫn debounce ở InlineToolbar)
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
    this.#history.clear();
    this.#selection.range = null;
    this.#pendingPreCursor = null;
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

  /** @returns {Set<string>} — bản copy, không phải reference gốc */
  get capabilities() { return new Set(this.#capabilities); }

  /** @returns {boolean} */
  get canUndo() { return this.#history.canUndo; }

  /** @returns {boolean} */
  get canRedo() { return this.#history.canRedo; }

  /** @returns {number} — số delta đang lưu trong ring */
  get historySize() { return this.#history.size; }

  beginRecord() {
    this.#pendingPreCursor = { ...this.#cursor };
  }

  /**
   * Ghi một delta vào history ring.
   *
   * Delta chỉ lưu sự thay đổi (prev/next), không lưu toàn bộ document.
   * Cursor state được đính kèm để undo/redo có thể restore vị trí con trỏ.
   *
   * @param {object}  opts
   * @param {string}  opts.blockId
   * @param {string}  opts.attribute   — tên field (vd: 'content', 'level', 'src')
   * @param {*}       opts.prev        — giá trị trước
   * @param {*}       opts.next        — giá trị sau
   * @param {string}  [opts.label]     — mô tả ngắn để debug
   */
  recordDelta({ blockId, attribute, prev, next, label = '' }) {
    // Skip nếu không có thay đổi thực sự
    if (prev === next) return;

    /** @type {Delta} */
    const delta = {
      blockId,
      attribute,
      prev,
      next,
      cursorBefore: this.#pendingPreCursor ?? { ...this.#cursor },
      cursorAfter: { ...this.#cursor },
      timestamp: Date.now(),
      label,
    };

    this.#history.push(delta);
    this.#pendingPreCursor = null;

    // Notify để UI cập nhật undo/redo button state
    this.#emitHistoryState();
  }

  /**
   * Undo: lấy delta gần nhất, emit để Manager apply prev value.
   *
   */
  undo() {
    const delta = this.#history.stepBack();
    if (!delta) return;

    this.#bus.dispatch('context:undo_applied', {
      blockId: delta.blockId,
      attribute: delta.attribute,
      value: delta.prev,          // apply giá trị cũ
      restoreCursor: delta.cursorBefore,
    });

    this.#emitHistoryState();
  }

  /**
   * Redo: tiến đến delta kế tiếp, emit để Manager apply next value.
   */
  redo() {
    const delta = this.#history.stepForward();
    if (!delta) return;

    this.#bus.dispatch('context:redo_applied', {
      blockId: delta.blockId,
      attribute: delta.attribute,
      value: delta.next,          // apply lại giá trị mới
      restoreCursor: delta.cursorAfter,
    });

    this.#emitHistoryState();
  }

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

  /**
   * Đăng ký capability group cho block type mới.
   * Proxy tới CapabilityResolver để caller không cần import CapabilityResolver.
   *
   * @param {string}   supportsKey
   * @param {string[]} capabilities
   */
  registerCapabilityGroup(supportsKey, capabilities) {
    CapabilityResolver.registerSupport(supportsKey, capabilities);
  }

  /**
   * Xóa toàn bộ history (vd: sau khi save thành công).
   */
  clearHistory() {
    this.#history.clear();
    this.#emitHistoryState();
  }

  // Tất cả mutation state đều đi qua đây để đảm bảo nhất quán.

  /**
   * Apply raw context từ ContextAnalyzer vào state.
   * Đây là hàm mutation trung tâm.
   * @param {RawContext} raw
   */
  #applyRawContext(raw) {
    const prevType = this.#type;
    const prevBlockId = this.#cursor.blockId;

    this.#type = raw.type;

    if (raw.type === 'cursor') {
      this.#cursor = {
        blockId: raw.blockId,
        blockType: raw.blockType,
        charOffset: raw.cursorOffset,
        line: raw.lineHint,
      };
      // Cursor đang trong block → cập nhật activeBlockId
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
      // Ghi selection — dùng InlineFormatter.getRangeOffsets() từ Manager
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

    } else {
      // none
      this.#clearSelection();
    }

    // Recompute capabilities nếu type hoặc block thay đổi
    if (prevType !== this.#type || prevBlockId !== this.#cursor.blockId) {
      this.#recomputeCapabilities();
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
    this.#recomputeCapabilities();
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
   * Tại điểm này Manager đã tính offsets — store nhận lại qua event này.
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
   * Tính char offset — nhỏ gọn, tự chứa, không import InlineFormatter
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
    const blockType = this.#canvas
      .querySelector(`[data-be-block-id="${blockId}"]`)
      ?.dataset.beBlockType ?? null;

    this.#activeBlockSchema = this.#schemaRegistry.get(blockType) ?? null;
    this.#recomputeCapabilities();

    this.#bus.dispatch('context:changed', this.#buildPublicSnapshot());
  }

  #recomputeCapabilities() {
    const isTextSelection = this.#type === 'text';
    this.#capabilities = CapabilityResolver.resolve(
      this.#type,
      this.#activeBlockSchema,
      isTextSelection
    );
  }

  #emitHistoryState() {
    this.#bus.dispatch('context:history_changed', {
      canUndo: this.#history.canUndo,
      canRedo: this.#history.canRedo,
      historySize: this.#history.size,
    });
  }

  /**
   * Tạo snapshot public để gắn vào event payload.
   * Frozen object — caller không thể mutate.
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
      canUndo: this.#history.canUndo,
      canRedo: this.#history.canRedo,
    });
  }
}