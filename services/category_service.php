<?php

namespace App\Services;

require_once BASE_PATH . '/models/category.php';
require_once BASE_PATH . '/db/database.php';

use App\Models\Category;
use Database;
use PDO;

// ============================================================================
// Interface
// ============================================================================
interface ICategoryRepository
{
  /** @return Category[] */
  public function getAllCategories(int $pageTo, int $limit = 15): array;

  /** @return Category[] */
  public function getRoots(): array;

  /** @return Category[] */
  public function getChildren(int $parentId): array;

  public function getById(int $id): ?Category;
  public function getBySlug(string $slug): ?Category;

  public function create(array $data): int;
  public function update(int $id, array $data): bool;
  public function delete(int $id): bool;
  public function getTotalCategoriesCount(): int;

  public function isSlugUnique(string $slug, ?int $excludeId = null): bool;
}

class CategoryService implements ICategoryRepository
{
  private $db;

  public function __construct()
  {
    $this->db = Database::getInstance()->getConnection();
  }

  /**
   * Lấy tất cả danh mục chưa bị xóa theo thứ tự cây (cha trước, con sau).
   * Sử dụng Recursive CTE để duyệt cây và tính depth, path cho mỗi node.
   * Kết quả được sắp xếp theo path — đảm bảo đúng thứ tự ở mọi độ sâu.
   *
   * @public
   * @return Category[] Danh sách danh mục đã được sắp xếp phẳng theo cấu trúc cây
   */
  public function getAllCategories(int $pageTo, int $limit = 15): array
  {
    $offset = (max(1, $pageTo) - 1) * $limit;

    $stmt = $this->db->prepare("
      WITH RECURSIVE category_tree AS (
        -- Anchor: root nodes (parent_id IS NULL)
        SELECT
          *,
          0 AS depth,
          CAST(LPAD(id, 10, '0') AS CHAR(1000)) AS path
        FROM `categories`
        WHERE `parent_id` IS NULL
          AND `deleted_at` IS NULL

        UNION ALL

        -- Recursive: children join to their parent
        SELECT
          c.*,
          ct.depth + 1,
          CONCAT(ct.path, '/', LPAD(c.id, 10, '0'))
        FROM `categories` c
        INNER JOIN category_tree ct ON c.parent_id = ct.id
        WHERE c.deleted_at IS NULL
      )
      SELECT * FROM category_tree
      ORDER BY path
      LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();

    return array_map(fn($row) => Category::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  /**
   * Lấy các danh mục gốc.
   *
   * @public
   * @return Category[]
   */
  public function getRoots(): array
  {
    $stmt = $this->db->prepare("
      SELECT * FROM `categories`
      WHERE `parent_id` IS NULL AND `deleted_at` IS NULL
      ORDER BY `id` ASC
    ");
    $stmt->execute();

    return array_map(fn($row) => Category::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  /**
   * Lấy các danh mục con trực tiếp của một danh mục cha.
   *
   * @public
   * @param int $parentId ID của danh mục cha
   * @return Category[]
   */
  public function getChildren(int $parentId): array
  {
    $stmt = $this->db->prepare("
      SELECT * FROM `categories`
      WHERE `parent_id` = :parent_id AND `deleted_at` IS NULL
      ORDER BY `id` ASC
    ");
    $stmt->execute([':parent_id' => $parentId]);

    return array_map(fn($row) => Category::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  /**
   * Tìm một danh mục theo ID.
   *
   * @public
   * @param int $id ID của danh mục
   * @return Category|null Trả về null nếu không tìm thấy hoặc đã bị xóa
   */
  public function getById(int $id): ?Category
  {
    $stmt = $this->db->prepare("
      SELECT * FROM `categories`
      WHERE `id` = :id AND `deleted_at` IS NULL
    ");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? Category::fromArray($row) : null;
  }

  /**
   * Tìm một danh mục theo slug.
   *
   * @public
   * @param string $slug Slug của danh mục
   * @return Category|null Trả về null nếu không tìm thấy hoặc đã bị xóa
   */
  public function getBySlug(string $slug): ?Category
  {
    $stmt = $this->db->prepare("
      SELECT * FROM `categories`
      WHERE `slug` = :slug AND `deleted_at` IS NULL
    ");
    $stmt->execute([':slug' => $slug]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? Category::fromArray($row) : null;
  }

  /**
   * Tạo mới một danh mục.
   *
   * @public
   * @param array $data Dữ liệu danh mục gồm name, slug, description, parent_id, meta
   * @return int ID của danh mục vừa tạo
   */
  public function create(array $data): int
  {
    $stmt = $this->db->prepare("
      INSERT INTO `categories` (`name`, `slug`, `description`, `parent_id`, `meta`)
      VALUES (:name, :slug, :description, :parent_id, :meta)
    ");
    $stmt->execute([
      ':name' => $data['name'],
      ':slug' => $data['slug'] ?? null,
      ':description' => $data['description'] ?? null,
      ':parent_id' => $data['parent_id'] ?? null,
      ':meta' => isset($data['meta']) ? json_encode($data['meta']) : null,
    ]);

    return (int) $this->db->lastInsertId();
  }

  /**
   * Cập nhật thông tin một danh mục theo ID.
   *
   * @public
   * @param int $id ID của danh mục cần cập nhật
   * @param array $data Dữ liệu mới gồm name, slug, description, parent_id, meta
   * @return bool True nếu cập nhật thành công
   */
  public function update(int $id, array $data): bool
  {
    $stmt = $this->db->prepare("
      UPDATE `categories` SET
        `name`        = :name,
        `slug`        = :slug,
        `description` = :description,
        `parent_id`   = :parent_id,
        `meta`        = :meta,
        `updated_at`  = NOW()
      WHERE `id` = :id AND `deleted_at` IS NULL
    ");

    return $stmt->execute([
      ':name' => $data['name'],
      ':slug' => $data['slug'] ?? null,
      ':description' => $data['description'] ?? null,
      ':parent_id' => $data['parent_id'] ?? null,
      ':meta' => isset($data['meta']) ? json_encode($data['meta']) : null,
      ':id' => $id,
    ]);
  }

  /**
   * Xóa mềm một danh mục bằng cách ghi nhận thời gian xóa.
   *
   * @public
   * @param int $id ID của danh mục cần xóa
   * @return bool True nếu xóa thành công
   */
  public function delete(int $id): bool
  {
    $stmt = $this->db->prepare("
      UPDATE `categories` SET `deleted_at` = NOW()
      WHERE `id` = :id AND `deleted_at` IS NULL
    ");

    return $stmt->execute([':id' => $id]);
  }

  /**
   * Kiểm tra slug có duy nhất trong bảng categories hay không.
   * Có thể loại trừ một ID cụ thể khi dùng cho trường hợp cập nhật.
   *
   * @public
   * @param string $slug Slug cần kiểm tra
   * @param int|null $excludeId ID danh mục cần loại trừ (dùng khi update)
   * @return bool True nếu slug chưa được dùng
   */
  public function isSlugUnique(string $slug, ?int $excludeId = null): bool
  {
    $sql = "SELECT COUNT(*) FROM `categories` WHERE `slug` = :slug AND `deleted_at` IS NULL";
    $params = [':slug' => $slug];

    if ($excludeId) {
      $sql .= " AND `id` != :exclude_id";
      $params[':exclude_id'] = $excludeId;
    }

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchColumn() == 0;
  }
  public function getTotalCategoriesCount(): int
  {
    $sql = "SELECT COUNT(c.id) 
            FROM `categories` c
            WHERE c.`deleted_at` IS NULL";

    $stmt = $this->db->query($sql);

    return (int) $stmt->fetchColumn();
  }
}