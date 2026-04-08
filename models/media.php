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
    public string $alt_text = '',
    public ?int $post_id = null,
    public ?string $created_at = null,
    public ?string $updated_at = null,
    public ?Post $post = null,
  ) {}
  public static function fromArray(array $row): static
  {
    return new static(
      id: isset($row['id']) ? (int) $row['id'] : null,
      file_name: $row['file_name'],
      file_path: $row['file_path'],
      mime_type: $row['mime_type'],
      file_size: $row['file_size'],
      alt_text: $row['alt_text'],
      post_id: isset($row['post_id']) ? (int) $row['post_id'] : null,
      created_at: $row['created_at'] ?? null,
      updated_at: $row['updated_at'] ?? null,
    );
  }
}