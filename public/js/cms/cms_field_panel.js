import { asArray, assetUrl, cssEscape, escapeAttr, escapeHtml, getPath } from './cms_utils.js';

export class CmsFieldPanel {
  constructor(bus, cmsDocument, root) {
    this.bus = bus;
    this.cmsDocument = cmsDocument;
    this.root = root;
    this.dndInstances = [];
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
      this.bus.dispatch('field:focus', {
        path: field.dataset.cmsPath,
        embedded: Boolean(field.closest('.cms-structure-manager')),
      });
    });

    this.root?.addEventListener('click', (event) => {
      const button = event.target.closest('[data-cms-media-path]');
      if (button) {
        this.bus.dispatch('field:media_select_request', { path: button.dataset.cmsMediaPath });
        return;
      }
      const structureButton = event.target.closest('[data-cms-array-action]');
      if (!structureButton) return;
      this.bus.dispatch('structure:mutate', {
        action: structureButton.dataset.cmsArrayAction,
        path: structureButton.dataset.cmsArrayPath,
        index: Number(structureButton.dataset.cmsArrayIndex),
        blueprint: structureButton.dataset.cmsArrayBlueprint,
      });
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

    if (section.type === 'sections/about_hero') {
      this.root.innerHTML = `
        <div class="cms-field-grid">
          ${this.#renderVariantField(section, variantOptions)}
          ${imageFields.map((field) => this.#renderImageField(field, activePath)).join('')}
          ${fields.map((field) => this.#renderTextField(field, getPath(section.data || {}, field.path), activePath)).join('')}
        </div>
      `;
      return;
    }

    if (!activePath) {
      const structure = this.#renderStructureManager(section, schema);
      if (structure) {
        this.root.innerHTML = structure;
        this.#initializeStructureWidgets();
        return;
      }
      this.root.innerHTML = variantOptions.length > 1
        ? `<div class="cms-field-grid">${this.#renderVariantField(section, variantOptions)}</div>`
        : this.#emptyPanel('Chọn một trường', 'Chọn văn bản có thể chỉnh sửa hoặc một hình ảnh từ bản xem trước.');
      return;
    }

    const activeImageField = imageFields.find((field) => field.path === activePath);
    if (activeImageField) {
      const relatedUrlField = this.#relatedImageUrlField(activeImageField, fields);
      this.root.innerHTML = `<div class="cms-field-grid">${this.#renderVariantField(section, variantOptions)}${relatedUrlField ? this.#renderTextField(relatedUrlField, getPath(section.data || {}, relatedUrlField.path), relatedUrlField.path) : ''}${this.#renderImageField(activeImageField, activePath)}${this.#renderBentoBackgroundField(section, activeImageField)}</div>`;
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

    return `
      <div class="field cms-text-field${active}">
        <label class="field__label" for="${escapeAttr(id)}">${escapeHtml(field.label)}</label>
        <textarea id="${escapeAttr(id)}" class="field__input" rows="4" data-cms-path="${escapeAttr(field.path)}">${escapeHtml(valueText)}</textarea>
        ${isIconField ? '<p class="field__description">Enter Font Awesome classes, for example fa-solid fa-award.</p>' : ''}
      </div>
    `;
  }

  #isIconPath(path) {
    return /(^|\.)icon$/.test(path);
  }

  #relatedImageUrlField(imageField, fields) {
    const match = imageField.path.match(/^(partners\.\d+)\.image\.src$/);
    if (!match) return null;
    return fields.find((field) => field.path === `${match[1]}.url`) || null;
  }

  #renderVariantField(section, options) {
    if (options.length <= 1) return '';

    const value = section.data?.variant || options[0]?.value || 'default';
    return `
      <div class="field cms-variant-field">
        <label class="field__label" for="${escapeAttr(`cms-field-${section.id}-variant`)}">Variant</label>
        <select id="${escapeAttr(`cms-field-${section.id}-variant`)}" class="field__input" data-cms-path="variant">
          ${options.map((option) => `<option value="${escapeAttr(option.value)}"${option.value === value ? ' selected' : ''}>${escapeHtml(option.label)}</option>`).join('')}
        </select>
      </div>
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
      <div class="field cms-color-field">
        <label class="field__label" for="${escapeAttr(`cms-field-${imageField.sectionId}-${backgroundPath}`.replace(/[^a-z0-9_-]+/gi, '-'))}">Background color ${Number(match[1]) + 1}</label>
        <input
          id="${escapeAttr(`cms-field-${imageField.sectionId}-${backgroundPath}`.replace(/[^a-z0-9_-]+/gi, '-'))}"
          class="field__input"
          type="color"
          value="${escapeAttr(value)}"
          data-cms-background-path="${escapeAttr(backgroundPath)}"
        >
      </div>
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

  #renderStructureManager(section, schema) {
    return this.#renderInteractiveStructure(section, schema);
    /* Legacy structure renderer retained temporarily below for compatibility. */
    const definitions = schema.repeaters || {};
    const groups = Object.entries(definitions).flatMap(([pattern, definition]) =>
      this.#expandRepeaterPattern(section.data || {}, pattern).map((path) => ({ path, pattern, definition }))
    );
    if (!groups.length) return '';

    const repeaters = groups
      .map((group) => this.#renderRepeater(section, group))
      .join('');
    return `
      <div class="cms-structure-manager">
        <div class="cms-structure-manager__intro">
          <strong>Cấu trúc nội dung</strong>
          <p class="cms-structure-manager__description">
            Thêm, nhân bản, sắp xếp hoặc xóa mục. Chọn nội dung trong bản xem trước để sửa văn bản.
          </p>
        </div>
        ${repeaters}
      </div>
    `;
  }

  #renderRepeater(section, { path, pattern, definition }) {
    const items = asArray(getPath(section.data || {}, path));
    const escapedPath = escapeAttr(path);

    return `
      <section class="cms-repeater">
        <header>
          <strong>${escapeHtml(definition.label || path)}</strong>
          <span>${items.length} mục</span>
        </header>
        <div class="cms-repeater__items">
          ${items.map((item, index) => this.#renderRepeaterItem(item, index, items.length, escapedPath)).join('')}
        </div>
        <button
          type="button"
          class="btn"
          data-size="sm"
          data-variant="outline"
          data-cms-array-action="add"
          data-cms-array-path="${escapedPath}"
          data-cms-array-blueprint="${escapeAttr(pattern)}"
        >
          <i class="fa-solid fa-plus"></i> Thêm mục
        </button>
      </section>
    `;
  }

  #renderRepeaterItem(item, index, itemCount, escapedPath) {
    const actionAttributes = `data-cms-array-path="${escapedPath}" data-cms-array-index="${index}"`;

    return `
      <div class="cms-repeater__item">
        <span class="cms-repeater__drag" title="Kéo để sắp xếp" aria-label="Kéo để sắp xếp"><i class="fa-solid fa-grip-vertical"></i></span>
        <span class="cms-repeater__item-label">${escapeHtml(this.#itemLabel(item, index))}</span>
        <div class="cms-repeater__actions">
          <button type="button" title="Nhân bản" data-cms-array-action="duplicate" ${actionAttributes}>
            <i class="fa-regular fa-copy"></i>
          </button>
          <button type="button" title="Xóa" data-cms-array-action="remove" ${actionAttributes}>
            <i class="fa-regular fa-trash-can"></i>
          </button>
        </div>
      </div>
    `;
  }

  #expandRepeaterPattern(data, pattern) {
    const segments = pattern.split('.');
    const paths = [];
    const walk = (value, index, trail) => {
      if (index >= segments.length) { if (Array.isArray(value)) paths.push(trail.join('.')); return; }
      const segment = segments[index];
      if (segment === '*') {
        if (!Array.isArray(value)) return;
        value.forEach((item, itemIndex) => walk(item, index + 1, [...trail, String(itemIndex)]));
      } else if (value && typeof value === 'object' && Object.prototype.hasOwnProperty.call(value, segment)) {
        walk(value[segment], index + 1, [...trail, segment]);
      }
    };
    walk(data, 0, []);
    return paths;
  }

  openRepeaterItem(path, index) {
    const container = this.root?.querySelector(`[data-cms-dnd-path="${cssEscape(path)}"]`);
    const item = container?.querySelector(`:scope > [data-id="${Number(index)}"]`);
    if (!item) return;
    const trigger = item.querySelector(':scope .accordion__trigger');
    if (trigger?.dataset.state !== 'open') trigger?.click();
    item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  #renderInteractiveStructure(section, schema) {
    const groups = Object.entries(schema.repeaters || {}).flatMap(([pattern, definition]) =>
      this.#expandRepeaterPattern(section.data || {}, pattern).map((path) => ({ path, pattern, definition }))
    );
    if (!groups.length) return '';
    return `<div class="cms-structure-manager">${groups.map((group) => this.#renderInteractiveRepeater(section, group)).join('')}</div>`;
  }

  #renderInteractiveRepeater(section, { path, pattern, definition }) {
    const items = asArray(getPath(section.data || {}, path));
    const escapedPath = escapeAttr(path);
    const programs = pattern === 'programs';
    return `<section class="field cms-repeater${programs ? ' cms-repeater--programs' : ''}">
      <header><span class="field__label">${escapeHtml(definition.label || path)}</span><span>${items.length} mục</span></header>
      <div class="cms-repeater__items accordion" data-cms-dnd-path="${escapedPath}" data-accordion-type="multiple" data-accordion-collapsible data-accordion-icon="none">
        ${items.map((item, index) => this.#renderInteractiveItem(section, item, index, escapedPath, pattern)).join('')}
      </div>
      <button type="button" class="btn" data-size="sm" data-variant="outline" data-cms-array-action="add" data-cms-array-path="${escapedPath}" data-cms-array-blueprint="${escapeAttr(pattern)}"><i class="fa-solid fa-plus"></i> Thêm mục</button>
    </section>`;
  }

  #renderInteractiveItem(section, item, index, escapedPath, pattern) {
    const attrs = `data-cms-array-path="${escapedPath}" data-cms-array-index="${index}"`;
    if (pattern === 'programs') {
      const prefix = `programs.${index}.`;
      const fields = this.cmsDocument.textFieldInstances(section.id)
        .filter((field) => field.path.startsWith(prefix) && !field.path.slice(prefix.length).includes('.'));
      return `<article class="cms-repeater__item cms-program-item accordion_item" data-id="${index}" data-accordion-value="program-${index}">
        <div class="cms-program-item__header">
          <span class="cms-repeater__drag" title="Kéo để sắp xếp" aria-label="Kéo để sắp xếp"><i class="fa-solid fa-grip-vertical"></i></span>
          <button type="button" class="accordion__trigger cms-program-item__trigger"><span>${escapeHtml(this.#itemLabel(item, index))}</span></button>
          <div class="cms-repeater__actions cms-program-item__actions">
            <button type="button" title="Nhân bản" data-cms-array-action="duplicate" ${attrs}><i class="fa-regular fa-copy"></i></button>
            <button type="button" title="Xóa" data-cms-array-action="remove" ${attrs}><i class="fa-regular fa-trash-can"></i></button>
          </div>
        </div>
        <div class="accordion__content cms-program-item__content" hidden><div class="cms-field-grid">${fields.map((field) => this.#renderTextField(field, getPath(section.data || {}, field.path), '')).join('')}</div></div>
      </article>`;
    }
    const prefix = `${escapedPath}.${index}.`;
    const textFields = this.cmsDocument.textFieldInstances(section.id)
      .filter((field) => field.path.startsWith(prefix) && !field.path.slice(prefix.length).includes('.'));
    const imageFields = this.cmsDocument.imageFieldInstances(section.id)
      .filter((field) => field.path.startsWith(prefix));
    const fields = [
      ...textFields.map((field) => this.#renderTextField(field, getPath(section.data || {}, field.path), '')),
      ...imageFields.map((field) => this.#renderImageField(field, '')),
    ].join('');
    if (!fields) {
      return `<div class="cms-repeater__item" data-id="${index}">
        <span class="cms-repeater__drag" title="Kéo để sắp xếp" aria-label="Kéo để sắp xếp"><i class="fa-solid fa-grip-vertical"></i></span>
        <span class="cms-repeater__item-label">${escapeHtml(this.#itemLabel(item, index))}</span><div class="cms-repeater__actions">
        <button type="button" title="Nhân bản" data-cms-array-action="duplicate" ${attrs}><i class="fa-regular fa-copy"></i></button>
        <button type="button" title="Xóa" data-cms-array-action="remove" ${attrs}><i class="fa-regular fa-trash-can"></i></button>
      </div></div>`;
    }
    const definition = this.cmsDocument.sectionSchema(section.id)?.repeaters?.[pattern] || {};
    const minimumReached = Number(definition.min || 0) >= (getPath(section.data || {}, escapedPath)?.length || 0);
    return `<article class="cms-repeater__item cms-program-item accordion_item" data-id="${index}" data-accordion-value="${escapeAttr(`${escapedPath}-${index}`)}">
      <div class="cms-program-item__header">
        <span class="cms-repeater__drag" title="Kéo để sắp xếp" aria-label="Kéo để sắp xếp"><i class="fa-solid fa-grip-vertical"></i></span>
        <button type="button" class="accordion__trigger cms-program-item__trigger"><span>${escapeHtml(this.#itemLabel(item, index))}</span></button>
        <div class="cms-repeater__actions cms-program-item__actions">
          <button type="button" title="Nhân bản" data-cms-array-action="duplicate" ${attrs}><i class="fa-regular fa-copy"></i></button>
          <button type="button" title="Xóa" data-cms-array-action="remove" ${attrs}${minimumReached ? ' disabled aria-disabled="true"' : ''}><i class="fa-regular fa-trash-can"></i></button>
        </div>
      </div>
      <div class="accordion__content cms-program-item__content" hidden><div class="cms-field-grid">${fields}</div></div>
    </article>`;
  }

  #initializeStructureWidgets() {
    this.dndInstances.forEach((instance) => instance.destroy());
    this.dndInstances = [];
    this.root.querySelectorAll('.accordion').forEach((root) => window.AccordionHandler?.instance.register(root));
    if (!window.DnD) return;
    this.root.querySelectorAll('[data-cms-dnd-path]').forEach((container) => {
      this.dndInstances.push(new window.DnD(container, {
        handle: '.cms-repeater__drag',
        draggable: ':scope > .cms-repeater__item',
        filter: 'button,input,textarea,select',
        animation: 180,
        direction: 'vertical',
        ghostHandle: false,
        onEnd: ({ oldIndex, newIndex }) => {
          if (oldIndex !== newIndex) this.bus.dispatch('structure:mutate', { action: 'move', path: container.dataset.cmsDndPath, index: oldIndex, newIndex });
        },
      }));
    });
  }


  #itemLabel(item, index) {
    if (typeof item === 'string' || typeof item === 'number') return String(item).slice(0, 70) || `Mục ${index + 1}`;
    return item?.name || item?.title || item?.label || item?.code || `Mục ${index + 1}`;
  }
}
