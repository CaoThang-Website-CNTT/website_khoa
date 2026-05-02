import { registry as BLOCK_REGISTRY } from './block_registry.js';
import { BlockSerializer } from './block_serializer_v2.js';
import { EditorBlock } from './blocks/editor_block.js';
import { EditorListView } from './editor_list_view.js';
import { ContextStore } from './context/context_store.js';
import { InlineToolbar } from './toolbar/inline_toolbar.js';
import { BlockToolbar } from './toolbar/block_toolbar.js';
import { InlineFormatter } from './inline_formatter_v2.js';
import { EditorUI } from './editor_ui.js';

/** Đảm nhận việc truyền tin */
class EditorEventBus {
  #listeners = new Map();

  // ===========================
  // Custom Event Listener
  // ===========================
  /**
   * 
   * @param {string} event 
   * @param {Function} fn 
   */
  subscribe(event, fn) {
    if (!this.#listeners.has(event)) this.#listeners.set(event, new Set());
    this.#listeners.get(event).add(fn);
    return () => off(event, fn);
  }

  /**
   * 
   * @param {string} event 
   * @param {Function} fn 
   */
  unsubscribe(event, fn) {
    this.#listeners.get(event)?.delete(fn);
  }

  /**
   * 
   * @param {string} event 
   * @param {object} payload 
   */
  dispatch(event, payload) {
    for (const fn of (this.#listeners.get(event) ?? [])) {
      try { fn(payload); } catch (e) { console.error(`[EditorCanvas] Lỗi ${event} handler:`, e); }
    }
  }
}

/** Quản lý cấp điều phối */
export class EditorManager {
  /** @type {EditorEventBus} */
  #bus;
  /** @type {EditorUI} */
  #ui;
  /** @type {InlineToolbar} */
  #inlineToolbar;
  /** @type {BlockToolbar} */
  #blockToolbar;
  /** @type {EditorListView} */
  #listView;
  /** @type {EditorCanvasMetadata} */
  #metadata;
  /** @type {ContextStore} */
  #contextStore;

  #blockList;
  #canvas;
  #activeBlockId;
  #isEmpty;
  #isDirty;
  #inputRecordTimeout = null;
  #lastInputSnapshot = null;
  #lastInputBlockId = null;
  #lastRecordTime = 0;

  constructor(initialMetaData = {}) {
    this.#bus = new EditorEventBus();

    // Khởi tạo
    this.#metadata = new EditorCanvasMetadata(this.#bus, initialMetaData);
    this.#canvas = new EditorCanvas(this.#bus);

    this.#ui = new EditorUI(this.#bus);
    this.#blockToolbar = new BlockToolbar(this.#bus);
    this.#listView = new EditorListView(this.#bus, this.#canvas);

    // Tham chiếu các phần tử DOM
    this.#blockList = document.querySelector('#be-block-list');

    this.#contextStore = new ContextStore(this.#bus, this.#blockList, {
      maxSnapshots: 10,
      schemaRegistry: BLOCK_REGISTRY,
    });

    this.#inlineToolbar = new InlineToolbar(
      this.#bus,
      this.#blockList,
      this.#contextStore,
      { offset: 8, debounceMs: 80 }
    );

    // Khởi tạo các State
    this.#activeBlockId = "";
    this.#isEmpty = true;
    this.#isDirty = false;

  }

  init() {

    // Subscribe manager events
    this.#bus.subscribe('block:selected', (block) => this.#onBlockSelected(block));
    this.#bus.subscribe('block:add_request', (block) => this.#onBlockAddRequested(block));
    this.#bus.subscribe('block:added', (block) => this.#onBlockAdded(block));
    this.#bus.subscribe('block:updated', (payload) => this.#onBlockUpdated(payload));
    this.#bus.subscribe('block:remove_request', (block) => this.#onBlockRemoveRequested(block));
    this.#bus.subscribe('block:removed', (payload) => this.#onBlockRemoved(payload));

    this.#bus.subscribe('block:action', (p) => this.#onBlockAction(p));
    this.#bus.subscribe('inline:selection_changed', (p) => this.#onInlineSelectionChanged(p));
    this.#bus.subscribe('inline:format_request', (p) => this.#onInlineFormatRequest(p));
    this.#bus.subscribe('inline:link_request', (p) => this.#onInlineLinkRequest(p));
    this.#bus.subscribe('inline:unlink_request', (p) => this.#onInlineUnlinkRequest(p));

    // this.#bus.subscribe('context:undo_applied', (p) => this.#onUndoApplied(p));
    // this.#bus.subscribe('context:redo_applied', (p) => this.#onRedoApplied(p));
    this.#bus.subscribe('block:input', (p) => this.#onBlockInput(p));

    this.#initialRender();
    this.#initCanvas();

    console.log('[EditorManager] Khởi tạo thành công.');
  }

  /**
   * Render khởi tạo
   */
  #initialRender() {
    const blocks = this.#canvas.getBlocks();
    if (blocks.length === 0) return;
    const fragment = document.createDocumentFragment();

    blocks.forEach((block, idx) => {
      const card = new EditorBlockWrapper(this.#bus, block);
      fragment.appendChild(card);
    });

    this.#blockList.innerHTML = "";
    this.#blockList.appendChild(fragment);
  }

  #initCanvas() {
    new DnD(this.#blockList, {
      animation: 150,
      group: "canvas",
      handle: '.be-drag-handle',
    });

    // Lắng nghe sự kiên drop block
    DnDMonitor.on('dragend', ({ item, from, to, oldIndex, newIndex, canceled }) => {
      console.log(item, from, to, oldIndex, newIndex, canceled)
    });

    this.#blockList.addEventListener('click', (e) => {
      const card = e.target.closest('.be-block-card');

      if (!card) return; // Click ra ngoài không làm gì cả

      const clickedId = card.dataset.beBlockId;

      const handle = e.target.closest('.be-drag-handle');

      if (handle) {
        const block = this.#canvas.getBlock(clickedId);
        this.#bus.dispatch('toolbar:toggle', { block, anchorEl: handle });
        return;
      }

      // Nếu block này chưa được active thì mới dispatch
      if (this.#activeBlockId !== clickedId) {
        this.#bus.dispatch('block:selected', { blockId: clickedId });
      }
    });

    this.#blockList.addEventListener('click', (e) => {
      const anchor = e.target.closest('a[href]');
      if (!anchor) return;

      e.preventDefault();
      e.stopPropagation();

      const href = anchor.getAttribute('href');
      if (href) window.open(href, '_blank', 'noopener,noreferrer');
    });

    this.#blockList.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        this.#handleGlobalEnter(e);
      }
    });
  }

  #onBlockAddRequested({ type, data = {}, afterId = null }) {
    // this.#commitPendingInput();
    // this.#contextStore.beginRecord();
    const newBlock = this.#canvas.addBlock(type, data, afterId);

    /* 
    Tạm thời gỡ bỏ undo/redo
    const index = this.#canvas.getBlocks().findIndex(b => b.id === newBlock.id);
    this.#contextStore.recordOperation({
      blockId: newBlock.id,
      attribute: 'lifecycle',
      prev: null,
      next: { type: newBlock.type, data: newBlock.data, index },
      label: `Thêm block ${type}`,
      action: 'LIFECYCLE'
    });
    */

    setTimeout(() => {
      if (newBlock && typeof newBlock.focus === 'function') {
        newBlock.focus(this.#bus, 'start');
      }
    }, 0);
  }

  #onBlockSelected({ blockId }) {
    // Stage 3: Chốt input đang gõ nếu có khi chuyển block
    if (this.#lastInputBlockId && this.#lastInputBlockId !== blockId) {
      this.#commitPendingInput();
    }

    this.#activeBlockId = blockId;
    const block = this.#canvas.getBlock(blockId);
    console.log(`[EditorManager][block:selected] Block: `, block);

    this.#ui.renderSettingsPanel(block);
  }

  #onBlockAdded({ block, afterId }) {
    console.log(`[EditorManager][block:added] Block: `, block);
    const card = new EditorBlockWrapper(this.#bus, block);

    if (this.#canvas.getBlocks().length === 1) {
      this.#isEmpty = false;
      this.#ui.hideCanvasEmptyState();
    }

    if (afterId) {
      const anchorEl = this.#blockList.querySelector(`[data-be-block-id="${afterId}"]`);

      if (anchorEl && anchorEl.nextSibling) {
        this.#blockList.insertBefore(card, anchorEl.nextSibling);
      } else {
        this.#blockList.appendChild(card);
      }
    } else {
      this.#blockList.appendChild(card);
    }

    // Dispatch event activate block
    this.#bus.dispatch("block:selected", { blockId: block.id })
  }

  #onBlockUpdated({ block, silent }) {
    if (silent) return; // Tuyệt đối không replace DOM nếu là silent update

    console.log(`[EditorManager][block:updated]`, block);
    const oldCard = document.querySelector(`[data-be-block-id="${block.id}"]`);
    if (!oldCard) return;

    const newCard = new EditorBlockWrapper(this.#bus, block);
    oldCard.replaceWith(newCard);
  }

  #onBlockRemoveRequested({ blockId }) {
    // this.#commitPendingInput();
    const block = this.#canvas.getBlock(blockId);
    if (!block) return;

    // this.#contextStore.beginRecord();
    const index = this.#canvas.getBlocks().findIndex(b => b.id === blockId);

    // Thực thi vật lý luôn, không thông qua record
    this.#performPhysicalAction('LIFECYCLE', blockId, 'lifecycle', null, null, false, `Xóa block ${block.type}`);
  }

  #onBlockRemoved({ blockId, index }) {
    console.log(`[EditorManager][block:removed]`, blockId);

    const cardElement = document.querySelector(`[data-be-block-id="${blockId}"]`);
    if (cardElement) {
      cardElement.remove();
    }

    const remainingBlocks = this.#canvas.getBlocks();

    if (remainingBlocks.length > 0) {
      // Ưu tiên 1: block nằm ở cùng vị trí index (block bên dưới bị đẩy lên)
      // Ưu tiên 2: block nằm ở index - 1 (block bên trên)
      const focusTarget = remainingBlocks[Math.max(0, index - 1)];
      console.log(focusTarget);

      if (focusTarget) {
        setTimeout(() => {
          focusTarget.focus(this.#bus, 'end');
        }, 0);
      }
    } else {
      this.#isEmpty = true;
      this.#ui.showCanvasEmptyState();
      this.#ui.clearSettingsPanel();
      this.#activeBlockId = "";
    }
  }

  #onBlockAction({ action, blockId, selection }) {
    const block = this.#canvas.getBlock(blockId);
    if (!block) return;

    if (typeof block.handleToolbarAction === 'function') {
      block.handleToolbarAction(action, selection);
    } else {
      console.warn(`[EditorManager] Block "${blockId}" không implement handleToolbarAction()`);
    }
  }

  /**
   * Khi selection thay đổi trong canvas:
   *   1. Lấy block đang chứa selection.
   *   2. Lấy HTML hiện tại của block → parse → tính activeMarks.
   *   3. Gửi lại 'inline:marks_updated' để InlineToolbar sync button state.
   *
   * @param {{ blockId: string, range: Range }} payload
   */
  #onInlineSelectionChanged({ blockId, range }) {
    const block = this.#canvas.getBlock(blockId);
    if (!block) return;

    const editable = this.#getEditableEl(blockId);
    if (!editable) return;

    const tokens = InlineFormatter.parse(editable.innerHTML);
    const offsets = InlineFormatter.getRangeOffsets(range, editable);
    if (!offsets) return;

    const activeMarks = InlineFormatter.getActiveMarks(tokens, offsets.start, offsets.end);

    this.#bus.dispatch('inline:marks_updated', { activeMarks });
  }

  /**
   * Format request (bold/italic/underline):
   *   1. Parse HTML → tokens.
   *   2. applyMark trên [start, end).
   *   3. serialize → write lại innerHTML.
   *   4. Restore selection (caret vẫn ở vị trí cũ).
   *   5. Gửi lại marks mới để toolbar cập nhật.
   *
   * @param {{ command: string, value: string|null, blockId: string, range: Range }} payload
   */
  #onInlineFormatRequest({ command, blockId, range }) {
    if (range.collapsed) return;

    const block = this.#canvas.getBlock(blockId);
    const editable = this.#getEditableEl(blockId);
    if (!block || !editable) return;

    const prevContent = block._cloneData().content;
    const offsets = InlineFormatter.getRangeOffsets(range, editable);
    if (!offsets) return;

    this.#commitPendingInput();
    this.#contextStore.beginRecord();
    const tokens = InlineFormatter.parse(editable.innerHTML);
    const newTokens = InlineFormatter.applyMark(tokens, offsets.start, offsets.end, command);

    const nextContent = BlockSerializer.tokensToSegments(newTokens);
    this.#recordAndApply(blockId, 'content', prevContent, nextContent, `Format: ${command}`);

    const activeMarks = InlineFormatter.getActiveMarks(newTokens, offsets.start, offsets.end);
    this.#bus.dispatch('inline:marks_updated', { activeMarks });
    this.#isDirty = true;
  }

  /**
   * Link request:
   *   applyMark với type='link' và href.
   *
   * @param {{ href: string, blockId: string, range: Range }} payload
   */
  #onInlineLinkRequest({ href, blockId, range }) {
    const block = this.#canvas.getBlock(blockId);
    const editable = this.#getEditableEl(blockId);
    if (!block || !editable) return;

    const prevContent = block._cloneData().content;
    const offsets = InlineFormatter.getRangeOffsets(range, editable);
    if (!offsets) return;

    this.#commitPendingInput();
    this.#contextStore.beginRecord();
    const tokens = InlineFormatter.parse(editable.innerHTML);
    const newTokens = InlineFormatter.applyMark(tokens, offsets.start, offsets.end, 'link', href);

    const nextContent = BlockSerializer.tokensToSegments(newTokens);
    this.#recordAndApply(blockId, 'content', prevContent, nextContent, 'Add Link');

    const activeMarks = InlineFormatter.getActiveMarks(newTokens, offsets.start, offsets.end);
    this.#bus.dispatch('inline:marks_updated', { activeMarks });
    this.#isDirty = true;
  }

  /**
   * Unlink request:
   *   removeLink trên vùng được chọn.
   *
   * @param {{ blockId: string, range: Range }} payload
   */
  #onInlineUnlinkRequest({ blockId, range }) {
    const block = this.#canvas.getBlock(blockId);
    const editable = this.#getEditableEl(blockId);
    if (!block || !editable) return;

    const prevContent = block._cloneData().content;
    const offsets = InlineFormatter.getRangeOffsets(range, editable);
    if (!offsets) return;

    this.#commitPendingInput();
    this.#contextStore.beginRecord();
    const tokens = InlineFormatter.parse(editable.innerHTML);
    const newTokens = InlineFormatter.removeLink(tokens, offsets.start, offsets.end);

    const nextContent = BlockSerializer.tokensToSegments(newTokens);
    this.#recordAndApply(blockId, 'content', prevContent, nextContent, 'Unlink');

    const activeMarks = InlineFormatter.getActiveMarks(newTokens, offsets.start, offsets.end);
    this.#bus.dispatch('inline:marks_updated', { activeMarks });
    this.#isDirty = true;
  }

  #onUndoApplied({ blockId, action, attr, value, cursor, label }) {
    this.#performPhysicalAction(action || 'UPDATE', blockId, attr, value, cursor, true, label);
  }

  #onRedoApplied({ blockId, action, attr, value, cursor, label }) {
    this.#performPhysicalAction(action || 'UPDATE', blockId, attr, value, cursor, true, label);
  }

  /**
   * Unified Action Performer - Phễu trung tâm điều khiển DOM & Canvas
   */
  #performPhysicalAction(action, blockId, attr, value, cursor = null, isUndoRedo = false, label = '') {
    if (action === 'LIFECYCLE') {
      if (value === null) {
        this.#canvas.removeBlock(blockId);
      } else {
        const blocks = this.#canvas.getBlocks();
        const afterId = value.index === 0 ? 'START' : (blocks[value.index - 1]?.id || null);
        this.#canvas.addBlock(value.type, value.data, afterId, blockId);
      }
    } else {
      const block = this.#canvas.getBlock(blockId);
      if (!block) return;

      // Cập nhật thẻ data trong Canvas (Luôn SILENT cho content để tránh re-render card)
      const isSilent = (attr === 'content') ? true : !isUndoRedo;
      this.#canvas.updateBlock(blockId, { [attr]: value }, { silent: isSilent });

      // CHỈ CẬP NHẬT DOM NẾU:
      // 1. Đó là Undo/Redo (cần khôi phục hình ảnh cũ)
      // 2. Đó là lệnh từ Toolbar/Format (cần áp dụng style mới)
      // KHÔNG CẬP NHẬT NẾU: Đang gõ văn bản (Typing) - vì DOM đã có sẵn nội dung rồi.
      if (attr === 'content' && (isUndoRedo || label !== 'Typing')) {
        const editable = this.#getEditableEl(blockId);
        if (editable) {
          // Serialize tokens -> HTML một cách chuẩn mực
          const tokens = Array.isArray(value) ? BlockSerializer.segmentsToTokens(value) : value;
          editable.innerHTML = InlineFormatter.serialize(tokens);
        }
      }
    }

    // Khôi phục con trỏ nếu có thông tin (chủ yếu dùng cho Undo/Redo hoặc Format)
    if (cursor && cursor.charOffset !== null) {
      setTimeout(() => {
        const editable = this.#getEditableEl(blockId);
        if (editable) {
          editable.focus();
          const off = cursor.charOffset;
          this.#restoreSelectionByOffset(editable, off, off);
        }
      }, isUndoRedo ? 10 : 0);
    }
  }

  /**
   * Thực thi và ghi lại một Operation
   */
  #recordAndApply(blockId, attr, prev, next, label, action = 'UPDATE') {
    // const op = { blockId, attribute: attr, prev, next, label, action };

    // 1. Ghi vào store (Tạm thời vô hiệu hóa)
    // this.#contextStore.recordOperation(op);

    // 2. Thực thi vật lý
    this.#performPhysicalAction(action, blockId, attr, next, null, false, label);
  }

  #onBlockInput({ blockId }) {
    const block = this.#canvas.getBlock(blockId);
    if (!block) return;

    // Cập nhật model với schema mới { rich_text, meta }
    const editable = this.#getEditableEl(blockId);
    if (editable) {
      const data = block.serializeData(editable);
      this.#canvas.updateBlock(blockId, data, { silent: true });
    }
  }

  /**
   * Chốt (Commit) mọi thay đổi (Đã vô hiệu hóa cùng với Undo/Redo)
   */
  #commitPendingInput() {
    // Logic gỡ bỏ tạm thời
  }

  /**
   * Xử lý phím Enter tập trung
   */
  #handleGlobalEnter(e) {
    const { blockId, blockType } = this.#contextStore.cursor;

    if (!blockId || this.#contextStore.type === 'none') return;

    if (blockType === 'blocks/paragraph') {
      e.preventDefault();
    }
  }

  #getEditableEl(blockId) {
    const card = this.#blockList.querySelector(`[data-be-block-id="${blockId}"]`);
    if (!card) return null;

    // Ưu tiên element được đánh dấu rõ ràng
    return (
      card.querySelector('[data-be-editable]') ??
      card.querySelector('[contenteditable="true"]') ??
      null
    );
  }

  #restoreSelectionByOffset(container, start, end) {
    const sel = window.getSelection();
    if (!sel) return;

    const findPoint = (targetOffset) => {
      let charCount = 0;

      function walk(node) {
        if (node.nodeType === Node.TEXT_NODE) {
          const len = node.textContent.length;
          if (charCount + len >= targetOffset) {
            return { node, offset: targetOffset - charCount };
          }
          charCount += len;
          return null;
        }
        for (const child of node.childNodes) {
          const result = walk(child);
          if (result) return result;
        }
        return null;
      }

      return walk(container);
    };

    const startPoint = findPoint(start);
    const endPoint = findPoint(end);
    if (!startPoint || !endPoint) return;

    try {
      const range = document.createRange();
      range.setStart(startPoint.node, startPoint.offset);
      range.setEnd(endPoint.node, endPoint.offset);

      sel.removeAllRanges();
      sel.addRange(range);
    } catch (err) {
      console.warn('[EditorManager] Không thể restore selection:', err);
    }
  }

  getCanvas() {
    return this.#canvas;
  }

  getPayload() {
    return {
      meta: {
        version: 1,
        ...this.#metadata.getData(),
      },
      blocks: this.#canvas.getBlocks().map(block =>
        BlockSerializer.toPayload(
          block,
          this.#getEditableEl(block.id)
        )
      ),
    };
  }
}
/** Quản lý các block hiện tại trong trang */
class EditorCanvas {
  /** @type {EditorEventBus} */
  #bus;
  /** 
   * @type {EditorBlock[]}
   * @private
   */
  #blocks = [];

  constructor(bus) {
    this.#bus = bus;
  }

  /**
   * Tính các thông số của page
   * @returns {{
   * blockCount: number,
   * readTime: number
   * }}
   */
  computeStats() {
    return {
      blockCount: this.#blocks.length
    };
  }

  // ===========================
  // Block API
  // ===========================
  /**
   * Thêm block mới.
   * @param {string}      type      — phải có trong BLOCK_REGISTRY
   * @param {object}      data      — merge với defaultData
   * @param {string|null} afterId   — chèn sau block có id này; null = cuối danh sách
   * @returns {string} id của block mới
  */
  addBlock(
    type,
    data = {},
    afterId = null,
    forceId = null // Thêm forceId để phục vụ Undo/Redo khôi phục đúng ID cũ
  ) {
    const blueprint = BLOCK_REGISTRY.get(type);
    if (!blueprint) throw new Error(`[EditorCanvas] Không tồn tại block type: "${type}"`);
    const { schema, blockClass } = blueprint;

    const block = new blockClass({
      id: forceId, // Nếu có forceId (hồi sinh block), dùng nó
      data
    }, schema, this.#bus);

    if (afterId === null) {
      this.#blocks = [...this.#blocks, block];
    } else if (afterId === 'START') {
      this.#blocks = [block, ...this.#blocks];
    } else {
      const idx = this.#blocks.findIndex(b => b.id === afterId);
      const insertAt = idx === -1 ? this.#blocks.length : idx + 1;
      this.#blocks = [
        ...this.#blocks.slice(0, insertAt),
        block,
        ...this.#blocks.slice(insertAt)
      ];
    }

    this.#bus.dispatch('block:added', {
      block,
      afterId
    });
    return block;
  }

  /**
   * Update một block
   * 
   * @param {string} id 
   * @param {object} payload 
   */
  updateBlock(id, payload, options = {}) {
    const idx = this.#blocks.findIndex(b => b.id === id);
    if (idx === -1) return null;

    const block = this.#blocks[idx];

    const isChanged = Object.keys(payload).some(
      key => payload[key] !== block.data[key]
    );
    if (!isChanged) return block;

    const prevData = { ...block.data };
    block.data = {
      rich_text: payload.rich_text ?? block.data.rich_text,
      meta: { ...block.data.meta, ...(payload.meta ?? {}) },
    }

    this.#bus.dispatch("block:updated", {
      block,
      prevData,
      silent: options.silent || false
    });
  }

  /**
   * Xóa block theo id
   * 
   * @param {EditorBlock['id']} id - ID của block cần xóa
   */
  removeBlock(id) {
    const idx = this.#blocks.findIndex(b => b.id === id);
    if (idx === -1) return;

    this.#blocks = this.#blocks.filter(b => b.id !== id);
    this.#bus.dispatch("block:removed", {
      blockId: id,
      index: idx
    });
    return idx;
  }

  /**
   * @returns {EditorBlock[]} Blocks
   */
  getBlocks() {
    return this.#blocks.map(b => b);
  }

  /**
   * @returns {EditorBlock|null} Block có ID cần tìm
   */
  getBlock(id) {
    return this.#blocks.find(b => b.id === id);
  }

  /**
   * Di chuyển block lên/xuống 1 vị trí.
   * @param {'up'|'down'} direction
   */
  moveBlock(id, direction) {
    const i = this.#blocks.findIndex(b => b.id === id);
    const j = direction === "up" ? i - 1 : i + 1;
    if (i === -1 || j < 0 || j >= this.#blocks.length) return;

    const next = [...this.#blocks];
    [next[i], next[j]] = [next[j], next[i]];
    this.#blocks = next;

    this.#bus.dispatch("block:reordered", {
      blocks: this.#blocks.map(b => ({ ...b }))
    });
  }

  /**
   * Reorder toàn bộ danh sách theo mảng id.
   * Gọi sau khi drag & drop kết thúc.
   * @param {EditorBlock['id'][]} orderedIds
   */
  reorderBlocks(orderedIds) {
    const map = new Map(this.#blocks.map(b => [b.id, b]));
    const next = orderedIds.map(id => map.get(id)).filter(Boolean);

    const missing = this.#blocks.filter(b => !orderedIds.includes(b.id));
    this.#blocks = [...next, ...missing];

    this.#bus.dispatch("block:reordered", {
      blocks: this.#blocks.map(b => ({ ...b }))
    })
  }
}
/** Lưu các metadata */
class EditorCanvasMetadata {
  /** @type {EditorEventBus} */
  #bus;

  #data = {
    title: 'Tiêu đề bài viết',
    slug: 'tieu-de-bai-viet',
    excerpt: '',
    author_id: '',
    status: 'draft',
    category_ids: [],
    featured_image: null,
    settings: {
      show_author: false,
      show_date: true,
      show_read_time: false,
      show_view_count: false,
    },
    read_time: 0, // minute
    init_view_count: 0
  };

  /**
   * @param {EditorEventBus} bus 
   * @param {object} initialData - Dữ liệu khởi tạo
   */
  constructor(bus, initialData = {}) {
    this.#bus = bus;

    this.#data = { ...this.#data, ...initialData };

    this.#initEvents();
  }

  #initEvents() {
    this.#bus.subscribe('meta:update_request', ({ key, value }) => {
      this.setData(key, value);
    });

    this.#bus.subscribe('meta:sync_request', () => {
      console.log('[EditorCanvasMetadata] Đang đồng bộ State khởi tạo xuống UI...');

      const traverse = (obj, prefix = '') => {
        for (const [key, value] of Object.entries(obj)) {
          const path = prefix ? `${prefix}.${key}` : key;

          if (value !== null && typeof value === 'object' && !Array.isArray(value)) {
            traverse(value, path);
          } else {
            this.#bus.dispatch('meta:updated', {
              key: path,
              value: value,
              allMeta: this.getData()
            });
          }
        }
      }

      traverse(this.#data);
    });
  }

  /**
   * Cập nhật một trường dữ liệu Meta cụ thể
   * @param {string} key - Tên trường (ví dụ: 'title', 'status')
   * @param {any} value - Giá trị mới
   */
  setData(key, value) {
    if (this.#data[key] !== value) {
      this.#data[key] = value;

      console.log(`[EditorCanvasMetadata] Đã cập nhật "${key}":`, value);

      this.#bus.dispatch('meta:updated', {
        key,
        value,
        allMeta: this.getData()
      });
    }
  }

  /**
   * Lấy toàn bộ dữ liệu Meta (Dùng khi lưu bài viết)
   * Dùng spread operator để tránh bị tham chiếu (mutate) từ bên ngoài
   */
  getData() {
    return { ...this.#data };
  }
}
/** Bọc 1 block với drag handle */
class EditorBlockWrapper {
  /**
   * 
   * @param {EditorBlock} block 
   * @returns 
   */
  constructor(bus, block) {
    const fraggment = document.createDocumentFragment();
    const wrapper = document.createElement('div');
    wrapper.className = 'be-block-card';
    wrapper.dataset.beBlockId = block.id;
    wrapper.dataset.beBlockType = block.type;

    const handle = document.createElement('div');
    handle.className = 'be-drag-handle';
    handle.title = 'Kéo để sắp xếp';
    handle.innerHTML = '<i class="fa-solid fa-grip-vertical"></i>';

    fraggment.appendChild(handle);
    fraggment.appendChild(block.render());

    wrapper.appendChild(fraggment);

    return wrapper;
  }
}