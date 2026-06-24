<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\InternshipBatchService;
use App\Services\ReferralLetterService;
use App\Core\Request;

class InternshipBatchController extends Controller
{
  private InternshipBatchService $_internshipBatchService;
  private ReferralLetterService $_referralLetterService;

  public function __construct(InternshipBatchService $internshipBatchService, ReferralLetterService $referralLetterService)
  {
    $this->_internshipBatchService = $internshipBatchService;
    $this->_referralLetterService = $referralLetterService;
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

  public function referralLetters($id, Request $request)
  {
    $batch = $this->_internshipBatchService->getBatchWithStats((int)$id);
    if (!$batch) {
      $request->session()->flashNotify('error', 'Không tìm thấy đợt thực tập này');
      return $this->redirect('admin/internship_batches');
    }

    $letters = $this->_referralLetterService->getAllWithDetailsByBatchId((int)$id);

    $this->render("admin/internship_batches/referral_letters", [
      'batch' => $batch,
      'letters' => $letters
    ], layout: "dashboard_layout");
  }

  public function printReferralLetter($id, $letterId, Request $request)
  {
    $batch = $this->_internshipBatchService->getBatchWithStats((int)$id);
    if (!$batch) {
      $request->session()->flashNotify('error', 'Không tìm thấy đợt thực tập này');
      return $this->redirect('admin/internship_batches');
    }

    $letter = $this->_referralLetterService->getForPrint((int)$letterId);
    if (!$letter) {
      $request->session()->flashNotify('error', 'Không tìm thấy giấy giới thiệu');
      return $this->redirect("admin/internship_batches/$id/referral_letters");
    }

    $this->render("admin/internship_batches/referral_letter_print", [
      'batch' => $batch,
      'letter' => $letter
    ], layout: null);
  }

  public function confirmPrint($id, $letterId, Request $request)
  {
    $data = $request->all();
    $overrides = [
      'internship_start_date' => $data['internship_start_date'] ?? null,
      'internship_end_date' => $data['internship_end_date'] ?? null,
      'document_number' => $data['document_number'] ?? null,
    ];

    $authUser = $request->session()->authUser();
    $processedBy = $authUser['account_id'] ?? 0;

    try {
      $isSuccess = $this->_referralLetterService->printLetter((int)$letterId, $processedBy, $overrides);
      if ($isSuccess) {
        return $this->json(null, 200, 'Cập nhật trạng thái in thành công');
      }
      return $this->json(null, 400, 'Có lỗi xảy ra khi cập nhật trạng thái in');
    } catch (\Exception $e) {
      return $this->json(null, 400, $e->getMessage());
    }
  }

  public function teachers($id, Request $request)
  {
    $batch = $this->_internshipBatchService->getBatchWithStats((int)$id);
    if (!$batch) {
      $request->session()->flashNotify('error', 'Không tìm thấy đợt thực tập này');
      return $this->redirect('admin/internship_batches');
    }

    $supervisors = $this->_internshipBatchService->getBatchSupervisors((int)$id);

    $this->render("admin/internship_batches/teachers", [
      'batch' => $batch,
      'supervisors' => $supervisors
    ], layout: "dashboard_layout");
  }

  public function students($id, Request $request)
  {
    $batch = $this->_internshipBatchService->getBatchWithStats((int)$id);
    if (!$batch) {
      $request->session()->flashNotify('error', 'Không tìm thấy đợt thực tập này');
      return $this->redirect('admin/internship_batches');
    }

    // Lấy dữ liệu để build các dropdown filter
    $students = $this->_internshipBatchService->getBatchStudents((int)$id);

    // filter lớp
    $classrooms = array_unique(array_filter(array_column($students, 'classroom_name')));
    sort($classrooms);
    $classOptions = [];
    foreach ($classrooms as $c) {
      $classOptions[] = ['label' => $c, 'value' => $c];
    }

    // filter công ty thực tập
    $companies = array_unique(array_filter(array_column($students, 'company_name')));
    sort($companies);
    $companyOptions = [];
    foreach ($companies as $c) {
      $companyOptions[] = ['label' => $c, 'value' => $c];
    }

    // filter giảng viên HD
    $supervisors = $this->_internshipBatchService->getBatchSupervisors((int)$id);
    $teacherOptions = [];
    foreach ($supervisors as $s) {
      $teacherOptions[] = ['label' => $s['full_name'], 'value' => $s['full_name']];
    }

    $this->render("admin/internship_batches/students", [
      'batch' => $batch,
      'classOptions' => $classOptions,
      'companyOptions' => $companyOptions,
      'teacherOptions' => $teacherOptions
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
