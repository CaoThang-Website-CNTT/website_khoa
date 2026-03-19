document.addEventListener("DOMContentLoaded", () => {
  const formHandler = new FormHandler();
  formHandler.init();
});

class FormHandler {
  constructor() {
    this._fields = document.querySelectorAll(".field");
    this._form = document.querySelector("form:has(.field-group, .field)");
    this._errors = window.__errors__ ?? {};
    this._old = window.__old__ ?? {};

    this._icons = {
      error: `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor"><path d="M256 512a256 256 0 1 1 0-512 256 256 0 1 1 0 512zm0-192a32 32 0 1 0 0 64 32 32 0 1 0 0-64zm0-192c-18.2 0-32.7 15.5-31.4 33.7l7.4 104c.9 12.6 11.4 22.3 23.9 22.3 12.6 0 23-9.7 23.9-22.3l7.4-104c1.3-18.2-13.1-33.7-31.4-33.7z"/></svg>
    `
    }
  }

  /**
   * Khởi tạo các logic liên quan đến form.
   * @private
   */
  init() {
    if (!this._fields.length) return;

    this._fields.forEach(field => {
      const fieldInput = field.querySelector(".field__input");
      if (!fieldInput) return;

      const rules = this._getFieldRules(field.dataset);
      rules.forEach(rule => this._applyRule(field, fieldInput, rule));

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
   * @param {DOMStringMap} dataset - Thuộc tính dataset của một element (field).
   * @returns {string[]} Mảng các data attribute đã được lấy.
   */
  _getFieldRules(dataset) {
    const rules = [];
    for (const key in dataset) {
      if (key.startsWith("field")) {
        rules.push(key);
      }
    }
    return rules;
  }

  /**
   * Áp dụng rule lên các field input.
   * @private
   * @param {HTMLElement} field - Field của input sẽ áp dụng.
   * @param {HTMLElement} input - Input element sẽ áp dụng.
   * @param {string} ruleType - Loại rule sẽ được áp dụng.
   */
  _applyRule(field, input, ruleType) {
    switch (ruleType) {
      case "fieldReadonly":
        input.readOnly = true;
        break;
      case "fieldDisabled":
        input.disabled = true;
        break;
      case "fieldRequired":
        input.required = true;
        break;
      default:
        console.warn(`FormHandler: Không nhận dạng được ruleType "${ruleType}"`);
    }
  }

  /**
   * Điền lại giá trị cũ vào input từ dữ liệu old input của session trước.
   * Hỗ trợ các loại input: text, select, checkbox, radio.
   * @private
   * @param {HTMLElement} input - Input element cần điền lại giá trị.
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
   * Hiển thị thông báo lỗi cho một field và đánh dấu trạng thái không hợp lệ.
   * Nếu field đã có lỗi trước đó, lỗi cũ sẽ bị xoá trước khi render lỗi mới.
   * @private
   * @param {HTMLElement} field - Field container chứa input cần hiển thị lỗi.
   * @param {HTMLElement} input - Input element tương ứng với field.
   * @param {string|string[]} messages - Thông báo lỗi hoặc mảng thông báo lỗi. Chỉ hiển thị thông báo đầu tiên.
   */
  _renderError(field, input, messages) {
    const message = Array.isArray(messages) ? messages[0] : messages;

    field.dataset.fieldInvalid = true;
    field.ariaInvalid = true;

    field.querySelector('.field__error')?.remove();

    const span = document.createElement('span');
    span.className = 'field__error';
    span.innerHTML = this._icons['error'];
    span.appendChild(document.createTextNode(message));
    input.insertAdjacentElement('afterend', span);
  }

  /**
   * Xoá trạng thái lỗi của một field, bao gồm thuộc tính data-field-invalid,
   * aria-invalid và phần tử thông báo lỗi .field__error.
   * @private
   * @param {HTMLElement} field - Field container cần xoá trạng thái lỗi.
   */
  _clearError(field) {
    delete field.dataset.fieldInvalid;
    field.ariaInvalid = false;
    field.querySelector('.field__error')?.remove();
  }

  /**
   * Gắn sự kiện tự động xoá lỗi khi người dùng bắt đầu nhập liệu vào input.
   * Lắng nghe cả sự kiện `input` (text, textarea) và `change` (select, checkbox, radio).
   * @private
   * @param {HTMLElement} field - Field container chứa input.
   * @param {HTMLElement} input - Input element cần gắn sự kiện.
   */
  _bindClearOnInput(field, input) {
    const clear = () => this._clearError(field);
    input.addEventListener('input', clear);
    input.addEventListener('change', clear);
  }
}