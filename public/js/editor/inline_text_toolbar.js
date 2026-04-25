export class InlineTextToolbar {
  static #URL_PATTERN = /^(https?:\/\/|mailto:|\/)[^\s]+$/i;
  static #COMMANDS = {
    BOLD: 'bold',
    ITALIC: 'italic',
    UNDERLINE: 'underline',
    CREATE_LINK: 'createLink',
    UNLINK: 'unlink',
  };
  static #KEY_MAP = {
    b: InlineTextToolbar.#COMMANDS.BOLD,
    i: InlineTextToolbar.#COMMANDS.ITALIC,
    u: InlineTextToolbar.#COMMANDS.UNDERLINE,
    k: InlineTextToolbar.#COMMANDS.CREATE_LINK,
  };
  static #ANCESTOR_TAGS = {
    [InlineTextToolbar.#COMMANDS.BOLD]: ['STRONG', 'B'],
    [InlineTextToolbar.#COMMANDS.ITALIC]: ['EM', 'I'],
    [InlineTextToolbar.#COMMANDS.UNDERLINE]: ['U'],
    [InlineTextToolbar.#COMMANDS.CREATE_LINK]: ['A'],
  };

  /** @type {object} */
  #bus;
  /** @type {HTMLElement} */
  #canvas;
  /** @type {HTMLElement} */
  #root;
  /** @type {Map<string,HTMLButtonElement>} */
  #buttons = new Map();
  /** @type {HTMLInputElement} */
  #linkInput;
  /** @type {HTMLElement} */
  #linkWrapper;
  /** @type {AbortController} */
  #abortController;
  /** @type {number|null} */
  #rafId = null;
  /** @type {{offset:number, debounceMs:number}} */
  #config;
  /** @type {Range|null} */
  #savedRange = null;
  /**
   * Flag chặn hide() khi link input đang mở.
   * @type {boolean}
  */
  #linkInputOpen = false;

  /**
   * @param {object}      bus             — EditorEventBus instance
   * @param {HTMLElement} canvasContainer — #be-block-list element
   * @param {{ offset?: number, debounceMs?: number }} [config]
   */
  constructor(bus, canvasContainer, config = {}) {
    this.#bus = bus;
    this.#canvas = canvasContainer;
    this.#config = {
      offset: config.offset ?? 8,
      debounceMs: config.debounceMs ?? 80
    };
  }

  /**
   * Build DOM and register all event listeners.
   * Call exactly once, after the host document is ready.
   */
  init() {
    this.#abortController = new AbortController();
    this.#buildDOM();
    this.#bindCanvasEvents();
    this.#bindBusSubscriptions();
  }

  #buildDOM() {
    const { signal } = this.#abortController;

    this.#root = document.createElement('div');
    this.#root.className = 'inline-toolbar';
    this.#root.setAttribute('role', 'toolbar');
    this.#root.setAttribute('aria-label', 'Inline text formatting');
    this.#root.style.display = 'none';

    // ---- Format buttons
    const btnGroup = document.createElement('div');
    btnGroup.className = 'inline-toolbar__btn-group';

    const btnDefs = [
      { command: InlineTextToolbar.#COMMANDS.BOLD, html: '<strong>B</strong>', title: 'Bold (Ctrl+B)' },
      { command: InlineTextToolbar.#COMMANDS.ITALIC, html: '<em>I</em>', title: 'Italic (Ctrl+I)' },
      { command: InlineTextToolbar.#COMMANDS.UNDERLINE, html: '<u>U</u>', title: 'Underline (Ctrl+U)' },
      { command: InlineTextToolbar.#COMMANDS.CREATE_LINK, html: '<i class="fa-solid fa-link"></i>', title: 'Link (Ctrl+K)' },
    ];

    btnDefs.forEach(({ command, html, title }) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'inline-toolbar__btn';
      btn.dataset.command = command;
      btn.title = title;
      btn.innerHTML = html;
      btn.setAttribute('aria-pressed', 'false');

      btn.addEventListener('click', (e) => {
        e.preventDefault();
        if (command === InlineTextToolbar.#COMMANDS.CREATE_LINK) { this.#openLinkInput(); return; }
        this.#dispatchFormat(command, null);
      }, { signal });

      btnGroup.appendChild(btn);
      this.#buttons.set(command, btn);
    });

    // ---- Link input panel
    this.#linkWrapper = document.createElement('div');
    this.#linkWrapper.className = 'inline-toolbar__link-wrapper';
    this.#linkWrapper.style.display = 'none';

    this.#linkInput = document.createElement('input');
    this.#linkInput.type = 'url';
    this.#linkInput.className = 'inline-toolbar__link-input';
    this.#linkInput.placeholder = 'https://example.com';
    this.#linkInput.setAttribute('aria-label', 'URL');

    const btnConfirm = document.createElement('button');
    btnConfirm.type = 'button';
    btnConfirm.className = 'inline-toolbar__btn inline-toolbar__btn--confirm';
    btnConfirm.innerHTML = '&#10003;';
    btnConfirm.title = 'Apply link (Enter)';

    const btnRemove = document.createElement('button');
    btnRemove.type = 'button';
    btnRemove.className = 'inline-toolbar__btn inline-toolbar__btn--remove';
    btnRemove.innerHTML = '&#215;';
    btnRemove.title = 'Remove link';

    this.#linkWrapper.append(this.#linkInput, btnConfirm, btnRemove);
    this.#root.append(btnGroup, this.#linkWrapper);
    document.body.appendChild(this.#root);

    // ---- Link panel interactions
    btnConfirm.addEventListener('click', () => this.#submitLink(), { signal });

    btnRemove.addEventListener('click', () => {
      this.#restoreSelection();
      this.#bus.dispatch('text:format_request', { command: InlineTextToolbar.#COMMANDS.UNLINK, value: null });
      this.#closeLinkInput();
    }, { signal });

    this.#linkInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') { e.preventDefault(); this.#submitLink(); }
      if (e.key === 'Escape') { this.#closeLinkInput(); }
    }, { signal });

    this.#root.addEventListener('mousedown', (e) => {
      e.preventDefault();
    }, { signal });

    this.#linkInput.addEventListener('click', () => {
      this.#linkInput.focus();
    }, { signal });
  }

  // ── Event Binding ────────────────────────────────────────────────────────

  #bindCanvasEvents() {
    const { signal } = this.#abortController;
    const debouncedHandle = this.#debounce(() => this.#handleSelection(), this.#config.debounceMs);

    // mouseup covers mouse-driven selections
    this.#canvas.addEventListener('mouseup', debouncedHandle, { signal });

    // selectionchange covers keyboard-driven selections (Shift+Arrow etc.)
    // Fires on document; #validateRange filters to canvas-owned ranges only.
    document.addEventListener('selectionchange', debouncedHandle, { signal });

    this.#canvas.addEventListener('keydown', (e) => this.#handleKeydown(e), { signal });

    document.addEventListener('mousedown', (e) => {
      if (this.#root.contains(e.target)) return;
      this.#hide();
    }, { signal });

    const onScrollOrResize = () => {
      if (this.#rafId !== null) return;
      this.#rafId = requestAnimationFrame(() => {
        this.#rafId = null;
        if (this.#root.style.display === 'none') return;
        const sel = window.getSelection();
        if (!this.#linkInputOpen) {
          if (!sel || sel.isCollapsed || sel.rangeCount === 0) { this.#hide(); return; }
          const range = sel.getRangeAt(0);
          if (!this.#validateRange(range)) { this.#hide(); return; }
          this.#positionToolbar(range);
        } else if (this.#savedRange) {
          // Reposition dựa trên savedRange khi link input đang mở
          this.#positionToolbar(this.#savedRange);
        }
      });
    };

    document.querySelector('[contenteditable="true"]').addEventListener('click', (e) => {
      console.log("fired")
      if (e.target.tagName === 'A' && (e.ctrlKey || e.metaKey)) {
        window.open(e.target.href, '_blank');
      }
    });

    window.addEventListener('scroll', onScrollOrResize, { signal, passive: true, capture: true });
    window.addEventListener('resize', onScrollOrResize, { signal });
  }

  #bindBusSubscriptions() {
    this.#bus.subscribe('text:format_applied', () => {
      const sel = window.getSelection();
      if (sel && sel.rangeCount > 0) this.#checkActiveState(sel.getRangeAt(0));
    });
  }

  // ── Selection Handling ───────────────────────────────────────────────────

  #handleSelection() {
    if (this.#linkInputOpen) return;

    const sel = window.getSelection();
    if (!sel || sel.isCollapsed || sel.rangeCount === 0) { this.#hide(); return; }

    const range = sel.getRangeAt(0);
    if (!this.#validateRange(range)) { this.#hide(); return; }

    this.#savedRange = range.cloneRange();
    this.#positionToolbar(range);
    this.#checkActiveState(range);
    this.#show();

    // Optional diagnostic event — EditorManager can subscribe for debugging
    this.#bus.dispatch('toolbar:inline_show', { timestamp: Date.now() });
  }

  /**
   * Validate that range is:
   *   1. Inside #canvas
   *   2. Confined to a single [data-be-block-id] (no cross-block span)
   *   3. Not intersecting contenteditable=false / media / interactive elements
   * @param {Range} range
   * @returns {boolean}
   */
  #validateRange(range) {
    if (!this.#canvas.contains(range.commonAncestorContainer)) return false;

    const toEl = (n) => n.nodeType === Node.TEXT_NODE ? n.parentElement : n;

    const startBlock = toEl(range.startContainer)?.closest('[data-be-block-id]');
    const endBlock = toEl(range.endContainer)?.closest('[data-be-block-id]');
    if (!startBlock || startBlock !== endBlock) return false;

    const ancestorEl = toEl(range.commonAncestorContainer);
    if (!ancestorEl) return false;

    const forbidden = ancestorEl.querySelectorAll(
      '[contenteditable="false"], img, input, button, select, textarea'
    );
    for (const el of forbidden) {
      if (range.intersectsNode(el)) return false;
    }

    return true;
  }

  // ── Positioning ──────────────────────────────────────────────────────────

  /**
   * Position toolbar above the selection rect using fixed positioning.
   * Briefly materialises root (hidden via visibility) to measure offsetHeight
   * without triggering a user-visible flash. Viewport-edge clamping runs in a
   * follow-up rAF after paint so getBoundingClientRect returns real values.
   * @param {Range} range
   */
  #positionToolbar(range) {
    const rect = range.getBoundingClientRect();
    if (rect.width === 0 && rect.height === 0) return;

    const wasHidden = this.#root.style.display === 'none';
    if (wasHidden) {
      this.#root.style.visibility = 'hidden';
      this.#root.style.display = 'flex';
    }
    const toolbarH = this.#root.offsetHeight;
    if (wasHidden) {
      this.#root.style.display = 'none';
      this.#root.style.visibility = '';
    }

    const top = rect.top - this.#config.offset - toolbarH;
    const left = rect.left + rect.width / 2;

    this.#root.style.top = `${top}px`;
    this.#root.style.left = `${left}px`;
    this.#root.style.transform = 'translateX(-50%)';

    requestAnimationFrame(() => {
      const r = this.#root.getBoundingClientRect();
      const M = 4; // viewport margin px
      if (r.left < M) {
        this.#root.style.left = `${left - r.left + M}px`;
        this.#root.style.transform = 'none';
      } else if (r.right > window.innerWidth - M) {
        this.#root.style.left = `${left - (r.right - (window.innerWidth - M))}px`;
        this.#root.style.transform = 'none';
      }
    });
  }

  // ── Visibility ───────────────────────────────────────────────────────────

  #show() {
    this.#root.style.display = 'flex';
    // rAF ensures CSS transition fires (display:none → flex needs one frame)
    requestAnimationFrame(() => this.#root.classList.add('is-visible'));
  }

  #hide() {
    this.#root.classList.remove('is-visible');
    this.#root.style.display = 'none';
    this.#closeLinkInput();
    this.#savedRange = null;
  }

  // ── Format Dispatch ──────────────────────────────────────────────────────

  /**
   * Restore selection then push command through bus.
   * execCommand execution lives in EditorManager#handleInlineFormat.
   * @param {string}      command
   * @param {string|null} value
   */
  #dispatchFormat(command, value) {
    this.#restoreSelection();
    this.#bus.dispatch('text:format_request', { command, value: value ?? null });
  }

  #handleKeydown(e) {
    if (!e.ctrlKey && !e.metaKey) return;
    const command = InlineTextToolbar.#KEY_MAP[e.key.toLowerCase()];
    if (!command) return;
    e.preventDefault();

    if (command === InlineTextToolbar.#COMMANDS.CREATE_LINK) {
      const sel = window.getSelection();
      if (!sel || sel.isCollapsed || sel.rangeCount === 0) return;
      const range = sel.getRangeAt(0);
      if (!this.#validateRange(range)) return;
      this.#savedRange = range.cloneRange();
      this.#positionToolbar(range);
      this.#checkActiveState(range);
      this.#show();
      this.#openLinkInput();
      return;
    }

    this.#dispatchFormat(command, null);
  }

  // ── Link Input Flow ──────────────────────────────────────────────────────

  #openLinkInput() {
    const sel = window.getSelection();
    if (sel && sel.rangeCount > 0) {
      const anchor = sel.getRangeAt(0).commonAncestorContainer
        ?.parentElement?.closest('a');
      this.#linkInput.value = anchor ? anchor.href : '';
    }

    this.#linkInputOpen = true;

    this.#linkWrapper.style.display = 'flex';

    this.#linkInput.focus();
  }

  #closeLinkInput() {
    this.#linkInputOpen = false;

    this.#linkWrapper.style.display = 'none';
    this.#linkInput.value = '';
    this.#linkInput.classList.remove('is-invalid');
    this.#linkInput.removeAttribute('aria-invalid');
  }

  #submitLink() {
    const raw = this.#linkInput.value.trim();

    if (!raw) {
      this.#restoreSelection();
      this.#bus.dispatch('text:format_request', { command: InlineTextToolbar.#COMMANDS.UNLINK, value: null });
      this.#closeLinkInput();
      this.#hide();
      return;
    }

    if (!InlineTextToolbar.#URL_PATTERN.test(raw)) {
      this.#linkInput.classList.add('is-invalid');
      this.#linkInput.setAttribute('aria-invalid', 'true');
      this.#linkInput.addEventListener('input', () => {
        this.#linkInput.classList.remove('is-invalid');
        this.#linkInput.removeAttribute('aria-invalid');
      }, { once: true });
      return;
    }

    this.#dispatchFormat(InlineTextToolbar.#COMMANDS.CREATE_LINK, raw);
    this.#closeLinkInput();
    this.#hide();
  }

  // ── Active State ─────────────────────────────────────────────────────────

  /**
   * Walk ancestor elements of the selection up to the block boundary.
   * Toggle .is-active / aria-pressed on buttons whose tag sets match.
   * @param {Range} range
   */
  #checkActiveState(range) {
    const toEl = (n) => n.nodeType === Node.TEXT_NODE ? n.parentElement : n;
    const blockEl = toEl(range.commonAncestorContainer)?.closest('[data-be-block-id]') ?? this.#canvas;

    const presentTags = new Set(
      this.#getAncestors(range.commonAncestorContainer, blockEl).map(n => n.tagName)
    );

    this.#buttons.forEach((btn, command) => {
      const matchTags = InlineTextToolbar.#ANCESTOR_TAGS[command];
      if (!matchTags) return;
      const active = matchTags.some(t => presentTags.has(t));
      btn.classList.toggle('is-active', active);
      btn.setAttribute('aria-pressed', String(active));
    });
  }

  // ── Selection Restoration ────────────────────────────────────────────────

  /**
   * Re-apply #savedRange to window.getSelection().
   * See #savedRange JSDoc for full rationale.
   */
  #restoreSelection() {
    if (!this.#savedRange) return;
    const sel = window.getSelection();
    if (!sel) return;
    sel.removeAllRanges();
    sel.addRange(this.#savedRange);
  }

  #debounce(fn, ms) {
    let t = null;
    return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
  }

  #getAncestors(node, boundary) {
    const out = [];
    let cur = node.nodeType === Node.TEXT_NODE ? node.parentElement : node;
    while (cur && cur !== boundary && boundary.contains(cur)) {
      out.push(cur);
      cur = cur.parentElement;
    }
    return out;
  }

  destroy() {
    this.#abortController?.abort();
    if (this.#rafId !== null) { cancelAnimationFrame(this.#rafId); this.#rafId = null; }
    this.#root?.remove();
    this.#savedRange = null;
    this.#linkInputOpen = false;
  }
}