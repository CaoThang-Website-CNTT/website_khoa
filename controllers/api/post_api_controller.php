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
    $sortBy = trim((string) $request->query('sort', $request->query('sort_by', 'published_at'))) ?: 'published_at';
    $sortBy = $sortBy === 'published_at' ? $sortBy : 'published_at';
    $order = strtolower(trim((string) $request->query('order', 'desc'))) === 'asc' ? 'asc' : 'desc';
    $featured = $request->query('featured');
    $featuredFilter = in_array((string) $featured, ['0', '1'], true) ? (string) $featured : null;

    try {
      return $this->json(
        $this->_postService->getPosts($page, $limit, false, [
          'search' => $search,
          'category' => $category,
          'sort' => $sortBy,
          'order' => $order,
          'is_featured' => $featuredFilter
        ]),
        200
      );
    } catch (Exception $e) {
      error_log('Lỗi truy vấn post: ' . $e->getMessage());
      return $this->json(null, 500, 'Không thể truy vấn post.');
    }
  }
}
