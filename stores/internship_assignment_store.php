<?php

namespace App\Stores;

require_once BASE_PATH . '/includes/core/store.php';
require_once BASE_PATH . '/models/internship_assignment.php';
require_once BASE_PATH . '/models/assignment_log.php';

use App\Core\Store;
use App\Core\Schema\QueryBuilder;
use App\Core\Schema\Compiler\MySQLCompiler;
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
  public function getMailingDetails(int $batchStudentId, ?int $oldTeacherId, ?int $newTeacherId): array;
  public function getStudentsInBatchWithAssignment(int $batchId): array;
  public function getStudentsInBatchWithAssignmentPaginated(int $batchId, int $page, int $limit, string $search, array $filters, array $sort): array;
  public function getTotalStudentsInBatchWithAssignmentCount(int $batchId, string $search, array $filters): int;
  public function getUnassignedStudentsInBatch(int $batchId): array;
  public function deleteAssignment(int $assignmentId): bool;
}

class InternshipAssignmentStore extends Store implements IInternshipAssignmentStore
{
  public function createAssignment(int $batchStudentId, int $teacherId, string $method = 'manual', ?int $assignedBy = null): ?int
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('internship_assignments')->insert([
      'batch_student_id' => $batchStudentId,
      'teacher_id' => $teacherId,
      'assignment_method' => $method,
      'assigned_by' => $assignedBy,
    ]);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    return (int)$this->db->lastInsertId();
  }

  public function getAssignmentById(int $assignmentId): ?InternshipAssignment
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('internship_assignments')->select('*')->eq('id', $assignmentId);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
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
    $query = (new QueryBuilder(new MySQLCompiler()))->from('internship_assignments')->select('*')->eq('batch_student_id', $batchStudentId);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
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
    $query = (new QueryBuilder(new MySQLCompiler()))->from('internship_assignments')->update([
      'teacher_id' => $newTeacherId,
      'updated_at' => date('Y-m-d H:i:s'),
    ])->eq('id', $assignmentId);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    return $stmt->rowCount() > 0;
  }

  public function deleteAssignment(int $assignmentId): bool
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('internship_assignments')->delete()->eq('id', $assignmentId);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    return $stmt->rowCount() > 0;
  }


  public function logAction(int $assignmentId, string $action, ?int $oldTeacherId, ?int $newTeacherId, ?int $performedBy, ?string $reason): bool
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('assignment_logs')->insert([
      'assignment_id' => $assignmentId,
      'action' => $action,
      'old_teacher_id' => $oldTeacherId,
      'new_teacher_id' => $newTeacherId,
      'performed_by' => $performedBy,
      'reason' => $reason
    ]);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
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
              t.full_name as teacher_name,
              c_info.name as company_name,
              c_info.tax_code as company_tax_code,
              c_info.address as company_address
            FROM internship_batch_students bs
            JOIN students s ON bs.student_id = s.id
            LEFT JOIN classrooms c ON s.classroom_id = c.id
            LEFT JOIN internship_assignments a ON bs.id = a.batch_student_id
            LEFT JOIN teachers t ON a.teacher_id = t.id
            LEFT JOIN companies c_info ON bs.company_id = c_info.id
            WHERE bs.batch_id = :batch_id";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([':batch_id' => $batchId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getStudentsInBatchWithAssignmentPaginated(int $batchId, int $page, int $limit, string $search, array $filters, array $sort): array
  {
    $offset = (max(1, $page) - 1) * $limit;

    $sql = "SELECT 
              bs.id as batch_student_id,
              bs.student_id,
              s.full_name as student_name,
              s.student_id as student_code,
              s.phone as student_phone,
              c.short_name as classroom_name,
              a.id as assignment_id,
              a.teacher_id,
              t.full_name as teacher_name,
              c_info.name as company_name,
              c_info.tax_code as company_tax_code,
              c_info.address as company_address
            FROM internship_batch_students bs
            JOIN students s ON bs.student_id = s.id
            LEFT JOIN classrooms c ON s.classroom_id = c.id
            LEFT JOIN internship_assignments a ON bs.id = a.batch_student_id
            LEFT JOIN teachers t ON a.teacher_id = t.id
            LEFT JOIN companies c_info ON bs.company_id = c_info.id
            WHERE bs.batch_id = :batch_id";

    $params = [':batch_id' => $batchId];

    if ($search !== '%' && $search !== '%%') {
      $sql .= " AND (s.student_id LIKE :search1 OR s.full_name LIKE :search2)";
      $params[':search1'] = $search;
      $params[':search2'] = $search;
    }

    foreach ($filters as $index => $filter) {
      if (!isset($filter['col']) || !isset($filter['value'])) continue;
      $colName = $filter['col'];
      $op = $filter['op'] ?? '=';
      $val = $filter['value'];
      $paramKey = ":filter_$index";

      $dbCol = '';
      if ($colName === 'student_code') $dbCol = 's.student_id';
      elseif ($colName === 'student_name') $dbCol = 's.full_name';
      elseif ($colName === 'classroom_name') $dbCol = 'c.short_name';
      elseif ($colName === 'company_name') {
        if ($val === 'unassign') {
          $sql .= $op === '!=' ? " AND bs.company_id IS NOT NULL" : " AND bs.company_id IS NULL";
          continue;
        }
        $dbCol = 'c_info.name';
      } elseif ($colName === 'teacher_name') {
        if ($val === 'unassign' || empty($val)) {
          $sql .= $op === '!=' ? " AND a.teacher_id IS NOT NULL" : " AND a.teacher_id IS NULL";
          continue;
        }
        $dbCol = 't.full_name';
      } else continue;

      if ($op === 'contains') {
        $sql .= " AND $dbCol LIKE $paramKey";
        $params[$paramKey] = "%$val%";
      } elseif ($op === '=') {
        $sql .= " AND $dbCol = $paramKey";
        $params[$paramKey] = $val;
      } elseif ($op === '!=') {
        $sql .= " AND $dbCol != $paramKey";
        $params[$paramKey] = $val;
      } elseif (in_array($op, ['>', '>=', '<', '<='])) {
        $sql .= " AND $dbCol $op $paramKey";
        $params[$paramKey] = $val;
      }
    }

    if (!empty($sort) && isset($sort['col']) && isset($sort['dir'])) {
      $col = $sort['col'];
      $dir = strtoupper($sort['dir']) === 'ASC' ? 'ASC' : 'DESC';
      $allowedCols = [
        'student_code' => 's.student_id',
        'student_name' => 's.full_name',
        'classroom_name' => 'c.short_name',
        'company_name' => 'c_info.name',
        'teacher_name' => 't.full_name'
      ];
      if (isset($allowedCols[$col])) {
        $sql .= " ORDER BY " . $allowedCols[$col] . " $dir";
      } else {
        $sql .= " ORDER BY s.student_id ASC";
      }
    } else {
      $sql .= " ORDER BY s.student_id ASC";
    }

    $sql .= " LIMIT :limit OFFSET :offset";

    $stmt = $this->db->prepare($sql);
    foreach ($params as $key => $value) {
      $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getTotalStudentsInBatchWithAssignmentCount(int $batchId, string $search, array $filters): int
  {
    $sql = "SELECT COUNT(bs.id)
            FROM internship_batch_students bs
            JOIN students s ON bs.student_id = s.id
            LEFT JOIN classrooms c ON s.classroom_id = c.id
            LEFT JOIN internship_assignments a ON bs.id = a.batch_student_id
            LEFT JOIN teachers t ON a.teacher_id = t.id
            LEFT JOIN companies c_info ON bs.company_id = c_info.id
            WHERE bs.batch_id = :batch_id";

    $params = [':batch_id' => $batchId];

    if ($search !== '%' && $search !== '%%') {
      $sql .= " AND (s.student_id LIKE :search1 OR s.full_name LIKE :search2)";
      $params[':search1'] = $search;
      $params[':search2'] = $search;
    }

    foreach ($filters as $index => $filter) {
      if (!isset($filter['col']) || !isset($filter['value'])) continue;
      $colName = $filter['col'];
      $op = $filter['op'] ?? '=';
      $val = $filter['value'];
      $paramKey = ":filter_$index";

      $dbCol = '';
      if ($colName === 'student_code') $dbCol = 's.student_id';
      elseif ($colName === 'student_name') $dbCol = 's.full_name';
      elseif ($colName === 'classroom_name') $dbCol = 'c.short_name';
      elseif ($colName === 'company_name') {
        if ($val === 'unassign') {
          $sql .= $op === '!=' ? " AND bs.company_id IS NOT NULL" : " AND bs.company_id IS NULL";
          continue;
        }
        $dbCol = 'c_info.name';
      } elseif ($colName === 'teacher_name') {
        if ($val === 'unassign' || empty($val)) {
          $sql .= $op === '!=' ? " AND a.teacher_id IS NOT NULL" : " AND a.teacher_id IS NULL";
          continue;
        }
        $dbCol = 't.full_name';
      } else continue;

      if ($op === 'contains') {
        $sql .= " AND $dbCol LIKE $paramKey";
        $params[$paramKey] = "%$val%";
      } elseif ($op === '=') {
        $sql .= " AND $dbCol = $paramKey";
        $params[$paramKey] = $val;
      } elseif ($op === '!=') {
        $sql .= " AND $dbCol != $paramKey";
        $params[$paramKey] = $val;
      } elseif (in_array($op, ['>', '>=', '<', '<='])) {
        $sql .= " AND $dbCol $op $paramKey";
        $params[$paramKey] = $val;
      }
    }

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
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

  public function getMailingDetails(int $batchStudentId, ?int $oldTeacherId, ?int $newTeacherId): array
  {
    $result = [
      'student' => null,
      'old_teacher' => null,
      'new_teacher' => null,
    ];

    $sqlStudent = "SELECT s.full_name AS name, s.student_id AS mssv, a.email,
                          c.short_name AS class_name, ib.title AS batch_title,
                          ib.start_at, ib.end_at, ib.status AS batch_status
                   FROM internship_batch_students bs
                   JOIN students s ON bs.student_id = s.id
                   LEFT JOIN classrooms c ON s.classroom_id = c.id
                   JOIN accounts a ON s.account_id = a.id
                   JOIN internship_batches ib ON bs.batch_id = ib.id
                   WHERE bs.id = :batch_student_id";
    $stmt = $this->db->prepare($sqlStudent);
    $stmt->execute([':batch_student_id' => $batchStudentId]);
    $result['student'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    $sqlTeacher = "SELECT t.full_name AS name, a.email
                   FROM teachers t
                   JOIN accounts a ON t.account_id = a.id
                   WHERE t.id = :teacher_id";
    $stmtTeacher = $this->db->prepare($sqlTeacher);

    if ($oldTeacherId) {
      $stmtTeacher->execute([':teacher_id' => $oldTeacherId]);
      $result['old_teacher'] = $stmtTeacher->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    if ($newTeacherId) {
      $stmtTeacher->execute([':teacher_id' => $newTeacherId]);
      $result['new_teacher'] = $stmtTeacher->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    return $result;
  }
}
