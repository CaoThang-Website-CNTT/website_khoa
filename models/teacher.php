<?php

namespace App\Models;

class Teacher
{
  public function __construct(
    public int $account_id,
    public string $fullname,
    public string $gender,
    public string $dob,
    public string $phone,
    public string $title,
    public string $department,
    public string $start_date,

    // Referenced Account data
    public ?Account $account = null
  ) {
  }
}