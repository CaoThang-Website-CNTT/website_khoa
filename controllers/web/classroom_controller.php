<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Services\ClassroomService;
use App\Services\TeacherService;

class ClassroomController extends Controller
{
  private ClassroomService $_classroomService;
  private TeacherService $_teacherService;

  public function __construct(
    ClassroomService $classroomService,
    TeacherService $teacherService
  ) {
    $this->_classroomService = $classroomService;
    $this->_teacherService = $teacherService;
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
    $specializations = $this->_classroomService->getAllSpecializations();
    $teachers = $this->_teacherService->getAllTeachers();

    $this->render('admin/classrooms/create', [
      'majors' => $majors,
      'specializations' => $specializations,
      'teachers' => $teachers,
    ], layout: 'dashboard_layout');
  }

  public function store(Request $request)
  {
    $data = $request->all();
    $validator = new Validator();

    $rules = [
      'major_id' => ['required'],
      'specialization_id' => ['nullable'],
      'class_of' => ['required'],
      'letter' => ['size:1'],
      'short_name' => ['required'],
      'homeroom_teacher_id' => ['required'],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs();
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect('admin/classrooms/create');
    }

    $newClassroom = $this->_classroomService->createClassroom($data);

    if ($newClassroom) {
      $request->session()->flashNotify(
        'success',
        'Lớp học đã được tạo thành công!',
        'Lớp ' . $newClassroom->short_name . ' đã được tạo.'
      );
    } else {
      $request->session()->flashNotify('error', 'Có lỗi xảy ra, không thể tạo lớp học.');
    }

    return $this->redirect('admin/classrooms/create');
  }

  public function edit($id, Request $request)
  {
    $classroom = $this->_classroomService->getClassroomById((int) $id);

    if (!$classroom) {
      $request->session()->flashNotify('error', 'Lớp học không tồn tại.');
      return $this->redirect('admin/classrooms');
    }

    $majors = $this->_classroomService->getAllMajors();
    $specializations = $this->_classroomService->getAllSpecializations();
    $teachers = $this->_teacherService->getAllTeachers();
    $classroom->homeroomTeacher = current(array_filter(
      $teachers,
      fn($t) => $t->id === $classroom->homeroom_teacher_id
    )) ?: null;

    $this->render('admin/classrooms/edit', [
      'classroom' => $classroom,
      'majors' => $majors,
      'specializations' => $specializations,
      'teachers' => $teachers,
    ], layout: 'dashboard_layout');
  }

  public function update($id, Request $request)
  {
    $data = $request->all();
    $validator = new Validator();

    $rules = [
      'major_id' => ['required'],
      'specialization_id' => ['nullable'],
      'class_of' => ['required'],
      'letter' => ['size:1'],
      'short_name' => ['required'],
      'homeroom_teacher_id' => ['required'],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs();
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect("admin/classrooms/{$id}");
    }

    $isSuccess = $this->_classroomService->updateClassroom($id, $data);

    if ($isSuccess) {
      $request->session()->flashNotify('success', 'Cập nhật lớp học thành công!');
    } else {
      $request->session()->flashNotify('error', 'Cập nhật lớp học không thành công.');
    }

    return $this->redirect("admin/classrooms/{$id}");
  }

  public function destroy($id, Request $request)
  {
    $isSuccess = $this->_classroomService->deleteClassroom((int) $id);

    if ($isSuccess) {
      $request->session()->flashNotify('success', 'Xóa lớp học thành công!');
    } else {
      $request->session()->flashNotify('error', 'Không thể xóa lớp học.');
    }

    return $this->redirect('admin/classrooms');
  }
}