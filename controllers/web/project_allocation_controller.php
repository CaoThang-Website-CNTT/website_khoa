<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\ProjectGroupService;
use App\Services\ProjectBatchService;
use App\Services\ProjectTopicService;
use App\Enums\ProjectBatchStatus;
use Exception;

class ProjectAllocationController extends Controller
{
  private ProjectGroupService $_groupService;
  private ProjectBatchService $_batchService;
  private ProjectTopicService $_topicService;

  public function __construct(
    ProjectGroupService $groupService,
    ProjectBatchService $batchService,
    ProjectTopicService $topicService
  ) {
    $this->_groupService = $groupService;
    $this->_batchService = $batchService;
    $this->_topicService = $topicService;
  }

  public function index(Request $request, $id)
  {
    $batchId = $id;
    $batch = $this->_batchService->getBatchById((int)$batchId);
    if (!$batch) {
      $request->session()->flashNotify('error', 'Không tìm thấy đợt đồ án.');
      return $this->redirect('/admin/project_batches');
    }

    $page = (int)$request->query('page', 1);
    $limit = 50;

    $filters = [];
    $status = $request->query('status');
    if ($status === 'assigned') {
      $filters['is_assigned'] = true;
    } elseif ($status === 'unassigned') {
      $filters['is_assigned'] = false;
    }

    // groups array
    $groups = $this->_groupService->getPaginatedByBatch($batch['id'], $page, $limit, $filters);
    $allAspirations = $this->_groupService->getAspirationsByBatch($batch['id']);

    $aspirationsByGroup = [];
    foreach ($allAspirations as $asp) {
      $aspirationsByGroup[$asp['group_id']][] = $asp;
    }

    foreach ($groups as &$group) {
      $members = $this->_groupService->getGroupMembers($group['id']);
      $group['members'] = $members;
      $group['aspirations'] = $aspirationsByGroup[$group['id']] ?? [];
    }

    $totalGroups = $this->_groupService->getTotalCountByBatch($batch['id']);
    $assignedGroupsCount = $this->_groupService->getTotalCountByBatch($batch['id'], ['is_assigned' => true]);
    $unassignedGroupsCount = $totalGroups - $assignedGroupsCount;

    $topics = $this->_topicService->getApprovedTopics($batch['id']);

    return $this->render('admin/project_batches/allocation', [
      'batchObj' => (object)$batch,
      'groups' => $groups,
      'topics' => $topics,
      'stats' => [
        'total' => $totalGroups,
        'assigned' => $assignedGroupsCount,
        'unassigned' => $unassignedGroupsCount
      ],
      'currentFilter' => $request->query('status', 'all')
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
      $request->session()->flashNotify('success', 'Gán đề tài thành công.');
    } catch (Exception $e) {
      $request->session()->flashNotify('error', 'Lỗi: ' . $e->getMessage());
    }

    return $this->redirect('/admin/project_batches/' . $batchId . '/allocation');
  }
}
