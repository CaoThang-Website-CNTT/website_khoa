/**
 * Class Modal.
 * Cung cấp các phương thức để hiển thị, ẩn và quản lý trạng thái của modal.
 */
class Modal {
  /**
   * Khởi tạo một đối tượng Modal.
   * * @param {HTMLElement|string} element - Element HTML của modal hoặc chuỗi CSS selector.
   * @param {Object} [options={}] - Các cấu hình tuỳ chọn.
   * @param {boolean} [options.overlay=true] - Bật/tắt màng mờ nền (overlay).
   * @throws {Error} Quăng lỗi nếu không tìm thấy element trên DOM.
   */
  constructor(element, options = {}) {
    this.element = typeof element === 'string'
      ? document.querySelector(element)
      : element;

    if (!this.element) {
      throw new Error('Không tìm thấy modal element');
    }

    this.element._modalInstance = this;

    this.options = {
      overlay: true,
      ...options
    };

    this.overlay = null;

    this._init();
  }

  /**
   * Khởi tạo các thành phần cơ bản và gán sự kiện.
   * @private
   */
  _init() {
    this.overlay = this._createOverlay();
    this._bindCloseButtons();
    this._bindOverlay();
  }

  /**
   * Tạo mới hoặc lấy overlay có sẵn của modal.
   * @private
   * @returns {HTMLElement|null} Element overlay được tạo hoặc tìm thấy.
   */
  _createOverlay() {
    const existing = this.element.nextElementSibling;
    if (existing?.classList.contains('modal-overlay')) {
      return existing;
    }

    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    this.element.parentNode.insertBefore(overlay, this.element.nextSibling);
    return overlay;
  }

  /**
   * Tìm và gán sự kiện click cho tất cả các nút đóng bên trong modal.
   * @private
   */
  _bindCloseButtons() {
    const closeButtons = this.element.querySelectorAll('[data-modal-close]');
    closeButtons.forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        this.hide();
      });
    });
  }

  /**
   * Gán sự kiện click vào overlay để đóng modal nếu được cấu hình.
   * @private
   */
  _bindOverlay() {
    if (!this.overlay) return;

    if (this.options.overlay === true) {
      this.overlay.addEventListener('click', () => this.hide());
    }
  }

  /**
   * Kiểm tra trạng thái hiện tại của modal.
   * @public
   * @returns {boolean} `true` nếu modal đang mở, ngược lại là `false`.
   */
  isOpen() {
    return this.element.getAttribute('data-state') === 'open';
  }

  /**
   * Hiển thị modal.
   * @public
   */
  show() {
    if (this.isOpen()) return;

    this.element.setAttribute('data-state', 'open');
    if (this.overlay) {
      this.overlay.setAttribute('data-state', 'open'); // Thêm dòng này để overlay cũng đồng bộ trạng thái
    }
  }

  /**
   * Ẩn modal.
   * @public
   */
  hide() {
    if (!this.isOpen()) return;

    this.element.setAttribute('data-state', 'closed');

    if (this.overlay) {
      this.overlay.setAttribute('data-state', 'closed');
    }
  }

  /**
   * Đảo ngược trạng thái của modal (mở thành đóng, đóng thành mở).
   * @public
   */
  toggle() {
    this.isOpen() ? this.hide() : this.show();
  }

  /**
   * Lấy instance Modal đã được khởi tạo từ một element.
   * @static
   * @param {HTMLElement|string} element - Element HTML hoặc chuỗi CSS selector.
   * @returns {Modal|null} Instance của Modal, hoặc `null` nếu chưa được khởi tạo.
   */
  static getInstance(element) {
    const el = typeof element === 'string'
      ? document.querySelector(element)
      : element;
    return el?._modalInstance || null;
  }
}

// Tự động khởi tạo
document.addEventListener('DOMContentLoaded', () => {
  const triggers = document.querySelectorAll('[data-modal-trigger]');

  triggers.forEach(trigger => {
    trigger.addEventListener('click', function (e) {
      e.preventDefault();

      const selector = this.getAttribute('data-modal-trigger');
      const target = document.querySelector(selector);

      if (!target) return;

      const modal = target._modalInstance || new Modal(target);

      modal.show();
    });
  });
});