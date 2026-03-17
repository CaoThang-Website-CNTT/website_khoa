-- ============================================================================
-- WEB SETTINGS SCHEMA
-- ============================================================================

DROP TABLE IF EXISTS `web_settings`;

CREATE TABLE `web_settings` (
 `id` SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 `key` VARCHAR(120) NOT NULL UNIQUE COMMENT 'VD: site_title, homepage.hero_title',
 `group` VARCHAR(60) NOT NULL DEFAULT 'general' COMMENT 'Nhóm cấu hình: general, homepage, mail, seo, ...',
 `type` ENUM('string','text','int','float','bool','json','color','image','file','email','url','datetime') NOT NULL DEFAULT 'string' COMMENT 'Kiểu dữ liệu để PHP tự động cast khi đọc ra',
 `value` MEDIUMTEXT NULL COMMENT 'Giá trị thực tế đang được áp dụng',
 `default_value` TEXT NULL COMMENT 'Fallback khi value NULL, tránh hardcode trong PHP',
 `label` VARCHAR(150) NOT NULL COMMENT 'Tên hiển thị trong admin UI, VD: Tên website',
 `description` VARCHAR(255) NULL COMMENT 'Gợi ý/mô tả bên dưới input trong admin',
 `rules` JSON NULL COMMENT 'Validation rules và UI hints, VD: {"min":0,"max":100}',
 `autoload` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Load vào cache mỗi request, chỉ bật cho settings hay dùng',
 `is_locked` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Khoá không cho sửa/xoá qua UI, dùng cho settings hệ thống',
 `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Thứ tự hiển thị trong cùng một group',
 `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời gian tạo',
 `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Thời gian cập nhật'
);


-- SAMPLE DATA
-- ============================================================================

-- group: general
INSERT INTO `web_settings` (`key`, `group`, `type`, `value`, `label`, `description`, `autoload`, `is_locked`, `sort_order`) VALUES
('site_title', 'general', 'string', 'Khoa Công Nghệ Thông Tin', 'Tên Website', 'Hiển thị trên tab trình duyệt và header', 1, 1, 1),
('site_logo', 'general', 'image', '/uploads/logo.png', 'Logo', 'Khuyến nghị: PNG nền trong, tối thiểu 200px', 1, 0, 3),
('site_favicon', 'general', 'image', '/uploads/favicon.ico', 'Favicon', 'File .ico hoặc .png, kích thước 32x32', 1, 0, 4),
('posts_per_page', 'general', 'int', '10', 'Bài Viết Mỗi Trang', NULL, 1, 0, 8),
('site_under_maintenance', 'general', 'bool', '0', 'Chế Độ Bảo Trì', 'Bật sẽ hiển thị trang bảo trì cho visitor', 1, 0, 9);

-- group: homepage
INSERT INTO `web_settings` (`key`, `group`, `type`, `value`, `label`, `description`, `autoload`, `sort_order`) VALUES
('homepage.hero_title', 'homepage', 'string', 'Chào Mừng Đến Với Khoa CNTT', 'Tiêu Đề Hero Banner', NULL, 1, 1),
('homepage.hero_subtitle', 'homepage', 'text', 'Đào tạo nguồn nhân lực chất lượng cao trong lĩnh vực Công nghệ Thông tin.', 'Mô Tả Hero Banner', NULL, 1, 2),
('homepage.hero_image', 'homepage', 'image', '/uploads/hero.jpg', 'Ảnh Nền Hero', 'Khuyến nghị: 1920x600px', 1, 3),
('homepage.hero_cta_text', 'homepage', 'string', 'Tìm Hiểu Thêm', 'Nút CTA – Label', NULL, 1, 4),
('homepage.hero_cta_url', 'homepage', 'url', '/gioi-thieu', 'Nút CTA – Đường Dẫn', NULL, 1, 5),
('homepage.show_news', 'homepage', 'bool', '1', 'Hiển Thị Khối Tin Tức', NULL, 1, 6),
('homepage.news_count', 'homepage', 'int', '6', 'Số Bài Tin Tức Trang Chủ', NULL, 1, 7),
('homepage.show_stats', 'homepage', 'bool', '1', 'Hiển Thị Thống Kê', 'Sinh viên, giảng viên, ...', 1, 8),
('homepage.stats', 'homepage', 'json', '[{"label":"Sinh viên","value":"2500+"},{"label":"Giảng viên","value":"120"},{"label":"Chương trình","value":"8"},{"label":"Năm thành lập","value":"1995"}]', 'Số Liệu Thống Kê', 'Mảng JSON: [{label, value}]', 0, 9);

-- group: contact
INSERT INTO `web_settings` (`key`, `group`, `type`, `value`, `label`, `autoload`, `sort_order`) VALUES
('contact.address', 'contact', 'text', '01 Võ Văn Ngân, Thủ Đức, TP. Hồ Chí Minh', 'Địa Chỉ', 1, 1),
('contact.phone', 'contact', 'string', '(028) 3896 8641', 'Số Điện Thoại', 1, 2),
('contact.email', 'contact', 'email', 'khoacntt@university.edu.vn', 'Email Liên Hệ', 1, 3);

-- group: social
INSERT INTO `web_settings` (`key`, `group`, `type`, `value`, `label`, `autoload`, `sort_order`) VALUES
('social.facebook', 'social', 'url', 'https://facebook.com/khoacntt', 'Facebook', 1, 1),
('social.youtube', 'social', 'url', NULL, 'YouTube', 1, 2),
('social.twitter', 'social', 'url', NULL, 'Twitter / X', 1, 3),
('social.linkedin', 'social', 'url', NULL, 'LinkedIn', 1, 4),
('social.zalo', 'social', 'url', NULL, 'Zalo OA', 1, 5);

-- group: seo
INSERT INTO `web_settings` (`key`, `group`, `type`, `value`, `label`, `autoload`, `sort_order`) VALUES
('seo.meta_title', 'seo', 'string', 'Khoa Công Nghệ Thông Tin', 'Meta Title Mặc Định', 1, 1),
('seo.meta_description', 'seo', 'text', 'Trang web chính thức của Khoa Công Nghệ Thông Tin.', 'Meta Description', 1, 2);