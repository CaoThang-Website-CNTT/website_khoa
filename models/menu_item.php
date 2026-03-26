<?php

namespace App\Models;

class MenuItem
{
  /**
   * @param MenuItem[] $children Được gán bởi MenuService::_buildItemTree(), không phải store.
   */
  public function __construct(
    public ?int $id = null,
    public ?int $menu_id = null,
    public ?int $parent_id = null,
    public string $label = '',
    public string $url = '',
    public int $sort_order = 0,
    public ?string $created_at = null,
    public ?string $updated_at = null,
    public ?string $deleted_at = null,
    public array $children = [],
  ) {
  }

  public static function fromArray(array $row): static
  {
    return new static(
      id: isset($row['id']) ? (int) $row['id'] : null,
      menu_id: (int) $row['menu_id'],
      parent_id: isset($row['parent_id']) ? (int) $row['parent_id'] : null,
      label: $row['label'],
      url: $row['url'],
      sort_order: isset($row['sort_order']) ? (int) $row['sort_order'] : 0,
      created_at: $row['created_at'] ?? null,
      updated_at: $row['updated_at'] ?? null,
      deleted_at: $row['deleted_at'] ?? null,
    );
  }

  /** Trả về true nếu item không có cha (tức là item cấp cao nhất). */
  public function isRoot(): bool
  {
    return $this->parent_id === null;
  }

  /** Trả về true nếu service đã gán ít nhất một item con cho item này. */
  public function hasChildren(): bool
  {
    return !empty($this->children);
  }

  /**
   * Trả về true nếu URL của item khớp với URL hiện tại.
   * Dùng trong template để đánh dấu nav link đang active.
   *
   * @param string $currentUrl URL hiện tại, thường là $_SERVER['REQUEST_URI']
   */
  public function isActive(string $currentUrl): bool
  {
    return rtrim($this->url, '/') === rtrim($currentUrl, '/');
  }
}