<?php

namespace App\Controllers;

require_once BASE_PATH . '/includes/core/request_validator.php';

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Services\TeacherService;

class TeacherController extends Controller
{
  private $_teacherService;

  public function __construct(TeacherService $teacherService)
  {
    $this->_teacherService = $teacherService;
  }

  public function index(Request $request)
  {
    $currentPage = $request->query('page') ?? 1;

    $data = $this->_teacherService->getTeachersPaginated($currentPage);

    $this->render('admin/teachers/index', [
      'data' => $data,
    ], layout: 'dashboard_layout');
  }

  public function create()
  {
    $this->render("admin/teachers/create", layout: 'dashboard_layout');
  }

  public function store(Request $request)
  {
    $data = $request->all();

    $validator = new Validator();
    $rules = [
      'email' => ['required', 'email', 'max:255'],
      'password' => ['required', 'password'],
      'password_comfirmation' => ['required', 'same:password'],
      'full_name' => ['required', 'max:255'],
      'phone' => ['required', 'phone', 'max:15'],
      'gender' => ['required'],
      'dob' => ['required', 'date'],
      'title' => ['max:150'],
      'department' => ['max:255'],
      'start_date' => ['required', 'date'],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs(excludedKeys: ['password']);
      $request->flashErrors($validator->getErrors());
      return $this->redirect('admin/teachers/create');
    }

    if ($this->_teacherService->isEmailUnique($data['email']) === false) {
      $validator->addError('email', 'Email này đã tồn tại trong hệ thống.');
      $request->flashOldInputs(excludedKeys: ['password']);
      $request->flashErrors($validator->getErrors());
      return $this->redirect('admin/teachers/create');
    }

    $newTeacherId = $this->_teacherService->createTeacher($data, $data['password']);

    if ($newTeacherId) {
      $request->flash('success', 'Tạo mới giảng viên thành công!');
    } else {
      $request->flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect('admin/teachers/create');
  }

  public function edit($id)
  {
    $teacher = $this->_teacherService->getTeacherById($id);
    if (!$teacher) {
      die("Không thấy giảng viên với id: $id");
    }
    $this->render("admin/teachers/edit", [
      "teacher" => $teacher,
    ], layout: 'dashboard_layout');
  }

  public function update($id, Request $request)
  {
    $data = $request->all();

    $validator = new Validator();
    $rules = [
      'full_name' => ['required', 'max:255'],
      'phone' => ['phone', 'max:15'],
      'gender' => ['required'],
      'dob' => ['required', 'date'],
      'title' => ['max:150'],
      'department' => ['max:255'],
      'start_date' => ['required', 'date'],
    ];

    if (!$validator->validate($data, $rules)) {
      $data['account_id'] = $id;
      $request->flashOldInputs();
      $request->flashErrors($validator->getErrors());
      return $this->redirect('admin/teachers/' . $id);
    }

    $isSuccess = $this->_teacherService->updateTeacher((int) $id, $data);

    if ($isSuccess) {
      $request->flash('success', 'Cập nhật giảng viên thành công!');
    } else {
      $request->flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect('admin/teachers/' . $id);
  }

  public function destroy($id, Request $request)
  {
    $isSuccess = $this->_teacherService->deleteTeacher($id);

    if ($isSuccess) {
      $request->flash('success', 'Xoá giảng viên thành công!');
    } else {
      $request->flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect('admin/teachers');
  }
}