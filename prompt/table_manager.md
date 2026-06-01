# Hand-off Context: TableManager Administrative System

## 1. Overview

The `TableManager` is a client-side and server-side capable data table system designed for the administrative dashboard. It follows a modular architecture to handle columns, sorting, filtering, and pagination independently.

- **Root Directory**: `public/js/table/`
- **Entry Point**: `public/js/table/index.js`
- **Files**:
  - `index.js` - Exports `TableManager` to `window`
  - `table_manager.js` - Core `TableManager` singleton and `TableInstance` class
  - `table_renderer.js` - DOM rendering logic (`TableRenderer`)
  - `column_registry.js` - Column definition parsing (`ColumnDef`, `ColumnRegistry`)
  - `template_engine.js` - Template compilation and rendering (`TemplateEngine`)

## 2. Core Architecture

| Class            | File                 | Responsibility                                                                                                                        |
| :--------------- | :------------------- | :------------------------------------------------------------------------------------------------------------------------------------ |
| `TableManager`   | `table_manager.js`   | Singleton orchestrator. Auto-boots on DOMContentLoaded. Registers instances, provides static API.                                     |
| `TableInstance`  | `table_manager.js`   | Orchestrates components, manages state (`sort`, `filters`, `search`, `pagination`). Handles lifecycle (`init`, `render`, `loadData`). |
| `TableRenderer`  | `table_renderer.js`  | Handles all DOM generation, toolbar, table skeleton, row rendering, pagination UI.                                                    |
| `ColumnRegistry` | `column_registry.js` | Parses `<template>` elements from HTML to define column behaviors (sortable, filterable, width, etc.).                                |
| `ColumnDef`      | `column_registry.js` | Represents a single column with metadata (key, label, sortable, filterType, filterOps, custom render).                                |
| `TemplateEngine` | `template_engine.js` | Compiles `<template>` HTML with `{{expr}}` syntax into render functions. Supports helpers.                                            |

## 3. Data Attributes & HTML Structure

### Root Table Element

```html
<div
  data-tm="table_categories"
  data-tm-mode="client"
  data-tm-src="/api/categories"
  data-tm-searchable
  data-tm-selectable
  data-tm-id-key="id"
  data-tm-toolbar-target="#custom-toolbar"
  data-tm-footer-target="#custom-footer"
  data-tm-search-target=".custom-search-input"
>
  <!-- Column templates -->
  <template
    data-tm-col="name"
    data-tm-label="Tên"
    data-tm-sortable
    data-tm-width="200px"
    data-tm-align="left"
    data-tm-filter-type="text"
    data-tm-filter-ops="contains,="
  >
    <b>{{ row.name }}</b>
  </template>

  <template
    data-tm-col="status"
    data-tm-label="Trạng thái"
    data-tm-filter-type="select"
    data-tm-filter-options='[{"value":"active","label":"Hoạt động"},{"value":"inactive","label":"Không hoạt động"}]'
  >
    <span class="badge" data-variant="primary">{{ row.status }}</span>
  </template>

  <!-- Pagination -->
  <template data-tm-pagination></template>
</div>

<!-- Inline data for client mode (optional) -->
<script type="application/json" data-tm-data="table_categories">
  [
    { "id": 1, "name": "Category A", "status": "active" },
    { "id": 2, "name": "Category B", "status": "inactive" }
  ]
</script>
```

### Data Attributes Reference

| Attribute                | Target   | Values                                   | Purpose                                                 |
| :----------------------- | :------- | :--------------------------------------- | :------------------------------------------------------ |
| `data-tm`                | Root div | string (table ID)                        | Identifies the table instance. Required.                |
| `data-tm-mode`           | Root div | `client` \| `server`                     | Determines data handling mode. Default: `client`.       |
| `data-tm-src`            | Root div | URL string                               | API endpoint for server mode.                           |
| `data-tm-searchable`     | Root div | presence flag                            | Enables global search input.                            |
| `data-tm-selectable`     | Root div | presence flag                            | Enables row checkboxes.                                 |
| `data-tm-id-key`         | Root div | string                                   | Row property key for selection. Default: `id`.          |
| `data-tm-toolbar-target` | Root div | CSS selector                             | External element to inject toolbar.                     |
| `data-tm-footer-target`  | Root div | CSS selector                             | External element to inject pagination.                  |
| `data-tm-search-target`  | Root div | CSS selector                             | External input for search (if `tmSearchable` set).      |
| `data-tm-col`            | template | string (key)                             | Row property key. Required per column.                  |
| `data-tm-label`          | template | string                                   | Display label. Default: `data-tm-col` value.            |
| `data-tm-sortable`       | template | presence flag                            | Enables sorting on this column.                         |
| `data-tm-width`          | template | CSS value                                | Column width (e.g., `100px`, `15%`).                    |
| `data-tm-align`          | template | `left` \| `center` \| `right`            | Cell text alignment. Default: `left`.                   |
| `data-tm-filter-type`    | template | `text` \| `number` \| `date` \| `select` | Filter control type.                                    |
| `data-tm-filter-ops`     | template | comma-separated                          | Operators: `contains`, `=`, `!=`, `>`, `>=`, `<`, `<=`. |
| `data-tm-filter-options` | template | JSON array                               | Options for `select` filter type.                       |

## 4. TableInstance API

### Constructor & Initialization

```javascript
// Auto-created by TableManager on DOMContentLoaded
const inst = new TableInstance(rootElement);
inst.init(); // Builds DOM layout, triggers render()
```

### State Management

```javascript
// Get current state snapshot
const state = inst.getState();
// Returns:
// {
//   sort: { col: 'name', dir: 'asc' },
//   filters: [{ col: 'status', op: '=', value: 'active' }],
//   search: 'hello',
//   pagination: { pageIndex: 0, pageSize: 20 },
//   mode: 'client',
//   totalRows: 100,
//   rowSelection: ['1', '3', '5']
// }
```

### Data Loading

```javascript
// Client mode: load full dataset (TableManager handles filter/sort/page)
inst.setData(rows);
inst.loadData(rows);

// Server mode: load single page (server handles filter/sort/page)
inst.loadData(
  {
    rows: pageData,
    total: 500, // Total rows on server (for pagination)
    page: 1, // Current page (1-indexed)
    limit: 20, // Rows per page
  },
  { total: 500 }, // or use meta param
);
```

### Sorting

```javascript
// Toggle sort on column: asc → desc → off
inst.setSort("name"); // Cycle: off → asc → desc → off

// Internal: sorts normalized Vietnamese strings
const cmp = (a, b) => {
  const va = String(a[col] ?? "");
  const vb = String(b[col] ?? "");
  return va.localeCompare(vb, "vi", { numeric: true, sensitivity: "accent" });
};
```

### Filtering

```javascript
// Set or replace filter rule for column
inst.setFilter("status", "=", "active");
inst.setFilter("price", ">", "1000");

// Clear specific column's filter
inst.clearFilter("status");

// Clear all filters and search
inst.clearFilters();

// Set global search query
inst.setSearch("keyword");
```

### Pagination

```javascript
// Page navigation
inst.canPrevPage(); // boolean
inst.canNextPage(); // boolean
inst.prevPage();
inst.nextPage();
inst.setPageIndex(2); // 0-indexed
inst.setPageSize(50);
inst.getPageCount(); // Total pages
inst.getVisibleRows(); // Current page rows (with search/sort/filter applied)
```

### Row Selection (if `tmSelectable` enabled)

```javascript
// Get selected row IDs
const ids = inst.getRowSelection(); // Returns: ['1', '3', '5']

// Check selection status
const isSelected = inst.hasRowSelection("1"); // boolean

// Toggle row selection
inst.toggleRowSelection("1", true); // Add to selection
inst.toggleRowSelection("1", false); // Remove from selection

// Clear all selections
inst.clearSelection();

// Update header checkbox UI
inst.updateHeaderCheckbox();
```

### Rendering

```javascript
// Trigger full render (called automatically on state change)
inst.render();

// Access internal data
inst.data; // Current rows array
inst.columns.all; // All ColumnDef[] instances
inst.id; // Table ID
inst.root; // Root HTMLElement
```

## 5. TableRenderer Syntax

### DOM Building

```javascript
const renderer = new TableRenderer(tableInstance);

// Build layout (called once during init)
const { table, hasPagination } = renderer.buildLayout();

// Render rows to tbody
renderer.renderRows(rows, table);

// Update sort indicators
renderer.updateSortUI(table, { col: "name", dir: "asc" });

// Render pagination controls
renderer.renderPagination({
  pagState: { pageIndex: 0, pageSize: 20 },
  totalRows: 100,
});

// Render active filter pills
renderer.renderFilters([{ col: "status", op: "=", value: "active" }]);
```

### Custom Select Builder (Internal)

```javascript
// Creates styled <select> for filter dropdowns
const select = renderer.#buildCustomSelect(
  options, // [{ value, label }, ...]
  placeholder, // string
  defaultValue, // string
);
// select._currentValue stores the selected value
```

### DOM Classes & Structure

```html
<!-- Main wrapper -->
<div class="tm-wrapper" data-tm-wrapper="table_id">
  <!-- Toolbar (search + filter dropdowns) -->
  <div class="tm-toolbar">
    <div class="tm-toolbar__top">
      <!-- Search -->
      <div class="tm-search-wrapper">
        <div class="tm-search">
          <span class="tm-search__icon"
            ><i class="fa-solid fa-magnifying-glass"></i
          ></span>
          <input
            class="tm-search__input"
            type="text"
            placeholder="Tìm kiếm..."
          />
        </div>
      </div>
      <!-- Filter dropdowns -->
      <div class="tm-filter-bar">
        <div class="dropdown tm-filter-dropdown" data-tm-col="status">
          <button class="dropdown__trigger tm-filter-btn">
            <i class="fa-solid fa-filter"></i>Trạng thái
          </button>
          <div class="dropdown__content">
            <div class="tm-filter-panel">
              <div class="select tm-filter-panel__op">...</div>
              <div class="select tm-filter-panel__value">...</div>
              <button class="btn tm-filter-panel__action-btn">Áp dụng</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Active filter pills -->
    <div class="tm-filter-pills" data-tm-filter-pills="table_id">
      <div class="badge tm-filter-pill">
        <span class="tm-filter-pill__text"><b>Status</b>: active</span>
        <button class="tm-filter-pill__remove">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- Data table -->
  <div class="tm-data-wrapper">
    <div class="tm-scroll">
      <table class="tm-table" data-tm-table="table_id">
        <colgroup>
          <col style="width: 40px;" />
          <col />
          <col />
        </colgroup>
        <thead class="tm-thead">
          <tr>
            <th class="tm-th tm-th--sortable" data-tm-sort-col="name">
              <span class="tm-th__label">Tên</span>
              <span class="tm-sort-icon">
                <i class="fa-solid fa-chevron-up tm-sort-icon__asc"></i>
                <i class="fa-solid fa-chevron-down tm-sort-icon__desc"></i>
              </span>
            </th>
          </tr>
        </thead>
        <tbody class="tm-tbody" data-tm-body="">
          <tr class="tm-tr" data-tm-row-index="0">
            <td class="tm-td">...</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="tm-loading-overlay" data-tm-loading="">
      <div class="tm-spinner"></div>
    </div>
  </div>

  <!-- Pagination -->
  <div class="tm-footer-controls">
    <div class="tm-page-info" data-tm-page-info="table_id">
      Showing 1-20 of 100
    </div>
    <div data-tm-pagination="table_id">
      <a class="tm-page-btn" href="?table_id_page=1">1</a>
      <a class="tm-page-btn tm-page-btn--active" href="?table_id_page=2">2</a>
      ...
    </div>
  </div>
</div>
```

## 6. TemplateEngine Syntax

### Template Compilation

```javascript
// Register helper functions (before compile)
TemplateEngine.registerHelper("formatDate", (val) =>
  dayjs(val).format("DD/MM/YYYY"),
);
TemplateEngine.registerHelper("currency", (val) =>
  new Intl.NumberFormat("vi-VN").format(val),
);

// Compile template element
const renderFn = TemplateEngine.compile(templateElement);

// Use render function in column
const frag = renderFn(row, rowValue);
```

### Template Syntax

```html
<!-- Supported expressions in {{ }} -->
<template data-tm-col="amount">
  {{ value }}
  <!-- Raw value -->
  {{ row.amount }}
  <!-- Dot-path access -->
  {{ row.amount || 'N/A' }}
  <!-- Fallback operator -->
  {{ formatDate(row.created_at) }}
  <!-- Helper function -->
  {{ currency(row.price) }}
  <!-- With args -->
  {{ row.status == 'active' ? 'Yes' : 'No' }}
  <!-- Ternary -->
</template>
```

### Execution Context

```javascript
// Inside a template expression, these are in scope:
{
  value,                  // The cell value (row[col.key])
  row,                    // The entire row object
  ...row,                 // Row properties spread (row.name === name)
  // Plus all registered helpers:
  formatDate,
  currency,
  // ...etc
}
```

### Escaping

- HTML is auto-escaped for safety (XSS prevention)
- Use raw expressions if needed (not recommended)

## 7. ColumnRegistry & ColumnDef

### ColumnRegistry

```javascript
// Auto-created from <template> elements in root
const registry = new ColumnRegistry(rootElement, DEFAULT_FILTER_OPS);

// Access column definitions
registry.all;  // Returns: ColumnDef[]

// Insert column at start (used internally for checkboxes)
registry.prepend(new ColumnDef({ key, label, ... }));
```

### ColumnDef

```javascript
const col = new ColumnDef({
  key: "name", // Row property key
  label: "Tên danh mục", // Display label
  sortable: true, // Allow sorting
  width: "200px", // Column width
  align: "left", // left|center|right
  filterType: "text", // text|number|date|select|null
  filterOps: ["contains", "=", "!="], // Allowed operators
  filterOptions: [
    // For select filters
    { value: "active", label: "Hoạt động" },
    { value: "inactive", label: "Không hoạt động" },
  ],
  render: (row, value) => DocumentFragment, // Custom renderer
});
```

## 8. TableManager Static API

```javascript
// Get or auto-create TableManager singleton
TableManager.instance;

// Initialize all [data-tm] tables on page
TableManager.instance.init();

// Get table instance by ID
const inst = TableManager.get("table_categories");

// Static shortcuts
TableManager.setFilter(tableId, col, op, value);
TableManager.setFilterOptions(tableId, col, options);
TableManager.getRowSelection(tableId); // [ids]
TableManager.clearSelection(tableId);
TableManager.loadData(tableId, payload);
TableManager.getSelectedIds(tableId); // @deprecated, use getRowSelection
```

## 9. Events

### Dispatched by TableInstance

```javascript
// Fired when state changes (sort, filter, search, pagination)
root.addEventListener("tm:state-change", (e) => {
  const { reason, state } = e.detail;
  // reason: 'sort' | 'filter' | 'search' | 'pagination'
  // state: full state snapshot
});

// Fired after render completes
root.addEventListener("tm:render", (e) => {
  const { tableId, visibleRows, totalRows, state } = e.detail;
});

// Fired when rows are selected/deselected
root.addEventListener("tm:selection-change", (e) => {
  const { rowSelection } = e.detail; // ['1', '3', '5']
});

// Search input changed
root.addEventListener("tm:search:change", (e) => {
  const { search } = e.detail;
});

// Filter applied
root.addEventListener("tm:filter:apply", (e) => {
  const { column, operator, value } = e.detail;
});

// Filter removed
root.addEventListener("tm:filter:clear", (e) => {
  const { column } = e.detail;
});
```

## 10. Data Flow: Client Mode vs Server Mode

### Client Mode

1. User loads page
2. `<script data-tm-data="id">` contains full dataset (or `loadData([...])` called)
3. TableManager loads all rows into memory (`inst.data`)
4. User sorts/filters/searches → `TableInstance` filters in-memory
5. Pagination slices filtered results
6. Display on page

```javascript
// Usage
inst.loadData(allRows); // Load all 10,000 rows
inst.setFilter("status", "=", "active"); // Filters in memory
inst.render(); // Display page 1 of filtered results
```

### Server Mode

1. User loads page
2. TableManager listens to `tm:state-change` events
3. User sorts/filters/searches → dispatch event (no render)
4. Dev's event handler: fetch API with current state
5. Dev calls `inst.loadData(pageData, { total })` with response
6. TableInstance renders received page
7. Pagination reflects server's total

```javascript
// Setup
root.addEventListener("tm:state-change", async (e) => {
  const { state } = e.detail;
  const params = new URLSearchParams({
    page: state.pagination.pageIndex + 1,
    limit: state.pagination.pageSize,
    sort_col: state.sort.col,
    sort_dir: state.sort.dir,
    filters: JSON.stringify(state.filters),
    search: state.search,
  });
  const res = await fetch(`/api/categories?${params}`);
  const { rows, total } = await res.json();
  inst.loadData({
    rows,
    total,
    page: state.pagination.pageIndex + 1,
    limit: state.pagination.pageSize,
  });
});

// Initialize with server mode
inst.loadData({ rows: firstPage, total: 1000 }, { total: 1000 });
```

## 11. Vietnamese Normalization

```javascript
// Used in sorting and searching
function normalizeStr(str) {
  return String(str || '')
    .toLowerCase()
    .normalize('NFD')              // Decompose accents (á → a + ´)
    .replace(/[\u0300-\u036f]/g, '')  // Remove diacritics
    .replace(/đ/g, 'd');           // Special case: đ → d
}

// Example
normalizeStr('Danh mục') → 'danh muc'
normalizeStr('Từ Điển') → 'tu dien'

// Comparison in sort
const cmp = va.localeCompare(vb, 'vi', { numeric: true, sensitivity: 'accent' });
```

## 12. Filter Operators Reference

```javascript
const DEFAULT_FILTER_OPS = {
  text: ["contains", "=", "!="],
  number: ["=", "!=", ">", ">=", "<", "<="],
  date: ["=", "!=", ">", ">=", "<", "<="],
  select: ["=", "!="],
};

// Usage examples
setFilter("name", "contains", "Việt");
setFilter("age", ">=", "18");
setFilter("created", ">", "2026-01-01");
setFilter("status", "=", "active");
```

## 13. Integration with DropdownHandler & SelectHandler

```javascript
// TableRenderer registers filter dropdowns with DropdownHandler
// (if window.DropdownHandler.instance exists)
requestAnimationFrame(() =>
  window.DropdownHandler.instance.register(filterDropdownElement),
);

// Custom select controls store current value in ._currentValue
// and state in ._tmControls reference:
{
  (opSelect, // Operator <select>
    valueEl); // Value <input> or <select>
}

// When filter pill is removed, state is reset via these references
dropdown._tmControls.opSelect._currentValue = "=";
dropdown._tmControls.valueEl.value = "";
```

## 14. Configuration & Setup Examples

### Basic HTML Setup

```html
<div data-tm="products" data-tm-mode="client">
  <template data-tm-col="name" data-tm-label="Tên SP" data-tm-sortable>
    {{ row.name }}
  </template>
  <template
    data-tm-col="price"
    data-tm-label="Giá"
    data-tm-sortable
    data-tm-filter-type="number"
  >
    {{ currency(value) }}
  </template>
  <template data-tm-pagination></template>
</div>

<script type="application/json" data-tm-data="products">
  [
    { "name": "Product A", "price": 100000 },
    { "name": "Product B", "price": 250000 }
  ]
</script>

<script>
  // Initialize and configure
  TemplateEngine.registerHelper("currency", (v) =>
    new Intl.NumberFormat("vi-VN", {
      style: "currency",
      currency: "VND",
    }).format(v),
  );

  TableManager.instance.init();
  const inst = TableManager.get("products");
  inst.setPageSize(50);
</script>
```

### Server Mode Setup

```javascript
const root = document.querySelector('[data-tm="users"]');
root.dataset.tmMode = "server";

const inst = TableManager.get("users");

root.addEventListener("tm:state-change", async (e) => {
  const { state } = e.detail;

  const params = {
    page: state.pagination.pageIndex + 1,
    limit: state.pagination.pageSize,
    sort: state.sort.col ? `${state.sort.col}:${state.sort.dir}` : null,
    search: state.search,
  };

  // Add filter rules
  state.filters.forEach((f) => {
    params[`filter_${f.col}`] = `${f.op}:${f.value}`;
  });

  const query = new URLSearchParams(params);
  const res = await fetch(`/api/users?${query}`);
  const data = await res.json();

  inst.loadData(
    {
      rows: data.items,
      total: data.total,
      page: data.current_page,
      limit: data.per_page,
    },
    { total: data.total },
  );
});

// Load first page
inst.loadData(
  {
    rows: [],
    total: 0,
    page: 1,
    limit: 20,
  },
  { total: 0 },
);
```

## 15. Technical Debt & Future Work

- **Select Reset API**: Currently resetting by DOM manipulation. A formal `.reset()` method in `SelectHandler` would be cleaner.
- **Multi-column Sort**: Single-column only currently.
- **Export Feature**: No CSV/Excel export yet.
- **Responsive Tables**: "Card-view" mode for mobile not implemented.
- **Inline Sort Toggle**: UI doesn't cycle sort direction cleanly (asc → desc → off).
- **Filter Pills State Sync**: Manual `_tmControls` refs could be abstracted into a formal state manager.

## 16. File Reference

- `public/js/table/index.js` - Entry point, exports `TableManager`
- `public/js/table/table_manager.js` - `TableManager` singleton and `TableInstance` class
- `public/js/table/table_renderer.js` - `TableRenderer` DOM building and rendering
- `public/js/table/column_registry.js` - `ColumnRegistry` and `ColumnDef` column definitions
- `public/js/table/template_engine.js` - `TemplateEngine` template compilation and helpers
- `public/css/common.css` - Styles for `.tm-*` classes
