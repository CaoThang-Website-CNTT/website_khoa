<?php

namespace App\Controllers;

require_once BASE_PATH . '/utils/request_validator.php';
require_once BASE_PATH . '/models/student.php';

use App\Core\Controller;
use App\Services\EducationService;

class UserController extends Controller
{
  private $_educationService;

  public function __construct(EducationService $educationService)
  {
    $this->_educationService = $educationService;
  }

  public function index()
  {
    $students = $this->_educationService->getAllStudents(1);
    $teachers = $this->_educationService->getAllTeachers(1);

    $this->render("admin/users/index", [
      'students' => $students,
      'teachers' => $teachers,
    ], layout: "dashboard_layout");
  }
}
