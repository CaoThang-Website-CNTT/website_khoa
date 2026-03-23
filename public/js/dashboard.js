document.addEventListener("DOMContentLoaded", () => {
  const sidebarHandler = new SidebarHandler();
  sidebarHandler.init();
});

class SidebarHandler {
  constructor() {
    this._triggers = document.querySelectorAll(".sidebar__trigger");
    this._container = document.querySelector(".sidebar__container");
  }

  /**
   * Khởi tạo trạng thái và sự kiện cho tất cả sidebar trigger.
   */
  init() {
    if (!this._triggers.length || !this._container) return;

    this._setDefaultState();
    this._bindEvents();
  }

  // ── private ────────────────────────────────────────────────────

  /**
   * Đặt data-state mặc định cho container nếu chưa có.
   * Đồng bộ aria-expanded lên tất cả trigger.
   * @private
   */
  _setDefaultState() {
    if (!this._container.dataset.state) {
      this._container.dataset.state = "expanded";
    }

    this._syncTriggers();
  }

  /**
   * Gắn sự kiện click và keyboard lên tất cả trigger.
   * @private
   */
  _bindEvents() {
    this._triggers.forEach(trigger => {
      trigger.addEventListener("click", () => {
        this._handleToggle()
      });
    });
  }

  /**
   * Đảo ngược trạng thái mở/đóng của sidebar.
   * @private
   */
  _handleToggle() {
    const isExpanded = this._container.dataset.state === "expanded";
    if (!isExpanded) this._open();
    else this._close();
  }

  /**
   * Mở sidebar và đồng bộ tất cả trigger.
   * @private
   */
  _open() {
    this._container.dataset.state = "expanded";
    this._syncTriggers();
  }

  /**
   * Đóng sidebar và đồng bộ tất cả trigger.
   * @private
   */
  _close() {
    this._container.dataset.state = "collapsed";
    this._syncTriggers();
  }

  /**
   * Đồng bộ aria-expanded trên tất cả trigger với trạng thái hiện tại của container.
   * @private
   */
  _syncTriggers() {
    const isExpanded = this._container.dataset.state === "expanded";
    this._triggers.forEach(trigger => {
      trigger.setAttribute("aria-expanded", isExpanded ? "true" : "false");
    });
  }
}