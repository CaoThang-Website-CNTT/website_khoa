<?php
interface EducationRepositoryInterface
{
  public function getAllStudents();
  public function getStudentById($id);
  public function createStudent(array $data);
  public function updateStudent($id, array $data);
  public function deleteStudent($id);

  public function getAllTeachers();
  public function getTeacherById($id);
  public function createTeacher(array $data);
  public function updateTeacher($id, array $data);
  public function deleteTeacher($id);
}
