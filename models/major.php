<?php

namespace App\Models;

class Major
{
  public function __construct(
    public ?int $id = null,
    public ?string $full_name = null,
    public ?string $short_name = null,
    public ?string $level = null,
    public ?string $created_at = null,
    public ?string $updated_at = null,
    public ?string $deleted_at = null,
  ) {
  }

  /**
   * Tự động mapping trường dữ liệu DB
   * @param array $data
   * @return Major
   */
  public static function fromArray(array $data): self
  {
    return new self(
      id: $data['id'] ?? null,
      full_name: $data['full_name'] ?? null,
      short_name: $data['short_name'] ?? null,
      level: $data['level'] ?? null,
      created_at: $data['created_at'] ?? null,
      updated_at: $data['updated_at'] ?? null,
      deleted_at: $data['deleted_at'] ?? null,
    );
  }
}