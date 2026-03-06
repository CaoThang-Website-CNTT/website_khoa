<?php

namespace App\Models;

class Account
{
  public function __construct(
    public ?int $id,
    public string $email,
    public ?string $password = null, // Hiện tại y2 alogrithm hash
    public string $role, // 'teacher' | 'student' | 'admin'
    public ?string $created_at = null,
    public ?string $updated_at = null,
    public ?string $deleted_at = null,
  ) {
  }
}