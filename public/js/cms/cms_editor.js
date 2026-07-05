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
    this.#bus.subscribe('preview:editable_selected', (payload) => this.#onPreviewEditableSelected(payload));
    this.#bus.subscribe('preview:image_selected', (payload) => this.#onPreviewImageSelected(payload));
    this.#bus.subscribe('preview:icon_selected', (payload) => this.#onPreviewIconSelected(payload));
    this.#bus.subscribe('preview:input', (payload) => this.#onPreviewInput(payload));
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
      this.#preview.render();
      this.#updatePreviewModeButtons();
    });

    document.querySelector('#be-toggle-left')?.addEventListener('click', () => this.#togglePanel('#be-left'));
    document.querySelector('#be-toggle-right')?.addEventListener('click', () => this.#togglePanel('#be-right'));
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

  #onFieldFocus({ path }) {
    this.#activePath = path;
    this.#fieldPanel.render(this.#activeSectionId, this.#activePath);
    this.#preview.markActiveEditable();
  }

  #onMediaSelectRequest({ path }) {
    this.#activePath = path;
    this.#fieldPanel.render(this.#activeSectionId, this.#activePath);
  }

  #onMediaSelected(event) {
    if (!this.#activeSectionId || !this.#activePath) return;

    const { media, close } = event.detail || {};
    if (!media?.file_path) return;

    const section = this.#cmsDocument.section(this.#activeSectionId);
    if (!section || !this.#cmsDocument.isImageEditable(this.#activeSectionId, this.#activePath)) return;

    setPath(section.data, this.#activePath, joinUrl(this.#cmsDocument.urls.media, media.file_path));
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

  #onPreviewInput({ sectionId, path, value }) {
    const section = this.#cmsDocument.section(sectionId);
    if (!section) return;
    this.#activeSectionId = sectionId;
    this.#activePath = path;
    setPath(section.data, path, value);
    this.#fieldPanel.syncValue(path, value);
  }

  #onStructureMutate({ action, path, index, blueprint }) {
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
      items.splice(index, 1);
    } else if (action === 'up' && index > 0) {
      [items[index - 1], items[index]] = [items[index], items[index - 1]];
    } else if (action === 'down' && index >= 0 && index < items.length - 1) {
      [items[index], items[index + 1]] = [items[index + 1], items[index]];
    }

    this.#activePath = null;
    this.#fieldPanel.render(this.#activeSectionId, null);
    this.#preview.render();
  }

  #serializeForm(event) {
    this.#clearError();
    if (!this.#editorData) return;

    try {
      this.#editorData.value = JSON.stringify({
        title: this.#cmsDocument.page.title || this.#cmsDocument.schema.title,
        content: this.#cmsDocument.document,
        settings: this.#cmsDocument.page.settings || {},
        action: event.submitter?.value || 'draft',
      });
    } catch {
      event.preventDefault();
      this.#showError('Could not serialize the CMS page payload.');
    }
  }

  #updatePreviewModeButtons() {
    document.querySelectorAll('[data-preview-width]').forEach((button) => {
      button.dataset.variant = button.dataset.previewWidth === this.#previewMode ? 'primary' : 'outline';
    });
  }

  #updateHighlightButton() {
    document.querySelectorAll('[data-preview-highlight]').forEach((button) => {
      button.dataset.variant = this.#highlightEditables ? 'primary' : 'outline';
      button.setAttribute('aria-pressed', this.#highlightEditables ? 'true' : 'false');
    });
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
}
