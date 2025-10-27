<?php
// Chỉ định PHP trả về lỗi nếu tham số của hàm/phương thức không đúng kiểu dữ liệu như khai báo
declare(strict_types=1);

// Namespace cho truy cập dễ dàng
namespace App\Types;

// Interface cơ bản cho View Components
interface IBaseViewComponent
{
  /**
   * Render component thành chuỗi HTML
   */
  public function render(): string;
}
