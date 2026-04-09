import { registry as BLOCK_REGISTRY } from './block_registry.js';
import { EditorBlock } from './blocks/editor_block.js';
import { EditorListView } from './editor_list_view.js';
import { EditorBlockToolbar } from './editor_toolbar.js';
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
  /** @type {EditorBlockToolbar} */
  #toolbar;
  /** @type {EditorListView} */
  #listView;

  #blockList;
  #canvas;
  #activeBlockId;
  #isEmpty;

  constructor() {
    this.#bus = new EditorEventBus();

    // Khởi tạo UI và Canvas
    this.#ui = new EditorUI(this.#bus);
    this.#canvas = new EditorCanvas(this.#bus);
    this.#toolbar = new EditorBlockToolbar(this.#bus, this.#canvas);
    this.#listView = new EditorListView(this.#bus, this.#canvas);

    // Tham chiếu các phần tử DOM
    this.#blockList = document.querySelector('#be-block-list');

    // Khởi tạo các State
    this.#activeBlockId = "";
    this.#isEmpty = true;

  }

  init() {

    // Subscribe manager events
    this.#bus.subscribe('block:selected', (block) => this.#onBlockSelected(block));
    this.#bus.subscribe('block:added', (block) => this.#onBlockAdded(block));
    this.#bus.subscribe('block:update_request', (block) => this.#onBlockUpdateRequested(block));
    this.#bus.subscribe('block:updated', (block) => this.#onBlockUpdated(block));
    this.#bus.subscribe('block:remove_request', (block) => this.#onBlockRemoveRequested(block));
    this.#bus.subscribe('block:removed', (payload) => this.#onBlockRemoved(payload));

    this.#initialRender();
    this.#initCanvas();
    this.#initLeftPanel();
    this.#initPanelToggle();

    this.#initKeyboardShortcuts();

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
      // Tìm xem user có click vào bên trong một block-card nào không
      const card = e.target.closest('.be-block-card');

      if (!card) return; // Click ra ngoài không làm gì cả

      const clickedId = card.dataset.beBlockId;

      // Nếu block này chưa được active thì mới dispatch
      if (this.#activeBlockId !== clickedId) {
        this.#bus.dispatch('block:selected', { blockId: clickedId });
      }
    });
  }

  /**
   * Khởi tạo left panel - block inventory
   */
  #initLeftPanel() {
    document.querySelectorAll('[data-add-block]').forEach(btn => {
      btn.addEventListener('click', () => {
        this.#canvas.addBlock(btn.dataset.addBlock);
        console.log(this.#canvas.getBlocks());
      });
    });
  }

  #initPanelToggle() {
    document.getElementById('be-toggle-left')?.addEventListener('click', () => {
      document.getElementById('be-left')?.classList.toggle('collapsed');
    });
    document.getElementById('be-toggle-right')?.addEventListener('click', () => {
      document.getElementById('be-right')?.classList.toggle('collapsed');
    });
  }

  #initKeyboardShortcuts() {
    // Lắng nghe sự kiện keydown trên canvas
    this.#blockList.addEventListener('keydown', (e) => {
      if (e.key !== 'Backspace') return;

      const target = e.target;
      if (target.getAttribute('contenteditable') !== 'true') return;

      const card = target.closest('.be-block-card');
      if (!card) return;

      const textContent = target.textContent.trim();

      if (textContent === '') {
        e.preventDefault();

        const blockId = card.dataset.beBlockId;

        this.#bus.dispatch('block:remove_request', { blockId });
      }
    })
  }


  #onBlockSelected({ blockId }) {
    this.#activeBlockId = blockId;
    const block = this.#canvas.getBlock(blockId);
    console.log(`[EditorManager][block:selected] Block: `, block);

    this.#ui.renderSettingsPanel(block);
  }

  #onBlockAdded({ block }) {
    console.log(`[EditorManager][block:added] Block: `, block);
    const card = new EditorBlockWrapper(this.#bus, block);

    if (this.#canvas.getBlocks().length === 1) {
      this.#isEmpty = false;
      this.#ui.hideCanvasEmptyState();
    }
    this.#blockList.appendChild(card);

    // Dispatch event activate block
    this.#bus.dispatch("block:selected", { blockId: block.id })
  }

  #onBlockUpdateRequested({ blockId, payload }) {
    console.log(`[EditorManager][block:update_request]`, blockId, payload);

    this.#canvas.updateBlock(blockId, payload);
  }

  #onBlockUpdated({ block }) {
    console.log(`[EditorManager][block:updated]`, block);
    const oldCard = document.querySelector(`[data-be-block-id="${block.id}"]`);
    if (!oldCard) return;

    const newCard = new EditorBlockWrapper(this.#bus, block);

    oldCard.replaceWith(newCard);
  }

  #onBlockRemoveRequested({ blockId }) {
    console.log(`[EditorManager][block:remove_request]`, blockId);

    this.#canvas.removeBlock(blockId);
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
}
/** Quản lý các block hiện tại trong trang */
class EditorCanvas {
  /** @type {EditorEventBus} */
  #bus;
  #metadata = {
    showAuthor: true,
    showPublishDate: true,
    showReadTime: true,
    showViews: true,

    author: null,
    publishDate: null,
    readTime: 0,
    views: 0,
  };
  /** 
   * @type {EditorBlock[]}
   * @private
   */
  #blocks = [];

  constructor(bus) {
    this.#bus = bus;
  }

  // ===========================
  // Metadata API
  // ===========================
  getMeta() {
    return { ...this.#metadata };
  }

  /**
   * Tính các thông số của page
   * @returns {{
   * blockCount: number,
   * wordCount: number,
   * readTime: number
   * }}
   */
  #computeStats() {
    let words = 0;
    for (const b of this.#blocks) {
      words += b.getStats().words;
    }
    const readTime = Math.max(1, Math.round(words / 200)); // 200wpm
    return {
      blockCount: this.#blocks.length,
      wordCount: words,
      readTime
    };
  }

  // ===========================
  // Block API
  // ===========================
  /**
   * Thêm block mới.
   * @param {string}      type      — phải có trong BLOCK_DEFAULTS
   * @param {object}      data      — merge với defaultData
   * @param {string|null} afterId   — chèn sau block có id này; null = cuối danh sách
   * @returns {string} id của block mới
  */
  addBlock(
    type,
    data = {},
    afterId = null
  ) {
    const blueprint = BLOCK_REGISTRY.get(type);
    if (!blueprint) throw new Error(`[EditorCanvas] Không tồn tại block type: "${type}"`);
    const { schema, blockClass } = blueprint;

    /** @var {EditorBlock} */
    const block = new blockClass({
      data
    }, schema);

    if (afterId === null) {
      this.#blocks = [...this.#blocks, block];
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
    return block.id;
  }

  /**
   * Update một block
   * 
   * @param {string} id 
   * @param {object} payload 
   */
  updateBlock(id, payload) {
    const idx = this.#blocks.findIndex(b => b.id === id);
    if (idx === -1) return null;

    const block = this.#blocks[idx];

    const isChanged = Object.keys(payload).some(
      key => payload[key] !== block.data[key]
    );
    if (!isChanged) return block;

    block.data = {
      ...block.data,
      ...payload
    };

    this.#bus.dispatch("block:updated", {
      block,
      prevData: { ...block.data }
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

    const handle = document.createElement('div');
    handle.className = 'be-drag-handle';
    handle.title = 'Kéo để sắp xếp';
    handle.innerHTML = '<i class="fa-solid fa-grip-vertical"></i>';

    handle.addEventListener('click', (e) => {
      e.stopPropagation();

      bus.dispatch('block:handle_click', {
        blockId: block.id,
        anchorEl: handle
      });
    });

    fraggment.appendChild(handle);
    fraggment.appendChild(block.render());

    wrapper.appendChild(fraggment);

    return wrapper;
  }
}