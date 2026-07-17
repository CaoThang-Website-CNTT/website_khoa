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
      const isNavigation = tabs.dataset.tabsMode === "navigation";
      const initialKey = this.resolveInitialKey(tabs, tabId, isNavigation);

      this.activate(tabs, initialKey);

      // Gán events cho tab triggers
      this.getOwnedElements(tabs, "[data-tabs-trigger]").forEach(trigger => {
        trigger.addEventListener("click", e => {
          if (isNavigation) return;

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
    // Nếu cấu hình toàn cục là true, kiểm tra xem HTML có ghi đè bằng data-tabs-sync="false" không
    return this.config.syncParams && tabs.dataset.tabsSync !== "false";
  }

  getOwnedElements(tabs, selector) {
    return Array.from(tabs.querySelectorAll(selector))
      .filter(element => element.closest('[data-tabs]') === tabs);
  }
  /**
   * Xác định key khởi tạo - ưu tiên query param, fallback về data-active.
   * URL format: ?users=students&reports=monthly
   *
   * @param {HTMLElement} tabs
   * @param {string} tabPanelId
   * @returns {string}
   */
  resolveInitialKey(tabs, tabPanelId, isNavigation = false) {
    if (isNavigation) {
      return (
        tabs.dataset.tabsPanelActive ??
        this.getOwnedElements(tabs, '[data-tabs-trigger-state="active"]')[0]?.dataset.tabsTrigger ??
        this.getOwnedElements(tabs, "[data-tabs-trigger]")[0]?.dataset.tabsTrigger
      );
    }

    // Check hash first (e.g., #tabsId:key)
    const hash = window.location.hash.substring(1);
    if (hash) {
      const [hashTabId, hashKey] = hash.split(":");
      if (hashTabId === tabPanelId && hashKey) {
        const matched = this.getOwnedElements(tabs, `[data-tabs-panel="${hashKey}"]`)[0];
        if (matched) return hashKey;
      }
    }

    const params = new URLSearchParams(window.location.search);
    const paramKey = params.get(tabPanelId);

    if (paramKey) {
      const matched = this.getOwnedElements(tabs, `[data-tabs-panel="${paramKey}"]`)[0];
      if (matched) return paramKey;
    }

    return (
      tabs.dataset.tabsPanelActive ??
      this.getOwnedElements(tabs, "[data-tabs-trigger]")[0]?.dataset.tabsTrigger
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
    this.getOwnedElements(tabs, "[data-tabs-trigger]").forEach(trigger => {
      const isActive = trigger.dataset.tabsTrigger === key;
      trigger.dataset.tabsTriggerState = isActive ? "active" : "idle";
      const badge = trigger.querySelector(":scope > .badge");
      if (badge) badge.dataset.variant = isActive ? "primary" : "outline";
      trigger.setAttribute("aria-selected", isActive ? "true" : "false");
      trigger.setAttribute("tabindex", isActive ? "0" : "-1");
    });

    // Bật panel
    this.getOwnedElements(tabs, "[data-tabs-panel]").forEach(panel => {
      panel.dataset.tabsPanelState = panel.dataset.tabsPanel === key ? "active" : "idle";
    });

    this.syncObservers(tabsId, key);
    if (this.canSyncParams(tabs) && tabsId) {
      this.syncParams(tabsId, key);
    } else if (tabs.dataset.tabsMode !== "navigation" && tabsId) {
      history.replaceState(null, "", `#${tabsId}:${key}`);
    }
  }

  /**
   * Đồng bộ tất cả observer elements bên ngoài tabs root.
   * Bất kỳ phần tử nào có data-tabs-observe="tabsId:key" sẽ được cập nhật data-state.
   *
   * Usage:
   *   <div data-tabs-observe="users:students">  → kích hoạt khi tab students kích hoạt
   *   <div data-tabs-observe="users:teachers">  → kích hoạt khi tab teachers kích hoạt
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
   * Đồng bộ URL query params - cập nhật key của tab hiện tại,
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
