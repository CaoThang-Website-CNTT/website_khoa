<?php
namespace App\Stores;

require_once BASE_PATH . '/models/carousel.php';
require_once BASE_PATH . '/models/carousel_slide.php';
require_once BASE_PATH . '/includes/core/store.php';

use App\Core\Store;
use App\Models\Carousel;
use App\Models\CarouselSlide;
use App\Core\Schema\QueryBuilder;
use App\Core\Schema\Compiler\MySQLCompiler;
use PDO;

interface ICarouselStore
{
  /** @return Carousel[] */
  public function getAll(): array;
  /** @return Carousel[] */
  public function getPaginated(int $page, int $limit = 15): array;
  public function getById(int $id): ?Carousel;
  public function getBySlug(string $slug): ?Carousel;
  public function create(array $data): int;
  public function update(int $id, array $data): bool;
  public function delete(int $id): bool;
  public function isSlugUnique(string $slug, ?int $excludeId = null): bool;
  /** @return CarouselSlide[] */
  public function getSlides(int $carouselId): array;
  public function getSlideById(int $id): ?CarouselSlide;
  public function createSlide(array $data): int;
  public function updateSlide(int $id, array $data): bool;
  public function deleteSlide(int $id): bool;
  public function getTotalCarouselsCount(): int;
  public function sortSlides(array $ids): bool;
}
class CarouselStore extends Store implements ICarouselStore
{
  /** @return Carousel[] */
  public function getAll(): array
  {
    $query = (new QueryBuilder(new MySQLCompiler()))
      ->from('carousels')
      ->is('deleted_at', null)
      ->order('id', ['ascending' => true]);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return array_map(fn($row) => Carousel::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }
  /** @return Carousel[] */
  public function getPaginated(int $page, int $limit = 15): array
  {
    $offset = (max(1, $page) - 1) * $limit;
    $query = (new QueryBuilder(new MySQLCompiler()))
      ->from('carousels')
      ->is('deleted_at', null)
      ->order('id', ['ascending' => true])
      ->limit($limit)
      ->range($offset, $offset + $limit - 1);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return array_map(fn($row) => Carousel::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }
  public function getById(int $id): ?Carousel
  {
    $query = (new QueryBuilder(new MySQLCompiler()))
      ->from('carousels')
      ->eq('id', $id)
      ->is('deleted_at', null)
      ->limit(1);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? Carousel::fromArray($row) : null;
  }
  public function getBySlug(string $slug): ?Carousel
  {
    $query = (new QueryBuilder(new MySQLCompiler()))
      ->from('carousels')
      ->eq('slug', $slug)
      ->is('deleted_at', null)
      ->limit(1);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? Carousel::fromArray($row) : null;
  }
  public function create(array $data): int
  {
    $query = (new QueryBuilder(new MySQLCompiler()))
      ->from('carousels')
      ->insert([
        'name' => $data['name'],
        'slug' => $data['slug'],
        'is_active' => $data['is_active'] ?? 1,
      ]);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return (int) $this->db->lastInsertId();
  }
  public function update(int $id, array $data): bool
  {
    $query = (new QueryBuilder(new MySQLCompiler()))
      ->from('carousels')
      ->update([
        'name' => $data['name'],
        'slug' => $data['slug'],
        'is_active' => $data['is_active'] ?? 1,
        'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
      ])
      ->eq('id', $id)
      ->is('deleted_at', null);

    $stmt = $this->db->prepare($query->toSql());
    return $stmt->execute($query->getBindings());
  }
  public function delete(int $id): bool
  {
    $this->db->beginTransaction();
    try {
      $now = (new \DateTime())->format('Y-m-d H:i:s');

      $slidesQuery = (new QueryBuilder(new MySQLCompiler()))
        ->from('carousel_slides')
        ->update(['deleted_at' => $now])
        ->eq('carousel_id', $id)
        ->is('deleted_at', null);

      $stmtSlides = $this->db->prepare($slidesQuery->toSql());
      $stmtSlides->execute($slidesQuery->getBindings());

      $carouselQuery = (new QueryBuilder(new MySQLCompiler()))
        ->from('carousels')
        ->update(['deleted_at' => $now])
        ->eq('id', $id)
        ->is('deleted_at', null);

      $stmtCarousel = $this->db->prepare($carouselQuery->toSql());
      $stmtCarousel->execute($carouselQuery->getBindings());

      $this->db->commit();
      return true;
    } catch (\Exception $e) {
      $this->db->rollBack();
      return false;
    }
  }
  public function isSlugUnique(string $slug, ?int $excludeId = null): bool
  {
    $query = (new QueryBuilder(new MySQLCompiler()))
      ->from('carousels')
      ->select('COUNT(*)')
      ->eq('slug', $slug)
      ->is('deleted_at', null);

    if ($excludeId) {
      $query->neq('id', $excludeId);
    }

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    return $stmt->fetchColumn() == 0;
  }
  /** @return CarouselSlide[] */
  public function getSlides(int $carouselId): array
  {
    $query = (new QueryBuilder(new MySQLCompiler()))
      ->from('carousel_slides')
      ->eq('carousel_id', $carouselId)
      ->is('deleted_at', null)
      ->order('sort_order', ['ascending' => true]);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return array_map(fn($row) => CarouselSlide::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }
  public function getSlideById(int $id): ?CarouselSlide
  {
    $query = (new QueryBuilder(new MySQLCompiler()))
      ->from('carousel_slides')
      ->eq('id', $id)
      ->is('deleted_at', null)
      ->limit(1);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? CarouselSlide::fromArray($row) : null;
  }
  public function createSlide(array $data): int
  {
    $query = (new QueryBuilder(new MySQLCompiler()))
      ->from('carousel_slides')
      ->insert([
        'carousel_id' => $data['carousel_id'],
        'title' => $data['title'],
        'title_highlight' => $data['title_highlight'] ?? null,
        'description' => $data['description'] ?? null,
        'image_path' => $data['image_path'],
        'image_alt' => $data['image_alt'] ?? '',
        'cta_label' => $data['cta_label'] ?? null,
        'cta_url' => $data['cta_url'] ?? null,
        'cta_variant' => $data['cta_variant'] ?? 'primary',
        'custom_html' => $data['custom_html'] ?? null,
        'use_custom_html' => $data['use_custom_html'] ?? 0,
        'sort_order' => $data['sort_order'] ?? 0,
        'is_active' => $data['is_active'] ?? 1,
      ]);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return (int) $this->db->lastInsertId();
  }
  public function updateSlide(int $id, array $data): bool
  {
    $query = (new QueryBuilder(new MySQLCompiler()))
      ->from('carousel_slides')
      ->update([
        'title' => $data['title'],
        'title_highlight' => $data['title_highlight'] ?? null,
        'description' => $data['description'] ?? null,
        'image_path' => $data['image_path'],
        'image_alt' => $data['image_alt'] ?? '',
        'cta_label' => $data['cta_label'] ?? null,
        'cta_url' => $data['cta_url'] ?? null,
        'cta_variant' => $data['cta_variant'] ?? 'primary',
        'custom_html' => $data['custom_html'] ?? null,
        'use_custom_html' => $data['use_custom_html'] ?? 0,
        'sort_order' => $data['sort_order'] ?? 0,
        'is_active' => $data['is_active'] ?? 1,
        'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
      ])
      ->eq('id', $id)
      ->is('deleted_at', null);

    $stmt = $this->db->prepare($query->toSql());
    return $stmt->execute($query->getBindings());
  }
  public function deleteSlide(int $id): bool
  {
    $query = (new QueryBuilder(new MySQLCompiler()))
      ->from('carousel_slides')
      ->update(['deleted_at' => (new \DateTime())->format('Y-m-d H:i:s')])
      ->eq('id', $id)
      ->is('deleted_at', null);

    $stmt = $this->db->prepare($query->toSql());
    return $stmt->execute($query->getBindings());
  }
  public function getTotalCarouselsCount(): int
  {
    $query = (new QueryBuilder(new MySQLCompiler()))
      ->from('carousels')
      ->select('COUNT(*)')
      ->is('deleted_at', null);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    return (int) $stmt->fetchColumn();
  }
  public function sortSlides(array $ids): bool
  {
    if (empty($ids)) {
      return false;
    }

    $this->db->beginTransaction();
    try {
      $now = (new \DateTime())->format('Y-m-d H:i:s');
      foreach ($ids as $order => $id) {
        $query = (new QueryBuilder(new MySQLCompiler()))
          ->from('carousel_slides')
          ->update([
            'sort_order' => $order,
            'updated_at' => $now
          ])
          ->eq('id', (int)$id)
          ->is('deleted_at', null);
        $stmt = $this->db->prepare($query->toSql());
        $stmt->execute($query->getBindings());
      }
      $this->db->commit();
      return true;
    } catch (\Exception $e) {
      $this->db->rollBack();
      return false;
    }
  }
}