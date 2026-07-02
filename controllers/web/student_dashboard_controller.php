<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\RequestValidator;
use App\Services\{StudentService, ClassroomService, InternshipBatchService, InternshipAssignmentService, CompanyService, InternshipSubmissionService, ReferralLetterService, WebSettingsService, InternshipWeeklyReportService};
use App\Core\Files\UploadedFileHandler;
use App\Enums\BatchStatus;
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

    $data = $request->all();
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

    if ($dashboardData['current'] && $dashboardData['current']['end_at']) {
      $endAtDt = new \DateTime($dashboardData['current']['end_at']);
      $reportDeadlineDt = (clone $endAtDt)->modify("+{$allowedDays} days");
      $reportDeadline = $reportDeadlineDt->format('Y-m-d');
      $now = new \DateTime();
      $canSubmitReport = ($now <= $reportDeadlineDt);
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

    return $this->render('student/dashboard/internship', array_merge($dashboardData, [
      'student' => $student,
      'title' => 'Thông tin thực tập',
      'can_edit_company' => $canEditCompany,
      'company_deadline' => $companyDeadline,
      'report_deadline' => $reportDeadline,
      'can_submit_report' => $canSubmitReport,
      'max_file_size_mb' => (int)$this->_webSettingsService->getValue('internship_report_max_size_mb', 50),
      'company_warning_days' => (int)$this->_webSettingsService->getValue('internship_company_warning_days', 3),
      'report_warning_days' => (int)$this->_webSettingsService->getValue('internship_report_warning_days', 3),
      'recent_referral_letters' => $recentReferralLetters,
      'total_referral_letters' => count($allLetters),
      'weekly_summary' => $weeklySummary
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

    $data = $request->all();
    $isManual = isset($data['is_manual']) && $data['is_manual'] == 1;

    $validator = new RequestValidator();

    $rules = [
      'tax_code' => $isManual ? ['nullable'] : ['required'],
      'name' => ['required', 'max:255'],
      'address' => ['required'],
      'position' => ['required', 'max:255'],
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
      // Server-side validation: Kiểm tra hạn chót nộp báo cáo
      $batch = $this->_internshipBatchService->getBatchById($batch_id);
      if ($batch && $batch['end_at']) {
        $allowedDays = (int)$this->_webSettingsService->getValue('internship_report_submission_days', 7);
        $deadlineDt = (new \DateTime($batch['end_at']))->modify("+{$allowedDays} days");
        if (new \DateTime() > $deadlineDt) {
          throw new Exception('Đã hết thời hạn nộp tài liệu thực tập.');
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
        if ($fileData && $fileData['error'] !== UPLOAD_ERR_NO_FILE) {
          $hasFile = true;
          $uploadedFile = $fileHandler->processUpload($fileData);

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

    return $this->render('student/dashboard/referral_letters_create', [
      'current' => $batch,
      'student' => $student,
      'majorName' => $majorName,
      'batch_student_id' => $batchStudentId,
      'batch_students' => $this->_internshipBatchService->getBatchStudents($batch_id)
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

    $data = $request->all();
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

    $batchStudentId = (int)$dashboardData['current']['batch_student_id'];
    $startAt = $dashboardData['current']['start_at'];
    $endAt = $dashboardData['current']['end_at'];

    $weeksData = $this->_weeklyReportService->getStudentWeeklyData($batchStudentId, $startAt, $endAt);

    return $this->render('student/dashboard/weekly_reports', array_merge($dashboardData, [
      'student' => $student,
      'title' => 'Báo cáo hàng tuần',
      'weeks_data' => $weeksData,
      'batch_student_id' => $batchStudentId
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

    $batchStudentId = (int)$dashboardData['current']['batch_student_id'];
    $startAt = $dashboardData['current']['start_at'];
    $endAt = $dashboardData['current']['end_at'];

    $weekNumber = (int)$request->input('week_number');
    $content = $request->input('content');
    $isExempt = (bool)$request->input('is_exempt');

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

          $ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
          $fileName = bin2hex(random_bytes(16)) . '.' . $ext;
          $destPath = $uploadDir . $fileName;

          if (!move_uploaded_file($files['tmp_name'][$i], $destPath)) {
            throw new Exception("Không thể lưu file ảnh vào máy chủ.");
          }

          $imagesData[] = [
            'original_file_name' => $files['name'][$i],
            'mime_type' => $mime,
            'file_path' => $subDir . '/' . $fileName,
            'file_size' => $files['size'][$i]
          ];
        }
      }

      $this->_weeklyReportService->submitWeeklyReport(
        $batchStudentId,
        $weekNumber,
        $content,
        $isExempt,
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
