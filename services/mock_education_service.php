<?php
namespace App\Services;

require_once __DIR__ . '/../models/account.php';
require_once __DIR__ . '/../models/student.php';
require_once __DIR__ . '/../models/teacher.php';
require_once __DIR__ . '/../models/classroom.php';
use App\Models\Account;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Classroom;

interface EducationRepositoryInterface
{
  // Student
  /** @return Student[] */
  public function getAllStudents(int $pageTo, int $limit = 15): array;
  public function getStudentById(int $id): ?Student;
  public function createStudent(Student $student, string $rawPassword): int;
  public function updateStudent(int $id, Student $student): bool;
  public function deleteStudent(int $id): bool;

  // Teacher
  /** @return Teacher[] */
  public function getAllTeachers(): array;
  public function getTeacherById(int $id): ?Teacher;
  public function createTeacher(Teacher $teacher, string $rawPassword): int;
  public function updateTeacher(int $id, Teacher $teacher): bool;
  public function deleteTeacher(int $id): bool;

  // Classroom
  /** @return Classroom[] */
  public function getAllClassrooms(): array;
}

class MockEducationService implements EducationRepositoryInterface
{
  /** @var array<int, Account> */
  private array $accounts = [];
  /** @var array<int, Student> */
  private array $students = [];
  /** @var array<int, Teacher> */
  private array $teachers = [];
  /** @var array<int, Classroom> */
  private array $classes = [];
  private int $lastId = 0;

  public function __construct()
  {
    $this->seedData();
  }

  private function seedData(): void
  {
    // Seed Teachers
    for ($i = 1; $i <= 10; $i++) {
      // Create an account
      $account = new Account(
        id: $i,
        email: "teacher$i@caothang.edu.vn",
        password: password_hash("123456", PASSWORD_DEFAULT),
        role: 'teacher',
        deleted_at: null
      );
      $this->accounts[$i] = clone $account;

      // Create a teacher linked to that account
      $this->teachers[$i] = new Teacher(
        account_id: $i,
        fullname: "Giảng viên $i",
        department: 'CNTT',
        account: clone $account,
        phone: "012345678$i",
        gender: ($i % 2 == 0) ? 'Nữ' : 'Nam',
        dob: "1980-01-0$i",
        title: ($i % 3 == 0) ? 'Phó Giáo sư' : 'Giảng viên',
        start_date: "2010-09-01",
      );
    }

    // Seed Students
    for ($i = 11; $i <= 40; $i++) {
      // Create an account
      $account = new Account(
        id: $i,
        email: "03062310$i@caothang.edu.vn",
        password: password_hash("123456", PASSWORD_DEFAULT),
        role: 'student',
        deleted_at: null
      );
      $this->accounts[$i] = clone $account;

      $this->students[$i] = new Student(
        account_id: $i,
        student_id: "03062310$i",
        fullname: "Sinh viên $i",
        account: clone $account,
        dob: "2000-01-0" . ($i - 10),
        phone: "012345678$i",
        gender: ($i % 2 == 0) ? 'Nữ' : 'Nam',
        class_id: "1",
      );
    }

    $this->lastId = 40;

    // Seed Classrooms
    $this->classes[1] = new Classroom(
      id: 1,
      name: "CDTH23WebC",
      description: "Lớp Lập trình web cao đẳng khóa 2023"
    );
    $this->classes[2] = new Classroom(
      id: 2,
      name: "CDTH23DiDongD",
      description: "Lớp Lập trình web cao đẳng khóa 2023"
    );
    $this->classes[3] = new Classroom(
      id: 3,
      name: "CDTH23MangA",
      description: "Lớp Lập trình web cao đẳng khóa 2023"
    );
  }

  // --- STUDENT METHODS ---

  public function getAllStudents(int $pageTo, int $limit = 15): array
  {
    $activeStudents = array_filter($this->students, function (Student $s) {
      return $s->account !== null && !$s->account->deleted_at;
    });

    $currentPage = max(1, $pageTo);
    $offset = ($currentPage - 1) * $limit;

    return array_slice($activeStudents, $offset, $limit);
  }

  public function getStudentById(int $id): ?Student
  {
    $match = array_filter($this->students ?? [], fn($s) => $s->account_id === $id);

    // reset() gets the first element of the filtered array, or false if empty
    return reset($match) ?: null;
  }

  public function createStudent(Student $student, string $rawPassword): int
  {
    $accId = ++$this->lastId;
    $now = date('Y-m-d H:i:s');

    // 1. Setup Account Model
    $account = $student->account ?? new Account(
      id: $accId,
      email: $student->student_id . "@caothang.edu.vn",
      role: 'student',
      deleted_at: null
    );
    $account->id = $accId;
    $account->email = $account->email ?: "student{$accId}@example.com"; // Fallback if empty
    $account->passwordHash = password_hash($rawPassword, PASSWORD_DEFAULT);
    $account->role = 'student';
    $account->isDeleted = false;
    $account->createdAt = $now;
    $account->updatedAt = $now;

    $this->accounts[$accId] = clone $account;

    // 2. Setup Student Model
    $student->accountId = $accId;
    $student->account = clone $account; // Link the relationship

    $this->students[$accId] = clone $student;

    return $accId;
  }

  public function updateStudent(int $id, Student $updatedStudent): bool
  {
    if (!isset($this->students[$id]) || !isset($this->accounts[$id])) {
      return false;
    }

    // Ensure ID integrity
    $updatedStudent->accountId = $id;

    // Touch the account timestamp
    $this->accounts[$id]->updatedAt = date('Y-m-d H:i:s');

    // Re-link the account to prevent the relationship from breaking
    $updatedStudent->account = clone $this->accounts[$id];

    // Overwrite existing state
    $this->students[$id] = clone $updatedStudent;
    return true;
  }

  public function deleteStudent(int $id): bool
  {
    if (isset($this->accounts[$id])) {
      $this->accounts[$id]->isDeleted = true;
      $this->accounts[$id]->deletedAt = date('Y-m-d H:i:s');

      // Sync the change to the nested object if someone fetches the student directly
      if (isset($this->students[$id]) && $this->students[$id]->account !== null) {
        $this->students[$id]->account->isDeleted = true;
      }
      return true;
    }
    return false;
  }

  // --- TEACHER METHODS ---
  // (These follow the exact same object-oriented pattern as the Student methods)

  public function getAllTeachers(): array
  {
    return array_filter($this->teachers, function (Teacher $t) {
      return $t->account !== null && !$t->account->deleted_at; // Check the nested account's deleted status
    });
  }

  public function getTeacherById(int $id): ?Teacher
  {
    return $this->teachers[$id] ?? null;
  }

  public function createTeacher(Teacher $teacher, string $rawPassword): int
  {
    $accId = ++$this->lastId;
    $now = date('Y-m-d H:i:s');
    $teacherEmail = preg_replace('/\s+/', '.', strtolower($teacher->fullname)) . "@caothang.edu.vn";

    $account = $teacher->account ?? new Account(
      id: $accId,
      email: $teacherEmail,
      role: 'teacher',
      deleted_at: null,
    );
    $account->id = $accId;
    $account->passwordHash = password_hash($rawPassword, PASSWORD_DEFAULT);
    $account->role = 'teacher';
    $account->deleted_at = null;
    $account->created_at = $now;
    $account->updated_at = $now;

    $this->accounts[$accId] = clone $account;

    $teacher->accountId = $accId;
    $teacher->account = clone $account;

    $this->teachers[$accId] = clone $teacher;

    return $accId;
  }

  public function updateTeacher(int $id, Teacher $updatedTeacher): bool
  {
    if (!isset($this->teachers[$id]) || !isset($this->accounts[$id])) {
      return false;
    }

    $updatedTeacher->accountId = $id;
    $this->accounts[$id]->updatedAt = date('Y-m-d H:i:s');
    $updatedTeacher->account = clone $this->accounts[$id];

    $this->teachers[$id] = clone $updatedTeacher;
    return true;
  }

  public function deleteTeacher(int $id): bool
  {
    if (isset($this->accounts[$id])) {
      $this->accounts[$id]->isDeleted = true;
      $this->accounts[$id]->deletedAt = date('Y-m-d H:i:s');

      if (isset($this->teachers[$id]) && $this->teachers[$id]->account !== null) {
        $this->teachers[$id]->account->isDeleted = true;
      }
      return true;
    }
    return false;
  }

  public function getAllClassrooms(): array
  {
    return array_values($this->classes);
  }
}