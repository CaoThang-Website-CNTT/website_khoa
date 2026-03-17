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

    $activeTab = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $studentPage = isset($_GET['student_page']) ? (int)$_GET['student_page'] : 1;
    $teacherPage = isset($_GET['teacher_page']) ? (int)$_GET['teacher_page'] : 1;
    $limit = 15;

    $studentData = $this->_educationService->getStudents($studentPage, $limit);
    $teacherData = $this->_educationService->getTeachers($teacherPage, $limit);

    $students = $studentData['data'];
    $studentTotalPages = $studentData['last_page'];
    $studentTotalRows = $studentData['total_rows'];

    $teachers = $teacherData['data'];
    $teacherTotalPages = $teacherData['last_page'];
    $teacherTotalRows = $teacherData['total_rows'];

    $studentBaseUrl = "?usersTabs=students&teacher_page={$teacherPage}&student_page=";
    $teacherBaseUrl = "?usersTabs=teachers&student_page={$studentPage}&teacher_page=";
    ob_start();
    require_once __DIR__ . '/../templates/pages/admin/dashboard_user.php';
    $content = ob_get_clean();
    require_once __DIR__ . '/../templates/layouts/dashboard_layout.php';
  }
}
