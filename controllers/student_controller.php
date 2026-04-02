<?php

namespace App\Controllers;

require_once BASE_PATH . "/includes/core/controller.php";
require_once BASE_PATH . '/includes/core/request_validator.php';
require_once BASE_PATH . '/models/student.php';
require_once BASE_PATH . '/includes/response.php';

use App\Core\Controller;
use App\Core\Page;
use App\Core\Request;
use App\Models\Student;
use App\Core\Validator;
use App\Services\EducationService;

class StudentController extends Controller
{
  private EducationService $_educationService;

  public function __construct(EducationService $educationService)
  {
    $this->_educationService = $educationService;
  }

  public function index()
  {
    $this->render("admin/students/index", layout: "dashboard_layout");
  }
  public function apiIndex(Request $request)
  {
    $currentPage = $request->query('page') ?? 1;
    $sortCol = $request->query('sort') ?? 'account_id';
    $sortDir = $request->query('dir') ?? 'ASC';
    $search = $request->query('search') ?? '';
    $limit = 15;
    // Lấy các tham số filter từ URL (Dạng array: &gender[]=Nam&classroom_id[]=1)
    $filters = [
      'classroom_id' => $request->query('classroom_id') ?? []
    ];

    try {
      $students = $this->_educationService->getAllStudents($currentPage, $limit, $sortCol, $sortDir, $search, $filters);
      $total = $this->_educationService->getTotalStudentsCount($search, $filters);

      $responseData = [
        'items'       => $students,
        'currentPage' => (int)$currentPage,
        'totalPages'  => ceil($total / $limit)
      ];
      jsonResponse($responseData, 'Lấy danh sách thành công', true);
    } catch (\Exception $e) {
      jsonResponse([], 'Có lỗi xảy ra khi lấy dữ liệu', false, [$e->getMessage()]);
    }
  }

  public function create()
  {
    $classrooms = $this->_educationService->getAllClassrooms();
    $this->render("admin/students/create", [
      'classrooms' => $classrooms
    ], layout: "dashboard_layout");
  }

  public function store(Request $request)
  {
    $data = $request->all();
    $validator = new Validator();

    $rules = [
      'student_id' => ['required', 'mssv', 'max:10'],
      'full_name' => ['required', 'max:255'],
      'phone' => ['required', 'phone', 'max:15'],
      'gender' => ['required'],
      'dob' => ['required', 'date'],
      'major' => ['max:150'],
      'classroom_id' => ['required'],
      'birth_place' => ['max:255'],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs(excludedKeys: ['password']);
      $request->flashErrors($validator->getErrors());
      return $this->redirect('admin/students/create');
    }

    if (!$this->_educationService->isStudentIdUnique($data['student_id'])) {
      $validator->addError('student_id', 'Mã số sinh viên này đã tồn tại trong hệ thống.');
      $request->flashOldInputs(excludedKeys: ['password']);
      $request->flashErrors($validator->getErrors());
      return $this->redirect('admin/students/create');
    }

    $newStudentId = $this->_educationService->createStudent($data, 'Khoacntt@123');

    if ($newStudentId) {
      $request->flash('success', 'Tạo mới sinh viên thành công!');
    } else {
      $request->flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    $this->redirect('admin/students/create');
    exit;
  }

  public function edit($id)
  {
    $student = $this->_educationService->getStudentById($id);
    if (!$student) {
      die("Không thấy sinh viên với id: $id");
    }
    $classrooms = $this->_educationService->getAllClassrooms();

    $this->render("admin/students/edit", [
      'student' => $student,
      'classrooms' => $classrooms
    ], layout: "dashboard_layout");
  }

  public function update($id, Request $request)
  {
    $data = $request->all();

    $validator = new Validator();
    $rules = [
      'full_name' => ['required', 'max:255'],
      'phone' => ['phone', 'max:15'],
      'major' => ['max:150'],
      'gender' => ['required'],
      'dob' => ['required', 'date'],
      'classroom_id' => ['required'],
      'birth_place' => ['max:255'],
    ];

    if (!$validator->validate($data, $rules)) {
      $data['account_id'] = $id;
      $request->flashOldInputs();
      $request->flashErrors($validator->getErrors());
      return $this->redirect('admin/students/' . $id);
    }

    $student = new Student(
      account_id: (int) $id,
      student_id: 0,
      full_name: $data['full_name'],
      gender: $data['gender'],
      dob: $data['dob'],
      phone: $data['phone'],
      classroom_id: (int) $data['classroom_id'],
      major: $data['major'],
      birth_place: $data['birth_place'],
    );

    $isSuccess = $this->_educationService->updateStudent((int) $id, $student);

    if ($isSuccess) {
      $request->flash('success', 'Cập nhật sinh viên thành công!');
    } else {
      $request->flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    $this->redirect('admin/students/' . $id);
    exit;
  }

  public function destroy($id, Request $request)
  {
    $isSuccess = $this->_educationService->deleteStudent($id);

    if ($isSuccess) {
      $request->flash('success', 'Xoá sinh viên thành công!');
    } else {
      $request->flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect('admin/users/');
  }

  public function import()
  {
    $this->render('admin/students/import', layout: 'dashboard_layout');
  }
}
