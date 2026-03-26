<?php

namespace App\Models;

class Specialization
{
  public function __construct(
    public ?int $id = null,
    public ?int $major_id = null,
    public ?string $full_name = null,
    public ?string $short_name = null,
    public ?string $created_at = null,
    public ?string $updated_at = null,
    public ?string $deleted_at = null,

    public ?Major $major = null
  ) {
  }

  /**
   * Tự động mapping trường dữ liệu DB
   * @param array $data
   * @return Specialization
   */
  public static function fromArray(array $data): self
  {
    return new self(
      id: $data['id'] ?? null,
      major_id: $data['major_id'] ?? null,
      full_name: $data['full_name'] ?? null,
      short_name: $data['short_name'] ?? null,
      created_at: $data['created_at'] ?? null,
      updated_at: $data['updated_at'] ?? null,
      deleted_at: $data['deleted_at'] ?? null,
    );
  }
}