<?php

require_once __DIR__ . '/../utils/request_validator.php';
require_once __DIR__ . '/../models/classroom.php';
require_once __DIR__ . '/../includes/response.php';

use App\Services\EducationRepositoryInterface;

class MajorController
{
  private $_educationService;

  public function __construct(EducationRepositoryInterface $educationService)
  {
    $this->_educationService = $educationService;
  }

  public function getMajorsByLevel()
  {
    $level = $_GET['level'] ?? '';

    if (!$level || !is_string($level)) {
      http_response_code(400);
      jsonResponse([], 'Bậc học không hợp lệ', false);
    }

    $majors = $this->_educationService->getMajorsByLevel($level);

    jsonResponse($majors, 'Thành công');
  }
  public function store()
  {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['full_name']) || empty($data['short_name']) || empty($data['level'])) {
      http_response_code(400);
      jsonResponse([], 'Vui lòng nhập đủ thông tin ngành (tên, viết tắt, bậc học)', false);
      return;
    }

    try {
      $id = $this->_educationService->createMajor($data);
      $data['id'] = $id;
      jsonResponse($data, 'Thêm ngành thành công');
    } catch (Exception $e) {
      http_response_code(500);
      jsonResponse([], 'Lỗi hệ thống: ' . $e->getMessage(), false);
    }
  }
}