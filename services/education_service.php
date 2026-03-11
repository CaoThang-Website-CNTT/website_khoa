<?php

namespace App\Services;

require_once __DIR__ . '/../models/account.php';
require_once __DIR__ . '/../models/student.php';
require_once __DIR__ . '/../models/teacher.php';
require_once __DIR__ . '/../models/classroom.php';
require_once __DIR__ . '/../db/database.php';

use App\Models\Account;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Classroom;
use Database;
use PDO;


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

class EducationService implements EducationRepositoryInterface
{
  private $db;

  public function __construct()
  {
    $this->db = Database::getInstance()->getConnection();
  }
  // --- STUDENT METHODS ---

  public function getAllStudents(int $pageTo, int $limit = 15): array
  {
    $currentPage = max(1, $pageTo);
    $offset = ($currentPage - 1) * $limit;

    $sql = "SELECT 
                s.*, 
                a.id AS acc_id, a.email as acc_email, a.role AS acc_role, 
                a.created_at AS acc_created_at, a.updated_at AS acc_updated_at, a.deleted_at AS acc_deleted_at
            FROM `students` s
            INNER JOIN `accounts` a ON s.`account_id` = a.`id`
            WHERE a.`deleted_at` IS NULL 
            LIMIT :limit OFFSET :offset";
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
    $stmt->execute();

    return array_map(
      fn($row) => Student::fromArray($row),
      $stmt->fetchAll(PDO::FETCH_ASSOC)
      );
    }

    return $students;
  }


  //TODO: Implemt toàn bộ các method còn lại
  public function getStudentById(int $id): ?Student
  {
    $sql = "SELECT s.*, a.id AS acc_id, a.email AS acc_email, a.role AS acc_role, 
                   a.created_at AS acc_created_at, a.updated_at AS acc_updated_at, a.deleted_at AS acc_deleted_at
            FROM `students` s 
            INNER JOIN `accounts` a ON s.`account_id` = a.`id`
            WHERE s.`account_id` = :id AND a.`deleted_at` IS NULL";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? Student::fromArray($row) : null;
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
    $account->password_hash = password_hash($rawPassword, PASSWORD_DEFAULT);
    $account->role = 'student';
    $account->created_at = $now;
    $account->updated_at = $now;

    $this->accounts[$accId] = clone $account;

    // 2. Setup Student Model
    $student->account_id = $accId;
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
    $updatedStudent->account_id = $id;

    // Touch the account timestamp
    $this->accounts[$id]->updated_at = date('Y-m-d H:i:s');

    // Re-link the account to prevent the relationship from breaking
    $updatedStudent->account = clone $this->accounts[$id];

    // Overwrite existing state
    $this->students[$id] = clone $updatedStudent;
    return true;
  }

  public function deleteStudent(int $id): bool
  {
    if (isset($this->accounts[$id])) {
      $this->accounts[$id]->deleted_at = date('Y-m-d H:i:s');

      // Sync the change to the nested object if someone fetches the student directly
      if (isset($this->students[$id]) && $this->students[$id]->account !== null) {
        $this->students[$id]->account->deleted_at = $this->accounts[$id]->deleted_at;
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
    $account->password_hash = password_hash($rawPassword, PASSWORD_DEFAULT);
    $account->role = 'teacher';
    $account->deleted_at = null;
    $account->created_at = $now;
    $account->updated_at = $now;

    $this->accounts[$accId] = clone $account;

    $teacher->account_id = $accId;
    $teacher->account = clone $account;

    $this->teachers[$accId] = clone $teacher;

    return $accId;
  }

  public function updateTeacher(int $id, Teacher $updatedTeacher): bool
  {
    if (!isset($this->teachers[$id]) || !isset($this->accounts[$id])) {
      return false;
    }

    $updatedTeacher->account_id = $id;
    $this->accounts[$id]->updated_at = date('Y-m-d H:i:s');
    $updatedTeacher->account = clone $this->accounts[$id];

    $this->teachers[$id] = clone $updatedTeacher;
    return true;
  }

  public function deleteTeacher(int $id): bool
  {
    if (isset($this->accounts[$id])) {
      $this->accounts[$id]->deleted_at = date('Y-m-d H:i:s');

      if (isset($this->teachers[$id]) && $this->teachers[$id]->account !== null) {
        $this->teachers[$id]->account->deleted_at = $this->accounts[$id]->deleted_at;
      }
      return true;
    }
    return false;
  }

  public function getAllClassrooms(): array
  {
    $sql = "SELECT * FROM `classrooms`";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    return array_map(
      fn($row) => Classroom::fromArray($row),
      $stmt->fetchAll((PDO::FETCH_ASSOC))
    );
  }
}