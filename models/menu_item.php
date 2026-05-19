<?php

namespace App\Models;

class MenuItem extends Model
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