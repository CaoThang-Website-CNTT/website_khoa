-- ============================================================================
-- WEB SETTINGS SCHEMA
-- ============================================================================
DROP TABLE IF EXISTS `web_settings`;
CREATE TABLE `web_settings` (
`id` SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
`key` VARCHAR(120) NOT NULL UNIQUE,
`group` VARCHAR(60) NOT NULL DEFAULT 'general',
`type` ENUM('string','text','email','url','json','bool','int','float','datetime') NOT NULL DEFAULT 'string',
`value` MEDIUMTEXT NULL,
`default_value` TEXT NULL,
`label` VARCHAR(150) NOT NULL,
`description` VARCHAR(255) NULL,
`autoload` TINYINT(1) NOT NULL DEFAULT 1,
`is_locked` TINYINT(1) NOT NULL DEFAULT 0,
`sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
`updated_by` SMALLINT UNSIGNED NULL,
`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
`updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================================
-- SAMPLE DATA
-- ============================================================================
-- group: general
INSERT INTO `web_settings` (`key`, `group`, `type`, `value`, `default_value`, `label`, `description`, `autoload`, `is_locked`, `sort_order`) VALUES
('site_title', 'general', 'string', 'KHOA CÔNG NGHỆ THÔNG TIN', NULL, 'Tên Website', 'Hiển thị trên tab trình duyệt và header', 1, 1, 1),
('posts_per_page', 'general', 'int', '10', '10', 'Bài Viết Mỗi Trang', NULL, 1, 0, 4),
('site_under_maintenance', 'general', 'bool', '0', '0', 'Chế Độ Bảo Trì', 'Bật sẽ hiển thị trang bảo trì cho visitor', 1, 0, 5);

-- group: homepage
INSERT INTO `web_settings` (`key`, `group`, `type`, `value`, `default_value`, `label`, `description`, `autoload`, `sort_order`) VALUES
('homepage.hero_title', 'homepage', 'string', 'Chào Mừng Đến Với Khoa CNTT', NULL, 'Tiêu Đề Hero Banner', NULL, 1, 1),
('homepage.hero_subtitle', 'homepage', 'text', 'Đào tạo nguồn nhân lực chất lượng cao trong lĩnh vực Công nghệ Thông tin.', NULL, 'Mô Tả Hero Banner', NULL, 1, 2),
('homepage.hero_cta_text', 'homepage', 'string', 'Tìm Hiểu Thêm', 'Tìm Hiểu Thêm', 'Nút CTA – Label', NULL, 1, 4),
('homepage.hero_cta_url', 'homepage', 'url', '/gioi-thieu', '/gioi-thieu', 'Nút CTA – Đường Dẫn', NULL, 1, 5),
('homepage.show_news', 'homepage', 'bool', '1', '1', 'Hiển Thị Khối Tin Tức', NULL, 1, 6),
('homepage.news_count', 'homepage', 'int', '6', '6', 'Số Bài Tin Tức Trang Chủ', NULL, 1, 7),
('homepage.show_stats', 'homepage', 'bool', '1', '1', 'Hiển Thị Thống Kê', 'Sinh viên, giảng viên, ...', 1, 8),
('homepage.stats', 'homepage', 'json', '[{"label":"Sinh viên","value":"2500+"},{"label":"Giảng viên","value":"120"},{"label":"Chương trình","value":"8"},{"label":"Năm thành lập","value":"1995"}]', NULL, 'Số Liệu Thống Kê', 'Mảng JSON: [{label, value}]', 0, 9);

-- group: contact
INSERT INTO `web_settings` (`key`, `group`, `type`, `value`, `default_value`, `label`, `autoload`, `sort_order`) VALUES
('contact.address', 'contact', 'text', 'Lầu 7 - Dãy F, 65 Huỳnh Thúc Kháng, Phường Sài Gòn, TP.HCM, Việt Nam', NULL, 'Địa Chỉ', 1, 1),
('contact.phone', 'contact', 'string', '+84 (08) 3821 2360', NULL, 'Số Điện Thoại', 1, 2),
('contact.email', 'contact', 'email', 'cntt@caothang.edu.vn', NULL, 'Email Liên Hệ', 1, 3),
('contact.description', 'contact', 'text', 'Trang web chính thức của Khoa Công Nghệ Thông Tin - Trường Cao Đẳng Kỹ Thuật Cao Thắng.', NULL, 'Mô Tả Ngắn (Footer)', 1, 4);

-- group: social
INSERT INTO `web_settings` (`key`, `group`, `type`, `value`, `default_value`, `label`, `autoload`, `sort_order`) VALUES
('social.facebook', 'social', 'url', NULL, NULL, 'Facebook', 1, 1),
('social.youtube', 'social', 'url', NULL, NULL, 'YouTube', 1, 2);

-- group: seo
INSERT INTO `web_settings` (`key`, `group`, `type`, `value`, `default_value`, `label`, `description`, `autoload`, `sort_order`) VALUES
('seo.meta_title', 'seo', 'string', 'Khoa CNTT - Trường CĐ Kỹ Thuật Cao Thắng', 'Khoa CNTT - Trường CĐ Kỹ Thuật Cao Thắng', 'Meta Title Mặc Định', 'Khuyến nghị tối đa 60 ký tự', 1, 1),
('seo.meta_description', 'seo', 'text', 'Trang web chính thức của Khoa Công Nghệ Thông Tin - Trường Cao Đẳng Kỹ Thuật Cao Thắng.', NULL, 'Meta Description', 'Khuyến nghị tối đa 160 ký tự', 1, 2);