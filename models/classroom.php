<?php

namespace App\Models;

class Classroom
{
  public function __construct(
    public int $id,
    public int $major_id,
    public int $class_of,
    public ?int $specialization_id,
    public string $letter,
    public string $short_name,
    public ?string $created_at = null,
    public ?string $updated_at = null,
    public ?string $deleted_at = null,

    public ?Major $major = null,
    public ?Specialization $specialization = null,
  ) {}
  public ?int $student_count;
  /**
   * Tự động mapping trường dữ liệu DB
   * @param array $data
   * @return Classroom
   */
  public static function fromArray(array $data, ?string $prefix = ''): self
  {
    $c = new self(
      id: $data[$prefix . 'id'] ?? 0,
      major_id: $data[$prefix . 'major_id'] ?? 0,
      class_of: $data[$prefix . 'class_of'] ?? 0,
      specialization_id: $data[$prefix . 'specialization_id'] ?? 0,
      letter: $data[$prefix . 'letter'] ?? '',
      short_name: $data[$prefix . 'short_name'] ?? '',
      created_at: $data[$prefix . 'created_at'] ?? null,
      updated_at: $data[$prefix . 'updated_at'] ?? null,
      deleted_at: $data[$prefix . 'deleted_at'] ?? null,

      major: isset($data['maj_full_name']) ? Major::fromArray($data, 'maj_') : null,
      specialization: isset($data['spe_full_name']) ? Specialization::fromArray($data, 'spe_') : null
    );
    $c->student_count = (int) ($data['student_count'] ?? 0);

    return $c;
  }
}