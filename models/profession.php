<?php

namespace App\Models;

class Profession
{
  public function __construct(
    public int $id,
    public string $full_name,
    public string $short_name,
    public ?string $created_at = null,
    public ?string $updated_at = null,
    public ?string $deleted_at = null,
  ) {}
  /**
   * Tự động mapping trường dữ liệu DB
   * @param array $data
   * @return Profession
   */
  public static function fromArray(array $data): self
  {
    return new self(
      id: $data['id'] ?? 0,
      full_name: $data['full_name'] ?? '',
      short_name: $data['short_name'] ?? '',
      created_at: $data['created_at'] ?? null,
      updated_at: $data['updated_at'] ?? null,
      deleted_at: $data['deleted_at'] ?? null
    );
  }
}
