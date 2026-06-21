<?php

namespace App\Services;

use App\Cms\CmsPageSchemaRegistry;
use App\Core\Pageable;
use App\Models\CmsPage;
use App\Stores\CmsPageStore;
use Database;

interface ICmsPageService
{
  public function getPages(int $page = 1, int $limit = 15, array $filters = []): Pageable;
  public function getPage(int $id): CmsPage;
  public function getPageBySlug(string $slug): CmsPage;
  public function getPublishedPageBySlug(string $slug): ?CmsPage;
  public function getPageForEditing(string $slug): array;
  public function saveDraft(string $slug, array $payload): CmsPage;
  public function publish(string $slug, array $payload): CmsPage;
  public function delete(int $id): void;
}

class CmsPageService implements ICmsPageService
{
  public function __construct(
    private CmsPageStore $_store,
    private CmsPageSchemaRegistry $_schemas,
  ) {
  }

  public function getPages(int $page = 1, int $limit = 15, array $filters = []): Pageable
  {
    $filters['page'] = max(1, $page);
    $filters['limit'] = max(1, $limit);

    return new Pageable(
      $this->_store->getPaginated($filters),
      $this->_store->getTotalCount($filters),
      $filters['limit'],
      $filters['page'],
    );
  }

  public function getPage(int $id): CmsPage
  {
    return $this->_store->getById($id)
      ?? throw new \RuntimeException("CMS page #{$id} does not exist.");
  }

  public function getPageBySlug(string $slug): CmsPage
  {
    if (!$this->_schemas->hasPage($slug)) {
      throw new \InvalidArgumentException("CMS page '{$slug}' is not registered.");
    }

    return $this->_store->findBySlug($slug)
      ?? $this->makeDefaultPage($slug);
  }

  public function getPublishedPageBySlug(string $slug): ?CmsPage
  {
    if (!$this->_schemas->hasPage($slug)) {
      return null;
    }

    return $this->_store->findPublishedBySlug($slug);
  }

  public function getPageForEditing(string $slug): array
  {
    $schema = $this->_schemas->page($slug);

    if ($schema === null) {
      throw new \InvalidArgumentException("CMS page '{$slug}' is not registered.");
    }

    $page = $this->getPageBySlug($slug);

    return [
      'page' => $page,
      'schema' => $schema,
      'document' => $this->normalizeDocument($slug, $page->content()),
    ];
  }

  public function saveDraft(string $slug, array $payload): CmsPage
  {
    return $this->upsertFromPayload($slug, $payload, 'draft');
  }

  public function publish(string $slug, array $payload): CmsPage
  {
    return $this->upsertFromPayload($slug, $payload, 'published');
  }

  public function delete(int $id): void
  {
    $this->getPage($id);
    $this->_store->softDelete($id);
  }

  private function upsertFromPayload(string $slug, array $payload, string $status): CmsPage
  {
    $schema = $this->_schemas->page($slug);

    if ($schema === null) {
      throw new \InvalidArgumentException("CMS page '{$slug}' is not registered.");
    }

    $document = $this->normalizeDocument($slug, $payload['content'] ?? $payload);
    $settings = $this->normalizeSettings($payload['settings'] ?? []);
    $existing = $this->_store->findBySlug($slug);
    $now = (new \DateTime())->format('Y-m-d H:i:s');

    $data = [
      'title' => trim((string) ($payload['title'] ?? $schema['title'])),
      'slug' => $schema['slug'],
      'route_path' => $schema['route_path'],
      'type' => $schema['type'],
      'status' => $status,
      'layout_mode' => $schema['layout_mode'],
      'content_json' => $this->encodeJson($document),
      'settings_json' => $this->encodeJson($settings),
    ];

    if ($status === 'published') {
      $data['published_at'] = $existing?->published_at ?? $now;
    }

    return Database::getInstance()->transaction(function () use ($existing, $data): CmsPage {
      if ($existing === null) {
        return $this->_store->create(new CmsPage(
          title: $data['title'],
          slug: $data['slug'],
          route_path: $data['route_path'],
          type: $data['type'],
          status: $data['status'],
          layout_mode: $data['layout_mode'],
          content_json: $data['content_json'],
          settings_json: $data['settings_json'],
          published_at: $data['published_at'] ?? null,
        ));
      }

      return $this->_store->update((int) $existing->id, $data);
    });
  }

  private function makeDefaultPage(string $slug): CmsPage
  {
    $schema = $this->_schemas->page($slug);

    if ($schema === null) {
      throw new \InvalidArgumentException("CMS page '{$slug}' is not registered.");
    }

    return new CmsPage(
      title: $schema['title'],
      slug: $schema['slug'],
      route_path: $schema['route_path'],
      type: $schema['type'],
      layout_mode: $schema['layout_mode'],
      content_json: $this->encodeJson($this->_schemas->defaultDocument($slug)),
      settings_json: '{}',
    );
  }

  private function normalizeDocument(string $slug, array $document): array
  {
    $sectionSchemas = $this->_schemas->sectionMap($slug);
    $submittedSections = $this->submittedSectionMap($document['sections'] ?? []);
    $sections = [];

    foreach ($sectionSchemas as $sectionId => $sectionSchema) {
      $submitted = $submittedSections[$sectionId] ?? [];
      $defaultData = $sectionSchema['data'] ?? [];
      $submittedData = is_array($submitted['data'] ?? null) ? $submitted['data'] : [];

      $sections[] = [
        'id' => $sectionId,
        'type' => $sectionSchema['type'],
        'locked' => (bool) ($sectionSchema['locked'] ?? false),
        'data' => ($sectionSchema['locked'] ?? false)
          ? $defaultData
          : $this->filterDataByAllowedPaths($submittedData, $sectionSchema['editable_fields'] ?? [], $defaultData),
      ];
    }

    return [
      'version' => 1,
      'sections' => $sections,
    ];
  }

  private function submittedSectionMap(array $sections): array
  {
    $map = [];

    foreach ($sections as $section) {
      if (!is_array($section) || !isset($section['id'])) {
        continue;
      }

      $map[(string) $section['id']] = $section;
    }

    return $map;
  }

  private function filterDataByAllowedPaths(array $submitted, array $allowedPaths, array $default): array
  {
    $filtered = $default;

    foreach ($allowedPaths as $path) {
      $this->copyAllowedPath($submitted, $filtered, explode('.', $path));
    }

    return $filtered;
  }

  private function copyAllowedPath(mixed $source, mixed &$target, array $segments): void
  {
    if (empty($segments)) {
      $target = $source;
      return;
    }

    if (!is_array($source)) {
      return;
    }

    $segment = array_shift($segments);

    if ($segment === '*') {
      foreach ($source as $key => $value) {
        if (!is_int($key) && !ctype_digit((string) $key)) {
          continue;
        }

        if (!is_array($target)) {
          $target = [];
        }

        $index = (int) $key;
        if (!array_key_exists($index, $target)) {
          $target[$index] = [];
        }

        $this->copyAllowedPath($value, $target[$index], $segments);
      }
      return;
    }

    if (!array_key_exists($segment, $source)) {
      return;
    }

    if (empty($segments)) {
      if (!is_array($target)) {
        $target = [];
      }
      $target[$segment] = $source[$segment];
      return;
    }

    if (!is_array($target)) {
      $target = [];
    }

    if (!array_key_exists($segment, $target)) {
      $target[$segment] = [];
    }

    $this->copyAllowedPath($source[$segment], $target[$segment], $segments);
  }

  private function normalizeSettings(array $settings): array
  {
    return [
      'seo' => is_array($settings['seo'] ?? null) ? $settings['seo'] : [],
      'visibility' => is_array($settings['visibility'] ?? null) ? $settings['visibility'] : [],
      'editor' => is_array($settings['editor'] ?? null) ? $settings['editor'] : [],
    ];
  }

  private function encodeJson(array $payload): string
  {
    $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($encoded === false) {
      throw new \InvalidArgumentException('CMS page payload cannot be encoded as JSON.');
    }

    return $encoded;
  }
}
