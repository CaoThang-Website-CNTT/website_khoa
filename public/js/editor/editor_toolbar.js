/** Toolbar khi chọn 1 block */
export class EditorBlockToolbar {
  /** @type {EditorEventBus} */
  #bus;
  /** @type {EditorCanvas} */
  #canvas;
  /** @type {HTMLElement} */
  #portalEl;
  /** @type {HTMLElement} */
  #toolbarBox;
  /** @type {HTMLElement} */
  #dynamicArea;
  #currentBlock = null;

  /** Offset giữa block và toolbar */
  #offset = 8;

  constructor(bus, canvas) {
    this.#bus = bus;
    this.#canvas = canvas;

    // Tự động render UI khi khởi tạo
    this.#createPortal();
    this.#initEvents();
  }
  #createPortal() {
    this.#portalEl = document.createElement('div');
    this.#portalEl.id = 'be-toolbar-portal';
    this.#portalEl.className = 'be-toolbar-overlay';

    Object.assign(this.#portalEl.style, {
      position: 'fixed',
      top: '0',
      left: '0',
      width: '100vw',
      height: '100vh',
      display: 'none',
      zIndex: '9999',
      backgroundColor: 'transparent'
    });

    this.#portalEl.innerHTML = `
      <div class="be-toolbar" style="position: absolute;"> 
        <div class="be-toolbar__inner">
          <div class="be-toolbar__group">
          </div>
          <div class="be-toolbar__group" id="be-toolbar-dynamic-area"></div>
          <div class="be-toolbar__group">
            <button class="be-toolbar__btn" data-be-toolbar-action="remove-block"><i class="fa-solid fa-trash-can"></i></button>
          </div>
        </div>
      </div>
    `;

    this.#dynamicArea = this.#portalEl.querySelector('#be-toolbar-dynamic-area');
    this.#toolbarBox = this.#portalEl.querySelector('.be-toolbar');
    document.body.appendChild(this.#portalEl);
  }


  #initEvents() {
    this.#bus.subscribe('block:handle_click', ({ blockId, anchorEl }) => {
      const block = this.#canvas.getBlock(blockId);
      if (!block) return;

      this.show(block);
    });

    this.#portalEl.addEventListener('mousedown', (e) => {
      if (e.target === this.#portalEl) {
        this.hide();
      }
    });

    this.#toolbarBox.addEventListener('mousedown', (e) => {
      e.stopPropagation();
      const btn = e.target.closest('button');
      if (!btn) return;

      e.preventDefault();

      const action = btn.dataset.beToolbarAction;

      if (action === 'remove-block' && this.#currentBlock) {
        this.#bus.dispatch('block:remove_request', {
          blockId: this.#currentBlock.id
        });

        this.hide();
      }
    });
  }

  show(block) {
    this.#currentBlock = block;

    this.#dynamicArea.innerHTML = '';
    const customUI = null;
    if (customUI) {
      this.#dynamicArea.appendChild(customUI);
    }

    this.#portalEl.style.display = 'block';
    this.updatePosition();
  }

  hide() {
    this.#portalEl.style.display = 'none';
  }

  updatePosition() {
    if (!this.#currentBlock || !this.#currentBlock.dom || !this.#toolbarBox) return;

    const blockRect = this.#currentBlock.dom.getBoundingClientRect();
    const toolbarRect = this.#toolbarBox.getBoundingClientRect();
    const offset = this.#offset;

    // Kiểm tra không gian phía trên
    // Nếu khoảng cách từ block tới đỉnh màn hình nhỏ hơn chiều cao toolbar + offset
    const cannotFitAbove = blockRect.top < (toolbarRect.height + offset);

    let top;

    if (cannotFitAbove) {
      // FLIP: Render phía dưới block
      top = blockRect.bottom + offset;
    } else {
      // NORMAL: Render phía trên block
      top = blockRect.top - toolbarRect.height - offset;
    }

    // 2. Tính toán trục X (Căn giữa và chống tràn lề)
    let left = blockRect.left + (blockRect.width / 2) - (toolbarRect.width / 2);

    // Chống tràn mép trái
    left = Math.max(offset, left);

    // Chống tràn mép phải
    const maxLeft = window.innerWidth - toolbarRect.width - offset;
    left = Math.min(left, maxLeft);

    this.#toolbarBox.style.top = `${top}px`;
    this.#toolbarBox.style.left = `${left}px`;
  }
}