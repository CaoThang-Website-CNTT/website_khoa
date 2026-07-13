<?php

namespace App\Models;

class InternshipBatchStudent
{
  public function __construct(
    public ?int $id = null,
    public ?int $batch_id = null,
    public ?int $student_id = null,
    public string $status = 'pending',
    public string $source = 'db_select',
    public ?string $note = null,
    public ?int $company_id = null,
    public ?string $position = null,
    public ?string $company_mentor_name = null,
    public ?string $company_mentor_phone = null,
    public ?string $company_mentor_email = null,
    public ?string $internship_start_date = null,
    public ?string $internship_end_date = null,
    public ?string $created_at = null,
    public ?string $updated_at = null
  ) {}
}
