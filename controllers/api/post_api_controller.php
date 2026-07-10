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
      $pageable = $this->_postService->getPosts($page, $limit, false, $filters);

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
          'created_at' => $post->created_at ? date('d/m/Y H:i', strtotime($post->created_at)) : 'N/A',
        ], $pageable->getItems()),
        'total' => $pageable->getTotal(),
        'page' => $pageable->getCurrentPage(),
        'limit' => $pageable->getPerPage()
      ], 200);
    } catch (Exception $e) {
      error_log('Lỗi truy vấn post: ' . $e->getMessage());
      return $this->json(null, 500, 'Không thể truy vấn post.');
    }
  }
}
