<?php

require_once __DIR__ . '/../utils/request_validator.php';
require_once __DIR__ . '/../models/teacher.php';

use App\Models\Teacher;
use App\Utils\Validator;
use App\Services\EducationRepositoryInterface;

class TeacherController
{
  private $_educationService;

  public function __construct(EducationRepositoryInterface $educationService)
  {
    $this->_educationService = $educationService;
  }

  public function index()
  {
    $teachers = $this->_educationService->getAllTeachers(1);
    ob_start();
    require_once __DIR__ . '/../dashboard_teacher.php';
    $content = ob_get_clean();
    require_once __DIR__ . '/../templates/layouts/dashboard_layout.php';
  }
  public function create()
  {
    ob_start();
    require_once __DIR__ . '/../teacher_new.php';
    $content = ob_get_clean();
    require_once __DIR__ . '/../templates/layouts/dashboard_layout.php';
  }
  public function store(array $data)
  {
    $validator = new Validator();
    $rules = [
      'email' => ['required', 'email', 'max:255'],
      'password' => ['required', 'password',],
      'password_comfirmation' => ['required', 'same:password',],
      'full_name' => ['required', 'max:255'],
      'phone' => ['required', 'phone', 'max:15'],
      'gender' => ['required'],
      'dob' => ['required', 'date'],
      'title' => ['max:150'],
      'department' => ['max:255'],
      'start_date' => ['required, date']
    ];

    if (!$validator->validate($data, $rules)) {
      return $this->redirectWithError($validator->getErrors(), $data);
    }

    if ($this->_educationService->isEmailUnique($data['email']) === false) {
      $validator->addError('email', 'Email này đã tồn tại trong hệ thống.');
      return $this->redirectWithError($validator->getErrors(), $data);
    }

    $newStudentId = $this->_educationService->createTeacher($data, $data['password']);
    if ($newStudentId) {
      $_SESSION['flash_message'] = ['type' => 'success', 'content' => 'Tạo mới giảng viên thành công!'];
    } else {
      $_SESSION['flash_message'] = ['type' => 'error', 'content' => 'Có lỗi xảy ra, vui lòng thử lại.'];
    }

    header("Location: " . url('admin/teachers'));
    exit;
  }

  public function update($id, array $data)
  {
    $validator = new Validator();
    $rules = [
      'full_name' => ['required', 'max:255'],
      'phone' => ['phone', 'max:15'],
      'gender' => ['required'],
      'dob' => ['required', 'date'],
      'title' => ['max:150'],
      'department' => ['max:255'],
      'start_date' => ['required, date']
    ];

    if (!$validator->validate($data, $rules)) {
      $data['account_id'] = $id;
      return $this->redirectWithError($validator->getErrors(), $data);
    }
    $teacher = new Teacher(
      account_id: (int)$id,
      full_name: $data['full_name'],
      gender: $data['gender'],
      dob: $data['dob'],
      phone: $data['phone'],
      title: $data['title'],
      department: $data['department'],
      start_date: $data['start_date']
    );

    session_start();
    $isSuccess = $this->_educationService->updateTeacher((int)$id, $teacher);

    if ($isSuccess) {
      $_SESSION['flash_message'] = ['type' => 'success', 'content' => 'Cập nhật giảng viên thành công!'];
    } else {
      $_SESSION['flash_message'] = ['type' => 'error', 'content' => 'Có lỗi xảy ra, vui lòng thử lại.'];
    }

    header("Location: " . url('admin/teachers'));
    exit;
  }

  public function edit($id)
  {
    $teacher = $this->_educationService->getTeacherById($id);
    if (!$teacher) {
      die("Không thấy giảng viên với id: $id");
    }
    ob_start();
    require_once __DIR__ . '/../teacher_detail.php';
    $content = ob_get_clean();
    require_once __DIR__ . '/../templates/layouts/dashboard_layout.php';
  }

  public function destroy($id)
  {
    $isSuccess = $this->_educationService->deleteTeacher($id);
    if ($isSuccess) {
      $_SESSION['flash_message'] = ['type' => 'success', 'content' => 'Xoá giảng viên thành công!'];
    } else {
      $_SESSION['flash_message'] = ['type' => 'error', 'content' => 'Có lỗi xảy ra, vui lòng thử lại.'];
    }

    header("Location: " . url('admin/teachers'));
    exit;
  }
  /**
   * Helper function, redirect về form tạo/sửa giảng viên khi có lỗi validate
   * @param array $errors
   * @param array $oldData
   * @return never
   */
  private function redirectWithError(array $errors, array $oldData)
  {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['errors'] = $errors;
    $_SESSION['old_data'] = $oldData;
    if (isset($oldData['account_id'])) {
      header("Location: " . url('admin/teachers/edit/' . $oldData['account_id']));
    } else {
      header("Location: " . url('admin/teachers/create'));
    }
    exit;
  }
}
