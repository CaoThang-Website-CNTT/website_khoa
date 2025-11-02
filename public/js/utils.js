/**
 * Kiểm tra element có đang nằm trong viewport hiện tại không
 * @param {HTMLElement} element
 * @param {number} [offset=0] padding (để thu nhỏ viewport)
 * @returns {boolean} Kết quả
 */
function isInViewport(element, offset = 0) {
  const rect = element.getBoundingClientRect();

  return (
    rect.top >= -offset &&
    rect.left >= -offset &&
    rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) + offset &&
    rect.right <= (window.innerWidth || document.documentElement.clientWidth) + offset
  );
}