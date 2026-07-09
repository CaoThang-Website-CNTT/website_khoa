<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\ValidationException;
use App\Services\ProjectBatchService;
use App\Services\ProjectGroupService;
use Exception;

class ProjectBatchApiController extends Controller
{
  private ProjectBatchService $_service;
  private ProjectGroupService $_groupService;

  public function __construct(ProjectBatchService $service, ProjectGroupService $groupService)
  {
    $this->_service = $service;
    $this->_groupService = $groupService;
  }

  public function store(Request $request)
  {
    $authUser = $request->session()->authUser();
    $adminId = $authUser['account_id'] ?? 0;

    if (!$adminId) {
      return $this->json(null, 401, 'Unauthorized');
    }

    try {
      $data = $this->validate($request, [
        'title' => ['required', 'max:255'],
        'description' => ['nullable'],
        'min_class_of' => ['required', 'integer'],
        'max_class_of' => ['required', 'integer'],
        'max_aspirations' => ['required', 'integer', 'min:1'],
        'topic_proposal_start' => ['required', 'date'],
        'topic_proposal_end' => ['required', 'date', 'after:topic_proposal_start'],
        'registration_start' => ['nullable', 'date'],
        'registration_end' => ['nullable', 'date', 'after:registration_start'],
        'supervisors' => ['required', 'array'],
      ]);
    } catch (ValidationException $e) {
      return $this->json(['errors' => $e->getErrors()], 422, 'Dữ liệu không hợp lệ.');
    }

    try {
      $batchId = $this->_service->createBatch($data, $adminId);
      return $this->json(['batch_id' => $batchId], 200, 'Tạo đợt đồ án thành công');
    } catch (Exception $e) {
      return $this->json(null, 422, $e->getMessage());
    }
  }

  public function getAvailableTeachers()
  {
    try {
      $teachers = $this->_service->getAvailableTeachers();
      return $this->json($teachers, 200, 'Success');
    } catch (Exception $e) {
      return $this->json(null, 500, $e->getMessage());
    }
  }

  public function getAllocations(Request $request, $id)
  {
    $batchId = (int)$id;
    $page = (int)$request->query('page', 1);
    $limit = (int)$request->query('limit', 15);

    $filters = [];

    $status = $request->query('status');
    if ($status === 'assigned') {
      $filters['is_assigned'] = true;
    } elseif ($status === 'unassigned') {
      $filters['is_assigned'] = false;
    }

    $search = $request->query('search');
    if (!empty($search)) {
      $filters['search'] = trim($search);
    }

    $sort = $request->query('sort');
    if (is_array($sort) && !empty($sort['col'])) {
      $filters['sort'] = [
        'col' => $sort['col'],
        'dir' => $sort['dir'] ?? 'DESC'
      ];
    }

    try {
      $groups = $this->_groupService->getPaginatedByBatch($batchId, $page, $limit, $filters);
      $total = $this->_groupService->getTotalCountByBatch($batchId, $filters);
      $allAspirations = $this->_groupService->getAspirationsByBatch($batchId);

      $aspirationsByGroup = [];
      foreach ($allAspirations as $asp) {
        $aspirationsByGroup[$asp['group_id']][] = $asp;
      }

      foreach ($groups as &$group) {
        $members = $this->_groupService->getGroupMembers($group['id']);
        $group['members'] = $members;
        $group['aspirations'] = $aspirationsByGroup[$group['id']] ?? [];
      }

      return $this->json([
        'data' => $groups,
        'total' => $total,
        'page' => $page,
        'limit' => $limit
      ], 200, 'Success');
    } catch (Exception $e) {
      return $this->json(null, 500, $e->getMessage());
    }
  }
}
