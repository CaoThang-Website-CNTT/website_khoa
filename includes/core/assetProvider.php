<?php

// Chỉ định PHP trả về lỗi nếu tham số của hàm/phương thức không đúng kiểu dữ liệu như khai báo
declare(strict_types=1);

namespace App\Core;

// Sử dụng trả ra lỗi runtime
use RuntimeException;

/**
 * Lớp xử lý cung cấp asset
 */
final class AssetProvider
{
  /** Đường dẫn tới thư mục chứa asset */
  private string $publicDir;
  /**
   * Hàm khởi tạo
   */
  public function __construct(
    string $publicDir = DIRECTORY_SEPARATOR,
  ) {
    // Bỏ '/' ở cuối
    $this->publicDir = dirname($_SERVER["SCRIPT_NAME"]) . rtrim($publicDir, DIRECTORY_SEPARATOR);
  }
  /** Public URL cho asset  */
  public function url(string $path): string
  {
    $assetPath = $this->publicDir . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    // Chuẩn hóa \\ -> /
    $assetPath = str_replace('\\', DIRECTORY_SEPARATOR, $assetPath);
    return $assetPath;
  }
  /** Getter cho `$publicDir` */
  public function getPublicDir(): string
  {
    return $this->publicDir;
  }
  /** Phương thức xử lý và trả về Inline SVG */
  public function svg(string $name, array $attrs = []): string
  {
    $name = pathinfo($name, PATHINFO_EXTENSION) ? $name : "$name.svg";
    $file = $_SERVER["DOCUMENT_ROOT"] . $this->publicDir . '/icons' . DIRECTORY_SEPARATOR . $name;

    if (!is_file($file)) throw new RuntimeException("<!--- Icon $name not found ---!>");

    $svg = file_get_contents($file);

    $attrStr = '';
    foreach ($attrs as $k => $v) {
      $attrStr .= ' ' . htmlspecialchars($k) . '="' . htmlspecialchars($v) . '"';
    }

    return preg_replace('/<svg/i', "<svg{$attrStr}", $svg, 1);
  }
}
