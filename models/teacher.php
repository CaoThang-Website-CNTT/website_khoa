<?php

namespace App\Models;

class Teacher
{
  public function __construct(
    public int $account_id,
    public string $full_name,
    public string $gender,
    public string $dob,
    public string $phone,
    public string $title,
    public string $department,
    public string $start_date,

    // Referenced Account data
    public ?Account $account = null
  ) {}
  /**
   * Tự động mapping trường dữ liệu DB
   * @param array $data
   * @return Teacher
   */
  public static function fromArray(array $data): self
  {
    return new self(
      account_id: $data['account_id'] ?? 0,
      full_name: $data['full_name'] ?? '',
      gender: $data['gender'] ?? '',
      dob: $data['dob'] ?? '',
      phone: $data['phone'] ?? '',
      title: $data['title'] ?? '',
      department: $data['department'] ?? '',
      start_date: $data['start_date'] ?? '',
      account: isset($data['acc_email']) ? Account::fromArray($data, 'acc_') : null
    );
  }
}
