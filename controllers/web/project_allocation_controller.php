<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\ProjectGroupService;
use App\Services\ProjectBatchService;
use App\Services\ProjectTopicService;
use App\Services\ProjectEligibilityService;
use App\Enums\ProjectBatchStatus;
use App\Core\Files\XlsxReader;
use Exception;

class ProjectAllocationController extends Controller
{
  private ProjectGroupService $_groupService;
  private ProjectBatchService $_batchService;
  private ProjectTopicService $_topicService;
  private ProjectEligibilityService $_eligibilityService;

  public function __construct(
    ProjectGroupService $groupService,
    ProjectBatchService $batchService,
    ProjectTopicService $topicService,
    ProjectEligibilityService $eligibilityService
  ) {
    $this->_groupService = $groupService;
    $this->_batchService = $batchService;
    $this->_topicService = $topicService;
    $this->_eligibilityService = $eligibilityService;
  }

  public function index(Request $request, $id)
  {
    $batchId = $id;
    $batch = $this->_batchService->getBatchById((int)$batchId);
    if (!$batch) {
      $request->session()->flashNotify('error', 'Không tìm thấy đợt đồ án.');
      return $this->redirect('/admin/project_batches');
    }



    $totalGroups = $this->_groupService->getTotalCountByBatch($batch['id']);
    $assignedGroupsCount = $this->_groupService->getTotalCountByBatch($batch['id'], ['is_assigned' => true]);
    $unassignedGroupsCount = $totalGroups - $assignedGroupsCount;

    $topics = $this->_topicService->getApprovedTopics($batch['id']);

    // Import Exception Data
    $incompleteGroups = $this->_groupService->getGroupsWithIneligibleMembers($batch['id']);
    foreach ($incompleteGroups as &$ig) {
      $ig['members'] = $this->_groupService->getGroupMembers($ig['id']);
    }
    
    $eligibleUnregisteredStudents = $this->_groupService->getEligibleUnregisteredStudents($batch['id']);
    $previewData = $request->session()->get('eligibility_preview_' . $batchId);

    return $this->render('admin/project_batches/allocation', [
      'batchObj' => (object)$batch,
      'topics' => $topics,
      'stats' => [
        'total' => $totalGroups,
        'assigned' => $assignedGroupsCount,
        'unassigned' => $unassignedGroupsCount
      ],
      'currentFilter' => $request->query('status', 'all'),
      'incompleteGroups' => $incompleteGroups,
      'eligibleUnregisteredStudents' => $eligibleUnregisteredStudents,
      'previewData' => $previewData
    ], layout: 'dashboard_layout');
  }

  public function autoAllocate(Request $request, $id)
  {
    $batchId = $id;
    if (!$request->isMethod('POST')) {
      return $this->redirect('/admin/project_batches/' . $batchId . '/allocation');
    }

    $batch = $this->_batchService->getBatchById((int)$batchId);
    if (!$batch) {
      $request->session()->flashNotify('error', 'Không tìm thấy đợt đồ án.');
      return $this->redirect('/admin/project_batches');
    }

    if ($batch['status'] !== ProjectBatchStatus::PUBLISHED) {
      $request->session()->flashNotify('error', 'Đợt đồ án chưa công bố hoặc đã kết thúc.');
      return $this->redirect('/admin/project_batches/' . $batchId . '/allocation');
    }

    try {
      $result = $this->_groupService->autoAllocateTopics((int)$batchId);
      $request->session()->flashNotify('success', $result['message']);
    } catch (Exception $e) {
      $request->session()->flashNotify('error', 'Lỗi phân bổ: ' . $e->getMessage());
    }

    return $this->redirect('/admin/project_batches/' . $batchId . '/allocation');
  }

  public function manualAssign(Request $request, $id)
  {
    $batchId = $id;
    if (!$request->isMethod('POST')) {
      return $this->redirect('/admin/project_batches/' . $batchId . '/allocation');
    }

    $groupId = (int)$request->input('group_id');
    $topicId = (int)$request->input('topic_id');

    if (!$groupId || !$topicId) {
      $request->session()->flashNotify('error', 'Dữ liệu không hợp lệ.');
      return $this->redirect('/admin/project_batches/' . $batchId . '/allocation');
    }

    try {
      $this->_groupService->manualAssignTopic($groupId, $topicId);
      $batch = $this->_batchService->getBatchById((int)$batchId);
      if (!empty($batch['allocation_published_at'])) {
        $request->session()->flashNotify('success', 'Đã phân bổ thành công. Sinh viên sẽ nhìn thấy kết quả ngay lập tức.');
      } else {
        $request->session()->flashNotify('success', 'Gán đề tài thành công.');
      }
    } catch (Exception $e) {
      $request->session()->flashNotify('error', 'Lỗi: ' . $e->getMessage());
    }

    return $this->redirect('/admin/project_batches/' . $batchId . '/allocation');
  }

  // --- IMPORT EXCEL ---
  public function previewImport(Request $request, $id)
  {
    $batchId = $id;
    if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
      $request->session()->flashNotify('error', 'Vui lòng chọn file Excel hợp lệ.', '');
      return $this->redirect('/admin/project_batches/' . $batchId . '/allocation');
    }

    $tmpPath = $_FILES['excel_file']['tmp_name'];
    try {
      require_once BASE_PATH . '/includes/files/xlsx_reader.php';
      $reader = XlsxReader::open($tmpPath);
      $excelStudentCodes = [];

      foreach ($reader->rows(2) as $row) {
        $mssv = trim((string)($row[2] ?? ''));
        if ($mssv !== '') {
          $excelStudentCodes[] = $mssv;
        }
      }

      $previewData = $this->_eligibilityService->previewExcelData((int)$batchId, $excelStudentCodes);
      $request->session()->put('eligibility_preview_' . $batchId, $previewData);

      return $this->redirect('/admin/project_batches/' . $batchId . '/allocation');
    } catch (Exception $e) {
      $request->session()->flashNotify('error', 'Lỗi đọc file Excel: ' . $e->getMessage(), '');
      return $this->redirect('/admin/project_batches/' . $batchId . '/allocation');
    }
  }

  public function confirmImport(Request $request, $id)
  {
    $batchId = $id;
    if (!$request->isMethod('POST')) return $this->redirect('/admin/project_batches/' . $batchId . '/allocation');

    $previewData = $request->session()->get('eligibility_preview_' . $batchId);
    if (!$previewData) {
      $request->session()->flashNotify('error', 'Không tìm thấy dữ liệu Preview.', '');
      return $this->redirect('/admin/project_batches/' . $batchId . '/allocation');
    }

    try {
      $this->_eligibilityService->processConfirmedData((int)$batchId, $previewData);
      $request->session()->forget('eligibility_preview_' . $batchId);
      $request->session()->flashNotify('success', 'Import danh sách sinh viên đủ điều kiện thành công!');

      $batch = $this->_batchService->getBatchById((int)$batchId);
      if (!empty($batch['allocation_published_at'])) {
        $request->session()->flashNotify('warning', 'Lưu ý: Đợt này đã công bố kết quả cho sinh viên. Các nhóm bị ảnh hưởng cần được xử lý thủ công và thông báo cho họ.');
      }
    } catch (Exception $e) {
      $request->session()->flashNotify('error', 'Lỗi khi import: ' . $e->getMessage(), '');
    }

    return $this->redirect('/admin/project_batches/' . $batchId . '/allocation');
  }

  // --- EXCEPTION HANDLING ---
  public function handleDissolveGroup(Request $request, $id)
  {
    $groupId = (int)$request->input('group_id');
    if ($this->_groupService->dissolveGroup($groupId)) {
        $request->session()->flashNotify('success', 'Đã giải tán nhóm thành công.');
    } else {
        $request->session()->flashNotify('error', 'Lỗi khi giải tán nhóm.');
    }
    return $this->redirect('/admin/project_batches/' . $id . '/allocation');
  }

  public function handleBulkDissolveInvalidGroups(Request $request, $id)
  {
    $batchId = (int)$id;
    $deletedCount = $this->_groupService->bulkDissolveInvalidGroups($batchId);
    
    if ($deletedCount > 0) {
      $request->session()->flashNotify('success', "Đã giải tán thành công $deletedCount nhóm không hợp lệ.");
    } else {
      $request->session()->flashNotify('info', 'Không có nhóm nào cần giải tán.');
    }
    
    return $this->redirect('/admin/project_batches/' . $batchId . '/allocation');
  }

  public function handleApproveSolo(Request $request, $id)
  {
    $groupId = (int)$request->input('group_id');
    if ($this->_groupService->updateSoloApproval($groupId, true)) {
        $request->session()->flashNotify('success', 'Đã cho phép sinh viên làm đồ án 1 mình.');
    } else {
        $request->session()->flashNotify('error', 'Lỗi khi duyệt.');
    }
    return $this->redirect('/admin/project_batches/' . $id . '/allocation');
  }

  public function handleReplaceMember(Request $request, $id)
  {
    $groupId = (int)$request->input('group_id');
    $oldStudentId = (int)$request->input('old_student_id');
    $newStudentId = (int)$request->input('new_student_id');

    if (!$newStudentId) {
        $request->session()->flashNotify('error', 'Vui lòng chọn sinh viên thay thế.');
        return $this->redirect('/admin/project_batches/' . $id . '/allocation');
    }

    if ($this->_groupService->replaceGroupMember($groupId, $oldStudentId, $newStudentId)) {
        $request->session()->flashNotify('success', 'Đã thay thế thành viên nhóm thành công.');
    } else {
        $request->session()->flashNotify('error', 'Lỗi khi thay thế thành viên.');
    }
    return $this->redirect('/admin/project_batches/' . $id . '/allocation');
  }

  public function publishAllocation(Request $request, $id)
  {
    $batchId = $id;
    $batch = $this->_batchService->getBatchById((int)$batchId);
    if (!$batch) return $this->redirect('/admin/project_batches');

    // Pre-check: Đếm nhóm hợp lệ chưa có đề tài
    $stats = $this->_groupService->getAllocationStats((int)$batchId);
    $orphanCount = $stats['unassigned'] ?? 0;

    if ($orphanCount > 0 && !$request->input('force')) {
      $request->session()->flashNotify(
        'warning',
        "Có {$orphanCount} nhóm chưa được phân công đề tài. Cần bấm lại để xác nhận."
      );
      return $this->redirect("/admin/project_batches/{$batchId}/allocation");
    }

    // Set cờ đã công bố phân công
    $this->_batchService->setAllocationPublished((int)$batchId);
    $request->session()->flashNotify('success', 'Đã công bố kết quả phân bổ cho sinh viên và giảng viên.');
    return $this->redirect("/admin/project_batches/{$batchId}/allocation");
  }

  public function unpublishAllocation(Request $request, $id)
  {
    $batchId = $id;
    $batch = $this->_batchService->getBatchById((int)$batchId);
    if (!$batch) return $this->redirect('/admin/project_batches');

    $this->_batchService->unpublishAllocation((int)$batchId);
    $request->session()->flashNotify('success', 'Đã thu hồi kết quả phân bổ. Sinh viên và Giảng viên sẽ không còn xem được kết quả.');
    return $this->redirect("/admin/project_batches/{$batchId}/allocation");
  }
}
