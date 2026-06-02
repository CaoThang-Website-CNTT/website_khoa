<?php

namespace App\Stores;

require_once BASE_PATH . '/includes/core/store.php';
require_once BASE_PATH . '/models/account.php';

use App\Core\Store;
use App\Core\Schema\QueryBuilder;
use App\Core\Schema\Compiler\MySQLCompiler;
use App\Models\Account;

interface IAccountStore
{
  public function create(string $email, string $hash, string $role): int;
  /** @return Account[] */
  public function getPaginated(int $pageTo, int $limit = 15): array;

  public function getById(int $id): ?Account;
  /** @return Account[] */
  public function getByIds(array $ids): array;
  /** @return Account[] */
  public function getAllByRole(string $role): array;
  public function findByEmail(string $email): ?Account;
  public function updatePassword(int $id, string $newHash): bool;
  public function updateRole(int $id, string $role): bool;
  public function softDelete(int $id): bool;
  public function isEmailUnique(string $email, ?int $excludeId = null): bool;
  public function existsWithRole(int $id, string $role): bool;
  public function getTotalCount(): int;
}

class AccountStore extends Store implements IAccountStore
{
  // Các cột dùng cho listing - tách biệt với detail để kiểm soát payload
  private const LISTING_COLUMNS = [
    'id',
    'email',
    'role',
    'created_at',
  ];
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

  public function getAllByRole(string $role): array
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder->from('accounts')
      ->from('accounts')
      ->select('*')
      ->eq('role', $role)
      ->is('deleted_at', null);

    $sql = $query->toSql();
    $bindings = $query->getBindings();

    $stmt = $this->db->prepare($sql);
    $stmt->execute($bindings);

    return array_map(fn($row) => Account::fromArray($row), $stmt->fetchAll(\PDO::FETCH_ASSOC));
  }

  public function getPaginated(int $page, int $limit = 20, string $search = '%'): array
  {
    $offset = ($page - 1) * $limit;
    $builder = new QueryBuilder(new MySQLCompiler());

    $builder
      ->from('accounts')
      ->select(self::LISTING_COLUMNS)
      ->is('deleted_at', null)
      ->like('email', $search)
      ->order('created_at', ['ascending' => false])
      ->limit($limit)
      ->range($offset, $offset + $limit - 1);

    $stmt = $this->db->prepare($builder->toSql());
    $stmt->execute($builder->getBindings());

    return array_map(
      fn(array $row) => Account::fromArray($row),
      $stmt->fetchAll(\PDO::FETCH_ASSOC),
    );
  }

  public function getById(int $id): ?Account
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder
      ->from('accounts')
      ->select('*')
      ->eq('id', $id)
      ->is('deleted_at', null)
      ->limit(1);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $row ? Account::fromArray($row) : null;
  }

  /** @return Account[] */
  public function getByIds(array $ids): array
  {
    if (empty($ids)) {
      return [];
    }

    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder->from('accounts')
      ->select('*')
      ->in('id', $ids)
      ->is('deleted_at', null);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return array_map(fn($row) => Account::fromArray($row), $stmt->fetchAll(\PDO::FETCH_ASSOC));
  }

  public function findByEmail(string $email): ?Account
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder->from('accounts')
      ->select('*')
      ->eq('email', $email)
      ->is('deleted_at', null)
      ->limit(1);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
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

  public function getTotalCount(): int
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder->from('accounts')->select('COUNT(*) AS total')->is('deleted_at', null);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $row ? (int) $row['total'] : 0;
  }
}