<?php

namespace App\Services;

require_once BASE_PATH . '/models/internship_assignment.php';
require_once BASE_PATH . '/models/assignment_log.php';
require_once BASE_PATH . '/stores/internship_assignment_store.php';

use App\Stores\{InternshipAssignmentStore, InternshipBatchStore};
use App\Enums\BatchStatus;
use Database;

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
  private ?MailService $_mailService;

  public function __construct(InternshipAssignmentStore $store, InternshipBatchStore $batchStore, ?MailService $mailService = null)
  {
    $this->_store = $store;
    $this->_batchStore = $batchStore;
    $this->_mailService = $mailService;
  }

  /**
   * Phân công lần đầu (hoặc chạy qua auto-assign)
   */
  public function assign(int $batchStudentId, int $teacherId, ?int $assignedBy = null): bool
  {
    return Database::getInstance()->transaction(function () use ($batchStudentId, $teacherId, $assignedBy) {
      $this->validateAssignmentTarget($batchStudentId, $teacherId);
      // Kiểm tra xem sinh viên đã có assignment nào chưa
      $existingAssignment = $this->_store->getAssignmentByBatchStudentId($batchStudentId);
      if ($existingAssignment) {
        throw new \Exception('Sinh viên này đã được phân công trong đợt. Vui lòng dùng tính năng Reassign.');
      }

      $assignmentId = $this->_store->createAssignment(
        batchStudentId: $batchStudentId,
        teacherId: $teacherId,
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

      $this->queueStudentNotification($batchStudentId, null, $teacherId);
      $digestData = [];
      $this->recordDigestChange($digestData, $batchStudentId, null, $teacherId);
      $studentDetails = $this->_store->getMailingDetails($batchStudentId, null, null);
      $this->queueTeacherDigestsFromStudentDetails($studentDetails, $digestData);

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
        throw new \Exception('Chỉ có thể phân công khi đợt thực tập ở trạng thái Nháp hoặc Đang mở.');
      }

      $unassigned = $this->_store->getUnassignedStudentsInBatch($batchId);
      if (empty($unassigned)) {
        return 0;
      }

      $supervisors = $this->_store->getBatchSupervisorsWithStats($batchId);
      $totalRemainingQuota = 0;
      $availableSupervisors = [];

      foreach ($supervisors as $sup) {
        $max = $sup['max_students'] === null ? count($unassigned) + (int) $sup['current_assigned'] : (int) $sup['max_students'];
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
        throw new \Exception('Tổng số sinh viên chưa phân công (' . count($unassigned) . ') lớn hơn tổng hạn mức còn lại (' . $totalRemainingQuota . ') của các giảng viên.');
      }

      if ($method === 'auto_shuffle') {
        shuffle($unassigned);
      }

      $assignedCount = 0;
      $digestData = [];

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

        $this->recordDigestChange($digestData, (int) $student['batch_student_id'], null, (int) $teacherId);
        $this->queueStudentNotification((int) $student['batch_student_id'], null, (int) $teacherId);

        $sup['current']++;
        $sup['remaining']--;

        if ($sup['remaining'] <= 0) {
          array_splice($availableSupervisors, $chosenIndex, 1);
        }

        $assignedCount++;
      }

      $this->queueTeacherDigests($batch, $digestData);
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

      $this->validateAssignmentTarget((int) $assignment->batch_student_id, $newTeacherId, $assignmentId);

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

      $digestData = [];
      $this->recordDigestChange($digestData, (int) $assignment->batch_student_id, (int) $oldTeacherId, $newTeacherId);
      $this->queueStudentNotification((int) $assignment->batch_student_id, (int) $oldTeacherId, $newTeacherId);
      $studentDetails = $this->_store->getMailingDetails((int) $assignment->batch_student_id, null, null);
      $this->queueTeacherDigestsFromStudentDetails($studentDetails, $digestData);

      return true;
    });
  }

  public function bulkSave(int $batchId, array $assignmentsData, int $adminId, string $reason): int
  {
    return Database::getInstance()->transaction(function () use ($batchId, $assignmentsData, $adminId, $reason) {
      $batch = $this->_batchStore->getById($batchId);
      if (!$batch || !in_array($batch['status'], ['draft', 'published'])) {
        throw new \Exception('Chỉ có thể phân công khi đợt thực tập ở trạng thái Nháp hoặc Đang mở.');
      }

      $count = 0;
      $digestData = [];
      foreach ($assignmentsData as $data) {
        $assignmentId = $data['assignment_id'] ?? null;
        $batchStudentId = $data['batch_student_id'] ?? null;
        $newTeacherId = $data['new_teacher_id'] ?? null;

        if (!array_key_exists('new_teacher_id', $data) || (!$assignmentId && !$batchStudentId)) {
          continue;
        }

        if ($assignmentId) {
          $assignment = $this->_store->getAssignmentById($assignmentId);
          if (!$assignment)
            continue;

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
            $this->recordDigestChange($digestData, (int) $assignment->batch_student_id, (int) $assignment->teacher_id, null);
            $this->queueStudentNotification((int) $assignment->batch_student_id, (int) $assignment->teacher_id, null);
            $count++;
            continue;
          }

          $this->validateAssignmentTarget((int) $assignment->batch_student_id, (int) $newTeacherId, (int) $assignmentId);

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
          $this->recordDigestChange($digestData, (int) $assignment->batch_student_id, (int) $oldTeacherId, (int) $newTeacherId);
          $this->queueStudentNotification((int) $assignment->batch_student_id, (int) $oldTeacherId, (int) $newTeacherId);
          $count++;
        } else {
          if ($newTeacherId === null || $newTeacherId === 0)
            continue;
          $this->validateAssignmentTarget((int) $batchStudentId, (int) $newTeacherId);

          $existing = $this->_store->getAssignmentByBatchStudentId($batchStudentId);
          if ($existing)
            continue;

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
            $this->recordDigestChange($digestData, (int) $batchStudentId, null, (int) $newTeacherId);
            $this->queueStudentNotification((int) $batchStudentId, null, (int) $newTeacherId);
            $count++;
          }
        }
      }

      $this->queueTeacherDigests($batch, $digestData);
      return $count;
    });
  }

  public function unassign(int $assignmentId, int $adminId, string $reason): bool
  {
    return Database::getInstance()->transaction(function () use ($assignmentId, $adminId, $reason) {
      $assignment = $this->_store->getAssignmentById($assignmentId);
      if (!$assignment) {
        throw new \Exception('Không tìm thấy bản ghi phân công này.');
      }

      $this->validateAssignmentTarget((int) $assignment->batch_student_id, (int) $assignment->teacher_id, (int) $assignment->id);

      $this->_store->logAction(
        assignmentId: $assignmentId,
        action: 'DELETE',
        oldTeacherId: $assignment->teacher_id,
        newTeacherId: null,
        performedBy: $adminId,
        reason: $reason
      );

      $digestData = [];
      $this->recordDigestChange($digestData, (int) $assignment->batch_student_id, (int) $assignment->teacher_id, null);
      $this->queueStudentNotification((int) $assignment->batch_student_id, (int) $assignment->teacher_id, null);
      $studentDetails = $this->_store->getMailingDetails((int) $assignment->batch_student_id, null, null);
      $this->queueTeacherDigestsFromStudentDetails($studentDetails, $digestData);

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

  private function queueStudentNotification(int $batchStudentId, ?int $oldTeacherId, ?int $newTeacherId): void
  {
    if (!$this->_mailService)
      return;

    $mailDetails = $this->_store->getMailingDetails($batchStudentId, $oldTeacherId, $newTeacherId);
    $student = $mailDetails['student'] ?? null;
    if (!$student || empty($student['email']) || ($student['batch_status'] ?? '') !== BatchStatus::PUBLISHED)
      return;

    $this->_mailService->sendReassignNotification($student['email'], $student['name'], [
      'studentName' => $student['name'],
      'mssv' => $student['mssv'],
      'batchTitle' => $student['batch_title'] ?? 'Không rõ',
      'startAt' => isset($student['start_at']) ? date('d/m/Y', strtotime($student['start_at'])) : 'Không rõ',
      'endAt' => isset($student['end_at']) ? date('d/m/Y', strtotime($student['end_at'])) : 'Không rõ',
      'oldTeacherName' => $mailDetails['old_teacher']['name'] ?? 'Không có',
      'newTeacherName' => $mailDetails['new_teacher']['name'] ?? 'Không có',
    ]);
  }

  private function recordDigestChange(array &$digestData, int $batchStudentId, ?int $oldTeacherId, ?int $newTeacherId): void
  {
    if (!$this->_mailService)
      return;

    $mailDetails = $this->_store->getMailingDetails($batchStudentId, $oldTeacherId, $newTeacherId);
    $student = $mailDetails['student'] ?? null;
    if (!$student)
      return;

    if ($oldTeacherId && !empty($mailDetails['old_teacher'])) {
      $digestData[$oldTeacherId] ??= [
        'teacher' => $mailDetails['old_teacher'],
        'assigned' => [],
        'unassigned' => [],
      ];
      $digestData[$oldTeacherId]['unassigned'][] = $student;
    }

    if ($newTeacherId && !empty($mailDetails['new_teacher'])) {
      $digestData[$newTeacherId] ??= [
        'teacher' => $mailDetails['new_teacher'],
        'assigned' => [],
        'unassigned' => [],
      ];
      $digestData[$newTeacherId]['assigned'][] = $student;
    }
  }

  private function queueTeacherDigests(array $batch, array $digestData): void
  {
    if (!$this->_mailService || ($batch['status'] ?? '') !== BatchStatus::PUBLISHED)
      return;

    $this->queueTeacherDigestsWithBatchDetails([
      'title' => $batch['title'] ?? 'Không rõ',
      'startAt' => isset($batch['start_at']) ? date('d/m/Y', strtotime($batch['start_at'])) : 'Không rõ',
      'endAt' => isset($batch['end_at']) ? date('d/m/Y', strtotime($batch['end_at'])) : 'Không rõ',
    ], $digestData);
  }

  private function queueTeacherDigestsFromStudentDetails(array $mailDetails, array $digestData): void
  {
    $student = $mailDetails['student'] ?? null;
    if (!$student || ($student['batch_status'] ?? '') !== BatchStatus::PUBLISHED)
      return;

    $this->queueTeacherDigestsWithBatchDetails([
      'title' => $student['batch_title'] ?? 'Không rõ',
      'startAt' => isset($student['start_at']) ? date('d/m/Y', strtotime($student['start_at'])) : 'Không rõ',
      'endAt' => isset($student['end_at']) ? date('d/m/Y', strtotime($student['end_at'])) : 'Không rõ',
    ], $digestData);
  }

  private function queueTeacherDigestsWithBatchDetails(array $batchDetails, array $digestData): void
  {
    if (!$this->_mailService)
      return;

    foreach ($digestData as $data) {
      $teacher = $data['teacher'];
      if (empty($teacher['email']))
        continue;

      $this->_mailService->queueDigestNotification(
        $teacher['email'],
        $teacher['name'] ?? 'Giảng viên',
        $batchDetails,
        $data['assigned'],
        $data['unassigned']
      );
    }
  }

  private function validateAssignmentTarget(int $batchStudentId, int $teacherId, ?int $currentAssignmentId = null): void
  {
    $student = $this->_batchStore->getStudentGradingDetail($batchStudentId);
    if (!$student)
      throw new \Exception('Sinh viên chưa được đăng ký vào đợc thực tập.');
    $batchId = (int) $student['batch_id'];
    $batch = $this->_batchStore->getById($batchId);
    if (!$batch || !in_array($batch['status'], ['draft', 'published'], true)) {
      throw new \Exception('Không thể thay đổi phân công cho đợt thực tập đã đóng.');
    }
    if (!$this->_batchStore->isSupervisorOfBatch($batchId, $teacherId)) {
      throw new \Exception('Giảng viên được chọn không phải là giảng viên hướng dẫn trong đợt thực tập này.');
    }
    foreach ($this->_store->getBatchSupervisorsWithStats($batchId) as $supervisor) {
      if ((int) $supervisor['teacher_id'] !== $teacherId)
        continue;
      $current = (int) $supervisor['current_assigned'];
      if ($currentAssignmentId !== null) {
        $currentAssignment = $this->_store->getAssignmentById($currentAssignmentId);
        if ($currentAssignment && (int) $currentAssignment->teacher_id === $teacherId)
          return;
      }
      if ($supervisor['max_students'] !== null && $current >= (int) $supervisor['max_students']) {
        throw new \Exception('Giảng viên hướng dẫn được chọn đã đạt giới hạn số lượng sinh viên.');
      }
      return;
    }
    throw new \Exception('Không tìm thấy thông tin về thông tin phân công của giảng viên hướng dẫn.');
  }
}
