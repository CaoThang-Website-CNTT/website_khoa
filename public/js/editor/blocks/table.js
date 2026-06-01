import { EditorBlock } from './editor_block.js';
import { BlockSerializer } from '../block_serializer_v2.js';
import { RichTextParser } from '../rich_text_parser.js';

export const TableSchema = {
  version: 1,
  icon: "<i class='fa-solid fa-table'></i>",
  type: 'blocks/table',
  title: 'Bảng',
  group: 'media',
  groupLabel: 'Phương tiện',
  meta: {
    hasHeader: { default: true },
    rows: {
      default: [
        ['Tiêu đề 1', 'Tiêu đề 2', 'Tiêu đề 3'],
        ['', '', ''],
        ['', '', ''],
      ],
    },
  },
  supports: {},
};

export class TableBlock extends EditorBlock {

  /** @type {{ row: number, col: number }|null} */
  #activeCursor = null;
  /** @type {{ type: 'row'|'col', index: number }|null} */
  #selectedSelection = null;

  #rowHandle = null;
  #colHandle = null;

  constructor(...args) {
    super(...args);
    // Deep clone rows để tránh mutate default schema
    if (this.data?.meta?.rows) {
      this.data.meta.rows = JSON.parse(JSON.stringify(this.data.meta.rows));
    }
  }

  render() {
    const wrapper = document.createElement('div');
    wrapper.className = 'be-table-wrapper';
    wrapper.style.position = 'relative';

    this.dom = wrapper;
    this.#buildTable();
    return wrapper;
  }

  // ─── Build ────────────────────────────────────────────────────────────────

  #buildTable() {
    this.dom.innerHTML = '';

    this.#colHandle = document.createElement('div');
    this.#colHandle.className = 'be-table-col-handle';
    this.#colHandle.innerHTML = '<i class="fa-solid fa-grip-horizontal"></i>';

    this.#rowHandle = document.createElement('div');
    this.#rowHandle.className = 'be-table-row-handle';
    this.#rowHandle.innerHTML = '<i class="fa-solid fa-grip-vertical"></i>';

    const scrollContainer = document.createElement('div');
    scrollContainer.className = 'be-table-scroll';

    const table = document.createElement('table');
    table.className = 'be-table';
    table.contentEditable = 'false';

    const { rows, hasHeader } = this.data.meta;

    if (hasHeader && rows.length > 0) {
      const thead = document.createElement('thead');
      thead.appendChild(this.#buildRow(rows[0], 0, 'th'));
      table.appendChild(thead);
    }

    const tbody = document.createElement('tbody');
    const startRow = hasHeader ? 1 : 0;
    for (let r = startRow; r < rows.length; r++) {
      tbody.appendChild(this.#buildRow(rows[r], r, 'td'));
    }
    table.appendChild(tbody);

    scrollContainer.appendChild(table);
    this.dom.appendChild(this.#colHandle);
    this.dom.appendChild(this.#rowHandle);
    this.dom.appendChild(scrollContainer);

    this.#setupHandleTracking();
  }

  #setupHandleTracking() {
    this.dom.addEventListener('mousemove', (e) => {
      const cell = e.target.closest('td, th');
      if (e.target.closest('.be-table-row-handle, .be-table-col-handle')) return;
      if (!cell) return;

      const tr = cell.closest('tr');
      const wrapperRect = this.dom.getBoundingClientRect();
      const cellRect = cell.getBoundingClientRect();
      const trRect = tr.getBoundingClientRect();

      this.#colHandle.style.display = 'flex';
      this.#rowHandle.style.display = 'flex';
      this.#colHandle.style.left = `${(cellRect.left - wrapperRect.left) + (cellRect.width / 2) - 20}px`;
      this.#rowHandle.style.top = `${(trRect.top - wrapperRect.top) + (trRect.height / 2) - 15}px`;

      this.#rowHandle.dataset.rowIndex = tr.rowIndex;
      this.#colHandle.dataset.colIndex = cell.cellIndex;
    });

    this.dom.addEventListener('mouseleave', () => {
      this.#colHandle.style.display = 'none';
      this.#rowHandle.style.display = 'none';
    });

    this.#rowHandle.addEventListener('click', (e) => {
      e.stopPropagation();
      this.#highlightRow(parseInt(this.#rowHandle.dataset.rowIndex, 10));
      this.bus?.dispatch('toolbar:toggle', {
        block: this, anchorEl: this.#rowHandle,
        selection: this.#selectedSelection, hideDefault: true,
      });
    });

    this.#colHandle.addEventListener('click', (e) => {
      e.stopPropagation();
      this.#highlightCol(parseInt(this.#colHandle.dataset.colIndex, 10));
      this.bus?.dispatch('toolbar:toggle', {
        block: this, anchorEl: this.#colHandle,
        selection: this.#selectedSelection, hideDefault: true,
      });
    });

    this.dom.addEventListener('click', (e) => {
      if (e.target.closest('.be-table-row-handle, .be-table-col-handle')) return;
      if (e.target.closest('td, th')) this.#clearHighlight();
    });
  }

  #clearHighlight() {
    this.dom?.querySelectorAll('.be-cell-selected')
      .forEach(cell => cell.classList.remove('be-cell-selected'));
    this.#selectedSelection = null;
  }

  #highlightRow(rowIndex) {
    this.#clearHighlight();
    const table = this.dom.querySelector('.be-table');
    if (!table?.rows[rowIndex]) return;
    [...table.rows[rowIndex].cells].forEach(c => c.classList.add('be-cell-selected'));
    this.#selectedSelection = { type: 'row', index: rowIndex };
  }

  #highlightCol(colIndex) {
    this.#clearHighlight();
    const table = this.dom.querySelector('.be-table');
    if (!table) return;
    for (const row of table.rows) {
      row.cells[colIndex]?.classList.add('be-cell-selected');
    }
    this.#selectedSelection = { type: 'col', index: colIndex };
  }

  // ─── Row builder ──────────────────────────────────────────────────────────

  /**
   * @param {Array<RichSegment[]|string>} cellData
   * @param {number} rowIndex
   * @param {'td'|'th'} cellTag
   * @returns {HTMLTableRowElement}
   */
  #buildRow(cellData, rowIndex, cellTag) {
    const tr = document.createElement('tr');
    tr.dataset.row = rowIndex;

    cellData.forEach((cellValue, colIndex) => {
      const cell = document.createElement(cellTag);
      cell.contentEditable = 'true';
      cell.spellcheck = false;
      cell.dataset.row = rowIndex;
      cell.dataset.col = colIndex;
      cell.dataset.placeholder = cellTag === 'th' ? 'Tiêu đề...' : '';

      cell.innerHTML = BlockSerializer.toHTML({ data: { rich_text: cellValue } });

      // Input: sync cell → RichSegment[] ngay
      cell.addEventListener('input', () => {
        const html = cell.innerHTML?.trim() ?? '';
        this.data.meta.rows[rowIndex][colIndex] = html
          ? BlockSerializer.tokensToSegments(RichTextParser.parse(html))
          : [];
      });

      cell.addEventListener('paste', (e) => {
        e.preventDefault();
        const text = (e.originalEvent || e).clipboardData.getData('text/plain');
        this.paste(this.esc(text));
      });

      cell.addEventListener('keydown', (e) => {
        if (e.key === 'Tab') { e.preventDefault(); this.#navigateCell(rowIndex, colIndex, e.shiftKey ? -1 : 1); }
        if (e.key === 'Enter') { e.preventDefault(); this.#navigateRow(rowIndex, colIndex, 1); }
      });

      cell.addEventListener('focus', () => {
        this.#activeCursor = { row: rowIndex, col: colIndex };
      });

      cell.addEventListener('blur', (e) => {
        const stillInTable = e.relatedTarget && this.dom.contains(e.relatedTarget);
        if (!stillInTable) {
          this.#activeCursor = null;
          this.bus?.dispatch('toolbar:hide_dynamic');
        }
      });

      tr.appendChild(cell);
    });

    return tr;
  }

  // ─── Navigation ───────────────────────────────────────────────────────────

  #navigateCell(row, col, direction) {
    const colCount = this.data.meta.rows[row]?.length ?? 0;
    const rowCount = this.data.meta.rows.length;
    let nextCol = col + direction;
    let nextRow = row;

    if (nextCol >= colCount) { nextCol = 0; nextRow = row + 1; }
    else if (nextCol < 0) { nextRow = row - 1; if (nextRow < 0) return; nextCol = (this.data.meta.rows[nextRow]?.length ?? 1) - 1; }

    if (nextRow >= rowCount) {
      this.insertRowAfter(rowCount - 1);
      requestAnimationFrame(() => this.#focusCell(nextRow, 0));
      return;
    }
    this.#focusCell(nextRow, nextCol);
  }

  #navigateRow(row, col, direction) {
    const nextRow = row + direction;
    if (nextRow < 0 || nextRow >= this.data.meta.rows.length) return;
    this.#focusCell(nextRow, col);
  }

  #focusCell(row, col) {
    const cell = this.dom.querySelector(`[data-row="${row}"][data-col="${col}"]`);
    if (!cell) return;
    cell.focus();
    const range = document.createRange();
    const sel = window.getSelection();
    range.selectNodeContents(cell);
    range.collapse(false);
    sel.removeAllRanges();
    sel.addRange(range);
  }

  // ─── Mutation API ─────────────────────────────────────────────────────────

  getDynamicToolbar(selection) {
    if (!selection) return '';
    if (selection.type === 'row') return `
      <button type="button" class="dropdown__item be-toolbar__item" data-action="table:insert-row-above">
        <i class="fa-solid fa-arrow-up"></i><span class="dropdown__item-label">Thêm dòng bên trên</span>
      </button>
      <button type="button" class="dropdown__item be-toolbar__item" data-action="table:insert-row-below">
        <i class="fa-solid fa-arrow-down"></i><span class="dropdown__item-label">Thêm dòng bên dưới</span>
      </button>
      <button type="button" class="dropdown__item be-toolbar__item be-toolbar__item--destructive" data-action="table:remove-row">
        <i class="fa-solid fa-trash-can"></i><span class="dropdown__item-label">Xóa dòng</span>
      </button>`;

    if (selection.type === 'col') return `
      <button type="button" class="dropdown__item be-toolbar__item" data-action="table:insert-col-before">
        <i class="fa-solid fa-arrow-left"></i><span class="dropdown__item-label">Thêm cột bên trái</span>
      </button>
      <button type="button" class="dropdown__item be-toolbar__item" data-action="table:insert-col-after">
        <i class="fa-solid fa-arrow-right"></i><span class="dropdown__item-label">Thêm cột bên phải</span>
      </button>
      <button type="button" class="dropdown__item be-toolbar__item be-toolbar__item--destructive" data-action="table:remove-col">
        <i class="fa-solid fa-trash-can"></i><span class="dropdown__item-label">Xóa cột</span>
      </button>`;
    return '';
  }

  handleToolbarAction(action, selection) {
    if (!selection) return;
    this.#clearHighlight();
    this.#colHandle.style.display = 'none';
    this.#rowHandle.style.display = 'none';

    const { type, index } = selection;
    switch (action) {
      case 'table:insert-row-above': this.insertRowBefore(index); break;
      case 'table:insert-row-below': this.insertRowAfter(index); break;
      case 'table:remove-row': this.removeRow(index); break;
      case 'table:insert-col-before': this.insertColBefore(index); break;
      case 'table:insert-col-after': this.insertColAfter(index); break;
      case 'table:remove-col': this.removeCol(index); break;
      default: console.warn(`[TableBlock] Action không hỗ trợ: ${action}`);
    }
  }

  insertRowBefore(rowIndex) {
    const colCount = this.data.meta.rows[0]?.length ?? 1;
    this.data.meta.rows.splice(rowIndex, 0, new Array(colCount).fill([]));
    this.#rerender(rowIndex, this.#activeCursor?.col ?? 0);
  }

  insertRowAfter(rowIndex) {
    const colCount = this.data.meta.rows[0]?.length ?? 1;
    this.data.meta.rows.splice(rowIndex + 1, 0, new Array(colCount).fill([]));
    this.#rerender(rowIndex + 1, this.#activeCursor?.col ?? 0);
  }

  removeRow(rowIndex) {
    if (this.data.meta.rows.length <= 1) return;
    this.data.meta.rows.splice(rowIndex, 1);
    this.#rerender(Math.min(rowIndex, this.data.meta.rows.length - 1), this.#activeCursor?.col ?? 0);
  }

  insertColBefore(colIndex) {
    this.data.meta.rows.forEach(row => row.splice(colIndex, 0, []));
    this.#rerender(this.#activeCursor?.row ?? 0, colIndex);
  }

  insertColAfter(colIndex) {
    this.data.meta.rows.forEach(row => row.splice(colIndex + 1, 0, []));
    this.#rerender(this.#activeCursor?.row ?? 0, colIndex + 1);
  }

  removeCol(colIndex) {
    const colCount = this.data.meta.rows[0]?.length ?? 0;
    if (colCount <= 1) return;
    this.data.meta.rows.forEach(row => row.splice(colIndex, 1));
    this.#rerender(this.#activeCursor?.row ?? 0, Math.min(colIndex, colCount - 2));
  }

  #rerender(focusRow, focusCol) {
    this.#buildTable();
    this.bus?.dispatch('block:updated', { block: this });
    requestAnimationFrame(() => {
      this.#focusCell(
        Math.min(focusRow, this.data.meta.rows.length - 1),
        Math.min(focusCol, (this.data.meta.rows[0]?.length ?? 1) - 1),
      );
    });
  }

  // ─── Overrides ────────────────────────────────────────────────────────────

  /**
   * Table sync cell data vào this.data.rows khi input -
   * serializeData chỉ cần clone, không đọc từ DOM.
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
   * Weight-based: 2 giây/cell.
   * @returns {{ seconds: number }}
   */
  renderInspectorControls() {
    const wrap = document.createElement('div');
    wrap.className = 'field-group';

    wrap.innerHTML = `
      <fieldset class="field__set">
        <legend class="field__label">Header</legend>
        <div class="radio-group grid gap-2"
             data-radio-name="has_header"
             data-radio-default-value="${this.data.meta.hasHeader}">
          <label class="field__label">
            <div class="field" data-orientation="horizontal">
              <button id="has_header" class="radio-group__item" type="button" role="radio" value="true"></button>
              <div class="field__title">Có header</div>
            </div>
          </label>
          <label class="field__label">
            <div class="field" data-orientation="horizontal">
              <button id="not_has_header" class="radio-group__item" type="button" role="radio" value="false"></button>
              <div class="field__title">Không header</div>
            </div>
          </label>
        </div>
      </fieldset>
    `;

    const radioGroup = wrap.querySelector('.radio-group');
    RadioHandler.instance.register(radioGroup);

    radioGroup.addEventListener('radio:change', (e) => {
      this.data.meta.hasHeader = e.detail.value === 'true';
      this.bus?.dispatch('block:updated', { block: this });
    });

    return wrap;
  }

  focus(bus, position = 'end') {
    if (!this.dom) return;
    const rows = this.data.meta.rows;
    if (!rows.length) return;

    if (position === 'end') {
      const lastRow = rows.length - 1;
      this.#focusCell(lastRow, (rows[lastRow]?.length ?? 1) - 1);
    } else {
      this.#focusCell(0, 0);
    }

    this.bus.dispatch('block:selected', { blockId: this.id });
  }
}