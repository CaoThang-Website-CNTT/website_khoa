<?php

namespace App\Stores;

use App\Core\Store;
use PDO;

interface IInternshipBatchStore
{
  public function createBatch(array $data): int;
  public function addClassroomsToBatch(int $batchId, array $classroomIds): bool;
  public function addStudentsToBatch(int $batchId, array $studentIds): bool;
  public function addSupervisorsToBatch(int $batchId, array $supervisors): bool;
  public function getEligibleStudentsByClassroom(int $classroomId): array;
  public function getEligibleStudentsByClassrooms(array $classroomIds): array;
  public function validateStudentsByStudentIds(array $studentIds): array;
  public function getActiveTeachers(): array;
  public function getAllClassrooms(): array;
  public function getPaginated(int $page, int $limit = 15): array;
  public function getTotalCount(): int;

  public function getById(int $id): ?array;
  public function getBatchStats(int $id): array;
  public function update(int $id, array $data): bool;
  public function delete(int $id): bool;
  public function updateStatus(int $id, string $status, array $extraData = []): bool;
  public function getBatchStudentsWithDetails(int $batchId): array;
  public function getBatchSupervisorsWithDetails(int $batchId): array;
  public function removeStudentFromBatch(int $batchId, int $studentId): bool;
  public function removeSupervisorFromBatch(int $batchId, int $teacherId): bool;
  public function updateSupervisorQuota(int $batchId, int $teacherId, int $newQuota): bool;
  public function searchEligibleStudents(int $batchId, string $query = '', ?int $classroomId = null): array;
  public function searchEligibleTeachers(int $batchId, string $query = ''): array;
  public function getBatchesByStudentId(int $studentId): array;
  public function getTeacherStudentsInBatch(int $batchId, int $teacherId): array;
  public function getStudentGradingDetail(int $batchStudentId): ?array;
  public function getTeacherBatchStats(int $batchId, int $teacherId): array;
  public function isSupervisorOfBatch(int $batchId, int $teacherId): bool;
}

class InternshipBatchStore extends Store implements IInternshipBatchStore
{
  public function createBatch(array $data): int
  {
    $sql = "INSERT INTO internship_batches 
            (title, description, class_of, level, start_at, end_at, status, created_by) 
            VALUES (:title, :description, :class_of, :level, :start_at, :end_at, :status, :created_by)";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      ':title' => $data['title'],
      ':description' => $data['description'] ?? null,
      ':class_of' => $data['class_of'] ?? null,
      ':level' => $data['level'] ?? null,
      ':start_at' => $data['start_at'],
      ':end_at' => $data['end_at'],
      ':status' => 'draft',
      ':created_by' => $data['created_by'] ?? null
    ]);

    return (int)$this->db->lastInsertId();
  }

  public function addClassroomsToBatch(int $batchId, array $classroomIds): bool
  {
    if (empty($classroomIds)) return true;

    $sql = "INSERT INTO internship_batch_classrooms (batch_id, classroom_id) VALUES ";
    $values = [];
    $params = [];
    foreach ($classroomIds as $i => $id) {
      $values[] = "(:batch_id_$i, :classroom_id_$i)";
      $params[":batch_id_$i"] = $batchId;
      $params[":classroom_id_$i"] = $id;
    }
    $sql .= implode(', ', $values);

    $stmt = $this->db->prepare($sql);
    return $stmt->execute($params);
  }

  public function addStudentsToBatch(int $batchId, array $studentIds): bool
  {
    if (empty($studentIds)) return true;

    $sql = "INSERT INTO internship_batch_students (batch_id, student_id, status, source) VALUES ";
    $values = [];
    $params = [];
    foreach ($studentIds as $i => $id) {
      $values[] = "(:batch_id_$i, :student_id_$i, 'pending', 'db_select')";
      $params[":batch_id_$i"] = $batchId;
      $params[":student_id_$i"] = $id;
    }
    $sql .= implode(', ', $values);

    $stmt = $this->db->prepare($sql);
    return $stmt->execute($params);
  }

  public function addSupervisorsToBatch(int $batchId, array $supervisors): bool
  {
    if (empty($supervisors)) return true;

    $sql = "INSERT INTO internship_batch_supervisors (batch_id, teacher_id, max_students, is_active) VALUES ";
    $values = [];
    $params = [];
    foreach ($supervisors as $i => $sup) {
      $values[] = "(:batch_id_$i, :teacher_id_$i, :max_students_$i, 1)";
      $params[":batch_id_$i"] = $batchId;
      $params[":teacher_id_$i"] = $sup['teacher_id'];
      $params[":max_students_$i"] = $sup['max_students'];
    }
    $sql .= implode(', ', $values);

    $stmt = $this->db->prepare($sql);
    return $stmt->execute($params);
  }

  public function getEligibleStudentsByClassroom(int $classroomId): array
  {
    // SV không thuộc đợt nào đang "draft" hoặc "public"
    $sql = "SELECT s.id, s.student_id, s.full_name, s.gender, s.dob, s.classroom_id
            FROM students s
            LEFT JOIN internship_batch_students bs ON s.id = bs.student_id
            LEFT JOIN internship_batches b ON bs.batch_id = b.id AND b.status IN ('draft', 'published')
            WHERE s.classroom_id = :classroom_id AND b.id IS NULL AND s.status = 'Đang học'
            GROUP BY s.id, s.student_id, s.full_name, s.gender, s.dob, s.classroom_id";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([':classroom_id' => $classroomId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getEligibleStudentsByClassrooms(array $classroomIds): array
  {
    if (empty($classroomIds)) return [];

    $placeholders = implode(',', array_fill(0, count($classroomIds), '?'));
    $sql = "SELECT s.id, s.student_id, s.full_name, s.gender, s.dob, s.classroom_id, s.phone
            FROM students s
            LEFT JOIN internship_batch_students bs ON s.id = bs.student_id
            LEFT JOIN internship_batches b ON bs.batch_id = b.id AND b.status IN ('draft', 'published')
            WHERE s.classroom_id IN ($placeholders) AND b.id IS NULL AND s.status = 'Đang học'
            GROUP BY s.id, s.student_id, s.full_name, s.gender, s.dob, s.classroom_id";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($classroomIds);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function validateStudentsByStudentIds(array $studentIds): array
  {
    if (empty($studentIds)) return [];

    $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
    $sql = "SELECT s.id, s.student_id, s.full_name, s.phone, s.gender, s.classroom_id, s.status,
            b.id as batch_id, b.status as batch_status, c.short_name as classroom_name
            FROM students s
            LEFT JOIN internship_batch_students bs ON s.id = bs.student_id
            LEFT JOIN internship_batches b ON bs.batch_id = b.id AND b.status IN ('draft', 'published')
            LEFT JOIN classrooms c ON s.classroom_id = c.id AND c.deleted_at IS NULL
            WHERE s.student_id IN ($placeholders)";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($studentIds);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getActiveTeachers(): array
  {
    $sql = "SELECT t.id, t.id as teacher_id, t.full_name, a.email, t.phone, d.full_name as department 
            FROM teachers t
            JOIN accounts a ON t.account_id = a.id
            LEFT JOIN departments d ON t.department_id = d.id
            WHERE t.deleted_at IS NULL";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getAllClassrooms(): array
  {
    $sql = "SELECT c.id, c.short_name as name, c.class_of, m.level 
            FROM classrooms c
            JOIN majors m ON c.major_id = m.id
            WHERE c.deleted_at IS NULL";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getPaginated(int $page, int $limit = 15): array
  {
    $offset = ($page - 1) * $limit;
    $sql = "SELECT * FROM internship_batches 
            WHERE deleted_at IS NULL 
            ORDER BY created_at DESC 
            LIMIT :limit OFFSET :offset";
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getPaginatedByTeacherId(int $teacherId, int $page, int $limit = 15): array
  {
    $offset = ($page - 1) * $limit;
    $sql = "SELECT ib.* FROM internship_batches ib
            JOIN internship_batch_supervisors ibs ON ib.id = ibs.batch_id
            WHERE ib.deleted_at IS NULL AND ibs.teacher_id = :teacher_id AND ib.status != 'draft'
            ORDER BY ib.created_at DESC 
            LIMIT :limit OFFSET :offset";
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':teacher_id', $teacherId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getTotalCount(): int
  {
    $sql = "SELECT COUNT(*) FROM internship_batches WHERE deleted_at IS NULL";
    $stmt = $this->db->query($sql);
    return (int)$stmt->fetchColumn();
  }

  public function getTotalCountByTeacherId(int $teacherId): int
  {
    $sql = "SELECT COUNT(*) FROM internship_batches ib
            JOIN internship_batch_supervisors ibs ON ib.id = ibs.batch_id
            WHERE ib.deleted_at IS NULL AND ibs.teacher_id = :teacher_id AND ib.status != 'draft'";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':teacher_id' => $teacherId]);
    return (int)$stmt->fetchColumn();
  }

  public function getById(int $id): ?array
  {
    $sql = "SELECT * FROM internship_batches WHERE id = :id AND deleted_at IS NULL";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':id' => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
  }

  public function getBatchStats(int $id): array
  {
    $stats = [
      'total_students' => 0,
      'total_supervisors' => 0,
      'assigned_students' => 0,
      'total_referrals' => 0,
      'pending_referrals' => 0,
      'has_submissions' => false,
      'has_grades' => false
    ];

    // Đếm sinh viên
    $sqlStudents = "SELECT COUNT(*) FROM internship_batch_students WHERE batch_id = :id";
    $stmt = $this->db->prepare($sqlStudents);
    $stmt->execute([':id' => $id]);
    $stats['total_students'] = (int)$stmt->fetchColumn();

    // Đếm giảng viên
    $sqlSupervisors = "SELECT COUNT(*) FROM internship_batch_supervisors WHERE batch_id = :id AND is_active = 1";
    $stmt = $this->db->prepare($sqlSupervisors);
    $stmt->execute([':id' => $id]);
    $stats['total_supervisors'] = (int)$stmt->fetchColumn();

    // Đếm đã phân công
    $sqlAssigned = "SELECT COUNT(DISTINCT batch_student_id) FROM internship_assignments a
                    JOIN internship_batch_students bs ON a.batch_student_id = bs.id
                    WHERE bs.batch_id = :id";
    $stmt = $this->db->prepare($sqlAssigned);
    $stmt->execute([':id' => $id]);
    $stats['assigned_students'] = (int)$stmt->fetchColumn();

    // Đếm tổng số giấy giới thiệu
    $sqlReferrals = "SELECT COUNT(*) FROM referral_letters rl
                     JOIN internship_batch_students bs ON rl.batch_student_id = bs.id
                     WHERE bs.batch_id = :id";
    $stmt = $this->db->prepare($sqlReferrals);
    $stmt->execute([':id' => $id]);
    $stats['total_referrals'] = (int)$stmt->fetchColumn();

    // Đếm số giấy giới thiệu chờ duyệt
    $sqlPendingReferrals = "SELECT COUNT(*) FROM referral_letters rl
                            JOIN internship_batch_students bs ON rl.batch_student_id = bs.id
                            WHERE bs.batch_id = :id AND rl.status = 'pending'";
    $stmt = $this->db->prepare($sqlPendingReferrals);
    $stmt->execute([':id' => $id]);
    $stats['pending_referrals'] = (int)$stmt->fetchColumn();

    // Kiểm tra bài nộp
    $sqlSubmissions = "SELECT COUNT(*) FROM internship_submissions s
                       JOIN internship_batch_students bs ON s.batch_student_id = bs.id
                       WHERE bs.batch_id = :id";
    $stmt = $this->db->prepare($sqlSubmissions);
    $stmt->execute([':id' => $id]);
    $stats['has_submissions'] = (int)$stmt->fetchColumn() > 0;

    // Kiểm tra điểm số
    $sqlGrades = "SELECT COUNT(*) FROM internship_grades g
                  JOIN internship_batch_students bs ON g.batch_student_id = bs.id
                  WHERE bs.batch_id = :id";
    $stmt = $this->db->prepare($sqlGrades);
    $stmt->execute([':id' => $id]);
    $stats['has_grades'] = (int)$stmt->fetchColumn() > 0;

    return $stats;
  }

  public function update(int $id, array $data): bool
  {
    $sql = "UPDATE internship_batches SET 
            title = :title, 
            description = :description, 
            start_at = :start_at, 
            end_at = :end_at,
            updated_at = NOW()
            WHERE id = :id";

    $stmt = $this->db->prepare($sql);
    return $stmt->execute([
      ':id' => $id,
      ':title' => $data['title'],
      ':description' => $data['description'] ?? null,
      ':start_at' => $data['start_at'],
      ':end_at' => $data['end_at']
    ]);
  }

  public function delete(int $id): bool
  {
    $sql = "UPDATE internship_batches SET deleted_at = NOW() WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([':id' => $id]);
  }

  public function updateStatus(int $id, string $status, array $extraData = []): bool
  {
    $fields = ["status = :status"];
    $params = [':id' => $id, ':status' => $status];

    if (isset($extraData['published_at'])) {
      $fields[] = "published_at = :published_at";
      $params[':published_at'] = $extraData['published_at'];
    }

    if (isset($extraData['closed_at'])) {
      $fields[] = "closed_at = :closed_at";
      $params[':closed_at'] = $extraData['closed_at'];
    }

    $sql = "UPDATE internship_batches SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute($params);
  }

  public function getBatchStudentsWithDetails(int $batchId): array
  {
    $sql = "SELECT bs.id as batch_student_id, bs.student_id, s.full_name, s.student_id as student_code, 
                   c.short_name as classroom_name, bs.status, bs.note
            FROM internship_batch_students bs
            JOIN students s ON bs.student_id = s.id
            LEFT JOIN classrooms c ON s.classroom_id = c.id
            WHERE bs.batch_id = :batch_id
            ORDER BY s.full_name ASC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':batch_id' => $batchId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getBatchSupervisorsWithDetails(int $batchId): array
  {
    $sql = "SELECT bs.id as batch_supervisor_id, bs.teacher_id, t.full_name, t.degree, 
                   t.phone, a.email, d.short_name as department_name, bs.max_students,
                   (SELECT COUNT(*) FROM internship_assignments a 
                    JOIN internship_batch_students s ON a.batch_student_id = s.id 
                    WHERE s.batch_id = :batch_id_sub AND a.teacher_id = bs.teacher_id) as assigned_count
            FROM internship_batch_supervisors bs
            JOIN teachers t ON bs.teacher_id = t.id
            LEFT JOIN accounts a ON t.account_id = a.id
            LEFT JOIN departments d ON t.department_id = d.id
            WHERE bs.batch_id = :batch_id AND bs.is_active = 1
            ORDER BY t.full_name ASC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':batch_id' => $batchId, ':batch_id_sub' => $batchId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function removeStudentFromBatch(int $batchId, int $studentId): bool
  {
    $sql = "DELETE FROM internship_batch_students WHERE batch_id = :batch_id AND student_id = :student_id";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([':batch_id' => $batchId, ':student_id' => $studentId]);
  }

  public function removeSupervisorFromBatch(int $batchId, int $teacherId): bool
  {
    $sql = "DELETE FROM internship_batch_supervisors WHERE batch_id = :batch_id AND teacher_id = :teacher_id";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([':batch_id' => $batchId, ':teacher_id' => $teacherId]);
  }

  public function updateSupervisorQuota(int $batchId, int $teacherId, int $newQuota): bool
  {
    $sql = "UPDATE internship_batch_supervisors SET max_students = :max_students, updated_at = NOW() 
            WHERE batch_id = :batch_id AND teacher_id = :teacher_id";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([
      ':batch_id' => $batchId,
      ':teacher_id' => $teacherId,
      ':max_students' => $newQuota
    ]);
  }

  public function searchEligibleStudents(int $batchId, string $query = '', ?int $classroomId = null): array
  {
    $params = [':batch_id' => $batchId];
    $where = ["s.id NOT IN (SELECT student_id FROM internship_batch_students WHERE batch_id = :batch_id)"];
    $where[] = "s.status = 'Đang học'";

    if ($query) {
      $where[] = "(s.full_name LIKE :query OR s.student_id LIKE :query)";
      $params[':query'] = "%$query%";
    }

    if ($classroomId) {
      $where[] = "s.classroom_id = :classroom_id";
      $params[':classroom_id'] = $classroomId;
    }

    $sql = "SELECT s.id, s.student_id as student_code, s.full_name, c.short_name as classroom_name
            FROM students s
            LEFT JOIN classrooms c ON s.classroom_id = c.id
            WHERE " . implode(' AND ', $where) . "
            LIMIT 50";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function searchEligibleTeachers(int $batchId, string $query = ''): array
  {
    $params = [':batch_id' => $batchId];
    $where = ["t.id NOT IN (SELECT teacher_id FROM internship_batch_supervisors WHERE batch_id = :batch_id)"];
    $where[] = "t.deleted_at IS NULL";

    if ($query) {
      $where[] = "(t.full_name LIKE :query OR t.phone LIKE :query)";
      $params[':query'] = "%$query%";
    }

    $sql = "SELECT t.id, t.full_name, t.degree, d.short_name as department_name
            FROM teachers t
            LEFT JOIN departments d ON t.department_id = d.id
            WHERE " . implode(' AND ', $where) . "
            LIMIT 50";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getBatchesByStudentId(int $studentId): array
  {
    $sql = "SELECT b.*, bs.status as student_status, bs.id as batch_student_id, 
                   bs.company_id, bs.position, bs.internship_start_date, bs.internship_end_date,
                   c.name as company_name, c.tax_code as company_tax_code, c.address as company_address, c.phone as company_phone
            FROM internship_batches b
            JOIN internship_batch_students bs ON b.id = bs.batch_id
            LEFT JOIN companies c ON bs.company_id = c.id
            WHERE bs.student_id = :student_id AND b.deleted_at IS NULL AND b.status IN ('published','closed')
            ORDER BY b.start_at DESC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':student_id' => $studentId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function updateBatchStudentCompany(int $batchStudentId, array $data): bool
  {
    $sql = "UPDATE internship_batch_students SET 
            company_id = :company_id, 
            position = :position, 
            internship_start_date = :internship_start_date, 
            internship_end_date = :internship_end_date,
            updated_at = NOW()
            WHERE id = :id";

    $stmt = $this->db->prepare($sql);
    return $stmt->execute([
      ':id' => $batchStudentId,
      ':company_id' => $data['company_id'],
      ':position' => $data['position'],
      ':internship_start_date' => $data['internship_start_date'],
      ':internship_end_date' => $data['internship_end_date']
    ]);
  }

  public function getTeacherStudentsInBatch(int $batchId, int $teacherId): array
  {
    $sql = "SELECT
              s.student_id AS student_code,
              s.full_name,
              c.short_name AS classroom_name,
              co.name AS company_name,
              sub_latest.original_file_name AS submission_name,
              sub_latest.file_path AS submission_path,
              sub_count.total_submissions AS submission_count,
              COALESCE(sub_required.required_count, 0) AS required_docs_submitted,
              g.id AS grade_id,
              g.final_score AS grade,
              g.score_reason,
              g.feedback,
              g.graded_at,
              bs.id AS batch_student_id
            FROM internship_assignments a
            JOIN internship_batch_students bs ON a.batch_student_id = bs.id
            JOIN students s ON bs.student_id = s.id
            LEFT JOIN classrooms c ON s.classroom_id = c.id
            LEFT JOIN companies co ON bs.company_id = co.id
            LEFT JOIN (
              SELECT s1.batch_student_id, s1.original_file_name, s1.file_path
              FROM internship_submissions s1
              JOIN (
                SELECT batch_student_id, MAX(id) as max_id
                FROM internship_submissions
                WHERE is_latest = 1 AND type != 'related_photo'
                GROUP BY batch_student_id
              ) s2 ON s1.id = s2.max_id
            ) sub_latest ON sub_latest.batch_student_id = bs.id
            LEFT JOIN (
              SELECT batch_student_id, COUNT(*) AS total_submissions
              FROM internship_submissions
              GROUP BY batch_student_id
            ) sub_count ON sub_count.batch_student_id = bs.id
            LEFT JOIN (
              SELECT batch_student_id, COUNT(DISTINCT type) AS required_count
              FROM internship_submissions
              WHERE is_latest = 1 AND type IN ('internship_report', 'evaluation_form')
              GROUP BY batch_student_id
            ) sub_required ON sub_required.batch_student_id = bs.id
            LEFT JOIN internship_grades g ON g.batch_student_id = bs.id
            WHERE bs.batch_id = :batch_id AND a.teacher_id = :teacher_id
            ORDER BY s.full_name ASC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      ':batch_id' => $batchId,
      ':teacher_id' => $teacherId
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getStudentGradingDetail(int $batchStudentId): ?array
  {
    $sql = "SELECT
              bs.id AS batch_student_id,
              bs.batch_id,
              s.student_id AS student_code,
              s.full_name,
              s.phone,
              c.short_name AS classroom_name,
              co.name AS company_name,
              co.address AS company_address,
              bs.position,
              bs.internship_start_date,
              bs.internship_end_date
            FROM internship_batch_students bs
            JOIN students s ON bs.student_id = s.id
            LEFT JOIN classrooms c ON s.classroom_id = c.id
            LEFT JOIN companies co ON bs.company_id = co.id
            WHERE bs.id = :batch_student_id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':batch_student_id' => $batchStudentId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
  }

  public function getTeacherBatchStats(int $batchId, int $teacherId): array
  {
    $stats = [
      'total_students' => 0,
      'has_submission' => 0,
      'has_company' => 0,
      'has_grade' => 0
    ];

    // Lấy danh sách ID sinh viên (batch_student_id) do GV này hướng dẫn trong đợt
    $sqlIds = "SELECT bs.id
               FROM internship_assignments a
               JOIN internship_batch_students bs ON a.batch_student_id = bs.id
               WHERE bs.batch_id = :batch_id AND a.teacher_id = :teacher_id";
    $stmt = $this->db->prepare($sqlIds);
    $stmt->execute([':batch_id' => $batchId, ':teacher_id' => $teacherId]);
    $batchStudentIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($batchStudentIds)) {
      return $stats;
    }

    $stats['total_students'] = count($batchStudentIds);
    $inClause = implode(',', array_map(function ($id) {
      return (int)$id;
    }, $batchStudentIds));

    // Đếm có tài liệu
    $sqlSub = "SELECT COUNT(DISTINCT batch_student_id) FROM internship_submissions WHERE batch_student_id IN ($inClause)";
    $stats['has_submission'] = (int)$this->db->query($sqlSub)->fetchColumn();

    // Đếm có công ty
    $sqlComp = "SELECT COUNT(*) FROM internship_batch_students WHERE id IN ($inClause) AND company_id IS NOT NULL";
    $stats['has_company'] = (int)$this->db->query($sqlComp)->fetchColumn();

    // Đếm có điểm
    $sqlGrade = "SELECT COUNT(DISTINCT batch_student_id) FROM internship_grades WHERE batch_student_id IN ($inClause)";
    $stats['has_grade'] = (int)$this->db->query($sqlGrade)->fetchColumn();

    return $stats;
  }

  public function isSupervisorOfBatch(int $batchId, int $teacherId): bool
  {
    $sql = "SELECT 1 FROM internship_batch_supervisors 
            WHERE batch_id = :batch_id AND teacher_id = :teacher_id AND is_active = 1";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':batch_id' => $batchId, ':teacher_id' => $teacherId]);
    return (bool)$stmt->fetchColumn();
  }

  public function getExportBatchStudents(int $batchId, array $filters = [], ?array $sort = null, array $selectedIds = []): array
  {
    $params = [':batch_id' => $batchId];
    $where = ["bs.batch_id = :batch_id"];

    if (!empty($selectedIds)) {
      $inClause = implode(',', array_map('intval', $selectedIds));
      $where[] = "bs.id IN ($inClause)";
    }

    foreach ($filters as $index => $filter) {
      if (empty($filter['value'])) continue;

      $paramKey = ":f_val_$index";
      $val = $filter['value'];

      switch ($filter['col']) {
        case 'student_code':
          $where[] = "s.student_id LIKE $paramKey";
          $params[$paramKey] = "%$val%";
          break;
        case 'student_name':
          $where[] = "s.full_name LIKE $paramKey";
          $params[$paramKey] = "%$val%";
          break;
        case 'classroom_name':
          $where[] = "c.short_name = $paramKey";
          $params[$paramKey] = $val;
          break;
        case 'company_name':
          $where[] = "co.name = $paramKey";
          $params[$paramKey] = $val;
          break;
        case 'teacher_name':
          $where[] = "t.full_name = $paramKey";
          $params[$paramKey] = $val;
          break;
      }
    }

    $orderBy = "s.full_name ASC";
    if ($sort && isset($sort['col']) && isset($sort['dir'])) {
      $dir = strtoupper($sort['dir']) === 'DESC' ? 'DESC' : 'ASC';
      switch ($sort['col']) {
        case 'student_code':
          $orderBy = "s.student_id $dir";
          break;
        case 'student_name':
          $orderBy = "s.full_name $dir";
          break;
        case 'classroom_name':
          $orderBy = "c.short_name $dir";
          break;
        case 'company_name':
          $orderBy = "co.name $dir";
          break;
        case 'grade':
          $orderBy = "ig.final_score $dir";
          break;
      }
    }

    $sql = "SELECT 
              bs.id AS batch_student_id,
              s.student_id AS student_code,
              s.full_name AS student_name,
              c.short_name AS classroom_name,
              s.phone AS student_phone,
              acc.email AS student_email,
              co.name AS company_name,
              co.tax_code AS company_tax_code,
              co.address AS company_address,
              t.full_name AS teacher_name,
              ig.final_score AS grade_score,
              ig.score_reason AS grade_reason,
              ig.feedback AS grade_feedback
            FROM internship_batch_students bs
            JOIN students s ON bs.student_id = s.id
            LEFT JOIN accounts acc ON s.account_id = acc.id
            LEFT JOIN classrooms c ON s.classroom_id = c.id
            LEFT JOIN companies co ON bs.company_id = co.id
            LEFT JOIN internship_assignments ia ON ia.batch_student_id = bs.id
            LEFT JOIN teachers t ON ia.teacher_id = t.id
            LEFT JOIN internship_grades ig ON ig.batch_student_id = bs.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY $orderBy";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
