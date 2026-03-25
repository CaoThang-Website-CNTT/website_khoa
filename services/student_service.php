<?php

namespace App\Services;

require_once BASE_PATH . '/models/account.php';
require_once BASE_PATH . '/models/student.php';
require_once BASE_PATH . '/db/database.php';

use App\Models\Student;
use Database;
use PDO;

class StudentService
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

  public function getTotalStudentsCount(): int
  {
    $sql = "SELECT COUNT(s.account_id) 
                FROM `students` s
                INNER JOIN `accounts` a ON s.`account_id` = a.`id`
                WHERE a.`deleted_at` IS NULL";

    $stmt = $this->db->query($sql);
    return (int) $stmt->fetchColumn();
  }

  /** @return Student[] */
  public function getAllStudents(int $pageTo, int $limit = 15): array
  {
    $offset = (max(1, $pageTo) - 1) * $limit;

    $sql = "SELECT 
                    s.*,
                    a.id AS acc_id, a.email as acc_email, a.role AS acc_role, 
                    a.created_at AS acc_created_at, a.updated_at AS acc_updated_at, a.deleted_at AS acc_deleted_at
                FROM `students` s
                INNER JOIN `accounts` a ON s.`account_id` = a.`id`
                WHERE a.`deleted_at` IS NULL 
                LIMIT :limit OFFSET :offset";

    $stmt = $this->db->prepare($sql);
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
}