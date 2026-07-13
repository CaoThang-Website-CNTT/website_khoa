<?php

namespace App\Models;

class Classroom extends Model
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
    public int $student_count = 0
  ) {}

  public static function fromArray(array $data): static
  {
    $instance = parent::fromArray($data);
    if (!isset($instance->homeroomTeacher)) $instance->homeroomTeacher = null;
    if (!isset($instance->major)) $instance->major = null;
    if (!isset($instance->specialization)) $instance->specialization = null;
    if (!isset($instance->student_count)) $instance->student_count = 0;

    return $instance;
  }
}