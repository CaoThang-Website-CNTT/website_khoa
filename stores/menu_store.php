<?php

namespace App\Stores;

require_once BASE_PATH . '/includes/core/store.php';
require_once BASE_PATH . '/models/menu.php';
require_once BASE_PATH . '/models/menu_item.php';

use App\Core\Store;
use App\Models\{Menu, MenuItem};
use PDO;

interface IMenuStore
{
  // ── Menus ──────────────────────────────────────────────────────────────────
  /** @return Menu[] */
  public function getAll(): array;
  /** @return Menu[] */
  public function getPaginated(int $pageTo, int $limit = 15): array;
  public function getById(int $id): ?Menu;
  public function getByKey(string $key): ?Menu;
  public function create(Menu $menu): int;
  public function update(Menu $menu): bool;
  public function softDelete(int $id): bool;
  public function isKeyUnique(string $key, ?int $excludeId = null): bool;
  public function getTotalCount(): int;

  // ── Menu Items ─────────────────────────────────────────────────────────────
  /** @return MenuItem[] Danh sách phẳng, sắp xếp theo sort_order */
  public function getItemsByMenuId(int $menuId): array;
  public function getItemById(int $id): ?MenuItem;
  public function createItem(MenuItem $item): int;
  public function updateItem(MenuItem $item): bool;
  public function softDeleteItem(int $id): bool;
  /** Cập nhật sort_order hàng loạt: [itemId => sortOrder, ...] */
  public function reorderItems(array $orderMap): bool;
}

class MenuStore extends Store implements IMenuStore
{
  // ── Menus ──────────────────────────────────────────────────────────────────

  /** @return Menu[] */
  public function getAll(): array
  {
    $stmt = $this->db->prepare("
      SELECT *
      FROM `menus`
      WHERE deleted_at IS NULL
      ORDER BY sort_order ASC, id ASC
    ");
    $stmt->execute();

    return array_map(fn($row) => Menu::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  /** @return Menu[] */
  public function getPaginated(int $pageTo, int $limit = 15): array
  {
    $offset = (max(1, $pageTo) - 1) * $limit;

    $stmt = $this->db->prepare("
      SELECT *
      FROM `menus`
      WHERE deleted_at IS NULL
      ORDER BY sort_order ASC, id ASC
      LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return array_map(fn($row) => Menu::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  public function getById(int $id): ?Menu
  {
    $stmt = $this->db->prepare("
      SELECT *
      FROM `menus`
      WHERE id = :id AND deleted_at IS NULL
      LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? Menu::fromArray($row) : null;
  }

  public function getByKey(string $key): ?Menu
  {
    $stmt = $this->db->prepare("
      SELECT *
      FROM `menus`
      WHERE `key` = :key AND deleted_at IS NULL
      LIMIT 1
    ");
    $stmt->execute([':key' => $key]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? Menu::fromArray($row) : null;
  }

  public function create(Menu $menu): int
  {
    $this->db->prepare("
      INSERT INTO `menus` (`key`, `label`, `description`, `type`, `sort_order`)
      VALUES (:key, :label, :description, :type, :sort_order)
    ")->execute([
          ':key' => $menu->key,
          ':label' => $menu->label,
          ':description' => $menu->description,
          ':type' => $menu->type,
          ':sort_order' => $menu->sort_order,
        ]);

    return (int) $this->db->lastInsertId();
  }

  public function update(Menu $menu): bool
  {
    return $this->db->prepare("
      UPDATE `menus` SET
        `key`       = :key,
        `label`     = :label,
        `description` = :description,
        `type`      = :type,
        `sort_order` = :sort_order,
        `updated_at` = NOW()
      WHERE id = :id AND deleted_at IS NULL
    ")->execute([
          ':key' => $menu->key,
          ':label' => $menu->label,
          ':description' => $menu->description,
          ':type' => $menu->type,
          ':sort_order' => $menu->sort_order,
          ':id' => $menu->id,
        ]);
  }

  public function softDelete(int $id): bool
  {
    return $this->db->prepare("
      UPDATE `menus` SET deleted_at = NOW()
      WHERE id = :id
    ")->execute([':id' => $id]);
  }

  public function isKeyUnique(string $key, ?int $excludeId = null): bool
  {
    $sql = "SELECT COUNT(*) FROM `menus` WHERE `key` = :key AND deleted_at IS NULL";
    $params = [':key' => $key];

    if ($excludeId !== null) {
      $sql .= " AND id != :exclude_id";
      $params[':exclude_id'] = $excludeId;
    }

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);

    return (int) $stmt->fetchColumn() === 0;
  }

  public function getTotalCount(): int
  {
    $stmt = $this->db->query("SELECT COUNT(id) FROM `menus` WHERE `deleted_at` IS NULL");
    return (int) $stmt->fetchColumn();
  }

  // ── Menu Items ─────────────────────────────────────────────────────────────

  /** @return MenuItem[] */
  public function getItemsByMenuId(int $menuId): array
  {
    $stmt = $this->db->prepare("
      SELECT *
      FROM `menu_items`
      WHERE menu_id = :menu_id AND deleted_at IS NULL
      ORDER BY sort_order ASC, id ASC
    ");
    $stmt->execute([':menu_id' => $menuId]);

    return array_map(fn($row) => MenuItem::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  public function getItemById(int $id): ?MenuItem
  {
    $stmt = $this->db->prepare("
      SELECT *
      FROM `menu_items`
      WHERE id = :id AND deleted_at IS NULL
      LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? MenuItem::fromArray($row) : null;
  }

  public function createItem(MenuItem $item): int
  {
    $this->db->prepare("
      INSERT INTO `menu_items` (`menu_id`, `parent_id`, `label`, `url`, `sort_order`)
      VALUES (:menu_id, :parent_id, :label, :url, :sort_order)
    ")->execute([
          ':menu_id' => $item->menu_id,
          ':parent_id' => $item->parent_id,
          ':label' => $item->label,
          ':url' => $item->url,
          ':sort_order' => $item->sort_order,
        ]);

    return (int) $this->db->lastInsertId();
  }

  public function updateItem(MenuItem $item): bool
  {
    return $this->db->prepare("
      UPDATE `menu_items` SET
        `parent_id`  = :parent_id,
        `label`      = :label,
        `url`        = :url,
        `sort_order` = :sort_order,
        `updated_at` = NOW()
      WHERE id = :id AND deleted_at IS NULL
    ")->execute([
          ':parent_id' => $item->parent_id,
          ':label' => $item->label,
          ':url' => $item->url,
          ':sort_order' => $item->sort_order,
          ':id' => $item->id,
        ]);
  }

  public function softDeleteItem(int $id): bool
  {
    return $this->db->prepare("
      UPDATE `menu_items` SET deleted_at = NOW()
      WHERE id = :id
    ")->execute([':id' => $id]);
  }

  /**
   * Cập nhật sort_order hàng loạt trong một transaction.
   * $orderMap = [itemId => sortOrder, ...]
   */
  public function reorderItems(array $orderMap): bool
  {
    if (empty($orderMap)) {
      return true;
    }

    $stmt = $this->db->prepare("
      UPDATE `menu_items` SET sort_order = :sort_order, updated_at = NOW()
      WHERE id = :id AND deleted_at IS NULL
    ");

    foreach ($orderMap as $id => $sortOrder) {
      $stmt->execute([
        ':sort_order' => (int) $sortOrder,
        ':id' => (int) $id,
      ]);
    }

    return true;
  }
}