<?php

namespace App\Services;

require_once BASE_PATH . '/models/account.php';
require_once BASE_PATH . '/models/student.php';
require_once BASE_PATH . '/models/teacher.php';
require_once BASE_PATH . '/models/classroom.php';
require_once BASE_PATH . '/db/database.php';

use App\Models\Student;
use App\Models\Teacher;
use App\Models\Classroom;
use Database;
use PDO;


interface IEducationRepository
{
  // Student
  /** @return Student[] */
  public function getAllStudents(int $pageTo, int $limit = 15, string $sortCol = 'account_id', string $sortDir = 'DESC', string $search = ''): array;
  public function getStudentById(int $id): ?Student;
  public function createStudent(array $student, string $rawPassword): int;
  public function updateStudent(int $id, Student $student): bool;
  public function deleteStudent(int $id): bool;
  public function importStudents(array $students): void;
  public function getTotalStudentsCount(string $search = ''): int;

  // Teacher
  /** @return Teacher[] */
  public function getAllTeachers(int $pageTo, int $limit = 15): array;
  public function getTeacherById(int $id): ?Teacher;
  public function createTeacher(array $teacher, string $rawPassword): int;
  public function updateTeacher(int $id, Teacher $teacher): bool;
  public function deleteTeacher(int $id): bool;
  public function getTotalTeachersCount(): int;


  // Classroom
  /** @return Classroom[] */
  public function getAllClassrooms(): array;
  public function createClassroom(array $classroom): int;

  // Helper
  public function isStudentIdUnique(string $studentId, ?int $excludeAccountId = null): bool;
  public function isEmailUnique(string $email, ?int $excludeAccountId = null): bool;
}

class EducationService implements IEducationRepository
{
  private $db;

  public function __construct()
  {
    $this->db = Database::getInstance()->getConnection();
  }

  private function createAccount(string $email, string $rawPassword, string $role): int
  {
    $now = date('Y-m-d H:i:s');
    $sql = "INSERT INTO `accounts` (email, password_hash, role, created_at, updated_at) 
                VALUES (:email, :password, :role, :created, :updated)";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      ':email' => $email,
      ':password' => password_hash($rawPassword, PASSWORD_DEFAULT),
      ':role' => $role,
      ':created' => $now,
      ':updated' => $now
    ]);
    return (int) $this->db->lastInsertId();
  }

  private function touchAccount(int $accountId): void
  {
    $sql = "UPDATE `accounts` SET updated_at = NOW() WHERE id = :id";
    $this->db->prepare($sql)->execute([':id' => $accountId]);
  }

  private function softDeleteAccount(int $accountId): bool
  {
    $sql = "UPDATE `accounts` SET deleted_at = NOW() WHERE id = :id";
    return $this->db->prepare($sql)->execute([':id' => $accountId]);
  }

  public function isStudentIdUnique(string $studentId, ?int $excludeAccountId = null): bool
  {
    $sql = "SELECT COUNT(*) FROM students WHERE student_id = :student_id";
    $params = [':student_id' => $studentId];

    if ($excludeAccountId) {
      $sql .= " AND account_id != :exclude_id";
      $params[':exclude_id'] = $excludeAccountId;
    }

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn() == 0;
  }

  public function isEmailUnique(string $email, ?int $excludeAccountId = null): bool
  {
    $sql = "SELECT COUNT(*) FROM accounts WHERE email = :email AND deleted_at IS NULL";
    $params = [':email' => $email];

    if ($excludeAccountId) {
      $sql .= " AND id != :exclude_id";
      $params[':exclude_id'] = $excludeAccountId;
    }

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn() == 0;
  }
  private function buildStudentFilterQuery(string &$sql, array &$params, string $search, array $filters)
  {
    if (!empty($search)) {
      $sql .= " AND (s.full_name LIKE :s1 OR s.student_id LIKE :s2 OR a.email LIKE :s3)";
      $term = "%$search%";
      $params[':s1'] = $term;
      $params[':s2'] = $term;
      $params[':s3'] = $term;
    }

    // Lọc theo Lớp học (Multi-select)
    if (!empty($filters['classroom_id']) && is_array($filters['classroom_id'])) {
      $cKeys = [];
      foreach ($filters['classroom_id'] as $i => $c) {
        $key = ":class_$i";
        $cKeys[] = $key;
        $params[$key] = $c;
      }
      $sql .= " AND s.classroom_id IN (" . implode(',', $cKeys) . ")";
    }
  }
  // ===================================
  // Student Methods
  // ===================================
  public function getTotalStudentsCount(string $search = '', array $filters = []): int
  {
    $sql = "SELECT COUNT(s.account_id) FROM `students` s INNER JOIN `accounts` a ON s.`account_id` = a.`id` WHERE a.`deleted_at` IS NULL";
    $params = [];

    $this->buildStudentFilterQuery($sql, $params, $search, $filters);

    $stmt = $this->db->prepare($sql);
    foreach ($params as $key => $value) {
      $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    return (int) $stmt->fetchColumn();
  }

  public function getAllStudents(int $pageTo, int $limit = 15, string $sortCol = 'account_id', string $sortDir = 'ASC', string $search = '', array $filters = []): array
  {
    $offset = (max(1, $pageTo) - 1) * $limit;
    $allowedSortColumns = [
      'account_id' => 's.account_id',
      'full_name' => 's.full_name',
      'email' => 'a.email',
      'gender' => 's.gender',
      'dob' => 's.dob',
      'phone' => 's.phone'
    ];
    $orderBy = $allowedSortColumns[$sortCol] ?? 's.account_id';
    $direction = strtoupper($sortDir) === 'ASC' ? 'ASC' : 'DESC';

    $sql = "SELECT s.*, 
              a.id AS acc_id, 
              a.email as acc_email, 
              c.id AS cla_id, 
              c.short_name AS cla_short_name 
            FROM `students` s 
            INNER JOIN `accounts` a ON s.`account_id` = a.`id` 
            LEFT JOIN `classrooms` c ON s.`classroom_id` = c.`id`
            WHERE a.`deleted_at` IS NULL AND c.`deleted_at` IS NULL";
    $params = [];

    $this->buildStudentFilterQuery($sql, $params, $search, $filters);

    $sql .= " ORDER BY {$orderBy} {$direction} LIMIT :limit OFFSET :offset";

    $stmt = $this->db->prepare($sql);
    foreach ($params as $key => $value) {
      $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return array_map(fn($row) => Student::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

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

  public function createStudent(array $student, string $rawPassword): int
  {
    return Database::getInstance()->transaction(function () use ($student, $rawPassword): int {
      $email = $student['student_id'] . "@caothang.edu.vn";
      $accId = $this->createAccount($email, $rawPassword, 'student');

      $sql = "INSERT INTO `students` 
                    (account_id, student_id, full_name, gender, dob, phone, classroom_id, major, birth_place) 
                VALUES 
                    (:acc_id, :code, :name, :gender, :dob, :phone, :classroom_id, :major, :birth_place)";

      $this->db->prepare($sql)->execute([
        ':acc_id' => $accId,
        ':code' => $student['student_id'],
        ':name' => $student['full_name'],
        ':gender' => $student['gender'],
        ':dob' => $student['dob'],
        ':phone' => $student['phone'],
        ':classroom_id' => $student['classroom_id'],
        ':major' => $student['major'],
        ':birth_place' => $student['birth_place'],
      ]);

      return $accId;
    });
  }

  public function updateStudent(int $id, Student $student): bool
  {
    return Database::getInstance()->transaction(function () use ($id, $student): bool {
      $this->touchAccount($id);

      $sql = "UPDATE `students` SET 
                full_name    = :name,
                gender       = :gender,
                dob          = :dob,
                phone        = :phone,
                classroom_id = :classroom_id,
                major        = :major,
                birth_place  = :birth_place
              WHERE account_id = :id";

      return $this->db->prepare($sql)->execute([
        ':name' => $student->full_name,
        ':gender' => $student->gender,
        ':dob' => $student->dob,
        ':phone' => $student->phone,
        ':classroom_id' => $student->classroom_id,
        ':major' => $student->major,
        ':birth_place' => $student->birth_place,
        ':id' => $id,
      ]);
    });
  }

  public function deleteStudent(int $id): bool
  {
    return $this->softDeleteAccount($id);
  }

  public function importStudents(array $rows): void
  {
    Database::getInstance()->transaction(function () use ($rows): void {
      $sql = "INSERT INTO `students` 
                    (account_id, student_id, full_name, gender, dob, phone, classroom_id, major, birth_place)
                VALUES 
                    (:acc_id, :code, :name, :gender, :dob, :phone, :classroom_id, :major, :birth_place)";
      $stmt = $this->db->prepare($sql);

      foreach ($rows as $row) {
        $email = $row['student_id'] . "@caothang.edu.vn";
        $accId = $this->createAccount($email, $row['password'] ?? $row['student_id'], 'student');

        $stmt->execute([
          ':acc_id' => $accId,
          ':code' => $row['student_id'],
          ':name' => $row['full_name'],
          ':gender' => $row['gender'],
          ':dob' => $row['dob'],
          ':phone' => $row['phone'],
          ':classroom_id' => $row['classroom_id'],
          ':major' => $row['major'],
          ':birth_place' => $row['birth_place'],
        ]);
      }
    });
  }

  // ===================================
  // Teacher Methods
  // ===================================
  public function getTotalTeachersCount(): int
  {
    $sql = "SELECT COUNT(t.account_id) 
            FROM `teachers` t
            INNER JOIN `accounts` a ON t.`account_id` = a.`id`
            WHERE a.`deleted_at` IS NULL";

    $stmt = $this->db->query($sql);

    return (int) $stmt->fetchColumn();
  }

  public function getAllTeachers(int $pageTo, int $limit = 15): array
  {
    $offset = (max(1, $pageTo) - 1) * $limit;

    $sql = "SELECT t.*, 
                  a.id AS acc_id, a.email AS acc_email, a.role AS acc_role, 
                  a.created_at AS acc_created_at, a.updated_at AS acc_updated_at, a.deleted_at AS acc_deleted_at
            FROM `teachers` t
            INNER JOIN `accounts` a ON t.`account_id` = a.`id`
            WHERE a.`deleted_at` IS NULL
            LIMIT :limit OFFSET :offset";

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return array_map(fn($row) => Teacher::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  public function getTeacherById(int $id): ?Teacher
  {
    $sql = "SELECT t.*, 
                  a.id AS acc_id, a.email AS acc_email, a.role AS acc_role, 
                  a.created_at AS acc_created_at, a.updated_at AS acc_updated_at, a.deleted_at AS acc_deleted_at
            FROM `teachers` t 
            INNER JOIN `accounts` a ON t.`account_id` = a.`id`
            WHERE t.`account_id` = :id AND a.`deleted_at` IS NULL";

    $stmt = $this->db->prepare($sql);
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? Teacher::fromArray($row) : null;
  }

  public function createTeacher(array $teacher, string $rawPassword): int
  {
    return Database::getInstance()->transaction(function () use ($teacher, $rawPassword): int {
      $accId = $this->createAccount($teacher['email'], $rawPassword, 'teacher');

      $sql = "INSERT INTO `teachers` 
                    (account_id, full_name, gender, dob, phone, title, department, `start_date`) 
                VALUES 
                    (:acc_id, :name, :gender, :dob, :phone, :title, :dept, :start)";

      $this->db->prepare($sql)->execute([
        ':acc_id' => $accId,
        ':name' => $teacher['full_name'],
        ':gender' => $teacher['gender'],
        ':dob' => $teacher['dob'],
        ':phone' => $teacher['phone'],
        ':title' => $teacher['title'],
        ':dept' => $teacher['department'],
        ':start' => $teacher['start_date'],
      ]);

      return $accId;
    });
  }

  public function updateTeacher(int $id, Teacher $teacher): bool
  {
    return Database::getInstance()->transaction(function () use ($id, $teacher): bool {
      $this->touchAccount($id);

      $sql = "UPDATE `teachers` SET 
                full_name   = :name,
                gender      = :gender,
                dob         = :dob,
                phone       = :phone,
                title       = :title,
                department  = :dept,
                `start_date`= :start
              WHERE account_id = :id";

      return $this->db->prepare($sql)->execute([
        ':name' => $teacher->full_name,
        ':gender' => $teacher->gender,
        ':dob' => $teacher->dob,
        ':phone' => $teacher->phone,
        ':title' => $teacher->title,
        ':dept' => $teacher->department,
        ':start' => $teacher->start_date,
        ':id' => $id,
      ]);
    });
  }

  public function deleteTeacher(int $id): bool
  {
    return $this->softDeleteAccount($id);
  }

  // ===================================
  // Classroom methods
  // ===================================
  public function getAllClassrooms(): array
  {
    $stmt = $this->db->prepare("SELECT * FROM `classrooms`");
    $stmt->execute();

    return array_map(fn($row) => Classroom::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  public function createClassroom(array $classroom): int
  {
    $stmt = $this->db->prepare("INSERT INTO `classrooms` (name) VALUES (:name)");
    $stmt->execute([':name' => $classroom['name']]);
    return (int) $this->db->lastInsertId();
  }
}