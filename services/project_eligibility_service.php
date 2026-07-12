<?php

namespace App\Services;

use App\Stores\ProjectGroupStore;
use App\Stores\StudentStore;

class ProjectEligibilityService
{
  private ProjectGroupStore $_groupStore;
  private StudentStore $_studentStore;

  public function __construct(ProjectGroupStore $groupStore, StudentStore $studentStore)
  {
    $this->_groupStore = $groupStore;
    $this->_studentStore = $studentStore;
  }

  /**
   * Phân tích và phân loại danh sách sinh viên so với file Excel.
   * Trả về mảng 3 loại: in_excel, ineligible, eligible_not_registered
   *
   * @param int $batchId
   * @param array $excelStudentCodes (mảng MSSV string từ file Excel)
   * @return array
   */
  public function previewExcelData(int $batchId, array $excelStudentCodes): array
  {
    // 1. Get all students currently registered in groups in this batch
    $currentStudents = $this->_groupStore->getCurrentStudentsInBatch($batchId);

    $inExcel = [];
    $legacyEligible = [];
    $ineligible = [];

    // Optimize search
    $excelMap = array_flip($excelStudentCodes);

    foreach ($currentStudents as $student) {
      $code = $student['student_code'] ?? $student['student_id'] ?? null;
      if ($code && isset($excelMap[$code])) {
        $student['student_code'] = $code; // Ensure consistency
        $inExcel[] = $student;
        unset($excelMap[$code]);
      } else {
        // Nếu không có trong file Excel thì bị loại
        $ineligible[] = $student;
      }
    }

    $eligibleNotRegistered = [];
    $notRegisteredStudentCodes = array_keys($excelMap);

    if (!empty($notRegisteredStudentCodes)) {
      $eligibleNotRegistered = $this->_studentStore->getBasicInfoByStudentCodes($notRegisteredStudentCodes);
    }

    return [
      'in_excel' => $inExcel,
      'ineligible' => $ineligible,
      'eligible_not_registered' => $eligibleNotRegistered
    ];
  }

  /**
   * Lưu dữ liệu đã duyệt từ Excel vào database.
   */
  public function processConfirmedData(int $batchId, array $previewData): void
  {
    // 1. Loại bỏ các sinh viên không đủ điều kiện (trong nhóm)
    $ineligibleIds = array_column($previewData['ineligible'] ?? [], 'id');
    if (!empty($ineligibleIds)) {
      $this->_groupStore->updateMemberEligibility($ineligibleIds, 0);
    }

    // 2. Lưu toàn bộ sinh viên đủ điều kiện vào project_batch_eligible_students
    // Bao gồm: in_excel, eligible_not_registered
    $eligibleIds = [];

    $inExcel = $previewData['in_excel'] ?? [];
    foreach ($inExcel as $s) {
      $eligibleIds[] = $s['id'];
    }

    $notRegistered = $previewData['eligible_not_registered'] ?? [];
    foreach ($notRegistered as $s) {
      $eligibleIds[] = $s['id'];
    }

    if (!empty($eligibleIds)) {
      if (($_ENV['APP_ENV']) !== 'local') {
        $activeBatchStudents = $this->_groupStore->getStudentsInOtherActiveBatches($batchId, $eligibleIds);
        if (!empty($activeBatchStudents)) {
          $errorMsg = 'Có ' . count($activeBatchStudents) . ' sinh viên đang tham gia đợt đồ án khác chưa đóng: ';
          $studentNames = array_map(fn($s) => $s['full_name'] . ' (' . $s['student_code'] . ')', array_slice($activeBatchStudents, 0, 3));
          $errorMsg .= implode(', ', $studentNames);
          if (count($activeBatchStudents) > 3) {
            $errorMsg .= ' và ' . (count($activeBatchStudents) - 3) . ' sinh viên khác.';
          }
          throw new \Exception($errorMsg);
        }
      }

      $this->_groupStore->saveEligibleStudents($batchId, $eligibleIds);
    }
  }
}
