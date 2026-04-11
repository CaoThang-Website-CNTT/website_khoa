/**
 * EditorBlockToolbar (Block-Driven Architecture)
 *
 * Chỉ chịu trách nhiệm:
 * 1. Nhận sự kiện 'block:handle_click' -> Hiện nút Xóa block.
 * 2. Nhận sự kiện 'toolbar:request_dynamic' -> Hiện các nút do Block TỰ ĐỊNH NGHĨA.
 * 3. Bấm nút -> Đẩy ngược (delegate) action về cho Block đó xử lý.
 */
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

  #anchorEl = null;

  #dynamicCtx = null;

  #offset = 8;

  constructor(bus, canvas) {
    this.#bus = bus;
    this.#canvas = canvas;
    this.#createPortal();
    this.#initEvents();
  }

  // ─── Portal ───────────────────────────────────────────────────

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
      backgroundColor: 'transparent',
      pointerEvents: 'none',
    });

    this.#portalEl.innerHTML = `
      <div class="be-toolbar" style="position: absolute; pointer-events: all;">
        <div class="be-toolbar__inner">
          <div class="be-toolbar__group" id="be-toolbar-dynamic-area"></div>
          
          <div class="be-toolbar__group" id="be-toolbar-block-actions">
            <button class="be-toolbar__btn" data-be-toolbar-action="remove-block" title="Xóa block">
              <i class="fa-solid fa-trash-can"></i>
            </button>
          </div>
        </div>
      </div>
    `;

    this.#dynamicArea = this.#portalEl.querySelector('#be-toolbar-dynamic-area');
    this.#toolbarBox = this.#portalEl.querySelector('.be-toolbar');
    document.body.appendChild(this.#portalEl);
  }

  #initEvents() {
    this.#bus.subscribe('block:handle_click', ({ blockId }) => {
      const block = this.#canvas.getBlock(blockId);
      if (!block) return;
      this.#showBlockMode(block);
    });

    this.#bus.subscribe('toolbar:request_dynamic', ({ block, anchorEl, controls, context }) => {
      this.#currentBlock = block;
      this.#showDynamicMode({ anchorEl, controls, context });
    });

    this.#bus.subscribe('toolbar:hide_dynamic', () => {
      this.hide();
    });

    this.#toolbarBox.addEventListener('mousedown', (e) => {
      const btn = e.target.closest('button[data-be-toolbar-action]');
      if (!btn) return;

      e.preventDefault(); // Ngăn mất focus ở Canvas

      const action = btn.dataset.beToolbarAction;
      this.#handleAction(action);
    });
  }

  #showBlockMode(block) {
    this.#currentBlock = block;
    this.#anchorEl = block.dom;
    this.#dynamicCtx = null;

    this.#dynamicArea.innerHTML = '';

    this.#portalEl.style.display = 'block';
    this.#updatePosition();
  }

  #showDynamicMode({ anchorEl, controls, context }) {
    this.#anchorEl = anchorEl;
    this.#dynamicCtx = context; // Lưu lại ngữ cảnh (ví dụ: { row: 1, col: 2 })

    this.#dynamicArea.innerHTML = '';

    if (controls && controls.length > 0) {
      this.#dynamicArea.appendChild(this.#buildDynamicActions(controls));
    }

    this.#portalEl.style.display = 'block';
    this.#updatePosition();
  }

  #buildDynamicActions(controls) {
    const fragment = document.createDocumentFragment();
    const group = document.createElement('div');
    group.className = 'be-toolbar__group';

    controls.forEach(item => {
      if (item.divider) {
        const sep = document.createElement('div');
        sep.className = 'be-toolbar__separator';
        group.appendChild(sep);
        return;
      }

      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = `be-toolbar__btn${item.danger ? ' be-toolbar__btn--danger' : ''}`;
      btn.dataset.beToolbarAction = item.action;
      btn.title = item.title;
      btn.innerHTML = `<i class="${item.icon}"></i>`;
      group.appendChild(btn);
    });

    fragment.appendChild(group);
    return fragment;
  }

  #handleAction(action) {
    if (action === 'remove-block') {
      if (!this.#currentBlock) return;
      this.#bus.dispatch('block:remove_request', { blockId: this.#currentBlock.id });
      this.hide();
      return;
    }

    if (this.#currentBlock && typeof this.#currentBlock.handleToolbarAction === 'function') {
      this.#currentBlock.handleToolbarAction(action, this.#dynamicCtx);
    }
  }

  #updatePosition() {
    if (!this.#anchorEl || !this.#toolbarBox) return;

    const anchorRect = this.#anchorEl.getBoundingClientRect();
    const toolbarRect = this.#toolbarBox.getBoundingClientRect();
    const offset = this.#offset;

    const cannotFitAbove = anchorRect.top < (toolbarRect.height + offset);

    const top = cannotFitAbove
      ? anchorRect.bottom + offset
      : anchorRect.top - toolbarRect.height - offset;

    let left = anchorRect.left + (anchorRect.width / 2) - (toolbarRect.width / 2);
    left = Math.max(offset, Math.min(left, window.innerWidth - toolbarRect.width - offset));

    this.#toolbarBox.style.top = `${top}px`;
    this.#toolbarBox.style.left = `${left}px`;
  }

  hide() {
    this.#portalEl.style.display = 'none';
    this.#dynamicCtx = null;
    this.#anchorEl = null;
  }

  updatePosition() {
    this.#updatePosition();
  }
}