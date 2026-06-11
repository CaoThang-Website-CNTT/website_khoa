<?php

namespace App\Stores;

use App\Core\Store;

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
    $sql = "SELECT g.*, t.full_name AS graded_by_name
            FROM internship_grades g
            LEFT JOIN teachers t ON g.graded_by = t.id
            WHERE g.batch_student_id = :batch_student_id";

    $stmt = $this->db->prepare($sql);
    $stmt->execute(['batch_student_id' => $batchStudentId]);

    $result = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $result ?: null;
  }

  public function create(array $data): int
  {
    $sql = "INSERT INTO internship_grades
            (batch_student_id, final_score, score_reason, feedback, graded_at, graded_by)
            VALUES (:batch_student_id, :final_score, :score_reason, :feedback, NOW(), :graded_by)";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      'batch_student_id' => $data['batch_student_id'],
      'final_score' => $data['final_score'],
      'score_reason' => $data['score_reason'] ?? null,
      'feedback' => $data['feedback'] ?? null,
      'graded_by' => $data['graded_by']
    ]);

    return (int)$this->db->lastInsertId();
  }

  public function update(int $id, array $data): bool
  {
    $sql = "UPDATE internship_grades
            SET final_score = :final_score, 
                score_reason = :score_reason, 
                feedback = :feedback,
                graded_at = NOW(), 
                graded_by = :graded_by, 
                updated_at = NOW()
            WHERE id = :id";

    $stmt = $this->db->prepare($sql);
    return $stmt->execute([
      'id' => $id,
      'final_score' => $data['final_score'],
      'score_reason' => $data['score_reason'] ?? null,
      'feedback' => $data['feedback'] ?? null,
      'graded_by' => $data['graded_by']
    ]);
  }
}
