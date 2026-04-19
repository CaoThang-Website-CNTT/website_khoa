<?php

namespace App\Models;

use App\Models\Account;

class Post extends Model
{
  public function __construct(
    public ?int $id = null,
    public string $title = '',
    public string $slug = '',
    public string $content_json,
    public ?int $author_id = null,
    public string $status = 'draft',
    public int $view_count = 0,
    public ?string $seo_description = null,
    public ?string $seo_image_url = null,

    public ?string $created_at = null,
    public ?string $published_at = null,
    public ?string $updated_at = null,
    public ?string $deleted_at = null,

    public ?Account $author = null,
    public ?array $medias = [],
    public ?array $categories = [],
  ) {}
}