<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Services\PostService;
use Exception;

class PostApiController extends Controller
{
  protected PostService $_postService;

  public function __construct(PostService $postService)
  {
    $this->_postService = $postService;
  }

  public function index(Request $request)
  {
    $page = max(1, (int) $request->query('page', 1));
    $limit = min(100, max(1, (int) $request->query('limit', 10)));
    $search = trim((string) $request->query('search', '')) ?: null;
    $category = trim((string) $request->query('filter', $request->query('category', ''))) ?: null;
    $rawSort = $request->query('sort', $request->query('sort_by', 'published_at'));
    $sortBy = is_array($rawSort) ? ($rawSort['col'] ?? 'published_at') : $rawSort;
    $sortBy = trim((string) $sortBy) ?: 'published_at';
    $order = is_array($rawSort) ? ($rawSort['dir'] ?? 'desc') : $request->query('order', 'desc');
    $order = strtolower(trim((string) $order)) === 'asc' ? 'asc' : 'desc';
    $featured = $request->query('featured');
    $featuredFilter = in_array((string) $featured, ['0', '1'], true) ? (string) $featured : null;
    $filters = [
      'search' => $search,
      'category' => $category,
      'sort' => $sortBy,
      'order' => $order,
      'is_featured' => $featuredFilter
    ];

    $rawFilters = $request->query('filters');
    if (is_array($rawFilters)) {
      foreach ($rawFilters as $filter) {
        $col = (string) ($filter['col'] ?? '');
        $op = (string) ($filter['op'] ?? '=');
        $value = trim((string) ($filter['value'] ?? ''));

        if ($value === '' || $op !== '=') {
          continue;
        }

        if ($col === 'status' && in_array($value, ['published', 'draft'], true)) {
          $filters['status'] = $value;
        }

        if (in_array($col, ['is_feature', 'is_featured'], true) && in_array($value, ['0', '1'], true)) {
          $filters['is_featured'] = $value;
        }
      }
    }

    try {
      $pageable = $this->_postService->getPosts($page, $limit, true, $filters);

      return $this->json([
        'data' => array_map(fn($post) => [
          'id' => $post->id,
          'title' => $post->title,
          'slug' => $post->slug,
          'author_email' => $post->author->email ?? 'N/A',
          'status' => $post->status,
          'is_feature' => $post->is_featured ? 1 : 0,
          'is_featured' => $post->is_featured ? 1 : 0,
          'view_count' => $post->view_count,
          'seo_description' => $post->seo_description,
          'seo_image_url' => $post->seo_image_url,
          'image_url' => $this->resolvePostImageUrl($post->seo_image_url),
          'published_at' => $post->published_at,
          'created_at' => $post->created_at ? date('d/m/Y H:i', strtotime($post->created_at)) : 'N/A',
          'categories' => array_map(fn($category) => [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
          ], $post->categories ?? []),
        ], $pageable->getItems()),
        'total' => $pageable->getTotal(),
        'page' => $pageable->getCurrentPage(),
        'limit' => $pageable->getPerPage(),
        'meta' => [
          'current_page' => $pageable->getCurrentPage(),
          'per_page' => $pageable->getPerPage(),
          'total' => $pageable->getTotal(),
          'last_page' => $pageable->getTotalPages(),
        ],
      ], 200);
    } catch (Exception $e) {
      error_log('Lỗi truy vấn post: ' . $e->getMessage());
      return $this->json(null, 500, 'Không thể truy vấn post.');
    }
  }

  public function related(Request $request, int $id)
  {
    $limit = min(20, max(1, (int) $request->query('limit', 3)));
    $offset = max(0, (int) $request->query('offset', 0));

    try {
      $post = $this->_postService->getPost($id);
      $relatedPosts = $this->_postService->getRelatedPosts($post, $limit, $offset);
      $total = $this->_postService->countRelatedPosts($post);

      return $this->json([
        'items' => array_map(fn($relatedPost) => [
          'id' => $relatedPost->id,
          'title' => $relatedPost->title,
          'slug' => $relatedPost->slug,
          'image_url' => $relatedPost->imageUrl(),
          'published_at' => $relatedPost->published_at,
          'created_at' => $relatedPost->created_at,
          'category' => $relatedPost->categories[0]->name ?? 'Tin tức',
        ], $relatedPosts),
        'total' => $total,
        'has_more' => ($offset + count($relatedPosts)) < $total,
      ], 200);
    } catch (Exception $e) {
      error_log('Lỗi truy vấn bài viết liên quan: ' . $e->getMessage());
      return $this->json(null, 500, 'Không thể tải bài viết liên quan.');
    }
  }

  private function resolvePostImageUrl(?string $imagePath): string
  {
    $fallbackUrl = url('public/img/default-post-thumb.jpg');
    $imagePath = trim((string) $imagePath);

    if ($imagePath === '') {
      return $fallbackUrl;
    }

    if (preg_match('/^https?:\/\//i', $imagePath)) {
      return $imagePath;
    }

    $relativePath = ltrim(str_replace('\\', '/', $imagePath), '/');
    if (str_starts_with($relativePath, 'public/media/')) {
      $relativePath = substr($relativePath, strlen('public/media/'));
    } elseif (str_starts_with($relativePath, 'media/')) {
      $relativePath = substr($relativePath, strlen('media/'));
    }

    $mediaFilePath = BASE_PATH . '/storage/media/' . $relativePath;
    if (!is_file($mediaFilePath)) {
      return $fallbackUrl;
    }

    return url('public/media/' . $relativePath);
  }
}
