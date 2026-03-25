-- ============================================================================
-- CLASSROOMS SCHEMA
-- ============================================================================
DROP TABLE IF EXISTS `majors`;
DROP TABLE IF EXISTS `specializations`;
DROP TABLE IF EXISTS `classrooms`;

CREATE TABLE `majors` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT COMMENT 'Khóa chính',
  `full_name` varchar(100) COMMENT 'Tên ngành học đầy đủ (VD: Công nghệ thông tin)',
  `short_name` varchar(20) UNIQUE COMMENT 'Tên viết tắt của ngành (VD: TH, CNTT, QTM)',
  `level` varchar(5) COMMENT 'Hệ đào tạo (VD: CĐ - Cao đẳng, CĐN - Cao đẳng nghề)',
  `updated_at` datetime COMMENT 'Thời gian cập nhật',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời gian tạo',
  `deleted_at` datetime COMMENT 'Thời gian xóa (Xóa mềm)'
) COMMENT='Bảng lưu trữ danh sách các ngành học';

CREATE TABLE `specializations` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT COMMENT 'Khóa chính',
  `major_id` bigint COMMENT 'ID ngành học chứa chuyên ngành này',
  `full_name` varchar(100) COMMENT 'Tên chuyên ngành đầy đủ (VD: Lập trình Website)',
  `short_name` varchar(20) COMMENT 'Tên viết tắt của chuyên ngành (VD: WEB, DĐ)',
  `updated_at` datetime COMMENT 'Thời gian cập nhật',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời gian tạo',
  `deleted_at` datetime COMMENT 'Thời gian xóa (Xóa mềm)'

  UNIQUE KEY `unique_spec_per_major` (`major_id`, `short_name`)
) COMMENT='Bảng lưu trữ danh sách các chuyên ngành thuộc ngành học';

CREATE TABLE `classrooms` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT COMMENT 'Khóa chính',
  `major_id` bigint COMMENT 'ID ngành học của lớp',
  `class_of` int COMMENT 'Khóa học / Niên khóa (VD: 23, 24, 25)',
  `specialization_id` bigint NULL COMMENT 'ID chuyên ngành (NULL nếu lớp không phân chuyên ngành)',
  `letter` varchar(1) NULL COMMENT 'Chữ cái phân lớp (VD: A, B, C)',
  `short_name` varchar(20) UNIQUE COMMENT 'Mã lớp / Tên lớp hiển thị (VD: CĐ TH 23 WEB C)',
  `updated_at` datetime COMMENT 'Thời gian cập nhật',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời gian tạo',
  `deleted_at` datetime COMMENT 'Thời gian xóa (Xóa mềm)'
) COMMENT='Bảng lưu trữ danh sách các lớp học';

ALTER TABLE `classrooms` ADD FOREIGN KEY (`major_id`) REFERENCES `majors` (`id`);
ALTER TABLE `specializations` ADD FOREIGN KEY (`major_id`) REFERENCES `majors` (`id`);
ALTER TABLE `classrooms` ADD FOREIGN KEY (`specialization_id`) REFERENCES `specializations` (`id`);

-- ============================================================================
-- SAMPLE DATA
-- ============================================================================

INSERT INTO `majors` (`full_name`, `short_name`, `level`, `created_at`) VALUES
('Tin học', 'TH', 'CĐ', NOW()),
('Công nghệ thông tin', 'CNTT', 'CĐ', NOW()),
('Quản trị mạng máy tính', 'QTM', 'CĐN', NOW()),
('Kỹ thuật sửa chữa, lắp ráp máy tính', 'SCMT', 'CĐN', NOW()),
('Nhiệt lạnh', 'NL', 'CĐ', NOW()),
('Công nghệ Kỹ thuật Điện, Điện tử', 'ĐĐT', 'CĐ', NOW()),
('Công nghệ Kỹ thuật Điện tử - Viễn thông', 'ĐTVT', 'CĐ', NOW()),
('Công nghệ Kỹ thuật Cơ khí', 'CK', 'CĐ', NOW()),
('Công nghệ Kỹ thuật ô tô', 'OTO', 'CĐ', NOW()),
('Công nghệ Kỹ thuật Điều khiển và Tự động hoá', 'TĐ', 'CĐ', NOW());

INSERT INTO `specializations` (`major_id`, `full_name`, `short_name`, `created_at`) VALUES
(1, 'Lập trình Website', 'WEB', NOW()),
(1, 'Lập trình Di động', 'DĐ', NOW()),
(2, 'Lập trình Website', 'WEB', NOW()),
(2, 'Lập trình Di động', 'DĐ', NOW()),
(2, 'Trí tuệ nhân tạo', 'AI', NOW());
 
INSERT INTO `classrooms` (`major_id`, `class_of`, `specialization_id`, `letter`, `short_name`, `updated_at`, `created_at`) VALUES 
(1, 23, 1, 'C', 'CĐ TH 23 WEB C', NOW(), NOW()),
(1, 23, 1, 'B', 'CĐ TH 23 WEB B', NOW(), NOW()),
(2, 25, NULL, 'A', 'CĐ CNTT 25 A', NOW(), NOW()),
(2, 24, 5, 'A', 'CĐ TH 24 AI A', NOW(), NOW()),
(3, 24, NULL, NULL, 'CĐN QTM 24', NOW(), NOW());