<?php

namespace App\Models;

class ReferralLetter
{
  public function __construct(
    public ?int $id = null,
    public ?int $batch_student_id = null,
    public ?int $company_id = null,
    public ?int $teacher_id = null,
    public string $status = 'pending',
    public ?string $cancel_reason = null,
    public ?string $reviewed_at = null,
    public ?string $internship_start_date = null,
    public ?string $internship_end_date = null,
    public ?string $document_number = null,
    public ?string $note = null,
    public ?string $printed_at = null,
    public ?int $processed_by = null,
    public ?int $cancelled_by = null,
    public ?string $recipient_name = null,
    public ?string $recipient_phone = null,
    public ?string $recipient_email = null,
    public ?string $received_at = null,
    public ?int $received_by = null,
    public ?string $created_at = null,
    public ?string $updated_at = null
  ) {}

  public static function fromArray(array $data): self
  {
    return new self(
      id: $data['id'] ?? null,
      batch_student_id: $data['batch_student_id'] ?? null,
      company_id: $data['company_id'] ?? null,
      teacher_id: $data['teacher_id'] ?? null,
      status: $data['status'] ?? 'pending',
      cancel_reason: $data['cancel_reason'] ?? null,
      reviewed_at: $data['reviewed_at'] ?? null,
      internship_start_date: $data['internship_start_date'] ?? null,
      internship_end_date: $data['internship_end_date'] ?? null,
      document_number: $data['document_number'] ?? null,
      note: $data['note'] ?? null,
      printed_at: $data['printed_at'] ?? null,
      processed_by: $data['processed_by'] ?? null,
      cancelled_by: $data['cancelled_by'] ?? null,
      recipient_name: $data['recipient_name'] ?? null,
      recipient_phone: $data['recipient_phone'] ?? null,
      recipient_email: $data['recipient_email'] ?? null,
      received_at: $data['received_at'] ?? null,
      received_by: $data['received_by'] ?? null,
      created_at: $data['created_at'] ?? null,
      updated_at: $data['updated_at'] ?? null,
    );
  }
}
