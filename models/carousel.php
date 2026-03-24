<?php

namespace App\Models;

class Carousel
{
  public function __construct(
    public int $id,
    public string $name,
    public string $slug,
    public bool $is_active,
    public string $created_at,
    public string $updated_at,

    /** @var CarouselSlide[] */
    public array $slides = [],
  ) {
  }

  /**
   * Tự động mapping trường dữ liệu DB
   *
   * @param array $data
   * @return self
   */
  public static function fromArray(array $data): self
  {
    return new self(
      id: (int) $data['id'],
      name: $data['name'],
      slug: $data['slug'],
      is_active: (bool) $data['is_active'],
      created_at: $data['created_at'],
      updated_at: $data['updated_at'],
    );
  }

  /**
   * Kiểm tra carousel có đang được hiển thị không.
   * Dùng để guard render trong view/controller.
   *
   * @return bool
   */
  public function isActive(): bool
  {
    return $this->is_active;
  }

  /**
   * Kiểm tra carousel có slide nào không.
   * Dùng để guard render trong view.
   *
   * @return bool
   */
  public function hasSlides(): bool
  {
    return !empty($this->slides);
  }

  /**
   * Lấy danh sách các slide đang active, giữ nguyên thứ tự sort_order.
   * Dùng trong view để chỉ render slide đang bật.
   *
   * @return CarouselSlide[]
   */
  public function getActiveSlides(): array
  {
    return array_values(
      array_filter($this->slides, fn(CarouselSlide $slide) => $slide->isActive())
    );
  }
}