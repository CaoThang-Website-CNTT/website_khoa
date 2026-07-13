<?php

namespace App\Console\Commands;

use App\Console\Commands\BaseCommand;
use App\Console\ConsoleColor;

class StorageLinkCommand extends BaseCommand
{
  protected string $name = 'storage-link';
  protected string $paramsDescription = '<storage_path> <public_path>';
  protected string $description = 'Tạo liên kết symbolic link từ storage sang thư mục public';

  public function handle(array $args): void
  {
    if (count($args) < 2) {
      $this->showUsage();
      return;
    }

    $storagePath = $args[0];
    $publicPath = $args[1];

    ConsoleColor::logLabel('STORAGE-LINK', "Khởi động quy trình tạo liên kết storage...", ConsoleColor::BG_BLUE);

    $targetAbsolute = $this->normalizePath(BASE_PATH, $storagePath);
    $linkAbsolute = $this->normalizePath(BASE_PATH, $publicPath);

    if (!is_dir($targetAbsolute)) {
      ConsoleColor::error("Thư mục storage gốc không tồn tại: {$targetAbsolute}");
      return;
    }

    if (file_exists($linkAbsolute) || is_link($linkAbsolute)) {
      if (is_link($linkAbsolute)) {
        if (!@unlink($linkAbsolute)) {
          ConsoleColor::error("Không thể xóa symlink cũ: {$linkAbsolute}");
          return;
        }
        echo "  " . ConsoleColor::colorText("→ Đã xóa symlink cũ", ConsoleColor::GRAY) . "\n";
      } elseif (is_file($linkAbsolute)) {
        if (!@unlink($linkAbsolute)) {
          ConsoleColor::error("Không thể xóa file trùng tên: {$linkAbsolute}");
          return;
        }
        echo "  " . ConsoleColor::colorText("→ Đã xóa file trùng tên", ConsoleColor::GRAY) . "\n";
      } elseif (is_dir($linkAbsolute)) {
        ConsoleColor::error("Thư mục đã tồn tại tại vị trí: {$linkAbsolute}");
        return;
      }
    }

    try {
      $linkDir = dirname($linkAbsolute);
      if (!is_dir($linkDir)) {
        if (!mkdir($linkDir, 0755, true)) {
          ConsoleColor::error("Không thể tạo thư mục: {$linkDir}");
          return;
        }
        echo "  " . ConsoleColor::colorText("→ Đã tạo thư mục: {$linkDir}", ConsoleColor::GRAY) . "\n";
      }

      // Tạo symlink
      if (!$this->createDirectoryLink($targetAbsolute, $linkAbsolute)) {
        $lastError = error_get_last();
        $errorMsg = $lastError['message'] ?? 'Không rõ nguyên nhân';
        ConsoleColor::error("Lỗi khi tạo symlink: {$errorMsg}");
        echo "  " . ConsoleColor::colorText("[!] Tip:", ConsoleColor::YELLOW) . " Trên Windows, cần chạy CMD/PowerShell với quyền Administrator.\n";
        return;
      }

      ConsoleColor::success(
        "Đã thiết lập symlink thành công từ [" . 
        ConsoleColor::colorText($storagePath, ConsoleColor::BLUE) . 
        "] sang [" . 
        ConsoleColor::colorText($publicPath, ConsoleColor::BLUE) . 
        "]"
      );

    } catch (\Throwable $e) {
      ConsoleColor::error("Exception: " . $e->getMessage());
    }
  }

  /**
   * Chuẩn hóa đường dẫn tương đối thành đường dẫn tuyệt đối
   * @param string $basePath Đường dẫn gốc (thường là BASE_PATH)
   * @param string $relativePath Đường dẫn tương đối do người dùng nhập
   * @return string Đường dẫn tuyệt đối đã chuẩn hóa
   */
  private function normalizePath(string $basePath, string $relativePath): string
  {
    // Nếu là đường dẫn tuyệt đối, trả về ngay
    if (str_starts_with($relativePath, '/') || 
        (strlen($relativePath) > 1 && $relativePath[1] === ':')) {
      return $relativePath;
    }

    // Nối basePath với relativePath và chuẩn hóa dấu gạch chéo
    $absolute = rtrim($basePath, '/\\') . DIRECTORY_SEPARATOR . ltrim($relativePath, '/\\');
    
    // Loại bỏ . và .. trong đường dẫn
    $absolute = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $absolute);
    $parts = array_filter(explode(DIRECTORY_SEPARATOR, $absolute), fn($p) => $p !== '' && $p !== '.');
    
    $resolved = [];
    foreach ($parts as $part) {
      if ($part === '..') {
        array_pop($resolved);
      } else {
        $resolved[] = $part;
      }
    }

    // Xây dựng lại đường dẫn cuối cùng
    $result = implode(DIRECTORY_SEPARATOR, $resolved);
    
    // Thêm lại ổ đĩa trên Windows nếu có
    if (strlen($result) > 1 && $result[1] === ':') {
      return $result;
    }
    
    return (PHP_OS_FAMILY === 'Windows' ? '' : DIRECTORY_SEPARATOR) . $result;
  }

  /**
   * Hiển thị hướng dẫn sử dụng lệnh
   */
  private function showUsage(): void
  {
    echo "\n";
    ConsoleColor::logLabel('ERROR', "Thiếu tham số!", ConsoleColor::BG_RED);
    echo "\n  " . ConsoleColor::colorText("CÚ PHÁP:", ConsoleColor::YELLOW) . "\n";
    echo "    php ctsdk.php storage-link <storage_path> <public_path>\n\n";
    echo "  " . ConsoleColor::colorText("VÍ DỤ:", ConsoleColor::YELLOW) . "\n";
    echo "    php ctsdk.php storage-link storage/media public/media\n";
    echo "    php ctsdk.php storage-link storage public/storage\n";
    echo "    php ctsdk.php storage-link includes/core/storage public/storage\n\n";
    echo "  " . ConsoleColor::colorText("GIẢI THÍCH:", ConsoleColor::CYAN) . "\n";
    echo "    - storage_path : Đường dẫn tương đối hoặc tuyệt đối của thư mục gốc (target)\n";
    echo "    - public_path  : Đường dẫn tương đối hoặc tuyệt đối của liên kết ảo (link)\n\n";
  }

  private function createDirectoryLink(string $targetAbsolute, string $linkAbsolute): bool
  {
    if (@symlink($targetAbsolute, $linkAbsolute)) {
      return true;
    }

    $lastError = error_get_last();
    $errorMsg = $lastError['message'] ?? 'Unknown reason';

    if (PHP_OS_FAMILY !== 'Windows') {
      ConsoleColor::error("Could not create symlink: {$errorMsg}");
      return false;
    }

    echo "  " . ConsoleColor::colorText("[!] Symlink failed:", ConsoleColor::YELLOW) . " {$errorMsg}\n";
    echo "  " . ConsoleColor::colorText("-> Trying Windows directory junction...", ConsoleColor::GRAY) . "\n";

    $command = 'cmd /c mklink /J ' . escapeshellarg($linkAbsolute) . ' ' . escapeshellarg($targetAbsolute);
    exec($command, $output, $exitCode);

    if ($exitCode !== 0 || !is_dir($linkAbsolute)) {
      ConsoleColor::error("Could not create junction. Run the terminal as Administrator or enable Windows Developer Mode.");
      if (!empty($output)) {
        echo implode("\n", $output) . "\n";
      }
      return false;
    }

    return true;
  }
}
