<?php

namespace App\Stores;

use App\Core\Store;
use App\Core\Schema\QueryBuilder;
use App\Core\Schema\Compiler\MySQLCompiler;

interface IInternshipGradeStore
{
  public function getByBatchStudentId(int $batchStudentId): ?array;
  public function create(array $data): int;
  public function update(int $id, array $data): bool;
  public function publishAllByTeacher(int $batchId, int $teacherId): int;
}

class InternshipGradeStore extends Store implements IInternshipGradeStore
{

  public function getByBatchStudentId(int $batchStudentId): ?array
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('internship_grades')
      ->select('internship_grades.*', 'teachers.full_name AS graded_by_name')
      ->leftJoin('teachers', 'internship_grades.graded_by', '=', 'teachers.id')
      ->eq('internship_grades.batch_student_id', $batchStudentId);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    $result = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $result ?: null;
  }

  public function create(array $data): int
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('internship_grades')->insert([
      'batch_student_id' => $data['batch_student_id'],
      'final_score' => $data['final_score'],
      'score_reason' => $data['score_reason'] ?? null,
      'feedback' => $data['feedback'] ?? null,
      'graded_at' => date('Y-m-d H:i:s'), 'graded_by' => $data['graded_by'],
      'grade_lock_at' => $data['grade_lock_at'] ?? null
    ]);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return (int)$this->db->lastInsertId();
  }

  public function update(int $id, array $data): bool
  {
    $now = date('Y-m-d H:i:s');
    $updateData = [
      'final_score' => $data['final_score'],
      'score_reason' => $data['score_reason'] ?? null,
      'feedback' => $data['feedback'] ?? null,
      'graded_at' => $now, 'graded_by' => $data['graded_by'], 'updated_at' => $now,
    ];
    if (array_key_exists('grade_lock_at', $data)) {
        $updateData['grade_lock_at'] = $data['grade_lock_at'];
    }
    
    $query = (new QueryBuilder(new MySQLCompiler()))->from('internship_grades')->update($updateData)->eq('id', $id);
    $stmt = $this->db->prepare($query->toSql());
    return $stmt->execute($query->getBindings());
  }

  public function publishAllByTeacher(int $batchId, int $teacherId): int
  {
    $sql = "UPDATE internship_grades ig
            JOIN internship_batch_students bs ON ig.batch_student_id = bs.id
            JOIN internship_assignments ia ON ia.batch_student_id = bs.id
            SET ig.grade_lock_at = NOW()
            WHERE bs.batch_id = :batch_id 
              AND ia.teacher_id = :teacher_id
              AND ig.final_score IS NOT NULL 
              AND ig.grade_lock_at IS NULL";
              
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
        'batch_id' => $batchId,
        'teacher_id' => $teacherId
    ]);
    
    return $stmt->rowCount();
  }
}
