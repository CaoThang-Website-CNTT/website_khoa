<?php

// Chỉ định PHP trả về lỗi nếu tham số của hàm/phương thức không đúng kiểu dữ liệu như khai báo
declare(strict_types=1);

namespace App\Core;

use App\Core\UI;

// Interface cơ bản cho View Components
interface IViewComponent
{
  /**
   * Render component thành chuỗi HTML
   */
  public function render(): string;
}

/**
 * Lớp trừu tượng cho View Component
 * Cung cấp các phương thức sử dụng các phương thức UI 
 */
abstract class ViewComponent implements IViewComponent
{
  /**
   * Hàm render chính (trả về HTML)
   */
  abstract public function render(): string;
  /**
   * Xử lý đường dẫn URL của asset
   */
  protected function asset(string $path): string
  {
    return UI::asset()->url($path);
  }
  /**
   * Render ra inline SVG
   *
   * @param string $name Tên của icon
   * @param array $attrs Thuộc tính HTML của icon (e.g. ['class' => 'icon', 'width' => 24])
   */
  protected function icon(string $name, array $attrs = []): string
  {
    return UI::asset()->svg($name, $attrs);
  }
  /**
   * Escape HTML (safe output)
   */
  protected function e(string $value): string
  {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }
}
