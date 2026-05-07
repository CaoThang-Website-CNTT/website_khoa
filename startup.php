<?php
// Kết nối các Dependency cần thiết cho App
define('BASE_PATH', __DIR__);

// App
require_once BASE_PATH . '/includes/env_loader.php';

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

// Session
require_once BASE_PATH . '/includes/core/session/session.php';
// Middelware
require_once BASE_PATH . '/includes/core/middleware/pipeline.php';
require_once BASE_PATH . '/includes/core/middleware/base_middleware.php';
require_once BASE_PATH . '/includes/core/middleware/start_session.php';
require_once BASE_PATH . '/includes/core/middleware/verify_csrf_token.php';
require_once BASE_PATH . '/includes/core/router.php';
// Request
require_once BASE_PATH . '/includes/core/request/request.php';
require_once BASE_PATH . '/includes/core/request/request_validator.php';
// Response
require_once BASE_PATH . '/includes/core/response/response.php';
require_once BASE_PATH . '/includes/core/response/json_response.php';
require_once BASE_PATH . "/includes/core/controller.php";
require_once BASE_PATH . '/includes/core/pageable.php';
require_once BASE_PATH . '/includes/files/uploaded_file_handler.php';

// Editor
require_once BASE_PATH . '/includes/editor/block_schema.php';
require_once BASE_PATH . '/includes/editor/block_renderer.php';

// Helpers
require_once BASE_PATH . '/includes/helpers.php';

// Model
require_once BASE_PATH . '/models/account.php';
require_once BASE_PATH . '/models/carousel_slide.php';
require_once BASE_PATH . '/models/carousel.php';
require_once BASE_PATH . '/models/category.php';
require_once BASE_PATH . '/models/classroom.php';
require_once BASE_PATH . '/models/major.php';
require_once BASE_PATH . '/models/menu_item.php';
require_once BASE_PATH . '/models/menu.php';
require_once BASE_PATH . '/models/specialization.php';
require_once BASE_PATH . '/models/student.php';
require_once BASE_PATH . '/models/teacher.php';
require_once BASE_PATH . '/models/web_setting.php';
require_once BASE_PATH . '/models/internship_batch.php';
require_once BASE_PATH . '/models/internship_batch_student.php';
require_once BASE_PATH . '/models/internship_batch_supervisor.php';
require_once BASE_PATH . '/models/internship_assignment.php';
require_once BASE_PATH . '/models/assignment_log.php';
require_once BASE_PATH . '/models/media.php';
require_once BASE_PATH . '/models/post.php';
require_once BASE_PATH . '/models/company.php';

// Store
require_once BASE_PATH . '/stores/account_store.php';
require_once BASE_PATH . '/stores/classroom_store.php';
require_once BASE_PATH . '/stores/student_store.php';
require_once BASE_PATH . '/stores/teacher_store.php';
require_once BASE_PATH . '/stores/category_store.php';
require_once BASE_PATH . '/stores/menu_store.php';
require_once BASE_PATH . '/stores/web_setting_store.php';
require_once BASE_PATH . '/stores/carousel_store.php';
require_once BASE_PATH . '/stores/internship_batch_store.php';
require_once BASE_PATH . '/stores/internship_assignment_store.php';
require_once BASE_PATH . '/stores/internship_submission_store.php';
require_once BASE_PATH . '/stores/media_store.php';
require_once BASE_PATH . '/stores/post_store.php';
require_once BASE_PATH . '/stores/company_store.php';

// Services
require_once BASE_PATH . '/services/google_oauth_service.php';
require_once BASE_PATH . '/services/student_service.php';
require_once BASE_PATH . '/services/teacher_service.php';
require_once BASE_PATH . '/services/classroom_service.php';
require_once BASE_PATH . '/services/category_service.php';
require_once BASE_PATH . '/services/menu_service.php';
require_once BASE_PATH . '/services/web_setting_service.php';
require_once BASE_PATH . '/services/carousel_service.php';
require_once BASE_PATH . '/services/internship_batch_service.php';
require_once BASE_PATH . '/services/internship_assignment_service.php';
require_once BASE_PATH . '/services/media_service.php';
require_once BASE_PATH . '/services/post_service.php';
require_once BASE_PATH . '/services/company_service.php';
require_once BASE_PATH . '/services/internship_submission_service.php';

// Controllers
// Web
require_once BASE_PATH . '/controllers/web/auth_controller.php';
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
require_once BASE_PATH . '/controllers/web/post_controller.php';
require_once BASE_PATH . '/controllers/web/internship_assignment_controller.php';
require_once BASE_PATH . '/controllers/web/internship_batch_controller.php';
require_once BASE_PATH . '/controllers/web/student_dashboard_controller.php';

// Api
require_once BASE_PATH . '/controllers/api/student_api_controller.php';
require_once BASE_PATH . '/controllers/api/internship_assignment_api_controller.php';
require_once BASE_PATH . '/controllers/api/internship_batch_api_controller.php';
require_once BASE_PATH . '/controllers/api/internship_batch_management_api_controller.php';
require_once BASE_PATH . '/controllers/api/media_api_controller.php';
require_once BASE_PATH . '/controllers/api/company_api_controller.php';
