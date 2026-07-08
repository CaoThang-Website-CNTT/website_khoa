-- Fixture kiểm thử preview/in phiếu đăng ký Đồ án tốt nghiệp.
-- Yêu cầu: đã chạy toàn bộ migration đến
--   2026_07_10_090000_add_registration_form_fields_to_project_groups.php
--
-- Tài khoản GV (mật khẩu chung: password):
--   datn.fixture.gv1@caothang.edu.vn
--   datn.fixture.gv2@caothang.edu.vn
--   datn.fixture.gv3@caothang.edu.vn
--
-- Script idempotent: chỉ xóa/tạo lại dữ liệu mang namespace DATN-FIXTURE.

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
START TRANSACTION;

SET @fixture_batch_title := '[DATN-FIXTURE] Đợt kiểm thử phiếu đăng ký';
SET @password_hash := '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- Xóa fixture cũ theo thứ tự khóa ngoại, không đụng dữ liệu khác.
DELETE pa FROM project_aspirations pa
JOIN project_groups pg ON pg.id = pa.group_id
JOIN project_batches pb ON pb.id = pg.batch_id
WHERE pb.title LIKE '[DATN-FIXTURE]%';
DELETE pgm FROM project_group_members pgm
JOIN project_groups pg ON pg.id = pgm.group_id
JOIN project_batches pb ON pb.id = pg.batch_id
WHERE pb.title LIKE '[DATN-FIXTURE]%';
DELETE pg FROM project_groups pg
JOIN project_batches pb ON pb.id = pg.batch_id
WHERE pb.title LIKE '[DATN-FIXTURE]%';
DELETE pt FROM project_topics pt
JOIN project_batches pb ON pb.id = pt.batch_id
WHERE pb.title LIKE '[DATN-FIXTURE]%';
DELETE pbs FROM project_batch_supervisors pbs
JOIN project_batches pb ON pb.id = pbs.batch_id
WHERE pb.title LIKE '[DATN-FIXTURE]%';
DELETE FROM project_batches WHERE title LIKE '[DATN-FIXTURE]%';

-- Bản ghi nền tối thiểu, dùng natural key để chạy an toàn trên database rỗng hoặc đã seed.
INSERT INTO departments (full_name, short_name, description, created_at, updated_at)
SELECT 'Khoa Công nghệ Thông tin', 'DATNFIX', 'Dữ liệu nền dành riêng cho fixture ĐATN', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM departments WHERE short_name = 'DATNFIX');
SET @department_id := (SELECT id FROM departments WHERE short_name = 'DATNFIX' LIMIT 1);

INSERT INTO majors (full_name, short_name, level, department_id, created_at, updated_at)
SELECT 'Công nghệ Thông tin - Fixture', 'DATNCNTT', 'CĐ', @department_id, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM majors WHERE short_name = 'DATNCNTT');
SET @major_id := (SELECT id FROM majors WHERE short_name = 'DATNCNTT' LIMIT 1);

INSERT INTO classrooms (major_id, specialization_id, homeroom_teacher_id, class_of, letter, short_name, created_at, updated_at)
SELECT @major_id, NULL, NULL, 23, 'F', 'DATN23F', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM classrooms WHERE short_name = 'DATN23F');
SET @classroom_id := (SELECT id FROM classrooms WHERE short_name = 'DATN23F' LIMIT 1);

INSERT INTO accounts (email, password_hash, role, created_at, updated_at)
VALUES
('datn.fixture.admin@caothang.edu.vn', @password_hash, 'admin', NOW(), NOW()),
('datn.fixture.gv1@caothang.edu.vn', @password_hash, 'teacher', NOW(), NOW()),
('datn.fixture.gv2@caothang.edu.vn', @password_hash, 'teacher', NOW(), NOW()),
('datn.fixture.gv3@caothang.edu.vn', @password_hash, 'teacher', NOW(), NOW())
ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = VALUES(role), deleted_at = NULL, updated_at = NOW();

SET @admin_account_id := (SELECT id FROM accounts WHERE email = 'datn.fixture.admin@caothang.edu.vn');
SET @gv1_account_id := (SELECT id FROM accounts WHERE email = 'datn.fixture.gv1@caothang.edu.vn');
SET @gv2_account_id := (SELECT id FROM accounts WHERE email = 'datn.fixture.gv2@caothang.edu.vn');
SET @gv3_account_id := (SELECT id FROM accounts WHERE email = 'datn.fixture.gv3@caothang.edu.vn');

INSERT INTO teachers (account_id, full_name, gender, phone, national_id, degree, title, position, department_id, created_at, updated_at)
VALUES
(@gv1_account_id, 'ThS. Nguyễn Minh An', 'male', '0909100001', '079900100001', 'Thạc sĩ', 'Giảng viên', 'Giáo viên', @department_id, NOW(), NOW()),
(@gv2_account_id, 'ThS. Trần Thu Bình', 'female', '0909100002', '079900100002', 'Thạc sĩ', 'Giảng viên', 'Giáo viên', @department_id, NOW(), NOW()),
(@gv3_account_id, 'TS. Lê Quốc Cường', 'male', '0909100003', '079900100003', 'Tiến sĩ', 'Giảng viên chính', 'Giáo viên', @department_id, NOW(), NOW())
ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), department_id = VALUES(department_id), deleted_at = NULL, updated_at = NOW();

SET @gv1 := (SELECT id FROM teachers WHERE account_id = @gv1_account_id);
SET @gv2 := (SELECT id FROM teachers WHERE account_id = @gv2_account_id);
SET @gv3 := (SELECT id FROM teachers WHERE account_id = @gv3_account_id);

-- 18 SV hợp lệ: hệ 03, ngành 06, niên khóa 23.
INSERT INTO accounts (email, password_hash, role, created_at, updated_at)
SELECT CONCAT('0306239', LPAD(n, 3, '0'), '@caothang.edu.vn'), @password_hash, 'student', NOW(), NOW()
FROM (
  SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6
  UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12
  UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15 UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18
) numbers
ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = VALUES(role), deleted_at = NULL, updated_at = NOW();

INSERT INTO students (account_id, full_name, gender, dob, phone, address, national_id, birth_place, student_id, classroom_id, major, status, notes, created_at, updated_at)
SELECT a.id, CONCAT('Sinh viên Fixture ', LPAD(x.n, 2, '0')),
       IF(MOD(x.n, 2) = 0, 'female', 'male'), '2005-01-15', CONCAT('0918', LPAD(x.n, 6, '0')),
       'TP. Hồ Chí Minh', CONCAT('079901', LPAD(x.n, 6, '0')), 'TP. Hồ Chí Minh',
       CONCAT('0306239', LPAD(x.n, 3, '0')), @classroom_id, 'Công nghệ Thông tin', 'Đang học',
       '[DATN-FIXTURE]', NOW(), NOW()
FROM (
  SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6
  UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12
  UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15 UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18
) x
JOIN accounts a ON a.email = CONCAT('0306239', LPAD(x.n, 3, '0'), '@caothang.edu.vn')
ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), classroom_id = VALUES(classroom_id), status = VALUES(status), deleted_at = NULL, updated_at = NOW();

INSERT INTO project_batches
(title, description, topic_proposal_start, topic_proposal_end, registration_start, registration_end, max_aspirations, min_class_of, max_class_of, status, created_by, published_at, created_at, updated_at)
VALUES
(@fixture_batch_title, 'Fixture hậu phân bổ dùng kiểm thử preview và in phiếu đăng ký ĐATN.',
 '2026-06-01 08:00:00', '2026-06-15 17:00:00', '2026-06-16 08:00:00', '2026-07-31 17:00:00',
 3, 23, 23, 'published', @admin_account_id, '2026-06-16 08:00:00', NOW(), NOW());
SET @batch_id := LAST_INSERT_ID();

INSERT INTO project_batch_supervisors (batch_id, teacher_id, min_students, max_students, is_active, created_at, updated_at)
VALUES (@batch_id, @gv1, 0, 8, 1, NOW(), NOW()), (@batch_id, @gv2, 0, 8, 1, NOW(), NOW()), (@batch_id, @gv3, 0, 8, 1, NOW(), NOW());

INSERT INTO project_topics
(batch_id, teacher_id, title, description, max_students, status, submitted_at, reviewed_at, reviewed_by, created_at, updated_at)
VALUES
(@batch_id, @gv1, '[DATN-FIXTURE] Hệ thống quản lý đồ án', '<p>Xây dựng hệ thống quản lý quy trình đồ án tốt nghiệp.</p><ul><li>Phân quyền người dùng</li><li>Quản lý nguyện vọng</li><li>Xuất biểu mẫu</li></ul>', 6, 'approved', NOW(), NOW(), @admin_account_id, NOW(), NOW()),
(@batch_id, @gv1, '[DATN-FIXTURE] Cổng hỗ trợ học tập', '<p>Phát triển cổng hỗ trợ học tập có tìm kiếm và thông báo.</p>', 2, 'approved', NOW(), NOW(), @admin_account_id, NOW(), NOW()),
(@batch_id, @gv2, '[DATN-FIXTURE] Ứng dụng quản lý phòng máy', '<p>Quản lý thiết bị, lịch sử bảo trì và báo cáo sự cố phòng máy.</p>', 4, 'approved', NOW(), NOW(), @admin_account_id, NOW(), NOW()),
(@batch_id, @gv2, '[DATN-FIXTURE] Nền tảng tuyển dụng sinh viên', '<p>Kết nối sinh viên với doanh nghiệp và theo dõi hồ sơ ứng tuyển.</p>', 2, 'approved', NOW(), NOW(), @admin_account_id, NOW(), NOW()),
(@batch_id, @gv3, '[DATN-FIXTURE] Trợ lý hỏi đáp học vụ', '<p>Xây dựng trợ lý hỏi đáp học vụ dựa trên kho tri thức nội bộ.</p>', 2, 'approved', NOW(), NOW(), @admin_account_id, NOW(), NOW()),
(@batch_id, @gv3, '[DATN-FIXTURE] Dashboard phân tích đào tạo', '<p>Tổng hợp chỉ số đào tạo và trực quan hóa dữ liệu theo thời gian.</p>', 2, 'approved', NOW(), NOW(), @admin_account_id, NOW(), NOW());

SET @t1 := (SELECT id FROM project_topics WHERE batch_id=@batch_id AND title LIKE '%Hệ thống quản lý đồ án');
SET @t2 := (SELECT id FROM project_topics WHERE batch_id=@batch_id AND title LIKE '%Cổng hỗ trợ học tập');
SET @t3 := (SELECT id FROM project_topics WHERE batch_id=@batch_id AND title LIKE '%Ứng dụng quản lý phòng máy');
SET @t4 := (SELECT id FROM project_topics WHERE batch_id=@batch_id AND title LIKE '%Nền tảng tuyển dụng sinh viên');
SET @t5 := (SELECT id FROM project_topics WHERE batch_id=@batch_id AND title LIKE '%Trợ lý hỏi đáp học vụ');
SET @t6 := (SELECT id FROM project_topics WHERE batch_id=@batch_id AND title LIKE '%Dashboard phân tích đào tạo');

-- 9 nhóm; t1 có 3 nhóm (editor chung), t3 có 2 nhóm dữ liệu khác nhau; nhóm 9 dùng fallback mô tả.
INSERT INTO project_groups
(batch_id, leader_student_id, assigned_topic_id, assigned_at, registration_requirements, supervisor_opinion, execution_start, execution_end, created_at, updated_at)
SELECT @batch_id, s.id,
 CASE n WHEN 1 THEN @t1 WHEN 2 THEN @t1 WHEN 3 THEN @t1 WHEN 4 THEN @t2 WHEN 5 THEN @t3 WHEN 6 THEN @t3 WHEN 7 THEN @t4 WHEN 8 THEN @t5 ELSE @t6 END,
 DATE_ADD('2026-07-01 08:00:00', INTERVAL n HOUR),
 CASE
  WHEN n IN (1,2,3) THEN '<p><strong>Yêu cầu chung:</strong></p><ul><li>Khảo sát nghiệp vụ</li><li>Thiết kế và kiểm thử hệ thống</li><li>Viết báo cáo hoàn chỉnh</li></ul>'
  WHEN n=5 THEN '<p>Xây dựng mô-đun <strong>quản lý thiết bị</strong> và lịch bảo trì.</p>'
  WHEN n=6 THEN '<p>Xây dựng mô-đun <strong>báo cáo sự cố</strong> và thống kê.</p>'
  WHEN n=9 THEN NULL
  ELSE '<p>Phân tích yêu cầu, xây dựng sản phẩm và trình bày kết quả.</p>' END,
 CASE WHEN n IN (1,2,3) THEN '<p>Nhóm thực hiện đúng tiến độ và báo cáo định kỳ mỗi tuần.</p>'
      WHEN n=9 THEN NULL ELSE CONCAT('<p>Ý kiến hướng dẫn riêng cho nhóm ', n, '.</p>') END,
 CASE WHEN n=9 THEN NULL ELSE '2026-08-01' END,
 CASE WHEN n=9 THEN NULL ELSE '2026-12-15' END,
 DATE_ADD('2026-06-20 08:00:00', INTERVAL n HOUR), NOW()
FROM (
 SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
) g
JOIN students s ON s.student_id = CONCAT('0306239', LPAD((g.n*2)-1, 3, '0'));

-- Thành viên: leader lẻ, member chẵn; tất cả confirmed và eligible.
INSERT INTO project_group_members (group_id, student_id, is_leader, is_confirmed, is_eligible, confirmed_at, created_at, updated_at)
SELECT pg.id, s.id, 1, 1, 1, pg.created_at, NOW(), NOW()
FROM project_groups pg JOIN students s ON s.id=pg.leader_student_id WHERE pg.batch_id=@batch_id;
INSERT INTO project_group_members (group_id, student_id, is_leader, is_confirmed, is_eligible, confirmed_at, created_at, updated_at)
SELECT pg.id, s.id, 0, 1, 1, pg.created_at, NOW(), NOW()
FROM project_groups pg
JOIN students leader ON leader.id=pg.leader_student_id
JOIN students s ON s.student_id=CONCAT('0306239', LPAD(CAST(RIGHT(leader.student_id,3) AS UNSIGNED)+1, 3, '0'))
WHERE pg.batch_id=@batch_id;

-- Ba nguyện vọng/nhóm. Nhóm 1-3 trúng NV1, 4-6 trúng NV2, 7-9 trúng NV3.
INSERT INTO project_aspirations (group_id, topic_id, priority, status, locked_at, created_at, updated_at)
SELECT pg.id,
 CASE group_no
  WHEN 1 THEN CASE rank_no WHEN 1 THEN @t1 WHEN 2 THEN @t2 ELSE @t3 END
  WHEN 2 THEN CASE rank_no WHEN 1 THEN @t1 WHEN 2 THEN @t3 ELSE @t4 END
  WHEN 3 THEN CASE rank_no WHEN 1 THEN @t1 WHEN 2 THEN @t5 ELSE @t6 END
  WHEN 4 THEN CASE rank_no WHEN 1 THEN @t3 WHEN 2 THEN @t2 ELSE @t4 END
  WHEN 5 THEN CASE rank_no WHEN 1 THEN @t4 WHEN 2 THEN @t3 ELSE @t5 END
  WHEN 6 THEN CASE rank_no WHEN 1 THEN @t5 WHEN 2 THEN @t3 ELSE @t6 END
  WHEN 7 THEN CASE rank_no WHEN 1 THEN @t1 WHEN 2 THEN @t2 ELSE @t4 END
  WHEN 8 THEN CASE rank_no WHEN 1 THEN @t2 WHEN 2 THEN @t3 ELSE @t5 END
  ELSE CASE rank_no WHEN 1 THEN @t3 WHEN 2 THEN @t4 ELSE @t6 END END,
 rank_no, IF((group_no<=3 AND rank_no=1) OR (group_no BETWEEN 4 AND 6 AND rank_no=2) OR (group_no>=7 AND rank_no=3), 'approved', 'rejected'),
 DATE_ADD('2026-06-25 08:00:00', INTERVAL group_no HOUR), DATE_ADD('2026-06-25 08:00:00', INTERVAL group_no HOUR), NOW()
FROM (
 SELECT pg0.*, (CAST(RIGHT(s0.student_id, 3) AS UNSIGNED) + 1) / 2 AS group_no
 FROM project_groups pg0 JOIN students s0 ON s0.id=pg0.leader_student_id WHERE pg0.batch_id=@batch_id
) pg
CROSS JOIN (SELECT 1 rank_no UNION ALL SELECT 2 UNION ALL SELECT 3) ranks;

COMMIT;

-- Tóm tắt phục vụ kiểm thử trực quan.
SELECT pb.id AS batch_id, pb.title, pb.status, COUNT(DISTINCT pt.id) AS topics, COUNT(DISTINCT pg.id) AS groups,
       COUNT(DISTINCT pgm.student_id) AS students, CONCAT('/teacher/project_batches/', pb.id) AS teacher_page
FROM project_batches pb
LEFT JOIN project_topics pt ON pt.batch_id=pb.id
LEFT JOIN project_groups pg ON pg.batch_id=pb.id
LEFT JOIN project_group_members pgm ON pgm.group_id=pg.id
WHERE pb.title=@fixture_batch_title GROUP BY pb.id;

SELECT t.full_name AS teacher, a.email AS login_email, pt.title AS topic, pg.id AS group_id,
       GROUP_CONCAT(s.student_id ORDER BY pgm.is_leader DESC, s.student_id SEPARATOR ', ') AS student_codes,
       CONCAT('/teacher/project_batches/', @batch_id, '/groups/', pg.id, '/registration-form') AS single_print_url
FROM project_groups pg
JOIN project_topics pt ON pt.id=pg.assigned_topic_id
JOIN teachers t ON t.id=pt.teacher_id JOIN accounts a ON a.id=t.account_id
JOIN project_group_members pgm ON pgm.group_id=pg.id JOIN students s ON s.id=pgm.student_id
WHERE pg.batch_id=@batch_id
GROUP BY t.id, pt.id, pg.id ORDER BY t.full_name, pt.title, pg.id;
