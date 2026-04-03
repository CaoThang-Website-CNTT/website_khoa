<?php
// Kết nối các Dependency cần thiết cho App
define('BASE_PATH', __DIR__);

// Core
// Database
require_once BASE_PATH . '/includes/core/database.php';
// Schema
require_once BASE_PATH . '/includes/core/schema/column_definition.php';
require_once BASE_PATH . '/includes/core/schema/foreign_definition.php';
require_once BASE_PATH . '/includes/core/schema/table_builder.php';
require_once BASE_PATH . '/includes/core/schema/query_builder.php';
require_once BASE_PATH . '/includes/core/schema/model.php';
// Schema Compiler
require_once BASE_PATH . '/includes/core/schema/compiler/base_sql_compiler.php';
require_once BASE_PATH . '/includes/core/schema/compiler/mysql_compiler.php';

require_once BASE_PATH . '/includes/core/router.php';
require_once BASE_PATH . '/includes/core/request.php';
require_once BASE_PATH . '/includes/core/response/response.php';
require_once BASE_PATH . '/includes/core/response/json_response.php';
require_once BASE_PATH . '/includes/core/request_validator.php';
require_once BASE_PATH . "/includes/core/controller.php";
require_once BASE_PATH . '/includes/core/pageable.php';
require_once BASE_PATH . '/includes/files/uploaded_file_handler.php';

// Helpers
require_once BASE_PATH . '/includes/helpers.php';

// Store
require_once BASE_PATH . '/stores/account_store.php';
require_once BASE_PATH . '/stores/classroom_store.php';
require_once BASE_PATH . '/stores/student_store.php';
require_once BASE_PATH . '/stores/teacher_store.php';
require_once BASE_PATH . '/stores/category_store.php';
require_once BASE_PATH . '/stores/menu_store.php';
require_once BASE_PATH . '/stores/web_setting_store.php';
require_once BASE_PATH . '/stores/carousel_store.php';

// Services
require_once BASE_PATH . '/services/student_service.php';
require_once BASE_PATH . '/services/teacher_service.php';
require_once BASE_PATH . '/services/classroom_service.php';
require_once BASE_PATH . '/services/category_service.php';
require_once BASE_PATH . '/services/menu_service.php';
require_once BASE_PATH . '/services/web_setting_service.php';
require_once BASE_PATH . '/services/carousel_service.php';

// Controllers
// Web
require_once BASE_PATH . '/controllers/web/site_controller.php';
require_once BASE_PATH . '/controllers/web/dashboard_controller.php';
require_once BASE_PATH . '/controllers/web/student_controller.php';
require_once BASE_PATH . '/controllers/web/student_import_controller.php';
require_once BASE_PATH . '/controllers/web/teacher_controller.php';
require_once BASE_PATH . '/controllers/web/classroom_controller.php';
require_once BASE_PATH . '/controllers/web/category_controller.php';
require_once BASE_PATH . '/controllers/web/menu_controller.php';
require_once BASE_PATH . '/controllers/web/web_setting_controller.php';
require_once BASE_PATH . '/controllers/web/carousel_controller.php';

// Api
require_once BASE_PATH . '/controllers/api/student_api_controller.php';
