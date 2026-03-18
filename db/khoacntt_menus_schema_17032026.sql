-- ============================================================================
-- MENU SCHEMA
-- ============================================================================

DROP TABLE IF EXISTS `menu_items`;
DROP TABLE IF EXISTS `menus`;

CREATE TABLE `menus` (
  `id`          TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key`         VARCHAR(60)  NOT NULL UNIQUE COMMENT 'Key dùng trong code',
  `label`       VARCHAR(100) NOT NULL       COMMENT 'Tên hiển thị trong UI',
  `description` VARCHAR(255) NULL           COMMENT 'Mô tả vị trí hiển thị của nhóm menu',
  `type`        ENUM('const','custom') NOT NULL DEFAULT 'const' COMMENT 'const = tĩnh do dev định nghĩa, custom = do admin tạo',
  `sort_order`  TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE `menu_items` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `menu_id`     TINYINT UNSIGNED NOT NULL  COMMENT 'Thuộc nhóm menu nào',
  `parent_id`   INT UNSIGNED NULL DEFAULT NULL COMMENT 'NULL = root item',
  `label`       VARCHAR(150) NOT NULL,
  `url`         VARCHAR(500) NOT NULL,
  `sort_order`  TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời gian tạo',
  `updated_at`  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Thời gian cập nhật',
  `deleted_at` DATETIME COMMENT 'Thời gian xóa (xóa mềm)'
);

ALTER TABLE `menu_items` ADD FOREIGN KEY (`menu_id`)   REFERENCES `menus`      (`id`) ON DELETE CASCADE;
ALTER TABLE `menu_items` ADD FOREIGN KEY (`parent_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;

-- ============================================================================
-- SAMPLE DATA
-- ============================================================================

INSERT INTO `menus` (`id`, `key`, `label`, `description`, `type`, `sort_order`) VALUES
(1, 'main_nav', 'Menu Chính', 'Thanh điều hướng chính trên header', 'const', 1);

-- main_nav root items
-- id 1: Trang Chủ
-- id 2-7: Dropdown
INSERT INTO `menu_items` (`id`, `menu_id`, `parent_id`, `label`, `url`, `sort_order`) VALUES
(1, 1, NULL, 'Trang Chủ',              '/',           1),
(2, 1, NULL, 'Giới Thiệu',            '/gioi-thieu', 2),
(3, 1, NULL, 'Chương Trình Đào Tạo',  '/dao-tao',    3),
(4, 1, NULL, 'Nghiên Cứu',            '/nghien-cuu', 4),
(5, 1, NULL, 'Tin Tức',               '/tin-tuc',    5),
(6, 1, NULL, 'Sinh Viên',             '/sinh-vien',  6),
(7, 1, NULL, 'Liên Hệ',               '/lien-he',    7);

-- children of Giới Thiệu (parent_id = 2)
INSERT INTO `menu_items` (`menu_id`, `parent_id`, `label`, `url`, `sort_order`) VALUES
(1, 2, 'Giới thiệu chung', '/gioi-thieu/chung', 1),
(1, 2, 'Lịch sử',          '/gioi-thieu/lich-su', 2);

-- children of Chương Trình Đào Tạo (parent_id = 3)
INSERT INTO `menu_items` (`menu_id`, `parent_id`, `label`, `url`, `sort_order`) VALUES
(1, 3, 'Cao đẳng', '/dao-tao/cao-dang',     1),
(1, 3, 'Trung cấp', '/dao-tao/cao-dang-nghe', 2);

-- children of Nghiên Cứu (parent_id = 4)
INSERT INTO `menu_items` (`menu_id`, `parent_id`, `label`, `url`, `sort_order`) VALUES
(1, 4, 'Đề tài',  '/nghien-cuu/de-tai', 1),
(1, 4, 'Công bố', '/nghien-cuu/cong-bo', 2);

-- children of Tin Tức (parent_id = 5)
INSERT INTO `menu_items` (`menu_id`, `parent_id`, `label`, `url`, `sort_order`) VALUES
(1, 5, 'Sự kiện',   '/tin-tuc/su-kien',  1),
(1, 5, 'Thông báo', '/tin-tuc/thong-bao', 2);

-- children of Sinh Viên (parent_id = 6)
INSERT INTO `menu_items` (`menu_id`, `parent_id`, `label`, `url`, `sort_order`) VALUES
(1, 6, 'Học bổng',  '/sinh-vien/hoc-bong',  1),
(1, 6, 'Hoạt động', '/sinh-vien/hoat-dong', 2);

-- children of Liên Hệ (parent_id = 7)
INSERT INTO `menu_items` (`menu_id`, `parent_id`, `label`, `url`, `sort_order`) VALUES
(1, 7, 'Địa chỉ',      '/lien-he/dia-chi', 1),
(1, 7, 'Gửi phản hồi', '/lien-he/phan-hoi', 2);