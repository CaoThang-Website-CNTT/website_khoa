<?php

namespace App\Models;

class InternshipBatchSupervisor
{
  public function __construct(
    public ?int $id = null,
    public ?int $batch_id = null,
    public ?int $teacher_id = null,
    public ?int $max_students = null,
    public bool $is_active = true,
    public ?string $created_at = null,
    public ?string $updated_at = null
  ) {}
}
