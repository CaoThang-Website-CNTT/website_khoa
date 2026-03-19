<?php

namespace App\Models;

class Major
{
  public function __construct(
    public int $id,
    public int $profession_id,
    public string $full_name,
    public string $short_name,
    public ?string $created_at = null,
    public ?string $updated_at = null,
    public ?string $deleted_at = null,

    public ?Profession $profession = null
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
      profession_id: $data[$prefix . 'profession_id'] ?? 0,
      full_name: $data[$prefix . 'full_name'] ?? '',
      short_name: $data[$prefix . 'short_name'] ?? '',
      created_at: $data[$prefix . 'created_at'] ?? null,
      updated_at: $data[$prefix . 'updated_at'] ?? null,
      deleted_at: $data[$prefix . 'deleted_at'] ?? null,

      profession: isset($data['pro_full_name']) ? Profession::fromArray($data, 'pro_') : null
    );
  }
}
