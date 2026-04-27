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
      ':class_of' => $data['class_of'],
      ':level' => $data['level'],
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
            LEFT JOIN internship_batches b ON bs.batch_id = b.id AND b.status IN ('draft', 'public')
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
            LEFT JOIN internship_batches b ON bs.batch_id = b.id AND b.status IN ('draft', 'public')
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
            LEFT JOIN internship_batches b ON bs.batch_id = b.id AND b.status IN ('draft', 'public')
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

  public function getTotalCount(): int
  {
    $sql = "SELECT COUNT(*) FROM internship_batches WHERE deleted_at IS NULL";
    $stmt = $this->db->query($sql);
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
}