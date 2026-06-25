import { registry as BLOCK_REGISTRY } from './block_registry.js';
import { BlockSerializer } from './block_serializer_v2.js';
import { EditorBlock } from './blocks/editor_block.js';
import { EditorListView } from './editor_list_view.js';
import { ContextStore } from './context/context_store.js';
import { InlineToolbar } from './toolbar/inline_toolbar.js';
import { BlockToolbar } from './toolbar/block_toolbar.js';
import { InlineFormatter } from './inline_formatter_v2.js';
import { EditorUI } from './editor_ui.js';
import { EditorHistory } from './editor_history.js';

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
  /** @type {EditorHistory} */
  #history;

  #blockList;
  #canvas;
  #activeBlockId;
  #isEmpty;
  #isDirty;
  #inputRecordTimeout = null;
  #lastInputBlockId = null;
  #isRestoring = false;
  #restoreFocusBlockId = null;

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
    this.#history = new EditorHistory(this.#bus, { limit: 100 });

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

    this.#bus.subscribe('block:input', (p) => this.#onBlockInput(p));

    this.#initialRender();
    this.#initCanvas();
    this.#history.reset(this.#snapshotBlocks());
    this.#initHistoryShortcuts();

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
    // Lắng nghe sự kiên drop block để cập nhật lại order trong Canvas
    DnDMonitor.on('dragend', () => {
      if (this.#isRestoring) return;
      this.#commitPendingInput();
      const orderedIds = Array.from(this.#blockList.querySelectorAll('.be-block-card'))
        .map(el => el.dataset.beBlockId);
      this.#canvas.reorderBlocks(orderedIds);
      this.#recordHistory('Reorder blocks');
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

    this.#blockList.addEventListener('input', (e) => {
      const card = e.target.closest?.('.be-block-card');
      if (!card || this.#isRestoring) return;
      this.#scheduleInputCommit(card.dataset.beBlockId);
    }, true);

    this.#blockList.addEventListener('focusout', (e) => {
      const fromCard = e.target.closest?.('.be-block-card');
      const toCard = e.relatedTarget?.closest?.('.be-block-card');
      if (fromCard && fromCard !== toCard) this.#commitPendingInput();
    }, true);
  }

  #onBlockAddRequested({ type, data = {}, afterId = null }) {
    if (!this.#isRestoring) this.#commitPendingInput();
    this.#canvas.addBlock(type, data, afterId);
    this.#recordHistory('Add block');
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
    this.#recordHistory('Update block');
  }

  #onBlockRemoveRequested({ blockId }) {
    if (!this.#isRestoring) this.#commitPendingInput();
    this.#restoreFocusBlockId = blockId;
    this.#canvas.removeBlock(blockId);
    this.#recordHistory('Delete block');
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
      this.#commitPendingInput();
      block.handleToolbarAction(action, selection);
      this.#recordHistory(action);
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

    const editable = this.#getEditableEl(blockId, range);
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
    const editable = this.#getEditableEl(blockId, range);
    if (!block || !editable) return;

    const prevContent = block._cloneData().rich_text;
    const offsets = InlineFormatter.getRangeOffsets(range, editable);
    if (!offsets) return;

    this.#commitPendingInput();
    const tokens = InlineFormatter.parse(editable.innerHTML);
    const newTokens = InlineFormatter.applyMark(tokens, offsets.start, offsets.end, command);

    const nextContent = BlockSerializer.tokensToSegments(newTokens);
    this.#recordAndApply(
      blockId,
      "rich_text",
      prevContent,
      nextContent,
      `Format: ${command}`,
      "UPDATE",
      editable
    );
    const restoredRange = this.#restoreSelectionByOffset(editable, offsets.start, offsets.end);
    if (restoredRange) {
      this.#bus.dispatch('inline:range_restored', {
        blockId,
        range: restoredRange,
      });
    }

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
    const editable = this.#getEditableEl(blockId, range);
    if (!block || !editable) return;

    const prevContent = block._cloneData().rich_text;
    const offsets = InlineFormatter.getRangeOffsets(range, editable);
    if (!offsets) return;

    this.#commitPendingInput();
    const tokens = InlineFormatter.parse(editable.innerHTML);
    const newTokens = InlineFormatter.applyMark(tokens, offsets.start, offsets.end, 'link', href);

    const nextContent = BlockSerializer.tokensToSegments(newTokens);
    this.#recordAndApply(
      blockId,
      "rich_text",
      prevContent,
      nextContent,
      "Add Link",
      "UPDATE",
      editable
    );
    const restoredRange = this.#restoreSelectionByOffset(editable, offsets.start, offsets.end);
    if (restoredRange) {
      this.#bus.dispatch('inline:range_restored', {
        blockId,
        range: restoredRange,
      });
    }

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
    const editable = this.#getEditableEl(blockId, range);
    if (!block || !editable) return;

    const prevContent = block._cloneData().rich_text;
    const offsets = InlineFormatter.getRangeOffsets(range, editable);
    if (!offsets) return;

    this.#commitPendingInput();
    const tokens = InlineFormatter.parse(editable.innerHTML);
    const newTokens = InlineFormatter.removeLink(tokens, offsets.start, offsets.end);

    const nextContent = BlockSerializer.tokensToSegments(newTokens);
    this.#recordAndApply(
      blockId,
      "rich_text",
      prevContent,
      nextContent,
      "Unlink",
      "UPDATE",
      editable
    );
    const restoredRange = this.#restoreSelectionByOffset(editable, offsets.start, offsets.end);
    if (restoredRange) {
      this.#bus.dispatch('inline:range_restored', {
        blockId,
        range: restoredRange,
      });
    }

    const activeMarks = InlineFormatter.getActiveMarks(newTokens, offsets.start, offsets.end);
    this.#bus.dispatch('inline:marks_updated', { activeMarks });
    this.#isDirty = true;
  }

  /**
   * Unified Action Performer - Đã được đơn giản hóa, chỉ xử lý cập nhật data.
   */
  #recordAndApply(blockId, attr, prev, next, label, action = 'UPDATE', editableEl = null) {
    const block = this.#canvas.getBlock(blockId);
    if (!block) return;

    // Cập nhật thẻ data trong Canvas
    this.#canvas.updateBlock(blockId, { [attr]: next }, { silent: true });

    // Chỉ đồng bộ DOM nếu là cập nhật nội dung văn bản
    if (attr === 'rich_text') {
      const editable = editableEl || this.#getEditableEl(blockId);

      if (editable) {
        const tokens = Array.isArray(next) ? BlockSerializer.segmentsToTokens(next) : next;
        editable.innerHTML = InlineFormatter.serialize(tokens);

        // Kích hoạt sự kiện input để các block phức tạp tự đồng bộ dữ liệu
        editable.dispatchEvent(new Event('input', { bubbles: true }));
      }
    }
    this.#recordHistory(label);
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
    this.#scheduleInputCommit(blockId);
  }

  /**
   * Chốt input đang debounce vào history.
   */
  #commitPendingInput() {
    if (!this.#inputRecordTimeout) return;

    clearTimeout(this.#inputRecordTimeout);
    this.#inputRecordTimeout = null;
    this.#commitAllDomToModel();
    this.#recordHistory('Edit content');
    this.#lastInputBlockId = null;
  }

  /**
   * Xử lý phím Enter tập trung
   */
  #scheduleInputCommit(blockId) {
    if (this.#isRestoring) return;

    if (this.#lastInputBlockId && this.#lastInputBlockId !== blockId) {
      this.#commitPendingInput();
    }

    this.#lastInputBlockId = blockId;
    if (this.#inputRecordTimeout) clearTimeout(this.#inputRecordTimeout);

    this.#inputRecordTimeout = setTimeout(() => {
      this.#inputRecordTimeout = null;
      this.#commitAllDomToModel();
      this.#recordHistory('Edit content');
      this.#lastInputBlockId = null;
    }, 700);
  }

  #commitAllDomToModel() {
    this.#canvas.getBlocks().forEach(block => {
      const data = block.serializeData(this.#getEditableEl(block.id));
      this.#canvas.updateBlock(block.id, data, { silent: true });
    });
  }

  #snapshotBlocks() {
    this.#commitAllDomToModel();

    return this.#canvas.getBlocks().map(block => ({
      id: block.id,
      type: block.type,
      version: block.schema?.version ?? 1,
      data: block._cloneData(),
    }));
  }

  #recordHistory(label) {
    if (this.#isRestoring) return;
    this.#isDirty = true;
    this.#history.record(this.#snapshotBlocks(), label);
    if (label !== 'Delete block') this.#restoreFocusBlockId = null;
  }

  #initHistoryShortcuts() {
    document.addEventListener('keydown', (e) => {
      const ctrl = e.ctrlKey || e.metaKey;
      if (!ctrl || !this.#isEditorInteraction()) return;

      const key = e.key.toLowerCase();
      if (key === 'z' && !e.shiftKey) {
        e.preventDefault();
        this.undo();
      } else if ((key === 'z' && e.shiftKey) || key === 'y') {
        e.preventDefault();
        this.redo();
      }
    }, { capture: true });
  }

  #isEditorInteraction() {
    const active = document.activeElement;
    if (active && (
      this.#blockList.contains(active) ||
      document.querySelector('#be-block-settings-panel')?.contains(active)
    )) {
      return true;
    }

    const sel = window.getSelection();
    if (!sel || sel.rangeCount === 0) return false;

    return this.#blockList.contains(sel.getRangeAt(0).commonAncestorContainer);
  }

  undo() {
    this.#commitPendingInput();
    const snapshot = this.#history.undo();
    if (!snapshot) return false;

    this.#restoreSnapshot(snapshot);
    return true;
  }

  redo() {
    this.#commitPendingInput();
    const snapshot = this.#history.redo();
    if (!snapshot) return false;

    this.#restoreSnapshot(snapshot);
    return true;
  }

  canUndo() {
    return this.#history.canUndo();
  }

  canRedo() {
    return this.#history.canRedo();
  }

  #restoreSnapshot(snapshot) {
    const previousActiveBlockId = this.#activeBlockId;

    this.#isRestoring = true;
    try {
      this.#canvas.replaceBlocks(snapshot);
      this.#renderCanvasFromState();

      const blocks = this.#canvas.getBlocks();
      const target =
        blocks.find(block => block.id === this.#restoreFocusBlockId) ??
        blocks.find(block => block.id === previousActiveBlockId) ??
        blocks[blocks.length - 1] ??
        null;

      if (target) {
        this.#activeBlockId = target.id;
        this.#ui.renderSettingsPanel(target);
        setTimeout(() => target.focus(this.#bus, 'end'), 0);
      } else {
        this.#activeBlockId = "";
        this.#ui.clearSettingsPanel();
      }
    } finally {
      this.#restoreFocusBlockId = null;
      this.#isRestoring = false;
    }
  }

  #renderCanvasFromState() {
    const blocks = this.#canvas.getBlocks();
    this.#blockList.innerHTML = "";

    if (blocks.length === 0) {
      this.#isEmpty = true;
      this.#ui.showCanvasEmptyState();
      return;
    }

    const fragment = document.createDocumentFragment();
    blocks.forEach(block => {
      fragment.appendChild(new EditorBlockWrapper(this.#bus, block));
    });

    this.#isEmpty = false;
    this.#blockList.appendChild(fragment);
  }

  #handleGlobalEnter(e) {
    const { blockId, blockType } = this.#contextStore.cursor;

    if (!blockId || this.#contextStore.type === 'none') return;

    if (blockType === 'blocks/paragraph') {
      e.preventDefault();
    }
  }

  #getEditableEl(blockId, range = null) {
    const card = this.#blockList.querySelector(`[data-be-block-id="${blockId}"]`);
    if (!card) return null;

    // Nếu có range, tìm element editable chứa range đó
    if (range) {
      let node = range.commonAncestorContainer;
      while (node && node !== card) {
        if (node.nodeType === Node.ELEMENT_NODE) {
          const el = /** @type {HTMLElement} */ (node);
          if (el.isContentEditable || el.dataset.beEditable !== undefined) {
            return el;
          }
        }
        node = node.parentNode;
      }
    }

    // Mặc định: Lấy element editable chính của card
    return (
      card.querySelector('[data-be-editable]') ??
      card.querySelector('[contenteditable="true"]') ??
      null
    );
  }

  #restoreSelectionByOffset(container, start, end) {
    const sel = window.getSelection();
    if (!sel) return null;

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
    if (!startPoint || !endPoint) return null;

    try {
      const range = document.createRange();
      range.setStart(startPoint.node, startPoint.offset);
      range.setEnd(endPoint.node, endPoint.offset);

      sel.removeAllRanges();
      sel.addRange(range);
      return range.cloneRange();
    } catch (err) {
      console.warn('[EditorManager] Không thể restore selection:', err);
      return null;
    }
  }

  importPayload(payload) {
    if (!payload) return;

    if (payload.meta) {

      this.#metadata.mergeData(payload.meta);

      // Force UI sync sau khi nạp xong
      this.#bus.dispatch('meta:sync_request');
    }

    if (payload.blocks && Array.isArray(payload.blocks)) {
      payload.blocks.forEach(b => {
        this.#canvas.addBlock(b.type, b.data || {}, null, b.id);
      });
    }
    this.#history.reset(this.#snapshotBlocks());
  }

  getCanvas() {
    return this.#canvas;
  }

  getPayload() {
    const allBlocks = this.#canvas.getBlocks().map(block =>
      BlockSerializer.toPayload(
        block,
        this.#getEditableEl(block.id)
      )
    );

    // Trim đầu cuối bài viết để dọn dẹp các block trống
    let start = 0;
    while (start < allBlocks.length && BlockSerializer.isBlockEmpty(allBlocks[start])) {
      start++;
    }

    let end = allBlocks.length - 1;
    while (end >= start && BlockSerializer.isBlockEmpty(allBlocks[end])) {
      end--;
    }

    const trimmedBlocks = start <= end ? allBlocks.slice(start, end + 1) : [];

    return {
      meta: {
        version: 1,
        ...this.#metadata.getData(),
      },
      blocks: trimmedBlocks,
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
   * @param {string}      type      - phải có trong BLOCK_REGISTRY
   * @param {object}      data      - merge với defaultData
   * @param {string|null} afterId   - chèn sau block có id này; null = cuối danh sách
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

  replaceBlocks(snapshot = []) {
    this.#blocks = snapshot.map(payload => {
      const blueprint = BLOCK_REGISTRY.get(payload.type);
      if (!blueprint) throw new Error(`[EditorCanvas] KhÃ´ng tá»“n táº¡i block type: "${payload.type}"`);

      const { schema, blockClass } = blueprint;
      return new blockClass({
        id: payload.id,
        data: this.#clone(payload.data || {}),
      }, schema, this.#bus);
    });

    this.#bus.dispatch("block:reordered", {
      blocks: this.#blocks.map(b => ({ ...b }))
    });
  }

  #clone(value) {
    try { return JSON.parse(JSON.stringify(value)); } catch { return value; }
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
      show_view_count: false,
      is_featured: false,
    },
    init_view_count: 0
  };

  /**
   * @param {EditorEventBus} bus 
   * @param {object} initialData - Dữ liệu khởi tạo
   */
  constructor(bus, initialData = {}) {
    this.#bus = bus;

    // Deep merge để bảo vệ structure mặc định
    this.#data = this.#deepMerge(this.#data, initialData);

    this.#initEvents();
  }

  mergeData(payload) {
    this.#data = this.#deepMerge(this.#data, payload);
  }

  /**
   * Deep merge objects
   * @private
   */
  #deepMerge(target, source) {
    console.log(target, source);
    const result = { ...target };

    for (const [key, value] of Object.entries(source)) {
      if (value === null || value === undefined) {
        result[key] = value;
      } else if (typeof value === 'object' && !Array.isArray(value) && typeof target[key] === 'object' && !Array.isArray(target[key])) {
        // Nếu cả 2 đều là object (không phải array), merge đệ quy
        result[key] = this.#deepMerge(target[key] || {}, value);
      } else {
        // Nếu là array hoặc type khác, ghi đè trực tiếp
        result[key] = value;
      }
    }

    return result;
  }

  #initEvents() {
    this.#bus.subscribe('meta:update_request', ({ key, value }) => {
      this.setData(key, value);
    });

    this.#bus.subscribe('meta:sync_request', () => {
      console.log('[EditorCanvasMetadata] Đang đồng bộ State khởi tạo xuống UI...');

      for (const [key, value] of Object.entries(this.#data)) {
        this.#bus.dispatch('meta:updated', {
          key: key, // Chỉ lấy key cấp 1 (VD: 'title', 'settings')
          value: value,
          allMeta: this.getData()
        });
      }
    });
  }

  /**
   * Cập nhật một trường dữ liệu Meta cụ thể
   * @param {string} key - Tên trường (ví dụ: 'title', 'status')
   * @param {any} value - Giá trị mới
   */
  setData(key, value) {
    console.log(this.#data, key, value);
    const keys = key.split('.');
    let target = this.#data;

    // Duyệt sâu để tìm đúng field (hỗ trợ dot notation)
    for (let i = 0; i < keys.length - 1; i++) {
      const k = keys[i];
      if (!target[k] || typeof target[k] !== 'object' || Array.isArray(target[k])) {
        // Nếu chưa tồn tại hoặc là array/primitive, khởi tạo object mới
        target[k] = {};
      }
      target = target[k];
    }

    const lastKey = keys[keys.length - 1];

    if (target[lastKey] !== value) {
      target[lastKey] = value;
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
