<?php

namespace App\Models;

class Media
{
  public function __construct(
    public readonly ?int $id = null,
    public ?int $post_id = null,

    public ?string $disk_path = null,
    public ?string $mime_type = null,
    public ?int $size_bytes = null,

    public ?string $original_name = null,
    public ?string $alt_text = null,

    public string $status = 'pending',

    public readonly ?string $created_at = null,
    public ?string $updated_at = null
  ) {
  }

  public function getPublicUrl(): string
  {
    return url($this->disk_path);
  }
}