<?php

namespace App\Services;

use App\Stores\ProjectGroupStore;
use App\Stores\ProjectAspirationStore;
use App\Stores\ProjectTopicStore;
use App\Stores\ProjectBatchStore;

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
  public function randomAllocateTopics(int $batchId): array;
  public function manualAssignTopic(int $groupId, int $topicId): bool;
  public function kickIneligibleMembers(int $batchId, array $ineligibleStudentIds): bool;
  public function getPaginatedByBatch(int $batchId, int $page, int $limit = 15, array $filters = []): array;
  public function getTotalCountByBatch(int $batchId, array $filters = []): int;
  public function getStudentsInOtherActiveBatches(int $currentBatchId, array $studentIds): array;
  public function getAllocationStats(int $batchId): array;
  public function getAspirationsByBatch(int $batchId): array;
  public function getExportAllocations(int $batchId, array $filters = [], ?array $sort = null, array $selectedIds = []): array;

  // Exception Handling
  public function getGroupsWithIneligibleMembers(int $batchId): array;
  public function dissolveGroup(int $groupId): bool;
  public function updateSoloApproval(int $groupId, bool $isApproved): bool;
  public function replaceGroupMember(int $groupId, int $oldStudentId, int $newStudentId): bool;
  public function getEligibleUnregisteredStudents(int $batchId): array;
  public function getAssignedGroupsByTeacher(int $batchId, int $teacherId): array;
  public function getAssignedGroupsForPrint(int $batchId, int $teacherId, array $groupIds): array;
  public function saveRegistrationForms(int $batchId, int $teacherId, array $forms): int;
}

class ProjectGroupService implements IProjectGroupService
{
  private ProjectGroupStore $_store;
  private ProjectAspirationStore $_aspirationStore;
  private ProjectTopicStore $_topicStore;
  private ProjectBatchStore $_batchStore;

  public function __construct(
    ProjectGroupStore $store,
    ProjectAspirationStore $aspirationStore,
    ProjectTopicStore $topicStore,
    ProjectBatchStore $batchStore
  ) {
    $this->_store = $store;
    $this->_aspirationStore = $aspirationStore;
    $this->_topicStore = $topicStore;
    $this->_batchStore = $batchStore;
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

  public function getStudentsInOtherActiveBatches(int $currentBatchId, array $studentIds): array
  {
    return $this->_store->getStudentsInOtherActiveBatches($currentBatchId, $studentIds);
  }

  public function getTotalCountByBatch(int $batchId, array $filters = []): int
  {
    return $this->_store->getTotalCountByBatch($batchId, $filters);
  }

  public function getAllocationStats(int $batchId): array
  {
    return $this->_store->getAllocationStats($batchId);
  }

  public function getExportAllocations(int $batchId, array $filters = [], ?array $sort = null, array $selectedIds = []): array
  {
    if (!empty($selectedIds)) {
      $filters['selected_ids'] = $selectedIds;
    }
    
    // Chỉ xuất các nhóm đã được phân công đề tài
    $filters['is_assigned'] = true;
    
    // Sử dụng _store->getPaginatedByBatch để lấy toàn bộ dữ liệu (không phân trang)
    // Hoặc query riêng. getPaginatedByBatch có parameter limit. Truyền limit cực lớn để lấy hết.
    $groups = $this->_store->getPaginatedByBatch($batchId, 1, 999999, $filters);
    
    $exportData = [];
    foreach ($groups as $group) {
      $members = $this->getGroupMembers($group['id']);
      
      $row = [
        'topic_title' => $group['assigned_topic_title'] ?? 'N/A',
        'teacher_name' => $group['assigned_teacher_name'] ?? 'N/A',
      ];
      
      // Thành viên (giả định tối đa 5 thành viên để tạo đủ số cột, tuy nhiên export config bên JS sẽ tự fix số cột)
      for ($i = 0; $i < 3; $i++) { // Hỗ trợ lên đến 3 thành viên theo chuẩn thông thường
        if (isset($members[$i])) {
          $row["mssv_" . ($i + 1)] = $members[$i]['student_code'] ?? $members[$i]['student_id'];
          $row["name_" . ($i + 1)] = $members[$i]['full_name'];
          $row["class_" . ($i + 1)] = $members[$i]['classroom_name'] ?? '';
        } else {
          $row["mssv_" . ($i + 1)] = '';
          $row["name_" . ($i + 1)] = '';
          $row["class_" . ($i + 1)] = '';
        }
      }
      
      $exportData[] = $row;
    }
    
    return $exportData;
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

  // --- Exception Handling ---

  public function getGroupsWithIneligibleMembers(int $batchId): array
  {
    return $this->_store->getGroupsWithIneligibleMembers($batchId);
  }

  public function dissolveGroup(int $groupId): bool
  {
    return $this->_store->dissolveGroup($groupId);
  }

  public function bulkDissolveInvalidGroups(int $batchId): int
  {
    return $this->_store->bulkDissolveInvalidGroups($batchId);
  }

  public function updateSoloApproval(int $groupId, bool $isApproved): bool
  {
    return $this->_store->updateSoloApproval($groupId, $isApproved);
  }

  public function replaceGroupMember(int $groupId, int $oldStudentId, int $newStudentId): bool
  {
    return $this->_store->replaceGroupMember($groupId, $oldStudentId, $newStudentId);
  }

  public function getEligibleUnregisteredStudents(int $batchId): array
  {
    return $this->_store->getEligibleUnregisteredStudents($batchId);
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

    // 3. Lấy dữ liệu Quota Giảng viên và Đề tài
    $supervisors = $this->_batchStore->getSupervisorsByBatchId($batchId);
    $teacherSlots = [];
    foreach ($supervisors as $sup) {
      $maxStudents = (int)$sup['max_students'];
      $maxGroups = $maxStudents === 0 ? PHP_INT_MAX : max(1, (int)floor($maxStudents / 2));
      $teacherSlots[$sup['teacher_id']] = [
        'max_groups' => $maxGroups,
        'assigned_groups' => 0
      ];
    }

    $allTopics = $this->_topicStore->getApprovedTopics($batchId);
    $topicSlots = [];
    foreach ($allTopics as $topic) {
      $maxStudents = (int)$topic['max_students'];
      $maxGroups = (int)($maxStudents / 2);
      $assignedGroups = (int)($topic['assigned_groups_count'] ?? 0);
      $teacherId = $topic['teacher_id'];

      $topicSlots[$topic['id']] = [
        'teacher_id' => $teacherId,
        'max_groups' => max(1, $maxGroups),
        'assigned_groups' => $assignedGroups
      ];

      // Cập nhật số nhóm đã gán cho giảng viên (nếu có gán thủ công từ trước)
      if (isset($teacherSlots[$teacherId])) {
        $teacherSlots[$teacherId]['assigned_groups'] += $assignedGroups;
      }
    }

    // 4. Deferred Acceptance Algorithm
    $proposalIndex = [];      // NV tiếp theo mà nhóm sẽ đề xuất
    $tentative = [];          // teacher_id => [group_id => item]
    $freeGroupIds = [];       // Set các nhóm chưa được match

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

      // Pha 1: Nhóm chưa match đề xuất vào nguyện vọng tiếp theo
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

        $teacherId = $topicSlots[$topicId]['teacher_id'];
        if (!isset($teacherSlots[$teacherId])) {
          continue; // Giảng viên không có trong đợt này?
        }

        if (!isset($tentative[$teacherId])) {
          $tentative[$teacherId] = [];
        }

        // Đưa nhóm vào danh sách ứng viên của giảng viên
        $tentative[$teacherId][$groupId] = [
          'group' => $groupsById[$groupId],
          'topic_id' => $topicId
        ];

        // Tạm thời coi như nhóm đã propose xong, chờ phản hồi của giảng viên
        // Ta xoá nhóm khỏi freeGroupIds ngay lúc này, nếu bị reject GV sẽ nhả lại
        unset($freeGroupIds[$groupId]);
      }

      // Pha 2: Giảng viên duyệt danh sách ứng viên
      foreach ($tentative as $teacherId => &$applicants) {
        // Sắp xếp ưu tiên: chốt nguyện vọng sớm -> tạo nhóm sớm
        uasort($applicants, function ($a, $b) use ($lockedAtByGroup) {
          $lockA = $lockedAtByGroup[$a['group']['id']] ?? '9999-12-31';
          $lockB = $lockedAtByGroup[$b['group']['id']] ?? '9999-12-31';
          if ($lockA !== $lockB) return strcmp($lockA, $lockB);
          return strcmp($a['group']['created_at'], $b['group']['created_at']);
        });

        $kept = [];
        $rejected = [];
        $currentTeacherAssigned = $teacherSlots[$teacherId]['assigned_groups'];
        $teacherMaxGroups = $teacherSlots[$teacherId]['max_groups'];
        $currentTopicAssigned = [];

        foreach ($applicants as $groupId => $item) {
          $topicId = $item['topic_id'];
          if (!isset($currentTopicAssigned[$topicId])) {
            $currentTopicAssigned[$topicId] = $topicSlots[$topicId]['assigned_groups'];
          }
          $topicMaxGroups = $topicSlots[$topicId]['max_groups'];

          // Điều kiện giữ lại: Đề tài còn chỗ VÀ Giảng viên còn chỗ
          if ($currentTopicAssigned[$topicId] < $topicMaxGroups && $currentTeacherAssigned < $teacherMaxGroups) {
            $kept[$groupId] = $item;
            $currentTopicAssigned[$topicId]++;
            $currentTeacherAssigned++;
          } else {
            $rejected[$groupId] = $item;
          }
        }

        $applicants = $kept;

        // Trả các nhóm bị từ chối lại danh sách tự do
        foreach ($rejected as $groupId => $item) {
          $freeGroupIds[$groupId] = true;
        }
      }
      unset($applicants);

      if (!$madeProgress) break;
    }

    // 5. Commit kết quả
    $successCount = 0;
    $failedCount = 0;

    $matchedGroupIds = [];
    foreach ($tentative as $teacherId => $matchedGroups) {
      foreach ($matchedGroups as $groupId => $item) {
        if ($this->_store->assignTopic($groupId, $item['topic_id'])) {
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
      'message' => "Đã phân bổ thành công $successCount nhóm"
    ];
  }

  public function randomAllocateTopics(int $batchId): array
  {
    // 1. Lấy danh sách nhóm hợp lệ CHƯA CÓ đề tài
    $allGroups = $this->_store->getValidGroupsForAllocation($batchId);
    $unassignedGroups = array_filter($allGroups, fn($g) => empty($g['assigned_topic_id']));
    $unassignedGroups = array_values($unassignedGroups);

    if (empty($unassignedGroups)) {
      return ['success' => 0, 'total' => 0, 'message' => 'Không có nhóm nào cần phân bổ ngẫu nhiên.'];
    }

    // 2. Lấy dữ liệu quota giảng viên
    $supervisors = $this->_batchStore->getSupervisorsByBatchId($batchId);
    $teacherSlots = [];
    foreach ($supervisors as $sup) {
      $maxStudents = (int)$sup['max_students'];
      $maxGroups = $maxStudents === 0 ? PHP_INT_MAX : max(1, (int)floor($maxStudents / 2));
      $teacherSlots[$sup['teacher_id']] = [
        'max_groups' => $maxGroups,
        'assigned_groups' => 0
      ];
    }

    // 3. Lấy danh sách đề tài đã duyệt và tính slot còn trống
    $allTopics = $this->_topicStore->getApprovedTopics($batchId);
    $availableSlots = [];

    foreach ($allTopics as $topic) {
      $maxStudents = (int)$topic['max_students'];
      $maxGroups = max(1, (int)($maxStudents / 2));
      $assignedGroups = (int)($topic['assigned_groups_count'] ?? 0);
      $teacherId = $topic['teacher_id'];

      // Cập nhật assigned_groups cho giảng viên
      if (isset($teacherSlots[$teacherId])) {
        $teacherSlots[$teacherId]['assigned_groups'] += $assignedGroups;
      }

      $remainingSlots = $maxGroups - $assignedGroups;
      for ($i = 0; $i < $remainingSlots; $i++) {
        $availableSlots[] = [
          'topic_id' => $topic['id'],
          'teacher_id' => $teacherId
        ];
      }
    }

    // 4. Shuffle cả 2 danh sách để random
    shuffle($unassignedGroups);
    shuffle($availableSlots);

    // 5. Gán 1-1, kiểm tra quota giảng viên
    $successCount = 0;
    $totalUnassigned = count($unassignedGroups);

    foreach ($unassignedGroups as $group) {
      $assigned = false;

      foreach ($availableSlots as $key => $slot) {
        $teacherId = $slot['teacher_id'];

        // Kiểm tra quota giảng viên
        if (isset($teacherSlots[$teacherId]) && $teacherSlots[$teacherId]['assigned_groups'] >= $teacherSlots[$teacherId]['max_groups']) {
          continue;
        }

        // Gán đề tài
        if ($this->_store->assignTopic($group['id'], $slot['topic_id'])) {
          $successCount++;
          if (isset($teacherSlots[$teacherId])) {
            $teacherSlots[$teacherId]['assigned_groups']++;
          }
          unset($availableSlots[$key]);
          $assigned = true;
          break;
        }
      }
    }

    $remainingCount = $totalUnassigned - $successCount;
    $msg = "Đã phân bổ ngẫu nhiên thành công $successCount/$totalUnassigned nhóm.";
    if ($remainingCount > 0) {
      $msg .= " Còn $remainingCount nhóm không thể phân bổ do hết chỗ trống.";
    }

    return ['success' => $successCount, 'total' => $totalUnassigned, 'message' => $msg];
  }

  public function manualAssignTopic(int $groupId, int $topicId): bool
  {
    // Tự động duyệt "làm 1 mình" nếu nhóm chỉ có 1 thành viên
    $members = $this->getGroupMembers($groupId);
    if (count($members) === 1) {
      $this->updateSoloApproval($groupId, true);
    }

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

  public function getAssignedGroupsByTeacher(int $batchId, int $teacherId): array
  {
    return $this->_store->getAssignedGroupsByTeacher($batchId, $teacherId);
  }

  public function getAssignedGroupsForPrint(int $batchId, int $teacherId, array $groupIds): array
  {
    return $this->_store->getAssignedGroupsForPrint($batchId, $teacherId, $groupIds);
  }

  public function saveRegistrationForms(int $batchId, int $teacherId, array $forms): int
  {
    if (!$forms) throw new \InvalidArgumentException('Không có phiếu nào để lưu.');
    $ids = array_map(fn($form) => (int)($form['group_id'] ?? 0), $forms);
    $authorized = $this->_store->getAssignedGroupsForPrint($batchId, $teacherId, $ids);
    $authorizedIds = array_flip(array_map(fn($group) => (int)$group['id'], $authorized));
    if (count($authorizedIds) !== count(array_unique(array_filter($ids)))) {
      throw new \RuntimeException('Bạn không có quyền cập nhật một hoặc nhiều nhóm đã chọn.');
    }

    $sanitize = static function ($html): string {
      $html = trim((string)$html);
      // contenteditable uses <div> for new lines in some browsers. Normalize
      // those blocks before stripping tags so saving never joins their text.
      $html = preg_replace('/<div\b[^>]*>/i', '<p>', $html);
      $html = preg_replace('/<\/div\s*>/i', '</p>', $html);
      // Preserve plain-text line breaks introduced by paste operations too.
      $html = preg_replace('/\R/u', '<br>', $html);
      $html = strip_tags($html, '<p><br><strong><b><em><i><ul><ol><li>');
      $html = preg_replace('/<(\/?)(p|br|strong|b|em|i|ul|ol|li)\b[^>]*>/i', '<$1$2>', $html);
      return $html;
    };

    $saved = 0;
    \Database::getInstance()->transaction(function () use ($forms, $authorizedIds, $sanitize, &$saved) {
      foreach ($forms as $form) {
        $groupId = (int)($form['group_id'] ?? 0);
        if (!$groupId || !isset($authorizedIds[$groupId])) continue;
        $start = trim((string)($form['execution_start'] ?? ''));
        $end = trim((string)($form['execution_end'] ?? ''));
        if (($start && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)) || ($end && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end))) {
          throw new \InvalidArgumentException('Ngày thực hiện đề tài không hợp lệ.');
        }
        if ($start && $end && $start > $end) {
          throw new \InvalidArgumentException('Ngày bắt đầu không được sau ngày kết thúc.');
        }
        $requirements = $sanitize($form['registration_requirements'] ?? '');
        $opinion = $sanitize($form['supervisor_opinion'] ?? '');
        if (mb_strlen($requirements) > 60000 || mb_strlen($opinion) > 60000) {
          throw new \InvalidArgumentException('Nội dung phiếu quá dài.');
        }
        $this->_store->updateRegistrationForm($groupId, [
          'registration_requirements' => $requirements,
          'supervisor_opinion' => $opinion,
          'execution_start' => $start,
          'execution_end' => $end,
        ]);
        $saved++;
      }
    });
    return $saved;
  }
}
