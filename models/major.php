<?php

namespace App\Models;

class Major
{
  public function __construct(
    public int $id,
    public string $full_name,
    public string $short_name,
    public string $level,
    public ?string $created_at = null,
    public ?string $updated_at = null,
    public ?string $deleted_at = null,
  ) {}
  /**
   * Tự động mapping trường dữ liệu DB
   * @param array $data
   * @return Major
   */
  public static function fromArray(array $data, string $prefix = ''): self
  {
    return new self(
      id: $data[$prefix . 'id'] ?? 0,
      full_name: $data[$prefix . 'full_name'] ?? '',
      short_name: $data[$prefix . 'short_name'] ?? '',
      level: $data[$prefix . 'level'] ?? '',
      created_at: $data[$prefix . 'created_at'] ?? null,
      updated_at: $data[$prefix . 'updated_at'] ?? null,
      deleted_at: $data[$prefix . 'deleted_at'] ?? null,
    );
  }
}