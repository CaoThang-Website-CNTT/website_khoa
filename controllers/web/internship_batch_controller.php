<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\InternshipBatchService;
use App\Services\ReferralLetterService;
use App\Core\Request;
use App\Core\ValidationException;

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
    $batch = $this->_internshipBatchService->getBatchWithStats((int) $id);
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
    $batch = $this->_internshipBatchService->getBatchWithStats((int) $id);
    if (!$batch) {
      $request->session()->flashNotify('error', 'Không tìm thấy đợt thực tập này');
      return $this->redirect('admin/internship_batches');
    }

    $this->render("admin/internship_batches/referral_letters", [
      'batch' => $batch
    ], layout: "dashboard_layout");
  }

  public function printReferralLetter($id, $letterId, Request $request)
  {
    $batch = $this->_internshipBatchService->getBatchWithStats((int) $id);
    if (!$batch) {
      $request->session()->flashNotify('error', 'Không tìm thấy đợt thực tập này');
      return $this->redirect('admin/internship_batches');
    }

    $letter = $this->_referralLetterService->getForPrint((int) $letterId);
    if (!$letter) {
      $request->session()->flashNotify('error', 'Không tìm thấy giấy giới thiệu');
      return $this->redirect("admin/internship_batches/$id/referral_letters");
    }
    if ((int) $letter['batch_id'] !== (int) $id || $letter['status'] !== 'approved') {
      $request->session()->flashNotify('error', 'Giấy giới thiệu phải được duyệt trước khi in.');
      return $this->redirect("admin/internship_batches/$id/referral_letters");
    }

    $this->render("admin/internship_batches/referral_letter_print", [
      'batch' => $batch,
      'letter' => $letter,
      'authUser' => $request->session()->authUser() ?? []
    ], layout: null);
  }

  public function confirmPrint($id, $letterId, Request $request)
  {
    $letter = $this->_referralLetterService->getForPrint((int) $letterId);
    if (!$letter || (int) $letter['batch_id'] !== (int) $id) {
      return $this->json(null, 404, 'Không tìm thấy giấy giới thiệu trong đợt thực tập này.');
    }

    try {
      $data = $this->validate($request, [
        'internship_start_date' => ['nullable', 'date'],
        'internship_end_date' => ['nullable', 'date', 'after:internship_start_date'],
        'document_number' => ['nullable', 'max:50'],
      ]);
    } catch (ValidationException $e) {
      return $this->json(['errors' => $e->getErrors()], 422, 'Dữ liệu không hợp lệ.');
    }

    $overrides = [
      'internship_start_date' => $data['internship_start_date'] ?? null,
      'internship_end_date' => $data['internship_end_date'] ?? null,
      'document_number' => $data['document_number'] ?? null,
      'approver_name' => $data['approver_name'] ?? null,
    ];

    $authUser = $request->session()->authUser();
    $processedBy = $authUser['account_id'] ?? 0;

    try {
      $isSuccess = $this->_referralLetterService->printLetter((int) $letterId, $processedBy, $overrides);
      if ($isSuccess) {
        return $this->json(null, 200, 'Cập nhật trạng thái in thành công');
      }
      return $this->json(null, 400, 'Có lỗi xảy ra khi cập nhật trạng thái in');
    } catch (\Exception $e) {
      return $this->json(null, 400, $e->getMessage());
    }
  }

  public function bulkPrintReferralLetters($id, Request $request)
  {
    $batch = $this->_internshipBatchService->getBatchWithStats((int)$id);
    $ids = array_values(array_unique(array_map('intval', (array)($request->all()['ids'] ?? []))));
    if (!$batch || !$ids) return $this->redirect("admin/internship_batches/$id/referral_letters");
    $letters = [];
    foreach ($ids as $letterId) {
      $letter = $this->_referralLetterService->getForPrint($letterId);
      if (!$letter || (int)$letter['batch_id'] !== (int)$id || $letter['status'] !== 'approved') {
        $request->session()->flashNotify('error', 'Chỉ có thể in các giấy đang xử lý thuộc đợt này.');
        return $this->redirect("admin/internship_batches/$id/referral_letters");
      }
      $letters[] = $letter;
    }
    $this->render('admin/internship_batches/referral_letters_bulk_print', [
      'batch' => $batch, 'letters' => $letters, 'ids' => $ids, 'isMerged' => false,
      'confirmUrl' => url("admin/internship_batches/{$id}/referral_letters/bulk-print/confirm"),
      'authUser' => $request->session()->authUser() ?? []
    ], layout: null);
  }

  public function mergedPrintReferralLetters($id, Request $request)
  {
    $batch = $this->_internshipBatchService->getBatchWithStats((int)$id);
    $ids = array_values(array_unique(array_map('intval', (array)($request->all()['ids'] ?? []))));
    if (!$batch || !$ids) return $this->redirect("admin/internship_batches/$id/referral_letters");
    $letters = [];
    foreach ($ids as $letterId) {
      $letter = $this->_referralLetterService->getForPrint($letterId);
      if (!$letter || (int)$letter['batch_id'] !== (int)$id || $letter['status'] !== 'approved') {
        $request->session()->flashNotify('error', 'Chỉ có thể in các giấy đang xử lý thuộc đợt này.');
        return $this->redirect("admin/internship_batches/$id/referral_letters");
      }
      $letters[] = $letter;
    }
    $companyIds = array_unique(array_map(fn($letter) => (int)$letter['company_id'], $letters));
    if (count($companyIds) !== 1) {
      $request->session()->flashNotify('error', 'Chỉ có thể in gộp các giấy cùng một công ty.');
      return $this->redirect("admin/internship_batches/$id/referral_letters");
    }
    $students = [];
    $seen = [];
    foreach ($letters as $letter) foreach (($letter['students'] ?? []) as $student) {
      $key = !empty($student['batch_student_id']) ? 'batch:' . $student['batch_student_id'] : (!empty($student['student_id']) ? 'student:' . $student['student_id'] : 'snapshot:' . mb_strtolower(trim($student['full_name'] ?? '')) . '|' . ($student['dob'] ?? ''));
      if (!isset($seen[$key])) { $seen[$key] = true; $students[] = $student; }
    }
    $mergedLetter = $letters[0];
    $mergedLetter['students'] = $students;
    $this->render('admin/internship_batches/referral_letters_bulk_print', [
      'batch' => $batch, 'letters' => [$mergedLetter], 'ids' => $ids, 'isMerged' => true,
      'confirmUrl' => url("admin/internship_batches/{$id}/referral_letters/merged-print/confirm"),
      'authUser' => $request->session()->authUser() ?? []
    ], layout: null);
  }

  public function confirmBulkPrint($id, Request $request)
  {
    return $this->confirmMultiplePrint($id, $request, false);
  }

  public function confirmMergedPrint($id, Request $request)
  {
    return $this->confirmMultiplePrint($id, $request, true);
  }

  private function confirmMultiplePrint($id, Request $request, bool $requireSameCompany)
  {
    $data = $request->all();
    $ids = array_values(array_unique(array_map('intval', (array)($data['ids'] ?? []))));
    $authUser = $request->session()->authUser();
    if (!$authUser || empty($authUser['account_id'])) return $this->json(null, 401, 'Lỗi xác thực người dùng.');
    try {
      $count = $this->_referralLetterService->bulkPrint($ids, (int)$id, (int)($authUser['account_id'] ?? 0), [
        'document_number' => trim((string)($data['document_number'] ?? '')),
        'internship_start_date' => $data['internship_start_date'] ?? null,
        'internship_end_date' => $data['internship_end_date'] ?? null,
        'approver_name' => trim((string)($data['approver_name'] ?? '')),
      ], $requireSameCompany);
      return $this->json(['count' => $count], 200, "Đã lưu thông tin in cho {$count} giấy giới thiệu.");
    } catch (\Exception $e) {
      return $this->json(null, 422, $e->getMessage());
    }
  }

  public function teachers($id, Request $request)
  {
    $batch = $this->_internshipBatchService->getBatchWithStats((int) $id);
    if (!$batch) {
      $request->session()->flashNotify('error', 'Không tìm thấy đợt thực tập này');
      return $this->redirect('admin/internship_batches');
    }

    $supervisors = $this->_internshipBatchService->getBatchSupervisors((int) $id);

    $this->render("admin/internship_batches/teachers", [
      'batch' => $batch,
      'supervisors' => $supervisors
    ], layout: "dashboard_layout");
  }

  public function students($id, Request $request)
  {
    $batch = $this->_internshipBatchService->getBatchWithStats((int) $id);
    if (!$batch) {
      $request->session()->flashNotify('error', 'Không tìm thấy đợt thực tập này');
      return $this->redirect('admin/internship_batches');
    }

    // Lấy dữ liệu để build các dropdown filter
    $students = $this->_internshipBatchService->getBatchStudents((int) $id);

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
    $supervisors = $this->_internshipBatchService->getBatchSupervisors((int) $id);
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
    try {
      $data = $this->validate($request, [
        'title' => ['required', 'max:255'],
        'description' => ['nullable'],
        'start_at' => ['required', 'date'],
        'end_at' => ['required', 'date', 'after:start_at'],
      ]);
      $isSuccess = $this->_internshipBatchService->updateBatch((int) $id, $data);
    } catch (ValidationException $e) {
      $request->session()->flashNotify('error', $e->getMessage());
      return $this->redirect("admin/internship_batches/$id");
    } catch (\Exception $e) {
      $request->session()->flashNotify('error', $e->getMessage());
      return $this->redirect("admin/internship_batches/$id");
    }
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
      $isSuccess = $this->_internshipBatchService->deleteBatch((int) $id);
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
    try {
      $isSuccess = $this->_internshipBatchService->publishBatch((int) $id);
    } catch (\Exception $e) {
      $request->session()->flashNotify('error', $e->getMessage());
      return $this->redirect("admin/internship_batches/$id");
    }
    if ($isSuccess) {
      $request->session()->flashNotify('success', 'Công bố đợt thực tập thành công!');
    } else {
      $request->session()->flashNotify('error', 'Có lỗi xảy ra.');
    }
    return $this->redirect("admin/internship_batches/$id");
  }

  public function close($id, Request $request)
  {
    try {
      $isSuccess = $this->_internshipBatchService->closeBatch((int) $id);
    } catch (\Exception $e) {
      $request->session()->flashNotify('error', $e->getMessage());
      return $this->redirect("admin/internship_batches/$id");
    }
    if ($isSuccess) {
      $request->session()->flashNotify('success', 'Kết thúc đợt thực tập thành công!');
    } else {
      $request->session()->flashNotify('error', 'Có lỗi xảy ra.');
    }
    return $this->redirect("admin/internship_batches/$id");
  }
}
