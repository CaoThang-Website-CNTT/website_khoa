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

    $teachers = $this->_ProjectBatchService->getSupervisorsByBatchId((int) $id);

    $this->render("admin/project_batches/topics", [
      'batch' => $batch,
      'teachers' => $teachers
    ], layout: "dashboard_layout");
  }

  public function topicDetail($id, $topicId, Request $request)
  {
    $batch = $this->_ProjectBatchService->getBatchWithStats((int) $id);
    if (!$batch) {
      $request->session()->flashNotify('error', 'Không tìm thấy đợt đồ án này');
      return $this->redirect('admin/project_batches');
    }

    $topic = $this->_TopicService->getTopicById((int) $topicId);
    if (!$topic || $topic['batch_id'] != $id) {
      $request->session()->flashNotify('error', 'Không tìm thấy đề tài hoặc đề tài không thuộc đợt đồ án này');
      return $this->redirect("admin/project_batches/$id/topics");
    }

    $this->render("admin/project_batches/topic_detail", [
      'batch' => $batch,
      'topic' => $topic
    ], layout: "dashboard_layout");
  }

  public function topicFile($id, $topicId, Request $request): void
  {
    $topic = $this->_TopicService->getTopicById((int) $topicId);
    if (!$topic || (int) $topic['batch_id'] !== (int) $id || empty($topic['pdf_file_path'])) {
      $this->abort(404);
    }

    $relativePath = str_replace('\\', '/', ltrim((string) $topic['pdf_file_path'], '/'));
    if (!str_starts_with($relativePath, 'project_topics/') || str_contains($relativePath, '..')) {
      $this->abort(404);
    }

    $filePath = BASE_PATH . '/storage/' . $relativePath;
    if (!is_file($filePath)) {
      $this->abort(404);
    }

    $fileName = basename($filePath);
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . addslashes($fileName) . '"');
    header('Content-Length: ' . filesize($filePath));
    header('X-Content-Type-Options: nosniff');
    readfile($filePath);
    exit;
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

    $teachers = $this->_ProjectBatchService->getSupervisorsByBatchId((int)$id);
    $allITTeachers = $this->_ProjectBatchService->getAvailableTeachers();

    $assignedTeacherIds = array_column($teachers, 'teacher_id');
    $availableTeachers = array_filter($allITTeachers, function ($t) use ($assignedTeacherIds) {
      return !in_array($t['teacher_id'], $assignedTeacherIds);
    });

    $this->render('admin/project_batches/teachers', [
      'batch' => $batch,
      'teachers' => $teachers,
      'availableTeachers' => $availableTeachers,
    ], layout: 'dashboard_layout');
  }

  public function addTeacher($id, Request $request)
  {
    try {
      $data = $this->validate($request, [
        'teacher_id' => ['required', 'integer'],
        'min_students' => ['required', 'integer'],
        'max_students' => ['required', 'integer'],
      ]);
    } catch (ValidationException $e) {
      $request->session()->flashNotify('error', 'Dữ liệu không hợp lệ.');
      return $this->redirect("admin/project_batches/$id/teachers");
    }

    try {
      $this->_ProjectBatchService->addSupervisorToBatch((int)$id, (int)$data['teacher_id'], (int)$data['min_students'], (int)$data['max_students']);
      $request->session()->flashNotify('success', 'Đã thêm giảng viên phụ trách.');
    } catch (Exception $e) {
      $request->session()->flashNotify('error', $e->getMessage());
    }

    return $this->redirect("admin/project_batches/$id/teachers");
  }

  public function updateTeacherCapacity($id, Request $request)
  {
    try {
      $data = $this->validate($request, [
        'teacher_id' => ['required', 'integer'],
        'min_students' => ['required', 'integer'],
        'max_students' => ['required', 'integer'],
      ]);
    } catch (ValidationException $e) {
      $request->session()->flashNotify('error', 'Dữ liệu không hợp lệ.');
      return $this->redirect("admin/project_batches/$id/teachers");
    }

    try {
      $this->_ProjectBatchService->updateSupervisorCapacity((int)$id, (int)$data['teacher_id'], (int)$data['min_students'], (int)$data['max_students']);
      $request->session()->flashNotify('success', 'Đã cập nhật chỉ tiêu sinh viên.');
    } catch (Exception $e) {
      $request->session()->flashNotify('error', $e->getMessage());
    }

    return $this->redirect("admin/project_batches/$id/teachers");
  }

  public function removeTeacher($id, Request $request)
  {
    try {
      $data = $this->validate($request, [
        'teacher_id' => ['required', 'integer'],
      ]);
    } catch (ValidationException $e) {
      $request->session()->flashNotify('error', 'Dữ liệu không hợp lệ.');
      return $this->redirect("admin/project_batches/$id/teachers");
    }

    try {
      $this->_ProjectBatchService->removeSupervisor((int)$id, (int)$data['teacher_id']);
      $request->session()->flashNotify('success', 'Đã loại giảng viên khỏi đợt.');
    } catch (Exception $e) {
      $request->session()->flashNotify('error', $e->getMessage());
    }

    return $this->redirect("admin/project_batches/$id/teachers");
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
