document.addEventListener("DOMContentLoaded", () => {
  const tabHandler = new TabHandler({ syncParams: false });
  tabHandler.init();
});

class TabHandler {
  constructor(options) {
    this._tabsList = document.querySelectorAll("[data-tabs]");

    this.config = {
      syncParams: false,
      ...options
    };
  }

  /**
   * Khởi tạo các logic liên quan đến tabs.
   * @private
   */
  init() {
    if (!this._tabsList.length) return;

    this._tabsList.forEach(tabs => {
      const tabId = tabs.dataset.tabsId;
      const initialKey = this.resolveInitialKey(tabs, tabId);

      console.log(tabs, initialKey);
      this.activate(tabs, initialKey);

      // Gán events cho tab triggers
      tabs.querySelectorAll("[data-tabs-trigger]").forEach(trigger => {
        trigger.addEventListener("click", e => {
          e.preventDefault();
          this.activate(tabs, trigger.dataset.tabsTrigger);
        });
      });
    });
  }
  /**
   * Xác định tab có được phép đồng bộ URL không
   * Dựa vào Global config HOẶC data-attribute trên HTML
   */
  canSyncParams(tabs) {
    // Nếu global config là true, kiểm tra xem HTML có ghi đè bằng data-tabs-sync="false" không
    return this.config.syncParams && tabs.dataset.tabsSync !== "false";
  }
  /**
   * Xác định key khởi tạo — ưu tiên query param, fallback về data-active.
   * URL format: ?users=students&reports=monthly
   *
   * @param {HTMLElement} tabs
   * @param {string} tabPanelId
   * @returns {string}
   */
  resolveInitialKey(tabs, tabPanelId) {
    const params = new URLSearchParams(window.location.search);
    const paramKey = params.get(tabPanelId);

    if (paramKey) {
      const matched = tabs.querySelector(`[data-tabs-panel="${paramKey}"]`);
      if (matched) return paramKey;
    }

    return (
      tabs.dataset.tabsPanelActive ??
      tabs.querySelector("[data-tabs-trigger]")?.dataset.tabsTrigger
    );
  }

  /**
   * Kích hoạt tab theo key.
   * @param {HTMLElement} tabs
   * @param {string} key
   */
  activate(tabs, key) {
    const tabsId = tabs.dataset.tabsId;

    // Bật active trên trigger
    tabs.querySelectorAll("[data-tabs-trigger]").forEach(trigger => {
      const isActive = trigger.dataset.tabsTrigger === key;
      trigger.dataset.tabsTriggerState = isActive ? "active" : "idle";
      trigger.setAttribute("aria-selected", isActive ? "true" : "false");
      trigger.setAttribute("tabindex", isActive ? "0" : "-1");
    });

    // Bật panel
    tabs.querySelectorAll("[data-tabs-panel]").forEach(panel => {
      panel.dataset.tabsPanelState = panel.dataset.tabsPanel === key ? "active" : "idle";
    });

    this.syncObservers(tabsId, key);
    if (this.canSyncParams(tabs) && tabsId) {
      this.syncParams(tabsId, key);
    }
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
      observer.dataset.tabsPanelState = observerKey === key ? "active" : "idle";
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