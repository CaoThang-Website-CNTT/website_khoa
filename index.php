<?php

use App\Models\Category;
use App\Services\CategoryService;
session_start();

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/core/router.php';
require_once __DIR__ . '/services/education_service.php';
require_once __DIR__ . '/services/category_service.php';
require_once __DIR__ . '/controllers/site_controller.php';
require_once __DIR__ . '/controllers/user_controller.php';
require_once __DIR__ . '/controllers/student_controller.php';
require_once __DIR__ . '/controllers/student_import_controller.php';
require_once __DIR__ . '/controllers/teacher_controller.php';
require_once __DIR__ . '/controllers/category_controller.php';

use App\Services\EducationService;

$router = require_once __DIR__ . '/routes.php';

$baseDir = '/website_khoa';
$requestUri = $_SERVER['REQUEST_URI'];
$uri = str_replace($baseDir, '', $requestUri);
$method = $_SERVER['REQUEST_METHOD'];

// --- Nơi khởi tạo các Service ---
$router->dispatch($uri, $method, [
  'educationService' => new EducationService(),
  'categoryService' => new CategoryService(),
]);
?>