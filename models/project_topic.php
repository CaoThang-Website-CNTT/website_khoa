<?php

namespace App\Models;

class ProjectTopic extends Model
{
  public function __construct(
    public ?int $id = null,
    public ?int $batch_id = null,
    public ?int $teacher_id = null,
    public string $title = '',
    public ?string $description = null,
    public ?string $pdf_file_path = null,
    public int $max_students = 2,
    public string $status = 'draft',
    public ?string $reject_reason = null,
    public ?string $submitted_at = null,
    public ?string $reviewed_at = null,
    public ?int $reviewed_by = null,
    public ?string $created_at = null,
    public ?string $updated_at = null,
    public ?string $deleted_at = null,

    public ?ProjectBatch $batch = null,
    public ?Teacher $teacher = null
  ) {}
}
