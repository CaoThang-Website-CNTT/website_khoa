<?php

use App\Controllers\{AuthController, DashboardController, MenuController, SiteController, StudentController, StudentImportController, TeacherController, CategoryController, WebSettingsController, CarouselController, ClassroomController, PostController};
use App\Core\Router;

$router->get('/', [SiteController::class, 'index']);
$router->get('/admin', [DashboardController::class, 'index']);

// Auth
$router->get('/login', [AuthController::class, 'show']);
$router->get('/login/oauth/callback', [AuthController::class, 'googleOAuthCallback']);
$router->get('/onboarding', [AuthController::class, 'onboard']);
$router->post('/onboarding', [AuthController::class, 'completeOnboarding']);

$router->prefix('admin')->group(function (Router $router) {
  $router->get('/', [DashboardController::class, 'index']);

  // Media

  // Posts
  $router->prefix('posts')->group(function ($router) {
    $router->get('/', [PostController::class, 'index']);
    $router->post('/', [PostController::class, 'store']);
    $router->get('/create', [PostController::class, 'create']);
    $router->get('/{post_id}', [PostController::class, 'show']);
    $router->put('/{post_id}', [PostController::class, 'update']);
    $router->delete('/{post_id}', [PostController::class, 'delete']);
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

    // Nested Menu Items mapped to a specific Menu
    $router->prefix('{menuId}/items')->group(function ($router) {
      $router->get('/create', [MenuController::class, 'createItem']);
      $router->post('/', [MenuController::class, 'storeItem']);
      $router->post('/reorder', [MenuController::class, 'reorderItems']);
    });
  });

  // Menu Items
  $router->prefix('menu-items')->group(function ($router) {
    $router->get('/{itemId}/edit', [MenuController::class, 'editItem']);
    $router->post('/{itemId}', [MenuController::class, 'updateItem']);
    $router->post('/{itemId}/delete', [MenuController::class, 'destroyItem']);
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

  // Carousels
  $router->prefix('carousels')->group(function ($router) {
    $router->get('/', [CarouselController::class, 'index']);
    $router->get('/create', [CarouselController::class, 'create']);
    $router->post('/', [CarouselController::class, 'store']);
    $router->get('/{id}', [CarouselController::class, 'edit']);
    $router->post('/{id}', [CarouselController::class, 'update']);
    $router->post('/delete/{id}', [CarouselController::class, 'destroy']);

    // Carousel Slides mapped to a specific Carousel
    $router->prefix('{carouselId}/slides')->group(function ($router) {
      $router->get('/', [CarouselController::class, 'slides']);
      $router->get('/create', [CarouselController::class, 'createSlide']);
      $router->post('/', [CarouselController::class, 'storeSlide']);
      $router->post('/reorder', [CarouselController::class, 'reorder']);
      $router->get('/{slideId}', [CarouselController::class, 'editSlide']);
      $router->post('/{slideId}', [CarouselController::class, 'updateSlide']);
      $router->post('/delete/{slideId}', [CarouselController::class, 'destroySlide']);
    });
  });
});