<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Stores\InternshipSubmissionStore;

class TeacherDashboardApiController extends Controller
{
  private InternshipSubmissionStore $_internshipSubmissionStore;

  public function __construct(
    InternshipSubmissionStore $internshipSubmissionStore
  ) {
    $this->_internshipSubmissionStore = $internshipSubmissionStore;
  }

  public function previewSubmission(Request $request, int $submissionId)
  {
    $authUser = $request->session()->authUser();
    if (!$authUser) {
      http_response_code(401);
      echo "Unauthorized";
      return;
    }

    $submission = $this->_internshipSubmissionStore->getById($submissionId);
    if (!$submission || empty($submission['file_path'])) {
      http_response_code(404);
      echo "File không tồn tại.";
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
