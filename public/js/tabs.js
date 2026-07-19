document.addEventListener("DOMContentLoaded", () => {
  const tabHandler = new TabHandler();
  tabHandler.init();
});

class TabHandler {
  constructor() {
    this._tabsList = document.querySelectorAll("[data-tabs]");
  }

  /**
   * Khởi tạo các logic liên quan đến tabs.
   * @private
   */
  init() {
    if (!this._tabsList.length) return;

    this.clearTabHash();

    this._tabsList.forEach(tabs => {
      const tabId = tabs.dataset.tabsId;
      const isNavigation = tabs.dataset.tabsMode === "navigation";
      const initialKey = this.resolveInitialKey(tabs, tabId, isNavigation);

      this.activate(tabs, initialKey);

      // Gán events cho tab triggers
      this.getOwnedElements(tabs, "[data-tabs-trigger]").forEach(trigger => {
        trigger.addEventListener("click", event => {
          if (isNavigation) return;

          event.preventDefault();
          this.activate(tabs, trigger.dataset.tabsTrigger);
        });
      });
    });
  }

  /**
   * Xóa hash tab cũ khỏi URL. Tabs không còn đồng bộ URL khi người dùng chuyển tab.
   */
  clearTabHash() {
    const hash = window.location.hash.substring(1);
    if (!hash) return;

    const [hashTabId, hashKey] = hash.split(":");
    if (!hashTabId || !hashKey) return;

    const matchedTab = Array.from(this._tabsList).find(tabs =>
      tabs.dataset.tabsId === hashTabId &&
      this.getOwnedElements(tabs, `[data-tabs-panel="${hashKey}"]`).length > 0
    );

    if (!matchedTab) return;

    history.replaceState(null, "", `${window.location.pathname}${window.location.search}`);
  }

  getOwnedElements(tabs, selector) {
    return Array.from(tabs.querySelectorAll(selector))
      .filter(element => element.closest("[data-tabs]") === tabs);
  }

  /**
   * Xác định key khởi tạo, fallback về data-active.
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
}
