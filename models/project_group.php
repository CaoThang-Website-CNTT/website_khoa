<?php

namespace App\Models;

class ProjectGroup extends Model
{
  public function __construct(
    public ?int $id = null,
    public ?int $batch_id = null,
    public ?int $leader_student_id = null,
    public ?int $assigned_topic_id = null,
    public ?string $assigned_at = null,
    public ?string $registration_requirements = null,
    public ?string $supervisor_opinion = null,
    public ?string $execution_start = null,
    public ?string $execution_end = null,
    public ?string $created_at = null,
    public ?string $updated_at = null,

    public ?ProjectBatch $batch = null,
    public ?Student $leader = null,
    public ?ProjectTopic $assigned_topic = null
  ) {}
}
