<?php

require_once __DIR__ . '/../utils/request_validator.php';

use App\Core\Request;
use App\Utils\Validator;
use App\Services\EducationRepositoryInterface;

class ClassroomController
{
  private $_educationService;

  public function __construct(EducationRepositoryInterface $educationService)
  {
    $this->_educationService = $educationService;
  }

  public function index()
  {
    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 15;

    $classroomData = $this->_educationService->getClassrooms($currentPage, $limit);

    $classrooms = $classroomData['data'];
    $classroomTotalPages = $classroomData['last_page'];
    $classroomTotalRows = $classroomData['total_rows'];

    $classroomBaseUrl = "?page={$currentPage}";
    ob_start();
    require_once __DIR__ . '/../templates/pages/admin/dashboard_classroom.php';
    $content = ob_get_clean();
    require_once __DIR__ . '/../templates/layouts/dashboard_layout.php';
  }
  public function edit($id)
  {
    $classroom = $this->_educationService->getClassroomById($id);
    if (!$classroom) {
      die("Không tìm thấy lớp học với id: $id");
    }

    $major = $this->_educationService->getMajorById($classroom->major_id);
    $specialization = $classroom->specialization_id ? $this->_educationService->getSpecializationById($classroom->specialization_id) : null;

    ob_start();
    require_once __DIR__ . '/../templates/pages/admin/classroom_detail.php';
    $content = ob_get_clean();
    require_once __DIR__ . '/../templates/layouts/dashboard_layout.php';
  }
  public function update($id)
  {
    $data = $_POST;
    $validator = new Validator();

    // Các rule cơ bản
    $rules = [
      'class_of' => ['required', 'numeric'],
      'short_name' => ['required'],
      'major_full_name' => ['required']
    ];

    if (!empty($data['specialization_id'])) {
      $rules['spec_full_name'] = ['required'];
      $rules['letter'] = ['required', 'max:1'];
    }

    if (!$validator->validate($data, $rules)) {
      var_dump($validator->getErrors());
      die();
      return $this->redirectWithError($validator->getErrors(), $data);
    }
    // var_dump($data);
    // die();

    // Xử lý Letter nếu trống
    $data['letter'] = isset($data['letter']) ? trim($data['letter']) : '';

    // Kiểm tra trùng lặp classroom.short_name MỚI nếu có thay đổi
    $classroom = $this->_educationService->getClassroomById($id);
    if ($classroom->short_name !== $data['short_name']) {
      if (!$this->_educationService->isClassroomShortNameUnique($data['short_name'])) {
        $validator->addError('short_name', 'Tên viết tắt lớp này đã tồn tại.');
        return $this->redirectWithError($validator->getErrors(), $data);
      }
    }

    // Cập nhật tên Ngành
    $this->_educationService->updateMajorFullName($data['major_id'], $data['major_full_name']);

    // Cập nhật tên Chuyên Ngành nếu có
    if (!empty($data['specialization_id']) && !empty($data['spec_full_name'])) {
      $this->_educationService->updateSpecializationFullName($data['specialization_id'], $data['spec_full_name']);
    }

    $success = $this->_educationService->updateClassroomInfo($id, $data);

    if (session_status() === PHP_SESSION_NONE) session_start();
    if ($success) {
      $_SESSION['flash_message'] = ['type' => 'success', 'content' => 'Cập nhật thông tin lớp học thành công!'];
    } else {
      $_SESSION['flash_message'] = ['type' => 'error', 'content' => 'Có lỗi xảy ra khi cập nhật.'];
    }

    header("Location: " . url('admin/classrooms'));
    exit;
  }
  public function destroy($id)
  {
    $isSuccess = $this->_educationService->deleteClassroom($id);
    if ($isSuccess) {
      $_SESSION['flash_message'] = ['type' => 'success', 'content' => 'Xoá lớp học thành công!'];
    } else {
      $_SESSION['flash_message'] = ['type' => 'error', 'content' => 'Có lỗi xảy ra, vui lòng thử lại.'];
    }

    header("Location: " . url('admin/classrooms'));
    exit;
  }
  public function create()
  {
    ob_start();
    $majors = $this->_educationService->getAllMajors();
    require_once __DIR__ . '/../templates/pages/admin/classroom_new.php';
    $content = ob_get_clean();
    require_once __DIR__ . '/../templates/layouts/dashboard_layout.php';
  }
  public function store()
  {
    $data = $_POST;

    $validator = new Validator();
    $rules = [
      'major_id'   => ['required', 'numeric'],
      'class_of'   => ['required', 'numeric'],
      'short_name' => ['required']
    ];

    if (empty($data['specialization_id']) || $data['specialization_id'] === '0') {
      $data['specialization_id'] = null;
    } else {
      // Nếu có chuyên ngành, bắt buộc phải nhập letter
      $rules['letter'] = ['required', 'max:1'];
    }
    if (!$validator->validate($data, $rules)) {
      return $this->redirectWithError($validator->getErrors(), $data);
    }
    $data['letter'] = isset($data['letter']) ? trim($data['letter']) : '';

    // Kiểm tra xem short_name đã tồn tại trong database chưa
    if (!$this->_educationService->isClassroomShortNameUnique($data['short_name'])) {
      $validator->addError('short_name', 'Tên lớp học này đã tồn tại trong hệ thống.');
      return $this->redirectWithError($validator->getErrors(), $data);
    }

    $newClassroomId = $this->_educationService->createClassroom($data);
    if ($newClassroomId) {
      if (session_status() === PHP_SESSION_NONE) session_start();
      $_SESSION['flash_message'] = [
        'type' => 'success',
        'content' => 'Tạo mới lớp học thành công!'
      ];
    } else {
      if (session_status() === PHP_SESSION_NONE) session_start();
      $_SESSION['flash_message'] = [
        'type' => 'error',
        'content' => 'Có lỗi xảy ra trong quá trình lưu, vui lòng thử lại.'
      ];
    }
    header("Location: " . url('admin/classrooms'));
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
    if (session_status() === PHP_SESSION_NONE)
      session_start();
    $_SESSION['errors'] = $errors;
    $_SESSION['old_data'] = $oldData;
    if (isset($oldData['id'])) {
      header("Location: " . url('admin/classrooms/edit/' . $oldData['id']));
    } else {
      header("Location: " . url('admin/classrooms/create'));
    }
    exit;
  }
}