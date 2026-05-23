<?php

namespace App\Models;

class Media extends Model
{
  public function __construct(
    public ?int $id = null,
    public string $title = '',
    public string $file_name = '',
    public string $file_path = '',
    public string $mime_type = '',
    public ?string $alt_text = null,
    public ?int $width = null,
    public ?int $height = null,
    public int $file_size = 0,
    public ?array $metadata = null,
    public ?int $uploader_id = null,
    public ?string $created_at = null,
    public ?string $updated_at = null,
  ) {
  }
}