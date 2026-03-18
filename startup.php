<?php
// Kết nối các Dependency cần thiết cho App
define('BASE_PATH', __DIR__);

// Core
require_once BASE_PATH . '/includes/core/request.php';
require_once BASE_PATH . '/includes/core/router.php';

// Helpers
require_once BASE_PATH . '/includes/helpers.php';

// Services
require_once BASE_PATH . '/services/education_service.php';
require_once BASE_PATH . '/services/category_service.php';

// Controllers
require_once BASE_PATH . '/controllers/site_controller.php';
require_once BASE_PATH . '/controllers/user_controller.php';
require_once BASE_PATH . '/controllers/student_controller.php';
require_once BASE_PATH . '/controllers/student_import_controller.php';
require_once BASE_PATH . '/controllers/teacher_controller.php';
require_once BASE_PATH . '/controllers/category_controller.php';