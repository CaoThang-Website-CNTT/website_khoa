<?php

namespace App\Stores;

require_once BASE_PATH . '/includes/core/store.php';
require_once BASE_PATH . '/models/department.php';

use PDO;
use App\Core\Store;
use App\Models\Department;

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
    $sql = "
      SELECT * FROM `departments`
      WHERE `deleted_at` IS NULL
      ORDER BY `full_name` ASC
    ";

    $stmt = $this->db->query($sql);
    return array_map(fn($row) => Department::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  public function getById(int $id): ?Department
  {
    $sql = "
      SELECT * FROM `departments`
      WHERE `id` = :id AND `deleted_at` IS NULL
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? Department::fromArray($row) : null;
  }

  /** @return Department[] */
  public function getByIds(array $ids): array
  {
    if (empty($ids)) {
      return [];
    }

    $placeholders = str_repeat('?,', count($ids) - 1) . '?';

    $sql = "
      SELECT * FROM `departments`
      WHERE `id` IN ($placeholders) AND `deleted_at` IS NULL
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($ids);

    return array_map(fn($row) => Department::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }
}
