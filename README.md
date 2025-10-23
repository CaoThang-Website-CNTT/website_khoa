# website_khoacntt_caothang
Đây là dự án Website cho Khoa CNTT của Trường Cao Đẳng Kỹ Thuật Cao Thắng

Tài liệu này cung cấp thông tin về cấu trúc thư mục, quy ước đặt tên, và các tiêu chuẩn lập trình cho dự án `website_khoacntt_caothang`.

---

## Mục lục

- [Công nghệ sử dụng](#công-nghệ-sử-dụng)
- [Cấu trúc thư mục](#cấu-trúc-thư-mục)
- [Quy ước đặt tên](#quy-ước-đặt-tên)
  - [Thư mục và file](#thư-mục-và-file)
  - [Biến, hàm, class (PHP)](#biến-hàm-class-php)
  - [CSS (Class, ID)](#css-class-id)
  - [JavaScript](#javascript)
- [Code style](#code-style)
- [Quy trình Git](#quy-trình-git)

---

## Công nghệ sử dụng

- PHP + HTML, CSS, JavaScript.

---

## Cấu trúc thư mục

Cấu trúc thư mục của dự án được tổ chức như sau:
```
web_khoa/
├── config/                 # Chứa các tệp cấu hình cho dự án
│   └── (ví dụ: database.php)    # Cấu hình kết nối cơ sở dữ liệu
├── includes/               # Chứa các tệp PHP dùng chung
│   ├── (ví dụ: utils.php)       # Các hàm tiện ích
│   ├── (ví dụ: auth.php)        # Quản lý đăng nhập/đăng xuất
│   ├── (ví dụ: header.php)      # Mẫu header chung (menu điều hướng, logo)
│   ├── (ví dụ: footer.php)      # Mẫu footer chung
│   └── (ví dụ: db.php)          # Các hàm hỗ trợ truy vấn cơ sở dữ liệu
├── models/                 # Chứa logic tương tác dữ liệu, bao gồm model để ánh xạ dữ liệu
├── controllers/            # Chứa logic điều khiển và xử lý yêu cầu
├── views/                  # Chứa các tệp giao diện (HTML + PHP)
│   ├── templates/          # Các giao diện có thể tái sử dụng
│   │   ├── (ví dụ: nav.php)     # Menu điều hướng (tùy chỉnh theo vai trò)
│   │   └── (ví dụ: form.php)    # Form chung
├── assets/                 # Chứa các tài nguyên tĩnh (CSS, JS, hình ảnh)
│   ├── css/                # Chứa các tệp CSS
│   ├── js/                 # Chứa các tệp JavaScript
│   └── img/                # Chứa hình ảnh (logo, banner)
├── public/                 # Thư mục gốc web (điểm truy cập - entry point)
│   ├── index.php           # Tệp định tuyến chính
│   ├── .htaccess           # Tệp cấu hình URL (Đã từng đề cập trong buổi đầu tiên môn NodeJS)
│   └── favicon.ico         # Biểu tượng website
├── db/                     # Chứa các tệp liên quan đến cơ sở dữ liệu
│   └── (ví dụ: schema.sql)      # Tệp SQL chứa lược đồ cơ sở dữ liệu và dữ liệu mẫu
├── tests/                  # Chứa các kiểm thử (Dự án cần phải kiểm thử cho nhiều chức năng)
│   └── (ví dụ: test_auth.php)   # Chứa các testcases của chức năng đăng nhập
├── .gitignore              # Tệp liệt kê các thư mục/tệp không đưa vào Git (ví dụ: config, vendor/)
└── README.md               # Tài liệu dự án
```

---

## Quy ước đặt tên

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

- **Biến/hàm**: Sử dụng `snake_case`.
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

- **Class**: Sử dụng BEM (Block__Element--Modifier).
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

**Ví dụ**:
- Tốt: `.schedule__table--admin { ... }`
- Xấu: `.red-bold { ... }`

### JavaScript

- **Biến/hàm**: Sử dụng `camelCase`.
  - Tốt: `let userId;`, `function fetchNews()`
  - Xấu: `let user_id;`, `function fetch_news()`
- **Quy tắc chung**:
  - Sử dụng `const`/`let`, tránh `var`.
  - Tách file JS theo chức năng, ví dụ: `ai-news.js`.
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

- **Branch**:
  - main: Branch chính (production).
  - feature/tên-tính-năng: Branch cho tính năng mới.
  - fix/tên-bug: Branch sửa lỗi.


- **Commit**: Sử dụng Conventional Commits.
  - Tốt: feat(news): add detail page
  - Xấu: update news

- **.gitignore**: Loại bỏ .env, file tạm, và các assets lớn.

**Ví dụ**:
```
Commit: fix(schedule): correct room display error
Branch: feature/ai-text-to-speech
```
