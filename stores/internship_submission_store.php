<?php

namespace App\Stores;

use App\Core\Store;
use App\Models\InternshipSubmission;
use App\Core\Schema\QueryBuilder;
use App\Core\Schema\Compiler\MySQLCompiler;
use PDO;

interface IInternshipSubmissionStore
{
  /** @return InternshipSubmission[] */
  public function getAllByBatchStudentId(int $batchStudentId): ?array;
  /** @return int */
  public function create(array $data): int;
  public function getLatestByBatchStudent(int $batchStudentId): ?array;
}
class InternshipSubmissionStore extends Store implements IInternshipSubmissionStore
{
  public function create(array $data): int
  {
    // Đánh dấu các file nộp trước đó là đã cũ
    $sqlReset = "UPDATE internship_submissions 
                 SET is_latest = 0 
                 WHERE batch_student_id = :batch_student_id";
    $stmtReset = $this->db->prepare($sqlReset);
    $stmtReset->execute([
      ':batch_student_id' => $data['batch_student_id']
    ]);

    $sql = "INSERT INTO internship_submissions 
            (batch_student_id, original_file_name, storage_mode, file_path, external_url, is_latest, submitted_at) 
            VALUES (:batch_student_id, :original_file_name, :storage_mode, :file_path, :external_url, 1, NOW())";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      ':batch_student_id' => $data['batch_student_id'],
      ':original_file_name' => $data['original_file_name'],
      ':storage_mode' => $data['storage_mode'] ?? 'file',
      ':file_path' => $data['file_path'] ?? null,
      ':external_url' => $data['external_url'] ?? null
    ]);

    return (int)$this->db->lastInsertId();
  }

  public function getLatestByBatchStudent(int $batchStudentId): ?array
  {
    $sql = "SELECT * FROM internship_submissions 
            WHERE batch_student_id = :batch_student_id AND is_latest = 1";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':batch_student_id' => $batchStudentId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
  }

  public function getAllByBatchStudentId(int $batchStudentId): ?array
  {
    $sql = "SELECT * FROM internship_submissions 
            WHERE batch_student_id = :batch_student_id
            ORDER BY submitted_at DESC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':batch_student_id' => $batchStudentId]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result ?: null;
  }
}
