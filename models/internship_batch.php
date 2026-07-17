<?php

namespace App\Models;

use App\Enums\BatchStatus;
use App\Core\AppTime;

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
    public ?string $grades_published_at = null,
    public ?string $created_at = null,
    public ?string $updated_at = null,
    public ?string $deleted_at = null
  ) {}

  public function getEffectiveStatus(): string
  {
    if ($this->status === BatchStatus::DRAFT) return BatchStatus::DRAFT;
    if ($this->status === BatchStatus::CLOSED) return BatchStatus::CLOSED;

    $now = AppTime::time();
    $start = $this->start_at ? strtotime($this->start_at) : 0;
    $end = $this->end_at ? strtotime($this->end_at) : 0;

    if ($start > 0 && $now < $start) return BatchStatus::UPCOMING;
    if ($start > 0 && $end > 0 && $now >= $start && $now <= $end) return BatchStatus::ACTIVE;
    if ($end > 0 && $now > $end) return BatchStatus::ENDED;

    return 'unknown';
  }
}
