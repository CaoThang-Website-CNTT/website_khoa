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
TRUNCATE TABLE `category_post`;
TRUNCATE TABLE `media`;
TRUNCATE TABLE `posts`;

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
INSERT INTO `teachers` (`id`, `account_id`, `full_name`, `gender`, `phone`, `national_id`, `degree`, `position`, `department_id`, `dob`, `address`) VALUES
(1, 2, 'Nguyễn Văn Dũng', 'male', '0901000001', '079082000001', 'Tiến sĩ', 'Trưởng khoa', 1, '1978-05-12', '65 Huỳnh Thúc Kháng, Q.1, TP.HCM'),
(2, 3, 'Lê Thị Mai', 'female', '0901000002', '079082000002', 'Thạc sĩ', 'Trưởng bộ môn', 5, '1985-11-20', '123 Nguyễn Thị Minh Khai, Q.3, TP.HCM'),
(3, 4, 'Phạm Văn Hùng', 'male', '0901000003', '079082000003', 'Thạc sĩ', 'Giảng viên', 2, '1982-03-15', '45 Lê Văn Sỹ, Q.Phú Nhuận'),
(4, 5, 'Trương Thị Lan', 'female', '0901000004', '079082000004', 'Thạc sĩ', 'Giảng viên', 5, '1987-07-08', '78 Võ Văn Tần, Q.3, TP.HCM'),
(5, 6, 'Hoàng Văn Minh', 'male', '0901000005', '079082000005', 'Tiến sĩ', 'Phó trưởng khoa', 4, '1979-09-25', '12 Pasteur, Q.1, TP.HCM'),
(6, 7, 'Hoàng Anh Tuấn', 'male', '0901000006', '079082000006', 'Thạc sĩ', 'Giảng viên', 1, '1984-01-30', '56 Nguyễn Đình Chiểu, Q.3'),
(7, 8, 'Lê Minh Hương', 'female', '0901000007', '079082000007', 'Cử nhân', 'Trợ giảng', 3, '1990-06-14', '89 Bà Huyện Thanh Quan, Q.3'),
(8, 9, 'Nguyễn Quốc Bình', 'male', '0901000008', '079082000008', 'Thạc sĩ', 'Giảng viên', 7, '1986-12-05', '34 Trần Quốc Thảo, Q.3'),
(9, 10, 'Vũ Thị Thủy', 'female', '0901000009', '079082000009', 'Thạc sĩ', 'Giảng viên', 6, '1988-04-22', '67 Nguyễn Văn Trỗi, Q.Phú Nhuận'),
(10, 11, 'Đinh Minh Khải', 'male', '0901000010', '079082000010', 'Tiến sĩ', 'Trưởng bộ môn', 9, '1975-08-10', '112 Lê Lợi, Q.1'),
(11, 12, 'Phạm Thị Linh', 'female', '0901000011', '079082000011', 'Thạc sĩ', 'Giảng viên', 8, '1989-02-18', '45 Nguyễn Đình Chiểu, Q.3'),
(12, 13, 'Nguyễn Thanh Sơn', 'male', '0901000012', '079082000012', 'Thạc sĩ', 'Giảng viên', 8, '1983-10-03', '23 Pasteur, Q.1'),
(13, 14, 'Đặng Thúy Quỳnh', 'female', '0901000013', '079082000013', 'Thạc sĩ', 'Giảng viên', 5, '1986-05-27', '78 Võ Thị Sáu, Q.3'),
(14, 15, 'Lý Hoàng Phong', 'male', '0901000014', '079082000014', 'Tiến sĩ', 'Trưởng bộ môn', 9, '1980-11-15', '90 Nguyễn Thị Minh Khai, Q.3'),
(15, 16, 'Bùi Thị Ngọc', 'female', '0901000015', '079082000015', 'Thạc sĩ', 'Giảng viên', 10, '1987-09-09', '12 Nguyễn Văn Trỗi, Q.Phú Nhuận'),
(16, 17, 'Đỗ Quang Hải', 'male', '0901000016', '079082000016', 'Thạc sĩ', 'Giảng viên', 10, '1984-07-21', '56 Lê Văn Sỹ, Q.Phú Nhuận'),
(17, 18, 'Nguyễn Thu Trang', 'female', '0901000017', '079082000017', 'Thạc sĩ', 'Giảng viên', 6, '1989-03-12', '34 Bà Huyện Thanh Quan, Q.3'),
(18, 19, 'Phan Văn Khoa', 'male', '0901000018', '079082000018', 'Tiến sĩ', 'Phó trưởng khoa', 2, '1977-06-05', '78 Huỳnh Thúc Kháng, Q.1'),
(19, 20, 'Nguyễn Thị Thanh Anh', 'female', '0901000019', '079082000019', 'Thạc sĩ', 'Giảng viên', 7, '1985-01-18', '45 Võ Văn Tần, Q.3'),
(20, 21, 'Lê Mạnh Cường', 'male', '0901000020', '079082000020', 'Thạc sĩ', 'Giảng viên', 1, '1982-08-30', '67 Nguyễn Đình Chiểu, Q.3');


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
INSERT INTO `menus` (`id`, `key`, `label`, `type`) VALUES 
(1, 'header_menu', 'Menu Chính', 'const'),
(2, 'footer_menu', 'Menu Chân Trang', 'const'),
(3, 'student_menu', 'Menu Sinh Viên', 'const'),
(4, 'navbar_menu', 'Menu điều hướng', 'custom');

INSERT INTO `menu_items` (`id`, `menu_id`, `parent_id`, `label`, `url`, `sort_order`, `type`) VALUES
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
(17, 3, NULL, 'Biểu mẫu', '/sinhvien/bieu-mau', 4),

-- Navbar Menu
(18, 4, NULL, 'Giới thiệu', '/gioi-thieu', 1),
(19, 4, NULL, 'Tuyển sinh', '/tuyen-sinh', 2),
(20, 4, NULL, 'Đào tạo', '/dao-tao', 3),
(21, 4, 20, 'Các ngành đào tạo', '/dao-tao/nganh-hoc', 1),
(22, 4, 20, 'Chuẩn đầu ra', '/dao-tao/chuan-dau-ra', 2),
(23, 4, NULL, 'Tin tức', '/tin-tuc', 4),
(24, 4, NULL, 'Liên hệ', '/lien-he', 5);

-- ---------------------------------------------------------------------------- 
-- 11. BẢNG CAROUSELS & CAROUSEL_SLIDES
-- ---------------------------------------------------------------------------- 
INSERT INTO `carousels` (`id`, `name`, `slug`, `is_active`) VALUES 
(1, 'Trang chủ (Landing Page)', 'landing-page', 1);

-- Thêm dữ liệu cho bảng carousel_slides (4 slides)
INSERT INTO `carousel_slides` 
(`id`, `carousel_id`, `title`, `title_highlight`, `description`, `image_path`, `cta_label`, `cta_url`, `custom_html`, `sort_order`) 
VALUES
(1, 1, 'Môi trường học tập', 'Chuyên nghiệp & Sáng tạo.', 'Không gian học tập mở, khuyến khích sự sáng tạo và hợp tác, với sự hỗ trợ từ đội ngũ giảng viên giàu kinh nghiệm và tận tâm.', 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=1920&q=80', 'Tìm hiểu thêm', NULL, NULL, 1),
(2, 1, 'Môi trường học tập', 'Chuyên nghiệp & Sáng tạo.', 'Không gian học tập mở, khuyến khích sự sáng tạo và hợp tác, với sự hỗ trợ từ đội ngũ giảng viên giàu kinh nghiệm và tận tâm.', 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1920&q=80', 'Tìm hiểu thêm', NULL, NULL, 2),
(3, 1, 'Môi trường học tập', 'Chuyên nghiệp & Sáng tạo.', 'Không gian học tập mở, khuyến khích sự sáng tạo và hợp tác, với sự hỗ trợ từ đội ngũ giảng viên giàu kinh nghiệm và tận tâm.', 'https://images.unsplash.com/photo-1513258496099-48168024aec0?auto=format&fit=crop&w=1920&q=80', 'Tìm hiểu thêm', NULL, NULL, 3),
(4, 1, 'Môi trường học tập', 'Chuyên nghiệp & Sáng tạo.', 'Không gian học tập mở, khuyến khích sự sáng tạo và hợp tác, với sự hỗ trợ từ đội ngũ giảng viên giàu kinh nghiệm và tận tâm.', 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?auto=format&fit=crop&w=1920&q=80', 'Tìm hiểu thêm', NULL, NULL, 4);

-- ============================================================================
-- 15. BẢNG POSTS
-- ============================================================================
-- POSTS (compact format - 1 line per post)
INSERT INTO `posts` (`id`, `title`, `slug`, `content_json`, `author_id`, `status`, `view_count`, `seo_description`, `seo_image_url`, `created_at`, `updated_at`, `published_at`) VALUES
(1, 'Thông báo danh sách và thời gian đăng ký nguyện vọng ĐATN Khoá 2023', 'thong-bao-danh-sach-dang-ky-nguyen-vong-datn-cdth-2023', '[{"id":"69d6970af0bb0","type":"paragraph","version":1,"order":0,"data":{"text":"Khoa Công nghệ thông tin thông báo danh sách và thời gian đăng ký nguyện vọng thực hiện đề tài Đồ án tốt nghiệp (ĐATN) sinh viên Cao đẳng Tin học Khoá 2023 và Khoá trước chưa hoàn thành học phần Đồ án tốt nghiệp:","marks":[]}},{"id":"69d6970af0bbc","type":"list","version":1,"order":1,"data":{"style":"unordered","items":[{"text":"Sinh viên đăng ký nguyện vọng thực hiện đề tài: từ 30/03/2026 - 04/04/2026","marks":[],"children":[{"text":"Form đăng ký: tại đây","marks":[{"type":"link","start":14,"end":21,"attrs":{"href":"https://tinyurl.com/dknv-detaidatn-20252","title":"","target":"_blank"}}],"children":[]},{"text":"Số nguyện vọng đăng ký: tối đa 3","marks":[],"children":[]},{"text":"Danh sách đề tài: tại đây (cập nhật thường xuyên)","marks":[{"type":"link","start":18,"end":25,"attrs":{"href":"https://tinyurl.com/dsdetai-datn-20252","title":"","target":"_blank"}}],"children":[]},{"text":" Sinh viên đăng ký theo nhóm: 2 sinh viên/ nhóm (1 sinh viên đại diện nhóm để đăng ký)","marks":[],"children":[]}]},{"text":"Kết quả xét nguyện vọng (dự kiến): từ 06/04/2026 - 11/04/2026","marks":[],"children":[{"text":"Kết quả xét dựa vào thứ tự ưu tiên:","marks":[],"children":[{"text":"Đề tài theo hướng chuyên ngành học của sinh viên","marks":[],"children":[]},{"text":"Thời gian đăng ký sớm.","marks":[],"children":[]}]},{"text":"Sau khi có kết quả, sinh viên vui lòng chủ động liên hệ GVHD để chuẩn bị cho việc thực hiện đề tài. Trường hợp sinh viên/ nhóm sinh viên không đạt các nguyện vọng đăng ký sẽ được phân công đề tài phù hợp chuyên ngành học của sinh viên/ nhóm sinh viên.","marks":[],"children":[]}]},{"text":"Nhà Trường xét điều kiện dự thi tốt nghiệp (Thực hiện ĐATN): 25/05/2026 - 31/05/2026","marks":[],"children":[]},{"text":"Thời gian thực hiện đề tài ĐATN: 01/06/2026 - 12/07/2026","marks":[],"children":[]}]}},{"id":"69d6970af0bbe","type":"paragraph","version":1,"order":2,"data":{"text":"Sinh viên đủ điều kiện dự thi tốt nghiệp mới chính thức được thực hiện đề tài ĐATN (Xem quy định chi tiết theo sổ tay sinh viên)","marks":[]}}]', 2, 'published', 150, 'Khoa Công nghệ thông tin thông báo danh sách và thời gian đăng ký nguyện vọng thực hiện đề tài Đồ án tốt nghiệp (ĐATN)...', NULL, '2026-03-25 08:00:00', '2026-03-25 08:00:00', '2026-03-25 08:00:00'),
(2, 'Cuộc thi Olympic Tin học Cao Thắng lần thứ 9 - 2026', 'cuoc-thi-olympic-tin-hoc-cao-thang-lan-9-2026', '[{"id":"69d6970af0bc0","type":"paragraph","version":1,"order":0,"data":{"text":"Nhân dịp kỷ niệm 120 năm thành lập Trường Cao đẳng Kỹ thuật Cao Thắng, Khoa Công nghệ Thông tin chính thức tổ chức cuộc thi \"Olympic Tin học Cao Thắng lần thứ 9 - 2026\" 🎉","marks":[]}},{"id":"69d6970af0bc1","type":"list","version":1,"order":1,"data":{"style":"unordered","items":[{"text":"Đây là sân chơi học thuật hấp dẫn dành cho sinh viên đam mê công nghệ, giúp các bạn:","marks":[],"children":[{"text":"✨ Nâng cao kiến thức chuyên môn","marks":[],"children":[]},{"text":"✨ Rèn luyện tư duy logic & kỹ năng lập trình","marks":[],"children":[]},{"text":"✨ Giao lưu, học hỏi và thể hiện bản thân","marks":[],"children":[]}]},{"text":"📌 Đối tượng tham gia:","marks":[],"children":[{"text":"Sinh viên đang học tập tại Khoa Công nghệ Thông tin","marks":[],"children":[]}]},{"text":"📌 Nội dung thi:","marks":[],"children":[{"text":"Bao gồm các lĩnh vực:","marks":[],"children":[]},{"text":" ","marks":[],"children":[]}]},{"text":"📌 Hình thức thi:","marks":[],"children":[{"text":"Trải qua 4 vòng:","marks":[],"children":[]},{"text":" ","marks":[],"children":[]}]},{"text":"📌 Thời gian:","marks":[],"children":[{"text":"🗓️ 23/03 – 03/04/2026: Vòng sơ loại","marks":[],"children":[]},{"text":"🗓️ 06/04 – 17/04/2026: Tứ kết & Bán kết","marks":[],"children":[]},{"text":"🗓️ 20/04 – 24/04/2026: Chung kết","marks":[],"children":[]}]},{"text":"📌 Lệ phí: 150.000 đồng/lớp","marks":[],"children":[]},{"text":"📌 Giải thưởng hấp dẫn:","marks":[],"children":[{"text":"🥇 Giải I: 3.000.000 đồng","marks":[],"children":[]},{"text":"🥈 Giải II: 2.000.000 đồng","marks":[],"children":[]},{"text":"🥉 Giải III: 1.000.000 đồng","marks":[],"children":[]},{"text":"🎁 Cùng nhiều giải thưởng khác: câu hỏi Gold, giải đồng đội, video ấn tượng...","marks":[],"children":[]}]}]}},{"id":"69d6970af0bc2","type":"paragraph","version":1,"order":2,"data":{"text":"🔥 Còn chần chờ gì nữa? Đăng ký ngay để khẳng định bản lĩnh ITer Cao Thắng!","marks":[]}},{"id":"69d6970af0bc3","type":"paragraph","version":1,"order":3,"data":{"text":"👉 Thông tin chi tiết được đăng tải tại fanpage: fb/olympicit.caothang","marks":[]}},{"id":"69d6970af0bc4","type":"paragraph","version":1,"order":4,"data":{"text":"#OlympicTinHoc2026 #CNTTCaoThang #ITChallenge #SinhVienCNTT","marks":[]}}]', 2, 'published', 450, 'Nhân dịp kỷ niệm 120 năm thành lập Trường Cao đẳng Kỹ thuật Cao Thắng, Khoa Công nghệ Thông tin chính thức tổ chức...', NULL, '2026-03-20 08:00:00', '2026-03-20 08:00:00', '2026-03-20 08:00:00'),
(3, 'Thời khoá biểu học kỳ phụ thứ 7, CN HK2 năm học 2025-2026', 'thoi-khoa-bieu-hoc-ky-phu-thu-7-cn-hk2-2025-2026', '[{"id":"69d6970af0ka2","type":"paragraph","version":1,"order":0,"data":{"text":"Khoa Công nghệ thông tin xin thông báo thời khoá biểu học kỳ phụ thứ 7, CN HK2 năm học 2025-2026 cho các lớp sau:","marks":[]}},{"id":"69d6970af0ka3","type":"list","version":1,"order":1,"data":{"style":"ordered","items":[{"text":"Lập trình ASP.NET Core. Tại đây: ","marks":[{"type":"link","start":33,"end":40,"attrs":{"href":"https://cntt.caothang.edu.vn/uploads/media/HKP/2526-HKP2/BM_HKPDot02_2025_2026_NguyenDucDuy.pdf","title":"","target":"_blank"}}],"children":[]}]}},{"id":"69d6970af0ka4","type":"paragraph","version":1,"order":2,"data":{"text":"Lưu ý: ","marks":[]}},{"id":"69d6970af0ka5","type":"list","version":1,"order":3,"data":{"style":"ordered","items":[{"text":"Các bước đăng ký:","marks":[],"children":[{"text":"Bước 1: Sinh viên theo dõi lịch học trên website Khoa/Bộ môn.","marks":[],"children":[]},{"text":"Bước 2: Sau khi có lịch học, sinh viên đăng ký trên phần mềm \"HỌC KỲ PHỤ\" tại máy tính Phòng Đào tạo.","marks":[],"children":[]},{"text":"Bước 3: Đóng kinh phí tại Phòng Tài chính – Kế toán.","marks":[],"children":[]},{"text":"Bước 4: Đi học theo lịch của Khoa/Bộ môn công bố.","marks":[],"children":[]}]},{"text":"Thời gian đóng kinh phí:","marks":[],"children":[{"text":"Từ ngày Khoa/Bộ môn công bố lịch học đến trước khi lớp học bắt đầu.","marks":[],"children":[]}]}]}},{"id":"69d6970af0ka6","type":"paragraph","version":1,"order":4,"data":{"text":"Lưu ý: Đối với những môn sinh viên đăng ký, đóng kinh phí không đủ số lượng từ 20 trở lên thì sẽ huỷ lớp học kỳ phụ đó, sinh viên liên hệ Bàn số 3 Phòng Đào tạo để được hướng dẫn thủ tục hoàn kinh phí.","marks":[]}}]', 3, 'published', 210, 'Khoa Công nghệ thông tin xin thông báo thời khoá biểu học kỳ phụ thứ 7, CN HK2 năm học 2025-2026...', NULL, '2026-03-10 08:00:00', '2026-03-10 08:00:00', '2026-03-10 08:00:00'),
(4, 'Thông tin về thực tập tốt nghiệp và đồ án tốt nghiệp CĐTH 23', 'thong-tin-ve-thuc-tap-tot-nghiep-va-do-an-tot-nghiep-cdth-23', '[{"id":"69d6970af0kb2","type":"paragraph","version":1,"order":0,"data":{"text":"Khoa Công nghệ thông tin xin thông báo một số thông tin về thực tập tốt nghiệp và đồ án tốt nghiệp CĐTH 23 như sau:","marks":[]}},{"id":"69d6970af0kb3","type":"list","version":1,"order":1,"data":{"style":"unordered","items":[{"text":"Các mốc thời gian thực tập:","marks":[],"children":[{"text":"Thời gian thực tập chính thức: Từ 02/03/2026 đến 26/04/2026","marks":[],"children":[]},{"text":"Báo cáo và chấm điểm thực tập (dự kiến): Từ 27/04/2026 đến 02/05/2026","marks":[],"children":[]},{"text":"Sinh viên nộp đơn đăng ký đề tài Đồ án tốt nghiệp có xác nhận GVHD, nộp phiếu đăng ký đề tài: 30/03/2026 - 05/04/2026 (Danh sách đề tài sẽ công bố trước thời gian đăng ký)","marks":[],"children":[]}]},{"text":"Danh sách phân công GVHD:","marks":[],"children":[{"text":"Danh sách GVHD thực tập tốt nghiệp và đồ án tốt nghiệp: tại đây","marks":[{"type":"link","start":56,"end":63,"attrs":{"href":"https://tinyurl.com/hd-tttn-datn-cdth23","title":"","target":"_blank"}}],"children":[]},{"text":"Danh sách email liên hệ GVHD: tại đây","marks":[{"type":"link","start":30,"end":37,"attrs":{"href":"https://tinyurl.com/gvhd-email-2026","title":"","target":"_blank"}}],"children":[]},{"text":"Lưu ý: danh sách Hướng dẫn Đồ án tốt nghiệp chỉ là dự kiến, sinh viên phải đủ điều kiện xét được thi tốt nghiệp của phòng Đào tạo mới thực hiện đề tài ĐATN.","marks":[],"children":[]}]},{"text":"Các tài nguyên:","marks":[],"children":[{"text":"Các tài nguyên/ biểu mẫu/ danh sách các công ty: tại đây","marks":[{"type":"link","start":49,"end":56,"attrs":{"href":"https://tinyurl.com/tainguyen-cdth23","title":"","target":"_blank"}}],"children":[]},{"text":"Đăng ký giấy giới thiệu thực tập: tại đây","marks":[{"type":"link","start":34,"end":41,"attrs":{"href":"https://tinyurl.com/gthieutt-cdth23","title":"","target":"_blank"}}],"children":[]}]}]}},{"id":"69d6970af0kb6","type":"paragraph","version":1,"order":2,"data":{"text":"Đối với sinh viên khóa trước đăng ký thực tập (ghép) chưa có trong danh sách hướng dẫn, sinh viên liên hệ thầy Nguyên qua email: lvhnguyen@caothang.edu.vn để được phổ biến thông tin hướng dẫn.","marks":[]}}]', 2, 'published', 325, 'Khoa Công nghệ thông tin xin thông báo một số thông tin về thực tập tốt nghiệp và đồ án tốt nghiệp CĐTH 23 như sau:...', NULL, '2026-02-15 08:00:00', '2026-02-15 08:00:00', '2026-02-15 08:00:00'),
(5, 'Thời khoá biểu thi lần 2 tuần 22, 23 HK1 năm học 2025-2026', 'thoi-khoa-bieu-thi-lan-2-tuan-22-23-hk1-nam-hoc-2025-2026', '[{"id":"69d6970af0kc2","type":"paragraph","version":1,"order":0,"data":{"text":"Khoa Công nghệ thông tin xin thông báo thời khoá biểu thi lần 2 tuần 22, 23 HK1 năm học 2025-2026 như sau:","marks":[]}},{"id":"69d6970af0kc3","type":"table","version":1,"order":1,"data":{"hasHeader":true,"rows":[["Lớp","Học phần","Thời gian thi lần 2","Phòng thi"],["CĐN QTM 25AB","Tin học ƯD","Tuần 22 - T7 (24/01/26) - 09h00","F7.12"],["CĐN SCMT 25","Tin học ƯD","Tuần 22 - T7 (24/01/26) - 09h00","F7.15"],["CĐ CNTT 25ABCDEF","Tin học ƯD","Tuần 22 - T7 (24/01/26) - 13h00","F7.12, F7.15"],["CĐ CNTT 25ABCDEF","Toán RR & LTĐT","Tuần 22 - T7 (24/01/26) - 15h00","F7.16: CĐ CNTT 25 AB, F7.14: CĐ CNTT 25 CD, F6.16: CĐ CNTT 25 EF"],["CĐ CNTT 25ABCDEF","PCMT","Tuần 23 - T7 (31/01/26) - 07h00","F7.16"],["CĐN QTM 25AB","Mạng MT","Tuần 23 - T7 (31/01/26) - 09h00","F7.16"],["CĐN SCMT 25","Mạng MT","Tuần 23 - T7 (31/01/26) - 09h00","F7.14"],["CĐ CNTT 25ABCDEF","NM lập trình","Tuần 23 - CN (01/02/26) - 07h00","F7.16, F7.14, F5.14"],["CĐN QTM 25AB","NM lập trình","Tuần 23 - CN (01/02/26) - 07h00","F6.16"]]}}]', 3, 'published', 280, 'Khoa Công nghệ thông tin xin thông báo thời khoá biểu thi lần 2 tuần 22, 23 HK1 năm học 2025-2026 như sau:...', NULL, '2026-01-20 08:00:00', '2026-01-20 08:00:00', '2026-01-20 08:00:00'),
(6, 'Lịch sinh hoạt phổ biến thông tin thực tập tốt nghiệp CĐ TH 23', 'lich-sinh-hoat-pho-bien-thong-tin-thuc-tap-tot-nghiep-cd-th-23', '[{"id":"69d6970af0kd2","type":"paragraph","version":1,"order":0,"data":{"text":"Khoa CNTT xin thông báo về lịch sinh hoạt phổ biến các thông tin cần thiết cho kỳ thực tập tốt nghiệp các lớp CĐ TH 23 như sau:","marks":[]}},{"id":"69d6970af0kd3","type":"list","version":1,"order":1,"data":{"style":"unordered","items":[{"text":"Thời gian: 08h00, thứ 5 ngày 15/01/2026","marks":[],"children":[]},{"text":"Địa điểm: F5.10","marks":[],"children":[]},{"text":"Nội dung:","marks":[],"children":[{"text":"Thời gian thực tập tốt nghiệp.","marks":[],"children":[]},{"text":"Quy định và những lưu ý trong quá trình thực tập.","marks":[],"children":[]},{"text":"Các biểu mẫu: giấy giới thiệu, báo cáo.","marks":[],"children":[]},{"text":"Và một số nội dung liên quan khác.","marks":[],"children":[]}]},{"text":"Lưu ý: các bạn sinh viên tham gia đầy đủ và đúng giờ","marks":[],"children":[]}]}}]', 4, 'published', 123, 'Khoa CNTT xin thông báo về lịch sinh hoạt phổ biến các thông tin cần thiết cho kỳ thực tập tốt nghiệp các lớp CĐ TH 23...', NULL, '2026-01-07 08:00:00', '2026-01-07 08:00:00', '2026-01-07 08:00:00'),
(7, 'Thời khoá biểu thi lần 2 tuần 19, 20 HK1 năm học 2025-2026', 'thoi-khoa-bieu-thi-lan-2-tuan-19-20-hk1-nam-hoc-2025-2026', '[{"id":"69d6970af0ke2","type":"paragraph","version":1,"order":0,"data":{"text":"Khoa Công nghệ thông tin xin thông báo thời khoá biểu thi lần 2 tuần 19, 20 HK1 năm học 2025-2026 như sau:","marks":[]}},{"id":"69d6970af0ke3","type":"table","version":1,"order":1,"data":{"hasHeader":true,"rows":[["LỊCH THI LẦN 2 KHOA CNTT\\nTUẦN 19 - 20"],["Lớp","Học phần","Thời gian thi lần 2","Phòng thi"],["CĐ TH 23MMTA","TK HT mạng","Tuần 19 - T7 (03/01/26) - 07h00","F7.16"]]}}]', 5, 'published', 205, 'Khoa Công nghệ thông tin xin thông báo thời khoá biểu thi lần 2 tuần 19, 20 HK1 năm học 2025-2026 như sau...', NULL, '2025-12-28 08:00:00', '2025-12-28 08:00:00', '2025-12-28 08:00:00'),
(8, 'Lễ Bảo Vệ Đồ Án Tốt Nghiệp Cao Đẳng Học Kỳ Phụ Đợt 1 NH2025-2026', 'le-bao-ve-do-an-tot-nghiep-cao-dang-hoc-ky-phu-dot-1-nh2025-2026', '[{"id":"blk_69d7d1f5d8d18","type":"paragraph","version":1,"order":0,"data":{"text":"Khoa CNTT thông báo Về Lễ Bảo Vệ Đồ Án Tốt Nghiệp Cao Đẳng Học Kỳ Phụ Đợt 1, NH2025-2026 như sau:","marks":[]}},{"id":"blk_69d7d1f5d8d1c","type":"list","version":1,"order":1,"data":{"style":"ordered","items":[{"text":"Lịch Hội đồng:","marks":[],"children":[{"text":"Thời gian: Thư Năm, ngày 11/12/2025","marks":[],"children":[{"text":"Sáng bắt đầu lúc 7h","marks":[],"children":[]},{"text":"Chiều bắt đầu lúc 13h","marks":[],"children":[]},{"text":"Sinh viên có mặt tại phòng Hội đồng trước 30 phút so với thời gian bắt đầu","marks":[],"children":[]}]},{"text":"Địa điểm: ","marks":[],"children":[{"text":"Hội đồng 1: F7.8","marks":[],"children":[]},{"text":"Hội đồng 2: F7.15","marks":[],"children":[]}]}]},{"text":" Danh sách thứ tự các nhóm: tại đây","marks":[{"type":"link","start":28,"end":35,"attrs":{"href":"https://cntt.caothang.edu.vn/uploads/media/DATN/DSBaoVe_HKP2025_DATN.pdf","title":"","target":"_blank"}}],"children":[]},{"text":"Thang điểm chấm:","marks":[],"children":[{"text":"Thang điểm chấm chung: tại đây","marks":[{"type":"link","start":23,"end":30,"attrs":{"href":"https://cntt.caothang.edu.vn/uploads/media/DATN/ThangDiemDATN.pdf","title":"","target":"_blank"}}],"children":[]},{"text":"Phiếu đánh giá cuốn báo cáo ĐATN: tại đây","marks":[{"type":"link","start":34,"end":41,"attrs":{"href":"https://cntt.caothang.edu.vn/uploads/media/DATN/phieudanhgiacuonbaocao.pdf","title":"","target":"_blank"}}],"children":[]},{"text":"Tiêu chí đánh giá điểm thuyết trình: tại đây","marks":[{"type":"link","start":37,"end":44,"attrs":{"href":"https://cntt.caothang.edu.vn/uploads/media/DATN/TieuChiDanhGiaThuyetTrinh_CNTT.pdf","title":"","target":"_blank"}}],"children":[]}]}]}}]', 3, 'published', 450, 'Khoa CNTT thông báo Về Lễ Bảo Vệ Đồ Án Tốt Nghiệp Cao Đẳng Học Kỳ Phụ Đợt 1, NH2025-2026 như sau...', NULL, '2025-12-05 08:00:00', '2025-12-05 08:00:00', '2025-12-05 08:00:00'),
(9, 'Danh sách đề tài và lịch phản biện ĐATN HKP đợt 1 - NH 2025-2026', 'danh-sach-de-tai-va-lich-phan-bien-datn-hkp-dot-1-nh-2025-2026', '[{"id":"blk_69d7d1f5d8d29","type":"paragraph","version":1,"order":0,"data":{"text":"Khoa CNTT thông báo về danh sách đề tài và lịch phản biện, thời gian nộp báo cáo ĐATN HKP đợt 1 - Năm học: 2025-2026.","marks":[]}},{"id":"blk_69d7d1f5d8d2a","type":"list","version":1,"order":1,"data":{"style":"ordered","items":[{"text":"Các mốc thời gian:","marks":[],"children":[{"text":"Báo cáo với GV phản biện: 08 - 10/12/2025","marks":[],"children":[]},{"text":"Nộp báo cáo: Từ 7 - 16h, ngày 10/12/2025","marks":[],"children":[]}]},{"text":"Lịch phản biện:","marks":[],"children":[{"text":"Danh sách đề tài và GVPB: tại đây","marks":[{"type":"link","start":26,"end":33,"attrs":{"href":"https://cntt.caothang.edu.vn/uploads/media/DATN/HKP2025_DATN.pdf","title":"","target":"_blank"}}],"children":[]},{"text":"Lịch phản biện (cập nhật thường xuyên): tại đây","marks":[{"type":"link","start":40,"end":47,"attrs":{"href":"https://docs.google.com/spreadsheets/d/1-PuKMqrO3KaB1MattZbE0UAzaHm9aMri5T6q5vWqGpM/edit?usp=sharing","title":"","target":"_blank"}}],"children":[]}]},{"text":"Lịch bảo vệ:","marks":[],"children":[{"text":"Thời gian: Thứ năm, ngày 11/12/2025","marks":[],"children":[{"text":"Sáng: Bắt đầu lúc 7h00","marks":[],"children":[]},{"text":"Chiều: Bắt đầu lúc 13h00","marks":[],"children":[]},{"text":"Sinh viên có mặt tại phòng Hội đồng trước 30p so với thời gian bắt đầu để chuẩn bị","marks":[],"children":[]}]},{"text":"Địa điểm: ","marks":[],"children":[{"text":"Hội đồng 1: F7.8","marks":[],"children":[]},{"text":"Hội đồng 2: F7.15","marks":[],"children":[]}]}]},{"text":"Nộp báo cáo:","marks":[],"children":[{"text":"Cuốn báo cáo: in 2 cuốn - một mặt","marks":[],"children":[{"text":"Trong cuốn báo cáo có đầy đủ phiếu đăng ký đề tài, nhận xét của GVHD và GVPB.","marks":[],"children":[]},{"text":"Xem quy định báo cáo (sinh viên sử dụng email sinh viên để truy cập): tại đây","marks":[{"type":"link","start":70,"end":77,"attrs":{"href":"https://docs.google.com/document/d/1yVmoH1rlkGXTAeeCoZ7p2PvjLeF2I4ec/edit?usp=sharing&ouid=115517836580847278480&rtpof=true&sd=true","title":"","target":"_blank"}}],"children":[]},{"text":"Định dạng mẫu báo cáo: tại đây","marks":[{"type":"link","start":23,"end":30,"attrs":{"href":"https://docs.google.com/document/d/1Anzqa8_b5NyhVK0ZkQ663KEiWYaD2UxT/edit?usp=sharing&ouid=115517836580847278480&rtpof=true&sd=true","title":"","target":"_blank"}}],"children":[]}]},{"text":"Báo cáo \"mềm\" (File): Tạo thư mục với tên theo định dạng: HoiDong_STT_TenDeTai gồm trong thư mục này chứa các nội dung trình bày dưới đây, sau đó nén và nộp file nén này (Hội đồng và số thứ tự trong danh sách hội đồng sẽ được thông báo sau - trước thời gian nộp báo cáo):","marks":[],"children":[{"text":"Tập tin info.txt chứa thông tin Sinh viên thực hiện đề tài (MSSV, Họ Tên, Lớp, Số điện thoại liên hệ, email)","marks":[],"children":[]},{"text":"Tập tin guide.docx và guide.pdf chứa hướng dẫn sử dụng đề tài (cách setup, cấu hình,...)","marks":[],"children":[]},{"text":"Thư mục src: Chức mã nguồn đề tài, cơ sở dữ liệu","marks":[],"children":[]},{"text":"Thư mục docs: chứa tập tin báo cáo và slide thuyết trình","marks":[],"children":[]},{"text":"Thư mục ref: Tài liệu tham khảo ebook, paper hoặc danh sách website","marks":[],"children":[]},{"text":"Thư mục demo: Chứa các video demo","marks":[],"children":[]}]}]}]}},{"id":"blk_69d7d1f5d8d41","type":"paragraph","version":1,"order":2,"data":{"text":"Các nhóm nhận phiếu đăng ký đề tài tại văn phòng Khoa.","marks":[]}}]', 2, 'published', 510, 'Khoa CNTT thông báo về danh sách đề tài và lịch phản biện, thời gian nộp báo cáo ĐATN HKP đợt 1 - Năm học: 2025-2026...', NULL, '2025-12-01 08:00:00', '2025-12-01 08:00:00', '2025-12-01 08:00:00'),
(10, 'Kết quả xét chuyên ngành đối với sinh viên CNTT khóa 2024', 'ket-qua-xet-chuyen-nganh-doi-voi-sinh-vien-cntt-khoa-2024', '[{"id":"blk_69d7d1f5d8d43","type":"paragraph","version":1,"order":0,"data":{"text":"Khoa Công nghệ thông tin xin thông báo kết quả xét chuyên ngành đối với sinh viên ngành Công nghệ thông tin khóa 2024 như sau:","marks":[]}},{"id":"blk_69d7d1f5d8d44","type":"list","version":1,"order":1,"data":{"style":"ordered","items":[{"text":"Kết quả xét chuyên ngành:","marks":[],"children":[{"text":"Sinh viên xem tại: tại đây","marks":[{"type":"link","start":19,"end":26,"attrs":{"href":"https://daotao.caothang.edu.vn/bai-viet/0-Thong-bao-ket-qua-xet-chuyen-nganh-doi-voi-sinh-vien-nganh,-nghe-Cong-nghe-thong-tin-khoa-2024-a65e252690ee82df138caca98659be87.html","title":"","target":"_blank"}}],"children":[]}]},{"text":"Thời gian khiếu nại (nếu có):","marks":[],"children":[{"text":"Liên hệ: Thầy Hải (Phòng Đào tạo)","marks":[],"children":[]},{"text":"Thời gian: Trước 16h ngày 28/11/2025.","marks":[],"children":[]}]}]}}]', 2, 'published', 890, 'Khoa Công nghệ thông tin xin thông báo kết quả xét chuyên ngành đối với sinh viên ngành Công nghệ thông tin khóa 2024...', NULL, '2025-11-20 08:00:00', '2025-11-20 08:00:00', '2025-11-20 08:00:00');

-- ============================================================================
-- 16. BẢNG CATEGORY_POST
-- ============================================================================
INSERT INTO `category_post` (`post_id`, `category_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1);

-- ============================================================================
-- 17. BẢNG MEDIA
-- ============================================================================
INSERT INTO `media` (`id`, `post_id`, `file_name`, `file_path`, `mime_type`, `size`, `created_at`) VALUES
(1, 1, 'tb-datn-2026.pdf', '/uploads/media/tb-datn-2026.pdf', 'application/pdf', 1024500, '2026-03-25 08:00:00'),
(2, 2, 'banner-olympic-2026.jpg', '/uploads/media/banner-olympic-2026.jpg', 'image/jpeg', 204800, '2026-03-20 08:00:00');

SET FOREIGN_KEY_CHECKS = 1;