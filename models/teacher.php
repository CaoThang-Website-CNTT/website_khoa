<?php

namespace App\Models;

use App\Models\Account;

class Teacher
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

  public static function fromArray(array $data): self
  {
    return new self(
      id: isset($data['id']) ? (int) $data['id'] : null,

      account_id: isset($data['account_id']) ? (int) $data['account_id'] : null,
      staff_code: $data['staff_code'] ?? '',

      full_name: $data['full_name'] ?? '',
      gender: $data['gender'] ?? '',
      dob: $data['dob'] ?? null,
      national_id: $data['national_id'] ?? null,

      phone: $data['phone'] ?? null,
      address: $data['address'] ?? null,

      degree: $data['degree'] ?? null,
      position: $data['position'] ?? null,
      title: $data['title'] ?? null,
      department: $data['department'] ?? null,
      contract_type: $data['contract_type'] ?? 'full_time',
      start_date: $data['start_date'] ?? null,
      end_date: $data['end_date'] ?? null,

      notes: $data['notes'] ?? null,
      created_at: $data['created_at'] ?? null,
      updated_at: $data['updated_at'] ?? null,
      deleted_at: $data['deleted_at'] ?? null,
    );
  }
}