import { EditorBlock } from './editor_block.js';
import { BlockSerializer } from '../block_serializer_v2.js';
import { RichTextParser } from '../rich_text_parser.js';

export const ListSchema = {
  version: 1,
  icon: "<i class='fa-solid fa-list-ul'></i>",
  type: 'blocks/list',
  title: 'Danh sách',
  group: 'paragraph',
  groupLabel: 'Văn Bản',
  meta: {
    style: { default: 'bullet' },
    items: { default: [{ rich_text: [], children: [] }] },
  },
  supports: { typography: false },
};

export class ListBlock extends EditorBlock {

  /** @type {HTMLElement|null} */
  #rootEl = null;

  // ─── Path helpers ─────────────────────────────────────────────────────────

  /** @param {HTMLElement} liEl @returns {number[]} */
  #pathOf(liEl) {
    return (liEl.dataset.path || '0').split('.').map(Number);
  }

  /**
   * @param {number[]} path
   * @returns {{ node: object, parent: object[]|null, localIndex: number }}
   */
  #nodeAt(path) {
    let list = this.data.meta.items;
    let node = null, parent = null;

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
  }

  // ─── Render ───────────────────────────────────────────────────────────────

  render() {
    const tag = this.data.meta.style === 'ordered' ? 'ol' : 'ul';
    const rootEl = document.createElement(tag);
    rootEl.className = 'be-list';
    rootEl.contentEditable = 'false';

    this.dom = rootEl;
    this.#rootEl = rootEl;

    this.#renderTree(this.data.meta.items, rootEl, []);
    return rootEl;
  }

  /**
   * Render đệ quy cây ListNode[] vào <ul>/<ol>.
   * @param {object[]} nodes
   * @param {HTMLElement} listEl
   * @param {number[]} basePath
   */
  #renderTree(nodes, listEl, basePath) {
    nodes.forEach((node, idx) => {
      const path = [...basePath, idx];
      const li = this.#createLi(node, path);
      listEl.appendChild(li);

      if (node.children?.length > 0) {
        const subTag = this.data.meta.style === 'ordered' ? 'ol' : 'ul';
        const subList = document.createElement(subTag);
        subList.className = 'be-list';
        li.appendChild(subList);
        this.#renderTree(node.children, subList, path);
      }
    });
  }

  /**
   * Tạo <li> kèm <span contenteditable>.
   * @param {object} node — { rich_text: RichSegment[], children: [] }
   * @param {number[]} path
   * @returns {HTMLLIElement}
   */
  #createLi(node, path) {
    const li = document.createElement('li');
    li.dataset.path = path.join('.');

    const span = document.createElement('span');
    span.className = 'be-list-item-text be-editable';
    span.contentEditable = 'true';
    span.spellcheck = false;
    span.dataset.placeholder = 'Nhập nội dung...';
    span.dataset.beEditable = 'list-item';

    span.innerHTML = BlockSerializer.toHTML({ data: { rich_text: node.rich_text } });

    li.appendChild(span);

    // Input: sync content → RichSegment[] ngay
    span.addEventListener('input', () => {
      const { node: n } = this.#nodeAt(path);
      if (!n) return;
      const html = span.innerHTML?.trim() ?? '';
      n.rich_text = html
        ? BlockSerializer.tokensToSegments(RichTextParser.parse(html))
        : [];
    });

    span.addEventListener('paste', (e) => {
      e.preventDefault();
      const text = (e.originalEvent || e).clipboardData.getData('text/plain');
      this.paste(this.esc(text));
    });

    span.addEventListener('keydown', (e) => this.#handleKeydown(e, li, path));

    return li;
  }

  // ─── Keyboard ─────────────────────────────────────────────────────────────

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
    const isEmpty = !node?.rich_text || (Array.isArray(node.rich_text) ? node.rich_text.length === 0 : node.rich_text.trim() === '');
    const depth = this.#depth(path);

    if (isEmpty) {
      depth > 0 ? this.#handleOutdent(liEl, path) : this.#exitBlock(liEl, path);
      return;
    }

    const { parent } = this.#nodeAt(path);
    const localIdx = path[path.length - 1];
    parent.splice(localIdx + 1, 0, { rich_text: [], children: [] });

    this.#rerender(() => {
      const newPath = [...path.slice(0, -1), localIdx + 1];
      const newLi = this.dom.querySelector(`li[data-path="${newPath.join('.')}"]`);
      if (newLi) this.#focusLi(newLi, 'start');
    });
  }

  #handleIndent(liEl, path) {
    if (this.#depth(path) >= 3) return;
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
    if (this.#depth(path) === 0) return;

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
    const localIdx = path[path.length - 1];

    if (this.#depth(path) === 0 && localIdx === 0 && this.data.meta.items.length === 1) {
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

    this.bus?.dispatch('block:updated', { block: this });
    this.bus?.dispatch('block:add_request', { type: 'blocks/paragraph', afterId: this.id });
  }

  // ─── Re-render ────────────────────────────────────────────────────────────

  #rerender(afterRender) {
    const tag = this.data.meta.style === 'ordered' ? 'ol' : 'ul';

    if (this.dom.tagName.toLowerCase() !== tag) {
      const newRoot = document.createElement(tag);
      newRoot.className = this.dom.className;
      this.dom.parentElement?.replaceChild(newRoot, this.dom);
      this.dom = newRoot;
      this.#rootEl = newRoot;
    }

    this.dom.innerHTML = '';
    this.#renderTree(this.data.meta.items, this.dom, []);

    this.bus?.dispatch('block:updated', { block: this });
    if (afterRender) requestAnimationFrame(afterRender);
  }

  // ─── Overrides ────────────────────────────────────────────────────────────

  /**
   * List tự sync content vào this.data.values khi input,
   * nên serializeData chỉ cần clone data — không cần đọc từ DOM.
   *
   * @param {HTMLElement|null} _editableEl
   * @returns {object}
   */
  serializeData(_editableEl) {
    return {
      rich_text: [],
      meta: { ...this.data.meta },
    };
  }

  /**
   * Weight-based: 3 giây/item (đệ quy).
   * @returns {{ seconds: number }}
   */
  getStats() {
    return { seconds: this.#countItems(this.data.meta.items ?? []) * 3 };
  }

  #countItems(nodes) {
    return nodes.reduce((sum, node) =>
      sum + 1 + this.#countItems(node.children ?? []), 0
    );
  }

  renderInspectorControls() {
    const wrap = document.createElement('div');
    wrap.className = 'field-group';

    wrap.innerHTML = `
      <fieldset class="field__set">
        <legend class="field__label">Kiểu danh sách</legend>
        <div class="radio-group grid gap-2"
             data-radio-name="list_type"
             data-radio-default-value="${this.data.meta.style}">
          <label class="field__label">
            <div class="field" data-orientation="horizontal">
              <button id="bullet" class="radio-group__item" type="button" role="radio" value="bullet"></button>
              <div class="field__title"><i class="fa-solid fa-list-ul"></i> Không đánh số</div>
            </div>
          </label>
          <label class="field__label">
            <div class="field" data-orientation="horizontal">
              <button id="ordered" class="radio-group__item" type="button" role="radio" value="ordered"></button>
              <div class="field__title"><i class="fa-solid fa-list-ol"></i> Có đánh số</div>
            </div>
          </label>
        </div>
      </fieldset>
    `;

    const radioGroup = wrap.querySelector('.radio-group');
    RadioHandler.instance.register(radioGroup);

    radioGroup.addEventListener('radio:change', (e) => {
      this.data.meta.style = e.detail.value;
      this.bus?.dispatch('block:updated', { block: this });
    });

    return wrap;
  }

  focus(bus, position = 'end') {
    if (!this.dom) return;

    const all = Array.from(this.dom.querySelectorAll('li[data-path]'));
    const target = position === 'end' ? all[all.length - 1] : all[0];
    if (target) this.#focusLi(target, position);

    this.bus.dispatch('block:selected', { blockId: this.id });
  }
}