document.addEventListener('DOMContentLoaded', () => {
  ModalHandler.instance.init();
});


class ModalHandler {
  static #instance = null;

  constructor() {
    if (ModalHandler.#instance) return ModalHandler.#instance;

    this._activeModals = [];
    this._portal = null;
    this._overlay = null;

    ModalHandler.#instance = this;
  }

  static get instance() {
    return ModalHandler.#instance || new ModalHandler();
  }

  init() {
    this._portal = this._createPortal();
    this._overlay = this._createOverlay();
    this._bindEvents();
  }

  _createPortal() {
    let portal = document.getElementById("modal-portal");
    if (!portal) {
      portal = document.createElement("div");
      portal.id = "modal-portal";
      document.body.appendChild(portal);
    }
    return portal;
  }

  _createOverlay() {
    let overlay = this._portal.querySelector(".modal-overlay");
    if (!overlay) {
      overlay = document.createElement("div");
      overlay.className = "modal-overlay";
      overlay.setAttribute("data-state", "closed");
      this._portal.appendChild(overlay);
    }
    return overlay;
  }

  _bindEvents() {
    document.addEventListener('click', (e) => {
      const trigger = e.target.closest('[data-modal-trigger]');
      if (trigger) {
        e.preventDefault();
        const selector = trigger.getAttribute('data-modal-trigger');
        this.open(selector);
        return;
      }

      const closeBtn = e.target.closest('[data-modal-close]');
      if (closeBtn) {
        e.preventDefault();
        this.close();
        return;
      }

      if (e.target === this._overlay) {
        e.preventDefault();
        this.close();
        return;
      }
    });
  }

  open(selector) {
    const targetModal = document.querySelector(selector);
    if (!targetModal) return;

    if (targetModal.parentElement !== this._portal) {
      this._portal.appendChild(targetModal);
    }

    if (!this._activeModals.includes(targetModal)) {
      this._activeModals.push(targetModal);
    }

    this._updateZIndices();

    this._overlay.setAttribute("data-state", "open");
    targetModal.setAttribute("data-state", "open");

    document.body.style.overflow = "hidden";

    // Dispatch Event
    targetModal.dispatchEvent(new CustomEvent("modal:open", {
      bubbles: true,
      detail: {
        modal: targetModal
      },
    }));
  }

  close() {
    if (this._activeModals.length === 0) return;

    const targetModal = this._activeModals.pop();
    targetModal.setAttribute("data-state", "closed");

    if (this._activeModals.length > 0) {
      this._updateZIndices();
    } else {
      this._overlay.setAttribute("data-state", "closed");
      document.body.style.overflow = "";
    }

    // Dispatch Event
    targetModal.dispatchEvent(new CustomEvent("modal:close", {
      bubbles: true,
      detail: {
        modal: targetModal,
      },
    }));
  }

  _updateZIndices() {
    const baseZIndex = 100;

    this._activeModals.forEach((modal, index) => {
      // Mỗi modal được mở sau sẽ có z-index cao hơn để xếp chồng lên nhau
      modal.style.zIndex = baseZIndex + (index * 2);
    });

    // Lớp overlay mờ luôn tự động nằm ngay dưới modal trên cùng
    const topIndex = this._activeModals.length - 1;
    this._overlay.style.zIndex = baseZIndex + (topIndex * 2) - 1;
  }
}