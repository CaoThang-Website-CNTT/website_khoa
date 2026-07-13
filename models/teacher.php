<?php

namespace App\Models;

use App\Models\Account;
use App\Models\Department;

class Teacher extends Model
{
  public function __construct(
    public ?int $id = null,

    public ?int $account_id = null,

    public string $full_name = '',
    public string $gender = '',
    public ?string $dob = null,
    public ?string $national_id = null,

    public ?string $phone = null,
    public ?string $address = null,

    public ?string $degree = null,
    public ?string $position = null,
    public ?string $title = null,
    public ?int $department_id = null,
    public ?string $start_date = null,
    public ?string $end_date = null,

    public ?string $notes = null,
    public ?string $created_at = null,
    public ?string $updated_at = null,
    public ?string $deleted_at = null,

    public ?Department $department = null,
    public ?Account $account = null
  ) {}

  public static function fromArray(array $data): static
  {
    $instance = parent::fromArray($data);
    if (!isset($instance->department)) $instance->department = null;
    if (!isset($instance->account)) $instance->account = null;

    return $instance;
  }
}
