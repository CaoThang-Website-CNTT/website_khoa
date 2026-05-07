<?php

namespace App\Stores;

use App\Core\Store;
use PDO;

class InternshipSubmissionStore extends Store
{
  public function create(array $data): int
  {
    // Mark previous submissions of the same type as not latest
    $sqlReset = "UPDATE internship_submissions 
                 SET is_latest = 0 
                 WHERE batch_student_id = :batch_student_id AND type = :type";
    $stmtReset = $this->db->prepare($sqlReset);
    $stmtReset->execute([
      ':batch_student_id' => $data['batch_student_id'],
      ':type' => $data['type']
    ]);

    $sql = "INSERT INTO internship_submissions 
            (batch_student_id, type, storage_mode, file_path, external_url, is_latest, submitted_at) 
            VALUES (:batch_student_id, :type, :storage_mode, :file_path, :external_url, 1, NOW())";
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      ':batch_student_id' => $data['batch_student_id'],
      ':type' => $data['type'],
      ':storage_mode' => $data['storage_mode'] ?? 'file',
      ':file_path' => $data['file_path'] ?? null,
      ':external_url' => $data['external_url'] ?? null
    ]);

    return (int)$this->db->lastInsertId();
  }

  public function getLatestByBatchStudent(int $batchStudentId, string $type): ?array
  {
    $sql = "SELECT * FROM internship_submissions 
            WHERE batch_student_id = :batch_student_id AND type = :type AND is_latest = 1";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':batch_student_id' => $batchStudentId, ':type' => $type]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
  }
}
