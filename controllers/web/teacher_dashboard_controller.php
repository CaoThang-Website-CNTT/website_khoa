<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\RequestValidator;
use App\Services\{TeacherService, ClassroomService, InternshipBatchService, InternshipAssignmentService, CompanyService, InternshipSubmissionService, ReferralLetterService, InternshipGradeService, InternshipWeeklyReportService};
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
  private InternshipGradeService $_gradeInternshipService;
  private InternshipWeeklyReportService $_weeklyReportService;

  public function __construct(
    TeacherService $teacherService,
    ClassroomService $classroomService,
    InternshipBatchService $internshipBatchService,
    InternshipAssignmentService $internshipAssignmentService,
    CompanyService $companyService,
    InternshipSubmissionService $submissionService,
    ReferralLetterService $referralLetterService,
    InternshipGradeService $gradeInternshipService,
    InternshipWeeklyReportService $weeklyReportService
  ) {
    $this->_teacherService = $teacherService;
    $this->_classroomService = $classroomService;
    $this->_internshipBatchService = $internshipBatchService;
    $this->_internshipAssignmentService = $internshipAssignmentService;
    $this->_companyService = $companyService;
    $this->_submissionService = $submissionService;
    $this->_referralLetterService = $referralLetterService;
    $this->_gradeInternshipService = $gradeInternshipService;
    $this->_weeklyReportService = $weeklyReportService;
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

  public function studentDetail(Request $request, int $batchId, int $batchStudentId)
  {
    $authUser = $request->session()->authUser();
    if (!$authUser) return $this->redirect('/login');

    $teacher = $this->_teacherService->getTeacherByAccountId($authUser['account_id']);
    if (!$teacher) return $this->redirect('/');

    if (!$this->_internshipBatchService->isSupervisorOfBatch($batchId, $teacher->id)) {
      $request->session()->flashNotify('error', 'Bạn không được phân công hướng dẫn đợt thực tập này.');
      return $this->redirect('/teacher/internship_batches');
    }

    $assignment = $this->_internshipAssignmentService->getAssignmentByBatchStudentId($batchStudentId);
    if (!$assignment || $assignment->teacher_id != $teacher->id) {
      $request->session()->flashNotify('error', 'Sinh viên này không do bạn hướng dẫn.');
      return $this->redirect("/teacher/internship_batches/{$batchId}");
    }

    $studentDetail = $this->_internshipBatchService->getTeacherStudentDetail($batchStudentId);
    if (!$studentDetail || $studentDetail['batch_id'] != $batchId) {
      $request->session()->flashNotify('error', 'Không tìm thấy dữ liệu sinh viên.');
      return $this->redirect("/teacher/internship_batches/{$batchId}");
    }

    $batchDetail = $this->_internshipBatchService->getBatchById($batchId);

    return $this->render('teacher/internship_batches/student_detail', [
      'teacher' => $teacher,
      'batch' => $batchDetail,
      'student' => $studentDetail,
      'title' => 'Chi tiết sinh viên: ' . $studentDetail['full_name']
    ], layout: 'dashboard_layout');
  }

  public function internshipGrade(Request $request, int $batchId, int $batchStudentId)
  {
    $authUser = $request->session()->authUser();
    if (!$authUser) return $this->redirect('/login');

    $teacher = $this->_teacherService->getTeacherByAccountId($authUser['account_id']);
    if (!$teacher) return $this->redirect('/');

    $canGrade = $this->_gradeInternshipService->canTeacherGrade($batchId, $teacher->id, $batchStudentId);
    if (!$canGrade['allowed']) {
      $request->session()->flashNotify('error', 'Không thể chấm điểm đợt thực tập này.');
      return $this->redirect("/teacher/internship_batches/{$batchId}");
    }

    $data = $this->_gradeInternshipService->getStudentGradingData($batchStudentId, $teacher->id);
    if (!$data) {
      $request->session()->flashNotify('error', 'Không tìm thấy dữ liệu sinh viên.');
      return $this->redirect("/teacher/internship_batches/{$batchId}");
    }

    $batchDetail = $this->_internshipBatchService->getTeacherBatchDetail($batchId, $teacher->id);
    $students = $batchDetail ? $batchDetail['students'] : [];

    // Lấy dữ liệu báo cáo tuần
    $timeline = [];
    if ($batchDetail && isset($batchDetail['batch'])) {
      $timeline = $this->_weeklyReportService->getStudentWeeklyTimeline($batchStudentId, $batchDetail['batch']['start_at'], $batchDetail['batch']['end_at']);
    }

    // Find index to navigate prev/next
    $currentIndex = -1;
    foreach ($students as $index => $s) {
      if ($s['batch_student_id'] == $batchStudentId) {
        $currentIndex = $index;
        break;
      }
    }

    $prevStudentId = $currentIndex > 0 ? $students[$currentIndex - 1]['batch_student_id'] : null;
    $nextStudentId = $currentIndex < count($students) - 1 ? $students[$currentIndex + 1]['batch_student_id'] : null;

    return $this->render('teacher/internship_batches/grade', [
      'teacher' => $teacher,
      'batchId' => $batchId,
      'batchStudentId' => $batchStudentId,
      'data' => $data,
      'timeline' => $timeline,
      'canGrade' => $canGrade,
      'prevStudentId' => $prevStudentId,
      'nextStudentId' => $nextStudentId,
      'title' => 'Chấm điểm sinh viên: ' . $data['student']['full_name']
    ], layout: 'dashboard_layout');
  }

  public function submitGrade(Request $request, int $batchId, int $batchStudentId)
  {
    $authUser = $request->session()->authUser();
    if (!$authUser) return $this->redirect('/login');

    $teacher = $this->_teacherService->getTeacherByAccountId($authUser['account_id']);
    if (!$teacher) return $this->redirect('/');

    $canGrade = $this->_gradeInternshipService->canTeacherGrade($batchId, $teacher->id, $batchStudentId);
    if (!$canGrade['allowed']) {
      $request->session()->flashNotify('error', $canGrade['reason']);
      return $this->redirect("/teacher/internship_batches/{$batchId}/grade/{$batchStudentId}");
    }

    $data = $request->all();
    $validator = new RequestValidator();
    $rules = [
      'score' => ['required', 'numeric'],
      'score_reason' => ['nullable'],
      'feedback' => ['nullable']
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs();
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect("/teacher/internship_batches/{$batchId}/grade/{$batchStudentId}");
    }

    $result = $this->_gradeInternshipService->saveGrade(
      $batchStudentId,
      (float)$data['score'],
      $data['score_reason'] ?? null,
      $data['feedback'] ?? null,
      $teacher->id
    );

    if ($result['success']) {
      $request->session()->flashNotify('success', $result['message']);
    } else {
      $request->session()->flashNotify('error', $result['message']);
    }

    return $this->redirect("/teacher/internship_batches/{$batchId}/grade/{$batchStudentId}");
  }

  public function weeklyReports(Request $request, int $batchId)
  {
    $authUser = $request->session()->authUser();
    if (!$authUser) return $this->redirect('/login');

    $teacher = $this->_teacherService->getTeacherByAccountId($authUser['account_id']);
    if (!$teacher) return $this->redirect('/');

    if (!$this->_internshipBatchService->isSupervisorOfBatch($batchId, $teacher->id)) {
      $request->session()->flashNotify('error', 'Bạn không được phân công hướng dẫn đợt thực tập này.');
      return $this->redirect('/teacher/internship_batches');
    }

    $batchDetail = $this->_internshipBatchService->getBatchById($batchId);
    if (!$batchDetail) {
      $request->session()->flashNotify('error', 'Không tìm thấy đợt thực tập.');
      return $this->redirect('/teacher/internship_batches');
    }

    // Lấy danh sách tuần thực tập
    $weeks = $this->_weeklyReportService->calculateWeeks($batchDetail['start_at'], $batchDetail['end_at']);

    // Tìm tuần hiện tại
    $currentWeekNumber = 1;
    $now = new \DateTime();
    $nowStr = $now->format('Y-m-d');

    foreach ($weeks as $w) {
      if ($nowStr >= $w['start'] && $nowStr <= $w['end']) {
        $currentWeekNumber = $w['week_number'];
        break;
      }
    }

    // Nếu có week trong query string thì dùng, không thì dùng tuần hiện tại
    $weekParam = $request->query('week') ?? $currentWeekNumber;
    $weekParam = (int)$weekParam;

    $currentPage = (int)($request->query('page') ?? 1);

    $data = $this->_weeklyReportService->getTeacherWeeklyOverview($batchId, $teacher->id, $weekParam, $currentPage, 1000);

    return $this->render('teacher/internship_batches/weekly_reports', [
      'teacher' => $teacher,
      'batch' => $batchDetail,
      'weeks_data' => $weeks,
      'current_week' => $weekParam,
      'reports_data' => $data,
      'title' => 'Báo cáo hàng tuần đợt thực tập'
    ], layout: 'dashboard_layout');
  }
}
