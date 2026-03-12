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

  public function store(array $data)
  {
    $newId = $this->_educationService->createTeacher($data);
    return $newId;
  }

  public function update($id, array $data)
  {
    $isSuccess = $this->_educationService->updateteacher($id, $data);
    return $isSuccess;
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
   * Helper function, redirect về form tạo/sửa sinh viên khi có lỗi validate
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
