const TEXT_BLOCKS = new Set(['cms/heading', 'cms/paragraph', 'cms/quote']);

const BLOCKS = [
  { type: 'cms/heading', title: 'Heading', icon: 'fa-heading', group: 'Content' },
  { type: 'cms/paragraph', title: 'Paragraph', icon: 'fa-paragraph', group: 'Content' },
  { type: 'cms/image', title: 'Image', icon: 'fa-image', group: 'Content' },
  { type: 'cms/button', title: 'Button', icon: 'fa-hand-pointer', group: 'Content' },
  { type: 'cms/button_group', title: 'Button group', icon: 'fa-grip', group: 'Content' },
  { type: 'cms/quote', title: 'Quote', icon: 'fa-quote-left', group: 'Content' },
  { type: 'cms/spacer', title: 'Spacer', icon: 'fa-arrows-up-down', group: 'Layout' },
  { type: 'cms/columns', title: 'Columns', icon: 'fa-columns', group: 'Layout' },
  { type: 'cms/card_grid', title: 'Card grid', icon: 'fa-table-cells-large', group: 'Layout' },
  { type: 'cms/stat_grid', title: 'Stat grid', icon: 'fa-chart-simple', group: 'Layout' },
  { type: 'cms/carousel', title: 'Carousel', icon: 'fa-images', group: 'Dynamic' },
  { type: 'cms/newsfeed', title: 'Newsfeed', icon: 'fa-newspaper', group: 'Dynamic' },
];

const DEFAULTS = {
  'cms/heading': { rich_text: text('New landing heading'), meta: { level: 2, align: 'center', variant: 'section' } },
  'cms/paragraph': { rich_text: text('Write landing page copy here.'), meta: { align: 'center', variant: 'body' } },
  'cms/image': { rich_text: [], meta: { url: '', alt: '', caption: '', ratio: 'wide', variant: 'rounded' } },
  'cms/button': { rich_text: [], meta: { label: 'Learn more', url: '#', variant: 'primary' } },
  'cms/button_group': { rich_text: [], meta: { buttons: [{ label: 'Primary action', url: '#', variant: 'primary' }, { label: 'Secondary', url: '#', variant: 'outline' }] } },
  'cms/quote': { rich_text: text('Add a memorable quote.'), meta: { citation: '' } },
  'cms/spacer': { rich_text: [], meta: { size: 'md' } },
  'cms/columns': { rich_text: [], meta: { variant: 'balanced', columns: [{ title: 'First column', body: 'Describe this point.' }, { title: 'Second column', body: 'Describe this point.' }] } },
  'cms/card_grid': { rich_text: [], meta: { columns: 3, variant: 'soft', items: [{ title: 'Card title', description: 'Card description.' }, { title: 'Card title', description: 'Card description.' }, { title: 'Card title', description: 'Card description.' }] } },
  'cms/stat_grid': { rich_text: [], meta: { columns: 4, variant: 'cards', items: [{ number: '100+', label: 'Metric', description: 'Short description.' }, { number: '24/7', label: 'Support', description: 'Short description.' }, { number: '50+', label: 'Partners', description: 'Short description.' }, { number: '95%', label: 'Success', description: 'Short description.' }] } },
  'cms/carousel': { rich_text: [], meta: { carousel_slug: 'landing-page', variant: 'standard' } },
  'cms/newsfeed': { rich_text: [], meta: { mode: 'featured_latest', featured_count: 4, latest_count: 3, variant: 'landing' } },
};

class CmsBuilder {
  constructor(payload) {
    this.payload = payload || {};
    this.page = { ...(payload.page || {}) };
    this.document = this.normalizeDocument(payload.document || {});
    this.activeId = this.document.blocks[0]?.id || null;
    this.mediaTargetId = null;

    this.blockList = document.querySelector('#cms-builder-block-list');
    this.library = document.querySelector('#cms-block-library');
    this.structure = document.querySelector('#cms-structure-list');
    this.inspector = document.querySelector('#cms-block-settings-panel');
    this.form = document.querySelector('#cms-page-form');
    this.hiddenInput = document.querySelector('#cms-editor-data');
    this.titleInput = document.querySelector('#cms-page-title-input');
    this.preview = document.querySelector('#cms-builder-preview');
  }

  init() {
    this.renderLibrary();
    this.render();
    this.bind();
    new DnD(this.blockList, { animation: 150, group: 'cms-builder', handle: '.be-drag-handle' });
  }

  normalizeDocument(document) {
    const blocks = Array.isArray(document.blocks) ? document.blocks : [];
    return {
      version: 1,
      blocks: blocks.map((block) => ({
        id: block.id || this.uid(),
        type: block.type,
        version: block.version || 1,
        data: {
          rich_text: Array.isArray(block.data?.rich_text) ? block.data.rich_text : [],
          meta: { ...(block.data?.meta || {}) },
        },
      })).filter((block) => DEFAULTS[block.type]),
    };
  }

  bind() {
    document.querySelector('#be-toggle-left')?.addEventListener('click', () => this.togglePanel('#be-left'));
    document.querySelector('#be-toggle-right')?.addEventListener('click', () => this.togglePanel('#be-right'));
    this.titleInput?.addEventListener('input', () => { this.page.title = this.titleInput.value; });

    this.blockList.addEventListener('click', (event) => {
      const mediaButton = event.target.closest('[data-pick-media]');
      if (mediaButton) {
        this.mediaTargetId = mediaButton.dataset.pickMedia;
        return;
      }

      const action = event.target.closest('[data-cms-action]');
      const card = event.target.closest('.cms-builder-card');
      if (!card) return;
      this.select(card.dataset.blockId);
      if (!action) return;
      const name = action.dataset.cmsAction;
      if (name === 'delete') this.deleteBlock(card.dataset.blockId);
      if (name === 'duplicate') this.duplicateBlock(card.dataset.blockId);
    });

    this.blockList.addEventListener('input', (event) => {
      const editable = event.target.closest('[data-cms-editable]');
      if (!editable) return;
      const block = this.block(editable.dataset.blockId);
      if (!block) return;
      block.data.rich_text = text(editable.innerText.trim());
      this.renderStructure();
    });

    DnDMonitor.on('dragend', () => {
      const order = Array.from(this.blockList.querySelectorAll('.cms-builder-card')).map((el) => el.dataset.blockId);
      const map = new Map(this.document.blocks.map((block) => [block.id, block]));
      this.document.blocks = order.map((id) => map.get(id)).filter(Boolean);
      this.renderStructure();
    });

    document.addEventListener('click', (event) => {
      const widthTrigger = event.target.closest('[data-preview-width]');
      if (!widthTrigger) return;
      this.preview.dataset.previewMode = widthTrigger.dataset.previewWidth === 'mobile' ? 'mobile' : 'desktop';
      document.querySelectorAll('[data-preview-width]').forEach((button) => {
        button.dataset.variant = button === widthTrigger ? 'primary' : 'outline';
      });
    });

    document.querySelector('#media-selector-modal')?.addEventListener('msm:submit', (event) => {
      const block = this.block(this.mediaTargetId);
      const media = event.detail?.media;
      if (!block || !media?.file_path) return;
      block.data.meta.url = `${(window.PUBLIC_MEDIA_BASE || '').replace(/\/$/, '')}/${String(media.file_path).replace(/^\//, '')}`;
      block.data.meta.alt = media.alt_text || media.title || '';
      event.detail?.close?.();
      this.render();
    });

    this.form?.addEventListener('submit', (event) => {
      this.syncFocusedEditable();
      const action = event.submitter?.value || 'draft';
      this.hiddenInput.value = JSON.stringify({
        title: this.page.title || this.payload.schema?.title || '',
        content: this.document,
        settings: this.page.settings || {},
        action,
      });
    });
  }

  renderLibrary() {
    const groups = BLOCKS.reduce((all, block) => {
      all[block.group] ||= [];
      all[block.group].push(block);
      return all;
    }, {});

    this.library.innerHTML = Object.entries(groups).map(([group, blocks]) => `
      <div class="be-block-group">
        <span class="be-block-group__label">${escapeHtml(group)}</span>
        ${blocks.map((block) => `
          <button type="button" class="btn be-block-btn" data-variant="outline" data-add-block="${escapeAttr(block.type)}">
            <div class="be-block-btn__icon"><i class="fa-solid ${block.icon}"></i></div>
            <div class="be-block-btn__name">${escapeHtml(block.title)}</div>
          </button>
        `).join('')}
      </div>
    `).join('');

    this.library.querySelectorAll('[data-add-block]').forEach((button) => {
      button.addEventListener('click', () => this.addBlock(button.dataset.addBlock));
    });
  }

  render() {
    this.blockList.innerHTML = this.document.blocks.length
      ? this.document.blocks.map((block) => this.renderCard(block)).join('')
      : this.emptyState();
    this.renderStructure();
    this.renderInspector();
  }

  renderCard(block) {
    const active = block.id === this.activeId ? ' is-active' : '';
    return `
      <div class="cms-builder-card${active}" data-block-id="${escapeAttr(block.id)}" data-block-type="${escapeAttr(block.type)}">
        <div class="be-drag-handle" title="Drag to reorder"><i class="fa-solid fa-grip-vertical"></i></div>
        <div class="cms-builder-card__actions">
          <button type="button" class="btn" data-size="sm" data-variant="outline" data-cms-action="duplicate"><i class="fa-regular fa-copy"></i></button>
          <button type="button" class="btn" data-size="sm" data-variant="outline" data-cms-action="delete"><i class="fa-regular fa-trash-can"></i></button>
        </div>
        ${this.renderBlock(block)}
      </div>
    `;
  }

  renderBlock(block) {
    const meta = block.data.meta || {};
    const content = escapeHtml(richTextPlain(block.data.rich_text));
    switch (block.type) {
      case 'cms/heading':
        return `<section class="cms-block-section cms-text-align-${meta.align || 'left'}"><h${meta.level || 2} class="cms-heading cms-heading--${meta.variant || 'section'}" contenteditable="true" data-cms-editable data-block-id="${block.id}">${content}</h${meta.level || 2}></section>`;
      case 'cms/paragraph':
        return `<section class="cms-block-section cms-text-align-${meta.align || 'left'}"><p class="cms-paragraph cms-paragraph--${meta.variant || 'body'}" contenteditable="true" data-cms-editable data-block-id="${block.id}">${content}</p></section>`;
      case 'cms/quote':
        return `<section class="cms-block-section"><blockquote class="cms-quote"><p contenteditable="true" data-cms-editable data-block-id="${block.id}">${content}</p><cite>${escapeHtml(meta.citation || '')}</cite></blockquote></section>`;
      case 'cms/image':
        return this.renderImageBlock(block);
      case 'cms/button':
        return `<section class="cms-block-section cms-text-align-center">${buttonHtml(meta)}</section>`;
      case 'cms/button_group':
        return `<section class="cms-block-section"><div class="cms-button-group">${(meta.buttons || []).map(buttonHtml).join('')}</div></section>`;
      case 'cms/spacer':
        return `<div class="cms-spacer cms-spacer--${meta.size || 'md'}"><span>Spacer: ${escapeHtml(meta.size || 'md')}</span></div>`;
      case 'cms/columns':
        return `<section class="cms-block-section"><div class="cms-columns">${(meta.columns || []).map((item) => `<article class="cms-column-card"><h3>${escapeHtml(item.title || '')}</h3><p>${escapeHtml(item.body || '')}</p></article>`).join('')}</div></section>`;
      case 'cms/card_grid':
        return `<section class="cms-block-section"><div class="cms-card-grid cms-grid-cols-${meta.columns || 3}">${(meta.items || []).map((item) => `<article class="cms-card"><h3>${escapeHtml(item.title || '')}</h3><p>${escapeHtml(item.description || '')}</p></article>`).join('')}</div></section>`;
      case 'cms/stat_grid':
        return `<section class="cms-block-section"><div class="cms-stat-grid cms-grid-cols-${meta.columns || 4}">${(meta.items || []).map((item) => `<article class="cms-stat"><strong>${escapeHtml(item.number || '')}</strong><h3>${escapeHtml(item.label || '')}</h3><p>${escapeHtml(item.description || '')}</p></article>`).join('')}</div></section>`;
      case 'cms/carousel':
        return `<section class="cms-dynamic-section relative container py-16"><div class="container-wrapper"><div class="cms-dynamic-section__box"><span class="badge" data-variant="primary"><i class="fa-solid fa-images"></i> Dynamic</span><h2 class="section__title">Carousel</h2><p class="section__sub-title">Renders existing carousel slides on the public page.</p></div></div></section>`;
      case 'cms/newsfeed':
        return `<section class="cms-dynamic-section relative container py-16"><div class="container-wrapper"><div class="cms-dynamic-section__box"><span class="badge" data-variant="primary"><i class="fa-regular fa-newspaper"></i> Dynamic</span><h2 class="section__title">Newsfeed</h2><p class="section__sub-title">Renders featured and latest posts on the public page.</p></div></div></section>`;
      default:
        return '';
    }
  }

  renderImageBlock(block) {
    const meta = block.data.meta || {};
    const url = meta.url || '';
    if (!url) {
      return `<section class="cms-block-section"><div class="cms-editable-image--empty"><i class="fa-regular fa-image"></i><button type="button" class="btn" data-variant="primary" data-size="md" data-pick-media="${block.id}" data-modal-trigger="#media-selector-modal">Choose image</button></div></section>`;
    }
    return `<section class="cms-block-section"><figure class="cms-image cms-image--${meta.ratio || 'wide'} cms-image--${meta.variant || 'rounded'}"><img src="${escapeAttr(url)}" alt="${escapeAttr(meta.alt || '')}"><figcaption>${escapeHtml(meta.caption || '')}</figcaption></figure></section>`;
  }

  renderStructure() {
    this.structure.innerHTML = this.document.blocks.length
      ? this.document.blocks.map((block) => `<button type="button" class="be-list-view__item${block.id === this.activeId ? ' is-active' : ''}" data-structure-id="${block.id}"><span class="be-list-view__icon"><i class="fa-solid ${iconFor(block.type)}"></i></span><span class="be-list-view__info"><span class="be-list-view__title">${escapeHtml(titleFor(block))}</span><span class="be-list-view__anchor">${escapeHtml(block.type)}</span></span></button>`).join('')
      : this.emptyState('No blocks yet');
    this.structure.querySelectorAll('[data-structure-id]').forEach((button) => {
      button.addEventListener('click', () => this.select(button.dataset.structureId));
    });
  }

  renderInspector() {
    const block = this.block(this.activeId);
    if (!block) {
      this.inspector.innerHTML = this.emptyState('Select a landing block to edit its settings.');
      return;
    }
    this.inspector.innerHTML = `<div class="field-group">${this.inspectorFields(block)}</div>`;
    this.inspector.querySelectorAll('[data-meta-key]').forEach((field) => {
      field.addEventListener('input', () => {
        this.setMeta(block, field.dataset.metaKey, field.type === 'number' ? Number(field.value) : field.value);
        this.renderPartial();
      });
      field.addEventListener('change', () => {
        this.setMeta(block, field.dataset.metaKey, field.type === 'number' ? Number(field.value) : field.value);
        this.renderPartial();
      });
    });
    this.inspector.querySelectorAll('[data-json-key]').forEach((field) => {
      field.addEventListener('input', () => {
        try {
          block.data.meta[field.dataset.jsonKey] = JSON.parse(field.value || '[]');
          field.classList.remove('is-invalid');
          this.renderPartial();
        } catch {
          field.classList.add('is-invalid');
        }
      });
    });
    this.inspector.querySelector('[data-pick-media]')?.addEventListener('click', (event) => {
      this.mediaTargetId = event.currentTarget.dataset.pickMedia;
    });
  }

  inspectorFields(block) {
    const meta = block.data.meta || {};
    const commonAlign = `<div class="field"><span class="field__label">Alignment</span>${select('align', meta.align || 'left', ['left', 'center', 'right'])}</div>`;
    switch (block.type) {
      case 'cms/heading':
        return `<div class="field"><span class="field__label">Level</span>${select('level', String(meta.level || 2), ['1', '2', '3'])}</div>${commonAlign}<div class="field"><span class="field__label">Variant</span>${select('variant', meta.variant || 'section', ['display', 'section', 'eyebrow'])}</div>`;
      case 'cms/paragraph':
        return `${commonAlign}<div class="field"><span class="field__label">Variant</span>${select('variant', meta.variant || 'body', ['body', 'lead', 'muted'])}</div>`;
      case 'cms/image':
        return `<div class="field"><span class="field__label">Image URL</span><input class="field__input" data-meta-key="url" value="${escapeAttr(meta.url || '')}"></div><button type="button" class="btn" data-variant="outline" data-pick-media="${block.id}" data-modal-trigger="#media-selector-modal">Choose from media</button><div class="field"><span class="field__label">Alt text</span><input class="field__input" data-meta-key="alt" value="${escapeAttr(meta.alt || '')}"></div><div class="field"><span class="field__label">Caption</span><input class="field__input" data-meta-key="caption" value="${escapeAttr(meta.caption || '')}"></div><div class="field"><span class="field__label">Ratio</span>${select('ratio', meta.ratio || 'wide', ['wide', 'square', 'banner'])}</div>`;
      case 'cms/button':
        return buttonFields(meta);
      case 'cms/button_group':
        return jsonField('buttons', meta.buttons || []);
      case 'cms/quote':
        return `<div class="field"><span class="field__label">Citation</span><input class="field__input" data-meta-key="citation" value="${escapeAttr(meta.citation || '')}"></div>`;
      case 'cms/spacer':
        return `<div class="field"><span class="field__label">Size</span>${select('size', meta.size || 'md', ['sm', 'md', 'lg'])}</div>`;
      case 'cms/columns':
        return jsonField('columns', meta.columns || []);
      case 'cms/card_grid':
      case 'cms/stat_grid':
        return `<div class="field"><span class="field__label">Columns</span><input class="field__input" type="number" min="2" max="4" data-meta-key="columns" value="${escapeAttr(meta.columns || 3)}"></div>${jsonField('items', meta.items || [])}`;
      case 'cms/carousel':
        return `<div class="field"><span class="field__label">Carousel slug</span><input class="field__input" data-meta-key="carousel_slug" value="${escapeAttr(meta.carousel_slug || 'landing-page')}"></div>`;
      case 'cms/newsfeed':
        return `<div class="field"><span class="field__label">Featured count</span><input class="field__input" type="number" min="0" max="6" data-meta-key="featured_count" value="${escapeAttr(meta.featured_count || 4)}"></div><div class="field"><span class="field__label">Latest count</span><input class="field__input" type="number" min="0" max="6" data-meta-key="latest_count" value="${escapeAttr(meta.latest_count || 3)}"></div>`;
      default:
        return '';
    }
  }

  addBlock(type) {
    const block = { id: this.uid(), type, version: 1, data: clone(DEFAULTS[type]) };
    this.document.blocks.push(block);
    this.activeId = block.id;
    this.render();
  }

  select(id) {
    this.syncFocusedEditable();
    this.activeId = id;
    this.render();
  }

  deleteBlock(id) {
    this.document.blocks = this.document.blocks.filter((block) => block.id !== id);
    if (this.activeId === id) this.activeId = this.document.blocks[0]?.id || null;
    this.render();
  }

  duplicateBlock(id) {
    const index = this.document.blocks.findIndex((block) => block.id === id);
    if (index < 0) return;
    const copy = clone(this.document.blocks[index]);
    copy.id = this.uid();
    this.document.blocks.splice(index + 1, 0, copy);
    this.activeId = copy.id;
    this.render();
  }

  block(id) {
    return this.document.blocks.find((block) => block.id === id) || null;
  }

  setMeta(block, path, value) {
    const keys = path.split('.');
    let target = block.data.meta;
    while (keys.length > 1) {
      const key = keys.shift();
      target[key] ||= {};
      target = target[key];
    }
    target[keys[0]] = value;
  }

  renderPartial() {
    this.syncFocusedEditable();
    this.render();
  }

  syncFocusedEditable() {
    const editable = document.activeElement?.closest?.('[data-cms-editable]');
    if (!editable) return;
    const block = this.block(editable.dataset.blockId);
    if (block) block.data.rich_text = text(editable.innerText.trim());
  }

  togglePanel(selector) {
    const panel = document.querySelector(selector);
    if (!panel) return;
    panel.dataset.bePanelState = panel.dataset.bePanelState === 'collapsed' ? 'expanded' : 'collapsed';
  }

  emptyState(text = 'Choose a block from the left panel to start building.') {
    return `<div class="empty"><div class="empty__header"><div class="empty__media"><i class="fa-solid fa-cubes"></i></div><div class="empty__title">No landing blocks</div><div class="empty__description">${escapeHtml(text)}</div></div></div>`;
  }

  uid() {
    return `cms_${Math.random().toString(16).slice(2)}${Date.now().toString(16)}`;
  }
}

function text(value) {
  return value ? [{ type: 'text', text: value, marks: [] }] : [];
}

function richTextPlain(segments) {
  return Array.isArray(segments) ? segments.map((segment) => segment.text || '').join('') : '';
}

function clone(value) {
  return JSON.parse(JSON.stringify(value));
}

function escapeHtml(value) {
  return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function escapeAttr(value) {
  return escapeHtml(value);
}

function select(key, current, values) {
  return `<select class="field__input" data-meta-key="${escapeAttr(key)}">${values.map((value) => `<option value="${escapeAttr(value)}" ${String(current) === String(value) ? 'selected' : ''}>${escapeHtml(value)}</option>`).join('')}</select>`;
}

function jsonField(key, value) {
  return `<div class="field"><span class="field__label">${escapeHtml(key)} JSON</span><textarea class="field__input cms-json-field" rows="10" data-json-key="${escapeAttr(key)}">${escapeHtml(JSON.stringify(value, null, 2))}</textarea><div class="field__description">Edit structured items for this preset block.</div></div>`;
}

function buttonFields(meta) {
  return `<div class="field"><span class="field__label">Label</span><input class="field__input" data-meta-key="label" value="${escapeAttr(meta.label || '')}"></div><div class="field"><span class="field__label">URL</span><input class="field__input" data-meta-key="url" value="${escapeAttr(meta.url || '#')}"></div><div class="field"><span class="field__label">Variant</span>${select('variant', meta.variant || 'primary', ['primary', 'outline', 'secondary'])}</div>`;
}

function buttonHtml(meta = {}) {
  return `<a class="btn cms-button" data-variant="${escapeAttr(meta.variant || 'primary')}" href="${escapeAttr(meta.url || '#')}">${escapeHtml(meta.label || 'Button')}</a>`;
}

function titleFor(block) {
  if (TEXT_BLOCKS.has(block.type)) {
    return richTextPlain(block.data.rich_text) || block.type;
  }
  return BLOCKS.find((item) => item.type === block.type)?.title || block.type;
}

function iconFor(type) {
  return BLOCKS.find((item) => item.type === type)?.icon || 'fa-square';
}

const builder = new CmsBuilder(window.CmsPageEditor || {});
builder.init();
window.CmsEditor = builder;
