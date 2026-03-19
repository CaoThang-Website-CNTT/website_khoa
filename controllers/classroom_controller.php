<?php

require_once __DIR__ . '/../utils/request_validator.php';
require_once __DIR__ . '/../models/classroom.php';

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
      die("Không thấy lớp học với id: $id");
    }
    $professions = $this->_educationService->getAllProfessions();
    $majorsOfProfession = $this->_educationService->getMajorsByProfessionId($classroom->profession_id);
    ob_start();
    require_once __DIR__ . '/../templates/pages/admin/classroom_detail.php';
    $content = ob_get_clean();
    require_once __DIR__ . '/../templates/layouts/dashboard_layout.php';
  }
}