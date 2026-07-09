<?php

namespace App\Services;

require_once BASE_PATH . '/stores/project_group_store.php';

use App\Stores\ProjectGroupStore;
use App\Stores\ProjectAspirationStore;
use App\Stores\ProjectTopicStore;

interface IProjectGroupService
{
  public function getGroupByStudent(int $batchId, int $studentId): ?array;
  public function getGroupMembers(int $groupId): array;
  public function createGroup(int $batchId, int $leaderStudentId): int;
  public function createGroupWithMembers(int $batchId, int $leaderStudentId, int $partnerStudentId): void;
  public function addMember(int $groupId, int $studentId, bool $isLeader = false, bool $isConfirmed = true): bool;
  public function confirmMember(int $groupId, int $studentId): bool;
  public function removeMember(int $groupId, int $studentId): bool;
  public function autoAllocateTopics(int $batchId): array;
  public function manualAssignTopic(int $groupId, int $topicId): bool;
  public function kickIneligibleMembers(int $batchId, array $ineligibleStudentIds): bool;
  public function getPaginatedByBatch(int $batchId, int $page, int $limit = 15, array $filters = []): array;
  public function getTotalCountByBatch(int $batchId, array $filters = []): int;
  public function getAspirationsByBatch(int $batchId): array;
}

class ProjectGroupService implements IProjectGroupService
{
  private ProjectGroupStore $_store;
  private ProjectAspirationStore $_aspirationStore;
  private ProjectTopicStore $_topicStore;

  public function __construct(
    ProjectGroupStore $store,
    ProjectAspirationStore $aspirationStore,
    ProjectTopicStore $topicStore
  ) {
    $this->_store = $store;
    $this->_aspirationStore = $aspirationStore;
    $this->_topicStore = $topicStore;
  }

  public function getGroupByStudent(int $batchId, int $studentId): ?array
  {
    return $this->_store->getGroupByStudent($batchId, $studentId);
  }

  public function getGroupMembers(int $groupId): array
  {
    return $this->_store->getGroupMembers($groupId);
  }

  public function getPaginatedByBatch(int $batchId, int $page, int $limit = 15, array $filters = []): array
  {
    return $this->_store->getPaginatedByBatch($batchId, $page, $limit, $filters);
  }

  public function getTotalCountByBatch(int $batchId, array $filters = []): int
  {
    return $this->_store->getTotalCountByBatch($batchId, $filters);
  }

  public function createGroup(int $batchId, int $leaderStudentId): int
  {
    return $this->_store->createGroup($batchId, $leaderStudentId);
  }

  public function createGroupWithMembers(int $batchId, int $leaderStudentId, int $partnerStudentId): void
  {
    \Database::getInstance()->transaction(function () use ($batchId, $leaderStudentId, $partnerStudentId) {
      $groupId = $this->_store->createGroup($batchId, $leaderStudentId);
      $this->_store->addMember($groupId, $leaderStudentId, true, true);
      $this->_store->addMember($groupId, $partnerStudentId, false, false);
    });
  }

  public function addMember(int $groupId, int $studentId, bool $isLeader = false, bool $isConfirmed = true): bool
  {
    return $this->_store->addMember($groupId, $studentId, $isLeader, $isConfirmed);
  }

  public function confirmMember(int $groupId, int $studentId): bool
  {
    return $this->_store->confirmMember($groupId, $studentId);
  }

  public function removeMember(int $groupId, int $studentId): bool
  {
    return $this->_store->removeMember($groupId, $studentId);
  }

  public function autoAllocateTopics(int $batchId): array
  {
    // 1. Lấy tất cả các nhóm hợp lệ để phân bổ
    $groups = $this->_store->getValidGroupsForAllocation($batchId);
    if (empty($groups)) {
      return ['success' => 0, 'failed' => 0, 'message' => 'Không có nhóm nào hợp lệ để phân bổ.'];
    }

    // 2. Lấy tất cả nguyện vọng trong đợt này
    $allAspirations = $this->_aspirationStore->getAspirationsByBatch($batchId);

    // Group nguyện vọng theo group_id và sắp xếp theo thứ tự ưu tiên
    $aspirationsByGroup = [];
    foreach ($allAspirations as $asp) {
      $aspirationsByGroup[$asp['group_id']][] = $asp;
    }

    // 3. Lấy tất cả các đề tài đã được phê duyệt trong đợt này
    $allTopics = $this->_topicStore->getApprovedTopics($batchId);

    // Tạo một map để theo dõi số lượng slot của từng đề tài
    $topicSlots = [];
    foreach ($allTopics as $topic) {
      $maxStudents = (int)$topic['max_students'];
      $maxGroups = (int)($maxStudents / 2);
      $topicSlots[$topic['id']] = [
        'max_groups' => max(1, $maxGroups),
        'assigned_groups' => (int)($topic['assigned_groups_count'] ?? 0)
      ];
    }

    $successCount = 0;
    $failedCount = 0;

    // 4. Xử lý từng nhóm
    foreach ($groups as $group) {
      $groupId = $group['id'];
      $aspirations = $aspirationsByGroup[$groupId] ?? [];

      $assigned = false;
      foreach ($aspirations as $asp) {
        $topicId = $asp['topic_id'];

        if (isset($topicSlots[$topicId])) {
          $slotInfo = $topicSlots[$topicId];
          if ($slotInfo['assigned_groups'] < $slotInfo['max_groups']) {
            // Allocate to this topic
            if ($this->_store->assignTopic($groupId, $topicId)) {
              $topicSlots[$topicId]['assigned_groups']++;
              $assigned = true;
              $successCount++;
              break;
            }
          }
        }
      }

      if (!$assigned) {
        $failedCount++;
      }
    }

    return [
      'success' => $successCount,
      'failed' => $failedCount,
      'message' => "Đã phân bổ thành công $successCount nhóm, $failedCount nhóm chưa có đề tài."
    ];
  }

  public function manualAssignTopic(int $groupId, int $topicId): bool
  {
    // Bypass kiểm tra slot, trực tiếp phân bổ
    return $this->_store->assignTopic($groupId, $topicId);
  }

  public function getAspirationsByBatch(int $batchId): array
  {
    return $this->_aspirationStore->getAspirationsByBatch($batchId);
  }

  public function kickIneligibleMembers(int $batchId, array $ineligibleStudentIds): bool
  {
    if (empty($ineligibleStudentIds)) {
      return true;
    }

    // Cập nhật is_eligible thành 0
    return $this->_store->updateMemberEligibility($ineligibleStudentIds, 0);
  }
}
