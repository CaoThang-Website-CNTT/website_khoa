export class EditorToolbar {
  /** @type {EditorEventBus} */
  bus;
  /** @type {HTMLElement} - #be-block-list */
  canvas;
  /** @type {AbortController} */
  abortController;
  /**
   * Child class gán giá trị này trong #buildDOM() của chúng.
   * BlockToolbar: root là .dropdown wrapper.
   * InlineToolbar: root là .inline-toolbar div.
   * @type {HTMLElement|null}
   */
  root = null;
  /**
   * Virtual trigger element dùng cho DropdownHandler (chỉ BlockToolbar cần).
   * InlineToolbar không dùng trường này.
   * @type {HTMLElement|null}
   */
  virtualTrigger = null;
  /**
   * Dynamic content area bên trong dropdown (chỉ BlockToolbar cần).
   * @type {HTMLElement|null}
   */
  dynamicArea = null;
  /**
   * Block đang được context menu áp vào.
   * BlockToolbar set trong toggle(), InlineToolbar không cần.
   * @type {EditorBlock|null}
   */
  currentBlock = null;

  /**
   * @param {EditorEventBus} bus
   * @param {HTMLElement}    canvas - #be-block-list element
   */
  constructor(bus = null, canvas = null) {
    this.bus = bus;
    this.canvas = canvas;
    this.abortController = new AbortController();
  }

  init() {
    throw new Error(`${this.constructor.name} phải implement init()`);
  }

  destroy() {
    this.abortController?.abort();
    this.root?.remove();
    this.root = null;
    this.currentBlock = null;
  }

  open(dropdownId) {
    try {
      DropdownHandler.instance.open(dropdownId);
    } catch (err) {
      console.warn(`[EditorToolbar] Không thể mở dropdown "${dropdownId}":`, err);
    }
  }

  close(dropdownId) {
    try {
      DropdownHandler.instance.close(dropdownId);
    } catch (err) {
      console.warn(`[EditorToolbar] Không thể đóng dropdown "${dropdownId}":`, err);
    }
  }

  /**
   * Đặt virtual trigger vào sát anchor element.
   * BlockToolbar gọi trước open() để dropdown xuất hiện đúng vị trí.
   *
   * @param {HTMLElement} anchorEl - element mà dropdown sẽ neo vào
   */
  positionVirtualTrigger(anchorEl) {
    if (!this.virtualTrigger) return;
    const rect = anchorEl.getBoundingClientRect();
    this.virtualTrigger.style.top = `${rect.top}px`;
    this.virtualTrigger.style.left = `${rect.left + rect.width}px`;
  }
}
