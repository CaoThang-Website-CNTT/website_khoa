import { EditorToolbar } from './editor_toolbar.js';

export class InlineToolbar extends EditorToolbar {
  static #URL_PATTERN = /^(https?:\/\/|mailto:|\/)[^\s]+$/i;

  static COMMANDS = {
    BOLD: 'bold',
    ITALIC: 'italic',
    UNDERLINE: 'underline',
    CREATE_LINK: 'link',
    UNLINK: 'unlink',
  };

  static SHORTCUTS = [
    { command: InlineToolbar.COMMANDS.BOLD, key: 'b', modifiers: ['ctrl'] },
    { command: InlineToolbar.COMMANDS.ITALIC, key: 'i', modifiers: ['ctrl'] },
    { command: InlineToolbar.COMMANDS.UNDERLINE, key: 'u', modifiers: ['ctrl'] },
    { command: InlineToolbar.COMMANDS.CREATE_LINK, key: 'k', modifiers: ['ctrl'] },
  ];

  /** @type {Map<string, string>} */
  #shortcutMap = new Map();

  /** @type {Map<string, HTMLButtonElement>} */
  #buttons = new Map();

  /** @type {HTMLInputElement} */
  #linkInput;

  /** @type {HTMLElement} */
  #linkWrapper;

  /** @type {number|null} */
  #rafId = null;

  /** @type {{ offset: number, debounceMs: number }} */
  #config;

  /**
   * Range được clone lại ngay khi toolbar hiện ra.
   * @type {Range|null}
   */
  #savedRange = null;

  /**
   * blockId của block đang chứa selection hiện tại.
   * Gửi kèm theo mọi format_request để Manager biết block nào cần update.
   * @type {string|null}
   */
  #activeBlockId = null;

  /**
   * Flag chặn hide() khi link input panel đang mở.
   * @type {boolean}
   */
  #linkInputOpen = false;

  /**
   * @type {ContextStore}
   */
  #store;

  /**
   * @param {EditorEventBus} bus
   * @param {HTMLElement}    canvas   - #be-block-list
   * @param {{ offset?: number, debounceMs?: number }} [config]
   */
  constructor(bus, canvas, store, config = {}) {
    super(bus, canvas);

    this.#store = store;
    this.#config = {
      offset: config.offset ?? 8,
      debounceMs: config.debounceMs ?? 80,
    };

    this.#buildShortcutMap();
    this.#buildDOM();
    this.#bindCanvasEvents();
    this.#bindBusSubscriptions();
  }

  /**
   * @override
   */
  destroy() {
    if (this.#rafId !== null) {
      cancelAnimationFrame(this.#rafId);
      this.#rafId = null;
    }
    this.#savedRange = null;
    this.#linkInputOpen = false;
    super.destroy(); // abort controller + root.remove()
  }

  #buildShortcutMap() {
    for (const sc of InlineToolbar.SHORTCUTS) {
      const mods = [];
      if (sc.modifiers.includes('ctrl')) mods.push('ctrl');
      if (sc.modifiers.includes('shift')) mods.push('shift');
      if (sc.modifiers.includes('alt')) mods.push('alt');

      // Key lookup chuẩn hóa: "ctrl+shift+b"
      const lookupKey = `${mods.join('+')}${mods.length ? '+' : ''}${sc.key.toLowerCase()}`;
      this.#shortcutMap.set(lookupKey, sc.command);
    }
  }

  #buildDOM() {
    const { signal } = this.abortController;

    this.root = document.createElement('div');
    this.root.className = 'inline-toolbar';
    this.root.setAttribute('role', 'toolbar');
    this.root.setAttribute('aria-label', 'Inline text formatting');
    this.root.style.display = 'none';

    // ── Format buttons ────────────────────────────────────────────────────
    const btnGroup = document.createElement('div');
    btnGroup.className = 'inline-toolbar__btn-group';

    const btnDefs = [
      { command: InlineToolbar.COMMANDS.BOLD, html: '<strong>B</strong>', title: 'Bold (Ctrl+B)' },
      { command: InlineToolbar.COMMANDS.ITALIC, html: '<em>I</em>', title: 'Italic (Ctrl+I)' },
      { command: InlineToolbar.COMMANDS.UNDERLINE, html: '<u>U</u>', title: 'Underline (Ctrl+U)' },
      { command: InlineToolbar.COMMANDS.CREATE_LINK, html: '<i class="fa-solid fa-link"></i>', title: 'Link (Ctrl+K)' },
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
        if (command === InlineToolbar.COMMANDS.CREATE_LINK) {
          this.#openLinkInput();
          return;
        }
        // Dispatch và GIỮ TOOLBAR MỞ - không gọi #hide()
        this.#dispatchFormat(command, null);
      }, { signal });

      btnGroup.appendChild(btn);
      this.#buttons.set(command, btn);
    });

    // Link input panel
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
    btnConfirm.innerHTML = '<i class="fa-solid fa-check"></i>';
    btnConfirm.title = 'Apply link (Enter)';

    const btnRemove = document.createElement('button');
    btnRemove.type = 'button';
    btnRemove.className = 'inline-toolbar__btn inline-toolbar__btn--remove';
    btnRemove.innerHTML = '<i class="fa-solid fa-xmark"></i>';
    btnRemove.title = 'Remove link';

    this.#linkWrapper.append(this.#linkInput, btnConfirm, btnRemove);
    this.root.append(btnGroup, this.#linkWrapper);
    document.body.appendChild(this.root);

    // ── Link panel interactions ───────────────────────────────────────────
    btnConfirm.addEventListener('click', () => this.#submitLink(), { signal });

    btnRemove.addEventListener('click', () => {
      this.#dispatchUnlink();
      this.#closeLinkInput();
      this.#hide();
    }, { signal });

    this.#linkInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') { e.preventDefault(); this.#submitLink(); }
      if (e.key === 'Escape') { this.#closeLinkInput(); }
    }, { signal });

    // Chặn mousedown trên toolbar để không làm mất selection
    this.root.addEventListener('mousedown', (e) => {
      e.preventDefault();
    }, { signal });

    this.#linkInput.addEventListener('click', () => {
      this.#linkInput.focus();
    }, { signal });
  }

  #bindCanvasEvents() {
    const { signal } = this.abortController;
    const debouncedHandle = this.#debounce(() => this.#handleSelection(), this.#config.debounceMs);

    // Mouse-driven selection
    this.canvas.addEventListener('mouseup', debouncedHandle, { signal });

    // Keyboard-driven selection (Shift+Arrow, etc.)
    // Gắn trên document để bắt cả khi focus đang ở trong block con
    document.addEventListener('selectionchange', debouncedHandle, { signal });

    this.canvas.addEventListener('keydown', (e) => this.#handleKeydown(e), { signal });

    // Click ra ngoài → ẩn toolbar
    document.addEventListener('mousedown', (e) => {
      if (this.root?.contains(e.target)) return;
      this.#hide();
    }, { signal });

    // Ctrl+Click vào link → mở tab mới
    this.canvas.addEventListener('click', (e) => {
      if (e.target.tagName === 'A' && (e.ctrlKey || e.metaKey)) {
        e.preventDefault();
        window.open(e.target.href, '_blank', 'noopener,noreferrer');
      }
    }, { signal });

    // Scroll/resize → reposition
    const onScrollOrResize = () => {
      if (this.#rafId !== null) return;
      this.#rafId = requestAnimationFrame(() => {
        this.#rafId = null;
        if (this.root?.style.display === 'none') return;

        if (!this.#linkInputOpen) {
          if (this.#store.type !== 'text' || !this.#savedRange) {
            this.#hide();
            return;
          }
          this.#positionToolbar(this.#savedRange);
        } else if (this.#savedRange) {
          this.#positionToolbar(this.#savedRange);
        }
      });
    };

    window.addEventListener('scroll', onScrollOrResize, { signal, passive: true, capture: true });
    window.addEventListener('resize', onScrollOrResize, { signal });
  }

  #bindBusSubscriptions() {
    // Manager gửi lại marks hiện tại sau khi InlineFormatter chạy xong
    this.bus.subscribe('inline:marks_updated', ({ activeMarks }) => {
      this.#syncButtonStates(activeMarks);
    });

    this.bus.subscribe('inline:range_restored', ({ blockId, range }) => {
      if (!range || !blockId) return;
      this.#savedRange = range.cloneRange();
      this.#activeBlockId = blockId;
      if (this.root?.style.display !== 'none') {
        this.#positionToolbar(this.#savedRange);
      }
    });
  }

  #handleSelection() {
    if (this.#linkInputOpen) return;

    if (this.#store.type !== 'text') {
      this.#hide();
      return;
    }

    const sel = this.#store.selection;

    if (!sel.range || !sel.blockId) {
      this.#hide();
      return;
    }

    this.#savedRange = sel.range;
    this.#activeBlockId = sel.blockId;

    this.#positionToolbar(this.#savedRange);

    this.bus.dispatch('inline:selection_changed', {
      blockId: this.#activeBlockId,
      range: this.#savedRange,
    });

    this.#showToolbar();
    this.bus.dispatch('toolbar:inline_show', { timestamp: Date.now() });
  }

  /**
   * Đặt toolbar ngay trên selection rect, giữa theo chiều ngang.
   * Briefly materialize để đo offsetHeight, sau đó clamp vào viewport.
   * @param {Range} range
   */
  #positionToolbar(range) {
    const rect = range.getBoundingClientRect();
    if (rect.width === 0 && rect.height === 0) return;

    const wasHidden = this.root.style.display === 'none';
    if (wasHidden) {
      this.root.style.visibility = 'hidden';
      this.root.style.display = 'flex';
    }
    const toolbarH = this.root.offsetHeight;
    if (wasHidden) {
      this.root.style.display = 'none';
      this.root.style.visibility = '';
    }

    const top = rect.top - this.#config.offset - toolbarH;
    const left = rect.left + rect.width / 2;

    this.root.style.top = `${top}px`;
    this.root.style.left = `${left}px`;
    this.root.style.transform = 'translateX(-50%)';

    requestAnimationFrame(() => {
      const r = this.root.getBoundingClientRect();
      const M = 4;
      if (r.left < M) {
        this.root.style.left = `${left - r.left + M}px`;
        this.root.style.transform = 'none';
      } else if (r.right > window.innerWidth - M) {
        this.root.style.left = `${left - (r.right - (window.innerWidth - M))}px`;
        this.root.style.transform = 'none';
      }
    });
  }

  #showToolbar() {
    this.root.style.display = 'flex';
    requestAnimationFrame(() => this.root.classList.add('is-visible'));
  }

  #hide() {
    this.root.classList.remove('is-visible');
    this.root.style.display = 'none';
    this.#closeLinkInput();
    this.#savedRange = null;
    this.#activeBlockId = null;
  }

  /**
   * Gửi format request lên bus kèm range offsets.
   * Manager nhận → gọi InlineFormatter → update DOM → gửi lại 'inline:marks_updated'.
   *
   * @param {string}      command
   * @param {string|null} value
   */
  #dispatchFormat(command, value) {
    if (!this.#savedRange || !this.#activeBlockId) return;

    this.bus.dispatch('inline:format_request', {
      command,
      value: value ?? null,
      blockId: this.#activeBlockId,
      range: this.#savedRange, // Manager sẽ gọi InlineFormatter.getRangeOffsets()
    });
  }

  #dispatchUnlink() {
    if (!this.#savedRange || !this.#activeBlockId) return;

    this.bus.dispatch('inline:unlink_request', {
      blockId: this.#activeBlockId,
      range: this.#savedRange,
    });
  }

  #handleKeydown(e) {
    // Chuẩn hóa modifiers
    const ctrl = e.ctrlKey || e.metaKey;
    const shift = e.shiftKey;
    const alt = e.altKey;
    const key = e.key.toLowerCase();

    const mods = [];
    if (ctrl) mods.push('ctrl');
    if (shift) mods.push('shift');
    if (alt) mods.push('alt');

    const lookupKey = `${mods.join('+')}${mods.length ? '+' : ''}${key}`;
    const command = this.#shortcutMap.get(lookupKey);

    if (!command) return;
    e.preventDefault();

    if (command === InlineToolbar.COMMANDS.CREATE_LINK) {
      if (this.#store.type !== 'text') return;

      const sel = this.#store.selection;
      if (!sel.range || !sel.blockId) return;

      this.#savedRange = sel.range;
      this.#activeBlockId = sel.blockId;

      this.#positionToolbar(this.#savedRange);
      this.#showToolbar();
      this.#openLinkInput();
      return;
    }

    this.#dispatchFormat(command, null);
  }

  // Link input
  #openLinkInput() {
    // Prefill nếu cursor đang trong link
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
      // Input trống → unlink
      this.#dispatchUnlink();
      this.#closeLinkInput();
      this.#hide();
      return;
    }

    if (!InlineToolbar.#URL_PATTERN.test(raw)) {
      this.#linkInput.classList.add('is-invalid');
      this.#linkInput.setAttribute('aria-invalid', 'true');
      this.#linkInput.addEventListener('input', () => {
        this.#linkInput.classList.remove('is-invalid');
        this.#linkInput.removeAttribute('aria-invalid');
      }, { once: true });
      return;
    }

    this.bus.dispatch('inline:link_request', {
      href: raw,
      blockId: this.#activeBlockId,
      range: this.#savedRange,
    });

    this.#closeLinkInput();
    this.#hide();
  }

  /**
   * Cập nhật visual trạng thái is-active / aria-pressed cho từng nút.
   * Được gọi khi bus emit 'inline:marks_updated'.
   *
   * @param {Set<string>} activeMarks - từ InlineFormatter.getActiveMarks()
   */
  #syncButtonStates(activeMarks) {
    this.#buttons.forEach((btn, command) => {
      // 'link' command map sang mark 'link'
      const mark = command === InlineToolbar.COMMANDS.CREATE_LINK ? 'link' : command;
      const active = activeMarks.has(mark);
      btn.classList.toggle('is-active', active);
      btn.setAttribute('aria-pressed', String(active));
    });
  }

  #debounce(fn, ms) {
    let t = null;
    return (...args) => {
      clearTimeout(t);
      t = setTimeout(() => fn(...args), ms);
    };
  }
}
