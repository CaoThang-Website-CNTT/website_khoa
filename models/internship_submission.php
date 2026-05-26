<?php

namespace App\Models;

class InternshipSubmission
{
  public function __construct(
    public ?int $id = null,
    public ?int $batch_student_id = null,
    public string $type = '',
    public string $storage_mode = 'file',
    public ?string $file_path = null,
    public ?string $external_url = null,
    public ?string $submitted_at = null,
    public bool $is_latest = true,
    public ?string $created_at = null,
    public ?string $updated_at = null
  ) {}
}
