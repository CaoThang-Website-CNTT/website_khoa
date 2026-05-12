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
}
