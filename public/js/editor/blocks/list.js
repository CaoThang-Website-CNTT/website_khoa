import { EditorBlock } from './editor_block.js';

export const ListSchema = {
  version: 1,
  icon: "<i class='fa-solid fa-list-ul'></i>",
  name: 'blocks/list',
  title: 'Danh sách',
  group: 'paragraph',
  groupLabel: 'Văn Bản',
  attributes: {
    values: { default: [{ text: '', children: [] }] },
    ordered: { default: false },
  },
  supports: {
    typography: false,
  }
};

/**
 * ListBlock
 *
 * Data model (recursive):
 *   type ListNode = { text: string, children: ListNode[] }
 *   block.data.values: ListNode[]
 *
 * DOM strategy:
 *   - Render toàn bộ cây thành <ul>/<ol> lồng nhau.
 *   - Mỗi <li> có contenteditable span để nhận input.
 *   - Tab / Shift+Tab → indent / outdent (max depth = 3).
 *   - Enter trên item rỗng → outdent; nếu depth=0 → exit block.
 *   - Structural change (indent/outdent/add/remove) → re-render DOM + dispatch block:updated.
 *   - Text change → chỉ write vào data tree, KHÔNG re-render, KHÔNG dispatch (in-place).
 */
export class ListBlock extends EditorBlock {

  /** @type {HTMLElement} Root list element */
  #rootEl = null;

  /**
   * Trả về path dạng mảng index từ root tới node chứa element.
   * Dùng data-path="0.children.1.children.0" được gắn lên mỗi <li>.
   * @param {HTMLElement} liEl
   * @returns {number[]}
   */
  #pathOf(liEl) {
    return (liEl.dataset.path || '0').split('.').map(Number);
  }

  /**
   * Đọc node tại path từ data.values.
   * path = [rootIndex, childIndex, grandChildIndex, ...]
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

  /**
   * Depth của path (root = 0).
   * @param {number[]} path
   * @returns {number}
   */
  #depth(path) {
    return path.length - 1;
  }

  /**
   * Tìm <li> element kế tiếp theo thứ tự DOM (bất kể depth).
   * @param {HTMLElement} liEl
   * @returns {HTMLElement|null}
   */
  #nextLi(liEl) {
    const all = Array.from(this.dom.querySelectorAll('li[data-path]'));
    const idx = all.indexOf(liEl);
    return all[idx + 1] || null;
  }

  /**
   * Tìm <li> element trước đó.
   * @param {HTMLElement} liEl
   * @returns {HTMLElement|null}
   */
  #prevLi(liEl) {
    const all = Array.from(this.dom.querySelectorAll('li[data-path]'));
    const idx = all.indexOf(liEl);
    return all[idx - 1] || null;
  }

  /**
   * Focus vào span của <li> tại vị trí end hoặc start.
   * @param {HTMLElement} liEl
   * @param {'start'|'end'} position
   */
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

  // ─── Render ───────────────────────────────────────────────────

  render() {
    const tag = this.data.ordered ? 'ol' : 'ul';
    const rootEl = document.createElement(tag);
    rootEl.className = `be-preview-list be-preview-list--${this.data.ordered ? 'ol' : 'ul'}`;
    rootEl.contentEditable = 'false';

    this.dom = rootEl;
    this.#rootEl = rootEl;

    this.#renderTree(this.data.values, rootEl, []);

    return rootEl;
  }

  /**
   * Render đệ quy cây ListNode[] vào một <ul>/<ol> element.
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
        subList.className = 'be-list-nested';
        li.appendChild(subList);
        this.#renderTree(node.children, subList, path);
      }
    });
  }

  /**
   * Tạo <li> kèm <span contenteditable> và wire events.
   * @param {ListNode} node
   * @param {number[]} path
   * @returns {HTMLElement}
   */
  #createLi(node, path) {
    const li = document.createElement('li');
    li.className = 'be-list-item';
    li.dataset.path = path.join('.');

    const span = document.createElement('span');
    span.className = 'be-list-item-text be-editable';
    span.contentEditable = 'true';
    span.spellcheck = false;
    span.dataset.placeholder = 'Nhập nội dung...';
    span.textContent = node.text || '';

    li.appendChild(span);

    // ── Event: input → sync text vào data tree (in-place, no re-render) ──
    span.addEventListener('input', () => {
      const { node: n } = this.#nodeAt(path);
      if (n) n.text = span.textContent;
      // Không dispatch block:updated — user đang typing
    });

    // ── Event: paste → strip HTML ──
    span.addEventListener('paste', (e) => {
      e.preventDefault();
      const text = (e.originalEvent || e).clipboardData.getData('text/plain');
      document.execCommand('insertText', false, text);
    });

    // ── Event: keydown → Enter / Tab / Backspace ──
    span.addEventListener('keydown', (e) => this.#handleKeydown(e, li, path));

    return li;
  }

  // ─── Keyboard logic ───────────────────────────────────────────

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
      if (span && span.textContent.trim() === '') {
        e.preventDefault();
        this.#handleBackspaceEmpty(liEl, path);
      }
    }
  }

  /**
   * Enter: thêm item mới sau item hiện tại cùng depth.
   * Nếu item rỗng và đang ở depth > 0 → outdent.
   * Nếu item rỗng và depth = 0 → exit block.
   */
  #handleEnter(liEl, path) {
    const { node } = this.#nodeAt(path);
    const text = node?.text?.trim() ?? '';
    const depth = this.#depth(path);

    if (text === '') {
      if (depth > 0) {
        this.#handleOutdent(liEl, path);
      } else {
        this.#exitBlock(liEl, path);
      }
      return;
    }

    // Thêm item mới sau path hiện tại, cùng level
    const { parent } = this.#nodeAt(path);
    const localIdx = path[path.length - 1];
    const newNode = { text: '', children: [] };
    parent.splice(localIdx + 1, 0, newNode);

    this.#rerender(() => {
      // Focus vào item mới
      const newPath = [...path.slice(0, -1), localIdx + 1];
      const newLi = this.dom.querySelector(`li[data-path="${newPath.join('.')}"]`);
      if (newLi) this.#focusLi(newLi, 'start');
    });
  }

  /**
   * Tab: indent item hiện tại thành con của item trước đó.
   * Giới hạn depth = 3.
   */
  #handleIndent(liEl, path) {
    const depth = this.#depth(path);
    if (depth >= 3) return; // max depth

    const localIdx = path[path.length - 1];
    if (localIdx === 0) return; // Không có item trước để làm parent

    const { parent, node } = this.#nodeAt(path);

    // Lấy item trước đó cùng level
    const prevSibling = parent[localIdx - 1];

    // Di chuyển node này thành con cuối của prevSibling
    parent.splice(localIdx, 1);
    prevSibling.children.push(node);

    const newPath = [...path.slice(0, -1), localIdx - 1, prevSibling.children.length - 1];

    this.#rerender(() => {
      const newLi = this.dom.querySelector(`li[data-path="${newPath.join('.')}"]`);
      if (newLi) this.#focusLi(newLi, 'end');
    });
  }

  /**
   * Shift+Tab: outdent item — đưa lên level cha, chèn sau parent item.
   */
  #handleOutdent(liEl, path) {
    const depth = this.#depth(path);
    if (depth === 0) return; // Đã ở root, không outdent được

    const localIdx = path[path.length - 1];
    const { parent: currentList, node } = this.#nodeAt(path);

    // Lấy grandparent list và parent node
    const parentPath = path.slice(0, -1);
    const { parent: grandParentList, localIndex: parentLocalIdx } = this.#nodeAt(parentPath);

    // Xóa node khỏi current list
    currentList.splice(localIdx, 1);

    // Chèn sau parent trong grandparent list
    grandParentList.splice(parentLocalIdx + 1, 0, node);

    // Các siblings còn lại trong currentList (sau localIdx) → trở thành children của node
    const orphans = currentList.splice(localIdx);
    node.children.push(...orphans);

    const newPath = [...parentPath.slice(0, -1), parentLocalIdx + 1];

    this.#rerender(() => {
      const newLi = this.dom.querySelector(`li[data-path="${newPath.join('.')}"]`);
      if (newLi) this.#focusLi(newLi, 'end');
    });
  }

  /**
   * Backspace trên item rỗng: xóa item, focus item trước.
   */
  #handleBackspaceEmpty(liEl, path) {
    const depth = this.#depth(path);
    const localIdx = path[path.length - 1];

    if (depth === 0 && localIdx === 0 && this.data.values.length === 1) {
      // List chỉ còn 1 item rỗng → exit block
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

  /**
   * Thoát block: xóa item rỗng cuối, dispatch add paragraph.
   */
  #exitBlock(liEl, path) {
    const { parent } = this.#nodeAt(path);
    const localIdx = path[path.length - 1];
    parent.splice(localIdx, 1);

    if (this.bus) {
      this.bus.dispatch('block:updated', { block: this });
      this.bus.dispatch('block:add_request', { type: 'blocks/paragraph' });
    }
  }

  // ─── Re-render helper ─────────────────────────────────────────

  /**
   * Re-render toàn bộ DOM của block, sau đó dispatch block:updated.
   * Dùng cho structural changes (indent/outdent/add/remove item).
   * @param {Function} [afterRender] — callback sau khi DOM sẵn sàng
   */
  #rerender(afterRender) {
    const tag = this.data.ordered ? 'ol' : 'ul';

    // Thay đổi tag nếu cần (ordered switch)
    if (this.dom.tagName.toLowerCase() !== tag) {
      const newRoot = document.createElement(tag);
      newRoot.className = this.dom.className;
      this.dom.parentElement?.replaceChild(newRoot, this.dom);
      this.dom = newRoot;
      this.#rootEl = newRoot;
    }

    this.dom.innerHTML = '';
    this.#renderTree(this.data.values, this.dom, []);

    if (this.bus) {
      this.bus.dispatch('block:updated', { block: this });
    }

    // Defer focus sau khi DOM settled
    if (afterRender) requestAnimationFrame(afterRender);
  }

  // ─── Inspector controls ───────────────────────────────────────

  renderInspectorControls(data, { onUpdate }) {
    const wrap = document.createElement('div');
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

  /**
   * Override focus: tìm item đầu/cuối để focus.
   */
  focus(bus, position = 'end') {
    if (!this.dom) return;

    const all = Array.from(this.dom.querySelectorAll('li[data-path]'));
    const target = position === 'end' ? all[all.length - 1] : all[0];
    if (target) this.#focusLi(target, position);

    bus.dispatch('block:selected', { blockId: this.id });
  }
}