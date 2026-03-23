document.addEventListener("DOMContentLoaded", () => {
  const collapisbleHandler = new CollapsibleHandler();
  collapisbleHandler.init();
});

class CollapsibleHandler {
  constructor() {
    this._collapsibles = document.querySelectorAll(".collapsible");
  }

  init() {
    if (!this._collapsibles.length) return;

    this._collapsibles.forEach(root => {
      const trigger = root.querySelector(".collapsible__trigger");
      const content = root.querySelector(".collapsible__content");
      if (!trigger || !content) return;

      this._setDefaultState(trigger, content);
      this._seedHeight(content);
      this._bindEvents(trigger, content);
    });
  }

  _setDefaultState(trigger, content) {
    if (!trigger.dataset.state)
      trigger.dataset.state = "closed";

    if (!content.dataset.state)
      content.dataset.state = "closed";
  }

  _seedHeight(content) {
    const height = content.dataset.state === "open" ? content.scrollHeight : 0;
    content.style.setProperty("--collapsible-content-height", height + "px");
  }

  _open(trigger, content) {
    const height = content.scrollHeight;

    trigger.dataset.state = "open";
    content.dataset.state = "open";
    requestAnimationFrame(() => {
      content.style.setProperty("--collapsible-content-height", content.scrollHeight + "px");
    })
  }

  _close(trigger, content) {
    trigger.dataset.state = "closed";
    content.dataset.state = "closed";
    content.style.setProperty("--collapsible-content-height", 0 + "px");
  }

  _bindEvents(trigger, content) {
    trigger.addEventListener("click", () => {
      if (trigger.disabled) return;

      const isOpen = trigger.dataset.state === "open";

      if (!isOpen)
        this._open(trigger, content);
      else
        this._close(trigger, content);
    });
  }
}