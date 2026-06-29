<?php

namespace App\Services;

use App\Enums\BatchStatus;
use App\Stores\InternshipGradeStore;
use App\Stores\InternshipSubmissionStore;
use App\Stores\InternshipBatchStore;
use App\Stores\InternshipAssignmentStore;
use DateTime;

interface IInternshipGradeService
{
  public function getGradeByBatchStudentId(int $batchStudentId): ?array;
  public function saveGrade(int $batchStudentId, float $score, ?string $scoreReason, ?string $feedback, int $teacherId): array;
  public function canTeacherGrade(int $batchId, int $teacherId, int $batchStudentId): array;
  public function isWithinGradingDeadline(int $batchId): bool;
  public function getStudentGradingData(int $batchStudentId, int $teacherId): ?array;
}

class InternshipGradeService implements IInternshipGradeService
{
  protected InternshipGradeStore $_gradeStore;
  protected InternshipBatchStore $_batchStore;
  protected InternshipSubmissionStore $_submissionStore;
  protected InternshipAssignmentStore $_assignmentStore;
  protected WebSettingsService $_webSettingsService;

  public function __construct(
    InternshipGradeStore $gradeStore,
    InternshipBatchStore $batchStore,
    InternshipSubmissionStore $submissionStore,
    InternshipAssignmentStore $assignmentStore,
    WebSettingsService $webSettingsService
  ) {
    $this->_gradeStore = $gradeStore;
    $this->_batchStore = $batchStore;
    $this->_submissionStore = $submissionStore;
    $this->_assignmentStore = $assignmentStore;
    $this->_webSettingsService = $webSettingsService;
  }

  public function getGradeByBatchStudentId(int $batchStudentId): ?array
  {
    return $this->_gradeStore->getByBatchStudentId($batchStudentId);
  }

  public function saveGrade(int $batchStudentId, float $score, ?string $scoreReason, ?string $feedback, int $teacherId): array
  {
    if ($score < 0 || $score > 10) {
      return ['success' => false, 'message' => 'Điểm phải nằm trong khoảng 0 - 10.'];
    }

    $existingGrade = $this->_gradeStore->getByBatchStudentId($batchStudentId);

    if ($existingGrade) {
      $this->_gradeStore->update($existingGrade['id'], [
        'final_score' => $score,
        'score_reason' => $scoreReason,
        'feedback' => $feedback,
        'graded_by' => $teacherId
      ]);

      return ['success' => true, 'message' => 'Cập nhật điểm thành công.', 'is_new' => false];
    } else {
      $this->_gradeStore->create([
        'batch_student_id' => $batchStudentId,
        'final_score' => $score,
        'score_reason' => $scoreReason,
        'feedback' => $feedback,
        'graded_by' => $teacherId
      ]);

      return ['success' => true, 'message' => 'Chấm điểm thành công.', 'is_new' => true];
    }
  }

  public function canTeacherGrade(int $batchId, int $teacherId, int $batchStudentId): array
  {
    $batch = $this->_batchStore->getById($batchId);
    if (!$batch || in_array($batch['status'], [BatchStatus::DRAFT, BatchStatus::CLOSED])) {
      return ['allowed' => false, 'reason' => 'Không thể thay đổi điểm cho đợt thực tập này.'];
    }
    $student = $this->_batchStore->getStudentGradingDetail($batchStudentId);
    if (!$student || (int) $student['batch_id'] !== $batchId) {
      return ['allowed' => false, 'reason' => 'Sinh viên không thuộc đợt thực tập này.'];
    }
    if (!$this->_batchStore->isSupervisorOfBatch($batchId, $teacherId)) {
      return ['allowed' => false, 'reason' => 'Bạn không phải giảng viên hướng dẫn của đợt thực tập này.'];
    }

    $assignment = $this->_assignmentStore->getAssignmentByBatchStudentId($batchStudentId);
    if (!$assignment || $assignment->teacher_id !== $teacherId) {
      return ['allowed' => false, 'reason' => 'Sinh viên này không được phân công cho bạn hướng dẫn.'];
    }

    if (!$this->isWithinGradingDeadline($batchId)) {
      return ['allowed' => false, 'reason' => 'Đã hết thời hạn chấm/sửa điểm cho đợt thực tập này.'];
    }

    return ['allowed' => true, 'reason' => ''];
  }

  public function isWithinGradingDeadline(int $batchId): bool
  {
    $batch = $this->_batchStore->getById($batchId);
    if (!$batch || empty($batch['start_at']))
      return false;

    $now = new DateTime();
    $startAt = new DateTime($batch['start_at']);

    if ($now < $startAt)
      return false;

    if (empty($batch['end_at']))
      return true;

    $deadlineWeeks = (int) $this->_webSettingsService->getValue('internship_grading_deadline_weeks', 2);
    $endAt = new DateTime($batch['end_at']);
    $deadline = (clone $endAt)->modify("+{$deadlineWeeks} weeks");

    return $now <= $deadline;
  }

  public function getStudentGradingData(int $batchStudentId, int $teacherId): ?array
  {
    $studentInfo = $this->_batchStore->getStudentGradingDetail($batchStudentId);
    if (!$studentInfo)
      return null;

    $submissions = $this->_submissionStore->getLatestByBatchStudentGroupedByType($batchStudentId);
    $allSubmissions = $this->_submissionStore->getAllByBatchStudentId($batchStudentId);
    $grade = $this->_gradeStore->getByBatchStudentId($batchStudentId);

    $requiredTypes = ['internship_report', 'evaluation_form'];
    $missingDocs = [];
    foreach ($requiredTypes as $type) {
      if (!isset($submissions[$type])) {
        $missingDocs[] = $type;
      }
    }

    return [
      'student' => $studentInfo,
      'submissions' => $submissions,
      'all_submissions' => $allSubmissions,
      'grade' => $grade,
      'missing_docs' => $missingDocs,
      'can_grade' => empty($missingDocs)
    ];
  }
}
