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
('social_facebook', 'social', 'Social', 'url', 'https://www.facebook.com/cntt.caothang/', 'Facebook'),
('social_youtube', 'social', 'Social', 'url', 'https://www.youtube.com/@khoacongnghethongtintruong9586/videos', 'Youtube'),
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
('internship_grading_deadline_weeks', 'internship', 'Thực tập tốt nghiệp', 'int', '2', 'Thời hạn chấm điểm (tuần)'),
('internship_weekly_report_late_days', 'internship', 'Thực tập tốt nghiệp', 'int', '7', 'Hạn nộp bù báo cáo tuần (ngày)'),
('social_tiktok', 'social', 'Social', 'url', 'https://www.tiktok.com/@cntt.ckc', 'TikTok');


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
(20, 'Tin nội bộ', 'tin-noi-bo', 'custom', 1),
(21, 'Hoạt động', 'hoat-dong', 'const', NULL),
(22, 'Công tác giảng dạy', 'cong-tac-giang-day', 'custom', 21),
(23, 'Nghiên cứu khoa học', 'nghien-cuu-khoa-hoc', 'custom', 21),
(24, 'Giảng viên', 'giang-vien', 'custom', 23),
(25, 'Học thuật', 'hoc-thuat', 'custom', 21),
(26, 'Thi đua đoàn thể', 'thi-dua-doan-the', 'custom', 21),
(27, 'Phong trào & Ngoại khóa', 'phong-trao-ngoai-khoa', 'custom', 21),
(28, 'CLB Tin học', 'clb-tin-hoc', 'custom', 21),
(29, 'Thông báo', 'thong-bao', 'const', NULL);


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
(4, 1, 3, 'Thông tin tuyển sinh', '/dao-tao#tuyen-sinh', 1),
(5, 1, 3, 'Chương trình đào tạo', '/dao-tao#chuong-trinh-dao-tao', 2),
(6, 1, 5, 'Chuẩn đầu ra', '/dao-tao#chuan-dau-ra', 1),
(7, 1, 5, 'Danh sách môn học', '/dao-tao#danh-sach-mon-hoc', 2),
(8, 1, NULL, 'Hoạt động', '/danh-muc/hoat-dong', 4),
(9, 1, 8, 'Công tác giảng dạy', '/danh-muc/cong-tac-giang-day', 1),
(10, 1, 8, 'Nghiên cứu khoa học', '/danh-muc/nghien-cuu-khoa-hoc', 2),
(11, 1, 10, 'Sinh viên', '/tin-tuc/nghien-cuu-sinh-vien', 1),
(12, 1, 10, 'Giảng viên', '/tin-tuc/nghien-cuu-giang-vien', 2),
(13, 1, 8, 'Học thuật', '/danh-muc/hoc-thuat', 3),
(14, 1, 8, 'Thi đua đoàn thể', '/danh-muc/thi-dua-doan-the', 4),
(15, 1, 8, 'Phong trào & Ngoại khóa', '/danh-muc/phong-trao-ngoai-khoa', 5),
(16, 1, 8, 'CLB Tin học', '/danh-muc/clb-tin-hoc', 6),
(17, 1, NULL, 'Sinh viên', '/danh-muc/sinh-vien', 5),
(18, 1, 17, 'E-learning', 'https://ttth-caothang.site', 1),
(19, 1, 17, 'Thông báo', '/danh-muc/thong-bao', 2),
(20, 1, 17, 'Portal', '/portal', 3),
(21, 1, 2, 'Tầm nhìn & Sứ mệnh', '/gioi-thieu#tam-nhin-su-menh', 1),
(25, 1, 2, 'Lịch sử phát triển', '/gioi-thieu#lich-su-phat-trien', 2),
(26, 1, 2, 'Đội ngũ giảng viên', '/giang-vien', 3),
(23, 1, 22, 'Doanh Nghiệp', '/viec-lam/doanh-nghiep', 1),
(22, 1, NULL, 'Việc Làm', '/danh-muc/tuyen-dung', 6),
(24, 1, 22, 'Tin Tuyển Dụng', '/danh-muc/tuyen-dung', 2),

-- Footer Menu
(105, 2, NULL, 'Liên kết nhanh', '#', 1),
(106, 2, NULL, 'Chương trình đào tạo', '#', 2),
(107, 2, 105, 'Trang chủ', '/', 1),
(108, 2, 105, 'Giới thiệu', '/gioi-thieu', 2),
(109, 2, 105, 'Đội ngũ giảng viên', '/giang-vien', 3),
(110, 2, 105, 'Tin tức & Sự kiện', '/tin-tuc', 4),
(111, 2, 105, 'Sinh viên', '/danh-muc/sinh-vien', 5),
(112, 2, 105, 'Việc làm', '/danh-muc/tuyen-dung', 6),
(113, 2, 106, 'Đào tạo', '/dao-tao', 1),
(114, 2, 106, 'Thông tin tuyển sinh', '/dao-tao#tuyen-sinh', 2),
(115, 2, 106, 'Chương trình đào tạo', '/dao-tao#chuong-trinh-dao-tao', 3),
(116, 2, 106, 'Chuẩn đầu ra', '/dao-tao#chuan-dau-ra', 4),
(117, 2, 106, 'Danh sách môn học', '/dao-tao#danh-sach-mon-hoc', 5),
(118, 2, 106, 'Doanh nghiệp đối tác', '/viec-lam/doanh-nghiep', 6);

-- ---------------------------------------------------------------------------- 
-- 11. BẢNG CAROUSELS & CAROUSEL_SLIDES
-- ---------------------------------------------------------------------------- 
INSERT INTO `carousels` (`id`, `name`, `slug`, `is_active`) VALUES 
(1, 'Trang chủ (Landing Page)', 'landing-page', 1),
(2, 'Doanh nghiệp đối tác', 'partner-companies', 1);

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
'{"version":1,"sections":[{"id":"hero","type":"sections/landing_hero","locked":true,"data":[]},{"id":"landing_about","type":"sections/landing_about","locked":false,"data":{"items":[{"number":"01","image":{"src":"public/media/2026/07/0eea3ed1-c7f1-42c3-96ed-f5bd4dee97ab_original.webp","alt":"Lecture hall with students"},"card":{"value":"Top 1","label":"Khoa CNTT tại Miền Nam"},"eyebrow":"LOREM ISPUM GÌ ĐÓ Ở ĐÂY","title":"Đảm bảo chất lượng đào tạo","description":"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sed leo et neque vehicula lacinia vel at lorem."},{"number":"02","image":{"src":"public/media/2026/07/0eea3ed1-c7f1-42c3-96ed-f5bd4dee97ab_original.webp","alt":"Lecture hall with students"},"card":{"value":"98%","label":"Tỷ lệ có việc làm"},"eyebrow":"LOREM ISPUM GÌ ĐÓ Ở ĐÂY","title":"Cơ hội Nghề nghiệp","description":"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sed leo et neque vehicula lacinia vel at lorem."},{"number":"03","image":{"src":"public/media/2026/07/0eea3ed1-c7f1-42c3-96ed-f5bd4dee97ab_original.webp","alt":"Lecture hall with students"},"card":{"value":"50+","label":"Doanh nghiệp"},"eyebrow":"LOREM ISPUM GÌ ĐÓ Ở ĐÂY","title":"Nghiên cứu Đột phá","description":"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sed leo et neque vehicula lacinia vel at lorem."}]}},{"id":"why_choose_us","type":"sections/why_choose_us","locked":false,"data":{"badge":"Tại sao chọn chúng tôi","title":"Trải nghiệm Khoa CNTT Cao Thắng","subtitle":"Nơi ươm mầm tài năng công nghệ thông tin, kết nối tri thức với thực tiễn","feature":{"image":"public/media/2026/07/8674792e-d9b8-4c4c-b419-4276dc08d1eb_original.webp","alt":"Trường Cao Thắng","badge":"Nổi bật","title":"Môi trường học tập hiện đại, sáng tạo","description":"Trang bị phòng lab tiêu chuẩn quốc tế, thư viện số phong phú, không gian làm việc nhóm linh hoạt và hệ thống học tập trực tuyến tiên tiến.","cta_label":"Khám phá ngay","cta_url":"#"},"stats":[{"number":"20","title":"Năm kinh nghiệm","description":"Tiên phong trong đào tạo CNTT chất lượng cao tại TP.HCM từ năm 2003"},{"number":"95%","title":"Tỷ lệ việc làm","description":"Sinh viên có việc làm trong vòng 6 tháng sau tốt nghiệp"}],"perks":[{"icon":"fa-solid fa-code","title":"Công nghệ tiên tiến","description":"Học tập với các công nghệ mới nhất: AI, Cloud, Blockchain, IoT"},{"icon":"fa-solid fa-user-group","title":"Cộng đồng Mạnh mẽ","description":"Kết nối với 10,000+ sinh viên và cựu sinh viên trên toàn quốc"},{"icon":"fa-solid fa-award","title":"Chất lượng Quốc tế","description":"Chương trình đạt chuẩn ABET và kiểm định quốc tế"},{"icon":"fa-solid fa-rocket","title":"Khởi nghiệp","description":"Hỗ trợ ý tưởng startup và kết nối nhà đầu tư"}],"highlights":[{"image":"public/media/2026/07/8674792e-d9b8-4c4c-b419-4276dc08d1eb_original.webp","alt":"Trường Cao Thắng","title":"Nghiên cứu & Phát triển","description":"Tham gia các dự án nghiên cứu thực tế cùng giảng viên"},{"image":"public/media/2026/07/8674792e-d9b8-4c4c-b419-4276dc08d1eb_original.webp","alt":"Trường Cao Thắng","title":"Hợp tác Quốc tế","description":"Cơ hội trao đổi sinh viên và học bổng du học"}]}},{"id":"stats","type":"sections/stats","locked":false,"data":{"title":"Khoa CNTT Cao Thắng","subtitle":"Định hình tương lai công nghệ thông tin Việt Nam","stats":[{"icon":"fa-solid fa-award","number":"50+","label":"Giải thưởng","description":"Trong các cuộc thi lập trình"},{"icon":"fa-solid fa-graduation-cap","number":"10K+","label":"Sinh viên","description":"Tốt nghiệp thành công"},{"icon":"fa-solid fa-briefcase","number":"95%","label":"Việc làm","description":"Sau 6 tháng tốt nghiệp"},{"icon":"fa-solid fa-earth-americas","number":"20+","label":"Quốc gia","description":"Hợp tác quốc tế"}],"benefits":[{"icon":"fa-solid fa-building-columns","title":"Chương trình Đào tạo Tiên tiến","items":["Cập nhật theo công nghệ mới nhất","Tích hợp chứng chỉ quốc tế","Thực hành dự án thực tế","Đào tạo kỹ năng mềm"]},{"icon":"fa-solid fa-arrow-trend-up","title":"Phát triển Nghề nghiệp","items":["Kết nối với 100+ doanh nghiệp","Thực tập tại công ty hàng đầu","Tư vấn định hướng nghề nghiệp","Cơ hội việc làm cao"]}],"cta":{"title":"Sẵn sàng bắt đầu hành trình của bạn?","description":"Gia nhập cộng đồng hơn 10,000 sinh viên và cựu sinh viên đang làm việc tại các công ty công nghệ hàng đầu","buttons":[{"label":"Đăng ký tư vấn","url":"#","variant":"outline-alt"},{"label":"Xem chương trình đào tạo","url":"#","variant":"outline"}]}}},{"id":"newsfeed","type":"sections/newsfeed","locked":true,"data":[]}]}',
'{}', NOW(), NOW(), NOW()),
('Giới thiệu', 'about', '/gioi-thieu', 'landing_page', 'published', 'section_schema',
'{"version":1,"sections":[{"id":"breadcrumbs","type":"sections/breadcrumbs","locked":true,"data":[]},{"id":"about_hero","type":"sections/about_hero","locked":false,"data":{"image":"public/media/2026/07/0eea3ed1-c7f1-42c3-96ed-f5bd4dee97ab_original.webp","badge":"Về Chúng Tôi","title":"Câu chuyện của Cao Thắng","subtitle":"Từ những ngày đầu tiên đến hôm nay, Cao Thắng không ngừng phát triển để mang đến giáo dục công nghệ chất lượng cao cho sinh viên Việt Nam"}},{"id":"history","type":"sections/history","locked":false,"data":{"sections":[{"image":{"src":"public/media/2026/07/0eea3ed1-c7f1-42c3-96ed-f5bd4dee97ab_original.webp","alt":"Lecture hall with students","caption":"Khoa CNTT được thành lập"},"year":"1998","badge":"<i class=\\"fa-solid fa-graduation-cap\\"></i> <span class=\\"text-sm\\">Khoa Công Nghệ Thông Tin</span>","title":"27 năm đổi mới & phát triển","timeline":[{"year":"1998","description":"Khoa Điện Tử - Tin Học, tiền thân của khoa Công Nghệ Thông Tin được thành lập."},{"year":"2020","description":"Đổi tên Khoa Điện tử - Tin học thành Khoa Công nghệ thông tin."}]},{"image":{"src":"public/media/2026/07/0eea3ed1-c7f1-42c3-96ed-f5bd4dee97ab_original.webp","alt":"Lecture hall with students","caption":"Trường được thành lập"},"year":"1906","badge":"<i class=\\"fa-solid fa-building-columns\\"></i> <span class=\\"text-sm\\">Trường Cao Đẳng Kỹ Thuật Cao Thắng</span>","title":"100+ năm truyền thống","timeline":[{"year":"1906","description":"Chính thức thành lập Trường Cơ khí Á Châu (L''école des Mécaniciens Asiatiques), tiền thân của trường."},{"year":"1915","description":"Chủ tịch Tôn Đức Thắng nhập học."},{"year":"2004","description":"Chính thức đổi tên thành Trường Cao đẳng Kỹ thuật Cao Thắng."},{"year":"2016","description":"Đạt chuẩn kiểm định quốc tế ABET."}]}]}},{"id":"bento_grid","type":"sections/bento_grid","locked":false,"data":{"items":[{"badge":"<i class=\\"fa-solid fa-award\\"></i> <span>Chứng nhận Quốc Tế</span>","image":{"src":"public/media/2026/07/0eea3ed1-c7f1-42c3-96ed-f5bd4dee97ab_original.webp","alt":""},"content":"Thành tựu","footer":"<span class=\\"badge px-3 py-2 text-sm md:text-md\\" data-variant=\\"glass\\">30+ Quốc gia công nhận</span> <span class=\\"badge px-3 py-2 text-sm md:text-md\\" data-variant=\\"glass\\">Top 5 Khoa CNTT VN</span>"},{"badge":"<i class=\\"fa-solid fa-user-group\\"></i>","image":{"src":"","alt":""},"content":"25+","subContent":"Giảng Viên","footer":"Có hơn 15 năm kinh nghiệm trong việc giảng dạy"},{"badge":"<i class=\\"fa-solid fa-award\\"></i>","image":{"src":"","alt":""},"content":"50+","subContent":"Giải Thưởng","footer":"Từ chính phủ và các tổ chức kiểm định quốc tế"},{"badge":"<i class=\\"fa-solid fa-rocket\\"></i>","image":{"src":"","alt":""},"content":"10+","subContent":"Phòng Lab hiện đại","footer":"Trang bị công nghệ tiên tiến phục vụ học tập và nghiên cứu"},{"badge":"<i class=\\"fa-solid fa-graduation-cap\\"></i>","image":{"src":"","alt":""},"content":"100+","subContent":"Học bổng hàng năm","footer":"Từ học bổng toàn phần đến các suất trao đổi quốc tế"},{"badge":"<i class=\\"fa-solid fa-user-group\\"></i> <span>Cộng đồng học tập</span>","image":{"src":"public/media/2026/07/0eea3ed1-c7f1-42c3-96ed-f5bd4dee97ab_original.webp","alt":""},"content":"Môi trường","subContent":"Năng động & sáng tạo","footer":"Nhiều hoạt động, sự kiện nhằm thúc đẩy tinh thần năng nổ của Sinh Viên"},{"badge":"<i class=\\"fa-solid fa-building\\"></i>","image":{"src":"","alt":""},"content":"100+","subContent":"Doanh nghiệp đối tác","footer":"FPT, Viettel, Samsung, Google và nhiều công ty hàng đầu"},{"badge":"<i class=\\"fa-solid fa-arrow-trend-up\\"></i>","image":{"src":"","alt":""},"content":"1,000+","subContent":"Cựu sinh viên","footer":"Làm việc tại các công ty công nghệ hàng đầu toàn cầu"}]}}]}',
'{}', NOW(), NOW(), NOW()),
('Tầm nhìn & Sứ mệnh', 'vision-mission', '/gioi-thieu/tam-nhin-su-menh', 'about_page', 'published', 'section_schema',
'{"version":1,"sections":[{"id":"vision_mission","type":"sections/vision_mission","locked":false,"data":{"eyebrow":"Định hướng phát triển","title":"Tầm nhìn & Sứ mệnh","introduction":"Kế thừa truyền thống đào tạo kỹ thuật của Cao Thắng, Khoa Công nghệ thông tin gắn tri thức với thực hành, đổi mới và nhu cầu của xã hội.","vision_title":"Tầm nhìn","vision":"Trở thành đơn vị đào tạo công nghệ thông tin ứng dụng vững mạnh, hiện đại và nhân văn; không ngừng nâng cao chất lượng để người học thích nghi, sáng tạo và phát triển trong môi trường công nghệ luôn thay đổi.","mission_title":"Sứ mệnh","mission":"Đào tạo nguồn nhân lực có kỷ luật, đạo đức nghề nghiệp, kiến thức vững và tay nghề tốt; kết nối đào tạo với thực tiễn doanh nghiệp, thúc đẩy nghiên cứu, đổi mới phương pháp giảng dạy và ứng dụng công nghệ phục vụ nhà trường và cộng đồng.","principles":[{"title":"Học đi đôi với hành","description":"Chú trọng năng lực thực hành, giải quyết vấn đề và khả năng đáp ứng công việc thực tế."},{"title":"Đổi mới liên tục","description":"Cập nhật chương trình, công nghệ và phương pháp giảng dạy phù hợp với sự phát triển của xã hội."},{"title":"Đồng hành cùng doanh nghiệp","description":"Mở rộng hợp tác trong đào tạo, thực tập, nghiên cứu và tạo cơ hội nghề nghiệp cho sinh viên."}],"source_note":"Nội dung được biên soạn từ tư liệu lịch sử Kỷ yếu Khoa Điện tử - Tin học; đây là bản CMS có thể tiếp tục hiệu chỉnh và phê duyệt."}}]}',
'{}', NOW(), NOW(), NOW()),
('Đào tạo', 'education', '/dao-tao', 'education_page', 'published', 'section_schema',
'{"version":1,"sections":[{"id":"education_hub","type":"sections/education_hub","locked":false,"data":{"eyebrow":"Đào tạo tại Khoa Công nghệ thông tin","title":"Chọn chương trình phù hợp với bạn","description":"Khám phá thông tin tuyển sinh, định hướng từng chương trình, chuẩn đầu ra và kế hoạch học tập được công bố chính thức.","links":[{"icon":"fa-solid fa-user-graduate","title":"Thông tin tuyển sinh","description":"Tìm hiểu ngành đào tạo trước khi xem hướng dẫn đăng ký chính thức của Trường.","url":"dao-tao/tuyen-sinh","label":"Xem thông tin"},{"icon":"fa-solid fa-graduation-cap","title":"Chương trình đào tạo","description":"So sánh mục tiêu, định hướng nghề nghiệp và cấu trúc của ba chương trình.","url":"dao-tao/chuong-trinh-dao-tao","label":"Khám phá chương trình"},{"icon":"fa-solid fa-bullseye","title":"Chuẩn đầu ra","description":"Những năng lực sinh viên cần đạt khi tốt nghiệp và sau khi tham gia thị trường lao động.","url":"dao-tao/chuan-dau-ra","label":"Xem chuẩn đầu ra"},{"icon":"fa-solid fa-list-check","title":"Danh sách môn học","description":"Theo dõi các học phần theo từng học kỳ, gồm tín chỉ và thời lượng lý thuyết, thực hành.","url":"dao-tao/danh-sach-mon-hoc","label":"Xem kế hoạch học tập"}],"programs_title":"Ba chương trình, nhiều hướng phát triển","programs_description":"Các chương trình kết hợp kiến thức nền tảng với thực hành nghề nghiệp và được tổ chức trong sáu học kỳ.","programs":[{"key":"cntt","name":"Cao đẳng Công nghệ thông tin","short_name":"CNTT","summary":"Phát triển giải pháp phần mềm, web, di động, mạng máy tính và trí tuệ nhân tạo ứng dụng.","credits":"134"},{"key":"qtm","name":"Cao đẳng Quản trị mạng máy tính","short_name":"QTM","summary":"Thiết kế, triển khai, vận hành và bảo vệ hạ tầng mạng, máy chủ và dịch vụ hệ thống.","credits":"134"},{"key":"scmt","name":"Cao đẳng Kỹ thuật sửa chữa, lắp ráp máy tính","short_name":"SCMT","summary":"Lắp ráp, chẩn đoán, sửa chữa phần cứng và triển khai hệ thống máy tính, thiết bị mạng.","credits":"131"}]}}]}',
'{}', NOW(), NOW(), NOW()),
('Thông tin tuyển sinh', 'admissions', '/dao-tao/tuyen-sinh', 'education_page', 'published', 'section_schema',
'{"version":1,"sections":[{"id":"admissions","type":"sections/admissions","locked":false,"data":{"eyebrow":"Thông tin tuyển sinh","title":"Bắt đầu hành trình tại Cao Thắng","description":"Khoa Công nghệ thông tin đào tạo theo định hướng ứng dụng, chú trọng năng lực thực hành và khả năng thích nghi với môi trường nghề nghiệp.","notice_title":"Thông tin đăng ký chính thức","notice":"Chỉ tiêu, phương thức xét tuyển, mốc thời gian, học phí và hồ sơ được Trường Cao đẳng Kỹ thuật Cao Thắng công bố tập trung trên cổng tuyển sinh. Nội dung tại trang này giúp bạn định hướng chương trình, không thay thế thông báo tuyển sinh chính thức.","cta_label":"Đến cổng tuyển sinh Cao Thắng","cta_url":"https://caothang.edu.vn/tuyensinh/","steps_title":"Tìm chương trình phù hợp","steps":[{"title":"Khám phá chương trình","description":"Đọc mục tiêu và định hướng nghề nghiệp của từng ngành."},{"title":"Kiểm tra chuẩn đầu ra","description":"Xác định những kiến thức và kỹ năng bạn sẽ hình thành."},{"title":"Xem kế hoạch học tập","description":"Tham khảo các môn học, tín chỉ và thời lượng thực hành."},{"title":"Theo dõi thông báo chính thức","description":"Kiểm tra điều kiện và thời hạn đăng ký tại cổng tuyển sinh của Trường."}],"programs":[{"key":"cntt","name":"Cao đẳng Công nghệ thông tin","summary":"Phát triển giải pháp phần mềm, web, di động, mạng máy tính và trí tuệ nhân tạo ứng dụng."},{"key":"qtm","name":"Cao đẳng Quản trị mạng máy tính","summary":"Thiết kế, triển khai, vận hành và bảo vệ hạ tầng mạng, máy chủ và dịch vụ hệ thống."},{"key":"scmt","name":"Cao đẳng Kỹ thuật sửa chữa, lắp ráp máy tính","summary":"Lắp ráp, chẩn đoán, sửa chữa phần cứng và triển khai hệ thống máy tính, thiết bị mạng."}]}}]}',
'{}', NOW(), NOW(), NOW()),
('Chương trình đào tạo', 'academic-programs', '/dao-tao/chuong-trinh-dao-tao', 'education_page', 'published', 'section_schema',
'{"version":1,"sections":[{"id":"programs","type":"sections/programs","locked":false,"data":{"eyebrow":"Chương trình đào tạo","title":"Nền tảng vững, kỹ năng sát thực tế","description":"Xem mục tiêu, cấu trúc và hướng phát triển nghề nghiệp của từng chương trình.","programs":[{"key":"cntt","short_name":"CNTT","name":"Cao đẳng Công nghệ thông tin","summary":"Phát triển giải pháp phần mềm, web, di động, mạng máy tính và trí tuệ nhân tạo ứng dụng.","duration":"6 học kỳ","credits":"134","practice_ratio":"59,8%","source_year":"2026","outcomes_year":"2026","updated_at":"21/04/2026","career":"Tham gia phân tích, thiết kế, triển khai và tư vấn các giải pháp công nghệ thông tin trong cơ quan, tổ chức và doanh nghiệp.","objectives":["Là thành viên chủ chốt trong nhóm phân tích, thiết kế, triển khai, tư vấn các giải pháp và dự án công nghệ thông tin.","Hoàn thiện kỹ năng và kiến thức các lĩnh vực công nghệ thông tin thông qua việc tiếp tục học tập nâng cao trình độ.","Trở thành chuyên gia công nghệ thông tin có tinh thần đóng góp xã hội; tuân thủ đạo đức, pháp luật và nhận thức tác động của công nghệ đối với xã hội."],"outcomes":["Phân tích bài toán trong phạm vi rộng và áp dụng kiến thức cơ bản về công nghệ thông tin để xác định giải pháp.","Thiết kế và triển khai các giải pháp công nghệ thông tin đáp ứng tập hợp yêu cầu trong ngữ cảnh cho trước.","Giao tiếp hiệu quả trong môi trường làm việc chuyên nghiệp.","Nhận thức trách nhiệm nghề nghiệp và đưa ra đánh giá dựa trên các nguyên tắc pháp lý, đạo đức.","Làm việc hiệu quả với vai trò thành viên của nhóm.","Áp dụng, tích hợp và quản lý bảo mật trong lĩnh vực công nghệ thông tin để đáp ứng nhu cầu người dùng."],"specializations":["Công nghệ lập trình ứng dụng Web","Công nghệ lập trình ứng dụng Di động","Mạng máy tính","Trí tuệ nhân tạo ứng dụng"]},{"key":"qtm","short_name":"QTM","name":"Cao đẳng Quản trị mạng máy tính","summary":"Thiết kế, triển khai, vận hành và bảo vệ hạ tầng mạng, máy chủ và dịch vụ hệ thống.","duration":"6 học kỳ","credits":"134","practice_ratio":"58,5%","source_year":"2026","outcomes_year":"2026","updated_at":"27/05/2026","career":"Làm việc trong quản trị hệ thống mạng, triển khai hạ tầng, bảo mật và tư vấn giải pháp tin học hóa cho tổ chức, doanh nghiệp.","objectives":["Là nhân viên lành nghề trong công việc quản trị hệ thống mạng của tổ chức, doanh nghiệp.","Là nhân viên lành nghề trong nhóm phân tích, thiết kế và triển khai các giải pháp hạ tầng hệ thống mạng.","Là nhân viên lành nghề trong hoạt động tư vấn kỹ thuật, tư vấn giải pháp tin học hóa."],"outcomes":["Tuân thủ quy định về an toàn thông tin, bảo mật dữ liệu và đạo đức nghề nghiệp; có trách nhiệm trong công việc.","Tự học, tìm hiểu tài liệu kỹ thuật và cập nhật công nghệ mới phục vụ chuyên môn.","Giao tiếp, làm việc nhóm, trình bày vấn đề kỹ thuật và viết báo cáo chuyên môn hiệu quả.","Sử dụng các phần mềm ứng dụng phục vụ công việc chuyên môn.","Lắp ráp, cài đặt, cấu hình, bảo trì và xử lý sự cố máy tính, thiết bị mạng cơ bản.","Phân tích yêu cầu để thiết kế, triển khai, vận hành và bảo trì hệ thống mạng.","Phân tích, thiết kế, triển khai và bảo trì cơ sở dữ liệu, phần mềm, website và mạng máy tính.","Áp dụng giải pháp bảo mật và an ninh mạng cơ bản để giám sát, bảo đảm an toàn hệ thống thông tin."],"specializations":[]},{"key":"scmt","short_name":"SCMT","name":"Cao đẳng Kỹ thuật sửa chữa, lắp ráp máy tính","summary":"Lắp ráp, chẩn đoán, sửa chữa phần cứng và triển khai hệ thống máy tính, thiết bị mạng.","duration":"6 học kỳ","credits":"131","practice_ratio":"59,8%","source_year":"2026","outcomes_year":"2024","updated_at":"27/05/2026","career":"Xây dựng, bảo trì và sửa chữa hệ thống máy tính; tư vấn giải pháp kỹ thuật và lựa chọn thiết bị tin học.","objectives":["Là nhân viên lành nghề trong xây dựng, bảo trì, sửa chữa hệ thống máy tính của cơ quan, tổ chức và doanh nghiệp.","Là nhân viên lành nghề trong hoạt động tư vấn giải pháp kỹ thuật và lựa chọn thiết bị tin học."],"outcomes":["Có động cơ nghề nghiệp đúng đắn, tuân thủ chuẩn mực và đạo đức nghề nghiệp, chịu trách nhiệm với công việc.","Có ý thức học tập và tự rèn luyện để nâng cao trình độ chuyên môn.","Áp dụng hiệu quả kỹ năng giao tiếp, làm việc nhóm, trình bày vấn đề kỹ thuật, quản lý thời gian và viết báo cáo.","Đọc hiểu tài liệu hướng dẫn tiếng Anh và sử dụng phần mềm văn phòng, đồ họa phục vụ công việc.","Lắp ráp, cài đặt phần mềm và xử lý các sự cố thường gặp của máy tính.","Phân tích, đánh giá, chẩn đoán sự cố; đưa ra giải pháp bảo trì, sửa chữa và thay thế phần cứng.","Phân tích yêu cầu để thiết kế, triển khai, vận hành và bảo trì hệ thống máy tính."],"specializations":[]}]}}]}',
'{}', NOW(), NOW(), NOW()),
('Chuẩn đầu ra', 'program-outcomes', '/dao-tao/chuan-dau-ra', 'education_page', 'published', 'section_schema',
'{"version":1,"sections":[{"id":"outcomes","type":"sections/outcomes","locked":false,"data":{"eyebrow":"Chuẩn đầu ra","title":"Năng lực được hình thành qua chương trình","description":"Mục tiêu chương trình mô tả năng lực sau 2–3 năm làm việc; chuẩn đầu ra là những gì sinh viên có khả năng thực hiện khi tốt nghiệp.","programs":[{"key":"cntt","name":"Cao đẳng Công nghệ thông tin","short_name":"CNTT","source_year":"2026","updated_at":"27/05/2026","objectives":["Là thành viên chủ chốt trong nhóm phân tích, thiết kế, triển khai, tư vấn các giải pháp và dự án công nghệ thông tin.","Hoàn thiện kỹ năng và kiến thức các lĩnh vực công nghệ thông tin thông qua việc tiếp tục học tập nâng cao trình độ.","Trở thành chuyên gia công nghệ thông tin có tinh thần đóng góp xã hội; tuân thủ đạo đức, pháp luật và nhận thức tác động của công nghệ đối với xã hội."],"outcomes":["Phân tích bài toán trong phạm vi rộng và áp dụng kiến thức cơ bản về công nghệ thông tin để xác định giải pháp.","Thiết kế và triển khai các giải pháp công nghệ thông tin đáp ứng tập hợp yêu cầu trong ngữ cảnh cho trước.","Giao tiếp hiệu quả trong môi trường làm việc chuyên nghiệp.","Nhận thức trách nhiệm nghề nghiệp và đưa ra đánh giá dựa trên các nguyên tắc pháp lý, đạo đức.","Làm việc hiệu quả với vai trò thành viên của nhóm.","Áp dụng, tích hợp và quản lý bảo mật trong lĩnh vực công nghệ thông tin để đáp ứng nhu cầu người dùng."]},{"key":"qtm","name":"Cao đẳng Quản trị mạng máy tính","short_name":"QTM","source_year":"2026","updated_at":"27/05/2026","objectives":["Là nhân viên lành nghề trong công việc quản trị hệ thống mạng của tổ chức, doanh nghiệp.","Là nhân viên lành nghề trong nhóm phân tích, thiết kế và triển khai các giải pháp hạ tầng hệ thống mạng.","Là nhân viên lành nghề trong hoạt động tư vấn kỹ thuật, tư vấn giải pháp tin học hóa."],"outcomes":["Tuân thủ quy định về an toàn thông tin, bảo mật dữ liệu và đạo đức nghề nghiệp; có trách nhiệm trong công việc.","Tự học, tìm hiểu tài liệu kỹ thuật và cập nhật công nghệ mới phục vụ chuyên môn.","Giao tiếp, làm việc nhóm, trình bày vấn đề kỹ thuật và viết báo cáo chuyên môn hiệu quả.","Sử dụng các phần mềm ứng dụng phục vụ công việc chuyên môn.","Lắp ráp, cài đặt, cấu hình, bảo trì và xử lý sự cố máy tính, thiết bị mạng cơ bản.","Phân tích yêu cầu để thiết kế, triển khai, vận hành và bảo trì hệ thống mạng.","Phân tích, thiết kế, triển khai và bảo trì cơ sở dữ liệu, phần mềm, website và mạng máy tính.","Áp dụng giải pháp bảo mật và an ninh mạng cơ bản để giám sát, bảo đảm an toàn hệ thống thông tin."]},{"key":"scmt","name":"Cao đẳng Kỹ thuật sửa chữa, lắp ráp máy tính","short_name":"SCMT","source_year":"2024","updated_at":"27/05/2026","objectives":["Là nhân viên lành nghề trong xây dựng, bảo trì, sửa chữa hệ thống máy tính của cơ quan, tổ chức và doanh nghiệp.","Là nhân viên lành nghề trong hoạt động tư vấn giải pháp kỹ thuật và lựa chọn thiết bị tin học."],"outcomes":["Có động cơ nghề nghiệp đúng đắn, tuân thủ chuẩn mực và đạo đức nghề nghiệp, chịu trách nhiệm với công việc.","Có ý thức học tập và tự rèn luyện để nâng cao trình độ chuyên môn.","Áp dụng hiệu quả kỹ năng giao tiếp, làm việc nhóm, trình bày vấn đề kỹ thuật, quản lý thời gian và viết báo cáo.","Đọc hiểu tài liệu hướng dẫn tiếng Anh và sử dụng phần mềm văn phòng, đồ họa phục vụ công việc.","Lắp ráp, cài đặt phần mềm và xử lý các sự cố thường gặp của máy tính.","Phân tích, đánh giá, chẩn đoán sự cố; đưa ra giải pháp bảo trì, sửa chữa và thay thế phần cứng.","Phân tích yêu cầu để thiết kế, triển khai, vận hành và bảo trì hệ thống máy tính."]}]}}]}',
'{}', NOW(), NOW(), NOW()),
('Danh sách môn học', 'curriculum', '/dao-tao/danh-sach-mon-hoc', 'education_page', 'published', 'section_schema',
'{"version":1,"sections":[{"id":"curriculum","type":"sections/curriculum","locked":false,"data":{"eyebrow":"Danh sách môn học","title":"Kế hoạch học tập theo từng học kỳ","description":"Chọn chương trình và học kỳ để xem các học phần.","programs":[{"key":"cntt","name":"Cao đẳng Công nghệ thông tin","short_name":"CNTT","source_year":"2026","updated_at":"27/05/2026","credits":"134","semesters":[{"key":"1","name":"Học kỳ 1","courses":[{"code":"","name":"Pháp luật","credits":"2","theory":"30","practice":"0"},{"code":"","name":"Toán cao cấp","credits":"3","theory":"45","practice":"0"},{"code":"","name":"Toán rời rạc và Lý thuyết đồ thị","credits":"3","theory":"21","practice":"24"},{"code":"","name":"Phần cứng máy tính","credits":"3","theory":"30","practice":"15"},{"code":"","name":"Nhập môn lập trình","credits":"5","theory":"57","practice":"18"},{"code":"","name":"Tin học ứng dụng","credits":"3","theory":"16","practice":"29"},{"code":"","name":"Thực tập Phần cứng máy tính","credits":"1","theory":"0","practice":"45"},{"code":"","name":"Thực tập Nhập môn lập trình","credits":"2","theory":"0","practice":"90"}]},{"key":"2","name":"Học kỳ 2","courses":[{"code":"","name":"Cơ sở dữ liệu","credits":"5","theory":"43","practice":"32"},{"code":"","name":"Cấu trúc dữ liệu và giải thuật","credits":"3","theory":"26","practice":"19"},{"code":"","name":"Mạng máy tính","credits":"3","theory":"36","practice":"9"},{"code":"","name":"Thiết kế Web","credits":"3","theory":"30","practice":"15"},{"code":"","name":"Thực tập Thiết kế Web","credits":"1","theory":"0","practice":"45"},{"code":"","name":"Thực tập Cấu trúc dữ liệu và giải thuật","credits":"1","theory":"0","practice":"45"},{"code":"","name":"Thực tập Mạng máy tính","credits":"1","theory":"0","practice":"45"},{"code":"","name":"Thực tập Cơ sở dữ liệu","credits":"1","theory":"0","practice":"45"}]},{"key":"3","name":"Học kỳ 3","courses":[{"code":"","name":"Giáo dục chính trị 1","credits":"3","theory":"45","practice":"0"},{"code":"","name":"Tiếng Anh 3","credits":"5","theory":"75","practice":"0"},{"code":"","name":"Vật lý đại cương","credits":"4","theory":"45","practice":"15"},{"code":"","name":"Cơ sở dữ liệu NoSQL","credits":"2","theory":"14","practice":"16"},{"code":"","name":"Quản trị hệ thống mạng Windows","credits":"3","theory":"30","practice":"15"},{"code":"","name":"Phương pháp lập trình hướng đối tượng","credits":"3","theory":"30","practice":"15"},{"code":"","name":"Lập trình Website cơ bản","credits":"3","theory":"15","practice":"30"},{"code":"","name":"Thực tập Quản trị hệ thống mạng Windows","credits":"1","theory":"0","practice":"45"},{"code":"","name":"Thực tập Phương pháp lập trình hướng đối tượng","credits":"1","theory":"0","practice":"45"}]},{"key":"4","name":"Học kỳ 4","courses":[{"code":"","name":"Giáo dục chính trị 2","credits":"2","theory":"30","practice":"0"},{"code":"","name":"Lập trình Windows và đồ án môn học","credits":"5","theory":"35","practice":"40"},{"code":"","name":"Thực tập Lập trình Windows","credits":"1","theory":"0","practice":"45"},{"code":"","name":"[Web/Di động] Phân tích thiết kế hệ thống thông tin","credits":"4","theory":"30","practice":"30"},{"code":"","name":"[Web/Di động/AI] Lập trình ứng dụng với Python","credits":"6","theory":"40","practice":"50"},{"code":"","name":"[Web/Di động] Lập trình ứng dụng với Nodejs","credits":"3","theory":"30","practice":"15"},{"code":"","name":"[Web/Di động] Công nghệ phần mềm","credits":"4","theory":"35","practice":"25"},{"code":"","name":"[Mạng] Lập trình Python","credits":"3","theory":"21","practice":"24"},{"code":"","name":"[Mạng] Hệ điều hành Linux","credits":"3","theory":"27","practice":"18"},{"code":"","name":"[Mạng] Dịch vụ mạng","credits":"5","theory":"39","practice":"36"},{"code":"","name":"[Mạng] Cấu hình và quản trị thiết bị mạng Cisco","credits":"6","theory":"60","practice":"30"},{"code":"","name":"[AI] Cơ sở trí tuệ nhân tạo","credits":"4","theory":"40","practice":"20"},{"code":"","name":"[AI] Trực quan hóa dữ liệu","credits":"3","theory":"20","practice":"25"}]},{"key":"5","name":"Học kỳ 5","courses":[{"code":"","name":"Tiếng Anh chuyên ngành CNTT","credits":"3","theory":"45","practice":"0"},{"code":"","name":"[Web] Kiểm thử phần mềm","credits":"4","theory":"35","practice":"25"},{"code":"","name":"[Web] Lập trình Backend","credits":"3","theory":"30","practice":"15"},{"code":"","name":"[Web] Công nghệ lập trình Web và triển khai hệ thống","credits":"6","theory":"45","practice":"45"},{"code":"","name":"[Web] Lập trình Website nâng cao","credits":"6","theory":"50","practice":"40"},{"code":"","name":"[Web] Lập trình Front End","credits":"5","theory":"45","practice":"30"},{"code":"","name":"[Di động] Kiểm thử phần mềm","credits":"4","theory":"35","practice":"25"},{"code":"","name":"[Di động] Lập trình Backend","credits":"3","theory":"30","practice":"15"},{"code":"","name":"[Di động] Lập trình ứng dụng trên thiết bị di động","credits":"6","theory":"50","practice":"40"},{"code":"","name":"[Di động] Lập trình Website nâng cao","credits":"6","theory":"45","practice":"45"},{"code":"","name":"[Di động] Công nghệ lập trình đa nền tảng","credits":"5","theory":"45","practice":"30"},{"code":"","name":"[Mạng] Thiết kế hệ thống mạng","credits":"5","theory":"37","practice":"38"},{"code":"","name":"[Mạng] Bảo mật thiết bị mạng Cisco","credits":"3","theory":"25","practice":"20"},{"code":"","name":"[Mạng] Điện toán đám mây","credits":"3","theory":"25","practice":"20"},{"code":"","name":"[Mạng] Quản lý hệ thống Web và Mail Server","credits":"3","theory":"27","practice":"18"},{"code":"","name":"[Mạng] An ninh mạng","credits":"5","theory":"39","practice":"36"},{"code":"","name":"[Mạng] Quản trị mạng Linux","credits":"5","theory":"40","practice":"35"},{"code":"","name":"[AI] Khai phá dữ liệu và ứng dụng","credits":"4","theory":"35","practice":"25"},{"code":"","name":"[AI] Công nghệ phần mềm","credits":"4","theory":"35","practice":"25"},{"code":"","name":"[AI] Lập trình Website nâng cao","credits":"6","theory":"45","practice":"45"},{"code":"","name":"[AI] Học máy","credits":"5","theory":"45","practice":"30"},{"code":"","name":"[AI] Xử lý ngôn ngữ tự nhiên và thị giác máy tính","credits":"5","theory":"50","practice":"25"}]},{"key":"6","name":"Học kỳ 6","courses":[{"code":"","name":"[Web] Đồ án lập trình Web","credits":"1","theory":"0","practice":"30"},{"code":"","name":"[Di động] Đồ án lập trình di động","credits":"1","theory":"0","practice":"30"},{"code":"","name":"[Mạng] Đồ án Quản trị hệ thống mạng","credits":"1","theory":"0","practice":"30"},{"code":"","name":"[AI] Đồ án trí tuệ nhân tạo ứng dụng","credits":"1","theory":"0","practice":"30"},{"code":"","name":"Thực tập tốt nghiệp","credits":"6","theory":"0","practice":"480"},{"code":"","name":"Đồ án tốt nghiệp","credits":"4","theory":"0","practice":"240"}]}]},{"key":"qtm","name":"Cao đẳng Quản trị mạng máy tính","short_name":"QTM","source_year":"2026","updated_at":"27/05/2026","credits":"134","semesters":[{"key":"1","name":"Học kỳ 1","courses":[{"code":"","name":"Pháp luật","credits":"2","theory":"30","practice":"0"},{"code":"","name":"Lắp ráp, cài đặt máy tính","credits":"4","theory":"45","practice":"45"},{"code":"","name":"Nhập môn lập trình","credits":"4","theory":"41","practice":"19"},{"code":"","name":"Tin học ứng dụng","credits":"5","theory":"30","practice":"45"},{"code":"","name":"Mạng máy tính","credits":"4","theory":"41","practice":"19"},{"code":"","name":"Thực tập Mạng máy tính","credits":"1","theory":"0","practice":"45"},{"code":"","name":"Thực tập Nhập môn lập trình","credits":"1","theory":"0","practice":"45"}]},{"key":"2","name":"Học kỳ 2","courses":[{"code":"","name":"Cơ sở dữ liệu","credits":"4","theory":"41","practice":"19"},{"code":"","name":"Kỹ thuật lập trình","credits":"5","theory":"30","practice":"45"},{"code":"","name":"Quản trị hệ thống mạng","credits":"4","theory":"40","practice":"50"},{"code":"","name":"Đồ họa ứng dụng","credits":"5","theory":"39","practice":"51"},{"code":"","name":"Thiết kế Web","credits":"4","theory":"30","practice":"60"}]},{"key":"3","name":"Học kỳ 3","courses":[{"code":"","name":"Giáo dục chính trị 1","credits":"3","theory":"45","practice":"0"},{"code":"","name":"Tiếng Anh chuyên ngành CNTT","credits":"3","theory":"45","practice":"0"},{"code":"","name":"Cấu trúc dữ liệu và giải thuật","credits":"5","theory":"30","practice":"45"},{"code":"","name":"Hệ quản trị cơ sở dữ liệu","credits":"5","theory":"30","practice":"45"},{"code":"","name":"Các dịch vụ mạng","credits":"6","theory":"39","practice":"51"},{"code":"","name":"Hệ điều hành Linux","credits":"5","theory":"30","practice":"45"}]},{"key":"4","name":"Học kỳ 4","courses":[{"code":"","name":"Giáo dục chính trị 2","credits":"2","theory":"30","practice":"0"},{"code":"","name":"Tiếng Anh 3","credits":"5","theory":"75","practice":"0"},{"code":"","name":"Thiết kế và quản lý hệ thống mạng","credits":"4","theory":"39","practice":"51"},{"code":"","name":"Lập trình Web PHP","credits":"6","theory":"30","practice":"60"},{"code":"","name":"Quản lý hệ thống Web và Mail Server","credits":"3","theory":"27","practice":"18"},{"code":"","name":"Cấu hình và quản trị thiết bị mạng Cisco","credits":"4","theory":"40","practice":"50"}]},{"key":"5","name":"Học kỳ 5","courses":[{"code":"","name":"Bảo mật hệ thống mạng","credits":"3","theory":"15","practice":"30"},{"code":"","name":"Quản trị hệ thống mạng Linux","credits":"6","theory":"30","practice":"60"},{"code":"","name":"Chuyên đề CMS - mã nguồn mở","credits":"3","theory":"15","practice":"30"},{"code":"","name":"Lập trình Windows","credits":"5","theory":"50","practice":"40"},{"code":"","name":"An ninh mạng","credits":"5","theory":"30","practice":"45"},{"code":"","name":"Điện toán đám mây","credits":"3","theory":"25","practice":"20"}]},{"key":"6","name":"Học kỳ 6","courses":[{"code":"","name":"Thực tập tốt nghiệp","credits":"7","theory":"0","practice":"560"},{"code":"","name":"Thi tốt nghiệp lý thuyết nghề","credits":"1","theory":"15","practice":"0"},{"code":"","name":"Thi tốt nghiệp thực hành nghề","credits":"1","theory":"0","practice":"35"}]}]},{"key":"scmt","name":"Cao đẳng Kỹ thuật sửa chữa, lắp ráp máy tính","short_name":"SCMT","source_year":"2026","updated_at":"27/05/2026","credits":"131","semesters":[{"key":"1","name":"Học kỳ 1","courses":[{"code":"","name":"Pháp luật","credits":"2","theory":"30","practice":"0"},{"code":"","name":"Mạng máy tính","credits":"4","theory":"41","practice":"19"},{"code":"","name":"Tin học ứng dụng","credits":"5","theory":"30","practice":"45"},{"code":"","name":"Lắp ráp, cài đặt máy tính","credits":"4","theory":"45","practice":"45"},{"code":"","name":"Thực tập Mạng máy tính","credits":"1","theory":"0","practice":"45"},{"code":"","name":"Điện tử cơ bản","credits":"4","theory":"56","practice":"4"},{"code":"","name":"Thực tập Điện tử cơ bản","credits":"2","theory":"0","practice":"70"}]},{"key":"2","name":"Học kỳ 2","courses":[{"code":"","name":"Nhập môn lập trình","credits":"4","theory":"41","practice":"19"},{"code":"","name":"Xử lý sự cố phần mềm máy tính","credits":"3","theory":"25","practice":"20"},{"code":"","name":"Quản trị hệ thống mạng","credits":"6","theory":"40","practice":"50"},{"code":"","name":"Sửa chữa phần cứng máy tính 1","credits":"4","theory":"42","practice":"48"},{"code":"","name":"Thực tập Nhập môn lập trình","credits":"1","theory":"0","practice":"45"},{"code":"","name":"Kỹ thuật xung số","credits":"4","theory":"56","practice":"4"},{"code":"","name":"Thực tập Hàn tay điện tử IPC","credits":"1","theory":"0","practice":"35"}]},{"key":"3","name":"Học kỳ 3","courses":[{"code":"","name":"Tiếng Anh chuyên ngành CNTT","credits":"3","theory":"45","practice":"0"},{"code":"","name":"Lập trình nhúng","credits":"5","theory":"30","practice":"45"},{"code":"","name":"Kiến trúc máy tính","credits":"3","theory":"30","practice":"15"},{"code":"","name":"Sửa chữa phần cứng máy tính 2","credits":"4","theory":"42","practice":"48"},{"code":"","name":"Các dịch vụ mạng","credits":"5","theory":"30","practice":"45"},{"code":"","name":"Đồ án mạch điện tử","credits":"2","theory":"0","practice":"60"},{"code":"","name":"Thực tập Vẽ và mô phỏng mạch điện tử","credits":"2","theory":"0","practice":"70"}]},{"key":"4","name":"Học kỳ 4","courses":[{"code":"","name":"Giáo dục chính trị 1","credits":"3","theory":"45","practice":"0"},{"code":"","name":"Tiếng Anh 3","credits":"5","theory":"75","practice":"0"},{"code":"","name":"Thiết kế và quản lý hệ thống mạng","credits":"5","theory":"30","practice":"45"},{"code":"","name":"Hệ điều hành Linux","credits":"5","theory":"30","practice":"45"},{"code":"","name":"Đồ họa ứng dụng","credits":"5","theory":"30","practice":"45"},{"code":"","name":"Sửa chữa màn hình, máy in","credits":"3","theory":"42","practice":"3"},{"code":"","name":"Thực tập Sửa chữa màn hình, máy in","credits":"2","theory":"0","practice":"70"}]},{"key":"5","name":"Học kỳ 5","courses":[{"code":"","name":"Giáo dục chính trị 2","credits":"2","theory":"30","practice":"0"},{"code":"","name":"Cấu hình và quản trị thiết bị mạng Cisco","credits":"4","theory":"30","practice":"45"},{"code":"","name":"Chuyên đề thiết bị di động","credits":"3","theory":"25","practice":"20"},{"code":"","name":"Thiết kế mẫu","credits":"5","theory":"30","practice":"45"},{"code":"","name":"Thiết bị đầu cuối","credits":"3","theory":"42","practice":"3"},{"code":"","name":"Thực tập Thiết bị đầu cuối","credits":"2","theory":"0","practice":"70"}]},{"key":"6","name":"Học kỳ 6","courses":[{"code":"","name":"Thực tập tốt nghiệp","credits":"7","theory":"0","practice":"560"},{"code":"","name":"Thi tốt nghiệp lý thuyết nghề","credits":"1","theory":"15","practice":"0"},{"code":"","name":"Thi tốt nghiệp thực hành nghề","credits":"1","theory":"0","practice":"35"}]}]}]}}]}',
'{}', NOW(), NOW(), NOW());

INSERT INTO `cms_pages` (`title`, `slug`, `route_path`, `type`, `status`, `layout_mode`, `content_json`, `settings_json`, `published_at`, `created_at`, `updated_at`) VALUES
('Đội ngũ giảng viên', 'faculty', '/giang-vien', 'faculty_page', 'published', 'section_schema',
'{"version":1,"sections":[{"id":"breadcrumbs","type":"sections/breadcrumbs","locked":true,"data":[]},{"id":"faculty_hero","type":"sections/about_hero","locked":true,"data":{"image":"public/media/2026/07/0eea3ed1-c7f1-42c3-96ed-f5bd4dee97ab_original.webp","badge":"Đội ngũ giảng viên","title":"Khoa Công nghệ Thông tin","subtitle":"Giàu kinh nghiệm, vững chuyên môn và luôn tiên phong đổi mới,\\nđồng hành cùng sinh viên làm chủ công nghệ tương lai."}},{"id":"teacher_directory","type":"sections/teacher_directory","locked":false,"data":{"teachers":[{"name":"TS. Nguyễn Thị Lan","role":"Phó Giáo sư, Trí tuệ nhân tạo","phone":"028 3821 2360","email":"lan.nguyen@faculty.edu.vn","portrait":{"src":"public/media/2026/07/0eea3ed1-c7f1-42c3-96ed-f5bd4dee97ab_original.webp"}},{"name":"ThS. Trần Minh Quân","role":"Giảng viên, Kỹ thuật phần mềm","phone":"028 3821 2361","email":"quan.tran@faculty.edu.vn","portrait":{"src":"public/media/2026/07/0eea3ed1-c7f1-42c3-96ed-f5bd4dee97ab_original.webp"}},{"name":"TS. Lê Hoàng Anh","role":"Trưởng bộ môn, Khoa học dữ liệu","phone":"028 3821 2362","email":"anh.le@faculty.edu.vn","portrait":{"src":"public/media/2026/07/0eea3ed1-c7f1-42c3-96ed-f5bd4dee97ab_original.webp"}},{"name":"ThS. Phạm Thu Hà","role":"Giảng viên, Hệ thống thông tin","phone":"028 3821 2363","email":"ha.pham@faculty.edu.vn","portrait":{"src":"public/media/2026/07/0eea3ed1-c7f1-42c3-96ed-f5bd4dee97ab_original.webp"}},{"name":"TS. Võ Quốc Bảo","role":"Giảng viên chính, An toàn thông tin","phone":"028 3821 2364","email":"bao.vo@faculty.edu.vn","portrait":{"src":"public/media/2026/07/0eea3ed1-c7f1-42c3-96ed-f5bd4dee97ab_original.webp"}},{"name":"ThS. Đặng Ngọc Mai","role":"Giảng viên, Mạng máy tính","phone":"028 3821 2365","email":"mai.dang@faculty.edu.vn","portrait":{"src":"public/media/2026/07/0eea3ed1-c7f1-42c3-96ed-f5bd4dee97ab_original.webp"}}]}}]}',
'{}', NOW(), NOW(), NOW());

-- ============================================================================
-- 15. BẢNG POSTS
-- ============================================================================
-- POSTS (compact format - 1 line per post)
-- Dữ liệu mẫu bài viết từ ID 1 đến 4 (Theo cấu trúc Block Schema lồng JSON trong trường data mới)
INSERT INTO `posts` (`id`, `title`, `slug`, `content_json`, `settings_json`, `author_id`, `status`, `view_count`, `seo_description`, `seo_image_url`, `created_at`, `updated_at`, `published_at`) VALUES

(1, 'Thông báo đăng ký đợt thực tập tốt nghiệp Học kỳ 2', 'thong-bao-dang-ky-dot-thuc-tap-tot-nghiep-hoc-ky-2', 
'[{"id":"blk_1_1","type":"blocks/heading","version":1,"data":{"rich_text":[{"type":"text","text":"Thông báo về việc đăng ký thực tập tốt nghiệp Học kỳ 2","marks":[]}],"meta":{"level":2,"align":"left","anchor_id":""}}},{"id":"blk_1_2","type":"blocks/paragraph","version":1,"data":{"rich_text":[{"type":"text","text":"Khoa Công nghệ thông tin yêu cầu toàn bộ sinh viên đủ điều kiện thực hiện các công việc sau để chuẩn bị cho đợt thực tập:","marks":[]}],"meta":{"align":"left","anchor_id":""}}},{"id":"blk_1_3","type":"blocks/list","version":1,"data":{"rich_text":[],"meta":{"style":"bullet","items":[{"rich_text":[{"type":"text","text":"Sinh viên nộp phiếu đăng ký đúng hạn theo kế hoạch phân phối","marks":[]}],"children":[]},{"rich_text":[{"type":"text","text":"Cập nhật đầy đủ CV cá nhân trên hệ thống quản lý thực tập","marks":[]}],"children":[]}],"anchor_id":""}}},{"id":"blk_1_4","type":"blocks/quote","version":1,"data":{"rich_text":[{"type":"text","text":"\\"Mọi sự chậm trễ sẽ không được giải quyết sau khi cổng đăng ký đóng lại.\\"","marks":[]}],"meta":{"citation":"Văn phòng Khoa CNTT"}}}]',
'{"version":1,"title":"Thông báo đăng ký đợt thực tập tốt nghiệp Học kỳ 2","slug":"thong-bao-dang-ky-dot-thuc-tap-tot-nghiep-hoc-ky-2","excerpt":"Khoa Công nghệ thông tin yêu cầu toàn bộ sinh viên đủ điều kiện nộp phiếu đăng ký đúng hạn để chuẩn bị cho đợt thực tập tốt nghiệp Học kỳ 2.","author_id":"1","status":"published","category_ids":[1],"featured_image":"2026/07/0eea3ed1-c7f1-42c3-96ed-f5bd4dee97ab_original.webp","settings":{"show_author":false,"show_date":true,"show_view_count":false},"init_view_count":150}',
1, 'published', 150, 'Thông báo chính thức về việc đăng ký đợt thực tập tốt nghiệp dành cho sinh viên Học kỳ 2, bao gồm thời gian và biểu mẫu liên quan.', '2026/07/0eea3ed1-c7f1-42c3-96ed-f5bd4dee97ab_original.webp', NOW(), NOW(), NOW()),

-- Bài viết 2
(2, 'Kết quả cuộc thi Olympic Tin học cấp Trường năm nay', 'ket-qua-cuoc-thi-olympic-tin-hoc-cap-truong', 
'[{"id":"blk_2_1","type":"blocks/heading","version":1,"data":{"rich_text":[{"type":"text","text":"Bảng vàng thành tích Olympic Tin học cấp Trường","marks":[]}],"meta":{"level":2,"align":"center","anchor_id":""}}},{"id":"blk_2_2","type":"blocks/paragraph","version":1,"data":{"rich_text":[{"type":"text","text":"Trải qua các vòng thi gay cấn, Ban tổ chức xin chính thức công bố kết quả chung cuộc của các thí sinh xuất sắc nhất năm nay:","marks":[]}],"meta":{"align":"left","anchor_id":""}}},{"id":"blk_2_3","type":"blocks/image","version":1,"data":{"rich_text":[],"meta":{"mediaId":2,"url":"/public/media/2026/07/8674792e-d9b8-4c4c-b419-4276dc08d1eb_original.webp","alt":"Trao giải Olympic Tin học","caption":[],"align":"center","width":"100%","anchor_id":""}}},{"id":"blk_2_4","type":"blocks/table","version":1,"data":{"rich_text":[],"meta":{"hasHeader":true,"rows":[["Hạng giải","Họ và tên","Lớp sinh hoạt"],["Giải Nhất","Nguyễn Văn A","CD CCQ 23"],["Giải Nhì","Trần Thị B","CD CCQ 24"]]}}}]',
'{"version":1,"title":"Kết quả cuộc thi Olympic Tin học cấp Trường năm nay","slug":"ket-qua-cuoc-thi-olympic-tin-hoc-cap-truong","excerpt":"Ban tổ chức chính thức công bố bảng vàng thành tích danh giá của các thí sinh xuất sắc nhất tại cuộc thi Olympic Tin học cấp Trường năm nay.","author_id":"1","status":"published","category_ids":[3],"featured_image":"2026/07/8674792e-d9b8-4c4c-b419-4276dc08d1eb_original.webp","settings":{"show_author":false,"show_date":true,"show_view_count":false},"init_view_count":320}',
1, 'published', 320, 'Chúc mừng các sinh viên đã đạt thành tích xuất sắc tại cuộc thi Olympic Tin học cấp Trường năm học này với các giải thưởng danh giá.', '2026/07/8674792e-d9b8-4c4c-b419-4276dc08d1eb_original.webp', NOW(), NOW(), NOW()),

-- Bài viết 3
(3, 'Lịch thi và danh sách phòng thi tốt nghiệp lý thuyết', 'lich-thi-va-danh-sach-phong-thi-tot-nghiep-ly-thuyet', 
'[{"id":"blk_3_1","type":"blocks/heading","version":1,"data":{"rich_text":[{"type":"text","text":"Lịch thi chính thức các môn lý thuyết tốt nghiệp","marks":[]}],"meta":{"level":2,"align":"left","anchor_id":""}}},{"id":"blk_3_2","type":"blocks/paragraph","version":1,"data":{"rich_text":[{"type":"text","text":"Sinh viên lưu ý có mặt trước giờ thi 15 phút, mang theo thẻ sinh viên và giấy tờ tùy thân hợp lệ:","marks":[]}],"meta":{"align":"left","anchor_id":""}}},{"id":"blk_3_3","type":"blocks/table","version":1,"data":{"rich_text":[],"meta":{"hasHeader":true,"rows":[["Ngày thi cụ thể","Môn thi tốt nghiệp","Phòng thi phân công"],["15/06/2026","Cơ sở dữ liệu","A.0501"],["16/06/2026","Lập trình Web","A.0502"]]}}}]', 
'{"version":1,"title":"Lịch thi và danh sách phòng thi tốt nghiệp lý thuyết","slug":"lich-thi-va-danh-sach-phong-thi-tot-nghiep-ly-thuyet","excerpt":"Cập nhật lịch thi chi tiết và phân công sơ đồ phòng thi các môn lý thuyết tốt nghiệp chuyên ngành dành cho sinh viên các lớp cuối khóa.","author_id":"2","status":"published","category_ids":[1],"featured_image":"2026/07/53701c8e-b31f-4ff5-8909-a9a1ff5473f3_medium.webp","settings":{"show_author":false,"show_date":true,"show_view_count":false},"init_view_count":415}',
2, 'published', 415, 'Cập nhật lịch thi chính thức và sơ đồ phòng thi tốt nghiệp phần lý thuyết chuyên ngành dành cho các lớp cuối khóa.', '2026/07/53701c8e-b31f-4ff5-8909-a9a1ff5473f3_medium.webp', NOW(), NOW(), NOW()),

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
(1, 'hoi-giang-2023', 'hoi-giang-2023.jpg', '2026/07/0eea3ed1-c7f1-42c3-96ed-f5bd4dee97ab_original.webp', 'image/webp', 'hoi-giang-2023', 500, 326, 18322, '{\"aspect_ratio\":1.53,\"processed_mode\":\"standard\"}', '2026-07-13 06:36:05', '2026-07-13 06:36:05'),
(2, 'olympic-tin-hoc', 'olympic-tin-hoc.jpg', '2026/07/8674792e-d9b8-4c4c-b419-4276dc08d1eb_original.webp', 'image/webp', 'olympic-tin-hoc', 500, 326, 37644, '{\"aspect_ratio\":1.53,\"processed_mode\":\"standard\"}', '2026-07-13 06:36:31', '2026-07-13 06:36:31'),
(3, 'giao-luu-ai', 'giao-luu-ai.jpg', '2026/07/53701c8e-b31f-4ff5-8909-a9a1ff5473f3_medium.webp', 'image/webp', 'giao-luu-ai', 800, 600, 55488, '{\"aspect_ratio\":1.33,\"processed_mode\":\"standard\"}', '2026-07-13 07:14:15', '2026-07-13 07:14:15'),
(4, 'bong-da', 'bong-da.jpg', '2026/07/c72d452b-d540-417a-b199-4317111a92e0_original.webp', 'image/webp', 'bong-da', 500, 326, 49440, '{\"aspect_ratio\":1.53,\"processed_mode\":\"standard\"}', '2026-07-13 07:14:31', '2026-07-13 07:14:31'),
(5, 'quan-su', 'quan-su.jpg', '2026/07/c6dde858-85a2-4243-b5ea-059ea53cd353_original.webp', 'image/webp', 'quan-su', 500, 326, 42482, '{\"aspect_ratio\":1.53,\"processed_mode\":\"standard\"}', '2026-07-13 07:14:45', '2026-07-13 07:14:45'),
(6, 'le-trao-bang', 'le-trao-bang.jpg', '2026/07/a916f16a-62b6-49a7-ae05-32e13e7796d6_original.webp', 'image/webp', 'le-trao-bang', 740, 340, 33426, '{\"aspect_ratio\":2.18,\"processed_mode\":\"standard\"}', '2026-07-13 07:14:58', '2026-07-13 07:14:58');

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


UPDATE `cms_pages` AS education
JOIN `cms_pages` AS admissions ON admissions.slug = 'admissions'
JOIN `cms_pages` AS programs ON programs.slug = 'academic-programs'
JOIN `cms_pages` AS outcomes ON outcomes.slug = 'program-outcomes'
JOIN `cms_pages` AS curriculum ON curriculum.slug = 'curriculum'
SET education.content_json = JSON_OBJECT(
  'version', 1,
  'sections', JSON_ARRAY(
    JSON_SET(JSON_EXTRACT(education.content_json, '$.sections[0]'), '$.locked', TRUE),
    JSON_SET(JSON_EXTRACT(admissions.content_json, '$.sections[0]'), '$.locked', TRUE),
    JSON_SET(JSON_EXTRACT(programs.content_json, '$.sections[0]'), '$.locked', TRUE),
    JSON_SET(JSON_EXTRACT(outcomes.content_json, '$.sections[0]'), '$.locked', TRUE),
    JSON_SET(JSON_EXTRACT(curriculum.content_json, '$.sections[0]'), '$.locked', TRUE)
  )
)
WHERE education.slug = 'education';

DELETE FROM `cms_pages` WHERE slug IN ('admissions', 'academic-programs', 'program-outcomes', 'curriculum');
UPDATE `cms_pages`
SET content_json = JSON_ARRAY_INSERT(content_json, '$.sections[2]', JSON_OBJECT(
  'id', 'vision_mission',
  'type', 'sections/vision_mission',
  'locked', FALSE,
  'data', (SELECT JSON_EXTRACT(source_page.content_json, '$.sections[0].data')
           FROM (SELECT content_json FROM cms_pages WHERE slug = 'vision-mission' LIMIT 1) AS source_page)
))
WHERE slug = 'about';
DELETE FROM `cms_pages` WHERE slug = 'vision-mission';
UPDATE `menu_items` SET url = '/dao-tao#tuyen-sinh' WHERE url = '/dao-tao/tuyen-sinh';
UPDATE `menu_items` SET url = '/dao-tao#chuong-trinh-dao-tao' WHERE url = '/dao-tao/chuong-trinh-dao-tao';
UPDATE `menu_items` SET url = '/dao-tao#chuan-dau-ra' WHERE url = '/dao-tao/chuan-dau-ra';
UPDATE `menu_items` SET url = '/dao-tao#danh-sach-mon-hoc' WHERE url = '/dao-tao/danh-sach-mon-hoc';

-- Replace About-page demo claims with dated facts from the Faculty yearbook.
UPDATE `cms_pages`
SET content_json = JSON_SET(
  content_json,
  '$.sections[3].data.sections[0].title', 'Thành lập và phát triển từ năm 1998',
  '$.sections[3].data.sections[0].timeline', JSON_ARRAY(
    JSON_OBJECT('year', '1998', 'description', 'Khoa Điện tử - Tin học được thành lập vào tháng 8, ban đầu đào tạo hai ngành Điện tử và Tin học.'),
    JSON_OBJECT('year', '2014', 'description', 'Olympic Tin học Cao Thắng được tổ chức lần đầu, mở đầu sân chơi học thuật thường niên cho sinh viên CNTT.'),
    JSON_OBJECT('year', '2018', 'description', 'Đội tuyển Tin học Cao Thắng giành giải nhất đồng đội khối Cao đẳng tại Olympic Tin học Sinh viên Việt Nam, cùng nhiều giải cá nhân.'),
    JSON_OBJECT('year', '2020', 'description', 'Khoa Điện tử - Tin học được đổi tên thành Khoa Công nghệ thông tin.')
  ),
  '$.sections[3].data.sections[1].title', 'Hơn một thế kỷ đào tạo kỹ thuật',
  '$.sections[3].data.sections[1].timeline', JSON_ARRAY(
    JSON_OBJECT('year', '1906', 'description', 'Trường Cơ khí Á Châu, tiền thân của Trường Cao đẳng Kỹ thuật Cao Thắng, được thành lập.'),
    JSON_OBJECT('year', '1915', 'description', 'Chủ tịch Tôn Đức Thắng theo học tại trường.'),
    JSON_OBJECT('year', '2004', 'description', 'Trường chính thức mang tên Trường Cao đẳng Kỹ thuật Cao Thắng.')
  ),
  '$.sections[4].data.items', JSON_ARRAY(
    JSON_OBJECT('badge', '<i class="fa-solid fa-handshake"></i> <span>Đào tạo gắn doanh nghiệp</span>', 'image', JSON_OBJECT('src', 'public/media/2026/07/0eea3ed1-c7f1-42c3-96ed-f5bd4dee97ab_original.webp', 'alt', 'Đào tạo Công nghệ thông tin gắn kết doanh nghiệp'), 'content', 'Thực tiễn', 'subContent', 'Kết nối doanh nghiệp', 'footer', 'Bộ môn Tin học gắn đào tạo với nhu cầu thực tế và tăng cường kết nối doanh nghiệp.'),
    JSON_OBJECT('badge', '<i class="fa-solid fa-computer"></i>', 'image', JSON_OBJECT('src', '', 'alt', ''), 'content', '10+', 'subContent', 'Phòng máy & thực hành', 'footer', 'Hệ thống phòng máy và phòng thực hành phục vụ đào tạo Công nghệ thông tin.'),
    JSON_OBJECT('badge', '<i class="fa-solid fa-graduation-cap"></i>', 'image', JSON_OBJECT('src', '', 'alt', ''), 'content', '4', 'subContent', 'Chương trình đào tạo CNTT', 'footer', 'Bộ môn Tin học tổ chức bốn chương trình đào tạo thuộc lĩnh vực Công nghệ thông tin.'),
    JSON_OBJECT('badge', '<i class="fa-solid fa-users"></i>', 'image', JSON_OBJECT('src', '', 'alt', ''), 'content', '1.700+', 'subContent', 'Người học liên quan CNTT', 'footer', 'Hơn 1.700 học sinh, sinh viên theo học các chương trình liên quan đến Công nghệ thông tin.'),
    JSON_OBJECT('badge', '<i class="fa-solid fa-trophy"></i> <span>Olympic Tin học</span>', 'image', JSON_OBJECT('src', 'public/media/2026/07/0eea3ed1-c7f1-42c3-96ed-f5bd4dee97ab_original.webp', 'alt', 'Thành tích Olympic Tin học của đội tuyển Cao Thắng'), 'content', 'Hạng nhất', 'subContent', 'Đồng đội khối Cao đẳng', 'footer', 'Đội tuyển Tin học Cao Thắng giành giải nhất đồng đội khối Cao đẳng cùng nhiều giải cá nhân.')
  )
)
WHERE slug = 'about';

-- Replace Landing-page placeholders with verified Khoa CNTT information.
UPDATE `cms_pages`
SET content_json = JSON_SET(
  content_json,
  '$.sections[1].data.items', JSON_ARRAY(
    JSON_OBJECT('number', '01', 'image', JSON_OBJECT('src', 'public/media/2026/07/0eea3ed1-c7f1-42c3-96ed-f5bd4dee97ab_original.webp', 'alt', 'Hoạt động của Khoa Công nghệ thông tin Cao Thắng'), 'card', JSON_OBJECT('value', '1998', 'label', 'Khởi đầu Bộ môn Tin học'), 'eyebrow', 'Hình thành & phát triển', 'title', 'Hơn hai thập kỷ đào tạo công nghệ thông tin', 'description', 'Từ Bộ môn Tin học thuộc Khoa Điện tử - Tin học, Khoa Công nghệ thông tin được tổ chức lại thành khoa chuyên ngành từ năm 2020, tập trung sâu vào đào tạo và ứng dụng CNTT.'),
    JSON_OBJECT('number', '02', 'image', JSON_OBJECT('src', 'public/media/2026/07/0eea3ed1-c7f1-42c3-96ed-f5bd4dee97ab_original.webp', 'alt', 'Sinh viên thực hành Công nghệ thông tin'), 'card', JSON_OBJECT('value', '12', 'label', 'Phòng thực hành CNTT'), 'eyebrow', 'Học đi đôi với hành', 'title', 'Không gian thực hành phục vụ kỹ năng nghề nghiệp', 'description', 'Hệ thống phòng thực hành hỗ trợ sinh viên rèn luyện lập trình, phần cứng, mạng máy tính và triển khai các sản phẩm công nghệ.'),
    JSON_OBJECT('number', '03', 'image', JSON_OBJECT('src', 'public/media/2026/07/0eea3ed1-c7f1-42c3-96ed-f5bd4dee97ab_original.webp', 'alt', 'Đội tuyển Olympic Tin học Cao Thắng'), 'card', JSON_OBJECT('value', 'Hạng nhất', 'label', 'Đồng đội khối Cao đẳng'), 'eyebrow', 'Bản lĩnh sinh viên', 'title', 'Khẳng định năng lực tại Olympic Tin học', 'description', 'Đội tuyển Tin học Cao Thắng từng giành giải nhất đồng đội khối Cao đẳng cùng nhiều giải cá nhân tại Olympic Tin học Sinh viên Việt Nam.')
  ),
  '$.sections[2].data', JSON_OBJECT(
    'badge', 'Tại sao chọn Khoa CNTT Cao Thắng',
    'title', 'Nền tảng nghề nghiệp được xây dựng từ thực hành',
    'subtitle', 'Chương trình chuyên môn, cơ sở thực hành và hoạt động kết nối doanh nghiệp cùng hướng đến khả năng làm việc thực tế của sinh viên.',
    'feature', JSON_OBJECT('image', 'public/media/2026/07/0eea3ed1-c7f1-42c3-96ed-f5bd4dee97ab_original.webp', 'alt', 'Sinh viên Khoa CNTT Cao Thắng học tập và thực hành', 'badge', 'Đào tạo gắn thực tiễn', 'title', 'Học qua bài tập, dự án và trải nghiệm nghề nghiệp', 'description', 'Sinh viên phát triển năng lực từ kiến thức nền tảng đến thực hành chuyên môn, đồ án, thực tập và các hoạt động học thuật của Khoa.', 'cta_label', 'Tìm hiểu chương trình', 'cta_url', '/dao-tao'),
    'stats', JSON_ARRAY(
      JSON_OBJECT('number', '12', 'title', 'Phòng thực hành CNTT', 'description', 'Phục vụ học tập và rèn luyện kỹ năng chuyên môn.'),
      JSON_OBJECT('number', '30', 'title', 'Giảng viên', 'description', 'Đội ngũ phụ trách đào tạo, nghiên cứu và đồng hành cùng sinh viên.')
    ),
    'perks', JSON_ARRAY(
      JSON_OBJECT('icon', 'fa-solid fa-graduation-cap', 'title', 'Ba chương trình đào tạo', 'description', 'Công nghệ thông tin, Quản trị mạng máy tính và Sửa chữa - lắp ráp máy tính.'),
      JSON_OBJECT('icon', 'fa-solid fa-code', 'title', 'Chuyên môn rõ ràng', 'description', 'Tổ chức chuyên môn về Công nghệ phần mềm và Phần cứng - Mạng máy tính.'),
      JSON_OBJECT('icon', 'fa-solid fa-handshake', 'title', 'Kết nối doanh nghiệp', 'description', 'Gắn đào tạo với tham quan, thực tập, tuyển dụng và nhu cầu nhân lực thực tế.'),
      JSON_OBJECT('icon', 'fa-solid fa-trophy', 'title', 'Học thuật và thi đấu', 'description', 'Olympic Tin học và hoạt động câu lạc bộ giúp sinh viên phát triển tư duy giải quyết vấn đề.')
    ),
    'highlights', JSON_ARRAY(
      JSON_OBJECT('image', 'public/media/2026/07/0eea3ed1-c7f1-42c3-96ed-f5bd4dee97ab_original.webp', 'alt', 'Sinh viên thực hiện đồ án Công nghệ thông tin', 'title', 'Đồ án và thực tập', 'description', 'Vận dụng kiến thức vào sản phẩm, nhiệm vụ và môi trường làm việc thực tế.'),
      JSON_OBJECT('image', 'public/media/2026/07/0eea3ed1-c7f1-42c3-96ed-f5bd4dee97ab_original.webp', 'alt', 'Hoạt động kết nối doanh nghiệp của Khoa CNTT', 'title', 'Đồng hành cùng doanh nghiệp', 'description', 'Mở rộng trải nghiệm nghề nghiệp thông qua hợp tác đào tạo và tuyển dụng.')
    )
  ),
  '$.sections[3].data', JSON_OBJECT(
    'title', 'Những con số về Khoa CNTT Cao Thắng',
    'subtitle', 'Nền tảng đào tạo được xây dựng từ chuyên môn, thực hành và hoạt động học thuật.',
    'stats', JSON_ARRAY(
      JSON_OBJECT('icon', 'fa-solid fa-graduation-cap', 'number', '3', 'label', 'Chương trình đào tạo', 'description', 'Ba lộ trình nghề nghiệp thuộc lĩnh vực CNTT.'),
      JSON_OBJECT('icon', 'fa-solid fa-code-branch', 'number', '2', 'label', 'Bộ môn chuyên môn', 'description', 'Công nghệ phần mềm và Phần cứng - Mạng máy tính.'),
      JSON_OBJECT('icon', 'fa-solid fa-trophy', 'number', '9', 'label', 'Kỳ Olympic Tin học', 'description', 'Olympic Tin học Cao Thắng đã bước sang lần tổ chức thứ 9.'),
      JSON_OBJECT('icon', 'fa-solid fa-building-columns', 'number', '2020', 'label', 'Khoa CNTT', 'description', 'Được tổ chức lại thành khoa chuyên ngành Công nghệ thông tin.')
    ),
    'benefits', JSON_ARRAY(
      JSON_OBJECT('icon', 'fa-solid fa-laptop-code', 'title', 'Đào tạo hướng đến năng lực thực hành', 'items', JSON_ARRAY('Rèn kỹ năng qua bài tập và giờ thực hành chuyên môn', 'Phát triển sản phẩm qua đồ án môn học và đồ án tốt nghiệp', 'Thực tập tốt nghiệp gắn với môi trường nghề nghiệp', 'Bổ sung kỹ năng giao tiếp, làm việc nhóm và trình bày kỹ thuật')),
      JSON_OBJECT('icon', 'fa-solid fa-arrow-trend-up', 'title', 'Phát triển nghề nghiệp cùng doanh nghiệp', 'items', JSON_ARRAY('Tham quan và tìm hiểu môi trường làm việc', 'Tiếp cận cơ hội thực tập và tuyển dụng', 'Kết nối nhu cầu doanh nghiệp với hoạt động đào tạo', 'Rèn tư duy và bản lĩnh qua sân chơi học thuật'))
    ),
    'cta', JSON_OBJECT('title', 'Sẵn sàng bắt đầu hành trình công nghệ?', 'description', 'Khám phá chương trình đào tạo và chọn lộ trình phù hợp với năng lực, sở thích và định hướng nghề nghiệp của bạn.', 'buttons', JSON_ARRAY(JSON_OBJECT('label', 'Xem chương trình đào tạo', 'url', '/dao-tao', 'variant', 'outline-alt'), JSON_OBJECT('label', 'Khám phá cơ hội nghề nghiệp', 'url', '/danh-muc/tuyen-dung', 'variant', 'outline')))
  )
)
WHERE slug = 'landing';

UPDATE `cms_pages`
SET content_json = JSON_ARRAY_INSERT(
  content_json,
  '$.sections[4]',
  JSON_OBJECT(
    'id', 'partnerships',
    'type', 'sections/partnerships',
    'locked', FALSE,
    'data', JSON_OBJECT(
      'variant', 'default',
      'title', 'Đối tác Doanh nghiệp',
      'subtitle', 'Sinh viên được kết nối trực tiếp với các doanh nghiệp hàng đầu trong lĩnh vực công nghệ.',
      'partners', JSON_ARRAY(
        JSON_OBJECT('name', 'NVIDIA', 'url', 'https://www.nvidia.com/en-in/', 'image', JSON_OBJECT('src', 'https://cntt.caothang.edu.vn/uploads/doanh-nghiep/f241854894669bf5ca4b65ce5614b3d2.png', 'alt', 'NVIDIA')),
        JSON_OBJECT('name', 'Lexar Việt Nam', 'url', 'https://www.facebook.com/Lexarviet', 'image', JSON_OBJECT('src', 'https://cntt.caothang.edu.vn/uploads/doanh-nghiep/41f87152f57bd05bf848bcf01113f3b3.png', 'alt', 'Lexar Việt Nam')),
        JSON_OBJECT('name', 'Tin học ngôi sao', 'url', 'https://tinhocngoisao.com/', 'image', JSON_OBJECT('src', 'https://cntt.caothang.edu.vn/uploads/doanh-nghiep/0c09c04e408300357f835dc97eb1a6e6.png', 'alt', 'Tin học ngôi sao')),
        JSON_OBJECT('name', 'An Phát Co.,Ltd', 'url', 'https://vitinhanphat.com.vn/', 'image', JSON_OBJECT('src', 'https://cntt.caothang.edu.vn/uploads/doanh-nghiep/1ae64b95ef7ddafef717166e9edabfd5.png', 'alt', 'An Phát Co.,Ltd')),
        JSON_OBJECT('name', 'SMNET', 'url', 'https://smnet.vn/', 'image', JSON_OBJECT('src', 'https://cntt.caothang.edu.vn/uploads/doanh-nghiep/20131828c2f34e5eec6dc06826cb936f.png', 'alt', 'SMNET')),
        JSON_OBJECT('name', 'Tin học đại dương', 'url', 'https://tinhocdaiduong.vn/', 'image', JSON_OBJECT('src', 'https://cntt.caothang.edu.vn/uploads/doanh-nghiep/cd22e173fe39bc2f2f1c6f756e46d78e.png', 'alt', 'Tin học đại dương')),
        JSON_OBJECT('name', 'Nguyễn Thuận', 'url', 'https://thuancomputer.com/', 'image', JSON_OBJECT('src', 'https://cntt.caothang.edu.vn/uploads/doanh-nghiep/6858fbd5fab3b644b2b486587b94cda5.png', 'alt', 'Nguyễn Thuận')),
        JSON_OBJECT('name', 'ENGENIUS', 'url', 'https://engenius.vn/', 'image', JSON_OBJECT('src', 'https://cntt.caothang.edu.vn/uploads/doanh-nghiep/4846e8f8f53fd5bb49b7ecda45792660.png', 'alt', 'ENGENIUS')),
        JSON_OBJECT('name', 'PAT GROUP / Siêu Thị Công Nghệ', 'url', 'https://www.sieuthicongnghe.com.vn/', 'image', JSON_OBJECT('src', 'https://cntt.caothang.edu.vn/uploads/doanh-nghiep/1cc65af6ae144feaa9d8ed7f66d7aee4.png', 'alt', 'PAT GROUP / Siêu Thị Công Nghệ')),
        JSON_OBJECT('name', 'Anta6', 'url', 'https://anta6.com/', 'image', JSON_OBJECT('src', 'https://cntt.caothang.edu.vn/uploads/doanh-nghiep/bb134adbde2b708a630f3539ac02aa02.png', 'alt', 'Anta6')),
        JSON_OBJECT('name', 'Waverley Software', 'url', 'https://waverleysoftware.com', 'image', JSON_OBJECT('src', 'https://cntt.caothang.edu.vn/uploads/doanh-nghiep/7717dab3bbd70e2f3d31aa0cd6202ff5.png', 'alt', 'Waverley Software')),
        JSON_OBJECT('name', 'Ryomo Vietnam Solutions Co., Ltd.', 'url', 'http://rvsc.ryomo-gr.com/vn/', 'image', JSON_OBJECT('src', 'https://cntt.caothang.edu.vn/uploads/doanh-nghiep/c31d40d21703b76e070989017e080e3d.png', 'alt', 'Ryomo Vietnam Solutions Co., Ltd.'))
      )
    )
  )
)
WHERE slug = 'landing';

UPDATE `carousel_slides`
SET title = CASE sort_order
    WHEN 1 THEN 'Khoa Công nghệ thông tin'
    WHEN 2 THEN 'Tuyển sinh'
    WHEN 3 THEN 'Olympic Tin học Cao Thắng'
  END,
  title_highlight = CASE sort_order
    WHEN 1 THEN 'Học từ thực hành, trưởng thành cùng công nghệ'
    WHEN 2 THEN 'Chọn lộ trình công nghệ phù hợp với bạn'
    WHEN 3 THEN 'Sân chơi học thuật và bản lĩnh sinh viên'
  END,
  description = CASE sort_order
    WHEN 1 THEN 'Đào tạo nguồn nhân lực công nghệ thông tin có kiến thức vững, kỹ năng nghề nghiệp và khả năng thích ứng với nhu cầu doanh nghiệp.'
    WHEN 2 THEN 'Khám phá ba chương trình: Công nghệ thông tin, Quản trị mạng máy tính và Sửa chữa - lắp ráp máy tính.'
    WHEN 3 THEN 'Nơi sinh viên vận dụng kiến thức, phát triển tư duy giải quyết vấn đề và làm việc với những sản phẩm thực tế.'
  END,
  cta_label = CASE sort_order WHEN 1 THEN 'Khám phá Khoa CNTT' WHEN 2 THEN 'Xem chương trình đào tạo' WHEN 3 THEN 'Xem tin nổi bật' END,
  cta_url = CASE sort_order WHEN 1 THEN '/gioi-thieu' WHEN 2 THEN '/dao-tao' WHEN 3 THEN '/tin-tuc' END,
  is_active = 1
WHERE carousel_id = 1 AND sort_order BETWEEN 1 AND 3;
UPDATE `carousel_slides` SET is_active = 0 WHERE carousel_id = 1 AND sort_order > 3;

SET FOREIGN_KEY_CHECKS = 1;
