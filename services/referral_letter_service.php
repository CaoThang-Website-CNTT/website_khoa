<?php

namespace App\Services;

use App\Stores\ReferralLetterStore;
use App\Models\ReferralLetter;
use Exception;
use App\Core\Pageable;
use App\Stores\ReferralLetterStudentStore;
use Database;

interface IReferralLetterService
{
  public function create(array $data, array $students = []): bool;
  public function getByBatchStudentId(int $batchStudentId): array;
  public function getLettersWithCompanyByBatchStudentId(int $batchStudentId): array;
  public function getById(int $id): ?ReferralLetter;
  public function getByIdWithCompany(int $id): ?array;
  public function getWithStudentsByLetterId(int $id): ?array;
  public function getForPrint(int $id): ?array;
  public function cancel(int $id, string $reason = ''): bool;
  public function updateCompany(int $id, int $companyId): bool;
  public function getAllWithDetailsByBatchId(int $batchId): array;
  public function printLetter(int $id, int $processedBy, array $printData = []): bool;
  public function bulkCancel(array $ids, string $reason, int $processedBy): int;
}

class ReferralLetterService implements IReferralLetterService
{
  private ReferralLetterStore $_store;
  private ReferralLetterStudentStore $_studentStore;

  public function __construct(ReferralLetterStore $store, ReferralLetterStudentStore $studentStore)
  {
    $this->_store = $store;
    $this->_studentStore = $studentStore;
  }

  public function create(array $data, array $students = []): bool
  {
    return Database::getInstance()->transaction(function () use ($data, $students) {
      $letterId = $this->_store->create($data);
      if ($letterId > 0 && !empty($students)) {
        $this->_studentStore->createBulk($letterId, $students);
      }
      return $letterId > 0;
    });
  }

  public function getByBatchStudentId(int $batchStudentId): array
  {
    return $this->_store->getAllByBatchStudentId($batchStudentId);
  }

  public function getLettersWithCompanyByBatchStudentId(int $batchStudentId): array
  {
    $letters = $this->_store->getLettersWithCompanyByBatchStudentId($batchStudentId);

    if (empty($letters)) return [];

    $letterIds = array_column($letters, 'id');
    $studentsGrouped = $this->_studentStore->getByLetterIds($letterIds);

    foreach ($letters as &$letter) {
      $students = $studentsGrouped[$letter['id']] ?? [];
      $letter['students'] = array_map(fn($s) => [
        'full_name' => $s->full_name,
        'training_program' => $s->training_program,
        'dob' => $s->dob,
        'address' => $s->address
      ], $students);
      $letter['student_count'] = count($students);
    }

    return $letters;
  }

  public function getById(int $id): ?ReferralLetter
  {
    return $this->_store->getById($id);
  }

  public function getByIdWithCompany(int $id): ?array
  {
    return $this->_store->getByIdWithCompany($id);
  }

  public function getWithStudentsByLetterId(int $id): ?array
  {
    return $this->_store->getWithStudentsByLetterId($id);
  }

  public function getForPrint(int $id): ?array
  {
    return $this->_store->getForPrint($id);
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

  public function printLetter(int $id, int $processedBy, array $printData = []): bool
  {
    $letter = $this->_store->getById($id);
    if (!$letter) {
      throw new Exception('Không tìm thấy giấy giới thiệu');
    }

    if ($letter->status === 'cancelled') {
      throw new Exception("Giấy giới thiệu #{$letter->id} đã bị hủy, không thể in.");
    }

    return Database::getInstance()->transaction(function () use ($id, $processedBy, $printData) {
      if (!empty($printData)) {
        $this->_store->updatePrintInfo($id, $printData);
      }

      return $this->_store->updateStatus($id, 'printed', [
        'printed_at' => date('Y-m-d H:i:s'),
        'processed_by' => $processedBy
      ]);
    });
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
