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

  public static function fromArray(array $data): self
  {
    return new self(
      id: $data['id'],
      batch_student_id: $data['batch_student_id'],
      company_id: $data['company_id'],
      status: $data['status'],
      cancel_reason: $data['cancel_reason'],
      printed_at: $data['printed_at'],
      processed_by: $data['processed_by'],
      created_at: $data['created_at'],
      updated_at: $data['updated_at'],
    );
  }
}
