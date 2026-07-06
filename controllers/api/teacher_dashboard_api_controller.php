<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Stores\InternshipSubmissionStore;
use App\Stores\InternshipAssignmentStore;
use App\Stores\TeacherStore;

class TeacherDashboardApiController extends Controller
{
  private InternshipSubmissionStore $_internshipSubmissionStore;
  private InternshipAssignmentStore $_internshipAssignmentStore;
  private TeacherStore $_teacherStore;

  public function __construct(
    InternshipSubmissionStore $internshipSubmissionStore,
    InternshipAssignmentStore $internshipAssignmentStore,
    TeacherStore $teacherStore
  ) {
    $this->_internshipSubmissionStore = $internshipSubmissionStore;
    $this->_internshipAssignmentStore = $internshipAssignmentStore;
    $this->_teacherStore = $teacherStore;
  }

  public function previewSubmission(Request $request, int $submissionId)
  {
    $authUser = $request->session()->authUser();
    if (!$authUser) {
      http_response_code(401);
      echo "Unauthorized";
      return;
    }

    if (($authUser['role'] ?? '') !== 'teacher') {
      http_response_code(403);
      echo "Forbidden";
      return;
    }

    $submission = $this->_internshipSubmissionStore->getById($submissionId);
    if (!$submission || empty($submission['file_path'])) {
      http_response_code(404);
      echo "File không tồn tại.";
      return;
    }

    $teacher = $this->_teacherStore->getByAccountId((int)$authUser['account_id']);
    $assignment = $this->_internshipAssignmentStore->getAssignmentByBatchStudentId((int)$submission['batch_student_id']);
    if (!$teacher || !$assignment || (int)$assignment->teacher_id !== (int)$teacher->id) {
      http_response_code(403);
      echo "Forbidden";
      return;
    }

    $filePath = BASE_PATH . '/storage/' . $submission['file_path'];
    if (!file_exists($filePath)) {
      http_response_code(404);
      echo "File không tồn tại trên server.";
      return;
    }

    $mimeType = $submission['mime_type'] ?? mime_content_type($filePath);
    if (!$mimeType) {
      $mimeType = 'application/octet-stream';
    }

    header("Content-Type: " . $mimeType);
    header("Content-Disposition: inline; filename=\"" . basename($submission['original_file_name']) . "\"");
    header("Content-Length: " . filesize($filePath));
    readfile($filePath);
    exit;
  }
}
