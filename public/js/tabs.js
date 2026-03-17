document.addEventListener("DOMContentLoaded", () => {
  const tabHandler = new TabHandler();
  tabHandler.init();
});

class TabHandler {
  constructor() {
    this._tabs = document.querySelectorAll("[data-tabs]");
  }

  /**
   * Khởi tạo các logic liên quan đến tabs.
   * @private
   */
  init() {
    if (!this._tabs.length) return;

    this._tabs.forEach(tab => {
      const tabId = tab.dataset.id;
      const initialKey = this.resolveInitialKey(tab, tabId);

      this.activate(tab, initialKey);

      tab.querySelectorAll("[data-tabs-trigger]").forEach(trigger => {
        trigger.addEventListener("click", e => {
          e.preventDefault();
          this.activate(tab, trigger.dataset.tabsTrigger);
        });
      });
    });
  }

  /**
   * Xác định key khởi tạo — ưu tiên query param, fallback về data-active.
   * URL format: ?users=students&reports=monthly
   *
   * @param {HTMLElement} tab
   * @param {string} tabId
   * @returns {string}
   */
  resolveInitialKey(tab, tabId) {
    const params = new URLSearchParams(window.location.search);
    const paramKey = params.get(tabId);

    if (paramKey) {
      const matched = tab.querySelector(`[data-tabs-panel="${paramKey}"]`);
      if (matched) return paramKey;
    }

    return (
      tab.dataset.active ??
      tab.querySelector("[data-tabs-trigger]")?.dataset.tabsTrigger
    );
  }

  /**
   * Kích hoạt tab theo key.
   * @param {HTMLElement} tab
   * @param {string} key
   */
  activate(tab, key) {
    const tabId = tab.dataset.id;

    tab.querySelectorAll("[data-tabs-trigger]").forEach(trigger => {
      const isActive = trigger.dataset.tabsTrigger === key;
      trigger.dataset.state = isActive ? "active" : "idle";
      trigger.setAttribute("aria-selected", isActive ? "true" : "false");
      trigger.setAttribute("tabindex", isActive ? "0" : "-1");
    });

    tab.querySelectorAll("[data-tabs-panel]").forEach(panel => {
      panel.dataset.state = panel.dataset.tabsPanel === key ? "active" : "idle";
    });

    this.syncObservers(tabId, key);
    this.syncParams(tabId, key);
  }

  /**
   * Đồng bộ tất cả observer elements bên ngoài tabs root.
   * Bất kỳ element nào có data-tabs-observe="tabsId:key" sẽ được cập nhật data-state.
   *
   * Usage:
   *   <div data-tabs-observe="users:students">  → active khi tab students active
   *   <div data-tabs-observe="users:teachers">  → active khi tab teachers active
   *
   * @param {string} tabId
   * @param {string} key
   */
  syncObservers(tabId, key) {
    document.querySelectorAll(`[data-tabs-observe^="${tabId}:"]`).forEach(observer => {
      const [, observerKey] = observer.dataset.tabsObserve.split(":");
      observer.dataset.state = observerKey === key ? "active" : "idle";
    });
  }

  /**
   * Đồng bộ URL query params — cập nhật key của tab hiện tại,
   * giữ nguyên tất cả các tab khác đang có trong URL.
   * 
   * Ví dụ: 
   * Trước: ?users=students&reports=monthly
   * Sau:  ?users=teachers&reports=monthly  ← chỉ đổi key của tab này
   * 
   * Hiện tại đang ví dụ đơn giản cách sử dụng, thực tế đặt id của tabs sao cho dễ nhận biết để tránh trùng `query params` khi `GET request` lên server yêu cầu dữ liệu.
   * Có thể nghĩ đến việc thêm `prefix`, `postfix` hay bất kì `convention` nào khác để tránh trùng `query params`.
   *
   * @param {string} tabId
   * @param {string} key
   */
  syncParams(tabId, key) {
    const params = new URLSearchParams(window.location.search);
    params.set(tabId, key);
    history.replaceState(null, "", `?${params.toString()}`);
  }
}