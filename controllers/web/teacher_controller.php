<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\RequestValidator;
use App\Services\TeacherService;

class TeacherController extends Controller
{
  private TeacherService $_teacherService;

  public function __construct(TeacherService $teacherService)
  {
    $this->_teacherService = $teacherService;
  }

  public function index(Request $request)
  {
    $currentPage = $request->query('page') ?? 1;

    $data = $this->_teacherService->getTeachers($currentPage);

    $this->render('admin/teachers/index', [
      'data' => $data,
    ], layout: 'dashboard_layout');
  }

  public function create()
  {
    $departments = $this->_teacherService->getAllDepartments();
    $this->render("admin/teachers/create", [
      'departments' => $departments,
    ], layout: 'dashboard_layout');
  }

  public function store(Request $request)
  {
    $data = $request->all();

    $validator = new RequestValidator();
    $rules = [
      'full_name' => ['required', 'max:255'],
      'dob' => ['required', 'date'],
      'national_id' => ['required', 'size:12'],
      'gender' => ['required', 'in:male,female'],
      'phone' => ['required', 'phone', 'max:15'],
      'address' => ['required'],

      'degree' => ['required', 'max:255'],
      'title' => ['nullable', 'max:150'],
      'position' => ['required', 'max:255'],
      'department_id' => ['required', 'numeric'],
      // 'start_date' => ['required', 'date'],
      // 'end_date' => ['required', 'date'],
      'notes' => ['nullable'],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs();
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect('admin/teachers/create');
    }

    if ($this->_teacherService->isEmailUnique($data['email']) === false) {
      $validator->addError('email', 'Email này đã tồn tại trong hệ thống.');
      $request->flashOldInputs();
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect('admin/teachers/create');
    }

    $newTeacher = $this->_teacherService->createTeacher($data);

    if ($newTeacher) {
      $request->session()->flashNotify(
        'success',
        'Tạo mới giảng viên thành công!',
        'Giảng viên ' . $newTeacher->full_name . ' đã được tạo.'
      );
    } else {
      $request->session()->flashNotify('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect('admin/teachers/create');
  }

  public function edit($id)
  {
    $teacher = $this->_teacherService->getTeacherById($id);
    if (!$teacher) {
      die("Không thấy giảng viên với id: $id");
    }
    $departments = $this->_teacherService->getAllDepartments();
    $this->render("admin/teachers/edit", [
      "teacher" => $teacher,
      "departments" => $departments,
    ], layout: 'dashboard_layout');
  }

  public function update($id, Request $request)
  {
    $data = $request->all();

    $validator = new RequestValidator();
    $rules = [
      'full_name' => ['required', 'max:255'],
      'dob' => ['required', 'date'],
      'national_id' => ['required', 'size:12'],
      'gender' => ['required', 'in:male,female'],
      'phone' => ['required', 'phone', 'max:15'],
      'address' => ['required'],

      'degree' => ['required', 'max:255'],
      'title' => ['nullable', 'max:150'],
      'position' => ['required', 'max:255'],
      'department_id' => ['required', 'numeric'],
      //'start_date' => ['required', 'date'],
      //'end_date' => ['required', 'date'],
      'notes' => ['nullable'],
    ];

    if (!$validator->validate($data, $rules)) {
      $data['account_id'] = $id;
      $request->flashOldInputs();
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect('admin/teachers/' . $id);
    }

    $isSuccess = $this->_teacherService->updateTeacher($id, $data);

    if ($isSuccess) {
      $request->session()->flashNotify('success', 'Cập nhật giảng viên thành công!');
    } else {
      $request->session()->flashNotify('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect('admin/teachers/' . $id);
  }

  public function destroy($id, Request $request)
  {
    $isSuccess = $this->_teacherService->deleteTeacher($id);

    if ($isSuccess) {
      $request->session()->flashNotify('success', 'Xoá giảng viên thành công!');
    } else {
      $request->session()->flashNotify('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect('admin/teachers');
  }
}
