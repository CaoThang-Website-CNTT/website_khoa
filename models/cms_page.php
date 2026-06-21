<?php

namespace App\Models;

final class CmsPage extends Model
{
  public function __construct(
    public ?int $id = null,
    public string $title = '',
    public string $slug = '',
    public ?string $route_path = null,
    public string $type = 'landing_page',
    public string $status = 'draft',
    public string $layout_mode = 'section_schema',
    public string $content_json = '{"version":1,"sections":[]}',
    public ?string $settings_json = null,
    public ?string $published_at = null,
    public ?string $created_at = null,
    public ?string $updated_at = null,
    public ?string $deleted_at = null,
  ) {
  }

  public function toArray(): array
  {
    return [
      'id' => $this->id,
      'title' => $this->title,
      'slug' => $this->slug,
      'route_path' => $this->route_path,
      'type' => $this->type,
      'status' => $this->status,
      'layout_mode' => $this->layout_mode,
      'content_json' => $this->content_json,
      'settings_json' => $this->settings_json,
      'published_at' => $this->published_at,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
      'deleted_at' => $this->deleted_at,
    ];
  }

  public function content(): array
  {
    $payload = json_decode($this->content_json, true);
    return is_array($payload) ? $payload : ['version' => 1, 'sections' => []];
  }

  public function settings(): array
  {
    $payload = json_decode($this->settings_json ?? '{}', true);
    return is_array($payload) ? $payload : [];
  }
}
