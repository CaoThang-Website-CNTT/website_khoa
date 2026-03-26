<?php

namespace App\Services;

require_once BASE_PATH . '/stores/category_store.php';
require_once BASE_PATH . '/models/category.php';
require_once BASE_PATH . '/includes/helpers.php';
require_once BASE_PATH . '/includes/core/pageable.php';

use App\Stores\CategoryStore;
use App\Models\Category;
use App\Core\Pageable;

interface ICategoryService
{
  /** @return Category[] */
  public function getAllCategories(): array;
  /** @return Pageable */
  public function getCategories(int $page, int $limit = 15): Pageable;
  public function getCategoriesTree(): array;
  public function getCategoryById(int $id): ?Category;
  public function getCategoryBySlug(string $slug): ?Category;
  public function createCategory(array $data): int;
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

  public function createCategory(array $data): int
  {
    $slug = $data['slug'] ?? generateSlug($data['name']);

    if (!$this->_categoryStore->isSlugUnique($slug)) {
      throw new \InvalidArgumentException("Slug '$slug' đã tồn tại.");
    }

    $category = new Category();
    $category->name = $data['name'];
    $category->slug = $slug;
    $category->type = $data['type'] ?? 'custom';
    $category->description = $data['description'] ?? null;
    $category->parent_id = isset($data['parent_id']) ? (int) $data['parent_id'] : null;
    $category->meta = isset($data['meta'])
      ? (is_string($data['meta']) ? $data['meta'] : json_encode($data['meta']))
      : null;

    return $this->_categoryStore->create($category);
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