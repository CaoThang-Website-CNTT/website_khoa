import { EditorBlock } from './editor_block.js';
import { BlockSerializer } from '../block_serializer.js';

export const ListSchema = {
  version: 1,
  icon: "<i class='fa-solid fa-list-ul'></i>",
  name: 'blocks/list',
  title: 'Danh sách',
  group: 'paragraph',
  groupLabel: 'Văn Bản',
  attributes: {
    values: { default: [{ content: '', children: [] }] },
    ordered: { default: false },
  },
  supports: {
    typography: false,
  }
};

export class ListBlock extends EditorBlock {

  /** @type {HTMLElement} Root list element */
  #rootEl = null;

  /**
   * Trả về path dạng mảng index từ root tới node.
   * @param {HTMLElement} liEl
   * @returns {number[]}
   */
  #pathOf(liEl) {
    return (liEl.dataset.path || '0').split('.').map(Number);
  }

  /**
   * Đọc node tại path từ data.values.
   * @param {number[]} path
   * @returns {{ node: ListNode, parent: ListNode[]|null, localIndex: number }}
   */
  #nodeAt(path) {
    let list = this.data.values;
    let node = null;
    let parent = null;

    for (let i = 0; i < path.length; i++) {
      parent = list;
      node = list[path[i]];
      if (i < path.length - 1) list = node.children;
    }

    return { node, parent, localIndex: path[path.length - 1] };
  }

  /** @param {number[]} path @returns {number} */
  #depth(path) { return path.length - 1; }

  /** @param {HTMLElement} liEl @returns {HTMLElement|null} */
  #nextLi(liEl) {
    const all = Array.from(this.dom.querySelectorAll('li[data-path]'));
    return all[all.indexOf(liEl) + 1] || null;
  }

  /** @param {HTMLElement} liEl @returns {HTMLElement|null} */
  #prevLi(liEl) {
    const all = Array.from(this.dom.querySelectorAll('li[data-path]'));
    return all[all.indexOf(liEl) - 1] || null;
  }

  /** @param {HTMLElement} liEl @param {'start'|'end'} position */
  #focusLi(liEl, position = 'end') {
    const span = liEl.querySelector(':scope > span.be-list-item-text');
    if (!span) return;
    span.focus();

    const range = document.createRange();
    const sel = window.getSelection();
    range.selectNodeContents(span);
    range.collapse(position === 'start');
    sel.removeAllRanges();
    sel.addRange(range);
  }

  // ─── Render ───────────────────────────────────────────────────────────────

  render() {
    const tag = this.data.ordered ? 'ol' : 'ul';
    const rootEl = document.createElement(tag);
    rootEl.className = `be-list`;
    rootEl.contentEditable = 'false';

    this.dom = rootEl;
    this.#rootEl = rootEl;

    // [ADDED] — migrate legacy nodes: node.text (string) → node.content (string)
    // Nếu data được load từ DB cũ vẫn còn field 'text', đổi sang 'content'
    this.#migrateNodes(this.data.values);

    this.#renderTree(this.data.values, rootEl, []);

    return rootEl;
  }

  /**
   * Migrate legacy node shape { text, children } → { content, children }.
   * Safe to call multiple times (idempotent).
   * @param {any[]} nodes
   */
  #migrateNodes(nodes) {
    if (!Array.isArray(nodes)) return;
    for (const node of nodes) {
      if ('text' in node && !('content' in node)) {
        node.content = node.text; // giữ nguyên giá trị (string)
        delete node.text;
      }
      if (Array.isArray(node.children)) {
        this.#migrateNodes(node.children);
      }
    }
  }

  /**
   * Render đệ quy cây ListNode[] vào một <ul>/<ol>.
   * @param {ListNode[]} nodes
   * @param {HTMLElement} listEl
   * @param {number[]} basePath
   */
  #renderTree(nodes, listEl, basePath) {
    nodes.forEach((node, idx) => {
      const path = [...basePath, idx];
      const li = this.#createLi(node, path);
      listEl.appendChild(li);

      if (node.children && node.children.length > 0) {
        const subTag = this.data.ordered ? 'ol' : 'ul';
        const subList = document.createElement(subTag);
        subList.className = 'be-list';
        li.appendChild(subList);
        this.#renderTree(node.children, subList, path);
      }
    });
  }

  /**
   * Tạo <li> kèm <span contenteditable>.
   * @param {ListNode} node
   * @param {number[]} path
   * @returns {HTMLLIElement}
   */
  #createLi(node, path) {
    const li = document.createElement('li');
    li.dataset.path = path.join('.');

    const span = document.createElement('span');
    span.className = 'be-editable';
    span.contentEditable = 'true';
    span.spellcheck = false;
    span.dataset.placeholder = 'Nhập nội dung...';
    span.dataset.beEditable = 'list-item'

    // Hydrate: segment[] hoặc plain string → HTML
    span.innerHTML = BlockSerializer.toHTML({ data: { content: node.content } });

    li.appendChild(span);

    // ── Input: sync content sang node trong data tree ──────────────────────
    span.addEventListener('input', () => {
      const { node: n } = this.#nodeAt(path);
      if (!n) return;

      // Parse innerHTML → segment[] và lưu vào node.content
      const html = span.innerHTML?.trim() ?? '';
      n.content = html
        ? BlockSerializer.tokensToRichText(BlockSerializer.parseHTML(html))
        : [];
    });

    // ── Paste: strip HTML ──────────────────────────────────────────────────
    span.addEventListener('paste', (e) => {
      e.preventDefault();
      const text = (e.originalEvent || e).clipboardData.getData('text/plain');
      this.paste(this.esc(text));
    });

    // ── Keyboard ───────────────────────────────────────────────────────────
    span.addEventListener('keydown', (e) => this.#handleKeydown(e, li, path));

    return li;
  }

  // ─── Keyboard logic ───────────────────────────────────────────────────────

  /**
   * @param {KeyboardEvent} e
   * @param {HTMLElement} liEl
   * @param {number[]} path
   */
  #handleKeydown(e, liEl, path) {
    if (e.key === 'Enter') {
      e.preventDefault();
      this.#handleEnter(liEl, path);
    } else if (e.key === 'Tab' && !e.shiftKey) {
      e.preventDefault();
      this.#handleIndent(liEl, path);
    } else if (e.key === 'Tab' && e.shiftKey) {
      e.preventDefault();
      this.#handleOutdent(liEl, path);
    } else if (e.key === 'Backspace') {
      const span = liEl.querySelector(':scope > span.be-list-item-text');
      if (span && span.innerHTML.trim() === '') {
        e.preventDefault();
        this.#handleBackspaceEmpty(liEl, path);
      }
    }
  }

  #handleEnter(liEl, path) {
    const { node } = this.#nodeAt(path);
    const isEmpty = !node?.content || (Array.isArray(node.content) ? node.content.length === 0 : node.content.trim() === '');
    const depth = this.#depth(path);

    if (isEmpty) {
      if (depth > 0) {
        this.#handleOutdent(liEl, path);
      } else {
        this.#exitBlock(liEl, path);
      }
      return;
    }

    const { parent } = this.#nodeAt(path);
    const localIdx = path[path.length - 1];
    const newNode = { content: [], children: [] };
    parent.splice(localIdx + 1, 0, newNode);

    this.#rerender(() => {
      const newPath = [...path.slice(0, -1), localIdx + 1];
      const newLi = this.dom.querySelector(`li[data-path="${newPath.join('.')}"]`);
      if (newLi) this.#focusLi(newLi, 'start');
    });
  }

  #handleIndent(liEl, path) {
    const depth = this.#depth(path);
    if (depth >= 3) return;

    const localIdx = path[path.length - 1];
    if (localIdx === 0) return;

    const { parent, node } = this.#nodeAt(path);
    const prevSibling = parent[localIdx - 1];

    parent.splice(localIdx, 1);
    prevSibling.children.push(node);

    const newPath = [...path.slice(0, -1), localIdx - 1, prevSibling.children.length - 1];

    this.#rerender(() => {
      const newLi = this.dom.querySelector(`li[data-path="${newPath.join('.')}"]`);
      if (newLi) this.#focusLi(newLi, 'end');
    });
  }

  #handleOutdent(liEl, path) {
    const depth = this.#depth(path);
    if (depth === 0) return;

    const localIdx = path[path.length - 1];
    const { parent: currentList, node } = this.#nodeAt(path);

    const parentPath = path.slice(0, -1);
    const { parent: grandParentList, localIndex: parentLocalIdx } = this.#nodeAt(parentPath);

    currentList.splice(localIdx, 1);
    grandParentList.splice(parentLocalIdx + 1, 0, node);

    const orphans = currentList.splice(localIdx);
    node.children.push(...orphans);

    const newPath = [...parentPath.slice(0, -1), parentLocalIdx + 1];

    this.#rerender(() => {
      const newLi = this.dom.querySelector(`li[data-path="${newPath.join('.')}"]`);
      if (newLi) this.#focusLi(newLi, 'end');
    });
  }

  #handleBackspaceEmpty(liEl, path) {
    const depth = this.#depth(path);
    const localIdx = path[path.length - 1];

    if (depth === 0 && localIdx === 0 && this.data.values.length === 1) {
      this.#exitBlock(liEl, path);
      return;
    }

    const prevLi = this.#prevLi(liEl);
    const { parent } = this.#nodeAt(path);
    parent.splice(localIdx, 1);

    this.#rerender(() => {
      if (prevLi) {
        const prevPath = prevLi.dataset.path;
        const restored = this.dom.querySelector(`li[data-path="${prevPath}"]`);
        if (restored) this.#focusLi(restored, 'end');
      }
    });
  }

  #exitBlock(liEl, path) {
    const { parent } = this.#nodeAt(path);
    parent.splice(path[path.length - 1], 1);

    if (this.bus) {
      this.bus.dispatch('block:updated', { block: this });
      this.bus.dispatch('block:add_request', { type: 'blocks/paragraph' });
    }
  }

  // ─── Re-render helper ─────────────────────────────────────────────────────

  #rerender(afterRender) {
    const tag = this.data.ordered ? 'ol' : 'ul';

    if (this.dom.tagName.toLowerCase() !== tag) {
      const newRoot = document.createElement(tag);
      newRoot.className = this.dom.className;
      this.dom.parentElement?.replaceChild(newRoot, this.dom);
      this.dom = newRoot;
      this.#rootEl = newRoot;
    }

    this.dom.innerHTML = '';
    this.#renderTree(this.data.values, this.dom, []);

    if (this.bus) this.bus.dispatch('block:updated', { block: this });

    if (afterRender) requestAnimationFrame(afterRender);
  }

  // ─── Inspector controls ───────────────────────────────────────────────────

  renderInspectorControls(data, { onUpdate }) {
    const wrap = document.createElement('div');
    wrap.className = "field-group";

    wrap.innerHTML = `
      <div class="be-settings-property-section">
        <span class="be-settings-property__label">Kiểu danh sách</span>
        <div class="be-settings-level-group">
          <button type="button" class="btn be-list-type-btn ${!data.ordered ? 'active' : ''}" data-variant="outline" data-ordered="false">
            <i class="fa-solid fa-list-ul"></i> Dấu chấm
          </button>
          <button type="button" class="btn be-list-type-btn ${data.ordered ? 'active' : ''}" data-variant="outline" data-ordered="true">
            <i class="fa-solid fa-list-ol"></i> Đánh số
          </button>
        </div>
      </div>
    `;

    wrap.querySelectorAll('.be-list-type-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const ordered = btn.dataset.ordered === 'true';
        onUpdate({ ordered });
        wrap.querySelectorAll('.be-list-type-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
      });
    });

    return wrap;
  }

  focus(bus, position = 'end') {
    if (!this.dom) return;

    const all = Array.from(this.dom.querySelectorAll('li[data-path]'));
    const target = position === 'end' ? all[all.length - 1] : all[0];
    if (target) this.#focusLi(target, position);

    bus.dispatch('block:selected', { blockId: this.id });
  }
}