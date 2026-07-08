<?php

namespace App\Models;

class ProjectAspiration extends Model
{
  public function __construct(
    public ?int $id = null,
    public ?int $group_id = null,
    public ?int $topic_id = null,
    public int $priority = 1,
    public string $status = 'pending',
    public ?string $created_at = null,
    public ?string $updated_at = null,

    public ?ProjectGroup $group = null,
    public ?ProjectTopic $topic = null
  ) {}
}
