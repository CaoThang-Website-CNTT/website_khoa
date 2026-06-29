SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;

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
TRUNCATE TABLE `cms_pages`;
TRUNCATE TABLE `companies`;

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
-- Mật khẩu mặc định cho tất cả tài khoản là "password" (đã được hash bằng bcrypt)
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
('copyright_text', 'general', 'General', 'string', '© 2026 Trường Cao đẳng Kỹ thuật Cao Thắng', 'Bản quyền'),
('internship_company_declaration_weeks', 'internship', 'Thực tập tốt nghiệp', 'int', '3', 'Thời gian khai báo công ty (tuần)'),
('internship_report_max_size_mb', 'internship', 'Thực tập tốt nghiệp', 'int', '50', 'Dung lượng báo cáo tối đa (MB)'),
('internship_report_submission_days', 'internship', 'Thực tập tốt nghiệp', 'int', '7', 'Hạn nộp báo cáo sau khi đợt kết thúc (ngày)'),
('internship_company_warning_days', 'internship', 'Thực tập tốt nghiệp', 'int', '3', 'Thời gian cảnh báo khai báo công ty (ngày)'),
('internship_report_warning_days', 'internship', 'Thực tập tốt nghiệp', 'int', '3', 'Thời gian cảnh báo nộp báo cáo (ngày)'),
('internship_grading_deadline_weeks', 'internship', 'Thực tập tốt nghiệp', 'int', '2', 'Thời hạn chấm điểm (tuần)');


-- ---------------------------------------------------------------------------- 
-- 9. BẢNG CATEGORIES
-- ---------------------------------------------------------------------------- 
INSERT INTO `categories` (`id`, `name`, `slug`, `type`, `parent_id`) VALUES
(1, 'Tin khoa', 'tin-khoa', 'const', NULL),
(2, 'Nghiên cứu', 'nghien-cuu', 'const', NULL),
(3, 'Sự kiện', 'su-kien', 'const', NULL),
(4, 'Sinh viên', 'sinh-vien', 'const', NULL),
(5, 'Tuyển dụng', 'tuyen-dung', 'const', NULL),
(6, 'Giải thưởng', 'giai-thuong', 'const', NULL),
(7, 'Hoạt động nổi bật', 'hoat-dong-noi-bat', 'custom', 1),
(8, 'Đoàn Thanh niên', 'doan-thanh-nien', 'custom', 1),
(9, 'Thông báo học vụ', 'thong-bao-hoc-vu', 'custom', 4),
(10, 'Lịch thi', 'lich-thi', 'custom', 4),
(11, 'Học bổng', 'hoc-bong', 'custom', 10),
(12, 'Việc làm', 'viec-lam', 'custom', 4),
(13, 'Tuyển sinh', 'tuyen-sinh', 'const', NULL),
(14, 'Thông báo tuyển sinh', 'thong-bao-tuyen-sinh', 'custom', 13),
(15, 'Hướng nghiệp', 'huong-nghiep', 'custom', 13),
(16, 'Doanh nghiệp', 'doanh-nghiep', 'const', NULL),
(17, 'Cơ hội việc làm', 'co-hoi-viec-lam', 'custom', 15),
(18, 'Sáng tạo khởi nghiệp', 'sang-tao-khoi-nghiep', 'custom', 19),
(19, 'Góc sinh viên', 'goc-sinh-vien', 'custom', 9),
(20, 'Tin nội bộ', 'tin-noi-bo', 'custom', 1);


-- ---------------------------------------------------------------------------- 
-- 10. BẢNG MENUS & MENU_ITEMS (Có parent_id để tạo menu cha-con)
-- ---------------------------------------------------------------------------- 
INSERT INTO `menus` (`id`, `key`, `label`, `type`) VALUES 
(1, 'header_menu', 'Menu Chính', 'const'),
(2, 'footer_menu', 'Menu Chân Trang', 'const');

INSERT INTO `menu_items` (`id`, `menu_id`, `parent_id`, `label`, `url`, `sort_order`) VALUES
-- Header Menu
(1, 1, NULL, 'Trang chủ', '/', 1),
(2, 1, NULL, 'Giới thiệu', '/gioi-thieu', 2),
(3, 1, NULL, 'Đào tạo', '/dao-tao', 3),
(4, 1, 3, 'Thông tin tuyển sinh', '/dao-tao/tuyen-sinh', 1),
(5, 1, 3, 'Chương trình đào tạo', '/dao-tao/chuong-trinh-dao-tao', 2),
(6, 1, 3, 'Chuẩn đầu ra', '/dao-tao/chuan-dau-ra', 3),
(8, 1, 3, 'Danh sách môn học', '/dao-tao/danh-sach-mon-hoc', 4),
(7, 1, NULL, 'Tin tức', '/tin-tuc', 5),
(25, 1, NULL, 'Portal', '/portal', 7),

-- Footer Menu
(9, 2, NULL, 'Chính sách bảo mật', '/privacy', 1),
(10, 2, NULL, 'Điều khoản sử dụng', '/terms', 2),
(11, 2, NULL, 'Sơ đồ website', '/sitemap', 3);

-- ---------------------------------------------------------------------------- 
-- 11. BẢNG CAROUSELS & CAROUSEL_SLIDES
-- ---------------------------------------------------------------------------- 
INSERT INTO `carousels` (`id`, `name`, `slug`, `is_active`) VALUES 
(1, 'Trang chủ (Landing Page)', 'landing-page', 1);

-- Thêm dữ liệu cho bảng carousel_slides (4 slides)
INSERT INTO `carousel_slides` 
(`id`, `carousel_id`, `title`, `title_highlight`, `description`, `media_id`, `cta_label`, `cta_url`, `custom_html`, `sort_order`) 
VALUES
(1, 1, 'Môi trường học tập', 'Chuyên nghiệp & Sáng tạo.', 'Không gian học tập mở, khuyến khích sự sáng tạo và hợp tác, với sự hỗ trợ từ đội ngũ giảng viên giàu kinh nghiệm và tận tâm.', '1', 'Tìm hiểu thêm', NULL, NULL, 1),
(2, 1, 'Môi trường học tập', 'Chuyên nghiệp & Sáng tạo.', 'Không gian học tập mở, khuyến khích sự sáng tạo và hợp tác, với sự hỗ trợ từ đội ngũ giảng viên giàu kinh nghiệm và tận tâm.', '2', 'Tìm hiểu thêm', NULL, NULL, 2),
(3, 1, 'Môi trường học tập', 'Chuyên nghiệp & Sáng tạo.', 'Không gian học tập mở, khuyến khích sự sáng tạo và hợp tác, với sự hỗ trợ từ đội ngũ giảng viên giàu kinh nghiệm và tận tâm.', '3', 'Tìm hiểu thêm', NULL, NULL, 3),
(4, 1, 'Môi trường học tập', 'Chuyên nghiệp & Sáng tạo.', 'Không gian học tập mở, khuyến khích sự sáng tạo và hợp tác, với sự hỗ trợ từ đội ngũ giảng viên giàu kinh nghiệm và tận tâm.', '1', 'Tìm hiểu thêm', NULL, NULL, 4);

-- ---------------------------------------------------------------------------- 
-- 12. BẢNG CMS_PAGES
-- ---------------------------------------------------------------------------- 
INSERT INTO `cms_pages` (`title`, `slug`, `route_path`, `type`, `status`, `layout_mode`, `content_json`, `settings_json`, `published_at`, `created_at`, `updated_at`) VALUES
('Trang chủ', 'landing', '/', 'landing_page', 'published', 'section_schema',
'{"version":1,"sections":[{"id":"hero","type":"sections/landing_hero","locked":true,"data":[]},{"id":"landing_about","type":"sections/landing_about","locked":false,"data":{"items":[{"number":"01","image":{"src":"./public/img/about.jpg","alt":"Lecture hall with students"},"card":{"value":"Top 1","label":"Khoa CNTT tại Miền Nam"},"eyebrow":"LOREM ISPUM GÌ ĐÓ Ở ĐÂY","title":"Đảm bảo chất lượng đào tạo","description":"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sed leo et neque vehicula lacinia vel at lorem."},{"number":"02","image":{"src":"./public/img/about.jpg","alt":"Lecture hall with students"},"card":{"value":"98%","label":"Tỷ lệ có việc làm"},"eyebrow":"LOREM ISPUM GÌ ĐÓ Ở ĐÂY","title":"Cơ hội Nghề nghiệp","description":"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sed leo et neque vehicula lacinia vel at lorem."},{"number":"03","image":{"src":"./public/img/about.jpg","alt":"Lecture hall with students"},"card":{"value":"50+","label":"Doanh nghiệp"},"eyebrow":"LOREM ISPUM GÌ ĐÓ Ở ĐÂY","title":"Nghiên cứu Đột phá","description":"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sed leo et neque vehicula lacinia vel at lorem."}]}},{"id":"why_choose_us","type":"sections/why_choose_us","locked":false,"data":{"badge":"Tại sao chọn chúng tôi","title":"Trải nghiệm Khoa CNTT Cao Thắng","subtitle":"Nơi ươm mầm tài năng công nghệ thông tin, kết nối tri thức với thực tiễn","feature":{"image":"https://images.unsplash.com/photo-1524178232363-1fb2b075b655?...","alt":"Trường Cao Thắng","badge":"Nổi bật","title":"Môi trường học tập hiện đại, sáng tạo","description":"Trang bị phòng lab tiêu chuẩn quốc tế, thư viện số phong phú, không gian làm việc nhóm linh hoạt và hệ thống học tập trực tuyến tiên tiến.","cta_label":"Khám phá ngay","cta_url":"#"},"stats":[{"number":"20","title":"Năm kinh nghiệm","description":"Tiên phong trong đào tạo CNTT chất lượng cao tại TP.HCM từ năm 2003"},{"number":"95%","title":"Tỷ lệ việc làm","description":"Sinh viên có việc làm trong vòng 6 tháng sau tốt nghiệp"}],"perks":[{"icon":"fa-solid fa-code","title":"Công nghệ tiên tiến","description":"Học tập với các công nghệ mới nhất: AI, Cloud, Blockchain, IoT"},{"icon":"fa-solid fa-user-group","title":"Cộng đồng Mạnh mẽ","description":"Kết nối với 10,000+ sinh viên và cựu sinh viên trên toàn quốc"},{"icon":"fa-solid fa-award","title":"Chất lượng Quốc tế","description":"Chương trình đạt chuẩn ABET và kiểm định quốc tế"},{"icon":"fa-solid fa-rocket","title":"Khởi nghiệp","description":"Hỗ trợ ý tưởng startup và kết nối nhà đầu tư"}],"highlights":[{"image":"https://images.unsplash.com/photo-1524178232363-1fb2b075b655?...","alt":"Trường Cao Thắng","title":"Nghiên cứu & Phát triển","description":"Tham gia các dự án nghiên cứu thực tế cùng giảng viên"},{"image":"https://images.unsplash.com/photo-1524178232363-1fb2b075b655?...","alt":"Trường Cao Thắng","title":"Hợp tác Quốc tế","description":"Cơ hội trao đổi sinh viên và học bổng du học"}]}},{"id":"stats","type":"sections/stats","locked":false,"data":{"title":"Khoa CNTT Cao Thắng","subtitle":"Định hình tương lai công nghệ thông tin Việt Nam","stats":[{"icon":"fa-solid fa-award","number":"50+","label":"Giải thưởng","description":"Trong các cuộc thi lập trình"},{"icon":"fa-solid fa-graduation-cap","number":"10K+","label":"Sinh viên","description":"Tốt nghiệp thành công"},{"icon":"fa-solid fa-briefcase","number":"95%","label":"Việc làm","description":"Sau 6 tháng tốt nghiệp"},{"icon":"fa-solid fa-earth-americas","number":"20+","label":"Quốc gia","description":"Hợp tác quốc tế"}],"benefits":[{"icon":"fa-solid fa-building-columns","title":"Chương trình Đào tạo Tiên tiến","items":["Cập nhật theo công nghệ mới nhất","Tích hợp chứng chỉ quốc tế","Thực hành dự án thực tế","Đào tạo kỹ năng mềm"]},{"icon":"fa-solid fa-arrow-trend-up","title":"Phát triển Nghề nghiệp","items":["Kết nối với 100+ doanh nghiệp","Thực tập tại công ty hàng đầu","Tư vấn định hướng nghề nghiệp","Cơ hội việc làm cao"]}],"cta":{"title":"Sẵn sàng bắt đầu hành trình của bạn?","description":"Gia nhập cộng đồng hơn 10,000 sinh viên và cựu sinh viên đang làm việc tại các công ty công nghệ hàng đầu","buttons":[{"label":"Đăng ký tư vấn","url":"#","variant":"outline-alt"},{"label":"Xem chương trình đào tạo","url":"#","variant":"outline"}]}}},{"id":"newsfeed","type":"sections/newsfeed","locked":true,"data":[]}]}',
'{}', NOW(), NOW(), NOW()),
('Giới thiệu', 'about', '/gioi-thieu', 'landing_page', 'published', 'section_schema',
'{"version":1,"sections":[{"id":"breadcrumbs","type":"sections/breadcrumbs","locked":true,"data":[]},{"id":"about_hero","type":"sections/about_hero","locked":false,"data":{"image":"public/img/about.jpg","badge":"Về Chúng Tôi","title":"Câu chuyện của Cao Thắng","subtitle":"Từ những ngày đầu tiên đến hôm nay, Cao Thắng không ngừng phát triển để mang đến giáo dục công nghệ chất lượng cao cho sinh viên Việt Nam"}},{"id":"history","type":"sections/history","locked":false,"data":{"sections":[{"image":{"src":"public/img/about.jpg","alt":"Lecture hall with students","caption":"Khoa CNTT được thành lập"},"year":"1998","badge":"<i class=\\"fa-solid fa-graduation-cap\\"></i> <span class=\\"text-sm\\">Khoa Công Nghệ Thông Tin</span>","title":"27 năm đổi mới & phát triển","timeline":[{"year":"1998","description":"Khoa Điện Tử - Tin Học, tiền thân của khoa Công Nghệ Thông Tin được thành lập."},{"year":"2020","description":"Đổi tên Khoa Điện tử - Tin học thành Khoa Công nghệ thông tin."}]},{"image":{"src":"public/img/about.jpg","alt":"Lecture hall with students","caption":"Trường được thành lập"},"year":"1906","badge":"<i class=\\"fa-solid fa-building-columns\\"></i> <span class=\\"text-sm\\">Trường Cao Đẳng Kỹ Thuật Cao Thắng</span>","title":"100+ năm truyền thống","timeline":[{"year":"1906","description":"Chính thức thành lập Trường Cơ khí Á Châu (L''école des Mécaniciens Asiatiques), tiền thân của trường."},{"year":"1915","description":"Chủ tịch Tôn Đức Thắng nhập học."},{"year":"2004","description":"Chính thức đổi tên thành Trường Cao đẳng Kỹ thuật Cao Thắng."},{"year":"2016","description":"Đạt chuẩn kiểm định quốc tế ABET."}]}]}},{"id":"bento_grid","type":"sections/bento_grid","locked":false,"data":{"items":[{"badge":"<i class=\\"fa-solid fa-award\\"></i> <span>Chứng nhận Quốc Tế</span>","image":{"src":"public/img/about.jpg","alt":""},"content":"Thành tựu","footer":"<span class=\\"badge px-3 py-2 text-sm md:text-md\\" data-variant=\\"glass\\">30+ Quốc gia công nhận</span> <span class=\\"badge px-3 py-2 text-sm md:text-md\\" data-variant=\\"glass\\">Top 5 Khoa CNTT VN</span>"},{"badge":"<i class=\\"fa-solid fa-user-group\\"></i>","image":{"src":"","alt":""},"content":"25+","subContent":"Giảng Viên","footer":"Có hơn 15 năm kinh nghiệm trong việc giảng dạy"},{"badge":"<i class=\\"fa-solid fa-award\\"></i>","image":{"src":"","alt":""},"content":"50+","subContent":"Giải Thưởng","footer":"Từ chính phủ và các tổ chức kiểm định quốc tế"},{"badge":"<i class=\\"fa-solid fa-rocket\\"></i>","image":{"src":"","alt":""},"content":"10+","subContent":"Phòng Lab hiện đại","footer":"Trang bị công nghệ tiên tiến phục vụ học tập và nghiên cứu"},{"badge":"<i class=\\"fa-solid fa-graduation-cap\\"></i>","image":{"src":"","alt":""},"content":"100+","subContent":"Học bổng hàng năm","footer":"Từ học bổng toàn phần đến các suất trao đổi quốc tế"},{"badge":"<i class=\\"fa-solid fa-user-group\\"></i> <span>Cộng đồng học tập</span>","image":{"src":"public/img/about.jpg","alt":""},"content":"Môi trường","subContent":"Năng động & sáng tạo","footer":"Nhiều hoạt động, sự kiện nhằm thúc đẩy tinh thần năng nổ của Sinh Viên"},{"badge":"<i class=\\"fa-solid fa-building\\"></i>","image":{"src":"","alt":""},"content":"100+","subContent":"Doanh nghiệp đối tác","footer":"FPT, Viettel, Samsung, Google và nhiều công ty hàng đầu"},{"badge":"<i class=\\"fa-solid fa-arrow-trend-up\\"></i>","image":{"src":"","alt":""},"content":"1,000+","subContent":"Cựu sinh viên","footer":"Làm việc tại các công ty công nghệ hàng đầu toàn cầu"}]}}]}',
'{}', NOW(), NOW(), NOW());

-- ============================================================================
-- 15. BẢNG POSTS
-- ============================================================================
-- POSTS (compact format - 1 line per post)
-- Dữ liệu mẫu bài viết từ ID 1 đến 4 (Theo cấu trúc Block Schema lồng JSON trong trường data mới)
INSERT INTO `posts` (`id`, `title`, `slug`, `content_json`, `settings_json`, `author_id`, `status`, `view_count`, `seo_description`, `seo_image_url`, `created_at`, `updated_at`, `published_at`) VALUES

(1, 'Thông báo đăng ký đợt thực tập tốt nghiệp Học kỳ 2', 'thong-bao-dang-ky-dot-thuc-tap-tot-nghiep-hoc-ky-2', 
'[{"id":"blk_1_1","type":"blocks/heading","version":1,"data":{"rich_text":[{"type":"text","text":"Thông báo về việc đăng ký thực tập tốt nghiệp Học kỳ 2","marks":[]}],"meta":{"level":2,"align":"left","anchor_id":""}}},{"id":"blk_1_2","type":"blocks/paragraph","version":1,"data":{"rich_text":[{"type":"text","text":"Khoa Công nghệ thông tin yêu cầu toàn bộ sinh viên đủ điều kiện thực hiện các công việc sau để chuẩn bị cho đợt thực tập:","marks":[]}],"meta":{"align":"left","anchor_id":""}}},{"id":"blk_1_3","type":"blocks/list","version":1,"data":{"rich_text":[],"meta":{"style":"bullet","items":[{"rich_text":[{"type":"text","text":"Sinh viên nộp phiếu đăng ký đúng hạn theo kế hoạch phân phối","marks":[]}],"children":[]},{"rich_text":[{"type":"text","text":"Cập nhật đầy đủ CV cá nhân trên hệ thống quản lý thực tập","marks":[]}],"children":[]}],"anchor_id":""}}},{"id":"blk_1_4","type":"blocks/quote","version":1,"data":{"rich_text":[{"type":"text","text":"\\"Mọi sự chậm trễ sẽ không được giải quyết sau khi cổng đăng ký đóng lại.\\"","marks":[]}],"meta":{"citation":"Văn phòng Khoa CNTT"}}}]',
'{"version":1,"title":"Thông báo đăng ký đợt thực tập tốt nghiệp Học kỳ 2","slug":"thong-bao-dang-ky-dot-thuc-tap-tot-nghiep-hoc-ky-2","excerpt":"Khoa Công nghệ thông tin yêu cầu toàn bộ sinh viên đủ điều kiện nộp phiếu đăng ký đúng hạn để chuẩn bị cho đợt thực tập tốt nghiệp Học kỳ 2.","author_id":"1","status":"published","category_ids":[1],"featured_image":"media/2026/05/6dace5fb-fcc9-44f2-a066-2f1734185b02_medium.webp","settings":{"show_author":false,"show_date":true,"show_view_count":false},"init_view_count":150}',
1, 'published', 150, 'Thông báo chính thức về việc đăng ký đợt thực tập tốt nghiệp dành cho sinh viên Học kỳ 2, bao gồm thời gian và biểu mẫu liên quan.', 'media/2026/05/6dace5fb-fcc9-44f2-a066-2f1734185b02_medium.webp', NOW(), NOW(), NOW()),

-- Bài viết 2
(2, 'Kết quả cuộc thi Olympic Tin học cấp Trường năm nay', 'ket-qua-cuoc-thi-olympic-tin-hoc-cap-truong', 
'[{"id":"blk_2_1","type":"blocks/heading","version":1,"data":{"rich_text":[{"type":"text","text":"Bảng vàng thành tích Olympic Tin học cấp Trường","marks":[]}],"meta":{"level":2,"align":"center","anchor_id":""}}},{"id":"blk_2_2","type":"blocks/paragraph","version":1,"data":{"rich_text":[{"type":"text","text":"Trải qua các vòng thi gay cấn, Ban tổ chức xin chính thức công bố kết quả chung cuộc của các thí sinh xuất sắc nhất năm nay:","marks":[]}],"meta":{"align":"left","anchor_id":""}}},{"id":"blk_2_3","type":"blocks/image","version":1,"data":{"rich_text":[],"meta":{"mediaId":101,"url":"/public/img/olympic-trao-giai.jpg","alt":"Trao giải Olympic Tin học","caption":[],"align":"center","width":"100%","anchor_id":""}}},{"id":"blk_2_4","type":"blocks/table","version":1,"data":{"rich_text":[],"meta":{"hasHeader":true,"rows":[["Hạng giải","Họ và tên","Lớp sinh hoạt"],["Giải Nhất","Nguyễn Văn A","CD CCQ 23"],["Giải Nhì","Trần Thị B","CD CCQ 24"]]}}}]',
'{"version":1,"title":"Kết quả cuộc thi Olympic Tin học cấp Trường năm nay","slug":"ket-qua-cuoc-thi-olympic-tin-hoc-cap-truong","excerpt":"Ban tổ chức chính thức công bố bảng vàng thành tích danh giá của các thí sinh xuất sắc nhất tại cuộc thi Olympic Tin học cấp Trường năm nay.","author_id":"1","status":"published","category_ids":[3],"featured_image":"media/2026/05/251d5979-1bb2-4b06-99fa-2ae2e91b157a_medium.webp","settings":{"show_author":false,"show_date":true,"show_view_count":false},"init_view_count":320}',
1, 'published', 320, 'Chúc mừng các sinh viên đã đạt thành tích xuất sắc tại cuộc thi Olympic Tin học cấp Trường năm học này với các giải thưởng danh giá.', 'media/2026/05/251d5979-1bb2-4b06-99fa-2ae2e91b157a_medium.webp', NOW(), NOW(), NOW()),

-- Bài viết 3
(3, 'Lịch thi và danh sách phòng thi tốt nghiệp lý thuyết', 'lich-thi-va-danh-sach-phong-thi-tot-nghiep-ly-thuyet', 
'[{"id":"blk_3_1","type":"blocks/heading","version":1,"data":{"rich_text":[{"type":"text","text":"Lịch thi chính thức các môn lý thuyết tốt nghiệp","marks":[]}],"meta":{"level":2,"align":"left","anchor_id":""}}},{"id":"blk_3_2","type":"blocks/paragraph","version":1,"data":{"rich_text":[{"type":"text","text":"Sinh viên lưu ý có mặt trước giờ thi 15 phút, mang theo thẻ sinh viên và giấy tờ tùy thân hợp lệ:","marks":[]}],"meta":{"align":"left","anchor_id":""}}},{"id":"blk_3_3","type":"blocks/table","version":1,"data":{"rich_text":[],"meta":{"hasHeader":true,"rows":[["Ngày thi cụ thể","Môn thi tốt nghiệp","Phòng thi phân công"],["15/06/2026","Cơ sở dữ liệu","A.0501"],["16/06/2026","Lập trình Web","A.0502"]]}}}]', 
'{"version":1,"title":"Lịch thi và danh sách phòng thi tốt nghiệp lý thuyết","slug":"lich-thi-va-danh-sach-phong-thi-tot-nghiep-ly-thuyet","excerpt":"Cập nhật lịch thi chi tiết và phân công sơ đồ phòng thi các môn lý thuyết tốt nghiệp chuyên ngành dành cho sinh viên các lớp cuối khóa.","author_id":"2","status":"published","category_ids":[1],"featured_image":"media/2026/05/c0b493fe-ea8f-4f27-93a1-357e1be09d41_medium.webp","settings":{"show_author":false,"show_date":true,"show_view_count":false},"init_view_count":415}',
2, 'published', 415, 'Cập nhật lịch thi chính thức và sơ đồ phòng thi tốt nghiệp phần lý thuyết chuyên ngành dành cho các lớp cuối khóa.', 'media/2026/05/c0b493fe-ea8f-4f27-93a1-357e1be09d41_medium.webp', NOW(), NOW(), NOW()),

-- Bài viết 4
(4, 'Thông tin về thực tập tốt nghiệp và đồ án tốt nghiệp CĐTH 23', 'thong-tin-ve-thuc-tap-tot-nghiep-va-do-an-tot-nghiep-cdth-23', 
'[{"id":"blk_4_1","type":"blocks/paragraph","version":1,"data":{"rich_text":[{"type":"text","text":"Khoa Công nghệ thông tin xin thông báo một số thông tin về thực tập tốt nghiệp và đồ án tốt nghiệp CĐTH 23 như sau:","marks":[]}],"meta":{"align":"left","anchor_id":""}}},{"id":"blk_4_2","type":"blocks/list","version":1,"data":{"rich_text":[],"meta":{"style":"bullet","items":[{"rich_text":[{"type":"text","text":"Các mốc thời gian thực tập:","marks":[]}],"children":["Thời gian thực tập chính thức: Từ 02/03/2026 đến 26/04/2026","Báo cáo và chấm điểm thực tập (dự kiến): Từ 27/04/2026 đến 02/05/2026","Sinh viên nộp đơn đăng ký đề tài Đồ án tốt nghiệp có xác nhận GVHD, nộp phiếu đăng ký đề tài: 30/03/2026 - 05/04/2026 (Danh sách đề tài sẽ công bố trước thời gian đăng ký)"]},{"rich_text":[{"type":"text","text":"Danh sách phân công GVHD:","marks":[]}],"children":["Danh sách GVHD thực tập tốt nghiệp và đồ án tốt nghiệp: <a href=\\"https://tinyurl.com/hd-tttn-datn-cdth23\\" target=\\"_blank\\">tại đây</a>","Danh sách email liên hệ GVHD: <a href=\\"https://tinyurl.com/gvhd-email-2026\\" target=\\"_blank\\">tại đây</a>","Lưu ý: danh sách Hướng dẫn Đồ án tốt nghiệp chỉ là dự kiến, sinh viên phải đủ điều kiện xét được thi tốt nghiệp của phòng Đào tạo mới thực hiện đề tài ĐATN."]},{"rich_text":[{"type":"text","text":"Các tài nguyên:","marks":[]}],"children":["Các tài nguyên/ biểu mẫu/ danh sách các công ty: <a href=\\"https://tinyurl.com/tainguyen-cdth23\\" target=\\"_blank\\">tại đây</a>","Đăng ký giấy giới thiệu thực tập: <a href=\\"https://tinyurl.com/gthieutt-cdth23\\" target=\\"_blank\\">tại đây</a>"]}],"anchor_id":""}}},{"id":"blk_4_3","type":"blocks/paragraph","version":1,"data":{"rich_text":[{"type":"text","text":"Đối với sinh viên khóa trước đăng ký thực tập (ghép) chưa có trong danh sách hướng dẫn, sinh viên liên hệ thầy Nguyên qua email để được phổ biến thông tin hướng dẫn.","marks":[]}],"meta":{"align":"left","anchor_id":""}}}]', 
'{"version":1,"title":"Thông tin về thực tập tốt nghiệp và đồ án tốt nghiệp CĐTH 23","slug":"thong-tin-ve-thuc-tap-tot-nghiep-va-do-an-tot-nghiep-cdth-23","excerpt":"Khoa Công nghệ thông tin thông báo kế hoạch chi tiết, mốc thời gian quan trọng, tài nguyên liên kết biểu mẫu và phân công GVHD thực tập, đồ án tốt nghiệp lớp CĐTH 23.","author_id":"2","status":"published","category_ids":[1],"featured_image":null,"settings":{"show_author":false,"show_date":true,"show_view_count":false},"init_view_count":325}',
2, 'published', 325, 'Khoa Công nghệ thông tin thông báo kế hoạch chi tiết, mốc thời gian quan trọng, tài nguyên liên kết biểu mẫu và phân công GVHD thực tập, đồ án tốt nghiệp lớp CĐTH 23.', NULL, '2026-02-15 08:00:00', '2026-02-15 08:00:00', '2026-02-15 08:00:00');
-- 16. BẢNG CATEGORY_POST
-- ============================================================================
INSERT INTO `category_post` (`post_id`, `category_id`) VALUES
(1, 1),
(2, 3),
(3, 1),
(4, 1);

-- ============================================================================
-- 17. BẢNG MEDIA
-- ============================================================================
INSERT INTO `media` (`id`, `title`, `file_name`, `file_path`, `mime_type`, `alt_text`, `width`, `height`, `file_size`, `metadata`, `created_at`, `updated_at`) VALUES
(1, 'demo-1', 'john-FlPc9_VocJ4-unsplash.jpg', 'media/2026/05/6dace5fb-fcc9-44f2-a066-2f1734185b02_medium.webp', 'image/webp', 'demo-1', 800, 533, 24250, '{\"aspect_ratio\":1.5,\"processed_mode\":\"standard\"}', '2026-05-24 22:31:48', '2026-05-24 22:31:48'),
(2, 'demo-2', 'susan-q-yin-2JIvboGLeho-unsplash.jpg', 'media/2026/05/251d5979-1bb2-4b06-99fa-2ae2e91b157a_medium.webp', 'image/webp', 'demo-2', 800, 534, 57904, '{\"aspect_ratio\":1.5,\"processed_mode\":\"standard\"}', '2026-05-24 22:32:08', '2026-05-24 22:32:08'),
(3, 'demo-3', 'yue-wu-iM1coCUa5gI-unsplash.jpg', 'media/2026/05/c0b493fe-ea8f-4f27-93a1-357e1be09d41_medium.webp', 'image/webp', 'demo-3', 800, 533, 84052, '{\"aspect_ratio\":1.5,\"processed_mode\":\"standard\"}', '2026-05-24 22:32:31', '2026-05-24 22:32:31');

COMMIT;

-- ============================================================================
-- 18. BẢNG COMPANIES 
-- ============================================================================
INSERT INTO `companies` (`id`, `tax_code`, `name`, `normalized_name`, `phone`, `email`, `website`, `address`, `is_verified`, `source`) VALUES
(1, '0312456789', 'Công ty TNHH Giải pháp Phần mềm Sài Gòn (Saigon Software)', 'cong ty tnhh giai phap phan mem sai gon saigon software', '02838210001', 'info@saigonsoft.vn', 'https://saigonsoft.vn', '123 Nguyễn Huệ, Phường Bến Nghé, Quận 1, TP.HCM', 1, 'api'),
(2, '0313987654', 'Công ty Cổ phần Công nghệ Vidon', 'cong ty co phan cong nghe vidon', '02839301122', 'contact@vidon.tech', 'https://vidon.tech', '45 Võ Văn Tần, Phường Võ Thị Sáu, Quận 3, TP.HCM', 1, 'api'),
(3, '0309112233', 'Công ty TNHH Tích hợp Hệ thống CMC (CMC SI)', 'cong ty tnhh tich hop he thong cmc cmc si', '02838445566', 'support@cmcsi.com.vn', 'https://cmcsi.com.vn', '12 Phổ Quang, Phường 2, Quận Tân Bình, TP.HCM', 1, 'api'),
(4, '0315667788', 'Công ty TNHH Phát triển Phần mềm Magestore', 'cong ty tnhh phat trien phan mem magestore', '02835102030', 'hr@magestore.com', 'https://magestore.com', '270 Bạch Đằng, Phường 24, Quận Bình Thạnh, TP.HCM', 1, 'api'),
(5, '0304556677', 'Công ty TNHH Giải pháp Công nghệ ITG', 'cong ty tnhh giai phap cong nghe itg', '02837151234', 'info@itg.vn', 'https://itg.vn', 'Công viên phần mềm Quang Trung, Quận 12, TP.HCM', 1, 'api'),
(6, '0311223344', 'Công ty TNHH Công nghệ Mật Mã (Cipher Tech)', 'cong ty tnhh cong nghe mat ma cipher tech', '02838940000', 'hello@ciphertech.vn', 'https://ciphertech.vn', '56 Quang Trung, Phường 10, Quận Gò Vấp, TP.HCM', 1, 'api'),
(7, '0314889900', 'Công ty Cổ phần Giải pháp Công nghệ Việt Nam (V-Sol)', 'cong ty co phan giai phap cong nghe viet nam v-sol', '02839951234', 'sales@v-sol.vn', 'https://v-sol.vn', '158 Đào Duy Anh, Phường 9, Quận Phú Nhuận, TP.HCM', 1, 'api'),
(8, '0308776655', 'Công ty TNHH Linh kiện Máy tính Song Huy', 'cong ty tnhh linh kien may tinh song huy', '02839274567', 'songhuy@hardware.vn', 'https://songhuycomputer.vn', '436/1 Đường 3/2, Phường 12, Quận 10, TP.HCM', 1, 'api'),
(9, '0316443322', 'Công ty TNHH Giải pháp ERP Toàn Cầu', 'cong ty tnhh giai phap erp toan cau', '02837718899', 'admin@erp-global.com', 'https://erp-global.com', 'Số 7 Đường số 2, KDC Him Lam, Quận 7, TP.HCM', 1, 'api'),
(10, '0310229988', 'Công ty TNHH MTV Kỹ thuật Phần cứng và Mạng Bách Khoa', 'cong ty tnhh mtv ky thuat phan cung va mang bach khoa', '02837223344', 'bachkhoa@bk-tech.edu.vn', 'https://bk-tech.vn', 'Khu Công nghệ cao, Quận Thủ Đức, TP.HCM', 1, 'api');

SET FOREIGN_KEY_CHECKS = 1;
