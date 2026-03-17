<?php

require_once __DIR__ . '/../utils/request_validator.php';
require_once __DIR__ . '/../models/student.php';

use App\Services\EducationRepositoryInterface;

class UserController
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

    $paginationData = $this->_educationService->getStudents($currentPage, $limit);

    $students = $paginationData['data'];
    $totalPages = $paginationData['last_page'];

    $teachers = $this->_educationService->getAllTeachers(1);
    ob_start();
    require_once __DIR__ . '/../templates/pages/admin/dashboard_user.php';
    $content = ob_get_clean();
    require_once __DIR__ . '/../templates/layouts/dashboard_layout.php';
  }
}
