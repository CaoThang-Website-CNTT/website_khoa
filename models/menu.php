<?php

namespace App\Models;

class Menu extends Model
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

  public function isEditable(): bool
  {
    return $this->type === "custom";
  }
}