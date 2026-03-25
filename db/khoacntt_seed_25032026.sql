SET FOREIGN_KEY_CHECKS = 0;

-- 1. ACADEMIC DATA (Majors -> Specializations -> Classrooms)
-- ----------------------------------------------------------------------------
INSERT INTO `majors` (`id`, `full_name`, `short_name`, `level`, `created_at`) VALUES
(1, 'Tin học', 'TH', 'CĐ', NOW()),
(2, 'Công nghệ thông tin', 'CNTT', 'CĐ', NOW()),
(3, 'Quản trị mạng máy tính', 'QTM', 'CĐN', NOW()),
(4, 'Kỹ thuật sửa chữa, lắp ráp máy tính', 'SCMT', 'CĐN', NOW()),
(5, 'Nhiệt lạnh', 'NL', 'CĐ', NOW()),
(6, 'Công nghệ Kỹ thuật Điện, Điện tử', 'ĐĐT', 'CĐ', NOW()),
(7, 'Công nghệ Kỹ thuật Điện tử - Viễn thông', 'ĐTVT', 'CĐ', NOW()),
(8, 'Công nghệ Kỹ thuật Cơ khí', 'CK', 'CĐ', NOW()),
(9, 'Công nghệ Kỹ thuật ô tô', 'OTO', 'CĐ', NOW()),
(10, 'Công nghệ Kỹ thuật Điều khiển và Tự động hoá', 'TĐ', 'CĐ', NOW());

INSERT INTO `specializations` (`id`, `major_id`, `full_name`, `short_name`, `created_at`) VALUES
(1, 1, 'Lập trình Website', 'WEB', NOW()),
(2, 1, 'Lập trình Di động', 'DĐ', NOW()),
(3, 2, 'Lập trình Website', 'WEB', NOW()),
(4, 2, 'Lập trình Di động', 'DĐ', NOW()),
(5, 2, 'Trí tuệ nhân tạo', 'AI', NOW());

INSERT INTO `classrooms` (`id`, `major_id`, `class_of`, `specialization_id`, `letter`, `short_name`, `updated_at`, `created_at`) VALUES 
(1, 1, 23, 1, 'C', 'CĐ TH 23 WEB C', NOW(), NOW()),
(2, 1, 23, 1, 'B', 'CĐ TH 23 WEB B', NOW(), NOW()),
(3, 2, 25, NULL, 'A', 'CĐ CNTT 25 A', NOW(), NOW()),
(4, 2, 24, 5, 'A', 'CĐ TH 24 AI A', NOW(), NOW()),
(5, 3, 24, NULL, NULL, 'CĐN QTM 24', NOW(), NOW());


-- 2. USER ACCOUNTS (Teachers & Students)
-- ----------------------------------------------------------------------------
-- Tài khoản giảng viên (ID 1-10)
INSERT INTO `accounts` (`id`, `email`, `password_hash`, `role`, `created_at`) VALUES
(1, 'nguyenvanan@caothang.edu.vn', '$2b$10$hash1', 'teacher', NOW()),
(2, 'lethibinh@caothang.edu.vn', '$2b$10$hash2', 'teacher', NOW()),
(3, 'tranminh@caothang.edu.vn', '$2b$10$hash3', 'teacher', NOW()),
(4, 'phamthidiem@caothang.edu.vn', '$2b$10$hash4', 'teacher', NOW()),
(5, 'hoangvanem@caothang.edu.vn', '$2b$10$hash5', 'teacher', NOW()),
(6, 'nguyenthiphong@caothang.edu.vn', '$2b$10$hash6', 'teacher', NOW()),
(7, 'dangvannghia@caothang.edu.vn', '$2b$10$hash7', 'teacher', NOW()),
(8, 'buithihanh@caothang.edu.vn', '$2b$10$hash8', 'teacher', NOW()),
(9, 'vovanluong@caothang.edu.vn', '$2b$10$hash9', 'teacher', NOW()),
(10, 'duongthikieu@caothang.edu.vn', '$2b$10$hash10', 'teacher', NOW());

-- Tài khoản sinh viên (ID 11-35)
INSERT INTO `accounts` (`id`, `email`, `password_hash`, `role`, `created_at`) VALUES
(11, '0306231001@caothang.edu.vn', '123456', 'student', NOW()),
(12, '0306231002@caothang.edu.vn', '123456', 'student', NOW()),
(13, '0306231003@caothang.edu.vn', '123456', 'student', NOW()),
(14, '0306231004@caothang.edu.vn', '123456', 'student', NOW()),
(15, '0306231005@caothang.edu.vn', '123456', 'student', NOW()),
(16, '0306231006@caothang.edu.vn', '123456', 'student', NOW()),
(17, '0306231007@caothang.edu.vn', '123456', 'student', NOW()),
(18, '0306231008@caothang.edu.vn', '123456', 'student', NOW()),
(19, '0306231009@caothang.edu.vn', '123456', 'student', NOW()),
(20, '0306231010@caothang.edu.vn', '123456', 'student', NOW()),
(21, '0306231011@caothang.edu.vn', '123456', 'student', NOW()),
(22, '0306231012@caothang.edu.vn', '123456', 'student', NOW()),
(23, '0306231013@caothang.edu.vn', '123456', 'student', NOW()),
(24, '0306231014@caothang.edu.vn', '123456', 'student', NOW()),
(25, '0306231015@caothang.edu.vn', '123456', 'student', NOW()),
(26, '0306231016@caothang.edu.vn', '123456', 'student', NOW()),
(27, '0306231017@caothang.edu.vn', '123456', 'student', NOW()),
(28, '0306231018@caothang.edu.vn', '123456', 'student', NOW()),
(29, '0306231019@caothang.edu.vn', '123456', 'student', NOW()),
(30, '0306231020@caothang.edu.vn', '123456', 'student', NOW()),
(31, '0306231021@caothang.edu.vn', '123456', 'student', NOW()),
(32, '0306231022@caothang.edu.vn', '123456', 'student', NOW()),
(33, '0306231023@caothang.edu.vn', '123456', 'student', NOW()),
(34, '0306231024@caothang.edu.vn', '123456', 'student', NOW()),
(35, '0306231025@caothang.edu.vn', '123456', 'student', NOW());


-- 3. PROFILE DATA
-- ----------------------------------------------------------------------------
INSERT INTO `teachers` (`account_id`, `full_name`, `gender`, `dob`, `phone`, `degree`, `title`, `department`, `start_date`) VALUES
(1, 'Nguyễn Văn An', 'male', '1985-05-20', '0901234561', 'Thạc sĩ', NULL, NULL, '2010-09-01'),
(2, 'Lê Thị Bình', 'female', '1988-03-15', '0901234562', 'Tiến sĩ', NULL, NULL, '2012-09-01'),
(3, 'Trần Minh', 'male', '1982-11-10', '0901234563', 'Thạc sĩ', NULL, NULL, '2008-09-01'),
(4, 'Phạm Thị Diễm', 'female', '1990-01-25', '0901234564', 'Đại học', NULL, NULL, '2015-09-01'),
(5, 'Hoàng Văn Em', 'male', '1987-07-07', '0901234565', 'Thạc sĩ', NULL, NULL, '2011-09-01'),
(6, 'Nguyễn Thị Phong', 'female', '1984-12-12', '0901234566', 'Thạc sĩ', NULL, NULL, '2009-09-01'),
(7, 'Đặng Văn Nghĩa', 'male', '1980-08-08', '0901234567', 'Tiến sĩ', NULL, NULL, '2005-09-01'),
(8, 'Bùi Thị Hạnh', 'female', '1992-04-04', '0901234568', 'Thạc sĩ', NULL, NULL, '2018-09-01'),
(9, 'Võ Văn Lượng', 'male', '1989-09-09', '0901234569', 'Thạc sĩ', NULL, NULL, '2014-09-01'),
(10, 'Dương Thị Kiều', 'female', '1991-10-10', '0901234570', 'Đại học', NULL, NULL, '2016-09-01');

INSERT INTO `students` (`account_id`, `student_id`, `full_name`, `gender`, `dob`, `phone`, `classroom_id`, `major`, `birth_place`) VALUES
(11, '0306231001', 'Nguyễn Sinh Viên 1', 'male', '2007-01-15', '0311111101', 1, NULL, 'TP.HCM'),
(12, '0306231002', 'Trần Sinh Viên 2', 'female', '2007-02-20', '0311111102', 1, NULL, 'Long An'),
(13, '0306231003', 'Lê Sinh Viên 3', 'male', '2007-03-10', '0311111103', 1, NULL, 'Tiền Giang'),
(14, '0306231004', 'Phạm Sinh Viên 4', 'female', '2007-04-05', '0311111104', 1, NULL, 'Đồng Nai'),
(15, '0306231005', 'Hoàng Sinh Viên 5', 'male', '2007-05-12', '0311111105', 1, NULL, 'Bình Dương'),
(16, '0306231006', 'Vũ Sinh Viên 6', 'female', '2007-06-25', '0311111106', 2, NULL, 'TP.HCM'),
(17, '0306231007', 'Đặng Sinh Viên 7', 'male', '2007-07-30', '0311111107', 2, NULL, 'Tây Ninh'),
(18, '0306231008', 'Bùi Sinh Viên 8', 'female', '2007-08-14', '0311111108', 2, NULL, 'Vũng Tàu'),
(19, '0306231009', 'Lý Sinh Viên 9', 'male', '2007-09-18', '0311111109', 2, NULL, 'Cần Thơ'),
(20, '0306231010', 'Ngô Sinh Viên 10', 'female', '2007-10-22', '0311111110', 2, NULL, 'Đồng Tháp'),
(21, '0306231011', 'Phan Sinh Viên 11', 'male', '2007-11-01', '0311111111', 3, NULL, 'TP.HCM'),
(22, '0306231012', 'Trịnh Sinh Viên 12', 'female', '2007-12-05', '0311111112', 3, NULL, 'An Giang'),
(23, '0306231013', 'Vương Sinh Viên 13', 'male', '2007-01-20', '0311111113', 3, NULL, 'Kiên Giang'),
(24, '0306231014', 'Đoàn Sinh Viên 14', 'female', '2007-02-28', '0311111114', 3, NULL, 'Bến Tre'),
(25, '0306231015', 'Mai Sinh Viên 15', 'male', '2007-03-15', '0311111115', 3, NULL, 'Sóc Trăng'),
(26, '0306231016', 'Đào Sinh Viên 16', 'female', '2007-04-10', '0311111116', 4, NULL, 'Trà Vinh'),
(27, '0306231017', 'Hồ Sinh Viên 17', 'male', '2007-05-05', '0311111117', 4, NULL, 'Vĩnh Long'),
(28, '0306231018', 'Lương Sinh Viên 18', 'female', '2007-06-12', '0311111118', 4, NULL, 'Bạc Liêu'),
(29, '0306231019', 'Thái Sinh Viên 19', 'male', '2007-07-20', '0311111119', 4, NULL, 'Cà Mau'),
(30, '0306231020', 'Tạ Sinh Viên 20', 'female', '2007-08-25', '0311111120', 4, NULL, 'Hậu Giang'),
(31, '0306231021', 'Quách Sinh Viên 21', 'male', '2007-09-30', '0311111121', 5, NULL, 'TP.HCM'),
(32, '0306231022', 'Kim Sinh Viên 22', 'female', '2007-10-14', '0311111122', 5, NULL, 'Bình Phước'),
(33, '0306231023', 'Chu Sinh Viên 23', 'male', '2007-11-08', '0311111123', 5, NULL, 'Đắk Lắk'),
(34, '0306231024', 'Mạc Sinh Viên 24', 'female', '2007-12-12', '0311111124', 5, NULL, 'Lâm Đồng'),
(35, '0306231025', 'Đỗ Sinh Viên 25', 'male', '2007-01-30', '0311111125', 5, NULL, 'Gia Lai');


-- 4. CONFIGURATION & UI DATA (Web Settings -> Menus -> Categories)
-- ----------------------------------------------------------------------------
-- Web Settings
-- group: general
INSERT INTO `web_settings` (`key`, `group`, `type`, `value`, `default_value`, `label`, `description`, `autoload`, `is_locked`, `sort_order`) VALUES
('site_title', 'general', 'string', 'KHOA CÔNG NGHỆ THÔNG TIN', NULL, 'Tên Website', 'Hiển thị trên tab trình duyệt và header', 1, 1, 1),
('posts_per_page', 'general', 'int', '10', '10', 'Bài Viết Mỗi Trang', NULL, 1, 0, 4),
('site_under_maintenance', 'general', 'bool', '0', '0', 'Chế Độ Bảo Trì', 'Bật sẽ hiển thị trang bảo trì cho visitor', 1, 0, 5);

-- group: homepage
INSERT INTO `web_settings` (`key`, `group`, `type`, `value`, `default_value`, `label`, `description`, `autoload`, `sort_order`) VALUES
('homepage.hero_title', 'homepage', 'string', 'Chào Mừng Đến Với Khoa CNTT', NULL, 'Tiêu Đề Hero Banner', NULL, 1, 1),
('homepage.hero_subtitle', 'homepage', 'text', 'Đào tạo nguồn nhân lực chất lượng cao trong lĩnh vực Công nghệ Thông tin.', NULL, 'Mô Tả Hero Banner', NULL, 1, 2),
('homepage.hero_cta_text', 'homepage', 'string', 'Tìm Hiểu Thêm', 'Tìm Hiểu Thêm', 'Nút CTA – Label', NULL, 1, 4),
('homepage.hero_cta_url', 'homepage', 'url', '/gioi-thieu', '/gioi-thieu', 'Nút CTA – Đường Dẫn', NULL, 1, 5),
('homepage.show_news', 'homepage', 'bool', '1', '1', 'Hiển Thị Khối Tin Tức', NULL, 1, 6),
('homepage.news_count', 'homepage', 'int', '6', '6', 'Số Bài Tin Tức Trang Chủ', NULL, 1, 7),
('homepage.show_stats', 'homepage', 'bool', '1', '1', 'Hiển Thị Thống Kê', 'Sinh viên, giảng viên, ...', 1, 8),
('homepage.stats', 'homepage', 'json', '[{"label":"Sinh viên","value":"2500+"},{"label":"Giảng viên","value":"120"},{"label":"Chương trình","value":"8"},{"label":"Năm thành lập","value":"1995"}]', NULL, 'Số Liệu Thống Kê', 'Mảng JSON: [{label, value}]', 0, 9);

-- group: contact
INSERT INTO `web_settings` (`key`, `group`, `type`, `value`, `default_value`, `label`, `autoload`, `sort_order`) VALUES
('contact.address', 'contact', 'text', 'Lầu 7 - Dãy F, 65 Huỳnh Thúc Kháng, Phường Sài Gòn, TP.HCM, Việt Nam', NULL, 'Địa Chỉ', 1, 1),
('contact.phone', 'contact', 'string', '+84 (08) 3821 2360', NULL, 'Số Điện Thoại', 1, 2),
('contact.email', 'contact', 'email', 'cntt@caothang.edu.vn', NULL, 'Email Liên Hệ', 1, 3),
('contact.description', 'contact', 'text', 'Trang web chính thức của Khoa Công Nghệ Thông Tin - Trường Cao Đẳng Kỹ Thuật Cao Thắng.', NULL, 'Mô Tả Ngắn (Footer)', 1, 4);

-- group: social
INSERT INTO `web_settings` (`key`, `group`, `type`, `value`, `default_value`, `label`, `autoload`, `sort_order`) VALUES
('social.facebook', 'social', 'url', NULL, NULL, 'Facebook', 1, 1),
('social.youtube', 'social', 'url', NULL, NULL, 'YouTube', 1, 2);

-- group: seo
INSERT INTO `web_settings` (`key`, `group`, `type`, `value`, `default_value`, `label`, `description`, `autoload`, `sort_order`) VALUES
('seo.meta_title', 'seo', 'string', 'Khoa CNTT - Trường CĐ Kỹ Thuật Cao Thắng', 'Khoa CNTT - Trường CĐ Kỹ Thuật Cao Thắng', 'Meta Title Mặc Định', 'Khuyến nghị tối đa 60 ký tự', 1, 1),
('seo.meta_description', 'seo', 'text', 'Trang web chính thức của Khoa Công Nghệ Thông Tin - Trường Cao Đẳng Kỹ Thuật Cao Thắng.', NULL, 'Meta Description', 'Khuyến nghị tối đa 160 ký tự', 1, 2);

-- Menus
INSERT INTO `menus` (`id`, `key`, `label`, `description`, `type`, `sort_order`) VALUES
(1, 'main_nav', 'Menu Chính', 'Thanh điều hướng chính trên header', 'const', 1);

INSERT INTO `menu_items` (`id`, `menu_id`, `parent_id`, `label`, `url`, `sort_order`) VALUES
(1, 1, NULL, 'Trang Chủ',              '/',           1),
(2, 1, NULL, 'Giới Thiệu',            '/gioi-thieu', 2),
(3, 1, NULL, 'Chương Trình Đào Tạo',  '/dao-tao',    3),
(4, 1, NULL, 'Nghiên Cứu',            '/nghien-cuu', 4),
(5, 1, NULL, 'Tin Tức',               '/tin-tuc',    5),
(6, 1, NULL, 'Sinh Viên',             '/sinh-vien',  6),
(7, 1, NULL, 'Liên Hệ',               '/lien-he',    7),
-- Children of Giới Thiệu (id 2)
(8, 1, 2, 'Giới thiệu chung', '/gioi-thieu/chung', 1),
(9, 1, 2, 'Lịch sử',          '/gioi-thieu/lich-su', 2),
-- Children of Chương Trình Đào Tạo (id 3)
(10, 1, 3, 'Cao đẳng', '/dao-tao/cao-dang',     1),
(11, 1, 3, 'Trung cấp', '/dao-tao/cao-dang-nghe', 2),
-- Children of Nghiên Cứu (id 4)
(12, 1, 4, 'Đề tài',  '/nghien-cuu/de-tai', 1),
(13, 1, 4, 'Công bố', '/nghien-cuu/cong-bo', 2),
-- Children of Tin Tức (id 5)
(14, 1, 5, 'Sự kiện',   '/tin-tuc/su-kien',  1),
(15, 1, 5, 'Thông báo', '/tin-tuc/thong-bao', 2),
-- Children of Sinh Viên (id 6)
(16, 1, 6, 'Học bổng',  '/sinh-vien/hoc-bong',  1),
(17, 1, 6, 'Hoạt động', '/sinh-vien/hoat-dong', 2),
-- Children of Liên Hệ (id 7)
(18, 1, 7, 'Địa chỉ',      '/lien-he/dia-chi', 1),
(19, 1, 7, 'Gửi phản hồi', '/lien-he/phan-hoi', 2);

-- Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `type`, `description`, `parent_id`) VALUES
(1, 'Tin Tức',   'tin-tuc',    'const',  'Tin tức và thông báo của khoa',       NULL),
(2, 'Công Nghệ', 'cong-nghe',  'custom', 'Tin tức về công nghệ và CNTT',        NULL),
(3, 'Nghiên Cứu','nghien-cuu', 'const',  'Các kết quả nghiên cứu và bài báo',   NULL),
(4, 'Sự Kiện',   'su-kien',    'const',  'Các sự kiện và hội thảo của khoa',    NULL),
(5, 'Tin Nóng',            'tin-nong',            'custom', 'Các tin tức mới nhất',              1),
(6, 'Thông Báo Chính Thức','thong-bao-chinh-thuc', 'custom', 'Thông báo từ lãnh đạo khoa',       1),
(7, 'Trí Tuệ Nhân Tạo',   'tri-tue-nhan-tao',    'custom', 'AI và Máy Học',                     2),
(8, 'Phát Triển Web',      'phat-trien-web',      'custom', 'Các công nghệ và framework web',    2),
(9, 'Cơ Sở Dữ Liệu',      'co-so-du-lieu',       'custom', 'Công nghệ cơ sở dữ liệu',           2),
(10, 'Phát Triển PHP',   'phat-trien-php',   'custom', 'Các chủ đề liên quan PHP', 8),
(11, 'Framework Laravel','framework-laravel','custom', 'Hướng dẫn sử dụng Laravel', 10),
(12, 'MySQL',            'mysql',            'custom', 'Quản lý và tối ưu MySQL',   9);


-- 5. CONTENT DATA (Carousels -> Slides)
-- ----------------------------------------------------------------------------
INSERT INTO `carousels` (`id`, `name`, `slug`)
VALUES (1, 'Landing Page', 'landing-page');

INSERT INTO `carousel_slides` (`carousel_id`, `title`, `title_highlight`, `description`, `image_path`, `image_alt`, `cta_label`, `cta_url`, `cta_variant`, `sort_order`, `is_active`)
VALUES
(1, 'Môi trường học tập', 'Chuyên nghiệp & Sáng tạo', 'Không gian học tập mở, khuyến khích sự sáng tạo và hợp tác.', 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655', 'Lecture hall', 'Tìm hiểu thêm', '#', 'secondary', 1, 1),
(1, 'Công nghệ tiên tiến', 'Hỗ trợ học tập 24/7', 'Hệ thống học trực tuyến hiện đại, tài liệu số hóa đầy đủ.', 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4', 'Modern lab', 'Khám phá ngay', '#', 'primary', 2, 1);

SET FOREIGN_KEY_CHECKS = 1;