import { CMS_VIEWPORTS } from './cms_config.js';
import { cssEscape, plainEditableText } from './cms_utils.js';

export class CmsPreview {
  constructor(bus, cmsDocument, root, getActiveState) {
    this.bus = bus; this.cmsDocument = cmsDocument; this.root = root; this.getActiveState = getActiveState;
    this.mode = 'desktop'; this.scale = 1; this.frame = null; this.resizeObserver = null;
    this.pendingScrollSectionId = null; this.highlightEditables = false;
    this.revision = 0; this.appliedRevision = 0; this.timer = null; this.controller = null; this.lastHtml = '';
    this.frameResizeObserver = null; this.frameMutationObserver = null; this.heightSyncFrame = null; this.heightSyncTimer = null;
  }

  init() {
    if (this.root && window.ResizeObserver) {
      this.resizeObserver = new ResizeObserver(() => this.updateScale());
      this.resizeObserver.observe(this.root);
    }
  }

  setMode(mode) { this.mode = mode === 'mobile' ? 'mobile' : 'desktop'; }

  render({ immediate = false } = {}) {
    clearTimeout(this.timer);
    if (immediate || !this.lastHtml) return this.#requestRender();
    this.timer = setTimeout(() => this.#requestRender(), 250);
  }

  async #requestRender() {
    if (!this.root || !this.cmsDocument.urls.preview) return;
    const revision = ++this.revision;
    this.controller?.abort(); this.controller = new AbortController();
    const controller = this.controller;
    this.root.dataset.state = 'loading';
    try {
      const csrf = document.querySelector('#cms-page-form input[name="_token"]')?.value || '';
      const response = await fetch(this.cmsDocument.urls.preview, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ content: this.cmsDocument.document, revision }),
        signal: controller.signal,
      });
      const payload = await response.json();
      if (!response.ok || !payload.success) throw new Error(payload.message || 'Preview render failed.');
      if (revision !== this.revision || revision < this.appliedRevision) return;
      this.appliedRevision = revision; this.lastHtml = payload.data.html;
      this.#mount(payload.data.html);
      this.bus.dispatch('preview:rendered', { revision });
    } catch (error) {
      if (error.name !== 'AbortError') this.bus.dispatch('preview:error', { message: error.message });
    } finally {
      if (revision === this.revision) delete this.root.dataset.state;
      if (this.controller === controller) this.controller = null;
    }
  }

  #mount(html) {
    const viewport = CMS_VIEWPORTS[this.mode];
    if (!this.frame) {
      this.root.innerHTML = '<iframe class="cms-preview-page" title="CMS page preview" scrolling="no"></iframe>';
      this.frame = this.root.querySelector('iframe');
    }
    this.frame.style.width = `${viewport.width}px`;
    this.frame.onload = () => { this.#bindFrame(); this.#observeFrameContent(); this.#applyEditableHighlights(); this.#flushPendingScroll(); this.#scheduleFrameHeightSync(); };
    this.frame.srcdoc = html; this.updateScale();
  }

  updateScale() {
    if (!this.root || !this.frame) return;
    const viewport = CMS_VIEWPORTS[this.mode];
    const fitScale = Math.min(1, Math.max(320, this.root.clientWidth - 16) / viewport.width);
    this.scale = this.mode === 'desktop' ? Math.min(0.9, fitScale) : fitScale;
    this.frame.style.width = `${viewport.width}px`; this.frame.style.transform = `scale(${this.scale})`;
    this.root.style.setProperty('--cms-preview-width', `${viewport.width * this.scale}px`); this.#syncFrameHeight();
  }

  scrollToSection(sectionId) { this.pendingScrollSectionId = sectionId; this.#flushPendingScroll(); }
  setEditableHighlights(enabled) { this.highlightEditables = Boolean(enabled); this.#applyEditableHighlights(); }
  #applyEditableHighlights() { this.frame?.contentDocument?.body?.classList.toggle('cms-preview-body--highlight-editables', this.highlightEditables); }

  #flushPendingScroll() {
    if (!this.pendingScrollSectionId) return;
    const node = this.frame?.contentDocument?.querySelector(`[data-section-id="${cssEscape(this.pendingScrollSectionId)}"]`);
    if (!node) return;
    node.scrollIntoView({ behavior: 'smooth', block: 'start' }); this.pendingScrollSectionId = null;
  }

  markActiveEditable() {
    const doc = this.frame?.contentDocument; const active = this.getActiveState(); if (!doc) return;
    doc.querySelectorAll('.is-active').forEach((node) => node.classList.remove('is-active'));
    if (active.sectionId && active.path) {
      doc.querySelector(`[data-section-id="${cssEscape(active.sectionId)}"][data-cms-path="${cssEscape(active.path)}"]`)?.classList.add('is-active');
    } else if (active.sectionId) {
      doc.querySelector(`[data-section-id="${cssEscape(active.sectionId)}"]`)?.classList.add('is-active');
    }
  }

  #bindFrame() {
    const doc = this.frame?.contentDocument; if (!doc) return;
    doc.querySelectorAll('[data-cms-locked="true"]').forEach((section) => {
      if (section.querySelector(':scope > .cms-locked-state')) return;
      const lockedState = doc.createElement('div');
      lockedState.className = 'cms-locked-state';
      lockedState.setAttribute('role', 'status');
      lockedState.innerHTML = '<i class="fa-solid fa-lock" aria-hidden="true"></i><span></span>';
      lockedState.querySelector('span').textContent = section.dataset.cmsLockLabel || '';
      section.prepend(lockedState);
    });
    doc.addEventListener('click', (event) => {
      this.#stabilizeInteraction();
      if (event.target.closest('a')) event.preventDefault();
      const editable = event.target.closest('[data-inline-edit="true"]'); const image = event.target.closest('[data-cms-image-edit="true"]');
      const link = event.target.closest('[data-cms-link-edit="true"]'); const icon = event.target.closest('[data-cms-icon-edit="true"]'); const repeater = event.target.closest('[data-cms-repeater-path]'); const section = event.target.closest('[data-section-id]');
      const overlayImage = event.target.closest('.image-wrapper')?.querySelector('[data-cms-image-edit="true"]');
      if (image) this.bus.dispatch('preview:image_selected', { sectionId: image.dataset.sectionId, path: image.dataset.cmsPath });
      else if (editable) this.bus.dispatch('preview:editable_selected', { sectionId: editable.dataset.sectionId, path: editable.dataset.cmsPath, value: plainEditableText(editable) });
      else if (link) this.bus.dispatch('preview:link_selected', { sectionId: link.dataset.sectionId, path: link.dataset.cmsPath });
      else if (icon) this.bus.dispatch('preview:icon_selected', { sectionId: icon.dataset.sectionId, path: icon.dataset.cmsPath });
      else if (overlayImage) this.bus.dispatch('preview:image_selected', { sectionId: overlayImage.dataset.sectionId, path: overlayImage.dataset.cmsPath });
      else if (repeater) this.bus.dispatch('preview:repeater_selected', { sectionId: repeater.dataset.sectionId, path: repeater.dataset.cmsRepeaterPath, index: Number(repeater.dataset.cmsRepeaterIndex) });
      else if (section) this.bus.dispatch('section:select_request', { sectionId: section.dataset.sectionId, scroll: false, rerenderPreview: false });
    });
    doc.addEventListener('input', (event) => {
      const editable = event.target.closest('[data-inline-edit="true"]'); if (!editable) return;
      this.bus.dispatch('preview:input', { sectionId: editable.dataset.sectionId, path: editable.dataset.cmsPath, value: plainEditableText(editable) });
    });
    doc.addEventListener('keydown', (event) => {
      const editable = event.target.closest('[data-inline-edit="true"]');
      if (!editable || editable.dataset.multiline === 'true' || event.key !== 'Enter') return;
      event.preventDefault(); editable.blur();
    });
    doc.addEventListener('paste', (event) => {
      const editable = event.target.closest('[data-inline-edit="true"]'); if (!editable) return;
      event.preventDefault();
      const text = event.clipboardData?.getData('text/plain') || '';
      editable.ownerDocument.execCommand('insertText', false, text);
    });
    doc.addEventListener('focusout', (event) => {
      const editable = event.target.closest('[data-inline-edit="true"]'); if (!editable) return;
      this.bus.dispatch('preview:text_commit', { sectionId: editable.dataset.sectionId, path: editable.dataset.cmsPath });
    });
    this.markActiveEditable();
  }

  #observeFrameContent() {
    this.#disconnectFrameObservers();
    const doc = this.frame?.contentDocument; if (!doc) return;
    const observedNodes = [doc.documentElement, doc.body, doc.querySelector('.cms-live-page')].filter(Boolean);

    if (this.frame?.contentWindow?.ResizeObserver) {
      this.frameResizeObserver = new this.frame.contentWindow.ResizeObserver(() => this.#scheduleFrameHeightSync());
      observedNodes.forEach((node) => this.frameResizeObserver.observe(node));
    }

    if (this.frame?.contentWindow?.MutationObserver && doc.body) {
      this.frameMutationObserver = new this.frame.contentWindow.MutationObserver(() => this.#scheduleFrameHeightSync(240));
      this.frameMutationObserver.observe(doc.body, {
        attributes: true,
        attributeFilter: ['class', 'data-state', 'hidden', 'style', 'aria-expanded'],
        childList: true,
        subtree: true,
      });
    }
  }

  #disconnectFrameObservers() {
    this.frameResizeObserver?.disconnect();
    this.frameMutationObserver?.disconnect();
    this.frameResizeObserver = null;
    this.frameMutationObserver = null;
    if (this.heightSyncFrame) {
      cancelAnimationFrame(this.heightSyncFrame);
      this.heightSyncFrame = null;
    }
    clearTimeout(this.heightSyncTimer);
    this.heightSyncTimer = null;
  }

  #scheduleFrameHeightSync(delay = 0) {
    if (this.heightSyncFrame) cancelAnimationFrame(this.heightSyncFrame);
    this.heightSyncFrame = requestAnimationFrame(() => {
      this.heightSyncFrame = null;
      this.#syncFrameHeight();
    });

    clearTimeout(this.heightSyncTimer);
    if (delay > 0) this.heightSyncTimer = window.setTimeout(() => this.#syncFrameHeight(), delay);
  }

  #stabilizeInteraction() {
    clearTimeout(this.timer);
    this.timer = null;
    if (!this.controller) return;
    this.revision += 1;
    this.controller.abort();
    this.controller = null;
    if (this.root) delete this.root.dataset.state;
  }

  #syncFrameHeight() {
    if (!this.frame || !this.root) return; const doc = this.frame.contentDocument; if (!doc) return;
    const content = doc.querySelector('.cms-live-page');
    const contentRect = content?.getBoundingClientRect();
    const contentHeight = content
      ? Math.max(content.scrollHeight, content.offsetHeight, contentRect?.height || 0, contentRect?.bottom || 0)
      : 0;
    const height = Math.max(1, Math.ceil(contentHeight || doc.body?.scrollHeight || doc.documentElement.scrollHeight || 0));
    this.frame.style.height = `${height}px`; this.root.style.setProperty('--cms-preview-height', `${height * this.scale}px`);
  }
}
