<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\ProjectEligibilityService;
use App\Services\ProjectGroupService;
use App\Services\ProjectBatchService;
use App\Core\Files\XlsxReader;
use Exception;

class ProjectEligibilityController extends Controller
{
  private ProjectEligibilityService $_eligibilityService;
  private ProjectGroupService $_groupService;
  private ProjectBatchService $_batchService;

  public function __construct(
    ProjectEligibilityService $eligibilityService,
    ProjectGroupService $groupService,
    ProjectBatchService $batchService
  ) {
    $this->_eligibilityService = $eligibilityService;
    $this->_groupService = $groupService;
    $this->_batchService = $batchService;
  }

  public function index(Request $request, $id)
  {
    $batchId = $id;
    $batch = $this->_batchService->getBatchById((int)$batchId);
    if (!$batch) {
      $request->session()->flashNotify('error', 'Không tìm thấy đợt đồ án.', '');
      return $this->redirect('/admin/project_batches');
    }

    return $this->render('admin/project_batches/eligibility', [
      'batchObj' => (object)$batch,
      'previewData' => null
    ]);
  }

  public function preview(Request $request, $id)
  {
    $batchId = $id;
    $batch = $this->_batchService->getBatchById((int)$batchId);
    if (!$batch) {
      $request->session()->flashNotify('error', 'Không tìm thấy đợt đồ án.', '');
      return $this->redirect('/admin/project_batches');
    }

    if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
      $request->session()->flashNotify('error', 'Vui lòng chọn file Excel hợp lệ.', '');
      return $this->redirect('/admin/project_batches/' . $batchId . '/eligibility');
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

      // Lưu data preview vào session để xác nhận
      $request->session()->put('eligibility_preview_' . $batchId, $previewData);

      return $this->render('admin/project_batches/eligibility', [
        'batchObj' => (object)$batch,
        'previewData' => $previewData
      ]);
    } catch (Exception $e) {
      $request->session()->flashNotify('error', 'Lỗi đọc file Excel: ' . $e->getMessage(), '');
      return $this->redirect('/admin/project_batches/' . $batchId . '/eligibility');
    }
  }

  public function confirm(Request $request, $id)
  {
    $batchId = $id;
    if (!$request->isMethod('POST')) {
      return $this->redirect('/admin/project_batches/' . $batchId . '/eligibility');
    }

    $previewData = $request->session()->get('eligibility_preview_' . $batchId);
    if (!$previewData) {
      $request->session()->flashNotify('error', 'Không tìm thấy dữ liệu Preview. Vui lòng tải lại file.', '');
      return $this->redirect('/admin/project_batches/' . $batchId . '/eligibility');
    }

    // Lấy danh sách ineligible ID (không đủ đk, có thể admin tick override)
    // Nếu UI gửi mảng "ineligible_ids"
    $ineligibleIds = $request->input('ineligible_ids', []);

    // Fallback: nếu không submit form mảng, ta lấy mặc định từ previewData['ineligible']
    if ($request->input('action') === 'confirm_all') {
      $ineligibleIds = array_column($previewData['ineligible'], 'id');
    }

    try {
      $this->_groupService->kickIneligibleMembers((int)$batchId, $ineligibleIds);
      $request->session()->forget('eligibility_preview_' . $batchId);
      $request->session()->flashNotify('success', 'Đã cập nhật điều kiện sinh viên thành công.', '');
    } catch (Exception $e) {
      $request->session()->flashNotify('error', 'Lỗi: ' . $e->getMessage());
    }

    return $this->redirect('/admin/project_batches/' . $batchId . '/allocation');
  }
}
