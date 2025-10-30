<?php
define('BASE_PATH', __DIR__);

// Include autoload (tự include tất cả file từ thư mục định nghĩa)
require_once BASE_PATH . '/autoload.php';

use App\Views\Homepage;
use App\Core\{UI, AssetProvider};

$asset = new AssetProvider("/public");

UI::setAsset($asset);

echo (new Homepage())->render();
