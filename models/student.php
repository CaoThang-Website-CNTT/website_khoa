<?php

namespace App\Models;

use App\Models\Account;
use App\Models\Classroom;

class Student extends Model
{
  public function __construct(
    public ?int $id = null,

    public ?int $account_id = null,
    public string $student_id = '',
    public ?int $classroom_id = null,

    public string $full_name = '',
    public string $gender = '',
    public ?string $dob = null,
    public ?string $national_id = null,
    public ?string $birth_place = null,

    public ?string $phone = null,
    public ?string $address = null,
    public ?string $major = null,
    public string $status = 'Đang học',
    public ?string $notes = null,

    public ?string $created_at = null,
    public ?string $updated_at = null,
    public ?string $deleted_at = null,

    public ?Account $account = null,
    public ?Classroom $classroom = null
  ) {
  }
}