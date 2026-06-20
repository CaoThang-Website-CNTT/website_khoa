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
    public int $is_verified = 0,
    public string $source = 'manual',
    public ?string $created_at = null,
    public ?string $updated_at = null,
    public ?string $deleted_at = null
  ) {}
  public static function fromArray(array $data): self
  {
    return new self(
      id: $data['id'] ?? null,
      tax_code: $data['tax_code'] ?? null,
      name: $data['name'] ?? '',
      normalized_name: $data['normalized_name'] ?? null,
      phone: $data['phone'] ?? null,
      email: $data['email'] ?? null,
      website: $data['website'] ?? null,
      address: $data['address'] ?? null,
      note: $data['note'] ?? null,
      is_verified: $data['is_verified'] ?? 0,
      source: $data['source'] ?? 'manual',
      created_at: $data['created_at'] ?? null,
      updated_at: $data['updated_at'] ?? null,
      deleted_at: $data['deleted_at'] ?? null
    );
  }
}
