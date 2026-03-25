SET FOREIGN_KEY_CHECKS = 0;

-- 1. DROP TABLES (Xóa theo thứ tự ngược lại để tránh lỗi khóa ngoại)
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
-- SCHEMA 1: ACADEMIC STRUCTURE (Ngành, Chuyên Ngành, Lớp)
-- ============================================================================

CREATE TABLE `majors` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT COMMENT 'Khóa chính',
  `full_name` varchar(100) COMMENT 'Tên ngành học đầy đủ',
  `short_name` varchar(20) UNIQUE COMMENT 'Tên viết tắt (VD: TH, CNTT)',
  `level` varchar(5) COMMENT 'Hệ đào tạo (VD: CĐ, CĐN)',
  `updated_at` datetime COMMENT 'Thời gian cập nhật',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời gian tạo',
  `deleted_at` datetime COMMENT 'Xóa mềm'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `specializations` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `major_id` bigint COMMENT 'FK trỏ tới majors',
  `full_name` varchar(100),
  `short_name` varchar(20),
  `updated_at` datetime,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime,

  CONSTRAINT `unique_spec_per_major` UNIQUE KEY (`major_id`, `short_name`),
  CONSTRAINT `fk_spec_major` FOREIGN KEY (`major_id`) REFERENCES `majors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `classrooms` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `major_id` bigint NOT NULL,
  `class_of` int COMMENT 'Khóa học (VD: 23)',
  `specialization_id` bigint NULL,
  `letter` varchar(1) NULL,
  `short_name` varchar(20) UNIQUE COMMENT 'Mã lớp (VD: CĐ TH 23 WEB C)',
  `updated_at` datetime,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime,
  CONSTRAINT `fk_class_major` FOREIGN KEY (`major_id`) REFERENCES `majors` (`id`),
  CONSTRAINT `fk_class_spec` FOREIGN KEY (`specialization_id`) REFERENCES `specializations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================================
-- SCHEMA 2: USER MANAGEMENT (Tài khoản, Giảng viên, Sinh viên)
-- ============================================================================

CREATE TABLE `accounts` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `email` varchar(255) UNIQUE,
  `password_hash` varchar(500),
  `role` enum('student','teacher','admin'),
  `updated_at` datetime,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `teachers` (
  `account_id` bigint PRIMARY KEY,
  `full_name` varchar(255),
  `gender` enum('male','female'),
  `dob` date,
  `phone` varchar(15),
  `degree` varchar(150),
  `title` varchar(150),
  `department` varchar(150),
  `start_date` date,
  CONSTRAINT `fk_teacher_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `students` (
  `account_id` bigint PRIMARY KEY,
  `student_id` varchar(10) UNIQUE,
  `full_name` varchar(255),
  `gender` enum('male','female'),
  `dob` date,
  `phone` varchar(15),
  `classroom_id` bigint,
  `major` varchar(150) COMMENT 'Lưu text hoặc có thể tham chiếu trực tiếp major_id',
  `birth_place` varchar(255),
  CONSTRAINT `fk_student_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_student_class` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================================
-- SCHEMA 3: CONTENT & TAXONOMY (Danh mục, Carousel)
-- ============================================================================

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
    `deleted_at`  DATETIME,
    CONSTRAINT `fk_category_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
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
 `deleted_at` TIMESTAMP NULL DEFAULT NULL,
 CONSTRAINT `fk_slide_carousel` FOREIGN KEY (`carousel_id`) REFERENCES `carousels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================================
-- SCHEMA 4: UI & CONFIGURATION (Menu, Cấu hình web)
-- ============================================================================

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
  `deleted_at`  DATETIME,
  CONSTRAINT `fk_item_menu` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_item_parent` FOREIGN KEY (`parent_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE
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