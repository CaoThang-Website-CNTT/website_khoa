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
    const titleInput = document.querySelector('#be-canvas-title');
    const authorSelect = document.querySelector('#be-author-select');
    const statusSelect = document.querySelector('#be-status-select');
    const excerptInput = document.querySelector('#be-excerpt-input');

    if (titleInput) {
      titleInput.addEventListener('input', (e) => {
        const newTitle = e.target.innerText.trim();
        this.#bus.dispatch('meta:update_request', { key: 'title', value: newTitle });
      });

      titleInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
        }
      });
    }

    if (authorSelect) {
      authorSelect.addEventListener('change', (e) => {
        this.#bus.dispatch('meta:update_request', { key: 'author_id', value: e.target.value });
      });
    }

    if (statusSelect) {
      statusSelect.addEventListener('change', (e) => {
        this.#bus.dispatch('meta:update_request', { key: 'status', value: e.target.value });
      });
    }

    if (excerptInput) {
      excerptInput.addEventListener('input', (e) => {
        this.#bus.dispatch('meta:update_request', { key: 'excerpt', value: e.target.value });
      });
    }

    this.#bus.subscribe('meta:updated', ({ key, value }) => {
      this.#syncMetaUIToState(key, value);
    });

    if (this.tabPostBtn && this.tabBlockBtn) {
      this.tabPostBtn.addEventListener('click', () => this.switchRightTab('post'));
      this.tabBlockBtn.addEventListener('click', () => this.switchRightTab('block'));
    }
  }

  #syncMetaUIToState(key, value) {
    if (key === 'author_id') {
      const select = document.querySelector('#be-author-select');
      if (select && select.value !== String(value)) select.value = value;
    }
    if (key === 'status') {
      const select = document.querySelector('#be-status-select');
      if (select && select.value !== value) select.value = value;
    }
    if (key === 'title') {
      const postTitle = document.querySelector('#be-post-title');
      postTitle.innerText = value || 'Không có tiêu đề';
    }
    if (key === 'excerpt') {
      const excerptInput = document.querySelector('#be-excerpt-input');
      if (excerptInput && excerptInput.value !== value) {
        excerptInput.value = value || '';
      }
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