<?php

namespace App\Stores;

require_once BASE_PATH . '/includes/core/store.php';
require_once BASE_PATH . '/models/menu.php';
require_once BASE_PATH . '/models/menu_item.php';

use App\Core\Store;
use App\Models\{Menu, MenuItem};
use PDO;

use App\Core\Schema\QueryBuilder;
use App\Core\Schema\Compiler\MySQLCompiler;

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
  /** Sắp xếp cấu trúc phân cấp cây menu items hàng loạt */
  public function sortItems(array $items): bool;
}

class MenuStore extends Store implements IMenuStore
{
  // ── Menus ──────────────────────────────────────────────────────────────────

  /** @return Menu[] */
  public function getAll(): array
  {
    $builder = new QueryBuilder(new MySQLCompiler());
    $query = $builder
      ->from('menus')
      ->select('*')
      ->is('deleted_at', null)
      ->order('sort_order', ['ascending' => true])
      ->order('id', ['ascending' => true]);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return array_map(fn($row) => Menu::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  /** @return Menu[] */
  public function getPaginated(int $pageTo, int $limit = 15): array
  {
    $offset = (max(1, $pageTo) - 1) * $limit;
    
    $builder = new QueryBuilder(new MySQLCompiler());
    $query = $builder
      ->from('menus')
      ->select('*')
      ->is('deleted_at', null)
      ->order('sort_order', ['ascending' => true])
      ->order('id', ['ascending' => true])
      ->limit($limit)
      ->range($offset, $offset + $limit - 1);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return array_map(fn($row) => Menu::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  public function getById(int $id): ?Menu
  {
    $builder = new QueryBuilder(new MySQLCompiler());
    $query = $builder
      ->from('menus')
      ->select('*')
      ->eq('id', $id)
      ->is('deleted_at', null)
      ->limit(1);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? Menu::fromArray($row) : null;
  }

  public function getByKey(string $key): ?Menu
  {
    $builder = new QueryBuilder(new MySQLCompiler());
    $query = $builder
      ->from('menus')
      ->select('*')
      ->eq('key', $key)
      ->is('deleted_at', null)
      ->limit(1);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? Menu::fromArray($row) : null;
  }

  public function create(Menu $menu): int
  {
    $builder = new QueryBuilder(new MySQLCompiler());
    $query = $builder->from('menus')->insert([
      'key' => $menu->key,
      'label' => $menu->label,
      'description' => $menu->description,
      'type' => $menu->type,
      'sort_order' => $menu->sort_order,
      'created_at' => date('Y-m-d H:i:s'),
      'updated_at' => date('Y-m-d H:i:s'),
    ]);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return (int) $this->db->lastInsertId();
  }

  public function update(Menu $menu): bool
  {
    $builder = new QueryBuilder(new MySQLCompiler());
    $query = $builder
      ->from('menus')
      ->update([
        'key' => $menu->key,
        'label' => $menu->label,
        'description' => $menu->description,
        'type' => $menu->type,
        'sort_order' => $menu->sort_order,
        'updated_at' => date('Y-m-d H:i:s'),
      ])
      ->eq('id', $menu->id)
      ->is('deleted_at', null);

    $stmt = $this->db->prepare($query->toSql());
    return $stmt->execute($query->getBindings());
  }

  public function softDelete(int $id): bool
  {
    $builder = new QueryBuilder(new MySQLCompiler());
    $query = $builder
      ->from('menus')
      ->update(['deleted_at' => date('Y-m-d H:i:s')])
      ->eq('id', $id);

    $stmt = $this->db->prepare($query->toSql());
    return $stmt->execute($query->getBindings());
  }

  public function isKeyUnique(string $key, ?int $excludeId = null): bool
  {
    $builder = new QueryBuilder(new MySQLCompiler());
    $query = $builder
      ->from('menus')
      ->select('COUNT(id) as total')
      ->eq('key', $key)
      ->is('deleted_at', null);

    if ($excludeId !== null) {
      $query->neq('id', $excludeId);
    }

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int) ($row['total'] ?? 0) === 0;
  }

  public function getTotalCount(): int
  {
    $builder = new QueryBuilder(new MySQLCompiler());
    $query = $builder
      ->from('menus')
      ->select('COUNT(*)')
      ->is('deleted_at', null);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return (int) $stmt->fetchColumn();
  }

  // ── Menu Items ─────────────────────────────────────────────────────────────

  /** @return MenuItem[] */
  public function getItemsByMenuId(int $menuId): array
  {
    $builder = new QueryBuilder(new MySQLCompiler());
    $query = $builder
      ->from('menu_items')
      ->select('*')
      ->eq('menu_id', $menuId)
      ->is('deleted_at', null)
      ->order('sort_order', ['ascending' => true])
      ->order('id', ['ascending' => true]);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return array_map(fn($row) => MenuItem::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  public function getItemById(int $id): ?MenuItem
  {
    $builder = new QueryBuilder(new MySQLCompiler());
    $query = $builder
      ->from('menu_items')
      ->select('*')
      ->eq('id', $id)
      ->is('deleted_at', null)
      ->limit(1);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? MenuItem::fromArray($row) : null;
  }

  public function createItem(MenuItem $item): int
  {
    $builder = new QueryBuilder(new MySQLCompiler());
    $query = $builder->from('menu_items')->insert([
      'menu_id' => $item->menu_id,
      'parent_id' => $item->parent_id,
      'label' => $item->label,
      'url' => $item->url,
      'sort_order' => $item->sort_order,
      'created_at' => date('Y-m-d H:i:s'),
      'updated_at' => date('Y-m-d H:i:s'),
    ]);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return (int) $this->db->lastInsertId();
  }

  public function updateItem(MenuItem $item): bool
  {
    $builder = new QueryBuilder(new MySQLCompiler());
    $query = $builder
      ->from('menu_items')
      ->update([
        'parent_id' => $item->parent_id,
        'label' => $item->label,
        'url' => $item->url,
        'sort_order' => $item->sort_order,
        'updated_at' => date('Y-m-d H:i:s'),
      ])
      ->eq('id', $item->id)
      ->is('deleted_at', null);

    $stmt = $this->db->prepare($query->toSql());
    return $stmt->execute($query->getBindings());
  }

  public function softDeleteItem(int $id): bool
  {
    $builder = new QueryBuilder(new MySQLCompiler());
    $query = $builder
      ->from('menu_items')
      ->update(['deleted_at' => date('Y-m-d H:i:s')])
      ->eq('id', $id);

    $stmt = $this->db->prepare($query->toSql());
    return $stmt->execute($query->getBindings());
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

  public function sortItems(array $items): bool
  {
    if (empty($items)) {
      return true;
    }

    $this->db->beginTransaction();
    try {
      foreach ($items as $item) {
        $parentId = (isset($item['parent_id']) && $item['parent_id'] !== '' && $item['parent_id'] !== 'null') 
          ? (int) $item['parent_id'] 
          : null;
        $sortOrder = isset($item['sort_order']) ? (int) $item['sort_order'] : 0;
        $id = (int) $item['id'];

        $builder = new QueryBuilder(new MySQLCompiler());
        $query = $builder
          ->from('menu_items')
          ->update([
            'parent_id' => $parentId,
            'sort_order' => $sortOrder,
            'updated_at' => date('Y-m-d H:i:s')
          ])
          ->eq('id', $id)
          ->is('deleted_at', null);

        $stmt = $this->db->prepare($query->toSql());
        $stmt->execute($query->getBindings());
      }

      $this->db->commit();
      return true;
    } catch (\Exception $e) {
      $this->db->rollBack();
      return false;
    }
  }
}