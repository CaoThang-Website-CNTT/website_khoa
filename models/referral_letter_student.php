<?php

namespace App\Models;

class ReferralLetterStudent extends Model
{
  public function __construct(
    public ?int $id = null,
    public ?int $referral_letter_id = null,
    public string $full_name = '',
    public ?string $training_program = null,
    public ?string $dob = null,
    public ?string $address = null,
    public ?int $student_id = null,
    public ?int $batch_student_id = null,
    public int $sort_order = 0,
    public ?string $created_at = null,
    public ?string $updated_at = null
  ) {}
}
