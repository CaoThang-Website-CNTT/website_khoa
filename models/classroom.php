<?php

namespace App\Models;

class Classroom
{
  public function __construct(
    public int $id,
    public string $level,
    public int $profession_id,
    public int $class_of,
    public ?int $major_id,
    public string $letter,
    public string $short_name,
    public ?string $created_at = null,
    public ?string $updated_at = null,
    public ?string $deleted_at = null,

    public ?Profession $profession = null,
    public ?Major $major = null,
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
      level: $data['level'] ?? '',
      profession_id: $data['profession_id'] ?? 0,
      class_of: $data['class_of'] ?? 0,
      major_id: $data['major_id'] ?? 0,
      letter: $data['letter'] ?? '',
      short_name: $data['short_name'] ?? '',
      created_at: $data['created_at'] ?? null,
      updated_at: $data['updated_at'] ?? null,
      deleted_at: $data['deleted_at'] ?? null,

      profession: isset($data['pro_full_name']) ? Profession::fromArray($data, 'pro_') : null,
      major: isset($data['maj_full_name']) ? Major::fromArray($data, 'maj_') : null
    );
  }
}
