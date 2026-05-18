# Codebase Handover & Technical Architecture

Tài liệu này cung cấp cái nhìn tổng quan về kiến trúc dự án và hướng dẫn sử dụng các module lõi để tiếp tục phát triển.

## 1. Kiến trúc phân tầng (Layered Architecture)
Dự án áp dụng mô hình Service-Store pattern để tách biệt hoàn toàn Logic nghiệp vụ và Logic truy vấn dữ liệu.

- **Controllers** (`/controllers`): Tiếp nhận request, gọi Service và render View.
- **Services** (`/services`): Chứa logic nghiệp vụ chính. Thường sử dụng Interface để dễ dàng mock/test.
- **Stores** (`/stores`): Thực hiện các truy vấn SQL thô hoặc thông qua QueryBuilder. Không chứa logic nghiệp vụ.
- **Models** (`/models`): Định nghĩa cấu trúc dữ liệu (DTO).
- **Templates** (`/templates`): Chứa các file view (PHP thuần).

### Nguồn tham chiếu (References):
- Kiến trúc Controller: `includes/core/controller.php`
- Cấu trúc Store cơ sở: `includes/core/store.php`
- Ví dụ triển khai mẫu: `controllers/web/post_controller.php` -> `services/post_service.php` -> `stores/post_store.php`

---

## 2. Hệ thống Database & Schema

### 2.1 Table Builder (`includes/core/schema/table_builder.php`)
Dùng để định nghĩa cấu trúc bảng bằng ngôn ngữ lập trình (DSL) tương tự Laravel Blueprint.
```php
$table->id();
$table->string('title', 255)->nullable();
$table->text('content');
$table->integer('view_count')->default(0);
$table->foreign('category_id')->references('id')->on('categories');
$table->timestamps();
```

### 2.2 Migrations (`/database/migrations`)
Các file migration kế thừa `BaseMigration` và sử dụng `TableBuilder`.
- `up()`: Tạo/Sửa bảng.
- `down()`: Hủy bảng.

### 2.3 Query Builder (`includes/core/schema/query_builder.php`)
Cung cấp giao diện Fluent để xây dựng câu lệnh SQL an toàn qua PDO.

### Nguồn tham chiếu (References):
- Core Table Builder: `includes/core/schema/table_builder.php`
- Core Query Builder: `includes/core/schema/query_builder.php`
- Hệ thống chạy Migration: `includes/migration/migration_runner.php`
- Các file Migration mẫu: `database/migrations/`
```php
$rows = $this->db->table('posts')
    ->select('posts.*', 'categories.name as cat_name')
    ->join('categories', 'posts.category_id', '=', 'categories.id')
    ->where('posts.status', '=', 'published')
    ->orderBy('created_at', 'DESC')
    ->limit(10)
    ->get();
```

---

## 3. Các Module đặc thù

### 3.1 TabularMagic (Table Manager)
Hệ thống quản lý bảng phía Frontend, hỗ trợ Search, Filter, Pagination và Bulk Actions một cách khai báo.
- **Khai báo**: Sử dụng các thẻ `<template data-tm-col="...">` trong HTML.
- **Javascript**: Khởi tạo thông qua `TableManager` trong `public/js/table/`.
- **Layout**: Toolbar (Search/Filter) nằm ở Card Header, Pagination nằm ở Tfoot.

### 3.2 Block Editor
Hệ thống soạn thảo dạng khối (Gutenberg/Notion style).
- Nằm tại `public/js/editor/`.
- Hỗ trợ Undo/Redo (Command Pattern).
- Lưu trữ dưới dạng JSON Schema (Rich Text & Meta).

### 3.3 Drag-and-Drop (DnD) Module
Hệ thống quản lý kéo thả (Drag and Drop) dùng để sắp xếp các phần tử trên giao diện một cách trực quan (Ví dụ: sắp xếp carousel slide, menu items).
- Nằm tại `public/js/dnd.js`.
- Hỗ trợ sắp xếp phần tử và gửi request API cập nhật thứ tự (batch sorting) dưới nền (asynchronous).

### 3.4 File & Image Processing
Các module xử lý tệp tin và hình ảnh được tải lên.
- **UploadedFileHandler**: Xử lý upload file an toàn (`includes/files/uploaded_file_handler.php`).
- **ImageProcessor**: Xử lý, tối ưu hóa, thay đổi kích thước (resize/crop) hình ảnh (`includes/core/image_processor.php`).
- **XLSX Reader**: Đọc file dữ liệu Excel, hỗ trợ import (`includes/files/xlsx_reader.php`).

### 3.5 Router System
Hệ thống định tuyến cho cả Web và API.
- Nằm tại `includes/core/router.php`.
- Hỗ trợ route grouping, middlewares, và RESTful patterns.
- Định nghĩa các route tại `routes.php` (Web) và `api_routes.php` (API).

### Nguồn tham chiếu (References):
- Logic Table Manager: `public/js/table/table_manager.js`
- Logic Table Renderer: `public/js/table/table_renderer.js`
- Block Editor Core: `public/js/editor/editor.js`
- Block Editor UI: `public/js/editor/editor_ui.js`
- Drag and Drop Logic: `public/js/dnd.js`
- Router Logic: `includes/core/router.php`
- Image Processor Logic: `includes/core/image_processor.php`

---

## 4. UI Components & Design System
- **Base CSS**: Chứa hệ thống Design Tokens (màu sắc, spacing) và các component toàn cục (`.avatar`, `.btn`, `.tm-checkbox`).
- **Common CSS**: Chứa các component phức tạp (`.dropdown`, `.popover`, `.tm-table`).
- **PHP Components**: Các phần dùng chung nằm trong `templates/components/`.

---

## 5. Quy tắc phát triển (Coding Standards)
1. **Dependency Injection**: Luôn inject Service vào Controller và Store vào Service thông qua Constructor.
2. **Naming**:
   - Biến private: `$_variableName`.
   - File controller/service/store: `snake_case.php` (hoặc PascalCase tùy folder, ưu tiên nhất quán).
3. **Database**: Tuyệt đối không viết SQL trực tiếp trong Controller. Phải thông qua Store.
4. **Security**: Luôn sử dụng `csrf_field()` trong form và QueryBuilder (PDO) để chống SQL Injection.

### Nguồn tham chiếu (References):
- Global Helpers: `includes/helpers.php`
- Session System: `includes/core/session/session.php`
- Request Handling: `includes/core/request/request.php`
- CSRF Logic: `includes/core/session/session.php#L46`
