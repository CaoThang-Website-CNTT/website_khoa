import { BlockInspector } from "./block_inspector.js";
import { EditorBlock } from './blocks/editor_block.js';

/** Quản lý UI */
export class EditorUI {
  /**@type {BlockInspector} */
  #inspector;
  /**@type {EditorEventBus} */
  #bus;

  constructor(bus) {
    this.#bus = bus;
    this.#inspector = new BlockInspector();

    // Tham chiếu các phần tử DOM
    this.leftPanel = document.querySelector('#be-left');
    this.rightPanel = document.querySelector('#be-right');
    this.blockSettingsPanel = document.querySelector("#be-block-settings-panel");
    this.canvasContainer = document.querySelector('#be-block-list');

    // Khởi tạo trạng thái UI
    this.leftPanelCollapsed = false;
    this.rightPanelCollapsed = false;
  }

  toggleLeftPanel() {
    this.leftPanelCollapsed = !this.leftPanelCollapsed;
    this.sync();
  }

  toggleRightPanel() {
    this.rightPanelCollapsed = !this.rightPanelCollapsed;
    this.sync();
  }

  /**
   * 
   * @param {EditorBlock} block 
   */
  renderSettingsPanel(block) {
    this.blockSettingsPanel.innerHTML = "";
    const schema = block.schema;
    const currentData = block.data;

    const updateHandler = (patchData) => {
      this.#bus.dispatch('block:update_request', { blockId: block.id, payload: patchData });
    };

    if (schema.supports) {
      if (schema.supports.typography) {
        const typoUI = this.#inspector.renderTypographySettings(currentData, updateHandler);
        this.blockSettingsPanel.appendChild(typoUI);
      }
    }

    if (typeof block.renderInspectorControls === 'function') {
      const customUI = block.renderInspectorControls(block.data, { onUpdate: updateHandler });

      const divider = document.createElement('hr');
      this.blockSettingsPanel.appendChild(divider);
      this.blockSettingsPanel.appendChild(customUI);
    }
  }

  sync() {
    document.querySelector('#be-left').classList.toggle("collapsed");
    document.querySelector('#be-right').classList.toggle("collapsed");
  }

  clearSettingsPanel() {
    this.blockSettingsPanel.innerHTML = EditorEmptyState.rightBlockSettingsPanel.html;
  }

  showCanvasEmptyState() {
    this.canvasContainer.innerHTML = EditorEmptyState.canvas.html;
  }

  hideCanvasEmptyState() {
    const hintElement = this.canvasContainer.querySelector(`#${EditorEmptyState.canvas.id}`);
    if (hintElement) {
      hintElement.remove();
    }
  }
}
class EditorEmptyState {
  static canvas = {
    id: 'be-canvas-empty-hint',
    html: `
      <div id="be-canvas-empty-hint" class="empty">
        <div class="empty__header">
          <div class="empty__media">
            <i class="fa-solid fa-feather"></i>
          </div>
          <div class="empty__title">Bắt đầu soạn thảo</div>
          <div class="empty__description">
            Chọn block từ panel bên trái để thêm nội dung.
          </div>
        </div>
      </div>
    `
  };

  static leftPanelNavigationPanel = {
    id: 'be-list-view-panel-empty-hint',
    html: `
      <div id="be-list-view-panel-empty-hint" class="empty">
        <div class="empty__header">
          <div class="empty__media">
            <i class="fa-solid fa-cubes"></i>
          </div>
          <div class="empty__title">Chưa có tiêu đề nào</div>
          <div class="empty__description">
            Nhấn "Thêm tiêu đề" để tạo cấu trúc mới.
          </div>
        </div>
      </div>
    `
  };

  static rightBlockSettingsPanel = {
    id: 'be-block-settings-panel-empty-hint',
    html: `
      <div id="be-block-settings-panel-empty-hint" class="empty">
        <div class="empty__header">
          <div class="empty__media">
            <i class="fa-regular fa-square"></i>
          </div>
          <div class="empty__title">Chưa chọn block</div>
          <div class="empty__description">
            Chọn một block trong canvas để chỉnh sửa thuộc tính.
          </div>
        </div>
      </div>
    `
  };
}