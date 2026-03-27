SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- BƯỚC 1: XÓA SẠCH DỮ LIỆU CŨ VÀ RESET AUTO_INCREMENT (TRUNCATE)
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


-- ============================================================================
-- BƯỚC 2: BƠM DỮ LIỆU (SEEDING) - ÍT NHẤT 20 DÒNG MỖI BẢNG
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 2.1 BẢNG MAJORS (NGÀNH HỌC - 20 Ngành)
-- ----------------------------------------------------------------------------
INSERT INTO `majors` (`id`, `full_name`, `short_name`, `level`) VALUES
(1, 'Công nghệ thông tin', 'CNTT', 'CĐ'),
(2, 'Công nghệ Kỹ thuật Ô tô', 'OTO', 'CĐ'),
(3, 'Công nghệ Kỹ thuật Điện, Điện tử', 'ĐĐT', 'CĐ'),
(4, 'Công nghệ Kỹ thuật Cơ khí', 'CK', 'CĐ'),
(5, 'Kế toán doanh nghiệp', 'KT', 'CĐ'),
(6, 'Quản trị kinh doanh', 'QTKD', 'CĐ'),
(7, 'Công nghệ kỹ thuật điều khiển và tự động hóa', 'TĐH', 'CĐ'),
(8, 'Công nghệ kỹ thuật nhiệt', 'Nhiệt', 'CĐ'),
(9, 'Cơ điện tử', 'CĐT', 'CĐ'),
(10, 'Thiết kế đồ họa', 'ĐH', 'CĐ'),
(11, 'Thương mại điện tử', 'TMĐT', 'CĐ'),
(12, 'Logistics và Quản lý chuỗi cung ứng', 'LOG', 'CĐ'),
(13, 'Tiếng Anh thương mại', 'NNA', 'CĐ'),
(14, 'Quản trị dịch vụ du lịch và lữ hành', 'DL', 'CĐ'),
(15, 'Quản trị khách sạn', 'KS', 'CĐ'),
(16, 'Công nghệ thực phẩm', 'TP', 'CĐ'),
(17, 'Công nghệ kỹ thuật môi trường', 'MT', 'CĐ'),
(18, 'Kỹ thuật xây dựng', 'XD', 'CĐ'),
(19, 'Bảo trì công nghiệp', 'BTCN', 'CĐ'),
(20, 'Kỹ thuật máy lạnh và điều hòa không khí', 'MLĐH', 'CĐN');


-- ----------------------------------------------------------------------------
-- 2.2 BẢNG SPECIALIZATIONS (CHUYÊN NGÀNH - 20 Chuyên ngành)
-- ----------------------------------------------------------------------------
INSERT INTO `specializations` (`id`, `major_id`, `full_name`, `short_name`) VALUES
(1, 1, 'Lập trình Phần mềm', 'PM'),
(2, 1, 'Mạng máy tính & An ninh mạng', 'ANM'),
(3, 1, 'Trí tuệ nhân tạo (AI)', 'AI'),
(4, 2, 'Công nghệ Ô tô đời mới', 'OTDM'),
(5, 2, 'Chẩn đoán điện ô tô', 'CDD'),
(6, 3, 'Điện công nghiệp', 'DCN'),
(7, 3, 'Điện tử viễn thông', 'DTVT'),
(8, 4, 'Cơ khí chế tạo máy', 'CTM'),
(9, 4, 'Cơ khí chính xác CNC', 'CNC'),
(10, 5, 'Kế toán thuế', 'KTT'),
(11, 5, 'Kế toán kiểm toán', 'KTKT'),
(12, 6, 'Marketing số (Digital Marketing)', 'MKT'),
(13, 6, 'Quản trị bán hàng', 'QTBH'),
(14, 7, 'Robot và tự động hóa', 'RBT'),
(15, 10, 'Thiết kế UI/UX', 'UIUX'),
(16, 11, 'Kinh doanh trực tuyến', 'KDTT'),
(17, 12, 'Quản lý kho bãi', 'QLK'),
(18, 14, 'Hướng dẫn viên du lịch', 'HDV'),
(19, 18, 'Thiết kế kỹ thuật xây dựng', 'TKXD'),
(20, 20, 'Sửa chữa điện lạnh', 'SCDL');


-- ----------------------------------------------------------------------------
-- 2.3 BẢNG ACCOUNTS (TÀI KHOẢN - 41 Tài khoản: 1 Admin, 20 GV, 20 SV)
-- Mật khẩu chung đã được hash là: password
-- ----------------------------------------------------------------------------
INSERT INTO `accounts` (`id`, `email`, `password_hash`, `role`) VALUES
(1, 'admin@caothang.edu.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
-- 20 Tài khoản Giảng viên (ID: 2 - 21)
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
-- 20 Tài khoản Sinh viên (ID: 22 - 41)
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
-- 2.4 BẢNG TEACHERS (GIẢNG VIÊN - 20 Giảng viên)
-- ----------------------------------------------------------------------------
INSERT INTO `teachers` (`id`, `account_id`, `staff_code`, `full_name`, `gender`, `phone`, `national_id`, `degree`, `position`, `department`) VALUES
(1, 2, 'GV001', 'Nguyễn Văn Dũng', 'male', '0901000001', '079082000001', 'Tiến sĩ', 'Trưởng khoa', 'Khoa Công nghệ thông tin'),
(2, 3, 'GV002', 'Lê Thị Mai', 'female', '0901000002', '079082000002', 'Thạc sĩ', 'Trưởng bộ môn', 'Bộ môn Kế toán'),
(3, 4, 'GV003', 'Phạm Văn Hùng', 'male', '0901000003', '079082000003', 'Thạc sĩ', 'Giảng viên', 'Khoa Cơ khí'),
(4, 5, 'GV004', 'Trương Thị Lan', 'female', '0901000004', '079082000004', 'Thạc sĩ', 'Giảng viên', 'Khoa Kinh tế'),
(5, 6, 'GV005', 'Hoàng Văn Minh', 'male', '0901000005', '079082000005', 'Tiến sĩ', 'Phó trưởng khoa', 'Khoa Ô tô'),
(6, 7, 'GV006', 'Hoàng Anh Tuấn', 'male', '0901000006', '079082000006', 'Thạc sĩ', 'Giảng viên', 'Khoa CNTT'),
(7, 8, 'GV007', 'Lê Minh Hương', 'female', '0901000007', '079082000007', 'Cử nhân', 'Trợ giảng', 'Khoa Điện'),
(8, 9, 'GV008', 'Nguyễn Quốc Bình', 'male', '0901000008', '079082000008', 'Thạc sĩ', 'Giảng viên', 'Khoa Ngoại ngữ'),
(9, 10, 'GV009', 'Vũ Thị Thủy', 'female', '0901000009', '079082000009', 'Thạc sĩ', 'Giảng viên', 'Khoa Du lịch'),
(10, 11, 'GV010', 'Đinh Minh Khải', 'male', '0901000010', '079082000010', 'Tiến sĩ', 'Trưởng bộ môn', 'Khoa Cơ bản'),
(11, 12, 'GV011', 'Phạm Thị Linh', 'female', '0901000011', '079082000011', 'Thạc sĩ', 'Giảng viên', 'Khoa Đồ họa'),
(12, 13, 'GV012', 'Nguyễn Thanh Sơn', 'male', '0901000012', '079082000012', 'Thạc sĩ', 'Giảng viên', 'Khoa Nhiệt lạnh'),
(13, 14, 'GV013', 'Đặng Thúy Quỳnh', 'female', '0901000013', '079082000013', 'Thạc sĩ', 'Giảng viên', 'Khoa Kinh tế'),
(14, 15, 'GV014', 'Lý Hoàng Phong', 'male', '0901000014', '079082000014', 'Tiến sĩ', 'Trưởng bộ môn', 'Khoa Xây dựng'),
(15, 16, 'GV015', 'Bùi Thị Ngọc', 'female', '0901000015', '079082000015', 'Thạc sĩ', 'Giảng viên', 'Khoa Thực phẩm'),
(16, 17, 'GV016', 'Đỗ Quang Hải', 'male', '0901000016', '079082000016', 'Thạc sĩ', 'Giảng viên', 'Khoa Môi trường'),
(17, 18, 'GV017', 'Nguyễn Thu Trang', 'female', '0901000017', '079082000017', 'Thạc sĩ', 'Giảng viên', 'Khoa Du lịch'),
(18, 19, 'GV018', 'Phan Văn Khoa', 'male', '0901000018', '079082000018', 'Tiến sĩ', 'Phó trưởng khoa', 'Khoa Cơ khí'),
(19, 20, 'GV019', 'Nguyễn T. Thanh Anh', 'female', '0901000019', '079082000019', 'Thạc sĩ', 'Giảng viên', 'Khoa Ngoại ngữ'),
(20, 21, 'GV020', 'Lê Mạnh Cường', 'male', '0901000020', '079082000020', 'Thạc sĩ', 'Giảng viên', 'Khoa CNTT');


-- ----------------------------------------------------------------------------
-- 2.5 BẢNG CLASSROOMS (LỚP HỌC - 20 Lớp, gán GVCN luôn)
-- ----------------------------------------------------------------------------
INSERT INTO `classrooms` (`id`, `major_id`, `specialization_id`, `homeroom_teacher_id`, `class_of`, `letter`, `short_name`) VALUES 
(1, 1, 1, 1, 23, 'A', 'CĐ CNTT 23A'),
(2, 1, 2, 6, 23, 'B', 'CĐ ANM 23B'),
(3, 2, 4, 5, 24, 'A', 'CĐ OTO 24A'),
(4, 3, 6, 7, 24, 'C', 'CĐ ĐĐT 24C'),
(5, 4, 8, 3, 23, 'A', 'CĐ CK 23A'),
(6, 5, 10, 2, 25, 'A', 'CĐ KT 25A'),
(7, 6, 12, 4, 25, 'B', 'CĐ QTKD 25B'),
(8, 7, 14, 18, 23, 'C', 'CĐ TĐH 23C'),
(9, 8, NULL, 12, 24, 'A', 'CĐ NHIỆT 24A'),
(10, 10, 15, 11, 24, 'B', 'CĐ ĐH 24B'),
(11, 11, 16, 13, 25, 'A', 'CĐ TMĐT 25A'),
(12, 12, 17, 4, 26, 'A', 'CĐ LOG 26A'),
(13, 13, NULL, 8, 26, 'B', 'CĐ NNA 26B'),
(14, 14, 18, 9, 24, 'A', 'CĐ DL 24A'),
(15, 15, NULL, 17, 25, 'A', 'CĐ KS 25A'),
(16, 16, NULL, 15, 23, 'C', 'CĐ TP 23C'),
(17, 17, NULL, 16, 24, 'A', 'CĐ MT 24A'),
(18, 18, 19, 14, 25, 'B', 'CĐ XD 25B'),
(19, 19, NULL, 3, 26, 'A', 'CĐ BTCN 26A'),
(20, 20, 20, 12, 26, 'C', 'CĐN MLĐH 26C');


-- ----------------------------------------------------------------------------
-- 2.6 BẢNG STUDENTS (SINH VIÊN - 20 Sinh viên rải rác các lớp)
-- ----------------------------------------------------------------------------
INSERT INTO `students` (`id`, `account_id`, `student_id`, `full_name`, `gender`, `phone`, `classroom_id`, `national_id`, `birth_place`) VALUES
(1, 22, '0306231001', 'Trần Anh Tuấn', 'male', '0388000001', 1, '079205000001', 'TP. Hồ Chí Minh'),
(2, 23, '0306231002', 'Lê Minh Hương', 'female', '0388000002', 1, '079205000002', 'Long An'),
(3, 24, '0306231003', 'Nguyễn Hoàng Nam', 'male', '0388000003', 2, '079205000003', 'Đồng Nai'),
(4, 25, '0306231004', 'Phạm Thùy Chi', 'female', '0388000004', 3, '079205000004', 'Tiền Giang'),
(5, 26, '0306231005', 'Võ Quốc Bảo', 'male', '0388000005', 4, '079205000005', 'Bình Dương'),
(6, 27, '0306241001', 'Trịnh Bích Ngọc', 'female', '0388000006', 5, '079205000006', 'Bến Tre'),
(7, 28, '0306241002', 'Hoàng Minh Khôi', 'male', '0388000007', 6, '079205000007', 'Vĩnh Long'),
(8, 29, '0306241003', 'Đinh Thanh Trúc', 'female', '0388000008', 7, '079205000008', 'Cần Thơ'),
(9, 30, '0306241004', 'Phan Hữu Thắng', 'male', '0388000009', 8, '079205000009', 'An Giang'),
(10, 31, '0306241005', 'Ngô Lan Anh', 'female', '0388000010', 9, '079205000010', 'Bà Rịa - Vũng Tàu'),
(11, 32, '0306251001', 'Lý Quang Hải', 'male', '0388000011', 10, '079205000011', 'Tây Ninh'),
(12, 33, '0306251002', 'Đỗ Mai Phương', 'female', '0388000012', 11, '079205000012', 'Bình Phước'),
(13, 34, '0306251003', 'Đặng Tuấn Tài', 'male', '0388000013', 12, '079205000013', 'Lâm Đồng'),
(14, 35, '0306251004', 'Bùi Ngọc Yến', 'female', '0388000014', 13, '079205000014', 'Khánh Hòa'),
(15, 36, '0306251005', 'Đoàn Nhật Minh', 'male', '0388000015', 14, '079205000015', 'Phú Yên'),
(16, 37, '0306261001', 'Hồ Yến Nhi', 'female', '0388000016', 15, '079205000016', 'Bình Định'),
(17, 38, '0306261002', 'Châu Gia Bảo', 'male', '0388000017', 16, '079205000017', 'Quảng Ngãi'),
(18, 39, '0306261003', 'Mạch Thảo Ly', 'female', '0388000018', 17, '079205000018', 'Quảng Nam'),
(19, 40, '0306261004', 'Tạ Thành Đạt', 'male', '0388000019', 18, '079205000019', 'Đà Nẵng'),
(20, 41, '0306261005', 'Văn Như Quỳnh', 'female', '0388000020', 20, '079205000020', 'Thừa Thiên Huế');


-- ----------------------------------------------------------------------------
-- 2.7 BẢNG WEB_SETTINGS (CẤU HÌNH WEB - 20 Dòng)
-- ----------------------------------------------------------------------------
INSERT INTO `web_settings` (`key`, `group`, `group_label`, `type`, `value`, `label`) VALUES
('site_name', 'general', 'General', 'string', 'Trường Cao đẳng Kỹ thuật Cao Thắng', 'Tên trường/khoa'),
('site_logo', 'general', 'General', 'url', '/images/logo.png', 'Logo website'),
('site_favicon', 'general', 'General', 'url', '/images/favicon.ico', 'Favicon website'),
('contact_hotline', 'contact', 'Contact', 'string', '028 3821 2360', 'Hotline liên hệ'),
('contact_email', 'contact', 'Contact', 'email', 'phongdaotao@caothang.edu.vn', 'Email hỗ trợ'),
('address_main', 'contact', 'Contact', 'text', '65 Huỳnh Thúc Kháng, P.Bến Nghé, Q.1, TP.HCM', 'Địa chỉ trụ sở'),
('social_facebook', 'social', 'Social', 'url', 'https://facebook.com/caothang.edu.vn', 'Link Facebook'),
('social_youtube', 'social', 'Social', 'url', 'https://youtube.com/caothang', 'Link Youtube'),
('seo_meta_desc', 'seo', 'SEO', 'text', 'Trường CĐ Kỹ thuật Cao Thắng đào tạo đa ngành nghề, uy tín chất lượng.', 'SEO Description'),
('seo_meta_keywords', 'seo', 'SEO', 'text', 'cao thang, cd ky thuat, cntt, o to, co khi', 'SEO Keywords'),
('maintenance_mode', 'system', 'System', 'bool', '0', 'Bảo trì hệ thống'),
('pagination_limit', 'ui', 'UI', 'int', '15', 'Số bài viết / trang'),
('theme_color_primary', 'ui', 'UI', 'string', '#1E3A8A', 'Màu chủ đạo (Primary)'),
('theme_color_secondary', 'ui', 'UI', 'string', '#F59E0B', 'Màu phụ (Secondary)'),
('admission_status', 'system', 'System', 'bool', '1', 'Đang mở đợt tuyển sinh'),
('notification_banner', 'ui', 'UI', 'text', 'Thông báo: Lịch thi học kỳ I năm 2026 đã được cập nhật!', 'Banner thông báo nổi bật'),
('copyright_text', 'general', 'General', 'string', '© 2026 Cao Thang Technical College. All rights reserved.', 'Chữ ký bản quyền footer'),
('google_analytics_id', 'seo', 'SEO', 'string', 'G-XXXXXXXXXX', 'Mã Google Analytics'),
('allow_student_registration', 'system', 'System', 'bool', '0', 'Cho phép SV tự đăng ký tài khoản'),
('default_student_password', 'system', 'System', 'string', 'caothang@123', 'Mật khẩu mặc định khi reset');

-- ----------------------------------------------------------------------------
-- 2.8 BẢNG CATEGORIES (DANH MỤC TIN TỨC - 20 Danh mục có phân cấp)
-- ----------------------------------------------------------------------------
INSERT INTO `categories` (`id`, `name`, `slug`, `type`, `parent_id`) VALUES
(1, 'Tin tức sự kiện', 'tin-tuc-su-kien', 'const', NULL),
(2, 'Hoạt động nổi bật', 'hoat-dong-noi-bat', 'custom', 1),
(3, 'Đoàn Thanh niên', 'doan-thanh-nien', 'custom', 1),
(4, 'Đào tạo', 'dao-tao', 'const', NULL),
(5, 'Thông báo học vụ', 'thong-bao-hoc-vu', 'custom', 4),
(6, 'Lịch thi', 'lich-thi', 'custom', 4),
(7, 'Đăng ký môn học', 'dang-ky-mon-hoc', 'custom', 4),
(8, 'Sinh viên', 'sinh-vien', 'const', NULL),
(9, 'Học bổng', 'hoc-bong', 'custom', 8),
(10, 'Việc làm bán thời gian', 'viec-lam-part-time', 'custom', 8),
(11, 'Góc sinh viên', 'goc-sinh-vien', 'custom', 8),
(12, 'Tuyển sinh', 'tuyen-sinh', 'const', NULL),
(13, 'Thông báo tuyển sinh', 'thong-bao-tuyen-sinh', 'custom', 12),
(14, 'Hướng nghiệp', 'huong-nghiep', 'custom', 12),
(15, 'Doanh nghiệp', 'doanh-nghiep', 'const', NULL),
(16, 'Cơ hội việc làm', 'co-hoi-viec-lam', 'custom', 15),
(17, 'Hội thảo chuyên đề', 'hoi-thao-chuyen-de', 'custom', 15),
(18, 'Khoa học Công nghệ', 'khoa-hoc-cong-nghe', 'const', NULL),
(19, 'Nghiên cứu khoa học', 'nghien-cuu-khoa-hoc', 'custom', 18),
(20, 'Sáng tạo khởi nghiệp', 'sang-tao-khoi-nghiep', 'custom', 18);


-- ----------------------------------------------------------------------------
-- 2.9 BẢNG CAROUSELS VÀ CAROUSEL_SLIDES (20 Slides chia cho 3 Carousels)
-- ----------------------------------------------------------------------------
INSERT INTO `carousels` (`id`, `name`, `slug`) VALUES
(1, 'Slider Trang chủ', 'home-slider'),
(2, 'Slider Tuyển sinh', 'admission-slider'),
(3, 'Slider Hoạt động Đoàn', 'youth-union-slider'),
(4, 'Landing Page', 'landing-page');

INSERT INTO `carousel_slides` (`carousel_id`, `title`, `image_path`, `sort_order`) VALUES
-- Home Slider (8 slides)
(1, 'Chào mừng Tân sinh viên K26', '/uploads/slides/home1.jpg', 1),
(1, 'Chất lượng tạo nên uy tín', '/uploads/slides/home2.jpg', 2),
(1, 'Môi trường thực hành hiện đại', '/uploads/slides/home3.jpg', 3),
(1, 'Hợp tác doanh nghiệp quốc tế', '/uploads/slides/home4.jpg', 4),
(1, 'Đội ngũ giảng viên tận tâm', '/uploads/slides/home5.jpg', 5),
(1, 'Cơ sở vật chất chuẩn quốc tế', '/uploads/slides/home6.jpg', 6),
(1, 'Tự hào 120 năm lịch sử', '/uploads/slides/home7.jpg', 7),
(1, 'Khởi nghiệp từ ghế nhà trường', '/uploads/slides/home8.jpg', 8),
-- Admission Slider (6 slides)
(2, 'Thông báo tuyển sinh 2026', '/uploads/slides/adm1.jpg', 1),
(2, 'Xét tuyển học bạ trực tuyến', '/uploads/slides/adm2.jpg', 2),
(2, 'Top 10 ngành HOT nhất', '/uploads/slides/adm3.jpg', 3),
(2, 'Cơ hội nhận học bổng 100%', '/uploads/slides/adm4.jpg', 4),
(2, 'Ký túc xá tiện nghi', '/uploads/slides/adm5.jpg', 5),
(2, 'Cam kết việc làm sau tốt nghiệp', '/uploads/slides/adm6.jpg', 6),
-- Youth Union Slider (6 slides)
(3, 'Chiến dịch Mùa hè xanh', '/uploads/slides/youth1.jpg', 1),
(3, 'Hội thao sinh viên toàn trường', '/uploads/slides/youth2.jpg', 2),
(3, 'Cuộc thi Robocon', '/uploads/slides/youth3.jpg', 3),
(3, 'Hiến máu tình nguyện', '/uploads/slides/youth4.jpg', 4),
(3, 'Văn nghệ chào mừng 20/11', '/uploads/slides/youth5.jpg', 5),
(3, 'Lễ ra quân thanh niên xung kích', '/uploads/slides/youth6.jpg', 6);

INSERT INTO `carousel_slides`
  (`carousel_id`, `title`, `title_highlight`, `description`, `image_path`, `image_alt`, `cta_label`, `cta_url`, `cta_variant`, `sort_order`, `is_active`)
VALUES
  (
    4,
    'Môi trường học tập',
    'Chuyên nghiệp & Sáng tạo',
    'Không gian học tập mở, khuyến khích sự sáng tạo và hợp tác, với sự hỗ trợ từ đội ngũ giảng viên giàu kinh nghiệm và tận tâm.',
    'https://images.unsplash.com/photo-1524178232363-1fb2b075b655',
    'Lecture hall with students',
    'Tìm hiểu thêm',
    '#',
    'secondary',
    1,
    1
  ),
  (
    4,
    'Công nghệ tiên tiến',
    'Hỗ trợ học tập 24/7',
    'Hệ thống học trực tuyến hiện đại, tài liệu số hóa đầy đủ, và phòng lab công nghệ cao giúp bạn học mọi lúc, mọi nơi.',
    'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4',
    'Modern computer lab',
    'Khám phá ngay',
    '#',
    'primary',
    2,
    1
  );


-- ----------------------------------------------------------------------------
-- 2.10 BẢNG MENUS VÀ MENU_ITEMS (20 Menu Items chia cho 3 Menus)
-- ----------------------------------------------------------------------------
INSERT INTO `menus` (`id`, `key`, `label`) VALUES 
(1, 'header_menu', 'Menu Chính Cấp 1'),
(2, 'footer_links', 'Menu Chân Trang'),
(3, 'student_sidebar', 'Menu Sinh Viên');

INSERT INTO `menu_items` (`id`, `menu_id`, `parent_id`, `label`, `url`, `sort_order`) VALUES
-- Header Menu (10 items - Có phân cấp)
(1, 1, NULL, 'Trang chủ', '/', 1),
(2, 1, NULL, 'Giới thiệu', '/gioi-thieu', 2),
(3, 1, NULL, 'Tuyển sinh', '/tuyen-sinh', 3),
(4, 1, NULL, 'Đào tạo', '/dao-tao', 4),
(5, 1, 4, 'Các ngành đào tạo', '/dao-tao/nganh-hoc', 1),
(6, 1, 4, 'Chuẩn đầu ra', '/dao-tao/chuan-dau-ra', 2),
(7, 1, NULL, 'Tin tức', '/tin-tuc', 5),
(8, 1, NULL, 'Đoàn - Hội', '/doan-hoi', 6),
(9, 1, NULL, 'Doanh nghiệp', '/doanh-nghiep', 7),
(10, 1, NULL, 'Liên hệ', '/lien-he', 8),

-- Footer Menu (4 items)
(11, 2, NULL, 'Chính sách bảo mật', '/privacy-policy', 1),
(12, 2, NULL, 'Điều khoản sử dụng', '/terms', 2),
(13, 2, NULL, 'Sơ đồ trang', '/sitemap', 3),
(14, 2, NULL, 'Đóng góp ý kiến', '/feedback', 4),

-- Student Sidebar Menu (6 items)
(15, 3, NULL, 'Thời khóa biểu', '/sv/thoi-khoa-bieu', 1),
(16, 3, NULL, 'Kết quả học tập', '/sv/diem-thi', 2),
(17, 3, NULL, 'Thông tin học phí', '/sv/hoc-phi', 3),
(18, 3, NULL, 'Đánh giá rèn luyện', '/sv/ren-luyen', 4),
(19, 3, NULL, 'Đăng ký học phần', '/sv/dang-ky-hoc', 5),
(20, 3, NULL, 'Biểu mẫu trực tuyến', '/sv/bieu-mau', 6);

SET FOREIGN_KEY_CHECKS = 1;