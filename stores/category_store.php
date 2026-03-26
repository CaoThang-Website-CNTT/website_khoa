<?php

namespace App\Stores;

require_once BASE_PATH . '/includes/core/store.php';
require_once BASE_PATH . '/models/category.php';

use App\Core\Store;
use App\Models\Category;
use PDO;

interface ICategoryStore
{
  /** @return Category[] */
  public function getAll(): array;
  /** @return Category[] */
  public function getPaginated(int $pageTo, int $limit = 15): array;
  /** @return Category[] */
  public function getByParentId(?int $parentId): array;
  /** @return Category[] */
  public function getByType(string $type): array;
  public function getById(int $id): ?Category;
  public function getBySlug(string $slug): ?Category;
  public function create(Category $category): int;
  public function update(Category $category): bool;
  public function softDelete(int $id): bool;
  public function getTotalCount(): int;
  public function isSlugUnique(string $slug, ?int $excludeId = null): bool;
}

class CategoryStore extends Store implements ICategoryStore
{
  /** @return Category[] */
  public function getAll(): array
  {
    $stmt = $this->db->prepare("
      SELECT *
      FROM `categories`
      WHERE deleted_at IS NULL
      ORDER BY parent_id ASC, id ASC
    ");
    $stmt->execute();

    return array_map(fn($row) => Category::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }
  /** @return Category[] */
  public function getPaginated(int $pageTo, int $limit = 15): array
  {
    $offset = (max(1, $pageTo) - 1) * $limit;

    $sql = "
      SELECT *
      FROM `categories`
      WHERE deleted_at IS NULL
      ORDER BY parent_id ASC, id ASC
      LIMIT :limit OFFSET :offset
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return array_map(fn($row) => Category::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  /** @return Category[] */
  public function getByParentId(?int $parentId): array
  {
    if ($parentId === null) {
      $stmt = $this->db->prepare("
        SELECT *
        FROM `categories`
        WHERE parent_id IS NULL AND deleted_at IS NULL
        ORDER BY id ASC
      ");
      $stmt->execute();
    } else {
      $stmt = $this->db->prepare("
        SELECT *
        FROM `categories`
        WHERE parent_id = :parent_id AND deleted_at IS NULL
        ORDER BY id ASC
      ");
      $stmt->execute([':parent_id' => $parentId]);
    }

    return array_map(fn($row) => Category::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  /** @return Category[] */
  public function getByType(string $type): array
  {
    $stmt = $this->db->prepare("
      SELECT *
      FROM `categories`
      WHERE type = :type AND deleted_at IS NULL
      ORDER BY id ASC
    ");
    $stmt->execute([':type' => $type]);

    return array_map(fn($row) => Category::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  public function getById(int $id): ?Category
  {
    $stmt = $this->db->prepare("
      SELECT *
      FROM `categories`
      WHERE id = :id AND deleted_at IS NULL
      LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? Category::fromArray($row) : null;
  }

  public function getBySlug(string $slug): ?Category
  {
    $stmt = $this->db->prepare("
      SELECT *
      FROM `categories`
      WHERE slug = :slug AND deleted_at IS NULL
      LIMIT 1
    ");
    $stmt->execute([':slug' => $slug]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? Category::fromArray($row) : null;
  }

  public function create(Category $category): int
  {
    $stmt = $this->db->prepare("
      INSERT INTO `categories` (name, slug, type, description, parent_id, meta)
      VALUES (:name, :slug, :type, :description, :parent_id, :meta)
    ");
    $stmt->execute([
      ':name' => $category->name,
      ':slug' => $category->slug,
      ':type' => $category->type,
      ':description' => $category->description,
      ':parent_id' => $category->parent_id,
      ':meta' => $category->meta,
    ]);

    return (int) $this->db->lastInsertId();
  }

  public function update(Category $category): bool
  {
    return $this->db->prepare("
      UPDATE `categories` SET
        name        = :name,
        slug        = :slug,
        type        = :type,
        description = :description,
        parent_id   = :parent_id,
        meta        = :meta
      WHERE id = :id AND deleted_at IS NULL
    ")->execute([
          ':name' => $category->name,
          ':slug' => $category->slug,
          ':type' => $category->type,
          ':description' => $category->description,
          ':parent_id' => $category->parent_id,
          ':meta' => $category->meta,
          ':id' => $category->id,
        ]);
  }

  public function softDelete(int $id): bool
  {
    return $this->db->prepare("
      UPDATE `categories` SET deleted_at = NOW()
      WHERE id = :id
    ")->execute([':id' => $id]);
  }

  public function getTotalCount(): int
  {
    $sql = "
      SELECT COUNT(id)
      FROM `categories`
      WHERE `deleted_at` IS NULL
    ";

    $stmt = $this->db->query($sql);
    return (int) $stmt->fetchColumn();
  }

  public function isSlugUnique(string $slug, ?int $excludeId = null): bool
  {
    $sql = "SELECT COUNT(*) FROM `categories` WHERE slug = :slug AND deleted_at IS NULL";
    $params = [':slug' => $slug];

    if ($excludeId !== null) {
      $sql .= " AND id != :exclude_id";
      $params[':exclude_id'] = $excludeId;
    }

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);

    return (int) $stmt->fetchColumn() === 0;
  }
}