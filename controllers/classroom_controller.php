<?php

namespace App\Controllers;

require_once BASE_PATH . '/includes/core/controller.php';
require_once BASE_PATH . '/includes/core/request_validator.php';

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Services\ClassroomService;

class ClassroomController extends Controller
{
  private ClassroomService $_classroomService;

  public function __construct(ClassroomService $classroomService)
  {
    $this->_classroomService = $classroomService;
  }

  public function index(Request $request)
  {
    $currentPage = $request->query('page') ?? 1;

    $data = $this->_classroomService->getClassroomsPaginated((int) $currentPage);

    $this->render('admin/classrooms/index', [
      'data' => $data,
    ], layout: 'dashboard_layout');
  }

  public function create()
  {
    $majors = $this->_classroomService->getAllMajors();

    $this->render('admin/classrooms/create', [
      'majors' => $majors,
    ], layout: 'dashboard_layout');
  }

  public function store(Request $request)
  {
    $data = $request->all();
    $validator = new Validator();

    $rules = [
      'major_id' => ['required'],
      'class_of' => ['required'],
      'letter' => ['max:1'],
      'specialization_id' => [],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs();
      $request->flashErrors($validator->getErrors());
      return $this->redirect('admin/classrooms/create');
    }

    $newClassroomId = $this->_classroomService->createClassroom($data);

    if ($newClassroomId) {
      $request->flash('success', 'Lớp học đã được tạo thành công!');
    } else {
      $request->flash('error', 'Có lỗi xảy ra, không thể tạo lớp học.');
    }

    return $this->redirect('admin/classrooms/create');
  }

  public function edit($id, Request $request)
  {
    $classroom = $this->_classroomService->getClassroomById((int) $id);

    if (!$classroom) {
      $request->flash('error', 'Lớp học không tồn tại.');
      return $this->redirect('admin/classrooms');
    }

    $specializations = $this->_classroomService->getSpecializationsByMajorId($classroom->major_id);

    $this->render('admin/classrooms/edit', [
      'classroom' => $classroom,
      'specializations' => $specializations,
    ], layout: 'dashboard_layout');
  }

  public function update($id, Request $request)
  {
    $data = $request->all();
    $validator = new Validator();

    $rules = [
      'class_of' => ['required'],
      'letter' => ['max:1'],
      'short_name' => ['required'],
      'major_level' => ['required'],
      'major_short' => ['required'],
      'specialization_id' => [],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs();
      $request->flashErrors($validator->getErrors());
      return $this->redirect("admin/classrooms/{$id}");
    }

    $isSuccess = $this->_classroomService->updateClassroom((int) $id, $data);

    if ($isSuccess) {
      $request->flash('success', 'Cập nhật lớp học thành công!');
    } else {
      $request->flash('error', 'Cập nhật lớp học không thành công.');
    }

    return $this->redirect("admin/classrooms/{$id}");
  }

  public function destroy($id, Request $request)
  {
    $classroom = $this->_classroomService->getClassroomById((int) $id);

    if (!$classroom) {
      $request->flash('error', 'Lớp học không tồn tại.');
      return $this->redirect('admin/classrooms');
    }

    $isSuccess = $this->_classroomService->deleteClassroom((int) $id);

    if ($isSuccess) {
      $request->flash('success', 'Xóa lớp học thành công!');
    } else {
      $request->flash('error', 'Không thể xóa lớp học.');
    }

    return $this->redirect('admin/classrooms');
  }
}