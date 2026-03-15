# Website Khoa CNTT - Trường CĐKT Cao Thắng

Đây là dự án Website cho Khoa CNTT của Trường Cao Đẳng Kỹ Thuật Cao Thắng

Tài liệu này cung cấp hướng dẫn cài đặt môi trường, tổng quan kiến trúc, cấu trúc thư mục và quy chuẩn lập trình.

---

## Mục lục
1. [Công nghệ sử dụng](#công-nghệ-sử-dụng)
2. [Cấu trúc Project MVC](#cấu-trúc-project-mvc)
3. [Hướng dẫn cài đặt & chạy dự án](#hướng-dẫn-cài-đặt--chạy-dự-án)
4. [Hướng dẫn cấu hình Database](#hướng-dẫn-cấu-hình-database)
5. [Quy ước lập trình (Coding Conventions)](#quy-ước-lập-trình-coding-conventions)
6. [Quy trình Git](#quy-trình-git)

---

## Công nghệ sử dụng
- **Backend Core**: PHP thuần (Kiến trúc MVC tuỳ biến), routing cơ bản tự build.
- **Database**: MySQL thông qua PDO (Performance & Security).
- **Frontend**: 
  - Vanilla HTML.
  - CSS thuần (Sử dụng CSS `@layer`, CSS Variables thay đổi theme).
  - Javascript thuần (Vanilla JS, Event Delegation, Data Attributes để quản lý state).

---

## Cấu trúc Project MVC

Dự án áp dụng mô hình tổ chức thư mục linh hoạt dạng Model-View-Controller nhằm tách biệt giao diện, logic xử lý và cơ sở dữ liệu.

```text
website_khoa/
├── config/                 # Chứa các file cấu hình hệ thống (ví dụ: config.php)
├── controllers/            # Controller xử lý Request, gọi Service & Model, trả về View
├── db/                     # Lưu trữ script schema SQL, database mock data
├── includes/               # Thư mục tiện ích hệ thống
│   ├── core/               # Routing xử lý Request/Response core
│   └── files/              # Xử lý các nghiệp vụ phụ như Upload File, XLSX Reader
├── models/                 # Chứa các Class Entity Base ánh xạ với Database (User, Student, Teacher)
├── public/                 # Assets tĩnh (Public access)
│   ├── css/                # Tổ chức module CSS (`base.css`, `dashboard.css`,...)
│   ├── js/                 # Tổ chức Javascript (`dashboard.js`, `tabs.js`)
│   └── img/                # Ảnh tĩnh, logos, SVG
├── services/               # Chứa logic nghiệp vụ phức tạp tách biệt khỏi Controller 
│   │                         (ví dụ: EducationService, EducationRepositoryInterface)
├── templates/              # Thư mục chứa toàn bộ giao diện HTML/PHP nội bộ
│   ├── components/         # Các mảnh Component tái sử dụng (Sidebar, Header, Footer)
│   ├── layouts/            # Layout bao bọc giao diện chung (Dashboard Layout)
│   └── pages/              # Nơi chứa Content chuyên biệt cho từng trang
│       ├── admin/          # Giao diện cho Admin Dashboard (Users, Teachers)
│       └── site/           # Giao diện Landing Page & Public Site
├── tests/                  # Script để Test cases
├── .gitignore              # Định nghĩa file ẩn với Git
├── DOCS.md                 # Tài liệu Frontend Component
├── index.php               # Front-Controller tiếp nhận toàn bộ request HTTP
├── README.md               # File tổng quan bạn đang đọc
└── routes.php              # Chứa định nghĩa các URL tới Controller
```

---

## Hướng dẫn cài đặt & chạy dự án

Dự án sử dụng PHP cơ bản và tương thích tốt với bất kỳ phần mềm local server nào (XAMPP, MAMP, Laragon, v.v.).

### Bước 1: Clone repo
Điều hướng tới thư mục chứa code của local server (Thường là `C:/xampp/htdocs` đối với Windows hoặc `/Applications/MAMP/htdocs` với MacOS).

Kéo dự án về máy:
```bash
git clone https://github.com/CaoThang-Website-CNTT/website_khoa.git
```
*(Nếu đổi tên thư mục thành `website_khoa` nếu clone sai tên)*.

### Bước 2: Config DB Server (XAMPP/MAMP)
1. Bật bảng điều khiển **XAMPP Control Panel**.
2. Start Module **Apache** và **MySQL**.
3. Import cơ sở dữ liệu. Mở `phpMyAdmin` (http://localhost/phpmyadmin), tạo một database tên là `khoacntt` với encoding `utf8mb4_general_ci`. Đổ nội dung file script .sql trong thư mục `db/` vào.

### Bước 3: Xem Website
Mở trình duyệt, truy cập: [http://localhost/website_khoa](http://localhost/website_khoa)

Lưu ý: Luôn chắc chắn cấu hình `RewriteRule` nếu hệ thống file `.htaccess` tự custom không hoạt động trên Apache của bạn.

---

## Hướng dẫn cấu hình Database

Để kết nối mã nguồn với cơ sở dữ liệu cá nhân, bạn cần tạo hoặc cấu hình lại thông số trong môi trường lập trình của mình:

Tạo hoặc chỉnh sửa nội dung file **`config/config.php`** với nội dung gốc sau:

```php
<?php
// config/config.php

return [
    'db' => [
        'host'     => '127.0.0.1',
        'dbname'   => 'khoacntt', // Chỉnh sửa lại tên DB bạn đã tự tạo trên phpMyAdmin
        'username' => 'root',         // Mặc định của XAMPP thường là root
        'password' => '',             // Mặc định của XAMPP thường để trống
        'charset'  => 'utf8mb4'
    ]
];
```

*Lưu ý: Không bao giờ đẩy cấu hình mật khẩu Production thực tế lên code-base public. Hãy thêm `config/config.php` vào `.gitignore`.*

---

## Quy ước đặt tên (Coding Conventions)

### Thư mục và file

- **Thư mục**: Sử dụng `kebab-case` (chữ thường, nối bằng dấu gạch ngang).
  - Tốt: `modules-x`
  - Xấu: `Modules X` hoặc `ModulesX`
- **File PHP**: Sử dụng `snake_case` (chữ thường, nối bằng dấu gạch dưới).
  - Tốt: `text_to_speech.php`
  - Xấu: `TextToSpeech.php`
- **File HTML/CSS/JS**: Sử dụng `kebab-case`.
  - Tốt: `style.css`, `main.js`
  - Xấu: `Style CSS.css`, `main_js.js`
- **Quy tắc chung**: Không sử dụng dấu cách hoặc ký tự đặc biệt.

**Ví dụ**:

- Tốt: `modules/grades/index.php`
- Xấu: `Grades Folder/GradePage.php`

### Biến, hàm, class (PHP)

- **Biến/hàm**: Sử dụng `camelCase`.
  - Tốt: `$user_id`, `get_news_list()`
  - Xấu: `$userId`, `getNewsList()`
- **Hằng số**: Sử dụng `UPPER_SNAKE_CASE`.
  - Tốt: `DB_HOST`
  - Xấu: `dbHost`
- **Class**: Sử dụng `PascalCase`.
  - Tốt: `NewsManager`
  - Xấu: `news_manager`
- **Quy tắc chung**: Tên phải rõ nghĩa, sử dụng tiếng Anh, tránh viết tắt.

**Ví dụ**:

- Tốt: `$news_title = get_news_by_id($id);`
- Xấu: `$nt = gnbi($i);`

### CSS (Class, ID)

- **Class**: Sử dụng BEM (Block\_\_Element--Modifier).
  - Tốt: `news__title--featured`
  - Xấu: `red-bold`
- **ID**: Sử dụng `kebab-case`, chỉ dùng cho JavaScript.
  - Tốt: `#news-list`
  - Xấu: `#NewsList`
- **File CSS**: Tách theo module.
  - Tốt: `news.css`
  - Xấu: `all-styles.css`
- **CSS variables**: Sử dụng `--var-name`.
  - Tốt: `--primary-color: #007bff;`
  - Xấu: `--PrimaryColor: #007bff;`
- **Quy tắc chung**: Tránh sử dụng `!important` (nếu dùng, cần ghi lý do).
- Để xem thông tin bổ trợ UI Kits, check qua [DOCS.md](./DOCS.md)

**Ví dụ**:

- Tốt: `.schedule__table--admin { ... }`
- Xấu: `.red-bold { ... }`

### JavaScript

- **Biến/hàm**: Sử dụng `camelCase`.
  - Tốt: `let userId;`, `function fetchNews()`
  - Xấu: `let user_id;`, `function fetch_news()`
- **Quy tắc chung**:
  - Sử dụng `const`/`let`, tránh `var`.
  - Tách file JS theo chức năng.
  - Sử dụng `addEventListener` cho sự kiện.

**Ví dụ**:

- Tốt: `document.querySelector('.news__button').addEventListener('click', handleClick);`
- Xấu: `onclick="play()"`

---

## Code style

- **Indent**: Sử dụng tab (2 spaces). Cài đặt trong VS Code: `settings/editor/tab size`.
- **Comment**: Sử dụng PHPDoc cho hàm PHP, giải thích "tại sao".
  - Tốt: `/** Lấy tin tức. @param int $limit */`
  - Xấu: `// get news`
- **File encoding**: UTF-8 without BOM.
- **Prettier**: Cấu hình `trailingComma: none` (không tự thêm dấu phẩy cuối trong mảng).

**Ví dụ**:

```php
/**
 * Lấy danh sách tin tức.
 * @param int $limit Số tin tức tối đa.
 * @return array
 */
function get_news_list($limit) {
    // Code logic
}
```

---

## Quy trình Git

Hiện tại team áp dụng **Conventional Commits** cho commit message và quản lý nhánh theo branch `feature/*`.

### Branching
- `main`: Branch code chuẩn bảo đảm luôn chạy và release.
- `feature/tên-tính-năng`: Branch để phát triển tác vụ mới. (Ví dụ: `feature/dashboard`, `feature/student-import`).
- `fix/tên-bug`: Branch fix bug nhanh.

### Commit Messages
Cú pháp Conventional theo kiểu tiếng Việt/tiếng Anh linh hoạt nhưng prefix phải đồng bộ:
- Thêm tính năng: `feat(dashboard): chức năng tạo mới giảng viên`
- Sửa lỗi: `fix(auth): sửa lỗi không lưu được session đăng nhập`
- Refactor Code: `refactor(core): tối ưu cấu trúc controller và move files tới templates/pages`
- Viết Document: `docs(readme): cập nhật hướng dẫn cài đặt`
