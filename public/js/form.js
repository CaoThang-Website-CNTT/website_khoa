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
      error: `<i class="fa-solid fa-circle-exclamation"></i>`
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
   * @param {DOMStringMap} dataset - Thuộc tính dataset của một element (field).
   * @returns {Array<{type: string, value: string}>} Mảng các data attribute đã được lấy.
   */
  _getFieldRules(dataset) {
    const rules = [];
    for (const key in dataset) {
      if (key.startsWith("field")) {
        rules.push({
          type: key,
          value: dataset[key]
        });
      }
    }
    return rules;
  }

  /**
   * Áp dụng rule lên các field input.
   * @private
   * @param {HTMLElement} input - Input element sẽ áp dụng.
   * @param {Object} rule - Loại rule sẽ được áp dụng.
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
        if (isNumericOrDate) {
          input.min = value;
        } else {
          input.minLength = value;
        }
        break;
      case "fieldMax":
        if (isNumericOrDate) {
          input.max = value;
        } else {
          input.maxLength = value;
        }
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