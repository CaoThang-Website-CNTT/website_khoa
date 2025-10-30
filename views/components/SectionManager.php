<?php

namespace App\Views\Components;

use App\Types\IBaseViewComponent;

/**
 * Quản lý mảng các thành phần giao diện, giúp thêm động các thành phần tuỳ chọn vào giao diện
 */
class SectionManager
{
  private array $sections = [];

  /**
   * Thêm một section vào danh sách
   */
  public function add(IBaseViewComponent $section): self
  {
    $this->sections[] = $section;
    return $this;
  }

  /**
   * Thêm nhiều section cùng lúc
   */
  public function addMany(array $sections): self
  {
    foreach ($sections as $section) {
      if ($section instanceof IBaseViewComponent) {
        $this->add($section);
      }
    }
    return $this;
  }

  /**
   * Xóa tất cả section
   */
  public function clear(): self
  {
    $this->sections = [];
    return $this;
  }

  /**
   * Lấy danh sách section
   */
  public function getSections(): array
  {
    return $this->sections;
  }

  /**
   * Render tất cả section
   */
  public function render(): string
  {
    $output = '';
    foreach ($this->sections as $section) {
      $output .= $section->render();
    }
    return $output;
  }
}
