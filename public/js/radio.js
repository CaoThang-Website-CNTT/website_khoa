document.addEventListener("DOMContentLoaded", () => {
  const radioHandler = new RadioHandler();
  radioHandler.init();
});

class RadioHandler {
  constructor() {
    this._groups = document.querySelectorAll(".radio-group");
  }
  init() {
    this._groups.forEach(group => {
      const hiddenInput = this._createHiddenInput(group);
      const radioBtns = group.querySelectorAll('button.radio-group__item');

      radioBtns.forEach(radioBtn => {
        this._setDefaultState(group, radioBtn, hiddenInput);
        this._bindEvents(radioBtns, radioBtn, hiddenInput, group);
      });
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
   * Nếu FormHandler đã chạy trước và set data-state từ __old__,
   * _setDefaultState sẽ bỏ qua (data-state đã tồn tại) — chỉ sync hidden input.
   * @private
   */
  _setDefaultState(group, radioBtn, hiddenInput) {
    // Nếu FormHandler đã set data-state từ old input, ưu tiên giá trị đó
    if (radioBtn.dataset.state === "checked") {
      hiddenInput.value = radioBtn.value;
      return;
    }

    if (radioBtn.value && group.dataset.radioDefaultValue === radioBtn.value) {
      radioBtn.dataset.state = "checked";
      hiddenInput.value = radioBtn.value;
      return;
    }

    if (!radioBtn.dataset.state) {
      radioBtn.dataset.state = "unchecked";
    }
  }
  /**
   * @private
   * @param {NodeList} siblingBtns
   * @param {HTMLElement} radioBtn
   * @param {HTMLInputElement} hiddenInput
   * @param {HTMLElement} group
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
    });
    clickedRadioBtn.dataset.state = "checked";
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