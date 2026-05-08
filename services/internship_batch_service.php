<?php

namespace App\Services;

use App\Stores\{InternshipBatchStore, InternshipAssignmentStore, TeacherStore, AccountStore, InternshipSubmissionStore};
use App\Core\Pageable;
use Database;

class InternshipBatchService
{
  private InternshipBatchStore $_store;
  private InternshipAssignmentStore $_assignmentStore;
  private TeacherStore $_teacherStore;
  private AccountStore $_accountStore;
  private InternshipSubmissionStore $_submissionStore;

  public function __construct(
    InternshipBatchStore $store,
    InternshipAssignmentStore $assignmentStore,
    TeacherStore $teacherStore,
    AccountStore $accountStore,
    InternshipSubmissionStore $submissionStore
  ) {
    $this->_store = $store;
    $this->_assignmentStore = $assignmentStore;
    $this->_teacherStore = $teacherStore;
    $this->_accountStore = $accountStore;
    $this->_submissionStore = $submissionStore;
  }

  /**
   * Tạo toàn bộ đợt thực tập trong 1 transaction (Batch, Classrooms, Students, Supervisors)
   */
  public function createFullBatch(array $batchData, array $studentIds, array $supervisors, array $classroomIds, int $adminId): int
  {
    return Database::getInstance()->transaction(function () use ($batchData, $studentIds, $supervisors, $classroomIds, $adminId) {

      $batchData['created_by'] = $adminId;
      $batchId = $this->_store->createBatch($batchData);

      if (!$batchId) {
        throw new \Exception('Không thể tạo thông tin đợt thực tập.');
      }

      $this->_store->addClassroomsToBatch($batchId, $classroomIds);
      $this->_store->addStudentsToBatch($batchId, $studentIds);
      $this->_store->addSupervisorsToBatch($batchId, $supervisors);

      return $batchId;
    });
  }

  public function getEligibleStudentsByClassroom(int $classroomId): array
  {
    return $this->_store->getEligibleStudentsByClassroom($classroomId);
  }

  public function getEligibleStudentsByClassrooms(array $classroomIds): array
  {
    return $this->_store->getEligibleStudentsByClassrooms($classroomIds);
  }

  public function validateStudentsBulk(array $studentIds): array
  {
    $students = $this->_store->validateStudentsByStudentIds($studentIds);

    $valid = [];
    $invalid = [];

    $studentsMap = [];
    foreach ($students as $s) {
      $studentsMap[$s['student_id']] = $s;
    }

    foreach ($studentIds as $studentId) {
      if (!isset($studentsMap[$studentId])) {
        $invalid[] = ['student_id' => $studentId, 'reason' => 'Không tìm thấy sinh viên trong hệ thống.'];
        continue;
      }

      $s = $studentsMap[$studentId];

      if ($s['status'] !== 'Đang học') {
        $invalid[] = ['student_id' => $studentId, 'reason' => 'Trạng thái không phải "Đang học".'];
        continue;
      }

      if ($s['batch_id']) {
        $invalid[] = ['student_id' => $studentId, 'reason' => 'Đã tham gia một đợt thực tập khác.'];
        continue;
      }

      $valid[] = $s;
    }

    return [
      'valid' => $valid,
      'invalid' => $invalid
    ];
  }

  public function getActiveTeachers(): array
  {
    return $this->_store->getActiveTeachers();
  }

  public function getAllClassrooms(): array
  {
    return $this->_store->getAllClassrooms();
  }

  public function getBatches(int $page, int $limit = 15): Pageable
  {
    $items = $this->_store->getPaginated($page, $limit);
    $total = $this->_store->getTotalCount();

    return new Pageable($items, $total, $limit, $page);
  }

  public function getBatchById(int $id): ?array
  {
    return $this->_store->getById($id);
  }

  public function getBatchWithStats(int $id): ?array
  {
    $batch = $this->_store->getById($id);
    if (!$batch) return null;

    $stats = $this->_store->getBatchStats($id);
    return array_merge($batch, ['stats' => $stats]);
  }

  public function updateBatch(int $id, array $data): bool
  {
    return $this->_store->update($id, $data);
  }

  public function deleteBatch(int $id): bool
  {
    $stats = $this->_store->getBatchStats($id);

    if ($stats['has_submissions'] || $stats['has_grades']) {
      throw new \Exception('Không thể xóa đợt thực tập đã có bài nộp hoặc điểm số.');
    }

    return $this->_store->delete($id);
  }

  public function publishBatch(int $id): bool
  {
    return $this->_store->updateStatus($id, 'published', [
      'published_at' => date('Y-m-d H:i:s')
    ]);
  }

  public function closeBatch(int $id): bool
  {
    return $this->_store->updateStatus($id, 'closed', [
      'closed_at' => date('Y-m-d H:i:s')
    ]);
  }

  public function getBatchStudents(int $batchId): array
  {
    return $this->_store->getBatchStudentsWithDetails($batchId);
  }

  public function getBatchSupervisors(int $batchId): array
  {
    return $this->_store->getBatchSupervisorsWithDetails($batchId);
  }

  public function addStudentToBatch(int $batchId, int $studentId): bool
  {
    return $this->_store->addStudentsToBatch($batchId, [$studentId]);
  }

  public function removeStudentFromBatch(int $batchId, int $studentId): bool
  {
    return $this->_store->removeStudentFromBatch($batchId, $studentId);
  }

  public function addSupervisorToBatch(int $batchId, int $teacherId, int $maxStudents): bool
  {
    return $this->_store->addSupervisorsToBatch($batchId, [
      ['teacher_id' => $teacherId, 'max_students' => $maxStudents]
    ]);
  }

  public function removeSupervisorFromBatch(int $batchId, int $teacherId): bool
  {
    return $this->_store->removeSupervisorFromBatch($batchId, $teacherId);
  }

  public function updateSupervisorQuota(int $batchId, int $teacherId, int $newQuota): bool
  {
    // Kiểm tra quota mới có nhỏ hơn số lượng đã phân công không
    $supervisors = $this->_store->getBatchSupervisorsWithDetails($batchId);
    foreach ($supervisors as $sup) {
      if ($sup['teacher_id'] == $teacherId) {
        if ($newQuota < $sup['assigned_count']) {
          throw new \Exception("Không thể giảm định mức xuống thấp hơn số sinh viên hiện đang hướng dẫn ({$sup['assigned_count']}).");
        }
        break;
      }
    }

    return $this->_store->updateSupervisorQuota($batchId, $teacherId, $newQuota);
  }

  public function searchEligibleStudents(int $batchId, string $query = '', ?int $classroomId = null): array
  {
    return $this->_store->searchEligibleStudents($batchId, $query, $classroomId);
  }

  public function searchEligibleTeachers(int $batchId, string $query = ''): array
  {
    return $this->_store->searchEligibleTeachers($batchId, $query);
  }

  public function getStudentDashboardData(int $studentId, ?int $batchId = null): array
  {
    // Lấy tất cả các đợt của SV này
    $batches = $this->_store->getBatchesByStudentId($studentId);

    if (empty($batches)) {
      return ['batches' => [], 'current' => null];
    }

    // Lấy đợt gần nhất mà SV tham gia
    $currentBatch = null;
    if ($batchId) {
      foreach ($batches as $b) {
        if ($b['id'] == $batchId) {
          $currentBatch = $b;
          break;
        }
      }
    } else {
      $currentBatch = $batches[0];
    }

    if (!$currentBatch) return ['batches' => $batches, 'current' => null];

    // Lấy chi tiết thông tin thực tập của SV
    $assignment = $this->_assignmentStore->getAssignmentByBatchStudentId($currentBatch['batch_student_id']);

    $supervisor = null;
    $logs = [];
    $submissions = [];
    if ($assignment) {
      $supervisor = $this->_teacherStore->getById($assignment->teacher_id);
      if ($supervisor->account_id) {
        $supervisor->account = $this->_accountStore->getById($supervisor->account_id);
      }
      $logs = $this->_assignmentStore->getLogsByBatchStudent($currentBatch['batch_student_id']);
      $submissions = $this->_submissionStore->getAllByBatchStudentId($currentBatch['batch_student_id']);
    }

    return [
      'batches' => $batches,
      'current' => $currentBatch,
      'assignment' => $assignment,
      'supervisor' => $supervisor,
      'submissions' => $submissions,
      'logs' => $logs
    ];
  }

  public function updateStudentInternshipInfo(int $batchStudentId, array $data): bool
  {
    return $this->_store->updateBatchStudentCompany($batchStudentId, $data);
  }
}
