<?php

namespace App\Services;

use App\Stores\ProjectTopicStore;
use App\Stores\ProjectBatchStore;
use App\Core\Pageable;
use App\Enums\ProjectTopicStatus;
use App\Enums\ProjectBatchStatus;
use Exception;

interface IProjectTopicService
{
  public function createTopic(array $data, int $teacherId): int;
  public function updateTopic(int $id, array $data, int $teacherId): bool;
  public function deleteTopic(int $id, int $teacherId): bool;
  public function getTopicById(int $id): ?array;
  public function getTopicsByTeacher(int $batchId, int $teacherId): array;
  public function getPaginatedByBatch(int $batchId, int $page, int $limit = 15, array $filters = []): Pageable;
  public function reviewTopic(int $id, string $status, ?string $reason, int $adminId): bool;
  public function submitTopic(int $id, int $teacherId): bool;
  public function getApprovedTopics(int $batchId): array;
  public function getPendingCountByBatch(int $batchId): int;
}

class ProjectTopicService implements IProjectTopicService
{
  private ProjectTopicStore $_store;
  private ProjectBatchStore $_batchStore;

  public function __construct(ProjectTopicStore $store, ProjectBatchStore $batchStore)
  {
    $this->_store = $store;
    $this->_batchStore = $batchStore;
  }

  public function createTopic(array $data, int $teacherId): int
  {
    $this->validateTopicPhase($data['batch_id']);

    $data['teacher_id'] = $teacherId;
    if (empty($data['title'])) {
      throw new Exception('Tên đề tài là bắt buộc.');
    }

    return $this->_store->createTopic($data);
  }

  public function updateTopic(int $id, array $data, int $teacherId): bool
  {
    $topic = $this->_store->getById($id);
    if (!$topic) {
      throw new Exception('Không tìm thấy đề tài.');
    }

    if ($topic['teacher_id'] != $teacherId) {
      throw new Exception('Bạn không có quyền chỉnh sửa đề tài này.');
    }

    if ($topic['status'] === ProjectTopicStatus::APPROVED) {
      throw new Exception('Không thể chỉnh sửa đề tài đã được duyệt.');
    }

    $this->validateTopicPhase($topic['batch_id']);

    if (empty($data['title'])) {
      throw new Exception('Tên đề tài là bắt buộc.');
    }

    // Nếu đề tài bị từ chối và được sửa lại -> chuyển về draft
    if ($topic['status'] === ProjectTopicStatus::REJECTED) {
      $data['status'] = ProjectTopicStatus::DRAFT;
    }

    return $this->_store->updateTopic($id, $data);
  }

  public function deleteTopic(int $id, int $teacherId): bool
  {
    $topic = $this->_store->getById($id);
    if (!$topic) {
      throw new Exception('Không tìm thấy đề tài.');
    }

    if ($topic['teacher_id'] != $teacherId) {
      throw new Exception('Bạn không có quyền xóa đề tài này.');
    }

    if ($topic['status'] === ProjectTopicStatus::APPROVED) {
      throw new Exception('Không thể xóa đề tài đã được duyệt.');
    }

    $this->validateTopicPhase($topic['batch_id']);

    return $this->_store->deleteTopic($id);
  }

  public function submitTopic(int $id, int $teacherId): bool
  {
    $topic = $this->_store->getById($id);
    if (!$topic) {
      throw new Exception('Không tìm thấy đề tài.');
    }

    if ($topic['teacher_id'] != $teacherId) {
      throw new Exception('Bạn không có quyền thao tác đề tài này.');
    }

    if ($topic['status'] !== ProjectTopicStatus::DRAFT && $topic['status'] !== ProjectTopicStatus::REJECTED) {
      throw new Exception('Chỉ có thể nộp đề tài ở trạng thái nháp hoặc bị từ chối.');
    }

    $this->validateTopicPhase($topic['batch_id']);

    return $this->_store->updateStatus($id, ProjectTopicStatus::PENDING, [
      'submitted_at' => date('Y-m-d H:i:s')
    ]);
  }

  public function getTopicById(int $id): ?array
  {
    return $this->_store->getById($id);
  }

  public function getTopicsByTeacher(int $batchId, int $teacherId): array
  {
    return $this->_store->getTopicsByTeacher($batchId, $teacherId);
  }

  public function getPaginatedByBatch(int $batchId, int $page, int $limit = 15, array $filters = []): Pageable
  {
    $items = $this->_store->getPaginatedByBatch($batchId, $page, $limit, $filters);
    $total = $this->_store->getTotalCountByBatch($batchId, $filters);
    return new Pageable($items, $total, $limit, $page);
  }

  public function reviewTopic(int $id, string $status, ?string $reason, int $adminId): bool
  {
    $topic = $this->_store->getById($id);
    if (!$topic) {
      throw new Exception('Không tìm thấy đề tài.');
    }

    if ($topic['status'] !== ProjectTopicStatus::PENDING) {
      throw new Exception('Chỉ có thể xét duyệt đề tài đang chờ duyệt.');
    }

    if (!in_array($status, [ProjectTopicStatus::APPROVED, ProjectTopicStatus::REJECTED])) {
      throw new Exception('Trạng thái duyệt không hợp lệ.');
    }

    if ($status === ProjectTopicStatus::REJECTED && empty($reason)) {
      throw new Exception('Vui lòng nhập lý do từ chối.');
    }

    return $this->_store->updateStatus($id, $status, [
      'reviewed_by' => $adminId,
      'reviewed_at' => date('Y-m-d H:i:s'),
      'reject_reason' => $reason
    ]);
  }

  public function getApprovedTopics(int $batchId): array
  {
    return $this->_store->getApprovedTopics($batchId);
  }

  public function getPendingCountByBatch(int $batchId): int
  {
    return $this->_store->getPendingCountByBatch($batchId);
  }

  private function validateTopicPhase(int $batchId): void
  {
    $batch = $this->_batchStore->getById($batchId);
    if (!$batch) {
      throw new Exception('Không tìm thấy đợt đồ án.');
    }

    if ($batch['status'] !== ProjectBatchStatus::PUBLISHED) {
      throw new Exception('Đợt đồ án chưa được công bố.');
    }

    $now = time();
    $start = strtotime((string) $batch['topic_proposal_start']);
    $end = strtotime((string) $batch['topic_proposal_end']);

    if ($now < $start || $now > $end) {
      throw new Exception('Đang ngoài thời gian đề xuất đề tài.');
    }
  }
}
