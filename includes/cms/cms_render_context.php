<?php

namespace App\Cms;

final class CmsRenderContext
{
  public function __construct(
    private string $pageSlug = '',
    private string $sectionId = '',
    private array $data = [],
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

  public function withSectionId(string $sectionId): self
  {
    return new self($this->pageSlug, $sectionId, $this->data);
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
