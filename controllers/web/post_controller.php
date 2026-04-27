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

  public function create(): void
  {
    $this->render('admin/posts/create', [
      'authors' => [],
      'categories' => $this->_categoryService->getAllCategories()
    ], layout: 'canva_layout');
  }

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

  public function show(Request $request, int $post_id)
  {
    try {
      $post = $this->_postService->get($post_id);

      $this->render('admin/posts/show', [
        'post' => $post,
        'categories' => $this->_categoryService->getAllCategories()
      ], layout: 'canva_layout');
    } catch (\RuntimeException $e) {
      $request->session()->flashNotify(
        'error',
        'Không tìm thấy bài viết',
        'Bài viết bạn yêu cầu không tồn tại hoặc đã bị xoá.'
      );
      return $this->redirect('admin/posts');
    }
  }

  public function update(Request $request, int $post_id)
  {
    $jsonString = $request->input('editor_data');
    $editorData = json_decode($jsonString, true);

    if (empty($editorData)) {
      $request->session()->flashNotify(
        'error',
        'Dữ liệu trống',
        'Vui lòng nhập ít nhất một trường để cập nhật.'
      );
      return $this->redirect("admin/posts/{$post_id}/edit");
    }

    // Validate status nếu có gửi lên
    $allowedStatuses = ['draft', 'published', 'deleted'];
    if (isset($editorData['meta']['status']) && !in_array($editorData['meta']['status'], $allowedStatuses, true)) {
      $request->flashOldInputs();
      $request->session()->flashNotify(
        'error',
        'Trạng thái không hợp lệ',
        'Các giá trị cho phép: ' . implode(', ', $allowedStatuses)
      );
      return $this->redirect("admin/posts/{$post_id}/edit");
    }

    try {
      $post = $this->_postService->update($post_id, $editorData);

      $request->session()->flashNotify(
        'success',
        'Cập nhật thành công',
        "Bài viết '" . htmlspecialchars($post->title) . "' đã được cập nhật."
      );

      return $this->redirect('admin/posts');
    } catch (\InvalidArgumentException $e) {
      $request->flashOldInputs();
      $request->session()->flashNotify(
        'error',
        'Dữ liệu không hợp lệ',
        $e->getMessage()
      );
      return $this->redirect("admin/posts/{$post_id}/edit");
    } catch (\RuntimeException $e) {
      $request->flashOldInputs();
      $request->session()->flashNotify(
        'error',
        'Lỗi hệ thống',
        'Không thể cập nhật bài viết. Vui lòng thử lại sau.'
      );
      return $this->redirect("admin/posts/{$post_id}/edit");
    }
  }

  public function destroy(Request $request, int $post_id)
  {
    try {
      $this->_postService->delete($post_id);

      $request->session()->flashNotify(
        'success',
        'Đã xoá bài viết',
        "Bài viết #" . $post_id . " đã được xoá."
      );

    } catch (\RuntimeException $e) {
      $request->session()->flashNotify(
        'error',
        'Không thể xoá',
        'Có lỗi xảy ra khi xoá bài viết. Vui lòng thử lại.'
      );
    }

    return $this->redirect("");
  }
}