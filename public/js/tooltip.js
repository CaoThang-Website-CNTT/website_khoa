document.addEventListener('DOMContentLoaded', () => {
  new TooltipHandler().init();
});

class TooltipHandler {
  static VIEWPORT_MARGIN = 8;
  static GAP = 4;
  static TOUCH_DELAY = 700;
  static TOUCH_DISMISS_DELAY = 1600;
  static TOUCH_MOVE_TOLERANCE = 10;
  static BASE_Z_INDEX = 100;
  static _zCounter = 0;

  static #instance = null;

  constructor() {
    if (TooltipHandler.#instance) return TooltipHandler.#instance;

    this._roots = document.querySelectorAll('.tooltip');
    this._instances = new Map();
    TooltipHandler.#instance = this;
  }

  static get instance() {
    return TooltipHandler.#instance || new TooltipHandler();
  }

  init() {
    this._roots.forEach(root => this._initRoot(root));
    this._bindGlobalListeners();
  }

  /**
   * Register a tooltip rendered dynamically after DOMContentLoaded.
   * @param {HTMLElement} root - The .tooltip element.
   */
  register(root) {
    this._initRoot(root);
  }

  unregister(rootOrId) {
    const id = typeof rootOrId === 'string' ? rootOrId : rootOrId?.dataset?.tooltipId;
    if (!id) return;

    const inst = this._instances.get(id);
    if (!inst) return;

    if (inst.listeners) {
      inst.trigger.removeEventListener('mouseenter', inst.listeners.mouseenter);
      inst.trigger.removeEventListener('mouseleave', inst.listeners.mouseleave);
      inst.trigger.removeEventListener('focus', inst.listeners.focus);
      inst.trigger.removeEventListener('blur', inst.listeners.blur);
      inst.trigger.removeEventListener('pointerdown', inst.listeners.pointerdown);
      inst.trigger.removeEventListener('pointermove', inst.listeners.pointermove);
      inst.trigger.removeEventListener('pointerup', inst.listeners.pointerup);
      inst.trigger.removeEventListener('pointercancel', inst.listeners.pointercancel);
      inst.trigger.removeEventListener('click', inst.listeners.click, true);
    }

    this._clearTouchState(inst);
    inst.content.remove();
    this._instances.delete(id);
  }

  open(id) {
    const inst = this._instances.get(id);
    if (inst) this._open(inst);
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
    const id = root.dataset.tooltipId || (root.dataset.tooltipId = crypto.randomUUID());

    if (this._instances.has(id)) return;

    const trigger = root.querySelector(':scope > .tooltip__trigger') || root.querySelector('.tooltip__trigger');
    const content = root.querySelector(':scope > .tooltip__content') || root.querySelector('.tooltip__content');

    if (!trigger || !content) {
      console.warn('[TooltipHandler] Missing trigger or content in', root);
      return;
    }

    const instance = {
      id,
      root,
      trigger,
      content,
      preferredSide: root.dataset.side || 'top',
      preferredAlign: root.dataset.align || 'center',
      touchDelay: Number(root.dataset.tooltipTouchDelay) || TooltipHandler.TOUCH_DELAY,
      touchDismissDelay: Number(root.dataset.tooltipTouchDismissDelay) || TooltipHandler.TOUCH_DISMISS_DELAY,
      isOpen: false,
      touchTimer: null,
      touchDismissTimer: null,
      touchStart: null,
      openedByTouch: false,
      suppressNextClick: false,
      lastTouchPointerAt: 0,
    };

    this._instances.set(id, instance);

    trigger.setAttribute('aria-describedby', `tooltip-content-${id}`);
    content.id = `tooltip-content-${id}`;
    content.setAttribute('role', 'tooltip');
    trigger.dataset.state = 'closed';
    content.dataset.state = 'closed';

    document.body.appendChild(content);
    this._bindTrigger(instance);
  }

  _bindTrigger(instance) {
    const { trigger } = instance;

    instance.listeners = {
      mouseenter: () => this._open(instance),
      mouseleave: () => this._close(instance),
      focus: () => this._handleFocus(instance),
      blur: () => this._close(instance),
      pointerdown: event => this._handlePointerDown(instance, event),
      pointermove: event => this._handlePointerMove(instance, event),
      pointerup: () => this._handlePointerEnd(instance),
      pointercancel: () => this._handlePointerEnd(instance),
      click: event => this._handleClick(instance, event),
    };

    trigger.addEventListener('mouseenter', instance.listeners.mouseenter);
    trigger.addEventListener('mouseleave', instance.listeners.mouseleave);
    trigger.addEventListener('focus', instance.listeners.focus);
    trigger.addEventListener('blur', instance.listeners.blur);
    trigger.addEventListener('pointerdown', instance.listeners.pointerdown);
    trigger.addEventListener('pointermove', instance.listeners.pointermove);
    trigger.addEventListener('pointerup', instance.listeners.pointerup);
    trigger.addEventListener('pointercancel', instance.listeners.pointercancel);
    trigger.addEventListener('click', instance.listeners.click, true);
  }

  _bindGlobalListeners() {
    document.addEventListener('keydown', event => {
      if (event.key === 'Escape') this.closeAll();
    });

    document.addEventListener('pointerdown', event => {
      this._instances.forEach(inst => {
        const clickedTrigger = inst.trigger === event.target || inst.trigger.contains(event.target);
        const clickedContent = inst.content === event.target || inst.content.contains(event.target);
        if (inst.isOpen && inst.openedByTouch && !clickedTrigger && !clickedContent) {
          this._close(inst);
        }
      });
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
    window.clearTimeout(instance.touchDismissTimer);
    instance.isOpen = true;
    instance.content.style.zIndex = TooltipHandler.BASE_Z_INDEX + (++TooltipHandler._zCounter);
    instance.content.dataset.state = 'open';
    instance.trigger.dataset.state = 'open';
    this._position(instance);
  }

  _close(instance) {
    if (!instance.isOpen) return;

    window.clearTimeout(instance.touchDismissTimer);
    instance.openedByTouch = false;
    instance.isOpen = false;
    instance.content.dataset.state = 'closed';
    instance.trigger.dataset.state = 'closed';
  }

  _handlePointerDown(instance, event) {
    if (!this._isTouchPointer(event)) return;

    instance.lastTouchPointerAt = Date.now();
    window.clearTimeout(instance.touchTimer);
    window.clearTimeout(instance.touchDismissTimer);
    instance.touchStart = { x: event.clientX, y: event.clientY };
    instance.openedByTouch = false;

    instance.touchTimer = window.setTimeout(() => {
      instance.openedByTouch = true;
      instance.suppressNextClick = true;
      this._open(instance);
    }, instance.touchDelay);
  }

  _handlePointerMove(instance, event) {
    if (!this._isTouchPointer(event) || !instance.touchStart) return;

    const deltaX = Math.abs(event.clientX - instance.touchStart.x);
    const deltaY = Math.abs(event.clientY - instance.touchStart.y);
    if (deltaX <= TooltipHandler.TOUCH_MOVE_TOLERANCE && deltaY <= TooltipHandler.TOUCH_MOVE_TOLERANCE) return;

    const wasOpenedByTouch = instance.openedByTouch;
    this._clearTouchState(instance);
    if (wasOpenedByTouch) this._close(instance);
  }

  _handlePointerEnd(instance) {
    window.clearTimeout(instance.touchTimer);
    instance.touchTimer = null;
    instance.touchStart = null;

    if (!instance.openedByTouch) return;

    window.clearTimeout(instance.touchDismissTimer);
    instance.touchDismissTimer = window.setTimeout(() => {
      this._close(instance);
    }, instance.touchDismissDelay);
  }

  _handleClick(instance, event) {
    if (!instance.suppressNextClick) return;

    event.preventDefault();
    event.stopPropagation();
    event.stopImmediatePropagation();
    instance.suppressNextClick = false;
  }

  _handleFocus(instance) {
    if (Date.now() - instance.lastTouchPointerAt < 800) return;
    this._open(instance);
  }

  _clearTouchState(instance) {
    window.clearTimeout(instance.touchTimer);
    window.clearTimeout(instance.touchDismissTimer);
    instance.touchTimer = null;
    instance.touchDismissTimer = null;
    instance.touchStart = null;
    instance.suppressNextClick = false;
  }

  _isTouchPointer(event) {
    return event.pointerType === 'touch' || event.pointerType === 'pen';
  }

  _position(instance) {
    const { content, trigger, preferredSide, preferredAlign } = instance;

    const triggerRect = trigger.getBoundingClientRect();
    const viewportWidth = window.innerWidth || document.documentElement.clientWidth;
    const viewportHeight = window.innerHeight || document.documentElement.clientHeight;

    const contentRect = this._measureContent(content);
    const side = this._resolveSide(preferredSide, triggerRect, contentRect, viewportWidth, viewportHeight);
    let { top, left } = this._computeCoords(side, preferredAlign, triggerRect, contentRect);

    const margin = TooltipHandler.VIEWPORT_MARGIN;
    left = Math.max(margin, Math.min(left, viewportWidth - contentRect.width - margin));
    top = Math.max(margin, Math.min(top, viewportHeight - contentRect.height - margin));

    content.style.position = 'fixed';
    content.style.top = `${Math.round(top)}px`;
    content.style.left = `${Math.round(left)}px`;
    content.dataset.side = side;
    content.dataset.align = preferredAlign;
  }

  _measureContent(content) {
    const previousVisibility = content.style.visibility;
    const previousDisplay = content.style.display;

    content.style.visibility = 'hidden';
    content.style.display = 'block';
    const rect = content.getBoundingClientRect();
    content.style.display = previousDisplay;
    content.style.visibility = previousVisibility;

    return rect;
  }

  _resolveSide(preferred, triggerRect, contentRect, viewportWidth, viewportHeight) {
    const gap = TooltipHandler.GAP;
    const margin = TooltipHandler.VIEWPORT_MARGIN;
    const opposite = { top: 'bottom', bottom: 'top', left: 'right', right: 'left' };
    const side = ['top', 'right', 'bottom', 'left'].includes(preferred) ? preferred : 'top';

    const available = {
      top: triggerRect.top - gap - margin,
      bottom: viewportHeight - triggerRect.bottom - gap - margin,
      left: triggerRect.left - gap - margin,
      right: viewportWidth - triggerRect.right - gap - margin,
    };

    const needed = {
      top: contentRect.height,
      bottom: contentRect.height,
      left: contentRect.width,
      right: contentRect.width,
    };

    if (available[side] >= needed[side]) return side;
    if (available[opposite[side]] >= needed[opposite[side]]) return opposite[side];
    return side;
  }

  _computeCoords(side, align, triggerRect, contentRect) {
    const gap = TooltipHandler.GAP;
    let top = 0;
    let left = 0;

    switch (side) {
      case 'bottom':
        top = triggerRect.bottom + gap;
        left = this._crossAxisOffset('x', align, triggerRect, contentRect);
        break;
      case 'left':
        left = triggerRect.left - contentRect.width - gap;
        top = this._crossAxisOffset('y', align, triggerRect, contentRect);
        break;
      case 'right':
        left = triggerRect.right + gap;
        top = this._crossAxisOffset('y', align, triggerRect, contentRect);
        break;
      case 'top':
      default:
        top = triggerRect.top - contentRect.height - gap;
        left = this._crossAxisOffset('x', align, triggerRect, contentRect);
        break;
    }

    return { top, left };
  }

  _crossAxisOffset(axis, align, triggerRect, contentRect) {
    const safeAlign = ['start', 'center', 'end'].includes(align) ? align : 'center';

    if (axis === 'x') {
      if (safeAlign === 'start') return triggerRect.left;
      if (safeAlign === 'end') return triggerRect.right - contentRect.width;
      return triggerRect.left + triggerRect.width / 2 - contentRect.width / 2;
    }

    if (safeAlign === 'start') return triggerRect.top;
    if (safeAlign === 'end') return triggerRect.bottom - contentRect.height;
    return triggerRect.top + triggerRect.height / 2 - contentRect.height / 2;
  }
}
