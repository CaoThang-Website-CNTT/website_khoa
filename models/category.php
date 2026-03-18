<?php

namespace App\Models;

class Category
{
  public function __construct(
    public int $id,
    public string $name,
    public ?string $slug,
    public ?string $description,
    public ?int $parent_id,
    public ?string $meta,
    public ?string $created_at,
    public ?string $updated_at,
    public ?string $deleted_at,

    // Referenced data
    public ?Category $parent_category = null,

    // Độ sâu (thuộc tính tự do của models không phản ánh schema)
    public int $depth = 0,
  ) {
  }

  /**
   * Tự động mapping trường dữ liệu DB
   * @param array $data
   * @return Category
   */
  public static function fromArray(array $data): self
  {
    return new self(
      id: $data['id'] ?? 0,
      name: $data['name'] ?? '',
      slug: $data['slug'] ?? null,
      description: $data['description'] ?? null,
      parent_id: isset($data['parent_id']) ? (int) $data['parent_id'] : null,
      meta: isset($data['meta']) ? json_decode($data['meta'], true) : null,
      created_at: $data['created_at'] ?? null,
      updated_at: $data['updated_at'] ?? null,
      deleted_at: $data['deleted_at'] ?? null,
    );
  }
}