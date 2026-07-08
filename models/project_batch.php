<?php

namespace App\Models;

use App\Enums\ProjectBatchStatus;

class ProjectBatch extends Model
{
  public function __construct(
    public ?int $id = null,
    public string $title = '',
    public ?string $description = null,
    public ?string $topic_proposal_start = null,
    public ?string $topic_proposal_end = null,
    public ?string $registration_start = null,
    public ?string $registration_end = null,
    public int $max_aspirations = 3,
    public int $min_class_of = 0,
    public int $max_class_of = 0,
    public string $status = 'draft',
    public ?int $created_by = null,
    public ?string $published_at = null,
    public ?string $closed_at = null,
    public ?string $allocation_published_at = null,
    public ?string $created_at = null,
    public ?string $updated_at = null,
    public ?string $deleted_at = null
  ) {}

  public function isAllocationPublished(): bool
  {
    return $this->allocation_published_at !== null;
  }

  public function getEffectivePhase(): string
  {
    if ($this->status === ProjectBatchStatus::DRAFT) return ProjectBatchStatus::DRAFT;
    if ($this->status === ProjectBatchStatus::CLOSED) return ProjectBatchStatus::CLOSED;

    // Nếu đã chốt phân bổ, giai đoạn sẽ là đã phân công (bất kể thời gian đăng ký đã kết thúc hay chưa)
    if ($this->allocation_published_at !== null) {
      return ProjectBatchStatus::ALLOCATED;
    }

    $now = time();
    $proposalStart = $this->topic_proposal_start ? strtotime($this->topic_proposal_start) : 0;
    $proposalEnd = $this->topic_proposal_end ? strtotime($this->topic_proposal_end) : 0;
    $regStart = $this->registration_start ? strtotime($this->registration_start) : 0;
    $regEnd = $this->registration_end ? strtotime($this->registration_end) : 0;

    if ($proposalStart > 0 && $proposalEnd > 0 && $now >= $proposalStart && $now < $proposalEnd) {
      return ProjectBatchStatus::TOPIC_PROPOSAL;
    }

    if ($regStart > 0 && $regEnd > 0 && $now >= $regStart && $now < $regEnd) {
      return ProjectBatchStatus::REGISTRATION;
    }

    if ($regEnd > 0 && $now >= $regEnd) {
      return ProjectBatchStatus::REVIEWING;
    }

    return ProjectBatchStatus::UPCOMING;
  }
}
