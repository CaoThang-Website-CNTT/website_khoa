class Utils {
  /**
   * Kiểm tra element có đang nằm trong viewport hiện tại không
   * @param {HTMLElement} element
   * @param {number} [offset=0] padding (để thu nhỏ viewport)
   * @returns {boolean} Kết quả
   */
  static isInViewport(element, offset = 0) {
    const rect = element.getBoundingClientRect();

    return (
      rect.top >= -offset &&
      rect.left >= -offset &&
      rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) + offset &&
      rect.right <= (window.innerWidth || document.documentElement.clientWidth) + offset
    );
  }
  static toCleanAscii(str) {
    if (!str) return "";

    return str
      .trim()
      .toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .replace(/đ/g, "d");
  }
}