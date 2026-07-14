<?php

namespace App\Core;

use DateTime;

class AppTime
{
  /**
   * Lấy đối tượng DateTime hiện tại. Tự động hỗ trợ giả lập thời gian nếu có cấu hình.
   *
   * @return DateTime
   */
  public static function isDebug(): bool
  {
    return (getenv('APP_DEBUG') === 'true' || (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true'));
  }

  public static function now(): DateTime
  {
    if (self::isDebug()) {
      $filePath = __DIR__ . '/../../storage/mock_time.json';

      if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        if ($content) {
          $config = json_decode($content, true);
          if (!empty($config['enabled']) && !empty($config['value'])) {
            try {
              return new DateTime($config['value']);
            } catch (\Exception $e) {
              // Fallback to normal time if parsing fails
            }
          }
        }
      }
    }

    return new DateTime();
  }

  /**
   * Lấy timestamp hiện tại
   *
   * @return int
   */
  public static function time(): int
  {
    return self::now()->getTimestamp();
  }
}
