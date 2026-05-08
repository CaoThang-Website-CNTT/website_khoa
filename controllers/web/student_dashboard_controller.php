<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\RequestValidator;
use App\Services\{StudentService, ClassroomService, InternshipBatchService, InternshipAssignmentService, CompanyService, InternshipSubmissionService};
use App\Core\Files\UploadedFileHandler;
use Exception;

class StudentDashboardController extends Controller
{
  private StudentService $_studentService;
  private ClassroomService $_classroomService;
  private InternshipBatchService $_internshipBatchService;
  private InternshipAssignmentService $_internshipAssignmentService;
  private CompanyService $_companyService;
  private InternshipSubmissionService $_submissionService;

  public function __construct(
    StudentService $studentService,
    ClassroomService $classroomService,
    InternshipBatchService $internshipBatchService,
    InternshipAssignmentService $internshipAssignmentService,
    CompanyService $companyService,
    InternshipSubmissionService $submissionService
  ) {
    $this->_studentService = $studentService;
    $this->_classroomService = $classroomService;
    $this->_internshipBatchService = $internshipBatchService;
    $this->_internshipAssignmentService = $internshipAssignmentService;
    $this->_companyService = $companyService;
    $this->_submissionService = $submissionService;
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

  /**
   * Hiển thị thông tin thực tập của sinh viên
   * 
   * @param Request $request
   * @param InternshipBatchService $internshipBatchService
   */
  public function internship(Request $request, InternshipBatchService $internshipBatchService)
  {
    $authUser = $request->session()->authUser();
    if (!$authUser) {
      return $this->redirect('/login');
    }
    $student = $this->_studentService->getStudentByAccountId($authUser['account_id']);

    $batchId = $request->query('batch_id');
    $dashboardData = $internshipBatchService->getStudentDashboardData($student->id, (int)$batchId ?: null);

    // Tính toán can_edit_company
    $canEditCompany = false;
    if ($dashboardData['current'] && $dashboardData['current']['start_at']) {
      $startAt = new \DateTime($dashboardData['current']['start_at']);
      $now = new \DateTime();

      // TECHDEBT - TODO: Sử dụng web_settings để lấy cấu hình thời gian cho phép khai báo (vd: 21 ngày = 3 tuần)
      $allowedDays = 21;

      $canEditCompany = true; // Mặc định cho phép sửa

      if (!empty($dashboardData['current']['start_at'])) {
        $startAt = new \DateTime($dashboardData['current']['start_at']);
        $now = new \DateTime();

        if ($now > $startAt) {
          $daysPassed = $now->diff($startAt)->days;
          $canEditCompany = ($daysPassed <= $allowedDays);
        }
      }
    }

    return $this->render('student/dashboard/internship', array_merge([
      'student' => $student,
      'title' => 'Thông tin thực tập',
      'can_edit_company' => $canEditCompany
    ], $dashboardData), layout: 'dashboard_layout');
  }

  public function updateCompany(Request $request)
  {
    $authUser = $request->session()->authUser();
    if (!$authUser) return $this->redirect('/login');

    $data = $request->all();
    $isManual = isset($data['is_manual']) && $data['is_manual'] == 1;

    $validator = new RequestValidator();

    $rules = [
      'batch_student_id' => ['required'],
      'tax_code' => $isManual ? ['nullable'] : ['required'],
      'name' => ['required', 'max:255'],
      'address' => ['required'],
      'position' => ['required', 'max:255'],
      'internship_start_date' => ['required', 'date'],
      'internship_end_date' => ['required', 'date'],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect('/student/internship');
    }

    try {
      if ($isManual) {
        $companyId = $this->_companyService->createManual([
          'tax_code' => $data['tax_code'] ?: null,
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

      $this->_internshipBatchService->updateStudentInternshipInfo((int)$data['batch_student_id'], [
        'company_id' => $companyId,
        'position' => $data['position'],
        'internship_start_date' => $data['internship_start_date'],
        'internship_end_date' => $data['internship_end_date'],
      ]);

      $request->session()->flashNotify('success', 'Lưu thông tin công ty thành công!');
    } catch (Exception $e) {
      $request->session()->flashNotify('error', 'Lỗi: ' . $e->getMessage());
    }

    return $this->redirect('/student/internship');
  }

  public function uploadSubmission(Request $request)
  {
    $authUser = $request->session()->authUser();
    if (!$authUser) return $this->redirect('/login');

    $batchStudentId = (int)$request->input('batch_student_id');
    if (!$batchStudentId) {
      $request->session()->flashNotify('error', 'Thiếu thông tin đợt thực tập.');
      return $this->redirect('/student/internship');
    }

    try {
      $fileHandler = new UploadedFileHandler();
      $uploadedFile = $fileHandler->fromGlobals('report_file');
      $subDir = 'internship_reports/' . date('Y/m/d'); // Chia nhỏ, quản lý file theo ngày
      $uploadDir = BASE_PATH . '/public/uploads/' . $subDir . '/';
      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
      }

      $fileName = bin2hex(random_bytes(16)) . '.' . $uploadedFile->extension;
      $destPath = $uploadDir . $fileName;

      if (!move_uploaded_file($uploadedFile->tmpPath, $destPath)) {
        throw new Exception('Không thể lưu file vào máy chủ.');
      }

      $this->_submissionService->createSubmission($batchStudentId, [
        'storage_mode' => 'file',
        'original_file_name' => $uploadedFile->originalName,
        'file_path' => $subDir . '/' . $fileName,
      ]);

      $request->session()->flashNotify('success', 'Nộp tài liệu thành công!');
    } catch (Exception $e) {
      $request->session()->flashNotify('error', 'Lỗi tải lên: ' . $e->getMessage());
    }

    return $this->redirect('/student/internship');
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
}
