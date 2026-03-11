<?php

namespace App\Models;

class Classroom
{
  public function __construct(
    public int $id,
    public string $name,
    public ?string $description,
  ) {}
  /**
   * Tự động mapping trường dữ liệu DB
   * @param array $data
   * @return Classroom
   */
  public static function fromArray(array $data): self
  {
    return new self(
      id: $data['id'] ?? 0,
      name: $data['name'] ?? '',
      description: $data['description'] ?? ''
    );
  }
}