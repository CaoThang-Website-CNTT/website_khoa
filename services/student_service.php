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
use App\Services\AccountService;
use App\Core\Pageable;

interface IStudentService
{
  /** @return Pageable */
  public function getStudentsPaginated(int $page, int $limit = 15): Pageable;

  public function isStudentIdUnique(string $studentId): bool;

  public function createStudent(array $data, string $password): int;

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
  private AccountService $_accountService;

  public function __construct(
    StudentStore $studentStore,
    AccountStore $accountStore,
    ClassroomStore $classroomStore,
    AccountService $accountService
  ) {
    $this->_studentStore = $studentStore;
    $this->_accountStore = $accountStore;
    $this->_classroomStore = $classroomStore;
    $this->_accountService = $accountService;
  }

  /** @return Pageable */
  public function getStudentsPaginated(int $page, int $limit = 15): Pageable
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

  public function createStudent(array $data, string $password): int
  {
    // Create account
    $accountId = $this->_accountService->createAccount($data['email'], $password, 'student');
    if (!$accountId) {
      throw new \Exception('Failed to create account');
    }

    // Create student
    $student = new Student();
    $student->account_id = $accountId;
    $student->student_id = $data['student_id'];
    $student->classroom_id = $data['classroom_id'] ?? null;
    $student->full_name = $data['full_name'];
    $student->gender = $data['gender'];
    $student->dob = $data['dob'] ?? null;
    $student->phone = $data['phone'] ?? null;
    $student->address = $data['address'] ?? null;
    $student->major = $data['major'] ?? null;
    $student->status = $data['status'] ?? 'Đang học';

    return $this->_studentStore->create($student);
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