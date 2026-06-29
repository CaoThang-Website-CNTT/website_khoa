document.addEventListener("DOMContentLoaded", () => {
  AccordionHandler.instance.init();
});

/**
 * AccordionHandler
 *
 * <div class="accordion" data-accordion-type="single|multiple"
 *      data-accordion-collapsible data-accordion-default-value="item-1">
 *   <div class="accordion_item" data-accordion-value="item-1">
 *     <button class="accordion__trigger" type="button">...</button>
 *     <div class="accordion__content">...</div>
 *   </div>
 * </div>
 *
 * Event: "accordion:change".
 * Single:   e.detail = { value }
 * Multiple: e.detail = { values }
 */
class AccordionHandler {
  static #instance = null;
  static #idCounter = 0;

  #instances = new Map();

  constructor() {
    if (AccordionHandler.#instance) return AccordionHandler.#instance;
    AccordionHandler.#instance = this;
  }

  static get instance() {
    return AccordionHandler.#instance || new AccordionHandler();
  }

  init() {
    document.querySelectorAll(".accordion").forEach(root => this._initRoot(root));
  }

  register(root) {
    if (!(root instanceof HTMLElement)) {
      console.warn("[AccordionHandler] register() yêu cầu một HTMLElement");
      return;
    }

    this._initRoot(root);
  }

  /**
   * Set the open value(s) without simulating user interaction.
   * @param {HTMLElement} root
   * @param {string|string[]|null} value
   * @param {{ emit?: boolean }} options
   */
  setValue(root, value, { emit = true } = {}) {
    this._initRoot(root);
    const instance = this.#instances.get(root);
    if (!instance) return;

    const requestedValues = instance.type === "multiple" && Array.isArray(value)
      ? new Set(value)
      : new Set(value == null ? [] : [value]);

    instance.items.forEach(item => {
      this._setState(item, !item.disabled && requestedValues.has(item.value));
    });

    if (emit) this._emitChange(instance);
  }

  _initRoot(root) {
    if (this.#instances.has(root)) return;

    const requestedType = root.dataset.accordionType;
    const type = requestedType === "multiple" ? "multiple" : "single";
    const instance = {
      root,
      type,
      collapsible: root.hasAttribute("data-accordion-collapsible"),
      items: [],
    };

    const itemElements = root.querySelectorAll(":scope > .accordion_item");
    itemElements.forEach((element, index) => {
      const trigger = element.querySelector(".accordion__trigger");
      const content = element.querySelector(":scope > .accordion__content");
      const value = element.dataset.accordionValue;

      if (!trigger || !content || !value) {
        console.warn("[AccordionHandler] Mỗi mục yêu cầu một value, trigger, và content", element);
        return;
      }

      const item = {
        element,
        trigger,
        content,
        value,
        disabled: element.hasAttribute("disabled"),
        hideTimer: null,
      };

      this._prepareItem(root, item, index);
      instance.items.push(item);
    });

    this.#instances.set(root, instance);
    this._setInitialState(instance);
    instance.items.forEach(item => this._bindItem(instance, item));
  }

  _prepareItem(root, item, index) {
    const rootId = root.dataset.accordionId || root.id || this._createId("accordion");
    root.dataset.accordionId = rootId;

    item.trigger.type = "button";
    item.trigger.id ||= `${rootId}-trigger-${index + 1}`;
    item.content.id ||= `${rootId}-content-${index + 1}`;
    item.trigger.setAttribute("aria-controls", item.content.id);
    item.content.setAttribute("aria-labelledby", item.trigger.id);
    item.trigger.disabled = item.disabled;
  }

  _setInitialState(instance) {
    const defaultValue = instance.root.dataset.accordionDefaultValue;
    let defaultApplied = false;

    instance.items.forEach(item => {
      const shouldOpen = !defaultApplied && !item.disabled && item.value === defaultValue;
      this._setState(item, shouldOpen, false);
      if (shouldOpen) defaultApplied = true;
    });
  }

  _bindItem(instance, item) {
    item.trigger.addEventListener("click", event => {
      event.preventDefault();
      if (item.disabled) return;
      this._toggle(instance, item);
    });

    item.trigger.addEventListener("keydown", event => {
      if (!["ArrowDown", "ArrowUp", "Home", "End"].includes(event.key)) return;

      const enabledItems = instance.items.filter(candidate => !candidate.disabled);
      if (!enabledItems.length) return;

      event.preventDefault();
      const currentIndex = enabledItems.indexOf(item);
      let nextIndex;

      if (event.key === "Home") nextIndex = 0;
      else if (event.key === "End") nextIndex = enabledItems.length - 1;
      else if (event.key === "ArrowDown") nextIndex = (currentIndex + 1) % enabledItems.length;
      else nextIndex = (currentIndex - 1 + enabledItems.length) % enabledItems.length;

      enabledItems[nextIndex].trigger.focus();
    });
  }

  _toggle(instance, item) {
    const isOpen = item.element.dataset.state === "open";

    if (isOpen) {
      if (instance.type === "single" && !instance.collapsible) return;
      this._setState(item, false);
    } else {
      if (instance.type === "single") {
        instance.items.forEach(sibling => {
          if (sibling !== item) this._setState(sibling, false);
        });
      }
      this._setState(item, true);
    }

    this._emitChange(instance);
  }

  _setState(item, open, animate = true) {
    window.clearTimeout(item.hideTimer);
    item.hideTimer = null;

    item.element.dataset.state = open ? "open" : "closed";
    item.trigger.dataset.state = open ? "open" : "closed";
    item.content.dataset.state = open ? "open" : "closed";
    item.trigger.setAttribute("aria-expanded", open ? "true" : "false");

    if (open) {
      item.content.hidden = false;
      item.content.style.setProperty("--accordion-content-height", `${item.content.scrollHeight}px`);
      return;
    }

    if (!animate) {
      item.content.style.setProperty("--accordion-content-height", "0px");
      item.content.hidden = true;
      return;
    }

    item.content.style.setProperty("--accordion-content-height", `${item.content.scrollHeight}px`);
    requestAnimationFrame(() => {
      item.content.style.setProperty("--accordion-content-height", "0px");
    });

    item.hideTimer = window.setTimeout(() => {
      if (item.content.dataset.state === "closed") item.content.hidden = true;
      item.hideTimer = null;
    }, 220);
  }

  _emitChange(instance) {
    const openValues = instance.items
      .filter(item => item.element.dataset.state === "open")
      .map(item => item.value);
    const detail = instance.type === "multiple"
      ? { values: openValues }
      : { value: openValues[0] ?? null };

    instance.root.dispatchEvent(new CustomEvent("accordion:change", {
      bubbles: true,
      detail,
    }));
  }

  _createId(prefix) {
    if (typeof crypto !== "undefined" && typeof crypto.randomUUID === "function") {
      return `${prefix}-${crypto.randomUUID()}`;
    }
    AccordionHandler.#idCounter += 1;
    return `${prefix}-${AccordionHandler.#idCounter}`;
  }
}
