document.addEventListener("DOMContentLoaded", () => {
  const formHandler = new FormHandler();
  formHandler.init();
});

class FormHandler {
  constructor() {
    this._form = document.querySelector("form:has(.field-group, .field, .radio-group)");
    this._errors = window.__errors__ ?? {};
    this._old = window.__old__ ?? {};

    this._icons = {
      error: `<i class="fa-solid fa-circle-exclamation"></i>`
    };
  }
  init() {
    // Xử lý các loại field chuyên biệt
    this._initFields();
    this._initPasswordFields();
    this._initRadioGroups();
  }
  _initPasswordFields() {
    const passwordToggleButtons = document.querySelectorAll('[data-password-toggle]');

    passwordToggleButtons.forEach((button) => {
      const input = document.getElementById(button.dataset.passwordToggle);
      const icon = button.querySelector('i');

      if (!input || !icon) return;

      button.addEventListener('click', () => {
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        icon.classList.toggle('fa-eye', !isPassword);
        icon.classList.toggle('fa-eye-slash', isPassword);
        button.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
      });
    });
  }
  _initFields() {
    // Lọc lấy các field <input /> thông thường
    const fields = [...document.querySelectorAll(".field")].filter(
      field => !field.closest(".radio-group")
    );

    fields.forEach(field => {
      const fieldInput = field.querySelector(".field__input");
      if (!fieldInput) return;

      const rules = this._getFieldRules(field.dataset);
      rules.forEach(rule => this._applyRule(fieldInput, rule));

      this._fillOld(fieldInput);

      const name = fieldInput.name;
      if (name && this._errors[name]) {
        this._renderError(field, fieldInput, this._errors[name]);
      }

      this._bindClearOnInput(field, fieldInput);
    });
  }

  /**
   * Lấy các data attribute bắt đầu với "field".
   * @private
   * @param {DOMStringMap} dataset
   * @returns {Array<{type: string, value: string}>}
   */
  _getFieldRules(dataset) {
    const rules = [];
    for (const key in dataset) {
      if (key.startsWith("field")) {
        rules.push({ type: key, value: dataset[key] });
      }
    }
    return rules;
  }

  /**
   * @private
   * @param {HTMLElement} input
   * @param {{ type: string, value: string }} rule
   */
  _applyRule(input, rule) {
    const { type, value } = rule;
    const isNumericOrDate = ['number', 'range', 'date', 'month', 'week', 'time', 'datetime-local'].includes(input.type);

    switch (type) {
      case "fieldReadonly":
        input.readOnly = value !== "false";
        break;
      case "fieldDisabled":
        input.disabled = value !== "false";
        break;
      case "fieldRequired":
        input.required = value !== "false";
        break;
      case "fieldMin":
        input[isNumericOrDate ? 'min' : 'minLength'] = value;
        break;
      case "fieldMax":
        input[isNumericOrDate ? 'max' : 'maxLength'] = value;
        break;
      default:
        console.warn(`FormHandler: Không nhận dạng được ruleType "${type}"`);
    }
  }
  /**
   * Điền lại giá trị cũ. Hỗ trợ: text, select, checkbox, radio, hidden, textarea.
   * @private
   * @param {HTMLElement} input
   */
  _fillOld(input) {
    const value = this._old[input.name];
    if (value === undefined) return;

    if (input.tagName === 'SELECT') {
      [...input.options].forEach(opt => {
        opt.selected = opt.value === String(value);
      });
    } else if (input.type === 'checkbox') {
      input.checked = Array.isArray(value)
        ? value.includes(input.value)
        : input.value === String(value);
    } else if (input.type === 'radio') {
      input.checked = input.value === String(value);
    } else {
      input.value = value;
    }
  }
  /**
   * Render error span ngay sau anchor element, đánh dấu field container invalid.
   * Dùng :scope > .field__error để tránh nhầm với error của nested elements.
   * @private
   * @param {HTMLElement} field - Container nhận data-field-invalid / aria-invalid.
   * @param {HTMLElement} anchor - Element mà span sẽ insertAdjacentElement('afterend').
   * @param {string|string[]} messages
   */
  _renderError(field, anchor, messages) {
    const message = Array.isArray(messages) ? messages[0] : messages;

    field.ariaInvalid = "true";
    field.querySelector(':scope > .field__error')?.remove();

    const span = document.createElement('span');
    span.className = 'field__error';
    span.innerHTML = this._icons['error'];
    span.appendChild(document.createTextNode(message));
    anchor.insertAdjacentElement('afterend', span);
  }
  /**
   * Xoá trạng thái lỗi của một field container.
   * @private
   * @param {HTMLElement} field
   */
  _clearError(field) {
    field.removeAttribute('aria-invalid');
    field.querySelector(':scope > .field__error')?.remove();
  }
  /**
   * @private
   * @param {HTMLElement} field
   * @param {HTMLElement} input
   */
  _bindClearOnInput(field, input) {
    const clear = () => this._clearError(field);
    input.addEventListener('input', clear);
    input.addEventListener('change', clear);
  }
  _initRadioGroups() {
    const groups = document.querySelectorAll(".radio-group");
    if (!groups.length) return;

    groups.forEach(group => {
      const name = group.dataset.radioName;
      if (!name) return;

      const radioBtns = group.querySelectorAll('button.radio-group__item');
      if (!radioBtns.length) return;

      this._fillOldRadio(group, name, radioBtns);

      if (this._errors[name]) {
        this._renderRadioError(group, radioBtns, this._errors[name]);
      }

      group.addEventListener('radio:change', () => {
        this._clearRadioError(group, radioBtns);
      });
    });
  }
  /**
   * Điền lại old value cho radio group:
   * cập nhật data-state trên từng button và value của hidden input (nếu đã tồn tại).
   *
   * Lưu ý thứ tự khởi tạo: FormHandler nên chạy trước RadioHandler.
   * RadioHandler._setDefaultState sẽ thấy data-state đã được set và bỏ qua,
   * nhưng vẫn tạo hidden input với data-radio-default-value. Ta override lại
   * hidden input value sau đó thông qua sự kiện nếu cần.
   * @private
   * @param {HTMLElement} group
   * @param {string} name
   * @param {NodeList} radioBtns
   */
  _fillOldRadio(group, name, radioBtns) {
    const oldValue = this._old[name];
    if (oldValue === undefined) return;

    const strOld = String(oldValue);
    radioBtns.forEach(btn => {
      btn.dataset.state = btn.value === strOld ? "checked" : "unchecked";
    });

    const hiddenInput = group.querySelector('input[type="hidden"]');
    if (hiddenInput) hiddenInput.value = strOld;
  }
  /**
   * Render lỗi cho radio group.
   * - data-field-invalid + aria-invalid trên .radio-group container
   * - aria-invalid trên mỗi button.radio-group__item
   * - Một .field__error span duy nhất là direct child của .radio-group,
   * @private
   * @param {HTMLElement} group
   * @param {NodeList} radioBtns
   * @param {string|string[]} messages
   */
  _renderRadioError(group, radioBtns, messages) {
    const message = Array.isArray(messages) ? messages[0] : messages;

    group.ariaInvalid = "true";
    radioBtns.forEach(btn => btn.ariaInvalid = "true");

    group.querySelector(':scope > .field__error')?.remove();

    const span = document.createElement('span');
    span.className = 'field__error';
    span.innerHTML = this._icons['error'];
    span.appendChild(document.createTextNode(message));

    group.insertAdjacentElement('afterend', span);
  }
  /**
   * Xoá trạng thái lỗi của radio group và tất cả button.
   * @private
   * @param {HTMLElement} group
   * @param {NodeList} radioBtns
   */
  _clearRadioError(group, radioBtns) {
    group.removeAttribute('aria-invalid');
    radioBtns.forEach(btn => btn.ariaInvalid = null);
    group.querySelector(':scope > .field__error')?.remove();
  }
}
