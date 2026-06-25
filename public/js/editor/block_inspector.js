export class BlockInspector {
  /**
   * Render các cài đặt dùng chung cho mọi block (VD: Anchor ID)
   * @param {EditorBlock} block 
   * @param {EditorEventBus} bus 
   */
  renderCommonSettings(block, bus) {
    const container = document.createElement('div');
    container.className = 'field-group';

    container.innerHTML = `
      <div class="field">
        <label class="field__label">HTML Anchor (ID)</label>
        <input type="text" class="field__input be-anchor-input" 
               placeholder="Ví dụ: section-1" 
               value="${block.data.meta.anchor_id || ''}">
        <p class="field__description" style="font-size: var(--text-xs); color: var(--muted-foreground); margin-top: 0.25rem;">
          Sử dụng để tạo liên kết nội bộ hoặc tùy chỉnh CSS.
        </p>
      </div>
    `;

    const input = container.querySelector('.be-anchor-input');
    input.addEventListener('input', (e) => {
      const value = e.target.value.trim().replace(/\s+/g, '-');
      block.data.meta.anchor_id = value;

      // Đồng bộ vào DOM ngay lập tức
      if (block.dom) {
        block.dom.id = value;
      }

      // Dispatch để các panel khác (như List View) cập nhật theo
      bus.dispatch('block:input', { blockId: block.id });
      bus.dispatch('block:updated', { block, silent: true });
    });

    return container;
  }

  renderTypographySettings() {
    const container = document.createElement('div');
    // Typography settings could be added here in the future
    return container;
  }
}
