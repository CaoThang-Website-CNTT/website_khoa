<?php

namespace App\Services;

use App\Stores\ProjectGroupStore;
use App\Stores\StudentStore;
use Database;
use PDO;

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
   * Trả về mảng 3 loại: in_excel, legacy_eligible, ineligible
   *
   * @param int $batchId
   * @param array $excelStudentCodes (mảng MSSV string từ file Excel)
   * @return array
   */
  public function previewExcelData(int $batchId, array $excelStudentCodes): array
  {
    $db = Database::getInstance()->getConnection();

    // 1. Get all students currently registered in groups in this batch
    $sqlCurrent = "
      SELECT s.id, s.student_id as student_code, s.full_name, c.short_name as classroom_name, gm.group_id
      FROM project_group_members gm
      JOIN students s ON gm.student_id = s.id
      LEFT JOIN classrooms c ON s.classroom_id = c.id
      JOIN project_groups g ON gm.group_id = g.id
      WHERE g.batch_id = :batch_id
    ";
    $stmtCurrent = $db->prepare($sqlCurrent);
    $stmtCurrent->execute([':batch_id' => $batchId]);
    $currentStudents = $stmtCurrent->fetchAll(PDO::FETCH_ASSOC);

    $inExcel = [];
    $legacyEligible = [];
    $ineligible = [];

    // Optimize search
    $excelMap = array_flip($excelStudentCodes);

    // Sinh viên được coi là legacy_eligible nếu từng tham gia đợt đồ án khác đợt hiện tại và có is_eligible = 1
    $sqlLegacy = "
      SELECT 1 
      FROM project_group_members gm
      JOIN project_groups g ON gm.group_id = g.id
      WHERE gm.student_id = :student_id 
        AND g.batch_id != :current_batch_id 
        AND gm.is_eligible = 1
      LIMIT 1
    ";
    $stmtLegacy = $db->prepare($sqlLegacy);

    foreach ($currentStudents as $student) {
      if (isset($excelMap[$student['student_code']])) {
        $inExcel[] = $student;
        unset($excelMap[$student['student_code']]);
      } else {
        // Kiểm tra lịch sử
        $stmtLegacy->execute([
          ':student_id' => $student['id'],
          ':current_batch_id' => $batchId
        ]);
        $hasLegacy = $stmtLegacy->fetchColumn();

        if ($hasLegacy) {
          $legacyEligible[] = $student;
        } else {
          $ineligible[] = $student;
        }
      }
    }

    $eligibleNotRegistered = [];
    $notRegisteredStudentCodes = array_keys($excelMap);

    if (!empty($notRegisteredStudentCodes)) {
      $placeholders = implode(',', array_fill(0, count($notRegisteredStudentCodes), '?'));
      
      $sqlNotReg = "
        SELECT s.id, s.student_id as student_code, s.full_name, c.short_name as classroom_name
        FROM students s
        LEFT JOIN classrooms c ON s.classroom_id = c.id
        WHERE s.student_id IN ($placeholders)
      ";
      
      $stmtNotReg = $db->prepare($sqlNotReg);
      $stmtNotReg->execute($notRegisteredStudentCodes);
      $eligibleNotRegistered = $stmtNotReg->fetchAll(PDO::FETCH_ASSOC);
    }

    return [
      'in_excel' => $inExcel,
      'legacy_eligible' => $legacyEligible,
      'ineligible' => $ineligible,
      'eligible_not_registered' => $eligibleNotRegistered
    ];
  }
}
