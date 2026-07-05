<?php

namespace App\Cms;

final class CmsRenderContext
{
  public function __construct(
    private string $pageSlug = '',
    private string $sectionId = '',
    private array $data = [],
    private bool $editorMode = false,
    private bool $locked = false,
  ) {
  }

  public function pageSlug(): string
  {
    return $this->pageSlug;
  }

  public function sectionId(): string
  {
    return $this->sectionId;
  }

  public function withSection(string $sectionId, bool $locked = false): self
  {
    return new self($this->pageSlug, $sectionId, $this->data, $this->editorMode, $locked);
  }

  public function editorMode(): bool
  {
    return $this->editorMode;
  }

  public function locked(): bool
  {
    return $this->locked;
  }

  public function sectionAttributes(): string
  {
    if (!$this->editorMode) return '';
    $attributes = ' data-section-id="' . $this->escape($this->sectionId) . '"';
    if ($this->locked) {
      $attributes .= ' data-cms-locked="true" data-cms-lock-label="Phần nội dung động, không thể chỉnh sửa"';
    }
    return $attributes;
  }

  public function textAttributes(string $path, bool $multiline = false): string
  {
    return $this->editableAttributes($path, ' data-inline-edit="true" contenteditable="plaintext-only" data-multiline="' . ($multiline ? 'true' : 'false') . '"');
  }

  public function linkAttributes(string $urlPath): string
  {
    return $this->editableAttributes($urlPath, ' data-cms-link-edit="true"');
  }

  public function imageAttributes(string $imagePath): string
  {
    return $this->editableAttributes($imagePath, ' data-cms-image-edit="true"');
  }

  public function iconAttributes(string $path): string
  {
    return $this->editableAttributes($path, ' data-cms-icon-edit="true"');
  }

  public function repeaterItemAttributes(string $path, int $index): string
  {
    if (!$this->editorMode || $this->locked) return '';
    return ' data-section-id="' . $this->escape($this->sectionId) . '" data-cms-repeater-path="' . $this->escape($path) . '" data-cms-repeater-index="' . $index . '"';
  }

  private function editableAttributes(string $path, string $attributes): string
  {
    if (!$this->editorMode || $this->locked) return '';
    return ' data-section-id="' . $this->escape($this->sectionId) . '" data-cms-path="' . $this->escape($path) . '"' . $attributes;
  }

  private function escape(string $value): string
  {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }

  public function value(string $key, mixed $default = null): mixed
  {
    return $this->data[$key] ?? $default;
  }

  public function all(): array
  {
    return $this->data;
  }

  public function url(string $path = '', bool $strict = false): string
  {
    return url($path, $strict);
  }
}
