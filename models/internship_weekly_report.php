<?php

namespace App\Models;

class InternshipWeeklyReport
{
  public function __construct(
    public ?int $id = null,
    public ?int $batch_student_id = null,
    public ?int $week_number = null,
    public ?string $week_start = null,
    public ?string $week_end = null,
    public ?string $content = null,
    public bool $is_exempt = false,
    public bool $is_late = false,
    public bool $is_latest = true,
    public ?string $submitted_at = null,
    public ?string $created_at = null,
    public ?string $updated_at = null
  ) {}
}
