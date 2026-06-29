<?php

namespace App\Services;

use App\Stores\ReferralLetterStore;
use App\Models\ReferralLetter;
use Exception;
use App\Core\Pageable;
use App\Enums\BatchStatus;
use App\Stores\ReferralLetterStudentStore;
use App\Stores\InternshipBatchStore;
use Database;
use App\Enums\ReferralLetterStatus;

interface IReferralLetterService
{
  public function create(array $data, array $students = []): bool;
  public function getByBatchStudentId(int $batchStudentId): array;
  public function getLettersWithCompanyByBatchStudentId(int $batchStudentId): array;
  public function getById(int $id): ?ReferralLetter;
  public function getByIdWithCompany(int $id): ?array;
  public function getWithStudentsByLetterId(int $id): ?array;
  public function getForPrint(int $id): ?array;
  public function cancel(int $id, string $reason = '', ?int $cancelledBy = null): bool;
  public function approve(int $id, int $processedBy): bool;
  public function reject(int $id, string $reason, int $processedBy): bool;
  public function bulkReview(array $ids, string $action, string $reason, int $processedBy): int;
  public function updateCompany(int $id, int $companyId): bool;
  public function getAllWithDetailsByBatchId(int $batchId): array;
  public function printLetter(int $id, int $processedBy, array $printData = []): bool;
  public function bulkCancel(array $ids, string $reason, int $processedBy): int;
}

class ReferralLetterService implements IReferralLetterService
{
  private ReferralLetterStore $_store;
  private ReferralLetterStudentStore $_studentStore;
  private InternshipBatchStore $_batchStore;

  public function __construct(ReferralLetterStore $store, ReferralLetterStudentStore $studentStore, InternshipBatchStore $batchStore)
  {
    $this->_store = $store;
    $this->_studentStore = $studentStore;
    $this->_batchStore = $batchStore;
  }

  public function create(array $data, array $students = []): bool
  {
    $primaryId = (int) ($data['batch_student_id'] ?? 0);
    $primary = $this->_batchStore->getStudentGradingDetail($primaryId);
    if (!$primary)
      throw new Exception('Sinh viên yêu cầu chưa được đăng ký vào đợt thực tập này.');
    $batch = $this->_batchStore->getById((int) $primary['batch_id']);
    if (!$batch || in_array($batch['status'], [BatchStatus::DRAFT, BatchStatus::CLOSED])) {
      throw new Exception('Bạn không thể xin giấy giới thiệu khi đợt thực tập đã đóng hoặc chưa được công bố.');
    }
    if (empty($students))
      throw new Exception('Yêu cầu phải có ít nhất một sinh viên trong danh sách.');
    foreach ($students as $student) {
      $memberId = (int) ($student['batch_student_id'] ?? 0);
      $member = $this->_batchStore->getStudentGradingDetail($memberId);
      if (!$member || (int) $member['batch_id'] !== (int) $primary['batch_id']) {
        throw new Exception('Tất cả thành viên tham gia nhận giấy giới thiệu phải thuộc cùng một đợt thực tập.');
      }
    }
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

    if (empty($letters))
      return [];

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

  public function cancel(int $id, string $reason = '', ?int $cancelledBy = null): bool
  {
    $letter = $this->_store->getById($id);
    if (!$letter) {
      throw new Exception('Không tìm thấy giấy giới thiệu');
    }
    if ($letter->status !== 'pending') {
      throw new Exception('Chỉ có thể hủy giấy giới thiệu đang chờ xử lý');
    }

    return $this->_store->updateStatus($id, ReferralLetterStatus::CANCELLED, [
      'cancel_reason' => $reason,
      'cancelled_by' => $cancelledBy,
    ]);
  }

  public function approve(int $id, int $processedBy): bool
  {
    return $this->review($id, ReferralLetterStatus::APPROVED, '', $processedBy);
  }

  public function reject(int $id, string $reason, int $processedBy): bool
  {
    if (trim($reason) === '')
      throw new Exception('A rejection reason is required.');
    return $this->review($id, ReferralLetterStatus::REJECTED, trim($reason), $processedBy);
  }

  private function review(int $id, string $status, string $reason, int $processedBy): bool
  {
    $letter = $this->_store->getById($id);
    if (!$letter)
      throw new Exception('Referral letter not found.');
    if ($letter->status !== ReferralLetterStatus::PENDING) {
      throw new Exception('Only pending referral letters can be reviewed.');
    }
    return $this->_store->updateStatus($id, $status, [
      'cancel_reason' => $reason ?: null,
      'processed_by' => $processedBy,
      'reviewed_at' => date('Y-m-d H:i:s'),
    ]);
  }

  public function bulkReview(array $ids, string $action, string $reason, int $processedBy): int
  {
    if (!in_array($action, ['approve', 'reject'], true))
      throw new Exception('Invalid review action.');
    return Database::getInstance()->transaction(function () use ($ids, $action, $reason, $processedBy) {
      $count = 0;
      foreach ($ids as $id) {
        $success = $action === 'approve'
          ? $this->approve((int) $id, $processedBy)
          : $this->reject((int) $id, $reason, $processedBy);
        if ($success)
          $count++;
      }
      return $count;
    });
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

    if (!in_array($letter->status, [ReferralLetterStatus::APPROVED, ReferralLetterStatus::PRINTED], true)) {
      throw new Exception('Giấy giới thiệu phải được duyệt trước khi in.');
    }

    return Database::getInstance()->transaction(function () use ($id, $processedBy, $printData) {
      if (!empty($printData)) {
        $this->_store->updatePrintInfo($id, $printData);
      }

      return $this->_store->updateStatus($id, ReferralLetterStatus::PRINTED, [
        'printed_at' => date('Y-m-d H:i:s'),
        'processed_by' => $processedBy
      ]);
    });
  }

  public function bulkCancel(array $ids, string $reason, int $processedBy): int
  {
    if (empty($ids))
      return 0;

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
