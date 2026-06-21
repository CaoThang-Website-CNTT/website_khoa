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
    public ?string $builder_draft_json = null,
    public ?string $builder_published_json = null,
    public ?string $builder_snapshots_json = null,
    public ?string $builder_enabled_at = null,
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
      'builder_draft_json' => $this->builder_draft_json,
      'builder_published_json' => $this->builder_published_json,
      'builder_snapshots_json' => $this->builder_snapshots_json,
      'builder_enabled_at' => $this->builder_enabled_at,
      'published_at' => $this->published_at,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
      'deleted_at' => $this->deleted_at,
    ];
  }

  public function content(): array
  {
    return $this->decodeJsonObject($this->content_json, ['version' => 1, 'sections' => []]);
  }

  public function settings(): array
  {
    return $this->decodeJsonObject($this->settings_json ?? '{}', []);
  }

  public function builderDraft(): array
  {
    return $this->decodeJsonObject($this->builder_draft_json ?? '{}', []);
  }

  public function builderPublished(): array
  {
    return $this->decodeJsonObject($this->builder_published_json ?? '{}', []);
  }

  public function builderSnapshots(): array
  {
    $payload = json_decode($this->builder_snapshots_json ?? '[]', true);

    if (is_array($payload)) {
      return $payload;
    }

    $unescapedPayload = json_decode(stripcslashes($this->builder_snapshots_json ?? '[]'), true);

    return is_array($unescapedPayload) ? $unescapedPayload : [];
  }

  private function decodeJsonObject(string $json, array $fallback): array
  {
    $payload = json_decode($json, true);

    if (is_array($payload)) {
      return $payload;
    }

    $unescapedPayload = json_decode(stripcslashes($json), true);

    return is_array($unescapedPayload) ? $unescapedPayload : $fallback;
  }
}
