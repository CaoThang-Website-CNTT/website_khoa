<?php

namespace App\Services;

require_once BASE_PATH . '/models/internship_assignment.php';
require_once BASE_PATH . '/models/assignment_log.php';
require_once BASE_PATH . '/stores/internship_assignment_store.php';

use App\Stores\InternshipAssignmentStore;
use Database;

interface IInternshipAssignmentService
{
  public function assign(int $batchStudentId, int $teacherId, bool $isDraft = true, ?int $assignedBy = null): bool;
  public function autoAssign(int $batchId, string $method, int $adminId): int;
  public function reassign(int $assignmentId, int $newTeacherId, int $adminId, string $reason): bool;
  public function bulkReassignAndPublish(int $batchId, array $assignmentsData, int $adminId, string $reason): int;
  public function publishBatch(int $batchId, int $adminId): int;
  public function getLogsByStudent(int $batchStudentId): array;
}

class InternshipAssignmentService implements IInternshipAssignmentService
{
  private InternshipAssignmentStore $_store;

  public function __construct(InternshipAssignmentStore $store)
  {
    $this->_store = $store;
  }

  /**
   * Phân công lần đầu (hoặc chạy qua auto-assign)
   */
  public function assign(int $batchStudentId, int $teacherId, bool $isDraft = true, ?int $assignedBy = null): bool
  {
    return Database::getInstance()->transaction(function () use ($batchStudentId, $teacherId, $isDraft, $assignedBy) {
      $status = $isDraft ? 'draft' : 'published';
      
      // Kiểm tra xem sinh viên đã có assignment nào chưa
      $existingAssignment = $this->_store->getAssignmentByBatchStudentId($batchStudentId);
      if ($existingAssignment) {
        throw new \Exception('Sinh viên này đã được phân công trong đợt. Vui lòng dùng tính năng Reassign.');
      }

      $assignmentId = $this->_store->createAssignment(
        batchStudentId: $batchStudentId, 
        teacherId: $teacherId, 
        status: $status, 
        method: 'manual', 
        assignedBy: $assignedBy
      );

      if (!$assignmentId) {
        throw new \Exception('Không thể lưu phân công.');
      }

      // Log hành động CREATE
      $this->_store->logAction(
        assignmentId: $assignmentId, 
        action: 'CREATE', 
        oldTeacherId: null, 
        newTeacherId: $teacherId, 
        performedBy: $assignedBy, 
        reason: 'Phân công lần đầu'
      );

      return true;
    });
  }

  /**
   * Phân công tự động (auto_even và auto_shuffle)
   */
  public function autoAssign(int $batchId, string $method, int $adminId): int
  {
    return Database::getInstance()->transaction(function () use ($batchId, $method, $adminId) {
      $unassigned = $this->_store->getUnassignedStudentsInBatch($batchId);
      if (empty($unassigned)) {
        return 0;
      }

      $supervisors = $this->_store->getBatchSupervisorsWithStats($batchId);
      $totalRemainingQuota = 0;
      $availableSupervisors = [];
      
      foreach ($supervisors as $sup) {
        $max = $sup['max_students'];
        $remaining = $max - $sup['current_assigned'];
        if ($remaining > 0) {
          $totalRemainingQuota += $remaining;
          $availableSupervisors[] = [
            'teacher_id' => $sup['teacher_id'],
            'remaining' => $remaining,
            'current' => $sup['current_assigned']
          ];
        }
      }

      //TODO: Hiển thị thông báo trực quan hơn throw exception
      if (count($unassigned) > $totalRemainingQuota) {
        throw new \Exception('Tổng số sinh viên chưa phân công ('.count($unassigned).') lớn hơn tổng hạn mức còn lại ('.$totalRemainingQuota.') của các giảng viên.');
      }

      if ($method === 'auto_shuffle') {
        shuffle($unassigned);
      }

      $assignedCount = 0;

      foreach ($unassigned as $student) {
        if (empty($availableSupervisors)) {
          break;
        }

        if ($method === 'auto_even') {
          // Ưu tiên GV có ít sinh viên nhất
          usort($availableSupervisors, function($a, $b) {
            return $a['current'] <=> $b['current'];
          });
          $chosenIndex = 0;
        } else {
          // Random GV còn quota
          $chosenIndex = array_rand($availableSupervisors);
        }

        $sup = &$availableSupervisors[$chosenIndex];
        $teacherId = $sup['teacher_id'];
        
        $assignmentId = $this->_store->createAssignment(
          batchStudentId: $student['batch_student_id'],
          teacherId: $teacherId,
          status: 'draft',
          method: $method,
          assignedBy: $adminId
        );
        
        $this->_store->logAction(
          assignmentId: $assignmentId,
          action: 'CREATE',
          oldTeacherId: null,
          newTeacherId: $teacherId,
          performedBy: $adminId,
          reason: 'Phân công tự động của hệ thống'
        );

        $sup['current']++;
        $sup['remaining']--;
        
        if ($sup['remaining'] <= 0) {
          array_splice($availableSupervisors, $chosenIndex, 1);
        }
        
        $assignedCount++;
      }

      return $assignedCount;
    });
  }

  /**
   * Chuyển giảng viên hướng dẫn (Cập nhật cục bộ)
   */
  public function reassign(int $assignmentId, int $newTeacherId, int $adminId, string $reason): bool
  {
    return Database::getInstance()->transaction(function () use ($assignmentId, $newTeacherId, $adminId, $reason) {
      $assignment = $this->_store->getAssignmentById($assignmentId);
      if (!$assignment) {
        throw new \Exception('Không tìm thấy bản ghi phân công này.');
      }

      if ($assignment->teacher_id == $newTeacherId) {
        throw new \Exception('Giảng viên mới trùng với giảng viên hiện tại.');
      }

      $oldTeacherId = $assignment->teacher_id;

      // Update ID Giảng viên mới (vẫn giữ nguyên status hiện tại)
      $updated = $this->_store->updateAssignmentTeacher($assignmentId, $newTeacherId);
      
      if (!$updated) {
        throw new \Exception('Cập nhật phân công thất bại.');
      }

      // Log hành động UPDATE
      $this->_store->logAction(
        assignmentId: $assignmentId, 
        action: 'UPDATE', 
        oldTeacherId: $oldTeacherId, 
        newTeacherId: $newTeacherId, 
        performedBy: $adminId, 
        reason: $reason
      );

      return true;
    });
  }

  /**
   * Lưu nhiều thay đổi phân công cùng lúc và công bố toàn bộ đợt
   * $assignmentsData có dạng: [['assignment_id' => 1, 'new_teacher_id' => 2], ...]
   */
  public function bulkReassignAndPublish(int $batchId, array $assignmentsData, int $adminId, string $reason): int
  {
    return Database::getInstance()->transaction(function () use ($batchId, $assignmentsData, $adminId, $reason) {
      foreach ($assignmentsData as $data) {
        if (!isset($data['assignment_id']) || !isset($data['new_teacher_id'])) {
          continue;
        }

        $assignmentId = $data['assignment_id'];
        $newTeacherId = $data['new_teacher_id'];
        
        $assignment = $this->_store->getAssignmentById($assignmentId);
        if (!$assignment || $assignment->teacher_id == $newTeacherId) {
          continue;
        }
        
        $oldTeacherId = $assignment->teacher_id;
        $this->_store->updateAssignmentTeacher($assignmentId, $newTeacherId);
        
        $this->_store->logAction(
          assignmentId: $assignmentId, 
          action: 'UPDATE', 
          oldTeacherId: $oldTeacherId, 
          newTeacherId: $newTeacherId, 
          performedBy: $adminId, 
          reason: $reason
        );
      }
      
      // Sau khi lưu toàn bộ thay đổi, tiến hành công bố (chuyển draft -> published)
      return $this->_store->publishBatchAssignments($batchId);
    });
  }

  /**
   * Công bố toàn bộ các bản nháp của 1 đợt thực tập
   */
  public function publishBatch(int $batchId, int $adminId): int
  {
    return Database::getInstance()->transaction(function () use ($batchId, $adminId) {
      // Chuyển toàn bộ status = 'draft' -> 'published' trong batch_id đó
      // Theo Option A đã chốt: Không sinh ra log cho hành động Publish để tránh rác DB
      return $this->_store->publishBatchAssignments($batchId);
    });
  }

  /**
   * Xem lịch sử của 1 sinh viên trong 1 đợt
   */
  public function getLogsByStudent(int $batchStudentId): array
  {
    return $this->_store->getLogsByBatchStudent($batchStudentId);
  }
}
