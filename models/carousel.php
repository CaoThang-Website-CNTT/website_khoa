<?php

namespace App\Models;

class Carousel extends Model
{
  public function __construct(
    public int $id,
    public string $name,
    public string $slug,
    public bool $is_active,
    public ?string $created_at = null,
    public ?string $updated_at = null,
    public ?string $deleted_at = null,

    /** @var CarouselSlide[] */
    public array $slides = [],
  ) {
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