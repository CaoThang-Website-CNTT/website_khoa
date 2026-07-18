<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\RequestValidator;
use App\Services\{StudentService, ClassroomService, InternshipBatchService, InternshipAssignmentService, CompanyService, InternshipSubmissionService, ReferralLetterService, WebSettingsService, InternshipWeeklyReportService};
use App\Core\Files\UploadedFileHandler;
use App\Enums\BatchStatus;
use App\Models\InternshipBatch;
use Exception;

class StudentDashboardController extends Controller
{
  private StudentService $_studentService;
  private ClassroomService $_classroomService;
  private InternshipBatchService $_internshipBatchService;
  private InternshipAssignmentService $_internshipAssignmentService;
  private CompanyService $_companyService;
  private InternshipSubmissionService $_submissionService;
  private ReferralLetterService $_referralLetterService;
  private WebSettingsService $_webSettingsService;
  private InternshipWeeklyReportService $_weeklyReportService;

  public function __construct(
    StudentService $studentService,
    ClassroomService $classroomService,
    InternshipBatchService $internshipBatchService,
    InternshipAssignmentService $internshipAssignmentService,
    CompanyService $companyService,
    InternshipSubmissionService $submissionService,
    ReferralLetterService $referralLetterService,
    WebSettingsService $webSettingsService,
    InternshipWeeklyReportService $weeklyReportService
  ) {
    $this->_studentService = $studentService;
    $this->_classroomService = $classroomService;
    $this->_internshipBatchService = $internshipBatchService;
    $this->_internshipAssignmentService = $internshipAssignmentService;
    $this->_companyService = $companyService;
    $this->_submissionService = $submissionService;
    $this->_referralLetterService = $referralLetterService;
    $this->_webSettingsService = $webSettingsService;
    $this->_weeklyReportService = $weeklyReportService;
  }

  /**
   * Hiển thị trang tổng quan sinh viên
   * 
   * @param Request $request
   */
  public function index(Request $request)
  {
    // @techdebt: Cần triển khai Middleware để kiểm tra quyền truy cập thay vì kiểm tra thủ công
    $authUser = $request->session()->authUser();
    if (!$authUser) {
      return $this->redirect('/login');
    }

    $student = $this->_studentService->getStudentByAccountId($authUser['account_id']);
    if (!$student) {
      $request->session()->flashNotify('error', 'Không tìm thấy hồ sơ sinh viên.');
      return $this->redirect('/');
    }

    return $this->render('student/dashboard/index', [
      'student' => $student,
      'title' => 'Tổng quan sinh viên'
    ], layout: 'dashboard_layout');
  }

  /**
   * Cập nhật thông tin cá nhân sinh viên
   */
  public function updateProfile(Request $request)
  {
    $authUser = $request->session()->authUser();
    if (!$authUser) {
      return $this->redirect('/login');
    }

    $student = $this->_studentService->getStudentByAccountId($authUser['account_id']);
    if (!$student) {
      return $this->redirect('/');
    }

    $data = $request->sanitized();
    $validator = new RequestValidator();

    // Chỉ cho phép cập nhật một số thông tin cơ bản
    $rules = [
      'full_name' => ['required', 'max:255'],
      'dob' => ['required', 'date'],
      'gender' => ['required', 'in:male,female,other'],
      'phone' => ['required', 'phone', 'max:15'],
      'address' => ['required'],
      'birth_place' => ['required', 'max:255'],
      'national_id' => ['required', 'size:12'],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs();
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect('/student');
    }

    // Giữ nguyên các thông tin học tập
    $updateData = array_merge([
      'student_id' => $student->student_id,
      'classroom_id' => $student->classroom_id,
      'status' => $student->status,
      'notes' => $student->notes,
    ], $data);

    try {
      $this->_studentService->updateStudent($student->student_id, $updateData);
      $request->session()->flashNotify('success', 'Cập nhật thông tin cá nhân thành công!');
    } catch (Exception $e) {
      $request->session()->flashNotify('error', 'Có lỗi xảy ra: ' . $e->getMessage());
    }

    return $this->redirect('/student');
  }

  public function internshipRedirect(Request $request)
  {
    $authUser = $request->session()->authUser();
    if (!$authUser) return $this->redirect('/login');
    $student = $this->_studentService->getStudentByAccountId($authUser['account_id']);

    $dashboardData = $this->_internshipBatchService->getStudentDashboardData($student->id, null);

    return $this->render('student/dashboard/internship', [
      'student' => $student,
      'title' => 'Thông tin thực tập',
      'batches' => $dashboardData['batches'] ?? [],
      'current' => null
    ], layout: 'dashboard_layout');
  }

  private function checkOwnershipAndGetBatchStudentId(int $studentId, int $batchId): ?int
  {
    $dashboardData = $this->_internshipBatchService->getStudentDashboardData($studentId, $batchId);
    if ($dashboardData['current'] && $dashboardData['current']['id'] == $batchId) {
      return (int)$dashboardData['current']['batch_student_id'];
    }
    return null;
  }

  /**
   * Hiển thị thông tin thực tập của sinh viên
   * 
   * @param Request $request
   * @param int $batch_id
   */
  public function internship(Request $request, int $batch_id)
  {
    $authUser = $request->session()->authUser();
    if (!$authUser) {
      return $this->redirect('/login');
    }
    $student = $this->_studentService->getStudentByAccountId($authUser['account_id']);

    $dashboardData = $this->_internshipBatchService->getStudentDashboardData($student->id, $batch_id);

    if (!$dashboardData['current'] || $dashboardData['current']['id'] != $batch_id) {
      $request->session()->flashNotify('error', 'Bạn không thuộc đợt thực tập này hoặc đợt không tồn tại.');
      return $this->redirect('/student/internship');
    }

    // Tính toán hạn chót khai báo công ty
    $allowedWeeks = (int)$this->_webSettingsService->getValue('internship_company_declaration_weeks', 3);
    $companyDeadline = null;
    $canEditCompany = false;

    if ($dashboardData['current'] && $dashboardData['current']['start_at']) {
      $startAt = new \DateTime($dashboardData['current']['start_at']);
      $companyDeadlineDt = (clone $startAt)->modify("+{$allowedWeeks} weeks");
      $companyDeadline = $companyDeadlineDt->format('Y-m-d');
      $now = new \DateTime();
      $canEditCompany = ($now <= $companyDeadlineDt);
    }

    // Tính toán hạn nộp báo cáo
    $allowedDays = (int)$this->_webSettingsService->getValue('internship_report_submission_days', 7);
    $reportDeadline = null;
    $canSubmitReport = true;
    $cannotSubmitReason = 'Đã hết hạn nộp';

    if ($dashboardData['current']) {
      $batchModel = new InternshipBatch();
      $batchModel->status = $dashboardData['current']['status'] ?? 'draft';
      $batchModel->start_at = $dashboardData['current']['start_at'] ?? null;
      $batchModel->end_at = $dashboardData['current']['end_at'] ?? null;
      $effStatus = $batchModel->getEffectiveStatus();

      if ($effStatus === BatchStatus::UPCOMING) {
        $canSubmitReport = false;
        $cannotSubmitReason = 'Đợt chưa bắt đầu';
      } elseif ($effStatus === BatchStatus::CLOSED) {
        $canSubmitReport = false;
        $cannotSubmitReason = 'Đợt đã đóng';
      }

      if ($dashboardData['current']['end_at']) {
        $endAtDt = new \DateTime($dashboardData['current']['end_at']);
        $reportDeadlineDt = (clone $endAtDt)->modify("+{$allowedDays} days");
        $reportDeadline = $reportDeadlineDt->format('Y-m-d');

        $now = new \DateTime();
        if ($now > $reportDeadlineDt) {
          $canSubmitReport = false;
          $cannotSubmitReason = 'Đã hết hạn nộp';
        }
      }
    }

    // Lấy 2 giấy giới thiệu mới nhất
    $allLetters = $this->_referralLetterService->getLettersWithCompanyByBatchStudentId((int)$dashboardData['current']['batch_student_id']);
    $recentReferralLetters = array_slice($allLetters, 0, 2);

    // Lấy thông tin tóm tắt về báo cáo tuần
    $weeklySummary = null;
    if ($dashboardData['current'] && $dashboardData['current']['start_at'] && $dashboardData['current']['end_at']) {
      $weeklySummary = $this->_weeklyReportService->getStudentWeeklySummary(
        (int)$dashboardData['current']['batch_student_id'],
        $dashboardData['current']['start_at'],
        $dashboardData['current']['end_at']
      );
    }

    // Chuẩn hóa trạng thái hành trình để view chỉ chịu trách nhiệm trình bày.
    $now = new \DateTime();
    $startAt = !empty($dashboardData['current']['start_at']) ? new \DateTime($dashboardData['current']['start_at']) : null;
    $endAt = !empty($dashboardData['current']['end_at']) ? new \DateTime($dashboardData['current']['end_at']) : null;
    
    $isGradesPublished = !empty($dashboardData['current']['grades_published_at']);
    $rawGrade = $dashboardData['grade'] ?? null;
    $rawIsGradeLocked = !empty($rawGrade['grade_lock_at']);
    $rawHasGrade = $rawGrade && isset($rawGrade['final_score']);
    
    // Chỉ đưa $grade ra view nếu đã công bố
    if (!$isGradesPublished) {
      $dashboardData['grade'] = null; 
      $grade = null;
    } else {
      $grade = $rawGrade;
    }

    $isGradeLocked = !empty($grade['grade_lock_at']);
    $hasGrade = $grade && isset($grade['final_score']);

    if ($rawIsGradeLocked || ($endAt && $now > $endAt)) {
      $activePhase = 2;
    } elseif ($startAt && $now >= $startAt) {
      $activePhase = 1;
    } else {
      $activePhase = 0;
    }

    $phaseStates = [];
    for ($phaseIndex = 0; $phaseIndex < 3; $phaseIndex++) {
      $phaseStates[] = $phaseIndex < $activePhase ? 'passed' : ($phaseIndex === $activePhase ? 'active' : 'upcoming');
    }

    $submittedTypes = array_keys($dashboardData['submissions_by_type'] ?? []);
    $requiredDocumentTypes = ['internship_report', 'evaluation_form', 'company_survey'];
    $submittedDocumentCount = count(array_intersect($requiredDocumentTypes, $submittedTypes));

    if ($activePhase === 0) {
      $nextAction = empty($allLetters)
        ? 'Đăng ký giấy giới thiệu thực tập và kiểm tra thông tin giảng viên hướng dẫn.'
        : 'Theo dõi trạng thái giấy giới thiệu trước khi bắt đầu thực tập.';
    } elseif ($activePhase === 1) {
      if (empty($dashboardData['current']['company_name']) && $canEditCompany) {
        $nextAction = 'Khai báo công ty thực tập trong thời hạn cho phép.';
      } elseif ($weeklySummary && !in_array($weeklySummary['current_week_status'], ['submitted', 'exempt', 'not_started', 'ended'], true)) {
        $nextAction = 'Cập nhật báo cáo của tuần hiện tại.';
      } elseif ($submittedDocumentCount < count($requiredDocumentTypes)) {
        $nextAction = 'Hoàn thiện báo cáo, phiếu đánh giá và phiếu khảo sát doanh nghiệp.';
      } else {
        $nextAction = 'Tiếp tục theo dõi tiến độ và hoàn thành các báo cáo còn lại.';
      }
    } else {
      if ($isGradesPublished) {
        $nextAction = $isGradeLocked
          ? 'Điểm đã được công bố. Kiểm tra kết quả và nhận xét của giảng viên hướng dẫn.'
          : ($hasGrade ? 'Giảng viên đang hoàn tất và chốt điểm.' : 'Chờ giảng viên hướng dẫn chấm điểm.');
        $gradeState = $isGradeLocked ? 'locked' : ($hasGrade ? 'graded' : 'waiting');
      } else {
        if ($rawIsGradeLocked) {
          $nextAction = 'Điểm đã được giảng viên chốt. Chờ Khoa công bố điểm chính thức.';
          $gradeState = 'graded';
        } elseif ($rawHasGrade) {
          $nextAction = 'Giảng viên đang chấm điểm. Chờ Khoa công bố điểm chính thức.';
          $gradeState = 'graded';
        } else {
          $nextAction = 'Chờ giảng viên chấm điểm và Khoa công bố điểm.';
          $gradeState = 'waiting';
        }
      }
    }

    $journey = [
      'active_phase' => $activePhase,
      'phase_states' => $phaseStates,
      'next_action' => $nextAction,
      'grade_state' => $gradeState ?? 'waiting',
      'submitted_document_count' => $submittedDocumentCount,
      'required_document_count' => count($requiredDocumentTypes)
    ];

    return $this->render('student/dashboard/internship', array_merge($dashboardData, [
      'student' => $student,
      'title' => 'Thông tin thực tập',
      'can_edit_company' => $canEditCompany,
      'company_deadline' => $companyDeadline,
      'report_deadline' => $reportDeadline,
      'can_submit_report' => $canSubmitReport,
      'cannot_submit_reason' => $cannotSubmitReason,
      'max_file_size_mb' => (int)$this->_webSettingsService->getValue('internship_report_max_size_mb', 50),
      'company_warning_days' => (int)$this->_webSettingsService->getValue('internship_company_warning_days', 3),
      'report_warning_days' => (int)$this->_webSettingsService->getValue('internship_report_warning_days', 3),
      'recent_referral_letters' => $recentReferralLetters,
      'total_referral_letters' => count($allLetters),
      'weekly_summary' => $weeklySummary,
      'journey' => $journey
    ]), layout: 'dashboard_layout');
  }

  public function updateCompany(Request $request, int $batch_id)
  {
    $authUser = $request->session()->authUser();
    if (!$authUser) return $this->redirect('/login');
    $student = $this->_studentService->getStudentByAccountId($authUser['account_id']);

    $batchStudentId = $this->checkOwnershipAndGetBatchStudentId($student->id, $batch_id);
    if (!$batchStudentId) {
      $request->session()->flashNotify('error', 'Bạn không thuộc đợt thực tập này.');
      return $this->redirect('/student/internship');
    }

    // Kiểm tra hạn chót khai báo/chỉnh sửa công ty
    $batch = $this->_internshipBatchService->getBatchById($batch_id);
    if ($batch && $batch['start_at']) {
      $allowedWeeks = (int)$this->_webSettingsService->getValue('internship_company_declaration_weeks', 3);
      $deadlineDt = (new \DateTime($batch['start_at']))->modify("+{$allowedWeeks} weeks");
      if (new \DateTime() > $deadlineDt) {
        $request->session()->flashNotify('error', 'Đã hết thời hạn khai báo thông tin công ty.');
        return $this->redirect("/student/internship/{$batch_id}");
      }
    }

    $data = $request->sanitized();
    $isManual = isset($data['is_manual']) && $data['is_manual'] == 1;

    $validator = new RequestValidator();

    $rules = [
      'tax_code' => $isManual ? ['nullable'] : ['required'],
      'name' => ['required', 'max:255'],
      'address' => ['required'],
      'position' => ['required', 'max:255'],
      'company_mentor_name' => ['required', 'max:255'],
      'company_mentor_phone' => ['required', 'max:20'],
      'company_mentor_email' => ['required', 'email', 'max:255'],
      'internship_start_date' => ['required', 'date'],
      'internship_end_date' => ['required', 'date'],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect("/student/internship/{$batch_id}");
    }

    try {
      if ($isManual && empty($data['tax_code'])) {
        $companyId = $this->_companyService->createManual([
          'tax_code' => null,
          'name' => $data['name'],
          'address' => $data['address'],
        ]);
      } else {
        $companyId = $this->_companyService->upsertFromApi([
          'tax_code' => $data['tax_code'],
          'name' => $data['name'],
          'address' => $data['address'],
        ]);
      }

      $success = $this->_internshipBatchService->updateStudentInternshipInfo($batchStudentId, [
        'company_id' => $companyId,
        'position' => $data['position'],
        'company_mentor_name' => trim($data['company_mentor_name']),
        'company_mentor_phone' => trim($data['company_mentor_phone']),
        'company_mentor_email' => trim($data['company_mentor_email']),
        'internship_start_date' => $data['internship_start_date'],
        'internship_end_date' => $data['internship_end_date'],
      ]);

      if ($success) {
        $request->session()->flashNotify('success', 'Lưu thông tin công ty thành công!');
      } else {
        $request->session()->flashNotify('error', 'Lỗi khi lưu thông tin công ty!');
      }
    } catch (Exception $e) {
      $request->session()->flashNotify('error', 'Lỗi: ' . $e->getMessage());
    }

    return $this->redirect("/student/internship/{$batch_id}");
  }

  public function uploadSubmission(Request $request, int $batch_id)
  {
    $authUser = $request->session()->authUser();
    if (!$authUser) return $this->redirect('/login');
    $student = $this->_studentService->getStudentByAccountId($authUser['account_id']);

    $batchStudentId = $this->checkOwnershipAndGetBatchStudentId($student->id, $batch_id);
    if (!$batchStudentId) {
      $request->session()->flashNotify('error', 'Bạn không thuộc đợt thực tập này.');
      return $this->redirect('/student/internship');
    }

    try {
      $batch = $this->_internshipBatchService->getBatchById($batch_id);

      if ($batch) {
        $batchModel = new InternshipBatch();
        $batchModel->status = $batch['status'] ?? 'draft';
        $batchModel->start_at = $batch['start_at'] ?? null;
        $batchModel->end_at = $batch['end_at'] ?? null;
        $effStatus = $batchModel->getEffectiveStatus();

        if ($effStatus === BatchStatus::UPCOMING) {
          throw new Exception('Đợt thực tập chưa bắt đầu, không thể nộp tài liệu.');
        }
        if ($effStatus === BatchStatus::CLOSED) {
          throw new Exception('Đợt thực tập đã đóng, không thể nộp tài liệu.');
        }

        if ($batch['end_at']) {
          $allowedDays = (int)$this->_webSettingsService->getValue('internship_report_submission_days', 7);
          $deadlineDt = (new \DateTime($batch['end_at']))->modify("+{$allowedDays} days");
          if (new \DateTime() > $deadlineDt) {
            throw new Exception('Đã hết thời hạn nộp tài liệu thực tập.');
          }
        }
      }

      $maxSizeMb = (int)$this->_webSettingsService->getValue('internship_report_max_size_mb', 10);
      $maxSizeBytes = $maxSizeMb * 1024 * 1024;

      $fileHandler = new UploadedFileHandler();
      $allowedFiles = [
        'file_internship_report' => 'internship_report',
        'file_evaluation_form' => 'evaluation_form',
        'file_company_survey' => 'company_survey',
        'file_related_photo' => 'related_photo'
      ];

      $hasFile = false;

      $subDir = 'internship_submissions/' . date('Y/m');
      $uploadDir = BASE_PATH . '/storage/' . $subDir . '/';

      foreach ($allowedFiles as $inputName => $docType) {
        $fileData = $request->file($inputName);
        if ($fileData) {
          $filesToProcess = isset($fileData['name']) ? [$fileData] : $fileData;

          if ($docType === 'related_photo' && count($filesToProcess) > 5) {
            throw new Exception('Chỉ được phép chọn tối đa 5 hình ảnh liên quan.');
          }

          foreach ($filesToProcess as $singleFileData) {
            if ($singleFileData['error'] !== UPLOAD_ERR_NO_FILE) {
              $hasFile = true;
              $uploadedFile = $fileHandler->processUpload($singleFileData);

              if (!$uploadedFile) {
                throw new Exception("Lỗi tải lên file cho loại tài liệu: {$docType}");
              }
              if ($uploadedFile->fileSize > $maxSizeBytes) {
                throw new Exception("Dung lượng file vượt quá giới hạn cho phép ({$maxSizeMb}MB) ở tài liệu: {$docType}");
              }

              if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
              }

              $fileName = bin2hex(random_bytes(16)) . '.' . $uploadedFile->extension;
              $destPath = $uploadDir . $fileName;

              if (!move_uploaded_file($uploadedFile->tmpPath, $destPath)) {
                throw new Exception("Không thể lưu file {$docType} vào máy chủ.");
              }

              $mimeType = mime_content_type($destPath) ?: 'application/octet-stream';

              $this->_submissionService->createTypedSubmission($batchStudentId, $docType, [
                'storage_mode' => 'file',
                'original_file_name' => $uploadedFile->originalName,
                'mime_type' => $mimeType,
                'file_path' => $subDir . '/' . $fileName,
              ]);
            }
          }
        }
      }

      if (!$hasFile) {
        throw new Exception("Vui lòng chọn ít nhất 1 file để nộp.");
      }

      $request->session()->flashNotify('success', 'Nộp tài liệu thành công!');
    } catch (Exception $e) {
      $request->session()->flashNotify('error', 'Lỗi: ' . $e->getMessage());
    }

    return $this->redirect("/student/internship/{$batch_id}");
  }

  public function createReferralLetter(Request $request, int $batch_id)
  {
    $authUser = $request->session()->authUser();
    if (!$authUser) return $this->redirect('/login');
    $student = $this->_studentService->getStudentByAccountId($authUser['account_id']);

    $batchStudentId = $this->checkOwnershipAndGetBatchStudentId($student->id, $batch_id);
    if (!$batchStudentId) {
      $request->session()->flashNotify('error', 'Bạn không thuộc đợt thực tập này.');
      return $this->redirect('/student/internship');
    }

    $batch = $this->_internshipBatchService->getBatchById($batch_id);
    if (!$batch || in_array($batch['status'], [BatchStatus::DRAFT, BatchStatus::CLOSED])) {
      $request->session()->flashNotify('error', 'Chỉ có thể đăng ký giấy giới thiệu cho đợt thực tập đang mở.');
      return $this->redirect("/student/internship/{$batch_id}/referral_letters");
    }

    $majorName = 'Công nghệ thông tin';
    if (!empty($student->classroom_id)) {
      $major = $this->_classroomService->getMajorByClassroomId($student->classroom_id);
      if ($major) {
        $majorName = $major->full_name;
      }
    }

    $allMajors = $this->_classroomService->getAllMajors();

    return $this->render('student/dashboard/referral_letters_create', [
      'current' => $batch,
      'student' => $student,
      'majorName' => $majorName,
      'batch_student_id' => $batchStudentId,
      'batch_students' => $this->_internshipBatchService->getBatchStudents($batch_id),
      'allMajors' => $allMajors
    ], layout: 'dashboard_layout');
  }

  public function requestReferralLetter(Request $request, int $batch_id)
  {
    $authUser = $request->session()->authUser();
    if (!$authUser) return $this->redirect('/login');
    $student = $this->_studentService->getStudentByAccountId($authUser['account_id']);

    $batchStudentId = $this->checkOwnershipAndGetBatchStudentId($student->id, $batch_id);
    if (!$batchStudentId) {
      $request->session()->flashNotify('error', 'Bạn không thuộc đợt thực tập này.');
      return $this->redirect('/student/internship');
    }

    $batch = $this->_internshipBatchService->getBatchById($batch_id);
    if (!$batch || in_array($batch['status'], [BatchStatus::DRAFT, BatchStatus::CLOSED])) {
      $request->session()->flashNotify('error', 'Chỉ có thể đăng ký giấy giới thiệu cho đợt thực tập đang mở.');
      return $this->redirect("/student/internship/{$batch_id}/referral_letters");
    }

    $data = $request->sanitized();
    $isManual = isset($data['is_manual']) && $data['is_manual'] == 1;

    $validator = new RequestValidator();

    $rules = [
      'tax_code' => $isManual ? ['nullable'] : ['required'],
      'name' => ['required', 'max:255'],
      'address' => ['required']
    ];

    if (!$validator->validate($data, $rules)) {
      $request->session()->flashErrors($validator->getErrors());
      $request->session()->flashErrors($data);
      return $this->redirect("/student/internship/{$batch_id}/referral_letters/create");
    }

    try {
      if ($isManual && empty($data['tax_code'])) {
        $companyId = $this->_companyService->createManual([
          'tax_code' => null,
          'name' => $data['name'],
          'address' => $data['address'],
        ]);
      } else {
        $companyId = $this->_companyService->upsertFromApi([
          'tax_code' => $data['tax_code'],
          'name' => $data['name'],
          'address' => $data['address'],
        ]);
      }

      // Fetch teacher_id from internship_assignments
      $assignment = $this->_internshipAssignmentService->getAssignmentByBatchStudentId($batchStudentId);
      $teacherId = $assignment ? $assignment->teacher_id : null;

      $letterData = [
        'batch_student_id' => $batchStudentId,
        'company_id' => $companyId,
        'teacher_id' => $teacherId,
      ];

      // Parse students from request
      $students = [];
      // Request might send arrays: student_name[], student_major[], student_dob[], student_address[]
      if (!empty($data['student_name']) && is_array($data['student_name'])) {
        if (count($data['student_name']) > 15) {
          $request->session()->flashNotify('error', 'Một nhóm đăng ký không được vượt quá 15 sinh viên.');
          return $this->redirect("/student/internship/{$batch_id}/referral_letters/create");
        }
        foreach ($data['student_name'] as $index => $name) {
          if (!trim($name)) continue;
          $students[] = [
            'full_name' => trim($name),
            'training_program' => $data['student_major'][$index] ?? null,
            'dob' => $data['student_dob'][$index] ?? null,
            'address' => $data['student_address'][$index] ?? null,
            // If it's the primary student (e.g. index 0), we can set their IDs
            'student_id' => ($index === 0) ? $student->id : null,
            'batch_student_id' => (int)($data['student_batch_student_id'][$index] ?? 0),
            'sort_order' => $index,
          ];
        }
      } else {
        // Fallback to single student
        // Find major
        $majorName = null;
        if ($student->classroom_id) {
          $major = $this->_classroomService->getMajorByClassroomId($student->classroom_id);
          if ($major) {
            $majorName = $major->full_name;
          }
        }
        $students[] = [
          'full_name' => $student->full_name,
          'training_program' => $majorName ?: 'Công nghệ thông tin',
          'dob' => $student->dob,
          'address' => $student->address,
          'student_id' => $student->id,
          'batch_student_id' => $batchStudentId,
          'sort_order' => 0,
        ];
      }

      $success = $this->_referralLetterService->create($letterData, $students);

      if ($success) {
        $request->session()->flashNotify('success', 'Đăng ký nhận giấy giới thiệu thành công!');
      } else {
        $request->session()->flashNotify('error', 'Lỗi trong lúc đăng ký giấy giới thiệu!');
      }
    } catch (Exception $e) {
      $request->session()->flashNotify('error', 'Lỗi: ' . $e->getMessage());
    }

    return $this->redirect("/student/internship/{$batch_id}/referral_letters");
  }

  public function referralLetters(Request $request, int $batch_id)
  {
    $authUser = $request->session()->authUser();
    if (!$authUser) return $this->redirect('/login');
    $student = $this->_studentService->getStudentByAccountId($authUser['account_id']);

    $dashboardData = $this->_internshipBatchService->getStudentDashboardData($student->id, $batch_id);
    if (!$dashboardData['current'] || $dashboardData['current']['id'] != $batch_id) {
      $request->session()->flashNotify('error', 'Bạn không thuộc đợt thực tập này.');
      return $this->redirect('/student/internship');
    }

    $referralLetters = $this->_referralLetterService->getLettersWithCompanyByBatchStudentId((int)$dashboardData['current']['batch_student_id']);
    $total_referral_letters = count($referralLetters);
    $recent_referral_letters = array_slice($referralLetters, 0, 2);

    $majorName = 'Công nghệ thông tin';
    if (!empty($student->classroom_id)) {
      $major = $this->_classroomService->getMajorByClassroomId($student->classroom_id);
      if ($major) {
        $majorName = $major->full_name;
      }
    }

    return $this->render('student/dashboard/referral_letters', array_merge($dashboardData, [
      'student' => $student,
      'majorName' => $majorName,
      'title' => 'Quản lý Giấy giới thiệu',
      'referralLetters' => $referralLetters,
      'total_referral_letters' => $total_referral_letters,
      'recent_referral_letters' => $recent_referral_letters,
      'canRequestLetter' => $dashboardData['current']['status'] === BatchStatus::PUBLISHED
    ]), layout: 'dashboard_layout');
  }

  public function showReferralLetter(Request $request, int $batch_id, int $letter_id)
  {
    $authUser = $request->session()->authUser();
    if (!$authUser) return $this->redirect('/login');
    $student = $this->_studentService->getStudentByAccountId($authUser['account_id']);

    $dashboardData = $this->_internshipBatchService->getStudentDashboardData($student->id, $batch_id);
    if (!$dashboardData['current'] || $dashboardData['current']['id'] != $batch_id) {
      $request->session()->flashNotify('error', 'Bạn không thuộc đợt thực tập này.');
      return $this->redirect('/student/internship');
    }

    $batchStudentId = (int)$dashboardData['current']['batch_student_id'];

    $letterResult = $this->_referralLetterService->getWithStudentsByLetterId($letter_id);
    if (!$letterResult || $letterResult['batch_student_id'] != $batchStudentId) {
      $request->session()->flashNotify('error', 'Giấy giới thiệu không tồn tại hoặc bạn không có quyền xem.');
      return $this->redirect("/student/internship/{$batch_id}/referral_letters");
    }

    return $this->render('student/dashboard/referral_letters_show', array_merge($dashboardData, [
      'student' => $student,
      'title' => 'Chi tiết Giấy giới thiệu',
      'letter' => $letterResult,
      'students' => $letterResult['students'] ?? []
    ]), layout: 'dashboard_layout');
  }

  public function cancelReferralLetter(Request $request, int $batch_id, int $letter_id)
  {
    $authUser = $request->session()->authUser();
    if (!$authUser) return $this->redirect('/login');
    $student = $this->_studentService->getStudentByAccountId($authUser['account_id']);

    $batchStudentId = $this->checkOwnershipAndGetBatchStudentId($student->id, $batch_id);
    if (!$batchStudentId) {
      $request->session()->flashNotify('error', 'Bạn không thuộc đợt thực tập này.');
      return $this->redirect('/student/internship');
    }

    // Verify ownership of the letter
    $letter = $this->_referralLetterService->getById($letter_id);
    if (!$letter || $letter->batch_student_id !== $batchStudentId) {
      $request->session()->flashNotify('error', 'Giấy giới thiệu không tồn tại hoặc bạn không có quyền hủy.');
      return $this->redirect("/student/internship/{$batch_id}/referral_letters");
    }

    $reason = $request->input('cancel_reason') ?: '';

    try {
      if ($this->_referralLetterService->cancel($letter_id, $reason, (int)$authUser['account_id'])) {
        $request->session()->flashNotify('success', 'Đã hủy đăng ký giấy giới thiệu.');
      } else {
        $request->session()->flashNotify('error', 'Có lỗi xảy ra khi hủy giấy giới thiệu.');
      }
    } catch (Exception $e) {
      $request->session()->flashNotify('error', $e->getMessage());
    }

    return $this->redirect("/student/internship/{$batch_id}/referral_letters");
  }


  /**
   * Hiển thị thông tin đồ án tốt nghiệp
   */
  public function graduation(Request $request)
  {
    // @techdebt: Kiểm tra quyền truy cập
    $authUser = $request->session()->authUser();
    $student = $this->_studentService->getStudentByAccountId($authUser['account_id']);

    //TODO: Lấy thông tin đồ án tốt nghiệp từ database
    return $this->render('student/dashboard/graduation', [
      'student' => $student,
      'title' => 'Đồ án tốt nghiệp'
    ], layout: 'dashboard_layout');
  }

  public function weeklyReports(Request $request, $batch_id)
  {
    $authUser = $request->session()->authUser();
    if (!$authUser) {
      return $this->redirect('/login');
    }

    $student = $this->_studentService->getStudentByAccountId($authUser['account_id']);
    if (!$student) {
      return $this->redirect('/');
    }

    $dashboardData = $this->_internshipBatchService->getStudentDashboardData($student->id, $batch_id);
    if (!$dashboardData['current']) {
      $request->session()->flashNotify('error', 'Đợt thực tập không tồn tại hoặc bạn không thuộc đợt này.');
      return $this->redirect('/student/internship');
    }

    $startAt = $dashboardData['current']['start_at'];
    $nowStr = (new \DateTime())->format('Y-m-d');
    if ($startAt) {
      $startDt = new \DateTime($startAt);
      $dayOfWeek = (int)$startDt->format('N');
      if ($dayOfWeek > 1) {
        $startDt->modify('-' . ($dayOfWeek - 1) . ' days');
      }
      if ($nowStr < $startDt->format('Y-m-d')) {
        $request->session()->flashNotify('error', 'Chưa đến thời gian nộp báo cáo tuần cho đợt này.');
        return $this->redirect("/student/internship/{$batch_id}");
      }
    }

    $batchStudentId = (int)$dashboardData['current']['batch_student_id'];
    $startAt = $dashboardData['current']['start_at'];
    $endAt = $dashboardData['current']['end_at'];

    $batchModel = new InternshipBatch();
    $batchModel->status = $dashboardData['current']['status'];
    $batchModel->start_at = $startAt;
    $batchModel->end_at = $endAt;
    $effStatus = $batchModel->getEffectiveStatus();

    $lateBufferDays = (int) $this->_webSettingsService->getValue('internship_weekly_report_late_days', 7);

    $weeksData = $this->_weeklyReportService->getStudentWeeklyData($batchStudentId, $startAt, $endAt);

    return $this->render('student/dashboard/weekly_reports', array_merge($dashboardData, [
      'student' => $student,
      'title' => 'Báo cáo hàng tuần',
      'weeks_data' => $weeksData,
      'batch_student_id' => $batchStudentId,
      'effStatus' => $effStatus,
      'lateBufferDays' => $lateBufferDays
    ]), layout: 'dashboard_layout');
  }

  public function submitWeeklyReport(Request $request, $batch_id)
  {
    $authUser = $request->session()->authUser();
    if (!$authUser) {
      return $this->redirect('/login');
    }

    $student = $this->_studentService->getStudentByAccountId($authUser['account_id']);
    $dashboardData = $this->_internshipBatchService->getStudentDashboardData($student->id, $batch_id);

    if (!$dashboardData['current']) {
      $request->session()->flashNotify('error', 'Lỗi phân quyền.');
      return $this->redirect('/student/internship');
    }

    $startAt = $dashboardData['current']['start_at'];
    $nowStr = (new \DateTime())->format('Y-m-d');
    if ($startAt) {
      $startDt = new \DateTime($startAt);
      $dayOfWeek = (int)$startDt->format('N');
      if ($dayOfWeek > 1) {
        $startDt->modify('-' . ($dayOfWeek - 1) . ' days');
      }
      if ($nowStr < $startDt->format('Y-m-d')) {
        $request->session()->flashNotify('error', 'Chưa đến thời gian nộp báo cáo tuần cho đợt này.');
        return $this->redirect("/student/internship/{$batch_id}");
      }
    }

    $batchStudentId = (int)$dashboardData['current']['batch_student_id'];
    $startAt = $dashboardData['current']['start_at'];
    $endAt = $dashboardData['current']['end_at'];

    $batchModel = new InternshipBatch();
    $batchModel->status = $dashboardData['current']['status'] ?? 'draft';
    $batchModel->start_at = $startAt;
    $batchModel->end_at = $endAt;
    $effStatus = $batchModel->getEffectiveStatus();

    if ($effStatus === BatchStatus::CLOSED) {
      $request->session()->flashNotify('error', 'Đợt thực tập đã đóng, không thể nộp báo cáo tuần.');
      return $this->redirect("/student/internship/{$batch_id}/weekly_reports");
    }

    $sanitized = $request->sanitized();
    $weekNumber = (int)($sanitized['week_number'] ?? 0);
    $content = $sanitized['content'] ?? null;
    $isExempt = (bool)($sanitized['is_exempt'] ?? false);
    $noActivityReason = $sanitized['no_activity_reason'] ?? null;
    $noActivityNote = $sanitized['no_activity_note'] ?? null;

    $maxImages = (int)$this->_webSettingsService->getValue('internship_weekly_report_max_images', 5);
    $maxSizeMb = (float)$this->_webSettingsService->getValue('internship_weekly_report_image_max_size_mb', 5);
    $maxSizeBytes = $maxSizeMb * 1024 * 1024;

    try {
      $imagesData = [];
      if (!$isExempt && isset($_FILES['images'])) {
        $files = $_FILES['images'];
        $fileCount = is_array($files['name']) ? count(array_filter($files['name'])) : (empty($files['name']) ? 0 : 1);

        if ($fileCount > $maxImages) {
          throw new Exception("Chỉ được phép tải lên tối đa {$maxImages} ảnh.");
        }

        $subDir = 'weekly_reports/' . date('Y/m');
        $uploadDir = BASE_PATH . '/storage/' . $subDir . '/';

        for ($i = 0; $i < $fileCount; $i++) {
          if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) continue;

          if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            throw new Exception("Lỗi khi tải ảnh: " . $files['name'][$i]);
          }

          if ($files['size'][$i] > $maxSizeBytes) {
            throw new Exception("Dung lượng ảnh vượt quá giới hạn ({$maxSizeMb}MB): " . $files['name'][$i]);
          }

          $mime = mime_content_type($files['tmp_name'][$i]);
          if (strpos($mime, 'image/') !== 0) {
            throw new Exception("Chỉ hỗ trợ file hình ảnh: " . $files['name'][$i]);
          }

          if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
          }

          // Xử lý nén ảnh
          $tmpName = $files['tmp_name'][$i];
          $imageString = file_get_contents($tmpName);
          $sourceImage = @imagecreatefromstring($imageString);

          if (!$sourceImage) {
            throw new Exception("File ảnh bị lỗi hoặc định dạng không được hỗ trợ: " . $files['name'][$i]);
          }

          $width = imagesx($sourceImage);
          $height = imagesy($sourceImage);
          $maxWidth = 1280;
          $maxHeight = 1280;

          $newWidth = $width;
          $newHeight = $height;

          if ($width > $height) {
            if ($width > $maxWidth) {
              $newHeight = (int)floor($height * ($maxWidth / $width));
              $newWidth = $maxWidth;
            }
          } else {
            if ($height > $maxHeight) {
              $newWidth = (int)floor($width * ($maxHeight / $height));
              $newHeight = $maxHeight;
            }
          }

          $targetImage = imagecreatetruecolor($newWidth, $newHeight);

          // Giữ nền trong suốt cho ảnh đầu vào là PNG/WebP (nếu có)
          imagealphablending($targetImage, false);
          imagesavealpha($targetImage, true);
          $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
          imagefilledrectangle($targetImage, 0, 0, $newWidth, $newHeight, $transparent);

          imagecopyresampled($targetImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

          $fileName = bin2hex(random_bytes(16)) . '.webp';
          $destPath = $uploadDir . $fileName;

          // Lưu ảnh với định dạng WebP, chất lượng 75%
          if (!imagewebp($targetImage, $destPath, 75)) {
            throw new Exception("Không thể xử lý và lưu file ảnh vào máy chủ.");
          }

          $finalFileSize = filesize($destPath);

          $imagesData[] = [
            'original_file_name' => preg_replace('/\.[^.]+$/', '.webp', $files['name'][$i]),
            'mime_type' => 'image/webp',
            'file_path' => $subDir . '/' . $fileName,
            'file_size' => $finalFileSize
          ];
        }
      }

      $this->_weeklyReportService->submitWeeklyReport(
        $batchStudentId,
        $weekNumber,
        $content,
        $isExempt,
        $noActivityReason,
        $noActivityNote,
        $imagesData,
        $startAt,
        $endAt
      );

      $request->session()->flashNotify('success', 'Đã lưu báo cáo tuần.');
    } catch (Exception $e) {
      $request->session()->flashNotify('error', $e->getMessage());
    }

    return $this->redirect("/student/internship/{$batch_id}/weekly_reports");
  }
}
