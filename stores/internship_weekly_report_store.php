<?php

namespace App\Stores;

use App\Core\Store;
use App\Core\Schema\QueryBuilder;
use App\Core\Schema\Compiler\MySQLCompiler;
use App\Core\AppTime;
use PDO;

interface IInternshipWeeklyReportStore
{
  public function getLatestByBatchStudentAndWeek(int $batchStudentId, int $weekNumber): ?array;
  public function getLatestByBatchStudent(int $batchStudentId): array;
  public function getByBatchAndTeacher(int $batchId, int $teacherId, int $weekNumber): array;
  public function getPaginatedByBatchAndTeacher(int $batchId, int $teacherId, int $weekNumber, int $page, int $limit = 15): array;
  public function countByBatchAndTeacher(int $batchId, int $teacherId, int $weekNumber): int;
  public function create(array $data): int;
  public function resetLatest(int $batchStudentId, int $weekNumber): void;
  public function getImagesByReportId(int $reportId): array;
  public function addImage(array $data): int;
  public function countSubmittedWeeks(int $batchStudentId): int;
  public function getHistoryByBatchStudentAndWeek(int $batchStudentId, int $weekNumber): array;
  public function updateTeacherFeedback(int $reportId, int $isSeen, ?string $feedback): void;
  public function markMultipleAsSeen(array $reportIds): int;
  
  // Transactions
  public function beginTransaction(): void;
  public function commit(): void;
  public function rollBack(): void;
}

class InternshipWeeklyReportStore extends Store implements IInternshipWeeklyReportStore
{
  public function getLatestByBatchStudentAndWeek(int $batchStudentId, int $weekNumber): ?array
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('internship_weekly_reports')
      ->select('*')
      ->eq('batch_student_id', $batchStudentId)
      ->eq('week_number', $weekNumber)
      ->eq('is_latest', 1);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result ?: null;
  }

  public function getLatestByBatchStudent(int $batchStudentId): array
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('internship_weekly_reports')
      ->select('*')
      ->eq('batch_student_id', $batchStudentId)
      ->eq('is_latest', 1)
      ->order('week_number', ['ascending' => true]);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $result ?: [];
  }

  public function getByBatchAndTeacher(int $batchId, int $teacherId, int $weekNumber): array
  {
    $sql = "SELECT 
              ibs.id AS batch_student_id,
              s.student_id AS student_code,
              s.full_name,
              c.short_name AS classroom_name,
              wr.id AS report_id,
              wr.content,
              wr.is_late,
              wr.is_exempt,
              wr.submitted_at,
              wr.is_seen_by_teacher,
              wr.teacher_feedback,
              wr.teacher_interacted_at,
              (SELECT COUNT(*) FROM internship_weekly_report_images WHERE weekly_report_id = wr.id) AS image_count
            FROM internship_batch_students ibs
            JOIN students s ON s.id = ibs.student_id
            LEFT JOIN classrooms c ON c.id = s.classroom_id
            JOIN internship_assignments ia ON ia.batch_student_id = ibs.id
            LEFT JOIN internship_weekly_reports wr 
              ON wr.batch_student_id = ibs.id 
              AND wr.week_number = :week_number
              AND wr.is_latest = 1
            WHERE ibs.batch_id = :batch_id
              AND ia.teacher_id = :teacher_id
            ORDER BY s.full_name ASC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      'week_number' => $weekNumber,
      'batch_id' => $batchId,
      'teacher_id' => $teacherId
    ]);

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result ?: [];
  }

  public function getPaginatedByBatchAndTeacher(int $batchId, int $teacherId, int $weekNumber, int $page = 1, int $limit = 15): array
  {
    $offset = ($page - 1) * $limit;
    $sql = "SELECT 
              ibs.id AS batch_student_id,
              s.student_id AS student_code,
              s.full_name,
              c.short_name AS classroom_name,
              wr.id AS report_id,
              wr.content,
              wr.is_late,
              wr.is_exempt,
              wr.submitted_at,
              wr.is_seen_by_teacher,
              wr.teacher_feedback,
              wr.teacher_interacted_at,
              (SELECT COUNT(*) FROM internship_weekly_report_images WHERE weekly_report_id = wr.id) AS image_count
            FROM internship_batch_students ibs
            JOIN students s ON s.id = ibs.student_id
            LEFT JOIN classrooms c ON s.classroom_id = c.id
            JOIN internship_assignments ia ON ia.batch_student_id = ibs.id
            LEFT JOIN internship_weekly_reports wr 
              ON wr.batch_student_id = ibs.id 
              AND wr.week_number = :week_number
              AND wr.is_latest = 1
            WHERE ibs.batch_id = :batch_id
              AND ia.teacher_id = :teacher_id
            ORDER BY s.full_name ASC
            LIMIT :limit OFFSET :offset";

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':week_number', $weekNumber, PDO::PARAM_INT);
    $stmt->bindValue(':batch_id', $batchId, PDO::PARAM_INT);
    $stmt->bindValue(':teacher_id', $teacherId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result ?: [];
  }

  public function countByBatchAndTeacher(int $batchId, int $teacherId, int $weekNumber): int
  {
    $sql = "SELECT COUNT(ibs.id)
            FROM internship_batch_students ibs
            JOIN internship_assignments ia ON ia.batch_student_id = ibs.id
            WHERE ibs.batch_id = :batch_id
              AND ia.teacher_id = :teacher_id";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      'batch_id' => $batchId,
      'teacher_id' => $teacherId
    ]);

    return (int)$stmt->fetchColumn();
  }

  public function create(array $data): int
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('internship_weekly_reports')->insert([
      'batch_student_id' => $data['batch_student_id'],
      'week_number' => $data['week_number'],
      'week_start' => $data['week_start'],
      'week_end' => $data['week_end'],
      'content' => $data['content'] ?? null,
      'is_exempt' => $data['is_exempt'] ?? 0,
      'no_activity_reason' => $data['no_activity_reason'] ?? null,
      'no_activity_note' => $data['no_activity_note'] ?? null,
      'is_late' => $data['is_late'] ?? 0,
      'is_latest' => 1,
      'submitted_at' => date('Y-m-d H:i:s')
    ]);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return (int)$this->db->lastInsertId();
  }

  public function resetLatest(int $batchStudentId, int $weekNumber): void
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('internship_weekly_reports')
      ->update(['is_latest' => 0])
      ->eq('batch_student_id', $batchStudentId)
      ->eq('week_number', $weekNumber);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
  }

  public function getImagesByReportId(int $reportId): array
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('internship_weekly_report_images')
      ->select('*')
      ->eq('weekly_report_id', $reportId)
      ->order('id', ['ascending' => true]);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $result ?: [];
  }

  public function addImage(array $data): int
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('internship_weekly_report_images')->insert([
      'weekly_report_id' => $data['weekly_report_id'],
      'original_file_name' => $data['original_file_name'],
      'mime_type' => $data['mime_type'] ?? null,
      'file_path' => $data['file_path'],
      'file_size' => $data['file_size'] ?? null,
    ]);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return (int)$this->db->lastInsertId();
  }

  public function countSubmittedWeeks(int $batchStudentId): int
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('internship_weekly_reports')
      ->select('COUNT(id) as total')
      ->eq('batch_student_id', $batchStudentId)
      ->eq('is_latest', 1);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result ? (int)$result['total'] : 0;
  }

  public function getHistoryByBatchStudentAndWeek(int $batchStudentId, int $weekNumber): array
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('internship_weekly_reports')
      ->select('*')
      ->eq('batch_student_id', $batchStudentId)
      ->eq('week_number', $weekNumber)
      ->order('submitted_at', ['ascending' => false])
      ->order('id', ['ascending' => false]);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $result ?: [];
  }
  public function updateTeacherFeedback(int $reportId, int $isSeen, ?string $feedback): void
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('internship_weekly_reports')
      ->update([
        'is_seen_by_teacher' => $isSeen,
        'teacher_feedback' => $feedback,
        'teacher_interacted_at' => AppTime::now()->format('Y-m-d H:i:s')
      ])
      ->eq('id', $reportId);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
  }

  public function markMultipleAsSeen(array $reportIds): int
  {
    if (empty($reportIds)) return 0;
    
    $query = (new QueryBuilder(new MySQLCompiler()))->from('internship_weekly_reports')
      ->update([
        'is_seen_by_teacher' => 1,
        'teacher_interacted_at' => date('Y-m-d H:i:s')
      ])
      ->in('id', $reportIds)
      ->eq('is_seen_by_teacher', 0);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    
    return $stmt->rowCount();
  }

  public function beginTransaction(): void
  {
    $this->db->beginTransaction();
  }

  public function commit(): void
  {
    $this->db->commit();
  }

  public function rollBack(): void
  {
    $this->db->rollBack();
  }
}
