<?php

namespace App\Controllers;

require_once BASE_PATH . "/includes/core/controller.php";
require_once BASE_PATH . '/includes/core/request_validator.php';
require_once BASE_PATH . '/models/classroom.php';
require_once BASE_PATH . '/models/major.php';
require_once BASE_PATH . '/models/specialization.php';

use App\Core\Controller;
use App\Core\Page;
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
  public function apiIndex(Request $request)
  {
    try {
      $classrooms = $this->_classroomService->getAllClassrooms();
      // Format dữ liệu trả về cho Dropdown Filter: { value, label }
      $data = array_map(function ($c) {
        return [
          'value' => $c->id,
          'label' => $c->short_name,
          'count' => $c->student_count
        ];
      }, $classrooms);

      jsonResponse($data, 'Thành công', true);
    } catch (\Exception $e) {
      jsonResponse([], 'Lỗi hệ thống', false, [$e->getMessage()]);
    }
  }
  public function index(Request $request)
  {
    $currentPage = $request->query('page') ?? 1;

    $classrooms = $this->_classroomService->getClassrooms($currentPage);
    $total = $this->_classroomService->getTotalClassroomsCount();

    $page = new Page($total, 15, $currentPage);

    $this->render("admin/classrooms/index", [
      'classrooms' => $classrooms,
      'page' => $page,
    ], layout: "dashboard_layout");
  }

  public function create()
  {
    $majors = $this->_classroomService->getAllMajors();

    $this->render("admin/classrooms/create", [
      'majors' => $majors,
    ], layout: "dashboard_layout");
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

    // Fetch major details for class code generation
    $major = $this->_classroomService->getMajorById($data['major_id']);
    if (!$major) {
      $request->flashOldInputs();
      $request->flashErrors(['major_id' => ['Ngành học không tồn tại.']]);
      return $this->redirect('admin/classrooms/create');
    }
    $data['major_level'] = $major['level'];
    $data['major_short'] = $major['short_name'];

    $classCode = $this->buildAndValidateClassCode($data);

    if (!$classCode) {
      $request->flashOldInputs();
      $request->flashErrors(['short_name' => ['Mã lớp không hợp lệ. Vui lòng kiểm tra lại ngành/chuyên ngành/năm học.']]);
      return $this->redirect('admin/classrooms/create');
    }

    $data['short_name'] = $classCode;

    $success = $this->_classroomService->createClassroom($data);

    if ($success) {
      $request->flash('success', 'Thành công', 'Lớp học đã được tạo thành công!');
    } else {
      $request->flash('error', 'Thất bại', 'Có lỗi xảy ra khi tạo lớp học.');
    }

    return $this->redirect('admin/classrooms/create');
  }

  public function edit($id, Request $request)
  {
    $classroom = $this->_classroomService->getClassroomById($id);

    if (!$classroom) {
      $request->flash('error', 'Không tìm thấy', 'Lớp học không tồn tại.');
      return $this->redirect('admin/classrooms');
    }

    $specializations = $this->_classroomService->getSpecializationsByMajorId($classroom->major_id);

    $this->render("admin/classrooms/edit", [
      'classroom' => $classroom,
      'specializations' => $specializations,
    ], layout: "dashboard_layout");
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

    $classCode = $this->buildAndValidateClassCode($data);

    if (!$classCode || $classCode !== trim($data['short_name'])) {
      $request->flashOldInputs();
      $request->flashErrors(['short_name' => ['Mã lớp không khớp với quy tắc. Vui lòng kiểm tra lại.']]);
      return $this->redirect("admin/classrooms/{$id}");
    }

    $data['short_name'] = $classCode;

    $success = $this->_classroomService->updateClassroom($id, $data);

    if ($success) {
      $request->flash('success', 'Thành công', 'Cập nhật lớp học thành công!');
    } else {
      $request->flash('error', 'Thất bại', 'Cập nhật lớp học không thành công.');
    }

    return $this->redirect("admin/classrooms/{$id}");
  }

  public function destroy($id, Request $request)
  {
    $classroom = $this->_classroomService->getClassroomById($id);

    if (!$classroom) {
      $request->flash('error', 'Không tìm thấy', 'Lớp học không tồn tại.');
      return $this->redirect('admin/classrooms');
    }

    $success = $this->_classroomService->deleteClassroom($id);

    if ($success) {
      $request->flash('success', 'Thành công', 'Xóa lớp học thành công!');
    } else {
      $request->flash('error', 'Thất bại', 'Không thể xóa lớp học.');
    }

    return $this->redirect('admin/classrooms');
  }

  /**
   * Build and validate class code using the exact same logic as JavaScript on UI
   */
  private function buildAndValidateClassCode(array $data): ?string
  {
    $level = trim($data['major_level'] ?? '');
    $majorShort = trim($data['major_short'] ?? '');
    $specId = $data['specialization_id'] ?? null;
    $year = trim($data['class_of'] ?? '');
    $letter = strtoupper(trim($data['letter'] ?? ''));

    $shortName = $majorShort;
    if ($specId) {
      $spec = $this->_classroomService->getSpecializationById($specId);
      if ($spec) {
        $shortName = $spec['short_name'] . $majorShort;
      } else {
        return null;
      }
    }

    if (empty($level) || empty($year) || empty($shortName)) {
      return null;
    }

    // Normalize year
    if (strlen($year) === 2) {
      $year = '20' . $year;
    }
    if (strlen($year) !== 4 || !is_numeric($year)) {
      return null;
    }

    // Letter: only 1 uppercase letter
    $letter = preg_replace('/[^A-Z]/', '', $letter);
    $letter = substr($letter, 0, 1);

    // Final class code: [Level][ShortName][Year][Letter]
    return $level . $shortName . $year . $letter;
  }
}