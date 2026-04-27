<?php

namespace App\Services;

use App\Stores\InternshipBatchStore;
use App\Core\Pageable;
use Database;

class InternshipBatchService
{
  private InternshipBatchStore $_store;

  public function __construct(InternshipBatchStore $store)
  {
    $this->_store = $store;
  }

  /**
   * Tạo toàn bộ đợt thực tập trong 1 transaction (Batch, Classrooms, Students, Supervisors)
   */
  public function createFullBatch(array $batchData, array $studentIds, array $supervisors, array $classroomIds, int $adminId): int
  {
    return Database::getInstance()->transaction(function () use ($batchData, $studentIds, $supervisors, $classroomIds, $adminId) {

      $batchData['created_by'] = $adminId;
      $batchId = $this->_store->createBatch($batchData);

      if (!$batchId) {
        throw new \Exception('Không thể tạo thông tin đợt thực tập.');
      }

      $this->_store->addClassroomsToBatch($batchId, $classroomIds);
      $this->_store->addStudentsToBatch($batchId, $studentIds);
      $this->_store->addSupervisorsToBatch($batchId, $supervisors);

      return $batchId;
    });
  }

  public function getEligibleStudentsByClassroom(int $classroomId): array
  {
    return $this->_store->getEligibleStudentsByClassroom($classroomId);
  }

  public function getEligibleStudentsByClassrooms(array $classroomIds): array
  {
    return $this->_store->getEligibleStudentsByClassrooms($classroomIds);
  }

  public function validateStudentsBulk(array $studentIds): array
  {
    $students = $this->_store->validateStudentsByStudentIds($studentIds);

    $valid = [];
    $invalid = [];

    $studentsMap = [];
    foreach ($students as $s) {
      $studentsMap[$s['student_id']] = $s;
    }

    foreach ($studentIds as $studentId) {
      if (!isset($studentsMap[$studentId])) {
        $invalid[] = ['student_id' => $studentId, 'reason' => 'Không tìm thấy sinh viên trong hệ thống.'];
        continue;
      }

      $s = $studentsMap[$studentId];

      if ($s['status'] !== 'Đang học') {
        $invalid[] = ['student_id' => $studentId, 'reason' => 'Trạng thái không phải "Đang học".'];
        continue;
      }

      if ($s['batch_id']) {
        $invalid[] = ['student_id' => $studentId, 'reason' => 'Đã tham gia một đợt thực tập khác.'];
        continue;
      }

      $valid[] = $s;
    }

    return [
      'valid' => $valid,
      'invalid' => $invalid
    ];
  }

  public function getActiveTeachers(): array
  {
    return $this->_store->getActiveTeachers();
  }

  public function getAllClassrooms(): array
  {
    return $this->_store->getAllClassrooms();
  }

  public function getBatches(int $page, int $limit = 15): Pageable
  {
    $items = $this->_store->getPaginated($page, $limit);
    $total = $this->_store->getTotalCount();

    return new Pageable($items, $total, $limit, $page);
  }

  public function getBatchById(int $id): ?array
  {
    return $this->_store->getById($id);
  }

  public function getBatchWithStats(int $id): ?array
  {
    $batch = $this->_store->getById($id);
    if (!$batch) return null;

    $stats = $this->_store->getBatchStats($id);
    return array_merge($batch, ['stats' => $stats]);
  }

  public function updateBatch(int $id, array $data): bool
  {
    return $this->_store->update($id, $data);
  }

  public function deleteBatch(int $id): bool
  {
    $stats = $this->_store->getBatchStats($id);

    if ($stats['has_submissions'] || $stats['has_grades']) {
      throw new \Exception('Không thể xóa đợt thực tập đã có bài nộp hoặc điểm số.');
    }

    return $this->_store->delete($id);
  }

  public function publishBatch(int $id): bool
  {
    return $this->_store->updateStatus($id, 'published', [
      'published_at' => date('Y-m-d H:i:s')
    ]);
  }

  public function closeBatch(int $id): bool
  {
    return $this->_store->updateStatus($id, 'closed', [
      'closed_at' => date('Y-m-d H:i:s')
    ]);
  }
}
