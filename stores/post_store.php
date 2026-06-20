<?php

namespace App\Stores;

use App\Core\Store;
use App\Models\Post;
use App\Core\Schema\QueryBuilder;
use App\Core\Schema\Compiler\MySQLCompiler;

interface IPostStore
{
  public function create(Post $post): Post;

  /** @return Post[] */
  public function getPaginated(array $filters = []): array;
  public function getById(int $id): ?Post;
  public function getFeatured(int $limit = 5, array $filters = []): array;
  public function getRelatedPosts(array $postIds, int $excludePostId, int $limit, int $offset): array;
  public function countRelatedPosts(array $postIds, int $excludePostId): int;

  public function update(int $id, array $data): Post;
  public function softDelete(int $id): void;
  public function findBySlug(string $slug): ?Post;
  public function getTotalCount(array $filters = []): int;
}

class PostStore extends Store implements IPostStore
{
  private const LISTING_COLUMNS = [
    'id',
    'title',
    'slug',
    'seo_description',
    'seo_image_url',
    'status',
    'view_count',
    'is_featured',
    'author_id',
    'published_at',
    'created_at',
  ];

  private const IMMUTABLE_COLUMNS = ['id', 'created_at'];

  public function create(Post $post): Post
  {
    $builder = new QueryBuilder(new MySQLCompiler());
    $now = (new \DateTime())->format('Y-m-d H:i:s');

    $query = $builder->from('posts')->insert([
      'title' => $post->title,
      'slug' => $post->slug,
      'content_json' => $post->content_json,
      'settings_json' => $post->settings_json,
      'author_id' => $post->author_id,
      'status' => $post->status,
      'view_count' => $post->view_count,
      'seo_description' => $post->seo_description,
      'seo_image_url' => $post->seo_image_url,
      'is_featured' => $post->is_featured ? 1 : 0,
      'published_at' => $post->published_at,
      'created_at' => $now,
      'updated_at' => $now,
    ]);

    $stmt = $this->db->prepare($query->toSql());
    $success = $stmt->execute($query->getBindings());

    if (!$success) {
      throw new \RuntimeException('Không thể lưu bài viết.');
    }

    $post->id = (int) $this->db->lastInsertId();
    return $post;
  }

  public function getPaginated(array $filters = []): array
  {
    $page = max(1, (int) ($filters['page'] ?? 1));
    $limit = max(1, (int) ($filters['limit'] ?? 15));
    $offset = ($page - 1) * $limit;

    $builder = $this->buildListingQuery($filters)
      ->select(self::LISTING_COLUMNS);

    $sortBy = $this->normalizeSortColumn((string) ($filters['sort'] ?? 'published_at'));
    $ascending = strtolower((string) ($filters['order'] ?? 'desc')) === 'asc';

    $query = $builder
      ->order($sortBy, ['ascending' => $ascending])
      ->order('id', ['ascending' => false])
      ->range($offset, $offset + $limit - 1);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return array_map(
      fn(array $row) => Post::fromArray($row),
      $stmt->fetchAll(\PDO::FETCH_ASSOC),
    );
  }

  public function getById(int $id): ?Post
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder
      ->from('posts')
      ->select('*')
      ->eq('id', $id)
      ->is('deleted_at', null)
      ->limit(1);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $row ? Post::fromArray($row) : null;
  }

  /** @return Post[] */
  public function getFeatured(int $limit = 5, array $filters = []): array
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder
      ->from('posts')
      ->select(self::LISTING_COLUMNS)
      ->is('deleted_at', null)
      ->eq('is_featured', "1")
      ->orderByField('status', ['draft', 'publish', 'deleted'])
      ->order('created_at', ['ascending' => false])
      ->limit($limit);

    if (isset($filters['status'])) {
      $query->eq('status', $filters['status']);
    }

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return array_map(
      fn(array $row) => Post::fromArray($row),
      $stmt->fetchAll(\PDO::FETCH_ASSOC),
    );
  }

  public function getRelatedPosts(array $postIds, int $excludePostId, int $limit, int $offset): array
  {
    if (empty($postIds)) {
      return [];
    }

    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder
      ->from('posts')
      ->select(self::LISTING_COLUMNS)
      ->is('deleted_at', null)
      ->eq('status', 'published')
      ->in('id', $postIds)
      ->neq('id', $excludePostId)
      ->order('is_featured', ['ascending' => false])
      ->order('published_at', ['ascending' => false])
      ->order('id', ['ascending' => false])
      ->range($offset, $offset + $limit - 1);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return array_map(
      fn(array $row) => Post::fromArray($row),
      $stmt->fetchAll(\PDO::FETCH_ASSOC),
    );
  }

  public function countRelatedPosts(array $postIds, int $excludePostId): int
  {
    if (empty($postIds)) {
      return 0;
    }

    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder
      ->from('posts')
      ->select('COUNT(*) AS total')
      ->is('deleted_at', null)
      ->eq('status', 'published')
      ->in('id', $postIds)
      ->neq('id', $excludePostId);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $row ? (int) $row['total'] : 0;
  }

  public function findBySlug(string $slug): ?Post
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder
      ->from('posts')
      ->select('*')
      ->eq('slug', $slug)
      ->is('deleted_at', null)
      ->limit(1);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $row ? Post::fromArray($row) : null;
  }

  public function update(int $id, array $data): Post
  {
    $builder = new QueryBuilder(new MySQLCompiler());
    $data = array_diff_key($data, array_flip(self::IMMUTABLE_COLUMNS));

    if (empty($data)) {
      return $this->getById($id)
        ?? throw new \RuntimeException("Bài viết #{$id} không tồn tại.");
    }

    $data['updated_at'] = (new \DateTime())->format('Y-m-d H:i:s');

    $query = $builder
      ->from('posts')
      ->update($data)
      ->eq('id', $id)
      ->is('deleted_at', null);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return $this->getById($id)
      ?? throw new \RuntimeException("Bài viết #{$id} không tồn tại sau khi cập nhật.");
  }

  public function softDelete(int $id): void
  {
    $builder = new QueryBuilder(new MySQLCompiler());
    $now = (new \DateTime())->format('Y-m-d H:i:s');

    $query = $builder
      ->from('posts')
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

  private function buildListingQuery(array $filters): QueryBuilder
  {
    $builder = (new QueryBuilder(new MySQLCompiler()))
      ->from('posts')
      ->is('deleted_at', null);

    $status = trim((string) ($filters['status'] ?? ''));
    if ($status !== '') {
      $builder->eq('status', $status);
    }

    if (isset($filters['is_featured'])) {
      $builder->eq('is_featured', (string) $filters['is_featured']);
    }

    $search = trim((string) ($filters['search'] ?? ''));
    if ($search !== '') {
      $builder->whereGroup(function (QueryBuilder $query) use ($search) {
        $query
          ->like('title', '%' . $search . '%')
          ->orLike('seo_description', '%' . $search . '%');
      });
    }

    $postIds = $filters['post_ids'] ?? null;
    if (is_array($postIds)) {
      $postIds = array_values(array_unique(array_map('intval', $postIds)));

      if (empty($postIds)) {
        $builder->in('id', [-1]);
      } else {
        $builder->in('id', $postIds);
      }
    }

    return $builder;
  }

  private function normalizeSortColumn(string $sort): string
  {
    return match ($sort) {
      'published_at' => 'published_at',
      default => 'published_at',
    };
  }
}