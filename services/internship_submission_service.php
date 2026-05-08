<?php

namespace App\Services;

use App\Models\InternshipSubmission;
use App\Stores\InternshipSubmissionStore;

interface IInternshipSubmissionService
{
  public function createSubmission(int $batchStudentId, array $data): int;
  public function getAllByBatchStudentId(int $batchStudentId): ?array;
}
class InternshipSubmissionService implements IInternshipSubmissionService
{
  private InternshipSubmissionStore $_store;

  public function __construct(InternshipSubmissionStore $store)
  {
    $this->_store = $store;
  }

  /**
   * Tạo bản ghi nộp bài
   * @param int $batchStudentId
   * @param array $data ['file_path' => string, 'storage_mode' => string]
   * @return int
   */
  public function createSubmission(int $batchStudentId, array $data): int
  {
    $data['batch_student_id'] = $batchStudentId;
    return $this->_store->create($data);
  }

  public function getAllByBatchStudentId(int $batchStudentId): ?array
  {
    return $this->_store->getAllByBatchStudentId($batchStudentId);
  }
}
