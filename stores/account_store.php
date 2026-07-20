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
  public function create(string $email, string $hash, string $role): Account;
  public function createMany(array $accounts): void;
  /** @return Account[] */
  public function getByEmails(array $emails): array;
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
  public function updateAccount(int $id, string $email, ?string $passwordHash, string $role): bool;
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
  public function create(string $email, string $hash, string $role): Account
  {
    $passwordHash = $this->ensurePasswordHash($hash);

    $builder = new QueryBuilder(new MySQLCompiler());
    $query = $builder->from('accounts')->insert([
      'email' => $email,
      'password_hash' => $passwordHash,
      'role' => $role,
      'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
      'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
    ]);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    $id = (int) $this->db->lastInsertId();
    return $this->getById($id) ?? throw new \RuntimeException("Không thể lấy Account sau khi tạo.");
  }

  public function createMany(array $accounts): void
  {
    if (empty($accounts)) {
      return;
    }
    $builder = new QueryBuilder(new MySQLCompiler());
    $query = $builder->from('accounts')->insert($accounts);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
  }

  private function ensurePasswordHash(string $password): string
  {
    if (password_get_info($password)['algoName'] !== 'unknown') {
      return $password;
    }

    return password_hash($password, PASSWORD_DEFAULT);
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

  /** @return Account[] */
  public function getByEmails(array $emails): array
  {
    if (empty($emails)) {
      return [];
    }

    $builder = new QueryBuilder(new MySQLCompiler());
    $query = $builder->from('accounts')
      ->select('*')
      ->in('email', $emails)
      ->is('deleted_at', null);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return array_map(fn($row) => Account::fromArray($row), $stmt->fetchAll(\PDO::FETCH_ASSOC));
  }
  public function updatePassword(int $id, string $newHash): bool
  {
    $passwordHash = $this->ensurePasswordHash($newHash);

    $builder = new QueryBuilder(new MySQLCompiler());
    $query = $builder->from('accounts')->update([
      'password_hash' => $passwordHash,
      'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
    ])->eq('id', $id);
    $stmt = $this->db->prepare($query->toSql());
    return $stmt->execute($query->getBindings());
  }

  public function updateRole(int $id, string $role): bool
  {
    $builder = new QueryBuilder(new MySQLCompiler());
    $query = $builder->from('accounts')->update([
      'role' => $role,
      'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
    ])->eq('id', $id);
    $stmt = $this->db->prepare($query->toSql());
    return $stmt->execute($query->getBindings());
  }

  public function updateAccount(int $id, string $email, ?string $passwordHash, string $role): bool
  {
    $builder = new QueryBuilder(new MySQLCompiler());
    $data = [
      'email' => $email,
      'role' => $role,
      'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
    ];
    if ($passwordHash) {
      $data['password_hash'] = $this->ensurePasswordHash($passwordHash);
    }
    $query = $builder->from('accounts')->update($data)->eq('id', $id);
    $stmt = $this->db->prepare($query->toSql());
    return $stmt->execute($query->getBindings());
  }

  public function softDelete(int $id): bool
  {
    $builder = new QueryBuilder(new MySQLCompiler());
    $query = $builder->from('accounts')->update([
      'deleted_at' => (new \DateTime())->format('Y-m-d H:i:s')
    ])->eq('id', $id);
    $stmt = $this->db->prepare($query->toSql());
    return $stmt->execute($query->getBindings());
  }

  public function isEmailUnique(string $email, ?int $excludeId = null): bool
  {
    $builder = new QueryBuilder(new MySQLCompiler());
    $builder->from('accounts')
      ->select('COUNT(*)')
      ->eq('email', $email)
      ->is('deleted_at', null);

    if ($excludeId !== null) {
      $builder->neq('id', $excludeId);
    }

    $stmt = $this->db->prepare($builder->toSql());
    $stmt->execute($builder->getBindings());
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
