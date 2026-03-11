<?php

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

  public function show($id)
  {
    $student = $this->_educationService->getStudentById($id);
    return $student;
  }

  public function store(array $data)
  {
    $newId = $this->_educationService->createStudent($data);
    return $newId;
  }

  public function update($id, array $data)
  {
    $isSuccess = $this->_educationService->updateStudent($id, $data);
    return $isSuccess;
  }

  public function destroy($id)
  {
    return $this->_educationService->deleteStudent($id);
  }
}