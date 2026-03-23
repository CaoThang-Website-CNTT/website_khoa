<?php

require_once __DIR__ . '/../utils/request_validator.php';
require_once __DIR__ . '/../includes/response.php';

use App\Services\EducationRepositoryInterface;

class SpecializationController
{
  private $_educationService;

  public function __construct(EducationRepositoryInterface $educationService)
  {
    $this->_educationService = $educationService;
  }

  public function getByMajor()
  {
    $majorId = $_GET['majorId'] ?? null;

    if (!$majorId || !is_numeric($majorId)) {
      http_response_code(400);
      jsonResponse([], 'Mã ngành không hợp lệ', false);
    }

    $majors = $this->_educationService->getSpecializationsByMajorId((int)$majorId);

    jsonResponse($majors, 'Thành công');
  }
  public function store()
  {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['full_name']) || empty($data['short_name']) || empty($data['major_id'])) {
      http_response_code(400);
      jsonResponse([], 'Vui lòng nhập đủ thông tin chuyên ngành (tên, viết tắt, mã ngành)', false);
      return;
    }

    try {
      $id = $this->_educationService->createSpecialization($data);
      $data['id'] = $id;
      jsonResponse($data, 'Thêm chuyên ngành thành công');
    } catch (Exception $e) {
      http_response_code(500);
      jsonResponse([], 'Lỗi hệ thống: ' . $e->getMessage(), false);
    }
  }
}