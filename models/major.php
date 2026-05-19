<?php

namespace App\Models;

class Major extends Model
{
  public function __construct(
    public ?int $id = null,
    public ?string $full_name = null,
    public ?string $short_name = null,
    public ?string $level = null,
    public ?string $created_at = null,
    public ?string $updated_at = null,
    public ?string $deleted_at = null,
  ) {
  }
}