<?php

namespace App\Controllers;

require_once BASE_PATH . "/includes/core/controller.php";
require_once BASE_PATH . '/includes/core/request_validator.php';
require_once BASE_PATH . '/models/category.php';

use App\Core\Controller;
use App\Core\Page;
use App\Core\Request;
use App\Core\Validator;
use App\Services\CategoryService;

class CategoryController extends Controller
{
  private CategoryService $_categoryService;

  public function __construct(CategoryService $categoryService)
  {
    $this->_categoryService = $categoryService;
  }

  public function index(Request $request)
  {
    $currentPage = $request->query('page') ?? 1;

    $categories = $this->_categoryService->getAllCategories($currentPage);
    $total = $this->_categoryService->getTotalCategoriesCount();

    $page = new Page($total, 15, $currentPage);

    $this->render("admin/categories/index", [
      "categories" => $categories,
      "page" => $page
    ], layout: 'dashboard_layout');
  }

  public function create()
  {
    $categories = $this->_categoryService->getAllCategories();
    $this->render("admin/categories/create", [
      "categories" => $categories
    ], layout: 'dashboard_layout');
  }

  public function edit(string $id)
  {
    $category = $this->_categoryService->getById((int) $id);
    $categories = $this->_categoryService->getAllCategories();

    if (!$category) {
      die("Không tìm thấy danh mục với id: $id");
    }

    $this->render("admin/categories/edit", [
      "category" => $category,
      "categories" => $categories
    ], layout: 'dashboard_layout');
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
      $request->flashOldInputs();
      $request->flashErrors($validator->getErrors());
      return $this->redirect('admin/categories/create');
    }

    // Auto-generate slug nếu để trống
    if (empty($data['slug'])) {
      $data['slug'] = $this->generateSlug($data['name']);
    }

    if (!$this->_categoryService->isSlugUnique($data['slug'])) {
      $validator->addError('slug', 'Slug này đã tồn tại, vui lòng chọn slug khác.');
      $request->flashOldInputs();
      $request->flashErrors($validator->getErrors());
      return $this->redirect('admin/categories/create');
    }

    $newId = $this->_categoryService->create([
      'name' => $data['name'],
      'slug' => $data['slug'],
      'description' => $data['description'] ?? null,
      'parent_id' => !empty($data['parent_id']) ? (int) $data['parent_id'] : null,
    ]);

    if ($newId) {
      $request->flash('success', 'Tạo danh mục thành công!');
    } else {
      $request->flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect('admin/categories/create');
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
      $request->flashOldInputs();
      $request->flashErrors($validator->getErrors());
      return $this->redirect('admin/categories/' . $id);
    }

    if (empty($data['slug'])) {
      $data['slug'] = $this->generateSlug($data['name']);
    }

    if (!$this->_categoryService->isSlugUnique($data['slug'], (int) $id)) {
      $validator->addError('slug', 'Slug này đã tồn tại, vui lòng chọn slug khác.');
      $request->flashOldInputs();
      $request->flashErrors($validator->getErrors());
      return $this->redirect('admin/categories/' . $id);
    }

    $isSuccess = $this->_categoryService->update((int) $id, [
      'name' => $data['name'],
      'slug' => $data['slug'],
      'description' => $data['description'] ?? null,
      'parent_id' => !empty($data['parent_id']) ? (int) $data['parent_id'] : null,
    ]);

    if ($isSuccess) {
      $request->flash('success', 'Cập nhật danh mục thành công!');
    } else {
      $request->flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect('admin/categories/' . $id);
  }

  public function destroy(string $id, Request $request)
  {
    $isSuccess = $this->_categoryService->delete((int) $id);

    if ($isSuccess) {
      $request->flash('success', 'Xoá danh mục thành công!');
    } else {
      $request->flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect('admin/categories');
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
}