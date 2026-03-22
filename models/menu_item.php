<?php

namespace App\Models;

class MenuItem
{
  public function __construct(
    public int $id,
    public int $menu_id,
    public ?int $parent_id,
    public string $label,
    public string $url,
    public int $sort_order,
    public string $created_at,
    public string $updated_at,
    public ?string $deleted_at,

    // Các trường bổ sung từ Recursive CTE (có thể null nếu query thường)
    public ?int $depth = null,
    public ?string $path = null,

    /** @var MenuItem[] */
    public array $children = [],
    public string $order_state = 'can_reorder'
  ) {
  }

  /**
   * Tạo một instance MenuItem từ mảng dữ liệu (thường từ PDO fetch).
   *
   * @param array $row Mảng dữ liệu từ database
   * @return self
   */
  public static function fromArray(array $row): self
  {
    return new self(
      id: (int) $row['id'],
      menu_id: (int) $row['menu_id'],
      parent_id: isset($row['parent_id']) ? (int) $row['parent_id'] : null,
      label: $row['label'],
      url: $row['url'],
      sort_order: (int) $row['sort_order'],
      created_at: $row['created_at'],
      updated_at: $row['updated_at'],
      deleted_at: $row['deleted_at'] ?? null,
      depth: isset($row['depth']) ? (int) $row['depth'] : null,
      path: $row['path'] ?? null,
    );
  }

  /**
   * Kiểm tra item có phải là root (không có cha) không.
   *
   * @return bool
   */
  public function isRoot(): bool
  {
    return $this->parent_id === null;
  }

  /**
   * Kiểm tra item có chứa item con không.
   *
   * @return bool
   */
  public function hasChildren(): bool
  {
    return !empty($this->children);
  }

  /**
   * Kiểm tra URL hiện tại có khớp với item này không.
   * Dùng để đánh dấu active trên nav.
   *
   * @param string $currentUrl URL hiện tại, thường là $_SERVER['REQUEST_URI']
   * @return bool
   */
  public function isActive(string $currentUrl): bool
  {
    return rtrim($this->url, '/') === rtrim($currentUrl, '/');
  }
}