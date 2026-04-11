import { EditorBlock } from './editor_block.js';

export const TableSchema = {
  version: 1,
  icon: "<i class='fa-solid fa-table'></i>",
  name: 'blocks/table',
  title: 'Bảng',
  group: 'media',
  groupLabel: 'Phương tiện',
  attributes: {
    rows: {
      default: [
        ['Tiêu đề 1', 'Tiêu đề 2', 'Tiêu đề 3'],
        ['', '', ''],
        ['', '', ''],
      ]
    },
    hasHeader: { default: true },
    fixedWidth: { default: false },
  },
  supports: {}
};

/**
 * TableBlock v2 - Block-Driven Architecture
 */
export class TableBlock extends EditorBlock {

  /** @type {{ row: number, col: number } | null} */
  #activeCursor = null;

  render() {
    const wrapper = document.createElement('div');
    wrapper.className = 'be-preview-table-wrapper';
    this.dom = wrapper;
    this.#buildTable();
    return wrapper;
  }

  // ─── Build / Rebuild ──────────────────────────────────────────

  #buildTable() {
    this.dom.innerHTML = '';

    const scrollWrap = document.createElement('div');
    scrollWrap.className = 'be-table-scroll';

    const table = document.createElement('table');
    table.className = `be-table${this.data.fixedWidth ? ' be-table--fixed' : ''}`;
    table.contentEditable = 'false';

    const { rows, hasHeader } = this.data;

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

    scrollWrap.appendChild(table);
    this.dom.appendChild(scrollWrap);
  }

  /**
   * @param {string[]} cellData
   * @param {number} rowIndex
   * @param {'td'|'th'} cellTag
   * @returns {HTMLTableRowElement}
   */
  #buildRow(cellData, rowIndex, cellTag) {
    const tr = document.createElement('tr');
    tr.dataset.row = rowIndex;

    cellData.forEach((cellText, colIndex) => {
      const cell = document.createElement(cellTag);
      cell.className = 'be-table-cell';
      cell.contentEditable = 'true';
      cell.spellcheck = false;
      cell.dataset.row = rowIndex;
      cell.dataset.col = colIndex;
      cell.dataset.placeholder = cellTag === 'th' ? 'Tiêu đề...' : '';
      cell.textContent = cellText;

      cell.addEventListener('input', () => {
        this.data.rows[rowIndex][colIndex] = cell.textContent;
      });

      cell.addEventListener('paste', (e) => {
        e.preventDefault();
        const text = (e.originalEvent || e).clipboardData.getData('text/plain');
        document.execCommand('insertText', false, text);
      });

      cell.addEventListener('keydown', (e) => {
        if (e.key === 'Tab') {
          e.preventDefault();
          this.#navigateCell(rowIndex, colIndex, e.shiftKey ? -1 : 1);
        }
        if (e.key === 'Enter') {
          e.preventDefault();
          this.#navigateRow(rowIndex, colIndex, 1);
        }
      });

      // 1. FOCUS: Gửi yêu cầu vẽ Toolbar động lên Global Event Bus
      cell.addEventListener('focus', () => {
        this.#activeCursor = { row: rowIndex, col: colIndex };

        if (this.bus) {
          this.bus.dispatch('toolbar:request_dynamic', {
            block: this,
            anchorEl: cell, // Thanh công cụ sẽ bám vào ô này
            context: { row: rowIndex, col: colIndex }, // Ngữ cảnh cần thiết
            controls: [     // TỰ ĐỊNH NGHĨA UI CHO TOOLBAR
              { action: 'table:insert-row-before', icon: 'fa-solid fa-arrow-up', title: 'Chèn dòng lên trên' },
              { action: 'table:insert-row-after', icon: 'fa-solid fa-arrow-down', title: 'Chèn dòng xuống dưới' },
              { action: 'table:remove-row', icon: 'fa-solid fa-minus', title: 'Xóa dòng', danger: true },
              { divider: true },
              { action: 'table:insert-col-before', icon: 'fa-solid fa-arrow-left', title: 'Chèn cột sang trái' },
              { action: 'table:insert-col-after', icon: 'fa-solid fa-arrow-right', title: 'Chèn cột sang phải' },
              { action: 'table:remove-col', icon: 'fa-solid fa-minus', title: 'Xóa cột', danger: true },
            ]
          });
        }
      });

      // 2. BLUR: Khi rời khỏi bảng, yêu cầu ẩn Toolbar
      cell.addEventListener('blur', (e) => {
        const relatedTarget = e.relatedTarget;
        const stillInTable = relatedTarget && this.dom.contains(relatedTarget);

        if (!stillInTable && this.bus) {
          this.#activeCursor = null;
          this.bus.dispatch('toolbar:hide_dynamic');
        }
      });

      tr.appendChild(cell);
    });

    return tr;
  }

  // ─── Navigation ───────────────────────────────────────────────

  #navigateCell(row, col, direction) {
    const colCount = this.data.rows[row]?.length ?? 0;
    const rowCount = this.data.rows.length;

    let nextCol = col + direction;
    let nextRow = row;

    if (nextCol >= colCount) {
      nextCol = 0;
      nextRow = row + 1;
    } else if (nextCol < 0) {
      nextRow = row - 1;
      if (nextRow < 0) return;
      nextCol = (this.data.rows[nextRow]?.length ?? 1) - 1;
    }

    if (nextRow >= rowCount) {
      this.insertRowAfter(rowCount - 1);
      requestAnimationFrame(() => this.#focusCell(nextRow, 0));
      return;
    }

    this.#focusCell(nextRow, nextCol);
  }

  #navigateRow(row, col, direction) {
    const nextRow = row + direction;
    if (nextRow < 0 || nextRow >= this.data.rows.length) return;
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

  // ─── Toolbar Delegation ───────────────────────────────────────

  /**
   * 3. Ủy Quyền Xử Lý: Toolbar sẽ gọi hàm này và trả lại ngữ cảnh (context)
   */
  handleToolbarAction(action, ctx) {
    if (!ctx) return;
    const { row, col } = ctx;

    switch (action) {
      case 'table:insert-row-before': this.insertRowBefore(row); break;
      case 'table:insert-row-after': this.insertRowAfter(row); break;
      case 'table:remove-row': this.removeRow(row); break;
      case 'table:insert-col-before': this.insertColBefore(col); break;
      case 'table:insert-col-after': this.insertColAfter(col); break;
      case 'table:remove-col': this.removeCol(col); break;
      default:
        console.warn(`Action ${action} chưa được hỗ trợ trên TableBlock`);
    }
  }

  // ─── Public Mutation API ──────────────────────────────────────

  insertRowBefore(rowIndex) {
    const colCount = this.data.rows[0]?.length ?? 1;
    this.data.rows.splice(rowIndex, 0, new Array(colCount).fill(''));
    this.#rerender(rowIndex, this.#activeCursor?.col ?? 0);
  }

  insertRowAfter(rowIndex) {
    const colCount = this.data.rows[0]?.length ?? 1;
    this.data.rows.splice(rowIndex + 1, 0, new Array(colCount).fill(''));
    this.#rerender(rowIndex + 1, this.#activeCursor?.col ?? 0);
  }

  removeRow(rowIndex) {
    if (this.data.rows.length <= 1) return;
    this.data.rows.splice(rowIndex, 1);
    const focusRow = Math.min(rowIndex, this.data.rows.length - 1);
    this.#rerender(focusRow, this.#activeCursor?.col ?? 0);
  }

  insertColBefore(colIndex) {
    this.data.rows.forEach(row => row.splice(colIndex, 0, ''));
    this.#rerender(this.#activeCursor?.row ?? 0, colIndex);
  }

  insertColAfter(colIndex) {
    this.data.rows.forEach(row => row.splice(colIndex + 1, 0, ''));
    this.#rerender(this.#activeCursor?.row ?? 0, colIndex + 1);
  }

  removeCol(colIndex) {
    const colCount = this.data.rows[0]?.length ?? 0;
    if (colCount <= 1) return;
    this.data.rows.forEach(row => row.splice(colIndex, 1));
    const focusCol = Math.min(colIndex, colCount - 2);
    this.#rerender(this.#activeCursor?.row ?? 0, focusCol);
  }

  // ─── Re-render ────────────────────────────────────────────────

  #rerender(focusRow, focusCol) {
    this.#buildTable();

    if (this.bus) {
      this.bus.dispatch('block:updated', { block: this });
    }

    requestAnimationFrame(() => {
      this.#focusCell(
        Math.min(focusRow, this.data.rows.length - 1),
        Math.min(focusCol, (this.data.rows[0]?.length ?? 1) - 1)
      );
    });
  }

  // ─── Inspector controls ───────────────────────────────────────

  renderInspectorControls(data, { onUpdate }) {
    const wrap = document.createElement('div');
    wrap.innerHTML = `
      <div class="be-settings-property-section">
        <span class="be-settings-property__label">Header</span>
        <div class="be-settings-level-group">
          <button type="button" class="btn be-table-header-btn ${data.hasHeader ? 'active' : ''}" data-variant="outline" data-has-header="true">
            <i class="fa-solid fa-table-columns"></i> Có header
          </button>
          <button type="button" class="btn be-table-header-btn ${!data.hasHeader ? 'active' : ''}" data-variant="outline" data-has-header="false">
            Không header
          </button>
        </div>
      </div>
      <div class="be-settings-property-section">
        <span class="be-settings-property__label">Độ rộng cột</span>
        <div class="be-settings-level-group">
          <button type="button" class="btn be-table-width-btn ${data.fixedWidth ? 'active' : ''}" data-variant="outline" data-fixed="true">
            Cố định
          </button>
          <button type="button" class="btn be-table-width-btn ${!data.fixedWidth ? 'active' : ''}" data-variant="outline" data-fixed="false">
            Tự động
          </button>
        </div>
      </div>
    `;

    wrap.querySelectorAll('.be-table-header-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        onUpdate({ hasHeader: btn.dataset.hasHeader === 'true' });
        wrap.querySelectorAll('.be-table-header-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
      });
    });

    wrap.querySelectorAll('.be-table-width-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        onUpdate({ fixedWidth: btn.dataset.fixed === 'true' });
        wrap.querySelectorAll('.be-table-width-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
      });
    });

    return wrap;
  }

  focus(bus, position = 'end') {
    if (!this.dom) return;
    const rows = this.data.rows;
    if (!rows.length) return;

    if (position === 'end') {
      const lastRow = rows.length - 1;
      this.#focusCell(lastRow, (rows[lastRow]?.length ?? 1) - 1);
    } else {
      this.#focusCell(0, 0);
    }

    bus.dispatch('block:selected', { blockId: this.id });
  }
}