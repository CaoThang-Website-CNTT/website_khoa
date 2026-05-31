<?php

namespace App\Services;

use App\Models\Post;
use App\Stores\PostStore;
use App\Stores\AccountStore;
use App\Stores\CategoryStore;
use App\Core\Pageable;
use Database;

interface IPostService
{
  public function create(array $payload): Post;

  public function getPosts(int $page, int $limit = 15): Pageable;
  public function getPost(int $post_id): Post;
  public function getPostBySlug(string $slug, bool $with_author = false): Post;
  /**
   * Lấy n (mặc định là 5) bài viết nổi bật nhất (featured).
   * 
   * @return Post[]
   */
  public function getFeaturedPosts(int $limit = 5): array;
  /**
   * Cập nhật nội dung và/hoặc trạng thái của bài viết.
   * Chỉ các field được truyền mới bị ghi đè — các field còn lại giữ nguyên.
   * Khi status chuyển sang 'published' lần đầu, published_at sẽ được ghi tự động.
   */
  public function update(int $id, array $payload): Post;
  public function delete(int $id): void;
}

class PostService implements IPostService
{
  private PostStore $_postStore;
  private AccountStore $_accountStore;
  private CategoryStore $_categoryStore;

  public function __construct(
    PostStore $postStore,
    AccountStore $accountStore,
    CategoryStore $categoryStore
  ) {
    $this->_postStore = $postStore;
    $this->_accountStore = $accountStore;
    $this->_categoryStore = $categoryStore;
  }

  public function getPosts(int $page, int $limit = 15): Pageable
  {
    $posts = $this->_postStore->getPaginated($page, $limit);
    $total = $this->_postStore->getTotalCount();

    // Tối ưu: Load bulk thông tin tác giả để tránh N+1
    $authorIds = array_unique(array_filter(array_map(fn($p) => $p->author_id, $posts)));
    if (!empty($authorIds)) {
      $authors = $this->_accountStore->getByIds($authorIds);
      $authorMap = [];
      foreach ($authors as $author) {
        $authorMap[$author->id] = $author;
      }

      foreach ($posts as $post) {
        if ($post->author_id && isset($authorMap[$post->author_id])) {
          $post->author = $authorMap[$post->author_id];
        }
      }
    }

    return new Pageable($posts, $total, $limit, $page);
  }

  public function getPost(int $id): Post
  {
    $post = $this->_postStore->getById($id)
      ?? throw new \RuntimeException("Bài viết #{$id} không tồn tại.");

    $categoryIds = $this->_postStore->getCategoryIds($id);
    $post->categories = $this->_categoryStore->getByIds($categoryIds);

    return $post;
  }

  public function getPostBySlug(string $slug, bool $with_author = false): Post
  {
    $post = $this->_postStore->findBySlug($slug)
      ?? throw new \RuntimeException("Bài viết với slug '{$slug}' không tồn tại.");

    $categoryIds = $this->_postStore->getCategoryIds($post->id);
    $post->categories = $this->_categoryStore->getByIds($categoryIds);

    if ($with_author && $post->author_id) {
      $post->author = $this->_accountStore->getById($post->author_id);
    }

    return $post;
  }

  /** @return Post[] */
  public function getFeaturedPosts(int $limit = 5): array
  {
    return $this->_postStore->getFeatured($limit);
  }

  public function create(array $payload): Post
  {
    $meta = $payload['meta'] ?? [];
    $blocks = $payload['blocks'] ?? [];

    if (!isset($meta['author_id']) || trim((string) $meta['author_id']) === '') {
      throw new \InvalidArgumentException("Dữ liệu không hợp lệ: Thiếu ID tác giả (author_id).");
    }

    $authorId = (int) $meta['author_id'];

    // Kiểm tra xem user có tồn tại không
    $author = $this->_accountStore->getById($authorId);

    if (!$author) {
      throw new \InvalidArgumentException("Tác giả với ID '{$authorId}' không tồn tại hoặc đã bị xoá.");
    }

    $contentJson = json_encode($blocks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $settingsJson = isset($meta['settings']) ? json_encode($meta['settings'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;

    if ($contentJson === false) {
      throw new \InvalidArgumentException('Blocks không hợp lệ, không thể encode JSON.');
    }

    $slug = $this->resolveSlug($meta['slug'] ?? '', $meta['title'] ?? '');

    // Kiểm tra slug trùng trước khi insert để cho lỗi rõ ràng hơn DB constraint
    if ($this->_postStore->findBySlug($slug) !== null) {
      throw new \RuntimeException("Slug '{$slug}' đã tồn tại.");
    }

    // published_at chỉ được ghi khi status là published
    $publishedAt = null;
    if (($meta['status'] ?? 'draft') === 'published') {
      $publishedAt = (new \DateTime())->format('Y-m-d H:i:s');
    }

    $post = new Post(
      title: $meta['title'] ?? '',
      slug: $slug,
      content_json: $contentJson,
      settings_json: $settingsJson,
      author_id: isset($meta['author_id']) ? (int) $meta['author_id'] : null,
      status: $meta['status'] ?? 'draft',
      view_count: (int) ($meta['init_view_count'] ?? 0),
      seo_description: $meta['excerpt'] ?? null,
      seo_image_url: $this->resolveSeoImage($meta['featured_image'] ?? null),
      is_featured: $meta['is_featured'] ?? false,
      published_at: $publishedAt,
    );

    return Database::getInstance()->transaction(function () use ($post, $meta, $payload) {

      $post = $this->_postStore->create($post);

      // Kiểm tra xem client có gửi category_ids lên không
      if (isset($meta['category_ids'])) {
        $categoryIds = is_array($meta['category_ids']) ? $meta['category_ids'] : [$meta['category_ids']];
        $this->_postStore->syncCategories($post->id, $categoryIds);
      }

      return $post;
    });
  }

  public function update(int $id, array $payload): Post
  {
    $existing = $this->_postStore->getById($id)
      ?? throw new \RuntimeException("Bài viết #{$id} không tồn tại.");

    $meta = $payload['meta'] ?? [];
    $blocks = $payload['blocks'] ?? null;

    $data = [];

    if (isset($meta['title']))
      $data['title'] = $meta['title'];
    if (isset($meta['slug']))
      $data['slug'] = $this->resolveSlug($meta['slug'], $meta['title'] ?? $existing->title);
    if (isset($meta['author_id']))
      $data['author_id'] = (int) $meta['author_id'];
    if (isset($meta['status']))
      $data['status'] = $meta['status'];
    if (isset($meta['init_view_count']))
      $data['view_count'] = (int) $meta['init_view_count'];
    if (isset($meta['excerpt']))
      $data['seo_description'] = $meta['excerpt'];
    if (isset($meta['featured_image']))
      $data['seo_image_url'] = $this->resolveSeoImage($meta['featured_image']);
    if (isset($meta['settings']['is_featured']))
      $data['is_featured'] = $meta['settings']['is_featured'];

    // published_at: chỉ ghi lần đầu khi chuyển sang published
    // Nếu đã có published_at trước đó thì giữ nguyên
    if (isset($meta['status']) && $meta['status'] === 'published' && $existing->published_at === null) {
      $data['published_at'] = (new \DateTime())->format('Y-m-d H:i:s');
    }

    if (isset($meta)) {
      $data['settings_json'] = json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    if ($blocks !== null) {
      $encoded = json_encode($blocks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

      if ($encoded === false) {
        throw new \InvalidArgumentException('blocks không hợp lệ, không thể encode JSON.');
      }

      $data['content_json'] = $encoded;
    }

    return Database::getInstance()->transaction(function () use ($id, $data, $meta) {
      $post = $this->_postStore->update($id, $data);

      // Đồng bộ danh mục nếu có gửi lên
      if (isset($meta['category_ids'])) {
        $categoryIds = is_array($meta['category_ids']) ? $meta['category_ids'] : [$meta['category_ids']];
        $this->_postStore->syncCategories($id, $categoryIds);
      }

      return $post;
    });
  }

  public function delete(int $id): void
  {
    // Xác nhận tồn tại trước để trả lỗi rõ ràng thay vì silent no-op
    $this->_postStore->getById($id)
      ?? throw new \RuntimeException("Bài viết #{$id} không tồn tại.");

    $this->_postStore->softDelete($id);
  }

  // ---------------------------------------------------------------------------
  // Private helpers
  // ---------------------------------------------------------------------------

  /**
   * Slug resolution: ưu tiên slug client gửi, fallback sang generate từ title.
   * Lowercase, bỏ dấu tiếng Việt, thay khoảng trắng bằng dấu gạch ngang.
   */
  private function resolveSlug(string $slug, string $title): string
  {
    $source = trim($slug) !== '' ? $slug : $title;
    return generateSlug($source);
  }

  /**
   * Xác định giá trị seo_image_url từ featured_image của meta.
   * featured_image có thể là:
   *   - null       → không có thumbnail
   *   - string URL → external URL, lưu thẳng
   *   - array      → object có 'url' key (định dạng media object từ editor)
   */
  private function resolveSeoImage(mixed $featuredImage): ?string
  {
    if ($featuredImage === null) {
      return null;
    }

    if (is_string($featuredImage)) {
      return $featuredImage ?: null;
    }

    if (is_array($featuredImage) && isset($featuredImage['url'])) {
      return $featuredImage['url'] ?: null;
    }

    return null;
  }
}