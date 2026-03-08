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
    public ?string $major = null,
    public ?string $birth_place,

    // Referenced Account data
    public ?Account $account = null,
    public ?Classroom $classroom = null
  ) {}
}
