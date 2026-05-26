<?php

namespace App\Models;

class InternshipBatch
{
  public function __construct(
    public ?int $id = null,
    public string $title = '',
    public ?string $description = null,
    public ?int $class_of = null,
    public string $level = '',
    public ?string $start_at = null,
    public ?string $end_at = null,
    public string $status = 'draft',
    public ?int $created_by = null,
    public ?string $published_at = null,
    public ?string $closed_at = null,
    public ?string $created_at = null,
    public ?string $updated_at = null,
    public ?string $deleted_at = null
  ) {}
}
