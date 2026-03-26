<?php

namespace App\Models;

class Menu
{
  /**
   * @param MenuItem[] $items Được gán bởi service (eager-load).
   */
  public function __construct(
    public ?int $id = null,
    public string $key = '',
    public string $label = '',
    public ?string $description = null,
    public string $type = 'const',   // 'const' | 'custom'
    public int $sort_order = 0,
    public ?string $created_at = null,
    public ?string $updated_at = null,
    public ?string $deleted_at = null,
    public array $items = [],
  ) {
  }

  public static function fromArray(array $row): static
  {
    return new static(
      id: isset($row['id']) ? (int) $row['id'] : null,
      key: $row['key'],
      label: $row['label'],
      description: $row['description'] ?? null,
      type: $row['type'] ?? 'const',
      sort_order: isset($row['sort_order']) ? (int) $row['sort_order'] : 0,
      created_at: $row['created_at'] ?? null,
      updated_at: $row['updated_at'] ?? null,
      deleted_at: $row['deleted_at'] ?? null,
    );
  }
}