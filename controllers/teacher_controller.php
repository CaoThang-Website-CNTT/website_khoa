<?php
class TeacherController
{
  private $_educationService;

  public function __construct(EducationRepositoryInterface $educationService)
  {
    $this->_educationService = $educationService;
  }

  public function index()
  {
    $teachers = $this->_educationService->getAllTeachers();
    return $teachers;
  }

  public function show($id)
  {
    $teacher = $this->_educationService->getTeacherById($id);
    return $teacher;
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

  public function destroy($id)
  {
    return $this->_educationService->deleteTeacher($id);
  }
}