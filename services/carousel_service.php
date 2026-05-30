<?php
namespace App\Services;

require_once BASE_PATH . '/stores/carousel_store.php';
require_once BASE_PATH . '/models/carousel.php';
require_once BASE_PATH . '/models/carousel_slide.php';

use App\Core\Pageable;
use App\Models\{Carousel, CarouselSlide};
use App\Stores\{CarouselStore, MediaStore};
use Database;


interface ICarouselService
{
  public function getCarousels(int $page, int $limit = 15): Pageable;
  /** @return Carousel[] Danh sách tất cả carousel kèm theo các slide của chúng */
  public function getAllWithSlides(): array;
  public function getCarouselById(int $id): ?Carousel;
  public function getBySlug(string $slug): ?Carousel;
  public function getWithSlides(int $id): ?Carousel;
  public function getBySlugWithSlides(string $slug, bool $with_media = false): ?Carousel;
  public function create(array $data): ?Carousel;
  public function update(int $id, array $data): bool;
  public function delete(int $id): bool;
  public function isSlugUnique(string $slug, ?int $excludeId = null): bool;
  public function addSlide(int $carouselId, array $data): int;
  public function updateSlide(int $id, array $data): bool;
  public function deleteSlide(int $id): bool;
  public function getSlideById(int $id): ?CarouselSlide;
  public function sortSlides(array $ids): bool;
}

class CarouselService implements ICarouselService
{
  private CarouselStore $_carouselStore;
  private MediaStore $_mediaStore;

  public function __construct(
    CarouselStore $carouselStore,
    MediaStore $mediaStore
  ) {
    $this->_carouselStore = $carouselStore;
    $this->_mediaStore = $mediaStore;
  }

  public function getCarousels(int $page, int $limit = 15): Pageable
  {
    $carousels = $this->_carouselStore->getPaginated($page, $limit);
    $total = $this->_carouselStore->getTotalCarouselsCount();
    return new Pageable($carousels, $total, $limit, $page);
  }

  /** @return Carousel[] */
  public function getAllWithSlides(): array
  {
    $carousels = $this->_carouselStore->getAll();

    return $carousels;
  }

  public function getCarouselById(int $id): ?Carousel
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

    // Truy vấn media
    $mediaIds = [];
    foreach ($carousel->slides as $slide) {
      if ($slide->media_id) {
        $mediaIds[] = $slide->media_id;
      }
    }
    $mediaList = array_column($this->_mediaStore->getByIds($mediaIds), null, 'id');
    foreach ($carousel->slides as $slide) {
      if ($slide->media_id) {
        $slide->media = $mediaList[$slide->media_id];
      }
    }
    return $carousel;
  }

  public function getBySlugWithSlides(string $slug, bool $with_media = false): ?Carousel
  {
    $carousel = $this->_carouselStore->getBySlug($slug);
    if ($carousel === null) {
      return null;
    }
    $carousel->slides = $this->_carouselStore->getSlides($carousel->id);
    if ($with_media) {
      $mediaIds = [];
      foreach ($carousel->slides as $slide) {
        if ($slide->media_id) {
          $mediaIds[] = $slide->media_id;
        }
      }
      $mediaList = array_column($this->_mediaStore->getByIds($mediaIds), null, 'id');
      foreach ($carousel->slides as $slide) {
        if ($slide->media_id) {
          $slide->media = $mediaList[$slide->media_id];
        }
      }
    }
    return $carousel;
  }

  public function create(array $data): ?Carousel
  {
    return Database::getInstance()->transaction(function () use ($data) {

      $slug = trim($data['slug'] ?? '');
      if ($slug === '') {
        $slug = generateSlug($data['name']);
      }

      if (!$this->_carouselStore->isSlugUnique($slug)) {
        throw new \InvalidArgumentException("Slug '{$slug}' đã tồn tại.");
      }

      $rawSlides = is_array($data['slides'] ?? null) ? $data['slides'] : [];
      $slides = array_values(array_map(function (array $slide) {
        $customHtml = trim($slide['custom_html'] ?? '');
        $useCustomHtml = !empty($slide['use_custom_html'])
          ? 1
          : ($customHtml !== '' ? 1 : 0);

        return [
          'title' => trim($slide['title']),
          'title_highlight' => $slide['title_highlight'] ?? null,
          'description' => $slide['description'] ?? null,
          'media_id' => $slide['media_id'] ?? null,
          'cta_label' => $slide['cta_label'] ?? null,
          'cta_url' => $slide['cta_url'] ?? null,
          'cta_variant' => in_array($slide['cta_variant'] ?? '', ['primary', 'secondary', 'outline'])
            ? $slide['cta_variant']
            : 'primary',
          'custom_html' => $useCustomHtml ? $customHtml : null,
          'use_custom_html' => $useCustomHtml,
          'sort_order' => (int) ($slide['sort_order'] ?? 0),
          'is_active' => !empty($slide['is_active']) ? 1 : 0,
        ];
      }, $rawSlides));

      $carouselId = $this->_carouselStore->create([
        'name' => trim($data['name']),
        'slug' => $slug,
        'is_active' => !empty($data['is_active']) ? 1 : 0,
      ]);

      foreach ($slides as $slide) {
        $this->_carouselStore->createSlide(['carousel_id' => $carouselId] + $slide);
      }

      return $this->getWithSlides($carouselId);
    });
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

    // Cập nhật thông tin carousel
    $isSuccess = $this->_carouselStore->update($id, [
      'name' => $data['name'],
      'slug' => $data['slug'],
      'is_active' => $data['is_active'],
    ]);
    if (!$isSuccess) {
      return false;
    }

    // Nếu có dữ liệu sắp xếp lại slides
    if (!empty($data['reorder'])) {
      $slideIds = json_decode($data['reorder'], true);
      if (is_array($slideIds)) {
        $normalizedIds = array_map('intval', $slideIds);
        $sortSuccess = $this->_carouselStore->sortSlides($normalizedIds);
        if (!$sortSuccess) {
          return false;
        }
      }
    }

    return true;
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
    if ($this->getSlideById($id) === null) {
      return false;
    }

    // Kiểm tra media_id có hợp lệ không
    if (!empty($data['media_id'])) {
      $media = $this->_mediaStore->getById($data['media_id']);
      if ($media === null) {
        throw new \InvalidArgumentException("Media #{$data['media_id']} không tồn tại.");
      }
    }

    return $this->_carouselStore->updateSlide($id, $data);
  }

  public function deleteSlide(int $id): bool
  {
    return $this->_carouselStore->deleteSlide($id);
  }

  public function getSlideById(int $id): ?CarouselSlide
  {
    return $this->_carouselStore->getSlideById($id);
  }

  public function sortSlides(array $ids): bool
  {
    return $this->_carouselStore->sortSlides($ids);
  }
}