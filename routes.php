<?php

use App\Controllers\{AccountController, AuthController, DashboardController, MenuController, SiteController, StudentController, StudentImportController, TeacherController, CategoryController, WebSettingsController, CarouselController, ClassroomController, PostController, MediaController, InternshipBatchController, StudentDashboardController, CompanyController, TeacherDashboardController, TicketController, SitemapController, CmsPageController, ProjectBatchController, ProjectAllocationController, ProjectEligibilityController, StudentProjectDashboardController, TeacherProjectDashboardController};
use App\Middlewares\{GuestMiddleware, VerifyAuth, VerifyRole};
use App\Core\Router;

// Site
$router->get('/', [SiteController::class, 'index']);
$router->get('/sitemap.xml', [SitemapController::class, 'index']);
$router->prefix('tin-tuc')->group(function (Router $router) {
  $router->get('/', [SiteController::class, 'news_index']);
  $router->get('/{slug}', [SiteController::class, 'news_show']);
});
$router->get('/gioi-thieu', [SiteController::class, 'about']);
$router->get('/giang-vien', [SiteController::class, 'faculty']);
$router->get('/viec-lam/doanh-nghiep', [SiteController::class, 'partners']);
$router->get('/dao-tao', [SiteController::class, 'education']);
$router->get('/dao-tao/tuyen-sinh', [SiteController::class, 'admissions']);
$router->get('/dao-tao/chuong-trinh-dao-tao', [SiteController::class, 'academicPrograms']);
$router->get('/dao-tao/chuan-dau-ra', [SiteController::class, 'programOutcomes']);
$router->get('/dao-tao/danh-sach-mon-hoc', [SiteController::class, 'curriculum']);
$router->get('/lien-he', [SiteController::class, 'contact']);
// Đăng ký route /portal để tự chuyển hướng theo role thay vì phải set cứng
$router->get('/portal', [SiteController::class, 'portal'])->middleware([VerifyAuth::class]);

// Auth
$router->get('/login', [AuthController::class, 'show'])->middleware([GuestMiddleware::class]);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/login/oauth/callback', [AuthController::class, 'googleOAuthCallback']);
$router->get('/onboarding', [AuthController::class, 'onboard']);
$router->post('/onboarding', [AuthController::class, 'completeOnboarding']);
$router->get('/logout', [AuthController::class, 'logout']);

// Admin
$router->prefix('admin')->middleware([VerifyAuth::class, new VerifyRole('admin', 'editor', 'super_admin')])->group(function (Router $router) {
  $router->get('/', [DashboardController::class, 'index']);

  // Accounts
  $router->prefix('accounts')->group(function ($router) {
    $router->get('/', [AccountController::class, 'index']);
    $router->get('/create', [AccountController::class, 'create']);
    $router->post('/', [AccountController::class, 'store']);
    $router->get('/{account_id}', [AccountController::class, 'edit']);
    $router->post('/{account_id}', [AccountController::class, 'update']);
    $router->delete('/{account_id}', [AccountController::class, 'destroy']);
  });

  // Media
  $router->prefix('media')->group(function ($router) {
    $router->get('/', [MediaController::class, 'index']);
    $router->get('/create', [MediaController::class, 'create']);
    $router->post('/', [MediaController::class, 'store']);
    $router->get('/{media_id}', [MediaController::class, 'edit']);
    $router->post('/{media_id}', [MediaController::class, 'update']);
    $router->delete('/{media_id}', [MediaController::class, 'destroy']);
  });

  // Posts
  $router->prefix('posts')->group(function ($router) {
    $router->get('/', [PostController::class, 'index']);
    $router->get('/create', [PostController::class, 'create']);
    $router->post('/', [PostController::class, 'store']);
    $router->get('/{post_id}', [PostController::class, 'show']);
    $router->put('/{post_id}', [PostController::class, 'update']);
    $router->delete('/{post_id}', [PostController::class, 'destroy']);
  });

  // Students
  $router->prefix('students')->group(function ($router) {
    $router->get('/', [StudentController::class, 'index']);
    $router->get('/create', [StudentController::class, 'create']);
    $router->post('/', [StudentController::class, 'store']);

    $router->get('/import', [StudentController::class, 'import']);
    $router->post('/import', [StudentImportController::class, 'store']);

    $router->get('/{student_id}', [StudentController::class, 'edit']);
    $router->post('/{student_id}', [StudentController::class, 'update']);
    $router->post('/delete/{student_id}', [StudentController::class, 'destroy']);
  });

  // Teachers
  $router->prefix('teachers')->group(function ($router) {
    $router->get('/', [TeacherController::class, 'index']);
    $router->get('/create', [TeacherController::class, 'create']);
    $router->post('/', [TeacherController::class, 'store']);
    $router->get('/{id}', [TeacherController::class, 'edit']);
    $router->post('/{id}', [TeacherController::class, 'update']);
    $router->post('/delete/{id}', [TeacherController::class, 'destroy']);
  });

  // Classrooms
  $router->prefix('classrooms')->group(function ($router) {
    $router->get('/', [ClassroomController::class, 'index']);
    $router->get('/create', [ClassroomController::class, 'create']);
    $router->post('/', [ClassroomController::class, 'store']);
    $router->get('/{id}', [ClassroomController::class, 'edit']);
    $router->post('/{id}', [ClassroomController::class, 'update']);
    $router->post('/delete/{id}', [ClassroomController::class, 'destroy']);
  });

  // Categories
  $router->prefix('categories')->group(function ($router) {
    $router->get('/', [CategoryController::class, 'index']);
    $router->get('/create', [CategoryController::class, 'create']);
    $router->post('/', [CategoryController::class, 'store']);
    $router->get('/{id}', [CategoryController::class, 'edit']);
    $router->post('/{id}', [CategoryController::class, 'update']);
    $router->post('/delete/{id}', [CategoryController::class, 'destroy']);
  });

  // Menus
  $router->prefix('menus')->group(function ($router) {
    $router->get('/', [MenuController::class, 'index']);
    $router->get('/create', [MenuController::class, 'create']);
    $router->post('/', [MenuController::class, 'store']);
    $router->get('/{id}', [MenuController::class, 'edit']);
    $router->post('/{id}', [MenuController::class, 'update']);
    $router->post('/delete/{id}', [MenuController::class, 'destroy']);

    // Cần có menu_id để biết menu_item thuộc menu nào?
    // Nên có 2 route sẽ thuộc sub route của menu. Còn lại có route riêng (LƯU Ý).
    $router->prefix('{menu_id}/items')->group(function ($router) {
      $router->get('/create', [MenuController::class, 'createItem']);
      $router->post('/', [MenuController::class, 'storeItem']);
    });
  });

  // Menu Items
  $router->prefix('menu-items')->group(function ($router) {
    $router->get('/{item_id}/edit', [MenuController::class, 'editItem']);
    $router->post('/{item_id}', [MenuController::class, 'updateItem']);
    $router->post('/{item_id}/delete', [MenuController::class, 'destroyItem']);
  });

  // Carousels
  $router->prefix('carousels')->group(function ($router) {
    $router->get('/', [CarouselController::class, 'index']);
    $router->get('/create', [CarouselController::class, 'create']);
    $router->post('/', [CarouselController::class, 'store']);
    $router->get('/{id}', [CarouselController::class, 'edit']);
    $router->post('/{id}', [CarouselController::class, 'update']);
    $router->post('/delete/{id}', [CarouselController::class, 'destroy']);

    // Cần có slide_id để biết slide_item thuộc slide nào?
    // Nên có 2 route sẽ thuộc sub route của slide. Còn lại có route riêng (LƯU Ý).
    $router->prefix('{carousel_id}/slides')->group(function ($router) {
      $router->get('/create', [CarouselController::class, 'createSlide']);
      $router->post('/', [CarouselController::class, 'storeSlide']);
    });
  });

  // Carousel Slides
  $router->prefix('carousel-slides')->group(function ($router) {
    $router->get('/{slide_id}/edit', [CarouselController::class, 'editSlide']);
    $router->post('/{slide_id}', [CarouselController::class, 'updateSlide']);
    $router->post('/{slide_id}/delete', [CarouselController::class, 'destroySlide']);
  });

  // Web Settings
  $router->prefix('web_settings')->group(function ($router) {
    $router->get('/', [WebSettingsController::class, 'index']);
    $router->get('/create', [WebSettingsController::class, 'create']);
    $router->post('/', [WebSettingsController::class, 'store']);
    $router->get('/{group}/edit', [WebSettingsController::class, 'edit']);
    $router->post('/{group}', [WebSettingsController::class, 'batchUpdate']);
    $router->post('/delete/{id}', [WebSettingsController::class, 'destroy']);
  });

  // CMS Pages
  $router->prefix('cms-pages')->group(function ($router) {
    $router->get('/', [CmsPageController::class, 'index']);
    $router->post('/{slug}/preview', [CmsPageController::class, 'preview']);
    $router->get('/{slug}', [CmsPageController::class, 'edit']);
    $router->post('/{slug}', [CmsPageController::class, 'update']);
    $router->post('/{slug}/publish', [CmsPageController::class, 'publish']);
  });

  // Internship Batches
  $router->prefix('internship_batches')->group(function ($router) {
    $router->get('/', [InternshipBatchController::class, 'index']);
    $router->get('/create', [InternshipBatchController::class, 'create']);
    $router->get('/{id}', [InternshipBatchController::class, 'show']);
    $router->post('/{id}', [InternshipBatchController::class, 'update']);
    $router->post('/delete/{id}', [InternshipBatchController::class, 'destroy']);
    $router->post('/{id}/publish', [InternshipBatchController::class, 'publish']);
    $router->post('/{id}/close', [InternshipBatchController::class, 'close']);

    // Referral Letters
    $router->get('/{id}/referral_letters', [InternshipBatchController::class, 'referralLetters']);
    $router->post('/{id}/referral_letters/bulk-print', [InternshipBatchController::class, 'bulkPrintReferralLetters']);
    $router->post('/{id}/referral_letters/bulk-print/confirm', [InternshipBatchController::class, 'confirmBulkPrint']);
    $router->get('/{id}/referral_letters/{letterId}/print', [InternshipBatchController::class, 'printReferralLetter']);
    $router->post('/{id}/referral_letters/{letterId}/print', [InternshipBatchController::class, 'confirmPrint']);

    // Students
    $router->get('/{id}/students', [InternshipBatchController::class, 'students']);

    // Teachers
    $router->get('/{id}/teachers', [InternshipBatchController::class, 'teachers']);
  });

  // Project Batches
  $router->prefix('project_batches')->group(function ($router) {
    $router->get('/', [ProjectBatchController::class, 'index']);
    $router->get('/create', [ProjectBatchController::class, 'create']);
    $router->post('/', [ProjectBatchController::class, 'store']);
    $router->get('/{id}', [ProjectBatchController::class, 'show']);
    $router->post('/{id}', [ProjectBatchController::class, 'update']);
    $router->post('/delete/{id}', [ProjectBatchController::class, 'destroy']);
    $router->post('/{id}/publish', [ProjectBatchController::class, 'publish']);
    $router->post('/{id}/close', [ProjectBatchController::class, 'close']);

    // Topics
    $router->get('/{id}/topics', [ProjectBatchController::class, 'topics']);
    $router->get('/{id}/teachers', [ProjectBatchController::class, 'teachers']);

    // Allocation 
    $router->get('/{id}/allocation', [ProjectAllocationController::class, 'index']);
    $router->post('/{id}/allocation/auto', [ProjectAllocationController::class, 'autoAllocate']);
    $router->post('/{id}/allocation/manual', [ProjectAllocationController::class, 'manualAssign']);

    // Eligibility
    $router->get('/{id}/eligibility', [ProjectEligibilityController::class, 'index']);
    $router->post('/{id}/eligibility/preview', [ProjectEligibilityController::class, 'preview']);
    $router->post('/{id}/eligibility/confirm', [ProjectEligibilityController::class, 'confirm']);
  });


  // Companies
  $router->prefix('companies')->group(function ($router) {
    $router->get('/', [CompanyController::class, 'index']);
    $router->get('/create', [CompanyController::class, 'create']);
    $router->post('/', [CompanyController::class, 'store']);

    $router->get('/duplicates', [CompanyController::class, 'duplicates']);
    $router->post('/bulk-approve', [CompanyController::class, 'bulkApprove']);
    $router->post('/quick-merge', [CompanyController::class, 'quickMerge']);
    $router->post('/bulk-quick-merge', [CompanyController::class, 'bulkQuickMerge']);

    $router->get('/{id}', [CompanyController::class, 'edit']);
    $router->post('/{id}', [CompanyController::class, 'update']);
    $router->post('/delete/{id}', [CompanyController::class, 'destroy']);

    $router->post('/{id}/approve', [CompanyController::class, 'approve']);
    $router->get('/{id}/merge', [CompanyController::class, 'mergeForm']);
    $router->post('/{id}/merge', [CompanyController::class, 'merge']);
  });

  // Tickets
  $router->prefix('tickets')->group(function ($router) {
    $router->get('/', [TicketController::class, 'index']);
    $router->get('/create', [TicketController::class, 'create']);
    $router->post('/', [TicketController::class, 'store']);
    $router->get('/{ticket_id}', [TicketController::class, 'show']);
    $router->get('/{ticket_id}/edit', [TicketController::class, 'edit']);
    $router->post('/{ticket_id}', [TicketController::class, 'update']);
  });
});

// Student Dashboard
$router->prefix('student')->middleware([VerifyAuth::class, new VerifyRole('student')])->group(function (Router $router) {
  // Thông tin tổng quan, tài khoản.
  $router->get('/', [StudentDashboardController::class, 'index']);
  // Thông tin thực tập.
  $router->get('/internship', [StudentDashboardController::class, 'internshipRedirect']);
  $router->get('/internship/{batch_id}', [StudentDashboardController::class, 'internship']);
  // Thông tin đồ án tốt nghiệp
  $router->prefix('project_batches')->group(function ($router) {
    $router->get('/', [StudentProjectDashboardController::class, 'index']);
    $router->get('/{id}', [StudentProjectDashboardController::class, 'show']);

    // Quản lý nhóm
    $router->post('/{id}/group/create', [StudentProjectDashboardController::class, 'createGroup']);
    $router->post('/{id}/group/confirm', [StudentProjectDashboardController::class, 'confirmGroup']);
    $router->post('/{id}/group/reject', [StudentProjectDashboardController::class, 'rejectGroup']);
    $router->post('/{id}/group/cancel', [StudentProjectDashboardController::class, 'cancelGroupInvite']);

    // Quản lý nguyện vọng
    $router->get('/{id}/topics', [StudentProjectDashboardController::class, 'topics']);
    $router->post('/{id}/aspirations/add', [StudentProjectDashboardController::class, 'addAspiration']);
    $router->post('/{id}/aspirations/remove', [StudentProjectDashboardController::class, 'removeAspiration']);
    $router->post('/{id}/aspirations/reorder', [StudentProjectDashboardController::class, 'reorderAspirations']);
    $router->post('/{id}/aspirations/lock', [StudentProjectDashboardController::class, 'lockAspirations']);
    $router->post('/{id}/aspirations/unlock', [StudentProjectDashboardController::class, 'unlockAspirations']);
  });
  // Cập nhật thông tin cá nhân
  $router->post('/profile/update', [StudentDashboardController::class, 'updateProfile']);

  // Khai báo công ty thực tập
  $router->post('/internship/{batch_id}/company', [StudentDashboardController::class, 'updateCompany']);
  // Nộp tài liệu
  $router->post('/internship/{batch_id}/upload', [StudentDashboardController::class, 'uploadSubmission']);
  // Đăng ký giấy giới thiệu
  $router->get('/internship/{batch_id}/referral_letters/create', [StudentDashboardController::class, 'createReferralLetter']);
  $router->post('/internship/{batch_id}/referral_letters', [StudentDashboardController::class, 'requestReferralLetter']);

  // Báo cáo tuần
  $router->get('/internship/{batch_id}/weekly_reports', [StudentDashboardController::class, 'weeklyReports']);
  $router->post('/internship/{batch_id}/weekly_reports', [StudentDashboardController::class, 'submitWeeklyReport']);

  // Trang danh sách giấy giới thiệu
  $router->get('/internship/{batch_id}/referral_letters', [StudentDashboardController::class, 'referralLetters']);
  // Trang chi tiết giấy giới thiệu
  $router->get('/internship/{batch_id}/referral_letters/{letter_id}', [StudentDashboardController::class, 'showReferralLetter']);
  // Hủy giấy giới thiệu
  $router->post('/internship/{batch_id}/referral_letters/{letter_id}/cancel', [StudentDashboardController::class, 'cancelReferralLetter']);
});

// Teacher Dashboard
$router->prefix('teacher')->middleware([VerifyAuth::class, new VerifyRole('teacher')])->group(function (Router $router) {
  // Thông tin tổng quan, tài khoản.
  $router->get('/', [TeacherDashboardController::class, 'index']);
  // Cập nhật thông tin cá nhân
  $router->post('/profile/update', [TeacherDashboardController::class, 'updateProfile']);
  // Thông tin thực tập.
  $router->prefix('internship_batches')->group(function ($router) {
    $router->get('/', [TeacherDashboardController::class, 'internshipIndex']);
    $router->get('/{id}', [TeacherDashboardController::class, 'internshipShow']);
    $router->get('/{batchId}/weekly_reports', [TeacherDashboardController::class, 'weeklyReports']);
    $router->get('/{batchId}/student/{batchStudentId}', [TeacherDashboardController::class, 'studentDetail']);
    $router->get('/{batchId}/grade/{batchStudentId}', [TeacherDashboardController::class, 'internshipGrade']);
    $router->post('/{batchId}/grade/{batchStudentId}', [TeacherDashboardController::class, 'submitGrade']);
    $router->post('/{batchId}/publish_grades', [TeacherDashboardController::class, 'publishAllGrades']);
  });
  // Thông tin đồ án
  $router->prefix('project_batches')->group(function ($router) {
    $router->get('/', [TeacherProjectDashboardController::class, 'index']);
    $router->get('/{id}', [TeacherProjectDashboardController::class, 'show']);
    $router->get('/{id}/groups/{groupId}/registration-form', [TeacherProjectDashboardController::class, 'previewRegistrationForm']);
    $router->post('/{id}/registration-forms/preview', [TeacherProjectDashboardController::class, 'previewRegistrationForms']);
    $router->post('/{id}/registration-forms/save', [TeacherProjectDashboardController::class, 'saveRegistrationForms']);
    $router->get('/{id}/topics/create', [TeacherProjectDashboardController::class, 'createTopic']);
    $router->post('/{id}/topics/create', [TeacherProjectDashboardController::class, 'storeTopic']);
    $router->get('/{id}/topics/{topicId}/edit', [TeacherProjectDashboardController::class, 'editTopic']);
    $router->post('/{id}/topics/{topicId}/edit', [TeacherProjectDashboardController::class, 'updateTopic']);
    $router->post('/{id}/topics/{topicId}/submit', [TeacherProjectDashboardController::class, 'submitTopic']);
    $router->post('/{id}/topics/{topicId}/delete', [TeacherProjectDashboardController::class, 'deleteTopic']);
  });
});
