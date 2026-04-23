<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\AccountService;
use App\Services\CategoryService;
use App\Services\PostService;

class PostController extends Controller
{
  private PostService $_postService;
  private CategoryService $_categoryService;

  public function __construct(
    PostService $postService,
    CategoryService $categoryService,
  ) {
    $this->_postService = $postService;
    $this->_categoryService = $categoryService;
  }

  /**
   * Render form tạo bài viết mới (page).
   */
  public function create(): void
  {
    $this->render('admin/posts/create', [
      'authors' => [],
      'categories' => $this->_categoryService->getAllCategories()
    ], layout: 'canva_layout');
  }

  /**
   * Listing — trả về JSON danh sách bài viết (chỉ metadata, không content).
   */
  public function index(Request $request): mixed
  {
    $limit = max(1, min(100, (int) $request->input('limit', 20)));
    $offset = max(0, (int) $request->input('offset', 0));

    $posts = $this->_postService->list(['limit' => $limit, 'offset' => $offset]);

    return $this->json(
      data: array_map(static fn($p) => $p->toArray(), $posts),
      message: 'OK',
    );
  }

  /**
   * Tạo bài viết mới.
   * Body: JSON với cấu trúc { meta: {...}, blocks: [...] }
   */
  public function store(Request $request)
  {
    $jsonString = $request->input('editor_data');
    $editorData = json_decode($jsonString, true);

    // Validate cấu trúc tối thiểu tại controller — chi tiết validation ở service
    if (empty($editorData['meta']['title'])) {
      return $this->json(data: null, message: 'Tiêu đề bài viết là bắt buộc.', status: 422);
    }

    if (!isset($editorData['blocks']) || !is_array($editorData['blocks'])) {
      return $this->json(data: null, message: 'blocks phải là một mảng.', status: 422);
    }

    try {
      $post = $this->_postService->create($editorData);
      $request->session()->flashNotify(
        'success',
        'Tạo mới bài viết thành công!',
        "Bài viết có tiêu đề '" . $post->title . "' đã được tạo."
      );
    } catch (\InvalidArgumentException $e) {
      $request->flashOldInputs();
      $request->session()->flashNotify(
        'error',
        'Không thể thêm bài viết',
        $e->getMessage()
      );
    } catch (\RuntimeException $e) {
      $request->flashOldInputs();
      $request->session()->flashNotify(
        'error',
        'Không thể thêm bài viết',
        $e->getMessage()
      );
    }

    return $this->redirect('admin/posts/create');
  }

  /**
   * Chi tiết một bài viết (bao gồm content_json).
   */
  public function show(Request $request, int $post_id): mixed
  {
    try {
      $post = $this->_postService->get($post_id);
      return $this->json(data: $post->toArray(), message: 'OK');
    } catch (\RuntimeException $e) {
      return $this->json(data: null, message: $e->getMessage(), status: 404);
    }
  }

  /**
   * Cập nhật nội dung và/hoặc trạng thái.
   * Body: JSON với cấu trúc { meta?: {...}, blocks?: [...] }
   * Chỉ các field có mặt trong payload mới được ghi đè.
   */
  public function update(Request $request, int $post_id): mixed
  {
    $payload = $request->json();

    if (empty($payload)) {
      return $this->json(data: null, message: 'Payload không được rỗng.', status: 422);
    }

    // Validate status nếu có gửi lên
    $allowedStatuses = ['draft', 'published', 'deleted'];
    if (isset($payload['meta']['status']) && !in_array($payload['meta']['status'], $allowedStatuses, true)) {
      return $this->json(
        data: null,
        message: 'status không hợp lệ. Các giá trị cho phép: ' . implode(', ', $allowedStatuses),
        status: 422,
      );
    }

    try {
      $post = $this->_postService->update($post_id, $payload);
      return $this->json(data: $post->toArray(), message: 'Cập nhật bài viết thành công.');
    } catch (\InvalidArgumentException $e) {
      return $this->json(data: null, message: $e->getMessage(), status: 422);
    } catch (\RuntimeException $e) {
      return $this->json(data: null, message: $e->getMessage(), status: 404);
    }
  }

  /**
   * Soft delete — ghi deleted_at, không xoá record vật lý.
   */
  public function destroy(Request $request, int $post_id): mixed
  {
    try {
      $this->_postService->delete($post_id);
      return $this->json(data: null, message: 'Đã xoá bài viết thành công.');
    } catch (\RuntimeException $e) {
      return $this->json(data: null, message: $e->getMessage(), status: 404);
    }
  }
}