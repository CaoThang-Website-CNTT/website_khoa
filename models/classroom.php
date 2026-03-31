<?php

namespace App\Models;

class Classroom
{
  public function __construct(
    public ?int $id = null,
    public ?int $major_id = null,
    public ?int $class_of = null,
    public ?int $specialization_id = null,
    public ?string $letter = null,
    public string $short_name = '',
    public ?int $homeroom_teacher_id = null,

    public ?string $created_at = null,
    public ?string $updated_at = null,
    public ?string $deleted_at = null,

    public ?Teacher $homeroomTeacher = null,
    public ?Major $major = null,
    public ?Specialization $specialization = null,
  ) {
  }
  /**
   * Tự động mapping trường dữ liệu DB
   * @param array $data
   * @return Classroom
   */
  public static function fromArray(array $data): self
  {
    return new self(
      id: $data['id'] ?? 0,
      major_id: $data['major_id'] ?? 0,
      class_of: $data['class_of'] ?? 0,
      specialization_id: $data['specialization_id'] ?? null,
      letter: $data['letter'] ?? null,
      short_name: $data['short_name'] ?? '',
      homeroom_teacher_id: $data['homeroom_teacher_id'] ?? null,
      created_at: $data['created_at'] ?? null,
      updated_at: $data['updated_at'] ?? null,
      deleted_at: $data['deleted_at'] ?? null,
    );
  }
}