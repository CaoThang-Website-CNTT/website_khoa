import { escapeAttr, escapeHtml } from './cms_utils.js';

export class CmsSectionNav {
  constructor(bus, cmsDocument, root) {
    this.bus = bus;
    this.cmsDocument = cmsDocument;
    this.root = root;
  }

  init() {
    this.root?.addEventListener('click', (event) => {
      const button = event.target.closest('[data-section-id]');
      if (!button) return;
      this.bus.dispatch('section:select_request', { sectionId: button.dataset.sectionId, scroll: true });
    });
  }

  render(activeSectionId) {
    if (!this.root) return;
    this.root.innerHTML = (this.cmsDocument.schema.sections || []).map((section) => `
      <button type="button" class="cms-section-item ${activeSectionId === section.id ? 'is-active' : ''}" data-section-id="${escapeAttr(section.id)}">
        <span class="cms-section-item__text">
          <span class="cms-section-item__label">${escapeHtml(section.label || section.id)}</span>
          <span class="cms-section-item__type">${escapeHtml(section.type || '')}</span>
        </span>
        ${section.locked ? '<span class="badge" data-variant="secondary"><i class="fa-solid fa-lock"></i></span>' : ''}
      </button>
    `).join('');
  }
}
