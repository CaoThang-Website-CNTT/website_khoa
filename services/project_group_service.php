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
    $lockedAtByGroup = [];
    foreach ($allAspirations as $asp) {
      $aspirationsByGroup[$asp['group_id']][] = $asp;
      if (!empty($asp['locked_at'])) {
        $lockedAtByGroup[$asp['group_id']] = $asp['locked_at'];
      }
    }

    // Lọc: chỉ giữ nhóm ĐÃ CHỐT nguyện vọng
    $groups = array_filter($groups, fn($g) => isset($lockedAtByGroup[$g['id']]));
    $groups = array_values($groups);

    if (empty($groups)) {
      return ['success' => 0, 'failed' => 0, 'message' => 'Không có nhóm nào đã chốt nguyện vọng.'];
    }

    // 3. Lấy tất cả các đề tài đã được phê duyệt trong đợt này
    $allTopics = $this->_topicStore->getApprovedTopics($batchId);

    // Tạo map để theo dõi slot của từng đề tài
    $topicSlots = [];
    foreach ($allTopics as $topic) {
      $maxStudents = (int)$topic['max_students'];
      $maxGroups = (int)($maxStudents / 2);
      $topicSlots[$topic['id']] = [
        'max_groups' => max(1, $maxGroups),
        'assigned_groups' => (int)($topic['assigned_groups_count'] ?? 0)
      ];
    }

    // 4. Deferred Acceptance Algorithm
    $proposalIndex = [];      // NV tiếp theo mà nhóm sẽ đề xuất
    $tentative = [];           // topic_id => [group data...]
    $freeGroupIds = [];        // Set các nhóm chưa được match

    foreach ($groups as $group) {
      $proposalIndex[$group['id']] = 0;
      $freeGroupIds[$group['id']] = true;
    }

    // Index groups by ID for quick lookup
    $groupsById = [];
    foreach ($groups as $group) {
      $groupsById[$group['id']] = $group;
    }

    $maxIterations = count($groups) * 10; // Safety limit
    $iteration = 0;

    while (!empty($freeGroupIds) && $iteration < $maxIterations) {
      $iteration++;
      $madeProgress = false;

      foreach (array_keys($freeGroupIds) as $groupId) {
        $aspirations = $aspirationsByGroup[$groupId] ?? [];
        $idx = $proposalIndex[$groupId];

        // Hết NV để đề xuất
        if ($idx >= count($aspirations)) {
          unset($freeGroupIds[$groupId]);
          continue;
        }

        $topicId = $aspirations[$idx]['topic_id'];
        $proposalIndex[$groupId]++;
        $madeProgress = true;

        // Đề tài không hợp lệ (không approved hoặc không tồn tại)
        if (!isset($topicSlots[$topicId])) {
          continue;
        }

        // Thêm group vào danh sách ứng viên của topic
        if (!isset($tentative[$topicId])) {
          $tentative[$topicId] = [];
        }
        $tentative[$topicId][$groupId] = $groupsById[$groupId];

        // Topic giữ lại top N nhóm, reject phần còn lại
        $available = $topicSlots[$topicId]['max_groups'] - $topicSlots[$topicId]['assigned_groups'];
        if ($available <= 0) {
          // Đề tài đã hết slot từ trước (assigned_groups_count đã đầy)
          unset($tentative[$topicId][$groupId]);
          continue;
        }

        if (count($tentative[$topicId]) > $available) {
          // Sắp xếp theo tiebreaker: locked_at ASC, created_at ASC
          uasort($tentative[$topicId], function ($a, $b) use ($lockedAtByGroup) {
            $lockA = $lockedAtByGroup[$a['id']] ?? '9999-12-31';
            $lockB = $lockedAtByGroup[$b['id']] ?? '9999-12-31';
            if ($lockA !== $lockB) return strcmp($lockA, $lockB);
            return strcmp($a['created_at'], $b['created_at']);
          });

          // Giữ top $available, reject phần còn lại
          $kept = array_slice($tentative[$topicId], 0, $available, true);
          $rejected = array_diff_key($tentative[$topicId], $kept);

          $tentative[$topicId] = $kept;

          foreach ($rejected as $rGroupId => $rGroup) {
            $freeGroupIds[$rGroupId] = true; // Nhóm bị reject tiếp tục vòng sau
          }
        }

        // Nhóm đã được tạm nhận sẽ được remove khỏi free list
        if (isset($tentative[$topicId][$groupId])) {
          unset($freeGroupIds[$groupId]);
        }
      }

      if (!$madeProgress) break;
    }

    // 5. Commit kết quả
    $successCount = 0;
    $failedCount = 0;

    $matchedGroupIds = [];
    foreach ($tentative as $topicId => $matchedGroups) {
      foreach ($matchedGroups as $groupId => $group) {
        if ($this->_store->assignTopic($groupId, $topicId)) {
          $successCount++;
          $matchedGroupIds[$groupId] = true;
        }
      }
    }

    // Đếm nhóm failed (tham gia thuật toán nhưng không match)
    foreach ($groups as $group) {
      if (!isset($matchedGroupIds[$group['id']])) {
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
