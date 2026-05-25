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
  private AccountService $_accountService;
  private CategoryService $_categoryService;

  public function __construct(
    PostService $postService,
    AccountService $accountService,
    CategoryService $categoryService,
  ) {
    $this->_postService = $postService;
    $this->_accountService = $accountService;
    $this->_categoryService = $categoryService;
  }

  public function create()
  {
    return $this->render('admin/posts/create', [
      'authors' => $this->_accountService->getAllAdmins(),
      'categories' => $this->_categoryService->getAllCategories()
    ], layout: 'canva_layout');
  }

  public function index(Request $request)
  {
    $currentPage = (int)$request->query('page', 1);
    $limit = (int)$request->query('limit', 15);

    $data = $this->_postService->getPosts($currentPage, $limit);

    return $this->render('admin/posts/index', [
      'data' => $data,
    ], layout: 'dashboard_layout');
  }

  public function store(Request $request)
  {
    $validated = $this->validate($request, [
      'editor_data' => ['required', 'json']
    ]);

    $editorData = json_decode($validated['editor_data'], true);

    // Kiểm tra tiêu đề trong JSON meta
    if (empty($editorData['meta']['title'])) {
      $request->session()->flashErrors(['editor_data' => ['Tiêu đề bài viết trong trình chỉnh sửa không được để trống.']]);
      $request->session()->flashNotify('error', 'Lỗi dữ liệu', 'Tiêu đề bài viết là bắt buộc.');
      $request->flashOldInputs(); // Giữ lại dữ liệu đã nhập
      return $this->redirect('admin/posts/create');
    }

    // Kiểm tra cấu trúc blocks
    if (!isset($editorData['blocks']) || !is_array($editorData['blocks'])) {
      $request->session()->flashErrors(['editor_data' => ['Cấu trúc nội dung bài viết không hợp lệ.']]);
      $request->session()->flashNotify('error', 'Lỗi dữ liệu', 'Nội dung bài viết (blocks) phải là một mảng.');
      $request->flashOldInputs();
      return $this->redirect('admin/posts/create');
    }

    try {
      $post = $this->_postService->create($editorData);
      $request->session()->flashNotify(
        'success',
        'Tạo mới bài viết thành công!',
        "Bài viết có tiêu đề '" . $post->title . "' đã được tạo."
      );
    } catch (\InvalidArgumentException | \RuntimeException $e) {
      $request->flashOldInputs();
      $request->session()->flashNotify(
        'error',
        'Lỗi tạo',
        $e->getMessage()
      );
    }

    return $this->redirect('admin/posts/create');
  }

  public function show(Request $request, int $post_id)
  {
    try {
      $post = $this->_postService->getPost($post_id);

      $this->render('admin/posts/edit', [
        'post' => $post,
        'authors' => $this->_accountService->getAllAdmins(),
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
    $validated = $this->validate($request, [
      'editor_data' => ['required', 'json']
    ]);

    $editorData = json_decode($validated['editor_data'], true);

    // Kiểm tra Tiêu đề
    if (empty($editorData['meta']['title'])) {
      $request->session()->flashErrors(['editor_data' => ['Tiêu đề không được để trống khi cập nhật.']]);
      $request->session()->flashNotify('error', 'Cập nhật thất bại', 'Tiêu đề bài viết là bắt buộc.');
      $request->flashOldInputs();
      return $this->redirect("admin/posts/{$post_id}");
    }

    // Kiểm tra cấu trúc Blocks
    if (!isset($editorData['blocks']) || !is_array($editorData['blocks'])) {
      $request->session()->flashErrors(['editor_data' => ['Dữ liệu nội dung không hợp lệ.']]);
      $request->session()->flashNotify('error', 'Cập nhật thất bại', 'Cấu trúc nội dung (blocks) không đúng định dạng.');
      $request->flashOldInputs();
      return $this->redirect("admin/posts/{$post_id}");
    }

    try {
      $post = $this->_postService->update($post_id, $editorData);

      $request->session()->flashNotify(
        'success',
        'Cập nhật thành công',
        "Bài viết '" . htmlspecialchars($post->title) . "' đã được cập nhật."
      );

      return $this->redirect("admin/posts/{$post_id}");
    } catch (\InvalidArgumentException | \RuntimeException $e) {
      $request->flashOldInputs();
      $request->session()->flashNotify(
        'error',
        'Lỗi cập nhật',
        $e->getMessage()
      );
      return $this->redirect("admin/posts/{$post_id}");
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