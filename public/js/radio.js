document.addEventListener("DOMContentLoaded", () => {
  RadioHandler.instance.init();
});

class RadioHandler {
  static #instance = null;
  #initializedGroups = new Set();

  constructor() {
    if (RadioHandler.#instance) return RadioHandler.#instance;
    RadioHandler.#instance = this;
  }

  /**
   * Global access point (Singleton)
   */
  static get instance() {
    return RadioHandler.#instance || new RadioHandler();
  }

  /**
   * Khởi tạo tất cả radio group có trong DOM tại thời điểm gọi.
   */
  init() {
    document.querySelectorAll(".radio-group").forEach(group => this._initGroup(group));
  }

  /**
   * Đăng ký thêm radio group được render động sau khi init()
   * @param {HTMLElement} group - Phần tử .radio-group
   */
  register(group) {
    if (!(group instanceof HTMLElement)) {
      console.warn("[RadioHandler] register() expects an HTMLElement");
      return;
    }
    this._initGroup(group);
  }

  setValue(group, value, { emit = true } = {}) {
    this._initGroup(group);
    const hiddenInput = group.querySelector('input[type="hidden"]');
    const radioBtns = group.querySelectorAll("button.radio-group__item");
    let selected = null;

    radioBtns.forEach(btn => {
      const checked = btn.value === String(value);
      btn.dataset.state = checked ? "checked" : "unchecked";
      btn.setAttribute("aria-checked", checked ? "true" : "false");
      if (checked) selected = btn;
    });
    if (!selected || !hiddenInput) return;
    hiddenInput.value = selected.value;

    if (emit) group.dispatchEvent(new CustomEvent("radio:change", {
      bubbles: true,
      detail: { name: hiddenInput.name, value: selected.value }
    }));
  }

  /**
   * Khởi tạo một .radio-group: tạo hidden input, set state, bind events.
   * Đảm bảo idempotent (không bind trùng lặp).
   * @private
   */
  _initGroup(group) {
    if (this.#initializedGroups.has(group)) return;
    this.#initializedGroups.add(group);

    const hiddenInput = this._createHiddenInput(group);
    const radioBtns = group.querySelectorAll("button.radio-group__item");

    radioBtns.forEach(radioBtn => {
      this._setDefaultState(group, radioBtn, hiddenInput);
      this._bindEvents(radioBtns, radioBtn, hiddenInput, group);
    });
  }

  _createHiddenInput(group) {
    let input = group.querySelector('input[type="hidden"]');
    if (!input) {
      input = document.createElement("input");
      input.type = "hidden";
      input.name = group.dataset.radioName || "radio_field";
      group.appendChild(input);
    }
    return input;
  }

  /**
   * Set trạng thái mặc định cho button.
   * Ưu tiên data-state đã tồn tại (ví dụ: từ FormHandler SSR/old values).
   * @private
   */
  _setDefaultState(group, radioBtn, hiddenInput) {
    if (radioBtn.dataset.state === "checked") {
      hiddenInput.value = radioBtn.value;
      radioBtn.setAttribute("aria-checked", "true");
      return;
    }
    if (radioBtn.value && group.dataset.radioDefaultValue === radioBtn.value) {
      radioBtn.dataset.state = "checked";
      radioBtn.setAttribute("aria-checked", "true");
      hiddenInput.value = radioBtn.value;
      return;
    }

    if (!radioBtn.dataset.state) {
      radioBtn.dataset.state = "unchecked";
    }
    radioBtn.setAttribute("aria-checked", "false");
  }

  /**
   * @private
   */
  _bindEvents(siblingBtns, radioBtn, hiddenInput, group) {
    radioBtn.addEventListener("click", (e) => {
      e.preventDefault();
      this._toggle(siblingBtns, radioBtn, hiddenInput, group);
    });
  }

  /**
   * Cập nhật trạng thái, ghi hidden input, phát radio:change lên group.
   * FormHandler lắng nghe radio:change để clear error.
   * @private
   */
  _toggle(siblingBtns, clickedRadioBtn, hiddenInput, group) {
    siblingBtns.forEach(btn => {
      btn.dataset.state = "unchecked";
      btn.setAttribute("aria-checked", "false");
    });
    clickedRadioBtn.dataset.state = "checked";
    clickedRadioBtn.setAttribute("aria-checked", "true");
    hiddenInput.value = clickedRadioBtn.value;

    group.dispatchEvent(new CustomEvent("radio:change", {
      bubbles: true,
      detail: {
        name: hiddenInput.name,
        value: clickedRadioBtn.value,
      }
    }));
  }
}

window.RadioHandler = RadioHandler;
