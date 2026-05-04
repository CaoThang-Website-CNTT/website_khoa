import { EditorToolbar } from './editor_toolbar.js';

export class BlockToolbar extends EditorToolbar {

  static DROPDOWN_ID = 'be-block-toolbar';

  /** @type {object|null} — context row/col khi block là table */
  #currentSelection = null;

  /** @type {HTMLElement} */
  #separator;

  /** @type {HTMLButtonElement} */
  #deleteBtn;

  /**
   * @param {EditorEventBus} bus
   */
  constructor(bus) {
    super(bus, null);

    this.#buildDOM();
    this.#setupListeners();
  }

  /**
   * @override
   */
  destroy() {
    super.destroy();
    this.#currentSelection = null;
  }

  // ─── DOM Build ────────────────────────────────────────────────────────────

  #buildDOM() {
    this.root = document.createElement('div');
    this.root.className = 'dropdown';
    this.root.dataset.dropdownId = BlockToolbar.DROPDOWN_ID;

    this.root.innerHTML = `
      <div class="dropdown__trigger"
          data-dropdown-trigger-mode="click"
          style="position: fixed; pointer-events: none; opacity: 0; visibility: hidden; width: 24px; height: 24px;">
      </div>

      <div class="dropdown__content be-toolbar">
        <div id="be-toolbar-dynamic-slot" class="be-toolbar__dynamic-area"></div>

        <div class="be-toolbar__separator"></div>

        <button type="button"
                class="dropdown__item be-toolbar__item be-toolbar__item--destructive"
                data-action="remove">
          <i class="fa-solid fa-trash-can"></i>
          <span class="dropdown__item-label">Xóa Block</span>
        </button>
      </div>
    `;

    document.body.appendChild(this.root);

    this.virtualTrigger = this.root.querySelector('.dropdown__trigger');
    this.dynamicArea = this.root.querySelector('#be-toolbar-dynamic-slot');
    this.#separator = this.root.querySelector('.be-toolbar__separator');
    this.#deleteBtn = this.root.querySelector('[data-action="remove"]');

    DropdownHandler.instance.register(this.root);
  }

  // ─── Listeners ────────────────────────────────────────────────────────────

  #setupListeners() {
    const { signal } = this.abortController;

    // Bus: khi block handle bị click
    this.bus.subscribe('toolbar:toggle', ({ block, anchorEl, selection, hideDefault }) => {
      this.toggle(block, anchorEl, selection, hideDefault);
    });

    // Xóa block — đóng dropdown ngay
    this.#deleteBtn.addEventListener('click', (e) => {
      e.preventDefault();
      if (!this.currentBlock) return;

      this.bus.dispatch('block:removed', { blockId: this.currentBlock.id });
      this.close(BlockToolbar.DROPDOWN_ID); // đóng sau action
    }, { signal });

    // Fallback: dropdown:select event từ DropdownHandler
    this.root.addEventListener('dropdown:select', (e) => {
      const { item } = e.detail;
      if (item.dataset.action === 'remove' && this.currentBlock) {
        this.bus.dispatch('block:removed', { blockId: this.currentBlock.id });
      }
    }, { signal });

    // Dynamic area: bắt các action tùy theo block type (vd: table:*)
    this.dynamicArea.addEventListener('click', (e) => {
      const item = e.target.closest('.dropdown__item');
      if (!item || 'disabled' in item.dataset) return;

      const action = item.dataset.action;
      if (!action || !this.currentBlock) return;

      // Dispatch action chung — Manager hoặc block tự xử lý
      this.bus.dispatch('block:action', {
        action,
        blockId: this.currentBlock.id,
        selection: this.#currentSelection,
      });

      this.dynamicArea.innerHTML = '';
      this.dynamicArea.style.display = 'none';

      // ĐÓNG NGAY sau khi chọn action — đây là điểm khác với InlineToolbar
      this.close(BlockToolbar.DROPDOWN_ID);
    }, { signal });
  }

  // ─── Public API ───────────────────────────────────────────────────────────

  /**
   * Đặt trigger vào vị trí anchor, điền dynamic content, rồi mở dropdown.
   * Được gọi từ bus event 'toolbar:toggle'.
   *
   * @param {EditorBlock} block
   * @param {HTMLElement} anchorEl       — drag handle element
   * @param {object|null} [selection]    — context row/col (table block)
   * @param {boolean}     [hideDefault]  — ẩn separator + delete button
   */
  toggle(block, anchorEl, selection = null, hideDefault = false) {
    this.currentBlock = block;
    this.#currentSelection = selection;

    // Hiện/ẩn default actions
    this.#separator.style.display = hideDefault ? 'none' : '';
    this.#deleteBtn.style.display = hideDefault ? 'none' : '';

    // Điền dynamic content từ block (nếu block implement getDynamicToolbar)
    if (typeof this.currentBlock.getDynamicToolbar === 'function') {
      const dynamicHTML = this.currentBlock.getDynamicToolbar(this.#currentSelection);
      this.dynamicArea.innerHTML = dynamicHTML;
      this.dynamicArea.style.display = dynamicHTML ? 'block' : 'none';

      // Re-register vì innerHTML thay đổi DOM bên trong dropdown
      DropdownHandler.instance.register(this.root);
    } else {
      this.dynamicArea.innerHTML = '';
      this.dynamicArea.style.display = 'none';
    }

    // Dùng method từ base class để đặt trigger position
    this.positionVirtualTrigger(anchorEl);

    // Dùng method từ base class để mở dropdown
    this.open(BlockToolbar.DROPDOWN_ID);
  }
}