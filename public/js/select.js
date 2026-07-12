/**
 * select.js - Simplified SelectHandler with Multi-Select & Search
 * Architecture: Single class, Map registry, portal on init, data-state driven.
 * Features: Single/Multi mode, live search, viewport flip, z-index stacking, event delegation.
 * 
 * HTML Contract:
 * <div class="select" data-select-id="unique" data-select-multiple data-select-searchable data-select-placeholder="Chọn...">
 *   <div class="select__option" data-select-value="1">Option 1</div>
 *   <div class="select__option" data-select-value="2">Option 2</div>
 * </div>
 * 
 * Event: "select:change" bubbles from root
 * Single: e.detail = { value, label }
 * Multi:  e.detail = { values: [{ value, label }] }
 */
document.addEventListener('DOMContentLoaded', () => new SelectHandler().init());

class SelectHandler {
  static ANCHOR_OFFSET = 4;
  static VIEWPORT_MARGIN = 8;
  static BASE_Z_INDEX = 100;
  #zCounter = 0;
  static #instance = null;
  #ICONS = {
    CHEVRON: `<i class="fa-solid fa-chevron-down"></i>`,
    CHECK: `<i class="fa-solid fa-check"></i>`,
    TAG_REMOVE: `<i class="fa-solid fa-xmark"></i>`,
  }

  #portal = null;
  _instances = new Map();

  constructor() {
    if (SelectHandler.#instance) return SelectHandler.#instance;
    SelectHandler.#instance = this;
  }

  static get instance() {
    return SelectHandler.#instance || new SelectHandler();
  }

  /* ── Public API ─────────────────────────────────────────────── */
  clearSelection(id) {
    const inst = this._instances.get(id);
    if (!inst) return;
    inst.selected.clear();
    this._syncSelection(inst);
    this._renderTriggerState(inst);
  }

  setValue(id, value, { emit = true } = {}) {
    const inst = this._instances.get(id);
    if (!inst || inst.isMultiple) return;
    const normalized = value == null ? null : String(value);
    const item = inst.items.find(candidate => candidate.dataset.selectValue === normalized);
    inst.selected.clear();
    if (item && !item.dataset.selectDisabled) inst.selected.add(normalized);
    this._syncSelection(inst);
    this._renderTriggerState(inst);
    if (emit) this._dispatch(inst);
  }

  /* ── Lifecycle & Registry ───────────────────────────────────── */
  init() {
    document.querySelectorAll('.select:not([data-select-initialized])').forEach(el => this._initRoot(el));
    this._bindGlobalListeners();
  }

  register(target, options = {}) {
    const el = typeof target === 'string' ? document.querySelector(target) : target;
    if (!el || el.dataset.selectInitialized) return;
    this._initRoot(el, options.options);
    if (typeof options.onChange === 'function') el.__selectOnChange = options.onChange;
  }

  open(id) {
    const inst = this._instances.get(id);
    if (!inst || inst.isOpen || inst.isDisabled) return;
    this.closeAll();
    inst.isOpen = true;
    inst.root.dataset.selectOpen = 'true';
    inst.content.dataset.state = 'open';

    document.body.style.overflow = "hidden";

    this._position(inst.content, inst.trigger);
    inst.content.style.zIndex = SelectHandler.BASE_Z_INDEX + (++this.#zCounter);
    if (inst.search) { inst.search.value = ''; this._filterItems(inst, ''); requestAnimationFrame(() => inst.search.focus()); }
  }

  close(id) {
    const inst = this._instances.get(id);
    if (!inst || !inst.isOpen) return;
    inst.isOpen = false;
    inst.root.dataset.selectOpen = 'false';
    inst.content.dataset.state = 'closed';

    document.body.style.overflow = "auto";

    if (inst.contentWrapper) {
      inst.contentWrapper.style.position = '';
      inst.contentWrapper.style.top = '';
      inst.contentWrapper.style.left = '';
      inst.contentWrapper.style.width = '';
      inst.contentWrapper.style.zIndex = '';
    }
  }

  closeAll() {
    this._instances.forEach(inst => inst.isOpen && this.close(inst.id));
  }

  /* ── Initialization ─────────────────────────────────────────── */
  _initRoot(root, dynamicOptions = []) {
    root.dataset.selectInitialized = 'true';
    const id = root.dataset.selectId || (root.dataset.selectId = crypto.randomUUID());
    if (this._instances.has(id)) return;

    const isDisabled = root.hasAttribute('data-select-disabled');
    const isMultiple = root.hasAttribute('data-select-multiple');
    const isSearchable = root.hasAttribute('data-select-searchable');
    const placeholder = root.getAttribute('data-select-placeholder') || 'Chọn...';

    const trigger = root.querySelector(':scope > .select__trigger') || this._buildTrigger(root, isDisabled, placeholder, isMultiple);
    const contentWrapper = document.createElement("div");
    contentWrapper.classList.add("select__content-wrapper");
    contentWrapper.dataset.selectFor = id;
    contentWrapper.appendChild(root.querySelector(':scope > .select__content') || this._buildContent(root, isSearchable))
    const content = contentWrapper;

    let search = content.querySelector('.select__search');
    if (isSearchable && !search) {
      search = this._buildSearchInput(content);
    }

    const empty = content.querySelector('.select__empty') || this._buildEmpty(content);

    // Normalize options
    let items = [...content.querySelectorAll('.select__item')];
    if (dynamicOptions.length) items = this._injectDynamicItems(viewport, dynamicOptions);
    items.forEach(el => el.classList.add('select__item')); // CSS sync
    items.forEach(item => {
      if (!item.querySelector('.select__item-indicator')) {
        const indicator = document.createElement('span');
        indicator.className = 'select__item-indicator';
        indicator.setAttribute('aria-hidden', 'true');
        indicator.innerHTML = `<i class="fa-solid fa-check"></i>`;
        item.appendChild(indicator);
      }
    });

    const instance = {
      id, root, trigger, content, search, empty,
      items, placeholder, isMultiple, isSearchable, isDisabled,
      selected: new Set(),
      isOpen: false
    };
    this._instances.set(id, instance);

    // Portal & UI setup
    this._getPortal().appendChild(content);
    this._renderTriggerState(instance);
    this._bindTrigger(instance);
    this._bindContent(instance);
    this._setDefaultValue(instance);
    if (isSearchable && search) this._bindSearch(instance);
  }

  _injectDynamicItems(container, groups) {
    container.innerHTML = '';
    const items = [];
    groups.forEach(g => {
      if (g.group) {
        const label = document.createElement('div');
        label.className = 'select__label';
        label.textContent = g.group;
        container.appendChild(label);
      }
      g.items.forEach(it => {
        const item = document.createElement('div');
        item.className = 'select__item';
        item.dataset.selectValue = it.value;
        if (it.disabled) item.dataset.selectDisabled = '';
        item.textContent = it.label;

        const indicator = document.createElement('span');
        indicator.className = 'select__item-indicator';
        indicator.setAttribute('aria-hidden', 'true');
        indicator.innerHTML = this.#ICONS.CHECK;
        item.appendChild(indicator);

        container.appendChild(item);
        items.push(item);
      });
    });
    return items;
  }

  /* ── DOM Builders ───────────────────────────────────────────── */
  /**
 * Builds and injects the search input if missing.
 * @param {HTMLElement} content - The .select__content container
 * @returns {HTMLInputElement} The search input element
 */
  _buildSearchInput(content) {
    const wrap = document.createElement('div');
    wrap.className = 'select__search-wrap';
    wrap.innerHTML = `<input type="text" class="select__search" placeholder="Tìm kiếm..." autocomplete="off" aria-label="Tìm kiếm" />`;

    // This ensures order: search
    if (content.firstChild) {
      content.insertBefore(wrap, content.firstChild);
    } else {
      content.appendChild(wrap);
    }
    return wrap.querySelector('.select__search');
  }

  _buildTrigger(root, disabled, placeholder, isMultiple) {
    const t = document.createElement('div');
    t.className = 'select__trigger';
    t.setAttribute('role', 'combobox');
    t.setAttribute('aria-expanded', 'false');
    t.setAttribute('aria-haspopup', 'listbox');
    t.setAttribute('tabindex', disabled ? '-1' : '0');
    const ariaLabel = root.getAttribute('aria-label');
    if (ariaLabel) t.setAttribute('aria-label', ariaLabel);
    const ariaLabelledby = root.getAttribute('aria-labelledby');
    if (ariaLabelledby) t.setAttribute('aria-labelledby', ariaLabelledby);
    if (disabled) t.dataset.selectDisabled = '';

    const container = document.createElement('div');
    container.className = isMultiple ? 'select__tags' : 'select__value';
    t.appendChild(container);

    const icon = document.createElement('span');
    icon.className = 'select__icon-wrapper';
    icon.innerHTML = this.#ICONS.CHEVRON;
    t.appendChild(icon);
    root.prepend(t);
    return t;
  }

  _buildContent(root, searchable) {
    const c = document.createElement('div');
    c.className = 'select__content';
    c.setAttribute('role', 'listbox');

    if (searchable) {
      const wrap = document.createElement('div');
      wrap.className = 'select__search-wrap';
      wrap.innerHTML = `<input type="text" class="select__search" placeholder="Tìm kiếm..." autocomplete="off" aria-label="Tìm kiếm" />`;
      c.appendChild(wrap);
    }

    const vp = document.createElement('div');
    vp.className = 'select__viewport';
    c.appendChild(vp);

    root.querySelectorAll(':scope > .select__item, :scope > .select__group > .select__item')
      .forEach(el => vp.appendChild(el));

    const empty = document.createElement('div');
    empty.className = 'select__empty';
    empty.textContent = 'Không có kết quả.';
    c.appendChild(empty);

    root.appendChild(c);
    return c;
  }

  _buildEmpty(parent) {
    const e = document.createElement('div');
    e.className = 'select__empty';
    e.textContent = 'Không có kết quả.';
    parent.appendChild(e);
    return e;
  }

  /* ── Event Binding ──────────────────────────────────────────── */
  _bindGlobalListeners() {
    document.addEventListener('click', e => { if (!e.target.closest('.select-portal')) this.closeAll(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') this.closeAll(); });
  }

  _bindTrigger(instance) {
    instance.trigger.addEventListener('click', e => {
      e.stopPropagation();
      if (instance.isDisabled) return;
      instance.isOpen ? this.close(instance.id) : this.open(instance.id);
    });
  }

  _bindContent(instance) {
    instance.content.addEventListener('click', e => {
      e.stopPropagation();
      const item = e.target.closest('.select__item');
      if (!item || item.dataset.selectDisabled || item.dataset.selectHidden) return;
      this._toggleSelect(instance, item);
    });
  }

  _setDefaultValue(instance) {
    const defaultValue = instance.root.dataset.selectDefaultValue?.trim();
    if (!defaultValue) return;

    const values = instance.isMultiple
      ? defaultValue.split(',').map(v => v.trim()).filter(v => v)
      : [defaultValue];

    values.forEach(v => {
      const item = instance.items.find(i => i.dataset.selectValue?.trim() === v);
      if (item && !item.dataset.selectDisabled) {
        instance.selected.add(v);
      }
    });

    this._syncSelection(instance);
    this._renderTriggerState(instance);
    this._dispatch(instance);
  }
  _bindSearch(instance) {
    instance.search.addEventListener('input', e => {
      e.stopPropagation();
      this._filterItems(instance, e.target.value)
    });
  }

  /* ── Core Logic ─────────────────────────────────────────────── */
  _toggleSelect(instance, item) {
    const value = item.dataset.selectValue;
    const label = item.textContent.trim();

    if (instance.isMultiple) {
      instance.selected.has(value) ? instance.selected.delete(value) : instance.selected.add(value);
    } else {
      instance.selected.clear();
      instance.selected.add(value);
      this.close(instance.id);
    }

    this._syncSelection(instance);
    this._renderTriggerState(instance);
    this._dispatch(instance);
  }

  _syncSelection(instance) {
    instance.items.forEach(item => {
      const v = item.dataset.selectValue;
      if (instance.selected.has(v)) {
        item.dataset.selectSelected = '';
        item.setAttribute('aria-selected', 'true');
      } else {
        delete item.dataset.selectSelected;
        item.setAttribute('aria-selected', 'false');
      }
    });
  }

  _filterItems(instance, query) {
    const q = query.trim().toLowerCase();
    let visibleCount = 0;

    instance.items.forEach(item => {
      const label = item.textContent.toLowerCase();
      const match = !q || label.includes(q);

      if (match) {
        delete item.dataset.selectHidden;
        visibleCount++;
      } else {
        item.dataset.selectHidden = '';
      }
    });

    if (instance.empty) {
      if (visibleCount === 0) {
        instance.empty.dataset.selectVisible = '';
      } else {
        delete instance.empty.dataset.selectVisible;
      }
    }
  }

  _renderTriggerState(instance) {
    const container = instance.trigger.querySelector('.select__tags, .select__value');
    container.innerHTML = '';

    if (instance.selected.size === 0) {
      const ph = document.createElement('span');
      ph.className = 'select__value';
      ph.dataset.selectPlaceholder = '';
      ph.textContent = instance.placeholder;
      container.appendChild(ph);
      return;
    }

    if (instance.isMultiple) {
      instance.selected.forEach(v => {
        const item = instance.items.find(item => item.dataset.selectValue === v);
        if (!item) return;
        const tag = document.createElement('span');
        tag.className = 'select__tag';
        tag.textContent = item.textContent.trim();
        const rm = document.createElement('span');
        rm.className = 'select__tag-remove';
        rm.innerHTML = this.#ICONS.TAG_REMOVE;
        rm.addEventListener('click', e => { e.stopPropagation(); this._toggleSelect(instance, item); });
        tag.appendChild(rm);
        container.appendChild(tag);
      });
    } else {
      const v = [...instance.selected][0];
      const item = instance.items.find(o => o.dataset.selectValue === v);
      container.textContent = item ? item.textContent.trim() : instance.placeholder;
      if (item) delete container.dataset.selectPlaceholder;
      else container.dataset.selectPlaceholder = '';
    }
  }

  _dispatch(instance) {
    const selectionData = instance.isMultiple
      ? { values: [...instance.selected].map(v => ({ value: v, label: instance.items.find(item => item.dataset.selectValue === v)?.textContent.trim() || v })) }
      : (() => { const v = [...instance.selected][0] ?? null; return v ? { value: v, label: instance.items.find(item => item.dataset.selectValue === v)?.textContent.trim() || v } : { value: null, label: null }; })();
    const detail = {
      ...selectionData,
      id: instance.id,
      isMultiple: instance.isMultiple
    };
    instance.root.dispatchEvent(new CustomEvent('select:change', { bubbles: true, detail }));
    if (typeof instance.root.__selectOnChange === 'function') instance.root.__selectOnChange(detail);
  }

  /* ── Positioning & Flip ─────────────────────────────────────── */
  _position(panel, anchor) {
    const anchorRect = anchor.getBoundingClientRect();
    panel.style.visibility = 'hidden';
    panel.style.display = 'block';
    panel.style.width = `${anchorRect.width}px`;

    let top = anchorRect.bottom + SelectHandler.ANCHOR_OFFSET;
    let left = anchorRect.left;

    panel.style.left = `${left}px`;
    panel.style.top = `${top}px`;

    if (this._isOverflowing(panel, left, top)) {
      const panelRect = panel.getBoundingClientRect();
      const viewportHeight = document.documentElement.clientHeight;
      const viewportWidth = document.documentElement.clientWidth;

      if (panelRect.bottom > viewportHeight - SelectHandler.VIEWPORT_MARGIN) {
        top = anchorRect.top - panelRect.height - SelectHandler.ANCHOR_OFFSET;
        panel.style.top = `${top}px`;
      }
      if (panelRect.right > viewportWidth - SelectHandler.VIEWPORT_MARGIN) {
        left = Math.max(SelectHandler.VIEWPORT_MARGIN, viewportWidth - panelRect.width - SelectHandler.VIEWPORT_MARGIN);
        panel.style.left = `${left}px`;
      }
    }

    panel.style.display = '';
    panel.style.visibility = '';
    return top < anchorRect.top ? 'top' : 'bottom';
  }

  _isOverflowing(panel, left, top) {
    return (left + panel.offsetWidth > document.documentElement.clientWidth - SelectHandler.VIEWPORT_MARGIN) ||
      (top + panel.offsetHeight > document.documentElement.clientHeight - SelectHandler.VIEWPORT_MARGIN);
  }

  _getPortal() {
    if (this.#portal && document.body.contains(this.#portal)) return this.#portal;
    const existing = document.querySelector('.select-portal');
    if (existing) return (this.#portal = existing);
    const portal = document.createElement('div');
    portal.className = 'select-portal';
    document.body.appendChild(portal);
    return (this.#portal = portal);
  }
}

window.SelectHandler = SelectHandler;
