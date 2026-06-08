<?php

namespace App\Models;

class Ticket extends Model
{
  public const TYPES = ['bug', 'improvement', 'feedback'];
  public const STATUSES = ['pending', 'processing', 'resolved', 'rejected'];

  public function __construct(
    public ?int $id = null,
    public string $title = '',
    public string $description = '',
    public string $type = 'feedback',
    public string $status = 'pending',
    public string $reporter_email = '',
    public ?string $created_at = null,
    public ?string $updated_at = null,
  ) {
  }
}
