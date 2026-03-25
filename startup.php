<?php
// Kết nối các Dependency cần thiết cho App
define('BASE_PATH', __DIR__);

// Core
require_once BASE_PATH . '/includes/core/request.php';
require_once BASE_PATH . '/includes/core/router.php';
require_once BASE_PATH . '/includes/files/uploaded_file_handler.php';
require_once BASE_PATH . '/includes/core/page.php';

// Helpers
require_once BASE_PATH . '/includes/helpers.php';

// Services
require_once BASE_PATH . '/services/education_service.php';
require_once BASE_PATH . '/services/classroom_service.php';
require_once BASE_PATH . '/services/category_service.php';
require_once BASE_PATH . '/services/menu_service.php';
require_once BASE_PATH . '/services/web_setting_service.php';
require_once BASE_PATH . '/services/carousel_service.php';

// Controllers
require_once BASE_PATH . '/controllers/site_controller.php';
require_once BASE_PATH . '/controllers/dashboard_controller.php';
require_once BASE_PATH . '/controllers/student_controller.php';
require_once BASE_PATH . '/controllers/student_import_controller.php';
require_once BASE_PATH . '/controllers/teacher_controller.php';
require_once BASE_PATH . '/controllers/classroom_controller.php';
require_once BASE_PATH . '/controllers/category_controller.php';
require_once BASE_PATH . '/controllers/menu_controller.php';
require_once BASE_PATH . '/controllers/web_setting_controller.php';
require_once BASE_PATH . '/controllers/carousel_controller.php';