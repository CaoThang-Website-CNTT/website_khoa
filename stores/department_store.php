<?php

namespace App\Stores;

require_once BASE_PATH . '/includes/core/store.php';
require_once BASE_PATH . '/models/department.php';

use PDO;
use App\Core\Store;
use App\Models\Department;
use App\Core\Schema\QueryBuilder;
use App\Core\Schema\Compiler\MySQLCompiler;

interface IDepartmentStore
{
  /** @return Department[] */
  public function getAll(): array;
  public function getById(int $id): ?Department;
  /** @return Department[] */
  public function getByIds(array $ids): array;
}

class DepartmentStore extends Store implements IDepartmentStore
{
  public function getAll(): array
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('departments')->select('*')
      ->is('deleted_at', null)->order('full_name');
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    return array_map(fn($row) => Department::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  public function getById(int $id): ?Department
  {
    $query = (new QueryBuilder(new MySQLCompiler()))->from('departments')->select('*')
      ->eq('id', $id)->is('deleted_at', null);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? Department::fromArray($row) : null;
  }

  /** @return Department[] */
  public function getByIds(array $ids): array
  {
    if (empty($ids)) {
      return [];
    }

    $query = (new QueryBuilder(new MySQLCompiler()))->from('departments')->select('*')
      ->in('id', $ids)->is('deleted_at', null);
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return array_map(fn($row) => Department::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }
}
