<?php

namespace App\Services;

use App\Models\Post;
use App\Stores\PostStore;
use App\Stores\AccountStore;
use App\Stores\MediaStore;

interface IPostService
{
  public function create(array $payload): Post;

  /**
   * Trả về danh sách bài viết dạng listing — không có content_json.
   * $filters hiện tại hỗ trợ: ['limit' => int, 'offset' => int]
   */
  public function list(array $filters = []): array;

  public function get(int $id): Post;

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
  private MediaStore $_mediaStore;
  private AccountStore $_accountStore;

  public function __construct(
    PostStore $postStore,
    MediaStore $mediaStore,
    AccountStore $accountStore
  ) {
    $this->_postStore = $postStore;
    $this->_mediaStore = $mediaStore;
    $this->_accountStore = $accountStore;
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
      author_id: isset($meta['author_id']) ? (int) $meta['author_id'] : null,
      status: $meta['status'] ?? 'draft',
      view_count: (int) ($meta['init_view_count'] ?? 0),
      seo_description: $meta['excerpt'] ?? null,
      seo_image_url: $this->resolveSeoImage($meta['featured_image'] ?? null),
      published_at: $publishedAt,
    );

    $post = $this->_postStore->create($post);

    // Sau khi có post ID, gắn các media nội bộ được tham chiếu trong blocks
    // External URL (mediaId === null) bỏ qua — chúng không có record trong DB
    $internalMediaIds = $this->extractInternalMediaIds($blocks);
    if (!empty($internalMediaIds)) {
      $this->_mediaStore->attachToPost($internalMediaIds, $post->id);
    }

    return $post;
  }

  public function list(array $filters = []): array
  {
    $limit = max(1, (int) ($filters['limit'] ?? 20));
    $offset = max(0, (int) ($filters['offset'] ?? 0));

    return $this->_postStore->findAll($limit, $offset);
  }

  public function get(int $id): Post
  {
    return $this->_postStore->findById($id)
      ?? throw new \RuntimeException("Bài viết #{$id} không tồn tại.");
  }

  public function update(int $id, array $payload): Post
  {
    // Xác nhận post tồn tại trước — lỗi sớm, rõ nguyên nhân
    $existing = $this->_postStore->findById($id)
      ?? throw new \RuntimeException("Bài viết #{$id} không tồn tại.");

    $meta = $payload['meta'] ?? [];
    $blocks = $payload['blocks'] ?? null; // null = không gửi blocks → không update content

    $data = [];

    // Chỉ map các field client chủ động gửi lên
    if (isset($meta['title']))
      $data['title'] = $meta['title'];
    if (isset($meta['excerpt']))
      $data['seo_description'] = $meta['excerpt'];
    if (isset($meta['status']))
      $data['status'] = $meta['status'];

    if (isset($meta['featured_image'])) {
      $data['seo_image_url'] = $this->resolveSeoImage($meta['featured_image']);
    }

    // published_at: chỉ ghi lần đầu khi chuyển sang published
    // Nếu đã có published_at trước đó thì giữ nguyên
    if (isset($meta['status']) && $meta['status'] === 'published' && $existing->published_at === null) {
      $data['published_at'] = (new \DateTime())->format('Y-m-d H:i:s');
    }

    if ($blocks !== null) {
      $encoded = json_encode($blocks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

      if ($encoded === false) {
        throw new \InvalidArgumentException('blocks không hợp lệ, không thể encode JSON.');
      }

      $data['content_json'] = $encoded;

      // Đồng bộ media: gắn các media nội bộ vào post sau khi nội dung thay đổi
      $internalMediaIds = $this->extractInternalMediaIds($blocks);
      if (!empty($internalMediaIds)) {
        $this->_mediaStore->attachToPost($internalMediaIds, $id);
      }
    }

    if (empty($data)) {
      // Không có gì cần cập nhật — trả về post hiện tại, tránh query thừa
      return $existing;
    }

    return $this->_postStore->update($id, $data);
  }

  public function delete(int $id): void
  {
    // Xác nhận tồn tại trước để trả lỗi rõ ràng thay vì silent no-op
    $this->_postStore->findById($id)
      ?? throw new \RuntimeException("Bài viết #{$id} không tồn tại.");

    $this->_postStore->softDelete($id);
  }

  // ---------------------------------------------------------------------------
  // Private helpers
  // ---------------------------------------------------------------------------

  /**
   * Duyệt qua blocks, lấy mediaId của các image block nội bộ.
   * External URL có mediaId === null → bỏ qua.
   */
  private function extractInternalMediaIds(array $blocks): array
  {
    $ids = [];

    foreach ($blocks as $block) {
      if (isset($block['mediaId']) && $block['mediaId'] !== null) {
        $ids[] = (int) $block['mediaId'];
      }
    }

    // array_unique để tránh duplicate khi cùng ảnh xuất hiện nhiều lần
    return array_values(array_unique($ids));
  }

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