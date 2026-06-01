/**
 * ================================================
 * DnD.js - Drag & Drop Library, Vanilla JavaScript
 * ================================================
 *
 * Container-centric API, inspired by SortableJS.
 * Gọi new DnD(containerEl, options) - children tự động có thể kéo.
 *
 * Tính năng:
 *   - Same-list sort
 *   - Multi-list transfer (group)
 *   - Nested lists
 *   - Drag handle
 *   - Swap mode (plugin)
 *   - Grid sort (auto-detect hướng)
 *   - Ghost: full clone hoặc handle clone
 *   - Auto-scroll khi kéo gần mép màn hình
 *   - FLIP animation cho các items bị đẩy
 *   - Callbacks trên instance + DnDMonitor event bus
 *
 * Cấu trúc:
 *   1. EventBus          - global DnDMonitor
 *   2. Global state      - STATE, Ghost, Scroller
 *   3. flipSnapshot()    - FLIP animation helper
 *   4. PointerSensor     - mouse + touch input
 *   5. DnD               - container-centric orchestrator
 */

/* ================================================================
   1. EventBus - global monitor, dùng DnDMonitor.on / off / emit
   ================================================================ */
class EventBus {
  #listeners = new Map();

  /** Thêm listener, trả về hàm unsubscribe */
  on(type, fn) {
    if (!this.#listeners.has(type)) this.#listeners.set(type, new Set());
    this.#listeners.get(type).add(fn);
    return () => this.off(type, fn);
  }

  off(type, fn) { this.#listeners.get(type)?.delete(fn); }

  emit(type, event) {
    for (const fn of (this.#listeners.get(type) ?? [])) fn(event);
    return event;
  }
}

/** Singleton global bus */
const DnDMonitor = new EventBus();

/* ================================================================
   2. Global shared state + singletons
   ================================================================ */

/** Trạng thái drag hiện tại - chia sẻ giữa tất cả DnD instances */
const STATE = {
  active: null,   // DnD instance đang drag
  dragEl: null,   // Element đang kéo
  fromEl: null,   // Container gốc của drag
  fromIndex: -1,     // Vị trí ban đầu
  swapEl: null,   // Element đang bị highlight (swap mode)
  swapInst: null,   // DnD instance sở hữu swapEl
  ghostClass: null,   // ghostClass đã apply (để remove đúng)
  chosenClass: null,   // chosenClass đã apply
};

/** Registry: containerEl → DnD instance */
const _instances = new WeakMap();

/* ── Ghost overlay ────────────────────────────────────────────── */
const Ghost = (() => {
  let _el = null;
  let _off = null;   // { x, y } cursor offset → top-left của clone

  function ensureEl() {
    if (_el) return;
    _el = document.getElementById('dnd-ghost');
    if (!_el) {
      _el = document.createElement('div');
      _el.id = 'dnd-ghost';
      document.body.appendChild(_el);
    }
    // pointer-events:none là BẮT BUỘC - nếu không ghost chặn pointerup
    // và _endDrag không bao giờ được gọi (drop bị stuck)
    Object.assign(_el.style, {
      position: 'fixed',
      top: '0',
      left: '0',
      pointerEvents: 'none',
      zIndex: '9999',
      display: 'none',
    });
  }

  return {
    /**
     * Spawn ghost từ itemEl hoặc handle của nó.
     * @param {Element}      itemEl
     * @param {{x,y}}        origin        - cursor position khi pointerdown
     * @param {string|null}  handleSel     - CSS selector của handle
     * @param {boolean}      ghostHandle   - true → clone handle thay vì item
     */
    spawn(itemEl, origin, handleSel, ghostHandle) {
      ensureEl();
      const src = (ghostHandle && handleSel)
        ? (itemEl.querySelector(handleSel) ?? itemEl)
        : itemEl;
      const rect = src.getBoundingClientRect();
      const clone = src.cloneNode(true);
      clone.style.cssText =
        `width:${rect.width}px;height:${rect.height}px;` +
        `pointer-events:none;user-select:none;`;
      _el.innerHTML = '';
      _el.appendChild(clone);
      _el.style.display = 'block';
      _off = { x: origin.x - rect.left, y: origin.y - rect.top };
      Ghost.move(origin);
    },

    move(pos) {
      if (!_off || !_el) return;
      _el.style.transform =
        `translate(${pos.x - _off.x}px,${pos.y - _off.y}px)`;
    },

    destroy() {
      if (_el) { _el.style.display = 'none'; _el.innerHTML = ''; }
      _off = null;
    },
  };
})();

/* ── AutoScroller ─────────────────────────────────────────────── */
const Scroller = (() => {
  let _raf = null;
  let _y = 0;
  let _zone = 80;
  let _speed = 16;
  let _enabled = true;

  function tick() {
    _raf = requestAnimationFrame(() => { scroll(); tick(); });
  }
  function scroll() {
    const vh = window.innerHeight;
    let d = 0;
    if (_y < _zone) d = -_speed * (1 - _y / _zone);
    else if (_y > vh - _zone) d = _speed * (1 - (vh - _y) / _zone);
    if (d !== 0) window.scrollBy({ top: d, behavior: 'instant' });
  }

  return {
    configure(enabled, zone, speed) { _enabled = enabled; _zone = zone; _speed = speed; },
    start() { if (_enabled && _raf == null) tick(); },
    update(y) { _y = y; },
    stop() { if (_raf != null) { cancelAnimationFrame(_raf); _raf = null; } },
  };
})();

/* ================================================================
   3. flipSnapshot - FLIP animation helper
   Snapshot positions BEFORE DOM move, play animation AFTER.

   Lý do phải tách 2 bước:
   insertBefore/after gây reflow → reset mọi transform đang set.
   Phải snapshot "first" trước, DOM move, rồi tính delta và play.
   ================================================================ */
function flipSnapshot(container, excludeEl, duration) {
  const children = [...container.children].filter(c => c !== excludeEl);

  // FIRST - đo vị trí trước khi DOM thay đổi
  const tops = new Map(children.map(c => [c, c.getBoundingClientRect().top]));

  // Trả về callback - gọi SAU khi DOM đã move
  return function play() {
    for (const [child, first] of tops) {
      if (!child.isConnected) continue;
      const last = child.getBoundingClientRect().top;  // LAST
      const delta = first - last;
      if (Math.abs(delta) < 1) continue;

      // INVERT - snap về vị trí cũ, không transition
      child.style.transition = 'none';
      child.style.transform = `translateY(${delta}px)`;
      void child.offsetHeight;  // force reflow

      // PLAY - bật transition, animate về 0
      child.style.transition = `transform ${duration}ms ease`;
      child.style.transform = '';

      const cleanup = () => {
        child.style.transition = '';
        child.style.transform = '';
        child.removeEventListener('transitionend', cleanup);
      };
      child.addEventListener('transitionend', cleanup);
    }
  };
}

/* ================================================================
   4. PointerSensor - event delegation on container element.
   Một listener duy nhất trên container, xử lý tất cả children.
   Hỗ trợ mouse + touch (Pointer Events API).
   ================================================================ */
class PointerSensor {
  static MOUSE_THRESHOLD = 5;    // px before mouse drag activates
  static TOUCH_DELAY = 200;  // ms hold before touch drag activates
  static TOUCH_TOLERANCE = 8;    // px movement cancels touch hold

  /** @type {DnD} */ #dnd;
  #cleanup = null;

  constructor(dnd) { this.#dnd = dnd; }

  attach() {
    const handler = (e) => this.#onDown(e);
    this.#dnd.el.addEventListener('pointerdown', handler);
    this.#cleanup = () => this.#dnd.el.removeEventListener('pointerdown', handler);
  }

  detach() { this.#cleanup?.(); this.#cleanup = null; }

  #onDown(e) {
    if (e.pointerType === 'mouse' && e.button !== 0) return;

    const opts = this.#dnd.options;
    if (opts.disabled) return;

    // Event delegation - tìm item cha gần nhất trong container này
    const itemEl = this.#findItem(e.target);
    if (!itemEl) return;

    // Handle constraint: chỉ kéo được từ handle element
    if (opts.handle) {
      const h = itemEl.querySelector(opts.handle);
      if (!h || !h.contains(e.target)) return;
    }

    // Filter constraint: một số item không được kéo
    if (opts.filter && e.target.closest(opts.filter)) return;

    e.preventDefault();

    const isTouch = e.pointerType === 'touch';
    const origin = { x: e.clientX, y: e.clientY };
    let active = false;
    let timer = null;

    const activate = () => {
      if (active) return;
      active = true;
      this.#dnd._startDrag(itemEl, origin, e);
    };

    // Touch: activate sau delay, cancel nếu user di chuyển quá sớm
    if (isTouch) timer = setTimeout(activate, PointerSensor.TOUCH_DELAY);

    const onMove = (ev) => {
      const d = Math.hypot(ev.clientX - origin.x, ev.clientY - origin.y);
      if (isTouch && !active) {
        if (d > PointerSensor.TOUCH_TOLERANCE) { clearTimeout(timer); done(); }
        return;
      }
      if (!isTouch && !active && d > PointerSensor.MOUSE_THRESHOLD) activate();
      if (active) this.#dnd._moveDrag(ev);
    };

    const onUp = (ev) => { clearTimeout(timer); done(); if (active) this.#dnd._endDrag(ev, false); };
    const onCancel = (ev) => { clearTimeout(timer); done(); if (active) this.#dnd._endDrag(ev, true); };
    const done = () => {
      window.removeEventListener('pointermove', onMove);
      window.removeEventListener('pointerup', onUp);
      window.removeEventListener('pointercancel', onCancel);
    };

    window.addEventListener('pointermove', onMove, { passive: false });
    window.addEventListener('pointerup', onUp);
    window.addEventListener('pointercancel', onCancel);
  }

  /**
   * Tìm direct child của container này chứa e.target.
   * Quan trọng với nested lists: đảm bảo item thuộc đúng container.
   *
   * Walk từ e.target lên. Nếu gặp một nested DnD container (khác với
   * container của chúng ta) TRƯỚC KHI tìm được direct child → item này
   * thuộc nested instance, không phải chúng ta → return null.
   */
  #findItem(target) {
    const container = this.#dnd.el;
    const opts = this.#dnd.options;

    let node = target;

    // Walk up đến khi node là direct child của container
    while (node && node.parentElement !== container) {
      node = node.parentElement;
      if (!node) return null;

      // Nếu ta đi qua một element là root của nested DnD instance
      // (và nó không phải container của chúng ta), thì item này thuộc
      // nested → bail out. Note: ta kiểm tra PARENT vì ta vừa di lên.
      if (node !== container && _instances.has(node)) return null;
    }

    if (!node || node.parentElement !== container) return null;

    // Check draggable selector (strip :scope > prefix nếu có)
    if (opts.draggable) {
      const sel = opts.draggable.replace(/^:scope\s*>\s*/, '');
      if (sel && !node.matches(sel)) return null;
    }

    return node;
  }
}

/* ================================================================
   5. DnD - container-centric orchestrator

   Static:
     DnD.create(el, opts)  - factory
     DnD.get(el)           - lấy instance từ element

   Instance:
     option(key, val)      - get/set option
     toArray()             - lấy data-id của items theo thứ tự
     destroy()             - huỷ instance
   ================================================================ */
class DnD {

  /* ── static ───────────────────────────────────────────────── */

  static get(el) { return _instances.get(el); }
  static create(el, opts) { return new DnD(el, opts); }

  /* ── private fields ───────────────────────────────────────── */

  #el;       // container element
  #opts;     // resolved options object
  #sensor;   // PointerSensor

  /* ── constructor ──────────────────────────────────────────── */

  constructor(el, opts = {}) {
    this.#el = el;
    this.#opts = DnD.#resolveOpts(opts);
    this.#sensor = new PointerSensor(this);
    this.#sensor.attach();
    _instances.set(el, this);
  }

  /* ── public API ───────────────────────────────────────────── */

  get el() { return this.#el; }
  get options() { return this.#opts; }

  /** Get hoặc set option */
  option(key, val) {
    if (val === undefined) return this.#opts[key];
    this.#opts[key] = val;
    if (key === 'group') this.#opts.group = DnD.#resolveGroup(val);
  }

  /** Trả về mảng data-id của items theo thứ tự DOM hiện tại */
  toArray() {
    const sel = this.#opts.draggable?.replace(/^:scope\s*>\s*/, '') || null;
    return [...this.#el.children]
      .filter(c => !sel || c.matches(sel))
      .map(c => c.dataset.id ?? '');
  }

  /** Huỷ hoàn toàn instance - remove listeners, xoá khỏi registry */
  destroy() {
    this.#sensor.detach();
    _instances.delete(this.#el);
  }

  /* ── drag lifecycle (gọi bởi PointerSensor) ──────────────── */

  _startDrag(itemEl, origin, nativeEvent) {
    if (STATE.active) return;  // đang có drag khác

    STATE.active = this;
    STATE.dragEl = itemEl;
    STATE.fromEl = this.#el;
    STATE.fromIndex = this.#indexOf(itemEl, this.#el);
    STATE.ghostClass = this.#opts.ghostClass || null;
    STATE.chosenClass = this.#opts.chosenClass || null;

    if (STATE.ghostClass) itemEl.classList.add(STATE.ghostClass);
    if (STATE.chosenClass) itemEl.classList.add(STATE.chosenClass);

    Ghost.spawn(itemEl, origin, this.#opts.handle, this.#opts.ghostHandle);
    Scroller.configure(this.#opts.autoScroll, this.#opts.scrollZone, this.#opts.scrollSpeed);
    Scroller.start();

    this.#fire('start', { item: itemEl, from: this.#el, oldIndex: STATE.fromIndex, originalEvent: nativeEvent });
    DnDMonitor.emit('dragstart', { instance: this, item: itemEl, from: this.#el, oldIndex: STATE.fromIndex });
  }

  _moveDrag(nativeEvent) {
    if (!STATE.active || STATE.active !== this) return;

    const pos = { x: nativeEvent.clientX, y: nativeEvent.clientY };
    Ghost.move(pos);
    Scroller.update(pos.y);

    // Tìm DnD instance tại vị trí con trỏ
    const overInst = DnD.#instAtPoint(pos);
    if (overInst) overInst.#onOver(pos, nativeEvent);
  }

  _endDrag(nativeEvent, canceled) {
    if (!STATE.active || STATE.active !== this) return;

    const { dragEl, fromEl, fromIndex } = STATE;
    const toEl = dragEl.parentElement;
    const toIndex = this.#indexOf(dragEl, toEl);
    const srcInst = _instances.get(fromEl);
    const toInst = _instances.get(toEl);

    // Xoá classes - dùng STATE vì source/dest có thể khác options
    if (STATE.ghostClass) dragEl.classList.remove(STATE.ghostClass);
    if (STATE.chosenClass) dragEl.classList.remove(STATE.chosenClass);

    // Swap mode: thực hiện actual DOM swap lúc drop
    if (STATE.swapEl && STATE.swapInst) {
      const swapEl = STATE.swapEl;
      const swapInst = STATE.swapInst;
      swapEl.classList.remove(swapInst.options.swapClass);

      if (!canceled) {
        // Đổi chỗ hai elements trong DOM
        const swapContainer = swapEl.parentElement;
        const play = flipSnapshot(swapContainer, null, swapInst.options.animation);

        // Swap: insert dragEl trước swapEl, insert swapEl vào chỗ dragEl
        const dragNext = dragEl.nextSibling;
        const dragParent = dragEl.parentElement;
        swapContainer.insertBefore(dragEl, swapEl);
        if (dragNext) dragParent.insertBefore(swapEl, dragNext);
        else dragParent.appendChild(swapEl);

        play();
      }

      STATE.swapEl = null;
      STATE.swapInst = null;
    }

    Ghost.destroy();
    Scroller.stop();

    if (!canceled) {
      const moved = toEl !== fromEl;
      const sorted = !moved && toIndex !== fromIndex;

      if (moved) {
        // remove fires on the source container
        if (srcInst) srcInst.#fire('remove', { item: dragEl, from: fromEl, oldIndex: fromIndex });
        // add fires on the destination container
        if (toInst) toInst.#fire('add', { item: dragEl, to: toEl, newIndex: toIndex, from: fromEl });
      }

      if (sorted) {
        // sort/update fire on the container where reordering happened
        if (toInst) toInst.#fire('sort', { item: dragEl, from: fromEl, to: toEl, oldIndex: fromIndex, newIndex: toIndex });
        if (toInst) toInst.#fire('update', { item: dragEl, from: fromEl, to: toEl, oldIndex: fromIndex, newIndex: toIndex });
      }

      // end always fires on the instance that initiated the drag
      this.#fire('end', {
        item: dragEl, from: fromEl, to: toEl,
        oldIndex: fromIndex, newIndex: toIndex, originalEvent: nativeEvent,
      });
      DnDMonitor.emit('dragend', {
        instance: this, item: dragEl,
        from: fromEl, to: toEl,
        oldIndex: fromIndex, newIndex: toIndex, canceled,
      });
    }

    // Reset global state
    STATE.active = null;
    STATE.dragEl = null;
    STATE.fromEl = null;
    STATE.fromIndex = -1;
    STATE.ghostClass = null;
    STATE.chosenClass = null;
  }

  /* ── private: over handling ───────────────────────────────── */

  /**
   * Xử lý khi ghost đang di chuyển trên container này.
   * Sort mode: tìm vị trí chèn và move DOM optimistically.
   * Swap mode: highlight item sẽ bị swap.
   */
  #onOver(pos, nativeEvent) {
    const { dragEl } = STATE;
    if (!dragEl) return;
    if (!this.#canReceive()) return;
    // sort=false nghĩa là không cho sắp xếp lại trong cùng list
    if (!this.#opts.sort && this.#el === STATE.fromEl) return;

    const overItem = this.#closestItem(pos, dragEl);

    if (this.#opts.swap) {
      this.#doSwap(dragEl, overItem);
    } else {
      this.#doSort(dragEl, overItem, pos, nativeEvent);
    }
  }

  #doSort(dragEl, overItem, pos, nativeEvent) {
    // Xử lý khi di chuyển vào một container trống rỗng
    if (!overItem) {
      if (this.#el.children.length === 0) {
        const moveResult = this.#opts.onMove?.({
          to: this.#el, from: STATE.fromEl,
          dragged: dragEl, related: null,
          originalEvent: nativeEvent,
        });
        if (moveResult === false) return;

        const playAnim = flipSnapshot(this.#el, dragEl, this.#opts.animation);
        this.#el.appendChild(dragEl);
        playAnim();
      }
      return;
    }

    if (overItem === dragEl) return;

    // Không cho phép chèn phần tử cha vào bên trong chính con cháu của nó
    if (dragEl.contains(overItem)) return;

    // onMove callback - return false để cancel
    const moveResult = this.#opts.onMove?.({
      to: this.#el, from: STATE.fromEl,
      dragged: dragEl, related: overItem,
      originalEvent: nativeEvent,
    });
    if (moveResult === false) return;

    // Detect direction để quyết định insert trước hay sau
    const dir = this.#detectDir();
    const r = overItem.getBoundingClientRect();
    const before = dir === 'horizontal'
      ? pos.x < r.left + r.width / 2
      : pos.y < r.top + r.height / 2;

    // Bỏ qua nếu vị trí không thực sự thay đổi
    if (before && overItem.previousSibling === dragEl) return;
    if (!before && overItem.nextSibling === dragEl) return;

    // FLIP step 1 - snapshot trước DOM move
    const parent = overItem.parentElement ?? this.#el;
    const playAnim = flipSnapshot(parent, dragEl, this.#opts.animation);

    // DOM move
    if (before) parent.insertBefore(dragEl, overItem);
    else overItem.after(dragEl);

    // FLIP step 2 - play animation sau DOM move
    playAnim();
  }

  #doSwap(dragEl, overItem) {
    // Clear highlight của swap target cũ
    if (STATE.swapEl && STATE.swapEl !== overItem) {
      const swapClass = STATE.swapInst ? STATE.swapInst.options.swapClass : '';
      STATE.swapEl.classList.remove(swapClass);
      STATE.swapEl = null;
      STATE.swapInst = null;
    }
    // dragEl được truyền vào đúng - dùng param, không dùng STATE trực tiếp
    if (!overItem || overItem === dragEl) return;

    if (overItem !== STATE.swapEl) {
      overItem.classList.add(this.#opts.swapClass);
      STATE.swapEl = overItem;
      STATE.swapInst = this;
    }
  }

  /* ── private: utilities ───────────────────────────────────── */

  /**
   * Tìm direct child của container này gần nhất với pos.
   * Bỏ qua excludeEl (dragEl). Chỉ xét children match draggable selector.
   * Dùng khoảng cách tới tâm item theo hướng được detect.
   */
  #closestItem(pos, excludeEl) {
    const dir = this.#detectDir();
    const sel = this.#opts.draggable?.replace(/^:scope\s*>\s*/, '') || null;
    const items = [...this.#el.children].filter(c => {
      if (c === excludeEl) return false;
      if (sel && !c.matches(sel)) return false;
      return true;
    });
    if (!items.length) return null;

    let best = null, bestDist = Infinity;
    for (const item of items) {
      const r = item.getBoundingClientRect();
      const cx = (r.left + r.right) / 2;
      const cy = (r.top + r.bottom) / 2;
      const d = dir === 'horizontal'
        ? Math.abs(pos.x - cx)
        : Math.abs(pos.y - cy);
      if (d < bestDist) { bestDist = d; best = item; }
    }
    return best;
  }

  /**
   * Auto-detect layout direction.
   * So sánh top của 2 children đầu tiên (không tính dragEl):
   * - Nếu chúng nằm cùng hàng (top gần bằng nhau) → horizontal (grid/flex-row)
   * - Ngược lại → vertical (list)
   */
  #detectDir() {
    if (this.#opts.direction !== 'auto') return this.#opts.direction;
    const kids = [...this.#el.children].filter(c => c !== STATE.dragEl);
    if (kids.length < 2) return 'vertical';
    const r0 = kids[0].getBoundingClientRect();
    const r1 = kids[1].getBoundingClientRect();
    // Nếu hai item đầu tiên có top gần bằng nhau → chúng nằm cùng hàng
    return Math.abs(r0.top - r1.top) < r0.height / 2 ? 'horizontal' : 'vertical';
  }

  /**
   * Kiểm tra container này có thể nhận dragEl không.
   * Dựa vào group option:
   *   - Cùng container: phụ thuộc sort option
   *   - Khác container: cùng group name hoặc put cho phép
   */
  #canReceive() {
    // Ngăn chặn chèn phần tử đang kéo vào bên trong các container con/cháu của chính nó!
    if (STATE.dragEl && STATE.dragEl.contains(this.#el)) return false;

    const srcInst = _instances.get(STATE.fromEl);
    const myGroup = this.#opts.group;
    const srcGroup = srcInst?.options.group;

    if (this.#el === STATE.fromEl) return this.#opts.sort;
    if (!myGroup?.name || !srcGroup?.name) return false;

    const { put } = myGroup;
    if (put === false) return false;
    if (put === true) return true;
    if (Array.isArray(put)) return put.includes(srcGroup.name);
    return myGroup.name === srcGroup.name;
  }

  #indexOf(el, container) {
    if (!container) return -1;
    return [...container.children].indexOf(el);
  }

  /** Fire callback được cấu hình trong options */
  #fire(type, data) {
    const key = 'on' + type[0].toUpperCase() + type.slice(1);
    this.#opts[key]?.({ ...data, target: this.#el });
  }

  /* ── static utilities ─────────────────────────────────────── */

  /**
   * Tìm DnD instance tại điểm pos.
   * Tạm ẩn ghost để elementFromPoint không bị chặn.
   * Ưu tiên container sâu nhất (nested list).
   */
  static #instAtPoint(pos) {
    const gEl = document.getElementById('dnd-ghost');
    if (gEl) gEl.style.display = 'none';
    const el = document.elementFromPoint(pos.x, pos.y);
    if (gEl) gEl.style.display = 'block';
    if (!el) return null;

    // Walk up DOM tìm container có DnD instance
    let node = el;
    while (node) {
      // Hỗ trợ kéo thả làm con đệ quy (nested tree drop-zone trống)
      if (node.classList.contains('menu-item')) {
        const subContainer = node.querySelector('.menu-item-children');
        if (subContainer) {
          const subInst = _instances.get(subContainer);
          if (subInst && !subInst.options.disabled) {
            const rect = node.getBoundingClientRect();
            // Nếu chuột nằm lệch sang bên phải (> 22% chiều rộng của item cha)
            if (pos.x > rect.left + rect.width * 0.22) {
              return subInst; // Chuyển hướng mục tiêu sang container con đệ quy!
            }
          }
        }
      }

      const inst = _instances.get(node);
      if (inst && !inst.options.disabled) return inst;
      node = node.parentElement;
    }
    return null;
  }

  /** Merge user options với defaults */
  static #resolveOpts(opts) {
    const resolved = Object.assign({
      group: null,
      sort: true,
      disabled: false,
      handle: null,
      draggable: ':scope > *',
      filter: null,
      animation: 150,
      ghostClass: 'dnd-ghost',
      chosenClass: 'dnd-chosen',
      ghostHandle: false,
      swap: false,
      swapClass: 'dnd-swap',
      direction: 'auto',
      autoScroll: true,
      scrollZone: 80,
      scrollSpeed: 16,
      // callbacks
      onStart: null,
      onEnd: null,
      onSort: null,
      onAdd: null,
      onRemove: null,
      onMove: null,
      onUpdate: null,
    }, opts);
    resolved.group = DnD.#resolveGroup(opts.group);
    return resolved;
  }

  /** Normalise group: string → { name, pull, put } */
  static #resolveGroup(g) {
    if (!g) return { name: null, pull: false, put: false };
    if (typeof g === 'string') return { name: g, pull: true, put: true };
    return { name: g.name ?? null, pull: g.pull ?? true, put: g.put ?? true };
  }
}