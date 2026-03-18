<?php

require_once __DIR__ . '/../utils/request_validator.php';
require_once __DIR__ . '/../models/category.php';

use App\Core\Request;
use App\Models\Category;
use App\Utils\Validator;
use App\Services\CategoryRepositoryInterface;

class CategoryController
{
  private $_categoryService;

  public function __construct(CategoryRepositoryInterface $categoryService)
  {
    $this->_categoryService = $categoryService;
  }

  public function index()
  {
    $categories = $this->flattenTree($this->_categoryService->getAll());
    ob_start();
    require_once __DIR__ . '/../templates/pages/admin/category/index.php';
    $content = ob_get_clean();
    require_once __DIR__ . '/../templates/layouts/dashboard_layout.php';
  }

  public function create()
  {
    $categories = $this->flattenTree($this->_categoryService->getAll());
    ob_start();
    require_once __DIR__ . '/../templates/pages/admin/category/create.php';
    $content = ob_get_clean();
    require_once __DIR__ . '/../templates/layouts/dashboard_layout.php';
  }

  public function edit(string $id)
  {
    $category = $this->_categoryService->getById((int) $id);
    $categories = $this->flattenTree($this->_categoryService->getAll());

    if (!$category) {
      die("Không tìm thấy danh mục với id: $id");
    }

    ob_start();
    require_once __DIR__ . '/../templates/pages/admin/category/edit.php';
    $content = ob_get_clean();
    require_once __DIR__ . '/../templates/layouts/dashboard_layout.php';
  }

  public function store(Request $request)
  {
    $data = $request->all();

    $validator = new Validator();
    $rules = [
      'name' => ['required', 'max:255'],
      'slug' => ['max:255'],
      'description' => ['max:5000'],
      'parent_id' => [],
    ];

    if (!$validator->validate($data, $rules)) {
      return $this->redirectWithError($validator->getErrors(), url('admin/categories/create'));
    }

    // Auto-generate slug nếu để trống
    if (empty($data['slug'])) {
      $data['slug'] = $this->generateSlug($data['name']);
    }

    if (!$this->_categoryService->isSlugUnique($data['slug'])) {
      $validator->addError('slug', 'Slug này đã tồn tại, vui lòng chọn slug khác.');
      return $this->redirectWithError($validator->getErrors(), url('admin/categories/create'));
    }

    $newId = $this->_categoryService->create([
      'name' => $data['name'],
      'slug' => $data['slug'],
      'description' => $data['description'] ?? null,
      'parent_id' => !empty($data['parent_id']) ? (int) $data['parent_id'] : null,
    ]);

    if ($newId) {
      flash('success', 'Tạo danh mục thành công!');
    } else {
      flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    header('Location: ' . url('admin/categories/create'));
    exit;
  }

  public function update(string $id, Request $request)
  {
    $data = $request->all();

    $category = $this->_categoryService->getById((int) $id);

    if (!$category) {
      die("Không tìm thấy danh mục với id: $id");
    }

    $validator = new Validator();
    $rules = [
      'name' => ['required', 'max:255'],
      'slug' => ['max:255'],
      'description' => ['max:5000'],
      'parent_id' => [],
    ];

    if (!$validator->validate($data, $rules)) {
      return $this->redirectWithError($validator->getErrors(), url('admin/categories/' . $id . '/edit'));
    }

    if (empty($data['slug'])) {
      $data['slug'] = $this->generateSlug($data['name']);
    }

    if (!$this->_categoryService->isSlugUnique($data['slug'], (int) $id)) {
      $validator->addError('slug', 'Slug này đã tồn tại, vui lòng chọn slug khác.');
      return $this->redirectWithError($validator->getErrors(), url('admin/categories/' . $id . '/edit'));
    }

    $isSuccess = $this->_categoryService->update((int) $id, [
      'name' => $data['name'],
      'slug' => $data['slug'],
      'description' => $data['description'] ?? null,
      'parent_id' => !empty($data['parent_id']) ? (int) $data['parent_id'] : null,
    ]);

    if ($isSuccess) {
      flash('success', 'Cập nhật danh mục thành công!');
    } else {
      flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    header('Location: ' . url('admin/categories/' . $id));
    exit;
  }

  public function destroy(string $id)
  {
    $isSuccess = $this->_categoryService->delete((int) $id);

    if ($isSuccess) {
      flash('success', 'Xoá danh mục thành công!');
    } else {
      flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    header('Location: ' . url('admin/categories'));
    exit;
  }

  // ── Private helpers ───────────────────────────────────────────────────────

  private function redirectWithError(array $errors, string $redirectUrl): void
  {
    $_SESSION['errors'] = $errors;
    header('Location: ' . $redirectUrl);
    exit;
  }

  private function generateSlug(string $name): string
  {
    return strtolower(
      preg_replace(
        '/\s+/',
        '-',
        preg_replace(
          '/[^a-z0-9\s-]/',
          '',
          str_replace(
            'đ',
            'd',
            iconv(
              'UTF-8',
              'ASCII//TRANSLIT',
              mb_strtolower(trim($name))
            )
          )
        )
      )
    );
  }

  /**
   * Sắp xếp phẳng danh sách category theo thứ tự cha trước, con sau.
   *
   * @param Category[] $categories
   * @return Category[]
   */
  private function flattenTree(array $categories): array
  {
    $map = [];
    $children = [];
    $roots = [];

    foreach ($categories as $category) {
      $map[$category->id] = $category;
    }

    foreach ($categories as $category) {
      if ($category->parent_id === null) {
        $roots[] = $category;
      } else {
        $children[$category->parent_id][] = $category;
      }
    }

    $result = [];
    $walk = function (Category $node, int $depth) use (&$walk, &$result, $children) {
      $node->depth = $depth;
      $result[] = $node;
      foreach ($children[$node->id] ?? [] as $child) {
        $walk($child, $depth + 1);
      }
    };

    foreach ($roots as $root) {
      $walk($root, 0);
    }

    return $result;
  }
}