# Hand-off Context: TableManager Administrative System

## 1. Overview
The `TableManager` is a client-side and server-side capable data table system designed for the administrative dashboard. It follows a modular architecture to handle columns, sorting, filtering, and pagination independently.

- **Root Directory**: `public/js/table/`
- **Entry Point**: `public/js/table/index.js`
- **Main Controller**: `table_manager.js` (Class `TableInstance`)

## 2. Core Architecture
The system is divided into several controllers and a dedicated renderer:

| Class | Responsibility |
| :--- | :--- |
| `TableInstance` | Orchestrates all components, manages state, and handles the lifecycle (`init`, `render`, `reload`). |
| `TableRenderer` | Handles all DOM generation. Uses `TemplateEngine` for custom cell rendering. |
| `ColumnRegistry` | Parses `<template>` elements from the HTML to define column behaviors (sortable, filterable, width, etc.). |
| `SortController` | Manages sorting state (`col`, `dir`) and provides a client-side `comparator`. |
| `FilterController` | Manages search keywords and advanced rules. Handles Vietnamese normalization. |
| `PaginationController` | Handles page logic (current, total, windowing) and URL parameter sync. |
| `DataAdapter` | Abstract layer for data fetching. Supports `server` (AJAX) and `client` (inline JSON) modes. |

## 3. UI & Integration Patterns
- **Custom Components**:
    - **Dropdowns**: Managed by `DropdownHandler` (portal-based). Registered via `DropdownHandler.instance.register()`.
    - **Selects**: Filter panels use a custom `SelectHandler`. Controls are stored in `element._tmControls` for external state synchronization.
- **Filter Pills**: Active filters are rendered as `badge` components in the toolbar. Clicking the remove button (`.tm-filter-pill__remove`) triggers a full sync that resets the corresponding dropdown UI.
- **Icons**: Uses Font Awesome (`fa-solid`). Sort indicators use `fa-chevron-up` and `fa-chevron-down`.

## 4. Current State & Recent Improvements
- **Vietnamese Support**: `FilterController` and `SortController` now use normalization (`NFD` normalization + regex) and `localeCompare('vi')` for accurate sorting and searching.
- **State Sync**: Fixed a major issue where removing a filter pill wouldn't reset the dropdown's internal inputs. Now uses `_tmControls` references to clear state.
- **Redundancy Fix**: Cleaned up `TableRenderer` to prevent race conditions between `render()` and `reload()`. Controllers now correctly manage their own update cycles via callbacks.
- **Design System**: Integrated with the dashboard's CSS variables (`--primary`, `--border`, etc.) and the `badge` component.

## 5. Technical Details for Continuation
### Data Attributes
- `[data-tm]`: The ID of the table.
- `[data-tm-mode]`: `server` or `client`.
- `[data-tm-src]`: AJAX endpoint for server mode.
- `[data-tm-sortable]`: Presence enables sorting on a column template.
- `[data-tm-col]`: Column key, must match the data object property.

### Key Logic: Vietnamese Normalization
```javascript
static #normalize(str) {
  return String(str || '')
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/đ/g, 'd');
}
```

## 6. Technical Debt & Future Work
- **Select Reset API**: Currently, resetting `SelectHandler` components is done by manual DOM manipulation of the `.select__value` container. A formal `.reset()` method in `SelectHandler` would be better.
- **Multi-column Sort**: The `SortController` currently only supports single-column sorting.
- **Export Feature**: No current support for exporting table data to CSV/Excel.
- **Responsive Tables**: The `tm-scroll` wrapper handles horizontal overflow, but a "card-view" for mobile is not yet implemented.

## 7. File Reference
- `public/js/table/table_manager.js`: Core logic.
- `public/js/table/table_renderer.js`: UI logic.
- `public/js/table/filter_controller.js`: Search/Filtering logic.
- `public/js/table/sort_controller.js`: Sorting logic.
- `public/css/common.css`: Main styles (look for `.tm-*` classes).
