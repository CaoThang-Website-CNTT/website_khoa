<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\ProjectBatchService;
use App\Services\ProjectTopicService;
use App\Core\Request;
use App\Core\ValidationException;
use Exception;

class ProjectBatchController extends Controller
{
  private ProjectBatchService $_ProjectBatchService;
  private ProjectTopicService $_TopicService;

  public function __construct(ProjectBatchService $ProjectBatchService, ProjectTopicService $topicService)
  {
    $this->_ProjectBatchService = $ProjectBatchService;
    $this->_TopicService = $topicService;
  }

  public function index(Request $request)
  {
    $currentPage = $request->query('page') ?? 1;
    $data = $this->_ProjectBatchService->getBatches($currentPage, 15);

    $this->render("admin/project_batches/index", [
      'data' => $data
    ], layout: "dashboard_layout");
  }

  public function create()
  {
    $this->render("admin/project_batches/create", [], layout: "dashboard_layout");
  }

  public function store(Request $request)
  {
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
      $request->session()->flashNotify('error', 'Dữ liệu không hợp lệ, vui lòng kiểm tra lại.');
      return $this->redirect('admin/project_batches/create');
    }

    $authUser = $request->session()->authUser();
    $adminId = $authUser['account_id'] ?? 0;

    try {
      $batchId = $this->_ProjectBatchService->createBatch($data, $adminId);
      $request->session()->flashNotify('success', 'Tạo đợt đồ án thành công!');
      return $this->redirect("admin/project_batches/$batchId");
    } catch (Exception $e) {
      $request->session()->flashNotify('error', $e->getMessage());
      return $this->redirect('admin/project_batches/create');
    }
  }

  public function show($id, Request $request)
  {
    $batch = $this->_ProjectBatchService->getBatchWithStats((int) $id);
    if (!$batch) {
      $request->session()->flashNotify('error', 'Không tìm thấy đợt đồ án này');
      return $this->redirect('admin/project_batches');
    }

    $this->render("admin/project_batches/edit", [
      'batch' => $batch
    ], layout: "dashboard_layout");
  }

  public function topics($id, Request $request)
  {
    $batch = $this->_ProjectBatchService->getBatchWithStats((int) $id);
    if (!$batch) {
      $request->session()->flashNotify('error', 'Không tìm thấy đợt đồ án này');
      return $this->redirect('admin/project_batches');
    }

    $this->render("admin/project_batches/topics", [
      'batch' => $batch
    ], layout: "dashboard_layout");
  }

  public function update($id, Request $request)
  {
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
      ]);
    } catch (ValidationException $e) {
      $request->session()->flashNotify('error', 'Dữ liệu không hợp lệ, vui lòng kiểm tra lại.');
      return $this->redirect("admin/project_batches/$id");
    }

    try {
      $isSuccess = $this->_ProjectBatchService->updateBatch((int) $id, $data);
      if ($isSuccess) {
        $request->session()->flashNotify('success', 'Cập nhật thông tin đợt đồ án thành công!');
      } else {
        $request->session()->flashNotify('error', 'Có lỗi xảy ra khi cập nhật.');
      }
    } catch (Exception $e) {
      $request->session()->flashNotify('error', $e->getMessage());
    }

    return $this->redirect("admin/project_batches/$id");
  }

  public function teachers($id, Request $request)
  {
    $batch = $this->_ProjectBatchService->getBatchWithStats((int)$id);
    if (!$batch) {
      $request->session()->flashNotify('error', 'Không tìm thấy đợt đồ án này.');
      return $this->redirect('admin/project_batches');
    }

    $this->render('admin/project_batches/teachers', [
      'batch' => $batch,
      'teachers' => $this->_ProjectBatchService->getSupervisorsByBatchId((int)$id),
    ], layout: 'dashboard_layout');
  }

  public function destroy($id, Request $request)
  {
    try {
      $isSuccess = $this->_ProjectBatchService->deleteBatch((int) $id);
      if ($isSuccess) {
        $request->session()->flashNotify('success', 'Xóa đợt đồ án thành công!');
      } else {
        $request->session()->flashNotify('error', 'Có lỗi xảy ra khi xóa.');
      }
    } catch (Exception $e) {
      $request->session()->flashNotify('error', $e->getMessage());
    }

    return $this->redirect('admin/project_batches');
  }

  public function publish($id, Request $request)
  {
    try {
      $isSuccess = $this->_ProjectBatchService->publishBatch((int) $id);
      if ($isSuccess) {
        $request->session()->flashNotify('success', 'Công bố đợt đồ án thành công!');
      } else {
        $request->session()->flashNotify('error', 'Có lỗi xảy ra.');
      }
    } catch (Exception $e) {
      $request->session()->flashNotify('error', $e->getMessage());
    }

    return $this->redirect("admin/project_batches/$id");
  }

  public function close($id, Request $request)
  {
    try {
      $isSuccess = $this->_ProjectBatchService->closeBatch((int) $id);
      if ($isSuccess) {
        $request->session()->flashNotify('success', 'Kết thúc đợt đồ án thành công!');
      } else {
        $request->session()->flashNotify('error', 'Có lỗi xảy ra.');
      }
    } catch (Exception $e) {
      $request->session()->flashNotify('error', $e->getMessage());
    }

    return $this->redirect("admin/project_batches/$id");
  }
}
