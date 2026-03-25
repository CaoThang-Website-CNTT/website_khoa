SET FOREIGN_KEY_CHECKS = 0;

-- 1. DROP TABLES
DROP TABLE IF EXISTS `web_settings`;
DROP TABLE IF EXISTS `menu_items`;
DROP TABLE IF EXISTS `menus`;
DROP TABLE IF EXISTS `carousel_slides`;
DROP TABLE IF EXISTS `carousels`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `teachers`;
DROP TABLE IF EXISTS `accounts`;
DROP TABLE IF EXISTS `classrooms`;
DROP TABLE IF EXISTS `specializations`;
DROP TABLE IF EXISTS `majors`;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- PHẦN 1: TẠO CẤU TRÚC BẢNG (KHÔNG CÓ KHÓA NGOẠI)
-- ============================================================================

CREATE TABLE `majors` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT COMMENT 'Khóa chính',
  `full_name` varchar(100) COMMENT 'Tên ngành học đầy đủ',
  `short_name` varchar(20) UNIQUE COMMENT 'Tên viết tắt (VD: TH, CNTT)',
  `level` varchar(5) COMMENT 'Hệ đào tạo (VD: CĐ, CĐN)',
  `updated_at` datetime,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `specializations` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `major_id` bigint COMMENT 'FK trỏ tới majors',
  `full_name` varchar(100),
  `short_name` varchar(20),
  `updated_at` datetime,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime,
  CONSTRAINT `unique_spec_per_major` UNIQUE KEY (`major_id`, `short_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `classrooms` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `major_id` bigint NOT NULL,
  `specialization_id` bigint NULL,
  `homeroom_teacher_id` bigint NULL COMMENT 'Giáo viên chủ nhiệm',
  `class_of` int COMMENT 'Khóa học (VD: 23)',
  `letter` varchar(1) NULL,
  `short_name` varchar(50) UNIQUE COMMENT 'Mã lớp',
  `updated_at` datetime,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `accounts` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `email` varchar(255) UNIQUE,
  `password_hash` varchar(500) comment "2y hash",
  `role` enum('student','teacher','admin'),
  `updated_at` datetime,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `teachers` (
  `id` bigint AUTO_INCREMENT PRIMARY KEY,
  `account_id` bigint UNIQUE,
  `staff_code` varchar(10) UNIQUE NOT NULL,
  `full_name` varchar(255),
  `gender` enum('male','female'),
  `dob` date,
  `phone` varchar(15),
  `address` text null,
  `national_id` varchar(12) UNIQUE comment 'CCCD của GV',
  `degree` varchar(150),
  `position` VARCHAR(100) NULL comment 'Chức vụ',
  `title` varchar(150),
  `department` varchar(150),
  `contract_type` ENUM('full_time', 'part_time', 'visiting', 'contract') NOT NULL DEFAULT 'full_time',
  `start_date` date,
  `end_date` date null,
  `notes` text null,
  `updated_at` datetime,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `students` (
  `id` bigint AUTO_INCREMENT PRIMARY KEY,
  `account_id` bigint UNIQUE,
  `student_id` varchar(10) UNIQUE NOT NULL,
  `full_name` varchar(255),
  `gender` enum('male','female'),
  `dob` date,
  `phone` varchar(15),
  `classroom_id` bigint,
  `address` text null,
  `national_id` varchar(12) UNIQUE comment 'CCCD của SV',
  `major` varchar(150) COMMENT 'Lưu text',
  `birth_place` varchar(255),
  `status` ENUM('Đang học', 'Đã tốt nghiệp', 'Tạm ngưng', 'Thôi học') DEFAULT 'Đang học',
  `notes` text null,
  `updated_at` datetime,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `categories` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `name`        VARCHAR(255) NOT NULL,
    `slug`        VARCHAR(255) NULL UNIQUE,
    `type`        ENUM('const', 'custom') NOT NULL DEFAULT 'custom',
    `description` LONGTEXT NULL,
    `parent_id`   INT NULL,
    `meta`        JSON NULL,
    `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`  DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `carousels` (
 `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 `name` VARCHAR(100) NOT NULL,
 `slug` VARCHAR(100) NOT NULL UNIQUE,
 `is_active` TINYINT(1) NOT NULL DEFAULT 1,
 `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 `deleted_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `carousel_slides` (
 `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 `carousel_id` INT UNSIGNED NOT NULL,
 `title` VARCHAR(255) NOT NULL,
 `title_highlight` VARCHAR(255) DEFAULT NULL,
 `description` TEXT DEFAULT NULL,
 `image_path` VARCHAR(500) NOT NULL,
 `image_alt` VARCHAR(255) NOT NULL DEFAULT '',
 `cta_label` VARCHAR(100) DEFAULT NULL,
 `cta_url` VARCHAR(500) DEFAULT NULL,
 `cta_variant` VARCHAR(20) NOT NULL DEFAULT 'primary',
 `custom_html` MEDIUMTEXT DEFAULT NULL,
 `use_custom_html` TINYINT(1) NOT NULL DEFAULT 0,
 `sort_order` SMALLINT NOT NULL DEFAULT 0,
 `is_active` TINYINT(1) NOT NULL DEFAULT 1,
 `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 `deleted_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `menus` (
  `id`          TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key`         VARCHAR(60)  NOT NULL UNIQUE,
  `label`       VARCHAR(100) NOT NULL,
  `description` VARCHAR(255) NULL,
  `type`        ENUM('const','custom') NOT NULL DEFAULT 'const',
  `sort_order`  TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`  DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `menu_items` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `menu_id`     TINYINT UNSIGNED NOT NULL,
  `parent_id`   INT UNSIGNED NULL DEFAULT NULL,
  `label`       VARCHAR(150) NOT NULL,
  `url`         VARCHAR(500) NOT NULL,
  `sort_order`  TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`  DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================================
-- PHẦN 2: THIẾT LẬP TẤT CẢ KHÓA NGOẠI (ALTER TABLE)
-- ============================================================================

-- 1. Academic Structure
ALTER TABLE `specializations` 
  ADD CONSTRAINT `fk_spec_major` FOREIGN KEY (`major_id`) REFERENCES `majors` (`id`) ON DELETE CASCADE;

ALTER TABLE `classrooms` 
  ADD CONSTRAINT `fk_class_major` FOREIGN KEY (`major_id`) REFERENCES `majors` (`id`),
  ADD CONSTRAINT `fk_class_spec` FOREIGN KEY (`specialization_id`) REFERENCES `specializations` (`id`),
  ADD CONSTRAINT `fk_class_teacher` FOREIGN KEY (`homeroom_teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL;

-- 2. User Management
ALTER TABLE `teachers` 
  ADD CONSTRAINT `fk_teacher_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE;

ALTER TABLE `students` 
  ADD CONSTRAINT `fk_student_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_student_class` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE SET NULL;

-- 3. Content & Taxonomy
ALTER TABLE `categories` 
  ADD CONSTRAINT `fk_category_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

ALTER TABLE `carousel_slides` 
  ADD CONSTRAINT `fk_slide_carousel` FOREIGN KEY (`carousel_id`) REFERENCES `carousels` (`id`) ON DELETE CASCADE;

-- 4. UI Structure
ALTER TABLE `menu_items` 
  ADD CONSTRAINT `fk_item_menu` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_item_parent` FOREIGN KEY (`parent_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;