<?php

namespace App\Core;

use App\Core\AssetProvider;

/**
 * Lớp UI để setup các DI cho View Component
 */
final class UI
{
  /** Chứa các phương thức xử lý đường dẫn asset*/
  private static AssetProvider $asset;

  public static function setAsset(AssetProvider $asset): void
  {
    self::$asset = $asset;
  }

  public static function asset(): AssetProvider
  {
    return self::$asset;
  }
}
