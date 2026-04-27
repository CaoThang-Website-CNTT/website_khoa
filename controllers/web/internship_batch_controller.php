<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\InternshipBatchService;
use App\Core\Request;

class InternshipBatchController extends Controller
{
  private InternshipBatchService $_internshipBatchService;

  public function __construct(InternshipBatchService $internshipBatchService)
  {
    $this->_internshipBatchService = $internshipBatchService;
  }

  public function index(Request $request)
  {
    $currentPage = $request->query('page') ?? 1;
    $data = $this->_internshipBatchService->getBatches($currentPage, 15);

    $this->render("admin/internship_batches/index", [
      'data' => $data
    ], layout: "dashboard_layout");
  }

  public function create()
  {
    $this->render("admin/internship_batches/create", [], layout: "dashboard_layout");
  }

  public function show($id, Request $request)
  {
    $batch = $this->_internshipBatchService->getBatchWithStats((int)$id);
    if (!$batch) {
      $request->session()->flashNotify('error', 'Không tìm thấy đợt thực tập này');
      return $this->redirect('admin/internship_batches');
    }

    $this->render("admin/internship_batches/edit", [
      'batch' => $batch
    ], layout: "dashboard_layout");
  }

  public function update($id, Request $request)
  {
    $data = $request->all();

    $isSuccess = $this->_internshipBatchService->updateBatch((int)$id, $data);
    if ($isSuccess) {
      $request->session()->flashNotify('success', 'Cập nhật thông tin đợt thực tập thành công!');
    } else {
      $request->session()->flashNotify('error', 'Có lỗi xảy ra khi cập nhật.');
    }

    return $this->redirect("admin/internship_batches/$id");
  }

  public function destroy($id, Request $request)
  {
    try {
      $isSuccess = $this->_internshipBatchService->deleteBatch((int)$id);
      if ($isSuccess) {
        $request->session()->flashNotify('success', 'Xóa đợt thực tập thành công!');
      } else {
        $request->session()->flashNotify('error', 'Có lỗi xảy ra khi xóa.');
      }
    } catch (\Exception $e) {
      $request->session()->flashNotify('error', $e->getMessage());
    }

    return $this->redirect('admin/internship_batches');
  }

  public function publish($id, Request $request)
  {
    $isSuccess = $this->_internshipBatchService->publishBatch((int)$id);
    if ($isSuccess) {
      $request->session()->flashNotify('success', 'Công bố đợt thực tập thành công!');
    } else {
      $request->session()->flashNotify('error', 'Có lỗi xảy ra.');
    }
    return $this->redirect("admin/internship_batches/$id");
  }

  public function close($id, Request $request)
  {
    $isSuccess = $this->_internshipBatchService->closeBatch((int)$id);
    if ($isSuccess) {
      $request->session()->flashNotify('success', 'Kết thúc đợt thực tập thành công!');
    } else {
      $request->session()->flashNotify('error', 'Có lỗi xảy ra.');
    }
    return $this->redirect("admin/internship_batches/$id");
  }
}
