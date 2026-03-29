<?php

namespace App\Models;

use App\Core\Model;

class Student
{
  public function __construct(
    public int $account_id,
    public string $student_id, // Mã sinh viên
    public string $full_name,
    public string $gender,
    public string $dob,
    public string $phone,
    public ?int $classroom_id = null,
    public ?string $major = null,
    public ?string $birth_place = '',

    // Referenced Account data
    public ?Account $account = null,
    public ?Classroom $classroom = null
  ) {}
  /**
   * Tự động mapping trường dữ liệu DB
   * @param array $data
   * @return Student
   */
  public static function fromArray(array $data): self
  {
    return new self(
      account_id: $data['account_id'] ?? 0,
      student_id: $data['student_id'] ?? '',
      full_name: $data['full_name'] ?? '',
      gender: $data['gender'] ?? '',
      dob: $data['dob'] ?? '',
      phone: $data['phone'] ?? '',
      classroom_id: $data['classroom_id'] ?? null,
      major: $data['major'] ?? null,
      birth_place: $data['birth_place'] ?? null,
      account: isset($data['acc_email']) ? Account::fromArray($data, 'acc_') : null,
      classroom: isset($data['cla_id']) ? Classroom::fromArray($data, 'cla_') : null
    );
  }
}
