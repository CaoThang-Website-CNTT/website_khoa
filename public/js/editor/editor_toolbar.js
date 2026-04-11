export class EditorBlockToolbar {
  #bus;
  #root;
  #virtualTrigger;
  #dynamicArea;
  #currentBlock = null;
  /** Lưu lại context (row/col index) */
  #currentSelection = null;
  #separator;
  #deleteBtn;

  static DROPDOWN_ID = 'be-block-toolbar';

  constructor(bus) {
    this.#bus = bus;
    this.#buildDOM();
    this.#setupListeners();
  }

  #buildDOM() {
    this.#root = document.createElement('div');
    this.#root.className = 'dropdown';
    this.#root.dataset.dropdownId = EditorBlockToolbar.DROPDOWN_ID;

    this.#root.innerHTML = `
      <div class="dropdown__trigger" 
          data-dropdown-trigger-mode="click" 
          style="position: fixed; pointer-events: none; opacity: 0; visibility: hidden; width: 24px; height: 24px;">
      </div>
      
        <div class="dropdown__content be-toolbar">
      
      <div id="be-toolbar-dynamic-slot" class="be-toolbar__dynamic-area"></div>
      
      <div class="be-toolbar__separator"></div>
      
      <button type="button" class="dropdown__item be-toolbar__item be-toolbar__item--destructive" data-action="remove">
        <i class="fa-solid fa-trash-can"></i>
        <span class="dropdown__item-label">Xóa Block</span>
      </button>
    </div>
    `;

    document.body.appendChild(this.#root);

    this.#virtualTrigger = this.#root.querySelector('.dropdown__trigger');
    this.#dynamicArea = this.#root.querySelector('#be-toolbar-dynamic-slot');
    this.#separator = this.#root.querySelector('.be-toolbar__separator');
    this.#deleteBtn = this.#root.querySelector('[data-action="remove"]');

    DropdownHandler.instance.register(this.#root);
  }

  #setupListeners() {
    this.#bus.subscribe('toolbar:toggle', ({ block, anchorEl, selection, hideDefault }) => {
      this.toggle(block, anchorEl, selection, hideDefault);
    });

    this.#root.addEventListener('dropdown:select', (e) => {
      const { item } = e.detail;
      const action = item.dataset.action;

      if (item.dataset.action === 'remove' && this.#currentBlock) {
        this.#bus.dispatch('block:removed', { blockId: this.#currentBlock.id });
      }
    });

    this.#dynamicArea.addEventListener('click', (e) => {
      const item = e.target.closest('.dropdown__item');

      if (!item || 'disabled' in item.dataset) return;

      const action = item.dataset.action;

      // Nếu là action của table, báo về cho Table xử lý
      if (action && action.startsWith('table:') && this.#currentBlock) {
        this.#currentBlock.handleToolbarAction(action, this.#currentSelection);

        // Đóng dropdown sau khi xử lý xong
        DropdownHandler.instance.close(EditorBlockToolbar.DROPDOWN_ID);
      }
    });
  }

  /**
   * Tính toán tọa độ và mở dropdown
   */
  toggle(block, anchorEl, selection = null, hideDefault = false) {
    this.#currentBlock = block;
    this.#currentSelection = selection;

    if (hideDefault) {
      this.#separator.style.display = 'none';
      this.#deleteBtn.style.display = 'none';
    } else {
      this.#separator.style.display = '';
      this.#deleteBtn.style.display = '';
    }

    if (this.#currentBlock && typeof this.#currentBlock.getDynamicToolbar === 'function') {
      const dynamicHTML = this.#currentBlock.getDynamicToolbar(this.#currentSelection);
      this.#dynamicArea.innerHTML = dynamicHTML;
      this.#dynamicArea.style.display = dynamicHTML ? 'block' : 'none';

      DropdownHandler.instance.register(this.#root);
    } else {
      this.#dynamicArea.innerHTML = '';
      this.#dynamicArea.style.display = 'none';
    }

    const rect = anchorEl.getBoundingClientRect();
    this.#virtualTrigger.style.top = `${rect.top}px`;
    this.#virtualTrigger.style.left = `${rect.left + rect.width}px`;

    DropdownHandler.instance.open(EditorBlockToolbar.DROPDOWN_ID);
  }
}