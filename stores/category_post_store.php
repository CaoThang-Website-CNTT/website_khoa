<?php

namespace App\Stores;

require_once BASE_PATH . '/includes/core/store.php';

use App\Core\Store;
use App\Core\Schema\QueryBuilder;
use App\Core\Schema\Compiler\MySQLCompiler;
use PDO;

interface ICategoryPostStore
{
  /** @return int[] */
  public function getCategoryIdsByPostId(int $postId): array;

  /** @return array<int, int[]> */
  public function getCategoryIdsByPostIds(array $postIds): array;

  /** @return int[] */
  public function getPostIdsByCategoryId(int $categoryId): array;

  public function syncPostCategories(int $postId, array $categoryIds): bool;
}

class CategoryPostStore extends Store implements ICategoryPostStore
{
  public function getCategoryIdsByPostId(int $postId): array
  {
    $query = (new QueryBuilder(new MySQLCompiler()))
      ->from('category_post')
      ->select('category_id')
      ->eq('post_id', $postId);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
  }

  public function getCategoryIdsByPostIds(array $postIds): array
  {
    $postIds = array_values(array_unique(array_map('intval', $postIds)));
    if (empty($postIds)) {
      return [];
    }

    $query = (new QueryBuilder(new MySQLCompiler()))
      ->from('category_post')
      ->select('post_id', 'category_id')
      ->in('post_id', $postIds);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    $map = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
      $postId = (int) $row['post_id'];
      $map[$postId] ??= [];
      $map[$postId][] = (int) $row['category_id'];
    }

    return $map;
  }

  public function getPostIdsByCategoryId(int $categoryId): array
  {
    $query = (new QueryBuilder(new MySQLCompiler()))
      ->from('category_post')
      ->select('post_id')
      ->eq('category_id', $categoryId);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
  }

  public function syncPostCategories(int $postId, array $categoryIds): bool
  {
    $newCategoryIds = array_values(array_unique(array_map('intval', $categoryIds)));
    $currentCategoryIds = $this->getCategoryIdsByPostId($postId);

    $toAdd = array_values(array_diff($newCategoryIds, $currentCategoryIds));
    $toRemove = array_values(array_diff($currentCategoryIds, $newCategoryIds));

    if (!empty($toRemove)) {
      $query = (new QueryBuilder(new MySQLCompiler()))
        ->from('category_post')
        ->delete()
        ->eq('post_id', $postId)
        ->in('category_id', $toRemove);

      $stmt = $this->db->prepare($query->toSql());
      if (!$stmt->execute($query->getBindings())) {
        throw new \RuntimeException('Không thể cập nhật post categories.');
      }
    }

    if (!empty($toAdd)) {
      $rows = array_map(
        fn(int $categoryId) => ['post_id' => $postId, 'category_id' => $categoryId],
        $toAdd
      );

      $query = (new QueryBuilder(new MySQLCompiler()))
        ->from('category_post')
        ->insert($rows);

      $stmt = $this->db->prepare($query->toSql());
      if (!$stmt->execute($query->getBindings())) {
        throw new \RuntimeException('Không thể cập nhật post categories.');
      }
    }

    return true;
  }
}
