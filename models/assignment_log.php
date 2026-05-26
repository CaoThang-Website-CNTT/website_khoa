<?php

namespace App\Models;

class AssignmentLog
{
  public function __construct(
    public ?int $id = null,
    public ?int $assignment_id = null,
    public string $action = '',
    public ?int $old_teacher_id = null,
    public ?int $new_teacher_id = null,
    public ?int $performed_by = null,
    public ?string $reason = null,
    public ?string $created_at = null
  ) {}
}
