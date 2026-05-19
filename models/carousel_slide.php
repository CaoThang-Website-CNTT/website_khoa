<?php

namespace App\Models;

class CarouselSlide extends Model
{
  public function __construct(
    public int $id,
    public int $carousel_id,
    public string $title,
    public ?string $title_highlight,
    public ?string $description,
    public string $image_path,
    public string $image_alt,
    public ?string $cta_label,
    public ?string $cta_url,
    public string $cta_variant,
    public ?string $custom_html,
    public bool $use_custom_html,
    public int $sort_order,
    public bool $is_active,
    public ?string $created_at = null,
    public ?string $updated_at = null,
    public ?string $deleted_at = null,
  ) {
  }

  /**
   * Kiểm tra slide có đang được hiển thị không.
   * Dùng để guard render trong view/controller.
   *
   * @return bool
   */
  public function isActive(): bool
  {
    return $this->is_active;
  }

  /**
   * Kiểm tra slide có nút CTA không.
   * Dùng để guard render button trong view.
   *
   * @return bool
   */
  public function hasCta(): bool
  {
    return $this->cta_label !== null && $this->cta_url !== null;
  }

  /**
   * Kiểm tra slide có dùng custom HTML không.
   * Nếu true, view nên render $custom_html thay vì các trường cấu trúc.
   *
   * @return bool
   */
  public function isCustom(): bool
  {
    return $this->use_custom_html && $this->custom_html !== null;
  }

  /**
   * Kiểm tra slide có tiêu đề nổi bật không.
   * Dùng để guard render <span> title_highlight trong view.
   *
   * @return bool
   */
  public function hasHighlight(): bool
  {
    return $this->title_highlight !== null;
  }

  /**
   * Kiểm tra variant của nút CTA có khớp không.
   * Dùng để gán class CSS tương ứng trong view.
   *
   * @param string $variant primary | secondary
   * @return bool
   */
  public function isCta(string $variant): bool
  {
    return $this->cta_variant === $variant;
  }
}