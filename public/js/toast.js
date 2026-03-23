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
   * Sử dụng Font Awesome SVG.
   * Được dùng trong `_template()` để render icon tương ứng với `type`.
   *
   * @static
   * @readonly
   * @type {{ success: string, error: string, warning: string, info: string, loading: string }}
   */
  static ICONS = Object.freeze({
    success: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor"><path d="M256 512a256 256 0 1 1 0-512 256 256 0 1 1 0 512zM374 145.7c-10.7-7.8-25.7-5.4-33.5 5.3L221.1 315.2 169 263.1c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l72 72c5 5 11.8 7.5 18.8 7s13.4-4.1 17.5-9.8L379.3 179.2c7.8-10.7 5.4-25.7-5.3-33.5z"/></svg>',
    error: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor"><path d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM167 167c9.4-9.4 24.6-9.4 33.9 0l55 55 55-55c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-55 55 55 55c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-55-55-55 55c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l55-55-55-55c-9.4-9.4-9.4-24.6 0-33.9z"/></svg>',
    warning: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor"><path d="M256 0c14.7 0 28.2 8.1 35.2 21l216 400c6.7 12.4 6.4 27.4-.8 39.5S486.1 480 472 480L40 480c-14.1 0-27.2-7.4-34.4-19.5s-7.5-27.1-.8-39.5l216-400c7-12.9 20.5-21 35.2-21zm0 352a32 32 0 1 0 0 64 32 32 0 1 0 0-64zm0-192c-18.2 0-32.7 15.5-31.4 33.7l7.4 104c.9 12.5 11.4 22.3 23.9 22.3 12.6 0 23-9.7 23.9-22.3l7.4-104c1.3-18.2-13.1-33.7-31.4-33.7z"/></svg>',
    info: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor"><path d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM224 160a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm-8 64l48 0c13.3 0 24 10.7 24 24l0 88 8 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-80 0c-13.3 0-24-10.7-24-24s10.7-24 24-24l24 0 0-64-24 0c-13.3 0-24-10.7-24-24s10.7-24 24-24z"/></svg>',
    loading: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor"><path d="M222.7 32.1c5 16.9-4.6 34.8-21.5 39.8-79.3 23.6-137.1 97.1-137.1 184.1 0 106 86 192 192 192s192-86 192-192c0-86.9-57.8-160.4-137.1-184.1-16.9-5-26.6-22.9-21.5-39.8s22.9-26.6 39.8-21.5C434.9 42.1 512 140 512 256 512 397.4 397.4 512 256 512S0 397.4 0 256c0-116 77.1-213.9 182.9-245.4 16.9-5 34.8 4.6 39.8 21.5z"/></svg>',
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
    if (this._stack.length > this._config.max_visible) {
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
        const y = i * this._config.collapsedGap;
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
      this._stackHeight ? `${this._stackHeight}px` : '0px'
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