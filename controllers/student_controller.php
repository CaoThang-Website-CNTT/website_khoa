<?php

require_once __DIR__ . '/../utils/request_validator.php';
require_once __DIR__ . '/../models/student.php';

use App\Models\Student;
use App\Utils\Validator;
use App\Services\EducationRepositoryInterface;

class StudentController
{
  private $_educationService;

  public function __construct(EducationRepositoryInterface $educationService)
  {
    $this->_educationService = $educationService;
  }

  public function index()
  {
    $students = $this->_educationService->getAllStudents(1);
    ob_start();
    require_once __DIR__ . '/../dashboard_user.php';
    $content = ob_get_clean();
    require_once __DIR__ . '/../templates/layouts/dashboard_layout.php';
  }

  public function create()
  {
    $classrooms = $this->_educationService->getAllClassrooms();
    ob_start();
    require_once __DIR__ . '/../user_new.php';
    $content = ob_get_clean();
    require_once __DIR__ . '/../templates/layouts/dashboard_layout.php';
  }
  public function store(array $data)
  {
    $validator = new Validator();
    $rules = [
      'student_id' => ['required', 'mssv', 'max:10'],
      'full_name' => ['required', 'max:255'],
      'phone' => ['phone', 'max:15'],
      'gender' => ['required'],
      'dob' => ['required', 'date'],
      'major' => ['max:150'],
      'classroom_id' => ['required'],
      'birth_place' => ['max:255']
    ];

    if (!$validator->validate($data, $rules)) {
      return $this->redirectWithError($validator->getErrors(), $data);
    }

    if (!$this->_educationService->isStudentIdUnique($data['student_id'])) {
      $validator->addError('student_id', 'Mã số sinh viên này đã tồn tại trong hệ thống.');
      return $this->redirectWithError($validator->getErrors(), $data);
    }
    session_start();
    $defaultPassword = 'Khoacntt@123';
    $newStudentId = $this->_educationService->createStudent($data, $defaultPassword);
    if ($newStudentId) {
      $_SESSION['flash_message'] = ['type' => 'success', 'content' => 'Tạo mới sinh viên thành công!'];
    } else {
      $_SESSION['flash_message'] = ['type' => 'error', 'content' => 'Có lỗi xảy ra, vui lòng thử lại.'];
    }

    header("Location: " . url('admin/students'));
    exit;
  }
  public function edit($id)
  {
    $student = $this->_educationService->getStudentById($id);
    if (!$student) {
      die("Không thấy sinh viên với id: $id");
    }
    $classrooms = $this->_educationService->getAllClassrooms();
    ob_start();
    require_once __DIR__ . '/../user_detail.php';
    $content = ob_get_clean();
    require_once __DIR__ . '/../templates/layouts/dashboard_layout.php';
  }

  public function update($id, array $data)
  {
    $validator = new Validator();
    $rules = [
      'full_name' => ['required', 'max:255'],
      'phone' => ['phone', 'max:15'],
      'major' => ['max:150'],
      'gender' => ['required'],
      'dob' => ['required', 'date'],
      'classroom_id' => ['required'],
      'birth_place' => ['max:255']
    ];

    if (!$validator->validate($data, $rules)) {
      $data['account_id'] = $id;
      return $this->redirectWithError($validator->getErrors(), $data);
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
      birth_place: $data['birth_place']
    );

    session_start();
    $isSuccess = $this->_educationService->updateStudent((int) $id, $student);

    if ($isSuccess) {
      $_SESSION['flash_message'] = ['type' => 'success', 'content' => 'Cập nhật sinh viên thành công!'];
    } else {
      $_SESSION['flash_message'] = ['type' => 'error', 'content' => 'Có lỗi xảy ra, vui lòng thử lại.'];
    }

    header("Location: " . url('admin/students'));
    exit;
  }

  public function destroy($id)
  {
    $isSuccess = $this->_educationService->deleteStudent($id);
    if ($isSuccess) {
      $_SESSION['flash_message'] = ['type' => 'success', 'content' => 'Xoá sinh viên thành công!'];
    } else {
      $_SESSION['flash_message'] = ['type' => 'error', 'content' => 'Có lỗi xảy ra, vui lòng thử lại.'];
    }

    header("Location: " . url('admin/students'));
    exit;
  }

  public function import()
  {

  }
  /**
   * Helper function, redirect về form tạo/sửa sinh viên khi có lỗi validate
   * @param array $errors
   * @param array $oldData
   * @return never
   */
  private function redirectWithError(array $errors, array $oldData)
  {
    if (session_status() === PHP_SESSION_NONE)
      session_start();
    $_SESSION['errors'] = $errors;
    $_SESSION['old_data'] = $oldData;
    if (isset($oldData['account_id'])) {
      header("Location: " . url('admin/students/edit/' . $oldData['account_id']));
    } else {
      header("Location: " . url('admin/students/create'));
    }
    exit;
  }
}
