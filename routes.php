<?php

use App\Controllers\{DashboardController, MenuController, SiteController, StudentController, StudentImportController, TeacherController, CategoryController, WebSettingsController, CarouselController, ClassroomController};

$router->get('/', [SiteController::class, 'index']);
$router->get('/admin', [DashboardController::class, 'index']);

// Students
$router->get('/admin/students', [StudentController::class, 'index']);
$router->get('/admin/students/create', [StudentController::class, 'create']);
$router->post('/admin/students', [StudentController::class, 'store']);
$router->get('/admin/students/import', [StudentController::class, 'import']);
$router->post('/admin/students/import', [StudentImportController::class, 'store']);
$router->get('/admin/students/{student_id}', [StudentController::class, 'edit']);
$router->post('/admin/students/{student_id}', [StudentController::class, 'update']);
$router->post('/admin/students/delete/{student_id}', [StudentController::class, 'destroy']);

// Teachers
$router->get('/admin/teachers', [TeacherController::class, 'index']);
$router->get('/admin/teachers/create', [TeacherController::class, 'create']);
$router->post('/admin/teachers', [TeacherController::class, 'store']);
$router->get('/admin/teachers/{id}', [TeacherController::class, 'edit']);
$router->post('/admin/teachers/{id}', [TeacherController::class, 'update']);
$router->post('/admin/teachers/delete/{id}', [TeacherController::class, 'destroy']);

// Classrooms
$router->get('/admin/classrooms', [ClassroomController::class, 'index']);
$router->get('/admin/classrooms/create', [ClassroomController::class, 'create']);
$router->post('/admin/classrooms', [ClassroomController::class, 'store']);
$router->get('/admin/classrooms/{id}', [ClassroomController::class, 'edit']);
$router->post('/admin/classrooms/{id}', [ClassroomController::class, 'update']);
$router->post('/admin/classrooms/delete/{id}', [ClassroomController::class, 'destroy']);

// Categories
$router->get('/admin/categories', [CategoryController::class, 'index']);
$router->get('/admin/categories/create', [CategoryController::class, 'create']);
$router->post('/admin/categories', [CategoryController::class, 'store']);
$router->get('/admin/categories/{id}', [CategoryController::class, 'edit']);
$router->post('/admin/categories/{id}', [CategoryController::class, 'update']);
$router->post('/admin/categories/delete/{id}', [CategoryController::class, 'destroy']);

// Menus
$router->get('/admin/menus', [MenuController::class, 'index']);
$router->get('/admin/menus/create', [MenuController::class, 'create']);
$router->post('/admin/menus', [MenuController::class, 'store']);
$router->get('/admin/menus/{id}', [MenuController::class, 'edit']);
$router->post('/admin/menus/{id}', [MenuController::class, 'update']);
$router->post('/admin/menus/delete/{id}', [MenuController::class, 'destroy']);

// Menu Items
$router->get('/admin/menus/{menuId}/items/create', [MenuController::class, 'createItem']);
$router->post('/admin/menus/{menuId}/items', [MenuController::class, 'storeItem']);
$router->post('/admin/menus/{menuId}/items/reorder', [MenuController::class, 'reorderItems']);
$router->get('/admin/menu-items/{itemId}/edit', [MenuController::class, 'editItem']);
$router->post('/admin/menu-items/{itemId}', [MenuController::class, 'updateItem']);
$router->post('/admin/menu-items/{itemId}/delete', [MenuController::class, 'destroyItem']);

// Web Settings
$router->get('/admin/web_settings', [WebSettingsController::class, 'index']);
$router->get('/admin/web_settings/create', [WebSettingsController::class, 'create']);
$router->post('/admin/web_settings', [WebSettingsController::class, 'store']);
$router->get('/admin/web_settings/{group}/edit', [WebSettingsController::class, 'edit']);
$router->post('/admin/web_settings/{group}', [WebSettingsController::class, 'batchUpdate']);
$router->post('/admin/web_settings/delete/{id}', [WebSettingsController::class, 'destroy']);

// Carousel
// Carousels
$router->get('/admin/carousels', [CarouselController::class, 'index']);
$router->get('/admin/carousels/create', [CarouselController::class, 'create']);
$router->post('/admin/carousels', [CarouselController::class, 'store']);
$router->get('/admin/carousels/{id}', [CarouselController::class, 'edit']);
$router->post('/admin/carousels/{id}', [CarouselController::class, 'update']);
$router->post('/admin/carousels/delete/{id}', [CarouselController::class, 'destroy']);

// Carousel Slides
$router->get('/admin/carousels/{carouselId}/slides', [CarouselController::class, 'slides']);
$router->get('/admin/carousels/{carouselId}/slides/create', [CarouselController::class, 'createSlide']);
$router->post('/admin/carousels/{carouselId}/slides', [CarouselController::class, 'storeSlide']);
$router->post('/admin/carousels/{carouselId}/slides/reorder', [CarouselController::class, 'reorder']);
$router->get('/admin/carousels/{carouselId}/slides/{slideId}', [CarouselController::class, 'editSlide']);
$router->post('/admin/carousels/{carouselId}/slides/{slideId}', [CarouselController::class, 'updateSlide']);
$router->post('/admin/carousels/{carouselId}/slides/delete/{slideId}', [CarouselController::class, 'destroySlide']);