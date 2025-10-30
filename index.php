<?php

define('BASE_PATH', __DIR__);

// Include thủ công (phải tự động hóa trong tương lai)
require_once BASE_PATH . '/includes/core/ui.php';
require_once BASE_PATH . '/includes/core/assetProvider.php';
require_once BASE_PATH . '/includes/core/viewComponent.php';
require_once BASE_PATH . '/views/components/Header.php';
require_once BASE_PATH . '/views/components/Footer.php';
require_once BASE_PATH . '/views/layouts/HomepageLayout.php';
require_once BASE_PATH . '/views/Homepage.php';

use App\Views\Homepage;
use App\Core\{UI, AssetProvider};

$asset = new AssetProvider("/public");

UI::setAsset($asset);

echo (new Homepage())->render();
