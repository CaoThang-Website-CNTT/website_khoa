import { CmsDocument } from './cms_document.js';
import { CmsFieldPanel } from './cms_field_panel.js';
import { CmsPreview } from './cms_preview.js';
import { CmsSectionNav } from './cms_section_nav.js';
import { getPath, joinUrl, setPath } from './cms_utils.js';

class CmsEditorEventBus {
  #listeners = new Map();

  // ===========================
  // Custom Event Listener
  // ===========================
  /**
   *
   * @param {string} event
   * @param {Function} fn
   */
  subscribe(event, fn) {
    if (!this.#listeners.has(event)) this.#listeners.set(event, new Set());
    this.#listeners.get(event).add(fn);
    return () => this.unsubscribe(event, fn);
  }

  /**
   *
   * @param {string} event
   * @param {Function} fn
   */
  unsubscribe(event, fn) {
    this.#listeners.get(event)?.delete(fn);
  }

  /**
   *
   * @param {string} event
   * @param {object} payload
   */
  dispatch(event, payload = {}) {
    for (const fn of this.#listeners.get(event) ?? []) {
      try {
        fn(payload);
      } catch (error) {
        console.error(`[CmsEditorCanvas] Lỗi ${event} handler:`, error);
      }
    }
  }
}

/** Quản lý cấp điều phối cho CMS editor */
export class CmsEditorManager {
  /** @type {CmsEditorEventBus} */
  #bus;
  /** @type {CmsDocument} */
  #cmsDocument;
  /** @type {CmsSectionNav} */
  #sectionNav;
  /** @type {CmsFieldPanel} */
  #fieldPanel;
  /** @type {CmsPreview} */
  #preview;

  #form;
  #editorData;
  #errorBox;
  #activeSectionId;
  #activePath = null;
  #previewMode = 'desktop';
  #highlightEditables = false;
  #pageStatus = 'draft';

  constructor(payload = {}) {
    this.#bus = new CmsEditorEventBus();

    // Khởi tạo data và các module UI
    this.#cmsDocument = new CmsDocument(payload);
    this.#activeSectionId = this.#cmsDocument.sections?.[0]?.id || this.#cmsDocument.schema.sections?.[0]?.id || null;

    this.#sectionNav = new CmsSectionNav(this.#bus, this.#cmsDocument, document.querySelector('#cms-section-list'));
    this.#fieldPanel = new CmsFieldPanel(this.#bus, this.#cmsDocument, document.querySelector('#cms-section-inspector'));
    this.#preview = new CmsPreview(
      this.#bus,
      this.#cmsDocument,
      document.querySelector('#cms-preview'),
      () => ({ sectionId: this.#activeSectionId, path: this.#activePath }),
    );

    // Tham chiếu các phần tử DOM
    this.#form = document.querySelector('#cms-page-form');
    this.#editorData = document.querySelector('#cms-editor-data');
    this.#errorBox = document.querySelector('#cms-editor-error');
    this.#pageStatus = this.#cmsDocument.page.status === 'published' ? 'published' : 'draft';
  }

  init() {
    // Subscribe manager events
    this.#bindEvents();

    // Khởi tạo UI
    this.#sectionNav.init();
    this.#fieldPanel.init();
    this.#preview.init();

    this.#initialRender();

    console.log('[CmsEditorManager] Khởi tạo thành công.');
  }

  #bindEvents() {
    this.#bus.subscribe('section:select_request', (payload) => this.#selectSection(payload));
    this.#bus.subscribe('field:input', (payload) => this.#onFieldInput(payload));
    this.#bus.subscribe('field:background_input', (payload) => this.#onBackgroundInput(payload));
    this.#bus.subscribe('field:focus', (payload) => this.#onFieldFocus(payload));
    this.#bus.subscribe('field:media_select_request', (payload) => this.#onMediaSelectRequest(payload));
    this.#bus.subscribe('field:media_clear_request', (payload) => this.#onMediaClearRequest(payload));
    this.#bus.subscribe('preview:editable_selected', (payload) => this.#onPreviewEditableSelected(payload));
    this.#bus.subscribe('preview:image_selected', (payload) => this.#onPreviewImageSelected(payload));
    this.#bus.subscribe('preview:link_selected', (payload) => this.#onPreviewImageSelected(payload));
    this.#bus.subscribe('preview:icon_selected', (payload) => this.#onPreviewIconSelected(payload));
    this.#bus.subscribe('preview:repeater_selected', (payload) => this.#onPreviewRepeaterSelected(payload));
    this.#bus.subscribe('preview:input', (payload) => this.#onPreviewInput(payload));
    this.#bus.subscribe('preview:text_commit', () => this.#preview.render({ immediate: true }));
    this.#bus.subscribe('preview:error', ({ message }) => this.#showError(message));
    this.#bus.subscribe('preview:rendered', () => this.#clearError());
    this.#bus.subscribe('structure:mutate', (payload) => this.#onStructureMutate(payload));

    document.addEventListener('click', (event) => {
      const widthTrigger = event.target.closest('[data-preview-width]');
      const highlightTrigger = event.target.closest('[data-preview-highlight]');
      if (!widthTrigger && !highlightTrigger) return;

      if (highlightTrigger) {
        this.#highlightEditables = !this.#highlightEditables;
        this.#preview.setEditableHighlights(this.#highlightEditables);
        this.#updateHighlightButton();
        return;
      }

      this.#previewMode = widthTrigger.dataset.previewWidth === 'mobile' ? 'mobile' : 'desktop';
      this.#preview.setMode(this.#previewMode);
      this.#preview.render({ immediate: true });
      this.#updatePreviewModeButtons();
    });

    document.addEventListener('dropdown:select', (event) => {
      const widthTrigger = event.detail?.item?.closest?.('[data-preview-width]');
      if (!widthTrigger) return;

      this.#previewMode = widthTrigger.dataset.previewWidth === 'mobile' ? 'mobile' : 'desktop';
      this.#preview.setMode(this.#previewMode);
      this.#preview.render({ immediate: true });
      this.#updatePreviewModeButtons();
    });

    document.querySelector('#be-toggle-left')?.addEventListener('click', () => this.#togglePanel('#be-left'));
    document.querySelector('#be-toggle-right')?.addEventListener('click', () => this.#togglePanel('#be-right'));
    document.querySelector('[data-cms-page-status]')?.addEventListener('select:change', (event) => {
      const status = event.detail?.value;
      if (!['draft', 'published'].includes(status)) return;
      this.#pageStatus = status;
      this.#updatePageStatusBadge();
    });
    this.#form?.addEventListener('submit', (event) => this.#serializeForm(event));
    document.querySelector('#media-selector-modal')?.addEventListener('msm:submit', (event) => this.#onMediaSelected(event));
  }

  /**
   * Render khởi tạo
   */
  #initialRender() {
    this.#sectionNav.render(this.#activeSectionId);
    this.#fieldPanel.render(this.#activeSectionId, this.#activePath);
    this.#preview.setMode(this.#previewMode);
    this.#preview.render();
    this.#updatePreviewModeButtons();
    this.#updateHighlightButton();
    this.#updatePageStatusBadge();
  }

  #selectSection({ sectionId, scroll = false, rerenderPreview = true }) {
    this.#activeSectionId = sectionId;
    this.#activePath = null;
    this.#sectionNav.render(this.#activeSectionId);
    this.#fieldPanel.render(this.#activeSectionId, this.#activePath);

    if (rerenderPreview) this.#preview.render();
    if (scroll) this.#preview.scrollToSection(sectionId);
  }

  #onFieldInput({ path, value }) {
    const section = this.#cmsDocument.section(this.#activeSectionId);
    if (!section) return;
    setPath(section.data, path, value);
    this.#activePath = path;
    this.#preview.render();
  }

  #onBackgroundInput({ path, value }) {
    const section = this.#cmsDocument.section(this.#activeSectionId);
    if (!section) return;
    setPath(section.data, path, value);
    this.#preview.render();
  }

  #onFieldFocus({ path, embedded = false }) {
    this.#activePath = path;
    if (!embedded) this.#fieldPanel.render(this.#activeSectionId, this.#activePath);
    this.#preview.markActiveEditable();
  }

  #onMediaSelectRequest({ path }) {
    this.#activePath = path;
    this.#fieldPanel.render(this.#activeSectionId, this.#activePath);
  }

  #onMediaClearRequest({ path }) {
    const section = this.#cmsDocument.section(this.#activeSectionId);
    if (!section || !this.#cmsDocument.isImageEditable(this.#activeSectionId, path)) return;

    setPath(section.data, path, '');
    this.#activePath = path;
    this.#fieldPanel.render(this.#activeSectionId, this.#activePath);
    this.#preview.render();
  }

  #onMediaSelected(event) {
    if (!this.#activeSectionId || !this.#activePath) return;

    const { media, close } = event.detail || {};
    if (!media?.file_path) return;

    const section = this.#cmsDocument.section(this.#activeSectionId);
    if (!section || !this.#cmsDocument.isImageEditable(this.#activeSectionId, this.#activePath)) return;

    let mediaPath = String(media.file_path || '').replace(/\\/g, '/').replace(/^\/+/, '');
    if (mediaPath.startsWith('public/media/')) mediaPath = mediaPath.slice('public/media/'.length);
    if (mediaPath.startsWith('media/')) mediaPath = mediaPath.slice('media/'.length);

    setPath(section.data, this.#activePath, joinUrl(this.#cmsDocument.urls.media, mediaPath));
    this.#fieldPanel.render(this.#activeSectionId, this.#activePath);
    this.#preview.render();
    close?.();
  }

  #onPreviewEditableSelected({ sectionId, path, value }) {
    this.#activeSectionId = sectionId;
    this.#activePath = path;
    this.#sectionNav.render(this.#activeSectionId);
    this.#fieldPanel.render(this.#activeSectionId, this.#activePath);
    this.#fieldPanel.syncValue(path, value);
    this.#preview.markActiveEditable();
  }

  #onPreviewImageSelected({ sectionId, path }) {
    this.#activeSectionId = sectionId;
    this.#activePath = path;
    this.#sectionNav.render(this.#activeSectionId);
    this.#fieldPanel.render(this.#activeSectionId, this.#activePath);
    this.#preview.markActiveEditable();
  }

  #onPreviewIconSelected({ sectionId, path }) {
    this.#activeSectionId = sectionId;
    this.#activePath = path;
    this.#sectionNav.render(this.#activeSectionId);
    this.#fieldPanel.render(this.#activeSectionId, this.#activePath);
    this.#preview.markActiveEditable();
  }

  #onPreviewRepeaterSelected({ sectionId, path, index }) {
    this.#activeSectionId = sectionId;
    this.#activePath = null;
    this.#sectionNav.render(this.#activeSectionId);
    this.#fieldPanel.render(this.#activeSectionId, null);
    this.#fieldPanel.openRepeaterItem(path, index);
    this.#preview.markActiveEditable();
  }

  #onPreviewInput({ sectionId, path, value }) {
    const section = this.#cmsDocument.section(sectionId);
    if (!section) return;
    this.#activeSectionId = sectionId;
    this.#activePath = path;
    setPath(section.data, path, value);
    this.#fieldPanel.syncValue(path, value);
  }

  #onStructureMutate({ action, path, index, newIndex, blueprint }) {
    const section = this.#cmsDocument.section(this.#activeSectionId);
    const schema = this.#cmsDocument.sectionSchema(this.#activeSectionId);
    const items = getPath(section?.data || {}, path);
    if (!section || !schema || !Array.isArray(items)) return;

    if (action === 'add') {
      const template = schema.repeaters?.[blueprint]?.item ?? '';
      items.push(structuredClone(template));
    } else if (action === 'duplicate' && items[index] !== undefined) {
      items.splice(index + 1, 0, structuredClone(items[index]));
    } else if (action === 'remove' && items[index] !== undefined) {
      const minimum = Number(schema.repeaters?.[blueprint || path]?.min ?? 0);
      if (items.length <= minimum) return;
      items.splice(index, 1);
    } else if (action === 'move' && index >= 0 && newIndex >= 0 && index !== newIndex) {
      const [item] = items.splice(index, 1);
      items.splice(newIndex, 0, item);
    }

    this.#activePath = null;
    this.#fieldPanel.render(this.#activeSectionId, null);
    this.#preview.render({ immediate: true });
  }

  #serializeForm(event) {
    this.#clearError();
    if (!this.#editorData) return;

    try {
      this.#editorData.value = JSON.stringify({
        title: this.#cmsDocument.page.title || this.#cmsDocument.schema.title,
        content: this.#cmsDocument.document,
        settings: this.#cmsDocument.page.settings || {},
        action: this.#pageStatus,
      });
    } catch {
      event.preventDefault();
      this.#showError('Could not serialize the CMS page payload.');
    }
  }

  #updatePreviewModeButtons() {
    document.querySelectorAll('[data-preview-width]').forEach((button) => {
      if (button.classList.contains('btn')) {
        button.dataset.variant = button.dataset.previewWidth === this.#previewMode ? 'primary' : 'outline';
      }
      button.toggleAttribute('data-highlighted', button.dataset.previewWidth === this.#previewMode);
    });

    document.querySelectorAll('[data-preview-viewport-trigger]').forEach((button) => {
      button.dataset.variant = 'primary';
      button.setAttribute('aria-label', this.#previewMode === 'mobile' ? 'Chế độ xem: Mobile' : 'Chế độ xem: Desktop');
      const icon = button.querySelector('[data-preview-viewport-icon]');
      if (icon) {
        icon.className = this.#previewMode === 'mobile' ? 'fa-solid fa-mobile-screen' : 'fa-solid fa-desktop';
      }
    });
  }

  #updateHighlightButton() {
    document.querySelectorAll('[data-preview-highlight]').forEach((button) => {
      button.dataset.variant = this.#highlightEditables ? 'primary' : 'outline';
      button.setAttribute('aria-pressed', this.#highlightEditables ? 'true' : 'false');
    });
  }

  #updatePageStatusBadge() {
    const badge = document.querySelector('[data-cms-page-status-badge]');
    if (!badge) return;
    badge.textContent = this.#pageStatus;
    badge.dataset.variant = this.#pageStatus === 'published' ? 'primary' : 'secondary';
  }

  #togglePanel(selector) {
    const panel = document.querySelector(selector);
    if (!panel) return;
    panel.dataset.bePanelState = panel.dataset.bePanelState === 'collapsed' ? 'expanded' : 'collapsed';
  }

  #showError(message) {
    if (!this.#errorBox) return;
    this.#errorBox.textContent = message;
    this.#errorBox.classList.add('is-visible');
  }

  #clearError() {
    if (!this.#errorBox) return;
    this.#errorBox.textContent = '';
    this.#errorBox.classList.remove('is-visible');
  }

  getAiContext() {
    return {
      page: this.#cmsDocument.page,
      schema: this.#cmsDocument.schema,
      document: this.#cmsDocument.document,
      activeSectionId: this.#activeSectionId,
      activePath: this.#activePath,
    };
  }

  getAiFieldContext(path) {
    const section = this.#cmsDocument.section(this.#activeSectionId);
    const field = this.#cmsDocument.textFieldInstances(this.#activeSectionId)
      .find((item) => item.path === path);
    return {
      surface: 'cms', section_id: this.#activeSectionId, path,
      field_label: field?.label || path,
      current_value: getPath(section?.data || {}, path) ?? '',
      context: this.getAiContext(),
    };
  }

  applyAiSuggestion(path, value) {
    const section = this.#cmsDocument.section(this.#activeSectionId);
    if (!section || !this.#cmsDocument.isTextEditable(this.#activeSectionId, path)) return false;
    setPath(section.data, path, String(value ?? ''));
    this.#activePath = path;
    this.#fieldPanel.render(this.#activeSectionId, path);
    this.#fieldPanel.syncValue(path, String(value ?? ''));
    this.#preview.render();
    return true;
  }

}
