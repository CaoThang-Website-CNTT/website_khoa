document.addEventListener('DOMContentLoaded', () => {
  new PopoverHandler().init();
});
class PopoverHandler {
  // Safe margin tối thiểu giữa panel và mép viewport (px)
  static VIEWPORT_MARGIN = 8;
  // Khoảng cách giữa trigger và content panel (px)
  static GAP = 4;
  #ignoreGlobalClick = false;

  static #instance = null;


  constructor() {
    if (PopoverHandler.#instance) return PopoverHandler.#instance;

    this._roots = document.querySelectorAll('.popover');
    this._instances = new Map();
    PopoverHandler.#instance = this;
  }

  /**
   * Global access point
   */
  static get instance() {
    return PopoverHandler.#instance || new PopoverHandler();
  }

  init() {
    this._roots.forEach(root => this._initRoot(root));
    this._bindGlobalListeners();
  }

  /**
   * Đăng ký thêm popover được render động sau khi init()
   * @param {HTMLElement} root - Phần tử .popover
   */
  register(root) {
    this._initRoot(root);
  }

  open(id) {
    const inst = this._instances.get(id);
    if (inst) {
      this.#ignoreGlobalClick = true;

      this._open(inst);

      setTimeout(() => {
        this.#ignoreGlobalClick = false;
      }, 0);
    }
  }

  close(id) {
    const inst = this._instances.get(id);
    if (inst) this._close(inst);
  }

  closeAll() {
    this._instances.forEach(inst => {
      if (inst.isOpen) this._close(inst);
    });
  }

  _initRoot(root) {
    const id = root.dataset.popoverId || (root.dataset.popoverId = crypto.randomUUID());
    const trigger = root.querySelector(':scope > .popover__trigger') || root.querySelector('.popover__trigger');
    const content = root.querySelector(':scope > .popover__content') || root.querySelector('.popover__content');

    if (!trigger || !content) {
      console.warn('[PopoverHandler] Missing trigger or content in', root);
      return;
    }

    if (this._instances.has(id)) return;

    const instance = {
      id,
      root,
      trigger,
      content,
      isOpen: false,
      preferredSide: root.dataset.side || 'bottom',
      preferredAlign: root.dataset.align || 'center'
    };

    this._instances.set(id, instance);

    // Setup ARIA accessibility
    trigger.setAttribute('aria-haspopup', 'dialog');
    trigger.setAttribute('aria-expanded', 'false');
    trigger.setAttribute('aria-controls', `popover-content-${id}`);
    content.id = `popover-content-${id}`;
    content.setAttribute('role', 'dialog');

    this._bindTrigger(instance);
  }

  _bindTrigger(instance) {
    instance.trigger.addEventListener('click', (e) => {
      e.stopPropagation();
      instance.isOpen ? this._close(instance) : this._open(instance);
    });
  }

  _bindGlobalListeners() {
    document.addEventListener('click', (e) => {
      if (this.#ignoreGlobalClick) return;

      const clickedInsideAny =
        e.target.closest('.popover__content') ||
        e.target.closest('.popover__trigger');

      if (!clickedInsideAny) {
        this._instances.forEach(inst => {
          if (inst.isOpen) this._close(inst);
        });
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key !== 'Escape') return;
      const openInstances = [...this._instances.values()].filter(inst => inst.isOpen);
      if (openInstances.length > 0) {
        this._close(openInstances[openInstances.length - 1]);
      }
    });

    const reposition = () => {
      this._instances.forEach(inst => {
        if (inst.isOpen) this._position(inst);
      });
    };
    window.addEventListener('scroll', reposition, { passive: true, capture: true });
    window.addEventListener('resize', reposition, { passive: true });
  }

  _open(instance) {
    document.body.appendChild(instance.content);

    // Đóng các popover khác
    this._instances.forEach(other => {
      if (other !== instance && other.isOpen) this._close(other);
    });

    instance.isOpen = true;
    instance.trigger.setAttribute('aria-expanded', 'true');


    instance.content.dataset.state = 'open';
    instance.trigger.dataset.state = 'open';

    this._position(instance);
  }

  _close(instance) {
    if (!instance.isOpen) return;

    instance.isOpen = false;
    instance.trigger.setAttribute('aria-expanded', 'false');

    instance.content.dataset.state = 'closing';
    instance.trigger.dataset.state = 'closed';
  }

  _position(instance) {
    const { content, trigger, preferredSide, preferredAlign } = instance;

    const trigRect = trigger.getBoundingClientRect();
    const vw = window.innerWidth;
    const vh = window.innerHeight;

    // Lấy kích thước thực tế của content
    const contentRect = content.getBoundingClientRect();

    // Flip logic
    const side = this._resolveSide(preferredSide, trigRect, contentRect, vw, vh);

    let { top, left } = this._computeCoords(side, preferredAlign, trigRect, contentRect);

    const vm = PopoverHandler.VIEWPORT_MARGIN;
    left = Math.max(vm, Math.min(left, vw - contentRect.width - vm));
    top = Math.max(vm, Math.min(top, vh - contentRect.height - vm));

    content.style.position = 'fixed';
    content.style.top = `${Math.round(top)}px`;
    content.style.left = `${Math.round(left)}px`;

    content.dataset.side = side;
    content.dataset.align = preferredAlign;
  }

  _resolveSide(preferred, trigRect, contentRect, vw, vh) {
    const gap = PopoverHandler.GAP;
    const vm = PopoverHandler.VIEWPORT_MARGIN;

    const available = {
      top: trigRect.top - gap - vm,
      bottom: vh - trigRect.bottom - gap - vm,
      left: trigRect.left - gap - vm,
      right: vw - trigRect.right - gap - vm,
    };

    const needed = {
      top: contentRect.height,
      bottom: contentRect.height,
      left: contentRect.width,
      right: contentRect.width,
    };

    const opposite = { top: 'bottom', bottom: 'top', left: 'right', right: 'left' };

    if (available[preferred] >= needed[preferred]) return preferred;
    if (available[opposite[preferred]] >= needed[opposite[preferred]]) return opposite[preferred];
    return preferred; // Ép về mặc định nếu cả 2 hướng đều tràn
  }

  _computeCoords(side, align, trigRect, contentRect) {
    const gap = PopoverHandler.GAP;
    let top = 0, left = 0;

    switch (side) {
      case 'bottom':
        top = trigRect.bottom + gap;
        left = this._crossAxisOffset('x', align, trigRect, contentRect);
        break;
      case 'top':
        top = trigRect.top - contentRect.height - gap;
        left = this._crossAxisOffset('x', align, trigRect, contentRect);
        break;
      case 'left':
        left = trigRect.left - contentRect.width - gap;
        top = this._crossAxisOffset('y', align, trigRect, contentRect);
        break;
      case 'right':
        left = trigRect.right + gap;
        top = this._crossAxisOffset('y', align, trigRect, contentRect);
        break;
    }

    return { top, left };
  }

  _crossAxisOffset(axis, align, trigRect, contentRect) {
    if (axis === 'x') {
      if (align === 'start') return trigRect.left;
      if (align === 'end') return trigRect.right - contentRect.width;
      /* center */ return trigRect.left + trigRect.width / 2 - contentRect.width / 2;
    } else {
      if (align === 'start') return trigRect.top;
      if (align === 'end') return trigRect.bottom - contentRect.height;
      /* center */ return trigRect.top + trigRect.height / 2 - contentRect.height / 2;
    }
  }
}