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
    ]);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return (int)$this->db->lastInsertId();
  }

  public function update(int $id, array $data): bool
  {
    $now = date('Y-m-d H:i:s');
    $query = (new QueryBuilder(new MySQLCompiler()))->from('internship_grades')->update([
      'final_score' => $data['final_score'],
      'score_reason' => $data['score_reason'] ?? null,
      'feedback' => $data['feedback'] ?? null,
      'graded_at' => $now, 'graded_by' => $data['graded_by'], 'updated_at' => $now,
    ])->eq('id', $id);
    $stmt = $this->db->prepare($query->toSql());
    return $stmt->execute($query->getBindings());
  }
}
