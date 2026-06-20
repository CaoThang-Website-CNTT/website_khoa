<?php

namespace App\Models;

use App\Models\Account;

final class Post extends Model
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
    public bool $is_featured = false,

    public ?string $created_at = null,
    public ?string $published_at = null,
    public ?string $updated_at = null,
    public ?string $deleted_at = null,

    public ?Account $author = null,
    /** @var Media[] */
    public ?array $media = [],
    /** @var Category[] */
    public ?array $categories = [],
  ) {}

  /**
   * toArray() được override để loại bỏ các trường ảo
   * (author, media, categories) không phải là cột thực tế trong bảng posts
   */
  public function toArray(): array
  {
    return [
      'id' => $this->id ?? null,
      'title' => $this->title ?? '',
      'slug' => $this->slug ?? '',
      'content_json' => $this->content_json ?? '[]',
      'settings_json' => $this->settings_json ?? null,
      'author_id' => $this->author_id ?? null,
      'status' => $this->status ?? 'draft',
      'view_count' => $this->view_count ?? 0,
      'seo_description' => $this->seo_description ?? null,
      'seo_image_url' => $this->seo_image_url ?? null,
      'image_url' => $this->resolvedSeoImageUrl(),
      'is_featured' => ($this->is_featured ?? false) ? 1 : 0,
      'published_at' => $this->published_at ?? null,
      'created_at' => $this->created_at ?? null,
      'updated_at' => $this->updated_at ?? null,
      'deleted_at' => $this->deleted_at ?? null,
    ];
  }

  private function resolvedSeoImageUrl(): string
  {
    $fallbackUrl = url('public/img/default-post-thumb.jpg');
    $imagePath = trim((string) ($this->seo_image_url ?? ''));

    if ($imagePath === '') {
      return $fallbackUrl;
    }

    if (preg_match('/^https?:\/\//i', $imagePath)) {
      return $imagePath;
    }

    $relativePath = ltrim($imagePath, '/\\');
    $mediaFilePath = BASE_PATH . '/public/media/' . $relativePath;

    if (!is_file($mediaFilePath)) {
      return $fallbackUrl;
    }

    return url('public/media/' . $relativePath);
  }
}
