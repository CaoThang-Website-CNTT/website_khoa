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

      this._setDefaultState(switchEl, thumb);

      this._bindEvents(switchEl, thumb);
    });
  }
  _setDefaultState(switchEl, switchThumb) {
    if (!switchEl.dataset.state)
      switchEl.dataset.state = "unchecked";

    if (!switchThumb.dataset.state)
      switchThumb.dataset.state = "unchecked";
  }
  _bindEvents(switchEl, switchThumb) {
    switchEl.addEventListener('click', () => {
      this._toggle(switchEl, switchThumb);
    });
  }
  _toggle(switchEl, switchThumb) {
    switchEl.dataset.state = switchEl.dataset.state === "unchecked" ? "checked" : "unchecked";
    switchThumb.dataset.state = switchThumb.dataset.state === "unchecked" ? "checked" : "unchecked";
  }
}