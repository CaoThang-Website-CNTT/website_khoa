document.addEventListener("DOMContentLoaded", () => {
  const formHandler = new FormHandler();
  formHandler.init();
});

class FormHandler {
  constructor() {
    // Truy vấn DOM Elements khi khởi tạo
    this._fields = document.querySelectorAll(".field");
    this._form = document.querySelector("form:has(.field-group, .field)");
  }

  /**
   * Khởi tạo các logic liên quan đến form.
   * @private
   */
  init() {
    if (!this._fields.length) return;

    this._fields.forEach(field => {
      const fieldInput = field.querySelector(".field__input");
      if (!fieldInput) return; // Skip

      const rules = this.getFieldRules(field.dataset);

      rules.forEach(rule => {
        this.applyRule(fieldInput, rule);
      });
    });
  }

  /**
   * Lấy các data attribute bắt đầu với "field".
   * @param {DOMStringMap} dataset - Thuộc tính dataset của một element (field).
   * @returns {string[]} Mảng các data attribute đã được lấy.
   */
  getFieldRules(dataset) {
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
   * @param {HTMLElement} input - Input element sẽ áp dụng.
   * @param {string} ruleType - Loại rule sẽ được áp dụng.
   */
  applyRule(input, ruleType) {
    switch (ruleType) {
      case "fieldReadonly":
        input.readOnly = true;
        break;
      case "fieldDisabled":
        input.disabled = true;
        break;
      default:
        console.warn(`FormHandler: Không nhận dạng được ruleType "${ruleType}"`);
    }
  }
}