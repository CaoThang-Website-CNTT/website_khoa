<?php

namespace App\Models;

use App\Models\Account;

class Post extends Model
{
  public function __construct(
    public ?int $id = null,
    public string $title = '',
    public string $slug = '',
    public string $content_json = '[]',
    public ?string $settings_json = null,
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
  ) {
  }

  public static function fromArray(array $row): static
  {
    return new static(
      id: isset($row['id']) ? (int) $row['id'] : null,
      title: $row['title'] ?? '',
      slug: $row['slug'] ?? '',
      content_json: $row['content_json'] ?? '[]',
      settings_json: $row['settings_json'] ?? null,
      author_id: isset($row['author_id']) ? (int) $row['author_id'] : null,
      status: $row['status'] ?? 'draft',
      view_count: isset($row['view_count']) ? (int) $row['view_count'] : 0,
      seo_description: $row['seo_description'] ?? null,
      seo_image_url: $row['seo_image_url'] ?? null,
      created_at: $row['created_at'] ?? null,
      published_at: $row['published_at'] ?? null,
      updated_at: $row['updated_at'] ?? null,
      deleted_at: $row['deleted_at'] ?? null,
    );
  }

  public function toArray(): array
  {
    return [
      'id' => $this->id,
      'title' => $this->title,
      'slug' => $this->slug,
      'content_json' => $this->content_json,
      'settings_json' => $this->settings_json,
      'author_id' => $this->author_id,
      'status' => $this->status,
      'view_count' => $this->view_count,
      'seo_description' => $this->seo_description,
      'seo_image_url' => $this->seo_image_url,
      'published_at' => $this->published_at,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ];
  }
}