<?php

namespace App\Services;

use App\Stores\ProjectGroupStore;
use App\Stores\ProjectAspirationStore;
use App\Stores\ProjectBatchStore;
use App\Stores\ProjectTopicStore;
use App\Stores\StudentStore;
use App\Enums\ProjectBatchStatus;
use App\Enums\ProjectTopicStatus;
use Exception;
use Database;

interface IProjectRegistrationService
{
  public function registerGroup(int $batchId, int $leaderId, string $memberMssv, array $topicIds): int;
  public function updateGroupAspirations(int $batchId, int $studentId, array $topicIds): bool;
  public function cancelRegistration(int $batchId, int $studentId): bool;
  public function getGroupDetail(int $batchId, int $studentId): ?array;
  public function validateStudentEligibility(string $mssv, int $classOf): bool;
}

class ProjectRegistrationService implements IProjectRegistrationService
{
  private ProjectGroupStore $_groupStore;
  private ProjectAspirationStore $_aspirationStore;
  private ProjectBatchStore $_batchStore;
  private ProjectTopicStore $_topicStore;
  private StudentStore $_studentStore;

  public function __construct(
    ProjectGroupStore $groupStore,
    ProjectAspirationStore $aspirationStore,
    ProjectBatchStore $batchStore,
    ProjectTopicStore $topicStore,
    StudentStore $studentStore
  ) {
    $this->_groupStore = $groupStore;
    $this->_aspirationStore = $aspirationStore;
    $this->_batchStore = $batchStore;
    $this->_topicStore = $topicStore;
    $this->_studentStore = $studentStore;
  }

  public function registerGroup(int $batchId, int $leaderId, string $memberMssv, array $topicIds): int
  {
    $this->validateRegistrationPhase($batchId);
    $batch = $this->_batchStore->getById($batchId);

    // 1. Validate Leader
    $leader = $this->_studentStore->getById($leaderId);
    if (!$leader) {
      throw new Exception('Không tìm thấy thông tin sinh viên đại diện.');
    }

    $this->validateStudentEligibility($leader->student_id, $batch['class_of']);

    if ($this->_groupStore->getGroupByStudent($batchId, $leader->id)) {
      throw new Exception('Bạn đã tham gia một nhóm trong đợt đồ án này.');
    }

    // 2. Validate Member
    if (trim($memberMssv) === $leader->student_id) {
      throw new Exception('Mã số sinh viên thành viên không được trùng với sinh viên đại diện.');
    }

    $member = $this->_studentStore->getByStudentId(trim($memberMssv));
    if (!$member) {
      throw new Exception("Không tìm thấy sinh viên có MSSV: $memberMssv trong hệ thống.");
    }

    $this->validateStudentEligibility($member->student_id, $batch['class_of']);

    if ($this->_groupStore->getGroupByStudent($batchId, $member->id)) {
      throw new Exception("Sinh viên $memberMssv đã tham gia một nhóm khác.");
    }

    // 3. Validate Topics
    if (empty($topicIds) || count($topicIds) > $batch['max_aspirations']) {
      throw new Exception("Bạn phải chọn từ 1 đến {$batch['max_aspirations']} nguyện vọng.");
    }

    // Kiểm tra topic có hợp lệ không (thuộc batch, đã duyệt)
    $uniqueTopics = array_unique($topicIds);
    if (count($uniqueTopics) !== count($topicIds)) {
      throw new Exception('Các nguyện vọng không được trùng lặp.');
    }

    foreach ($uniqueTopics as $topicId) {
      $topic = $this->_topicStore->getById($topicId);
      if (!$topic || $topic['batch_id'] != $batchId || $topic['status'] !== ProjectTopicStatus::APPROVED) {
        throw new Exception('Một hoặc nhiều đề tài không hợp lệ hoặc chưa được duyệt.');
      }
    }

    // 4. Transaction: Create Group -> Add Members -> Add Aspirations
    return Database::getInstance()->transaction(function () use ($batchId, $leader, $member, $uniqueTopics) {
      $groupId = $this->_groupStore->createGroup($batchId, $leader->id);

      $this->_groupStore->addMember($groupId, $leader->id, true);
      $this->_groupStore->addMember($groupId, $member->id, false);

      $this->_aspirationStore->addAspirations($groupId, $uniqueTopics);

      return $groupId;
    });
  }

  public function updateGroupAspirations(int $batchId, int $studentId, array $topicIds): bool
  {
    $this->validateRegistrationPhase($batchId);
    $batch = $this->_batchStore->getById($batchId);

    $group = $this->_groupStore->getGroupByStudent($batchId, $studentId);
    if (!$group) {
      throw new Exception('Bạn chưa đăng ký nhóm nào.');
    }

    if ($group['leader_student_id'] != $studentId) {
      throw new Exception('Chỉ nhóm trưởng mới được phép cập nhật nguyện vọng.');
    }

    if (empty($topicIds) || count($topicIds) > $batch['max_aspirations']) {
      throw new Exception("Bạn phải chọn từ 1 đến {$batch['max_aspirations']} nguyện vọng.");
    }

    $uniqueTopics = array_unique($topicIds);
    if (count($uniqueTopics) !== count($topicIds)) {
      throw new Exception('Các nguyện vọng không được trùng lặp.');
    }

    foreach ($uniqueTopics as $topicId) {
      $topic = $this->_topicStore->getById($topicId);
      if (!$topic || $topic['batch_id'] != $batchId || $topic['status'] !== ProjectTopicStatus::APPROVED) {
        throw new Exception('Một hoặc nhiều đề tài không hợp lệ hoặc chưa được duyệt.');
      }
    }

    return $this->_aspirationStore->addAspirations($group['id'], $uniqueTopics);
  }

  public function cancelRegistration(int $batchId, int $studentId): bool
  {
    $this->validateRegistrationPhase($batchId);

    $group = $this->_groupStore->getGroupByStudent($batchId, $studentId);
    if (!$group) {
      throw new Exception('Bạn chưa đăng ký nhóm nào.');
    }

    if ($group['leader_student_id'] != $studentId) {
      throw new Exception('Chỉ sinh viên đại diện (nhóm trưởng) mới được phép hủy đăng ký.');
    }

    return $this->_groupStore->deleteGroup($group['id']);
  }

  public function getGroupDetail(int $batchId, int $studentId): ?array
  {
    $group = $this->_groupStore->getGroupByStudent($batchId, $studentId);
    if (!$group) {
      return null;
    }

    // Enhance group details
    $fullGroup = $this->_groupStore->getGroupById($group['id']);
    $members = $this->_groupStore->getGroupMembers($group['id']);
    $aspirations = $this->_aspirationStore->getAspirationsByGroup($group['id']);

    $fullGroup['members'] = $members;
    $fullGroup['aspirations'] = $aspirations;

    return $fullGroup;
  }

  public function validateStudentEligibility(string $mssv, int $classOf): bool
  {
    // Format: Level (2) + Major (2) + Year (2) + Suffix
    if (strlen($mssv) < 8) {
      throw new Exception("MSSV $mssv không hợp lệ.");
    }

    $level = substr($mssv, 0, 2);
    $major = substr($mssv, 2, 2);
    $year = substr($mssv, 4, 2);

    $batchYear = (int) substr((string)$classOf, -2);
    $studentYear = (int) $year;

    if (!in_array($level, ['03', '04'])) {
      throw new Exception("Sinh viên $mssv không thuộc hệ Cao đẳng (Mã hệ $level).");
    }

    if ($major !== '06') {
      throw new Exception("Sinh viên $mssv không thuộc Khoa CNTT (Mã ngành $major).");
    }

    if ($studentYear > $batchYear) {
      throw new Exception("Sinh viên $mssv (Khóa $year) không đủ điều kiện. Đợt này chỉ dành cho Khóa $batchYear trở về trước.");
    }

    return true;
  }

  private function validateRegistrationPhase(int $batchId): void
  {
    $batch = $this->_batchStore->getById($batchId);
    if (!$batch) {
      throw new Exception('Không tìm thấy đợt đồ án.');
    }

    if ($batch['status'] !== ProjectBatchStatus::PUBLISHED) {
      throw new Exception('Đợt đồ án chưa được công bố hoặc đã đóng.');
    }

    $now = time();
    $start = strtotime((string) $batch['registration_start']);
    $end = strtotime((string) $batch['registration_end']);

    if ($now < $start || $now > $end) {
      throw new Exception('Đang ngoài thời gian đăng ký đồ án.');
    }
  }
}
