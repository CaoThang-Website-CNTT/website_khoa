import { BlockInspector } from "./block_inspector.js";
import { EditorBlock } from './blocks/editor_block.js';
import { registry } from "./block_registry.js";

/** Quản lý UI */
export class EditorUI {
  /** @type {BlockInspector} */
  #inspector;
  /** @type {EditorEventBus} */
  #bus;

  constructor(bus, registry) {
    this.#bus = bus;
    this.#inspector = new BlockInspector();

    // Tham chiếu các phần tử DOM
    this.leftPanel = document.querySelector('#be-left');
    this.rightPanel = document.querySelector('#be-right');

    this.blockSettingsPanel = document.querySelector("#be-block-settings-panel");
    this.postSettingsPanel = document.querySelector("#be-post-settings-panel");

    this.canvasContainer = document.querySelector('#be-block-list');

    // Khởi tạo trạng thái UI
    this.leftPanelCollapsed = false;
    this.rightPanelCollapsed = false;

    this.#initPanels();
    this.#initPanelToggle();
    this.#initMetaUIEvents();
  }
  /**
 * Khởi tạo 2 panel
 * Left panel - block inventory
 */
  #initPanels() {
    const menuContainer = document.querySelector("#be-blocks-menu-panel");
    menuContainer.innerHTML = '';

    const fragment = document.createDocumentFragment();
    const groupedBlocks = registry.getGrouped();

    for (const [groupKey, groupData] of Object.entries(groupedBlocks)) {

      const groupWrapper = document.createElement('div');
      groupWrapper.className = 'be-block-group';

      const groupLabel = document.createElement('span');
      groupLabel.className = 'be-block-group__label';
      groupLabel.textContent = groupData.label;
      groupWrapper.appendChild(groupLabel);

      groupData.items.forEach(schema => {
        const blockBtn = document.createElement('button');
        blockBtn.type = 'button';
        blockBtn.className = 'btn be-block-btn';
        blockBtn.setAttribute('data-variant', 'outline');

        blockBtn.setAttribute('data-add-block', schema.name);

        const iconDiv = document.createElement('div');
        iconDiv.className = 'be-block-btn__icon';
        iconDiv.innerHTML = schema.icon;

        const textWrapper = document.createElement('div');

        const nameDiv = document.createElement('div');
        nameDiv.className = 'be-block-btn__name';
        nameDiv.textContent = schema.title;

        textWrapper.appendChild(nameDiv);

        blockBtn.appendChild(iconDiv);
        blockBtn.appendChild(textWrapper);

        blockBtn.addEventListener('click', () => {
          this.#bus.dispatch("block:add_request", { type: blockBtn.dataset.addBlock })
        });

        groupWrapper.appendChild(blockBtn);
      });

      fragment.appendChild(groupWrapper);
    }

    menuContainer.appendChild(fragment);
  }

  #initPanelToggle() {
    document.getElementById('be-toggle-left')?.addEventListener('click', () => {
      const panelState = this.leftPanel?.dataset.bePanelState;
      this.leftPanel?.setAttribute("data-be-panel-state", panelState === "collapsed" ? "open" : "collapsed");
    });
    document.getElementById('be-toggle-right')?.addEventListener('click', () => {
      const panelState = this.rightPanel?.dataset.bePanelState;
      this.rightPanel?.setAttribute("data-be-panel-state", panelState === "collapsed" ? "open" : "collapsed");
    });
  }

  #initMetaUIEvents() {
    const metaElements = document.querySelectorAll('[data-be-meta-key]');

    metaElements.forEach(el => {
      if (el.classList.contains('switch')) {
        el.addEventListener('click', () => {
          const key = el.dataset.beMetaKey;
          const newValue = el.dataset.switchState !== 'checked'; // Đảo ngược trạng thái
          this.#bus.dispatch('meta:update_request', { key, value: newValue });
        });
      }
      else {
        el.addEventListener('input', (e) => {
          const key = el.dataset.beMetaKey;
          let value = e.target.value || el.textContent.trim();

          if (el.type === 'number') value = parseInt(value) || 0;
          this.#bus.dispatch('meta:update_request', { key, value });
        });
      }
    });

    this.#bus.subscribe('meta:updated', ({ key, value }) => {
      const elements = document.querySelectorAll(`[data-meta-key="${key}"]`);

      elements.forEach(el => {
        if (el.classList.contains('switch')) {
          const stateStr = value ? 'checked' : 'unchecked';
          el.dataset.switchState = stateStr;

          const thumb = el.querySelector('.switch__thumb');
          if (thumb) thumb.dataset.switchState = stateStr;

          // Xử lý riêng cho nút View Botting
          if (key === 'show_view_count') {
            const wrapper = document.querySelector('#be-view-botting-wrapper');
            if (wrapper) wrapper.classList.toggle("hidden");
          }
        }
        else {
          if (el.value != value) el.value = value;
        }
      });

      const previewElements = document.querySelectorAll(`[data-be-meta-preview="${key}"]`);

      previewElements.forEach(previewEl => {
        const action = previewEl.dataset.bePreviewAction || 'text';

        if (action === 'toggle') {
          previewEl.classList.toggle("hidden");
        }

        else if (action === 'text') {
          let displayText = value;

          const sourceInput = document.querySelector(`select[data-be-meta-key="${key}"]`);
          if (sourceInput && sourceInput.options) {
            displayText = sourceInput.options[sourceInput.selectedIndex]?.text || value;
          }

          if (previewEl.textContent !== displayText) {
            previewEl.textContent = displayText || previewEl.dataset.previewDefault || '';
          }
        }

        else if (action === 'status-badge') {
          const statusLabels = { draft: 'Nháp', published: 'Đã xuất bản', archived: 'Lưu trữ' };
          previewEl.textContent = statusLabels[value] || 'Nháp';
          previewEl.dataset.variant = value === 'published' ? 'primary' : 'secondary';
        }
      });
    });

    this.#bus.dispatch('meta:sync_request');

    // ==========================================
    // Tìm button tab dựa vào thuộc tính data của chúng
    const tabPostBtn = document.querySelector('[data-tabs-trigger="be-post-settings-panel"]');
    const tabBlockBtn = document.querySelector('[data-tabs-trigger="be-block-settings-panel"]');

    if (tabPostBtn && tabBlockBtn) {
      tabPostBtn.addEventListener('click', () => this.switchRightTab('post'));
      tabBlockBtn.addEventListener('click', () => this.switchRightTab('block'));
    }
  }

  switchRightTab(tabName) {
    const targetPanelId = tabName === 'post' ? 'be-post-settings-panel' : 'be-block-settings-panel';

    const targetBtn = document.querySelector(`.be-right-tab[data-tabs-trigger="${targetPanelId}"]`);

    if (targetBtn) {
      targetBtn.click();
    }
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
    this.switchRightTab('block');

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
    this.switchRightTab('post');
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