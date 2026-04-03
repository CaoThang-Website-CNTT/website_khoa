/**
 * ================================================
 * THƯ VIỆN DRAG & DROP (DND) - Vanilla JavaScript
 * ================================================
 * 
 * Hỗ trợ kéo thả bằng chuột + cảm ứng (touch)
 * Tính năng chính:
 *   - Draggable, Droppable, Sortable
 *   - Ghost element khi kéo
 *   - Optimistic reordering (sắp xếp ngay lập tức)
 *   - Hỗ trợ single list và multi-group
 * 
 * Cấu trúc file:
 *   1. Utilities & Helpers
 *   2. Sensor
 *   3. Collision Detection
 *   4. Registry
 *   5. DragDropManager (Core)
 */

/* ====================== UTILITIES ====================== */

/**
 * EventMonitor - Quản lý việc thêm, xóa và phát sự kiện
 * Sử dụng Map + Set để lưu listeners, hỗ trợ cleanup dễ dàng
 */
class EventMonitor {
  #listeners = new Map();

  /**
   * Thêm listener cho một loại sự kiện
   * @param {string} type - Tên sự kiện (dragstart, dragover, dragend, ...)
   * @param {Function} fn - Hàm callback
   * @returns {Function} Hàm để remove listener
   */
  addEventListener(type, fn) {
    if (!this.#listeners.has(type)) this.#listeners.set(type, new Set());
    this.#listeners.get(type).add(fn);
    return () => this.removeEventListener(type, fn);
  }

  removeEventListener(type, fn) {
    this.#listeners.get(type)?.delete(fn);
  }

  /**
   * Phát sự kiện đến tất cả listener
   * @param {string} type 
   * @param {*} event 
   * @returns {*}
   */
  dispatch(type, event) {
    for (const fn of this.#listeners.get(type) ?? []) fn(event);
    return event;
  }
}

/* ====================== SENSOR ====================== */

/**
 * PointerSensor - Xử lý tương tác pointer (mouse + touch)
 * Hỗ trợ:
 *   - Mouse: kéo sau khi di chuyển vượt ngưỡng
 *   - Touch: giữ một lúc rồi mới kích hoạt kéo (tránh conflict với scroll)
 */
class PointerSensor {
  static MOUSE_THRESHOLD = 5;   // px trước khi mouse drag kích hoạt
  static TOUCH_DELAY = 200;     // ms giữ để touch drag kích hoạt
  static TOUCH_TOLERANCE = 8;   // px di chuyển tối đa khi đang hold touch

  #manager;
  #cleanups = new WeakMap();

  constructor(manager) {
    this.#manager = manager;
  }

  /**
   * Gắn sensor vào một draggable/sortable
   * @param {Draggable|Sortable} draggable 
   */
  attach(draggable) {
    const el = draggable.handle ?? draggable.element;
    if (!el) return;
    const onDown = (e) => this.#onPointerDown(e, draggable);
    el.addEventListener("pointerdown", onDown);
    this.#cleanups.set(draggable, () =>
      el.removeEventListener("pointerdown", onDown),
    );
  }

  detach(draggable) {
    this.#cleanups.get(draggable)?.();
    this.#cleanups.delete(draggable);
  }

  /**
   * Xử lý khi pointerdown (bắt đầu tương tác)
   * @private
   */
  #onPointerDown(e, draggable) {
    if (e.pointerType === "mouse" && e.button !== 0) return;
    if (draggable.disabled) return;
    e.preventDefault();

    const isTouch = e.pointerType === "touch";
    const origin = { x: e.clientX, y: e.clientY };
    let activated = false;
    let holdTimer = null;

    const activate = () => {
      if (activated) return;
      activated = true;
      this.#manager._startDrag(draggable, origin, e);
    };

    if (isTouch) holdTimer = setTimeout(activate, PointerSensor.TOUCH_DELAY);

    const onMove = (ev) => {
      const dist = Math.hypot(ev.clientX - origin.x, ev.clientY - origin.y);
      if (isTouch && !activated) {
        if (dist > PointerSensor.TOUCH_TOLERANCE) {
          clearTimeout(holdTimer);
          done();
        }
        return;
      }
      if (!isTouch && !activated && dist > PointerSensor.MOUSE_THRESHOLD)
        activate();
      if (activated) this.#manager._moveDrag(ev);
    };

    const onUp = (ev) => {
      clearTimeout(holdTimer);
      done();
      if (activated) this.#manager._endDrag(ev, false);
    };

    const onCancel = (ev) => {
      clearTimeout(holdTimer);
      done();
      if (activated) this.#manager._endDrag(ev, true);
    };

    const done = () => {
      window.removeEventListener("pointermove", onMove);
      window.removeEventListener("pointerup", onUp);
      window.removeEventListener("pointercancel", onCancel);
    };

    window.addEventListener("pointermove", onMove, { passive: false });
    window.addEventListener("pointerup", onUp);
    window.addEventListener("pointercancel", onCancel);
  }

  destroy() { }
}

/* ====================== COLLISION DETECTION ====================== */

/**
 * Collision - Các thuật toán phát hiện va chạm giữa ghost và droppable
 */
class Collision {
  /**
   * Tính diện tích giao nhau giữa ghostRect và các droppable
   * Trả về id của droppable có diện tích giao nhau lớn nhất
   * @param {DOMRect} ghostRect 
   * @param {Map<string, DOMRect>} droppableRects 
   * @returns {string|null}
   */
  static rectIntersection(ghostRect, droppableRects) {
    let best = null,
      bestArea = 0;
    for (const [id, rect] of droppableRects) {
      const ox = Math.max(
        0,
        Math.min(ghostRect.right, rect.right) -
        Math.max(ghostRect.left, rect.left),
      );
      const oy = Math.max(
        0,
        Math.min(ghostRect.bottom, rect.bottom) -
        Math.max(ghostRect.top, rect.top),
      );
      const area = ox * oy;
      if (area > bestArea) {
        bestArea = area;
        best = id;
      }
    }
    return best;
  }

  /**
   * Tìm droppable gần nhất với trung tâm của ghost
   * Thường cho kết quả mượt mà hơn rectIntersection
   * @param {DOMRect} ghostRect 
   * @param {Map<string, DOMRect>} droppableRects 
   * @returns {string|null}
   */
  static closestCenter(ghostRect, droppableRects) {
    const cx = (ghostRect.left + ghostRect.right) / 2;
    const cy = (ghostRect.top + ghostRect.bottom) / 2;
    let best = null,
      bestDist = Infinity;
    for (const [id, rect] of droppableRects) {
      const d = Math.hypot(
        cx - (rect.left + rect.right) / 2,
        cy - (rect.top + rect.bottom) / 2,
      );
      if (d < bestDist) {
        bestDist = d;
        best = id;
      }
    }
    return best;
  }
}

/**
 * Registry - Quản lý đăng ký tất cả draggable và droppable
 */
class Registry {
  draggables = new Map();
  droppables = new Map();

  /**
   * Đăng ký một entity (Draggable, Droppable hoặc Sortable)
   * @param {any} instance 
   * @returns {Function} Hàm unregister
   */
  register(instance) {
    if (instance._isDraggable) this.draggables.set(instance.id, instance);
    if (instance._isDroppable) this.droppables.set(instance.id, instance);
    return () => this.unregister(instance);
  }

  unregister(instance) {
    if (instance._isDraggable) this.draggables.delete(instance.id);
    if (instance._isDroppable) this.droppables.delete(instance.id);
  }

  /**
   * Lấy danh sách rect của tất cả droppable đang active
   * @returns {Map<string, DOMRect>}
   */
  getDroppableRects() {
    const map = new Map();
    for (const [id, d] of this.droppables)
      if (!d.disabled && d.element)
        map.set(id, d.element.getBoundingClientRect());
    return map;
  }
}
/* ====================== CORE MANAGER ====================== */

/**
 * DragDropManager - Trung tâm điều khiển toàn bộ hệ thống Drag & Drop
 * Quản lý: sensor, ghost, registry, operation state, và phát sự kiện
 */
class DragDropManager {
  monitor = new EventMonitor();
  registry = new Registry();

  /** 
   * Trạng thái kéo thả hiện tại 
   */
  dragOperation = {
    source: null,
    target: null,
    position: { initial: null, current: null },
    status: "idle",
    canceled: false,
  };

  #sensors = [];
  #ghostEl = null;
  #ghostOff = null;
  #autoScroller = null;

  /**
   * @param {Object}   [options]
   * @param {Array}    [options.sensors]            - Các sensor muốn dùng
   * @param {Function} [options.collisionDetection] - Thuật toán collision
   * @param {boolean}  [options.autoScroll]          - Tự cuộn khi kéo gần mép màn hình (mặc định true)
   * @param {number}   [options.scrollZone]          - Vùng kích hoạt scroll tính từ mép viewport (px, mặc định 80)
   * @param {number}   [options.scrollSpeed]         - Tốc độ cuộn tối đa (px/frame, mặc định 16)
   */
  constructor(options = {}) {
    const SensorClasses = options.sensors ?? [PointerSensor];
    this.#sensors = SensorClasses.map((S) => new S(this));
    this.collisionDetection =
      options.collisionDetection ?? Collision.closestCenter;

    this.#autoScroller = new AutoScroller({
      enabled: options.autoScroll ?? true,
      scrollZone: options.scrollZone ?? 80,
      scrollSpeed: options.scrollSpeed ?? 16,
    });

    // Tạo ghost element nếu chưa có
    this.#ghostEl = document.getElementById("dnd-ghost");
    if (!this.#ghostEl) {
      const ghostEl = document.createElement('div');
      ghostEl.id = 'dnd-ghost';
      this.#ghostEl = ghostEl;
      document.body.appendChild(ghostEl);
    }

    // Style cho ghost
    this.#ghostEl.style.pointerEvents = 'none';
    this.#ghostEl.style.position = 'fixed';
    this.#ghostEl.style.top = '0';
    this.#ghostEl.style.left = '0';
    this.#ghostEl.style.zIndex = '9999';
    this.#ghostEl.style.display = 'none';
  }

  // ====================== SENSOR ATTACH ======================
  _attachSensors(entity) {
    for (const s of this.#sensors) s.attach(entity);
  }
  _detachSensors(entity) {
    for (const s of this.#sensors) s.detach(entity);
  }

  // ====================== DRAG LIFECYCLE ======================
  /**
   * Bắt đầu kéo một draggable
   * @private
   */
  _startDrag(draggable, origin, nativeEvent) {
    const before = {
      operation: this.dragOperation,
      _prevented: false,
      preventDefault() {
        this._prevented = true;
      },
    };
    this.monitor.dispatch("beforedragstart", before);
    if (before._prevented) return;

    const op = this.dragOperation;
    op.source = draggable;
    op.target = null;
    op.position = { initial: { ...origin }, current: { ...origin } };
    op.status = "dragging";
    op.canceled = false;

    draggable.element?.setAttribute('data-is-dragging', 'true');
    this.#spawnGhost(draggable.element, origin, draggable.handle);
    this.#autoScroller.start();
    this.monitor.dispatch("dragstart", { operation: op, nativeEvent });
  }

  /**
   * Khi đang kéo (pointermove)
   * @private
   */
  _moveDrag(nativeEvent) {
    if (this.dragOperation.status !== "dragging") return;
    const op = this.dragOperation;
    const pos = { x: nativeEvent.clientX, y: nativeEvent.clientY };
    op.position.current = pos;
    this.#moveGhost(pos);
    this.#autoScroller.update(pos);

    const ghostRect = this.#ghostEl.firstElementChild
      ? this.#ghostEl.firstElementChild.getBoundingClientRect()
      : { left: pos.x, top: pos.y, right: pos.x + 1, bottom: pos.y + 1 };

    const rects = this.registry.getDroppableRects();
    const overId = this.collisionDetection(ghostRect, rects);
    const over =
      overId != null ? (this.registry.droppables.get(overId) ?? null) : null;

    if (op.target?.id !== (over?.id ?? null)) {
      op.target?.element?.removeAttribute("data-dnd-overing");
      op.target = over;
      over?.element?.setAttribute("data-dnd-overing", "true");
      this.monitor.dispatch("dragover", { operation: op, nativeEvent });
    }

    this.monitor.dispatch("dragmove", {
      operation: op,
      nativeEvent,
      to: pos,
      by: {
        x: pos.x - op.position.initial.x,
        y: pos.y - op.position.initial.y,
      },
    });
  }

  /**
   * Kết thúc kéo (pointerup hoặc cancel)
   * @private
   */
  _endDrag(nativeEvent, canceled = false) {
    if (this.dragOperation.status !== "dragging") return;
    const op = this.dragOperation;
    op.status = "idle";
    op.canceled = canceled;

    op.source?.element?.removeAttribute("data-is-dragging");
    op.target?.element?.removeAttribute("data-dnd-overing");
    this.#autoScroller.stop();
    this.#destroyGhost();

    this.monitor.dispatch("dragend", {
      operation: {
        source: op.source,
        target: op.target,
        position: op.position,
        status: op.status,
        canceled,
      },
      canceled,
      nativeEvent,
    });

    // Reset sau dispatch
    op.source = null;
    op.target = null;
    op.position = { initial: null, current: null };
    op.canceled = false;
  }

  /**
   * Các action công khai để điều khiển từ bên ngoài (thường dùng bởi plugin)
   */
  get actions() {
    const op = this.dragOperation;
    return {
      /**
       * Đặt droppable đang được hover (thường dùng bởi OptimisticSortingPlugin)
       * @param {string|null} id 
       */
      setDropTarget: (id) => {
        op.target?.element?.removeAttribute("data-dnd-overing");
        op.target =
          id != null ? (this.registry.droppables.get(id) ?? null) : null;
        op.target?.element?.setAttribute("data-dnd-overing", "true");
      },
    };
  }

  // ====================== GHOST MANAGEMENT ======================

  /**
   * Tạo ghost element khi bắt đầu kéo.
   *
   * Nếu draggable có handle:
   *   Clone handle thay vì toàn bộ element — ghost nhỏ gọn,
   *   bám theo con trỏ tại đúng vị trí handle.
   *
   * Nếu không có handle:
   *   Clone toàn bộ element (hành vi gốc),
   *   bám tại điểm người dùng bấm xuống.
   *
   * @param {Element}      element - Element chính của draggable/sortable
   * @param {{x,y}}        origin  - Tọa độ con trỏ lúc pointerdown
   * @param {Element|null} handle  - Handle element nếu có, null nếu không
   */
  #spawnGhost(element, origin, handle = null) {
    if (!element || !this.#ghostEl) return;

    // Clone handle nếu có, toàn bộ element nếu không
    const source = element;
    const rect = source.getBoundingClientRect();
    const clone = source.cloneNode(true);

    clone.style.cssText = `width:${rect.width}px;height:${rect.height}px;pointer-events:none;user-select:none;`;

    this.#ghostEl.innerHTML = "";
    this.#ghostEl.appendChild(clone);
    this.#ghostEl.style.display = "block";

    // Offset tính từ góc trên-trái của element được clone (handle hoặc full)
    // Ghost sẽ luôn bám đúng vào điểm con trỏ bất kể dùng handle hay không
    this.#ghostOff = { x: origin.x - rect.left, y: origin.y - rect.top };
    this.#moveGhost(origin);
  }

  #moveGhost(pos) {
    if (!this.#ghostOff || !this.#ghostEl) return;
    this.#ghostEl.style.transform = `translate(${pos.x - this.#ghostOff.x}px,${pos.y - this.#ghostOff.y}px)`;
  }

  #destroyGhost() {
    if (this.#ghostEl) {
      this.#ghostEl.style.display = "none";
      this.#ghostEl.innerHTML = "";
    }
    this.#ghostOff = null;
  }

  /**
   * Đồng bộ lại index của tất cả sortable trong một group
   * Dùng sau khi thêm/xóa item bằng DOM thuần (không qua drag)
   */
  reindexGroup(group) {
    const items = [];
    for (const s of this.registry.draggables.values())
      if (isSortable(s) && s.group === group) items.push(s);

    items.sort((a, b) => {
      if (!a.element || !b.element) return a.index - b.index;
      const pos = a.element.compareDocumentPosition(b.element);
      return pos & Node.DOCUMENT_POSITION_FOLLOWING ? -1 : 1;
    });

    items.forEach((s, i) => {
      s.index = i;
      s.initialIndex = i;
    });
  }

  destroy() {
    this.#autoScroller.stop();
    for (const s of this.#sensors) s.destroy();
    for (const d of this.registry.draggables.values()) d.destroy?.();
    for (const d of this.registry.droppables.values()) d.destroy?.();
  }
}

/* ====================== ENTITIES ====================== */

/**
 * Draggable - Thực thể có thể kéo
 */
class Draggable {
  _isDraggable = true;
  _isDroppable = false;

  constructor(options, manager) {
    this.id = options.id;
    this.element = options.element ?? null;
    this.handle = options.handle ?? null;
    this.disabled = options.disabled ?? false;
    this.data = options.data ?? {};
    this._manager = manager;

    if (manager && options.register !== false) {
      manager.registry.register(this);
      if (this.element) manager._attachSensors(this);
    }
  }

  get isDragging() {
    return this._manager?.dragOperation.source?.id === this.id;
  }

  destroy() {
    this._manager?._detachSensors(this);
    this._manager?.registry.unregister(this);
  }
}

/**
 * Droppable - Thực thể có thể thả vào
 */
class Droppable {
  _isDraggable = false;
  _isDroppable = true;

  constructor(options, manager) {
    this.id = options.id;
    this.element = options.element ?? null;
    this.disabled = options.disabled ?? false;
    this.data = options.data ?? {};
    this._manager = manager;
    if (manager && options.register !== false) manager.registry.register(this);
  }

  get isOver() {
    return this._manager?.dragOperation.target?.id === this.id;
  }

  destroy() {
    this._manager?.registry.unregister(this);
  }
}

const _pluginAttached = new WeakSet();

/**
 * Sortable - Kết hợp cả Draggable + Droppable + tự động sắp xếp
 * Dùng cho danh sách có thể kéo thả để thay đổi thứ tự
 */
class Sortable {
  _isDraggable = true;
  _isDroppable = true;
  type = "sortable";

  constructor(options, manager) {
    this.id = options.id;
    this.element = options.element ?? null;
    this.handle = options.handle ?? null;
    this.group = options.group ?? "default";
    this.index = options.index ?? 0;
    this.initialIndex = this.index;
    this.initialGroup = this.group;
    this.disabled = options.disabled ?? false;
    this.data = options.data ?? {};
    this._manager = manager;

    manager.registry.register(this);
    if (this.element) manager._attachSensors(this);

    if (!_pluginAttached.has(manager)) {
      _pluginAttached.add(manager);
      manager.monitor.addEventListener("dragover", (e) =>
        OptimisticSortingPlugin.onDragOver(e, manager),
      );
    }
  }

  get isDragging() {
    return this._manager?.dragOperation.source?.id === this.id;
  }
  get isDropTarget() {
    return this._manager?.dragOperation.target?.id === this.id;
  }

  destroy() {
    this._manager?._detachSensors(this);
    this._manager?.registry.unregister(this);
  }
}

/**
 * Kiểm tra một instance có phải Sortable không
 * @param {*} x 
 * @returns {boolean}
 */
function isSortable(x) {
  return x?.type === "sortable";
}

/**
 * OptimisticSortingPlugin - Plugin tự động sắp xếp mượt mà
 * 
 * Đặc điểm chính:
 * - Reorder DOM ngay trong lúc kéo (dragover) thay vì chờ thả chuột
 * - Tạo animation đẩy item
 * - Hỗ trợ kéo trong cùng group và giữa các group khác nhau
 * - Luôn set target = source (đặc trưng của optimistic sorting)
 */
class OptimisticSortingPlugin {
  /**
   * Handler chính cho event dragover
   * @param {Object} event 
   * @param {DragDropManager} manager 
   */
  static onDragOver(event, manager) {
    const { source, target } = event.operation;
    if (!source || !target) return;
    if (!isSortable(source) || !isSortable(target)) return;
    if (source.id === target.id) return;

    if (source.group === target.group) {
      OptimisticSortingPlugin._reorderSameGroup(source, target, manager);
    } else {
      OptimisticSortingPlugin._transferCrossGroup(source, target, manager);
    }

    manager.actions.setDropTarget(source.id);
  }

  /**
   * Sắp xếp lại thứ tự trong cùng một group
   * @private
   */
  static _reorderSameGroup(source, target, manager) {
    const group = OptimisticSortingPlugin._groupItems(manager, source.group);
    const fromIdx = group.findIndex((s) => s.id === source.id);
    const toIdx = group.findIndex((s) => s.id === target.id);
    if (fromIdx === -1 || toIdx === -1) return;

    // FLIP bước 1: snapshot vị trí các sibling TRƯỚC khi DOM thay đổi
    const playAnimation = OptimisticSortingPlugin._animateDisplaced(
      group, fromIdx, toIdx, source.element
    );

    // DOM move
    const parent = target.element?.parentElement;
    if (parent && source.element && target.element) {
      if (fromIdx < toIdx) target.element.after(source.element);
      else parent.insertBefore(source.element, target.element);
    }

    // FLIP bước 2: đo vị trí mới, tính delta, chạy animation
    playAnimation();

    group.splice(fromIdx, 1);
    group.splice(toIdx, 0, source);
    group.forEach((s, i) => {
      s.index = i;
    });
  }

  /**
   * Chuyển item từ group này sang group khác (cross-group)
   * @private
   */
  static _transferCrossGroup(source, target, manager) {
    const srcItems = OptimisticSortingPlugin._groupItems(manager, source.group);
    const tgtItems = OptimisticSortingPlugin._groupItems(manager, target.group);
    const fromIdx = srcItems.findIndex((s) => s.id === source.id);
    const toIdx = tgtItems.findIndex((s) => s.id === target.id);
    if (fromIdx === -1 || toIdx === -1) return;

    const tgtParent = target.element?.parentElement;
    if (tgtParent && source.element && target.element) {
      tgtParent.insertBefore(source.element, target.element);
    }

    srcItems.splice(fromIdx, 1);
    srcItems.forEach((s, i) => {
      s.index = i;
    });

    tgtItems.splice(toIdx, 0, source);
    tgtItems.forEach((s, i) => {
      s.index = i;
    });

    source.group = target.group;
  }

  /**
   * Lấy danh sách sortable theo group và sắp xếp theo index
   * @private
   */
  static _groupItems(manager, group) {
    const items = [];
    for (const s of manager.registry.draggables.values())
      if (isSortable(s) && s.group === group) items.push(s);
    return items.sort((a, b) => a.index - b.index);
  }

  /**
   * FLIP animation cho các sibling bị đẩy khi reorder.
   *
   * Trả về callback — gọi SAU KHI DOM đã move.
   *
   * Tại sao phải dùng callback hai bước:
   *   - Nếu animate TRƯỚC DOM move: insertBefore/after gây reflow,
   *     reset toàn bộ transform đang set → animation bị hủy.
   *   - Nếu animate SAU DOM move mà không snapshot trước: không biết
   *     item đã dịch bao nhiêu px để tính delta.
   *   → Snapshot TRƯỚC, DOM move, rồi play SAU.
   *
   * @param {Array}   group    - Danh sách sortable trong group
   * @param {number}  fromIdx  - Vị trí cũ của source
   * @param {number}  toIdx    - Vị trí mới của source
   * @param {Element} sourceEl - Element của source (bỏ qua, không animate)
   * @returns {Function} Callback để gọi sau DOM move
   * @private
   */
  static _animateDisplaced(group, fromIdx, toIdx, sourceEl) {
    const lo = Math.min(fromIdx, toIdx);
    const hi = Math.max(fromIdx, toIdx);

    // Chỉ animate các sibling trong vùng bị ảnh hưởng, bỏ qua source
    const affected = group
      .slice(lo, hi + 1)
      .filter((s) => s.element && s.element !== sourceEl);

    // FIRST — đo vị trí trước khi DOM thay đổi
    const firstTops = new Map();
    for (const s of affected) {
      firstTops.set(s.id, s.element.getBoundingClientRect().top);
    }

    // Trả về callback để gọi SAU khi DOM đã move
    return () => {
      for (const s of affected) {
        const first = firstTops.get(s.id);
        if (first == null || !s.element) continue;

        // LAST — đo vị trí sau DOM move
        const last = s.element.getBoundingClientRect().top;
        const delta = first - last;
        if (Math.abs(delta) < 1) continue;

        // INVERT — snap về vị trí cũ, không transition
        s.element.style.transition = "none";
        s.element.style.transform = `translateY(${delta}px)`;

        // Force reflow — browser commit trạng thái invert trước khi tiếp tục
        void s.element.offsetHeight;

        // PLAY — bật transition, xóa offset → browser animate từ delta về 0
        s.element.style.transition = "transform 200ms ease";
        s.element.style.transform = "";

        // Dọn inline style sau khi animation kết thúc
        const el = s.element;
        const cleanup = () => {
          el.style.transition = "";
          el.style.transform = "";
          el.removeEventListener("transitionend", cleanup);
        };
        el.addEventListener("transitionend", cleanup);
      }
    };
  }
}
/* ====================== AUTO SCROLLER ====================== */

/**
 * AutoScroller - Tự động cuộn trang khi con trỏ gần mép trên/dưới màn hình.
 *
 * Cách hoạt động:
 *   - Chạy vòng lặp requestAnimationFrame trong suốt thời gian kéo
 *   - Tính "cường độ" cuộn tuyến tính: 0 ở rìa vùng, 1 tại mép màn hình
 *   - Gọi window.scrollBy() mỗi frame với số pixel tỉ lệ cường độ đó
 *
 * @param {Object}  options
 * @param {boolean} options.enabled     - Bật/tắt (mặc định true)
 * @param {number}  options.scrollZone  - Chiều cao vùng kích hoạt tính từ mép viewport (px)
 * @param {number}  options.scrollSpeed - Tốc độ cuộn tối đa (px/frame, 1 frame ≈ 16ms)
 */
class AutoScroller {
  #enabled;
  #scrollZone;
  #scrollSpeed;
  #rafId = null;
  #cursorY = 0;

  constructor(options = {}) {
    this.#enabled = options.enabled ?? true;
    this.#scrollZone = options.scrollZone ?? 80;
    this.#scrollSpeed = options.scrollSpeed ?? 16;
  }

  /** Gọi khi drag bắt đầu — khởi chạy RAF loop */
  start() {
    if (!this.#enabled || this.#rafId != null) return;
    this.#tick();
  }

  /** Gọi mỗi pointermove để cập nhật vị trí con trỏ */
  update(pos) {
    this.#cursorY = pos.y;
  }

  /** Gọi khi drag kết thúc hoặc manager bị destroy — dừng RAF loop */
  stop() {
    if (this.#rafId != null) {
      cancelAnimationFrame(this.#rafId);
      this.#rafId = null;
    }
  }

  #tick() {
    this.#rafId = requestAnimationFrame(() => {
      this.#scroll();
      this.#tick();
    });
  }

  #scroll() {
    const vh = window.innerHeight;
    const y = this.#cursorY;
    const zone = this.#scrollZone;
    let delta = 0;

    if (y < zone) {
      // Gần mép trên → cuộn lên; intensity = 1 tại y=0, giảm về 0 tại y=zone
      delta = -this.#scrollSpeed * (1 - y / zone);
    } else if (y > vh - zone) {
      // Gần mép dưới → cuộn xuống
      delta = this.#scrollSpeed * (1 - (vh - y) / zone);
    }

    if (delta !== 0) window.scrollBy({ top: delta, behavior: "instant" });
  }
}