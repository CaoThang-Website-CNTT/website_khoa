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
  public function closeBatch(int $id): bool;
  public function deleteBatch(int $id): bool;
  public function getBatches(int $page, int $limit = 15): Pageable;
  public function getBatchesByTeacherId(int $teacherId, int $page, int $limit = 15): Pageable;
  public function getBatchById(int $id): ?array;
  public function getBatchWithStats(int $id): ?array;
  public function getActiveBatches(): array;
  public function getAvailableTeachers(): array;
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
      if ($min < 0 || $max <= 0) {
        throw new Exception('Số lượng sinh viên phải lớn hơn 0.');
      }
      if ($max < $min) {
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

    return $this->_store->updateStatus($id, ProjectBatchStatus::PUBLISHED, [
      'published_at' => date('Y-m-d H:i:s')
    ]);
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

  private function validateBatchDates(array $data): void
  {
    if (empty($data['title'])) {
      throw new Exception('Tiêu đề là bắt buộc.');
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
