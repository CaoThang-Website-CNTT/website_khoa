<?php

namespace App\Services;

require_once BASE_PATH . '/models/student.php';
require_once BASE_PATH . '/models/account.php';
require_once BASE_PATH . '/models/classroom.php';
require_once BASE_PATH . '/stores/student_store.php';
require_once BASE_PATH . '/stores/account_store.php';
require_once BASE_PATH . '/stores/classroom_store.php';
require_once BASE_PATH . '/services/account_service.php';
require_once BASE_PATH . '/includes/core/pageable.php';

use App\Models\{Student, Classroom};
use App\Stores\{StudentStore, AccountStore, ClassroomStore};
use App\Core\Pageable;
use Database;

interface IStudentService
{
  /** @return Pageable */
  public function getStudents(int $page, int $limit = 15): Pageable;
  public function isStudentIdUnique(string $studentId): bool;
  public function createStudent(array $data): ?Student;
  public function getStudentById(int $id): ?Student;
  /** @return Classroom[] */
  public function getAllClassrooms(): array;
  public function updateStudent(int $id, array $data): bool;
  public function deleteStudent(int $id): bool;
}

class StudentService implements IStudentService
{
  private StudentStore $_studentStore;
  private AccountStore $_accountStore;
  private ClassroomStore $_classroomStore;

  public function __construct(
    StudentStore $studentStore,
    AccountStore $accountStore,
    ClassroomStore $classroomStore,
  ) {
    $this->_studentStore = $studentStore;
    $this->_accountStore = $accountStore;
    $this->_classroomStore = $classroomStore;
  }

  /** @return Pageable */
  public function getStudents(int $page, int $limit = 15): Pageable
  {
    $students = $this->_studentStore->getPaginated($page, $limit);

    // Eager load classrooms, accounts
    $classroomIds = array_filter(array_column($students, 'classroom_id'));
    $accountIds = array_filter(array_column($students, 'account_id'));

    $classrooms = $this->_classroomStore->getByIds($classroomIds);
    $accounts = $this->_accountStore->getByIds($accountIds);

    $classroomMap = array_column($classrooms, null, 'id');
    $accountMap = array_column($accounts, null, 'id');

    foreach ($students as $student) {
      if ($student->classroom_id && isset($classroomMap[$student->classroom_id])) {
        $student->classroom = $classroomMap[$student->classroom_id];
      }
      if ($student->account_id && isset($accountMap[$student->account_id])) {
        $student->account = $accountMap[$student->account_id];
      }
    }

    $total = $this->_studentStore->getTotalCount();
    return new Pageable($students, $total, $limit, $page);
  }

  public function isStudentIdUnique(string $studentId): bool
  {
    return $this->_studentStore->isStudentIdUnique($studentId);
  }

  public function createStudent(array $data): ?Student
  {
    return Database::getInstance()->transaction(function () use ($data) {
      // Check mail
      // Nếu email tồn tại, kiểm tra unique nó
      // Còn không thì tạo MSSV@caothang.edu.vn
      if (isset($data['email'])) {
        if (!$this->_accountStore->isEmailUnique($data['email'])) {
          throw new \Exception('Email này đã tồn tại trong hệ thống.');
        }
      } else {
        $data['email'] = $data['student_id'] . '@caothang.edu.vn';
      }

      // Create account
      $accountId = $this->_accountStore->create($data['email'], $data['national_id'], 'student');
      if (!$accountId) {
        throw new \Exception('Tạo tài khoản thất bại');
      }

      $major = $this->_classroomStore->getMajorById($data['classroom_id'])->id;
      if (!$major) {
        throw new \Exception('Lớp học không hợp lệ. Không tồn tại ngành học cho lớp này.');
      }

      // Create student
      // 'full_name' => ['required', 'max:255'],
      //   'dob' => ['required', 'date'],
      //   'birth_place' => ['max:255'],
      //   'national_id' => ['required', 'size:12'],
      //   'gender' => ['required', 'in:male,female'],
      //   'student_id' => ['required', 'size:10'],
      //   'phone' => ['required', 'phone', 'max:15'],
      //   'address' => ['required'],
      //   'classroom_id' => ['required'],
      //   'notes' => ['nullable'],
      //   'status' => ['required', 'in:Đang học,Đã tốt nghiệp,Tạm ngưng,Thôi học']
      $student = new Student(
        account_id: $accountId,
        student_id: $data['student_id'],
        full_name: $data['full_name'],
        gender: $data['gender'],
        dob: $data['dob'],
        national_id: $data['national_id'],
        phone: $data['phone'],
        address: $data['address'],
        classroom_id: $data['classroom_id'],
        birth_place: $data['birth_place'],
        notes: $data['notes'] ?? null,
        status: $data['status'],
        major: $major,
      );

      return $this->_studentStore->create($student);
    });
  }

  public function getStudentById(int $id): ?Student
  {
    $student = $this->_studentStore->getById($id);
    if (!$student) {
      return null;
    }

    // Eager load classroom, account
    if ($student->classroom_id) {
      $student->classroom = $this->_classroomStore->getById($student->classroom_id);
    }
    if ($student->account_id) {
      $student->account = $this->_accountStore->getById($student->account_id);
    }

    return $student;
  }

  /** @return Classroom[] */
  public function getAllClassrooms(): array
  {
    return $this->_classroomStore->getAll();
  }

  public function updateStudent(int $id, array $data): bool
  {
    $student = $this->_studentStore->getById($id);
    if (!$student) {
      return false;
    }

    $student->full_name = $data['full_name'] ?? $student->full_name;
    $student->gender = $data['gender'] ?? $student->gender;
    $student->dob = $data['dob'] ?? $student->dob;
    $student->phone = $data['phone'] ?? $student->phone;
    $student->address = $data['address'] ?? $student->address;
    $student->major = $data['major'] ?? $student->major;
    $student->status = $data['status'] ?? $student->status;
    $student->classroom_id = $data['classroom_id'] ?? $student->classroom_id;

    return $this->_studentStore->update($student);
  }

  public function deleteStudent(int $id): bool
  {
    return $this->_studentStore->softDelete($id);
  }
}