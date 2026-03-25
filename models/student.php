<?php

namespace App\Models;

use App\Models\Account;
use App\Models\Classroom;

class Student
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

  /**
   * Tự động mapping trường dữ liệu DB sang Object
   * @param array $data Row fetched from PDO
   * @return Student
   */
  public static function fromArray(array $data): self
  {
    return new self(
      id: isset($data['id']) ? (int) $data['id'] : null,
      account_id: isset($data['account_id']) ? (int) $data['account_id'] : null,
      classroom_id: isset($data['classroom_id']) ? (int) $data['classroom_id'] : null,

      student_id: $data['student_id'] ?? '',
      full_name: $data['full_name'] ?? '',
      gender: $data['gender'] ?? '',
      dob: $data['dob'] ?? null,
      national_id: $data['national_id'] ?? null,
      birth_place: $data['birth_place'] ?? null,

      phone: $data['phone'] ?? null,
      address: $data['address'] ?? null,
      major: $data['major'] ?? null,
      status: $data['status'] ?? 'Đang học',
      notes: $data['notes'] ?? null,

      created_at: $data['created_at'] ?? null,
      updated_at: $data['updated_at'] ?? null,
      deleted_at: $data['deleted_at'] ?? null,
    );
  }
}