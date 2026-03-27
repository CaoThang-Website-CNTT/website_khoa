<?php
namespace App\Services;

require_once BASE_PATH . '/stores/carousel_store.php';
require_once BASE_PATH . '/models/carousel.php';
require_once BASE_PATH . '/models/carousel_slide.php';

use App\Stores\CarouselStore;
use App\Models\Carousel;
use App\Models\CarouselSlide;

interface ICarouselService
{
  /** @return Carousel[] */
  public function getAll(): array;
  /** @return Carousel[] Danh sách tất cả carousel kèm theo các slide của chúng */
  public function getAllWithSlides(): array;
  public function getById(int $id): ?Carousel;
  public function getBySlug(string $slug): ?Carousel;
  public function getWithSlides(int $id): ?Carousel;
  public function getBySlugWithSlides(string $slug): ?Carousel;
  public function create(array $data): int;
  public function update(int $id, array $data): bool;
  public function delete(int $id): bool;
  public function isSlugUnique(string $slug, ?int $excludeId = null): bool;
  public function addSlide(int $carouselId, array $data): int;
  public function updateSlide(int $id, array $data): bool;
  public function deleteSlide(int $id): bool;
  public function reorderSlides(int $moveId, string $direction): bool;
}

class CarouselService implements ICarouselService
{
  private CarouselStore $_carouselStore;

  public function __construct(CarouselStore $carouselStore)
  {
    $this->_carouselStore = $carouselStore;
  }

  /** @return Carousel[] */
  public function getAll(): array
  {
    return $this->_carouselStore->getAll();
  }

  /** @return Carousel[] */
  public function getAllWithSlides(): array
  {
    $carousels = $this->_carouselStore->getAll();

    foreach ($carousels as $carousel) {
      $carousel->slides = $this->_carouselStore->getSlides($carousel->id);
    }

    return $carousels;
  }

  public function getById(int $id): ?Carousel
  {
    return $this->_carouselStore->getById($id);
  }

  public function getBySlug(string $slug): ?Carousel
  {
    return $this->_carouselStore->getBySlug($slug);
  }

  public function getWithSlides(int $id): ?Carousel
  {
    $carousel = $this->_carouselStore->getById($id);
    if ($carousel === null) {
      return null;
    }
    $carousel->slides = $this->_carouselStore->getSlides($carousel->id);
    return $carousel;
  }

  public function getBySlugWithSlides(string $slug): ?Carousel
  {
    $carousel = $this->_carouselStore->getBySlug($slug);
    if ($carousel === null) {
      return null;
    }
    $carousel->slides = $this->_carouselStore->getSlides($carousel->id);
    return $carousel;
  }

  public function create(array $data): int
  {
    if (!$this->_carouselStore->isSlugUnique($data['slug'])) {
      throw new \InvalidArgumentException("Slug '{$data['slug']}' đã tồn tại.");
    }
    return $this->_carouselStore->create($data);
  }

  public function update(int $id, array $data): bool
  {
    $carousel = $this->_carouselStore->getById($id);
    if ($carousel === null) {
      return false;
    }
    if ($data['slug'] !== $carousel->slug && !$this->_carouselStore->isSlugUnique($data['slug'], $id)) {
      throw new \InvalidArgumentException("Slug '{$data['slug']}' đã tồn tại.");
    }
    return $this->_carouselStore->update($id, $data);
  }

  public function delete(int $id): bool
  {
    return $this->_carouselStore->delete($id);
  }

  public function isSlugUnique(string $slug, ?int $excludeId = null): bool
  {
    return $this->_carouselStore->isSlugUnique($slug, $excludeId);
  }

  public function addSlide(int $carouselId, array $data): int
  {
    if ($this->_carouselStore->getById($carouselId) === null) {
      throw new \RuntimeException("Carousel #$carouselId không tồn tại.");
    }
    $data['carousel_id'] = $carouselId;
    return $this->_carouselStore->createSlide($data);
  }

  public function updateSlide(int $id, array $data): bool
  {
    // Cần phải check xem slide có tồn tại hay không, bạn có thể bổ sung logic getSlideById ở Store
    // Ở đây dựa theo file gốc bạn chưa có $this->_carouselStore->getSlideById() trong code cung cấp,
    // nhưng nếu đã có ở Store thì giữ nguyên dòng dưới:
    // if ($this->_carouselStore->getSlideById($id) === null) { return false; }

    return $this->_carouselStore->updateSlide($id, $data);
  }

  public function deleteSlide(int $id): bool
  {
    return $this->_carouselStore->deleteSlide($id);
  }

  public function reorderSlides(int $moveId, string $direction): bool
  {
    return $this->_carouselStore->reorderSlides($moveId, $direction);
  }
}