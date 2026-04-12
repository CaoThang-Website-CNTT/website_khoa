document.addEventListener("DOMContentLoaded", () => {
  const switchHandler = new SwitchHandler();
  switchHandler.init();
});
class SwitchHandler {
  constructor() {
    this._switchs = document.querySelectorAll(".switch");
  }
  init() {
    this._switchs.forEach(switchEl => {
      const thumb = switchEl.querySelector(".switch__thumb");
      const name = switchEl.getAttribute("name");

      this._setDefaultState(switchEl, thumb);

      if (name) {
        this._ensureHiddenInput(switchEl, name);
      }

      this._bindEvents(switchEl, thumb);
    });
  }
  _setDefaultState(switchEl, switchThumb) {
    const initialState = switchEl.dataset.switchDefaultState || "unchecked";

    if (!switchEl.dataset.switchState) {
      switchEl.dataset.switchState = initialState;
    }

    switchThumb.dataset.switchState = switchEl.dataset.switchState;
  }
  _ensureHiddenInput(switchEl, name) {
    let hiddenInput = switchEl.querySelector(`input[type="hidden"][name="${name}"]`);

    if (!hiddenInput) {
      hiddenInput = document.createElement("input");
      hiddenInput.type = "hidden";
      hiddenInput.name = name;
      hiddenInput.value = switchEl.dataset.state === "checked" ? "1" : "0";
      switchEl.appendChild(hiddenInput);
    }
  }
  _bindEvents(switchEl, switchThumb) {
    switchEl.addEventListener('click', () => {
      this._toggle(switchEl, switchThumb);
    });
  }
  _toggle(switchEl, switchThumb) {
    const currentState = switchEl.dataset.switchState;
    const newState = currentState === "unchecked" ? "checked" : "unchecked";

    switchEl.dataset.switchState = newState;
    switchThumb.dataset.switchState = newState;

    const hiddenInput = switchEl.querySelector('input[type="hidden"]');
    if (hiddenInput) {
      hiddenInput.value = newState === "checked" ? "1" : "0";
    }
  }
}