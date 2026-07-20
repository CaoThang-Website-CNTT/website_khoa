<?php

namespace App\Stores;

use App\Core\AppTime;

use App\Core\Store;
use App\Core\Schema\QueryBuilder;
use App\Core\Schema\Compiler\MySQLCompiler;
use PDO;

interface IInternshipSubmissionStore
{
  public function getById(int $id): ?array;
  public function getAllByBatchStudentId(int $batchStudentId): ?array;
  public function create(array $data): int;
  public function getLatestByBatchStudent(int $batchStudentId): ?array;
  public function createWithType(array $data): int;
  public function getLatestByBatchStudentGroupedByType(int $batchStudentId): array;
  public function getAllByBatchStudentAndType(int $batchStudentId, string $type): array;
}

class InternshipSubmissionStore extends Store implements IInternshipSubmissionStore
{
  public function getById(int $id): ?array
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('internship_submissions')->select('*')->eq('id', $id);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
  }

  public function create(array $data): int
  {
    // Đánh dấu các file nộp trước đó là đã cũ
    $query = (new QueryBuilder(new MySQLCompiler()))->from('internship_submissions')
      ->update(['is_latest' => 0])->eq('batch_student_id', $data['batch_student_id']);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    $query = (new QueryBuilder(new MySQLCompiler()))->from('internship_submissions')->insert([
      'batch_student_id' => $data['batch_student_id'], 'original_file_name' => $data['original_file_name'],
      'storage_mode' => $data['storage_mode'] ?? 'file', 'file_path' => $data['file_path'] ?? null,
      'external_url' => $data['external_url'] ?? null, 'is_latest' => 1,
      'submitted_at' => AppTime::now()->format('Y-m-d H:i:s'),
    ]);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return (int)$this->db->lastInsertId();
  }

  public function getLatestByBatchStudent(int $batchStudentId): ?array
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('internship_submissions')
      ->select('*')->eq('batch_student_id', $batchStudentId)->eq('is_latest', 1);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
  }

  public function getAllByBatchStudentId(int $batchStudentId): ?array
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('internship_submissions')
      ->select('*')->eq('batch_student_id', $batchStudentId)->order('submitted_at', ['ascending' => false]);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result ?: null;
  }

  public function createWithType(array $data): int
  {
    // Reset is_latest for the specific type
    $query = (new QueryBuilder(new MySQLCompiler()))->from('internship_submissions')->update(['is_latest' => 0])
      ->eq('batch_student_id', $data['batch_student_id'])->eq('type', $data['type']);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    // Insert new submission
    $query = (new QueryBuilder(new MySQLCompiler()))->from('internship_submissions')->insert([
      'batch_student_id' => $data['batch_student_id'], 'type' => $data['type'],
      'original_file_name' => $data['original_file_name'], 'mime_type' => $data['mime_type'] ?? null,
      'storage_mode' => $data['storage_mode'] ?? 'file', 'file_path' => $data['file_path'] ?? null,
      'external_url' => $data['external_url'] ?? null, 'is_latest' => 1,
      'submitted_at' => AppTime::now()->format('Y-m-d H:i:s'),
    ]);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return (int)$this->db->lastInsertId();
  }

  public function getLatestByBatchStudentGroupedByType(int $batchStudentId): array
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('internship_submissions')
      ->select('*')->eq('batch_student_id', $batchStudentId)->eq('is_latest', 1)->order('type');
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    if ($rows) {
      foreach ($rows as $row) {
        $result[$row['type']] = $row;
      }
    }
    return $result;
  }

  public function getAllByBatchStudentAndType(int $batchStudentId, string $type): array
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('internship_submissions')
      ->select('*')->eq('batch_student_id', $batchStudentId)
      ->eq('type', $type)->order('submitted_at', ['ascending' => false]);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result ?: [];
  }
}
