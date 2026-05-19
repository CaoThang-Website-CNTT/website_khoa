/**
 * 
 */
class Toast {
  /**
 * Danh sách các vị trí hợp lệ mà Toast hỗ trợ.
 * Được dùng để validate giá trị `position` trong constructor.
 * Đây là hằng số — không thay đổi theo instance.
 *
 * @static
 * @readonly
 * @type {string[]}
 */
  static VALID_POSITIONS = Object.freeze([
    'top-left', 'top-center', 'top-right',
    'bottom-left', 'bottom-center', 'bottom-right',
  ]);

  /**
   * Tập hợp icon SVG đại diện cho từng trạng thái toast.
   * Sử dụng Font Awesome.
   * Được dùng trong `_template()` để render icon tương ứng với `type`.
   *
   * @static
   * @readonly
   * @type {{ success: string, error: string, warning: string, info: string, loading: string }}
   */
  static ICONS = Object.freeze({
    success: '<i class="fa-solid fa-circle-check"></i>',
    error: '<i class="fa-solid fa-circle-xmark"></i>',
    warning: '<i class="fa-solid fa-triangle-exclamation"></i>',
    info: '<i class="fa-solid fa-circle-info"></i>',
    loading: '<i class="fa-solid fa-circle-notch fa-spin"></i>',
  });

  /**
 * Khởi tạo một instance Toast với cấu hình riêng.
 * Mỗi trang có thể tạo instance với config khác nhau mà không ảnh hưởng lẫn nhau.
 *
 * @param {object}  [options={}]                      - Tùy chọn cấu hình
 *
 * @param {string}  [options.position='bottom-center'] - Vị trí hiển thị của toast trên màn hình.
 *   Các giá trị hợp lệ: `'top-left'` | `'top-center'` | `'top-right'`
 *                      | `'bottom-left'` | `'bottom-center'` | `'bottom-right'`
 *   Nếu truyền giá trị không hợp lệ sẽ fallback về `'bottom-center'`.
 *
 * @param {number}  [options.maxVisible=3]            - Số lượng toast tối đa hiển thị cùng lúc
 *   trong stack. Toast cũ hơn sẽ bị xóa khi vượt quá giới hạn này.
 *   Phải là số nguyên dương. Giá trị không hợp lệ fallback về `3`.
 *
 * @param {number}  [options.collapsedGap=8]          - Khoảng cách (px) giữa các toast
 *   khi stack đang ở trạng thái thu gọn (chưa hover).
 *   Dùng để tạo hiệu ứng chiều sâu — toast phía sau nhô ra một khoảng nhỏ.
 *   Phải là số nguyên. Giá trị không hợp lệ fallback về `8`.
 *
 * @param {number}  [options.expandedGap=8]           - Khoảng cách (px) giữa các toast
 *   khi stack được mở rộng (khi người dùng hover vào vùng toast).
 *   Phải là số nguyên. Giá trị không hợp lệ fallback về `8`.
 *
 * @param {number}  [options.duration=4000]           - Thời gian tồn tại (ms) của toast
 *   trước khi tự động biến mất. Không áp dụng cho toast loại `loading` —
 *   loại này tồn tại vĩnh viễn cho đến khi bị dismiss thủ công hoặc qua `promise()`.
 *   Phải là số nguyên. Giá trị không hợp lệ fallback về `4000`.
 *
 * @example
 * // Dashboard — toast ở giữa trên cùng, tồn tại 5 giây
 * window.toast = new Toast({ position: 'top-center', duration: 5000 });
 *
 * @example
 * // Trang người dùng — toast góc trên trái, tối đa 2 cái
 * window.toast = new Toast({ position: 'top-left', maxVisible: 2 });
 */
  constructor(options = {}) {
    /**
     * Cấu hình instance — bất biến sau khi khởi tạo.
     * Được khóa bằng Object.freeze() để tránh bị thay đổi từ bên ngoài.
     *
     * @private
     * @readonly
     * @type {{ position: string, maxVisible: number, collapsedGap: number, expandedGap: number, duration: number }}
     */
    this._config = Object.freeze({
      position: Toast.VALID_POSITIONS.includes(options.position)
        ? options.position
        : 'bottom-center',
      maxVisible: Number.isInteger(options.maxVisible) ? options.maxVisible : 3,
      collapsedGap: Number.isInteger(options.collapsedGap) ? options.collapsedGap : 8,
      expandedGap: Number.isInteger(options.expandedGap) ? options.expandedGap : 8,
      duration: Number.isInteger(options.duration) ? options.duration : 4000,
    });

    /**
     * Stack chứa các toast đang hiển thị.
     * Index 0 là toast mới nhất (front), index cao nhất là toast cũ nhất (back).
     *
     * @private
     * @type {Array<{ id: string, element: HTMLElement, timerId: number|null }>}
     */
    this._stack = [];

    /**
     * Trạng thái mở rộng của stack — true khi người dùng hover vào container.
     *
     * @private
     * @type {boolean}
     */
    this._expanded = false;

    /**
     * Bộ đếm để tạo id duy nhất cho mỗi toast.
     *
     * @private
     * @type {number}
     */
    this._idSeq = 0;

    /** @private */
    this._isTop = this._config.position.startsWith('top');

    /** @private */
    this._container = this._createContainer();
  }

  /**
   * Hiển thị toast loại `success`.
   * @param {string}  title - Tiêu đề chính của toast.
   * @param {string} [desc] - Mô tả phụ (tuỳ chọn).
   * @returns {string} id của toast vừa tạo.
   */
  success(title, desc) { return this._show('success', title, desc); }

  /**
   * Hiển thị toast loại `error`.
   * @param {string}  title
   * @param {string} [desc]
   * @returns {string}
   */
  error(title, desc) { return this._show('error', title, desc); }

  /**
   * Hiển thị toast loại `warning`.
   * @param {string}  title
   * @param {string} [desc]
   * @returns {string}
   */
  warn(title, desc) { return this._show('warning', title, desc); }

  /**
   * Hiển thị toast loại `info`.
   * @param {string}  title
   * @param {string} [desc]
   * @returns {string}
   */
  info(title, desc) { return this._show('info', title, desc); }

  /**
   * Đóng một hoặc tất cả toast.
   * @param {string} [id] - Id toast cần đóng. Nếu không truyền, đóng tất cả.
   */
  dismiss(id) {
    if (id) {
      this.remove(id);
    } else {
      [...this._stack].forEach(t => this.remove(t.id));
    }
  }

  /**
   * Xóa một toast khỏi stack và DOM (có animation).
   * @param {string} id - Id của toast cần xóa.
   */
  remove(id) {
    const idx = this._stack.findIndex(t => t.id === id);
    if (idx === -1) return;

    clearTimeout(this._stack[idx].timerId);

    const element = this._stack[idx].element;

    element.style.transform = '';
    element.style.opacity = '';

    element.dataset.removed = 'true';
    this._stack.splice(idx, 1);

    setTimeout(() => {
      element.remove();
      this._updateStack();
    }, 280);
  }

  /**
   * Hiển thị toast dưới dạng Promise: loading → success | error.
   *
   * @param {Promise|Function} promiseOrFn - Promise hoặc hàm trả về Promise.
   * @param {object}   messages
   * @param {string}   messages.loading            - Nội dung toast khi đang chờ.
   * @param {string|Function} messages.success     - Nội dung khi thành công.
   *   Có thể là callback nhận kết quả: `(data) => \`Đã thêm ${data.name}\``
   * @param {string|Function} messages.error       - Nội dung khi thất bại.
   *   Có thể là callback nhận lỗi: `(err) => \`Lỗi: ${err.message}\``
   * @returns {Promise} Promise gốc được trả về để có thể chain tiếp nếu cần.
   *
   * @example
   * toast.promise(fetch('/admin/students', { method: 'POST', body: fd }), {
   *   loading: 'Đang lưu...',
   *   success: 'Lưu thành công!',
   *   error:   (err) => `Lỗi: ${err.message}`,
   * });
   */
  promise(promiseOrFn, messages) {
    const p = typeof promiseOrFn === 'function' ? promiseOrFn() : promiseOrFn;
    const id = this._show('loading', messages.loading);

    p.then((result) => {
      this.remove(id);
      const msg = typeof messages.success === 'function'
        ? messages.success(result)
        : messages.success;
      setTimeout(() => this._show('success', msg), 60);
    }).catch((err) => {
      this.remove(id);
      const msg = typeof messages.error === 'function'
        ? messages.error(err)
        : messages.error;
      setTimeout(() => this._show('error', msg), 60);
    });

    return p;
  }

  /**
   * Hàm tạo toast container (portal)
   * @private
   * @returns {HTMLDivElement} Phần tử container
   */
  _createContainer() {
    const element = document.createElement('div');
    element.id = 'toast-portal';
    element.dataset.position = this._config.position;
    document.body.appendChild(element);

    element.addEventListener('mouseenter', () => {
      if (this._stack.length > 1) {
        this._expanded = true;
        this._updateStack();
      }
    });
    element.addEventListener('mouseleave', () => {
      this._expanded = false;
      this._updateStack();
    });

    return element;
  }

  /**
   * Phương thức tạo ra một toast
   * @private
   * @param {string} type - loại
   * @param {string} title - tiêu đề
   * @param {string} desc - mô tả
   * @param {string} [duration] - thời gian tồn tại
   * @returns 
   */
  _show(type, title, desc, duration) {
    const loading = type === 'loading';
    const dur = duration ?? (loading ? 9999999 : this._config.duration);
    const id = 'toast-' + (++this._idSeq);

    const element = document.createElement('div');
    element.className = 'toast';
    element.dataset.variant = type;
    element.dataset.mounted = 'false';
    element.id = id;
    element.innerHTML = this._template(type, title, desc, loading);

    element.querySelector('.toast-close')?.addEventListener('click', (e) => {
      e.stopPropagation();
      this.remove(id);
    });

    this._container.appendChild(element);
    // Đẩy ở đầu stack và đẩy các phần tử khác về sau 1 ô
    this._stack.unshift({ id, element, timerId: null });

    // Kiểm tra phần tữ cũ nhất vượt quá max của config
    // -> Loại bỏ
    if (this._stack.length > this._config.maxVisible) {
      const oldest = this._stack[this._stack.length - 1];
      this.remove(oldest.id);
    }

    this._updateStack();

    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        element.dataset.mounted = 'true';
        this._updateStack();
      });
    });

    if (!loading) {
      this._stack[0].timerId = setTimeout(() => this.remove(id), dur);
    }

    return id;
  }

  /**
   * Truyền dữ liệu vào template đã chuẩn bị
   * @private
   * @param {string} type 
   * @param {string} title 
   * @param {string} desc 
   * @param {string} loading 
   * @returns 
   */
  _template(type, title, desc, loading) {
    const icon = Toast.ICONS[type];

    return `
      ${icon}
 
      <div class="toast-title">${this._esc(title)}</div>
 
      ${desc ? `<div class="toast-description">${this._esc(desc)}</div>` : ''}
 
      <div class="toast-action">
        ${!loading ? `<button class="toast-close" aria-label="Dismiss">&#x2715;</button>` : ''}
      </div>
     `;
  }

  _updateStack() {
    let expandedOffset = 0;
    const dir = this._isTop ? 1 : -1; // top: > 0, bottom: < 0
    let stackHeight = 0;

    this._stack.forEach((item, i) => {
      const element = item.element;
      element.style.zIndex = String(100 - i);
      stackHeight += element.getBoundingClientRect().height;

      if (element.dataset.mounted === 'false') return;

      if (i >= this._config.maxVisible && !this._expanded) {
        element.style.opacity = '0';
        element.style.transform = `translateY(0) scale(${1 - this._config.maxVisible * 0.04})`;
        return;
      }

      if (this._expanded) {
        element.style.opacity = '1';
        element.style.transform = `translateY(${dir * expandedOffset}px) scale(1)`;
        expandedOffset += element.offsetHeight + this._config.expandedGap;
      } else {
        const frontEl = this._stack[0]?.element;
        const frontHeight = frontEl ? frontEl.offsetHeight : 0;
        const currentHeight = element.offsetHeight;

        // Công thức tính khoảng dịch chuyển y để mép toast luôn nhô ra đúng bằng collapsedGap
        const y = i === 0
          ? 0
          : Math.max(i * this._config.collapsedGap, frontHeight + i * this._config.collapsedGap - currentHeight);

        const s = 1 - i * 0.04;
        const op = Math.max(0, 1 - i * 0.05);
        element.style.opacity = String(op);
        element.style.transform = `translateY(${dir * y}px) scale(${s})`;
      }
    });

    // Set chiều cao của toast mới nhất
    const frontEl = this._stack[0]?.element;
    this._container.style.setProperty(
      '--front-toast-height',
      frontEl ? `${frontEl.offsetHeight}px` : '0px'
    );

    // Set chiều cao của stack (tổng các toast)
    this._container.style.setProperty(
      '--toast-stack-height',
      stackHeight ? `${stackHeight}px` : '0px'
    );
  }

  /**
   * Escape chống XSS attack
   * @private
   * @param {string} str 
   * @returns 
   */
  _esc(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(String(str ?? '')));
    return d.innerHTML;
  }
}