<?php

namespace App\Services;

require_once BASE_PATH . '/models/internship_assignment.php';
require_once BASE_PATH . '/models/assignment_log.php';
require_once BASE_PATH . '/stores/internship_assignment_store.php';

use App\Stores\{InternshipAssignmentStore, InternshipBatchStore};
use App\Services\MailService;
use Database;
use Exception;

interface IInternshipAssignmentService
{
  public function assign(int $batchStudentId, int $teacherId, ?int $assignedBy = null): bool;
  public function autoAssign(int $batchId, string $method, int $adminId): int;
  public function reassign(int $assignmentId, int $newTeacherId, int $adminId, string $reason): bool;
  public function bulkSave(int $batchId, array $assignmentsData, int $adminId, string $reason): int;
  public function unassign(int $assignmentId, int $adminId, string $reason): bool;
  public function getLogsByStudent(int $batchStudentId): array;
  public function getAssignmentByBatchStudentId(int $batchStudentId);
}

class InternshipAssignmentService implements IInternshipAssignmentService
{
  private InternshipAssignmentStore $_store;
  private InternshipBatchStore $_batchStore;
  private MailService $_mailService;

  public function __construct(InternshipAssignmentStore $store, InternshipBatchStore $batchStore, ?MailService $mailService = null)
  {
    $this->_store = $store;
    $this->_batchStore = $batchStore;
    $this->_mailService = $mailService;
  }

  private function _sendReassignEmails(int $batchStudentId, ?int $oldTeacherId, ?int $newTeacherId, string $reason): void
  {
    $mailDetails = $this->_store->getMailingDetails($batchStudentId, $oldTeacherId, $newTeacherId);
    $details = [
      'studentName' => $mailDetails['student']['name'] ?? 'Sinh viên',
      'mssv' => $mailDetails['student']['mssv'] ?? 'Không rõ',
      'batchTitle' => $mailDetails['student']['batch_title'] ?? 'Không rõ',
      'startAt' => isset($mailDetails['student']['start_at']) ? date('d/m/Y', strtotime($mailDetails['student']['start_at'])) : 'Không rõ',
      'endAt' => isset($mailDetails['student']['end_at']) ? date('d/m/Y', strtotime($mailDetails['student']['end_at'])) : 'Không rõ',
      'oldTeacherName' => $mailDetails['old_teacher']['name'] ?? 'Không có',
      'newTeacherName' => $mailDetails['new_teacher']['name'] ?? 'Không có',
      'reason' => $reason
    ];

    if (!empty($mailDetails['student']['email'])) {
      $this->_mailService->sendReassignNotification($mailDetails['student']['email'], $mailDetails['student']['name'], $details);
    }
    if (!empty($mailDetails['old_teacher']['email'])) {
      $this->_mailService->sendReassignNotification($mailDetails['old_teacher']['email'], $mailDetails['old_teacher']['name'], $details);
    }
    if (!empty($mailDetails['new_teacher']['email'])) {
      $this->_mailService->sendReassignNotification($mailDetails['new_teacher']['email'], $mailDetails['new_teacher']['name'], $details);
    }
  }

  /**
   * Phân công lần đầu (hoặc chạy qua auto-assign)
   */
  public function assign(int $batchStudentId, int $teacherId, ?int $assignedBy = null): bool
  {
    return Database::getInstance()->transaction(function () use ($batchStudentId, $teacherId, $assignedBy) {
      // Kiểm tra xem sinh viên đã có assignment nào chưa
      $existingAssignment = $this->_store->getAssignmentByBatchStudentId($batchStudentId);
      if ($existingAssignment) {
        throw new Exception('Sinh viên này đã được phân công trong đợt. Vui lòng dùng tính năng Reassign.');
      }

      $assignmentId = $this->_store->createAssignment(
        batchStudentId: $batchStudentId,
        teacherId: $teacherId,
        method: 'manual',
        assignedBy: $assignedBy
      );

      if (!$assignmentId) {
        throw new Exception('Không thể lưu phân công.');
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
      $batch = $this->_batchStore->getById($batchId);
      if (!$batch || !in_array($batch['status'], ['draft', 'published'])) {
        throw new Exception('Chỉ có thể phân công khi đợt thực tập ở trạng thái Nháp hoặc Đang mở.');
      }

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

      if (count($unassigned) > $totalRemainingQuota) {
        throw new Exception('Tổng số sinh viên chưa phân công (' . count($unassigned) . ') lớn hơn tổng hạn mức còn lại (' . $totalRemainingQuota . ') của các giảng viên.');
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
          usort($availableSupervisors, function ($a, $b) {
            return $a['current'] <=> $b['current'];
          });
          $chosenIndex = 0;
        } else {
          $chosenIndex = array_rand($availableSupervisors);
        }

        $sup = &$availableSupervisors[$chosenIndex];
        $teacherId = $sup['teacher_id'];

        $assignmentId = $this->_store->createAssignment(
          batchStudentId: $student['batch_student_id'],
          teacherId: $teacherId,
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
        throw new Exception('Không tìm thấy bản ghi phân công này.');
      }

      if ($assignment->teacher_id == $newTeacherId) {
        throw new Exception('Giảng viên mới trùng với giảng viên hiện tại.');
      }

      $oldTeacherId = $assignment->teacher_id;

      // Update ID Giảng viên mới (vẫn giữ nguyên status hiện tại)
      $updated = $this->_store->updateAssignmentTeacher($assignmentId, $newTeacherId);

      if (!$updated) {
        throw new Exception('Cập nhật phân công thất bại.');
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

      // Gửi email thông báo
      $this->_sendReassignEmails($assignment->batch_student_id, $oldTeacherId, $newTeacherId, $reason);

      return true;
    });
  }

  public function bulkSave(int $batchId, array $assignmentsData, int $adminId, string $reason): int
  {
    return Database::getInstance()->transaction(function () use ($batchId, $assignmentsData, $adminId, $reason) {
      $batch = $this->_batchStore->getById($batchId);
      if (!$batch || !in_array($batch['status'], ['draft', 'published'])) {
        throw new Exception('Chỉ có thể phân công khi đợt thực tập ở trạng thái Nháp hoặc Đang mở.');
      }

      $count = 0;
      foreach ($assignmentsData as $data) {
        $assignmentId = $data['assignment_id'] ?? null;
        $batchStudentId = $data['batch_student_id'] ?? null;
        $newTeacherId = $data['new_teacher_id'] ?? null;

        if (!array_key_exists('new_teacher_id', $data) || (!$assignmentId && !$batchStudentId)) {
          continue;
        }

        if ($assignmentId) {
          $assignment = $this->_store->getAssignmentById($assignmentId);
          if (!$assignment) continue;

          if ($newTeacherId === null || $newTeacherId === 0) {
            // UNASSIGN
            $this->_store->logAction(
              assignmentId: $assignmentId,
              action: 'DELETE',
              oldTeacherId: $assignment->teacher_id,
              newTeacherId: null,
              performedBy: $adminId,
              reason: $reason
            );
            $this->_store->deleteAssignment($assignmentId);
            $count++;
            continue;
          }

          if ($assignment->teacher_id == $newTeacherId) {
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

          // Gửi email thông báo
          $this->_sendReassignEmails($assignment->batch_student_id, $oldTeacherId, $newTeacherId, $reason);

          $count++;
        } else {
          if ($newTeacherId === null || $newTeacherId === 0) continue;

          $existing = $this->_store->getAssignmentByBatchStudentId($batchStudentId);
          if ($existing) continue;

          $newId = $this->_store->createAssignment(
            batchStudentId: $batchStudentId,
            teacherId: $newTeacherId,
            method: 'manual',
            assignedBy: $adminId
          );

          if ($newId) {
            $this->_store->logAction(
              assignmentId: $newId,
              action: 'CREATE',
              oldTeacherId: null,
              newTeacherId: $newTeacherId,
              performedBy: $adminId,
              reason: $reason
            );

            // Gửi email thông báo
            $this->_sendReassignEmails($batchStudentId, null, $newTeacherId, $reason);

            $count++;
          }
        }
      }

      return $count;
    });
  }

  public function unassign(int $assignmentId, int $adminId, string $reason): bool
  {
    return Database::getInstance()->transaction(function () use ($assignmentId, $adminId, $reason) {
      $assignment = $this->_store->getAssignmentById($assignmentId);
      if (!$assignment) {
        throw new Exception('Không tìm thấy bản ghi phân công này.');
      }

      $this->_store->logAction(
        assignmentId: $assignmentId,
        action: 'DELETE',
        oldTeacherId: $assignment->teacher_id,
        newTeacherId: null,
        performedBy: $adminId,
        reason: $reason
      );

      return $this->_store->deleteAssignment($assignmentId);
    });
  }

  /**
   * Xem lịch sử của 1 sinh viên trong 1 đợt
   */
  public function getLogsByStudent(int $batchStudentId): array
  {
    return $this->_store->getLogsByBatchStudent($batchStudentId);
  }

  public function getAssignmentByBatchStudentId(int $batchStudentId)
  {
    return $this->_store->getAssignmentByBatchStudentId($batchStudentId);
  }
}
