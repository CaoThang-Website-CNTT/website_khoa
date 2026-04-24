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
}