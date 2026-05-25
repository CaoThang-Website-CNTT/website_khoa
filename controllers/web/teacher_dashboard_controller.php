<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\RequestValidator;
use App\Services\{TeacherService, ClassroomService, InternshipBatchService, InternshipAssignmentService, CompanyService, InternshipSubmissionService, ReferralLetterService};
use App\Core\Files\UploadedFileHandler;
use Exception;

class TeacherDashboardController extends Controller
{
  private TeacherService $_teacherService;
  private ClassroomService $_classroomService;
  private InternshipBatchService $_internshipBatchService;
  private InternshipAssignmentService $_internshipAssignmentService;
  private CompanyService $_companyService;
  private InternshipSubmissionService $_submissionService;
  private ReferralLetterService $_referralLetterService;

  public function __construct(
    TeacherService $teacherService,
    ClassroomService $classroomService,
    InternshipBatchService $internshipBatchService,
    InternshipAssignmentService $internshipAssignmentService,
    CompanyService $companyService,
    InternshipSubmissionService $submissionService,
    ReferralLetterService $referralLetterService
  ) {
    $this->_teacherService = $teacherService;
    $this->_classroomService = $classroomService;
    $this->_internshipBatchService = $internshipBatchService;
    $this->_internshipAssignmentService = $internshipAssignmentService;
    $this->_companyService = $companyService;
    $this->_submissionService = $submissionService;
    $this->_referralLetterService = $referralLetterService;
  }

  /**
   * Hiển thị trang tổng quan giảng viên
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

    $teacher = $this->_teacherService->getTeacherByAccountId($authUser['account_id']);
    if (!$teacher) {
      $request->session()->flashNotify('error', 'Không tìm thấy hồ sơ giảng viên.');
      return $this->redirect('/');
    }

    return $this->render('teacher/index', [
      'teacher' => $teacher,
      'title' => 'Tổng quan giảng viên'
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

    $teacher = $this->_teacherService->getTeacherByAccountId($authUser['account_id']);
    if (!$teacher) {
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
      'national_id' => ['required', 'size:12'],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs();
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect('/teacher');
    }

    // Giữ nguyên các thông tin chuyên môn
    $updateData = array_merge([
      'position' => $teacher->position,
      'degree' => $teacher->degree,
      //'department' => $teacher->department,
      'title' => $teacher->title,
      'notes' => $teacher->notes,
    ], $data);

    try {
      $this->_teacherService->updateTeacher($teacher->id, $updateData);
      $request->session()->flashNotify('success', 'Cập nhật thông tin cá nhân thành công!');
    } catch (Exception $e) {
      $request->session()->flashNotify('error', 'Có lỗi xảy ra: ' . $e->getMessage());
    }

    return $this->redirect('/teacher');
  }

  public function internshipIndex(Request $request)
  {
    $authUser = $request->session()->authUser();
    if (!$authUser) {
      return $this->redirect('/login');
    }

    $teacher = $this->_teacherService->getTeacherByAccountId($authUser['account_id']);
    if (!$teacher) {
      $request->session()->flashNotify('error', 'Không tìm thấy hồ sơ giảng viên.');
      return $this->redirect('/');
    }

    $currentPage = $request->query('page') ?? 1;
    $data = $this->_internshipBatchService->getBatchesByTeacherId($teacher->id, $currentPage, 15);

    $this->render("teacher/internship_batches/index", [
      'data' => $data
    ], layout: "dashboard_layout");
  }

  public function internshipShow(Request $request, int $id)
  {
    $authUser = $request->session()->authUser();
    if (!$authUser) {
      return $this->redirect('/login');
    }

    $teacher = $this->_teacherService->getTeacherByAccountId($authUser['account_id']);
    if (!$teacher) {
      $request->session()->flashNotify('error', 'Không tìm thấy hồ sơ giảng viên.');
      return $this->redirect('/');
    }

    // Kiểm tra quyền
    if (!$this->_internshipBatchService->isSupervisorOfBatch($id, $teacher->id)) {
      $request->session()->flashNotify('error', 'Bạn không được phân công tham gia hướng dẫn đợt thực tập này.');
      return $this->redirect('/teacher/internship_batches');
    }

    $detail = $this->_internshipBatchService->getTeacherBatchDetail($id, $teacher->id);
    if (!$detail) {
      $request->session()->flashNotify('error', 'Không tìm thấy đợt thực tập.');
      return $this->redirect('/teacher/internship_batches');
    }

    // Không cho xem đợt draft
    if ($detail['batch']['status'] === 'draft') {
      $request->session()->flashNotify('error', 'Đợt thực tập này chưa được công bố.');
      return $this->redirect('/teacher/internship_batches');
    }

    return $this->render('teacher/internship_batches/show', [
      'teacher' => $teacher,
      'batch' => $detail['batch'],
      'stats' => $detail['stats'],
      'students' => $detail['students'],
      'title' => 'Chi tiết đợt thực tập #' . $detail['batch']['id']
    ], layout: 'dashboard_layout');
  }
}
