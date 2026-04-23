<?php

namespace App\Models;

class ReferralLetter
{
  public function __construct(
    public ?int $id = null,
    public ?int $batch_student_id = null,
    public ?int $company_id = null,
    public string $status = 'pending',
    public ?string $cancel_reason = null,
    public ?string $printed_at = null,
    public ?int $processed_by = null,
    public ?string $created_at = null,
    public ?string $updated_at = null
  ) {}
}
