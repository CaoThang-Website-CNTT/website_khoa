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
  public function bulkReview(array $ids, int $batchId, string $action, string $reason, int $processedBy): int;
  public function updateCompany(int $id, int $companyId): bool;
  public function getAllWithDetailsByBatchId(int $batchId): array;
  public function printLetter(int $id, int $processedBy, array $printData = []): bool;
  public function bulkCancel(array $ids, string $reason, int $processedBy): int;
  public function complete(int $id, int $processedBy): bool;
  public function receive(int $id, int $batchId, array $recipient, int $receivedBy): bool;
  public function bulkPrint(array $ids, int $batchId, int $processedBy, array $printData): int;
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
      throw new Exception('Vui lòng nhập lý do từ chối giấy giới thiệu.');
    return $this->review($id, ReferralLetterStatus::REJECTED, trim($reason), $processedBy);
  }

  private function review(int $id, string $status, string $reason, int $processedBy): bool
  {
    $letter = $this->_store->getById($id);
    if (!$letter)
      throw new Exception('Không tìm thấy giấy giới thiệu.');
    if ($letter->status !== ReferralLetterStatus::PENDING) {
      throw new Exception('Chỉ giấy giới thiệu đang chờ duyệt mới có thể được duyệt hoặc từ chối.');
    }
    return $this->_store->updateStatus($id, $status, [
      'cancel_reason' => $reason ?: null,
      'processed_by' => $processedBy,
      'reviewed_at' => date('Y-m-d H:i:s'),
    ]);
  }

  public function bulkReview(array $ids, int $batchId, string $action, string $reason, int $processedBy): int
  {
    if (!in_array($action, ['approve', 'reject', 'complete'], true))
      throw new Exception('Thao tác xét duyệt giấy giới thiệu không hợp lệ.');
    $ids = array_values(array_unique(array_map('intval', $ids)));
    $letters = $this->_store->getByIdsForBatch($ids, $batchId);
    if (count($letters) !== count($ids)) throw new Exception('Có giấy không thuộc đợt thực tập này.');
    foreach ($letters as $letter) {
      if (in_array($action, ['approve', 'reject'], true) && $letter->status !== ReferralLetterStatus::PENDING)
        throw new Exception("Giấy giới thiệu #{$letter->id} không ở trạng thái chờ duyệt.");
      if ($action === 'complete' && ($letter->status !== ReferralLetterStatus::APPROVED || !$letter->printed_at))
        throw new Exception("Giấy giới thiệu #{$letter->id} chưa được in hoặc không ở trạng thái đang xử lý.");
    }
    return Database::getInstance()->transaction(function () use ($letters, $action, $reason, $processedBy) {
      $count = 0;
      foreach ($letters as $letter) {
        $id = (int)$letter->id;
        $success = $action === 'approve'
          ? $this->approve($id, $processedBy)
          : ($action === 'reject' ? $this->reject($id, $reason, $processedBy) : $this->complete($id, $processedBy));
        if ($success)
          $count++;
      }
      return $count;
    });
  }

  public function complete(int $id, int $processedBy): bool
  {
    $letter = $this->_store->getById($id);
    if (!$letter) throw new Exception('Không tìm thấy giấy giới thiệu.');
    if ($letter->status !== ReferralLetterStatus::APPROVED)
      throw new Exception('Chỉ giấy đang xử lý mới có thể hoàn thành.');
    if (!$letter->printed_at)
      throw new Exception('Giấy phải được in trước khi xác nhận hoàn thành.');
    return $this->_store->updateStatus($id, ReferralLetterStatus::COMPLETED, ['processed_by' => $processedBy]);
  }

  public function receive(int $id, int $batchId, array $recipient, int $receivedBy): bool
  {
    $matches = $this->_store->getByIdsForBatch([$id], $batchId);
    $letter = $matches[0] ?? null;
    if (!$letter) throw new Exception('Không tìm thấy giấy giới thiệu.');
    if ($letter->status !== ReferralLetterStatus::COMPLETED)
      throw new Exception('Chỉ giấy đã hoàn thành mới có thể bàn giao.');
    $name = trim((string)($recipient['recipient_name'] ?? ''));
    $phone = trim((string)($recipient['recipient_phone'] ?? ''));
    $email = trim((string)($recipient['recipient_email'] ?? ''));
    if ($name === '' || $phone === '' || $email === '')
      throw new Exception('Họ tên, số điện thoại và email người nhận là bắt buộc.');
    if (!preg_match('/^[0-9+() .-]{8,15}$/', $phone))
      throw new Exception('Số điện thoại người nhận không hợp lệ.');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
      throw new Exception('Email người nhận không hợp lệ.');
    return $this->_store->updateReceipt($id, [
      'status' => ReferralLetterStatus::RECEIVED,
      'recipient_name' => $name,
      'recipient_phone' => $phone,
      'recipient_email' => $email,
      'received_at' => date('Y-m-d H:i:s'),
      'received_by' => $receivedBy,
    ]);
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

    if ($letter->status !== ReferralLetterStatus::APPROVED) {
      throw new Exception('Giấy giới thiệu phải được duyệt trước khi in.');
    }
    $printData['approver_name'] = trim((string)($printData['approver_name'] ?? ''));
    if ($printData['approver_name'] === '') throw new Exception('Vui lòng nhập tên giảng viên phê duyệt.');

    return Database::getInstance()->transaction(function () use ($id, $processedBy, $printData) {
      if (!empty($printData)) {
        $this->_store->updatePrintInfo($id, $printData);
      }

      return $this->_store->updateStatus($id, ReferralLetterStatus::APPROVED, [
        'printed_at' => date('Y-m-d H:i:s'),
        'processed_by' => $processedBy
      ]);
    });
  }

  public function bulkPrint(array $ids, int $batchId, int $processedBy, array $printData): int
  {
    $ids = array_values(array_unique(array_map('intval', $ids)));
    if (empty($ids)) throw new Exception('Không có giấy giới thiệu nào được chọn.');
    if ($processedBy <= 0) throw new Exception('Không xác định được người thực hiện.');
    $documentNumber = trim((string)($printData['document_number'] ?? ''));
    $startDate = (string)($printData['internship_start_date'] ?? '');
    $endDate = (string)($printData['internship_end_date'] ?? '');
    $approverName = trim((string)($printData['approver_name'] ?? ''));
    if ($approverName === '') throw new Exception('Vui lòng nhập tên giảng viên phê duyệt.');
    $printData['approver_name'] = $approverName;
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate) || $startDate > $endDate)
      throw new Exception('Thời gian thực tập không hợp lệ.');
    $printData['document_number'] = $documentNumber !== '' ? $documentNumber : null;
    $letters = $this->_store->getByIdsForBatch($ids, $batchId);
    if (count($letters) !== count($ids)) throw new Exception('Có giấy không thuộc đợt thực tập này.');
    foreach ($letters as $letter) {
      if ($letter->status !== ReferralLetterStatus::APPROVED)
        throw new Exception("Giấy giới thiệu #{$letter->id} không ở trạng thái đang xử lý.");
    }
    $companyIds = array_unique(array_map(fn($letter) => (int)$letter->company_id, $letters));
    if (count($companyIds) !== 1) throw new Exception('Chỉ có thể in gộp các giấy cùng một công ty.');
    return Database::getInstance()->transaction(function () use ($letters, $processedBy, $printData) {
      $count = 0;
      foreach ($letters as $letter) {
        $id = (int)$letter->id;
        $this->_store->updatePrintInfo($id, $printData);
        if ($this->_store->updateStatus($id, ReferralLetterStatus::APPROVED, [
          'printed_at' => date('Y-m-d H:i:s'), 'processed_by' => $processedBy
        ])) $count++;
      }
      return $count;
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
