<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Services\ProjectBatchService;
use Exception;

class ProjectBatchApiController extends Controller
{
  private ProjectBatchService $_service;

  public function __construct(ProjectBatchService $service)
  {
    $this->_service = $service;
  }

  public function store(Request $request)
  {
    $authUser = $request->session()->authUser();
    $adminId = $authUser['account_id'] ?? 0;
    
    if (!$adminId) {
      return $this->json(null, 401, 'Unauthorized');
    }

    $data = $request->all();

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
}
