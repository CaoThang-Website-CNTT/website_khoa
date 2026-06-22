import { assetUrl, cssEscape, escapeAttr, escapeHtml, getPath } from './cms_utils.js';

export class CmsFieldPanel {
  constructor(bus, cmsDocument, root) {
    this.bus = bus;
    this.cmsDocument = cmsDocument;
    this.root = root;
  }

  init() {
    this.root?.addEventListener('input', (event) => {
      const backgroundField = event.target.closest('[data-cms-background-path]');
      if (backgroundField) {
        this.bus.dispatch('field:background_input', {
          path: backgroundField.dataset.cmsBackgroundPath,
          value: backgroundField.value,
        });
        return;
      }

      const field = event.target.closest('[data-cms-path]');
      if (!field) return;
      this.bus.dispatch('field:input', { path: field.dataset.cmsPath, value: field.value });
    });

    this.root?.addEventListener('focusin', (event) => {
      const field = event.target.closest('[data-cms-path]');
      if (!field) return;
      this.bus.dispatch('field:focus', { path: field.dataset.cmsPath });
    });

    this.root?.addEventListener('click', (event) => {
      const button = event.target.closest('[data-cms-media-path]');
      if (!button) return;
      this.bus.dispatch('field:media_select_request', { path: button.dataset.cmsMediaPath });
    });
  }

  render(sectionId, activePath) {
    if (!this.root) return;
    const section = this.cmsDocument.section(sectionId);
    const schema = this.cmsDocument.sectionSchema(sectionId);

    if (!section || !schema) {
      this.root.innerHTML = this.#emptyPanel('Chọn section', 'Chọn một section từ live preview.');
      return;
    }

    const fields = this.cmsDocument.textFieldInstances(sectionId);
    const imageFields = this.cmsDocument.imageFieldInstances(sectionId);
    const variantOptions = this.cmsDocument.variantOptions(sectionId);
    if (schema.locked || (!fields.length && !imageFields.length && variantOptions.length <= 1)) {
      this.root.innerHTML = this.#emptyPanel('Section bị khóa', 'Section này đã bị khóa trong CMS v1.');
      return;
    }

    if (!activePath) {
      this.root.innerHTML = variantOptions.length > 1
        ? `<div class="cms-field-grid">${this.#renderVariantField(section, variantOptions)}</div>`
        : this.#emptyPanel('Chọn một trường', 'Chọn văn bản có thể chỉnh sửa hoặc một hình ảnh từ bản xem trước.');
      return;
    }

    const activeImageField = imageFields.find((field) => field.path === activePath);
    if (activeImageField) {
      this.root.innerHTML = `<div class="cms-field-grid">${this.#renderVariantField(section, variantOptions)}${this.#renderImageField(activeImageField, activePath)}${this.#renderBentoBackgroundField(section, activeImageField)}</div>`;
      return;
    }

    const activeTextField = fields.find((field) => field.path === activePath);
    if (activeTextField) {
      this.root.innerHTML = `
        <div class="cms-field-grid">
          ${this.#renderVariantField(section, variantOptions)}
          ${this.#renderTextField(activeTextField, getPath(section.data || {}, activeTextField.path), activePath)}
        </div>
      `;
      return;
    }

    this.root.innerHTML = this.#emptyPanel('Trường không khả dụng', 'Trường này không chỉnh sửa ở CMS v1.');
  }

  syncValue(path, value) {
    const field = this.root?.querySelector(`[data-cms-path="${cssEscape(path)}"]`);
    if (field && field.value !== value) field.value = value;
  }

  #renderTextField(field, value, activePath) {
    const id = `cms-field-${field.sectionId}-${field.path}`.replace(/[^a-z0-9_-]+/gi, '-');
    const valueText = value == null ? '' : String(value);
    const active = activePath === field.path ? ' is-active' : '';
    const isIconField = this.#isIconPath(field.path);

    if (field.control === 'textarea') {
      return `
        <label class="field cms-text-field${active}" for="${escapeAttr(id)}">
          <span class="field__label">${escapeHtml(field.label)}</span>
          <textarea id="${escapeAttr(id)}" class="field__input" rows="4" data-cms-path="${escapeAttr(field.path)}">${escapeHtml(valueText)}</textarea>
        </label>
      `;
    }

    return `
      <label class="field cms-text-field${active}" for="${escapeAttr(id)}">
        <span class="field__label">${escapeHtml(field.label)}</span>
        <input id="${escapeAttr(id)}" class="field__input" type="text" value="${escapeAttr(valueText)}" data-cms-path="${escapeAttr(field.path)}">
        ${isIconField ? '<small class="field__description">Enter Font Awesome classes, for example fa-solid fa-award.</small>' : ''}
      </label>
    `;
  }

  #isIconPath(path) {
    return /(^|\.)icon$/.test(path);
  }

  #renderVariantField(section, options) {
    if (options.length <= 1) return '';

    const value = section.data?.variant || options[0]?.value || 'default';
    return `
      <label class="field cms-variant-field" for="${escapeAttr(`cms-field-${section.id}-variant`)}">
        <span class="field__label">Variant</span>
        <select id="${escapeAttr(`cms-field-${section.id}-variant`)}" class="field__input" data-cms-path="variant">
          ${options.map((option) => `<option value="${escapeAttr(option.value)}"${option.value === value ? ' selected' : ''}>${escapeHtml(option.label)}</option>`).join('')}
        </select>
      </label>
    `;
  }

  #renderImageField(field, activePath) {
    const imageUrl = assetUrl(this.cmsDocument.urls, field.value);
    const active = activePath === field.path ? ' is-active' : '';

    return `
      <div class="field cms-image-field${active}">
        <span class="field__label">${escapeHtml(field.label)}</span>
        <div class="cms-image-field__preview">
          ${imageUrl
        ? `<img src="${escapeAttr(imageUrl)}" alt="${escapeAttr(field.label)}">`
        : `<div class="cms-image-field__empty"><i class="fa-regular fa-image"></i></div>`
      }
        </div>
        <button type="button" class="btn" data-size="sm" data-variant="outline" data-cms-media-path="${escapeAttr(field.path)}" data-modal-trigger="#media-selector-modal">
          <i class="fa-solid fa-images"></i> Đổi Hình Ảnh
        </button>
      </div>
    `;
  }

  #renderBentoBackgroundField(section, imageField) {
    const match = imageField.sectionId === 'bento_grid'
      ? imageField.path.match(/^items\.(\d+)\.image\.src$/)
      : null;
    if (!match) return '';

    const backgroundPath = `items.${match[1]}.background`;
    const value = this.#normalizeColor(getPath(section.data || {}, backgroundPath));

    return `
      <label class="field cms-color-field" for="${escapeAttr(`cms-field-${imageField.sectionId}-${backgroundPath}`.replace(/[^a-z0-9_-]+/gi, '-'))}">
        <span class="field__label">Background color ${Number(match[1]) + 1}</span>
        <input
          id="${escapeAttr(`cms-field-${imageField.sectionId}-${backgroundPath}`.replace(/[^a-z0-9_-]+/gi, '-'))}"
          class="field__input"
          type="color"
          value="${escapeAttr(value)}"
          data-cms-background-path="${escapeAttr(backgroundPath)}"
        >
      </label>
    `;
  }

  #normalizeColor(value) {
    const color = String(value || '').trim();
    return /^#[0-9a-f]{6}$/i.test(color) ? color : '#ffffff';
  }

  #emptyPanel(title, description) {
    return `
      <div class="empty">
        <div class="empty__header">
          <div class="empty__media"><i class="fa-regular fa-square"></i></div>
          <div class="empty__title">${escapeHtml(title)}</div>
          <div class="empty__description">${escapeHtml(description)}</div>
        </div>
      </div>
    `;
  }
}
