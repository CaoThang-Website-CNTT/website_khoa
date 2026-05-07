<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\RequestValidator;
use App\Services\{CompanyService};

class CompanyController extends Controller
{
  private CompanyService $_companyService;

  public function __construct(
    CompanyService $companyService
  ) {
    $this->_companyService = $companyService;
  }

  public function index(Request $request)
  {
    $currentPage = $request->query('page') ?? 1;

    $data = $this->_companyService->getCompanies($currentPage, 15);

    $this->render("admin/companies/index", [
      'data' => $data,
    ], layout: "dashboard_layout");
  }

  public function create()
  {
    // $classrooms = $this->_classroomService->getAllClassrooms();
    // $this->render("admin/students/create", [
    //   'classrooms' => $classrooms
    // ], layout: "dashboard_layout");
  }

  public function store(Request $request)
  {
    // $data = $request->all();

    // $validator = new RequestValidator();
    // $rules = [
    //   'full_name' => ['required', 'max:255'],
    //   'dob' => ['required', 'date'],
    //   'birth_place' => ['required', 'max:255'],
    //   'national_id' => ['required', 'size:12'],
    //   'gender' => ['required', 'in:male,female'],
    //   'phone' => ['required', 'phone', 'max:15'],
    //   'address' => ['required'],

    //   'student_id' => ['required', 'size:10'],
    //   'classroom_id' => ['required'],
    //   'notes' => ['nullable'],

    //   'status' => ['required', 'in:Đang học,Đã tốt nghiệp,Tạm ngưng,Thôi học']
    // ];

    // if (!$validator->validate($data, $rules)) {
    //   $request->flashOldInputs();
    //   $request->session()->flashErrors($validator->getErrors());
    //   return $this->redirect('admin/students/create');
    // }

    // if (!$this->_studentService->isStudentIdUnique($data['student_id'])) {
    //   $validator->addError('student_id', 'Mã số sinh viên này đã tồn tại trong hệ thống.');
    //   $request->flashOldInputs();
    //   $request->session()->flashErrors($validator->getErrors());
    //   return $this->redirect('admin/students/create');
    // }

    // $newStudent = $this->_studentService->createStudent($data);

    // if ($newStudent) {
    //   $request->session()->flashNotify(
    //     'success',
    //     'Tạo mới sinh viên thành công!',
    //     'Sinh viên có mã #' . $newStudent->student_id . ' đã được tạo.'
    //   );
    // } else {
    //   $request->session()->flashNotify('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    // }

    // $this->redirect('admin/students/create');
    // exit;
  }

  public function edit($student_id, Request $request)
  {
    // $student = $this->_studentService->getStudentByStudentId($student_id);
    // if (!$student) {
    //   $request->session()->flashNotify(
    //     'error',
    //     'Không tìm thấy Sinh Viên #' . $student_id . '!',
    //     'Hãy thử lại sau.'
    //   );
    //   $this->redirect('admin/students');
    // }
    // $classrooms = $this->_studentService->getAllClassrooms();

    // $this->render("admin/students/edit", [
    //   'student' => $student,
    //   'classrooms' => $classrooms
    // ], layout: "dashboard_layout");
  }

  public function update($student_id, Request $request)
  {
    // $data = $request->all();

    // $validator = new RequestValidator();
    // $rules = [
    //   'full_name' => ['required', 'max:255'],
    //   'dob' => ['required', 'date'],
    //   'birth_place' => ['required', 'max:255'],
    //   'national_id' => ['required', 'size:12'],
    //   'gender' => ['required', 'in:male,female'],
    //   'phone' => ['required', 'phone', 'max:15'],
    //   'address' => ['required'],

    //   'student_id' => ['required', 'size:10'],
    //   'classroom_id' => ['required'],
    //   'notes' => ['nullable'],

    //   'status' => ['required', 'in:Đang học,Đã tốt nghiệp,Tạm ngưng,Thôi học']
    // ];

    // if (!$validator->validate($data, $rules)) {
    //   $request->flashOldInputs();
    //   $request->session()->flashErrors($validator->getErrors());
    //   return $this->redirect('admin/students/' . $student_id);
    // }

    // $isSuccess = $this->_studentService->updateStudent($student_id, $data);

    // if ($isSuccess) {
    //   $request->session()->flashNotify('success', 'Cập nhật sinh viên thành công!');
    // } else {
    //   $request->session()->flashNotify('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    // }

    // $this->redirect('admin/students/' . $student_id);
    // exit;
  }

  public function destroy($student_id, Request $request)
  {
    // $isSuccess = $this->_studentService->deleteStudent($student_id);

    // if ($isSuccess) {
    //   $request->session()->flashNotify('success', 'Xoá sinh viên thành công!');
    // } else {
    //   $request->session()->flashNotify('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    // }

    // return $this->redirect('admin/students');
  }
}
