import { CMS_VIEWPORTS } from './cms_config.js';
import { cssEscape, escapeAttr, joinUrl, plainEditableText } from './cms_utils.js';
import { StaticLayoutRenderer } from './static_layout_renderer.js';

export class CmsPreview {
  constructor(bus, cmsDocument, root, getActiveState) {
    this.bus = bus;
    this.cmsDocument = cmsDocument;
    this.root = root;
    this.getActiveState = getActiveState;
    this.renderer = new StaticLayoutRenderer(cmsDocument, getActiveState);
    this.mode = 'desktop';
    this.scale = 1;
    this.frame = null;
    this.resizeObserver = null;
    this.pendingScrollSectionId = null;
    this.highlightEditables = false;
  }

  init() {
    if (this.root && window.ResizeObserver) {
      this.resizeObserver = new ResizeObserver(() => this.updateScale());
      this.resizeObserver.observe(this.root);
    }
  }

  setMode(mode) {
    this.mode = mode === 'mobile' ? 'mobile' : 'desktop';
  }

  render() {
    if (!this.root) return;
    const viewport = CMS_VIEWPORTS[this.mode];

    this.root.innerHTML = `
      <iframe class="cms-preview-page" title="CMS page preview" scrolling="no" style="width:${viewport.width}px;transform:scale(${this.scale})"></iframe>
    `;

    this.frame = this.root.querySelector('iframe');
    this.frame.addEventListener('load', () => {
      this.#bindFrame();
      this.#applyEditableHighlights();
      this.#flushPendingScroll();
      requestAnimationFrame(() => this.#syncFrameHeight());
    });
    this.frame.srcdoc = this.#renderDocument();
    this.updateScale();
  }

  updateScale() {
    if (!this.root || !this.frame) return;
    const viewport = CMS_VIEWPORTS[this.mode];
    const available = Math.max(320, this.root.clientWidth - 16);
    const fitScale = Math.min(1, available / viewport.width);
    const maxReadableScale = this.mode === 'desktop' ? 0.9 : 1;
    this.scale = this.mode === 'desktop'
      ? Math.min(maxReadableScale, fitScale)
      : fitScale;

    this.frame.style.width = `${viewport.width}px`;
    this.frame.style.transform = `scale(${this.scale})`;
    this.root.style.setProperty('--cms-preview-width', `${viewport.width * this.scale}px`);
    this.#syncFrameHeight();
  }

  scrollToSection(sectionId) {
    this.pendingScrollSectionId = sectionId;
    this.#flushPendingScroll();
  }

  setEditableHighlights(enabled) {
    this.highlightEditables = Boolean(enabled);
    this.#applyEditableHighlights();
  }

  #applyEditableHighlights() {
    const body = this.frame?.contentDocument?.body;
    if (!body) return;
    body.classList.toggle('cms-preview-body--highlight-editables', this.highlightEditables);
  }

  #flushPendingScroll() {
    const sectionId = this.pendingScrollSectionId;
    if (!sectionId) return;

    const node = this.frame?.contentDocument?.querySelector(`[data-section-id="${cssEscape(sectionId)}"]`);
    if (!node) return;

    node.scrollIntoView({ behavior: 'smooth', block: 'start' });
    this.pendingScrollSectionId = null;
  }

  markActiveEditable() {
    const doc = this.frame?.contentDocument;
    const active = this.getActiveState();
    if (!doc) return;
    doc.querySelectorAll('.cms-editable-text.is-active').forEach((node) => node.classList.remove('is-active'));
    doc.querySelectorAll('.cms-editable-image.is-active').forEach((node) => node.classList.remove('is-active'));
    doc.querySelectorAll('.cms-editable-icon.is-active').forEach((node) => node.classList.remove('is-active'));
    if (!active.sectionId || !active.path) return;
    doc.querySelector(`[data-section-id="${cssEscape(active.sectionId)}"] [data-cms-path="${cssEscape(active.path)}"]`)?.classList.add('is-active');
  }

  #renderLiveSection(section) {
    const schema = this.cmsDocument.sectionSchema(section.id) || {};
    const active = this.getActiveState();
    const activeClass = active.sectionId === section.id ? ' is-active' : '';
    const locked = schema.locked || !this.cmsDocument.textFieldInstances(section.id).length ? ' is-locked' : '';

    return `
      <div class="cms-live-section${activeClass}${locked}" data-section-id="${escapeAttr(section.id)}">
        ${this.renderer.renderSection(section)}
      </div>
    `;
  }

  #renderDocument() {
    return `<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  ${this.#stylesheetLinks()}
  <style>
    html,
    body {
      margin: 0;
      min-height: 0;
      overflow: visible;
      scrollbar-width: none;
    }

    body::-webkit-scrollbar {
      display: none;
    }
  </style>
</head>
<body class="cms-preview-body">
  <main class="cms-live-page">
    ${this.cmsDocument.sections.map((section) => this.#renderLiveSection(section)).join('')}
  </main>
</body>
</html>`;
  }

  #stylesheetLinks() {
    const files = [
      'css/fontawesome/fontawesome.min.css',
      'css/fontawesome/solid.min.css',
      'css/fontawesome/regular.min.css',
      'css/fonts.css',
      'css/base.css',
      'css/common.css',
      'css/main.css',
      'css/landing.css',
      'css/block_preview.css',
      'css/cms_page_editor.css',
    ];

    return files.map((file) => `<link rel="stylesheet" href="${escapeAttr(joinUrl(this.cmsDocument.urls.public, file))}">`).join('');
  }

  #bindFrame() {
    const doc = this.frame?.contentDocument;
    if (!doc) return;

    doc.addEventListener('click', (event) => {
      const editableNode = event.target.closest('[data-inline-edit="true"]');
      const imageNode = event.target.closest('[data-cms-image-edit="true"]');
      const iconNode = event.target.closest('[data-cms-icon-edit="true"]');
      const sectionNode = event.target.closest('[data-section-id]');

      if (editableNode) {
        this.bus.dispatch('preview:editable_selected', {
          sectionId: editableNode.dataset.sectionId,
          path: editableNode.dataset.cmsPath,
          value: plainEditableText(editableNode),
        });
        return;
      }

      if (imageNode) {
        this.bus.dispatch('preview:image_selected', {
          sectionId: imageNode.dataset.sectionId,
          path: imageNode.dataset.cmsPath,
        });
        return;
      }

      if (iconNode) {
        this.bus.dispatch('preview:icon_selected', {
          sectionId: iconNode.dataset.sectionId,
          path: iconNode.dataset.cmsPath,
        });
        return;
      }

      if (sectionNode) {
        this.bus.dispatch('section:select_request', { sectionId: sectionNode.dataset.sectionId, scroll: false, rerenderPreview: false });
      }
    });

    doc.addEventListener('input', (event) => {
      const editableNode = event.target.closest('[data-inline-edit="true"]');
      if (!editableNode) return;
      this.bus.dispatch('preview:input', {
        sectionId: editableNode.dataset.sectionId,
        path: editableNode.dataset.cmsPath,
        value: plainEditableText(editableNode),
      });
      requestAnimationFrame(() => this.#syncFrameHeight());
    });

    doc.addEventListener('keydown', (event) => {
      const editableNode = event.target.closest('[data-inline-edit="true"]');
      if (editableNode && editableNode.dataset.multiline !== 'true' && event.key === 'Enter') {
        event.preventDefault();
        editableNode.blur();
      }
    });

    this.scrollToSection(this.getActiveState().sectionId);
    this.markActiveEditable();
    requestAnimationFrame(() => this.#syncFrameHeight());
  }

  #syncFrameHeight() {
    if (!this.frame || !this.root) return;
    const doc = this.frame.contentDocument;
    if (!doc) return;

    const height = Math.max(
      CMS_VIEWPORTS[this.mode].height,
      doc.documentElement.scrollHeight,
      doc.body?.scrollHeight || 0,
    );

    this.frame.style.height = `${height}px`;
    this.root.style.setProperty('--cms-preview-height', `${height * this.scale}px`);
  }
}
