CREATE TABLE `classrooms` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `major_id` bigint,
  `class_of` int,
  `specialization_id` bigint NULL,
  `letter` varchar(1) NULL,
  `short_name` varchar(20) UNIQUE,
  `updated_at` datetime,
  `created_at` datetime,
  `deleted_at` datetime
);

CREATE TABLE `majors` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `full_name` varchar(100),
  `short_name` varchar(20) UNIQUE,
  `level` varchar(5),
  `updated_at` datetime,
  `created_at` datetime,
  `deleted_at` datetime
);

CREATE TABLE `specializations` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `major_id` bigint,
  `full_name` varchar(100),
  `short_name` varchar(20) UNIQUE,
  `updated_at` datetime,
  `created_at` datetime,
  `deleted_at` datetime
);

ALTER TABLE `students` ADD FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`);

ALTER TABLE `classrooms` ADD FOREIGN KEY (`major_id`) REFERENCES `majors` (`id`);

ALTER TABLE `specializations` ADD FOREIGN KEY (`major_id`) REFERENCES `majors` (`id`);

ALTER TABLE `classrooms` ADD FOREIGN KEY (`specialization_id`) REFERENCES `specializations` (`id`);

-- add sample data
INSERT INTO `majors` (`full_name`, `short_name`, `level`, `created_at`) VALUES
('Tin học', 'TH', 'CĐ', NOW()),
('Công nghệ thông tin', 'CNTT', 'CĐ', NOW()),
('Quản trị mạng máy tính', 'QTM', 'CĐN', NOW()),
('Kỹ thuật sửa chữa, lắp ráp máy tính', 'SCMT', 'CĐN', NOW()),
('Nhiệt lạnh', 'NL', 'CĐ', NOW()),
('Công nghệ Kỹ thuật Điện, Điện tử', 'ĐĐT', 'CĐ', NOW()),
('Công nghệ Kỹ thuật Điện tử - Viễn thông', 'ĐTVT', 'CĐ', NOW()),
('Công nghệ Kỹ thuật Cơ khí', 'CK', 'CĐ', NOW()),
('Công nghệ Kỹ thuật ô tô', 'OTO', 'CĐ', NOW());
('Công nghệ Kỹ thuật Điền khiển và Tự động hoá', 'TĐ', 'CĐ', NOW());

INSERT INTO `specializations` (`major_id`, `full_name`, `short_name`, `created_at`) VALUES
(1, 'Lập trình Website', 'WEB', NOW()),
(1, 'Lập trình Di động', 'DĐ', NOW()),
(2, 'Lập trình Website', 'WEB', NOW()),
(2, 'Lập trình Di động', 'DĐ', NOW()),
(2, 'Trí tuệ nhân tạo', 'AI', NOW());
 
INSERT INTO `classrooms` (`major_id`, `class_of`, `specialization_id`, `letter`, `short_name`, `updated_at`) VALUES 
(1, 23, 1, 'A', 'CĐ TH 23 WEB C', NOW()),
(1, 23, 1, 'B', 'CĐ TH 23 WEB B', NOW()),
(2, 25, NULL, 'A', 'CĐ CNTT 25 A', NOW()),
(2, 24, 1, 'A', 'CĐ TH 24 AI A', NOW()),
(3, 24, NULL, NULL, 'CĐN QTM 24', NOW());