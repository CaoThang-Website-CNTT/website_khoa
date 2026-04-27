<?php

namespace App\Stores;

require_once BASE_PATH . '/includes/core/store.php';
require_once BASE_PATH . '/models/account.php';

use App\Core\Store;
use App\Core\Schema\QueryBuilder;
use App\Core\Schema\Compiler\MySQLCompiler;
use App\Models\Account;
use PDO;

interface IAccountStore
{
  public function create(string $email, string $hash, string $role): int;
  public function getById(int $id): ?Account;
  /** @return Account[] */
  public function getByIds(array $ids): array;
  public function findByEmail(string $email): ?Account;
  public function updatePassword(int $id, string $newHash): bool;
  public function updateRole(int $id, string $role): bool;
  public function softDelete(int $id): bool;
  public function isEmailUnique(string $email, ?int $excludeId = null): bool;
  public function existsWithRole(int $id, string $role): bool;
}

class AccountStore extends Store implements IAccountStore
{
  public function create(string $email, string $hash, string $role): int
  {
    $sql = "
      INSERT INTO `accounts` (email, password_hash, role) 
      VALUES (:email, :hash, :role)
    ";

    $this->db->prepare($sql)->execute([
      ':email' => $email,
      ':hash' => $hash,
      ':role' => $role
    ]);

    return (int) $this->db->lastInsertId();
  }

  public function getById(int $id): ?Account
  {
    $sql = "
      SELECT *
      FROM `accounts`
      WHERE id = :id AND deleted_at IS NULL
      LIMIT 1
    ";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? Account::fromArray($row) : null;
  }

  /** @return Account[] */
  public function getByIds(array $ids): array
  {
    if (empty($ids)) {
      return [];
    }

    $placeholders = str_repeat('?,', count($ids) - 1) . '?';

    $sql = "
      SELECT *
      FROM accounts WHERE id IN ($placeholders) AND deleted_at IS NULL
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($ids);

    return array_map(fn($row) => Account::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  public function findByEmail(string $email): ?Account
  {
    $sql = "
      SELECT *
      FROM `accounts`
      WHERE email = :email AND deleted_at IS NULL
      LIMIT 1
    ";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':email' => $email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? Account::fromArray($row) : null;
  }

  public function updatePassword(int $id, string $newHash): bool
  {
    return $this->db->prepare("
      UPDATE `accounts` SET
      password_hash = :hash
      WHERE id = :id
    ")->execute([
          ':hash' => $newHash,
          ':id' => $id
        ]);
  }

  public function updateRole(int $id, string $role): bool
  {
    return $this->db->prepare("
      UPDATE `accounts` SET
      role = :role
      WHERE id = :id"
    )->execute([
          ':role' => $role,
          ':id' => $id
        ]);
  }

  public function softDelete(int $id): bool
  {
    return $this->db->prepare("
      UPDATE `accounts` SET
      deleted_at = NOW()
      WHERE id = :id
    ")->execute([':id' => $id]);
  }

  public function isEmailUnique(string $email, ?int $excludeId = null): bool
  {
    $sql = "
      SELECT COUNT(*)
      FROM `accounts`
      WHERE email = :email AND deleted_at IS NULL";
    $params = [':email' => $email];

    if ($excludeId !== null) {
      $sql .= " AND id != :exclude_id";
      $params[':exclude_id'] = $excludeId;
    }

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn() === 0;
  }
  public function existsWithRole(int $id, string $role): bool
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder->from('accounts')
      ->from('accounts')
      ->select('COUNT(*)')
      ->eq('id', $id)
      ->eq('role', $role)
      ->is('deleted_at', null);

    $sql = $query->toSql();
    $bindings = $query->getBindings();

    $stmt = $this->db->prepare($sql);
    $stmt->execute($bindings);

    return (int) $stmt->fetchColumn() > 0;
  }
}