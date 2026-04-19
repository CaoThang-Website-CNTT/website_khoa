<?php

namespace App\Models;

class Category
{
  /**
   * @param ?string $meta Chuỗi JSON thô.
   * @param ?Category $parent Được gán bởi service (eager-load).
   * @param Category[] $children Được gán bởi service (eager-load).
   */
  public function __construct(
    public ?int $id = null,
    public string $name = '',
    public ?string $slug = null,
    public string $type = 'custom',  // 'const' | 'custom'
    public ?string $description = null,
    public ?int $parent_id = null,
    public ?string $meta = null,
    public ?string $created_at = null,
    public ?string $updated_at = null,
    public ?string $deleted_at = null,
    public ?Category $parent = null,
    public array $children = [],
    public array $posts = [],
  ) {
  }

  public static function fromArray(array $row): static
  {
    return new static(
      id: isset($row['id']) ? (int) $row['id'] : null,
      name: $row['name'],
      slug: $row['slug'] ?? null,
      type: $row['type'] ?? 'custom',
      description: $row['description'] ?? null,
      parent_id: isset($row['parent_id']) ? (int) $row['parent_id'] : null,
      meta: $row['meta'] ?? null,
      created_at: $row['created_at'] ?? null,
      updated_at: $row['updated_at'] ?? null,
      deleted_at: $row['deleted_at'] ?? null,
    );
  }

  /** Giải mã JSON của trường meta, trả về mảng (mảng rỗng nếu null hoặc không hợp lệ). */
  public function metaArray(): array
  {
    if ($this->meta === null) {
      return [];
    }
    return json_decode($this->meta, true) ?? [];
  }
}