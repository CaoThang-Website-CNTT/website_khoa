<?php

namespace App\Services;

use App\Stores\ProjectBatchStore;
use App\Core\Pageable;
use App\Enums\ProjectBatchStatus;
use Database;
use Exception;

interface IProjectBatchService
{
  public function createBatch(array $data, int $adminId): int;
  public function updateBatch(int $id, array $data): bool;
  public function publishBatch(int $id): bool;
  public function setAllocationPublished(int $id): void;
  public function unpublishAllocation(int $id): void;
  public function closeBatch(int $id): bool;
  public function deleteBatch(int $id): bool;
  public function getBatches(int $page, int $limit = 15): Pageable;
  public function getBatchesByTeacherId(int $teacherId, int $page, int $limit = 15): Pageable;
  public function getBatchById(int $id): ?array;
  public function getBatchWithStats(int $id): ?array;
  public function getActiveBatches(): array;
  public function getAvailableTeachers(): array;
  public function getSupervisorsByBatchId(int $batchId): array;
  public function isTeacherAssigned(int $batchId, int $teacherId): bool;
  public function addSupervisorToBatch(int $batchId, int $teacherId, int $minStudents, int $maxStudents): void;
  public function updateSupervisorCapacity(int $batchId, int $teacherId, int $minStudents, int $maxStudents): void;
  public function removeSupervisor(int $batchId, int $teacherId): void;
  public function getSupervisorInfo(int $batchId, int $teacherId): ?array;
}

class ProjectBatchService implements IProjectBatchService
{
  private ProjectBatchStore $_store;

  public function __construct(ProjectBatchStore $store)
  {
    $this->_store = $store;
  }

  public function createBatch(array $data, int $adminId): int
  {
    $this->validateBatchDates($data);
    $data['created_by'] = $adminId;

    $supervisors = $data['supervisors'] ?? [];
    if (empty($supervisors)) {
      throw new Exception('Vui lòng chọn ít nhất 1 giảng viên cho đợt đồ án.');
    }
    
    foreach ($supervisors as $sv) {
      if (!isset($sv['min_students']) || !isset($sv['max_students'])) {
        throw new Exception('Thông tin sinh viên tối thiểu/tối đa không hợp lệ.');
      }
      $min = (int) $sv['min_students'];
      $max = (int) $sv['max_students'];
      if ($min < 0 || $max < 0 || $min % 2 !== 0 || $max % 2 !== 0) {
        throw new Exception('Số lượng sinh viên phải là số chẵn không âm; tối đa bằng 0 nghĩa là không giới hạn.');
      }
      if ($max !== 0 && $max < $min) {
        throw new Exception('Số SV tối đa không được nhỏ hơn SV tối thiểu.');
      }
    }

    $batchId = 0;
    try {
      Database::getInstance()->transaction(function () use ($data, $supervisors, &$batchId) {
        $batchId = $this->_store->createBatch($data);
        $this->_store->addSupervisors($batchId, $supervisors);
      });
      return $batchId;
    } catch (Exception $e) {
      throw new Exception('Không thể tạo đợt đồ án: ' . $e->getMessage());
    }
  }

  public function updateBatch(int $id, array $data): bool
  {
    $batch = $this->_store->getById($id);
    if (!$batch) {
      throw new Exception('Không tìm thấy đợt đồ án.');
    }

    $this->validateBatchDates($data);
    return $this->_store->updateBatch($id, $data);
  }

  public function publishBatch(int $id): bool
  {
    $batch = $this->_store->getById($id);
    if (!$batch) {
      throw new Exception('Không tìm thấy đợt đồ án.');
    }
    if ($batch['status'] !== ProjectBatchStatus::DRAFT) {
      throw new Exception('Chỉ có đợt đồ án ở trạng thái bản nháp mới có thể công bố.');
    }

    if (empty($batch['registration_start']) || empty($batch['registration_end'])) {
      throw new Exception('Vui lòng thiết lập thời gian đăng ký đề tài trước khi công bố.');
    }

    return $this->_store->updateStatus($id, ProjectBatchStatus::PUBLISHED, [
      'published_at' => date('Y-m-d H:i:s')
    ]);
  }

  public function setAllocationPublished(int $batchId): void
  {
    $this->_store->updateAllocationPublishedAt($batchId, date('Y-m-d H:i:s'));
  }

  public function unpublishAllocation(int $batchId): void
  {
    $this->_store->updateAllocationPublishedAt($batchId, null);
  }

  public function closeBatch(int $id): bool
  {
    $batch = $this->_store->getById($id);
    if (!$batch) {
      throw new Exception('Không tìm thấy đợt đồ án.');
    }
    if ($batch['status'] !== ProjectBatchStatus::PUBLISHED) {
      throw new Exception('Chỉ có đợt đồ án đã công bố mới có thể đóng.');
    }

    return $this->_store->updateStatus($id, ProjectBatchStatus::CLOSED, [
      'closed_at' => date('Y-m-d H:i:s')
    ]);
  }

  public function deleteBatch(int $id): bool
  {
    $stats = $this->_store->getBatchStats($id);
    if ($stats['total_topics'] > 0 || $stats['total_groups'] > 0) {
      throw new Exception('Không thể xóa đợt đồ án đã có đề tài hoặc nhóm sinh viên.');
    }

    return $this->_store->deleteBatch($id);
  }

  public function getBatches(int $page, int $limit = 15): Pageable
  {
    $items = $this->_store->getPaginated($page, $limit);
    $total = $this->_store->getTotalCount();
    return new Pageable($items, $total, $limit, $page);
  }

  public function getBatchesByTeacherId(int $teacherId, int $page, int $limit = 15): Pageable
  {
    $items = $this->_store->getBatchesByTeacherId($teacherId, $page, $limit);
    $total = $this->_store->getTotalCountByTeacherId($teacherId);
    return new Pageable($items, $total, $limit, $page);
  }

  public function getBatchById(int $id): ?array
  {
    return $this->_store->getById($id);
  }

  public function getBatchWithStats(int $id): ?array
  {
    $batch = $this->_store->getById($id);
    if (!$batch) {
      return null;
    }

    $batch['stats'] = $this->_store->getBatchStats($id);
    return $batch;
  }

  public function getActiveBatches(): array
  {
    return $this->_store->getActiveBatches();
  }

  public function getAvailableTeachers(): array
  {
    return $this->_store->getITTeachers();
  }

  public function getSupervisorsByBatchId(int $batchId): array
  {
    return $this->_store->getSupervisorsByBatchId($batchId);
  }

  public function isTeacherAssigned(int $batchId, int $teacherId): bool
  {
    return $this->_store->isTeacherAssigned($batchId, $teacherId);
  }

  public function addSupervisorToBatch(int $batchId, int $teacherId, int $minStudents, int $maxStudents): void
  {
    if ($minStudents < 0 || $maxStudents < 0 || $minStudents % 2 !== 0 || $maxStudents % 2 !== 0) {
      throw new Exception('Số lượng sinh viên phải là số chẵn không âm.');
    }
    if ($maxStudents !== 0 && $maxStudents < $minStudents) {
      throw new Exception('Số SV tối đa không được nhỏ hơn SV tối thiểu.');
    }
    if ($this->isTeacherAssigned($batchId, $teacherId)) {
      throw new Exception('Giảng viên đã có trong danh sách phụ trách đợt này.');
    }

    $this->_store->addSupervisor($batchId, $teacherId, $minStudents, $maxStudents);
  }

  public function updateSupervisorCapacity(int $batchId, int $teacherId, int $minStudents, int $maxStudents): void
  {
    if ($minStudents < 0 || $maxStudents < 0 || $minStudents % 2 !== 0 || $maxStudents % 2 !== 0) {
      throw new Exception('Số lượng sinh viên phải là số chẵn không âm.');
    }
    if ($maxStudents !== 0 && $maxStudents < $minStudents) {
      throw new Exception('Số SV tối đa không được nhỏ hơn SV tối thiểu.');
    }
    if (!$this->isTeacherAssigned($batchId, $teacherId)) {
      throw new Exception('Giảng viên không có trong đợt đồ án này.');
    }

    $this->_store->updateSupervisorCapacity($batchId, $teacherId, $minStudents, $maxStudents);
  }

  public function removeSupervisor(int $batchId, int $teacherId): void
  {
    if (!$this->isTeacherAssigned($batchId, $teacherId)) {
      throw new Exception('Giảng viên không có trong đợt đồ án này.');
    }

    // Backend validation just to be completely safe
    $supervisors = $this->getSupervisorsByBatchId($batchId);
    $target = array_filter($supervisors, fn($s) => $s['teacher_id'] == $teacherId);
    $target = reset($target);

    if ($target && isset($target['current_load']) && $target['current_load'] > 0) {
      throw new Exception('Không thể xóa giảng viên đã được phân công nhóm sinh viên.');
    }

    $this->_store->removeSupervisor($batchId, $teacherId);
  }

  public function getSupervisorInfo(int $batchId, int $teacherId): ?array
  {
    return $this->_store->getSupervisorInfo($batchId, $teacherId);
  }

  private function validateBatchDates(array $data): void
  {
    if (empty($data['title'])) {
      throw new Exception('Tiêu đề là bắt buộc.');
    }

    $minClassOf = filter_var($data['min_class_of'] ?? null, FILTER_VALIDATE_INT);
    $maxClassOf = filter_var($data['max_class_of'] ?? null, FILTER_VALIDATE_INT);
    if ($minClassOf === false || $maxClassOf === false || $minClassOf <= 0 || $maxClassOf <= 0) {
      throw new Exception('Niên khóa áp dụng không hợp lệ.');
    }
    if ($minClassOf > $maxClassOf) {
      throw new Exception('Niên khóa bắt đầu không được lớn hơn niên khóa kết thúc.');
    }

    $dates = [
      'topic_proposal_start',
      'topic_proposal_end'
    ];

    foreach ($dates as $dateKey) {
      if (empty($data[$dateKey])) {
        throw new Exception("Trường {$dateKey} là bắt buộc.");
      }
    }

    $proposalStart = strtotime((string) $data['topic_proposal_start']);
    $proposalEnd = strtotime((string) $data['topic_proposal_end']);

    if ($proposalStart >= $proposalEnd) {
      throw new Exception('Thời gian kết thúc đề xuất phải lớn hơn thời gian bắt đầu đề xuất.');
    }

    if (!empty($data['registration_start']) && !empty($data['registration_end'])) {
      $regStart = strtotime((string) $data['registration_start']);
      $regEnd = strtotime((string) $data['registration_end']);

      if ($regStart >= $regEnd) {
        throw new Exception('Thời gian kết thúc đăng ký phải lớn hơn thời gian bắt đầu đăng ký.');
      }

      if ($proposalEnd > $regStart) {
        throw new Exception('Thời gian đề xuất đề tài phải kết thúc trước khi bắt đầu đăng ký.');
      }
    }
  }
}
