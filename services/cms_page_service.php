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
  public function getSectionData(string $slug, string $sectionId): array;
  public function getPageForEditing(string $slug): array;
  public function saveDraft(string $slug, array $payload): CmsPage;
  public function publish(string $slug, array $payload): CmsPage;
  public function prepareDocument(string $slug, array $document): array;
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

  public function getSectionData(string $slug, string $sectionId): array
  {
    $page = $this->getPublishedPageBySlug($slug) ?? $this->getPageBySlug($slug);
    $document = $this->normalizeDocument($slug, $page->content());

    foreach ($document['sections'] as $section) {
      if (($section['id'] ?? '') === $sectionId) {
        return is_array($section['data'] ?? null) ? $section['data'] : [];
      }
    }

    return [];
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
    $this->validateEducationDocument($slug, $document);
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
          : $this->normalizeSectionData($submittedData, $sectionSchema, $defaultData),
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

  private function normalizeSectionData(array $submittedData, array $sectionSchema, array $defaultData): array
  {
    $allowedPaths = $sectionSchema['editable_fields'] ?? [];
    $variants = is_array($sectionSchema['variants'] ?? null) ? $sectionSchema['variants'] : [];

    if (!empty($variants)) {
      $allowedPaths[] = 'variant';
    }

    $filtered = $this->filterDataByAllowedPaths($submittedData, $allowedPaths, $defaultData);

    foreach (array_keys(is_array($sectionSchema['repeaters'] ?? null) ? $sectionSchema['repeaters'] : []) as $path) {
      if (str_contains($path, '*') || !array_key_exists($path, $submittedData))
        continue;
      $filtered[$path] = is_array($submittedData[$path]) ? array_values($submittedData[$path]) : [];
    }

    if (!empty($variants)) {
      $defaultVariant = array_key_first($variants) ?: 'default';
      $variant = trim((string) ($filtered['variant'] ?? $defaultVariant));
      $filtered['variant'] = isset($variants[$variant]) ? $variant : $defaultVariant;
    }

    return $filtered;
  }

  public function prepareDocument(string $slug, array $document): array
  {
    if (!$this->_schemas->hasPage($slug)) {
      throw new \InvalidArgumentException("Trang CMS'{$slug}' chưa được đăng ký.");
    }

    return $this->normalizeDocument($slug, $document);
  }

  private function validateEducationDocument(string $slug, array $document): void
  {
    if (!in_array($slug, ['education', 'admissions', 'academic-programs', 'program-outcomes', 'curriculum'], true))
      return;
    $section = $document['sections'][0]['data'] ?? [];
    if (trim((string) ($section['title'] ?? '')) === '') {
      throw new \InvalidArgumentException('Tiêu đề trang đào tạo không được để trống.');
    }

    foreach (['cta_url' => true] as $field => $externalOnly) {
      if (!isset($section[$field]))
        continue;
      $value = trim((string) $section[$field]);
      if ($value !== '' && (!filter_var($value, FILTER_VALIDATE_URL) || !preg_match('/^https?:\/\//i', $value))) {
        throw new \InvalidArgumentException("{$field} phải là URL HTTP hoặc HTTPS hợp lệ.");
      }
    }

    foreach ($section['links'] ?? [] as $link) {
      if (trim((string) ($link['title'] ?? '')) === '')
        throw new \InvalidArgumentException('Tiêu đề thẻ điều hướng không được để trống.');
      $url = trim((string) ($link['url'] ?? ''));
      if ($url === '' || preg_match('/^(javascript|data):/i', $url))
        throw new \InvalidArgumentException('Liên kết điều hướng không hợp lệ.');
    }

    $programs = is_array($section['programs'] ?? null) ? $section['programs'] : [];
    if (in_array($slug, ['academic-programs', 'program-outcomes', 'curriculum'], true) && !$programs) {
      throw new \InvalidArgumentException('Trang phải có ít nhất một chương trình đào tạo.');
    }
    $programKeys = [];
    foreach ($programs as $program) {
      $key = trim((string) ($program['key'] ?? ''));
      $name = trim((string) ($program['name'] ?? ''));
      if ($key === '' || !preg_match('/^[a-z0-9_-]+$/', $key))
        throw new \InvalidArgumentException('Mã chương trình chỉ được chứa chữ thường không dấu, số, gạch ngang hoặc gạch dưới.');
      if ($name === '')
        throw new \InvalidArgumentException('Tên chương trình không được để trống.');
      if (isset($programKeys[$key]))
        throw new \InvalidArgumentException("Mã chương trình '{$key}' bị trùng.");
      $programKeys[$key] = true;

      $semesterKeys = [];
      foreach ($program['semesters'] ?? [] as $semester) {
        $semesterKey = trim((string) ($semester['key'] ?? ''));
        if ($semesterKey === '' || isset($semesterKeys[$semesterKey]))
          throw new \InvalidArgumentException("Mã học kỳ của chương trình '{$key}' phải có giá trị và không trùng.");
        $semesterKeys[$semesterKey] = true;
        if (trim((string) ($semester['name'] ?? '')) === '')
          throw new \InvalidArgumentException('Tên học kỳ không được để trống.');
        foreach ($semester['courses'] ?? [] as $course) {
          if (trim((string) ($course['name'] ?? '')) === '')
            throw new \InvalidArgumentException('Tên học phần không được để trống.');
          foreach (['credits', 'theory', 'practice'] as $numberField) {
            $value = (string) ($course[$numberField] ?? '');
            if ($value === '' || !is_numeric($value) || (float) $value < 0)
              throw new \InvalidArgumentException("{$numberField} của học phần phải là số không âm.");
          }
        }
      }
    }
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
