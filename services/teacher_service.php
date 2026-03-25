<?php

namespace App\Services;

require_once BASE_PATH . '/models/account.php';
require_once BASE_PATH . '/models/teacher.php';
require_once BASE_PATH . '/db/database.php';

use App\Models\Teacher;
use Database;
use PDO;

class TeacherService
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

  public function getTotalTeachersCount(): int
  {
    $sql = "SELECT COUNT(t.account_id) 
                FROM `teachers` t
                INNER JOIN `accounts` a ON t.`account_id` = a.`id`
                WHERE a.`deleted_at` IS NULL";

    $stmt = $this->db->query($sql);
    return (int) $stmt->fetchColumn();
  }

  /** @return Teacher[] */
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
}