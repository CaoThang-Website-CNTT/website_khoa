<?php

namespace App\Models;

class ProjectGroupMember extends Model
{
  public function __construct(
    public ?int $id = null,
    public ?int $group_id = null,
    public ?int $student_id = null,
    public bool $is_leader = false,
    public bool $is_confirmed = false,
    public bool $is_eligible = true,
    public ?string $confirmed_at = null,
    public ?string $created_at = null,
    public ?string $updated_at = null,

    public ?ProjectGroup $group = null,
    public ?Student $student = null
  ) {}
}
