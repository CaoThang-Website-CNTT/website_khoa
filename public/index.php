<?php

define('BASE_PATH', dirname(__DIR__));

// Manual includes (guaranteed to work everywhere)
require_once BASE_PATH . '/includes/types.php';
require_once BASE_PATH . '/views/components/Header.php';
require_once BASE_PATH . '/views/components/Footer.php';
require_once BASE_PATH . '/views/layouts/HomepageLayout.php';
require_once BASE_PATH . '/views/Homepage.php';

use App\Views\Homepage;

echo (new Homepage())->render();
