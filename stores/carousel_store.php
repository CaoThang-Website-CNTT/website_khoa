<?php
namespace App\Stores;

require_once BASE_PATH . '/models/carousel.php';
require_once BASE_PATH . '/models/carousel_slide.php';
require_once BASE_PATH . '/includes/core/store.php';

use App\Core\Store;
use App\Models\Carousel;
use App\Models\CarouselSlide;
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
  public function reorderSlides(int $moveId, string $direction): bool;
}
class CarouselStore extends Store implements ICarouselStore
{
  /** @return Carousel[] */
  public function getAll(): array
  {
    $stmt = $this->db->prepare("SELECT * FROM `carousels` WHERE `deleted_at` IS NULL ORDER BY `id` ASC");
    $stmt->execute();
    return array_map(fn($row) => Carousel::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }
  /** @return Carousel[] */
  public function getPaginated(int $page, int $limit = 15): array
  {
    $offset = (max(1, $page) - 1) * $limit;
    $stmt = $this->db->prepare("SELECT * FROM `carousels` WHERE `deleted_at` IS NULL ORDER BY `id` ASC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return array_map(fn($row) => Carousel::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }
  public function getById(int $id): ?Carousel
  {
    $stmt = $this->db->prepare("SELECT * FROM `carousels` WHERE `id` = :id AND `deleted_at` IS NULL");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? Carousel::fromArray($row) : null;
  }
  public function getBySlug(string $slug): ?Carousel
  {
    $stmt = $this->db->prepare("SELECT * FROM `carousels` WHERE `slug` = :slug AND `deleted_at` IS NULL");
    $stmt->execute([':slug' => $slug]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? Carousel::fromArray($row) : null;
  }
  public function create(array $data): int
  {
    $stmt = $this->db->prepare("INSERT INTO `carousels` (`name`, `slug`, `is_active`) VALUES (:name, :slug, :is_active)");
    $stmt->execute([':name' => $data['name'], ':slug' => $data['slug'], ':is_active' => $data['is_active'] ?? 1]);
    return (int) $this->db->lastInsertId();
  }
  public function update(int $id, array $data): bool
  {
    $stmt = $this->db->prepare("UPDATE `carousels` SET `name` = :name, `slug` = :slug, `is_active` = :is_active, `updated_at` = NOW() WHERE `id` = :id AND `deleted_at` IS NULL");
    return $stmt->execute([':name' => $data['name'], ':slug' => $data['slug'], ':is_active' => $data['is_active'] ?? 1, ':id' => $id]);
  }
  public function delete(int $id): bool
  {
    $this->db->beginTransaction();
    try {
      $stmtSlides = $this->db->prepare("UPDATE `carousel_slides` SET `deleted_at` = NOW() WHERE `carousel_id` = :carousel_id AND `deleted_at` IS NULL");
      $stmtSlides->execute([':carousel_id' => $id]);
      $stmtCarousel = $this->db->prepare("UPDATE `carousels` SET `deleted_at` = NOW() WHERE `id` = :id AND `deleted_at` IS NULL");
      $stmtCarousel->execute([':id' => $id]);
      $this->db->commit();
      return true;
    } catch (\Exception $e) {
      $this->db->rollBack();
      return false;
    }
  }
  public function isSlugUnique(string $slug, ?int $excludeId = null): bool
  {
    $sql = "SELECT COUNT(*) FROM `carousels` WHERE `slug` = :slug AND `deleted_at` IS NULL";
    $params = [':slug' => $slug];
    if ($excludeId) {
      $sql .= " AND `id` != :exclude_id";
      $params[':exclude_id'] = $excludeId;
    }
    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn() == 0;
  }
  /** @return CarouselSlide[] */
  public function getSlides(int $carouselId): array
  {
    $stmt = $this->db->prepare("SELECT * FROM `carousel_slides` WHERE `carousel_id` = :carousel_id AND `deleted_at` IS NULL ORDER BY `sort_order` ASC");
    $stmt->execute([':carousel_id' => $carouselId]);
    return array_map(fn($row) => CarouselSlide::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }
  public function getSlideById(int $id): ?CarouselSlide
  {
    $stmt = $this->db->prepare("SELECT * FROM `carousel_slides` WHERE `id` = :id AND `deleted_at` IS NULL");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? CarouselSlide::fromArray($row) : null;
  }
  public function createSlide(array $data): int
  {
    $stmt = $this->db->prepare("INSERT INTO `carousel_slides` (`carousel_id`, `title`, `title_highlight`, `description`, `image_path`, `image_alt`, `cta_label`, `cta_url`, `cta_variant`, `custom_html`, `use_custom_html`, `sort_order`, `is_active`) VALUES (:carousel_id, :title, :title_highlight, :description, :image_path, :image_alt, :cta_label, :cta_url, :cta_variant, :custom_html, :use_custom_html, :sort_order, :is_active)");
    $stmt->execute([
      ':carousel_id' => $data['carousel_id'],
      ':title' => $data['title'],
      ':title_highlight' => $data['title_highlight'] ?? null,
      ':description' => $data['description'] ?? null,
      ':image_path' => $data['image_path'],
      ':image_alt' => $data['image_alt'] ?? '',
      ':cta_label' => $data['cta_label'] ?? null,
      ':cta_url' => $data['cta_url'] ?? null,
      ':cta_variant' => $data['cta_variant'] ?? 'primary',
      ':custom_html' => $data['custom_html'] ?? null,
      ':use_custom_html' => $data['use_custom_html'] ?? 0,
      ':sort_order' => $data['sort_order'] ?? 0,
      ':is_active' => $data['is_active'] ?? 1,
    ]);
    return (int) $this->db->lastInsertId();
  }
  public function updateSlide(int $id, array $data): bool
  {
    $stmt = $this->db->prepare("UPDATE `carousel_slides` SET `title` = :title, `title_highlight` = :title_highlight, `description` = :description, `image_path` = :image_path, `image_alt` = :image_alt, `cta_label` = :cta_label, `cta_url` = :cta_url, `cta_variant` = :cta_variant, `custom_html` = :custom_html, `use_custom_html` = :use_custom_html, `sort_order` = :sort_order, `is_active` = :is_active, `updated_at` = NOW() WHERE `id` = :id AND `deleted_at` IS NULL");
    return $stmt->execute([
      ':title' => $data['title'],
      ':title_highlight' => $data['title_highlight'] ?? null,
      ':description' => $data['description'] ?? null,
      ':image_path' => $data['image_path'],
      ':image_alt' => $data['image_alt'] ?? '',
      ':cta_label' => $data['cta_label'] ?? null,
      ':cta_url' => $data['cta_url'] ?? null,
      ':cta_variant' => $data['cta_variant'] ?? 'primary',
      ':custom_html' => $data['custom_html'] ?? null,
      ':use_custom_html' => $data['use_custom_html'] ?? 0,
      ':sort_order' => $data['sort_order'] ?? 0,
      ':is_active' => $data['is_active'] ?? 1,
      ':id' => $id,
    ]);
  }
  public function deleteSlide(int $id): bool
  {
    $stmt = $this->db->prepare("UPDATE `carousel_slides` SET `deleted_at` = NOW() WHERE `id` = :id AND `deleted_at` IS NULL");
    return $stmt->execute([':id' => $id]);
  }
  public function getTotalCarouselsCount(): int
  {
    $stmt = $this->db->query("SELECT COUNT(*) FROM `carousels` WHERE `deleted_at` IS NULL");
    return (int) $stmt->fetchColumn();
  }
  public function reorderSlides(int $moveId, string $direction): bool
  {
    $stmt = $this->db->prepare("SELECT `id`, `sort_order` FROM `carousel_slides` WHERE `id` = :id AND `deleted_at` IS NULL");
    $stmt->execute([':id' => $moveId]);
    $target = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$target)
      return false;
    if ($direction === 'up') {
      $stmt = $this->db->prepare("SELECT `id`, `sort_order` FROM `carousel_slides` WHERE `sort_order` < :sort_order AND `deleted_at` IS NULL ORDER BY `sort_order` DESC LIMIT 1");
    } else {
      $stmt = $this->db->prepare("SELECT `id`, `sort_order` FROM `carousel_slides` WHERE `sort_order` > :sort_order AND `deleted_at` IS NULL ORDER BY `sort_order` ASC LIMIT 1");
    }
    $stmt->execute([':sort_order' => $target['sort_order']]);
    $neighbour = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$neighbour)
      return false;
    $this->db->beginTransaction();
    try {
      $swap = $this->db->prepare("UPDATE `carousel_slides` SET `sort_order` = :sort_order, `updated_at` = NOW() WHERE `id` = :id AND `deleted_at` IS NULL");
      $swap->execute([':sort_order' => $neighbour['sort_order'], ':id' => $target['id']]);
      $swap->execute([':sort_order' => $target['sort_order'], ':id' => $neighbour['id']]);
      $this->db->commit();
      return true;
    } catch (\Exception $e) {
      $this->db->rollBack();
      return false;
    }
  }
}