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
  public function saveBuilderDraft(string $slug, array $payload, ?int $actorId = null): CmsPage;
  public function publishBuilder(string $slug): CmsPage;
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
      ?? throw new \RuntimeException("Trang CMS #{$id} không tồn tại.");
  }

  public function getPageBySlug(string $slug): CmsPage
  {
    if (!$this->_schemas->hasPage($slug)) {
      throw new \InvalidArgumentException("Trang CMS '{$slug}' chưa được đăng ký.");
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
      throw new \InvalidArgumentException("Trang CMS '{$slug}' chưa được đăng ký.");
    }

    $page = $this->getPageBySlug($slug);

    return [
      'page' => $page,
      'schema' => $schema,
      'document' => $this->normalizeDocument($slug, $page->content()),
      'builderDocument' => $this->normalizeBuilderDocument($page->builderDraft()),
      'builderPublishedDocument' => $this->normalizeBuilderDocument($page->builderPublished()),
      'builderSnapshots' => $page->builderSnapshots(),
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

  public function saveBuilderDraft(string $slug, array $payload, ?int $actorId = null): CmsPage
  {
    $schema = $this->_schemas->page($slug);

    if ($schema === null) {
      throw new \InvalidArgumentException("Trang CMS '{$slug}' chưa được đăng ký.");
    }

    $page = $this->getPageBySlug($slug);
    $document = $this->normalizeBuilderDocument($payload['content'] ?? $payload);
    $snapshots = $this->appendBuilderSnapshot($page->builderSnapshots(), $document, $actorId);

    $data = [
      'builder_draft_json' => $this->encodeJson($document),
      'builder_snapshots_json' => $this->encodeJson($snapshots),
    ];

    return Database::getInstance()->transaction(function () use ($page, $schema, $data): CmsPage {
      if ($page->id === null) {
        return $this->_store->create(new CmsPage(
          title: $schema['title'],
          slug: $schema['slug'],
          route_path: $schema['route_path'],
          type: $schema['type'],
          layout_mode: $schema['layout_mode'],
          content_json: $this->encodeJson($this->_schemas->defaultDocument($schema['slug'])),
          settings_json: '{}',
          builder_draft_json: $data['builder_draft_json'],
          builder_snapshots_json: $data['builder_snapshots_json'],
        ));
      }

      return $this->_store->update((int) $page->id, $data);
    });
  }

  public function publishBuilder(string $slug): CmsPage
  {
    $schema = $this->_schemas->page($slug);

    if ($schema === null) {
      throw new \InvalidArgumentException("Trang CMS '{$slug}' chưa được đăng ký.");
    }

    $page = $this->getPageBySlug($slug);
    $document = $this->normalizeBuilderDocument($page->builderDraft());

    if (empty($document['blocks'])) {
      throw new \InvalidArgumentException('Bản nháp CMS v2 không có block nào để xuất bản.');
    }

    $data = [
      'layout_mode' => 'block_builder',
      'builder_published_json' => $this->encodeJson($document),
      'builder_enabled_at' => (new \DateTime())->format('Y-m-d H:i:s'),
    ];

    return Database::getInstance()->transaction(function () use ($page, $schema, $document, $data): CmsPage {
      if ($page->id === null) {
        return $this->_store->create(new CmsPage(
          title: $schema['title'],
          slug: $schema['slug'],
          route_path: $schema['route_path'],
          type: $schema['type'],
          status: 'published',
          layout_mode: 'block_builder',
          content_json: $this->encodeJson($this->_schemas->defaultDocument($schema['slug'])),
          settings_json: '{}',
          builder_draft_json: $this->encodeJson($document),
          builder_published_json: $data['builder_published_json'],
          builder_enabled_at: $data['builder_enabled_at'],
        ));
      }

      return $this->_store->update((int) $page->id, $data);
    });
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
      throw new \InvalidArgumentException("Trang CMS '{$slug}' chưa được đăng ký.");
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
      throw new \InvalidArgumentException("Trang CMS '{$slug}' chưa được đăng ký.");
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

  private function normalizeBuilderDocument(array $document): array
  {
    $theme = is_array($document['theme'] ?? null) ? $document['theme'] : [];
    $activeTheme = trim((string) ($theme['active'] ?? 'default'));

    if (!preg_match('/^[a-z][a-z0-9_-]{0,63}$/', $activeTheme)) {
      throw new \InvalidArgumentException('Tên theme CMS v2 không hợp lệ.');
    }

    return [
      'version' => 2,
      'blocks' => $this->normalizeBuilderBlocks($document['blocks'] ?? []),
      'globalStyles' => $this->filterStyleObject($document['globalStyles'] ?? []),
      'theme' => [
        'active' => $activeTheme,
        'options' => is_array($theme['options'] ?? null) ? $theme['options'] : [],
      ],
    ];
  }

  private function normalizeBuilderBlocks(mixed $blocks, int $depth = 0): array
  {
    if (!is_array($blocks)) {
      throw new \InvalidArgumentException('Các block CMS v2 phải là một mảng.');
    }

    if ($depth > 6) {
      throw new \InvalidArgumentException('Các block CMS v2 được lồng nhau quá sâu.');
    }

    return array_map(
      fn(mixed $block) => $this->normalizeBuilderBlock($block, $depth),
      array_values($blocks),
    );
  }

  private function normalizeBuilderBlock(mixed $block, int $depth): array
  {
    if (!is_array($block)) {
      throw new \InvalidArgumentException('Block CMS v2 phải là một đối tượng.');
    }

    $type = (string) ($block['type'] ?? '');
    $allowedTypes = [
      'cms/section',
      'cms/row',
      'cms/column',
      'cms/grid',
      'cms/heading',
      'cms/paragraph',
      'cms/image',
      'cms/button',
      'cms/spacer',
      'cms/theme-banner',
    ];

    if (!in_array($type, $allowedTypes, true)) {
      throw new \InvalidArgumentException("Loại block '{$type}' không được phép sử dụng");
    }

    $id = trim((string) ($block['id'] ?? ''));
    if ($id !== '' && !preg_match('/^[A-Za-z][A-Za-z0-9_-]{0,63}$/', $id)) {
      throw new \InvalidArgumentException("ID block'{$id}' không hợp lệ.");
    }

    $attrs = is_array($block['attrs'] ?? null) ? $block['attrs'] : [];
    $attrs['advanced'] = $this->normalizeAdvancedAttrs(
      is_array($attrs['advanced'] ?? null) ? $attrs['advanced'] : [],
    );

    return [
      'id' => $id,
      'type' => $type,
      'version' => max(1, (int) ($block['version'] ?? 1)),
      'attrs' => $this->filterBuilderAttrs($attrs),
      'style' => $this->filterStyleObject($block['style'] ?? []),
      'children' => $this->normalizeBuilderBlocks($block['children'] ?? [], $depth + 1),
    ];
  }

  private function normalizeAdvancedAttrs(array $advanced): array
  {
    $anchorId = trim((string) ($advanced['anchorId'] ?? ''));
    if ($anchorId !== '' && !preg_match('/^[A-Za-z][A-Za-z0-9_-]{0,63}$/', $anchorId)) {
      throw new \InvalidArgumentException("Anchor ID '{$anchorId}' không hợp lệ.");
    }

    $classTokens = $advanced['classTokens'] ?? [];
    if (!is_array($classTokens)) {
      $classTokens = [];
    }

    $safeTokens = [];
    foreach ($classTokens as $token) {
      $token = trim((string) $token);
      if ($token === '') {
        continue;
      }
      if (!preg_match('/^[a-z][a-z0-9_-]{0,63}$/', $token)) {
        throw new \InvalidArgumentException("Class '{$token}' không hợp lệ.");
      }
      $safeTokens[] = $token;
    }

    return [
      'anchorId' => $anchorId,
      'classTokens' => array_values(array_unique($safeTokens)),
    ];
  }

  private function filterBuilderAttrs(array $attrs): array
  {
    foreach (['url', 'href', 'src'] as $key) {
      if (isset($attrs[$key]) && !$this->isSafeUrl((string) $attrs[$key])) {
        throw new \InvalidArgumentException("Trường URL CMS v2 '{$key}' không hợp lệ.");
      }
    }

    return $attrs;
  }

  private function filterStyleObject(mixed $style): array
  {
    if (!is_array($style)) {
      return [];
    }

    $safe = [];
    foreach ($style as $key => $value) {
      if (!is_string($key) || !preg_match('/^[a-z][a-zA-Z0-9_-]{0,63}$/', $key)) {
        continue;
      }

      if (is_scalar($value) || $value === null) {
        $safe[$key] = $value;
      } elseif (is_array($value)) {
        $safe[$key] = $this->filterStyleObject($value);
      }
    }

    return $safe;
  }

  private function appendBuilderSnapshot(array $snapshots, array $document, ?int $actorId): array
  {
    array_unshift($snapshots, [
      'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
      'actor_id' => $actorId,
      'summary' => 'Đã lưu bản nháp CMS v2',
      'document' => $document,
    ]);

    return array_slice($snapshots, 0, 20);
  }

  private function isSafeUrl(string $url): bool
  {
    $url = trim($url);
    return $url === '' || (bool) preg_match('/^(https?:\/\/|mailto:|\/|public\/|media\/)[^\s]*$/i', $url);
  }

  private function encodeJson(array $payload): string
  {
    $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($encoded === false) {
      throw new \InvalidArgumentException('Dữ liệu trang CMS không thể mã hóa thành JSON.');
    }

    return $encoded;
  }
}
