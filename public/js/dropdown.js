document.addEventListener('DOMContentLoaded', () => {
  new DropdownHandler().init();
});

/**
 * DropdownHandler - quản lý tất cả Dropdown Menu trên trang.
 *
 * Tất cả panel được portal vào <body> lúc init() - trước khi bất kỳ
 * hover event nào xảy ra - để e.relatedTarget luôn resolve đúng.
 *
 * Cấu hình trigger mode: data-dropdown-trigger-mode="hover|click"
 * Đặt trên .dropdown__trigger. Mặc định: "hover".
 *
 * Event khi click item: "dropdown:select" bubble từ .dropdown__item
 *   e.detail = { label, item }
 */
class DropdownHandler {
  // Khoảng cách giữa trigger và content panel (px)
  static ANCHOR_OFFSET = 4;

  // Khoảng cách giữa sub-trigger và sub-content panel (px)
  static PANEL_OFFSET = -2;

  // Safe margin tối thiểu giữa panel và mép viewport (px)
  static VIEWPORT_MARGIN = 8;

  // Z-index tối thiểu - panel mở sau luôn cao hơn panel mở trước
  static BASE_Z_INDEX = 100;
  static _zCounter = 0;

  static #instance = null;
  #ignoreGlobalClick = false;

  // Transform-origin cho từng hướng mở của panel
  origins = {
    bottom: 'top left',
    top: 'bottom left',
    right: 'top left',
    left: 'top right',
  };

  constructor() {
    if (DropdownHandler.#instance) return DropdownHandler.#instance;

    this._roots = document.querySelectorAll('.dropdown');
    this._instances = new Map();
    DropdownHandler.#instance = this;
  }

  static get instance() {
    return DropdownHandler.#instance || new DropdownHandler();
  }

  /**
   * Đăng ký dropdown được tạo động sau khi DOMContentLoaded
   * @param {HTMLElement} root - Phần tử .dropdown
   */
  register(root) {
    this._initRoot(root);
  }

  /* ------------------------------------------------------------------ */
  /* Public                                                               */
  /* ------------------------------------------------------------------ */
  init() {
    this._roots.forEach(root => this._initRoot(root));
    this._bindGlobalListeners();
  }

  /**
   * Mở dropdown bằng code (Programmatic)
   */
  open(id) {
    const inst = this._instances.get(id);
    if (inst) {
      // Khóa sự kiện global click để tránh bị đóng ngay lập tức (Event Bubbling Trap)
      this.#ignoreGlobalClick = true;
      this._open(inst);

      setTimeout(() => {
        this.#ignoreGlobalClick = false;
      }, 0);
    }
  }

  /**
   * Đóng dropdown bằng code
   */
  close(id) {
    const inst = this._instances.get(id);
    if (inst) this._close(inst);
  }

  /**
   * Đóng tất cả dropdown đang mở
   */
  closeAll() {
    this._instances.forEach(inst => {
      if (inst.content.dataset.state === 'open') this._close(inst);
    });
  }


  /**
   * Khởi tạo một .dropdown root: tạo instance object, thu thập sub,
   * portal tất cả panel vào body, sau đó gắn các event.
   * @private
   * @param {HTMLElement} root - Phần tử .dropdown cần khởi tạo.
   */
  _initRoot(root) {
    const id = root.dataset.dropdownId || (root.dataset.dropdownId = crypto.randomUUID());

    // Ngăn chặn khởi tạo 2 lần trên cùng 1 element
    if (this._instances.has(id)) return;

    const rootTrigger = root.querySelector(':scope > .dropdown__trigger');
    const rootContent = root.querySelector(':scope > .dropdown__content');
    const triggerMode = rootTrigger?.dataset.dropdownTriggerMode ?? 'hover';

    if (!rootTrigger || !rootContent) {
      console.warn('[DropdownHandler] Missing trigger or content in', root);
      return;
    }

    const instance = {
      id,
      root,
      trigger: rootTrigger,
      content: rootContent,
      mode: triggerMode,
      subs: [],
      activeSub: null,
      highlighted: null,
    };

    this._instances.set(id, instance);

    // Thu thập sub-menus
    rootContent.querySelectorAll('.dropdown__sub').forEach(subWrapper => {
      const subTrigger = subWrapper.querySelector(':scope > .dropdown__sub-trigger');
      const subContent = subWrapper.querySelector(':scope > .dropdown__sub-content');
      if (subTrigger && subContent) {
        instance.subs.push({ trigger: subTrigger, content: subContent });
      }
    });

    // Portal tất cả panel vào <body>
    instance.subs.forEach(sub => document.body.appendChild(sub.content));
    document.body.appendChild(rootContent);

    // Gắn event
    this._bindTrigger(instance);
    this._bindContent(instance);
  }

  /* ------------------------------------------------------------------ */
  /* Bind events                                                          */
  /* ------------------------------------------------------------------ */

  _bindGlobalListeners() {
    document.addEventListener('click', event => {
      // Nếu đang được mở bằng API -> bỏ qua cú click nổi bọt này
      if (this.#ignoreGlobalClick) return;

      const clickedInsideAny =
        event.target.closest('.dropdown__content') ||
        event.target.closest('.dropdown__sub-content') ||
        event.target.closest('.dropdown__trigger');

      if (!clickedInsideAny) {
        this.closeAll();
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') this.closeAll();
    });
  }

  /**
   * Gắn sự kiện click và hover (nếu mode="hover") lên root trigger.
   * @private
   * @param {object} instance - Instance của dropdown root.
   */
  _bindTrigger(instance) {
    const { trigger, content, mode } = instance;

    trigger.addEventListener('click', event => {
      event.stopPropagation();
      content.dataset.state === 'open' ? this._close(instance) : this._open(instance);
    });

    if (mode === 'hover') {
      trigger.addEventListener('mouseenter', () => this._open(instance));

      trigger.addEventListener('mouseleave', event => {
        const cursorTarget = event.relatedTarget;
        const movedIntoContent = cursorTarget &&
          (cursorTarget === content || content.contains(cursorTarget));
        const movedIntoActiveSub = instance.activeSub &&
          (cursorTarget === instance.activeSub.content ||
            instance.activeSub.content.contains(cursorTarget));

        if (movedIntoContent || movedIntoActiveSub) return;
        this._close(instance);
      });
    }
  }

  /**
   * Gắn sự kiện cho content panel và các item/sub bên trong.
   * Chỉ bind item nằm trực tiếp trong content - item trong sub-content
   * được bind riêng trong _bindSub.
   * @private
   * @param {object} instance - Instance của dropdown root.
   */
  _bindContent(instance) {
    const { trigger, content, mode } = instance;

    if (mode === 'hover') {
      content.addEventListener('mouseleave', event => {
        const cursorTarget = event.relatedTarget;
        const movedBackToTrigger = cursorTarget &&
          (cursorTarget === trigger || trigger.contains(cursorTarget));
        const movedIntoActiveSub = instance.activeSub &&
          (cursorTarget === instance.activeSub.content ||
            instance.activeSub.content.contains(cursorTarget));

        if (movedBackToTrigger || movedIntoActiveSub) return;
        this._close(instance);
      });
    }

    // Chỉ bind item trực tiếp trong content, bỏ qua item nằm trong .dropdown__sub
    content.querySelectorAll('.dropdown__item:not(.dropdown__sub-trigger)')
      .forEach(item => {
        if (!item.closest('.dropdown__sub')) this._bindItem(instance, item);
      });

    instance.subs.forEach(sub => this._bindSub(instance, sub));
  }

  /**
   * Gắn sự kiện cho một cặp sub { trigger, content }.
   * Hover vào sub-trigger → mở sub-content.
   * Rời khỏi một trong hai bên → đóng sub (trừ khi cursor đi sang bên còn lại).
   * @private
   * @param {object} instance - Instance của dropdown root.
   * @param {{ trigger: HTMLElement, content: HTMLElement }} sub - Cặp sub cần bind.
   */
  _bindSub(instance, sub) {
    const { trigger: subTrigger, content: subContent } = sub;

    // Sub-trigger cũng là một item thông thường - highlight khi hover
    this._bindItem(instance, subTrigger);

    subTrigger.addEventListener('mouseenter', () => {
      // Xóa state của tất cả sub-trigger trước - đảm bảo không còn sub cũ nào open
      instance.subs.forEach(s => {
        delete s.trigger.dataset.state;
        delete s.trigger.dataset.side;
      });
      this._closeSub(instance, instance.activeSub);
      instance.activeSub = sub;
      subTrigger.dataset.state = 'open';
      // Sync data-side lên subTrigger để CSS bridge biết hướng mở
      subTrigger.dataset.side = this._position(subContent, subTrigger, 'right');
      // Panel mở sau luôn có z-index cao hơn
      subContent.style.zIndex = DropdownHandler.BASE_Z_INDEX + (++DropdownHandler._zCounter);
      subContent.dataset.state = 'open';
    });

    subTrigger.addEventListener('mouseleave', event => {
      const cursorTarget = event.relatedTarget;
      const movedIntoSubContent = cursorTarget &&
        (cursorTarget === subContent || subContent.contains(cursorTarget));
      if (!movedIntoSubContent) this._closeSub(instance, sub);
    });

    subContent.addEventListener('mouseleave', event => {
      const cursorTarget = event.relatedTarget;
      const movedBackToTrigger = cursorTarget &&
        (cursorTarget === subTrigger || subTrigger.contains(cursorTarget));
      const movedBackToParentContent = cursorTarget &&
        (cursorTarget === instance.content || instance.content.contains(cursorTarget));

      if (movedBackToTrigger || movedBackToParentContent) return;
      this._closeSub(instance, sub);
    });

    // Bind item trong sub-content - tách biệt với item trong parent content
    subContent.querySelectorAll('.dropdown__item')
      .forEach(item => this._bindItem(instance, item));
  }

  /**
   * Gắn sự kiện hover (highlight) và click (select) cho một item.
   * Sub-trigger: chỉ highlight, không click (việc mở do hover đảm nhiệm).
   * Leaf item: đóng active sub khi hover sang item khác trong parent content.
   * @private
   * @param {object} instance - Instance của dropdown root.
   * @param {HTMLElement} item - Item cần gắn sự kiện.
   */
  _bindItem(instance, item) {
    item.addEventListener('mouseenter', () => {
      if ('disabled' in item.dataset) return;

      if (!item.classList.contains('dropdown__sub-trigger')) {
        // Đóng active sub chỉ khi hover vào item trong parent content,
        // không đóng khi hover vào item nằm bên trong chính sub đó
        const cursorIsInsideActiveSub = instance.activeSub &&
          instance.activeSub.content.contains(item);
        if (!cursorIsInsideActiveSub) this._closeSub(instance, instance.activeSub);
      }
    });

    if (!item.classList.contains('dropdown__sub-trigger')) {
      item.addEventListener('click', event => {
        event.stopPropagation();
        if ('disabled' in item.dataset) return;
        item.dispatchEvent(new CustomEvent('dropdown:select', {
          bubbles: true,
          detail: {
            label: item.querySelector('.dropdown__item-label')?.textContent.trim() ?? '',
            item,
          },
        }));
        this._close(instance);
      });
    }
  }

  /* ------------------------------------------------------------------ */
  /* Open / close                                                         */
  /* ------------------------------------------------------------------ */

  /** Định vị và mở root content panel. @private */
  _open(instance) {
    // Đóng tất cả instance khác - xử lý trường hợp cursor đi từ sub-content
    // của menu A thẳng sang trigger B mà không qua content A hay trigger A
    this._instances.forEach(other => {
      if (other !== instance) this._close(other);
    });
    this._position(instance.content, instance.trigger, instance.trigger.dataset.side || 'bottom');
    // Panel mở sau luôn có z-index cao hơn
    instance.content.style.zIndex = DropdownHandler.BASE_Z_INDEX + (++DropdownHandler._zCounter);
    instance.content.dataset.state = 'open';
    instance.trigger.dataset.state = 'open';
  }

  /** Đóng root content panel và active sub (nếu có). @private */
  _close(instance) {
    this._closeSub(instance, instance.activeSub);
    instance.content.dataset.state = 'closed';
    instance.trigger.dataset.state = 'closed';
    // Reset state của tất cả sub-trigger
    instance.subs.forEach(sub => {
      delete sub.trigger.dataset.state;
      delete sub.trigger.dataset.side;
    });
  }

  /**
   * Đóng một sub cụ thể. Nhận null mà không báo lỗi.
   * @private
   * @param {object} instance - Instance của dropdown root.
   * @param {{ trigger: HTMLElement, content: HTMLElement }|null} sub - Sub cần đóng.
   */
  _closeSub(instance, sub) {
    if (!sub) return;
    sub.content.dataset.state = 'closed';
    // Reset state và side của tất cả sub-trigger khi đóng
    instance.subs.forEach(s => {
      delete s.trigger.dataset.state;
      delete s.trigger.dataset.side;
    });
    if (instance.activeSub === sub) instance.activeSub = null;
  }

  /* ------------------------------------------------------------------ */
  /* Position                                                             */
  /* ------------------------------------------------------------------ */

  /**
   * Định vị panel cạnh anchor.
   * direction="bottom" → đặt bên dưới trigger (root dropdown)
   * direction="right"  → đặt bên phải sub-trigger (submenu)
   *
   * Đặt vị trí mặc định trước, sau đó dùng isInViewport() từ utils.js
   * để phát hiện tràn viewport và lật chiều.
   * Nếu isInViewport không có sẵn, dùng phép tính thủ công thay thế.
   * Trả về side thực tế sau khi tính toán.
   * @private
   * @param {HTMLElement} panel              - Panel cần định vị.
   * @param {HTMLElement} anchor             - Phần tử tham chiếu để căn vị trí.
   * @param {"bottom"|"right"} direction    - Hướng mở mặc định.
   * @returns {"bottom"|"top"|"right"|"left"}
   */
  _position(panel, anchor, direction) {
    const anchorRect = anchor.getBoundingClientRect();
    const { ANCHOR_OFFSET: anchorOffset, PANEL_OFFSET: panelOffset, VIEWPORT_MARGIN: margin } = DropdownHandler;
    // direction='bottom' dùng anchorOffset (trigger→panel), direction='right' dùng panelOffset (panel→panel)
    const offset = direction === 'bottom' ? anchorOffset : panelOffset;

    // Hiện tạm để đo kích thước thực của panel
    panel.style.visibility = 'hidden';
    panel.style.display = 'block';

    // Đặt vị trí mặc định
    let left = direction === 'bottom' ? anchorRect.left : anchorRect.right + offset;
    let top = direction === 'bottom' ? anchorRect.bottom + offset : anchorRect.top;
    let side = direction === 'bottom' ? 'bottom' : 'right';

    panel.style.left = left + 'px';
    panel.style.top = top + 'px';

    // Phát hiện tràn viewport và lật chiều nếu cần
    if (this._isOverflowing(panel, left, top)) {
      const panelRect = panel.getBoundingClientRect();
      const viewportWidth = document.documentElement.clientWidth;
      const viewportHeight = document.documentElement.clientHeight;

      if (direction === 'bottom') {
        if (panelRect.right > viewportWidth - margin) {
          left = Math.max(margin, viewportWidth - panel.offsetWidth - margin);
          panel.style.left = left + 'px';
        }
        if (panelRect.bottom > viewportHeight - margin) {
          top = anchorRect.top - panel.offsetHeight - offset;
          side = 'top';
          panel.style.top = top + 'px';
        }
      } else {
        if (panelRect.right > viewportWidth - margin) {
          left = anchorRect.left - panel.offsetWidth - offset;
          side = 'left';
          panel.style.left = left + 'px';
        }
        if (panelRect.bottom > viewportHeight - margin) {
          top = Math.max(margin, viewportHeight - panel.offsetHeight - margin);
          panel.style.top = top + 'px';
        }
      }
    }

    // Ẩn lại sau khi tính toán xong
    panel.style.display = '';
    panel.style.visibility = '';
    panel.style.transform = '';

    panel.dataset.side = side;
    panel.style.setProperty('--dropdown-transform-origin', this.origins[side]);

    return side;
  }

  /**
   * Kiểm tra panel có đang tràn ra ngoài viewport không.
   * Ưu tiên dùng isInViewport() từ utils.js, fallback tính thủ công.
   * @private
   * @param {HTMLElement} panel - Panel cần kiểm tra.
   * @param {number} left       - Vị trí left hiện tại của panel (px).
   * @param {number} top        - Vị trí top hiện tại của panel (px).
   * @returns {boolean}
   */
  _isOverflowing(panel, left, top) {
    const { VIEWPORT_MARGIN: margin } = DropdownHandler;
    if (typeof isInViewport === 'function') {
      return !Utils.isInViewport(panel, -margin);
    }
    const viewportWidth = document.documentElement.clientWidth;
    const viewportHeight = document.documentElement.clientHeight;
    return (left + panel.offsetWidth > viewportWidth - margin) ||
      (top + panel.offsetHeight > viewportHeight - margin);
  }
}