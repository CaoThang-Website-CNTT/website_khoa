<?php

namespace App\Stores;

require_once BASE_PATH . '/includes/core/store.php';
require_once BASE_PATH . '/models/internship_assignment.php';
require_once BASE_PATH . '/models/assignment_log.php';

use App\Core\Store;
use App\Models\InternshipAssignment;
use App\Models\AssignmentLog;
use PDO;

interface IInternshipAssignmentStore
{
  public function createAssignment(int $batchStudentId, int $teacherId, string $method = 'manual', ?int $assignedBy = null): ?int;
  public function getAssignmentByBatchStudentId(int $batchStudentId): ?InternshipAssignment;
  public function getAssignmentById(int $assignmentId): ?InternshipAssignment;
  public function updateAssignmentTeacher(int $assignmentId, int $newTeacherId): bool;
  public function logAction(int $assignmentId, string $action, ?int $oldTeacherId, ?int $newTeacherId, ?int $performedBy, ?string $reason): bool;
  public function getLogsByBatchStudent(int $batchStudentId): array;

  public function getBatchSupervisorsWithStats(int $batchId): array;
  public function getStudentsInBatchWithAssignment(int $batchId): array;
  public function getUnassignedStudentsInBatch(int $batchId): array;
  public function deleteAssignment(int $assignmentId): bool;
}

class InternshipAssignmentStore extends Store implements IInternshipAssignmentStore
{
  public function createAssignment(int $batchStudentId, int $teacherId, string $method = 'manual', ?int $assignedBy = null): ?int
  {
    $sql = "INSERT INTO internship_assignments (batch_student_id, teacher_id, assignment_method, assigned_by) 
            VALUES (:batch_student_id, :teacher_id, :method, :assigned_by)";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      ':batch_student_id' => $batchStudentId,
      ':teacher_id' => $teacherId,
      ':method' => $method,
      ':assigned_by' => $assignedBy
    ]);
    return $this->db->lastInsertId();
  }

  public function getAssignmentById(int $assignmentId): ?InternshipAssignment
  {
    $sql = "SELECT * FROM internship_assignments WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':id' => $assignmentId]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$data) return null;

    return new InternshipAssignment(
      id: $data['id'],
      batch_student_id: $data['batch_student_id'],
      teacher_id: $data['teacher_id'],
      assignment_method: $data['assignment_method'],
      assigned_at: $data['assigned_at'],
      assigned_by: $data['assigned_by'],
      note: $data['note'],
      created_at: $data['created_at'],
      updated_at: $data['updated_at']
    );
  }

  public function getAssignmentByBatchStudentId(int $batchStudentId): ?InternshipAssignment
  {
    $sql = "SELECT * FROM internship_assignments WHERE batch_student_id = :batch_student_id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':batch_student_id' => $batchStudentId]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$data) return null;

    return new InternshipAssignment(
      id: $data['id'],
      batch_student_id: $data['batch_student_id'],
      teacher_id: $data['teacher_id'],
      assignment_method: $data['assignment_method'],
      assigned_at: $data['assigned_at'],
      assigned_by: $data['assigned_by'],
      note: $data['note'],
      created_at: $data['created_at'],
      updated_at: $data['updated_at']
    );
  }

  public function updateAssignmentTeacher(int $assignmentId, int $newTeacherId): bool
  {
    $sql = "UPDATE internship_assignments 
            SET teacher_id = :teacher_id, updated_at = CURRENT_TIMESTAMP 
            WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':teacher_id' => $newTeacherId, ':id' => $assignmentId]);
    return $stmt->rowCount() > 0;
  }

  public function deleteAssignment(int $assignmentId): bool
  {
    $sql = "DELETE FROM internship_assignments WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':id' => $assignmentId]);
    return $stmt->rowCount() > 0;
  }


  public function logAction(int $assignmentId, string $action, ?int $oldTeacherId, ?int $newTeacherId, ?int $performedBy, ?string $reason): bool
  {
    $sql = "INSERT INTO assignment_logs (assignment_id, action, old_teacher_id, new_teacher_id, performed_by, reason) 
            VALUES (:assignment_id, :action, :old_teacher_id, :new_teacher_id, :performed_by, :reason)";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      'assignment_id' => $assignmentId,
      'action' => $action,
      'old_teacher_id' => $oldTeacherId,
      'new_teacher_id' => $newTeacherId,
      'performed_by' => $performedBy,
      'reason' => $reason
    ]);
    return $stmt->rowCount() > 0;
  }

  /**
   * Lấy lịch sử phân công theo ID sinh viên trong đợt
   */
  public function getLogsByBatchStudent(int $batchStudentId): array
  {
    $sql = "SELECT l.* 
            FROM assignment_logs l
            INNER JOIN internship_assignments a ON l.assignment_id = a.id
            WHERE a.batch_student_id = :batch_student_id
            ORDER BY l.created_at DESC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':batch_student_id' => $batchStudentId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getBatchSupervisorsWithStats(int $batchId): array
  {
    $sql = "SELECT 
              s.id as supervisor_id,
              s.teacher_id,
              s.max_students,
              t.full_name as teacher_name,
              COUNT(a.id) as current_assigned
            FROM internship_batch_supervisors s
            JOIN teachers t ON s.teacher_id = t.id
            LEFT JOIN internship_assignments a ON a.teacher_id = s.teacher_id 
              AND a.batch_student_id IN (SELECT id FROM internship_batch_students WHERE batch_id = :batch_id_sub)
            WHERE s.batch_id = :batch_id AND s.is_active = 1
            GROUP BY s.id, s.teacher_id, s.max_students, t.full_name";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      ':batch_id' => $batchId,
      ':batch_id_sub' => $batchId
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getStudentsInBatchWithAssignment(int $batchId): array
  {
    $sql = "SELECT 
              bs.id as batch_student_id,
              bs.student_id,
              s.full_name as student_name,
              s.student_id as student_code,
              s.phone as student_phone,
              c.short_name as classroom_name,
              a.id as assignment_id,
              a.teacher_id,
              t.full_name as teacher_name
            FROM internship_batch_students bs
            JOIN students s ON bs.student_id = s.id
            LEFT JOIN classrooms c ON s.classroom_id = c.id
            LEFT JOIN internship_assignments a ON bs.id = a.batch_student_id
            LEFT JOIN teachers t ON a.teacher_id = t.id
            WHERE bs.batch_id = :batch_id";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([':batch_id' => $batchId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getUnassignedStudentsInBatch(int $batchId): array
  {
    $sql = "SELECT bs.id as batch_student_id
            FROM internship_batch_students bs
            LEFT JOIN internship_assignments a ON bs.id = a.batch_student_id
            WHERE bs.batch_id = :batch_id AND a.id IS NULL";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([':batch_id' => $batchId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
