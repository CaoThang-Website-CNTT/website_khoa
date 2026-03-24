<?php

namespace App\Services;

require_once BASE_PATH . '/models/Carousel.php';
require_once BASE_PATH . '/models/CarouselSlide.php';
require_once BASE_PATH . '/db/database.php';

use App\Models\Carousel;
use App\Models\CarouselSlide;
use Database;
use PDO;

// ============================================================================
// Interface
// ============================================================================
interface ICarouselRepository
{
  // --- Carousel ---

  /** @return Carousel[] */
  public function getAll(): array;

  public function getById(int $id): ?Carousel;
  public function getBySlug(string $slug): ?Carousel;

  public function create(array $data): int;
  public function update(int $id, array $data): bool;
  public function delete(int $id): bool;

  public function isSlugUnique(string $slug, ?int $excludeId = null): bool;

  // --- Carousel Slides ---

  /** @return CarouselSlide[] */
  public function getSlides(int $carouselId): array;

  public function getSlideById(int $id): ?CarouselSlide;

  public function createSlide(array $data): int;
  public function updateSlide(int $id, array $data): bool;
  public function deleteSlide(int $id): bool;

  public function reorderSlides(array $orderedIds): bool;
}

class CarouselService implements ICarouselRepository
{
  private $db;

  public function __construct()
  {
    $this->db = Database::getInstance()->getConnection();
  }

  // ============================================================================
  // Carousel
  // ============================================================================

  /**
   * Lấy tất cả carousel chưa bị xóa.
   *
   * @public
   * @return Carousel[] Danh sách carousel không kèm slides
   */
  public function getAll(): array
  {
    $stmt = $this->db->prepare("
   SELECT * FROM `carousels`
   WHERE `deleted_at` IS NULL
   ORDER BY `id` ASC
  ");
    $stmt->execute();

    return array_map(fn($row) => Carousel::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  /**
   * Tìm một carousel theo ID, kèm danh sách slides.
   *
   * @public
   * @param int $id ID của carousel
   * @return Carousel|null Trả về null nếu không tìm thấy hoặc đã bị xóa
   */
  public function getById(int $id): ?Carousel
  {
    $stmt = $this->db->prepare("
   SELECT * FROM `carousels`
   WHERE `id` = :id AND `deleted_at` IS NULL
  ");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row)
      return null;

    $carousel = Carousel::fromArray($row);
    $carousel->slides = $this->getSlides($carousel->id);

    return $carousel;
  }

  /**
   * Tìm một carousel theo slug, kèm danh sách slides.
   * Đây là method chính dùng trong view landing page.
   *
   * @public
   * @param string $slug Slug của carousel, ví dụ: 'landing_page'
   * @return Carousel|null Trả về null nếu không tìm thấy hoặc đã bị xóa
   */
  public function getBySlug(string $slug): ?Carousel
  {
    $stmt = $this->db->prepare("
   SELECT * FROM `carousels`
   WHERE `slug` = :slug AND `deleted_at` IS NULL
  ");
    $stmt->execute([':slug' => $slug]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row)
      return null;

    $carousel = Carousel::fromArray($row);
    $carousel->slides = $this->getSlides($carousel->id);

    return $carousel;
  }

  /**
   * Tạo mới một carousel.
   *
   * @public
   * @param array $data Dữ liệu gồm name, slug, is_active
   * @return int ID của carousel vừa tạo
   */
  public function create(array $data): int
  {
    $stmt = $this->db->prepare("
   INSERT INTO `carousels` (`name`, `slug`, `is_active`)
   VALUES (:name, :slug, :is_active)
  ");
    $stmt->execute([
      ':name' => $data['name'],
      ':slug' => $data['slug'],
      ':is_active' => $data['is_active'] ?? 1,
    ]);

    return (int) $this->db->lastInsertId();
  }

  /**
   * Cập nhật thông tin một carousel theo ID.
   *
   * @public
   * @param int $id ID của carousel cần cập nhật
   * @param array $data Dữ liệu mới gồm name, slug, is_active
   * @return bool True nếu cập nhật thành công
   */
  public function update(int $id, array $data): bool
  {
    $stmt = $this->db->prepare("
   UPDATE `carousels` SET
    `name` = :name,
    `slug` = :slug,
    `is_active` = :is_active,
    `updated_at` = NOW()
   WHERE `id` = :id AND `deleted_at` IS NULL
  ");

    return $stmt->execute([
      ':name' => $data['name'],
      ':slug' => $data['slug'],
      ':is_active' => $data['is_active'] ?? 1,
      ':id' => $id,
    ]);
  }

  /**
   * Xóa mềm một carousel và toàn bộ slides thuộc nó.
   *
   * @public
   * @param int $id ID của carousel cần xóa
   * @return bool True nếu xóa thành công
   */
  public function delete(int $id): bool
  {
    $this->db->beginTransaction();

    try {
      $stmtSlides = $this->db->prepare("
    UPDATE `carousel_slides` SET `deleted_at` = NOW()
    WHERE `carousel_id` = :carousel_id AND `deleted_at` IS NULL
   ");
      $stmtSlides->execute([':carousel_id' => $id]);

      $stmtCarousel = $this->db->prepare("
    UPDATE `carousels` SET `deleted_at` = NOW()
    WHERE `id` = :id AND `deleted_at` IS NULL
   ");
      $stmtCarousel->execute([':id' => $id]);

      $this->db->commit();
      return true;
    } catch (\Exception $e) {
      $this->db->rollBack();
      return false;
    }
  }

  /**
   * Kiểm tra slug có duy nhất trong bảng carousels hay không.
   * Có thể loại trừ một ID cụ thể khi dùng cho trường hợp cập nhật.
   *
   * @public
   * @param string $slug Slug cần kiểm tra
   * @param int|null $excludeId ID carousel cần loại trừ (dùng khi update)
   * @return bool True nếu slug chưa được dùng
   */
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

  // ============================================================================
  // Carousel Slides
  // ============================================================================

  /**
   * Lấy tất cả slides của một carousel, sắp xếp theo sort_order.
   * Chỉ trả về slides chưa bị xóa, bao gồm cả slide đang tắt (is_active = 0).
   * View dùng $carousel->getActiveSlides() nếu chỉ muốn slide đang bật.
   *
   * @public
   * @param int $carouselId ID của carousel
   * @return CarouselSlide[]
   */
  public function getSlides(int $carouselId): array
  {
    $stmt = $this->db->prepare("
   SELECT * FROM `carousel_slides`
   WHERE `carousel_id` = :carousel_id AND `deleted_at` IS NULL
   ORDER BY `sort_order` ASC
  ");
    $stmt->execute([':carousel_id' => $carouselId]);

    return array_map(fn($row) => CarouselSlide::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  /**
   * Tìm một slide theo ID.
   *
   * @public
   * @param int $id ID của slide
   * @return CarouselSlide|null Trả về null nếu không tìm thấy hoặc đã bị xóa
   */
  public function getSlideById(int $id): ?CarouselSlide
  {
    $stmt = $this->db->prepare("
   SELECT * FROM `carousel_slides`
   WHERE `id` = :id AND `deleted_at` IS NULL
  ");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? CarouselSlide::fromArray($row) : null;
  }

  /**
   * Tạo mới một slide thuộc carousel.
   *
   * @public
   * @param array $data Dữ liệu slide gồm carousel_id, title, title_highlight,
   *                    description, image_path, image_alt, cta_label, cta_url,
   *                    cta_variant, custom_html, use_custom_html, sort_order, is_active
   * @return int ID của slide vua tao
   */
  public function createSlide(array $data): int
  {
    $stmt = $this->db->prepare("
   INSERT INTO `carousel_slides`
    (`carousel_id`, `title`, `title_highlight`, `description`,
     `image_path`, `image_alt`, `cta_label`, `cta_url`, `cta_variant`,
     `custom_html`, `use_custom_html`, `sort_order`, `is_active`)
   VALUES
    (:carousel_id, :title, :title_highlight, :description,
     :image_path, :image_alt, :cta_label, :cta_url, :cta_variant,
     :custom_html, :use_custom_html, :sort_order, :is_active)
  ");
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

  /**
   * Cập nhật thông tin một slide theo ID.
   *
   * @public
   * @param int $id ID của slide can cap nhat
   * @param array $data Dữ liệu mới của slide
   * @return bool True nếu cập nhật thành công
   */
  public function updateSlide(int $id, array $data): bool
  {
    $stmt = $this->db->prepare("
   UPDATE `carousel_slides` SET
    `title` = :title,
    `title_highlight` = :title_highlight,
    `description` = :description,
    `image_path` = :image_path,
    `image_alt` = :image_alt,
    `cta_label` = :cta_label,
    `cta_url` = :cta_url,
    `cta_variant` = :cta_variant,
    `custom_html` = :custom_html,
    `use_custom_html` = :use_custom_html,
    `sort_order` = :sort_order,
    `is_active` = :is_active,
    `updated_at` = NOW()
   WHERE `id` = :id AND `deleted_at` IS NULL
  ");

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

  /**
   * Xóa mềm một slide theo ID.
   *
   * @public
   * @param int $id ID của slide can xoa
   * @return bool True nếu xóa thành công
   */
  public function deleteSlide(int $id): bool
  {
    $stmt = $this->db->prepare("
   UPDATE `carousel_slides` SET `deleted_at` = NOW()
   WHERE `id` = :id AND `deleted_at` IS NULL
  ");

    return $stmt->execute([':id' => $id]);
  }

  /**
   * Cập nhật lại sort_order cho danh sách slides theo thứ tự mảng ID truyền vào.
   * Dùng cho tính năng drag-and-drop sắp xếp slide trong admin.
   *
   * @public
   * @param array $orderedIds Mảng ID slide theo thứ tự mới, ví dụ: [3, 1, 2]
   * @return bool True nếu toàn bộ cập nhật thành công
   */
  public function reorderSlides(array $orderedIds): bool
  {
    $this->db->beginTransaction();

    try {
      $stmt = $this->db->prepare("
    UPDATE `carousel_slides` SET `sort_order` = :sort_order
    WHERE `id` = :id AND `deleted_at` IS NULL
   ");

      foreach ($orderedIds as $order => $id) {
        $stmt->execute([
          ':sort_order' => $order + 1,
          ':id' => $id,
        ]);
      }

      $this->db->commit();
      return true;
    } catch (\Exception $e) {
      $this->db->rollBack();
      return false;
    }
  }
}