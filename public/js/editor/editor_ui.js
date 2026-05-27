import { BlockInspector } from "./block_inspector.js";
import { EditorBlock } from './blocks/editor_block.js';
import { registry } from "./block_registry.js";

/** Quản lý UI */
export class EditorUI {
  /** @type {BlockInspector} */
  #inspector;
  /** @type {EditorEventBus} */
  #bus;
  /** @type {EditorMetaBinder} */
  #metaBinder;

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
    this.#initMetaBinder();
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

        blockBtn.setAttribute('data-add-block', schema.type);

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

  #initMetaBinder() {
    this.#metaBinder = new EditorMetaBinder(document.body, this.#bus);
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
    const wrapper = document.createElement('div');
    wrapper.classList.add("field__set");
    const schema = block.schema;
    const currentData = block.data;

    const commonUI = this.#inspector.renderCommonSettings(block, this.#bus);
    wrapper.appendChild(commonUI);

    if (schema.supports) {
      if (schema.supports.typography) {
        const typoUI = this.#inspector.renderTypographySettings();
        this.blockSettingsPanel.appendChild(typoUI);
      }
    }

    if (typeof block.renderInspectorControls === 'function') {
      const customUI = block.renderInspectorControls();

      const divider = document.createElement('hr');
      divider.classList.add("separator");
      wrapper.appendChild(divider);
      wrapper.appendChild(customUI);
    }

    this.blockSettingsPanel.appendChild(wrapper);
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
/**
 * Xử lý binding metadata (theo declarative)
 */
class EditorMetaBinder {
  /**
   * @param {HTMLElement} container - Root element chứa các phần tử meta controls/previews
   * @param {EditorEventBus} bus - Event channel
   */
  constructor(container, bus) {
    this.container = container;
    this.bus = bus;
    this.controls = new Map();
    this.previews = new Map();
    this.#init();
  }

  #init() {
    this.#cacheElements();
    this.#bindEvents();

    this.bus.subscribe('meta:updated', ({ key, value, allMeta }) => {
      this.#syncControl(key, value);
      this.#syncPreview(key, value, allMeta);
    });

    this.bus.dispatch('meta:sync_request');
  }

  #cacheElements() {
    // Cache form controls
    this.container.querySelectorAll('[data-be-meta-key]').forEach(el => {
      const key = el.dataset.beMetaKey;
      if (!this.controls.has(key)) this.controls.set(key, []);
      this.controls.get(key).push(el);
    });

    // Cache preview elements
    this.container.querySelectorAll('[data-be-meta-preview]').forEach(el => {
      const key = el.dataset.beMetaPreview;
      if (!this.previews.has(key)) this.previews.set(key, []);
      this.previews.get(key).push(el);
    });
  }

  #bindEvents() {
    this.container.addEventListener('input', e => this.#handleInput(e));
    this.container.addEventListener('change', e => this.#handleChange(e));
    this.container.addEventListener('click', e => this.#handleSwitchClick(e));

    document.addEventListener('select:change', e => this.#handleSelectClick(e));
  }

  #handleInput(e) {
    const el = e.target.closest('[data-be-meta-key]:not(select, .select, .switch)');
    if (!el) return;

    let value;

    if (el.isContentEditable) {
      value = el.innerText;
    } else {
      value = el.type === 'number' ? parseInt(el.value) || 0 : el.value;
    }

    this.bus.dispatch('meta:update_request', { key: el.dataset.beMetaKey, value });
  }

  #handleChange(e) {
    const el = e.target.closest('select[data-be-meta-key]');
    if (!el) return;
    this.bus.dispatch('meta:update_request', { key: el.dataset.beMetaKey, value: el.value });
  }

  #handleSwitchClick(e) {
    const switchEl = e.target.closest('.switch[data-be-meta-key]');
    if (!switchEl) return;

    e.preventDefault();
    const key = switchEl.dataset.beMetaKey;
    const current = switchEl.dataset.switchState === 'checked';

    this.bus.dispatch('meta:update_request', { key, value: current });
  }

  #handleSelectClick(e) {
    const { id, label, isMultiple } = e.detail;
    const value = isMultiple ? e.detail.values.map(item => item.value) : e.detail.value;

    const selectEl = document.querySelector(`[data-select-id='${id}']`);
    if (!selectEl || !selectEl.dataset.beMetaKey) return;

    this.bus.dispatch('meta:update_request', { key: selectEl.dataset.beMetaKey, value: value });
  }

  #syncControl(key, value) {
    this.controls.get(key)?.forEach(el => {
      if (el.classList.contains('switch')) {
        const state = value ? 'checked' : 'unchecked';
        el.dataset.switchState = state;
        el.querySelector('.switch__thumb')?.setAttribute('data-switch-state', state);
      }
      else if (el.classList.contains('select')) {
        this.#updateSelectUI(el, value);
      }
      else if (el.tagName === 'SELECT') {
        el.value = value;
      }
      // Contenteditable element
      else if (el.isContentEditable) {
        el.innerText = value;
      }
      else {
        el.value = value ?? '';
      }
    });
  }

  #syncPreview(key, value, allMeta) {
    this.previews.get(key)?.forEach(el => {
      const action = el.dataset.bePreviewAction || 'text';
      switch (action) {
        case 'toggle':
          el.classList.toggle('hidden', !value);
          break;
        case 'text':
          el.textContent = value ?? el.dataset.previewDefault ?? '';
          break;
        case 'status-badge': {
          const labels = { draft: 'Nháp', published: 'Đã xuất bản', archived: 'Lưu trữ' };
          el.textContent = labels[value] || 'Nháp';
          el.dataset.variant = value === 'published' ? 'primary' : 'secondary';
          break;
        }
      }

      if (key === 'show_view_count') {
        document.getElementById('be-view-botting-wrapper')?.classList.toggle('hidden', !value);
      }
    });
  }

  #updateSelectUI(selectEl, value) {
    const items = selectEl.querySelectorAll('.select__item');
    let activeItem = null;

    items.forEach(item => {
      const isActive = item.dataset.selectValue == value;
      item.classList.toggle('active', isActive);
      item.classList.toggle('selected', isActive);
      if (isActive) activeItem = item;
    });

    const trigger = selectEl.querySelector('.select__label, .select__trigger, [data-select-label]');
    if (trigger && activeItem) {
      trigger.textContent = activeItem.textContent.trim();
    }

    selectEl.dispatchEvent(new CustomEvent('meta:select-changed', {
      detail: { key: selectEl.dataset.beMetaKey, value }
    }));
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