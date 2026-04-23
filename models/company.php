<?php

namespace App\Models;

class Company
{
  public function __construct(
    public ?int $id = null,
    public ?string $tax_code = null,
    public string $name = '',
    public ?string $normalized_name = null,
    public ?string $phone = null,
    public ?string $email = null,
    public ?string $website = null,
    public ?string $address = null,
    public ?string $note = null,
    public ?string $created_at = null,
    public ?string $updated_at = null,
    public ?string $deleted_at = null
  ) {}
}
