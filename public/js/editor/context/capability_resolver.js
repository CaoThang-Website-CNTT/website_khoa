import { ContextAnalyzer } from './context_analyzer.js';

export class CapabilityResolver {
  /**
   * Map từ schema.supports key → danh sách capability tương ứng.
   * Block registry đăng ký thêm vào đây nếu muốn mở rộng.
   *
   * @type {Map<string, string[]>}
   */
  static #SUPPORTS_MAP = new Map([
    ['typography', ['bold', 'italic', 'underline', 'link']],
    ['tableOperations', ['insert-row', 'delete-row', 'insert-col', 'delete-col', 'merge-cells']],
    ['mediaReplace', ['replace-media']],
    ['codeLanguage', ['change-language']],
    ['captionEditable', ['edit-caption']],
    ['listNesting', ['indent', 'outdent']],
  ]);

  /**
   * Đăng ký capability group tùy chỉnh từ block ngoài (mở rộng không phá vỡ core).
   *
   * Được gọi từ block registry khi register block type mới:
   *   CapabilityResolver.registerSupport('myCustomFeature', ['do-x', 'do-y'])
   *
   * @param {string}   supportsKey
   * @param {string[]} capabilities
   */
  static registerSupport(supportsKey, capabilities) {
    if (this.#SUPPORTS_MAP.has(supportsKey)) {
      console.warn(`[CapabilityResolver] "${supportsKey}" đã tồn tại, sẽ bị ghi đè.`);
    }
    this.#SUPPORTS_MAP.set(supportsKey, [...capabilities]);
  }

  /**
   * Tính capabilities cuối cùng = base + block-specific.
   * Trả về Set để caller dễ kiểm tra `has()`.
   *
   * @param {'text'|'cursor'|'block'|'none'} contextType
   * @param {object|null} blockSchema — schema.supports của block hiện tại
   * @param {boolean} isTextSelection
   * @returns {Set<string>}
   */
  static resolve(contextType, blockSchema = null, isTextSelection = false) {
    /** @type {Set<string>} */
    const caps = new Set();

    // Tầng 1: Base capabilities theo context type
    const base = ContextAnalyzer.BASE_CAPABILITIES[contextType] ?? [];
    for (const c of base) caps.add(c);

    // Tầng 2: Block-specific từ schema.supports
    if (!blockSchema?.supports) return caps;

    for (const [key, enabled] of Object.entries(blockSchema.supports)) {
      if (!enabled) continue;
      const extras = this.#SUPPORTS_MAP.get(key);
      if (extras) {
        for (const c of extras) caps.add(c);
      }
    }

    // Lọc: nếu không phải text selection thì bỏ typography caps khỏi kết quả
    // (tránh hiện nút Bold khi user chỉ click vào block, không bôi đen gì)
    if (!isTextSelection) {
      for (const c of ContextAnalyzer.BASE_CAPABILITIES.text) {
        caps.delete(c);
      }
    }

    return caps;
  }
}