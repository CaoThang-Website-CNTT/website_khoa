<?php

namespace App\Stores;

use App\Core\Store;
use App\Models\Post;
use App\Core\Schema\QueryBuilder;
use App\Core\Schema\Compiler\MySQLCompiler;

interface IPostStore
{
  public function create(Post $post): Post;

  /**
   * Lấy danh sách bài viết — chỉ trả về các cột cần thiết cho listing:
   * title, slug, seo_description (excerpt), seo_image_url (thumbnail),
   * status, view_count, author_id, published_at, created_at.
   * Không trả về content_json để tránh tải dữ liệu nặng không cần thiết.
   */
  public function findAll(int $limit = 20, int $offset = 0): array;

  public function findById(int $id): ?Post;

  /**
   * Chỉ cập nhật các field được truyền vào — partial update.
   * Các field immutable (id, author_id, created_at) bị chặn tại store.
   */
  public function update(int $id, array $data): Post;
  public function softDelete(int $id): void;

  public function findBySlug(string $slug): ?Post;
  public function syncCategories(int $postId, array $categoryIds): bool;
}

class PostStore extends Store implements IPostStore
{
  // Các cột dùng cho listing — tách biệt với detail để kiểm soát payload
  private const LISTING_COLUMNS = [
    'id',
    'title',
    'slug',
    'seo_description',
    'seo_image_url',
    'status',
    'view_count',
    'author_id',
    'published_at',
    'created_at',
  ];

  // Các cột không được phép thay đổi sau khi tạo
  private const IMMUTABLE_COLUMNS = ['id', 'author_id', 'created_at', 'slug'];

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
      'published_at' => $post->published_at,
      'created_at' => $now,
      'updated_at' => $now,
    ]);

    $stmt = $this->db->prepare($query->toSql());
    $success = $stmt->execute($query->getBindings());

    if (!$success) {
      throw new \RuntimeException('Không thể lưu bài viết vào cơ sở dữ liệu.');
    }

    $post->id = (int) $this->db->lastInsertId();
    return $post;
  }

  public function findAll(int $limit = 20, int $offset = 0): array
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder
      ->from('posts')
      ->select(self::LISTING_COLUMNS)
      ->is('deleted_at', null)          // loại trừ soft-deleted
      ->order('created_at', ['ascending' => false])
      ->limit($limit)
      ->range($offset, $offset + $limit - 1);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return array_map(
      static fn(array $row) => Post::fromArray($row),
      $stmt->fetchAll(\PDO::FETCH_ASSOC),
    );
  }

  public function findById(int $id): ?Post
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
      return $this->findById($id)
        ?? throw new \RuntimeException("Post #{$id} không tồn tại.");
    }

    $data['updated_at'] = (new \DateTime())->format('Y-m-d H:i:s');

    $query = $builder
      ->from('posts')
      ->update($data)
      ->eq('id', $id)
      ->is('deleted_at', null);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return $this->findById($id)
      ?? throw new \RuntimeException("Post #{$id} không tồn tại sau khi cập nhật.");
  }

  public function softDelete(int $id): void
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $now = (new \DateTime())->format('Y-m-d H:i:s');

    // Ghi deleted_at — record vẫn tồn tại trong DB, chỉ bị ẩn khỏi mọi query thường
    $query = $builder
      ->from('posts')
      ->update(['deleted_at' => $now, 'updated_at' => $now])
      ->eq('id', $id);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
  }

  public function syncCategories(int $postId, array $newCategoryIds): bool
  {
    $newCategoryIds = array_unique(array_map('intval', $newCategoryIds));

    $query = (new QueryBuilder(new MySQLCompiler()))
      ->from('category_post')
      ->select('category_id')
      ->eq('post_id', $postId);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    $currentCategoryIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

    $toAdd = array_diff($newCategoryIds, $currentCategoryIds);
    $toRemove = array_diff($currentCategoryIds, $newCategoryIds);

    print_r([
      'current' => $currentCategoryIds,
      'new' => $newCategoryIds,
      'toAdd' => $toAdd,
      'toRemove' => $toRemove,
    ]);

    if (empty($toAdd) && empty($toRemove))
      return true;

    if (!empty($toRemove)) {
      $deleteQuery = (new QueryBuilder(new MySQLCompiler()))
        ->from('category_post')
        ->delete()
        ->eq('post_id', $postId)
        ->in('category_id', $toRemove);

      $stmtDel = $this->db->prepare($deleteQuery->toSql());
      $success = $stmtDel->execute($deleteQuery->getBindings());

      if (!$success) {
        throw new \RuntimeException('Không thể cập nhật danh mục cho bài viết.');
      }
    }


    if (!empty($toAdd)) {
      $insertQuery = (new QueryBuilder(new MySQLCompiler()))
        ->from('category_post')
        ->insert(array_map(fn($categoryId) => ['post_id' => $postId, 'category_id' => $categoryId], $toAdd));

      $stmtIns = $this->db->prepare($insertQuery->toSql());
      $success = $stmtIns->execute($insertQuery->getBindings());

      if (!$success) {
        throw new \RuntimeException('Không thể cập nhật danh mục cho bài viết.');
      }
    }

    return true;
  }
}