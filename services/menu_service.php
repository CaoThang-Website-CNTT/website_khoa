<?php

namespace App\Services;

require_once BASE_PATH . '/models/menu.php';
require_once BASE_PATH . '/models/menu_item.php';
require_once BASE_PATH . '/db/database.php';

use App\Models\Menu;
use App\Models\MenuItem;
use Database;
use PDO;

// ============================================================================
// Interface
// ============================================================================
interface IMenuRepository
{
  // -- Menus ------------------------------------------------------------------

  /** @return Menu[] */
  public function getAllMenus(): array;

  public function getMenuById(int $id): ?Menu;
  public function getMenuByKey(string $key): ?Menu;

  public function createMenu(array $data): int;
  public function updateMenu(int $id, array $data): bool;
  public function deleteMenu(int $id): bool;

  public function isKeyUnique(string $key, ?int $excludeId = null): bool;

  // -- Menu Items ------------------------------------------------------------

  /**
   * Lấy toàn bộ menu_items dạng cây lồng nhau.
   * Dùng để render header nav: root items là mục ngoài cùng,
   * children là dropdown cấp 1, children của children là dropdown cấp 2, v.v.
   *
   * @return MenuItem[]
   */
  public function getItemsTree(int $menuId): array;

  /**
   * Lấy toàn bộ menu_items dạng phẳng có depth và path.
   * Dùng trong admin editor để hiển thị cây có thụt lề theo độ sâu.
   *
   * @return MenuItem[]
   */
  public function getItemsFlat(int $menuId): array;

  public function getItemById(int $id): ?MenuItem;

  public function createItem(array $data): int;
  public function updateItem(int $id, array $data): bool;
  public function deleteItem(int $id): bool;

  public function reorderItems(int $moveId, string $direction): bool;
}

// ============================================================================
// Service
// ============================================================================
class MenuService implements IMenuRepository
{
  private $db;

  public function __construct()
  {
    $this->db = Database::getInstance()->getConnection();
  }

  // --------------------------------------------------------------------------
  // Menus
  // --------------------------------------------------------------------------

  /**
   * Lấy tất cả nhóm menu, sắp xếp theo sort_order.
   *
   * @public
   * @return Menu[]
   */
  public function getAllMenus(): array
  {
    $stmt = $this->db->prepare("
      SELECT * FROM `menus`
      ORDER BY `sort_order` ASC, `id` ASC
    ");
    $stmt->execute();

    return array_map(fn($row) => Menu::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  /**
   * Tìm một nhóm menu theo ID.
   *
   * @public
   * @param int $id ID của nhóm menu
   * @return Menu|null Trả về null nếu không tìm thấy
   */
  public function getMenuById(int $id): ?Menu
  {
    $stmt = $this->db->prepare("
      SELECT * FROM `menus` WHERE `id` = :id
    ");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? Menu::fromArray($row) : null;
  }

  /**
   * Tìm một nhóm menu theo key (dùng trong code để render nav).
   * Ví dụ: getMenuByKey('main_nav')
   *
   * @public
   * @param string $key Key của nhóm menu
   * @return Menu|null Trả về null nếu không tìm thấy
   */
  public function getMenuByKey(string $key): ?Menu
  {
    $stmt = $this->db->prepare("
      SELECT * FROM `menus` WHERE `key` = :key
    ");
    $stmt->execute([':key' => $key]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? Menu::fromArray($row) : null;
  }

  /**
   * Tạo mới một nhóm menu.
   *
   * @public
   * @param array $data Dữ liệu gồm key, label, description, type, sort_order
   * @return int ID của nhóm menu vừa tạo
   */
  public function createMenu(array $data): int
  {
    $stmt = $this->db->prepare("
      INSERT INTO `menus` (`key`, `label`, `description`, `type`, `sort_order`)
      VALUES (:key, :label, :description, :type, :sort_order)
    ");
    $stmt->execute([
      ':key' => $data['key'],
      ':label' => $data['label'],
      ':description' => $data['description'] ?? null,
      ':type' => $data['type'] ?? 'custom',
      ':sort_order' => $data['sort_order'] ?? 0,
    ]);

    return (int) $this->db->lastInsertId();
  }

  /**
   * Cập nhật thông tin một nhóm menu theo ID.
   * Chỉ áp dụng cho menu có type = 'custom' — việc kiểm tra do controller đảm nhiệm.
   *
   * @public
   * @param int $id ID của nhóm menu cần cập nhật
   * @param array $data Dữ liệu mới gồm key, label, description, sort_order
   * @return bool True nếu cập nhật thành công
   */
  public function updateMenu(int $id, array $data): bool
  {
    $stmt = $this->db->prepare("
      UPDATE `menus` SET
        `key`         = :key,
        `label`       = :label,
        `description` = :description,
        `sort_order`  = :sort_order,
        `updated_at`  = NOW()
      WHERE `id` = :id
    ");

    return $stmt->execute([
      ':key' => $data['key'],
      ':label' => $data['label'],
      ':description' => $data['description'] ?? null,
      ':sort_order' => $data['sort_order'] ?? 0,
      ':id' => $id,
    ]);
  }

  /**
   * Xóa cứng một nhóm menu theo ID.
   * Tất cả menu_items thuộc nhóm này sẽ bị xóa theo do ON DELETE CASCADE.
   * Chỉ áp dụng cho menu có type = 'custom' — việc kiểm tra do controller đảm nhiệm.
   *
   * @public
   * @param int $id ID của nhóm menu cần xóa
   * @return bool True nếu xóa thành công
   */
  public function deleteMenu(int $id): bool
  {
    $stmt = $this->db->prepare("
      DELETE FROM `menus` WHERE `id` = :id
    ");

    return $stmt->execute([':id' => $id]);
  }

  /**
   * Kiểm tra key có duy nhất trong bảng menus hay không.
   * Có thể loại trừ một ID cụ thể khi dùng cho trường hợp cập nhật.
   *
   * @public
   * @param string $key Key cần kiểm tra
   * @param int|null $excludeId ID nhóm menu cần loại trừ (dùng khi update)
   * @return bool True nếu key chưa được dùng
   */
  public function isKeyUnique(string $key, ?int $excludeId = null): bool
  {
    $sql = "SELECT COUNT(*) FROM `menus` WHERE `key` = :key";
    $params = [':key' => $key];

    if ($excludeId) {
      $sql .= " AND `id` != :exclude_id";
      $params[':exclude_id'] = $excludeId;
    }

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchColumn() == 0;
  }

  // --------------------------------------------------------------------------
  // Menu Items
  // --------------------------------------------------------------------------

  /**
   * Lấy toàn bộ menu_items của một menu dưới dạng cây lồng nhau.
   *
   * Đây là method chính dùng để render header nav. Kết quả trả về là
   * danh sách root items (sort_order ASC), mỗi item có thể có children
   * là dropdown cấp 1, children của children là dropdown cấp 2, v.v.
   *
   * Ví dụ sử dụng trong layout:
   *   $menuService->getItemsTree('main_nav') → render nav-menu.php
   *
   * @public
   * @param int $menuId ID của nhóm menu
   * @return MenuItem[] Root items, mỗi item có ->children đã được populate
   */
  public function getItemsTree(int $menuId): array
  {
    $flat = $this->getItemsFlat($menuId);
    return $this->buildTree($flat);
  }

  /**
   * Lấy toàn bộ menu_items của một menu theo thứ tự cây (cha trước, con sau).
   * Sử dụng Recursive CTE để duyệt cây và tính depth, path cho mỗi node.
   * Kết quả phẳng, sắp xếp theo path — dùng trong admin editor để hiển thị
   * cây có thụt lề theo depth.
   *
   * @public
   * @param int $menuId ID của nhóm menu
   * @return MenuItem[] Danh sách phẳng đã sắp xếp theo cấu trúc cây
   */
  public function getItemsFlat(int $menuId): array
  {
    $stmt = $this->db->prepare("
      WITH RECURSIVE menu_tree AS (
        -- Anchor: root items (parent_id IS NULL), sắp xếp theo sort_order
        SELECT
          *,
          0 AS depth,
          CAST(LPAD(sort_order, 5, '0') AS CHAR(1000)) AS path
        FROM `menu_items`
        WHERE `menu_id`    = :menu_id
          AND `parent_id`  IS NULL
          AND `deleted_at` IS NULL

        UNION ALL

        -- Recursive: children join to their parent
        SELECT
          mi.*,
          mt.depth + 1,
          CONCAT(mt.path, '/', LPAD(mi.sort_order, 5, '0'))
        FROM `menu_items` mi
        INNER JOIN menu_tree mt ON mi.parent_id = mt.id
        WHERE mi.deleted_at IS NULL
      )
      SELECT * FROM menu_tree
      ORDER BY path
    ");
    $stmt->execute([':menu_id' => $menuId]);

    return array_map(fn($row) => MenuItem::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  /**
   * Tìm một menu_item theo ID.
   *
   * @public
   * @param int $id ID của menu_item
   * @return MenuItem|null Trả về null nếu không tìm thấy hoặc đã bị xóa
   */
  public function getItemById(int $id): ?MenuItem
  {
    $stmt = $this->db->prepare("
      SELECT * FROM `menu_items`
      WHERE `id` = :id AND `deleted_at` IS NULL
    ");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? MenuItem::fromArray($row) : null;
  }

  /**
   * Tạo mới một menu_item.
   * sort_order mặc định là sau item cuối cùng trong cùng cấp (parent).
   *
   * @public
   * @param array $data Dữ liệu gồm menu_id, parent_id, label, url, sort_order
   * @return int ID của menu_item vừa tạo
   */
  public function createItem(array $data): int
  {
    $sortOrder = $data['sort_order'] ?? $this->nextSortOrder(
      (int) $data['menu_id'],
      isset($data['parent_id']) ? (int) $data['parent_id'] : null
    );

    $stmt = $this->db->prepare("
      INSERT INTO `menu_items` (`menu_id`, `parent_id`, `label`, `url`, `sort_order`)
      VALUES (:menu_id, :parent_id, :label, :url, :sort_order)
    ");
    $stmt->execute([
      ':menu_id' => $data['menu_id'],
      ':parent_id' => $data['parent_id'] ?? null,
      ':label' => $data['label'],
      ':url' => $data['url'],
      ':sort_order' => $sortOrder,
    ]);

    return (int) $this->db->lastInsertId();
  }

  /**
   * Cập nhật thông tin một menu_item theo ID.
   *
   * @public
   * @param int $id ID của menu_item cần cập nhật
   * @param array $data Dữ liệu mới gồm parent_id, label, url, sort_order
   * @return bool True nếu cập nhật thành công
   */
  public function updateItem(int $id, array $data): bool
  {
    $stmt = $this->db->prepare("
      UPDATE `menu_items` SET
        `parent_id`  = :parent_id,
        `label`      = :label,
        `url`        = :url,
        `sort_order` = :sort_order,
        `updated_at` = NOW()
      WHERE `id` = :id AND `deleted_at` IS NULL
    ");

    return $stmt->execute([
      ':parent_id' => $data['parent_id'] ?? null,
      ':label' => $data['label'],
      ':url' => $data['url'],
      ':sort_order' => $data['sort_order'] ?? 0,
      ':id' => $id,
    ]);
  }

  /**
   * Xóa mềm một menu_item và toàn bộ con cháu của nó.
   *
   * ON DELETE CASCADE trên DB chỉ hoạt động với hard delete. Vì ta dùng
   * soft delete (deleted_at), cần tự tay đánh dấu tất cả con cháu trong
   * một transaction — nếu không chúng sẽ còn trong DB với parent_id trỏ
   * đến item đã bị soft-delete, gây ra orphan data trong CTE.
   *
   * @public
   * @param int $id ID của menu_item gốc cần xóa
   * @return bool True nếu toàn bộ xóa thành công
   */
  public function deleteItem(int $id): bool
  {
    $this->db->beginTransaction();
    try {
      // Thu thập tất cả ID con cháu bằng Recursive CTE
      $stmt = $this->db->prepare("
        WITH RECURSIVE descendants AS (
          SELECT `id` FROM `menu_items`
          WHERE `id` = :id AND `deleted_at` IS NULL

          UNION ALL

          SELECT mi.`id` FROM `menu_items` mi
          INNER JOIN descendants d ON mi.parent_id = d.id
          WHERE mi.deleted_at IS NULL
        )
        SELECT `id` FROM descendants
      ");
      $stmt->execute([':id' => $id]);
      $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

      if (empty($ids)) {
        $this->db->rollBack();
        return false;
      }

      // Soft-delete tất cả cùng lúc
      $placeholders = implode(',', array_fill(0, count($ids), '?'));
      $stmt = $this->db->prepare("
        UPDATE `menu_items` SET `deleted_at` = NOW()
        WHERE `id` IN ($placeholders) AND `deleted_at` IS NULL
      ");
      $stmt->execute($ids);

      $this->db->commit();
      return true;
    } catch (\Exception $e) {
      $this->db->rollBack();
      return false;
    }
  }

  /**
   * Di chuyển một menu_item lên hoặc xuống một bậc trong cùng cấp (parent_id).
   * Hoán đổi sort_order của item đó với item kề trên hoặc kề dưới.
   *
   * @public
   * @param int $moveId ID của menu_item cần di chuyển
   * @param string $direction 'up' hoặc 'down'
   * @return bool True nếu hoán đổi thành công
   */
  public function reorderItems(int $moveId, string $direction): bool
  {
    // Lấy item cần di chuyển
    $stmt = $this->db->prepare("
      SELECT `id`, `menu_id`, `parent_id`, `sort_order`
      FROM `menu_items`
      WHERE `id` = :id AND `deleted_at` IS NULL
    ");
    $stmt->execute([':id' => $moveId]);
    $target = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$target)
      return false;

    // Tìm item kề cạnh trong cùng cấp (cùng menu_id + parent_id)
    // Up   → item có sort_order lớn nhất nhưng nhỏ hơn target
    // Down → item có sort_order nhỏ nhất nhưng lớn hơn target
    if ($direction === 'up') {
      $stmt = $this->db->prepare("
        SELECT `id`, `sort_order`
        FROM `menu_items`
        WHERE `menu_id`    = :menu_id
          AND `parent_id`  <=> :parent_id
          AND `sort_order` < :sort_order
          AND `deleted_at` IS NULL
        ORDER BY `sort_order` DESC
        LIMIT 1
      ");
    } else {
      $stmt = $this->db->prepare("
        SELECT `id`, `sort_order`
        FROM `menu_items`
        WHERE `menu_id`    = :menu_id
          AND `parent_id`  <=> :parent_id
          AND `sort_order` > :sort_order
          AND `deleted_at` IS NULL
        ORDER BY `sort_order` ASC
        LIMIT 1
      ");
    }

    $stmt->execute([
      ':menu_id' => $target['menu_id'],
      ':parent_id' => $target['parent_id'],
      ':sort_order' => $target['sort_order'],
    ]);
    $neighbour = $stmt->fetch(PDO::FETCH_ASSOC);

    // No neighbour in that direction — already at the boundary
    if (!$neighbour)
      return false;

    // Swap sort_order between the two items in a transaction
    $swap = $this->db->prepare("
      UPDATE `menu_items` SET
        `sort_order` = :sort_order,
        `updated_at` = NOW()
      WHERE `id` = :id AND `deleted_at` IS NULL
    ");

    $this->db->beginTransaction();
    try {
      $swap->execute([':sort_order' => $neighbour['sort_order'], ':id' => $target['id']]);
      $swap->execute([':sort_order' => $target['sort_order'], ':id' => $neighbour['id']]);
      $this->db->commit();
      return true;
    } catch (\Exception $e) {
      $this->db->rollBack();
      return false;
    }
  }

  // --------------------------------------------------------------------------
  // Private helpers
  // --------------------------------------------------------------------------

  /**
   * Chuyển danh sách phẳng MenuItem[] thành cây lồng nhau dựa trên parent_id.
   * Dùng index map để đạt O(n) thay vì O(n²).
   *
   * @param MenuItem[] $flat Danh sách phẳng từ getItemsFlat()
   * @return MenuItem[] Root items với children đã được populate
   */
  private function buildTree(array $flat): array
  {
    // Index tất cả items theo id để tra cứu O(1)
    $map = [];
    foreach ($flat as $item) {
      $map[$item->id] = $item;
    }

    $siblings = [];
    foreach ($flat as $item) {
      $key = $item->parent_id ?? 'root';
      $siblings[$key][] = $item->id;
    }

    $roots = [];
    foreach ($flat as $item) {
      if ($item->parent_id === null) {
        $roots[] = $item;
      } else if (isset($map[$item->parent_id])) {
        $map[$item->parent_id]->children[] = $item;
      }
    }

    foreach ($siblings as $group) {
      $count = count($group);
      foreach ($group as $index => $id) {
        if (!isset($map[$id]))
          continue;
        $d = ($index === 0);
        $isLast = ($index === $count - 1);

        $map[$id]->order_state = match (true) {
          $d && $isLast => 'no_reorder',
          $d => 'no_up',
          $isLast => 'no_down',
          default => 'can_reorder',
        };
      }
    }

    return $roots;
  }

  /**
   * Tính sort_order tiếp theo cho item mới trong cùng cấp (menu_id + parent_id).
   * Trả về max(sort_order) + 1, hoặc 1 nếu chưa có item nào.
   *
   * @param int $menuId
   * @param int|null $parent_id
   * @return int
   */
  private function nextSortOrder(int $menuId, ?int $parent_id): int
  {
    $stmt = $this->db->prepare("
      SELECT COALESCE(MAX(`sort_order`), 0) + 1
      FROM `menu_items`
      WHERE `menu_id`   = :menu_id
        AND `parent_id` <=> :parent_id
        AND `deleted_at` IS NULL
    ");
    $stmt->execute([
      ':menu_id' => $menuId,
      ':parent_id' => $parent_id,
    ]);

    return (int) $stmt->fetchColumn();
  }
}