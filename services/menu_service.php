<?php

namespace App\Services;

require_once BASE_PATH . '/stores/menu_store.php';
require_once BASE_PATH . '/models/menu.php';
require_once BASE_PATH . '/models/menu_item.php';
require_once BASE_PATH . '/includes/core/pageable.php';

use App\Stores\MenuStore;
use App\Models\{Menu, MenuItem};
use App\Core\Pageable;

interface IMenuService
{
  // ── Menus ──────────────────────────────────────────────────────────────────
  /** @return Menu[] Tất cả menu (chưa load items) */
  public function getAllMenus(): array;
  /** @return Pageable Tất cả menu (chưa load items)*/
  public function getMenus(int $page, int $limit = 15): Pageable;
  public function getMenuWithItems(int $id): ?Menu;
  public function getMenuByKeyWithItems(string $key): ?Menu;
  public function getMenuById(int $id): ?Menu;
  public function createMenu(array $data): int;
  public function updateMenu(int $id, array $data): bool;
  public function deleteMenu(int $id): bool;

  // ── Menu Items ─────────────────────────────────────────────────────────────
  public function getItemById(int $id): ?MenuItem;
  public function addItem(int $menuId, array $data): int;
  public function updateItem(int $id, array $data): bool;
  public function removeItem(int $id): bool;
  /** $orderMap = [itemId => sortOrder, ...] */
  public function reorderItems(array $orderMap): bool;
}

class MenuService implements IMenuService
{
  private MenuStore $_menuStore;

  public function __construct(MenuStore $menuStore)
  {
    $this->_menuStore = $menuStore;
  }

  // ── Menus ──────────────────────────────────────────────────────────────────

  /** @return Menu[] */
  public function getAllMenus(): array
  {
    return $this->_menuStore->getAll();
  }

  public function getMenuWithItems(int $id): ?Menu
  {
    $menu = $this->_menuStore->getById($id);
    if ($menu === null) {
      return null;
    }

    $menu->items = $this->_buildItemTree(
      $this->_menuStore->getItemsByMenuId($id)
    );

    return $menu;
  }

  public function getMenus(int $page, int $limit = 15): Pageable
  {
    $menus = $this->_menuStore->getPaginated($page, $limit);
    $total = $this->_menuStore->getTotalCount();
    return new Pageable($menus, $total, $limit, $page);
  }

  public function getMenuByKeyWithItems(string $key): ?Menu
  {
    $menu = $this->_menuStore->getByKey($key);
    if ($menu === null) {
      return null;
    }

    $menu->items = $this->_buildItemTree(
      $this->_menuStore->getItemsByMenuId($menu->id)
    );

    return $menu;
  }

  public function getMenuById(int $id): ?Menu
  {
    return $this->_menuStore->getById($id);
  }

  public function createMenu(array $data): int
  {
    $key = $data['key'];
    if (!$this->_menuStore->isKeyUnique($key)) {
      throw new \InvalidArgumentException("Menu key '$key' đã tồn tại.");
    }

    $menu = new Menu();
    $menu->key = $key;
    $menu->label = $data['label'];
    $menu->description = $data['description'] ?? null;
    $menu->type = $data['type'] ?? 'custom';
    $menu->sort_order = $data['sort_order'] ?? 0;

    return $this->_menuStore->create($menu);
  }

  public function updateMenu(int $id, array $data): bool
  {
    $menu = $this->_menuStore->getById($id);
    if ($menu === null) {
      return false;
    }

    if (isset($data['key']) && $data['key'] !== $menu->key) {
      if (!$this->_menuStore->isKeyUnique($data['key'], $id)) {
        throw new \InvalidArgumentException("Menu key '{$data['key']}' đã tồn tại.");
      }
      $menu->key = $data['key'];
    }

    $menu->label = $data['label'] ?? $menu->label;
    $menu->description = $data['description'] ?? $menu->description;
    $menu->type = $data['type'] ?? $menu->type;
    $menu->sort_order = $data['sort_order'] ?? $menu->sort_order;

    return $this->_menuStore->update($menu);
  }

  public function deleteMenu(int $id): bool
  {
    $menuDeleted = $this->_menuStore->softDelete($id);

    if ($menuDeleted) {
      $items = $this->_menuStore->getItemsByMenuId($id);

      foreach ($items as $item) {
        $this->_menuStore->softDeleteItem($item->id);
      }
    }

    return $menuDeleted;
  }

  // ── Menu Items ─────────────────────────────────────────────────────────────
  public function getItemById(int $id): ?MenuItem
  {
    return $this->_menuStore->getItemById($id);
  }
  public function addItem(int $menuId, array $data): int
  {
    if ($this->_menuStore->getById($menuId) === null) {
      throw new \RuntimeException("Menu #$menuId không tồn tại.");
    }

    $item = new MenuItem();
    $item->menu_id = $menuId;
    $item->parent_id = isset($data['parent_id']) ? (int) $data['parent_id'] : null;
    $item->label = $data['label'];
    $item->url = $data['url'];
    $item->sort_order = $data['sort_order'] ?? 0;

    return $this->_menuStore->createItem($item);
  }

  public function updateItem(int $id, array $data): bool
  {
    $item = $this->_menuStore->getItemById($id);
    if ($item === null) {
      return false;
    }

    $item->parent_id = array_key_exists('parent_id', $data)
      ? (isset($data['parent_id']) ? (int) $data['parent_id'] : null)
      : $item->parent_id;
    $item->label = $data['label'] ?? $item->label;
    $item->url = $data['url'] ?? $item->url;
    $item->sort_order = $data['sort_order'] ?? $item->sort_order;

    return $this->_menuStore->updateItem($item);
  }

  public function removeItem(int $id): bool
  {
    return $this->_menuStore->softDeleteItem($id);
  }

  public function reorderItems(array $orderMap): bool
  {
    return $this->_menuStore->reorderItems($orderMap);
  }

  // ── Private helpers ────────────────────────────────────────────────────────

  /**
   * Chuyển đổi danh sách MenuItem[] phẳng (đã sắp xếp theo sort_order) thành cây lồng nhau.
   * Các item gốc (parent_id === null) được trả về; con của chúng được gán
   * đệ quy qua $item->children.
   *
   * @param  MenuItem[] $flat
   * @return MenuItem[]
   */
  private function _buildItemTree(array $flat): array
  {
    $map = [];
    foreach ($flat as $item) {
      $item->children = [];
      $map[$item->id] = $item;
    }

    $roots = [];
    foreach ($map as $item) {
      if ($item->parent_id !== null && isset($map[$item->parent_id])) {
        $map[$item->parent_id]->children[] = $item;
      } else {
        $roots[] = $item;
      }
    }

    return $roots;
  }
}