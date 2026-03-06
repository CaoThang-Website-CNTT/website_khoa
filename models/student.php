<?php

namespace App\Models;

class Student
{
  public function __construct(
    public int $account_id,
    public string $student_id, // Mã sinh viên
    public string $fullname,
    public string $gender,
    public string $dob,
    public string $phone,
    public int $class_id,

    // Referenced Account data
    public ?Account $account = null
  ) {
  }
}