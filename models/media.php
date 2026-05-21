<?php

namespace App\Models;

use App\Models\Post;

class Media extends Model
{
  public function __construct(
    public ?int $id = null,
    public string $file_name = '',
    public string $file_path = '',
    public string $mime_type = '',
    public int $file_size = 0,
    public ?string $alt_text = null,
    public ?int $post_id = null,
    public ?int $uploader_id = null,
    public ?string $created_at = null,
    public ?string $updated_at = null,
    public ?Post $post = null,
  ) {
  }

}