<?php

namespace App\Stores;

use App\Core\Schema\Compiler\MySQLCompiler;
use App\Core\Schema\QueryBuilder;
use App\Core\Store;
use App\Models\CmsPage;

interface ICmsPageStore
{
  public function create(CmsPage $page): CmsPage;

  /** @return CmsPage[] */
  public function getPaginated(array $filters = []): array;
  public function getById(int $id): ?CmsPage;
  public function findBySlug(string $slug): ?CmsPage;
  public function findPublishedBySlug(string $slug): ?CmsPage;
  public function update(int $id, array $data): CmsPage;
  public function softDelete(int $id): void;
  public function getTotalCount(array $filters = []): int;
}

class CmsPageStore extends Store implements ICmsPageStore
{
  private const LISTING_COLUMNS = [
    'id',
    'title',
    'slug',
    'route_path',
    'type',
    'status',
    'layout_mode',
    'published_at',
    'created_at',
    'updated_at',
  ];

  private const IMMUTABLE_COLUMNS = ['id', 'created_at', 'deleted_at'];

  public function create(CmsPage $page): CmsPage
  {
    $builder = new QueryBuilder(new MySQLCompiler());
    $now = (new \DateTime())->format('Y-m-d H:i:s');

    $query = $builder->from('cms_pages')->insert([
      'title' => $page->title,
      'slug' => $page->slug,
      'route_path' => $page->route_path,
      'type' => $page->type,
      'status' => $page->status,
      'layout_mode' => $page->layout_mode,
      'content_json' => $page->content_json,
      'settings_json' => $page->settings_json,
      'builder_draft_json' => $page->builder_draft_json,
      'builder_published_json' => $page->builder_published_json,
      'builder_snapshots_json' => $page->builder_snapshots_json,
      'builder_enabled_at' => $page->builder_enabled_at,
      'published_at' => $page->published_at,
      'created_at' => $now,
      'updated_at' => $now,
    ]);

    $stmt = $this->db->prepare($query->toSql());
    $success = $stmt->execute($query->getBindings());

    if (!$success) {
      throw new \RuntimeException('Khong the luu trang CMS.');
    }

    $page->id = (int) $this->db->lastInsertId();
    $page->created_at = $now;
    $page->updated_at = $now;

    return $page;
  }

  /** @return CmsPage[] */
  public function getPaginated(array $filters = []): array
  {
    $page = max(1, (int) ($filters['page'] ?? 1));
    $limit = max(1, (int) ($filters['limit'] ?? 15));
    $offset = ($page - 1) * $limit;

    $query = $this->buildListingQuery($filters)
      ->select(self::LISTING_COLUMNS)
      ->order('updated_at', ['ascending' => false])
      ->order('id', ['ascending' => false])
      ->range($offset, $offset + $limit - 1);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return array_map(
      fn(array $row) => CmsPage::fromArray($row),
      $stmt->fetchAll(\PDO::FETCH_ASSOC),
    );
  }

  public function getById(int $id): ?CmsPage
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder
      ->from('cms_pages')
      ->select('*')
      ->eq('id', $id)
      ->is('deleted_at', null)
      ->limit(1);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $row ? CmsPage::fromArray($row) : null;
  }

  public function findBySlug(string $slug): ?CmsPage
  {
    return $this->findBySlugAndStatus($slug);
  }

  public function findPublishedBySlug(string $slug): ?CmsPage
  {
    return $this->findBySlugAndStatus($slug, 'published');
  }

  public function update(int $id, array $data): CmsPage
  {
    $builder = new QueryBuilder(new MySQLCompiler());
    $data = array_diff_key($data, array_flip(self::IMMUTABLE_COLUMNS));

    if (empty($data)) {
      return $this->getById($id)
        ?? throw new \RuntimeException("Trang CMS #{$id} khong ton tai.");
    }

    $data['updated_at'] = (new \DateTime())->format('Y-m-d H:i:s');

    $query = $builder
      ->from('cms_pages')
      ->update($data)
      ->eq('id', $id)
      ->is('deleted_at', null);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return $this->getById($id)
      ?? throw new \RuntimeException("Trang CMS #{$id} khong ton tai sau khi cap nhat.");
  }

  public function softDelete(int $id): void
  {
    $builder = new QueryBuilder(new MySQLCompiler());
    $now = (new \DateTime())->format('Y-m-d H:i:s');

    $query = $builder
      ->from('cms_pages')
      ->update(['deleted_at' => $now, 'updated_at' => $now])
      ->eq('id', $id);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
  }

  public function getTotalCount(array $filters = []): int
  {
    $query = $this->buildListingQuery($filters)
      ->select('COUNT(*) AS total');

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $row ? (int) $row['total'] : 0;
  }

  private function findBySlugAndStatus(string $slug, ?string $status = null): ?CmsPage
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder
      ->from('cms_pages')
      ->select('*')
      ->eq('slug', $slug)
      ->is('deleted_at', null)
      ->limit(1);

    if ($status !== null) {
      $query->eq('status', $status);
    }

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $row ? CmsPage::fromArray($row) : null;
  }

  private function buildListingQuery(array $filters): QueryBuilder
  {
    $builder = (new QueryBuilder(new MySQLCompiler()))
      ->from('cms_pages')
      ->is('deleted_at', null);

    $status = trim((string) ($filters['status'] ?? ''));
    if ($status !== '') {
      $builder->eq('status', $status);
    }

    $type = trim((string) ($filters['type'] ?? ''));
    if ($type !== '') {
      $builder->eq('type', $type);
    }

    $layoutMode = trim((string) ($filters['layout_mode'] ?? ''));
    if ($layoutMode !== '') {
      $builder->eq('layout_mode', $layoutMode);
    }

    $search = trim((string) ($filters['search'] ?? ''));
    if ($search !== '') {
      $builder->whereGroup(function (QueryBuilder $query) use ($search) {
        $query
          ->like('title', '%' . $search . '%')
          ->orLike('slug', '%' . $search . '%')
          ->orLike('route_path', '%' . $search . '%');
      });
    }

    return $builder;
  }
}
