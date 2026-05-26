<?php

namespace App\Services;

use App\Stores\ReferralLetterStore;
use App\Models\ReferralLetter;
use Exception;
use App\Core\Pageable;

interface IReferralLetterService
{
  public function create(array $data): bool;
  public function getByBatchStudentId(int $batchStudentId): array;
  public function getLettersWithCompanyByBatchStudentId(int $batchStudentId): array;
  public function getById(int $id): ?ReferralLetter;
  public function getByIdWithCompany(int $id): ?array;
  public function cancel(int $id, string $reason = ''): bool;
  public function updateCompany(int $id, int $companyId): bool;
  public function getAllWithDetailsByBatchId(int $batchId): array;
  public function bulkApprove(array $ids, int $processedBy): int;
  public function bulkCancel(array $ids, string $reason, int $processedBy): int;
}

class ReferralLetterService implements IReferralLetterService
{
  private ReferralLetterStore $_store;

  public function __construct(ReferralLetterStore $store)
  {
    $this->_store = $store;
  }

  public function create(array $data): bool
  {
    return $this->_store->create($data) > 0;
  }

  public function getByBatchStudentId(int $batchStudentId): array
  {
    return $this->_store->getAllByBatchStudentId($batchStudentId);
  }

  public function getLettersWithCompanyByBatchStudentId(int $batchStudentId): array
  {
    return $this->_store->getLettersWithCompanyByBatchStudentId($batchStudentId);
  }

  public function getById(int $id): ?ReferralLetter
  {
    return $this->_store->getById($id);
  }

  public function getByIdWithCompany(int $id): ?array
  {
    return $this->_store->getByIdWithCompany($id);
  }

  public function cancel(int $id, string $reason = ''): bool
  {
    $letter = $this->_store->getById($id);
    if (!$letter) {
      throw new Exception('Không tìm thấy giấy giới thiệu');
    }
    if ($letter->status !== 'pending') {
      throw new Exception('Chỉ có thể hủy giấy giới thiệu đang chờ xử lý');
    }

    return $this->_store->updateStatus($id, 'cancelled', ['cancel_reason' => $reason]);
  }

  public function updateCompany(int $id, int $companyId): bool
  {
    $letter = $this->_store->getById($id);
    if (!$letter) {
      throw new Exception('Không tìm thấy giấy giới thiệu');
    }
    if ($letter->status !== 'pending') {
      throw new Exception('Chỉ có thể cập nhật thông tin khi giấy giới thiệu đang chờ xử lý');
    }

    return $this->_store->updateCompanyId($id, $companyId);
  }

  public function getAllWithDetailsByBatchId(int $batchId): array
  {
    return $this->_store->getAllWithDetailsByBatchId($batchId);
  }

  public function bulkApprove(array $ids, int $processedBy): int
  {
    if (empty($ids)) return 0;
    
    $letters = $this->_store->getByIds($ids);
    $processedCount = 0;
    
    foreach ($letters as $letter) {
      if ($letter->status !== 'pending') {
        throw new Exception("Giấy giới thiệu #{$letter->id} không ở trạng thái chờ xử lý, không thể duyệt.");
      }
    }
    
    foreach ($letters as $letter) {
      $success = $this->_store->updateStatus($letter->id, 'printed', [
        'printed_at' => date('Y-m-d H:i:s'),
        'processed_by' => $processedBy
      ]);
      if ($success) {
        $processedCount++;
      }
    }
    
    return $processedCount;
  }

  public function bulkCancel(array $ids, string $reason, int $processedBy): int
  {
    if (empty($ids)) return 0;
    
    $letters = $this->_store->getByIds($ids);
    $processedCount = 0;
    
    foreach ($letters as $letter) {
      if ($letter->status !== 'pending') {
        throw new Exception("Giấy giới thiệu #{$letter->id} không ở trạng thái chờ xử lý, không thể hủy.");
      }
    }
    
    foreach ($letters as $letter) {
      $success = $this->_store->updateStatus($letter->id, 'cancelled', [
        'cancel_reason' => $reason,
        'processed_by' => $processedBy
      ]);
      if ($success) {
        $processedCount++;
      }
    }
    
    return $processedCount;
  }
}
