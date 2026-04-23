<?php

namespace App\Models;

class InternshipAssignment
{
  public function __construct(
    public ?int $id = null,
    public ?int $batch_student_id = null,
    public ?int $teacher_id = null,
    public string $status = 'draft',
    public string $assignment_method = 'manual',
    public ?string $assigned_at = null,
    public ?int $assigned_by = null,
    public ?string $note = null,
    public ?string $created_at = null,
    public ?string $updated_at = null
  ) {}
}
