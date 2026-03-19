-- ============================================================================
-- CATEGORIES SCHEMA
-- ============================================================================

DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `name`        VARCHAR(255) NOT NULL COMMENT 'Tên Danh Mục',
    `slug`        VARCHAR(255) NULL UNIQUE COMMENT 'Slug để làm đẹp URL cho SEO',
    `type`        ENUM('const', 'custom') NOT NULL DEFAULT 'custom' COMMENT 'const = tĩnh do dev định nghĩa | custom = do admin tạo qua CRUD',
    `description` LONGTEXT NULL COMMENT 'Mô Tả Danh Mục',
    `parent_id`   INT NULL COMMENT 'Trỏ tới Danh Mục cha (NULL là root)',
    `meta`        JSON NULL COMMENT 'Chứa thông tin linh hoạt, phục vụ cho các cấu hình danh mục liên quan đến UI như WordPress',
    `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời gian tạo',
    `updated_at`  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Thời gian cập nhật',
    `deleted_at`  DATETIME COMMENT 'Thời gian xóa (xóa mềm)',

    KEY `idx_parent_id` (`parent_id`) COMMENT 'Speed up child lookups'
);

ALTER TABLE `categories` ADD FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`);

-- ============================================================================
-- SAMPLE DATA
-- ============================================================================

INSERT INTO `categories` (`name`, `slug`, `type`, `description`, `parent_id`) VALUES
('Tin Tức',   'tin-tuc',    'const',  'Tin tức và thông báo của khoa',       NULL),
('Công Nghệ', 'cong-nghe',  'custom', 'Tin tức về công nghệ và CNTT',        NULL),
('Nghiên Cứu','nghien-cuu', 'const',  'Các kết quả nghiên cứu và bài báo',   NULL),
('Sự Kiện',   'su-kien',    'const',  'Các sự kiện và hội thảo của khoa',    NULL);

INSERT INTO `categories` (`name`, `slug`, `type`, `description`, `parent_id`) VALUES
('Tin Nóng',            'tin-nong',            'custom', 'Các tin tức mới nhất',              1),
('Thông Báo Chính Thức','thong-bao-chinh-thuc', 'custom', 'Thông báo từ lãnh đạo khoa',       1),
('Trí Tuệ Nhân Tạo',   'tri-tue-nhan-tao',    'custom', 'AI và Máy Học',                     2),
('Phát Triển Web',      'phat-trien-web',      'custom', 'Các công nghệ và framework web',    2),
('Cơ Sở Dữ Liệu',      'co-so-du-lieu',       'custom', 'Công nghệ cơ sở dữ liệu',           2);

INSERT INTO `categories` (`name`, `slug`, `type`, `description`, `parent_id`) VALUES
('Phát Triển PHP',   'phat-trien-php',   'custom', 'Các chủ đề liên quan PHP', 8),
('Framework Laravel','framework-laravel','custom', 'Hướng dẫn sử dụng Laravel', 11),
('MySQL',            'mysql',            'custom', 'Quản lý và tối ưu MySQL',   9);