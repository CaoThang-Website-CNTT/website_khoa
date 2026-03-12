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
use Exception;


interface EducationRepositoryInterface
{
  // Student
  /** @return Student[] */
  public function getAllStudents(int $pageTo, int $limit = 15): array;
  public function getStudentById(int $id): ?Student;
  public function createStudent(array $student, string $rawPassword): int;
  public function updateStudent(int $id, Student $student): bool;
  public function deleteStudent(int $id): bool;

  // Teacher
  /** @return Teacher[] */
  public function getAllTeachers(int $pageTo, int $limit = 15): array;
  public function getTeacherById(int $id): ?Teacher;
  public function createTeacher(array $teacher, string $rawPassword): int;
  public function updateTeacher(int $id, Teacher $teacher): bool;
  public function deleteTeacher(int $id): bool;

  // Classroom
  /** @return Classroom[] */
  public function getAllClassrooms(): array;
  // Helper
  public function isStudentIdUnique(string $studentId, ?int $excludeAccountId = null): bool;
  public function isEmailUnique(string $email, ?int $excludeAccountId = null): bool;
}

class EducationService implements EducationRepositoryInterface
{
  private $db;

  public function __construct()
  {
    $this->db = Database::getInstance()->getConnection();
  }
  // --- HELPER METHODS ---
  /**
   * Tạo tài khoản
   * @param string $email
   * @param string $rawPassword
   * @param string $role
   * @return int
   */
  private function createAccount(string $email, string $rawPassword, string $role): int
  {
    $now = date('Y-m-d H:i:s');
    $sql = "INSERT INTO `accounts` (email, password_hash, role, created_at, updated_at) 
                VALUES (:email, :password, :role, :created, :updated)";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      ':email'    => $email,
      ':password' => password_hash($rawPassword, PASSWORD_DEFAULT),
      ':role'     => $role,
      ':created'  => $now,
      ':updated'  => $now
    ]);
    return (int)$this->db->lastInsertId();
  }

  /**
   * Summary of touchAccount
   * @param int $accountId
   * @return void
   */
  private function touchAccount(int $accountId): void
  {
    $sql = "UPDATE `accounts` SET updated_at = NOW() WHERE id = :id";
    $this->db->prepare($sql)->execute([':id' => $accountId]);
  }

  /**
   * Xoá mềm tài khoản
   * @param int $accountId
   * @return bool
   */
  private function softDeleteAccount(int $accountId): bool
  {
    $sql = "UPDATE `accounts` SET deleted_at = NOW() WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([':id' => $accountId]);
  }
  /**
   * Kiểm tra mssv unique
   * @param string $studentId
   * @param mixed $excludeAccountId
   * @return bool
   */
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
  /**
   * Kiểm tra email unique
   * @param string $email
   * @param mixed $excludeAccountId
   * @return bool
   */
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
    try {
      $this->db->beginTransaction();

      $email = $student['student_id'] . "@caothang.edu.vn";
      $accId = $this->createAccount($email, $rawPassword, 'student');

      $sqlStu = "INSERT INTO `students` (account_id, student_id, full_name, gender, dob, phone, classroom_id, major, birth_place) 
                VALUES (:acc_id, :code, :name, :gender, :dob, :phone, :classroom_id, :major, :birth_place)";

      $this->db->prepare($sqlStu)->execute([
        ':acc_id'       => $accId,
        ':code'         => $student['student_id'],
        ':name'         => $student['full_name'],
        ':gender'       => $student['gender'],
        ':dob'          => $student['dob'],
        ':phone'        => $student['phone'],
        ':classroom_id' => $student['classroom_id'],
        ':major'        => $student['major'],
        ':birth_place'  => $student['birth_place']
      ]);

      $this->db->commit();
      return $accId;
    } catch (Exception $e) {
      $this->db->rollBack();
      throw $e;
    }
  }

  public function updateStudent(int $id, Student $student): bool
  {
    try {
      $this->db->beginTransaction();

      $this->touchAccount($id);
      $sqlStu = "UPDATE `students` SET 
                full_name = :name, gender = :gender, dob = :dob, 
                phone = :phone, classroom_id = :classroom_id, major = :major, birth_place = :birth_place 
                WHERE account_id = :id";

      $result = $this->db->prepare($sqlStu)->execute([
        ':name'         => $student->full_name,
        ':gender'       => $student->gender,
        ':dob'          => $student->dob,
        ':phone'        => $student->phone,
        ':classroom_id' => $student->classroom_id,
        ':major'        => $student->major,
        ':birth_place'  => $student->birth_place,
        ':id'           => $id
      ]);

      $this->db->commit();
      return $result;
    } catch (Exception $e) {
      $this->db->rollBack();
      return false;
    }
  }

  public function deleteStudent(int $id): bool
  {
    return $this->softDeleteAccount($id);
  }

  // --- TEACHER METHODS ---

  public function getAllTeachers(int $pageTo, int $limit = 15): array
  {
    $currentPage = max(1, $pageTo);
    $offset = ($currentPage - 1) * $limit;
    $sql = "SELECT t.*, 
                  a.id AS acc_id, a.email AS acc_email, a.role AS acc_role, 
                  a.created_at AS acc_created_at, a.updated_at AS acc_updated_at, a.deleted_at AS acc_deleted_at
            FROM `teachers` t
            INNER JOIN `accounts` a ON t.`account_id` = a.`id`
            WHERE a.`deleted_at` IS NULL
            LIMIT :limit OFFSET :offset";

    $stmt = $this->db->prepare($sql);
    $stmt->bindvalue(":limit", $limit, PDO::PARAM_INT);
    $stmt->bindvalue(":offset", $offset, PDO::PARAM_INT);
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
    try {
      $this->db->beginTransaction();
      $email = $teacher['email'];
      $accId = $this->createAccount($email, $rawPassword, 'teacher');
      $sqlTeach = "INSERT INTO `teachers` (account_id, full_name, gender, dob, phone, title, department,`start_date`) 
                  VALUES (:acc_id, :name, :gender, :dob, :phone, :title, :dept, :start)";

      $this->db->prepare($sqlTeach)->execute([
        ':acc_id' => $accId,
        ':name'   => $teacher['full_name'],
        ':gender' => $teacher['gender'],
        ':dob'    => $teacher['dob'],
        ':phone'  => $teacher['phone'],
        ':title'  => $teacher['title'],
        ':dept'   => $teacher['department'],
        ':start'  => $teacher['start_date']
      ]);

      $this->db->commit();
      return $accId;
    } catch (Exception $e) {
      $this->db->rollBack();
      throw $e;
    }
  }

  public function updateTeacher(int $id, Teacher $teacher): bool
  {
    try {
      $this->db->beginTransaction();

      $this->touchAccount($id);

      $sqlTeach = "UPDATE `teachers` SET 
                    full_name = :name, gender = :gender, dob = :dob, 
                    phone = :phone, title = :title, department = :dept, `start_date` = :start 
                  WHERE account_id = :id";

      $result = $this->db->prepare($sqlTeach)->execute([
        ':name'   => $teacher->full_name,
        ':gender' => $teacher->gender,
        ':dob'    => $teacher->dob,
        ':phone'  => $teacher->phone,
        ':title'  => $teacher->title,
        ':dept'   => $teacher->department,
        ':start'  => $teacher->start_date,
        ':id'     => $id
      ]);

      $this->db->commit();
      return $result;
    } catch (Exception $e) {
      $this->db->rollBack();
      return false;
    }
  }

  public function deleteTeacher(int $id): bool
  {
    return $this->softDeleteAccount($id);
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
