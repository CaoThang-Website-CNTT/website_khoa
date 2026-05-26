<?php

namespace App\Models;

class InternshipGrade
{
  public function __construct(
    public ?int $id = null,
    public ?int $batch_student_id = null,
    public ?float $final_score = null,
    public ?string $score_reason = null,
    public ?string $feedback = null,
    public ?string $graded_at = null,
    public ?int $graded_by = null,
    public ?string $grade_lock_at = null,
    public ?string $created_at = null,
    public ?string $updated_at = null
  ) {}
}
