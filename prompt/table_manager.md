# TableManager - Hệ Thống Quản Lý Bảng Dữ Liệu

## Tổng Quan

**TableManager** là một hệ thống quản lý bảng dữ liệu (Data Table) cho phép xử lý filter, sort, search, pagination và row selection. Hệ thống hỗ trợ hai chế độ hoạt động:

- **Client Mode**: TableManager tự xử lý filter/sort/paginate toàn bộ dữ liệu trong bộ nhớ (dùng cho dataset nhỏ, ≤ vài nghìn rows)
- **Server Mode**: TableManager dispatch event khi state thay đổi, dev tự gọi API để fetch dữ liệu theo điều kiện

---

## 1. Kiến Trúc Hệ Thống

### 1.1 Cấu Trúc Module

```
public/js/table/
├── table_manager.js       # Lõi: quản lý state, API công khai
├── table_renderer.js      # Render UI, điều khiển DOM
├── column_registry.js     # Đăng ký và quản lý định nghĩa cột
├── template_engine.js     # Biên dịch template với helper functions
└── index.js               # Entry point, expose global window.TableManager
```

### 1.2 Luồng Dữ Liệu

```
┌─────────────────────────────────────────────────────────────┐
│ HTML [data-tm] Element (định nghĩa bảng + cấu hình)          │
└─────────────────┬───────────────────────────────────────────┘
                  │
                  ▼
         ┌────────────────────┐
         │ TableManager.init()│ (Auto-trigger trên DOMContentLoaded)
         └────────┬───────────┘
                  │
                  ▼
      ┌───────────────────────────────┐
      │ TableInstance (1 bảng = 1 inst)│
      │ - quản lý state               │
      │ - cung cấp public API          │
      └────┬──────────────────────────┘
           │
           ├─────────────────┬─────────────────┐
           ▼                 ▼                 ▼
      TableRenderer    ColumnRegistry  TemplateEngine
      (render UI)      (cột definitions) (compile templates)
           │
           ▼
      [DOM] Table được render
      - Search, Filter, Sort
      - Pagination
      - Row Selection
      - Events dispatch
```

### 1.3 State Model

Mỗi `TableInstance` duy trì một state nội bộ:

```javascript
{
  // Sắp xếp
  sort: {
    col: string|null,      // Tên cột
    dir: 'asc'|'desc'|null // Hướng sắp xếp
  },

  // Bộ lọc
  filters: [
    { col: string, op: string, value: string },  // VD: { col: 'status', op: '=', value: 'active' }
    // ...
  ],

  // Tìm kiếm toàn cục
  search: string,

  // Phân trang
  pagination: {
    pageIndex: number,  // 0-based
    pageSize: number    // Số dòng/trang
  },

  // Chọn hàng
  rowSelection: Set<string>,  // Tập ID được chọn

  // Cấu hình chế độ
  mode: 'client'|'server',
  totalRows: number           // Tổng số hàng (server mode)
}
```

---

## 2. Định Nghĩa Cột (Column Definition)

### 2.1 Cấu Trúc Cột

```javascript
class ColumnDef {
  key; // string - Khóa trường trong row data
  label; // string - Tiêu đề hiển thị
  sortable; // boolean - Cho phép sắp xếp?
  width; // string|null - Độ rộng CSS (VD: '100px', '15%')
  align; // 'left'|'center'|'right' - Căn lề (mặc định: 'left')
  filterType; // string|null - Loại bộ lọc: 'text'|'number'|'date'|'select'
  filterOps; // string[] - Danh sách toán tử lọc được phép
  filterOptions; // array|null - Danh sách option cho select filter
  render; // Function|null - Hàm render DOM tùy chỉnh
}
```

### 2.2 Định Nghĩa Cột Trong HTML

Sử dụng `<template data-tm-col>` bên trong element `[data-tm]`:

```html
<div data-tm="customers" data-tm-mode="client">
  <!-- Cột: ID (khóa chính, không hiển thị) -->
  <template data-tm-col="id" data-tm-label="ID"></template>

  <!-- Cột: Tên (sortable, searchable) -->
  <template data-tm-col="name" data-tm-label="Tên" data-tm-sortable></template>

  <!-- Cột: Email (sortable, có bộ lọc text) -->
  <template
    data-tm-col="email"
    data-tm-label="Email"
    data-tm-sortable
    data-tm-filter-type="text"
  ></template>

  <!-- Cột: Tuổi (bộ lọc số) -->
  <template
    data-tm-col="age"
    data-tm-label="Tuổi"
    data-tm-sortable
    data-tm-filter-type="number"
    data-tm-filter-ops="=,!=,>,>=,<,<="
  ></template>

  <!-- Cột: Status (bộ lọc select) -->
  <template
    data-tm-col="status"
    data-tm-label="Trạng Thái"
    data-tm-sortable
    data-tm-filter-type="select"
    data-tm-filter-options='[
      {"value":"active","label":"Đang Hoạt Động"},
      {"value":"inactive","label":"Không Hoạt Động"}
    ]'
  ></template>

  <!-- Cột: Ngày Tạo (bộ lọc ngày) -->
  <template
    data-tm-col="created_at"
    data-tm-label="Ngày Tạo"
    data-tm-sortable
    data-tm-filter-type="date"
  ></template>

  <!-- Cột: Action (render tùy chỉnh) -->
  <template
    data-tm-col="action"
    data-tm-label="Thao Tác"
    data-tm-align="center"
  >
    <button class="btn btn-sm" onclick="editRow({{ row.id }})">Sửa</button>
    <button class="btn btn-sm" onclick="deleteRow({{ row.id }})">Xóa</button>
  </template>
</div>
```

### 2.3 Các Attribute HTML

| Attribute                | Giá Trị                    | Mô Tả                        |
| ------------------------ | -------------------------- | ---------------------------- |
| `data-tm-col`            | string                     | Khóa trường (bắt buộc)       |
| `data-tm-label`          | string                     | Tiêu đề cột                  |
| `data-tm-sortable`       | flag                       | Cho phép sắp xếp cột         |
| `data-tm-width`          | CSS                        | Độ rộng (VD: '100px', '15%') |
| `data-tm-align`          | left\|center\|right        | Căn lề                       |
| `data-tm-filter-type`    | text\|number\|date\|select | Loại bộ lọc                  |
| `data-tm-filter-ops`     | comma-separated            | Danh sách toán tử lọc        |
| `data-tm-filter-options` | JSON                       | Option cho select filter     |

---

## 3. Template Engine (Render Tùy Chỉnh)

### 3.1 Cú Pháp Template

Dùng `{{expression}}` bên trong `<template data-tm-col>`:

```html
<!-- Hiển thị giá trị đơn giản -->
<template data-tm-col="status"> {{ value }} </template>

<!-- Truy cập thuộc tính row -->
<template data-tm-col="name">
  <strong>{{ row.name }}</strong>
</template>

<!-- Dùng helper function -->
<template data-tm-col="created_at"> {{ formatDate(row.created_at) }} </template>

<!-- Logic với fallback -->
<template data-tm-col="phone"> {{ row.phone || 'Chưa có' }} </template>

<!-- HTML động -->
<template data-tm-col="status">
  <span
    class="badge"
    data-variant="{{ row.status === 'active' ? 'success' : 'danger' }}"
  >
    {{ row.status === 'active' ? 'Hoạt Động' : 'Không Hoạt Động' }}
  </span>
</template>
```

### 3.2 Đăng Ký Helper Functions

Helper functions được dùng trong template:

```javascript
// Đăng ký trước khi compile template (trước init bảng)
TemplateEngine.registerHelper("formatDate", (val) => {
  return dayjs(val).format("DD/MM/YYYY");
});

TemplateEngine.registerHelper("currency", (val) => {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(val);
});

TemplateEngine.registerHelper("statusLabel", (status) => {
  const labels = {
    active: "Đang Hoạt Động",
    inactive: "Không Hoạt Động",
    pending: "Chờ Duyệt",
  };
  return labels[status] || status;
});

// Dùng trong template:
// {{ formatDate(row.created_at) }}
// {{ currency(row.salary) }}
// {{ statusLabel(row.status) }}
```

### 3.3 Biên Dịch Thủ Công

Có thể compile template thủ công nếu cần:

```javascript
const tpl = document.querySelector('template[data-tm-col="action"]');
const renderFn = TemplateEngine.compile(tpl);

// Sử dụng
const row = { id: 1, name: "John" };
const value = "some_value";
const fragment = renderFn(row, value); // Returns DocumentFragment
element.appendChild(fragment);
```

---

## 4. Cấu Hình Bảng

### 4.1 Attribute của Element [data-tm]

| Attribute                | Giá Trị        | Mô Tả                                |
| ------------------------ | -------------- | ------------------------------------ |
| `data-tm`                | string         | ID bảng (bắt buộc)                   |
| `data-tm-mode`           | client\|server | Chế độ hoạt động (mặc định: client)  |
| `data-tm-selectable`     | flag           | Bật chọn hàng                        |
| `data-tm-id-key`         | string         | Tên field ID hàng (mặc định: 'id')   |
| `data-tm-searchable`     | flag           | Bật tìm kiếm toàn cục                |
| `data-tm-search-target`  | selector       | Selector input tìm kiếm bên ngoài    |
| `data-tm-toolbar-target` | selector       | Selector container toolbar bên ngoài |
| `data-tm-footer-target`  | selector       | Selector container footer bên ngoài  |

### 4.2 Ví Dụ Cấu Hình Client Mode

```html
<div
  data-tm="products"
  data-tm-mode="client"
  data-tm-selectable
  data-tm-id-key="product_id"
  data-tm-searchable
>
  <!-- Templates cột -->
  <template
    data-tm-col="name"
    data-tm-label="Tên Sản Phẩm"
    data-tm-sortable
  ></template>
  <template
    data-tm-col="price"
    data-tm-label="Giá"
    data-tm-sortable
    data-tm-filter-type="number"
  ></template>
  <template data-tm-col="category" data-tm-label="Danh Mục"></template>
  <!-- ... -->
</div>

<!-- Dữ liệu inline (tùy chọn) -->
<script type="application/json" data-tm-data="products">
  [
    {
      "product_id": 1,
      "name": "Laptop",
      "price": 15000000,
      "category": "Electronics"
    },
    {
      "product_id": 2,
      "name": "Mouse",
      "price": 250000,
      "category": "Accessories"
    }
  ]
</script>

<script>
  // Dữ liệu được tự load từ inline data, hoặc:
  const tm = TableManager.get("products");
  tm.loadData([
    { product_id: 1, name: "Laptop", price: 15000000, category: "Electronics" },
    { product_id: 2, name: "Mouse", price: 250000, category: "Accessories" },
  ]);
</script>
```

### 4.3 Ví Dụ Cấu Hình Server Mode

```html
<div
  data-tm="users"
  data-tm-mode="server"
  data-tm-selectable
  data-tm-searchable
>
  <template data-tm-col="id" data-tm-label="ID"></template>
  <template data-tm-col="name" data-tm-label="Tên" data-tm-sortable></template>
  <template
    data-tm-col="email"
    data-tm-label="Email"
    data-tm-sortable
  ></template>
  <template
    data-tm-col="role"
    data-tm-label="Vai Trò"
    data-tm-filter-type="select"
    data-tm-filter-options='[{"value":"admin","label":"Admin"},{"value":"user","label":"User"}]'
  ></template>
  <!-- Pagination container -->
  <template data-tm-pagination></template>
</div>

<script>
  const tm = TableManager.get("users");

  // Lắng nghe state-change event để fetch API
  tm.root.addEventListener("tm:state-change", async (e) => {
    const { state } = e.detail;

    // Gọi API với điều kiện từ state
    const response = await fetch("/api/users", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        page: state.pagination.pageIndex + 1,
        limit: state.pagination.pageSize,
        search: state.search,
        filters: state.filters,
        sort: state.sort,
      }),
    });

    const data = await response.json();

    // Load dữ liệu với tổng số từ server
    tm.loadData({
      rows: data.records,
      total: data.total,
      page: state.pagination.pageIndex + 1,
      limit: state.pagination.pageSize,
    });
  });

  // Trigger fetch lần đầu
  tm.loadData({ rows: [], total: 0 });
</script>
```

---

## 5. Public API

### 5.1 Lấy Instance

```javascript
// Bằng Singleton Pattern
const tm = TableManager.instance;

// Hoặc bằng ID
const tm = TableManager.get("table-id");

// Truy cập từ root element
const tm = TableManager.get(rootElement.dataset.tm);
```

### 5.2 Tải Dữ Liệu

```javascript
// Client mode: truyền toàn bộ dataset
tm.loadData([
  { id: 1, name: "John", email: "john@example.com" },
  { id: 2, name: "Jane", email: "jane@example.com" },
]);

// Server mode: truyền một trang + tổng số hàng
tm.loadData({
  rows: [
    { id: 1, name: "John", email: "john@example.com" },
    { id: 2, name: "Jane", email: "jane@example.com" },
  ],
  total: 245, // Tổng số hàng trên server
  page: 1, // Trang hiện tại (1-based)
  limit: 20, // Số hàng/trang
});

// Shorthand: chỉ rows (hữu ích khi nạp lần đầu)
tm.loadData([...rows]);
```

### 5.3 Filter / Search

```javascript
// Đặt bộ lọc cho một cột
// setFilter(colName, operator, value)
tm.setFilter("status", "=", "active");
tm.setFilter("age", ">=", "18");
tm.setFilter("created_at", ">", "2025-01-01");

// Xóa bộ lọc của một cột
tm.clearFilter("status");

// Xóa toàn bộ bộ lọc và search
tm.clearFilters();

// Tìm kiếm toàn cục
tm.setSearch("john");

// Lấy danh sách hàng hiện tại (đã filter/sort)
const rows = tm.getVisibleRows();

// Lấy state hiện tại
const state = tm.getState();
console.log(state.filters);
console.log(state.search);
```

### 5.4 Sắp Xếp

```javascript
// Toggle sort trên cột (asc → desc → off)
tm.setSort("name");

// Lấy state sort
const state = tm.getState();
console.log(state.sort); // { col: 'name', dir: 'asc' }
```

### 5.5 Phân Trang

```javascript
// Đến trang tiếp theo
tm.nextPage();

// Quay lại trang trước
tm.prevPage();

// Đến trang cụ thể (0-based index)
tm.setPageIndex(2); // Trang 3

// Đặt số hàng/trang (reset về trang 1)
tm.setPageSize(50);

// Kiểm tra có thể chuyển trang?
if (tm.canNextPage()) tm.nextPage();
if (tm.canPrevPage()) tm.prevPage();

// Lấy tổng số trang
const totalPages = tm.getPageCount();
```

### 5.6 Row Selection

```javascript
// Bật chọn hàng trong HTML (nếu chưa bật)
// <div data-tm="..." data-tm-selectable>

// Lấy danh sách ID hàng được chọn
const selectedIds = tm.getRowSelection();
console.log(selectedIds); // ['1', '3', '5']

// Xóa toàn bộ lựa chọn
tm.clearSelection();

// Kiểm tra hàng có được chọn không?
const isSelected = tm.hasRowSelection("3");

// Toggle hàng (thêm/xóa từ selection)
tm.toggleRowSelection("3", true);
```

### 5.7 Static Methods

```javascript
// Lấy instance theo ID
TableManager.get("table-id");

// Đặt bộ lọc (shorthand)
TableManager.setFilter("table-id", "status", "=", "active");

// Cập nhật filter options động
TableManager.setFilterOptions("table-id", "status", [
  { value: "new", label: "Mới" },
  { value: "active", label: "Hoạt Động" },
  { value: "archived", label: "Lưu Trữ" },
]);

// Lấy danh sách hàng được chọn
const ids = TableManager.getRowSelection("table-id");

// Xóa lựa chọn
TableManager.clearSelection("table-id");

// Tải dữ liệu
TableManager.loadData("table-id", [...rows]);
```

---

## 6. Events

### 6.1 Các Event Được Dispatch

Lắng nghe event trên element `[data-tm]`:

```javascript
const tm = TableManager.get("table-id");
const root = tm.root;

// State thay đổi (server mode: để fetch API)
root.addEventListener("tm:state-change", (e) => {
  const { reason, state } = e.detail;
  console.log(`State thay đổi: ${reason}`, state);
  // reason: 'sort' | 'filter' | 'search' | 'pagination'
});

// Render xong
root.addEventListener("tm:render", (e) => {
  const { tableId, visibleRows, totalRows, state } = e.detail;
  console.log(`Render xong: ${totalRows} hàng hiển thị`);
});

// Selection thay đổi
root.addEventListener("tm:selection-change", (e) => {
  const { rowSelection } = e.detail;
  console.log("Hàng được chọn:", rowSelection);
});

// Filter được áp dụng
root.addEventListener("tm:filter:apply", (e) => {
  const { column, operator, value } = e.detail;
  console.log(`Filter: ${column} ${operator} ${value}`);
});

// Filter bị xóa
root.addEventListener("tm:filter:clear", (e) => {
  const { column } = e.detail;
  console.log(`Xóa filter: ${column}`);
});

// Tìm kiếm thay đổi
root.addEventListener("tm:search:change", (e) => {
  const { search } = e.detail;
  console.log(`Search: ${search}`);
});

// Pagination thay đổi
root.addEventListener("tm:pagination:change", (e) => {
  const { page, totalPages } = e.detail;
  console.log(`Trang ${page}/${totalPages}`);
});
```

### 6.2 Event Detail Structure

| Event                  | Detail Properties                              | Mô Tả                                          |
| ---------------------- | ---------------------------------------------- | ---------------------------------------------- |
| `tm:state-change`      | `reason`, `state`                              | State thay đổi (filter/sort/search/pagination) |
| `tm:render`            | `tableId`, `visibleRows`, `totalRows`, `state` | Render xong                                    |
| `tm:selection-change`  | `rowSelection`                                 | Selection thay đổi (hàng được chọn)            |
| `tm:filter:apply`      | `column`, `operator`, `value`                  | Filter được áp dụng                            |
| `tm:filter:clear`      | `column`                                       | Filter bị xóa                                  |
| `tm:search:change`     | `search`                                       | Tìm kiếm thay đổi                              |
| `tm:pagination:change` | `page`, `totalPages`                           | Phân trang thay đổi                            |

---

## 7. Filter Operators

### 7.1 Danh Sách Toán Tử

| Toán Tử    | Kiểu Dữ Liệu               | Mô Tả             |
| ---------- | -------------------------- | ----------------- |
| `contains` | text                       | Chứa chuỗi        |
| `=`        | text, number, date, select | Bằng              |
| `!=`       | text, number, date, select | Không bằng        |
| `>`        | number, date               | Lớn hơn           |
| `>=`       | number, date               | Lớn hơn hoặc bằng |
| `<`        | number, date               | Nhỏ hơn           |
| `<=`       | number, date               | Nhỏ hơn hoặc bằng |

### 7.2 Toán Tử Mặc Định

Ánh xạ tự động dựa trên `filterType`:

```javascript
DEFAULT_FILTER_OPS = {
  text: ["contains", "=", "!="],
  number: ["=", "!=", ">", ">=", "<", "<="],
  date: ["=", "!=", ">", ">=", "<", "<="],
  select: ["=", "!="],
};
```

### 7.3 Override Toán Tử

```html
<!-- Chỉ định toán tử cho cột -->
<template
  data-tm-col="price"
  data-tm-filter-type="number"
  data-tm-filter-ops=">,>=,<,<="
></template>
```

---

## 8. Normalization & Search

### 8.1 Chuẩn Hóa Chuỗi Tiếng Việt

TableManager tự động chuẩn hóa chuỗi để tìm kiếm không dấu:

```javascript
// Input: "Việt Nam"
// Normalized: "viet nam"

// Input: "Đặc Biệt"
// Normalized: "dac biet"

// Search không dấu hoạt động tự động
tm.setSearch("dac biet"); // Sẽ tìm thấy "Đặc Biệt"
```

### 8.2 Searchable Keys

- Client mode: Tìm kiếm trên tất cả cột (có `key`) trừ `_checkbox`
- Server mode: Dev tự xử lý tìm kiếm trên API

---

## 9. Ví Dụ Hoàn Chỉnh

### 9.1 Client Mode - Bảng Sinh Viên

```html
<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" href="path/to/styles.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
  </head>
  <body>
    <!-- Bảng sinh viên -->
    <div
      data-tm="students"
      data-tm-mode="client"
      data-tm-selectable
      data-tm-id-key="mssv"
      data-tm-searchable
    >
      <template
        data-tm-col="mssv"
        data-tm-label="MSSV"
        data-tm-sortable
      ></template>

      <template
        data-tm-col="name"
        data-tm-label="Tên"
        data-tm-sortable
      ></template>

      <template
        data-tm-col="email"
        data-tm-label="Email"
        data-tm-sortable
      ></template>

      <template
        data-tm-col="major"
        data-tm-label="Chuyên Ngành"
        data-tm-filter-type="select"
        data-tm-filter-options='[
        {"value":"IT","label":"Công Nghệ Thông Tin"},
        {"value":"BIZ","label":"Kinh Doanh"},
        {"value":"MKT","label":"Marketing"}
      ]'
      ></template>

      <template
        data-tm-col="status"
        data-tm-label="Trạng Thái"
        data-tm-filter-type="select"
        data-tm-filter-options='[
        {"value":"active","label":"Đang Học"},
        {"value":"graduated","label":"Tốt Nghiệp"},
        {"value":"dropped","label":"Bỏ Học"}
      ]'
      >
        <span
          class="badge"
          data-variant="{{ row.status === 'active' ? 'success' : row.status === 'graduated' ? 'info' : 'danger' }}"
        >
          {{ row.status === 'active' ? 'Đang Học' : row.status === 'graduated' ?
          'Tốt Nghiệp' : 'Bỏ Học' }}
        </span>
      </template>

      <template
        data-tm-col="action"
        data-tm-label="Thao Tác"
        data-tm-align="center"
      >
        <button class="btn btn-sm" onclick="editStudent('{{ row.mssv }}')">
          Sửa
        </button>
        <button
          class="btn btn-sm btn-danger"
          onclick="deleteStudent('{{ row.mssv }}')"
        >
          Xóa
        </button>
      </template>

      <!-- Pagination -->
      <template data-tm-pagination></template>
    </div>

    <!-- Dữ liệu inline (hoặc fetch từ API) -->
    <script type="application/json" data-tm-data="students">
      [
        {
          "mssv": "2020001",
          "name": "Nguyễn Văn A",
          "email": "a@uni.edu.vn",
          "major": "IT",
          "status": "active"
        },
        {
          "mssv": "2020002",
          "name": "Trần Thị B",
          "email": "b@uni.edu.vn",
          "major": "BIZ",
          "status": "graduated"
        },
        {
          "mssv": "2020003",
          "name": "Lê Văn C",
          "email": "c@uni.edu.vn",
          "major": "MKT",
          "status": "active"
        }
      ]
    </script>

    <script src="path/to/table_manager.js"></script>
    <script>
      // Lắng nghe khi hàng được chọn
      const root = document.querySelector('[data-tm="students"]');
      root.addEventListener("tm:selection-change", (e) => {
        const selectedIds = e.detail.rowSelection;
        console.log("Sinh viên được chọn:", selectedIds);
      });

      // Function thao tác
      function editStudent(mssv) {
        alert(`Sửa sinh viên: ${mssv}`);
      }
      function deleteStudent(mssv) {
        alert(`Xóa sinh viên: ${mssv}`);
      }
    </script>
  </body>
</html>
```

### 9.2 Server Mode - Bảng User Từ API

```html
<div
  data-tm="users"
  data-tm-mode="server"
  data-tm-selectable
  data-tm-searchable
>
  <template data-tm-col="id" data-tm-label="ID"></template>
  <template
    data-tm-col="username"
    data-tm-label="Tên Đăng Nhập"
    data-tm-sortable
  ></template>
  <template
    data-tm-col="email"
    data-tm-label="Email"
    data-tm-sortable
  ></template>
  <template
    data-tm-col="role"
    data-tm-label="Vai Trò"
    data-tm-filter-type="select"
    data-tm-filter-options='[
      {"value":"admin","label":"Admin"},
      {"value":"moderator","label":"Moderator"},
      {"value":"user","label":"Người Dùng"}
    ]'
  ></template>
  <template data-tm-col="created_at" data-tm-label="Tạo Lúc"></template>
  <template data-tm-pagination></template>
</div>

<script>
  // Đăng ký helper để format ngày
  TemplateEngine.registerHelper("formatDate", (val) => {
    if (!val) return "N/A";
    return new Date(val).toLocaleDateString("vi-VN");
  });

  const tm = TableManager.get("users");

  // Hàm fetch API
  async function fetchUsers() {
    const state = tm.getState();

    const response = await fetch("/api/users", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        page: state.pagination.pageIndex + 1,
        limit: state.pagination.pageSize,
        search: state.search,
        filters: state.filters,
        sort: state.sort,
      }),
    });

    const data = await response.json();
    return data;
  }

  // Lắng nghe state-change để fetch API
  tm.root.addEventListener("tm:state-change", async (e) => {
    try {
      const data = await fetchUsers();
      tm.loadData({
        rows: data.users,
        total: data.total,
        page: data.page,
        limit: data.limit,
      });
    } catch (err) {
      console.error("Lỗi fetch API:", err);
    }
  });

  // Fetch lần đầu
  (async () => {
    const data = await fetchUsers();
    tm.loadData({ rows: data.users, total: data.total });
  })();
</script>
```

---

## 10. Tips & Best Practices

### 10.1 Performance

- **Client Mode**: Dùng cho dataset ≤ 10,000 rows. Với dataset lớn hơn, dùng Server Mode
- **Server Mode**: Chỉ render một trang dữ liệu, giảm tải DOM
- **Virtual Scrolling**: Nếu cần hiển thị hàng nghìn rows, xem xét implement thêm

### 10.2 Searchable Columns

- Chỉ những cột có `data-tm-col` mới được tìm kiếm
- Cột `_checkbox` tự động loại trừ khỏi tìm kiếm

### 10.3 Filter Best Practices

```javascript
// ✓ Tốt: Xóa toàn bộ filter khi reset
tm.clearFilters();

// ✓ Tốt: Check filter trước apply
const hasFilter = tm.getState().filters.length > 0;

// ✗ Tránh: Spam setFilter nhiều lần liên tiếp
// for (let i = 0; i < 100; i++) tm.setFilter(...);  // ✗ BAD

// ✓ Tốt: Batch filter thay vào
filters.forEach((f) => tm.setFilter(f.col, f.op, f.value));
```

### 10.4 Row Selection

```javascript
// ✓ Tốt: Dùng event để lắng nghe selection thay đổi
tm.root.addEventListener("tm:selection-change", (e) => {
  const selected = e.detail.rowSelection;
  // Xử lý...
});

// ✓ Tốt: Clear selection trước bulk action
TableManager.clearSelection("table-id");
```

### 10.5 Template Rendering

```html
<!-- ✓ Tốt: Escape HTML tự động -->
<template data-tm-col="comment">
  {{ row.comment }}
  <!-- An toàn -->
</template>

<!-- ✓ Tốt: Dùng helper function -->
<template data-tm-col="created_at"> {{ formatDate(row.created_at) }} </template>

<!-- ✗ Tránh: Dùng innerHTML trực tiếp trong template -->
<!-- Không cần, Template Engine tự escape -->
```

### 10.6 Debugging

```javascript
// Lấy state đầy đủ
console.log(tm.getState());

// Lấy dữ liệu hiện tại
console.log(tm.getVisibleRows());

// Kiểm tra instance
console.log(TableManager.get("table-id"));

// Monitor event
document
  .querySelector('[data-tm="table-id"]')
  .addEventListener("tm:render", (e) => {
    console.log("Render event:", e.detail);
  });
```

---

## 11. API Reference (Ringkasan)

### Instance Methods

| Method                             | Tham Số             | Trả Về   | Mô Tả                    |
| ---------------------------------- | ------------------- | -------- | ------------------------ |
| `loadData(payload)`                | rows\[], total?     | void     | Tải dữ liệu              |
| `setData(rows)`                    | rows[]              | void     | Đặt dữ liệu              |
| `render()`                         | -                   | void     | Re-render bảng           |
| `setFilter(col, op, value)`        | string, string, any | void     | Áp dụng filter           |
| `clearFilter(col)`                 | string              | void     | Xóa filter cột           |
| `clearFilters()`                   | -                   | void     | Xóa tất cả filter        |
| `setSearch(query)`                 | string              | void     | Tìm kiếm                 |
| `setSort(col)`                     | string              | void     | Toggle sort              |
| `setPageIndex(index)`              | number              | void     | Đến trang                |
| `setPageSize(size)`                | number              | void     | Đặt size/trang           |
| `nextPage()`                       | -                   | void     | Trang tiếp               |
| `prevPage()`                       | -                   | void     | Trang trước              |
| `getPageCount()`                   | -                   | number   | Tổng trang               |
| `canNextPage()`                    | -                   | boolean  | Có trang tiếp?           |
| `canPrevPage()`                    | -                   | boolean  | Có trang trước?          |
| `getVisibleRows()`                 | -                   | object[] | Hàng hiện tại            |
| `getState()`                       | -                   | object   | State snapshot           |
| `getRowSelection()`                | -                   | string[] | Hàng được chọn           |
| `toggleRowSelection(id, selected)` | string, boolean     | void     | Toggle selection         |
| `hasRowSelection(id)`              | string              | boolean  | Hàng được chọn?          |
| `clearSelection()`                 | -                   | void     | Xóa selection            |
| `updateHeaderCheckbox()`           | -                   | void     | Cập nhật checkbox header |

### Static Methods

| Method                                                 | Tham Số     | Trả Về        |
| ------------------------------------------------------ | ----------- | ------------- |
| `TableManager.instance`                                | -           | TableInstance |
| `TableManager.get(id)`                                 | string      | TableInstance |
| `TableManager.setFilter(tableId, col, op, value)`      | ...         | void          |
| `TableManager.setFilterOptions(tableId, col, options)` | ...         | void          |
| `TableManager.getRowSelection(tableId)`                | string      | string[]      |
| `TableManager.clearSelection(tableId)`                 | string      | void          |
| `TableManager.loadData(tableId, payload)`              | string, any | void          |

---

## 12. Troubleshooting

### Bảng không hiển thị

- ✓ Kiểm tra `[data-tm]` element có trong DOM?
- ✓ Kiểm tra `<template data-tm-col>` có định nghĩa cột?
- ✓ Kiểm tra `table_manager.js` được load?
- ✓ Kiểm tra browser console có lỗi?

### Filter không hoạt động

- ✓ Kiểm tra `data-tm-filter-type` được set?
- ✓ Kiểm tra `DropdownHandler` được load (cho UI dropdown)?
- ✓ Kiểm tra filter operator có hợp lệ?

### Event không trigger

- ✓ Kiểm tra listener được attach vào element `[data-tm]`?
- ✓ Kiểm tra event name đúng (VD: `tm:render` không phải `tm-render`)?

### Template render sai

- ✓ Kiểm tra `{{}}` syntax đúng?
- ✓ Kiểm tra helper function được đăng ký trước init?
- ✓ Kiểm tra browser console có lỗi parse?

### Row selection không hoạt động

- ✓ Kiểm tra `data-tm-selectable` có được set?
- ✓ Kiểm tra `data-tm-id-key` match với field ID trong data?
- ✓ Kiểm tra row có field ID (mặc định: `id`)?
