-- ============================================================================
-- CATEGORIES SCHEMA
-- ============================================================================

DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL COMMENT 'Tên Danh Mục',
    `slug` VARCHAR(255) NULL UNIQUE COMMENT 'Slug để làm đẹp URL cho SEO',
    `description` LONGTEXT NULL COMMENT 'Mô Tả Danh Mục',
    `parent_id` INT NULL COMMENT 'Trỏ tới Danh Mục cha (NULL là root)',
    `meta` JSON NULL COMMENT 'Chứa thông tin linh hoạt, phục vụ cho các cấu hình danh mục liên quan đến UI như WordPress',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời gian tạo',
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Thời gian cập nhật',
    `deleted_at` DATETIME COMMENT 'Thời gian xóa (xóa mềm)',
    
    -- Đặt Index cho truy vấn nhanh hơn (Chưa xác định được cấu hình server nên hạn chế set Index lung tung)
    KEY `idx_parent_id` (`parent_id`) COMMENT 'Speed up child lookups'
);

ALTER TABLE `categories` ADD FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`);

-- SAMPLE DATA
-- ============================================================================
 
INSERT INTO `categories` (`name`, `slug`, `description`, `parent_id`) VALUES
('Tin Tức', 'tin-tuc', 'Tin tức và thông báo của khoa', NULL),
('Công Nghệ', 'cong-nghe', 'Tin tức về công nghệ và CNTT', NULL),
('Nghiên Cứu', 'nghien-cuu', 'Các kết quả nghiên cứu và bài báo', NULL),
('Sự Kiện', 'su-kien', 'Các sự kiện và hội thảo của khoa', NULL);
 
INSERT INTO `categories` (`name`, `slug`, `description`, `parent_id`) VALUES
('Tin Nóng', 'tin-nong', 'Các tin tức mới nhất', 1),
('Thông Báo Chính Thức', 'thong-bao-chinh-thuc', 'Thông báo từ lãnh đạo khoa', 1),
('Trí Tuệ Nhân Tạo', 'tri-tue-nhan-tao', 'AI và Máy Học', 2),
('Phát Triển Web', 'phat-trien-web', 'Các công nghệ và framework web', 2),
('Cơ Sở Dữ Liệu', 'co-so-du-lieu', 'Công nghệ cơ sở dữ liệu', 2);
 
INSERT INTO `categories` (`name`, `slug`, `description`, `parent_id`) VALUES
('Phát Triển PHP', 'phat-trien-php', 'Các chủ đề liên quan PHP', 8),
('Framework Laravel', 'framework-laravel', 'Hướng dẫn sử dụng Laravel', 11),
('MySQL', 'mysql', 'Quản lý và tối ưu MySQL', 9);