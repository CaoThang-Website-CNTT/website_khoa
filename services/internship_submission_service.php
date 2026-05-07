<?php
namespace App\Services;

use App\Stores\InternshipSubmissionStore;

class InternshipSubmissionService
{
  private InternshipSubmissionStore $_store;

  public function __construct(InternshipSubmissionStore $store)
  {
    $this->_store = $store;
  }

  /**
   * Tạo bản ghi nộp bài
   * @param int $batchStudentId
   * @param array $data ['file_path' => string, 'type' => string, 'storage_mode' => string]
   * @return int
   */
  public function createSubmission(int $batchStudentId, array $data): int
  {
    $data['batch_student_id'] = $batchStudentId;
    return $this->_store->create($data);
  }
}
