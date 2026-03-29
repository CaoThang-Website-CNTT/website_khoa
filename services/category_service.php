<?php

namespace App\Services;

require_once BASE_PATH . '/stores/category_store.php';
require_once BASE_PATH . '/models/category.php';
require_once BASE_PATH . '/includes/helpers.php';
require_once BASE_PATH . '/includes/core/pageable.php';

use App\Stores\CategoryStore;
use App\Models\Category;
use App\Core\Pageable;
use Database;

interface ICategoryService
{
  /** @return Category[] */
  public function getAllCategories(): array;
  /** @return Pageable */
  public function getCategories(int $page, int $limit = 15): Pageable;
  public function getCategoriesTree(): array;
  public function getCategoryById(int $id): ?Category;
  public function getCategoryBySlug(string $slug): ?Category;
  public function createCategory(array $data): ?Category;
  public function updateCategory(int $id, array $data): bool;
  public function deleteCategory(int $id): bool;
  public function isSlugUnique(string $slug, ?int $excludeId = null): bool;
}

class CategoryService implements ICategoryService
{
  private CategoryStore $_categoryStore;

  public function __construct(CategoryStore $categoryStore)
  {
    $this->_categoryStore = $categoryStore;
  }

  /** @return Category[] */
  public function getAllCategories(): array
  {
    return $this->_categoryStore->getAll();
  }

  public function getCategories(int $page, int $limit = 15): Pageable
  {
    $menus = $this->_categoryStore->getPaginated($page, $limit);
    $total = $this->_categoryStore->getTotalCount();
    return new Pageable($menus, $total, $limit, $page);
  }

  /** @return Category[] */
  public function getCategoriesTree(): array
  {
    $all = $this->_categoryStore->getAll();

    // Đánh index theo id để gán con với độ phức tạp O(1)
    $map = [];
    foreach ($all as $cat) {
      $cat->children = [];
      $map[$cat->id] = $cat;
    }

    $roots = [];
    foreach ($map as $cat) {
      if ($cat->parent_id !== null && isset($map[$cat->parent_id])) {
        $map[$cat->parent_id]->children[] = $cat;
      } else {
        $roots[] = $cat;
      }
    }

    return $roots;
  }

  public function getCategoryById(int $id): ?Category
  {
    return $this->_categoryStore->getById($id);
  }

  public function getCategoryBySlug(string $slug): ?Category
  {
    return $this->_categoryStore->getBySlug($slug);
  }

  public function createCategory(array $data): ?Category
  {
    return Database::getInstance()->transaction(function () use ($data) {
      // Check slug
      // Nếu slug tồn tại, kiểm tra unique nó
      // Còn không thì tạo từ tên danh mục
      $slug = $data['slug'] ?? generateSlug($data['name']);
      if (!$this->_categoryStore->isSlugUnique($slug)) {
        throw new \InvalidArgumentException("Slug '$slug' đã tồn tại.");
      }

      // 'name' => ['required', 'max:255'],
      // 'slug' => ['nullable', 'max:255'],
      // 'description' => ['nullable'],
      // 'meta' => ['json'],
      // 'parent_id' => ['nullable'],
      $category = new Category(
        name: $data['name'],
        slug: $slug,
        description: $data['description'] ?? null,
        parent_id: (!empty($data['parent_id'])) ? (int) $data['parent_id'] : null,
        meta: !empty($data['meta'])
        ? (is_array($data['meta']) ? json_encode($data['meta']) : $data['meta'])
        : null
      );
      print_r($data);

      return $this->_categoryStore->create($category);
    });
  }

  public function updateCategory(int $id, array $data): bool
  {
    $category = $this->_categoryStore->getById($id);
    if ($category === null) {
      return false;
    }

    if (isset($data['slug']) && $data['slug'] !== $category->slug) {
      if (!$this->_categoryStore->isSlugUnique($data['slug'], $id)) {
        throw new \InvalidArgumentException("Slug '{$data['slug']}' đã tồn tại.");
      }
      $category->slug = $data['slug'];
    }

    $category->name = $data['name'] ?? $category->name;
    $category->type = $data['type'] ?? $category->type;
    $category->description = $data['description'] ?? $category->description;
    $category->parent_id = array_key_exists('parent_id', $data)
      ? (isset($data['parent_id']) ? (int) $data['parent_id'] : null)
      : $category->parent_id;

    if (array_key_exists('meta', $data)) {
      $category->meta = is_string($data['meta'])
        ? $data['meta']
        : json_encode($data['meta']);
    }

    return $this->_categoryStore->update($category);
  }

  public function deleteCategory(int $id): bool
  {
    return $this->_categoryStore->softDelete($id);
  }

  public function isSlugUnique(string $slug, ?int $excludeId = null): bool
  {
    return $this->_categoryStore->isSlugUnique($slug, $excludeId);
  }

}