<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Enums\ProjectBatchStatus;
use App\Models\ProjectBatch;
use App\Services\{StudentService, ProjectBatchService, ProjectTopicService, ProjectGroupService, ProjectAspirationService};
use Exception;

class StudentProjectDashboardController extends Controller
{
  private StudentService $_studentService;
  private ProjectBatchService $_projectBatchService;
  private ProjectGroupService $_projectGroupService;
  private ProjectTopicService $_projectTopicService;
  private ProjectAspirationService $_projectAspirationService;

  public function __construct(
    StudentService $studentService,
    ProjectBatchService $projectBatchService,
    ProjectGroupService $projectGroupService,
    ProjectTopicService $projectTopicService,
    ProjectAspirationService $projectAspirationService
  ) {
    $this->_studentService = $studentService;
    $this->_projectBatchService = $projectBatchService;
    $this->_projectGroupService = $projectGroupService;
    $this->_projectTopicService = $projectTopicService;
    $this->_projectAspirationService = $projectAspirationService;
  }

  /**
   * Kiểm tra điều kiện tham gia đợt đồ án của sinh viên
   */
  private function checkEligibility(string $studentId, array $batch): array
  {
    if (strlen($studentId) < 6) {
      return [
        'isEligible' => false,
        'reason' => 'Mã số sinh viên không hợp lệ (không đủ độ dài).'
      ];
    }

    $bacHoc = substr($studentId, 0, 2);
    $nganh = substr($studentId, 2, 2);
    $khoa = (int) substr($studentId, 4, 2);

    if (!in_array($bacHoc, ['03', '04'])) {
      return [
        'isEligible' => false,
        'reason' => 'Chỉ dành cho sinh viên hệ Cao đẳng hoặc Cao đẳng nghề.'
      ];
    }

    if ($nganh !== '06') {
      return [
        'isEligible' => false,
        'reason' => 'Chỉ dành cho sinh viên ngành Công nghệ thông tin.'
      ];
    }

    $minClass = $batch['min_class_of'] ?? 0;
    $maxClass = $batch['max_class_of'] ?? 0;

    if ($minClass > 0 && $maxClass > 0) {
      if ((int)$khoa < $minClass || (int)$khoa > $maxClass) {
        return [
          'isEligible' => false,
          'reason' => "Đợt đồ án này chỉ dành cho sinh viên khóa {$minClass} đến khóa {$maxClass}."
        ];
      }
    }

    return [
      'isEligible' => true,
      'reason' => ''
    ];
  }

  /**
   * Danh sách các đợt đồ án
   * 
   * @param Request $request
   */
  public function index(Request $request)
  {
    $authUser = $request->session()->authUser();
    if (!$authUser) {
      return $this->redirect('/login');
    }

    $student = $this->_studentService->getStudentByAccountId($authUser['account_id']);
    if (!$student) {
      $request->session()->flashNotify('error', 'Không tìm thấy thông tin sinh viên.');
      return $this->redirect('/');
    }

    $allActiveBatches = $this->_projectBatchService->getActiveBatches();
    $visibleBatches = [];

    foreach ($allActiveBatches as $batchData) {
      $model = new ProjectBatch(
        status: $batchData['status'],
        topic_proposal_start: $batchData['topic_proposal_start'],
        topic_proposal_end: $batchData['topic_proposal_end'],
        registration_start: $batchData['registration_start'],
        registration_end: $batchData['registration_end']
      );

      $phase = $model->getEffectivePhase();
      // Sinh viên chỉ xem được khi đã tới giai đoạn đăng ký (registration) hoặc sau đó (reviewing)
      if (in_array($phase, [ProjectBatchStatus::REGISTRATION, ProjectBatchStatus::REVIEWING, ProjectBatchStatus::CLOSED])) {
        $visibleBatches[] = $batchData;
      }
    }

    return $this->render('student/project_batches/index', [
      'title' => 'Danh sách đợt đồ án',
      'student' => $student,
      'batches' => $visibleBatches
    ], layout: 'dashboard_layout');
  }

  /**
   * Hiển thị trang tổng quan đồ án tốt nghiệp sinh viên cho 1 đợt cụ thể
   * 
   * @param Request $request
   * @param int $id
   */
  public function show(Request $request, int $id)
  {
    $authUser = $request->session()->authUser();
    if (!$authUser) {
      return $this->redirect('/login');
    }

    $student = $this->_studentService->getStudentByAccountId($authUser['account_id']);
    if (!$student) {
      $request->session()->flashNotify('error', 'Không tìm thấy thông tin sinh viên.');
      return $this->redirect('/');
    }

    $currentBatch = $this->_projectBatchService->getBatchById($id);
    if (!$currentBatch) {
      $request->session()->flashNotify('error', 'Đợt đồ án không tồn tại.');
      return $this->redirect('/student/project_batches');
    }

    $model = new ProjectBatch(
      status: $currentBatch['status'],
      topic_proposal_start: $currentBatch['topic_proposal_start'],
      topic_proposal_end: $currentBatch['topic_proposal_end'],
      registration_start: $currentBatch['registration_start'],
      registration_end: $currentBatch['registration_end']
    );

    $phase = $model->getEffectivePhase();
    if (!in_array($phase, [ProjectBatchStatus::REGISTRATION, ProjectBatchStatus::REVIEWING, ProjectBatchStatus::CLOSED])) {
      $request->session()->flashNotify('error', 'Đợt đồ án này chưa mở cho sinh viên tham gia.');
      return $this->redirect('/student/project_batches');
    }

    $eligibility = $this->checkEligibility($student->student_id, $currentBatch);

    $group = null;
    $groupMembers = [];
    $aspirations = [];
    $isLeader = false;
    $isLocked = false;

    if ($eligibility['isEligible']) {
      $group = $this->_projectGroupService->getGroupByStudent($currentBatch['id'], $student->id);
      if ($group) {
        $groupMembers = $this->_projectGroupService->getGroupMembers($group['id']);
        $aspirations = $this->_projectAspirationService->getAspirationsByGroup($group['id']);
        $isLocked = $this->_projectAspirationService->isLocked($group['id']);
        foreach ($groupMembers as $member) {
          if ($member['student_id'] == $student->id && $member['is_leader']) {
            $isLeader = true;
          }
        }
      }
    }

    return $this->render('student/project_batches/show', [
      'title' => 'Đồ án tốt nghiệp - ' . htmlspecialchars($currentBatch['title']),
      'student' => $student,
      'currentBatch' => $currentBatch,
      'isEligible' => $eligibility['isEligible'],
      'ineligibilityReason' => $eligibility['reason'],
      'group' => $group,
      'groupMembers' => $groupMembers,
      'aspirations' => $aspirations,
      'isLeader' => $isLeader,
      'isLocked' => $isLocked
    ], layout: 'dashboard_layout');
  }

  /**
   * Danh sách đề tài (Topics)
   */
  public function topics(Request $request, int $id)
  {
    $authUser = $request->session()->authUser();
    if (!$authUser) return $this->redirect('/login');

    $student = $this->_studentService->getStudentByAccountId($authUser['account_id']);
    if (!$student) return $this->redirect('/');

    $currentBatch = $this->_projectBatchService->getBatchById($id);
    if (!$currentBatch) return $this->redirect('/student/project_batches');

    $group = $this->_projectGroupService->getGroupByStudent($id, $student->id);
    if (!$group) {
      $request->session()->flashNotify('error', 'Bạn cần tham gia nhóm trước khi đăng ký nguyện vọng.');
      return $this->redirect("/student/project_batches/{$id}");
    }

    $isLeader = false;
    $isConfirmed = false;
    $members = $this->_projectGroupService->getGroupMembers($group['id']);
    foreach ($members as $member) {
      if ($member['student_id'] == $student->id) {
        $isLeader = (bool)$member['is_leader'];
        $isConfirmed = (bool)$member['is_confirmed'];
      }
    }

    $page = (int)$request->input('page', 1);
    // Danh sách toàn bộ đề tài đã duyệt của đợt
    $topicsData = $this->_projectTopicService->getPaginatedByBatch($id, $page, 50, ['status' => 'approved']);
    $aspirations = $this->_projectAspirationService->getAspirationsByGroup($group['id']);
    $aspirationTopicIds = array_column($aspirations, 'topic_id');
    $isLocked = $this->_projectAspirationService->isLocked($group['id']);

    return $this->render('student/project_batches/topics', [
      'title' => 'Đăng ký đề tài - ' . htmlspecialchars($currentBatch['title']),
      'student' => $student,
      'currentBatch' => $currentBatch,
      'group' => $group,
      'isLeader' => $isLeader,
      'isConfirmed' => $isConfirmed,
      'topics' => $topicsData->getItems(),
      'pagination' => $topicsData,
      'aspirationTopicIds' => $aspirationTopicIds,
      'maxAspirations' => $currentBatch['max_aspirations'] ?? 3,
      'isLocked' => $isLocked
    ], layout: 'dashboard_layout');
  }

  /**
   * Thêm nguyện vọng
   */
  public function addAspiration(Request $request, int $id)
  {
    $authUser = $request->session()->authUser();
    $student = $this->_studentService->getStudentByAccountId($authUser['account_id']);
    $group = $this->_projectGroupService->getGroupByStudent($id, $student->id);

    if ($group && $group['leader_student_id'] == $student->id) {
      if ($this->_projectAspirationService->isLocked($group['id'])) {
        $request->session()->flashNotify('error', 'Nguyện vọng đã được chốt, không thể thay đổi.');
        return $this->redirect("/student/project_batches/{$id}/topics");
      }

      $topicId = (int)$request->input('topic_id');
      $aspirations = $this->_projectAspirationService->getAspirationsByGroup($group['id']);
      $maxAspirations = $this->_projectBatchService->getBatchById($id)['max_aspirations'] ?? 3;

      if (count($aspirations) >= $maxAspirations) {
        $request->session()->flashNotify('error', "Bạn chỉ được đăng ký tối đa {$maxAspirations} nguyện vọng.");
      } else {
        $topicIds = array_column($aspirations, 'topic_id');
        if (!in_array($topicId, $topicIds)) {
          $topicIds[] = $topicId;
          $this->_projectAspirationService->addAspirations($group['id'], $topicIds);
          $request->session()->flashNotify('success', 'Đã thêm vào danh sách nguyện vọng.');
        } else {
          $request->session()->flashNotify('error', 'Đề tài này đã có trong danh sách nguyện vọng.');
        }
      }
    } else {
      $request->session()->flashNotify('error', 'Chỉ nhóm trưởng mới có quyền đăng ký nguyện vọng.');
    }
    return $this->redirect("/student/project_batches/{$id}/topics");
  }

  /**
   * Xóa nguyện vọng
   */
  public function removeAspiration(Request $request, int $id)
  {
    $authUser = $request->session()->authUser();
    $student = $this->_studentService->getStudentByAccountId($authUser['account_id']);
    $group = $this->_projectGroupService->getGroupByStudent($id, $student->id);

    if ($group && $group['leader_student_id'] == $student->id) {
      if ($this->_projectAspirationService->isLocked($group['id'])) {
        $request->session()->flashNotify('error', 'Nguyện vọng đã được chốt, không thể thay đổi.');
        return $this->redirect("/student/project_batches/{$id}");
      }

      $topicId = (int)$request->input('topic_id');
      $aspirations = $this->_projectAspirationService->getAspirationsByGroup($group['id']);
      $topicIds = array_column($aspirations, 'topic_id');

      $newTopicIds = array_filter($topicIds, fn($id) => $id !== $topicId);
      $this->_projectAspirationService->addAspirations($group['id'], array_values($newTopicIds));
      $request->session()->flashNotify('success', 'Đã xóa khỏi danh sách nguyện vọng.');
    }
    return $this->redirect("/student/project_batches/{$id}");
  }

  /**
   * Sắp xếp lại nguyện vọng
   */
  public function reorderAspirations(Request $request, int $id)
  {
    $authUser = $request->session()->authUser();
    $student = $this->_studentService->getStudentByAccountId($authUser['account_id']);
    $group = $this->_projectGroupService->getGroupByStudent($id, $student->id);

    if ($group && $group['leader_student_id'] == $student->id) {
      if ($this->_projectAspirationService->isLocked($group['id'])) {
        $request->session()->flashNotify('error', 'Nguyện vọng đã được chốt, không thể thay đổi.');
        return $this->redirect("/student/project_batches/{$id}");
      }

      // Nhận 1 mảng topic_ids đã được sắp xếp từ client
      $topicIds = $request->input('topic_ids', []);
      if (!empty($topicIds) && is_array($topicIds)) {
        // Có thể bổ sung check topic_ids phải thuộc group
        $this->_projectAspirationService->addAspirations($group['id'], array_map('intval', $topicIds));
        $request->session()->flashNotify('success', 'Đã cập nhật thứ tự nguyện vọng.');
      }
    }
    return $this->redirect("/student/project_batches/{$id}");
  }

  public function lockAspirations(Request $request, int $id)
  {
    $authUser = $request->session()->authUser();
    $student = $this->_studentService->getStudentByAccountId($authUser['account_id']);
    $group = $this->_projectGroupService->getGroupByStudent($id, $student->id);

    if ($group && $group['leader_student_id'] == $student->id) {
      if ($this->_projectAspirationService->lockAspirations($group['id'])) {
        $request->session()->flashNotify('success', 'Đã chốt nguyện vọng thành công.');
      } else {
        $request->session()->flashNotify('error', 'Có lỗi khi chốt nguyện vọng hoặc nguyện vọng đã được chốt.');
      }
    } else {
      $request->session()->flashNotify('error', 'Chỉ nhóm trưởng mới có quyền chốt nguyện vọng.');
    }
    return $this->redirect("/student/project_batches/{$id}");
  }

  public function unlockAspirations(Request $request, int $id)
  {
    $authUser = $request->session()->authUser();
    $student = $this->_studentService->getStudentByAccountId($authUser['account_id']);
    $group = $this->_projectGroupService->getGroupByStudent($id, $student->id);

    if ($group && $group['leader_student_id'] == $student->id) {
      if ($this->_projectAspirationService->unlockAspirations($group['id'])) {
        $request->session()->flashNotify('success', 'Đã mở khóa nguyện vọng thành công. Lưu ý: tiebreaker của nhóm đã bị đặt lại.');
      } else {
        $request->session()->flashNotify('error', 'Có lỗi xảy ra hoặc nguyện vọng chưa được chốt.');
      }
    } else {
      $request->session()->flashNotify('error', 'Chỉ nhóm trưởng mới có quyền mở khóa nguyện vọng.');
    }
    return $this->redirect("/student/project_batches/{$id}");
  }

  /**
   * Tạo nhóm và mời thành viên
   */
  public function createGroup(Request $request, int $id)
  {
    $authUser = $request->session()->authUser();
    if (!$authUser) return $this->redirect('/login');

    $student = $this->_studentService->getStudentByAccountId($authUser['account_id']);
    if (!$student) return $this->redirect('/');

    $currentBatch = $this->_projectBatchService->getBatchById($id);
    if (!$currentBatch) return $this->redirect('/student/project_batches');

    $existingGroup = $this->_projectGroupService->getGroupByStudent($id, $student->id);
    $maxStudents = $currentBatch['max_students'] ?? 2;

    if ($existingGroup) {
      if ($existingGroup['leader_student_id'] != $student->id) {
        $request->session()->flashNotify('error', 'Chỉ nhóm trưởng mới có quyền mời thêm thành viên.');
        return $this->redirect("/student/project_batches/{$id}");
      }

      $groupMembers = $this->_projectGroupService->getGroupMembers($existingGroup['id']);
      if (count($groupMembers) >= $maxStudents) {
        $request->session()->flashNotify('error', 'Nhóm đã đủ số lượng thành viên.');
        return $this->redirect("/student/project_batches/{$id}");
      }
    }

    $partnerMssv = trim($request->input('partner_mssv', ''));
    if (empty($partnerMssv)) {
      $request->session()->flashNotify('error', 'Vui lòng nhập MSSV của bạn cùng nhóm.');
      return $this->redirect("/student/project_batches/{$id}");
    }

    if ($partnerMssv === $student->student_id) {
      $request->session()->flashNotify('error', 'Không thể tự mời chính mình.');
      return $this->redirect("/student/project_batches/{$id}");
    }

    $partner = $this->_studentService->getStudentByStudentId($partnerMssv);
    if (!$partner) {
      $request->session()->flashNotify('error', 'Không tìm thấy sinh viên có MSSV này.');
      return $this->redirect("/student/project_batches/{$id}");
    }

    $eligibility = $this->checkEligibility($partner->student_id, $currentBatch);
    if (!$eligibility['isEligible']) {
      $request->session()->flashNotify('error', "Sinh viên được mời không đủ điều kiện: {$eligibility['reason']}");
      return $this->redirect("/student/project_batches/{$id}");
    }

    $partnerExistingGroup = $this->_projectGroupService->getGroupByStudent($id, $partner->id);
    if ($partnerExistingGroup) {
      $request->session()->flashNotify('error', 'Sinh viên này đã tham gia một nhóm khác.');
      return $this->redirect("/student/project_batches/{$id}");
    }

    try {
      if ($existingGroup) {
        $this->_projectGroupService->addMember($existingGroup['id'], $partner->id, false, false);
        $request->session()->flashNotify('success', 'Đã gửi lời mời thành công.');
      } else {
        $this->_projectGroupService->createGroupWithMembers($id, $student->id, $partner->id);
        $request->session()->flashNotify('success', 'Đã tạo nhóm và gửi lời mời thành công.');
      }
    } catch (Exception $e) {
      $request->session()->flashNotify('error', 'Có lỗi xảy ra khi thao tác.');
    }

    return $this->redirect("/student/project_batches/{$id}");
  }

  /**
   * Thành viên được mời đồng ý vào nhóm
   */
  public function confirmGroup(Request $request, int $id)
  {
    $authUser = $request->session()->authUser();
    $student = $this->_studentService->getStudentByAccountId($authUser['account_id']);
    $group = $this->_projectGroupService->getGroupByStudent($id, $student->id);

    if ($group) {
      $this->_projectGroupService->confirmMember($group['id'], $student->id);
      $request->session()->flashNotify('success', 'Bạn đã gia nhập nhóm thành công.');
    }
    return $this->redirect("/student/project_batches/{$id}");
  }

  /**
   * Thành viên được mời từ chối vào nhóm
   */
  public function rejectGroup(Request $request, int $id)
  {
    $authUser = $request->session()->authUser();
    $student = $this->_studentService->getStudentByAccountId($authUser['account_id']);
    $group = $this->_projectGroupService->getGroupByStudent($id, $student->id);

    if ($group && $group['leader_student_id'] != $student->id) {
      $this->_projectGroupService->removeMember($group['id'], $student->id);
      // Optional: Delete group if it's empty. Since leader is still there, we just leave it with 1 member.
      $request->session()->flashNotify('success', 'Bạn đã từ chối lời mời vào nhóm.');
    }
    return $this->redirect("/student/project_batches/{$id}");
  }

  /**
   * Nhóm trưởng hủy lời mời (xóa thành viên)
   */
  public function cancelGroupInvite(Request $request, int $id)
  {
    $authUser = $request->session()->authUser();
    $student = $this->_studentService->getStudentByAccountId($authUser['account_id']);
    $group = $this->_projectGroupService->getGroupByStudent($id, $student->id);

    if ($group && $group['leader_student_id'] == $student->id) {
      $memberIdToRemove = (int)$request->input('student_id');
      if ($memberIdToRemove !== $student->id) {
        $this->_projectGroupService->removeMember($group['id'], $memberIdToRemove);
        $request->session()->flashNotify('success', 'Đã hủy lời mời/xóa thành viên.');
      }
    }
    return $this->redirect("/student/project_batches/{$id}");
  }
}
