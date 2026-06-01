<?php

namespace App\Models;

class Account extends Model
{
  public function __construct(
    public ?int $id = null,
    public string $email = "",
    public string $password_hash = "", // Cần lưu ý không được expose này ra ngoài. Nội bộ server cần để xác thực.
    public string $role = "",
    public ?string $created_at = null,
    public ?string $updated_at = null,
    public ?string $deleted_at = null,
  ) {
  }
}
