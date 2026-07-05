<?php

namespace App\Cms;

final class CmsSectionRegistry
{
  /** @var array<string, CmsSectionDefinitionInterface> */
  private array $_sections = [];

  public function register(CmsSectionDefinitionInterface $section): void
  {
    $type = trim($section->type());
    if ($type === '') {
      throw new \InvalidArgumentException('CMS section type cannot be empty.');
    }

    $this->_sections[$type] = $section;
  }

  public function get(string $type): ?CmsSectionDefinitionInterface
  {
    return $this->_sections[$type] ?? null;
  }

  public function has(string $type): bool
  {
    return isset($this->_sections[$type]);
  }

  /** @return array<string, CmsSectionDefinitionInterface> */
  public function all(): array
  {
    return $this->_sections;
  }

  public function normalizeSection(array $section): array
  {
    $type = (string) ($section['type'] ?? '');
    if ($type === '') {
      $type = $this->legacyTypeForId((string) ($section['id'] ?? ''));
    }

    $definition = $this->get($type);
    $defaults = $definition?->defaults() ?? [];
    $data = is_array($section['data'] ?? null) ? $section['data'] : [];

    if ($definition !== null) {
      $data = array_replace_recursive($defaults, $data);
      $data['variant'] = $this->normalizeVariant($definition, $data['variant'] ?? null);
    }

    return [
      'id' => (string) ($section['id'] ?? $type),
      'type' => $type,
      'locked' => (bool) ($section['locked'] ?? false),
      'data' => $data,
    ];
  }

  public function renderSection(array $section, CmsRenderContext $context): string
  {
    $normalized = $this->normalizeSection($section);
    $definition = $this->get($normalized['type']);

    if ($definition === null) {
      return '<!-- Unknown CMS section: ' . htmlspecialchars($normalized['type'], ENT_QUOTES, 'UTF-8') . ' -->';
    }

    $sectionContext = $context->withSection($normalized['id'], $normalized['locked']);
    return $definition->render($normalized['data'], $sectionContext);
  }

  private function normalizeVariant(CmsSectionDefinitionInterface $definition, mixed $variant): string
  {
    $variants = $definition->variants();
    $default = array_key_first($variants) ?: 'default';
    $variant = trim((string) $variant);

    return isset($variants[$variant]) ? $variant : $default;
  }

  private function legacyTypeForId(string $id): string
  {
    return match ($id) {
      'hero' => 'sections/landing_hero',
      'newsfeed' => 'sections/newsfeed',
      'breadcrumbs' => 'sections/breadcrumbs',
      'landing_about' => 'sections/landing_about',
      'why_choose_us' => 'sections/why_choose_us',
      'stats' => 'sections/stats',
      'partnerships' => 'sections/partnerships',
      'about_hero' => 'sections/about_hero',
      'history' => 'sections/history',
      'bento_grid' => 'sections/bento_grid',
      default => '',
    };
  }
}
