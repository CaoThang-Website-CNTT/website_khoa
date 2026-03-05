<?php

class MockEducationService implements EducationRepositoryInterface
{
  private $accounts = [];
  private $students = [];
  private $teachers = [];
  private $lastId = 0;

  public function __construct()
  {
    $this->seedData();
  }

  private function seedData()
  {
    for ($i = 1; $i <= 10; $i++) {
      $id = $i;
      $this->accounts[$id] = ['id' => $id, 'role' => 'teacher', 'is_deleted' => false];
      $this->teachers[$id] = ['account_id' => $id, 'full_name' => "Giảng viên $i", 'department' => 'CNTT'];
    }

    for ($i = 11; $i <= 40; $i++) {
      $id = $i;
      $this->accounts[$id] = ['id' => $id, 'role' => 'student', 'is_deleted' => false];
      $this->students[$id] = ['account_id' => $id, 'student_code' => "SV$id", 'full_name' => "Sinh viên $i"];
    }

    $this->lastId = 40;
  }
  public function getAllStudents()
  {
    return array_filter($this->students, function ($s) {
      return !$this->accounts[$s['account_id']]['is_deleted'];
    });
  }

  public function getStudentById($id)
  {
    return $this->students[$id] ?? null;
  }

  public function createStudent(array $data)
  {
    $accId = ++$this->lastId;

    $this->accounts[$accId] = [
      'id' => $accId,
      'email' => $data['email'],
      'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
      'role' => 'student',
      'is_deleted' => false,
      'created_at' => date('Y-m-d H:i:s'),
      'updated_at' => date('Y-m-d H:i:s'),
      'deleted_at' => null
    ];

    $this->students[$accId] = [
      'account_id' => $accId,
      'student_code' => $data['student_code'],
      'full_name' => $data['full_name'],
      'gender' => $data['gender'],
      'dob' => $data['dob'],
      'phone' => $data['phone'],
      'class_id' => $data['class_id'],
      'major' => $data['major']
    ];

    return $accId;
  }

  public function updateStudent($id, array $data)
  {
    if (!isset($this->students[$id])) return false;

    $this->students[$id] = array_merge($this->students[$id], $data);
    $this->accounts[$id]['updated_at'] = date('Y-m-d H:i:s');
    return true;
  }

  public function deleteStudent($id)
  {
    if (isset($this->accounts[$id])) {
      $this->accounts[$id]['is_deleted'] = true;
      $this->accounts[$id]['deleted_at'] = date('Y-m-d H:i:s');
      return true;
    }
    return false;
  }


  // --- teacher ---
  public function getAllTeachers()
  {
    return array_filter($this->teachers, function ($t) {
      return !$this->accounts[$t['account_id']]['is_deleted'];
    });
  }
  public function getTeacherById($id)
  {
    return $this->teachers[$id] ?? null;
  }

  public function createTeacher(array $data)
  {
    $accId = ++$this->lastId;
    $this->accounts[$accId] = [
      'id' => $accId,
      'email' => $data['email'],
      'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
      'role' => 'teacher',
      'is_deleted' => false,
      'created_at' => date('Y-m-d H:i:s'),
      'updated_at' => date('Y-m-d H:i:s')
    ];

    $this->teachers[$accId] = [
      'account_id' => $accId,
      'full_name' => $data['full_name'],
      'gender' => $data['gender'],
      'dob' => $data['dob'],
      'phone' => $data['phone'],
      'degree' => $data['degree'],
      'title' => $data['title'],
      'department' => $data['department'],
      'start_date' => $data['start_date']
    ];

    return $accId;
  }

  public function updateTeacher($id, array $data)
  {
    if (!isset($this->teachers[$id])) return false;

    $this->teachers[$id] = array_merge($this->teachers[$id], $data);
    $this->accounts[$id]['updated_at'] = date('Y-m-d H:i:s');
    return true;
  }

  public function deleteTeacher($id)
  {
    if (isset($this->accounts[$id])) {
      $this->accounts[$id]['is_deleted'] = true;
      $this->accounts[$id]['deleted_at'] = date('Y-m-d H:i:s');
      return true;
    }
    return false;
  }
}