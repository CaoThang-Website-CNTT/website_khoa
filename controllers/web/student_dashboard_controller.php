<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\RequestValidator;
use App\Services\{StudentService, ClassroomService, InternshipBatchService, InternshipAssignmentService};

class StudentDashboardController extends Controller
{
  private StudentService $_studentService;
  private ClassroomService $_classroomService;
  private InternshipBatchService $_internshipBatchService;
  private InternshipAssignmentService $_internshipAssignmentService;

  public function __construct(
    StudentService $studentService,
    ClassroomService $classroomService,
    InternshipBatchService $internshipBatchService,
    InternshipAssignmentService $internshipAssignmentService
  ) {
    $this->_studentService = $studentService;
    $this->_classroomService = $classroomService;
    $this->_internshipBatchService = $internshipBatchService;
    $this->_internshipAssignmentService = $internshipAssignmentService;
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
    } catch (\Exception $e) {
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
    // @techdebt: Kiểm tra quyền truy cập
    $authUser = $request->session()->authUser();
    $student = $this->_studentService->getStudentByAccountId($authUser['account_id']);

    $batchId = $request->query('batch_id');
    $dashboardData = $internshipBatchService->getStudentDashboardData($student->id, (int)$batchId ?: null);

    return $this->render('student/dashboard/internship', array_merge([
      'student' => $student,
      'title' => 'Thông tin thực tập'
    ], $dashboardData), layout: 'dashboard_layout');
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
