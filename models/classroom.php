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
  ) {
  }
}