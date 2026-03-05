<?php
class StudentController
{
  private $_educationService;

  public function __construct(EducationRepositoryInterface $educationService)
  {
    $this->_educationService = $educationService;
  }

  public function index()
  {
    $students = $this->_educationService->getAllStudents();
    return $students;
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