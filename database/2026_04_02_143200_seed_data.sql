SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- XÓA DỮ LIỆU CŨ VÀ RESET AUTO_INCREMENT
-- ============================================================================
TRUNCATE TABLE `menu_items`;
TRUNCATE TABLE `menus`;
TRUNCATE TABLE `carousel_slides`;
TRUNCATE TABLE `carousels`;
TRUNCATE TABLE `categories`;
TRUNCATE TABLE `web_settings`;
TRUNCATE TABLE `students`;
TRUNCATE TABLE `teachers`;
TRUNCATE TABLE `accounts`;
TRUNCATE TABLE `classrooms`;
TRUNCATE TABLE `specializations`;
TRUNCATE TABLE `majors`;
TRUNCATE TABLE `departments`;

-- ============================================================================
-- 1. BẢNG DEPARTMENTS
-- ============================================================================
INSERT INTO `departments` (`id`, `full_name`, `short_name`, `description`) VALUES
(1, 'Khoa Công nghệ Thông tin', 'CNTT', 'Khoa đào tạo các ngành CNTT, An ninh mạng, AI...'),
(2, 'Khoa Cơ khí', 'CK', 'Khoa Cơ khí - Chế tạo máy, CNC'),
(3, 'Khoa Điện - Điện tử', 'ĐĐT', 'Khoa Điện công nghiệp, Điện tử viễn thông'),
(4, 'Khoa Ô tô', 'OTO', 'Khoa Công nghệ Kỹ thuật Ô tô'),
(5, 'Khoa Kinh tế - Kế toán', 'KT', 'Kế toán, Quản trị kinh doanh, TMĐT'),
(6, 'Khoa Du lịch - Khách sạn', 'DLKS', 'Quản trị Du lịch và Khách sạn'),
(7, 'Khoa Ngoại ngữ', 'NNA', 'Tiếng Anh thương mại'),
(8, 'Khoa Nhiệt lạnh', 'Nhiệt', 'Máy lạnh, Điều hòa không khí'),
(9, 'Khoa Xây dựng', 'XD', 'Kỹ thuật Xây dựng'),
(10, 'Khoa Thực phẩm - Môi trường', 'TPMT', 'Công nghệ Thực phẩm và Môi trường');


-- ============================================================================
-- 2. BẢNG MAJORS (10 ngành phổ biến nhất)
-- ============================================================================
INSERT INTO `majors` (`id`, `full_name`, `short_name`, `level`, `department_id`) VALUES
(1, 'Công nghệ Kỹ thuật Ô tô', 'OTO', 'CĐ', 4),
(2, 'Công nghệ Thông tin', 'CNTT', 'CĐ', 1),
(3, 'Công nghệ Kỹ thuật Điện, Điện tử', 'ĐĐT', 'CĐ', 3),
(4, 'Công nghệ Kỹ thuật Cơ khí', 'CK', 'CĐ', 2),
(5, 'Kế toán tin học', 'KT', 'CĐ', 5),
(6, 'Công nghệ Kỹ thuật Bán dẫn và Vi mạch', 'BDVM', 'CĐ', 3),
(7, 'Công nghệ Kỹ thuật Nhiệt (Cơ điện lạnh)', 'Nhiệt', 'CĐ', 8),
(8, 'Công nghệ Kỹ thuật Điều khiển và Tự động hóa', 'TĐH', 'CĐ', 3),
(9, 'Công nghệ Kỹ thuật Cơ điện tử', 'CĐT', 'CĐ', 2),
(10, 'Quản trị mạng máy tính', 'QTMMT', 'CĐ', 1);


-- ============================================================================
-- 3. BẢNG SPECIALIZATIONS
-- ============================================================================
INSERT INTO `specializations` (`id`, `major_id`, `full_name`, `short_name`) VALUES
(1, 1, 'Công nghệ Ô tô hiện đại', 'OTDM'),
(2, 2, 'Lập trình Phần mềm', 'PM'),
(3, 2, 'Trí tuệ nhân tạo', 'AI'),
(4, 2, 'An ninh mạng', 'ANM'),
(5, 3, 'Điện công nghiệp', 'DCN'),
(6, 3, 'Điện tử công nghiệp', 'ĐTCN'),
(7, 4, 'Cơ khí chế tạo', 'CTM'),
(8, 4, 'Cơ khí CNC', 'CNC'),
(9, 5, 'Kế toán doanh nghiệp', 'KTDN'),
(10, 6, 'Công nghệ Bán dẫn', 'CNBD'),
(11, 7, 'Công nghệ Nhiệt lạnh', 'CNHL'),
(12, 8, 'Tự động hóa công nghiệp', 'TĐHCN'),
(13, 9, 'Cơ điện tử', 'CĐT'),
(14, 10, 'Quản trị mạng máy tính', 'QTMMT');


-- ============================================================================
-- 4. BẢNG ACCOUNTS
-- ============================================================================
INSERT INTO `accounts` (`id`, `email`, `password_hash`, `role`) VALUES
(1, 'admin@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),

-- 20 Giảng viên
(2, 'gv_dungnv@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
(3, 'gv_mailt@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
(4, 'gv_hungpv@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
(5, 'gv_lantt@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
(6, 'gv_minhhv@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
(7, 'gv_tuanha@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
(8, 'gv_huonglm@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
(9, 'gv_binhnq@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
(10, 'gv_thuyvt@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
(11, 'gv_khaidm@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
(12, 'gv_linhpt@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
(13, 'gv_sonnt@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
(14, 'gv_quynhdt@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
(15, 'gv_phonglh@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
(16, 'gv_ngocbt@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
(17, 'gv_haidq@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
(18, 'gv_trangnt@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
(19, 'gv_khoapv@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
(20, 'gv_anhntt@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
(21, 'gv_cuonglm@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),

-- 20 Sinh viên
(22, '0306231001@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
(23, '0306231002@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
(24, '0306231003@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
(25, '0306231004@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
(26, '0306231005@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
(27, '0306241001@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
(28, '0306241002@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
(29, '0306241003@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
(30, '0306241004@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
(31, '0306241005@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
(32, '0306251001@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
(33, '0306251002@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
(34, '0306251003@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
(35, '0306251004@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
(36, '0306251005@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
(37, '0306261001@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
(38, '0306261002@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
(39, '0306261003@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
(40, '0306261004@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
(41, '0306261005@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student');


-- ---------------------------------------------------------------------------- 
-- 5. BẢNG CLASSROOMS
-- ---------------------------------------------------------------------------- 
INSERT INTO `classrooms` (`id`, `major_id`, `specialization_id`, `homeroom_teacher_id`, `class_of`, `letter`, `short_name`) VALUES 
(1, 2, 2, 1, 23, 'A', 'CĐCNTT23A'),
(2, 2, 4, 6, 23, 'B', 'CĐANM23B'),
(3, 1, 1, 5, 24, 'A', 'CĐOTO24A'),
(4, 3, 5, 7, 24, 'C', 'CĐĐĐT24C'),
(5, 4, 7, 3, 23, 'A', 'CĐCK23A'),
(6, 5, 9, 2, 25, 'A', 'CĐKT25A'),
(7, 6, 10, 4, 25, 'B', 'CĐQTKD25B'),
(8, 8, 12, 18, 23, 'C', 'CĐTĐH23C'),
(9, 7, 11, 12, 24, 'A', 'CĐNhiệt24A'),
(10, 9, 13, 11, 24, 'B', 'CĐCĐT24B'),
(11, 10, 14, 13, 25, 'A', 'CĐQTMMT25A'),
(12, 2, 3, 4, 26, 'A', 'CĐAI26A'),
(13, 3, 6, 8, 26, 'B', 'CĐDCN26B'),
(14, 1, NULL, 9, 24, 'A', 'CĐOTODM24A'),
(15, 5, NULL, 17, 25, 'A', 'CĐKTDN25A'),
(16, 4, 8, 15, 23, 'C', 'CĐCTM23C'),
(17, 7, NULL, 16, 24, 'A', 'CĐCNHL24A'),
(18, 8, NULL, 14, 25, 'B', 'CĐTĐH25B'),
(19, 9, NULL, 3, 26, 'A', 'CĐCĐT26A'),
(20, 10, NULL, 12, 26, 'C', 'CĐQTMMT26C');


-- ---------------------------------------------------------------------------- 
-- 6. BẢNG TEACHERS (Thêm dob và address)
-- ---------------------------------------------------------------------------- 
INSERT INTO `teachers` (`id`, `account_id`, `staff_code`, `full_name`, `gender`, `phone`, `national_id`, `degree`, `position`, `department_id`, `dob`, `address`) VALUES
(1, 2, 'GV001', 'Nguyễn Văn Dũng', 'male', '0901000001', '079082000001', 'Tiến sĩ', 'Trưởng khoa', 1, '1978-05-12', '65 Huỳnh Thúc Kháng, Q.1, TP.HCM'),
(2, 3, 'GV002', 'Lê Thị Mai', 'female', '0901000002', '079082000002', 'Thạc sĩ', 'Trưởng bộ môn', 5, '1985-11-20', '123 Nguyễn Thị Minh Khai, Q.3, TP.HCM'),
(3, 4, 'GV003', 'Phạm Văn Hùng', 'male', '0901000003', '079082000003', 'Thạc sĩ', 'Giảng viên', 2, '1982-03-15', '45 Lê Văn Sỹ, Q.Phú Nhuận'),
(4, 5, 'GV004', 'Trương Thị Lan', 'female', '0901000004', '079082000004', 'Thạc sĩ', 'Giảng viên', 5, '1987-07-08', '78 Võ Văn Tần, Q.3, TP.HCM'),
(5, 6, 'GV005', 'Hoàng Văn Minh', 'male', '0901000005', '079082000005', 'Tiến sĩ', 'Phó trưởng khoa', 4, '1979-09-25', '12 Pasteur, Q.1, TP.HCM'),
(6, 7, 'GV006', 'Hoàng Anh Tuấn', 'male', '0901000006', '079082000006', 'Thạc sĩ', 'Giảng viên', 1, '1984-01-30', '56 Nguyễn Đình Chiểu, Q.3'),
(7, 8, 'GV007', 'Lê Minh Hương', 'female', '0901000007', '079082000007', 'Cử nhân', 'Trợ giảng', 3, '1990-06-14', '89 Bà Huyện Thanh Quan, Q.3'),
(8, 9, 'GV008', 'Nguyễn Quốc Bình', 'male', '0901000008', '079082000008', 'Thạc sĩ', 'Giảng viên', 7, '1986-12-05', '34 Trần Quốc Thảo, Q.3'),
(9, 10, 'GV009', 'Vũ Thị Thủy', 'female', '0901000009', '079082000009', 'Thạc sĩ', 'Giảng viên', 6, '1988-04-22', '67 Nguyễn Văn Trỗi, Q.Phú Nhuận'),
(10, 11, 'GV010', 'Đinh Minh Khải', 'male', '0901000010', '079082000010', 'Tiến sĩ', 'Trưởng bộ môn', 9, '1975-08-10', '112 Lê Lợi, Q.1'),
(11, 12, 'GV011', 'Phạm Thị Linh', 'female', '0901000011', '079082000011', 'Thạc sĩ', 'Giảng viên', 8, '1989-02-18', '45 Nguyễn Đình Chiểu, Q.3'),
(12, 13, 'GV012', 'Nguyễn Thanh Sơn', 'male', '0901000012', '079082000012', 'Thạc sĩ', 'Giảng viên', 8, '1983-10-03', '23 Pasteur, Q.1'),
(13, 14, 'GV013', 'Đặng Thúy Quỳnh', 'female', '0901000013', '079082000013', 'Thạc sĩ', 'Giảng viên', 5, '1986-05-27', '78 Võ Thị Sáu, Q.3'),
(14, 15, 'GV014', 'Lý Hoàng Phong', 'male', '0901000014', '079082000014', 'Tiến sĩ', 'Trưởng bộ môn', 9, '1980-11-15', '90 Nguyễn Thị Minh Khai, Q.3'),
(15, 16, 'GV015', 'Bùi Thị Ngọc', 'female', '0901000015', '079082000015', 'Thạc sĩ', 'Giảng viên', 10, '1987-09-09', '12 Nguyễn Văn Trỗi, Q.Phú Nhuận'),
(16, 17, 'GV016', 'Đỗ Quang Hải', 'male', '0901000016', '079082000016', 'Thạc sĩ', 'Giảng viên', 10, '1984-07-21', '56 Lê Văn Sỹ, Q.Phú Nhuận'),
(17, 18, 'GV017', 'Nguyễn Thu Trang', 'female', '0901000017', '079082000017', 'Thạc sĩ', 'Giảng viên', 6, '1989-03-12', '34 Bà Huyện Thanh Quan, Q.3'),
(18, 19, 'GV018', 'Phan Văn Khoa', 'male', '0901000018', '079082000018', 'Tiến sĩ', 'Phó trưởng khoa', 2, '1977-06-05', '78 Huỳnh Thúc Kháng, Q.1'),
(19, 20, 'GV019', 'Nguyễn Thị Thanh Anh', 'female', '0901000019', '079082000019', 'Thạc sĩ', 'Giảng viên', 7, '1985-01-18', '45 Võ Văn Tần, Q.3'),
(20, 21, 'GV020', 'Lê Mạnh Cường', 'male', '0901000020', '079082000020', 'Thạc sĩ', 'Giảng viên', 1, '1982-08-30', '67 Nguyễn Đình Chiểu, Q.3');


-- ---------------------------------------------------------------------------- 
-- 7. BẢNG STUDENTS (Thêm dob)
-- ---------------------------------------------------------------------------- 
INSERT INTO `students` (`id`, `account_id`, `student_id`, `full_name`, `gender`, `phone`, `classroom_id`, `national_id`, `birth_place`, `dob`) VALUES
(1, 22, '0306231001', 'Trần Anh Tuấn', 'male', '0388000001', 1, '079205000001', 'TP. Hồ Chí Minh', '2005-03-15'),
(2, 23, '0306231002', 'Lê Minh Hương', 'female', '0388000002', 1, '079205000002', 'Long An', '2005-07-22'),
(3, 24, '0306231003', 'Nguyễn Hoàng Nam', 'male', '0388000003', 2, '079205000003', 'Đồng Nai', '2005-11-08'),
(4, 25, '0306231004', 'Phạm Thùy Chi', 'female', '0388000004', 3, '079205000004', 'Tiền Giang', '2005-02-19'),
(5, 26, '0306231005', 'Võ Quốc Bảo', 'male', '0388000005', 4, '079205000005', 'Bình Dương', '2005-09-30'),
(6, 27, '0306241001', 'Trịnh Bích Ngọc', 'female', '0388000006', 5, '079205000006', 'Bến Tre', '2006-01-12'),
(7, 28, '0306241002', 'Hoàng Minh Khôi', 'male', '0388000007', 6, '079205000007', 'Vĩnh Long', '2006-04-25'),
(8, 29, '0306241003', 'Đinh Thanh Trúc', 'female', '0388000008', 7, '079205000008', 'Cần Thơ', '2006-06-07'),
(9, 30, '0306241004', 'Phan Hữu Thắng', 'male', '0388000009', 8, '079205000009', 'An Giang', '2006-08-19'),
(10, 31, '0306241005', 'Ngô Lan Anh', 'female', '0388000010', 9, '079205000010', 'Bà Rịa - Vũng Tàu', '2006-10-03'),
(11, 32, '0306251001', 'Lý Quang Hải', 'male', '0388000011', 10, '079205000011', 'Tây Ninh', '2007-03-28'),
(12, 33, '0306251002', 'Đỗ Mai Phương', 'female', '0388000012', 11, '079205000012', 'Bình Phước', '2007-05-14'),
(13, 34, '0306251003', 'Đặng Tuấn Tài', 'male', '0388000013', 12, '079205000013', 'Lâm Đồng', '2007-07-09'),
(14, 35, '0306251004', 'Bùi Ngọc Yến', 'female', '0388000014', 13, '079205000014', 'Khánh Hòa', '2007-09-21'),
(15, 36, '0306251005', 'Đoàn Nhật Minh', 'male', '0388000015', 14, '079205000015', 'Phú Yên', '2007-11-05'),
(16, 37, '0306261001', 'Hồ Yến Nhi', 'female', '0388000016', 15, '079205000016', 'Bình Định', '2008-02-17'),
(17, 38, '0306261002', 'Châu Gia Bảo', 'male', '0388000017', 16, '079205000017', 'Quảng Ngãi', '2008-04-29'),
(18, 39, '0306261003', 'Mạch Thảo Ly', 'female', '0388000018', 17, '079205000018', 'Quảng Nam', '2008-06-11'),
(19, 40, '0306261004', 'Tạ Thành Đạt', 'male', '0388000019', 18, '079205000019', 'Đà Nẵng', '2008-08-23'),
(20, 41, '0306261005', 'Văn Như Quỳnh', 'female', '0388000020', 20, '079205000020', 'Thừa Thiên Huế', '2008-10-07');


-- ---------------------------------------------------------------------------- 
-- 8. BẢNG WEB_SETTINGS
-- ---------------------------------------------------------------------------- 
INSERT INTO `web_settings` (`key`, `group`, `group_label`, `type`, `value`, `label`) VALUES
('site_name', 'general', 'General', 'string', 'Trường Cao đẳng Kỹ thuật Cao Thắng', 'Tên trường'),
('site_logo', 'general', 'General', 'url', '/images/logo.png', 'Logo website'),
('site_favicon', 'general', 'General', 'url', '/images/favicon.ico', 'Favicon'),
('contact_hotline', 'contact', 'Contact', 'string', '028 3821 2360', 'Hotline'),
('contact_email', 'contact', 'Contact', 'email', 'phongdaotao@caothang.edu.vn', 'Email hỗ trợ'),
('address_main', 'contact', 'Contact', 'text', '65 Huỳnh Thúc Kháng, Phường Bến Nghé, Quận 1, TP.HCM', 'Địa chỉ'),
('social_facebook', 'social', 'Social', 'url', 'https://facebook.com/caothang.edu.vn', 'Facebook'),
('social_youtube', 'social', 'Social', 'url', 'https://youtube.com/caothang', 'Youtube'),
('seo_meta_desc', 'seo', 'SEO', 'text', 'Trường Cao đẳng Kỹ thuật Cao Thắng - Đào tạo chất lượng cao', 'SEO Description'),
('seo_meta_keywords', 'seo', 'SEO', 'text', 'cao thang, cntt, ô tô, cơ khí, kế toán', 'SEO Keywords'),
('maintenance_mode', 'system', 'System', 'bool', '0', 'Bảo trì hệ thống'),
('pagination_limit', 'ui', 'UI', 'int', '15', 'Số lượng mỗi trang'),
('theme_color_primary', 'ui', 'UI', 'string', '#1E3A8A', 'Màu chủ đạo'),
('theme_color_secondary', 'ui', 'UI', 'string', '#F59E0B', 'Màu phụ'),
('admission_status', 'system', 'System', 'bool', '1', 'Đang mở tuyển sinh'),
('notification_banner', 'ui', 'UI', 'text', 'Chào mừng năm học 2026 - 2027!', 'Banner thông báo'),
('copyright_text', 'general', 'General', 'string', '© 2026 Trường Cao đẳng Kỹ thuật Cao Thắng', 'Bản quyền');


-- ---------------------------------------------------------------------------- 
-- 9. BẢNG CATEGORIES
-- ---------------------------------------------------------------------------- 
INSERT INTO `categories` (`id`, `name`, `slug`, `type`, `parent_id`) VALUES
(1, 'Tin tức sự kiện', 'tin-tuc-su-kien', 'const', NULL),
(2, 'Hoạt động nổi bật', 'hoat-dong-noi-bat', 'custom', 1),
(3, 'Đoàn Thanh niên', 'doan-thanh-nien', 'custom', 1),
(4, 'Đào tạo', 'dao-tao', 'const', NULL),
(5, 'Thông báo học vụ', 'thong-bao-hoc-vu', 'custom', 4),
(6, 'Lịch thi', 'lich-thi', 'custom', 4),
(7, 'Sinh viên', 'sinh-vien', 'const', NULL),
(8, 'Học bổng', 'hoc-bong', 'custom', 7),
(9, 'Việc làm', 'viec-lam', 'custom', 7),
(10, 'Tuyển sinh', 'tuyen-sinh', 'const', NULL),
(11, 'Thông báo tuyển sinh', 'thong-bao-tuyen-sinh', 'custom', 10),
(12, 'Hướng nghiệp', 'huong-nghiep', 'custom', 10),
(13, 'Doanh nghiệp', 'doanh-nghiep', 'const', NULL),
(14, 'Cơ hội việc làm', 'co-hoi-viec-lam', 'custom', 13),
(15, 'Khoa học Công nghệ', 'khoa-hoc-cong-nghe', 'const', NULL),
(16, 'Nghiên cứu khoa học', 'nghien-cuu-khoa-hoc', 'custom', 15),
(17, 'Sáng tạo khởi nghiệp', 'sang-tao-khoi-nghiep', 'custom', 15),
(18, 'Góc sinh viên', 'goc-sinh-vien', 'custom', 7),
(19, 'Tin nội bộ', 'tin-noi-bo', 'custom', 1),
(20, 'Sự kiện', 'su-kien', 'custom', 1);


-- ---------------------------------------------------------------------------- 
-- 10. BẢNG MENUS & MENU_ITEMS (Có parent_id để tạo menu cha-con)
-- ---------------------------------------------------------------------------- 
INSERT INTO `menus` (`id`, `key`, `label`) VALUES 
(1, 'header_menu', 'Menu Chính'),
(2, 'footer_menu', 'Menu Chân Trang'),
(3, 'student_menu', 'Menu Sinh Viên');

INSERT INTO `menu_items` (`id`, `menu_id`, `parent_id`, `label`, `url`, `sort_order`) VALUES
-- Header Menu
(1, 1, NULL, 'Trang chủ', '/', 1),
(2, 1, NULL, 'Giới thiệu', '/gioi-thieu', 2),
(3, 1, NULL, 'Tuyển sinh', '/tuyen-sinh', 3),
(4, 1, NULL, 'Đào tạo', '/dao-tao', 4),
(5, 1, 4, 'Các ngành đào tạo', '/dao-tao/nganh-hoc', 1),
(6, 1, 4, 'Chuẩn đầu ra', '/dao-tao/chuan-dau-ra', 2),
(7, 1, NULL, 'Tin tức', '/tin-tuc', 5),
(8, 1, NULL, 'Liên hệ', '/lien-he', 6),

-- Footer Menu
(9, 2, NULL, 'Chính sách bảo mật', '/privacy', 1),
(10, 2, NULL, 'Điều khoản sử dụng', '/terms', 2),
(11, 2, NULL, 'Sơ đồ website', '/sitemap', 3),

-- Student Menu (có parent_id)
(12, 3, NULL, 'Học tập', NULL, 1),
(13, 3, 12, 'Thời khóa biểu', '/sinhvien/thoi-khoa-bieu', 1),
(14, 3, 12, 'Kết quả học tập', '/sinhvien/diem', 2),
(15, 3, NULL, 'Học phí', '/sinhvien/hoc-phi', 2),
(16, 3, NULL, 'Đăng ký học phần', '/sinhvien/dang-ky', 3),
(17, 3, NULL, 'Biểu mẫu', '/sinhvien/bieu-mau', 4);

SET FOREIGN_KEY_CHECKS = 1;