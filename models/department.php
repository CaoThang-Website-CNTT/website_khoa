<?php

namespace App\Models;

class Department extends Model
{
  public function __construct(
    public ?int $id = null,
    public string $full_name = '',
    public ?string $short_name = null,
    public ?string $description = null,
    public ?string $created_at = null,
    public ?string $updated_at = null,
    public ?string $deleted_at = null
  ) {}

  public static function fromArray(array $data): static
  {
    return new static(
      id: $data['id'] ?? null,
      full_name: $data['full_name'] ?? '',
      short_name: $data['short_name'] ?? null,
      description: $data['description'] ?? null,
      created_at: $data['created_at'] ?? null,
      updated_at: $data['updated_at'] ?? null,
      deleted_at: $data['deleted_at'] ?? null
    );
  }
}
