/**
 * block_serializer.js
 * ===================
 * Đọc trạng thái hiện tại của BlockList → tạo JSON array để submit.
 * 
 * Chạy ngay trước khi form submit (không chạy liên tục).
 * Nguồn thứ tự là DOM (blockList.getOrderedItems()),
 * KHÔNG phải thứ tự insert — đảm bảo đúng sau khi drag.
 * 
 * Mỗi block trong output tuân theo contract:
 * {
 *   id:      string   (duy nhất, stable across edits)
 *   type:    string   (phải có trong BlockRegistry)
 *   version: number   (lấy từ Registry descriptor)
 *   order:   number   (index trong DOM tại thời điểm submit)
 *   data:    object   (do form.getData() cung cấp)
 * }
 * 
 * Public API:
 *   serialize(blockList)            — trả về array block objects
 *   attachToForm(formEl, blockList, hiddenInputEl)  — tự động serialize khi submit
 */

const BlockSerializer = (() => {

  /**
   * Serialize toàn bộ danh sách block theo thứ tự DOM hiện tại.
   * 
   * @param {ReturnType<createBlockList>} blockList
   * @returns {Array<object>}  Mảng block objects sẵn sàng JSON.stringify
   */
  function serialize(blockList) {
    return blockList.getOrderedItems().map((item, index) => {
      const descriptor = BlockRegistry.get(item.type);
      const data       = item.getData();

      return {
        id:      item.id,
        type:    item.type,
        version: descriptor?.version ?? 1,
        order:   index,
        data,
      };
    });
  }

  /**
   * Gắn serializer vào form submit event.
   * Tự động ghi JSON vào hidden input ngay trước khi form được submit.
   * 
   * @param {HTMLFormElement}             formEl
   * @param {ReturnType<createBlockList>} blockList
   * @param {HTMLInputElement}            hiddenInputEl  input[type=hidden][name=content_json]
   * @param {object}                      options
   * @param {Function}                    options.onValidate  Callback (blocks) → bool, nếu false thì cancel submit
   */
  function attachToForm(formEl, blockList, hiddenInputEl, { onValidate } = {}) {
    formEl.addEventListener('submit', (e) => {
      const blocks = serialize(blockList);

      // Validation tối thiểu ở client: không cho submit rỗng
      if (blocks.length === 0) {
        e.preventDefault();
        alert('Bài viết phải có ít nhất một block nội dung.');
        return;
      }

      // Custom validation hook (tuỳ chọn)
      if (onValidate && !onValidate(blocks)) {
        e.preventDefault();
        return;
      }

      hiddenInputEl.value = JSON.stringify(blocks);
      // Form tiếp tục submit bình thường
    });
  }

  return { serialize, attachToForm };
})();
