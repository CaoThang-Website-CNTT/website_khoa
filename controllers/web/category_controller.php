<?php

namespace App\Controllers;

require_once BASE_PATH . '/models/category.php';

use App\Core\Controller;
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

    $data = $this->_categoryService->getCategories($currentPage, 15);

    $this->render("admin/categories/index", [
      'data' => $data,
    ], layout: 'dashboard_layout');
  }

  public function create()
  {
    $categories = $this->_categoryService->getAllCategories();
    $this->render("admin/categories/create", [
      "categories" => $categories
    ], layout: 'dashboard_layout');
  }

  public function edit($id)
  {
    $category = $this->_categoryService->getCategoryById($id);

    if (!$category) {
      return $this->abort(404);
    }

    $categories = $this->_categoryService->getAllCategories();

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
      'slug' => ['nullable', 'max:255'],
      'description' => ['nullable'],
      'meta' => ['json'],
      'parent_id' => ['nullable'],
    ];

    if (!$validator->validate($data, $rules)) {
      $request->flashOldInputs();
      $request->flashErrors($validator->getErrors());
      return $this->redirect('admin/categories/create');
    }

    $newCategory = $this->_categoryService->createCategory($data);

    if ($newCategory) {
      $request->flash(
        'success',
        'Tạo danh mục thành công!',
        'Danh mục ' . $newCategory->name . ' đã được tạo.'
      );
    } else {
      $request->flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect('admin/categories/create');
  }

  public function update($id, Request $request)
  {
    $data = $request->all();

    $category = $this->_categoryService->getCategoryById($id);

    if (!$category) {
      return $this->abort(404);
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

    try {
      $isSuccess = $this->_categoryService->updateCategory($id, $data);
    } catch (\InvalidArgumentException $e) {
      $validator->addError('slug', 'Slug này đã tồn tại, vui lòng chọn slug khác.');
      $request->flashOldInputs();
      $request->flashErrors($validator->getErrors());
      return $this->redirect('admin/categories/' . $id);
    }

    if ($isSuccess) {
      $request->flash('success', 'Cập nhật danh mục thành công!');
    } else {
      $request->flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect('admin/categories/' . $id);
  }

  public function destroy($id, Request $request)
  {
    $isSuccess = $this->_categoryService->deleteCategory($id);

    if ($isSuccess) {
      $request->flash('success', 'Xoá danh mục thành công!');
    } else {
      $request->flash('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }

    return $this->redirect('admin/categories');
  }
}