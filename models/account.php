<?php

namespace App\Models;

class Account
{
  public function __construct(
    public int $id,
    public string $email,
    public string $role, // 'teacher' | 'student' | 'admin'
    public ?string $created_at = null,
    public ?string $updated_at = null,
    public ?string $deleted_at = null,
  ) {
  }
  /**
   * Tự động mapping trường dữ liệu DB
   * @param array $data
   * @param string $prefix
   * @return Account
   */
  public static function fromArray(array $data, string $prefix = ''): self
  {
    return new self(
      id: $data[$prefix . 'id'] ?? ($data[$prefix . 'acc_id'] ?? null),
      email: $data[$prefix . 'email'] ?? '',
      role: $data[$prefix . 'role'] ?? '',
      created_at: $data[$prefix . 'created_at'] ?? null,
      updated_at: $data[$prefix . 'updated_at'] ?? null,
      deleted_at: $data[$prefix . 'deleted_at'] ?? null
    );
  }
}
