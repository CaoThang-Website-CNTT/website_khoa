<?php

namespace App\Models;

class InternshipWeeklyReportImage
{
  public function __construct(
    public ?int $id = null,
    public ?int $weekly_report_id = null,
    public ?string $original_file_name = null,
    public ?string $mime_type = null,
    public ?string $file_path = null,
    public ?int $file_size = null,
    public ?string $created_at = null
  ) {}
}
