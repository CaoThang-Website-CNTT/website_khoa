<?php

namespace App\Models;

use App\Models\Account;

class Teacher extends Model
{
  public function __construct(
    public ?int $id = null,

    public ?int $account_id = null,
    public string $staff_code = '',

    public string $full_name = '',
    public string $gender = '',
    public ?string $dob = null,
    public ?string $national_id = null,

    public ?string $phone = null,
    public ?string $address = null,

    public ?string $degree = null,
    public ?string $position = null,
    public ?string $title = null,
    public ?string $department = null,
    public string $contract_type = 'full_time',
    public ?string $start_date = null,
    public ?string $end_date = null,

    public ?string $notes = null,
    public ?string $created_at = null,
    public ?string $updated_at = null,
    public ?string $deleted_at = null,

    public ?Account $account = null
  ) {
  }
}