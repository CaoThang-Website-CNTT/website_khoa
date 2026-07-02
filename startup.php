<?php
// Kết nối các Dependency cần thiết cho App

// Core
// Database
require_once BASE_PATH . '/includes/core/database.php';
// Schema
require_once BASE_PATH . '/includes/core/schema/column_type_trait.php';
require_once BASE_PATH . '/includes/core/schema/column_definition.php';
require_once BASE_PATH . '/includes/core/schema/foreign_definition.php';
require_once BASE_PATH . '/includes/core/schema/alter_builder.php';
require_once BASE_PATH . '/includes/core/schema/table_builder.php';
require_once BASE_PATH . '/includes/core/schema/query_builder.php';
require_once BASE_PATH . '/includes/core/schema/model.php';
// Schema Compiler
require_once BASE_PATH . '/includes/core/schema/compiler/base_sql_compiler.php';
require_once BASE_PATH . '/includes/core/schema/compiler/mysql_compiler.php';

// View
require_once BASE_PATH . '/includes/core/layout.php';
// Enums
require_once BASE_PATH . '/includes/core/enums/batch_status.php';

// Session
require_once BASE_PATH . '/includes/core/session/session.php';
// Middleware
require_once BASE_PATH . '/includes/core/middleware/pipeline.php';
require_once BASE_PATH . '/includes/core/middleware/base_middleware.php';
require_once BASE_PATH . '/includes/core/middleware/start_session.php';
require_once BASE_PATH . '/includes/core/middleware/verify_csrf_token.php';
// Custom Middlewares
require_once BASE_PATH . '/middlewares/has_dashboard_routing_trait.php';
require_once BASE_PATH . '/middlewares/guest_middleware.php';
require_once BASE_PATH . '/middlewares/verify_auth.php';
require_once BASE_PATH . '/middlewares/verify_role.php';

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
require_once BASE_PATH . '/includes/core/image_processor.php';
require_once BASE_PATH . '/includes/files/batch_student_importer.php';
require_once BASE_PATH . '/includes/files/xlsx_writer.php';

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
require_once BASE_PATH . '/models/internship_weekly_report.php';
require_once BASE_PATH . '/models/internship_weekly_report_image.php';
require_once BASE_PATH . '/models/internship_batch_supervisor.php';
require_once BASE_PATH . '/includes/core/enums/referral_letter_status.php';
require_once BASE_PATH . '/models/internship_assignment.php';
require_once BASE_PATH . '/models/assignment_log.php';
require_once BASE_PATH . '/models/media.php';
require_once BASE_PATH . '/models/post.php';
require_once BASE_PATH . '/models/cms_page.php';
require_once BASE_PATH . '/models/company.php';
require_once BASE_PATH . '/models/referral_letter.php';
require_once BASE_PATH . '/models/ticket.php';
require_once BASE_PATH . '/models/department.php';
require_once BASE_PATH . '/models/referral_letter_student.php';

// Editor (Post)
require_once BASE_PATH . '/includes/editor/rich_text_renderer.php';
require_once BASE_PATH . '/includes/editor/render_result.php';
require_once BASE_PATH . '/includes/editor/blocks/abstract_block_renderer.php';
require_once BASE_PATH . '/includes/editor/blocks/heading_renderer.php';
require_once BASE_PATH . '/includes/editor/blocks/paragraph_renderer.php';
require_once BASE_PATH . '/includes/editor/blocks/quote_renderer.php';
require_once BASE_PATH . '/includes/editor/blocks/list_renderer.php';
require_once BASE_PATH . '/includes/editor/blocks/image_renderer.php';
require_once BASE_PATH . '/includes/editor/blocks/table_renderer.php';
require_once BASE_PATH . '/includes/editor/block_validator.php';
require_once BASE_PATH . '/includes/editor/block_renderer.php';

// CMS
require_once BASE_PATH . '/includes/cms/cms_render_context.php';
require_once BASE_PATH . '/includes/cms/cms_section_definition_interface.php';
require_once BASE_PATH . '/includes/cms/cms_callback_section_definition.php';
require_once BASE_PATH . '/includes/cms/cms_section_registry.php';
require_once BASE_PATH . '/includes/cms/education_page_defaults.php';
require_once BASE_PATH . '/includes/cms/education_section_renderer.php';
require_once BASE_PATH . '/includes/cms/cms_static_page_renderer.php';
require_once BASE_PATH . '/includes/cms/cms_page_schema_registry.php';

// Mail
require_once BASE_PATH . '/includes/mail/PHPMailer/Exception.php';
require_once BASE_PATH . '/includes/mail/PHPMailer/PHPMailer.php';
require_once BASE_PATH . '/includes/mail/PHPMailer/SMTP.php';

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
require_once BASE_PATH . '/stores/internship_grade_store.php';
require_once BASE_PATH . '/stores/internship_weekly_report_store.php';
require_once BASE_PATH . '/stores/media_store.php';
require_once BASE_PATH . '/stores/post_store.php';
require_once BASE_PATH . '/stores/cms_page_store.php';
require_once BASE_PATH . '/stores/category_post_store.php';
require_once BASE_PATH . '/stores/company_store.php';
require_once BASE_PATH . '/stores/referral_letter_store.php';
require_once BASE_PATH . '/stores/referral_letter_student_store.php';
require_once BASE_PATH . '/stores/ticket_store.php';
require_once BASE_PATH . '/stores/department_store.php';
require_once BASE_PATH . '/stores/email_job_store.php';

// Services
require_once BASE_PATH . '/services/account_service.php';
require_once BASE_PATH . '/services/auth_service.php';
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
require_once BASE_PATH . '/services/cms_page_service.php';
require_once BASE_PATH . '/services/company_service.php';
require_once BASE_PATH . '/services/internship_submission_service.php';
require_once BASE_PATH . '/services/internship_grade_service.php';
require_once BASE_PATH . '/services/internship_weekly_report_service.php';
require_once BASE_PATH . '/services/referral_letter_service.php';
require_once BASE_PATH . '/services/ticket_service.php';
require_once BASE_PATH . '/services/mail_service.php';

// Controllers
// Web
require_once BASE_PATH . '/controllers/web/auth_controller.php';
require_once BASE_PATH . '/controllers/web/account_controller.php';
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
require_once BASE_PATH . '/controllers/web/media_controller.php';
require_once BASE_PATH . '/controllers/web/post_controller.php';
require_once BASE_PATH . '/controllers/web/cms_page_controller.php';
require_once BASE_PATH . '/controllers/web/internship_batch_controller.php';
require_once BASE_PATH . '/controllers/web/student_dashboard_controller.php';
require_once BASE_PATH . '/controllers/web/company_controller.php';
require_once BASE_PATH . '/controllers/web/teacher_dashboard_controller.php';
require_once BASE_PATH . '/controllers/web/ticket_controller.php';

// Api
require_once BASE_PATH . '/controllers/api/account_api_controller.php';
require_once BASE_PATH . '/controllers/api/student_api_controller.php';
require_once BASE_PATH . '/controllers/api/internship_assignment_api_controller.php';
require_once BASE_PATH . '/controllers/api/internship_batch_api_controller.php';
require_once BASE_PATH . '/controllers/api/internship_batch_management_api_controller.php';
require_once BASE_PATH . '/controllers/api/media_api_controller.php';
require_once BASE_PATH . '/controllers/api/carousel_api_controller.php';
require_once BASE_PATH . '/controllers/api/menu_api_controller.php';
require_once BASE_PATH . '/controllers/api/company_api_controller.php';
require_once BASE_PATH . '/controllers/api/post_api_controller.php';
require_once BASE_PATH . '/controllers/api/teacher_dashboard_api_controller.php';
require_once BASE_PATH . '/controllers/api/export_api_controller.php';
require_once BASE_PATH . '/controllers/api/classroom_api_controller.php';
